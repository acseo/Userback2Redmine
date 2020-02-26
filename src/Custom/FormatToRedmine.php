<?php

namespace App\Custom;

trait FormatToRedmine
{
    private function formatToRedmine($key, $value) {
        if ($key == 'start_date') {
            $date =  new \Datetime($value);
            return $date->format('Y-m-d');
        }

        if ($key == 'subject') {
            if (strlen(trim($value)) == 0) {
                return "Unknown subject";
            }
            return substr($value, 0, 255);
        }

        if ($key == 'status_id') {
            // You have access to the Redmine issueStatuses here with : $this->issueStatuses;
            
            // Example : convert a Userback issue status to a Redmine issue status id
            // if ($value == 'Lorem Ipsum') {
            //     foreach($this->issueStatuses as $i => $is) {
            //         if ($is['name'] == 'Dolor sit') {
            //             return $is['id'];
            //         }
            //     }
            // }
        }

        return $value;
    }
}