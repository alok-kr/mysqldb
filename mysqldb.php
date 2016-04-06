<?php

/**
 * This class contains function to connect and fire query to MySQL DataBase.
 * 
 * @author Alok Kumar
 * @version 0.2
 * 
 * Config.php Constant Vars
 * 
 * MySQL_Host
 * MySQL_User
 * MySQL_Pass
 * MySQL_DataBase
 * 
 */

if ( basename(__FILE__) == basename($_SERVER["SCRIPT_FILENAME"]) ) {
	include_once './../config.php';
	header('Location: '.config::websiteAddr);
	exit();
}

class mysqldb {
	public $conn = null;
	public $errorStr = null;
	public $database = null;
	
	/**
	 * Connect to database
	 * 
	 * @param string $database
	 */
	public function __construct ($database = config::MySQL_DataBase) {
		$this->database = $database; 
		$this->connect($database);
	}
	
	public function __sleep() {
		//mysqli_close($this->conn);
		return array('database');
	}
	
	public function __wakeup() {
		$this->connect();
	}
	
	public function __destruct() {
		mysqli_close($this->conn);
	}
	
	/**
	 * Connect to Database
	 * 		Return true if connected
	 * 		Return false if not connected and set errStr to error string
	 * 
	 * @param string $database by default it is null and it uses config::MySQL_Database
	 * @return boolean
	 */
	public function connect($database = null) {
		$this->errorStr = null;
		if($database==null)
			$database = $this->database;
		else
			$this->database = $database;
		
		try {
			if($this->conn)
				mysqli_close($this->conn);
			
			$this->conn = mysqli_connect(config::MySQL_Host, config::MySQL_User, config::MySQL_Pass);
			if(!($this->conn))
				throw new Exception("Invalid MySQL Host/User/Password Configuration");
			if(!mysqli_select_db($this->conn, $database))
				throw new Exception("Unknown Database '".$database."'.");
			$this->conn->autocommit(true);
			return true;
		}
		catch (Exception $e) {
			$this->errorStr = $e->getMessage();
	    }
	    return false;
	}
	
	/**
	 * Run Query
	 * 		 
	 * 		Return false if any error occur and set errorStr to error string
	 * 		Return integer, On any insert, update or delete query it returns number of affected rows.
	 * 		Return array, On select query, array length is equal to number of rows return.
	 * 			array () [ Object, Object, ... ]
	 * 			Each Object contain property corresponds to column name in table
	 * 
	 * 
	 * @param string $qstr
	 * 		Query String
	 * 
	 * @param array $param [optional]
	 * 		Parameter to pass for binding
	 * 		if query does not any binding mark i.e. ?, then it is null and can be skipped.
	 * 		else format of providing binding variable is
	 * 			array ( "format string", &$var1, &$var2, ... )
	 * 
	 * @return array|integer|boolean
	 */
	public function runQuery($qstr, $param = array()) {
		$this->errorStr = null;
		try {
			$stmt = $this->conn->prepare($qstr);
			if(!$stmt){
				throw new Exception("Query Error : ".$this->conn->error);
			}
			
			if($param)
				call_user_func_array(array($stmt, 'bind_param'), $param);
			
			if(!$stmt->execute()) {
				throw new Exception("Failed to execute query : ".$stmt->error);
			}
				
			if($stmt->affected_rows==-1) {
				
				/* Get Columns Names */
				$fieldNames = array();
				$resultMeta = $stmt->result_metadata();
				while($field=mysqli_fetch_field($resultMeta)) {
					$fieldNames[] = $field->name; 
				}
				
				/* Bind objects using stmt->bind_result() */
				$resultparam = array();
				for($i=0; $i<$stmt->field_count; $i++)
					$resultparam[] = &new stdClass;

				call_user_func_array(array($stmt, 'bind_result'), $resultparam);
				
				/* Create array of result objects */
				$result = array();
				while($stmt->fetch()) {
					$temp = array();
					foreach ($resultparam as $r)
						$temp[] = $r;
					$result[] = (Object)array_combine($fieldNames, $temp);
				}
				return $result;
			}
			else {
				$affRows = $stmt->affected_rows;
				$stmt->close();
				return $affRows;
			}
		}
		catch (Exception $e) {
			$this->errorStr = $e->getMessage();
	    }
	    return false;
		
	}
}
