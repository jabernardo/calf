<?php

namespace Calf\HTTP;

/**
 * Route Parser Class
 * 
 * @version 1.1
 * @author  John Aldrich Bernardo <4ldrich@protonmail.com>
 * 
 */
class RouteParser
{
    /**
     * @var string  Active URL Path
     * 
     */
    private $_path = '';
    
    /**
     * @var string  Route pattern
     * 
     */
    private $_pattern = '';
    
    /**
     * @var array   Mapped Keys Value of matches
     * 
     */
    private $_mapped = [];
    
    /**
     * Class construct
     * 
     * @example
     * 
     * Route:   /page/{number}
     * URL:     /page/1
     * 
     * Matches:
     * 
     * [
     *      "number" => 1
     * ]
     * 
     * @access  public
     * @param   string  $path   Active URL Path
     * @return  void
     * 
     */
    function __construct($path) {
        $this->_path = $path;
    }
    
    /**
     * Check if Route matches Path
     * 
     * @access  public
     * @param   string  $route  Route pattern
     * @return  bool
     * 
     */
    public function test($route) {
        // Make sure that matches are empty
        $this->_mapped = [];
        
        $this->_pattern = $route;
        
        // Replace keys with RegEx
        $translated_path = preg_replace(['/\//i', '/({\w*?})/i'], ['\/', '([^\/\n\r\t]+)'], trim($route, '/'));
        
        // Active script or running script (this is when no redirection is being done in .htaccess)
        $as =  str_replace('/', '\/', trim($_SERVER["SCRIPT_NAME"], '/') . ($translated_path ? '/' : ''));
        
        $matches = [];
        
        // Check regex if matching our current path
        $test = preg_match('/^' . $translated_path . '$/i', $this->_path, $matches) ||
                    preg_match('/^' . $as . $translated_path . '$/i', $this->_path, $matches);

        // Make sure to remove the full string from the matches...
        array_shift($matches);

        // Param keys
        $keys = [];
        preg_match_all('/\{(\w*?)\}/i', $route, $keys);
                
        if (isset($keys[1]) && count($matches) === count($keys[1])) {
            for ($i = 0; $i < count($matches); $i++) {
                $this->_mapped[$keys[1][$i]] = $matches[$i];
            }
        }
        
        return $test;
    }
    
    /**
     * Get pattern from last test
     * 
     * @return  string
     * 
     */
    public function getPattern() {
        return $this->_pattern;
    }
    
    /**
     * Get matches
     * 
     * @return  array
     * 
     */
    public function getMatches() {
        return $this->_mapped;
    }
}
