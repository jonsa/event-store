<?php
/**
 * This file is part of the prooph/event-store.
 * (c) 2014-2018 prooph software GmbH <contact@prooph.de>
 * (c) 2015-2018 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Prooph\EventStore\Util;

class ArrayCache
{
    /**
     * @var array
     */
    private $container = [];

    /**
     * @var int
     */
    private $size;

    /**
     * @var int
     */
    private $position = -1;

    public function __construct($size)
    {
        if ($size <= 0) {
            throw new \InvalidArgumentException('Size must be a positive integer');
        }

        $this->size = $size;
        $this->container = array_fill(0, $size, null);
    }

    /**
     * @param mixed $value
     */
    public function rollingAppend($value)
    {
        $this->container[$this->nextPosition()] = $value;
    }

    /**
     * @param int $position
     * @return mixed
     */
    public function get($position)
    {
        if ($position >= $this->size
            || $position < 0
        ) {
            throw new \InvalidArgumentException('Position must be between 0 and ' . ($this->size - 1));
        }

        return $this->container[$position];
    }

    public function has($value)
    {
        return in_array($value, $this->container, true);
    }

    public function size()
    {
        return $this->size;
    }

    private function nextPosition()
    {
        return $this->position = ++$this->position % $this->size;
    }
}
