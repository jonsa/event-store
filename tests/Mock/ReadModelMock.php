<?php
/**
 * This file is part of the prooph/event-store.
 * (c) 2014-2018 prooph software GmbH <contact@prooph.de>
 * (c) 2015-2018 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace ProophTest\EventStore\Mock;

use Prooph\EventStore\Exception\InvalidArgumentException;
use Prooph\EventStore\Projection\AbstractReadModel;

class ReadModelMock extends AbstractReadModel
{
    private $storage;

    /**
     * @var array
     */
    private $stack = [];

    public function insert($key, $value)
    {
        $this->storage[$key] = $value;
    }

    public function update($key, $value)
    {
        if (! array_key_exists($key, $this->storage)) {
            throw new InvalidArgumentException('Invalid key given');
        }

        $this->storage[$key] = $value;
    }

    public function hasKey($key)
    {
        return is_array($this->storage) && array_key_exists($key, $this->storage);
    }

    public function read($key)
    {
        return $this->storage[$key];
    }

    public function init()
    {
        $this->storage = [];
    }

    public function isInitialized()
    {
        return is_array($this->storage);
    }

    public function reset()
    {
        $this->storage = [];
    }

    public function delete()
    {
        $this->storage = null;
    }
}
