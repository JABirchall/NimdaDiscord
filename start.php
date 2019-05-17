<?php
include_once("vendor/autoload.php");

DEFINE('NIMDA_PATH' ,__DIR__.'/Nimda/');

$nimda = new Nimda\Nimda();
$nimda->run();