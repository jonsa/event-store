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

interface Query
{
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
     *         $state->count++;
     *         return $state;
     *     },
     *     'UserDeleted' => function (array $state, Message $event) {
     *         $state->count--;
     *         return $state;
     *     }
     * ])
     */
    public function when(array $handlers);

    /**
     * For example:
     * function(array $state, Message $event) {
     *     $state->count++;
     *     return $state;
     * }
     */
    public function whenAny(Closure $closure);

    public function reset();

    public function run();

    public function stop();

    public function getState();
}
