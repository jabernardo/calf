<?php

if (file_exists('PHPUnit/Autoload.php'))
    require_once('PHPUnit/Autoload.php');

// backward compatibility
if (!class_exists('\PHPUnit\Framework\TestCase') &&
    class_exists('\PHPUnit_Framework_TestCase')) {
    class_alias('\PHPUnit_Framework_TestCase', '\PHPUnit\Framework\TestCase');
}

class AppTest extends \PHPUnit\Framework\TestCase
{
    public function testApp() {
        $container = new \Calf\Saddle();
        $container->message = 'Hello World!';

        $this->assertEquals($container->message, 'Hello World!');

        $app = new \Calf\App($container);

        $this->assertTrue($app instanceof \Calf\App);

        $c = $app->getContainer();

        $this->assertTrue($c instanceof \Calf\Saddle);

        $r = $app->getRouter();

        $this->assertTrue($r instanceof \Calf\HTTP\Router);

        $_SERVER = [];
        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER["SCRIPT_NAME"] = '/index.php';

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
