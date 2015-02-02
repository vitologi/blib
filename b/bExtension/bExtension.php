<?php
defined('_BLIB') or die;

/**
 * Class bExtension
 * Compile dev. files into product version (block.js.dev + elem1.js.dev + elem2.js.dev + mode1.js.dev = block.js)
 */
class bExtension extends bBlib{

	/**
	 * @var null|static		- variable for storage Singleton object
     */
	private static $_instance = null;

	/**
	 * Overload object factory for Singleton
	 *
	 * @return null|static
     */
	static public function create() {
        if (self::$_instance === null)self::$_instance = parent::create(func_get_args());
        return self::$_instance;
    }

	/**
	 * Return current object without parent block
	 *
	 * @return $this
     */
	public function output(){
        $this->_parent = null;
        return $this;
	}

	/**
	 * Glues files abstraction in working files
	 * @param array $list		- two-dimensional array ( names of dev-files, like ["bBlock"=>["bBlock_mode1"],"bBlock2"=>["bBlock2_mode1","bBlock2_mode2"]] )
	 * @param bBlib $caller
	 */
	public static function _concat($list = array(), bBlib $caller){

        foreach($list as $key => $value){
            $caller->getInstance(__class__)->concat($key, $value);
        }
    }

	/**
	 * Directly glues one block and his modifiers
	 *
	 * @param string $block		- block`s name
	 * @param string[] $list	- modifiers list
	 * @return $this			- for chaining
     */
	private function concat($block = '', $list = array()){

        $files = $this->concatCode($block, array());
        
		foreach($list as $key => $value){
			$files = $this->concatCode($value, $files);
		}
        
		foreach($files as $key => $value){
			file_put_contents(bBlib::path($block, $key), $value);
		}
		return $this;

	}


	/**
	 * Collects all code from the dev-files and sorts it by file extension
	 *
	 * @param string $name		- block`s name
	 * @param array $stack		- empty array or result previous iteration
	 * @return array			- sorted glues code (like ["js"=>"code1 \n code2", "css"=>"css rule1 \n css rule2"])
     */
	private function concatCode($name = '', $stack = array()){
		$path = bBlib::path($name);
		$folder =  opendir($path);
		while($file = readdir($folder)){
			if(preg_match('/\w*.(\w+).dev$/', $file, $matches)){
				if(!isset($stack[$matches[1]]))$stack[$matches[1]]='';
				$stack[$matches[1]] .= file_get_contents($path.$file);
				continue;
			}
            
            if(is_dir($path.$file) && substr($file, 0,2) === '__'){
				$stack = $this->concatCode($name.$file, $stack);
			};
		}
		
		return $stack;
	}
    
    
}