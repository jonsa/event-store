<?php
/**
 * This file is part of the prooph/event-store.
 * (c) 2014-2018 prooph software GmbH <contact@prooph.de>
 * (c) 2015-2018 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Prooph\EventStore\Plugin;

use Prooph\EventStore\ActionEventEmitterEventStore;

abstract class AbstractPlugin implements Plugin
{
    protected $listenerHandlers = [];

    public function detachFromEventStore(ActionEventEmitterEventStore $eventStore)
    {
        foreach ($this->listenerHandlers as $listenerHandler) {
            $eventStore->detach($listenerHandler);
        }

        $this->listenerHandlers = [];
    }
}
