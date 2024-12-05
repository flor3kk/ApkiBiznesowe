php -r "touch('database\database.sqlite');"

php -r "copy('.env.example', '.env');"

call composer install

call php artisan migrate:fresh --seed

call php artisan key:generate

call php artisan jwt:secret

start cmd.exe /c %LocalAppData%\DBeaver\dbeaver-cli.exe -bringToFront -con "driver=sqlite|database=database\database.sqlite|name=Lab006_PAB|openConsole=true"

code .
