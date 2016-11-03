<?php

require_once("vendor/autoload.php");

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Hush\Hush;

// create a log channel
$log = new Logger('RECTALCOMPUTER');
//$log->pushHandler(new StreamHandler('vibe.log', Logger::DEBUG));
//$log->pushHandler(new StreamHandler('php://output', Logger::DEBUG));

$username = "username@gmail.com";
$targetUsername = "victim@gmail.com";
$password = "lolbutts";

$hush = Hush::Factory($username, $password, $targetUsername)
    ->setLogger($log);

$hush->setBuzzSpeed(100);
sleep(3);
$hush->setBuzzSpeed(0);

