<?php
/**
 * This file is part of the prooph/event-store.
 * (c) 2014-2018 prooph software GmbH <contact@prooph.de>
 * (c) 2015-2018 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Prooph\EventStore\Metadata;

use MabeEnum\Enum;

/**
 * @method static FieldType METADATA()
 * @method static FieldType MESSAGE_PROPERTY()
 */
final class FieldType extends Enum
{
    const METADATA = 0;
    const MESSAGE_PROPERTY = 1;
}
