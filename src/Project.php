<?php

namespace Optimum7;

class Project {

	public $db;
	public $service;
	public $credentials;

	public function __construct($modules,$credentials) {


		if(empty($credentials)) {
			throw new \Exception('You must set a credentials for Project');
		} else {
			$this->credentials = $credentials;
			if(empty($modules)) {
				throw New \Exception('You must set a module for Project');
			} else {
				if(in_array('db',$modules)) {
					if(array_key_exists('db', $credentials)) {
						$this->db = new \Optimum7\DB($credentials['db']);
					}else {
						throw new \Exception('You cannot use DB module without its credentials');
					}
				} 

				if(in_array('service',$modules)) {
					if(array_key_exists('service', $credentials)) {
						$this->service = new \Optimum7\Service($credentials['service']);
					}else {
						throw New \Exception('You cannot use Service module without its credentials');
					}
				}
			}	
		}

	
	}


}