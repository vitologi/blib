<?php
defined('_BLIB') or die;

class bDocumentation extends bBlib{	
	
	protected function inputSelf(){
		$this->version = '1.0.0';
		$this->parents = array('bSystem', 'bDatabase', 'bConfig');
	}
	
	protected function input($data, $caller){
		$this->data = $this->hook('getData', array($data));
	}
	
	public function output(){

		$answer = array(
			'block' => __class__,
			'mods' => $this->data['mods']
		);

		if($this->data['id']){
		
			$Q = array(
				'select'	=> array(
					'bDocumentation' => array('id', 'name', 'description' ,'group', 'parent'=>'bDocumentation_id')
				),
				'where' => array(
					'bDocumentation' => array('id'=>$this->data['id'])
				)
			);
			$result = $this->_query($Q);
			$temp = $result->fetch(PDO::FETCH_ASSOC);
			$temp['description'] = json_decode($temp['description']);
			$group = json_decode($temp['group']);
			unset($temp['group']);
			
			if($group){
				foreach($group as $id){
					$where[] = array('id',$id,false,true);
				}
				$Q = array(
					'select'	=> array(
						'bDocumentation' => array('id', 'name', 'note', 'parent'=>'bDocumentation_id')
					),
					'where' => array(
						'bDocumentation' => $where
					)
				);
				$result = $this->_query($Q);
				$content = $result->fetchALL(PDO::FETCH_ASSOC);
			}
			
			$temp['content'] = $content;
			$answer['item'] = $temp;
		}
		
		if($this->data['chapter']){
			$Q = array(
				'select'	=> array(
					'bDocumentation' => array('id', 'name', 'parent'=>'bDocumentation_id')
				)
			);
			$result = $this->_query($Q);
			$navigation = $result->fetchALL(PDO::FETCH_ASSOC);
						
			$answer['navigation'] = $navigation;
			$answer['chapter'] = $this->data['chapter'];
		}
		
		if($this->data['ajax']){
			echo json_encode($answer);
		}else{
			return $answer;
		}
		
	}
	
	protected function getData($data){
		return $data;
	}
	
}