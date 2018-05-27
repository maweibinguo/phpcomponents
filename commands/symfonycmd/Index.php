#!/usr/bin/env php
<?php
require dirname(dirname(__DIR__)) . '/vendor/autoload.php';

use Symfony\Component\Console\Application;

$application = new Application();
 
$application->run();
