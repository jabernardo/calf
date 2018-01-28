<?php

namespace Calf\HTTP\Interfaces;

/**
 * Middleware Interface
 * 
 * @version 1.0
 * @author  John Aldrich Bernardo <4ldrich@protonmail.com>
 * 
 */
interface Middleware {
    
    /**
     * All middleware will be required to be a callable
     * so __invoke function is a must so parameters to be instance of Calf Router
     * classes
     * 
     * @access  public
     * @param   \Calf\HTTP\Request    $request    HTTP Request Object
     * @param   \Calf\HTTP\Response   $response   HTTP Response Object
     * @return  \Calf\HTTP\Response   Response Object
     * 
     */
    public function __invoke(\Calf\HTTP\Request $request, \Calf\HTTP\Response $response, callable $next);
    
}
