<?php

include_once 'vendor/autoload.php';

$console = new \Deimos\Console();

$input = '';
$cache = array();

$console->run(function (\Deimos\Console $console) use (&$input, &$cache) {

    $line = $console->getLine();

    if (preg_match('~^(get input)~ui', $line)) {
        echo '$ ', $input, "\n";
    }
    else if (preg_match('~^(get queries)~ui', $line)) {
        foreach (array_keys($cache) as $sql) {
            echo '$ ', $sql, "\n";
        }
    }
    else if (($pos = mb_strpos($line, ';')) !== false) {

        $mcTime = microtime();
        $input .= mb_substr($line, 0, $pos);

        if (!isset($cache[$input])) {
            try {
                $query = new \Deimos\Query($input);
                $cache[$input] = $query->execute();
            }
            catch (\Exception $exception) {
                // TODO: \Exception $exception
            }
        }

        if (isset($cache[$input])) {
            var_dump($cache[$input]);
            printf("\n$ runtime: %f ms\n", microtime() - $mcTime);
        }

        $input = mb_substr($line, $pos + 2);

        if (!empty($input)) {
            $input .= ' ';
        }

    }
    else {
        $input .= $line . ' ';
    }

});

$time = time();
$date = date('d.m.Y', $time);

$dir = dirname(__DIR__);
$dir = preg_replace('~phar://~', '', $dir);
$dir = dirname($dir);

if (!file_exists($dir . '/logs/' . $date)) {
    mkdir($dir . '/logs/' . $date, 0777, true);
}

try {
    $filename = $dir . '/logs/' . $date . '/' . date('H_i_s', $time) . '.log';
    file_put_contents($filename, json_encode($cache, JSON_PRETTY_PRINT));
    printf("# session save to %s\n", $filename);
}
catch (\Exception $e) {
}
