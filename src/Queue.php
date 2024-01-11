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
use Predis\Connection\ConnectionException as RedisConnectionException;
use Smncd\Retask\Exceptions\ConnectionException;
use Smncd\Retask\Exceptions\RetaskException;
use Smncd\Retask\Interfaces\RedisInterface;
use Smncd\Retask\Redis;

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
    public string $name;

    protected string $queueName;

    protected RedisInterface $redis;

    protected array $config = [
        'host' => 'localhost',
        'port' => 6379,
        'db' => 0,
        'password' => null,
    ];

    protected bool $connected = false;

    public function __construct(string $name, ?array $config = null)
    {
        $this->name = $name;

        $this->queueName = "retaskqueue-{$name}";

        if ($config) {
            $this->config = array_merge($this->config, $config);
        }
    }

    public function names(): array
    {
        $data = null;

        if (!$this->connected) {
            throw new ConnectionException('Queue is not connected');
        }

        try {
            $data = $this->redis->keys('retaskqueue-*');
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

    public function length(): int
    {
        if (!$this->connected) {
            throw new ConnectionException('Queue is not connected');
        }

        try {
            return $this->redis->llen($this->queueName);
        } catch (RedisConnectionException $error) {
            throw new ConnectionException($error->getMessage());
        }
    }

    public function connect(
        string $redisClient = Redis::class
    ): bool
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

    public function isConnected(): bool
    {
        return $this->connected;
    }

    public function wait(?int $waitTime = 0): Task | bool
    {
        if (!$this->connected) {
            throw new ConnectionException('Queue is not connected');
        }

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

    public function dequeue(): ?Task
    {
        if (!$this->connected) {
            throw new ConnectionException('Queue is not connected');
        }

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

    public function enqueue(Task $task): ?Job
    {
        if (!$this->connected) {
            throw new ConnectionException('Queue is not connected');
        }

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

    public function send(Task $task, array|object|string $result, int $expire = 60): void
    {
        if (!$this->connected) {
            throw new ConnectionException('Queue is not connected');
        }

        $this->redis->lpush(
            key: $task->urn,
            values: (array) json_encode($result),
        );

        $this->redis->expire(
            key: $task->urn,
            seconds: $expire,
        );
    }
}
