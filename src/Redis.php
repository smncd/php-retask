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

use Smncd\Retask\Interfaces\RedisInterface;
use Predis\Client;

/**
 * Redis class.
 *
 * @package retask
 * @author Simon Lagerlöf <contact@smn.codes>
 * @license MIT
 * @version 0.0.0-dev
 */
class Redis implements RedisInterface
{
    private Client $redis;

    public function __construct(mixed $parameters = null, mixed $options = null)
    {
        $this->redis = new Client($parameters, $options);
    }

    public function info(mixed $section = null): array
    {
        return $this->redis->info($section);
    }

    public function keys(string $pattern): array
    {
        return $this->redis->keys($pattern);
    }

    public function llen(string $key): int
    {
        return $this->redis->llen($key);
    }

    public function lpush(string $key, array $values): int
    {
        return $this->redis->lpush($key, $values);
    }

    public function rpop(string $key): string|null
    {
        return $this->redis->rpop($key);
    }

    public function brpop(array|string $keys, float|int $timeout): array|null
    {
        return $this->redis->brpop($keys, $timeout);
    }

    public function delete(array|string $keyOrKeys, string|null ...$keys): int
    {
        return $this->redis->del($keyOrKeys, ...$keys);
    }
}
