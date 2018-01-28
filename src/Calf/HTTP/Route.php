<?php

namespace Calf\HTTP;

/**
 * Route Class
 * 
 * @version 1.0
 * @author  John Aldrich Bernardo <4ldrich@protonmail.com>
 * 
 */
class Route
{
    use \Calf\HTTP\Traits\MiddlewareLayer;
    
    /**
     * @var     string  Route name
     * @access  private
     * 
     */
    private $_name = '';
    
    /**
     * @var     string  URL Path
     * @access  private
     * 
     */
    private $_path;
    
    /**
     * @var     Closure Route Callback
     * @access  private
     * 
     */
    private $_callback;
    
    /**
     * @var     mixed   HTTP Request Methods
     * @access  private
     * @example
     *  ''
     *      - Accept all request methods
     * 
     *  ['GET', 'POST']
     *      - Specified targeted request methods
     * 
     */
    private $_method = '';
    
    /**
     * @var     array   Parameters
     * @access  private
     * 
     */
    private $_params = '';
    
    /**
     * Class construct
     * 
     * @access  public
     * @param   string      $path       URL Path
     * @param   Callable    $callback   Route callback function
     * @param   mixed       $method     HTTP Request methods
     * @return  void
     * 
     * @return  \Calf\Exception\InvalidArgument   If callback is not callable
     * 
     */
    function __construct($path, callable $callback, $method = '') {
        if (!is_callable($callback)) {
            throw new \Calf\Exception\InvalidArgument('Invalid callback.');
        }
        
        $this->_path = $path;
        $this->_callback = $callback;
        $this->_method = $method;
    }
    
    /**
     * Get Route Name
     * 
     * @access  public
     * @return  string
     * 
     */
    public function getName() {
        return $this->_name;
    }
    
    /**
     * Get Path
     * 
     * @access  public
     * @return  string
     * 
     */
    public function getPath() {
        return $this->_path;
    }
    
    /**
     * Get Method
     * 
     * @access  public
     * @return  mixed
     * 
     */
    public function getMethod() {
        return $this->_method;
    }
    
    /**
     * Get Callable
     * 
     * @access  public
     * @return  mixed
     * 
     */
    public function getCallable() {
        return $this->_callback;
    }

    /**
     * Get parameters
     * 
     * @access  public
     * @return  string
     * 
     */
    public function getParameters() {
        return  $this->_params;
    }

    /**
     * Set route name
     * 
     * @access  public
     * @return  \Calf\HTTP\Route
     * 
     */
    public function setName($name) {
        $this->_name = $name;
        
        return $this;
    }

    /**
     * Override route path
     * 
     * @access  public
     * @return  \Calf\HTTP\Route
     * 
     */
    public function setPath($path) {
        $this->_path = $path;
        
        return $this;
    }
    
    /**
     * Override callable
     * 
     * @param   \Callable   $callback
     * @return  \Calf\HTTP\Route
     * 
     */
    public function setCallable(callable $callback) {
        $this->_method = $callback;
        
        return $this;
    }

    /**
     * Set HTTP Request Methods
     * 
     * @access  public
     * @param   mixed   $method HTTP Request methods
     * @return  \Calf\HTTP\Route
     * 
     */
    public function setMethod($method) {
        $this->_method = $method;
        
        return $this;
    }
    
    /**
     * Set parameters for route
     * 
     * @access  public
     * @param   array   $args   Arguments
     * @return  \Calf\HTTP\Route
     * 
     */
    public function setParameters(array $args) {
        $this->_params = $args;
        
        return $this;
    }
    
    /**
     * Execute Route
     * 
     * @access  public
     * @param   \Calf\HTTP\Request    $request    HTTP Request Object
     * @param   \Calf\HTTP\Response   $response   HTTP Response Object
     * @return  void
     * 
     */
    function __invoke(\Calf\HTTP\Request $request, \Calf\HTTP\Response $response) {
        $response = call_user_func($this->_callback, $request, $response, $this->_params);
        
        if (!($response instanceof \Calf\HTTP\Response)) {
            $response = new \Calf\HTTP\Response($response);
        }
        
        return $response;
    }
}
