<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Tests\Component\EventDispatcher;

use Symfony\Bundle\FrameworkBundle\ContainerAwareEventDispatcher;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\Event;

class ContainerAwareEventDispatcherTest extends \PHPUnit_Framework_TestCase
{
    private $container;

    protected function setUp()
    {
        $this->container = new Container();
        
        $this->container->set('foo.event_listener', new TestEventListener());
        
        $dispatcher = new ContainerAwareEventDispatcher($this->container);
        $dispatcher->addListenerService(TestEventListener::onFoo, array('foo.event_listener', 'onFoo'));
        
        $this->container->set('event_dispatcher', $dispatcher);
    }

    protected function tearDown()
    {
        $this->container = null;
    }
    
    public function testDispatch()
    {
        $this->assertTrue($this->container->get('event_dispatcher')->hasListeners(TestEventListener::onFoo));
        $this->container->get('event_dispatcher')->dispatch(TestEventListener::onFoo, new Event());
        $this->assertTrue($this->container->get('foo.event_listener')->onFooInvoked);
    }
    
    public function testRemoveAfterDispatch()
    {
        $this->assertTrue($this->container->get('event_dispatcher')->hasListeners(TestEventListener::onFoo));
        $this->container->get('event_dispatcher')->dispatch(TestEventListener::onFoo, new Event());
        $this->assertTrue($this->container->get('foo.event_listener')->onFooInvoked);
        $this->container->get('event_dispatcher')->removeListener(TestEventListener::onFoo, array($this->container->get('event_dispatcher'), 'onFoo'));
        $this->assertTrue($this->container->get('event_dispatcher')->hasListeners(TestEventListener::onFoo));
    }

    /* this test is failing */
    public function testRemoveWithoutDispatch()
    {
        $this->assertTrue($this->container->get('event_dispatcher')->hasListeners(TestEventListener::onFoo));
        // remove listener
        $this->container->get('event_dispatcher')->removeListener(TestEventListener::onFoo, array($this->container->get('foo.event_listener'), 'onFoo'));
        
        // failed, still can be invoked
        $this->container->get('event_dispatcher')->dispatch(TestEventListener::onFoo, new Event());
        $this->assertFalse($this->container->get('foo.event_listener')->onFooInvoked);
        $this->assertFalse($this->container->get('event_dispatcher')->hasListeners(TestEventListener::onFoo));
    }
}

class TestEventListener
{
    const onFoo = 'on.foo';
    
    public $onFooInvoked = false;

    /* onFoo listener */
    public function onFoo(Event $e)
    {
        $this->onFooInvoked = true;
    }
}