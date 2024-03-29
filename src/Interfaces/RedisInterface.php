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

namespace Smncd\Retask\Interfaces;

/**
 * Redis interface.
 *
 * @package retask
 * @author Simon Lagerlöf <contact@smn.codes>
 * @license MIT
 * @version 0.0.0-dev
 */
interface RedisInterface
{
    public function __construct(mixed $parameters = null, mixed $options = null);

    /**
     * Returns information and statistics about the server.
     *
     * @see https://redis.io/commands/info/
     */
    public function info(mixed $section = null): array;

    /**
     * Returns all keys matching `$pattern`.
     *
     * @see https://redis.io/commands/keys/
     */
    public function keys(string $pattern): array;

    /**
     * Returns the length of the list stored at `$key`.
     *
     * @see https://redis.io/commands/llen/
     */
    public function llen(string $key): int;

    /**
     * Insert all the specified values at the head of the list stored at `$key`.
     *
     * @see https://redis.io/commands/lpush/
     */
    public function lpush(string $key, array $values): int;

    /**
     * Removes and returns the last elements of the list stored at `$key`.
     *
     * @see https://redis.io/commands/rpop/
     */
    public function rpop(string $key): string|null;

    /**
     * The blocking version of `rpop()` because it blocks the connection when
     * there are no elements to pop from any of the given lists.
     *
     * @see https://redis.io/commands/brpop/
     */
    public function brpop(array|string $keys, float|int $timeout): array|null;

    /**
     * Removes the specified keys. A key is ignored if it does not exist.
     *
     * @see https://redis.io/commands/del/
     */
    public function del(array|string $keyOrKeys, string|null ...$keys): int;

    /**
     * Set a timeout on key. After the timeout has expired,
     * the key will automatically be deleted.
     *
     * @seehttps://redis.io/commands/expire/
     */
    public function expire(string $key, int $seconds, string|null $expireOption = ''): int;
}
