<?php 

namespace Optimum7;

class DB {


    public $volusion_site_url;
    public $volusion_server_asp_url;
    public $query_response;
    public $query_error;
    public $sql;
    public $where = '';
    public $columns = '*';
    public $table = '';
    public $utils;

    public function __construct($db) {
        $this->utils = new \Optimum7\Utils;
        foreach($db as $key => $value) {
            $this->$key = $value;
        }
    }

    public function query($query){
        $this->sql = $query;
        try{
            $post_types = "url=" . $_SERVER['REQUEST_URI'] . "&query=".urlencode($query)."&action=ExecuteQuerySchema";
            $this->connect($post_types);
        }catch(Exception $e){
            return false;
        }
        return $this;
    }

    public function select($table) {
        $this->table = $table;
        return $this;

    }


    public function column($fields) {

     if(is_array($fields)) {
            $fieldLast =  $utils->recursive_implode($fields);
        }else {
            $fieldLast = $fields;  
        }

        $this->columns = $fieldLast;
        return $this;
    }

    public function where($where) {

        $whereString = "";
        if($where) {
            $whereString = "WHERE $where";
        }
        $this->where = $whereString;
        return $this;
    }

    public function response($return_type = '') {
         switch ($return_type) {
            case 'arr':
                return $this->utils->xml2array(simplexml_load_string($this->query_response));
                break;
            case 'obj':
                return json_decode(json_encode($this->utils->xml2array(simplexml_load_string($this->query_response))), FALSE);
                break;
            default:
                return simplexml_load_string($this->query_response);
                break;
        }
    }
    public function get($return_type = '') {

        $this->sql = "SELECT ".$this->columns." FROM ".$this->table." ".$this->where."";
        $this->query($this->sql);
         switch ($return_type) {
            case 'arr':
                return $this->utils->xml2array(simplexml_load_string($this->query_response));
                break;
            case 'obj':
                return json_decode(json_encode($this->utils->xml2array(simplexml_load_string($this->query_response))), FALSE);
                break;
            default:
                return simplexml_load_string($this->query_response);
                break;
        }

    }


    public function insert($table,$data = array()) {
        if(!isset($data)) 
            return false;
        else {
            $fields = array();
            $values = array();
             foreach( array_keys($data) as $key ) {
                    
                $fields[] = "$key";
                $values[] = "'" . $data[$key] . "'";
                    
            }
        }

        $fields = implode(",", $fields);
        $values = implode(",", $values);

        $sql = "INSERT INTO $table ($fields) VALUES ($values);";
       // echo $sql;

        $this->query($sql);
    }


   
    public function connect($post_fields,$return_type=null){
        try{
            $curl = curl_init();
            curl_setopt_array($curl,
                array(CURLOPT_URL => $this->volusion_site_url.$this->volusion_server_asp_url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "POST",
                    CURLOPT_POSTFIELDS => $post_fields,
                    CURLOPT_HTTPHEADER => array("cache-control: no-cache", "content-type: application/x-www-form-urlencoded")
                )
            );
            $err = curl_error($curl);
            $this->query_error = $err;
            $this->query_response = curl_exec($curl);
            curl_close($curl);
            if ($err) {
                return false;
            }else {
                return $this->query_response;
            }
        }catch(Exception $e){
            return false;
        }
    }

    public function listMethods() {
        echo '<pre>';
        var_dump(get_class_methods($this));
        echo '</pre>';
    }

}