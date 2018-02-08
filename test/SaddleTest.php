<?php

if (file_exists('PHPUnit/Autoload.php'))
    require_once('PHPUnit/Autoload.php');

// backward compatibility
if (!class_exists('\PHPUnit\Framework\TestCase') &&
    class_exists('\PHPUnit_Framework_TestCase')) {
    class_alias('\PHPUnit_Framework_TestCase', '\PHPUnit\Framework\TestCase');
}

class SaddleTest extends \PHPUnit\Framework\TestCase
{
    function testSaddle() {
        $container = new \Calf\Saddle(['message' => 'Hello World!']);

        $this->assertTrue($container->exists('message'));
        $this->assertEquals('Hello World!', $container->get('message'));

        $container->add('callback', function($c) {
            return $c->get('message');
        }, true);

        $this->assertEquals('Hello World!', $container->get('callback'));
        $this->assertTrue(is_callable($container->get('callback', true)));

        $container->remove('message');
        $container->add('Direction', 'up');
        $container->update('Direction', 'down');

        $this->assertEquals($container->get('Direction'), 'down');
        $this->assertFalse($container->exists('message'));

        $container->add('safe', 'Another Test', true);

        try {
            $container->add(1, 'booooo');
        } catch (\Calf\Exception\InvalidArgument $ex) {
            $this->assertEquals($ex->getCode(), 101);
        }

        try {
            $container->add('safe', 'booooo');
        } catch (\Calf\Exception\Runtime $ex) {
            $this->assertEquals($ex->getCode(), 100);
        }

        try {
            $container->remove('safe');
        } catch (\Calf\Exception\Runtime $ex) {
            $this->assertEquals($ex->getCode(), 100);
        }

        try {
            $container->update('safe', 'just an update');
        } catch (\Calf\Exception\Runtime $ex) {
            $this->assertEquals($ex->getCode(), 100);
        }
    }
}
