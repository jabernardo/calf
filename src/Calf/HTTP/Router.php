<?php

namespace Calf\HTTP;

/**
 * Router class
 * 
 * @version 1.0
 * @author  John Aldrich Bernardo <4ldrich@protonmail.com>
 * 
 */
class Router
{
    /**
     * @var     array   Routes  registered
     * 
     */
    private $_routes = [
        'GET'       => [],
        'POST'      => [],
        'PUT'       => [],
        'DELETE'    => []
    ];
    
    /**
     * @var     \Calf\HTTP\Request    Request object
     * 
     */
    private $_request = null;
    
    /**
     * @var     \Calf\HTTP\Response   Response object
     * 
     */
    private $_response = null;
    
    /**
     * @var     array   All router level middlewares
     * 
     */
    private $_middlewares = [];

    /**
     * Class construct
     * 
     * @access  public
     * @return  void
     * 
     */
    function __construct() {
        // Create a new HTTP Request Object
        $this->_request = new \Calf\HTTP\Request();
        
        // and new HTTP Response Object
        $this->_response = new \Calf\HTTP\Response();
    }
    
    /**
     * Add new route
     * 
     * @param   \Calf\HTTP\Route  $route  Route
     * 
     * @return  void
     * 
     * @throws  \Calf\Exception\Router\DuplicatedPath Duplicated path
     * 
     */
    public function add(\Calf\HTTP\Route $route) {
        // For php 5.4 compatibility
        $path = $route->getPath();
        
        foreach ($route->getMethod() as $route_method) {
            // Check for duplicated routes
            if (isset($this->_routes[$route_method][$path])) {
                throw new \Calf\Exception\Router\DuplicatedPath('Route with path "' . $path . '" already exists.');
            }
            
            // We'll be using the path as key for the new route
            $this->_routes[$route_method][$path] = $route;
        }
    }

    /**
     * Add a new Route Group
     * 
     * @access  public
     * @param   \Calf\HTTP\RouteGroup $group  New Route Group
     * @return  void
     * 
     */
    public function addGroup(\Calf\HTTP\RouteGroup $group) {
        $routes = $group->getRoutes();

        foreach ($routes as $route) {
            $this->add($route);
        }
    }
    
    /**
     * Add a new router-level middleware
     * 
     * @access  public
     * @param   callable    $middleware Middleware to be added
     * @return  void
     * 
     */
    public function addMiddleware(callable $middleware) {
        $this->_middlewares[] = $middleware;
    }

    /**
     * Remove Route
     * 
     * @access  public
     * @param   mixed   $path   Route Path
     * @param   string  $method Request method
     * @return  bool
     * 
     * @throws  \Calf\Exception\InvalidArgument   Invalid argument exception
     * 
     */
    public function remove($path, $methods = ['GET', 'POST', 'PUT', 'DELETE']) {
        $route = null;
        $deleted = false;
        
        if (is_object($path) && $path instanceof \Calf\HTTP\Route) {
            $route = $path->getPath();
            $methods = $path->getMethod();
        } else if (is_string($path)) {
            $route = trim($path, '/');
        } else {
            throw new \Calf\Exception\InvalidArgument('Invalid route');
        }
        
        foreach ($methods as $method) {
            if (is_string($route) && isset($this->_routes[$method][$route])) {
                unset($this->_routes[$method][$route]);
                $deleted = true;
            } 
        }

        return $deleted;
    }
    
    /**
     * Check if Route Path exists
     * 
     * @access  public
     * @param   mixed  $path    Route path
     * @param   string $method  Reques
     * @return  bool
     * 
     * @throws  \Calf\Exception\InvalidArgument   Invalid argument exception
     * 
     */
    public function exists($path, array $methods = ['GET', 'POST', 'PUT', 'DELETE']) {
        $route = null;

        if (is_object($path) && $path instanceof \Calf\HTTP\Route) {
            $route = $path->getPath();
            $methods = $path->getMethod();
        } else if (is_string($path)) {
            $route = trim($path, '/');
        } else {
            throw new \Calf\Exception\InvalidArgument('Invalid route');
        }
        
        foreach ($methods as $method) {
            if (is_string($route) && isset($this->_routes[$method][$route])) {
                return true;
            }
        }

        return false;
    }
    
    /**
     * Dispatch Router
     * 
     * @access  public
     * @param   \Calf\HTTP\Request  $request    Request override
     * @param   \Calf\HTTP\Response $response   Response override
     * @return void
     * 
     */
    public function dispatch(\Calf\HTTP\Request $request = null, \Calf\HTTP\Response $response = null) {
        // Allow overrides for application
        // This is to enable application level middlewares
        if ($request) {
            $this->_request = $request;
        }

        if ($response) {
            $this->_response = $response;
        }

        // Request method
        $request_method = $this->_request->method();

        // Set `active` route to be an instance of Page Not Found
        // This will be our default page
        $active = new \Calf\HTTP\Route\PageNotFound($this->_request, $this->_response);

        // Overriding default 404 Page
        if (isset($this->_routes[$request_method]['404'])) {
            $active = $this->_routes[$request_method]['404'];
        }
        
        // Get URL path
        $url = parse_url($this->_request->url(), PHP_URL_PATH);
        
        // Route parser
        $parser = new \Calf\HTTP\RouteParser($url);
        
        foreach ($this->_routes[$request_method] as $route) {
            // Test Route pattern
            if ($parser->test($route->getPath())) {
                // Set route that matches to be the active one
                $active = $this->_routes[$request_method][$parser->getPattern()];
                
                // Set parameters for route
                $active->setParameters($parser->getMatches());
                
                // Set route information as request attribute
                $this->_request->withAttribute('route', $active);

                // Then break this loop...
                break;
            }
        }

        // Make sure that active route is an instance of \Calf\HTTP\Route
        if (!($active instanceof \Calf\HTTP\Route)) {
            throw new \Calf\Exception\Runtime('Didn\'t fetch a valid route.');
        }

        // Register the router-level middlewares
        foreach ($this->_middlewares as $middleware) {
            $active->addMiddleware($middleware);
        }

        // Execute Route
        $this->_response = $active->process($this->_request, $this->_response);
        
        return $this->_response;
    }
}
