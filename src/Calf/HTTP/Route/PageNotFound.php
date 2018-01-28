<?php

namespace Calf\HTTP\Route;

/**
 * Page Not Found
 * 
 * @version 1.1
 * @author  John Aldrich Bernardo <4ldrich@protonmail.com>
 * 
 */
class PageNotFound extends \Calf\HTTP\Route
{
    /**
     * Class construct
     * 
     * @access  public
     * 
     */
    function __construct() {
        parent::__construct('404', function(\Calf\HTTP\Request $req, \Calf\HTTP\Response $res) {
            $res->set(
                '<!DOCTYPE html>'
                . '<!-- Calf Router for PHP by John Aldrich Bernardo -->'
                . '<html>'
                . '<head><title>404 Not Found</title></head>'
                . '<meta name="viewport" content="width=device-width, initial-scale=1">'
                . '<body>'
                . '<h1>404 Not Found</h1>'
                . '<p>The page that you have requested could not be found.</p>'
                . '</body>'
                . '</html>'
            );
            
            $res->setHeader('HTTP/1.0 404 Not Found');
            
            return $res;
        });
    }
}
