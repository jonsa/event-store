<?php
/**
 * This file is part of the prooph/event-store.
 * (c) 2014-2018 prooph software GmbH <contact@prooph.de>
 * (c) 2015-2018 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace ProophTest\EventStore\Projection;

use Prooph\EventStore\EventStore;
use Prooph\EventStore\EventStoreDecorator;
use Prooph\EventStore\Exception\InvalidArgumentException;
use Prooph\EventStore\InMemoryEventStore;
use Prooph\EventStore\Projection\InMemoryEventStoreQuery;
use Prooph\EventStore\Projection\InMemoryProjectionManager;

class InMemoryEventStoreQueryTest extends AbstractEventStoreQueryTest
{
    /**
     * @var InMemoryProjectionManager
     */
    protected $projectionManager;

    /**
     * @var InMemoryEventStore
     */
    protected $eventStore;

    protected function setUp()
    {
        $this->eventStore = new InMemoryEventStore();
        $this->projectionManager = new InMemoryProjectionManager($this->eventStore);
    }

    /**
     * @test
     */
    public function it_throws_exception_when_unknown_event_store_instance_passed()
    {
        $this->expectException(InvalidArgumentException::class);

        $eventStore = $this->prophesize(EventStore::class);

        new InMemoryEventStoreQuery($eventStore->reveal());
    }

    /**
     * @test
     */
    public function it_throws_exception_when_invalid_wrapped_event_store_instance_passed()
    {
        $this->expectException(InvalidArgumentException::class);

        $eventStore = $this->prophesize(EventStore::class);
        $wrappedEventStore = $this->prophesize(EventStoreDecorator::class);
        $wrappedEventStore->getInnerEventStore()->willReturn($eventStore->reveal())->shouldBeCalled();

        new InMemoryEventStoreQuery($wrappedEventStore->reveal());
    }
}
