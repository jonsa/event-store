<?php
/**
 * This file is part of the prooph/event-store.
 * (c) 2014-2018 prooph software GmbH <contact@prooph.de>
 * (c) 2015-2018 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Prooph\EventStore\Projection;

use Closure;
use Prooph\Common\Messaging\Message;

interface Projector
{
    const OPTION_CACHE_SIZE = 'cache_size';
    const OPTION_SLEEP = 'sleep';
    const OPTION_PERSIST_BLOCK_SIZE = 'persist_block_size';
    const OPTION_LOCK_TIMEOUT_MS = 'lock_timeout_ms';
    const OPTION_PCNTL_DISPATCH = 'trigger_pcntl_dispatch';
    const OPTION_UPDATE_LOCK_THRESHOLD = 'update_lock_threshold';

    const DEFAULT_CACHE_SIZE = 1000;
    const DEFAULT_SLEEP = 100000;
    const DEFAULT_PERSIST_BLOCK_SIZE = 1000;
    const DEFAULT_LOCK_TIMEOUT_MS = 1000;
    const DEFAULT_PCNTL_DISPATCH = false;
    const DEFAULT_UPDATE_LOCK_THRESHOLD = 0;

    /**
     * The callback has to return an array
     */
    public function init(Closure $callback);

    public function fromStream($streamName);

    public function fromStreams(...$streamNames);

    public function fromCategory($name);

    public function fromCategories(...$names);

    public function fromAll();

    /**
     * For example:
     *
     * when([
     *     'UserCreated' => function (array $state, Message $event) {
     *         $state['count']++;
     *         return $state;
     *     },
     *     'UserDeleted' => function (array $state, Message $event) {
     *         $state['count']--;
     *         return $state;
     *     }
     * ])
     */
    public function when(array $handlers);

    /**
     * For example:
     * function(array $state, Message $event) {
     *     $state['count']++;
     *     return $state;
     * }
     */
    public function whenAny(Closure $closure);

    public function reset();

    public function stop();

    public function getState();

    public function getName();

    public function emit(Message $event);

    public function linkTo($streamName, Message $event);

    public function delete($deleteEmittedEvents);

    public function run($keepRunning = true);
}
