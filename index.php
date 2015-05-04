<?php
require 'vendor/autoload.php';

$app = new \Slim\Slim();
$app->add(new \Slim\Middleware\ContentTypes());
$app->add(new \Rephlect\Rephlect(array('fq_class_name')));
$app->run();
