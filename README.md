# laravel-module-nauth

## Description
Module N Auth. Module to authenticate &amp; authorize for Laravel

## Getting Started
### Add package:
To start using the authentication package from NAuth, you need to initialize a submodule for your Laravel project.
```git
git clone https://github.com/ChauCongTu/laravel-module-nauth.git packages/nauth_module
```

### Update composer.json in main project
```json
"scripts": {...},
"repositories": [
        {
            "type": "path",
            "url": "packages/*"
        }
    ],
```

### Require nauth_module
Run that command `composer require cqn/nauth_module`

### Add HasRoles to User Model:

```php
<?php
namespace App\Models;

...
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles;
    ...
}
```

### Config enviroment variables
Config your `.env` file:
```
GOOGLE_CLIENT_ID="your_id"
GOOGLE_CLIENT_SECRET="your_secret_key"
GOOGLE_REDIRECT="Your_callback_url
```
### Config somes file

`config/app.php`
```php
'providers' => ServiceProvider::defaultProviders()->merge([
        ...
        Spatie\Permission\PermissionServiceProvider::class,
    ])->toArray(),
```
```php
'aliases' => Facade::defaultAliases()->merge([
        ...
        'Socialite' => Laravel\Socialite\Facades\Socialite::class,
    ])->toArray(),
```

`config/auth.php`
```php
 'guards' => [
        ...
        'api' => [
            'driver' => 'passport',
            'provider' => 'users',
        ],
    ],
```

`Middleware/Authenticate.php`
```php 
class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson() ? null : route('403');
    }
}
```

## How To Use
See: [Api Document](https://github.com/ChauCongTu/laravel-module-nauth/blob/main/docs-request.json)
