<?php

if (file_exists('build/query.phar')) {
    unlink('build/query.phar');
}

$phar = new \Phar('build/query.phar');

$phar->startBuffering();

$phar->buildFromDirectory('libs');
$phar->buildFromDirectory('vendor');
$phar->addFile('cmd.php', 'index.php');

$signatures = \Phar::getSupportedSignatures();

if (in_array('SHA-512', $signatures)) {
    $phar->setSignatureAlgorithm(\Phar::SHA512);
}
else if (in_array('SHA-256', $signatures)) {
    $phar->setSignatureAlgorithm(\Phar::SHA256);
}
else if (in_array('SHA-1', $signatures)) {
    $phar->setSignatureAlgorithm(\Phar::SHA1);
}
else if (in_array('MD5', $signatures)) {
    $phar->setSignatureAlgorithm(\Phar::MD5);
}

$phar->stopBuffering();