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
 * Class plcMySQLDBConnector is a part of PHP framework - Chassis.   
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
 * Documents the plcMySQLDBConnector class.
 * 
 * Following class is intended for simplification of tasks connected to the work with MYSQL database.
 *   
 * @subpackage plcMySQLDBConnector
 * @author Selihov Sergei Stanislavovich <red331@mail.ru>   
 */
 
require_once('Chassis/ErrorHandling/plcChassisException.php');
require_once('Chassis/DBConnectors/Interfaces.php');

class plcMySQLDBConnector implements pliSharedDBConnector, pliSmplSQLDBConnector, pliSmplMYSQLDBConnector, pliSQLBufferedResultIterator
	{	
  /**
   * @access protected
   * @var object instance of the current class 
   */		
  	
	protected static $CurInstance = NULL; 	
	
  /**
   * @access protected
   * @var link MySQL link identifier 
   */	
	
	protected $DBLink = NULL;
	
  /**
   * @access protected
   * @var string database server name 
   */	
	
	protected $DBServerName = NULL;
	
  /**
   * @access protected
   * @var string database user name 
   */	

	protected $DBUserName = NULL;
	
  /**
   * @access protected
   * @var string database user password 
   */	
	
	protected $DBPassword = NULL;
	
  /**
   * @access protected
   * @var string database name 
   */	

	protected $DBName = NULL;
	
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
   * @param string name of the database server.
   * @param string name of the database user.  
   * @param string name of the database user password.
   * 
   * @throws plcChassisException         
   * 
   * @return bool returns TRUE on succesfully connection to database.
   *                         
   */  

	public function ConnectToDB($usrServer, $usrUser, $usrPassword)
		{
		$tmpDBLink = FALSE;
		
		$this->CloseDBLink();

		if (empty($usrServer))
			{
			throw new plcChassisException('User does not provided server name.', 1101, null ,'Cannot connect to unknown server.');
			}

		if (empty($usrUser))
			{
			throw new plcChassisException('User does not provided user name.', 1102, null ,'Cannot connect to data base without specified user.');
			}

		if (empty($usrPassword))
			{
			throw new plcChassisException('User does not provided password.', 1103, null ,'Cannot connect to data base without specified password.');
			}
		
		$this->DBServerName = $usrServer;
		$this->DBUserName = $usrUser;
		$this->DBPassword = $usrPassword;
	
		$tmpDBLink = @mysql_connect($usrServer, $usrUser, $usrPassword, true);

		if ($tmpDBLink === FALSE)
			{
			throw new plcChassisException('Could not connect to database.', 1108, null ,'Server name, user name or password is incorrect.');
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
   * @see plcMySQLDBConnector::ConnectToDB() 
   *                              
   */ 
		
	public function ReConnectToDB()
    {
    $this->CloseDBLink();
    	
		$tmpDBLink = @mysql_connect($this->DBServerName, $this->DBUserName, $this->DBPassword);

		if ($tmpDBLink === FALSE)
			{
			throw new plcChassisException('Could not connect to database.', 1108, null ,'Server name, user name or password is incorrect.');
			}
		else
			{
			$this->DBLink = $tmpDBLink;
			}	
     
    return $this->SetDatabase($this->DBName);    
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
   * @see plcMySQLDBConnector::ConnectToDB()  
   * @see plcMySQLDBConnector::SetDatabase()                             
   */ 

	public function ExecuteQuery()
		{
		$this->ResultRowCount = 0;
		$this->ResultFieldCount = 0;
		
		$tmpResult = $this->GetResource();

		if ($tmpResult !== FALSE && $tmpResult !== TRUE)
			{
			mysql_free_result($tmpResult);
			
			return TRUE;
			}
		else if ($tmpResult !== FALSE && $tmpResult === TRUE)
		  {
		  return TRUE;
      }
		else
			{
			$this->LastErrorNumber = mysql_errno($this->DBLink);
			$this->LastErrorText = mysql_error($this->DBLink);
			
			throw new plcChassisException('Error while executing query.', 1105, null ,'SQL error:'.$this->LastErrorNumber.' - '.$this->LastErrorText);
			}
		}
		
  /**
   * Function that used to close connection to database.
   * 
   * Simple function that closes connection with database.
   * Note that this function will be automaticly called inside destructor function.
   * 
   * @access public 
   *    
   * @return bool returns TRUE if function successfuly close connection to database, FALSE if connection to database was not yet established.
   * 
   * @see plcMySQLDBConnector::ConnectToDB()  
   * @see plcMySQLDBConnector::ReConnectToDB()                             
   */ 

	public function CloseDBLink()
		{
		$this->ResultRowCount = 0;
		$this->ResultFieldCount = 0;
		
		if ($this->DBLink != NULL)
			{
			@mysql_close($this->DBLink);
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
   * Note that this function will work if you already connected to database.
   * 
   * @access public 
   * 
   * @throws plcChassisException         
   *    
   * @return string returns escaped string on success.
   * 
   * @see plcMySQLDBConnector::ConnectToDB()  
   * @see plcMySQLDBConnector::ReConnectToDB()                             
   */ 
		
	public function EscapeString($usrString)
    {   
    $tmpResult = '';
     
		if ($this->DBLink == NULL)
			{
			throw new plcChassisException('Connection to data base does not exist.', 1109, null ,'Connection to data base does not exist or wrong server.');
			}
    
    $tmpResult = mysql_real_escape_string($usrString, $this->DBLink);
    
    if($tmpResult === FALSE)
      {
      throw new plcChassisException('Can not escape user string.', 1107, null ,'User string is ither empty or invalid.');
      }
      
    return $tmpResult; 
    }
    
  /**
   * Function that used to reset data pointer for iterator functions.
   * 
   * Simple function that resets data pointer for iterator functions. All next calls to such functions as NextRow() or PrevRow()
   * will start at the first index of the returned result.   
   * 
   * @access public 
   *  
   * @see plcMySQLDBConnector::LoadResult()          
   * @see plcMySQLDBConnector::NextRow()  
   * @see plcMySQLDBConnector::PrevRow()
   *                                 
   */ 
       
  public function ResetDataPointer()
    {
    $this->RowPointer = 0;
    $this->FieldPointer = 0; 
    }
    
  /**
   * Function that frees memory used by result set returned by previous call to LoadResult() method. Note that you only need to 
   * call this functions if you use functions from plcSQLBufferedResultIterator interface, so you do not need to call this
   * method after your call such function as GetResultAssoc().       
   * 
   * Simple function that resets data pointer for iterator functions. All next calls to such functions as NextRow() or PrevRow()
   * will start at the first index of the returned result.  
   * 
   * @access public 
   *  
   * @see plcMySQLDBConnector::LoadResult()          
   * @see plcMySQLDBConnector::NextRow()  
   * @see plcMySQLDBConnector::PrevRow()
   *                                 
   */ 
    
  public function FreeResult()
    {
    $this->ResetDataPointer();
    if (is_null($this->ResultRes) !== TRUE){mysql_free_result($this->ResultRes); $this->ResultRes = null;} 
    }
      
  /**
   * Function that used to load query results set and buffer them for later use.
   * 
   * Simple function that load results set of the current query and buffer it for later use. 
   * Note that you do not need to call ExecuteQuery() function before calling LoadResult(). You only need to set query
   * using SetQuery() function.        
   * 
   * @access public 
   * 
   * @throws plcChassisException      
   *    
   * @return bool returns TRUE on succes and FALSE if there were no result set to buffer.  
   * 
   * @see plcMySQLDBConnector::SetQuery()  
   *                                    
   */ 
        
  public function LoadResult()
    {  
    $this->FreeResult();
    
    $tmpResource = FALSE;
    $tmpResource = $this->GetResource();
    
    $tmpNumRows = 0;

    $this->ResultRowCount = 0;
    $this->ResultFieldCount = 0;

		if ($tmpResource === TRUE)
			{
			throw new plcChassisException('Can not return result for this type of query.', 1110, null ,'The function is not supporting such query type.');
			}
		else if ($tmpResource === FALSE)
		  {
			$this->LastErrorNumber = mysql_errno($this->DBLink);
			$this->LastErrorText = mysql_error($this->DBLink);
			
			throw new plcChassisException('Error while executing query.', 1105, null ,'SQL error:'.$this->LastErrorNumber.' - '.$this->LastErrorText);		  
      }
		else
			{
			$tmpNumRows = mysql_num_rows($tmpResource);
			
			if ($tmpNumRows === FALSE || $tmpNumRows == 0)
        {
        $this->ResultRowCount = 0; 
        $this->ResultFieldCount = 0;     
         
        return FALSE;
        }
      else
        {
        $this->ResultRowCount = $tmpNumRows;
        $this->ResultFieldCount = mysql_num_fields($tmpResource);
        $this->ResultRes = $tmpResource;

        return TRUE;     
        }	
			}   
    }
    
  /**
   * Function that used to indicates whethere data pointer is set on last row of buffered result set or not.
   * 
   * Simple function that is used to indicate whethere data pointer is set on last row of buffered result set or not.
   * Note that you need to load result set into buffer using LoadResult() function if it is not done already.        
   * 
   * @access public  
   *    
   * @return bool returns TRUE on succes and FALSE on fail.  
   * 
   * @see plcMySQLDBConnector::LoadResult()  
   * @see plcMySQLDBConnector::NextRow()
   * @see plcMySQLDBConnector::PrevRow()         
   *                                    
   */ 
    
  public function IsHasNextRow()
    {
    if (is_null($this->ResultRes) === TRUE || (($this->RowPointer + 1) >= $this->ResultRowCount))
      {
      return FALSE;
      }
    else
      {
      return TRUE;
      }
    }
    
  /**
   * Function that used to indicates whethere field pointer is set on last field of buffered result row or not.
   * 
   * Simple function that is used to indicate whethere field pointer is set on last field of buffered result row or not.
   * Note that you need to load result set into buffer using LoadResult() function if it is not done already.        
   * 
   * @access public  
   *    
   * @return bool returns TRUE on succes and FALSE on fail.  
   * 
   * @see plcMySQLDBConnector::LoadResult()  
   * @see plcMySQLDBConnector::NextField()
   * @see plcMySQLDBConnector::PrevField()         
   *                                    
   */ 
    
  public function IsHasNextField()
    {
    if (is_null($this->ResultRes) === TRUE || (($this->FieldPointer + 1) >= $this->ResultFieldCount))
      {
      return FALSE;
      }
    else
      {
      return TRUE;
      }    
    }
    
  /**
   * Function that moves data pointer forward in result set.
   * 
   * Simple function that moves data pointer forward in result set.
   * 
   * @access public 
   *    
   * @return TRUE on succes and FALSE if data pointer can not be moved.    
   *                            
   */ 
    
  public function NextRow()
    {
    $this->FieldPointer = 0;
    
    if (($this->RowPointer + 1) >= $this->ResultRowCount)
      {
      return FALSE;
      }
    else
      {
      if (is_null($this->ResultRes) === TRUE){throw new plcChassisException('Can not move data pointer to next/prev row.', 1113, null ,'Can not move data pointer - result set is not buffered yet.');} 
      $this->RowPointer = $this->RowPointer + 1;
      mysql_data_seek($this->ResultRes, $this->RowPointer);
      
      return TRUE;
      }
    }
    
  /**
   * Function that moves data pointer backword in result set.
   * 
   * Simple function that moves data pointer backword in result set.
   * 
   * @access public 
   *    
   * @return TRUE on succes and FALSE if data pointer can not be moved.    
   *                            
   */ 
    
  public function PrevRow()
    {
    $this->FieldPointer = 0;
    
    if (($this->RowPointer - 1) < 0)
      {
      return FALSE;
      }
    else
      {
      if (is_null($this->ResultRes) === TRUE){throw new plcChassisException('Can not move data pointer to next/prev row.', 1113, null ,'Can not move data pointer - result set is not buffered yet.');}           
      $this->RowPointer = $this->RowPointer - 1;
      mysql_data_seek($this->ResultRes, $this->RowPointer);
      
      return TRUE;
      }    
    }
    
  /**
   * Function that moves field pointer forward in the current row.
   * 
   * Simple function that moves field pointer forward in the current row.
   * 
   * @access public 
   *    
   * @return TRUE on succes and FALSE if field pointer can not be moved.    
   *                            
   */ 
    
  public function NextField()
    {
    if (($this->FieldPointer + 1) >= $this->ResultFieldCount)
      {
      return FALSE;
      }
    else
      {
      if (is_null($this->ResultRes) === TRUE){throw new plcChassisException('Can not move field pointer to next/prev field.', 1115, null ,'Can not move field pointer - result set is not buffered yet.');} 
      $this->FieldPointer = $this->FieldPointer + 1;
      mysql_field_seek($this->ResultRes, $this->FieldPointer);
      
      return TRUE;
      } 
    }
    
  /**
   * Function that moves field pointer backword in result set.
   * 
   * Simple function that moves field pointer backword in result set.
   * 
   * @access public 
   *    
   * @return TRUE on succes and FALSE if field pointer can not be moved.    
   *                            
   */ 
    
  public function PrevField()
    {
    if (($this->FieldPointer - 1) < 0)
      {
      return FALSE;
      }
    else
      {
      if (is_null($this->ResultRes) === TRUE){throw new plcChassisException('Can not move field pointer to next/prev field.', 1115, null ,'Can not move field pointer - result set is not buffered yet.');}          
      $this->FieldPointer = $this->FieldPointer - 1;
      mysql_field_seek($this->ResultRes, $this->FieldPointer);
      
      return TRUE;
      }  
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
   */   
    
  public static function GetNewInstance()
    {
    return new self();
    }
  
  /**
   * Function that used to return database host that was set previously.
   * 
   * Simple function that returns host name.
   * Note that this function will only work if you set host name previously.
   * 
   * @access public 
   *    
   * @return string|NULL returns current host name and NULL if host name was not yet set.                            
   */ 
    
  public function GetHost()
    {
    return $this->DBServerName;
    } 
    
  /**
   * Function that used to return database current user that was set previously.
   * 
   * Simple function that returns database current user.
   * Note that this function will only work if you set database user previously.
   * 
   * @access public 
   *    
   * @return string|NULL returns current database user and NULL if host name was not yet set.                            
   */ 
    
  public function GetUser()
    {
    return $this->DBUserName;
    }
    
  /**
   * Function that used to return database current user password that was set previously.
   * 
   * Simple function that returns database current user password.
   * Note that this function will only work if you set database user password previously.
   * 
   * @access public 
   *    
   * @return string|NULL returns current database user and NULL if host name was not yet set.                            
   */ 
    
  public function GetPassword()
    {
    return $this->DBPassword;
    }
  
  /**
   * Function that used to return database name that was set previously.
   * 
   * Simple function that returns database name.
   * Note that this function will only work if you set database name previously.
   * 
   * @access public 
   *    
   * @return string|NULL returns current database name and NULL if database name was not yet set.                            
   */ 

	public function GetDatabaseName()
		{
		return $this->DBName;
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
			throw new plcChassisException('Connection to data base does not exist.', 1109, null ,'Connection to data base does not exist or wrong server.');
			} 
      
    $tmpResult = mysql_insert_id($this->DBLink);
    
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
   * @see plcMySQLDBConnector::GetResultAssoc()   
   * @see plcMySQLDBConnector::GetResultRow()   
   * @see plcMySQLDBConnector::GetResult()    
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
   * @see plcMySQLDBConnector::GetResultAssoc()   
   * @see plcMySQLDBConnector::GetResultRow()   
   * @see plcMySQLDBConnector::GetResult()    
   *                                  
   */ 
    
  public function GetNumFields()
    {
    return $this->ResultFieldCount;
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
			throw new plcChassisException('Query to data base is not set.', 1104, null ,'Query to data base must be set before calling any function for data retrieving.');
			}

		if ($this->DBLink == NULL)
			{
			throw new plcChassisException('Connection to data base does not exist.', 1109, null ,'Connection to data base does not exist or wrong server.');
			}

		return mysql_query($this->Query, $this->DBLink);
    }
    
  /**
   * Function that used to return value of autocommit server variable.
   *       
   * Simple function that is used to return value of autocommit server variable. This variable shows whether changes made to
   * the database commits instantly or after calling appropriate command.     
   *   
   * @access public 
   * 
   * @throws plcChassisException        
   *    
   * @return int returns integer value on success (1 - autocommit is on, 0 - off).  
   *                                 
   */ 
    
  public function GetAutoCommitMode()
    {
		$tmpResult = FALSE;
		
		$this->SetQuery('SHOW VARIABLES LIKE "autocommit"');
    $tmpResult = $this->GetResultRow(); 
    
    if ($tmpResult !== FALSE)
      {
      return $tmpResult['Value'];
      }
    else
      {
      $this->SetQuery('SELECT @@autocommit');	
      $tmpResult = $this->GetResult();
      
      if ($tmpResult === FALSE)
        {
        throw new plcChassisException("'autocommit' server variable does not exist.", 1106, null , "SQL server has no 'autocommit' variable.");
        } 
      else
        {
        return $tmpResult;
        } 
      }
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
   * @see plcMySQLDBConnector::SetQuery()                                  
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
			throw new plcChassisException('Can not return result for this type of query.', 1110, null ,'The function is not supporting such query type.');
			}
		else if ($tmpResource === FALSE)
		  {
			$this->LastErrorNumber = mysql_errno($this->DBLink);
			$this->LastErrorText = mysql_error($this->DBLink);
			
			throw new plcChassisException('Error while executing query.', 1105, null ,'SQL error:'.$this->LastErrorNumber.' - '.$this->LastErrorText);
      }
		else
			{
			$tmpNumRows = mysql_num_rows($tmpResource);
			
			if ($tmpNumRows === FALSE || $tmpNumRows == 0)
        {
        $this->ResultRowCount = 0;   
        $this->ResultFieldCount = 0;
            
        return FALSE;
        }
      else
        {
        $this->ResultRowCount = $tmpNumRows;
        $this->ResultFieldCount = mysql_num_fields($tmpResource);
        
        $tmpAssocArray = array();
        $tmpRow = NULL; 

        $Counter1 = 0;
       
        while ($tmpRow = mysql_fetch_assoc($tmpResource)) 
          { 
          $tmpAssocArray[$Counter1] = $tmpRow;

          $Counter1 = $Counter1 + 1;
          }

        mysql_free_result($tmpResource);

        if(count($tmpAssocArray) < 1)
          {
          return FALSE;
          } 

        return $tmpAssocArray;     
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
   * @see plcMySQLDBConnector::SetQuery()                               
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
			throw new plcChassisException('Can not return result for this type of query.', 1110, null ,'The function is not supporting such query type.');
			}
		else if ($tmpResource === FALSE)
		  {
			$this->LastErrorNumber = mysql_errno($this->DBLink);
			$this->LastErrorText = mysql_error($this->DBLink);
			
			throw new plcChassisException('Error while executing query.', 1105, null ,'SQL error:'.$this->LastErrorNumber.' - '.$this->LastErrorText);
      }
		else
			{
			$tmpNumRows = mysql_num_rows($tmpResource);
			
			if ($tmpNumRows === FALSE || $tmpNumRows == 0)
        {
        $this->ResultRowCount = 0;
        $this->ResultFieldCount = 0;
        
        return FALSE;
        }
      else
        {
        $this->ResultRowCount = $tmpNumRows;
        $this->ResultFieldCount = mysql_num_fields($tmpResource);
        
        $tmpRow = FALSE; 

        $Counter1 = 0;
       
        $tmpRow = mysql_fetch_assoc($tmpResource);
        mysql_free_result($tmpResource);
        return $tmpRow;      
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
   * @see plcMySQLDBConnector::SetQuery()                               
   */ 

	public function GetResult()
		{
		$tmpResult = '';
		$tmpResource = FALSE;
		$tmpResource = $this->GetResource();
		
		$tmpNumRows = 0;
		
		$this->ResultRowCount = 0;
		$this->ResultFieldCount = 0;

		if ($tmpResource === TRUE)
			{
			throw new plcChassisException('Can not return result for this type of query.', 1110, null ,'The function is not supporting such query type.');
			}
		else if ($tmpResource === FALSE)
		  {
			$this->LastErrorNumber = mysql_errno($this->DBLink);
			$this->LastErrorText = mysql_error($this->DBLink);
			
			throw new plcChassisException('Error while executing query.', 1105, null ,'SQL error:'.$this->LastErrorNumber.' - '.$this->LastErrorText);
      }
		else
			{
			$tmpNumRows = mysql_num_rows($tmpResource);
			
			if ($tmpNumRows === FALSE || $tmpNumRows == 0)
        {
        $this->ResultRowCount = 0;
        $this->ResultFieldCount = 0;
        
        return FALSE;
        }
      else
        {
        $this->ResultRowCount = $tmpNumRows;
        $this->ResultFieldCount = mysql_num_fields($tmpResource);
        
        $tmpResult = mysql_result($tmpResource, 0);
			  mysql_free_result($tmpResource);
			  
			  return $tmpResult;        
        }
			}
		}
		
  /**
   * Function that used to return simple row result from the buffered query.
   * 
   * Simple function that returns simple row results from user defined query in a form of associative.
   * Note that you do not need to call ExecuteQuery() function before calling GetRow(). You only need to set query
   * using SetQuery() function and load result into buffer with function LoadResult().       
   * 
   * @access public 
   * 
   * @throws plcChassisException       
   *    
   * @return array|bool returns result array on succes and FALSE if there is nothing to return. 
   *
   * @see plcMySQLDBConnector::SetQuery()   
   * @see plcMySQLDBConnector::LoadResult() 
   *                                   
   */ 
		
  public function GetRow()
    {
    $tmpResult = FALSE;
    
    if (is_null($this->ResultRes) === TRUE) {throw new plcChassisException('Can not return result row.', 1114, null ,'Can not return result row - result set is not buffered yet.');}
    $tmpResult = mysql_fetch_assoc($this->ResultRes); 
    
    if ($tmpResult !== FALSE)
      {
      mysql_data_seek($this->ResultRes, $this->RowPointer);
      return $tmpResult;
      }
    else
      {
      return FALSE;
      } 
    }
    
  /**
   * Function that used to return simple field result from the buffered query.
   * 
   * Simple function that returns simple field results from user defined query.
   * Note that you do not need to call ExecuteQuery() function before calling GetField(). You only need to set query
   * using SetQuery() function and load result into buffer with function LoadResult().       
   * 
   * @access public 
   * 
   * @throws plcChassisException       
   *    
   * @return string|bool returns result string on succes and FALSE if there is nothing to return. 
   *
   * @see plcMySQLDBConnector::SetQuery()   
   * @see plcMySQLDBConnector::LoadResult() 
   *                                   
   */ 
    
  public function GetField()
    {
    if (is_null($this->ResultRes) === TRUE) {throw new plcChassisException('Can not get field data.', 1116, null ,'Can not get field data - result set is not buffered yet.');}      
    return mysql_result($this->ResultRes, $this->RowPointer, $this->FieldPointer);
    }
    
  /**
   * Function that used to return name of the field on which field pointer is set on in buffered query.
   * 
   * Simple function that returns name of the field on which field pointer is set on in buffered query.
   * Note that you do not need to call ExecuteQuery() function before calling GetName(). You only need to set query
   * using SetQuery() function and load result into buffer with function LoadResult().       
   * 
   * @access public 
   * 
   * @throws plcChassisException       
   *    
   * @return string|bool returns result string on succes and FALSE if there is nothing to return. 
   *
   * @see plcMySQLDBConnector::SetQuery()   
   * @see plcMySQLDBConnector::LoadResult() 
   *                                   
   */     
    
  public function GetName()
    {
    if (is_null($this->ResultRes) === TRUE) {throw new plcChassisException('Can not get field name.', 1117, null ,'Can not get field name - result set is not buffered yet.');}      
    return mysql_field_name($this->ResultRes, $this->FieldPointer);    
    }
    
  /**
   * Function that used to return length of the field on which field pointer is set on in buffered query.
   * 
   * Simple function that returns length of the field on which field pointer is set on in buffered query.
   * Note that you do not need to call ExecuteQuery() function before calling GetName(). You only need to set query
   * using SetQuery() function and load result into buffer with function LoadResult().       
   * 
   * @access public 
   * 
   * @throws plcChassisException       
   *    
   * @return int|bool returns result string on succes and FALSE if there is nothing to return. 
   *
   * @see plcMySQLDBConnector::SetQuery()   
   * @see plcMySQLDBConnector::LoadResult() 
   *                                   
   */     
  
  public function GetFieldLen()
    {
    if (is_null($this->ResultRes) === TRUE) {throw new plcChassisException('Can not get field length.', 1118, null ,'Can not get field length - result set is not buffered yet.');}      
    return mysql_field_len($this->ResultRes, $this->FieldPointer);      
    }
   
  /**
   * Function that used to current position of data pointer for buffered result set.
   * 
   * Simple function that returns current position of data pointer for buffered result set.    
   * 
   * @access public 
   *        
   * @return int returns position of data pointer. 
   *
   * @see plcMySQLDBConnector::LoadResult()   
   * @see plcMySQLDBConnector::NextRow()  
   * @see plcMySQLDBConnector::PrevRow()     
   *                                   
   */    
    
  public function GetRowNum()
    {
    return $this->RowPointer; 
    }
    
  /**
   * Function that used to current position of field pointer for buffered result set.
   * 
   * Simple function that returns current position of field pointer for buffered result set.    
   * 
   * @access public 
   *        
   * @return int returns position of field pointer. 
   *
   * @see plcMySQLDBConnector::LoadResult()   
   * @see plcMySQLDBConnector::NextField()  
   * @see plcMySQLDBConnector::PrevField()     
   *                                   
   */      
    
  public function GetFieldNum()
    {
    return $this->FieldPointer; 
    }
    		
	/* Get functions ends here */
	
	/* Set functions starts here */
	
  /**
   * Function that used to set current database host.
   * 
   * Simple function that sets (changes) current database host. Note that you must reconnect to the database in order
   * chages make effect.    
   * 
   * @access public
   * 
   * @param string address of the new host        
   * 
   * @throws plcChassisException      
   *    
   * @return bool returns TRUE on succes. 
   *
   * @see plcMySQLDBConnector::ReConnectToDB()                               
   */
	
  public function SetHost($usrHost)
    {
    if (empty($usrHost))
      {
      throw new plcChassisException('User does not provided server name.', 1101, null ,'Cannot connect to unknown server.');
      }
      
    $this->DBServerName = $usrHost;
    
    return TRUE;	 
    }
    
  /**
   * Function that used to set current database user name.
   * 
   * Simple function that sets (changes) current database user name. Note that you must reconnect to the database in order
   * chages make effect. 
   * 
   * @access public
   * 
   * @param string new user name           
   * 
   * @throws plcChassisException      
   *    
   * @return bool returns TRUE on succes. 
   *
   * @see plcMySQLDBConnector::ReConnectToDB()                               
   */
    
  public function SetUser($usrUser)
    {
		if (empty($usrUser))
			{
			throw new plcChassisException('User does not provided user name.', 1102, null ,'Cannot connect to data base without specified user.');
			}
			
		$this->DBUserName = $usrUser;
    
    return TRUE;	
    }
    
  /**
   * Function that used to set current database user password.
   * 
   * Simple function that sets (changes) current database user password. Note that you must reconnect to the database in order
   * chages make effect. 
   * 
   * @access public
   * 
   * @param string new user password       
   * 
   * @throws plcChassisException      
   *    
   * @return bool returns TRUE on succes. 
   *
   * @see plcMySQLDBConnector::ReConnectToDB()                               
   */
    
  public function SetPassword($usrPassword)
    {
		if (empty($usrPassword))
			{
			throw new plcChassisException('User does not provided password.', 1103, null ,'Cannot connect to data base without specified password.');
			}
      
    $this->DBPassword = $usrPassword; 
    
    return TRUE;   
    }
	
  /**
   * Function that used to set current working database.
   * 
   * Simple function that sets (changes) current working database name. 
   * 
   * @access public
   * 
   * @param string new database name            
   * 
   * @throws plcChassisException      
   *    
   * @return bool returns TRUE on succes. 
   *
   * @see plcMySQLDBConnector::SetQuery() 
   *                                  
   */ 
		
	public function SetDatabaseName($usrDBName)
		{
		if (empty($usrDBName))
			{
			throw new plcChassisException('User does not provided data base name.', 1111, null ,'Cannot select data base without specifying data base name.');
			}

    $this->DBName = $usrDBName;

		if ($this->DBLink == NULL)
			{
			throw new plcChassisException('Connection to data base does not exist.', 1109, null ,'Connection to data base does not exist or wrong server.');
			}
		
		if (mysql_select_db($this->DBName, $this->DBLink) === FALSE)
			{
			throw new plcChassisException('Could not select data base.', 1112, null , 'Some internal error occurred. Check that Server name, user name or password are correct.');
			}		

		return TRUE;
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