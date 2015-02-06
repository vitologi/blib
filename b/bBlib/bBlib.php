<?php

abstract class bBlib{

    const VERSION           = "0.0.2";  // Engine version
    protected $_parent      = null;     // Block - creator
    protected $_traits      = array();  // Blocks list for multiple inheritance
    protected $_instances   = array();  // Implemented objects
    protected $_vars        = array();  // Local variables
    protected static $_VARS = array();  // Global variables
    
    /**
     * Create all bBlib concrete instance factory method
     *
     * @return static       - concrete instance
     */
    static public function create() {
        return new static(func_get_args());
    }


    /** BASE INPUT/OUTPUT METHODS */
    protected function input(){}
    public function output(){}

    
    /** INTERFACES */
    /**
     * Get included instance
     *
     * @param string $name      - instance`s name
     * @return null|bBlib       - null or implemented block
     */
    final protected function getInstance($name = ''){
        return (isset($this->_instances[$name])?$this->_instances[$name]:null);
    }
    

    /**
     * Set caller block
     *
     * @param bBlib $block      - block which initiated creation
     * @return $this            - for chaining
     */
    final public function setParent(bBlib $block = null){
        $this->_parent = $block;
        return $this;
    }


    /**
     * Extend functionality of block by saving instance of extender
     *
     * @param string $name      - block`s name
     * @param array $data       - some data
     * @return $this            - for chaining
     */
    final protected function setTrait($name = '', $data = array()){
        if(!is_array($this->_traits))$this->_traits = array();
        if(!in_array($name, $this->_traits))$this->_traits[] = $name;
        
        $result = $name::create($data)->setParent($this)->output();
        
        if($result instanceof bBlib){
            $this->_instances[$name] = $result;
        }else{
            foreach((array)$result as $key => $value){
                if(isset($this->_vars[$key]))continue;
                $this->_vars[$key] = $value;
            }
        }
        
        return $this;
    }


    /**
     * Generate path to block in BEM notation
     *
     * @param string $name      - block`s name
     * @param string $ext       - extension of file
     * @return string           - path to block folder or to file (if have $ext)
     * @throws Exception
     */
    final public static function path($name = '', $ext = ''){
        if($name === '')throw new Exception('Given incorrect argument') ;
        if($ext != ''){$ext = $name.'.'.$ext;}
        return $name{0}.'/'.preg_replace('/(_+)/i', '/${1}', $name).'/'.$ext;
    }


    /**
     * Start application point
     *
     * @param string $block     - block`s name
     */
    final public static function init($block = '') {
        try{
            define("_BLIB", true);
            self::autoload();
            if(!is_string($block) or $block === '')return;
            $block::create()->output();
        }catch(Exception $e){
            echo sprintf('(%1$s) [%2$s] - %3$s ', $e->getFile(), $e->getLine(), $e->getMessage());
        }
    }

    
    /** INTERCEPTION METHODS */
    final private function __clone(){}  // Protect from cloning
    final private function __sleep(){}  // Protect from serialize
    final private function __wakeup(){} // Protect from unserialize
    final private function __construct(){
        $data = (func_num_args()===1)?func_get_arg(0):func_get_args();
        
        $traits = is_array($this->_traits)?$this->_traits:array();
        foreach($traits as $value)$this->setTrait($value, $data);
        $this->input($data);
    }
    
    // Devide scope on local & global
    function __get($key){
        if($key{0}==="_"){
            return isset(self::$_VARS[$key])?self::$_VARS[$key]:null;
        }
        return isset($this->_vars[$key])?$this->_vars[$key]:null;
    }

    function __set($key, $value){
        return($key{0}==="_")?(isset(self::$_VARS[$key]) or self::$_VARS[$key] = $value):(isset($this->_vars[$key]) or $this->_vars[$key] = $value);
    }

    function __isset($key){
        return ($key{0}==="_")?isset(self::$_VARS[$key]):isset($this->_vars[$key]);
    }
    
    // Call method from trait blocks
    function __call($method, $args){
        if(!is_array($this->_traits))$this->_traits = array();
        
        foreach($this->_traits as $value){
            if (!method_exists($value, $method)) continue;
            $args[] = $this;
            return call_user_func_array(array($value, $method), $args);
        }
    } 

    // Autoload class
    private static function autoload(){
        
        function _autoload($class){
            $path = bBlib::path($class, 'php');
            if(!file_exists($path)){throw new Exception('Called class '.$class.' is missing.');}
            require_once($path);
        }
        
        if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
            spl_autoload_register('_autoload');
        }else {
            function __autoload($class) {
                _autoload($class);
            }
        }
    }
    
}
