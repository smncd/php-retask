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

use Ramsey\Uuid\Uuid;
use Smncd\Retask\Interfaces\RedisInterface;

use function json_decode;

/**
 * Job class.
 *
 * @package retask
 * @author Simon Lagerlöf <contact@smn.codes>
 * @license MIT
 * @version 0.0.0-dev
 */
class Job
{
    public string $urn;

    private mixed $result = null;

    public function __construct(
        private RedisInterface $redis,
    ) {
        $this->urn = Uuid::uuid4()->getUrn();
    }

    public function result(): ?array
    {
        if ($this->result) {
            return $this->result;
        }

        $data = $this->redis->rpop($this->urn);

        if ($data) {
            $this->redis->del($this->urn);

            $data = json_decode($data);

            $this->result = $data;

            return $data;
        }
    }

    public function wait(?int $waitTime = 0): bool
    {
        if ($this->result) {
            return true;
        }

        $data = $this->redis->brpop(
            keys: $this->urn,
            timeout: $waitTime,
        );

        if ($data) {
            $this->redis->del($this->urn);

            $data = json_decode($data[1]);

            $this->result = $data;

            return true;
        } else {
            return false;
        }
    }
}
