<?php
/**
 * Copyright (C) 2024, Simon Lagerlöf
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
 * of the Software, and to permit persons to whom the Software is furnished to do
 * so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
declare(strict_types=1);

namespace Smncd\Retask;

use Exception;
use Smncd\Retask\Exceptions\ConnectionException;
use Smncd\Retask\Exceptions\RedisConnectionException;
use Smncd\Retask\Exceptions\RetaskException;
use Smncd\Retask\Interfaces\RedisInterface;
use Smncd\Retask\Redis;

use function array_merge;
use function class_exists;
use function get_object_vars;
use function is_array;
use function json_decode;
use function json_encode;
use function mb_convert_encoding;
use function substr;

/**
 * Queue class.
 *
 * @todo Add find() method.
 *
 * @package retask
 * @author Simon Lagerlöf <contact@smn.codes>
 * @license MIT
 * @version 0.0.0-dev
 */
class Queue
{
    /**
     * Name of the retask Redis queue.
     */
    public string $name;

    /**
     * Prepended to queue name.
     */
    protected string $queuePrefix = 'retaskqueue';

    /**
     * The full queue name.
     */
    protected string $queueName;

    /**
     * Redis instance.
     */
    protected RedisInterface $redis;

    /**
     * Default Redis config.
     */
    protected array $config = [
        'host' => 'localhost',
        'port' => 6379,
        'db' => 0,
        'password' => null,
    ];

    /**
     * Status of connection to Redis.
     */
    protected bool $connected = false;

    public function __construct(string $name, ?array $config = null)
    {
        $this->name = $name;

        $this->queueName = "{$this->queuePrefix}-{$name}";

        if ($config) {
            $this->config = array_merge($this->config, $config);
        }
    }

    /**
     * Returns a list of queues available, `null` if no such queues found.
     */
    public function names(): ?array
    {
        $data = null;

        $this->checkConnection();

        try {
            $data = $this->redis->keys("{$this->queuePrefix}-*");
        } catch (RedisConnectionException $error) {
            throw new ConnectionException($error->getMessage());
        }

        $result = [];
        foreach ($data as $name) {
            $result[] = substr(
                string: $name,
                offset: 12,
            );
        }
        return $result;
    }

    /**
     * Gives the length of the queue. Returns `null` if the queue is not connected.
     */
    public function length(): ?int
    {
        $this->checkConnection();

        try {
            return $this->redis->llen($this->queueName);
        } catch (RedisConnectionException $error) {
            throw new ConnectionException($error->getMessage());
        }
    }

    /**
     * Attempts to connect with the Redis server.
     *
     * @param string|null $redisClient The default Redis client is \Smncd\Retask\Redis, which is a wrapper on top of Predis.
     */
    public function connect(?string $redisClient = Redis::class): bool
    {
        if (!class_exists($redisClient)) {
            throw new RetaskException("Class '{$redisClient}' doesn't exist.");
        }

        $this->redis = new $redisClient($this->config);

        if (!($this->redis instanceof RedisInterface)) {
            throw new RetaskException("Class '{$redisClient}' does not implement required type '\Smncd\Retask\Interfaces\RedisInterface'.");
        }

        try {
            $info = $this->redis->info();
            $this->connected = true;

            return true;
        } catch (RedisConnectionException) {
            return false;
        }
    }

    /**
     * Returns a boolean representing the connection status to the Redis server.
     */
    public function isConnected(): bool
    {
        return $this->connected;
    }

    /**
     * Returns a Task from the queue. If the request timeouts, it returns false.
     * This is a blocking call, you can specity waitTime argument for timeout.
     */
    public function wait(?int $waitTime = 0): Task | bool
    {
        $this->checkConnection();

        $data = $this->redis->brpop(
            keys: $this->queueName,
            timeout: $waitTime
        );

        if (is_array($data)) {
            return new Task(json_decode($data[1]));
        } else {
            return false;
        }
    }

    /**
     * Returns a `Task` instance from the queue, or `null`, should the queue be empty.
     */
    public function dequeue(): ?Task
    {
        $this->checkConnection();

        if ($this->redis->llen($this->queueName) === 0) {
            return null;
        }

        $data = $this->redis->rpop($this->queueName);

        if (!$data) {
            return null;
        }

        $sdata = json_decode(
            mb_convert_encoding(
                string: $data,
                to_encoding: 'UTF-8'
            )
        );

        // TODO: we should verify that both of these args actually exist on the `$sdata` object.
        return new Task(
            data: json_decode($sdata->_data),
            urn: $sdata->urn
        );

    }

    /**
     * Enqueues a `Task` instance to the queue and returns a `Job` instance.
     */
    public function enqueue(Task $task): ?Job
    {
        $this->checkConnection();

        try {
            $job = new Job($this->redis);

            $task->urn = $job->urn;

            $text = json_encode(get_object_vars($task));

            $this->redis->lpush($this->queueName, (array) $text);

            return $job;
        } catch (Exception) {
            return null;
        }
    }

    /**
     *  Sends the result back to the producer.
     */
    public function send(Task $task, array|object|string $result, int $expire = 60): void
    {
        $this->checkConnection();

        $this->redis->lpush(
            key: $task->urn,
            values: (array) json_encode($result),
        );

        $this->redis->expire(
            key: $task->urn,
            seconds: $expire,
        );
    }

    /**
     * Make sure that there is a connection to the Redis server.
     */
    private function checkConnection(): void
    {
        if (!$this->connected) {
            throw new ConnectionException('Queue is not connected');
        }
    }
}
