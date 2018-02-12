<?php

namespace Calf;

/**
 * Calf Application
 * 
 * @version     1.0
 * @author      John Aldrich Bernardo <4ldrich@protonmail.com>
 * 
 */
class App
{
    use \Calf\HTTP\Traits\MiddlewareLayer;

    /**
     * Calf Router Instance
     * 
     * @access  private
     * @var     \Calf\HTTP\Router
     * 
     */
    private $_router;

    /**
     * @access
     *
     * @var     private
     * @var     \Calf\Saddle
     */
    private $_container;

    /**
     * Class construct
     *
     * @access  public
     * @param   mixed   $container
     */
    function __construct(\Calf\Saddle $container = null) {
        $this->_router = new \Calf\HTTP\Router();

        if (is_null($container)) {
            $container  = new \Calf\Saddle();
        }

        $this->_container = $container;
    }

    /**
     * Add application route
     *
     * @access  public
     * @param   \Calf\HTTP\Route    $route  Calf HTTP Route
     * @return  void
     */
    public function add(\Calf\HTTP\Route $route) {
        $route->setCallable(new \Calf\DeferredCallable($route->getCallable(), $this->_container));
        
        $this->_router->add($route);
    }

    /**
     * Add Route Group to application
     *
     * @access  public
     * @param \Calf\HTTP\RouteGroup $group
     * @return void
     */
    public function addGroup(\Calf\HTTP\RouteGroup $group) {
        foreach ($group->getRoutes() as $route) {
            $route->setCallable(new \Calf\DeferredCallable($route->getCallable(), $this->_container));
        }

        $this->_router->addGroup($group);
    }

    /**
     * Get application container instance
     *
     * @access  public
     * @return  \Calf\Saddle
     */
    public function getContainer() {
        return $this->_container;
    }
    
    /**
     * Get Router
     *
     * @access  public
     * @return  \Calf\HTTP\Router
     */
    public function getRouter() {
        return $this->_router;
    }

    /**
     * Run Application
     *
     * @access  public
     * @param   boolean     $silent     Do not render response
     * @return  \Calf\HTTP\Response
     */
    public function run($silent = false) {
        $request = new \Calf\HTTP\Request();
        $response = new \Calf\HTTP\Response();
        
        $response = $this->process($request, $response);
        
        if (!$silent) {
            $response->render();
        }

        return $response;
    }

    /**
     * Kernel function for middleware stack
     *
     * @param   \Calf\HTTP\Request  $request    Calf HTTP Request
     * @param   \Calf\HTTP\Response $response   Calf HTTP Response
     * @return  \Calf\HTTP\Response 
     */
    function __invoke(\Calf\HTTP\Request $request, \Calf\HTTP\Response $response) {
        $response = $this->_router->dispatch($request, $response);

        return $response;
    }
}
