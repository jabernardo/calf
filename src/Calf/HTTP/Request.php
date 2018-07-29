<?php

namespace Calf\HTTP;

/**
 * Request Class
 * 
 * @link        https://github.com/jabernardo/lollipop-php/blob/master/Library/HTTP/Request.php
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
    private $_requests = [];
    
    /**
     * @access  private
     * @var     array   Queries
     * 
     */
    private $_queries = [];

    /**
     * @access  private
     * @var     array   Request files
     * 
     */
    private $_files = [];

    /**
     * @access  private
     * @var     array   Request headers
     */
    private $_headers = [];
    
    /**
     * @access  private
     * @var     array   Request cookies
     * 
     */
    private $_cookies = [];

    /**
     * @access  private
     * @var     string  Request method
     * 
     */
    private $_method = 'GET';
    
    /**
     * Class construct
     * 
     * @access  public
     * @return  void
     * 
     */
    public function __construct() {
        // Also support PUT and DELETE
        $_php_request = [];
        parse_str(file_get_contents("php://input"), $_php_request);

        // Merge with POST and GET
        $this->_requests = array_merge($this->_requests, array_merge($_POST, $_php_request));
        
        // Get url queries
        $this->_queries = $_GET;

        // File uploads
        $this->_files = $_FILES;

        // Request headers
        $this->_headers = \getallheaders();

        // Request cookies
        $this->_cookies = $_COOKIE;
        
        // Request method
        $this->_method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
    }

    /**
     * Get input data
     * 
     * @access  public
     * @param   string  $name   Input name
     * @param   string  $val    Default value if input doesn't exists
     * @return  mixed
     * 
     */
    public function input($name, $val = null) {
        return isset($this->_requests[$name]) ?
                    $this->_requests[$name] :
                    $val;
    }
    
    /**
     * Alias only
     * 
     * @access  public
     * @param   array   $names   Input names
     * @param   mixed   $default    Default value (null)
     * @return  array
     * 
     */
    public function inputs(array $names, $val = null) {
        return $this->only($names, $val);
    }
    
    /**
     * Get query string from url
     * 
     * @access  public
     * @param   string  $name   Input name
     * @param   string  $val    Default value if query doesn't exists
     * @return  mixed
     * 
     */
    public function query($name, $val = null) {
        return isset($this->_queries[$name]) ?
                    $this->_queries[$name] :
                    $val;
    }
    
    /**
     * Getting querie from url parameters
     * 
     * @access  public
     * @param   array   $name       Query names
     * @param   mixed   $default    Default value (null)
     * @return  array
     * 
     */
    public function queries(array $names, $default = null) {
        $var = [];
        
        foreach ($names as $in) {
            $var[$in] = isset($this->_queries[$in]) ? 
                $this->_queries[$in] :
                $default;
        }
        
        return $var;
    }
    
    /**
     * Getting segments of inputs
     * 
     * @access  public
     * @param   array   $names      Input names
     * @param   mixed   $default    Default value (null)
     * @return  array
     * 
     */
    public function only(array $names, $default = null) {
        $var = [];
        
        foreach ($names as $in) {
            $var[$in] = isset($this->_requests[$in]) ? 
                $this->_requests[$in] :
                $default;
        }
        
        return $var;
    }
    
    /**
     * Get data input except some
     * 
     * @access  public
     * @param   array   $name   Input names
     * @return  array
     * 
     */
    public function except(array $name) {
        $var = [];
        
        foreach ($this->_requests as $k => $v) {
            if (!in_array($k, $name)) {
                $var[$k] = $v;
            }
        }
        
        return $var;
    }
    
    /**
     * Check if input is received
     * 
     * @access  public
     * @param   string  $name   Input name
     * @return  bool
     * 
     */
    public function has($name) {
        return isset($this->_requests[$name]);
    }
    
    /**
     * Alias has
     * 
     * @access  public
     * @param   string  $name   Input name
     * @return  bool
     * 
     */
    public function hasInput($name) {
        return $this->has($name);
    }
    
    /**
     * Check if inputs are received
     * 
     * @access  public
     * @param   array   $names  Input names
     * @return  bool
     * 
     */
    public function hasInputs(array $names) {
        foreach ($names as $name) {
            if (!$this->has($name))
                return false;
        }
        
        return true;
    }
    
    /**
     * Check if query is received
     * 
     * @access  public
     * @param   string  $name   Input name
     * @return  bool
     * 
     */
    public function hasQuery($name) {
        return isset($this->_queries[$name]);
    }
    
    /**
     * Check if queries exists
     * 
     * @access  public
     * @param   array  $names   Query names
     * @return  bool
     * 
     */
    public function hasQueries(array $names) {
        foreach ($names as $name) {
            if (!$this->hasQuery($name))
                return false;
        }
        
        return true;
    }

    /**
     * File upload
     *
     * @param   string  $name
     * @return  mixed
     */
    public function file($name) {
        return isset($this->_files[$name]) ?
                new \Calf\HTTP\FileUpload($name) :
                null;
    }

    /**
     * File uploads
     * 
     * @access  public
     * @return  array
     * 
     */
    public function files(array $files) {
        $uploads = [];

        foreach ($files as $file) {
            array_push($uploads, $this->file($file));
        }

        return $uploads;
    }

    /**
     * Check if request method is in use
     * 
     * @access  public
     * @param   string  $method     Request method
     * @return  bool
     * 
     */
    public function isMethod($method) {
        return !strcasecmp($method, $this->_method);
    }

    /**
     * Get request method
     * 
     * @access  public
     * @return  string
     * 
     */
    public function method() {
        return $this->_method;
    }
    
    /**
     * Get request URL
     * 
     * @access  public
     * @param   int     $component  URL parse_url component
     * @return  string
     * 
     */
    public function url($component = -1) {
        $server_name = $_SERVER['SERVER_NAME']; 
        $server_port = $_SERVER['SERVER_PORT'];
        $server      = $server_port == '8080' || $server_port == '80' || $server_port == '443' ? $server_name : "$server_name:$server_port";
        $protocol    = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ||
            (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on'))
                    ? 'https://' : 'http://';

        $full_url = $protocol . $server . $_SERVER['REQUEST_URI'];
                
        if ($component > -1) {
            return parse_url($full_url, $component);
        }

        return $full_url;
    }
    
    /**
     * Get request header value
     * 
     * @access  public
     * @param   string  $header     Request header
     * @return  mixed   `null` if header is not set
     * 
     */
    public function header($header) {
        foreach($this->_headers as $k => $v) {
            if (!strcasecmp($k, $header)) {
                return $v;
            }
        }
        
        return null;
    }

    /**
     * Get request headers
     * 
     * @access  public
     * @return  array
     * 
     */
    public function headers() {
        return $this->_headers;
    }

    /**
     * Get request cookie value
     * 
     * @access  public
     * @param   string  $name   Request cookie
     * @return  mixed   `null` if cookie is not set
     * 
     */
    public function cookie($name) {
        return isset($this->_cookies[$name]) ?
            $this->_cookies[$name] :
            null;
    }

    /**
     * Get request cookies
     * 
     * @access  public
     * @return  array
     * 
     */
    public function cookies() {
        return $this->_cookies;
    }
}
