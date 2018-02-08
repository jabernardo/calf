<?php

namespace Calf;

/**
 * Saddle: A Simple Dependency Injection
 * 
 * @version     1.0.0
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
            $this->add($key, $value);
        }
    }

    /**
     * Add Service
     *
     * @access  public
     * @param   string  $key        Service ID
     * @param   mixed   $value      Service Object
     * @param   boolean $protected  Protected Service
     * @return  object  Container
     */
    function add($key, $value, $protected = false) {
        if (!is_string($key)) {
            throw new \Calf\Exception\InvalidArgument('Key must be string.');
        }

        if (isset($this->_keys[$key])) {
            throw new \Calf\Exception\Runtime('Duplicated key.');
        }

        $this->_keys[$key] = true;
        $this->_values[$key] = $value;

        if ($protected) {
            $this->_protected[$key] = true;
        }

        return $this;
    }

    /**
     * Check if service exists
     *
     * @access  public
     * @param   string  $key    Service ID
     * @return  boolean
     */
    public function exists($key) {
        if (!is_string($key)) {
            throw new \Calf\Exception\InvalidArgument('Key must be string.');
        }

        return isset($this->_keys[$key]);
    }

    /**
     * Get Service Value
     * If service is callable will automatically pass container
     * as parameter.
     * 
     * @access  public
     * @param   string  $key    Service ID
     * @param   boolean $raw    Get service value in raw format
     * @return  void
     */
    function get($key, $raw = false) {
        if (!is_string($key)) {
            throw new \Calf\Exception\InvalidArgument('Key must be string.');
        }

        if (!isset($this->_keys[$key])) {
            throw new \Calf\Exception\Runtime('Key doesn\'t exists.');
        }

        if (is_callable($this->_values[$key]) && !$raw) {
            $call = $this->_values[$key];

            return \call_user_func($call, $this);
        }

        return $this->_values[$key];
    }

    /**
     * Remove Service
     *
     * @access  public
     * @param   string  $key    Service ID
     * @return  object  Container
     */
    function remove($key) {
        if (!is_string($key)) {
            throw new \Calf\Exception\InvalidArgument('Key must be string.');
        }

        if (!isset($this->_keys[$key])) {
            throw new \Calf\Exception\Runtime('Key doesn\'t exists.');
        }

        if (isset($this->_protected[$key])) {
            throw new \Calf\Exception\Runtime('Key is protected.');
        }

        unset($this->_keys[$key]);
        unset($this->_values[$key]);

        return $this;
    }

    /**
     * Update Service
     *
     * @access  public
     * @param   string  $key    Service ID
     * @param   mixed   $value  Service
     * @return  object  container
     */
    function update($key, $value) {
        if (!is_string($key)) {
            throw new \Calf\Exception\InvalidArgument('Key must be string.');
        }

        if (!isset($this->_keys[$key])) {
            throw new \Calf\Exception\Runtime('Key doesn\'t exists.');
        }

        if (isset($this->_protected[$key])) {
            throw new \Calf\Exception\Runtime('Key is protected.');
        }

        $this->_keys[$key] = true;
        $this->_values[$key] = $value;

        return $this;
    }
}
