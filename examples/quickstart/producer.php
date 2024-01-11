<?php
declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use Smncd\Retask\Queue;
use Smncd\Retask\Task;

$queue = new Queue('example');

$queue->connect();

$task = new Task([
    'user' => 'John Doe',
    'task' => 'High-five a sea otter.',
]);

$queue->enqueue($task);
