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

/**
 * Task class.
 *
 * @package retask
 * @author Simon Lagerlöf <contact@smn.codes>
 * @license MIT
 * @version 0.0.0-dev
 */
class Task
{
    public string $_data = '';

    public string $urn = '';

    public function __construct(
        array|string|object $data = null,

        // TODO: Consider if $raw should be removed,
        // we could instead decide this basedon if $data is a string or not-
        ?bool $raw = false,
        ?string $urn = null
    )
    {

        if (!$raw && $data) {
            $this->_data = json_encode($data);
        } elseif (is_string($data)) {
            $this->_data = $data;
        }

        if ($urn) {
            $this->urn = $urn;
        }
    }

    public function data(): array|string|object
    {
        return json_decode(
            json: $this->_data,
            associative: true,
        );
    }

    public function rawData(): string
    {
        return $this->_data;
    }

    public function __toString()
    {
        return sprintf('%s(%s)', get_class($this), json_encode($this->data()));
    }
}
