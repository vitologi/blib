<?php 

abstract class bBlib{
	
	/** GLOBAL DATA */
	protected static $global = array(
		'_version'	=> "0.0.2",
		'_request'	=> null,
		'hook'		=> array(
			'list'		=> array(),
			'handlers'	=> array()
		)
	);
	
	/** LOCAL DATA */
	protected $local = array(
		
	);
	
	/** INTERCEPTION METHODS */
	final public function __construct(Array $data = array(), bBlib $caller = null){
		
		$this->inputSelf();
		
		$parents = is_array($this->local['parents'])?$this->local['parents']:array();
		foreach($parents as $value){
			$parent = new $value($data, $this);
			$this->inputSystem((array)$parent->output());
		}

		$this->input($data, $caller);
	}
	
	//increases the access time (local by 11 times)(global by 5 times) (1kk iteration test) need use $this->local['name'] or bBlib::$global['name'] for ignore it
	function __get($property){
		return ($property{0}==="_")?self::$global[$property]:$this->local[$property];
	}
	
	function __set($property, $value){
		return($property{0}==="_")?(isset(self::$global[$property]) or self::$global[$property] = $value):(isset($this->local[$property]) or $this->local[$property] = $value);
	}
	
	function __isset($property){
		return ($property{0}==="_")?isset(self::$global[$property]):isset($this->local[$property]);
	}
	
	//increases the access time by 6 times(1kk iteration test) need overload methods in child class for ignore it
	function __call($method, $args){
		$parents = is_array($this->local['parents'])?$this->local['parents']:array();
		foreach($parents as $value){
			if (!method_exists($value, $method)) continue;
			return call_user_func_array(array($value, $method), array($args, $this));
		}
	} 
	
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
	
	/** SETTERS */
	private static function inputGlobals(){
		self::$global['_request'] = (array)json_decode(file_get_contents("php://input"),true)+(array)$_POST +(array)$_GET;
	}
	
	abstract protected function inputSelf();
	
	final protected function inputSystem($data){
		foreach($data as $key => $value){
			if(isset($this->local[$key]))continue;
			$this->local[$key] = $value;
		}
	}
	
	protected function input($data, $caller){}
	
	/** GETTERS */
	public function output(){}

	/** INTERFACES */
	final public function call($name, $args){
		return call_user_func_array(array($this, $name), (array)$args);
	}
	
	final public function path($name = null, $ext = null){
		$name = ($name)?$name:get_class($this);
		if($ext){$ext = $name.'.'.$ext;}
		return $name{0}.'/'.preg_replace('/(_+)/i', '/${1}', $name).'/'.$ext;		
	}

	final protected function hook($method, $input = array()){
		
		$block = get_class($this);
		$_hook = bBlib::$global['hook'];
		
		if(!$_list = $_hook['list']){
			$path = bBlib::path('bBlib__hook', 'ini');
			if(!file_exists($path)){return;}
			$_list = json_decode(file_get_contents($path), true);
		}
		
		if(!$_list[$block]){return;}
		
		$list = $_list[$block];
		$answer = array();
		$returnFlag = false;
		
		if(!$handlers = $_hook['handlers'][$block]){
			$handlers = array();
			foreach($list as $value){
				$handlers[$value] = new $value(array(), $this);
			}
			$_hook['handlers'][$block] = $handlers;
		}
		
		foreach($handlers as $value){
			if(!method_exists($value, $method)){continue;}
			$answer = (array)$value->$method(array('input'=> $input, 'output'=> $output), $this);

			if($answer['input']){
				$input = $answer['input'];
			}
			if($answer['output']){
				$output = $answer['output'];
				$returnFlag = true;
			}
		}
		
		if(!$returnFlag && method_exists($this, $method)){
			$output = call_user_func_array(array($this, $method), (array)$input);
		}
		
		return $output;
	}
	
	final public static function gate() {
		try{
			define("_BLIB", true);
			self::autoload();
			self::inputGlobals();
			
			if($blib = self::$global['_request']['blib']){
				$block = new $blib(self::$global['_request']);
				$block->output();
			}
		}catch(Exception $e){
			echo sprintf('(%1$s) [%2$s] - %3$s ', $e->getFile(), $e->getLine(), $e->getMessage());
		}
    }
	
	final public static function compile($block){

		$_hook = bBlib::$global['hook'];
		
		if(!$_list = $_hook['list']){
			$path = bBlib::path('bBlib__hook', 'ini');
			if(!file_exists($path)){return;}
			$_list = json_decode(file_get_contents($path), true);
		}
		
		if(!$_list[$block]){return;}
		
		$files = array();
		$list = $_list[$block];
		
		$files = bBlib::compileGlue($block, $files);

		foreach($list as $value){
			$files = bBlib::compileGlue($value, $files);
		}

		foreach($files as $key => $value){
			file_put_contents(bBlib::path($block, $key), $value);
		}

	}
	
	final private static function compileGlue($name, $stack){
		$path = bBlib::path($name);
		$folder =  opendir($path);
		while($file = readdir($folder)){
			if(preg_match('/\w*.(\w+).dev$/', $file, $matches)){
				$stack[$matches[1]] .= file_get_contents($path.$file);
				continue;
			}
			
			if(is_dir($path.$file) && substr($file, 0,2) === '__'){
				$stack = bBlib::compileGlue($name.$file, $stack);
			};
		}
		
		return $stack;
	}
}

