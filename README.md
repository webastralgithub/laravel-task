# 1- Build the project :
- `composer install`
- `composer require tymon/jwt-auth --ignore-platform-reqs`
- `composer require nexmo/client`
# 2-Setup the database :
- create a database name it `laraveltask`

# 3-Essential commands to run in the backend container
- `php artisan migrate`
- `php artisan db:seed`
- `php artisan jwt:secret`
- `php artisan storage:link`
- `php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"`

# 4-Run the project
- Just visit `php artisan serv`


