<?php
/**
 * This file is part of the prooph/event-store.
 * (c) 2014-2018 prooph software GmbH <contact@prooph.de>
 * (c) 2015-2018 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace ProophTest\EventStore\Projection;

use PHPUnit\Framework\TestCase;
use Prooph\EventStore\Exception\InvalidArgumentException;
use Prooph\EventStore\Exception\OutOfRangeException;
use Prooph\EventStore\Exception\ProjectionNotFound;
use Prooph\EventStore\Projection\ProjectionManager;
use Prooph\EventStore\Projection\ProjectionStatus;

/**
 * Common tests for all projection manager implementations
 */
abstract class AbstractProjectionManagerTest extends TestCase
{
    /**
     * @var ProjectionManager
     */
    protected $projectionManager;

    /**
     * @test
     */
    public function it_fetches_projection_names()
    {
        $projections = [];

        try {
            for ($i = 0; $i < 50; $i++) {
                $projection = $this->projectionManager->createProjection('user-' . $i);
                $projection->fromAll()->whenAny(function () {
                })->run(false);
                $projections[] = $projection;
            }

            for ($i = 0; $i < 20; $i++) {
                $projection = $this->projectionManager->createProjection(uniqid('rand'));
                $projection->fromAll()->whenAny(function () {
                })->run(false);
                $projections[] = $projection;
            }

            $this->assertCount(20, $this->projectionManager->fetchProjectionNames(null));
            $this->assertCount(70, $this->projectionManager->fetchProjectionNames(null, 200, 0));
            $this->assertCount(0, $this->projectionManager->fetchProjectionNames(null, 200, 100));
            $this->assertCount(10, $this->projectionManager->fetchProjectionNames(null, 10, 0));
            $this->assertCount(10, $this->projectionManager->fetchProjectionNames(null, 10, 10));
            $this->assertCount(5, $this->projectionManager->fetchProjectionNames(null, 10, 65));

            for ($i = 0; $i < 20; $i++) {
                $this->assertStringStartsWith('rand', $this->projectionManager->fetchProjectionNames(null, 1, $i)[0]);
            }

            $this->assertCount(30, $this->projectionManager->fetchProjectionNamesRegex('ser-', 30, 0));
            $this->assertCount(0, $this->projectionManager->fetchProjectionNamesRegex('n-', 30, 0));
        } finally {
            foreach ($projections as $projection) {
                $projection->delete(false);
            }
        }
    }

    /**
     * @test
     */
    public function it_fetches_projection_names_with_filter()
    {
        $projection = $this->projectionManager->createProjection('user-1');
        $projection->fromAll()->whenAny(function () {
        })->run(false);

        $projection = $this->projectionManager->createProjection('user-2');
        $projection->fromAll()->whenAny(function () {
        })->run(false);

        $projection = $this->projectionManager->createProjection('rand-1');
        $projection->fromAll()->whenAny(function () {
        })->run(false);

        $projection = $this->projectionManager->createProjection('user-3');
        $projection->fromAll()->whenAny(function () {
        })->run(false);

        $this->assertSame(['user-1'], $this->projectionManager->fetchProjectionNames('user-1'));
        $this->assertSame(['user-2'], $this->projectionManager->fetchProjectionNames('user-2', 2));
        $this->assertSame(['rand-1'], $this->projectionManager->fetchProjectionNames('rand-1', 5));

        $this->assertSame([], $this->projectionManager->fetchProjectionNames('foo'));
        $this->assertSame([], $this->projectionManager->fetchProjectionNames('foo', 5));
        $this->assertSame([], $this->projectionManager->fetchProjectionNames('foo', 10, 100));
    }

    /**
     * @test
     */
    public function it_fetches_projection_names_sorted()
    {
        $projection = $this->projectionManager->createProjection('user-100');
        $projection->fromAll()->whenAny(function () {
        })->run(false);

        $projection = $this->projectionManager->createProjection('user-21');
        $projection->fromAll()->whenAny(function () {
        })->run(false);

        $projection = $this->projectionManager->createProjection('rand-5');
        $projection->fromAll()->whenAny(function () {
        })->run(false);

        $projection = $this->projectionManager->createProjection('user-10');
        $projection->fromAll()->whenAny(function () {
        })->run(false);

        $projection = $this->projectionManager->createProjection('user-1');
        $projection->fromAll()->whenAny(function () {
        })->run(false);

        $this->assertEquals(
            ['rand-5', 'user-1', 'user-10', 'user-100', 'user-21'],
            $this->projectionManager->fetchProjectionNames(null)
        );
    }

    /**
     * @test
     */
    public function it_throws_exception_when_fetching_projection_names_using_invalid_limit()
    {
        $this->expectException(OutOfRangeException::class);
        $this->expectExceptionMessage('Invalid limit "-1" given. Must be greater than 0.');

        $this->projectionManager->fetchProjectionNames(null, -1, 0);
    }

    /**
     * @test
     */
    public function it_throws_exception_when_fetching_projection_names_using_invalid_offset()
    {
        $this->expectException(OutOfRangeException::class);
        $this->expectExceptionMessage('Invalid offset "-1" given. Must be greater or equal than 0.');

        $this->projectionManager->fetchProjectionNames(null, 1, -1);
    }

    /**
     * @test
     */
    public function it_fetches_projection_names_using_regex()
    {
        for ($i = 0; $i < 50; $i++) {
            $projection = $this->projectionManager->createProjection('user-' . $i);
            $projection->fromAll()->whenAny(function () {
            })->run(false);
        }

        for ($i = 0; $i < 20; $i++) {
            $projection = $this->projectionManager->createProjection(uniqid('rand'));
            $projection->fromAll()->whenAny(function () {
            })->run(false);
        }

        $this->assertCount(20, $this->projectionManager->fetchProjectionNamesRegex('user'));
        $this->assertCount(50, $this->projectionManager->fetchProjectionNamesRegex('user', 100));
        $this->assertCount(30, $this->projectionManager->fetchProjectionNamesRegex('ser-', 30, 0));
        $this->assertCount(0, $this->projectionManager->fetchProjectionNamesRegex('n-', 30, 0));
        $this->assertCount(5, $this->projectionManager->fetchProjectionNamesRegex('rand', 100, 15));
    }

    /**
     * @test
     */
    public function it_fetches_projection_names_sorted_using_regex()
    {
        $projection = $this->projectionManager->createProjection('user-100');
        $projection->fromAll()->whenAny(function () {
        })->run(false);

        $projection = $this->projectionManager->createProjection('user-21');
        $projection->fromAll()->whenAny(function () {
        })->run(false);

        $projection = $this->projectionManager->createProjection('rand-5');
        $projection->fromAll()->whenAny(function () {
        })->run(false);

        $projection = $this->projectionManager->createProjection('user-10');
        $projection->fromAll()->whenAny(function () {
        })->run(false);

        $projection = $this->projectionManager->createProjection('user-1');
        $projection->fromAll()->whenAny(function () {
        })->run(false);

        $this->assertEquals(
            json_encode(['user-1', 'user-10', 'user-100', 'user-21']),
            json_encode($this->projectionManager->fetchProjectionNamesRegex('ser-'))
        );
    }

    /**
     * @test
     */
    public function it_throws_exception_when_fetching_projection_names_using_regex_with_invalid_limit()
    {
        $this->expectException(OutOfRangeException::class);
        $this->expectExceptionMessage('Invalid limit "-1" given. Must be greater than 0.');

        $this->projectionManager->fetchProjectionNamesRegex('foo', -1, 0);
    }

    /**
     * @test
     */
    public function it_throws_exception_when_fetching_projection_names_using_regex_with_invalid_offset()
    {
        $this->expectException(OutOfRangeException::class);
        $this->expectExceptionMessage('Invalid offset "-1" given. Must be greater or equal than 0.');

        $this->projectionManager->fetchProjectionNamesRegex('bar', 1, -1);
    }

    /**
     * @test
     */
    public function it_throws_exception_when_fetching_projection_names_using_invalid_regex()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid regex pattern given');

        $this->projectionManager->fetchProjectionNamesRegex('invalid)', 10, 0);
    }

    /**
     * @test
     */
    public function it_throws_exception_when_asked_for_unknown_projection_status()
    {
        $this->expectException(ProjectionNotFound::class);

        $this->projectionManager->fetchProjectionStatus('unkown');
    }

    /**
     * @test
     */
    public function it_throws_exception_when_asked_for_unknown_projection_stream_positions()
    {
        $this->expectException(ProjectionNotFound::class);

        $this->projectionManager->fetchProjectionStreamPositions('unkown');
    }

    /**
     * @test
     */
    public function it_throws_exception_when_asked_for_unknown_projection_state()
    {
        $this->expectException(ProjectionNotFound::class);

        $this->projectionManager->fetchProjectionState('unkown');
    }

    /**
     * @test
     */
    public function it_fetches_projection_status()
    {
        $projection = $this->projectionManager->createProjection('test-projection');
        $projection->fromAll()->whenAny(function () {
        })->run(false);

        $this->assertSame(ProjectionStatus::IDLE(), $this->projectionManager->fetchProjectionStatus('test-projection'));
    }

    /**
     * @test
     */
    public function it_fetches_projection_stream_positions()
    {
        $projection = $this->projectionManager->createProjection('test-projection');
        $projection->fromAll()->whenAny(function () {
        })->run(false);

        $this->assertSame([], $this->projectionManager->fetchProjectionStreamPositions('test-projection'));
    }

    /**
     * @test
     */
    public function it_fetches_projection_state()
    {
        $projection = $this->projectionManager->createProjection('test-projection');
        $projection->fromAll()->whenAny(function () {
        })->run(false);

        $this->assertSame([], $this->projectionManager->fetchProjectionState('test-projection'));
    }

    /**
     * @test
     */
    public function it_throws_exception_when_trying_to_delete_non_existing_projection()
    {
        $this->expectException(ProjectionNotFound::class);

        $this->projectionManager->deleteProjection('unknown', false);
    }

    /**
     * @test
     */
    public function it_throws_exception_when_trying_to_reset_non_existing_projection()
    {
        $this->expectException(ProjectionNotFound::class);

        $this->projectionManager->resetProjection('unknown');
    }

    /**
     * @test
     */
    public function it_throws_exception_when_trying_to_stop_non_existing_projection()
    {
        $this->expectException(ProjectionNotFound::class);

        $this->projectionManager->stopProjection('unknown');
    }

    /**
     * @test
     */
    public function it_does_not_fail_deleting_twice()
    {
        $projection = $this->projectionManager->createProjection('test-projection');
        $projection->fromAll()->whenAny(function () {
        })->run(false);

        $this->projectionManager->deleteProjection('test-projection', false);
        $this->projectionManager->deleteProjection('test-projection', false);

        $this->assertTrue($this->projectionManager->fetchProjectionStatus('test-projection')->is(ProjectionStatus::DELETING()));
    }

    /**
     * @test
     */
    public function it_does_not_fail_resetting_twice()
    {
        $projection = $this->projectionManager->createProjection('test-projection');
        $projection->fromAll()->whenAny(function () {
        })->run(false);

        $this->projectionManager->resetProjection('test-projection');
        $this->projectionManager->resetProjection('test-projection');

        $this->assertTrue($this->projectionManager->fetchProjectionStatus('test-projection')->is(ProjectionStatus::RESETTING()));
    }

    /**
     * @test
     */
    public function it_does_not_fail_stopping_twice()
    {
        $projection = $this->projectionManager->createProjection('test-projection');
        $projection->fromAll()->whenAny(function () {
        })->run(false);

        $this->projectionManager->stopProjection('test-projection');
        $this->projectionManager->stopProjection('test-projection');

        $this->assertTrue($this->projectionManager->fetchProjectionStatus('test-projection')->is(ProjectionStatus::STOPPING()));
    }
}
