<?php
defined('_BLIB') or die;

class bExample extends bBlib{	
	
	protected function inputSelf(){
		$this->version = '1.0.0';
		$this->parents = array('bSystemAlias', 'bConfig', 'bDatabase');
	}
	
	protected function input($data, $caller){
		$this->caller = $caller;
	}
	

	
	public function output(){
		
		if($this->caller){
			$veryImportantData = array(
				'local' => $this->version,
				'global' => $this->_version
			);
			
			$forMyChild = 'do this';
			$forAllMyChild = 'do that';
			
			if($this->caller){
				return array(
					'do'	=>	$forMyChild,
					'data'	=>	$veryImportantData
				);
			}else{
				return array( 'do' => $forAllMyChild);
			}
		}else{
			//$this->install(11,59);
			var_dump($this);
			
			//header('Content-Type: text/html; charset=utf-8');
			//echo '<div>Hallo win.</div>';
			//exit;
		}
	}
	
	
	/** overload for parent block bDatabase */
	/*
	public function install() {
		return false;
    }
	
	public function uninstall() {
		return false;
    }
	*/
}

class bExample__object{
	
	public function __construct($data, $caller){
		
	}

}