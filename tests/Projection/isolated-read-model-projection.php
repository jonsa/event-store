<?php
/**
 * This file is part of the prooph/event-store.
 * (c) 2014-2018 prooph software GmbH <contact@prooph.de>
 * (c) 2015-2018 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


use Prooph\EventStore\InMemoryEventStore;
use Prooph\EventStore\Projection\InMemoryProjectionManager;
use Prooph\EventStore\Projection\ReadModel;
use Prooph\EventStore\Projection\ReadModelProjector;
use Prooph\EventStore\Stream;
use Prooph\EventStore\StreamName;

require __DIR__ . '/../../vendor/autoload.php';

class IsolatedReadModelProjection_ReadModel implements ReadModel
{
    public function init()
    {
    }

    public function isInitialized()
    {
        return true;
    }

    public function reset()
    {
    }

    public function delete()
    {
    }

    public function stack($operation, ...$args)
    {
    }

    public function persist()
    {
    }
}

$readModel = new IsolatedReadModelProjection_ReadModel;

$eventStore = new InMemoryEventStore();
$eventStore->create(new Stream(new StreamName('user-123'), new ArrayIterator([])));

$projectionManager = new InMemoryProjectionManager($eventStore);
$projection = $projectionManager->createReadModelProjection(
    'test_projection',
    $readModel,
    [
        ReadModelProjector::OPTION_PCNTL_DISPATCH => true,
    ]
);
pcntl_signal(SIGQUIT, function () use ($projection) {
    $projection->stop();
    exit(SIGUSR1);
});
$projection
    ->fromStream('user-123')
    ->whenAny(function () {
    })
    ->run();
