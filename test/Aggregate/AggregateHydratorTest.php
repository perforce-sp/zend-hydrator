<?php
/**
 * @see       https://github.com/zendframework/zend-hydrator for the canonical source repository
 * @copyright Copyright (c) 2010-2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-hydrator/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ZendTest\Hydrator\Aggregate;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use stdClass;
use Zend\EventManager\EventManager;
use Zend\Hydrator\Aggregate\AggregateHydrator;
use Zend\Hydrator\Aggregate\ExtractEvent;
use Zend\Hydrator\Aggregate\HydrateEvent;
use Zend\Hydrator\HydratorInterface;

/**
 * Unit tests for {@see AggregateHydrator}
 */
class AggregateHydratorTest extends TestCase
{
    /**
     * @var AggregateHydrator
     */
    protected $hydrator;

    /**
     * @var \Zend\EventManager\EventManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManager;

    /**
     * {@inheritDoc}
     */
    protected function setUp() : void
    {
        $this->eventManager = $this->prophesize(EventManager::class);
        $this->hydrator     = new AggregateHydrator();

        $this->hydrator->setEventManager($this->eventManager->reveal());
    }

    /**
     * @covers \Zend\Hydrator\Aggregate\AggregateHydrator::add
     */
    public function testAdd()
    {
        $attached = $this->prophesize(HydratorInterface::class);

        $this->eventManager
            ->attach(HydrateEvent::EVENT_HYDRATE, Argument::type('callable'), 123)
            ->shouldBeCalled();
        $this->eventManager
            ->attach(ExtractEvent::EVENT_EXTRACT, Argument::type('callable'), 123)
            ->shouldBeCalled();

        $this->hydrator->add($attached->reveal(), 123);
    }

    /**
     * @covers \Zend\Hydrator\Aggregate\AggregateHydrator::hydrate
     */
    public function testHydrate()
    {
        $object = new stdClass();

        $this->eventManager
            ->triggerEvent(Argument::type(HydrateEvent::class))
            ->shouldBeCalled();

        $this->assertSame($object, $this->hydrator->hydrate(['foo' => 'bar'], $object));
    }

    /**
     * @covers \Zend\Hydrator\Aggregate\AggregateHydrator::extract
     */
    public function testExtract()
    {
        $object = new stdClass();

        $this->eventManager
            ->triggerEvent(Argument::type(ExtractEvent::class))
            ->shouldBeCalled();

        $this->assertSame([], $this->hydrator->extract($object));
    }

    /**
     * @covers \Zend\Hydrator\Aggregate\AggregateHydrator::getEventManager
     * @covers \Zend\Hydrator\Aggregate\AggregateHydrator::setEventManager
     */
    public function testGetSetManager()
    {
        $hydrator     = new AggregateHydrator();
        $eventManager = $this->prophesize(EventManager::class);

        $this->assertInstanceOf(EventManager::class, $hydrator->getEventManager());

        $eventManager
            ->setIdentifiers([AggregateHydrator::class, AggregateHydrator::class])
            ->shouldBeCalled();

        $hydrator->setEventManager($eventManager->reveal());

        $this->assertSame($eventManager->reveal(), $hydrator->getEventManager());
    }
}
