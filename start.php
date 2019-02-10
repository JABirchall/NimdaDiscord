<?php
use Nimda\Nimda;
use Nimda\Configuration\Discord;

include_once("vendor/autoload.php");

$nimda = new Nimda(Discord::$config);
$nimda->newInstance();
$nimda->run();