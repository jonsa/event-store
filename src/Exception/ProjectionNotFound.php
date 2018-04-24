<?php
/**
 * This file is part of the prooph/event-store.
 * (c) 2014-2018 prooph software GmbH <contact@prooph.de>
 * (c) 2015-2018 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Prooph\EventStore\Exception;

class ProjectionNotFound extends RuntimeException
{
    public static function withName($name)
    {
        return new self('A projection with name "' . $name . '" could not be found.');
    }
}
