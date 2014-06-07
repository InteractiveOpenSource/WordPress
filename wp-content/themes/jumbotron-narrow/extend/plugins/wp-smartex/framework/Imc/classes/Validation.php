<?php

class Validation {
	/**
	 * 
	 * @param Array $data
	 * @return Validation
	 */
	public static function factory($data) {
		return new Validation($data);
	}
	
	private $data;
	
	public function __construct($data) {
		$this->data = $data;
	}
	
	public function __call($name, $args) {
		echo "validation " . $name;
		exit;
	}
	
	public function check() {
		return true;
	}
	
	public function bind($key, $object) {
		
	}
}
