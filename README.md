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
    > After that, run `composer update`.
    > 
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

> ### Add local Role and Permission models:
> Instead of using `Role` and `Permission` models from the package directly, create new ones in `App\Models` so we can track the change history of those models.
> 1. Use the command below to generate the models
>     ```sh
>     php artisan make:model Role
>     php artisan make:model Permission
>     ```
> 2. Open and modify `Models\Role`
>     ```php
>     use Maklad\Permission\Models\Role as ModelsRole;
>     
>     class Role extends ModelsRole
>     {
>         // ...
>     }
>     ```
> 3. Open and modify `Models\Permission`
>     ```php
>     use Maklad\Permission\Models\Permission as ModelsPermission;
>     
>     class Permission extends ModelsPermission
>     {
>         // ...
>     }
>     ```
> 4. Open `config\permission.php` and modify the `models`
>     ```php
>     <?php
>     
>     return [
>         'models' => [
>             'permission' => App\Models\Permission::class,
>             'role' => App\Models\Role::class,
>         ]
>     ];
>     ```

> ### Handles the assignment of roles to users:
> 1. Create a new observer class
>     ```sh
>     php artisan make:observer UserObserver --model=User
>     ```
>     This command will place the new observer in your `app/Observers` directory.
> 2. Open and modify functions `created` and `updated` in the `UserObserver.php` file
>     ```php
>     use App\Models\User;
>     use App\Models\Role;
> 
>     class UserObserver
>     {
>         /**
>          * Handle the User "created" event.
>          */
>         public function created(User $user): void
>         {
>             $role_ids = $user->role_ids;
>     
>             if (! $role_ids) {
>                 return;
>             }
> 
>             $user->roles()->sync([]);
>     
>             foreach ($role_ids as $role_id) {
>                 $user->assignRole(Role::where('_id', $role_id)->value('name'));
>             }
>         }
> 
>         /**
>          * Handle the User "updated" event.
>          */
>         public function updated(User $user): void
>         {
>             $role_ids = $user->role_ids;
>             $user->roles()->detach();
>     
>             foreach ($role_ids as $role_id) {
>                 $role_data = Role::where('_id', $role_id)->get();
>                 if ($role_data) {
>                     $user->assignRole($role_data->value('name'));
>                 }
>             }
>         }
> 
>         // ...
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

> ### An error occurred in the Role, Permission and User relationship
> In case your Role, Permission and User relationship are **not generated automatically**. Complete the following steps to add it manually.
> 1. Open `Models\`**`Role`** and **add** the following function:
>     ```php
>     use Maklad\Permission\Models\Role as ModelsRole;
>     use MongoDB\Laravel\Relations\BelongsToMany;
> 
>     class Role extends ModelsRole
>     {
>         // ...
> 
>         /**
>          * A role may be given various permissions.
>          */
>         public function permissions(): BelongsToMany
>         {
>             return $this->belongsToMany(
>                 config('permission.models.permission'),
>                 config('permission.models.role'),
>                 'role_ids',
>                 'permission_ids'
>             );
>         }
> 
>         /**
>          * A role belongs to some users of the model associated with its guard.
>          * @return BelongsToMany
>          */
>         public function users(): BelongsToMany
>         {
>             return $this->belongsToMany(
>                 User::class,
>                 config('permission.models.role'),
>                 'role_ids',
>                 'user_ids'
>             );
>         }
> 
>         // ...
>     }
>     ```
> 2. Open `Models\`**`Permission`** and **add** the following function:
>     ```php
>     use Maklad\Permission\Models\Permission as ModelsPermission;
>     use MongoDB\Laravel\Relations\BelongsToMany;
> 
>     class Permission extends ModelsPermission
>     {
>         // ...
> 
>         /**
>          * A permission can be applied to roles.
>          */
>         public function roles(): BelongsToMany
>         {
>             return $this->belongsToMany(
>                 config('permission.models.role'),
>                 config('permission.models.permission'),
>                 'permission_ids',
>                 'role_ids'
>             );
>         }
> 
>         /**
>          * A permission belongs to some users of the model associated with its guard.
>          * @return BelongsToMany
>          */
>         public function users(): BelongsToMany
>         {
>             return $this->belongsToMany(
>                 User::class,
>                 config('permission.models.permission'),
>                 'permission_ids',
>                 'user_ids'
>             );
>         }
> 
>         // ...
>     }
>     ```
> 3. Open `Models\`**`User`** and **add** the following function:
>     ```php
>     use MongoDB\Laravel\Auth\User as Authenticatable;
>     use MongoDB\Laravel\Relations\BelongsToMany;
> 
>     class User extends Authenticatable
>     {
>         // ...
> 
>         /**
>          * Some user may be given various permissions.
>          */
>         public function permissions(): BelongsToMany
>         {
>             return $this->belongsToMany(
>                 config('permission.models.permission'),
>                 User::class,
>                 'user_ids',
>                 'permission_ids'
>             );
>         }
> 
>         /**
>          * Some user may be given various roles.
>          */
>         public function roles(): BelongsToMany
>         {
>             return $this->belongsToMany(
>                 config('permission.models.role'),
>                 User::class,
>                 'user_ids',
>                 'role_ids'
>             );
>         }
> 
>         // ...
>     }
>     ```

> ### Error grant permission(s) to User or Role
> To fix a bug where the `permission` collection doesn't have `user_ids` or `role_ids` after calling the `givePermissionTo` function:
> 1. Open `Models\Role` and **add** this function:
>     ```php
>     class Role
>     {
>         // ...
>         public function givePermissionTo(...$permissions): self
>         {
>             $permissions = collect($permissions)
>                 ->flatten()
>                 ->map(function ($permission) {
>                     return $this->getStoredPermission($permission);
>                 })
>                 ->each(function ($permission) {
>                     $this->ensureModelSharesGuard($permission);
>                 })
>                 ->all();
>     
>             $this->permissions()->saveMany($permissions);
>     
>             $this->forgetCachedPermissions();
>     
>             return $this;
>         }
>     }
>     ```
> 2. Open `Models\User` and **add** this function:
>     ```php
>     class User 
>     {
>         // ...
>         public function givePermissionTo(...$permissions)
>         {
>             $permissions = collect($permissions)
>                 ->flatten()
>                 ->map(function ($permission) {
>                     return $this->getStoredPermission($permission);
>                 })
>                 ->each(function ($permission) {
>                     $this->ensureModelSharesGuard($permission);
>                 })
>                 ->all();
>     
>             $this->permissions()->saveMany($permissions);
>     
>             $this->forgetCachedPermissions();
>     
>             return $permissions;
>         }
>     }
>     ```

> ### Prevent role deletion
> You can set a list of Role names that cannot be deleted.
> 1. Open `Models\Role` and **add** the following attribute:
>     ```php
>     use Maklad\Permission\Models\Role as ModelsRole;
> 
>     class Role extends ModelsRole
>     {
>         // ...
>         public $prevent_deleting = ['super admin', 'admin', 'user'];
>         // ...
>     }
>     ```
> 2. Open `Resources\RoleResource` and modify `DeleteAction` action
>     ```php
>     use Filament\Resources\Resource;
>     use Filament\Tables\Actions\DeleteAction;
>     use App\Models\Role;
>     use Filament\Notifications\Notification;
> 
>     class RoleResource extends Resource
>     {
>         // ...
>         public static function table(Table $table): Table
>         {
>             return $table
>                 // ...
>                 ->actions([
>                     // ...
>                     DeleteAction::make()
>                         ->before(function (DeleteAction $action, Role $record) {
>                             if (in_array($record->name, $record->prevent_deleting)) {
>                                 Notification::make()
>                                     ->warning()
>                                     ->title('Failed to delete!')
>                                     ->body("You cannot delete the \"{$record->name}\" role.")
>                                     ->persistent()
>                                     ->send();
>                             
>                                 $action->cancel();
>                             }
>                         }
>                     ),
>                     // ...
>                 ])
>                 // ...
>         }
>         // ...
>     }
>     ```
> 3. Then modify `BulkActionGroup` action
>     ```php
>     use Filament\Resources\Resource;
>     use Filament\Tables\Actions\DeleteBulkAction;
>     use Illuminate\Database\Eloquent\Collection;
>     use App\Models\Role;
>     use Filament\Notifications\Notification;
> 
>     class RoleResource extends Resource
>     {
>         // ...
>         public static function table(Table $table): Table
>         {
>             return $table
>                 // ...
>                 ->bulkActions([
>                     // ...
>                     DeleteBulkAction::make()
>                         ->action(function (DeleteBulkAction $action, Collection $records) {
>                             $records->each(function (Role $record) use ($action) {
>                                 if (in_array($record->name, $record->prevent_deleting)) {
>                                     Notification::make()
>                                         ->warning()
>                                         ->title('Failed to delete!')
>                                         ->body("You cannot delete the \"{$record->name}\" role.")
>                                         ->persistent()
>                                         ->send();
>                                 } else {
>                                     $record->delete();
>                                     $action->success();
>                                 }
>                             });
>                         }
>                     ),
>                     // ...
>                 ])
>                 // ...
>         }
>         // ...
>     }
>     ```
> 4. Open `Resources\RoleResource\EditRole` and modify `DeleteAction` action
>     ```php
>     use Filament\Resources\Pages\EditRecord;
>     use Filament\Actions\DeleteAction;
>     use App\Models\Role;
>     use Filament\Notifications\Notification;
> 
>     class EditRole extends EditRecord
>     {
>         // ...
>         protected function getHeaderActions(): array
>         {
>             return [
>                 DeleteAction::make()
>                     ->before(function (DeleteAction $action, Role $record) {
>                         if (in_array($record->name, $record->prevent_deleting)) {
>                             Notification::make()
>                                 ->warning()
>                                 ->title('Failed to delete!')
>                                 ->body("You cannot delete the \"{$record->name}\" role.")
>                                 ->persistent()
>                                 ->send();
> 
>                             $action->cancel();
>                         }
>                     }
>                 ),
>             ];
>         }
>         // ...
>     }
>     ```

> ### Prevent role updates
> You can set a list of Role names that cannot be edited.
> 1. Open `Models\Role` and **add** the following attribute:
>     ```php
>     use Maklad\Permission\Models\Role as ModelsRole;
> 
>     class Role extends ModelsRole
>     {
>         // ...
>         public $prevent_editing = ['super admin'];
>         // ...
>     }
>     ```
> 2. Open `Resources\RoleResource` and modify `TextInput` for `name` input
>     ```php
>     use Filament\Resources\Resource;
>     use Filament\Forms\Components\TextInput;
>     use Filament\Forms\Get;
>     use App\Models\Role;
> 
>     class RoleResource extends Resource
>     {
>         // ...
>         public static function table(Table $table): Table
>         {
>             return $form
>                 ->schema([
>                     TextInput::make('name')
>                         // ...
>                         ->disabled(function (Get $get): bool {
>                             $role = new Role();
>                             return in_array($get('name), $role->prevent_editing);
>                         })
>                         // ...
>                 ])
>                 // ...
>         }
>         // ...
>     }
>     ```


## Preparing Filament - first time

1. Install the Filament package by running the following commands in your Laravel project directory
    ```sh
    composer require filament/filament
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
    > **NB**: You can also generate users using the seeders provided. **Be careful** when using `migrate:fresh` instead of `migrate`, you will lose all data on your database.
    > ```sh
    > php artisan migrate:fresh --seeder=DatabaseSeeder
    > ```
6. For more information please read [this](https://filamentphp.com/docs/3.x/panels/installation).

> **Authorizing access to the panel**
> 1. To set up your `Models\User` to access Filament in non-local environments, you must implement the `FilamentUser` contract:
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
>             return $this->hasAnyRole(['super admin', 'admin']);
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
