PHP retask
==========

This is a PHP port of the [retask library](https://github.com/kushaldas/retask) written by Kushal Das.

retask is a lightweight library used to create distributed task queues using Redis.

It is basically a wrapper on top of Redis's existing functionality, and meant to be compatible with the original retask Python module.

⚠️ At the moment this repo is a proof of concept and will probably change a fair bit, so use at your own risk, and have fun!

Installation
------------

The package is available via composer, however you will need to set the project minimum stability to `dev`:
```json
// composer.json
{
  ...
  "minimum-stability": "dev",
  ...
}

```

Then, you can install the package like normal:

```bash
composer require smncd/retask
```

Example
-------

```php
// provider.php
<?php
declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use Smncd\Retask\Queue;
use Smncd\Retask\Task;


$queue = new Queue('example');

$queue->connect();

$task = new Task([
    'user' => 'John Doe',
    'task' => 'High-five a sea otter.',
]);

$queue->enqueue($task);
```

```php
// worker.php
<?php
declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

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
```