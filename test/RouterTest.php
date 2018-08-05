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

class RouterTest extends \PHPUnit\Framework\TestCase
{
    public function testRouterInstance() {
        $router = new \Calf\HTTP\Router();
        
        $this->assertTrue($router instanceof \Calf\HTTP\Router);
    }
    
    public function testRouterAdd() {
        $router = new \Calf\HTTP\Router();
        
        $home = new \Calf\HTTP\Route('/', function($req, $res) {
            return $res->set('Hello World!');
        });
        
        $router->add($home);
        
        $this->assertTrue($router->exists('/'));
    }
    
    public function testRouterRemove() {
        $router = new \Calf\HTTP\Router();
        
        $home = new \Calf\HTTP\Route('/', function($req, $res) {
            return $res->set('Hello World!');
        });
        
        $router->add($home);
        $this->assertTrue($router->remove('/'));
        $this->assertFalse($router->exists('/'));
    }
    
    public function testRouterRemoveFail() {
        $router = new \Calf\HTTP\Router();
        
        $home = new \Calf\HTTP\Route('/', function($req, $res) {
            return $res->set('Hello World!');
        });
        
        $router->add($home);
        $this->assertFalse($router->remove('/test'));
        $this->assertFalse($router->exists('/test'));
    }
    
    public function testRouterRemoveObject() {
        $router = new \Calf\HTTP\Router();
        
        $home = new \Calf\HTTP\Route('/', function($req, $res) {
            return $res->set('Hello World!');
        });
        
        $router->add($home);
        $this->assertTrue($router->remove($home));
        $this->assertFalse($router->exists($home));
    }
    
    public function testRouterRemoveObjectFail() {
        $router = new \Calf\HTTP\Router();
        
        $home = new \Calf\HTTP\Route('/', function($req, $res) {
            return $res->set('Hello World!');
        });
        
        $router->add($home);
        
        try {
            $router->remove(12321);
        } catch (\Calf\Exception\InvalidArgument $ex) {
            $this->assertEquals($ex->getCode(), 101);
        }
        
        $this->assertTrue($router->exists($home));
    }
    
    public function testDispatch() {
        $_SERVER = [];
        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['SCRIPT_NAME'] = 'index.php';

        $_GET = [];
        $_POST = [];
        $_FILES = [];

        $router = new \Calf\HTTP\Router();
        
        $home = new \Calf\HTTP\Route('/', function($req, $res) {
            return $res->set('Hello World!');
        });
        
        $router->add($home);

        $response = $router->dispatch();

        $this->assertEquals($response->get(true), 'Hello World!');
    }
    
    public function testDispatchWrite() {
        $_SERVER = [];
        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['SCRIPT_NAME'] = 'index.php';

        $_GET = [];
        $_POST = [];
        $_FILES = [];

        $router = new \Calf\HTTP\Router();
        
        $home = new \Calf\HTTP\Route('/', function($req, $res) {
            $res->write('Hello World');
            $res->write('!');
            
            return $res;
        });
        
        $router->add($home);
        
        $response = $router->dispatch();

        $this->assertEquals($response->get(true), 'Hello World!');
    }

    public function testDispatchMiddleware() {
        $_SERVER = [];
        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['SCRIPT_NAME'] = 'index.php';

        $_GET = [];
        $_POST = [];
        $_FILES = [];
        
        $router = new \Calf\HTTP\Router();
        
        $home = new \Calf\HTTP\Route('/', function($req, $res) {
            return $res->set($res->get() . 'Hello World!');
        });
        
        $home->addMiddleware(function($req, $res, $next) {
            $res->set($res->get() . '1');
            $next($req, $res);
            $res->set($res->get() . '1');

            return $res;
        });
        
        $router->add($home);
        
        $response = $router->dispatch();

        $this->assertEquals($response->get(true), '1Hello World!1');
    }
    
    public function testDispatchMiddlewareMultiple() {
        $_SERVER = [];
        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['SCRIPT_NAME'] = 'index.php';

        $_GET = [];
        $_POST = [];
        $_FILES = [];
        
        $router = new \Calf\HTTP\Router();
        
        $home = new \Calf\HTTP\Route('/', function($req, $res) {
            return $res->set($res->get() . 'Hello World!');
        });
        
        $home->addMiddleware(function($req, $res, $next) {
            $res->set($res->get() . '1');
            $next($req, $res);
            $res->set($res->get() . '1');

            return $res;
        });
        
        $home->addMiddleware(function($req, $res, $next) {
            $res->set($res->get() . '2');
            $next($req, $res);
            $res->set($res->get() . '2');

            return $res;
        });
        
        $router->add($home);
        
        $response = $router->dispatch();

        $this->assertEquals($response->get(true), '21Hello World!12');
    }

    public function testRouteGroup() {
        $_SERVER = [];
        $_SERVER['REQUEST_URI'] = '/products/get';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['SCRIPT_NAME'] = 'index.php';

        $_GET = [];
        $_POST = [];
        $_FILES = [];

        $router = new \Calf\HTTP\Router();
        
        $productsGroup = new \Calf\HTTP\RouteGroup('products');

        $productsGroup->add(
            new \Calf\HTTP\Route('get', function(\Calf\HTTP\Request $req, \Calf\HTTP\Response $res, array $args) {
                return $res->set('Pen, Pineapple, Apple');
            })
        );
        
        $productsGroup->add(
            new \Calf\HTTP\Route('get/fruits', function(\Calf\HTTP\Request $req, \Calf\HTTP\Response $res, array $args) {
                return $res->set('Pineapple, Apple');
            })
        );

        $router->addGroup($productsGroup);

        $response = $router->dispatch();

        $this->assertEquals('Pen, Pineapple, Apple', $response->get(true));
    }

    public function testRoutePatterns() {
        $_SERVER = [];
        $_SERVER['REQUEST_URI'] = '/pages/12';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['SCRIPT_NAME'] = 'index.php';

        $_GET = [];
        $_POST = [];
        $_FILES = [];

        $router = new \Calf\HTTP\Router();
        
        $home = new \Calf\HTTP\Route('/pages[/{page:\d+}]', function($req, $res, array $params = []) {
            if (!isset($params['page'])) {
                return 'All pages are here';
            }

            return $res->set('You are in page ' . $params['page']);
        });
        
        $router->add($home);

        $response = $router->dispatch();

        $this->assertEquals($response->get(true), 'You are in page 12');

        $_SERVER = [];
        $_SERVER['REQUEST_URI'] = '/pages';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['SCRIPT_NAME'] = 'index.php';

        $_GET = [];
        $_POST = [];
        $_FILES = [];

        $response = $router->dispatch();

        $this->assertEquals($response->get(true), 'All pages are here');
    }
}
