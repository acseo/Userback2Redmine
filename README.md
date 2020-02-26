# Import Userback CSV file in Redmine

## Presentation

This tool allows to transfer userback issues in redmine issues

### Features :

* create or update tickets
* field mapping between Userback and Redmine

## Usage

```
# clone the repository
$ composer install

# create a config file based on config.sample.yml
$ php bin/userback2redmine sync /path/to/config/file.yml path/to/userback/issues.csv
```

### Configuration file

```
# config.samle.yml
redmine:
    # URL of your redmine instance
    url: ~
    # Your Remdine API access key (go to your account settings to get one)
    access_key: ~
    # The target Redmine project name
    project: ~
issues:
    # field or custom field used for identification of the issue
    identifier: 
        userback: 'ID'
        redmine: 'My Redmine Custom field'
    # map Userback CSV columns to Redmine field or custom fields
    mapping:
        ID: ~
        Collaboration Url: ~
        Date: ~
        Page: ~
        Email: ~
        Rating: ~
        Description: 'subject'
        Screenshot: ~
        Attachment: ~
        User Agent: ~
        Window Size: ~
        Screen Resolution: ~
        Workflow: ~
        Priority: ~
        Category: ~
        Assignee: ~
        Country: ~
        City: ~
```

### format data sent to Redmine

We use an uggly method in order to manipulate csv data extracted from the Userback CSV file and transform it before sending it to redmine.

It is achieved in the file `src/Custom/FormatToRedmine.php` that you can edit according to your needs.
