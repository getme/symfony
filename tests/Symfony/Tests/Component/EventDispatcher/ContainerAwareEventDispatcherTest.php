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
        $listener = $this->container->get('foo.event_listener');
        $dispatcher = $this->container->get('event_dispatcher');
        
        $this->assertTrue($dispatcher->hasListeners($listener::onFoo));
        $dispatcher->dispatch($listener::onFoo, new Event());
        $this->assertTrue($listener->onFooInvoked);
    }
    
    /* failing */
    public function testRemoveAfterDispatch()
    {
        $listener = $this->container->get('foo.event_listener');
        $dispatcher = $this->container->get('event_dispatcher');
        
        $this->assertTrue($listener instanceof TestEventListener);
        $this->assertTrue($dispatcher->hasListeners($listener::onFoo));
        $dispatcher->dispatch($listener::onFoo, new Event());
        $this->assertTrue($listener->onFooInvoked);
        $dispatcher->removeListener($listener::onFoo, array($listener, 'onFoo'));
        $this->assertFalse($dispatcher->hasListeners($listener::onFoo));
    }

    /* failing */
    public function testRemoveBeforeDispatch()
    {
        $listener = $this->container->get('foo.event_listener');
        $dispatcher = $this->container->get('event_dispatcher');
        
        $this->assertTrue($listener instanceof TestEventListener);
        $this->assertTrue($dispatcher->hasListeners($listener::onFoo));
        $dispatcher->removeListener($listener::onFoo, array($listener, 'onFoo'));
        $dispatcher->dispatch($listener::onFoo, new Event());
        $this->assertFalse($listener->onFooInvoked);
        $this->assertFalse($dispatcher->hasListeners(TestEventListener::onFoo));
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