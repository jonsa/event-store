<?php
/**
 * This file is part of the prooph/event-store.
 * (c) 2014-2018 prooph software GmbH <contact@prooph.de>
 * (c) 2015-2018 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Prooph\EventStore\Upcasting;

use Iterator;
use Prooph\Common\Messaging\Message;

final class UpcastingIterator implements Iterator
{
    /**
     * @var Upcaster
     */
    private $upcaster;

    /**
     * @var Iterator
     */
    private $innerIterator;

    /**
     * @var array
     */
    private $storedMessages = [];

    private $position = 0;

    public function __construct(Upcaster $upcaster, Iterator $iterator)
    {
        $this->upcaster = $upcaster;
        $this->innerIterator = $iterator;
    }

    public function current()
    {
        if (! empty($this->storedMessages)) {
            return reset($this->storedMessages);
        }

        $current = null;

        if (! $this->innerIterator instanceof \EmptyIterator) {
            $current = $this->innerIterator->current();
        }

        if (null === $current) {
            return null;
        }

        $this->storedMessages = $this->upcaster->upcast($current);

        while (empty($this->storedMessages)) {
            $this->innerIterator->next();

            if (! $this->innerIterator->valid()) {
                return null;
            }

            $this->storedMessages = $this->upcaster->upcast($this->innerIterator->current());
        }

        return reset($this->storedMessages);
    }

    public function next()
    {
        ++$this->position;

        if (! empty($this->storedMessages)) {
            array_shift($this->storedMessages);
        }

        if (! empty($this->storedMessages)) {
            return;
        }

        while (empty($this->storedMessages)) {
            $this->innerIterator->next();

            if (! $this->innerIterator->valid()) {
                return;
            }

            $this->storedMessages = $this->upcaster->upcast($this->innerIterator->current());
        }
    }

    /**
     * @return bool|int
     */
    public function key()
    {
        return $this->position;
    }

    public function valid()
    {
        if ($this->innerIterator instanceof \EmptyIterator) {
            return false;
        }

        return null !== $this->current();
    }

    public function rewind()
    {
        $this->storedMessages = [];
        $this->innerIterator->rewind();
        $this->position = 0;
    }
}
