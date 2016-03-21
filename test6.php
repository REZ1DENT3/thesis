#!/usr/bin/env php
<?php

require_once __DIR__ . '/vendor/autoload.php';

set_time_limit(0);

use App\Console;

$app = new Console();

$app->add(new App\Command\Query());

$app->run();