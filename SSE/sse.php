<?php
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
set_time_limit(0);

$lastData = "";

while (true) {
    clearstatcache();
    $data = @file_get_contents("input.txt");

    if ($data !== false && $data !== $lastData) {
        echo "data: " . trim($data) . "\n\n";
        ob_flush();
        flush();
        $lastData = $data;
    }

}
