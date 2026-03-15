<?php
//use Symfony\Component\HttpKernel\Kernel;
use App\Kernel;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';
/*
ini_set('display_errors', 1);

ini_set('display_startup_errors', 1);

error_reporting(E_ALL);*/
date_default_timezone_set('America/Argentina/Buenos_Aires');

//print 'DIR NAME: ' . dirname(__DIR__);
//print 'BASE NAME: ' . basename(getcwd());

return function (array $context) {

    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
