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
use Prooph\Common\Event\ActionEvent;
use Prooph\Common\Event\ActionEventEmitter;
use Prooph\Common\Event\ListenerHandler;
use Prooph\EventStore\Exception\ConcurrencyException;
use Prooph\EventStore\Exception\StreamExistsAlready;
use Prooph\EventStore\Exception\StreamNotFound;
use Prooph\EventStore\Metadata\MetadataMatcher;
use Prooph\EventStore\Util\Assertion;

class ActionEventEmitterEventStore implements EventStoreDecorator
{
    const EVENT_APPEND_TO = 'appendTo';
    const EVENT_CREATE = 'create';
    const EVENT_LOAD = 'load';
    const EVENT_LOAD_REVERSE = 'loadReverse';
    const EVENT_DELETE = 'delete';
    const EVENT_HAS_STREAM = 'hasStream';
    const EVENT_FETCH_STREAM_METADATA = 'fetchStreamMetadata';
    const EVENT_UPDATE_STREAM_METADATA = 'updateStreamMetadata';
    const EVENT_FETCH_STREAM_NAMES = 'fetchStreamNames';
    const EVENT_FETCH_STREAM_NAMES_REGEX = 'fetchStreamNamesRegex';
    const EVENT_FETCH_CATEGORY_NAMES = 'fetchCategoryNames';
    const EVENT_FETCH_CATEGORY_NAMES_REGEX = 'fetchCategoryNamesRegex';

    const ALL_EVENTS = [
        self::EVENT_APPEND_TO,
        self::EVENT_CREATE,
        self::EVENT_LOAD,
        self::EVENT_LOAD_REVERSE,
        self::EVENT_DELETE,
        self::EVENT_HAS_STREAM,
        self::EVENT_FETCH_STREAM_METADATA,
        self::EVENT_UPDATE_STREAM_METADATA,
        self::EVENT_FETCH_STREAM_NAMES,
        self::EVENT_FETCH_STREAM_NAMES_REGEX,
        self::EVENT_FETCH_CATEGORY_NAMES,
        self::EVENT_FETCH_CATEGORY_NAMES_REGEX,
    ];

    /**
     * @var ActionEventEmitter
     */
    protected $actionEventEmitter;

    /**
     * @var EventStore
     */
    protected $eventStore;

    public function __construct(EventStore $eventStore, ActionEventEmitter $actionEventEmitter)
    {
        $this->eventStore = $eventStore;
        $this->actionEventEmitter = $actionEventEmitter;

        $actionEventEmitter->attachListener(self::EVENT_CREATE, function (ActionEvent $event) {
            $stream = $event->getParam('stream');

            try {
                $this->eventStore->create($stream);
            } catch (StreamExistsAlready $exception) {
                $event->setParam('streamExistsAlready', true);
            }
        });

        $actionEventEmitter->attachListener(self::EVENT_APPEND_TO, function (ActionEvent $event) {
            $streamName = $event->getParam('streamName');
            $streamEvents = $event->getParam('streamEvents');

            try {
                $this->eventStore->appendTo($streamName, $streamEvents);
            } catch (StreamNotFound $exception) {
                $event->setParam('streamNotFound', true);
            } catch (ConcurrencyException $exception) {
                $event->setParam('concurrencyException', true);
            }
        });

        $actionEventEmitter->attachListener(self::EVENT_LOAD, function (ActionEvent $event) {
            $streamName = $event->getParam('streamName');
            $fromNumber = $event->getParam('fromNumber');
            $count = $event->getParam('count');
            $metadataMatcher = $event->getParam('metadataMatcher');

            try {
                $streamEvents = $this->eventStore->load($streamName, $fromNumber, $count, $metadataMatcher);
                $event->setParam('streamEvents', $streamEvents);
            } catch (StreamNotFound $exception) {
                $event->setParam('streamNotFound', true);
            }
        });

        $actionEventEmitter->attachListener(self::EVENT_LOAD_REVERSE, function (ActionEvent $event) {
            $streamName = $event->getParam('streamName');
            $fromNumber = $event->getParam('fromNumber');
            $count = $event->getParam('count');
            $metadataMatcher = $event->getParam('metadataMatcher');

            try {
                $streamEvents = $this->eventStore->loadReverse($streamName, $fromNumber, $count, $metadataMatcher);
                $event->setParam('streamEvents', $streamEvents);
            } catch (StreamNotFound $exception) {
                $event->setParam('streamNotFound', true);
            }
        });

        $actionEventEmitter->attachListener(self::EVENT_DELETE, function (ActionEvent $event) {
            $streamName = $event->getParam('streamName');

            try {
                $this->eventStore->delete($streamName);
            } catch (StreamNotFound $exception) {
                $event->setParam('streamNotFound', true);
            }
        });

        $actionEventEmitter->attachListener(self::EVENT_HAS_STREAM, function (ActionEvent $event) {
            $streamName = $event->getParam('streamName');

            $event->setParam('result', $this->eventStore->hasStream($streamName));
        });

        $actionEventEmitter->attachListener(self::EVENT_FETCH_STREAM_METADATA, function (ActionEvent $event) {
            $streamName = $event->getParam('streamName');

            try {
                $metadata = $this->eventStore->fetchStreamMetadata($streamName);
                $event->setParam('metadata', $metadata);
            } catch (StreamNotFound $exception) {
                $event->setParam('streamNotFound', true);
            }
        });

        $actionEventEmitter->attachListener(self::EVENT_UPDATE_STREAM_METADATA, function (ActionEvent $event) {
            $streamName = $event->getParam('streamName');
            $metadata = $event->getParam('metadata');

            try {
                $this->eventStore->updateStreamMetadata($streamName, $metadata);
            } catch (StreamNotFound $exception) {
                $event->setParam('streamNotFound', true);
            }
        });

        $actionEventEmitter->attachListener(self::EVENT_FETCH_STREAM_NAMES, function (ActionEvent $event) {
            $filter = $event->getParam('filter');
            $metadataMatcher = $event->getParam('metadataMatcher');
            $limit = $event->getParam('limit');
            $offset = $event->getParam('offset');

            $streamNames = $this->eventStore->fetchStreamNames($filter, $metadataMatcher, $limit, $offset);

            $event->setParam('streamNames', $streamNames);
        });

        $actionEventEmitter->attachListener(self::EVENT_FETCH_STREAM_NAMES_REGEX, function (ActionEvent $event) {
            $filter = $event->getParam('filter');
            $metadataMatcher = $event->getParam('metadataMatcher');
            $limit = $event->getParam('limit');
            $offset = $event->getParam('offset');

            $streamNames = $this->eventStore->fetchStreamNamesRegex($filter, $metadataMatcher, $limit, $offset);

            $event->setParam('streamNames', $streamNames);
        });

        $actionEventEmitter->attachListener(self::EVENT_FETCH_CATEGORY_NAMES, function (ActionEvent $event) {
            $filter = $event->getParam('filter');
            $limit = $event->getParam('limit');
            $offset = $event->getParam('offset');

            $streamNames = $this->eventStore->fetchCategoryNames($filter, $limit, $offset);

            $event->setParam('categoryNames', $streamNames);
        });

        $actionEventEmitter->attachListener(self::EVENT_FETCH_CATEGORY_NAMES_REGEX, function (ActionEvent $event) {
            $filter = $event->getParam('filter');
            $limit = $event->getParam('limit');
            $offset = $event->getParam('offset');

            $streamNames = $this->eventStore->fetchCategoryNamesRegex($filter, $limit, $offset);

            $event->setParam('categoryNames', $streamNames);
        });
    }

    public function create(Stream $stream)
    {
        $argv = ['stream' => $stream];

        $event = $this->actionEventEmitter->getNewActionEvent(self::EVENT_CREATE, $this, $argv);

        $this->actionEventEmitter->dispatch($event);

        if ($event->getParam('streamExistsAlready', false)) {
            throw StreamExistsAlready::with($stream->streamName());
        }
    }

    public function appendTo(StreamName $streamName, Iterator $streamEvents)
    {
        $argv = ['streamName' => $streamName, 'streamEvents' => $streamEvents];

        $event = $this->actionEventEmitter->getNewActionEvent(self::EVENT_APPEND_TO, $this, $argv);

        $this->actionEventEmitter->dispatch($event);

        if ($event->getParam('streamNotFound', false)) {
            throw StreamNotFound::with($streamName);
        }

        if ($event->getParam('concurrencyException', false)) {
            throw new ConcurrencyException();
        }
    }

    public function load(
        StreamName $streamName,
        $fromNumber = 1,
        $count = null,
        MetadataMatcher $metadataMatcher = null
    ) {
        Assertion::greaterOrEqualThan($fromNumber, 1);
        Assertion::nullOrGreaterOrEqualThan($count, 1);

        $argv = [
            'streamName' => $streamName,
            'fromNumber' => $fromNumber,
            'count' => $count,
            'metadataMatcher' => $metadataMatcher,
        ];

        $event = $this->actionEventEmitter->getNewActionEvent(self::EVENT_LOAD, $this, $argv);

        $this->actionEventEmitter->dispatch($event);

        if ($event->getParam('streamNotFound', false)) {
            throw StreamNotFound::with($streamName);
        }

        $stream = $event->getParam('streamEvents', false);

        if (! $stream instanceof Iterator) {
            throw StreamNotFound::with($streamName);
        }

        return $stream;
    }

    public function loadReverse(
        StreamName $streamName,
        $fromNumber = null,
        $count = null,
        MetadataMatcher $metadataMatcher = null
    ) {
        Assertion::nullOrGreaterOrEqualThan($fromNumber, 1);
        Assertion::nullOrGreaterOrEqualThan($count, 1);

        $argv = [
            'streamName' => $streamName,
            'fromNumber' => $fromNumber,
            'count' => $count,
            'metadataMatcher' => $metadataMatcher,
        ];

        $event = $this->actionEventEmitter->getNewActionEvent(self::EVENT_LOAD_REVERSE, $this, $argv);

        $this->actionEventEmitter->dispatch($event);

        if ($event->getParam('streamNotFound', false)) {
            throw StreamNotFound::with($streamName);
        }

        $stream = $event->getParam('streamEvents', false);

        if (! $stream instanceof Iterator) {
            throw StreamNotFound::with($streamName);
        }

        return $stream;
    }

    public function delete(StreamName $streamName)
    {
        $event = $this->actionEventEmitter->getNewActionEvent(self::EVENT_DELETE, $this, ['streamName' => $streamName]);

        $this->actionEventEmitter->dispatch($event);

        if ($event->getParam('streamNotFound', false)) {
            throw StreamNotFound::with($streamName);
        }
    }

    public function hasStream(StreamName $streamName)
    {
        $event = $this->actionEventEmitter->getNewActionEvent(
            self::EVENT_HAS_STREAM,
            $this,
            ['streamName' => $streamName]
        );

        $this->actionEventEmitter->dispatch($event);

        return $event->getParam('result', false);
    }

    public function fetchStreamMetadata(StreamName $streamName)
    {
        $event = $this->actionEventEmitter->getNewActionEvent(
            self::EVENT_FETCH_STREAM_METADATA,
            $this,
            ['streamName' => $streamName]
        );

        $this->actionEventEmitter->dispatch($event);

        if ($event->getParam('streamNotFound', false)) {
            throw StreamNotFound::with($streamName);
        }

        $metadata = $event->getParam('metadata', false);

        if (! is_array($metadata)) {
            throw StreamNotFound::with($streamName);
        }

        return $metadata;
    }

    public function updateStreamMetadata(StreamName $streamName, array $newMetadata)
    {
        $event = $this->actionEventEmitter->getNewActionEvent(
            self::EVENT_UPDATE_STREAM_METADATA,
            $this,
            [
                'streamName' => $streamName,
                'metadata' => $newMetadata,
            ]
        );

        $this->actionEventEmitter->dispatch($event);

        if ($event->getParam('streamNotFound', false)) {
            throw StreamNotFound::with($streamName);
        }
    }

    public function fetchStreamNames(
        $filter,
        MetadataMatcher $metadataMatcher = null,
        $limit = 20,
        $offset = 0
    ) {
        $event = $this->actionEventEmitter->getNewActionEvent(
            self::EVENT_FETCH_STREAM_NAMES,
            $this,
            [
                'filter' => $filter,
                'metadataMatcher' => $metadataMatcher,
                'limit' => $limit,
                'offset' => $offset,
            ]
        );

        $this->actionEventEmitter->dispatch($event);

        return $event->getParam('streamNames', []);
    }

    public function fetchStreamNamesRegex(
        $filter,
        MetadataMatcher $metadataMatcher = null,
        $limit = 20,
        $offset = 0
    ) {
        $event = $this->actionEventEmitter->getNewActionEvent(
            self::EVENT_FETCH_STREAM_NAMES_REGEX,
            $this,
            [
                'filter' => $filter,
                'metadataMatcher' => $metadataMatcher,
                'limit' => $limit,
                'offset' => $offset,
            ]
        );

        $this->actionEventEmitter->dispatch($event);

        return $event->getParam('streamNames', []);
    }

    public function fetchCategoryNames($filter, $limit = 20, $offset = 0)
    {
        $event = $this->actionEventEmitter->getNewActionEvent(
            self::EVENT_FETCH_CATEGORY_NAMES,
            $this,
            [
                'filter' => $filter,
                'limit' => $limit,
                'offset' => $offset,
            ]
        );

        $this->actionEventEmitter->dispatch($event);

        return $event->getParam('categoryNames', []);
    }

    public function fetchCategoryNamesRegex($filter, $limit = 20, $offset = 0)
    {
        $event = $this->actionEventEmitter->getNewActionEvent(
            self::EVENT_FETCH_CATEGORY_NAMES_REGEX,
            $this,
            [
                'filter' => $filter,
                'limit' => $limit,
                'offset' => $offset,
            ]
        );

        $this->actionEventEmitter->dispatch($event);

        return $event->getParam('categoryNames', []);
    }

    public function attach($eventName, callable $listener, $priority = 0)
    {
        return $this->actionEventEmitter->attachListener($eventName, $listener, $priority);
    }

    public function detach(ListenerHandler $handler)
    {
        $this->actionEventEmitter->detachListener($handler);
    }

    public function getInnerEventStore()
    {
        return $this->eventStore;
    }
}
