<?php

namespace Calf\HTTP;

/**
 * File Upload
 * 
 * @package Calf
 * @version 1.0.0
 * @author  John Aldrich Bernardo <4ldrich@protonmail.com>
 * 
 */
class FileUpload
{
    /**
     * Input name of file upload
     * 
     * @var string
     * 
     */
    private $_file = '';
    
    /**
     * Class construct
     * 
     * @throws  \Calf\Exception\Argument
     * @throws  \Calf\Exception\Runtime
     * 
     */
    function __construct($file) {
        if (!isset($_FILES)) {
            throw new \Calf\Exception\Runtime('$_FILES is not defined');
        }
        
        if (!is_string($file)) {
            throw new \Calf\Exception\InvalidArgument('String expected');
        }
        
        $this->_file = $file;
    }
    
    /**
     * Get filename(s) of file input
     * 
     * @access  public
     * @return  mixed
     * 
     */
    public function getName() {
        if (!isset($_FILES[$this->_file])) {
            return false;
        }
        
        return $_FILES[$this->_file]['name'];
    }
    
    /**
     * Get type(s) of file input
     * 
     * @access  public
     * @return  mixed
     * 
     */
    public function getType() {
        if (!isset($_FILES[$this->_file])) {
            return false;
        }
        
        return $_FILES[$this->_file]['type'];
    }
    
    /**
     * Get status(es) of file input
     * 
     * @access  public
     * @return  mixed
     * 
     */
    public function getStatus() {
        if (!isset($_FILES[$this->_file])) {
            return false;
        }
        
        return $_FILES[$this->_file]['error'];
    }
    
    /**
     * Store temporary files to destination path of uploads
     * 
     * @access  public
     * @param   string      $dest_folder    Destination path
     * @param   Callable    $modifyName     Name modification callback
     * @return  mixed       Returns `false` if upload fails. Returns `array` of 
     *      stored and failed files for multiple file upload.
     * 
     */
    public function store($dest_folder, Callable $modifyName = null) {
        if (!isset($_FILES[$this->_file])) {
            return false;
        }
        
        if (is_array($_FILES[$this->_file]['name'])) {
            // Multiple file uploads
            $count = count($_FILES[$this->_file]['name']);
            $stored = [];
            $failed = [];
            
            for ($i = 0; $i < $count; $i++) {
                // Get modified name from callback
                $dest = rtrim($dest_folder, '/') . '/' . 
                    (is_callable($modifyName) ? $modifyName($_FILES[$this->_file]['name'][$i]) : $_FILES[$this->_file]['name'][$i]);
                
                if (\move_uploaded_file($_FILES[$this->_file]['tmp_name'][$i], $dest)) {
                    // Record all uploaded files
                    $stored[] = $_FILES[$this->_file]['name'][$i];
                } else {
                    $failed[] = $_FILES[$this->_file]['name'][$i];
                }
            }
            
            return [
                    'stored' => $stored,
                    'failed' => $failed
                ];
        }
        
        // Single file upload
        $dest = rtrim($dest_folder, '/') . '/' . 
            (is_callable($modifyName) ? $modifyName($_FILES[$this->_file]['name']) : $_FILES[$this->_file]['name']);
        
        return \move_uploaded_file($_FILES[$this->_file]['tmp_name'], $dest);
    }
}
