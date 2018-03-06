<?php

namespace Calf;

/**
 * Deferred Callable
 * 
 * @version     1.1
 * @author      John Aldrich Bernardo <4ldrich@protonmail.com>
 * @package     Calf
 * 
 */
class DeferredCallable
{
    /**
     * Callable instance
     *
     * @access  private
     * @var     \Callable
     */
    private $_callable;
    
    /**
     * Callable DI
     *
     * @access  private
     * @var     object  \Calf\Saddle
     */
    private $_container;
    
    /**
     * Class construct
     *
     * @param   \Callable   $callable   Callback for route
     * @param   mixed       $container  Callback DI
     * @throws  \Calf\Exception\InvalidArgument     Invalid callable
     */
    public function __construct($callable, $container = null)
    {
        if (!is_object($callable) && !($callable instanceof \Closure)) {
            throw new \Calf\Exception\InvalidArgument('Invalid callable.');
        }

        $this->_callable = $callable;
        $this->_container = $container;
    }

    /**
     * Magic function __invoke
     *
     * @access  public
     * @return  \Callable
     */
    public function __invoke()
    {
        $callable = $this->_callable;
        $callable = $callable->bindTo($this->_container);

        $args = func_get_args();
        
        return call_user_func_array($callable, $args);
    }
}
