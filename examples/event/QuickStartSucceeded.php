<?php
/**
 * This file is part of the prooph/event-store.
 * (c) 2014-2018 prooph software GmbH <contact@prooph.de>
 * (c) 2015-2018 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Prooph\EventStore\QuickStart\Event;

use Prooph\Common\Messaging\DomainEvent;
use Prooph\EventStore\Util\Assertion;

/**
 * Class QuickStartSucceeded
 *
 * @author Alexander Miertsch <contact@prooph.de>
 */
final class QuickStartSucceeded extends DomainEvent
{
    /**
     * @var string
     */
    private $text;

    public static function withSuccessMessage($text)
    {
        return new self($text);
    }

    private function __construct($text)
    {
        Assertion::minLength($text, 1, 'Success message must be at least 1 char long');
        $this->text = $text;
        $this->metadata['_aggregate_version'] = 1;
        $this->init();
    }

    public function getText()
    {
        return $this->text;
    }

    public function payload()
    {
        return ['text' => $this->text];
    }

    protected function setPayload(array $payload)
    {
        $this->text = $payload['text'];
    }
}
