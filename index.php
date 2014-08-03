<?php
require 'vendor/autoload.php';

$app = new \Slim\Slim();
$rephlect = new \Rephlect\Rephlect($app);
$app->run();
