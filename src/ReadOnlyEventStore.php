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
use Prooph\EventStore\Metadata\MetadataMatcher;

interface ReadOnlyEventStore
{
    public function fetchStreamMetadata(StreamName $streamName);

    public function hasStream(StreamName $streamName);

    public function load(
        StreamName $streamName,
        $fromNumber = 1,
        $count = null,
        MetadataMatcher $metadataMatcher = null
    );

    public function loadReverse(
        StreamName $streamName,
        $fromNumber = null,
        $count = null,
        MetadataMatcher $metadataMatcher = null
    );

    /**
     * @return StreamName[]
     */
    public function fetchStreamNames(
        $filter,
        MetadataMatcher $metadataMatcher = null,
        $limit = 20,
        $offset = 0
    );

    /**
     * @return StreamName[]
     */
    public function fetchStreamNamesRegex(
        $filter,
        MetadataMatcher $metadataMatcher = null,
        $limit = 20,
        $offset = 0
    );

    /**
     * @return string[]
     */
    public function fetchCategoryNames($filter, $limit = 20, $offset = 0);

    /**
     * @return string[]
     */
    public function fetchCategoryNamesRegex($filter, $limit = 20, $offset = 0);
}
