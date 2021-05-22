<?php

namespace Calf\HTTP;

/**
 * Route Group class
 * 
 * @version 1.0
 * @author  John Aldrich Bernardo <4ldrich@protonmail.com>
 * 
 */
class RouteGroup
{
    /**
     * @var     string  Group Name
     * @access  private
     * 
     */
    private $_name;

    /**
     * @var     array   Route Group
     * @access  private
     * 
     */
    private $_group = [];

    /**
     * @var     array   Middlewares
     * @access  private
     * 
     */
    private $_middlewares = [];

    /**
     * Class construct
     * 
     * @access  public
     * @param   string  $name   Group Name
     * @return  void
     * 
     */
    function __construct($name) {
        if (!is_string($name)) {
            throw new \Calf\Exception\InvalidArgument('Invalid route group name.');
        }

        $this->_name = trim($name, '/');
    }

    /**
     * Add a new route to the group
     * 
     * @access  public
     * @param   \Calf\HTTP\Route  $route  New Route
     * @return  void
     * 
     */
    public function add(\Calf\HTTP\Route $route) {
        $old_path = trim($route->getPath(), '/');
        $route->setPath($this->_name . '/' . $old_path);

        foreach ($this->_middlewares as $middleware) {
            $route->addMiddleware($middleware);
        }

        $this->_group[] = $route;
    }

    /**
     * Allow middleware
     * 
     * @access  public
     * @param   \Calf\HTTP\Interfaces\Middleware @middleware    New Middleware entry
     * @return  \Calf\HTTP\RouteGroup
     */
    public function addMiddleware(\Calf\HTTP\Interfaces\Middleware $middleware) {
        $this->_middlewares[] = $middleware;

        return $this;
    }

    /**
     * Get routes from this group
     * 
     * @access  public
     * @return  array
     * 
     */
    public function getRoutes() {
        return $this->_group;
    }
}
