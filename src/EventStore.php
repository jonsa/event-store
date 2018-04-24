<?php
/**
 * This file is part of the prooph/event-store.
 * (c) 2014-2018 prooph software GmbH <contact@prooph.de>
 * (c) 2015-2018 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Prooph\EventStore;

use Iterator;

interface EventStore extends ReadOnlyEventStore
{
    public function updateStreamMetadata(StreamName $streamName, array $newMetadata);

    public function create(Stream $stream);

    public function appendTo(StreamName $streamName, Iterator $streamEvents);

    public function delete(StreamName $streamName);
}
