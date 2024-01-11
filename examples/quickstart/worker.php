<?php
declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use Smncd\Retask\Queue;

$queue = new Queue('example');

$queue->connect();

while (true) {
    $task = $queue->dequeue();

    if ($task) {
        $data = $task->data();

        print_r($task->data());
    }
}
