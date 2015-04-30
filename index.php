<?php
require 'vendor/autoload.php';

$app = new \Slim\Slim();
$rephlect = new \Rephlect\Rephlect($app);

$app->hook('slim.before.router', function() use ($rephlect) {
    // $rephlect->addResource('class_name');
});

$app->run();
