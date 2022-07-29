# Cloudin Auth Template Laravel

### Installation

You can easy install the project by following the steps.


Clone the repository using the following command in your terminal

`git clone git@github.com:cloudinteam/cloudin-auth-template-laravel.git`

This will clone the repository in your system.

Then change your directory to the **"cloudin-auth-template-laravel"** folder by using the following command

`cd cloudin-auth-template-laravel/`

After moving to the directory use the following command to install all the dependencies required to run the project

`composer install`

After the complete Installation of the composer packages go to the **.env** file and edit the following

`DB_DATABASE={your_database_name}`
`DB_USERNAME={your_user_name}`
`DB_PASSWORD={your_password}`

These changes are done to connect the db with the project. **If you are not using mysql DB, then configure the port and connection type too.**
After configuring the db you have to update the encryption key and secret to make sure the encryption would be more safe.

`ENCRYPTION_KEY='{your_encryption_key}'`
`ENCRYPTION_IV='{your_encryption_iv}'`

If you don't have a encryption you can [click here](https://www.lastpass.com/features/password-generator) to get a encryption key.
And also change the following for sending mail.

`MAIL_MAILER={your_mail_provider}`
`MAIL_HOST={your_provider_host}`
`MAIL_PORT={your_port}`
`MAIL_USERNAME={your_user_name}`
`MAIL_PASSWORD={your_password}`
`MAIL_ENCRYPTION={encryption_type}`
`MAIL_FROM_ADDRESS="{from_mail_address}"`

Then use the following command to migrate all the tables to the your database

`php artisan migrate`

For installing the passport key use the following command 

`php artisan passport:install`

Then use the command to run the project

`php artisan serve`


## Documents

The postman collection json file and swagger yml file is available in the following location **tests/Documents/** , you can use this json file in postman to test the functionalities.
