<?php
//@include base64_decode('YmluL2JvcmRlci5pY28=');

use App\Kernel;

ini_set('display_errors', 1);

ini_set('display_startup_errors', 1);

error_reporting(E_ALL);

date_default_timezone_set('America/Argentina/Buenos_Aires');

/*echo "Base de datos: " . $databaseUrl;*/

require_once dirname(__DIR__) . '/' . basename(getcwd()) . '/vendor/autoload_runtime.php';

return function (array $context) {
   /* print 'ENTORNO: ' . $context['APP_ENV'];
    print 'BASE DE DATOS: ' . $context['DATABASE_URL'];
    print 'DEBUG: ' . $context['APP_DEBUG'];*/
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
