<?php

require_once('../src/autoload.php');

// Create a new Saddle (Dependency Injector)
$container = new \Calf\Saddle();

// Test message for our Saddle
$container->message = 'Hello World';

// More examples for our Saddle
$container->products = [
    'supplies'  => ['Pen'],
    'fruits'    => ['Apple', 'Pineapple']
];

$container->getProducts = function(\Calf\Saddle $c) {
    return $c->products;
};

// Let's instantiate our application
$app = new \Calf\App($container);

// Our homepage
$home = (new \Calf\HTTP\Route('/', function(\Calf\HTTP\Request $req, \Calf\HTTP\Response $res, array $args = []) {
    // $this->message is the data from our container
    $res->write($this->message);
    $res->write(' to ' . $req->attribute('route')->getName());

    // Return response
    return $res;
}, ['GET', 'POST']))->setName('Home');

// Adding middleware to route
$home->addMiddleware(function(\Calf\HTTP\Request $req, \Calf\HTTP\Response $res, callable $next) {
    $res->write('[BEGIN]');
    $next($req, $res);
    $res->write('[END]');

    return $res;
});

// Make sure to register our routes
$app->add($home);

// Optional Parameters
$articles = new \Calf\HTTP\Route('/articles[/{article}]', function(\Calf\HTTP\Request $req, \Calf\HTTP\Response $res, array $params = []) {
    if (isset($params['article']) && $params['article']) {
        return 'Are you looking for articles related to `' . $params['article'] . '`?';
    }

    return 'Articles will be displayed here.';
});

$app->add($articles);

// Optionals and optionals
$articles_pages = new \Calf\HTTP\Route('/articles[/{article}]/pages[/{page}]', function(\Calf\HTTP\Request $req, \Calf\HTTP\Response $res, array $params = []) {
    $article = $params['article'] ?: '';
    $page = $params['page'] ?: '';

    if ($article && $page) {
        return "You are looking at $article at page $page.";
    } else if ($article) {
        return "What page are you now on `$article`?";
    }

    return 'Forgot your bookmark?';
});

$app->add($articles_pages);

// Products Service Route Group
$products = new \Calf\HTTP\RouteGroup('products');

$products->add(
    // Test using url:
    // {host}/products/get
    new \Calf\HTTP\Route('/get', function(\Calf\HTTP\Request $req, \Calf\HTTP\Response $res, array $args = []) {
        $res->write($this->getProducts);

        return $res;
    }, 'GET')
);

$products->add(
    // Test using url:
    // {host}/products/get/{fruits|supplies|...}
    new \Calf\HTTP\Route('/get/{category:\w+}', function(\Calf\HTTP\Request $req, \Calf\HTTP\Response $res, array $args = []) {
        $prods = [];
        $category = $args['category'];

        if (!isset($this->getProducts[$category])) {
            $res->write('No products found for keyword: ' .$category);

            return $res;
        }
        
        $prods = $this->getProducts[$category];
        $res->write('Available: ');
        $res->write(implode(', ', $prods));

        return $res;
    }, 'GET')
);

// Register route group to application
$app->addGroup($products);

// App and Running!
$app->run();
