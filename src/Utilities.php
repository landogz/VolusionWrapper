<?php

namespace Optimum7;

class Utilities {

	const KEEP_JSON_LOGS_FOR = '30 Days';
	const FILE_JSON_TRANSACTION_LOG = 'transaction-logs.json';
	const FILE_JSON_EXCEPTION_LOG = 'exception-logs.json';

	public static function __callStatic($method, $arguments) {
		switch($method) {
			case 'write_transaction':
				$arguments[] = FALSE;
				call_user_func_array(array( self, 'write_log' ), $arguments);
			break;
			case 'write_exception':
				$arguments[] = TRUE;
				call_user_func_array(array( self, 'write_log' ), $arguments);
			break;
			default:
		}
	}

	/**
	 * Internal function to write the log rows to json files
	 *
	 * @param string $client_unique_id A definitive string and user readable client id for each client
	 * @param mixed  $parameters       If it is a string it should contain a message otherwise it should be an array to
	 *                                 create custom log rows
	 * @param bool   $exception        Determining that if this log is an Exception log or a Transaction log
	 *
	 * @return bool
	 */
	public static function write_log($client_unique_id, $parameters, $exception = FALSE) {
		try {
			if ($exception) {
				$log_file = dirname(__FILE__) . '/' . self::FILE_JSON_EXCEPTION_LOG;
			} else {
				$log_file = dirname(__FILE__) . '/' . self::FILE_JSON_TRANSACTION_LOG;
			}
			if (utilities::create_json_log_file()) {
				utilities::clean_json_log_file($log_file);
				$json = json_decode(file_get_contents($log_file));
				//we don't have to specify __FILE__ and __LINE__ for each log function call; with this debug_backtrace() function we can do it from here
				$debug_backtrace = debug_backtrace();
				$debug_backtrace = $debug_backtrace[ ( count($debug_backtrace) - 1 ) ];
				$log_row['file'] = $debug_backtrace['file'];
				$log_row['line'] = $debug_backtrace['line'];
				$log_row['date'] = (int) date('U');
				$log_row['date_string'] = date('l jS \of F Y H:i:s');
				if (is_array($parameters)) {
					$log_row = $log_row + $parameters;
				} else {
					$log_row['message'] = $parameters;
				}
				$json->{$client_unique_id}[] = $log_row;
				$json = json_encode($json);
				file_put_contents($log_file, $json);

				return TRUE;
			} else {
				throw new Exception('log file could not be created');
			}
		} catch(Exception $e) {
			return FALSE;
		}
	}

	public static function create_json_log_file() {
		try {
			$transaction_log_file = dirname(__FILE__) . '/' . self::FILE_JSON_TRANSACTION_LOG;
			$exception_log_file = dirname(__FILE__) . '/' . self::FILE_JSON_EXCEPTION_LOG;
			if (!file_exists($transaction_log_file)) {
				file_put_contents($transaction_log_file, '');
			}
			if (!file_exists($exception_log_file)) {
				file_put_contents($exception_log_file, '');
			}

			return TRUE;
		} catch(Exception $e) {
			return FALSE;
		}
	}

	public static function clean_json_log_file($log_file) {
		if (file_exists($log_file)) {
			$json = json_decode(file_get_contents($log_file));
			if (!empty( $json )) {
				$new_json = new stdClass();
				foreach($json as $client_unique_id => $client) {
					$new_json->$client_unique_id = array();
					foreach($client as $log) {
						if ((int) $log->date > (int) strtotime('-' . self::KEEP_JSON_LOGS_FOR)) { //clean the log entries that older than the specified period
							$new_json->{$client_unique_id}[] = $log;
						} else {
							//echo $log->date; //uncomment this line to see if some logs deleted while testing
						}
					}
				}
				//var_dump($json);
				$json = json_encode($new_json);
				file_put_contents($log_file, $json);
			}
		}
	}

	public static function call($service, $function, $params, $old = FALSE) {
		try {
			ini_set('default_socket_timeout', 600);
			if ($old) {
				ini_set('memory_limit', '1024M');
				$url = 'http://opt7dev.com/web-services/service/' . $service . '/index.php?wsdl';
			} else {
				$url = 'https://io.optimum7.com/ws/' . $service . '/index.php?wsdl';
			}
			$client = new \SoapClient($url, array( 'cache_wsdl' => WSDL_CACHE_NONE ));
			if (isset($client->fault) && $client->fault) {
				throw new Exception($client->getError());
			} else {
				return $client->__soapCall($function, array( $params ));
			}
		} catch(Exception $e) {
			$return = new stdClass();
			$return->error = $e->getMessage();
			$return->return = '';

			return $return;
		}
	}

	public static function debug($var, $type ='print', $exit = FALSE) {
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


}