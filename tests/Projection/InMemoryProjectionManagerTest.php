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
use Prooph\EventStore\Exception\RuntimeException;
use Prooph\EventStore\InMemoryEventStore;
use Prooph\EventStore\Projection\InMemoryProjectionManager;

class InMemoryProjectionManagerTest extends AbstractProjectionManagerTest
{
    /**
     * @var InMemoryProjectionManager
     */
    protected $projectionManager;

    protected function setUp()
    {
        $this->projectionManager = new InMemoryProjectionManager(new InMemoryEventStore());
    }

    /**
     * @test
     */
    public function it_throws_exception_when_invalid_event_store_instance_passed()
    {
        $this->expectException(InvalidArgumentException::class);

        $eventStore = $this->prophesize(EventStore::class);

        new InMemoryProjectionManager($eventStore->reveal());
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

        new InMemoryProjectionManager($wrappedEventStore->reveal());
    }

    /**
     * @test
     */
    public function it_cannot_delete_projections()
    {
        $this->expectException(RuntimeException::class);

        $this->projectionManager->deleteProjection('foo', true);
    }

    /**
     * @test
     */
    public function it_cannot_reset_projections()
    {
        $this->expectException(RuntimeException::class);

        $this->projectionManager->resetProjection('foo');
    }

    /**
     * @test
     */
    public function it_cannot_stop_projections()
    {
        $this->expectException(RuntimeException::class);

        $this->projectionManager->stopProjection('foo');
    }

    /**
     * @test
     */
    public function it_throws_exception_when_trying_to_delete_non_existing_projection()
    {
        $this->markTestSkipped('Deleting a projection is not supported in ' . InMemoryProjectionManager::class);
    }

    /**
     * @test
     */
    public function it_throws_exception_when_trying_to_reset_non_existing_projection()
    {
        $this->markTestSkipped('Resetting a projection is not supported in ' . InMemoryProjectionManager::class);
    }

    /**
     * @test
     */
    public function it_throws_exception_when_trying_to_stop_non_existing_projection()
    {
        $this->markTestSkipped('Stopping a projection is not supported in ' . InMemoryProjectionManager::class);
    }

    /**
     * @test
     */
    public function it_does_not_fail_deleting_twice()
    {
        $this->markTestSkipped('Deleting a projection is not supported in ' . InMemoryProjectionManager::class);
    }

    /**
     * @test
     */
    public function it_does_not_fail_resetting_twice()
    {
        $this->markTestSkipped('Resetting a projection is not supported in ' . InMemoryProjectionManager::class);
    }

    /**
     * @test
     */
    public function it_does_not_fail_stopping_twice()
    {
        $this->markTestSkipped('Stopping a projection is not supported in ' . InMemoryProjectionManager::class);
    }
}
