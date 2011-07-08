<?php

/**
 * Chassis
 * 
 * NOTICE OF LICENSE 
 * 
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (Version 3)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to red331@mail.ru so we can send you a copy immediately.  
 * 
 * Class plcSQLiteDBConnector is a part of PHP framework - Chassis.   
 * 
 * @package     Chassis
 * @author      Selihov Sergei Stanislavovich <red331@mail.ru> 
 * @copyright   Copyright (c) 2010-2011 Selihov Sergei Stanislavovich.
 * @license     http://www.gnu.org/licenses/gpl.html  GNU GENERAL PUBLIC LICENSE (Version 3)
 *    
 */
 
/**
 * Classes for work with databases.
 *  
 * @subpackage DBConnectors
 * @author Selihov Sergei Stanislavovich <red331@mail.ru>   
 */
 
/**
 * Documents the plcSQLiteDBConnector class.
 * 
 * Following class is intended for simplification of tasks connected to the work with SQLite database. This class
 * utilizes facade design pattern to provide unified interface for PHP implementaion of SQLite extension. 
 *  
 *   
 * @subpackage plcSQLiteDBConnector
 * @author Selihov Sergei Stanislavovich <red331@mail.ru>   
 */
 
require_once('Chassis/ErrorHandling/plcChassisException.php');
require_once('Chassis/DBConnectors/Interfaces.php');

class plcSQLiteDBConnector implements pliSharedDBConnector, pliSmplSQLDBConnector, pliSmplSQLiteDBConnector 
	{		
  /**
   * @access protected
   * @var object instance of the current class 
   */		
  	
	protected static $CurInstance = NULL; 
	
  /**
   * @access protected
   * @var link SQLite link identifier 
   */	
	
	protected $DBLink = NULL;
  
  /**
   * @access protected
   * @var string path to current SQLite database
   */		
	
	protected $DBFilePath = NULL;
	
  /**
   * @access protected
   * @var string database file open mode (example: 'rw' - read/write mode, 'rc' - read/create mode)
   */			
	
	protected $CurOpenModeStr = 'c';
	
  /**
   * @access protected
   * @var mixed current encryption key used when encrypting and decrypting an SQLite database
   */		
	
	protected $CurEncryptionKey = NULL;	
  
  /**
   * @access protected
   * @var string current query to database 
   */	
	
	protected $Query = NULL;
  
  /**
   * @access protected
   * @var string last error number occured during query execution 
   */	

	protected $LastErrorNumber = 0;
	
  /**
   * @access protected
   * @var string last error text occured during query execution 
   */	
	
	protected $LastErrorText = '';

  /**
   * @access protected
   * @var int row count for current result set
   */	
  
  protected $ResultRowCount = 0;
 
  /**
   * @access protected
   * @var int field count for current result set
   */	 
  
  protected $ResultFieldCount = 0;
  
  /**
   * @access protected
   * @var int row pointer for current result set
   */	  
  
  protected $RowPointer = 0;
  
  /**
   * @access protected
   * @var int field pointer for current result set
   */	    

  protected $FieldPointer = 0; 
  
  /**
   * @access protected
   * @var resource for results sets used by iterator functions
   */	    
  
  protected $ResultRes = null;    	
		
	/* Core functions starts here */
	
	protected function __construct()
		{		
		}
		
  public function __destruct() 
		{
    $this->CloseDBLink();   
   	}
     
  /**
   * Function that used to connect to database.
   * 
   * It is a first function that needs to be called before work with database and execute queries.
   * 
   * @access public 
   *    
   * @param string name of the database file.
   * @param string database file open mode (example: "rwc", "rw").  
   * @param string database encryption key.
   * 
   * @throws plcChassisException         
   * 
   * @return bool returns TRUE on succesfully connection to database.
   *                         
   */ 	
   	
	public function ConnectToDB($usrFileName, $usrFlags = "c", $usrEncryptionKey = NULL)
    {  
    $tmpDBLink = FALSE; 
    $usrFlags = strtolower($usrFlags);
    $tmpErrorMessage = '';
    
    $this->CloseDBLink();
    
    if (empty($usrFileName) === TRUE || is_string($usrFileName) === FALSE)
      {
      throw new plcChassisException('Invalid file name.', 1301, null ,'User provided emtpy filename or it is not of string type.');
      }
    
    $this->DBFilePath = $usrFileName;    
    $this->SetOpenMode($usrFlags);
    $this->CurEncryptionKey = $usrEncryptionKey;
    
    $tmpDBLink = sqlite_open($usrFileName, 0666, $tmpErrorMessage);  
    
    if ($tmpDBLink === FALSE) 
      {
      throw new plcChassisException('Could not connect to database.', 1306, null ,'File name, file open mode or encryption key is incorrect ('.$tmpErrorMessage.').');
      }
    else
      {
      $this->DBLink = $tmpDBLink;
      }
		      
    return TRUE;	
    }
    
  /**
   * Function that used to reconnect to database.
   * 
   * Function is used to reconnect to database based on settings from previous call to ConnectToDB() function.
   * 
   * @access public 
   * 
   * @throws plcChassisException       
   *    
   * @return bool returns TRUE on succesfully reconnection to database.
   * 
   * @see plcSQLiteDBConnector::ConnectToDB() 
   *                              
   */ 
		
	public function ReConnectToDB()
    {
    $tmpErrorMessage = '';
    
    $this->CloseDBLink();   
    $this->SetOpenMode($this->CurOpenModeStr);
    
    $tmpDBLink = sqlite_open($this->DBFilePath, 0666, $tmpErrorMessage);  
    
    if ($tmpDBLink === FALSE) 
      {
      throw new plcChassisException('Could not connect to database.', 1306, null ,'File name, file open mode or encryption key is incorrect ('.$tmpErrorMessage.').');
      }
    else
      {
      $this->DBLink = $tmpDBLink;
      }
		      
    return TRUE;	    	  
    }
    
  /**
   * Function that used to execute user defined SQL query.
   * 
   * Mainly used to execute queries such as INSERT or UPDATE. For queries that returns result please use other functions.
   * Note that you must first call ConnectToDB() and  SetDatabase() functions.
   * 
   * @access public 
   * 
   * @throws plcChassisException      
   *    
   * @return bool returns TRUE on succesfull execution of the query.
   * 
   * @see plcSQLiteDBConnector::ConnectToDB()                             
   */ 

	public function ExecuteQuery()
		{
		$this->ResultRowCount = 0;
		$this->ResultFieldCount = 0;
		
		$tmpResult = $this->GetResource();
		
		if ($tmpResult !== FALSE)
		  {
		  return TRUE;
      }
    else
      {
      $this->LastErrorNumber = sqlite_last_error($this->DBLink);
			$this->LastErrorText = sqlite_error_string($this->LastErrorNumber);
			
			throw new plcChassisException('Error while executing query.', 1309, null ,'SQL error:'.$this->LastErrorNumber.' - '.$this->LastErrorText);
      }
		}    
    
  /**
   * Function that used to close connection to database.
   * 
   * Simple function that closes connection with database and free resources for SQLite prepared statemen object.
   * Note that this function will be automaticly called inside destructor function.
   * 
   * @access public 
   *    
   * @return bool returns TRUE if function successfuly close connection to database, FALSE if connection to database was not yet established.
   * 
   * @see plcSQLiteDBConnector::ConnectToDB()  
   * @see plcSQLiteDBConnector::ReConnectToDB()                             
   */ 

	public function CloseDBLink()
		{
		if ($this->DBLink != NULL)
		  {
		  sqlite_close($this->DBLink);
		  $this->DBLink = NULL;
		  return TRUE;
      }
    else
      {
      return FALSE;
      }
		}
    
  /**
   * Function that used to escape special characters in user defined string.
   * 
   * Simple function that escapes special characters in user defined string (SQL query) to prevent SQL-injection attack.
   * 
   * @access public 
   * 
   * @throws plcChassisException         
   *    
   * @return string returns escaped string on success.
   *                              
   */ 
		
	public function EscapeString($usrString)
    {            
    return sqlite_escape_string($usrString); 
    }  
	
	/* Core functions ends here */
	
  /* Get functions starts here */
  
  /**
   * Function that used to return current instance of the current class (Singleton).
   * 
   * Simple function that current instance of the current class (Singleton). Mainly used for shared database connection.
   * 
   * @access public 
   *    
   * @return object returns current instance of the current class. 
   *                               
   */   
  
  public static function GetInstance()
    {
    if (self::$CurInstance === NULL) 
      {
      self::$CurInstance = new self();
      }
      
    return self::$CurInstance;    
    }  
  
  
  /**
   * Function that used to return new instance of the current class.
   * 
   * Simple function that new instance of the current class. Basicly acts as a constructor function, if you do not need shared
   * database connection and in need of new one.   
   * 
   * @access public 
   *    
   * @return object returns new instance of the current class.  
   *                              
   */   
    
  public static function GetNewInstance()
    {
    return new self();
    }
    
  /**
   * Function that used to return current query to database.
   * 
   * Simple function that returns current query to database.
   * 
   * @access public 
   *    
   * @return string|NULL returns current query to database and NULL if database query was not yet set.                            
   */ 

	public function GetQuery()
		{
		return $this->Query;
		} 
		
  /**
   * Function that used to return ID generated for an AUTO_INCREMENT column by the previous query.
   * 
   * Simple function that returns ID generated for an AUTO_INCREMENT column by the previous query.
   * 
   * @access public 
   * 
   * @throws plcChassisException      
   *    
   * @return int|Bool returns ID  for an AUTO_INCREMENT column by the previous query on succes and FALSE if ID was not generated.                            
   */ 
		
	public function GetLastInsertId()
    {  
    $tmpResult = null;
    
		if ($this->DBLink == NULL)
			{
			throw new plcChassisException('Connection to data base does not exist.', 1307, null ,'Connection to data base does not exist or wrong server.');
			} 
      
    $tmpResult = sqlite_last_insert_rowid($this->DBLink);
    
    if ($tmpResult === FALSE || $tmpResult == 0)
      {
      return FALSE;
      }   
    else
      {
      return $tmpResult; 
      }
    }
    
  /**
   * Function that used to return number of rows in result set (for SELECT and SHOW statements).
   * 
   * Simple function that returns number of rows in result set (for SELECT and SHOW statements).
   * 
   * @access public     
   *    
   * @return int returns number of rows in result set.   
   * 
   * @see plcSQLiteDBConnector::GetResultAssoc()   
   * @see plcSQLiteDBConnector::GetResultRow()   
   * @see plcSQLiteDBConnector::GetResult()    
   *                                  
   */ 
    
	public function GetNumRows()
    {
    return $this->ResultRowCount;
    }
    
  /**
   * Function that used to return number of fields in result set (for SELECT and SHOW statements).
   * 
   * Simple function that returns number of fields in result set (for SELECT and SHOW statements).
   * 
   * @access public     
   *    
   * @return int returns number of fields in result set.   
   * 
   * @see plcSQLiteDBConnector::GetResultAssoc()   
   * @see plcSQLiteDBConnector::GetResultRow()   
   * @see plcSQLiteDBConnector::GetResult()    
   *                                  
   */ 
    
  public function GetNumFields()
    {
    return $this->ResultFieldCount;
    }
    
  /**
   * Function that used to get query results in form of associative array.
   * 
   * Simple function that returns results of the current query in form of associative array. First index form the array is numerical
   * which represents row from the database, second index represents column name.
   * Note that you do not need to call ExecuteQuery() function before calling GetResultAssoc(). You only need to set query
   * using SetQuery() function.        
   * 
   * @access public 
   * 
   * @throws plcChassisException      
   *    
   * @return array|bool returns associative array on succes and FALSE if there is nothing to return.  
   * 
   * @see plcSQLiteDBConnector::SetQuery()                                  
   */ 

	public function GetResultAssoc()
		{
		$tmpResource = FALSE;
    $tmpResource = $this->GetResource();
    
    $tmpNumRows = 0;
                                  
    $this->ResultRowCount = 0;
    $this->ResultFieldCount = 0;

		if ($tmpResource === TRUE)
			{
			throw new plcChassisException('Can not return result for this type of query.', 1310, null , 'The function is not supporting such query type.');
			}
		else if ($tmpResource === FALSE)
		  {
		  $this->LastErrorNumber = sqlite_last_error($this->DBLink);
			$this->LastErrorText = sqlite_error_string($this->LastErrorNumber);
			
			throw new plcChassisException('Error while executing query.', 1309, null ,'SQL error:'.$this->LastErrorNumber.' - '.$this->LastErrorText);
      }
		else
			{
			$tmpNumRows = sqlite_num_rows($tmpResource);
			
			if ($tmpNumRows === FALSE || $tmpNumRows == 0)
        {
        $this->ResultRowCount = 0;   
        $this->ResultFieldCount = 0;
            
        return FALSE;
        }
      else
        {
        $this->ResultRowCount = $tmpNumRows;
        $this->ResultFieldCount = sqlite_num_fields($tmpResource);
        
        return sqlite_fetch_all($tmpResource, SQLITE_ASSOC);     
        }	
			}
		}
		
  /**
   * Function that used to return simple row result from the query.
   * 
   * Simple function that returns simple row results from user defined query in a form of associative.
   * Note that you do not need to call ExecuteQuery() function before calling GetResultRow(). You only need to set query
   * using SetQuery() function.       
   * 
   * @access public 
   * 
   * @throws plcChassisException       
   *    
   * @return array|bool returns result array on succes and FALSE if there is nothing to return. 
   *
   * @see plcSQLiteDBConnector::SetQuery()                               
   */ 
		
	public function GetResultRow()
    {
		$tmpResource = FALSE;
    $tmpResource = $this->GetResource();
    
    $tmpNumRows = 0;
                                  
    $this->ResultRowCount = 0;
    $this->ResultFieldCount = 0;

		if ($tmpResource === TRUE)
			{
			throw new plcChassisException('Can not return result for this type of query.', 1310, null , 'The function is not supporting such query type.');
			}
		else if ($tmpResource === FALSE)
		  {
		  $this->LastErrorNumber = sqlite_last_error($this->DBLink);
			$this->LastErrorText = sqlite_error_string($this->LastErrorNumber);
			
			throw new plcChassisException('Error while executing query.', 1309, null ,'SQL error:'.$this->LastErrorNumber.' - '.$this->LastErrorText);
      }
		else
			{
			$tmpNumRows = sqlite_num_rows($tmpResource);
			
			if ($tmpNumRows === FALSE || $tmpNumRows == 0)
        {
        $this->ResultRowCount = 0;   
        $this->ResultFieldCount = 0;
            
        return FALSE;
        }
      else
        {
        $this->ResultRowCount = $tmpNumRows;
        $this->ResultFieldCount = sqlite_num_fields($tmpResource);
        
        return sqlite_fetch_array($tmpResource, SQLITE_ASSOC);     
        }	
			}
    }
    
  /**
   * Function that used to return simple column result from the query.
   * 
   * Simple function that returns simple colum results from user defined query.
   * Note that you do not need to call ExecuteQuery() function before calling GetResult(). You only need to set query
   * using SetQuery() function.        
   * 
   * @access public 
   * 
   * @throws plcChassisException       
   *    
   * @return mixed|bool returns result value on succes and FALSE if there is nothing to return. 
   *
   * @see plcSQLiteDBConnector::SetQuery()                               
   */ 

	public function GetResult()
		{
		$tmpResult = array();
		$tmpResource = FALSE;
    $tmpResource = $this->GetResource();
    
    $tmpNumRows = 0;
                                  
    $this->ResultRowCount = 0;
    $this->ResultFieldCount = 0;

		if ($tmpResource === TRUE)
			{
			throw new plcChassisException('Can not return result for this type of query.', 1310, null , 'The function is not supporting such query type.');
			}
		else if ($tmpResource === FALSE)
		  {
		  $this->LastErrorNumber = sqlite_last_error($this->DBLink);
			$this->LastErrorText = sqlite_error_string($this->LastErrorNumber);
			
			throw new plcChassisException('Error while executing query.', 1309, null ,'SQL error:'.$this->LastErrorNumber.' - '.$this->LastErrorText);
      }
		else
			{
			$tmpNumRows = sqlite_num_rows($tmpResource);
			
			if ($tmpNumRows === FALSE || $tmpNumRows == 0)
        {
        $this->ResultRowCount = 0;   
        $this->ResultFieldCount = 0;
            
        return FALSE;
        }
      else
        {
        $this->ResultRowCount = $tmpNumRows;
        $this->ResultFieldCount = sqlite_num_fields($tmpResource);
        
        $tmpResult = sqlite_fetch_array($tmpResource, SQLITE_NUM);
        
        return $tmpResult[0];     
        }	
			}			
    }		
    
  /**
   * Function that used to return resource for the current query.
   * 
   * Simple function that executes current query and returns the resource for the current query.
   * 
   * @access protected
   * 
   * @throws plcChassisException         
   *    
   * @return bool|resource returns TRUE for successfull execution of such queries as INSERT, UPDATE, DELETE, DROP and 
   * resource for such queries as SELECT, SHOW, DESCRIBE, EXPLAIN.                            
   */ 
		
  protected function GetResource()
    {
		$this->ResultRowCount = 0;
		$this->ResultFieldCount = 0;

		if ($this->Query == NULL)
			{
			throw new plcChassisException('Query to data base is not set.', 1308, null ,'Query to data base must be set before calling any function for data retrieving.');
			}

		if ($this->DBLink == NULL)
			{
			throw new plcChassisException('Connection to data base does not exist.', 1307, null ,'Connection to data base does not exist or wrong server.');
			}

		return @sqlite_query($this->Query, $this->DBLink);
    } 
    
  /**
   * Function that used to return path to database file.
   * 
   * Simple function that used to return path to database file.
   * 
   * @access public
   *           
   * @return bool|string returns FALSE if database file was not yet set and path to database file on success. 
   *                             
   */  
    
  public function GetDBFile()
    {
    if (is_null($this->DBFilePath) === TRUE)
      {
      return FALSE;
      }
    else
      {
      return $this->DBFilePath;
      } 
    }
    
  /**
   * Function that used to return database file open mode.
   * 
   * Simple function that used to return database file open mode.
   * 
   * @access public
   *                                    
   * @return string returns database file open mode (example: 'rw' - read/write mode, 'rc' - read/create mode). 
   *                             
   */     
    
  public function GetOpenMode()
    {
    return $this->CurOpenModeStr;
    }
    
  /**
   * Function that used to return current encryption key used when encrypting and decrypting an SQLite database.
   * 
   * Simple function that used to return current encryption key used when encrypting and decrypting an SQLite database.
   * 
   * @access public
   *                                    
   * @return smixed returns current encryption key used when encrypting and decrypting an SQLite database. 
   *                             
   */     
     
  public function GetCurEncKey()   
    {
    return $this->CurEncryptionKey; 
    }   
        
  /* Get functions ends here */
  
  /* Set functions starts here */
  
  /**
   * Function that used to set path to a database file.
   * 
   * Simple function that sets (changes) path to a database file. Note that you can not change database file while connection to database file is open. 
   * 
   * @access public
   * 
   * @param string path to a database file      
   * 
   * @throws plcChassisException      
   *    
   * @return bool returns TRUE on succes. 
   *                             
   */   
  
  public function SetDBFile($usrDBFilePath)
    {
    if (is_null($this->DBLink) !== TRUE)
      {
      throw new plcChassisException('Can not change database file.', 1312, null ,'Connection to database is already open, can not change database file.');
      }    
    
    if (empty($usrDBFilePath) === TRUE || is_string($usrDBFilePath) === FALSE)
      {
      throw new plcChassisException('Invalid file name.', 1301, null ,'User provided emtpy filename or it is not of string type.');
      } 
      
    $this->DBFilePath = $usrDBFilePath;   
    $this->SetOpenMode($this->CurOpenModeStr); 
    
    return TRUE;  
    }  
  
  /**
   * Function that used to set open mode for current database file.
   * 
   * Simple function that sets (changes) open mode for current database file. Note that you can not change open mode while connection to database file is open. 
   * 
   * @access public
   * 
   * @param string new database file open mode (example: "rwc", "rw")      
   * 
   * @throws plcChassisException      
   *    
   * @return bool returns TRUE on succes. 
   *                             
   */ 
  
	public function SetOpenMode ($usrFlags = "c")
    { 
    $usrFlags = strtolower($usrFlags);
    
    if (is_null($this->DBLink) !== TRUE)
      {
      throw new plcChassisException('Can not change database open mode.', 1311, null ,'Connection to database is already open, can not change open mode.');
      }
   
    if (strpos($usrFlags, 'rw') !== FALSE)
      {
      if (strpos($usrFlags, 'c') === FALSE)
        {
        if ($this->DBFilePath == NULL)
          {
          throw new plcChassisException('Database file name is not set yet.', 1302, null ,'You must first set database file before feature actions.');
          }
        
        if (file_exists($this->DBFilePath) === FALSE)
          {
          throw new plcChassisException('Database file is not exist.', 1303, null ,'Database file: "'.$this->DBFilePath.'" does not exist.');
          }
              
        if (is_readable($this->DBFilePath) !== TRUE)
          {
          throw new plcChassisException('Database file is not readable.', 1304, null ,'Database file: "'.$this->DBFilePath.'" is not readable.');
          }
          
        if (is_writable($this->DBFilePath) !== TRUE)
          {
          throw new plcChassisException('Database file is not writable.', 1305, null ,'Database file: "'.$this->DBFilePath.'" is not writable.');
          }                    
        }
      }
    else if (strpos($usrFlags, 'r') !== FALSE)
      {
      if (strpos($usrFlags, 'c') === FALSE)
        { 
        if ($this->DBFilePath == NULL)
          {
          throw new plcChassisException('Database file name is not set yet.', 1302, null ,'You must first set database file before feature actions.');
          }
        
        if (file_exists($this->DBFilePath) === FALSE)
          {
          throw new plcChassisException('Database file is not exist.', 1303, null ,'Database file: "'.$this->DBFilePath.'" does not exist.');
          }
              
        if (is_readable($this->DBFilePath) !== TRUE)
          {
          throw new plcChassisException('Database file is not readable.', 1304, null ,'Database file: "'.$this->DBFilePath.'" is not readable.');
          }
        }
      }
    else if (strpos($usrFlags, 'c') !== FALSE)
      {
      $usrFlags = "c";
      }
    else
      {
      $usrFlags = "c";
      }
       
    $this->CurOpenModeStr = $usrFlags;
    return TRUE;
    }
    
  /**
   * Function that used to set current encryption key.
   * 
   * Simple function that sets (changes) current encryption key used when encrypting and decrypting an SQLite database. 
   * 
   * @access public
   * 
   * @param mixed new encryption key      
   *                                   
   */ 
    
  public function SetCurEncKey($usrEncryptionKey) 
    {
    $this->CurEncryptionKey = $usrEncryptionKey; 
    }    
    
  /**
   * Function that used to set current query.
   * 
   * Simple function that sets current query to the database. 
   * 
   * @access public 
   *
   * @param string new query to the database    
   *                                 
   */ 

	public function SetQuery($usrQuery)
		{
		$this->Query = $usrQuery;
		}   
  
  /* Set functions ends here */
	} 
	
?>