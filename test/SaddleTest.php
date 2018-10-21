<?php

if (file_exists('PHPUnit/Autoload.php'))
    require_once('PHPUnit/Autoload.php');

// backward compatibility
if (!class_exists('\PHPUnit\Framework\TestCase') &&
    class_exists('\PHPUnit_Framework_TestCase')) {
    class_alias('\PHPUnit_Framework_TestCase', '\PHPUnit\Framework\TestCase');
}

// Include vendor autoload
require('vendor/autoload.php');

class SaddleTest extends \PHPUnit\Framework\TestCase
{
    function testSaddle() {
        $container = new \Saddle\Container(['message' => 'Hello World!']);

        $this->assertTrue(isset($container->message));
        $this->assertEquals('Hello World!', $container->message);

        $container->callback = function($c) {
            return $c->message;
        };

        $this->assertEquals('Hello World!', $container->callback);
        $this->assertTrue(is_callable($container->raw('callback')));

        unset($container->message);
        $container->Direction = 'up';
        $container->Direction = 'down';

        $this->assertEquals($container->Direction, 'down');
        $this->assertFalse(isset($container->message));

        $container->safe = 'Another Test';
        $container->protect('safe');

        try {
            $container->safe = 'booooo';
        } catch (\Calf\Exception\Runtime $ex) {
            $this->assertEquals($ex->getCode(), 100);
        }

        try {
            unset($container->safe);
        } catch (\Calf\Exception\Runtime $ex) {
            $this->assertEquals($ex->getCode(), 100);
        }

        try {
            $container->safe = 'just an update';
        } catch (\Calf\Exception\Runtime $ex) {
            $this->assertEquals($ex->getCode(), 100);
        }
    }
}
