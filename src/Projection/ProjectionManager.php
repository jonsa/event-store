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

use Prooph\EventStore\Exception\ProjectionNotFound;

interface ProjectionManager
{
    public function createQuery();

    public function createProjection(
        $name,
        array $options = []
    );

    public function createReadModelProjection(
        $name,
        ReadModel $readModel,
        array $options = []
    );

    /**
     * @throws ProjectionNotFound
     */
    public function deleteProjection($name, $deleteEmittedEvents);

    /**
     * @throws ProjectionNotFound
     */
    public function resetProjection($name);

    /**
     * @throws ProjectionNotFound
     */
    public function stopProjection($name);

    /**
     * @return string[]
     */
    public function fetchProjectionNames($filter, $limit = 20, $offset = 0);

    /**
     * @return string[]
     */
    public function fetchProjectionNamesRegex($regex, $limit = 20, $offset = 0);

    /**
     * @throws ProjectionNotFound
     */
    public function fetchProjectionStatus($name);

    /**
     * @throws ProjectionNotFound
     */
    public function fetchProjectionStreamPositions($name);

    /**
     * @throws ProjectionNotFound
     */
    public function fetchProjectionState($name);
}
