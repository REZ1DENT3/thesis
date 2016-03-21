<?php

$phar = new \Phar('query.phar');
$phar->startBuffering();
$phar->buildFromDirectory('libs');
$phar->buildFromDirectory('vendor');
$phar->addFile('cmd.php', 'index.php');
$phar->stopBuffering();