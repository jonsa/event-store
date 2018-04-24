<?php
/**
 * This file is part of the prooph/event-store.
 * (c) 2014-2018 prooph software GmbH <contact@prooph.de>
 * (c) 2015-2018 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace ProophTest\EventStore\Upcasting;

use PHPUnit\Framework\TestCase;
use Prooph\Common\Messaging\Message;
use Prooph\EventStore\Upcasting\SingleEventUpcaster;

class SingleEventUpcasterTest extends TestCase
{
    /**
     * @test
     */
    public function it_upcasts()
    {
        $upcastedMessage = $this->prophesize(Message::class);
        $upcastedMessage = $upcastedMessage->reveal();

        $message = $this->prophesize(Message::class);
        $message->withAddedMetadata('key', 'value')->willReturn($upcastedMessage)->shouldBeCalled();
        $message = $message->reveal();

        $upcaster = $this->createUpcasterWhoCanUpcast();

        $messages = $upcaster->upcast($message);

        $this->assertInternalType('array', $messages);
        $this->assertNotEmpty($messages);
        $this->assertSame($upcastedMessage, $messages[0]);
    }

    /**
     * @test
     */
    public function it_does_not_upcast_when_impossible()
    {
        $message = $this->prophesize(Message::class);
        $message->withAddedMetadata('key', 'value')->shouldNotBeCalled();
        $message = $message->reveal();

        $upcaster = $this->createUpcasterWhoCannotUpcast();

        $messages = $upcaster->upcast($message);

        $this->assertInternalType('array', $messages);
        $this->assertNotEmpty($messages);
        $this->assertSame($message, $messages[0]);
    }

    protected function createUpcasterWhoCanUpcast()
    {
        return new SingleEventUpcasterTest_SingleEventUpcaster(true);
    }

    protected function createUpcasterWhoCannotUpcast()
    {
        return new SingleEventUpcasterTest_SingleEventUpcaster(false);
    }
}

class SingleEventUpcasterTest_SingleEventUpcaster extends SingleEventUpcaster
{
    private $canUpcast;

    public function __construct($canUpcast)
    {
        $this->canUpcast = $canUpcast;
    }

    protected function canUpcast(Message $message)
    {
        return $this->canUpcast;
    }

    protected function doUpcast(Message $message)
    {
        return [$message->withAddedMetadata('key', 'value')];
    }
}
