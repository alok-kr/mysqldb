# mysqldb
A php class for accessing mysql database

Create a config.php for following string constants
```
$MySQL_Host
$MySQL_User
$MySQL_Pass
$MySQL_DataBase
```

###Class mysqldb
  
  - Functions
      1. connect()
      ```
           Connect to Database
           		Return true if connected
           		Return false if not connected and set errStr to error string
           
           @param string $database by default it is null and it uses config::MySQL_Database
           @return boolean
      ```
        
      2. runQuery()
		 
      ```
          Return false if any error occur and set errorStr to error string
      		Return integer, On any insert, update or delete query it returns number of affected rows.
      		Return array, On select query, array length is equal to number of rows return.
      			array () [ Object, Object, ... ]
      			Each Object contain property corresponds to column name in table
      
      
          @param string $qstr
          		Query String
          
          @param array $param [optional]
          		Parameter to pass for binding
          		if query does not any binding mark i.e. ?, then it is null and can be skipped.
          		else format of providing binding variable is
          			array ( "format string", &$var1, &$var2, ... )
          
          @return array|integer|boolean

      ```
