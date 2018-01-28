<?php

namespace Calf\HTTP;

/**
 * Request Class
 * 
 * @reference   https://github.com/jabernardo/lollipop-php/blob/master/Library/HTTP/Request.php
 * @version     1.0
 * @author      John Aldrich Bernardo <4ldrich@protonmail.com>
 * 
 */
class Request
{
    /**
     * @access  private
     * @var     array   Centralized session requests
     * 
     */
    private $_all_requests = [];
    
    /**
     * Check for request(s)
     *
     * @param   mixed   $requests   Request names
     *
     * @return bool
     * 
     */
    function is($requests) {
        $is = true;
        
        // Also support PUT and DELETE
        parse_str(file_get_contents("php://input"), $_php_request);
        // Merge with POST and GET
        $this->_all_requests = array_merge($this->_all_requests, array_merge($_REQUEST, $_php_request));
        
        if (is_array($requests)) {
            $returns = [];
            
            foreach ($requests as $request) {
                array_push($returns, isset($this->_all_requests[$request]));
            }
            
            foreach ($returns as $return) {
                if ($return == false) {
                    $is = false;
                }
            }
        } else {
            $is = isset($this->_all_requests[$requests]);
        }
        
        return $is;
    }
    
    /**
     * Check if request method is in use
     * 
     * @access  public
     * @param   string  $method     Request method
     * @return  bool
     * 
     */
    function isMethod($method) {
        return !strcasecmp($method, $_SERVER['REQUEST_METHOD']);
    }
    
    /**
     * Gets values of request(s)
     *
     * @param   array   $requests   Request names
     *
     * @return  array
     * 
     */
    function get($requests = null) {
        $var = [];
        
        // Also support PUT and DELETE
        parse_str(file_get_contents("php://input"), $_php_request);
        // Merge with POST and GET
        $this->_all_requests = array_merge($this->_all_requests, array_merge($_REQUEST, $_php_request));
        
        if (is_array($requests)) {
            foreach ($requests as $request) {
                $var[$request] = isset($this->_all_requests[$request]) ? $this->_all_requests[$request] : null;
            }
        } else if (is_null($requests)) {
            $var = $this->_all_requests;
        } else {
            $var = (isset($this->_all_requests[$requests])) ? $this->_all_requests[$requests] : null;
        }
        
        return $var;
    }
    
    /**
     * Get request method
     * 
     * @access  public
     * @return  string
     * 
     */
    public function getMethod() {
        return $_SERVER['REQUEST_METHOD'];
    }
    
    /**
     * Get request URL
     * 
     * @access  public
     * @return  string
     * 
     */
    public function getURL() {
        return $_SERVER['REQUEST_URI'];
    }
    
    /**
     * Get request headers
     * 
     * @access  public
     * @return  array
     * 
     */
    public function getHeaders() {
        return getallheaders();
    }
    
    /**
     * Get request header value
     * 
     * @access  public
     * @param   string  $header     Request header
     * @return  mixed   `null` if header is not set
     * 
     */
    public function getHeader($header) {
        foreach(getallheaders() as $k => $v) {
            if (!strcasecmp($k, $header)) {
                return $v;
            }
        }
        
        return null;
    }
}
