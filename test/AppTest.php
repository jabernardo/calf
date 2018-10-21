<?php

if (file_exists('PHPUnit/Autoload.php'))
    require_once('PHPUnit/Autoload.php');

// backward compatibility
if (!class_exists('\PHPUnit\Framework\TestCase') &&
    class_exists('\PHPUnit_Framework_TestCase')) {
    class_alias('\PHPUnit_Framework_TestCase', '\PHPUnit\Framework\TestCase');
}

// test compatibility
if (!function_exists('getallheaders')) {
    function getallheaders() {
    $headers = [];
    foreach ($_SERVER as $name => $value) {
        if (substr($name, 0, 5) == 'HTTP_') {
            $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
        }
    }
    return $headers;
    }
}

// Include vendor autoload
require('vendor/autoload.php');

class AppTest extends \PHPUnit\Framework\TestCase
{
    private $requestTimeFloat;

    public function setUp()
    {
        $this->requestTimeFloat = $_SERVER['REQUEST_TIME_FLOAT'];
    }

    public function tearDown()
    {
        $_SERVER['REQUEST_TIME_FLOAT'] = $this->requestTimeFloat;
    }
    
    public function testApp() {
        $container = new \Saddle\Container();

        $container->message = 'Hello World!';

        $this->assertEquals($container->message, 'Hello World!');

        $app = new \Calf\App($container);

        $this->assertTrue($app instanceof \Calf\App);

        $c = $app->getContainer();

        $this->assertTrue($c instanceof \Saddle\Container);

        $r = $app->getRouter();

        $this->assertTrue($r instanceof \Calf\HTTP\Router);

        $_SERVER = [];
        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['SCRIPT_NAME'] = 'index.php';

        $_GET = [];
        $_POST = [];
        $_FILES = [];

        $home = new \Calf\HTTP\Route('/', function($req, $res) {
            return $res->write($this->message);
        });
    
        $app->add($home);

        $res = $app->run(true);

        ob_start();
        $app->run();
        $output = ob_get_contents();
        ob_end_clean();

        $this->assertEquals($output, 'Hello World!');
        $this->assertEquals($res->get(), 'Hello World!');
        $this->assertTrue($res instanceof \Calf\HTTP\Response);
    }
}
