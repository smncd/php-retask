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

use PHPUnit\Framework\TestCase;
use Smncd\Retask\Exceptions\RetaskException;
use Smncd\Retask\Interfaces\RedisInterface;
use Smncd\Retask\Queue;

final class QueueTest extends TestCase
{
    private $redisStub;

    public function setUp(): void
    {
        $this->redisStub = $this->getMockBuilder(
            $this->createMock(
                RedisInterface::class,
            )::class,
        )
            ->onlyMethods([
                'info',
                'keys',
                'llen',
                'lpush',
                'rpop',
                'brpop',
                'delete',
            ])
            ->getMock();
    }

    public function tearDown(): void
    {
        // Clean up any resources used during testing
    }

    public function testProperRedisConnection(): void
    {
        $redisStub = $this->redisStub;

        $queue = new Queue('testqueue');

        $this->assertTrue($queue->connect($redisStub::class));
        $this->assertTrue($queue->isConnected());
    }

    public function testFaultyRedisConnection(): void
    {
        $redisStub = $this->redisStub;

        $queue = new Queue('testqueue');

        $this->expectException(RetaskException::class);
        $queue->connect('');
        $queue->connect(DateTime::class);
        $this->assertFalse($queue->isConnected());
    }
}
