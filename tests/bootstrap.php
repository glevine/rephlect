<?php
$loader = require __DIR__ . '/../vendor/autoload.php';
$loader->addPsr4('Tests\\', __DIR__);
$loader->addPsr4('Slim\\', __DIR__ . '/Slim');
