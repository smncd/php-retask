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
use Smncd\Retask\Interfaces\RedisInterface;
use Smncd\Retask\Job;

final class JobTest extends TestCase
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

    public function testRedis(): void
    {
        $redis = $this->redisStub;

        $job = new Job(
            redis: (object) new $redis(),
        );

        $this->assertIsString($job->urn);
    }

}
