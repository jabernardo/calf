<?php

namespace Calf;

/**
 * Saddle: A Simple Dependency Injection
 * 
 * @version     2.0.0
 * @author      John Aldrich Bernardo <4ldrich@protonmail.com>
 * @package     Calf
 */
class Saddle
{
    /**
     * Keys
     *
     * @var     array
     * @access  private
     */
    private $_keys = [];

    /**
     * Values
     *
     * @var     array
     * @access  private
     */
    private $_values = [];

    /**
     * Protected
     *
     * @var     array
     * @access  private
     */
    private $_protected = [];

    /**
     * Class construct
     *
     * @access  public
     * @param   array   $data   Services
     * @return  void
     */
    function __construct(array $data = []) {
        foreach ($data as $key => $value) {
            $this->__set($key, $value);
        }
    }

    /**
     * Add Service
     *
     * @access  public
     * @param   string  $key        Service ID
     * @param   mixed   $value      Service Object
     * @param   boolean $protected  Protected Service
     * @return  void
     * @throws  \Calf\Exception\InvalidArgument     Key must be string
     * @throws  \Calf\Exception\Runtime             Key is protected
     */
    function __set($key, $value) {
        if (!is_string($key)) {
            throw new \Calf\Exception\InvalidArgument('Key must be string.');
        }

        if (isset($this->_protected[$key])) {
            throw new \Calf\Exception\Runtime('Key is protected: '. $key);
        }

        $this->_keys[$key] = true;
        $this->_values[$key] = $value;
    }

    /**
     * Get Service Value
     * If service is callable will automatically pass container
     * as parameter.
     * 
     * @access  public
     * @param   string  $key    Service ID
     * @return  void
     * @throws  \Calf\Exception\InvalidArgument Key must be string
     * @throws  \Calf\Exception\Runtime         Key doesn't exists
     */
    function __get($key) {
        if (!is_string($key)) {
            throw new \Calf\Exception\InvalidArgument('Key must be string.');
        }

        if (!isset($this->_keys[$key])) {
            throw new \Calf\Exception\Runtime('Key doesn\'t exists: ' . $key);
        }

        if (is_callable($this->_values[$key])) {
            $call = $this->_values[$key];

            return \call_user_func($call, $this);
        }

        return $this->_values[$key];
    }

    /**
     * Isset override
     *
     * @access  public
     * @param   string  $key    Service ID
     * @return  boolean
     * @throws  \Calf\Exception\InvalidArgument     Key must be string
     */
    function __isset($key) {
        if (!is_string($key)) {
            throw new \Calf\Exception\InvalidArgument('Key must be string.');
        }

        return isset($this->_keys[$key]);
    }

    /**
     * Protect key
     *
     * @access  public
     * @return  void
     * @throws  \Calf\Exception\InvalidArgument Key must be string
     * @throws  \Calf\Exception\Runtime         Key doesn't exists
     */
    public function protect($key) {
        if (!is_string($key)) {
            throw new \Calf\Exception\InvalidArgument('Key must be string.');
        }

        if (!isset($this->_keys[$key])) {
            throw new \Calf\Exception\Runtime('Key doesn\'t exists: ' . $key);
        }

        $this->_protected[$key] = true;
    }

    /**
     * Get raw value
     *
     * @access  public
     * @return  void
     * @throws  \Calf\Exception\InvalidArgument Key must be string
     * @throws  \Calf\Exception\Runtime         Key doesn't exists
     */
    public function raw($key) {
        if (!is_string($key)) {
            throw new \Calf\Exception\InvalidArgument('Key must be string.');
        }

        if (!isset($this->_keys[$key])) {
            throw new \Calf\Exception\Runtime('Key doesn\'t exists: ' . $key);
        }

        return $this->_values[$key];
    }

    /**
     * Remove Service
     *
     * @access  public
     * @param   string  $key    Service ID
     * @return  void
     * @throws  \Calf\Exception\InvalidArgument Key must be string
     * @throws  \Calf\Exception\Runtime         Key doesn't exists
     * @throws  \Calf\Exception\Runtime         Key is protected
     */
    function __unset($key) {
        if (!is_string($key)) {
            throw new \Calf\Exception\InvalidArgument('Key must be string.');
        }

        if (!isset($this->_keys[$key])) {
            throw new \Calf\Exception\Runtime('Key doesn\'t exists: ' . $key);
        }

        if (isset($this->_protected[$key])) {
            throw new \Calf\Exception\Runtime('Key is protected: ' . $key);
        }

        unset($this->_keys[$key]);
        unset($this->_values[$key]);
    }
}
