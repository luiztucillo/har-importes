<?php

$args = getopt('f:d:l:r:');

if (!isset($args['f']) || !file_exists($args['f'])) {
    echo 'php import-images.php -f file.har [-d absolute_destiny_path] [-l local_url_to_replace] [-r remote_url_to_replace]' . "\n";
    die;
}

$files  = array();
$har    = json_decode(file_get_contents($args['f']), true);

if (isset($har['log']) && isset($har['log']['entries'])) {
    foreach ($har['log']['entries'] as $entry) {
        if (!isset($entry['response'])) {
            continue;
        }

        $status = $entry['response']['status'];
        if ($status == 404) {
            $files[] = $entry['request']['url'];
        }
    }
}

if (!isset($args['d']) && !isset($args['l']) && !isset($args['r'])) {
    foreach ($files as $file) {
        echo $file . "\n";
    }
    die;
}

foreach ($files as $file) {
    $base   = trim(str_replace($args['l'], '', $file), '/');
    $url    = trim($args['r'], '/') . '/' . $base;
    $dst    = '/' . trim($args['d'], '/') . '/' . $base;

    if (file_exists($dst) && !is_dir($dst)) {
        unlink($dst);
    }

    if (!file_exists($dst)) {
        $dir = dirname($dst);
        @mkdir($dir, 0777, true);
    }

    $content = file_get_contents($url);
    file_put_contents($dst, $content);
    echo 'File ' . $dst . ' Created' . "\n";
}
