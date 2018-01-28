<?php

namespace Calf\HTTP\Traits;

/**
 * Middleware Layer Traits
 * 
 * This should be used only on \Calf\HTTP\Route only.
 * And was based on Slim/MiddlewareAwareTrait.php
 * 
 * @author  John Aldrich Bernardo <4ldrich@protonmail.com>
 * 
 */
trait MiddlewareLayer
{
    /**
     * @var callable    First callable middleware
     * 
     */
    protected $_top;
    
    /**
     * @var bool    Is running middlewares?
     * 
     */
    protected $_busy = false;
    
    /**
     * Add a new middleware to the layer
     * 
     * @access  public
     * @param   callable    $callback   Middleware layer
     * @return  static
     * 
     * @throws  \Calf\Exception\Runtime   Throws exception when layer is dequeuing
     * 
     */
    public function addMiddleware(callable $callback) {
        if ($this->_busy) {
            // Make sure it's not busy before adding something.
            throw new \Calf\Exception\Runtime('Can\'t add new middleware while dequeue in progress.');
        }
        
        if (is_null($this->_top)) {
            // If there was nothing yet added make the current route default
            $kernel = $this;
            $this->_top = $kernel;
        }
        
        // Make a new address for the callback
        $next = $this->_top;
        
        // The update the top function
        $this->_top = function(\Calf\HTTP\Request $req, \Calf\HTTP\Response $res) use ($callback, $next) {
            // Pass the last function
            $res = call_user_func($callback, $req, $res, $next);
            
            // Return the new result
            return $res;
        };
    }
    
    /**
     * Execute Middleware Layer
     * 
     * @access  public
     * @param   \Calf\HTTP\Request    $req    HTTP Request
     * @param   \Calf\HTTP\Response   $res    HTTP Response
     * @return  \Calf\HTTP\Response
     * 
     */
    public function process(\Calf\HTTP\Request $req, \Calf\HTTP\Response $res) {
        if (is_null($this->_top)) {
            // If there was nothing yet added make the current route default
            $kernel = $this;
            $this->_top = $kernel;
        }
        
        // Create a new address for the top callable
        $start = $this->_top;
        
        // Start dequeue
        $this->_busy = true;
        $new_response = $start($req, $res);
        $this->_busy = false;
        
        // Just want to make sure that processed response from middlewares
        // are instance of \Calf\HTTP\Response
        if (!($new_response instanceof \Calf\HTTP\Response)) {
            $res->set($new_response);
        }

        return $res;
    }
}
