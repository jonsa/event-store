<?php
/**
 * This file is part of the prooph/event-store.
 * (c) 2014-2018 prooph software GmbH <contact@prooph.de>
 * (c) 2015-2018 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace ProophTest\EventStore\Example;

use PHPUnit\Framework\TestCase;

class QuickStartTest extends TestCase
{
    /**
     * @test
     */
    public function it_provides_the_correct_example_output()
    {
        $pattern = sprintf(
            '~^Event with name Prooph\\\\EventStore\\\\QuickStart\\\\Event\\\\QuickStartSucceeded was recorded\. It occurred on %s ///\n\nIt works$~',
            '\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}'
        );

        $this->assertRegExp($pattern, $this->getQuickstartOutput());
    }

    private function getQuickstartOutput()
    {
        ob_start();
        include __DIR__ . '/../../examples/quickstart.php';

        return ob_get_clean();
    }
}
