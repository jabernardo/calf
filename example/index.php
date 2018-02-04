<?php

// Include autoload
require('../src/autoload.php');

use \Calf\HTTP\Router as Router;
use \Calf\HTTP\Route as Route;
use \Calf\HTTP\RouteGroup as RouteGroup;
use \Calf\HTTP\Request as Request;
use \Calf\HTTP\Response as Response;

// Create a new instance of Calf Router
$router = new Router();

// Then create a new Route
$home = new Route('/', function(Request $req, Response $res, array $args) {
    return $res->write('Hello World');
});

// For some reason, you might need to use middlewares...
$home->addMiddleware(function(Request $req, Response $res, callable $next) {
    // Before route is called,
    // Some codes might go here...

    // Call the next middleware
    $next($req, $res);

    // After route is executed.
    $res->write('!');

    return $res;
});

// Finally after creating a route you'll be
// needing to register it on our router
$router->add($home);

//  Managing a large group of routes with RouteGroup
$productsGroup = new RouteGroup('products');

$productsGroup->add(
    new Route('get', function(Request $req, Response $res, array $args) {
        return $res->write(['Pen', 'Pineapple', 'Apple']);
    })
);

$productsGroup->add(
    new Route('get/fruits', function(Request $req, Response $res, array $args) {
        return $res->write(['Pineapple', 'Apple']);
    })
);

// Add group to router
$router->addGroup($productsGroup);

// After all, you need some high-level control on all routes using middleware
// here are some samples of middleware in router-level
// NOTE: Enabling codes below will affect code samples for 
//      -   /products/get
//      -   /products/get/fruits

// $router->addMiddleware(function(Request $req, Response $res, callable $next) {
//     $res->set($res->get() . 'Message: ');
//     $next($req, $res);
//     return $res;
// });

// $router->addMiddleware(function(Request $req, Response $res, callable $next) {
//     $next($req, $res);
//     $res->set($res->get() . '!!');
//     return $res;
// });

// Then let the Router do its work.
$router->dispatch();
