<?php

namespace Calf\HTTP;

/**
 * Response Class
 * 
 * @link        https://github.com/jabernardo/lollipop-php/blob/master/Library/HTTP/Response.php
 * @version     1.2
 * @author      John Aldrich Bernardo <4ldrich@protonmail.com>
 * 
 */
class Response
{
    /**
     * @var     array   HTTP Headers
     * @access  private
     * 
     */
    private $_headers = [];
    
    /**
     * @var     array   Response cookies
     * @access  private
     * 
     */
    private $_cookies = [];
    
    /**
     * @var     string  Response data
     * @access  private
     * 
     */
    private $_data = null;
    
    /**
     * @var     bool    Compress output using gzip
     * @access  private
     * 
     */
    private $_compress = false;
    
    /**
     * Class construct
     * 
     * @access  public
     * @return  object
     * 
     */
    function __construct($data = null) {
        $this->_data = $data;
        
        return $this;
    }

    /**
     * Return string value for data
     *
     * @access  private
     * @param   object  $data   Data to convert
     * @return  string
     *
     */
    private function _format($data) {
        $output_callback_function = '';
        
        // If data is in array format then set content-type
        // to application/json
        if (is_array($data) || is_object($data)) {
            $this->setHeader('Content-type: application/json');
            // Convert to json
            $output_callback_function = json_encode($data);
        } else {
            // Default
            $output_callback_function = $data;
        }
        
        $output = $output_callback_function;
        
        // GZIP output compression
        if ($this->_compress) {
            // Set Content coding a gzip
            $this->setHeader('Content-Encoding: gzip');
            
            // Set headers for gzip
            $output = "\x1f\x8b\x08\x00\x00\x00\x00\x00";
            $output .= gzcompress($output_callback_function);
        }
        
        return $output;
    }
    
    /**
     * Set Response data
     * 
     * @access  public
     * @param   mixed   $data   New response data
     * @return  object
     * 
     */
    public function set($data) {
        $this->_data = $data;
        
        return $this;
    }

    /**
     * Write response data
     * 
     * @access  public
     * @param   mixed   $data   Response data
     * @return  object
     * 
     * @throws  \Calf\Exception\InvalidArgument Data type mismatched for response data
     * 
     */
    public function write($data) {
        if ((is_null($this->_data) || is_string($this->_data)) && is_string($data)) {
            if (is_null($this->_data)) {
                $this->_data = '';
            }

            $this->_data .= $data;
        } else if ((is_null($this->_data) || is_array($this->_data) || is_object($this->_data)) && 
            (is_array($data) || is_object($data))) {
            if (is_null($this->_data)) {
                $this->_data = [];
            }
            
            $this->_data = array_merge_recursive((array)$this->_data, (array)$data);
        } else {
            throw new \Calf\Exception\InvalidArgument('Data type mismatched.');
        }

        return $this;
    }
    
    /**
     * Compress output
     * 
     * @access  public
     * @param   bool    $enabled    Enable gzip (default true)
     * @return  object
     * 
     */
    public function compress($enabled = true) {
        $this->_compress = $enabled;
        
        return $this;
    }
    
    /**
     * Set response cookies
     * 
     * @access  public
     * @param   array   $data   Cookie key value
     * @return  object
     * 
     */
    public function setCookie(array $data) {
        $this->_cookies = array_merge_recursive($this->_cookies, $data);
        
        return  $this;
    }
    
    /**
     * Get formatted responsed data
     * 
     * @access  public
     * @return  string
     * 
     */
    public function get($raw = false) {
        return $raw 
                ? $this->_data
                : $this->_format($this->_data);
    }
    
    /**
     * Set header
     *
     * @param   mixed    $headers    HTTP header
     * @return  object
     *
     */
    public function setHeader($headers) {
        // Record HTTP header
        if (is_array($headers)) {
            foreach ($headers as $header) {
                array_push($this->_headers, $header);
            }
        } else if (is_string($headers)) {
            array_push($this->_headers, $headers);
        }
        
        return $this;
    }
    
    /**
     * Get headers for response
     * 
     * @access  public
     * @return  array
     * 
     */
    public function getHeaders() {
        return $this->_headers;
    }


    /**
     * Get header
     * 
     * @access public
     * @return any
     */
    public function getHeader($key) {
        foreach ($this->_headers as $header) {
            $matches = [];
            preg_match("/^{$key}:(.*)/i", $header, $matches);

            if ($matches) return trim($matches[1]);
        }
    }
    
    /**
     * Get cookies for response
     * 
     * @access  public
     * @return  array
     * 
     */
    public function getCookies() {
        return $this->_cookies;
    }
    
    /**
     * Set response headers and print response text
     * 
     * @access  public
     * @return  object
     * 
     */
    public function render() {
        // Parse contents
        $res = $this->get();
        
        // Set HTTP Headers
        foreach ($this->_headers as $header) {
            header($header);
        }
        
        // Set cookies
        foreach($this->_cookies as $k => $v) {
            setcookie($k, $v);
        }
        
        print($res);
    }
}
