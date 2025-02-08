set XDEBUG_MODE=coverage
::php vendor/bin/phpunit test --debug --verbose --coverage-html build/coverage
php vendor/bin/phpunit test --debug --verbose 
composer dump-autoload
CHOICE /T 5 /C YN /D Y