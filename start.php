<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);

include_once("vendor/autoload.php");

DEFINE('NIMDA_PATH' ,__DIR__.'/Nimda/');

$nimda = new Nimda\Nimda();
$nimda->run();