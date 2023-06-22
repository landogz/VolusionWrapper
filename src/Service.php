<?php

namespace Optimum7;

class Service {

	protected $api_credientals;
	protected $extra_params;
	protected $query_params;
	protected $service;
	protected $url;
 
	public function __construct($service) {

		$this->api_credientals = $service;
		$this->setEndPoint();
		$this->setParams();

	}


	public function setParams($params = array()) {
		if(isset($params)) {
			$this->extra_params = $params;
			$this->query_params = array_merge($this->api_credientals,$this->extra_params);
		}
	}

	public function getParams() {
		return $this->extra_params;
	}

	public function getQueryParams() {
		return $this->query_params;
	}

	public function setEndPoint($url = 'https://io.optimum7.com/ws/', $service = 'volusion') {
		$this->url = $url;
		$this->service = $service;
		return true;
	}

	public function query($function) {
		try {
			ini_set('default_socket_timeout', 600);

				$url = $this->url . $this->service . '/index.php?wsdl';
			
			$client = new \SoapClient($url, array( 'cache_wsdl' => WSDL_CACHE_NONE ));

			if (isset($client->fault) && $client->fault) {
				throw new Exception($client->getError());
			} else {
				return $client->__soapCall($function, array( $this->query_params ));
			}
		} catch(Exception $e) {

			$return = new stdClass();
			$return->error = $e->getMessage();
			$return->return = '';

			return $return;
		}
	}

	public function getCustomers($params) {
		
		$this->setParams($params);
		$result = $this->query('getCustomers');
		return $result;

	}

	public function getCustomerByID($id) {

		$param = array('customerID' => $id);
		$this->setParams($param);
		$result = $this->query('getCustomerByID');
		return $result;

	}

	public function getCustomerByEmail($email) {

		$param = array('customerEmail' => $email);
		$this->setParams($param);
		$result = $this->query('getCustomerByEmail');
		return $result;

	}

	public function getCustomerByAccessKey($accessKey) {

		$param = array('AccessKey' => $accessKey);
		$this->setParams($param);
		$result = $this->query('getCustomerByAccessKey');
		return $result;

	}

	public function addCustomers($customer) {
	
    	$json = json_encode(array('array'=>array($customer)));
    	$param = array('json' => $json);
    	$this->setParams($param);
    	$result = $this->query('addCustomers');
    	return $result;
    	
	}		

	public function updateCustomers($customer) {
	
    	$json = json_encode(array('array'=>array($customer)));
    	$param = array('json' => $json);
    	$this->setParams($param);
    	$result = $this->query('updateCustomers');
    	return $result;
    	
	}	

	public function getProducts($params) {
		
		$this->setParams($params);
		$result = $this->query('getProducts');
		return $result;

	}

	public function addProducts($product) {
	
    	$json = json_encode(array('array'=>array($product)));
    	$param = array('json' => $json);
    	$this->setParams($param);
    	$result = $this->query('addProducts');
    	return $result;

	}	

	public function updateProducts($product) {
	
    	$json = json_encode(array('array'=>array($product)));
    	$param = array('json' => $json);
    	$this->setParams($param);
    	$result = $this->query('updateProducts');
    	return $result;

	}	

	public function insertProducts($product) {
	
    	$json = json_encode(array('array'=>array($product)));
    	$param = array('json' => $json);
    	$this->setParams($param);
    	$result = $this->query('insertProducts');
    	return $result;

	}



	public function getProductBySkuCode($sku) {

		$param = array('skuCode' => $sku);
		$this->setParams($param);
		$result = $this->query('getProductBySkuCode');
		return $result;

	}	

	public function getChildProductsOf($sku) {

		$param = array('skuCode' => $sku);
		$this->setParams($param);
		$result = $this->query('getChildProductsOf');
		return $result;

	}

	public function getOrders($params) {
		
		$this->setParams($params);
		$result = $this->query('getOrders');
		return $result;

	}

	public function getOrderByID($id) {

		$param = array('orderID' => $id);
		$this->setParams($param);
		$result = $this->query('getOrderByID');
		return $result;

	}


	public function getAllProducts(){
		$result = $this->query('getAllProducts');
		return $result;
	}


	public function debug($var, $type ='print', $exit = FALSE) {
		echo '<pre>';
		if($type == 'print') 
			print_r($var);
		else 
			var_dump($var);
		echo '</pre>';
		if ($exit) {
			exit( 'Code execution stopped.' );
		}
	}

	public function listMethods() {
        echo '<pre>';
        var_dump(get_class_methods($this));
        echo '</pre>';
    }


}