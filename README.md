# PML (Pizza Markup Language) Test #

### What is this repository for? ###

* Technical Test | Penbrothers Application

### How do I get set up?

* Clone this repo
* Run `composer install`
* Create own database
* Copy `.env-example` to `.env` for environment configuration and change database config
* Change `config/database.php` file - default value to `mysql` or any database you are using (I used pgsql just for heroku)
* Run `php artisan key:generate`
* Migrate database `php artisan migrate`
* Run php server `php artisan serve`

### Testing

### Who do I talk to? ###

* Mervin Villaceran
