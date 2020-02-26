# Import Userback CSV file in Redmine

## Presentation

This tool allows to transfer userback issues in redmine issues

### Features :

* create or update tickets
* field mapping between Userback and Redmine

## Usage

```bash
$ git@github.com:acseo/Userback2Redmine.git
$ composer install

# create a config file based on config.sample.yml

# launch the tool
$ php bin/userback2redmine sync /path/to/config/file.yml path/to/userback/issues.csv
```

### Configuration file

```yaml
redmine:
    # URL of your redmine instance
    url: ~
    # Your Remdine API access key (go to your account settings to get one)
    access_key: ~
    # The target Redmine project name
    project: ~
issues:
    # Field or custom field used for identification of the issue
    identifier: 
        userback: 'ID'
        redmine: 'My Custom Field'
    # Map Userback CSV columns to Redmine field or custom fields
    mapping:
        ID: ~
        Collaboration Url: ~
        Date: 'start_date' # for example
        Page: ~
        Email: ~
        Rating: ~
        Description: 'subject'
        Screenshot: ~
        Attachment: ~
        User Agent: ~
        Window Size: ~
        Screen Resolution: ~
        Workflow: 'status_id'
        Priority: ~
        Category: ~
        Assignee: ~
        Country: ~
        City: ~

```

### format data sent to Redmine

We use an uggly method in order to manipulate csv data extracted from the Userback CSV file and transform it before sending it to redmine.

It is achieved in the file `src/Custom/FormatToRedmine.php` that you can edit according to your needs.
