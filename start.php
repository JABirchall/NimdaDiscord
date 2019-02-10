<?php
use Nimda\Nimda;
use Nimda\Configuration\Discord;

include_once("vendor/autoload.php");

DEFINE('NIMDA_PATH' ,__DIR__.'/Nimda/');

$nimda = new Nimda(Discord::$config);
$nimda->run();