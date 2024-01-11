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
use Ramsey\Uuid\Uuid;
use Smncd\Retask\Task;

final class TaskTest extends TestCase
{
    public function testReturnsCorrectDataIfGivenArray(): void
    {
        $data = [
            'task_name' => 'High-five a sea otter.',
        ];
        $urn = self::getUrn();

        $task = new Task(
            data: $data,
            urn: $urn,
        );

        $this->assertSame(
            expected: $data['task_name'],
            actual: $task->data()['task_name']
        );
    }

    public function testReturnsCorrectDataIfGivenString(): void
    {
        $data = '{"task_name":"High-five a sea otter."}';
        $urn = self::getUrn();

        $task = new Task(
            data: $data,
            raw: true,
            urn: $urn,
        );

        $this->assertSame(
            expected: json_decode($data)->task_name,
            actual: $task->data()['task_name']
        );
    }

    public function testReturnsRawData(): void
    {
        $dataString = '{"task_name":"High-five a sea otter."}';

        $this->assertSame(
            expected: $dataString,
            actual: (new Task(
                data: json_decode(
                    json: $dataString,
                    associative: true,
                ))
            )->rawData(),
        );

        $this->assertSame(
            expected: $dataString,
            actual: (new Task(
                data: $dataString,
                raw: true,
            ))->rawData(),
        );
    }

    private static function getUrn(): string
    {
        return Uuid::uuid4()->getUrn();
    }
}
