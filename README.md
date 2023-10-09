# About Project

EPS-TOPIK exam web browser project by CuBe. This project is written in PHP and will use several frameworks such as Laravel, MongoDB Laravel, Filament, and Spatie Laravel Permission.

# User Guide

## Preparing Laravel Permission MongoDB - first time

> **NB**: Before install the package, let's setup MongoDB Configuration first!
> 1. Add the `MongoDBServiceProvider` to `Autoloaded Service Providers` in `config/app.php` file:
>     ```php
>     'providers' => [
>         // ...
>         MongoDB\Laravel\MongoDBServiceProvider::class,
>     ],
>     ```
> 2. Open `config/database.php` from config folder and change `Default Database Connection Name`
>     ```php
>     'default' => env('DB_CONNECTION', 'mongodb'),
>     ```
> 3. And add the following lines to `Database Connections` list
>     ```php
>     'connections' => [
>         'mongodb' => [
>             'driver' => 'mongodb',
>             'dsn' => env('DB_DSN', 'mongodb://localhost:27017'),
>             'database' => env('DB_DATABASE', 'homestead'),
>         ],
>         // ...
>     ],
>     ```
>     **NB**: Instead of using a connection string, you can also use the host and port configuration options
>     ```php
>     'connections' => [
>         'mongodb' => [
>             'driver' => 'mongodb',
>             'host' => env('DB_HOST', '127.0.0.1'),
>             'port' => env('DB_PORT', 27017),
>             'database' => env('DB_DATABASE', 'homestead'),
>             'username' => env('DB_USERNAME', 'homestead'),
>             'password' => env('DB_PASSWORD', 'secret'),
>             'options' => [
>                 'appname' => 'homestead',
>             ],
>         ],
>         // ...
>     ],
>     ```

1. Install the Laravel Permission MongoDB package by running the following commands in your Laravel project directory
    ```sh
    composer require mostafamaklad/laravel-permission-mongodb
    ```

    > Or manual get from github, open and update your `composer.json` file with code below:
    > ```json
    > // ...
    > "repositories": [
    >     {
    >         "type": "vcs",
    >         "url": "https://github.com/zoltech/laravel-permission-mongodb.git"
    >     }
    > ],
    > "require": {
    >     // ...
    >     "zoltech/laravel-permission-mongodb": "dev-master"
    > },
    > // ...
    > ```
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

> **NB:** Always use classes from `Mongodb\` to use as extends in model classes!

> **Add default Role for new users:** 
> 1. Create a new observer class
>     ```sh
>     php artisan make:observer UserObserver --model=User
>     ```
>     This command will place the new observer in your `app/Observers` directory.
> 2. Set the role for each new user has been created. Open and update `UserObserver.php` file
>     ```php
>     public function created(User $user)
>     {
>         $user->assignRole('User');
>     }
>     ```
> 3. To register an observer, you need to call the `observe` method on the model you wish to observe. You may register observers in the `boot` method of your application's `App\Providers\EventServiceProvider` service provider:
>     ```php
>     use App\Models\User;
>     use App\Observers\UserObserver;
>      
>     /**
>      * Register any events for your application.
>      */
>     public function boot(): void
>     {
>         User::observe(UserObserver::class);
>     }
>     ```
> 4. For more information please read [this](https://laravel.com/docs/10.x/eloquent#observers)

## Preparing Filament - first time

1. Install the Filament package by running the following commands in your Laravel project directory
    ```sh
    composer require filament/filament
    # or
    composer require filament/filament:*
    ```
2. Install the Filament Panel Builder
    ```sh
    php artisan filament:install --panels
    ```
3. **Optional**: The service provider will automatically get registered. Or you may manually add the service provider in your `config/app.php` file
    ```php
    'providers' => [
        // ...
        App\Providers\Filament\AdminPanelProvider::class,
    ];
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

> **Authorizing access to the panel**
> 1. To set up your `App\Models\User` to access Filament in non-local environments, you must implement the `FilamentUser` contract:
>     ```php
>     <?php
>      
>     namespace App\Models;
>      
>     use Filament\Models\Contracts\FilamentUser;
>     use Filament\Panel;
>     use Illuminate\Foundation\Auth\User as Authenticatable;
>     // use MongoDB\Laravel\Auth\User as Authenticatable;  // if you used MongoDB
>      
>     class User extends Authenticatable implements FilamentUser
>     {
>         // ...
>      
>         public function canAccessPanel(Panel $panel): bool
>         {
>             return $this->hasAnyRole(['Super Admin', 'Admin']);
>         }
>     }
>     ```
> 

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
