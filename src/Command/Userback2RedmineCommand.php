<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Yaml\Yaml;
use League\Csv\Reader;
use Symfony\Component\Console\Helper\ProgressBar;

class UserBack2RedmineCommand extends Command
{
    // Custom trait used to format data to redmine.
    use \App\Custom\FormatToRedmine;

    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'sync';
    
    protected $customFields;
    protected $config;
    protected $issueMapping;

    protected function configure()
    {
        $this
            ->addArgument('config', InputArgument::REQUIRED, 'The configuration file.')
            ->addArgument('issues', InputArgument::REQUIRED, 'The Userback issues, CSV format')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            '=============',
            '<comment>Userback to Redmine</comment>',
            '=============',
            '',
        ]);
        
        $configFile = $input->getArgument('config');
        $config = Yaml::parseFile($configFile);

        $client = new \Redmine\Client($config['redmine']['url'], $config['redmine']['access_key']);

        // Getting custom fields
        $this->customFields = $client->custom_fields->all()['custom_fields'];

        // Getting statuses
        $this->issueStatuses = $client->issue_status->all()['issue_statuses'];

        // Get the issue mapping without the null (not mapped) values of the CSV file
        $this->issueMapping = array_filter($config['issues']['mapping'], function($v) {
            return $v != null;
        });

        // CSV column to custom fiels codes
        $identifier = $config['issues']['identifier'];
        foreach($this->customFields as $cf) {
            // Identifier
            if ($cf['name'] == $config['issues']['identifier']['redmine']) {
                $identifier  = 'cf_'.$cf['id'];
            }
            // Issue mapping
            $key = array_search($cf['name'], $config['issues']['mapping']);
            if ($key !== false) {
                $issueMapping[$key] = ['id' => $cf['id']];
            }
        }


        $issueFile = $input->getArgument('issues');
        $csv = Reader::createFromPath($issueFile, 'r');
        $csv->setDelimiter(',');
        $csv->setHeaderOffset(0);
       
        $records = $csv->getRecords();
        $nbRecords = count($csv);

        $progressBar = new ProgressBar($output, $nbRecords);
        ProgressBar::setFormatDefinition('custom', ' %current%/%max% -- %message%');

        $progressBar->setFormat('custom');
        $progressBar->setMessage('Start');
        $progressBar->start();
        foreach ($records as $offset => $record) {
            
            $issueData = [
                'project_id' => $config['redmine']['project'],
                'custom_fields' => []
            ];


            foreach($record as $key => $value) {
                if (isset($issueMapping[$key])) {
                    if (is_array($issueMapping[$key])) {

                        $issueData['custom_fields'][] = [ 
                            'id' => $issueMapping[$key]['id'],
                            'value' => $value
                        ];
                    }
                    else {
                        $issueData[$issueMapping[$key]] = $value = $this->formatToRedmine($issueMapping[$key], $value);
                    }
                }
            }

            $progressBar->setMessage("Searching if issue with $identifier = ".$record[$config['issues']['identifier']['userback']]. " exists... ");
            $existingIssues = $client->issue->all([
                $identifier => $record[$config['issues']['identifier']['userback']],
                'project_id' => $config['redmine']['project'],
            ])['issues'];  

            if (sizeof($existingIssues) == 0) {
                $progressBar->setMessage("The issue doesn't exists. <info>Creating it.</info>");
                $result = $client->issue->create($issueData);
                if (isset(json_decode(json_encode($result), true)['error'])) {
                    $output->writeln("There was an error while creating the issue : <error>".json_decode(json_encode($result), true)['error']."</error>");
                }
            } elseif (sizeof($existingIssues) == 1) {
                $progressBar->setMessage("The issue exists (#".$existingIssues[0]['id']."). <comment>Updating it.</comment>");
                $result = $client->issue->update($existingIssues[0]['id'], $issueData);
                if (isset(json_decode(json_encode($result), true)['error'])) {
                    $output->writeln("There was an error while updating the issue : <error>".json_decode(json_encode($result), true)['error']."</error>");
                }
            } else {
                $output->writeln("More than one issue have been found with $identifier = ".$record[$config['issues']['identifier']['userback']]. "... <comment> Skipping it.</comment>");
            }
            $progressBar->advance();
        }  
        $progressBar->finish();     
        

        $output->writeln([
            '',
            '=============',
            'Transfer from Userback to Redmine <info>is done !</info>',
            '=============',
            '',
        ]);        
        return 0;
    }

    
}
