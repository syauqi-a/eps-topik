# About Project

EPS-TOPIK exam web browser project by CuBe. This project is written in PHP and will use several frameworks such as Laravel, MongoDB Laravel, Filament, and Spatie Laravel Permission.

# User Guide

## Preparing Laravel Permission MongoDB - first time

1. Install the package by running the following commands in your Laravel project directory
    ```sh
    composer require mostafamaklad/laravel-permission-mongodb
    ```
2. You can publish the migration with:
    ```sh
    php artisan vendor:publish --provider="Maklad\Permission\PermissionServiceProvider" --tag="migrations"
    ```
   and run migrate command
    ```sh
    php artisan migrate
    ```
3. You can publish the config file with:
    ```sh
    php artisan vendor:publish --provider="Maklad\Permission\PermissionServiceProvider" --tag="config"
    ```
4. For more information please read [this](https://github.com/mostafamaklad/laravel-permission-mongodb#laravel).

> **NB:** Always use classes from `Jenssegers\Mongodb\` to use as extends in model classes!

## Preparing Filament - first time

1. Install the Filament package by running the following commands in your Laravel project directory
    ```sh
    composer require filament/filament
    # or
    composer require filament/filament:*
    ```
2. **Optional**: The service provider will automatically get registered. Or you may manually add the service provider in your `config/app.php` file
    ```php
    'providers' => [
        // ...
        App\Providers\Filament\AdminPanelProvider::class,
    ];
    ```
3. Install the Filament Panel Builder
    ```sh
    php artisan filament:install --panels
    ```
4. Generate tables from migration
    ```sh
    php artisan migrate
    ```
5. You can create a new user account with the following command
    ```sh
    php artisan make:filament-user
    ```
6. For more information please read [this](https://filamentphp.com/docs/3.x/panels/installation).

## Run App (EPS TOPIK Quiz)

To test the application on local, follow the steps mentioned below to install and run the project.

1. **Clone** or **download** the repository
   ```sh
   git clone https://github.com/syauqi-a/eps-topik.git
   ```
2. Go to the **project directory** and run
   ```sh
   composer install
   ```
3. **Create** `.env` file by copying the `.env.example`
   ```sh
   cp .env.example .env
   ```
4. **Update** the database name and credentials in `.env` file
5. **Add key** to `.env` file
   ```sh
   php artisan key:generate
   ```
8.  You may create a **virtualhost** entry to access the application or run
    ```sh
    php artisan serve
    ```
    from the project root and visit http://127.0.0.1:8000

## Credential

There are default credentials available in `DatabaseSeeder.php`. After you perform the seed command with the file, you can log in to the admin panel using the following credentials:

> Admin credentials
> ```
> name => Admin
> email => admin@gmail.com
> password => password
> ```

> User credentials
> ```
> name => User
> email => user@gmail.com
> password => password
> ```

# Support Me

<p>
    <a href="http://www.buymeacoffee.com/syauqia"><img src="https://cdn.buymeacoffee.com/buttons/v2/default-yellow.png" height="50" width="210" alt="syauqia" title="Send me a tip with Buy Me a Coffe"></a>
    <a href="https://ko-fi.com/syauqi_a"><img src="https://cdn.ko-fi.com/cdn/kofi3.png?v=3" height="50" width="210" alt="syauqi_a" title="Send me a tip with Ko-fi"></a>
    <a href="https://trakteer.id/syauqi-a/tip" target="_blank"><img id="wse-buttons-preview" src="https://cdn.trakteer.id/images/embed/trbtn-purple-3.png" height="50" style="border:0px;height:50px;" alt="Trakteer Saya" title="Send me a cendol with Trakteer"></a>
</p>

# License

The project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
