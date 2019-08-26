<?php
require __DIR__ . '/../vendor/autoload.php';
ini_set('error_reporting', E_ALL); // or error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

$dotenv = Dotenv\Dotenv::create(__DIR__.'/../');
$dotenv->load();

function dd()
{
    array_map(
        function ($x) {
            var_dump($x);
        },
        func_get_args()
    );
    die;
}
