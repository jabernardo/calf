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
    private $_routes = [];
    
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
        
        // Check for duplicated routes
        if (isset($this->_routes[$path])) {
            throw new \Calf\Exception\Router\DuplicatedPath('"' . $path . '" already exists.');
        }
        
        // We'll be using the path as key for the new route
        $this->_routes[$path] = $route;
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
     * @param   mixed  $path   Route Path
     * @return  bool
     * 
     * @throws  \Calf\Exception\InvalidArgument   Invalid argument exception
     * 
     */
    public function remove($path) {
        $route = null;
        
        if (is_object($path) && $path instanceof \Calf\HTTP\Route) {
            $route = $path->getPath();
        } else if (is_string($path)) {
            $route = trim($path, '/');
        } else {
            throw new \Calf\Exception\InvalidArgument('Invalid route');
        }
        
        if (is_string($route) && isset($this->_routes[$route])) {
            unset($this->_routes[$route]);
            return true;
        }
    
        return false;
    }
    
    /**
     * Check if Route Path exists
     * 
     * @access  public
     * @param   mixed  $path   Route path
     * @return  bool
     * 
     * @throws  \Calf\Exception\InvalidArgument   Invalid argument exception
     * 
     */
    public function exists($path) {
        $route = null;
        
        if (is_object($path) && $path instanceof \Calf\HTTP\Route) {
            $route = $path->getPath();
        } else if (is_string($path)) {
            $route = trim($path, '/');
        } else {
            throw new \Calf\Exception\InvalidArgument('Invalid route');
        }
        
        return is_string($route) && isset($this->_routes[$route]);
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

        // Set `active` route to be an instance of Page Not Found
        // This will be our default page
        $active = new \Calf\HTTP\Route\PageNotFound($this->_request, $this->_response);
        
        // Overriding default 404 Page
        if (isset($this->_routes['404'])) {
            $active = $this->_routes['404'];
        }
        
        // Get URL path
        $url = parse_url($this->_request->url(), PHP_URL_PATH);
        
        // Route parser
        $parser = new \Calf\HTTP\RouteParser($url);
        
        foreach (array_keys($this->_routes) as $route) {
            // Check if request method matches
            $request_method = $this->_routes[$route]->getMethod();
            
            if (is_array($request_method)) {
                // Make sure all request methods are in uppercase
                // Most of servers are configured with uppercase
                $request_method = array_map('strtoupper', $request_method);
            }
            
            // Check if current request method matches our applications
            // preferred request method
            // If Route's preferred HTTP Request is empty, it means it doesn't require method checking
            $rest_test = is_array($request_method) && 
                (in_array($this->_request->method(), $request_method) || count($request_method) === 0);

            // Test Route pattern
            if ($rest_test && $parser->test($route)) {
                // Set route that matches to be the active one
                $active = $this->_routes[$parser->getPattern()];
                
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
