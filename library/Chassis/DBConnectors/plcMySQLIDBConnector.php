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
 * Class plcMySQLIDBConnector is a part of PHP framework - Chassis.   
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
 * Documents the plcMySQLIDBConnector class.
 * 
 * Following class is intended for simplification of tasks connected to the work with MYSQL database. This class
 * utilizes facade design pattern to provide unified interface for PHP implementaion of MySQLI extension. 
 *  
 *   
 * @subpackage plcMySQLIDBConnector
 * @author Selihov Sergei Stanislavovich <red331@mail.ru>   
 */

require_once('Chassis/ErrorHandling/plcChassisException.php');
require_once('Chassis/DBConnectors/Interfaces.php');

class plcMySQLIDBConnector implements pliSharedDBConnector, pliSmplSQLDBConnector, pliSmplMYSQLDBConnector, pliPrepStmtSQLDBConnector
	{	
  /**
   * @access protected
   * @var object instance of the current class 
   */		
  	
	protected static $CurInstance = NULL; 
	
  /**
   * @access protected
   * @var object instance of mysqli class 
   */		
	
	protected $MYSQLIObj = NULL;
	
  /**
   * @access protected
   * @var object instance of mysqli statement class 
   */			
	
	protected $MYSQLIStmtObj = NULL;
	
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
   * @var string current query to database that will be prepared before use 
   */	
	
	protected $PrepQuery = NULL;

  /**
   * @access protected
   * @var array parameters to query to database that will be prepared before use 
   */	
	
	protected $PrepQueryParams = NULL;
	
	/**
   * @access protected
   * @var array binded result for recent query 
   */	
	
	protected $PrepQueryBindResult = array();
	
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
		
	/* Core functions starts here */

	protected function __construct()
		{		
		}

  function __destruct() 
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
		$tmpDBObj = FALSE;
		
		$this->CloseDBLink();
		
		if (empty($usrServer))
			{
			throw new plcChassisException('User does not provided server name.', 1201, null ,'Cannot connect to unknown server.');
			}

		if (empty($usrUser))
			{
			throw new plcChassisException('User does not provided user name.', 1202, null ,'Cannot connect to data base without specified user.');
			}

		/*if (empty($usrPassword))
			{
			throw new plcChassisException('User does not provided password.', 1203, null ,'Cannot connect to data base without specified password.');
			}  */
		
		$this->DBServerName = $usrServer;
		$this->DBUserName = $usrUser;
		$this->DBPassword = $usrPassword;
	
		$tmpDBObj = new mysqli($usrServer, $usrUser, $usrPassword);
		
    if (mysqli_connect_errno()) 
      {
      throw new plcChassisException('Could not connect to database.', 1208, null ,'Server name, user name or password is incorrect.');
      }
    else
      {
      $this->MYSQLIObj = $tmpDBObj;
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
   * @see plcMySQLIDBConnector::ConnectToDB() 
   *                              
   */ 
		
	public function ReConnectToDB()
    {
    $this->CloseDBLink();
    			
		$tmpDBObj = new mysqli($this->DBServerName, $this->DBUserName, $this->DBPassword, $this->DBName);
		
    if (mysqli_connect_errno()) 
      {
      throw new plcChassisException('Could not connect to database.', 1208, null ,'Server name, user name or password is incorrect.');
      }
    else
      {
      $this->MYSQLIObj = $tmpDBObj;
      }		
     
    return TRUE;    
    }
    
  /**
   * Function that used to prepare SQL query before sending it into database.
   * 
   * This function is part of implementation of preapred SQL statements.
   * 
   * @access protected 
   * 
   * @throws plcChassisException       
   *    
   * @return bool returns TRUE on succesfull preparation of the query.
   * 
   * @see plcMySQLIDBConnector::ExecutePrepGetQuery()
   * @see plcMySQLIDBConnector::ExecutePrepQuery()
   * @see plcMySQLIDBConnector::GetPrepQuery()
   * @see plcMySQLIDBConnector::GetPrepQueryParams()
   * @see plcMySQLIDBConnector::GetPrepResultAssoc()
   * @see plcMySQLIDBConnector::GetPrepResultRow()
   * @see plcMySQLIDBConnector::GetPrepResult()
   * @see plcMySQLIDBConnector::SetPrepQuery()
   * @see plcMySQLIDBConnector::SetPrepQueryParams()
   *                              
   */ 
    
  protected function PrepareQuery()
    {
    $tmpBindParams = '';
    $tmpMYSQLIStmtObj = FALSE;
    $tmpResult = FALSE;
    
    $Counter1 = 0;
    
    $this->ResultRowCount = 0;
    $this->ResultFieldCount = 0;
    
    /* MYSQLIStmtObj preparation starts here */
    
    if ($this->MYSQLIStmtObj != NULL)
      {
      @$this->MYSQLIStmtObj->close();
      }
		if ($this->MYSQLIObj == NULL)
      {
      throw new plcChassisException('Connection to data base does not exist.', 1209, null ,'Connection to data base does not exist or wrong server.');
      }      
      
    $tmpMYSQLIStmtObj = $this->MYSQLIObj->stmt_init();
      
    if ($tmpMYSQLIStmtObj === FALSE)
      {
      throw new plcChassisException('Could not initialize MYSQLI statement.', 1213, null ,'Some internal error occurred. Could not initialize MYSQLI statement.');
      }
    else
      {
      $this->MYSQLIStmtObj = $tmpMYSQLIStmtObj; 
      }
      
    /* MYSQLIStmtObj preparation ends here */
      
    if ($this->PrepQuery == NULL)
      {
      throw new plcChassisException('Query that must be prepared is not set.', 1214, null ,'Query to data base must be set before it can be prepared.');
      }
    
    $tmpResult = $this->MYSQLIStmtObj->prepare($this->PrepQuery);

    if ($tmpResult === FALSE)
      {
      throw new plcChassisException('Error while preparation of the query.', 1215, null ,'MySQLI statement error:'.$this->MYSQLIStmtObj->errno.' - '.$this->MYSQLIStmtObj->error);
      }
    
    $Counter1 = 0;
    
    /* Params bind starts here */
       
    if ($this->PrepQueryParams != NULL)
      {
      for ($Counter1 = 1; $Counter1 < count($this->PrepQueryParams); $Counter1++)
        {
        $tmpBindParams .= '$this->PrepQueryParams['.$Counter1.']';
        
        if ($Counter1 + 1 < count($this->PrepQueryParams))
          {
          $tmpBindParams .= ',';
          }
        }
        
      $tmpBindParams = '$this->MYSQLIStmtObj->bind_param("'.$this->PrepQueryParams[0].'",'.$tmpBindParams.');';
      $tmpResult = eval($tmpBindParams); 
      
      if ($tmpResult === FALSE)
        {
        throw new plcChassisException('Error while preparation of the query.', 1220, null ,'Can not bind user parameters.');
        }
      }
    
    /* Params bind ends here */
    
    
    return TRUE;
    }
    
  /**
   * Function that used to execute prepared user defined SQL query and bind result to internal variable.
   * 
   * Current function is for internal use only.
   * 
   * @access public 
   *    
   * @throws plcChassisException      
   *    
   * @return array|bool returns array of result field names and FALSE on fail.
   * 
   * @see plcMySQLIDBConnector::PrepareQuery() 
   * @see plcMySQLIDBConnector::ExecutePrepQuery()
   * @see plcMySQLIDBConnector::GetPrepQuery()
   * @see plcMySQLIDBConnector::GetPrepQueryParams()
   * @see plcMySQLIDBConnector::GetPrepResultAssoc()
   * @see plcMySQLIDBConnector::GetPrepResultRow()
   * @see plcMySQLIDBConnector::GetPrepResult()
   * @see plcMySQLIDBConnector::SetPrepQuery()
   * @see plcMySQLIDBConnector::SetPrepQueryParams()      
   *                               
   */ 
    
  protected function ExecutePrepGetQuery()
    { 
    $tmpEvalString = '';
    $tmpResult = FALSE;
    $tmpFieldNames = array();
    $tmpField = NULL;
    
    $Counter1 = 0;
    
    $this->ResultRowCount = 0;
    $this->ResultFieldCount = 0;
    
    $this->PrepareQuery();        
    $tmpResult = $this->MYSQLIStmtObj->execute();
     
    if ($tmpResult === FALSE)
      {     
      $this->MYSQLIStmtObj->close();
      return FALSE;
      }
    else
      {      
      /* Field names fetch starts here */
        
      $tmpResult = $this->MYSQLIStmtObj->result_metadata();
        
      if ($tmpResult === FALSE)
        {
        $this->MYSQLIStmtObj->free_result();
        $this->MYSQLIStmtObj->close();
        throw new plcChassisException('Can not get table field names from result.', 1222, null , 'MySQLI statement error:'.$this->MYSQLIStmtObj->errno.' - '.$this->MYSQLIStmtObj->error);
        }
        
      $Counter1 = 0;
          
      while($tmpField = $tmpResult->fetch_field())
        {
        $tmpFieldNames[$Counter1] = $tmpField->name;
          
        $Counter1 += 1;
        }
         
      $tmpResult->close(); 
          
      /* Field names fetch ends here */
      
      /* Result bind starts here */
      
      if (count($tmpFieldNames) <= 0)
        {
        $this->MYSQLIStmtObj->free_result();
        $this->MYSQLIStmtObj->close();
        return FALSE;
        }
      
      for ($Counter1 = 0; $Counter1 < count($tmpFieldNames); $Counter1++)
        {
        $tmpEvalString .= '$this->PrepQueryBindResult['.$Counter1.']';
        
        if ($Counter1 + 1 < count($tmpFieldNames))
          {
          $tmpEvalString .= ',';
          }
        }
      
      $tmpEvalString = '$this->MYSQLIStmtObj->bind_result('.$tmpEvalString.');';      
      $tmpResult = eval($tmpEvalString);
      
      /* Result bind ends here */
       
      if ($tmpResult === FALSE)
        {
        $this->PrepQueryBindResult = array();
        $this->MYSQLIStmtObj->free_result();  
        $this->MYSQLIStmtObj->close();
        throw new plcChassisException('Error while execution of the query.', 1221, null ,'Can not bind result variables.');
        }
                
      return $tmpFieldNames;
      }
    } 
    
  /**
   * Function that used to execute prepared user defined SQL query.
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
   * @see plcMySQLIDBConnector::PrepareQuery() 
   * @see plcMySQLIDBConnector::ExecutePrepGetQuery()
   * @see plcMySQLIDBConnector::GetPrepQuery()
   * @see plcMySQLIDBConnector::GetPrepQueryParams()
   * @see plcMySQLIDBConnector::GetPrepResultAssoc()
   * @see plcMySQLIDBConnector::GetPrepResultRow()
   * @see plcMySQLIDBConnector::GetPrepResult()
   * @see plcMySQLIDBConnector::SetPrepQuery()
   * @see plcMySQLIDBConnector::SetPrepQueryParams()      
   *                               
   */ 
    
  public function ExecutePrepQuery()
    {
    $this->ResultRowCount = 0;
    $this->ResultFieldCount = 0;
    
    $tmpResult = FALSE;
    
    $this->PrepareQuery();        
    $tmpResult = $this->MYSQLIStmtObj->execute();
     
    if ($tmpResult === FALSE)
      {     
      $this->MYSQLIStmtObj->close();
      throw new plcChassisException('Error while execution of the query.', 1224, null , 'MySQLI statement error:'.$this->MYSQLIStmtObj->errno.' - '.$this->MYSQLIStmtObj->error);
      }
    else
      {
      $this->MYSQLIStmtObj->close();      
      return TRUE;
      }
    } 
    
  /**
   * Function that used to execute user defined SQL query.
   * 
   * Mainly used to execute queries such as INSERT or UPDATE. For queries that returns result please use other functions.
   * Note that you must first call ConnectToDB() and  SetDatabase() functions.
   * 
   * @access public 
   * 
   * @param bool indicates whether large amount of data will be processd or not. Solely fo optemization purpose.      
   * 
   * @throws plcChassisException      
   *    
   * @return bool returns TRUE on succesfull execution of the query.
   * 
   * @see plcMySQLIDBConnector::ConnectToDB()  
   * @see plcMySQLIDBConnector::SetDatabase()   
   *                              
   */ 

	public function ExecuteQuery($usrIsBigQuery = FALSE)
		{
		$this->ResultRowCount = 0;
		$this->ResultFieldCount = 0;
		
		$tmpResult = $this->GetResource($usrIsBigQuery);

		if ($tmpResult !== FALSE && $tmpResult !== TRUE)
			{
			$tmpResult->close();
			
			return TRUE;
			}
		else if ($tmpResult !== FALSE && $tmpResult === TRUE)
		  {
		  return TRUE;
      }
		else
			{
			$this->LastErrorNumber = $this->MYSQLIObj->errno;
			$this->LastErrorText = $this->MYSQLIObj->error;
			
			throw new plcChassisException('Error while executing query.', 1205, null ,'SQL error:'.$this->LastErrorNumber.' - '.$this->LastErrorText);
			}
		}
		
  /**
   * Function that used to close connection to database.
   * 
   * Simple function that closes connection with database and free resources for MySQL prepared statemen object.
   * Note that this function will be automaticly called inside destructor function.
   * 
   * @access public 
   *    
   * @return bool returns TRUE if function successfuly close connection to database, FALSE if connection to database was not yet established.
   * 
   * @see plcMySQLIDBConnector::ConnectToDB()  
   * @see plcMySQLIDBConnector::ReConnectToDB()                             
   */ 

	public function CloseDBLink()
		{
		$this->ResultRowCount = 0;
		$this->ResultFieldCount = 0;
		
		if ($this->MYSQLIStmtObj != NULL)
		  {
		  @$this->MYSQLIStmtObj->close();
      }
		
		if ($this->MYSQLIObj != NULL)
			{
			@$this->MYSQLIObj->close();
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
   * @see plcMySQLIDBConnector::ConnectToDB()  
   * @see plcMySQLIDBConnector::ReConnectToDB()                             
   */ 
		
	public function EscapeString($usrString)
    {   
    $tmpResult = '';
     
		if ($this->MYSQLIObj == NULL)
			{
			throw new plcChassisException('Connection to data base does not exist.', 1209, null ,'Connection to data base does not exist or wrong server.');
			}
    
    $tmpResult = $this->MYSQLIObj->real_escape_string($usrString);
    
    if($tmpResult === FALSE || empty($tmpResult) === TRUE)
      {
      throw new plcChassisException('Can not escape user string.', 1207, null ,'User string is ither empty or invalid.');
      }
      
    return $tmpResult; 
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
    
		if ($this->MYSQLIObj == NULL)
			{
			throw new plcChassisException('Connection to data base does not exist.', 1209, null ,'Connection to data base does not exist or wrong server.');
			}
      
    $tmpResult = $this->MYSQLIObj->insert_id();
    
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
   * @see plcMySQLIDBConnector::GetResultAssoc()   
   * @see plcMySQLIDBConnector::GetResultRow()   
   * @see plcMySQLIDBConnector::GetResult()    
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
   * @see plcMySQLIDBConnector::GetResultAssoc()   
   * @see plcMySQLIDBConnector::GetResultRow()   
   * @see plcMySQLIDBConnector::GetResult()    
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
   * @param bool indicates whether large amount of data will be processd or not. Solely fo optemization purpose.       
   * 
   * @throws plcChassisException         
   *    
   * @return bool|resource returns TRUE for successfull execution of such queries as INSERT, UPDATE, DELETE, DROP and 
   * resource for such queries as SELECT, SHOW, DESCRIBE, EXPLAIN.                            
   */ 
		
  protected function GetResource($usrIsBigQuery = FALSE)
    {
		$tmpResult = FALSE;
		
		$this->ResultRowCount = 0;
		$this->ResultFieldCount = 0;

		if ($this->Query == NULL)
			{
			throw new plcChassisException('Query to data base is not set.', 1204, null ,'Query to data base must be set before calling any function for data retrieving.');
			}

		if ($this->MYSQLIObj == NULL)
			{
			throw new plcChassisException('Connection to data base does not exist.', 1209, null ,'Connection to data base does not exist or wrong server.');
			}

		if ($usrIsBigQuery === TRUE)
		  {
		  return $this->MYSQLIObj->query($this->Query, MYSQLI_USE_RESULT);
      }
    else
      {
      return $this->MYSQLIObj->query($this->Query, MYSQLI_STORE_RESULT);
      }
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
        throw new plcChassisException("'autocommit' server variable does not exist.", 1206, null , "SQL server has no 'autocommit' variable.");
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
   * @param bool indicates whether large amount of data will be processd or not. Solely fo optemization purpose.       
   * 
   * @throws plcChassisException      
   *    
   * @return array|bool returns associative array on succes and FALSE if there is nothing to return.  
   * 
   * @see plcMySQLIDBConnector::SetQuery()                                  
   */ 

	public function GetResultAssoc($usrIsBigQuery = FALSE)
		{
		$tmpResource = FALSE;
    $tmpResource = $this->GetResource($usrIsBigQuery);
    
    $this->ResultRowCount = 0;
    $this->ResultFieldCount = 0;

		if ($tmpResource === TRUE)
			{
			throw new plcChassisException('Can not return result for this type of query.', 1210, null ,'The function is not supporting such query type.');
			}
		else if ($tmpResource === FALSE)
		  {
			$this->LastErrorNumber = $this->MYSQLIObj->errno;
			$this->LastErrorText = $this->MYSQLIObj->error;
			
			throw new plcChassisException('Error while executing query.', 1205, null ,'SQL error:'.$this->LastErrorNumber.' - '.$this->LastErrorText);
      }
		else
			{
			$tmpAssocArray = array();
			$tmpRow = NULL; 

			$Counter1 = 0;
       
			while ($tmpRow = $tmpResource->fetch_assoc()) 
				{ 
				$tmpAssocArray[$Counter1] = $tmpRow;

				$Counter1 = $Counter1 + 1;
				}

      $this->ResultRowCount = $tmpResource->num_rows;
      $this->ResultFieldCount = $tmpResource->field_count;
      
			$tmpResource->close();

			if(count($tmpAssocArray) < 1)
        {
        $this->ResultRowCount = 0;
        $this->ResultFieldCount = 0;
        
        return FALSE;
        } 

			return $tmpAssocArray;
			}
		}

  /**
   * Function that used to return simple row result from the query.
   * 
   * Simple function that returns simple row results from user defined query in a form of associative array.
   * Note that you do not need to call ExecuteQuery() function before calling GetResultRow(). You only need to set query
   * using SetQuery() function.       
   * 
   * @access public
   * 
   * @param bool indicates whether large amount of data will be processd or not. Solely fo optemization purpose.           
   * 
   * @throws plcChassisException       
   *    
   * @return array|bool returns result array on succes and FALSE if there is nothing to return. 
   *
   * @see plcMySQLIDBConnector::SetQuery()                               
   */ 
		
	public function GetResultRow($usrIsBigQuery = FALSE)
    {
		$tmpResource = FALSE;
		$tmpResource = $this->GetResource($usrIsBigQuery = FALSE);
		
		$this->ResultRowCount = 0;
		$this->ResultFieldCount = 0;

		if ($tmpResource === TRUE)
			{
			throw new plcChassisException('Can not return result for this type of query.', 1210, null ,'The function is not supporting such query type.');
			}
		else if ($tmpResource === FALSE)
		  {
			$this->LastErrorNumber = $this->MYSQLIObj->errno;
			$this->LastErrorText = $this->MYSQLIObj->error;
			
			throw new plcChassisException('Error while executing query.', 1205, null ,'SQL error:'.$this->LastErrorNumber.' - '.$this->LastErrorText);
      }
		else
			{
			$tmpRow = FALSE; 

			$Counter1 = 0;
       
      $tmpRow = $tmpResource->fetch_assoc();
      $this->ResultRowCount = $tmpResource->num_rows; 
      $this->ResultFieldCount = $tmpResource->field_count;
      
      $tmpResource->close();
      
      if (empty($tmpRow) === TRUE)
        {
        $this->ResultRowCount = 0;
        $this->ResultFieldCount = 0;
        
        return FALSE;
        }
      else
        {     
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
   * @param bool indicates whether large amount of data will be processd or not. Solely fo optemization purpose.          
   * 
   * @throws plcChassisException       
   *    
   * @return mixed|bool returns result value on succes and FALSE if there is nothing to return. 
   *
   * @see plcMySQLIDBConnector::SetQuery()   
   *                                
   */ 

	public function GetResult($usrIsBigQuery = FALSE)
		{
		$tmpResult = '';
		$tmpResource = FALSE;
		$tmpResource = $this->GetResource($usrIsBigQuery = FALSE);
		
		$this->ResultRowCount = 0;
		$this->ResultFieldCount = 0;

		if ($tmpResource === TRUE)
			{
			throw new plcChassisException('Can not return result for this type of query.', 1210, null ,'The function is not supporting such query type.');
			}
		else if ($tmpResource === FALSE)
		  {
			$this->LastErrorNumber = $this->MYSQLIObj->errno;
			$this->LastErrorText = $this->MYSQLIObj->error;
			
			throw new plcChassisException('Error while executing query.', 1205, null ,'SQL error:'.$this->LastErrorNumber.' - '.$this->LastErrorText);
      }
		else
			{
			$tmpResult = $tmpResource->fetch_row();
			$this->ResultRowCount = $tmpResource->num_rows;
			$this->ResultFieldCount = $tmpResource->field_count;
			
			$tmpResource->close();
			
      if (empty($tmpResult[0]) === TRUE)
        {
        $this->ResultRowCount = 0;
        $this->ResultFieldCount = 0;
        
        return FALSE;
        }
      else
        {
        return $tmpResult[0];
        }
			}
		}
		
  /**
   * Function that used to get current query that will be prepared before use.
   * 
   * Simple function that get current query to the database that will be prepared before use. 
   * 
   * @access public
   * 
   * @return string|NULL returns query that will be prepared before use and NULL if it is not set.        
   *  
   * @see plcMySQLIDBConnector::PrepareQuery() 
   * @see plcMySQLIDBConnector::ExecutePrepGetQuery()
   * @see plcMySQLIDBConnector::ExecutePrepQuery()
   * @see plcMySQLIDBConnector::GetPrepQueryParams()
   * @see plcMySQLIDBConnector::GetPrepResultAssoc()
   * @see plcMySQLIDBConnector::GetPrepResultRow()
   * @see plcMySQLIDBConnector::GetPrepResult()
   * @see plcMySQLIDBConnector::SetPrepQuery()
   * @see plcMySQLIDBConnector::SetPrepQueryParams()
   *                                            
   */ 
		
  public function GetPrepQuery()
    {
    return $this->PrepQuery;
    }
    
  /**
   * Function that used to get parameters for current query that will be prepared before use.
   * 
   * Simple function that get parameters for current query to the database that will be prepared before use. 
   * 
   * @access public
   *           
   * @return array|NULL returns array of parameters and NULL if parameters is not set.  
   *   
   * @see plcMySQLIDBConnector::PrepareQuery() 
   * @see plcMySQLIDBConnector::ExecutePrepGetQuery()
   * @see plcMySQLIDBConnector::ExecutePrepQuery()
   * @see plcMySQLIDBConnector::GetPrepQuery()
   * @see plcMySQLIDBConnector::GetPrepResultAssoc()
   * @see plcMySQLIDBConnector::GetPrepResultRow()
   * @see plcMySQLIDBConnector::GetPrepResult()
   * @see plcMySQLIDBConnector::SetPrepQuery()
   * @see plcMySQLIDBConnector::SetPrepQueryParams()   
   *                                       
   */ 
    
  public function GetPrepQueryParams()
    {
    return $this->PrepQueryParams;
    }
    
  /**
   * Function that used to get query results in form of associative array.
   * 
   * Simple function that returns results of the current prepared query in form of associative array. First index form the array is numerical
   * which represents row from the database, second index represents column name.
   * Note that you do not need to call ExecutePrepQuery() function before calling GetPrepResultAssoc(). You only need to set query
   * using SetPrepQuery() function.        
   * 
   * @access public 
   *      
   * @throws plcChassisException      
   *    
   * @return array|bool returns associative array on succes and FALSE if there is nothing to return.  
   *       
   * @see plcMySQLIDBConnector::PrepareQuery() 
   * @see plcMySQLIDBConnector::ExecutePrepGetQuery()
   * @see plcMySQLIDBConnector::ExecutePrepQuery()
   * @see plcMySQLIDBConnector::GetPrepQuery()
   * @see plcMySQLIDBConnector::GetPrepQueryParams()
   * @see plcMySQLIDBConnector::GetPrepResultRow()
   * @see plcMySQLIDBConnector::GetPrepResult()
   * @see plcMySQLIDBConnector::SetPrepQuery()
   * @see plcMySQLIDBConnector::SetPrepQueryParams()
   *                                
   */ 
  
  public function GetPrepResultAssoc()
    {
    $tmpResult = FALSE;
    $tmpResultArray = array();
    $tmpFieldNames = array();
    $tmpField = NULL;
    
    $Counter1 = 0;
    $Counter2 = 0;
    
    $this->ResultRowCount = 0;
    $this->ResultFieldCount = 0;
    
    $tmpFieldNames = $this->ExecutePrepGetQuery();
    
    if ($tmpFieldNames === FALSE)
      {
      return FALSE;
      }
    else
      {        
      /* Data fetch starts here */
        
      $Counter1 = 0;
 
      while ($this->MYSQLIStmtObj->fetch())
        {
        $tmpResultArray[$Counter1] = array();
          
        for ($Counter2 = 0; $Counter2 < count($tmpFieldNames); $Counter2++)
          {            
          eval('$tmpResultArray[$Counter1][$tmpFieldNames[$Counter2]] = $this->PrepQueryBindResult['.$Counter2.'];');
          }
          
        $Counter1 += 1;
        $tmpResult = TRUE;
        }
        
      /* Data fetch ends here */                   
      }
    
    $this->ResultRowCount = $this->MYSQLIStmtObj->num_rows;
    $this->ResultFieldCount = $this->MYSQLIStmtObj->field_count;
    
    $this->PrepQueryBindResult = array();          
    $this->MYSQLIStmtObj->free_result(); 
    $this->MYSQLIStmtObj->close();
    
    if ($tmpResult === TRUE)
      {
      return $tmpResultArray;
      }
    else
      {
      $this->ResultRowCount = 0;
      $this->ResultFieldCount = 0;
      
      return FALSE;
      }
    }
    
  /**
   * Function that used to return simple row result from the prepared query.
   * 
   * Simple function that returns simple row results from user defined prepared query in a form of associative array.
   * Note that you do not need to call ExecutePrepQuery() function before calling GetPrepResultRow(). You only need to set query
   * using SetPrepQuery() function.       
   * 
   * @access public 
   * 
   * @throws plcChassisException       
   *    
   * @return array|bool returns result array on succes and FALSE if there is nothing to return. 
   * 
   * @see plcMySQLIDBConnector::PrepareQuery() 
   * @see plcMySQLIDBConnector::ExecutePrepGetQuery()
   * @see plcMySQLIDBConnector::ExecutePrepQuery()
   * @see plcMySQLIDBConnector::GetPrepQuery()
   * @see plcMySQLIDBConnector::GetPrepQueryParams()
   * @see plcMySQLIDBConnector::GetPrepResultAssoc()
   * @see plcMySQLIDBConnector::GetPrepResult()
   * @see plcMySQLIDBConnector::SetPrepQuery()
   * @see plcMySQLIDBConnector::SetPrepQueryParams()      
   *                              
   */ 
    
  public function GetPrepResultRow()
    {
    $tmpResult = FALSE;
    $tmpResultArray = array();
    $tmpFieldNames = array();
    $tmpField = NULL;
    
    $Counter1 = 0;
    
    $this->ResultRowCount = 0;
    $this->ResultFieldCount = 0;
    
    $tmpFieldNames = $this->ExecutePrepGetQuery();

    if ($tmpFieldNames === FALSE)
      {
      return FALSE;
      }
    else
      {       
      /* Data fetch starts here */
 
      while ($this->MYSQLIStmtObj->fetch())
        {
        $tmpResultArray[$Counter1] = array();
          
        for ($Counter1 = 0; $Counter1 < count($tmpFieldNames); $Counter1++)
          {            
          eval('$tmpResultArray[$tmpFieldNames[$Counter1]] = $this->PrepQueryBindResult['.$Counter1.'];');
          }
          
        $tmpResult = TRUE;       
        break;
        }
        
      /* Data fetch ends here */                   
      }
  
    $this->ResultRowCount = $this->MYSQLIStmtObj->num_rows;
    $this->ResultFieldCount = $this->MYSQLIStmtObj->field_count;
  
    $this->PrepQueryBindResult = array();          
    $this->MYSQLIStmtObj->free_result(); 
    $this->MYSQLIStmtObj->close();

    if ($tmpResult === TRUE)
      {
      return $tmpResultArray;
      }
    else
      {
      $this->ResultRowCount = 0;
      $this->ResultFieldCount = 0;
      
      return FALSE;
      }
    }
    
  /**
   * Function that used to return simple column result from the prepared query.
   * 
   * Simple function that returns simple colum results from user defined prepared query.
   * Note that you do not need to call ExecutePrepQuery() function before calling GetPrepResult(). You only need to set query
   * using SetPrepQuery() function.        
   * 
   * @access public 
   * 
   * @throws plcChassisException       
   *    
   * @return mixed|bool returns result value on succes and FALSE if there is nothing to return.
   * 
   * @see plcMySQLIDBConnector::PrepareQuery() 
   * @see plcMySQLIDBConnector::ExecutePrepGetQuery()
   * @see plcMySQLIDBConnector::ExecutePrepQuery()
   * @see plcMySQLIDBConnector::GetPrepQueryParams()
   * @see plcMySQLIDBConnector::GetPrepResultAssoc()
   * @see plcMySQLIDBConnector::GetPrepResultRow()
   * @see plcMySQLIDBConnector::GetPrepResult()
   * @see plcMySQLIDBConnector::SetPrepQuery()
   * @see plcMySQLIDBConnector::SetPrepQueryParams()         
   *                                
   */ 
    
  public function GetPrepResult()
    {
    $tmpResult = FALSE;
    $tmpResultArray = array();
    $tmpFieldNames = array();
    $tmpField = NULL;
    
    $Counter1 = 0;
    $Counter2 = 0;
    
    $this->ResultRowCount = 0;
    $this->ResultFieldCount = 0;
    
    $tmpFieldNames = $this->ExecutePrepGetQuery();
    
    if ($tmpFieldNames === FALSE)
      {
      return FALSE;
      }
    else
      {        
      /* Data fetch starts here */
        
      $Counter1 = 0;
 
      while ($this->MYSQLIStmtObj->fetch())
        {           
        $tmpResult = $this->PrepQueryBindResult[0];    
        break;
        }
        
      /* Data fetch ends here */                   
      }
    
    $this->ResultRowCount = $this->MYSQLIStmtObj->num_rows;
    $this->ResultFieldCount = $this->MYSQLIStmtObj->field_count;
    
    $this->PrepQueryBindResult = array();          
    $this->MYSQLIStmtObj->free_result(); 
    $this->MYSQLIStmtObj->close();
    
    if ($tmpResult !== FALSE)
      {
      return $tmpResult;
      }
    else
      {
      $this->ResultRowCount = 0;
      $this->ResultFieldCount = 0;
      
      return FALSE;
      }
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
   * @see plcMySQLIDBConnector::ReConnectToDB()                               
   */
	
  public function SetHost($usrHost)
    {    
    if (empty($usrHost))
      {
      throw new plcChassisException('User does not provided server name.', 1201, null ,'Cannot connect to unknown server.');
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
   * @param bool flag that indicates whether changes will be made imidiatly without need to call to ReConnectToDB() method             
   * 
   * @throws plcChassisException      
   *    
   * @return bool returns TRUE on succes. 
   *
   * @see plcMySQLIDBConnector::ReConnectToDB()                               
   */
    
  public function SetUser($usrUser)
    {
		if (empty($usrUser))
			{
			throw new plcChassisException('User does not provided user name.', 1202, null ,'Cannot connect to data base without specified user.');
			}
			
		$this->DBUserName = $usrUser;
		
    if ($usrChangeNow === TRUE)
      {
		  if ($this->MYSQLIObj == NULL)
        {
        throw new plcChassisException('Connection to data base does not exist.', 1209, null ,'Connection to data base does not exist or wrong server.');
        }
        
      if ($this->MYSQLIObj->change_user($this->DBUserName, $this->DBPassword, $this->DBName) === FALSE)
        {
        throw new plcChassisException('Could not select data base.', 1212, null , 'Some internal error occurred. Check that Server name, user name or password are correct.');
        }		
      } 
    
    return TRUE;	
    }
    
  /**
   * Function that used to set current database user password.
   * 
   * Simple function that sets (changes) current database user password. Note that you must reconnect to the database in order
   * chages make effect if second parametere is set to FALSE. 
   * 
   * @access public
   * 
   * @param string new user password
   * @param bool flag that indicates whether changes will be made imidiatly without need to call to ReConnectToDB() method               
   * 
   * @throws plcChassisException      
   *    
   * @return bool returns TRUE on succes. 
   *
   * @see plcMySQLIDBConnector::ReConnectToDB()                               
   */
    
  public function SetPassword($usrPassword, $usrChangeNow = FALSE)
    {
		if (empty($usrPassword))
			{
			throw new plcChassisException('User does not provided password.', 1203, null ,'Cannot connect to data base without specified password.');
			}
      
    $this->DBPassword = $usrPassword;
    
    if ($usrChangeNow === TRUE)
      {
		  if ($this->MYSQLIObj == NULL)
        {
        throw new plcChassisException('Connection to data base does not exist.', 1209, null ,'Connection to data base does not exist or wrong server.');
        }
        
      if ($this->MYSQLIObj->change_user($this->DBUserName, $this->DBPassword, $this->DBName) === FALSE)
        {
        throw new plcChassisException('Could not select data base.', 1212, null , 'Some internal error occurred. Check that Server name, user name or password are correct.');
        }		
      } 
    
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
   * @see plcMySQLIDBConnector::SetQuery()    
   *                               
   */ 
		
	public function SetDatabaseName($usrDBName)
		{
		if (empty($usrDBName))
			{
			throw new plcChassisException('User does not provided data base name.', 1211, null ,'Cannot select data base without specifying data base name.');
			}

    $this->DBName = $usrDBName;

		if ($this->MYSQLIObj == NULL)
			{
			throw new plcChassisException('Connection to data base does not exist.', 1209, null ,'Connection to data base does not exist or wrong server.');
			}
		
		if ($this->MYSQLIObj->change_user($this->DBUserName, $this->DBPassword, $this->DBName) === FALSE)
			{
			throw new plcChassisException('Could not select data base.', 1212, null , 'Some internal error occurred. Check that Server name, user name or password are correct.');
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
		
  /**
   * Function that used to set current query that will be prepared before use.
   * 
   * Simple function that sets current query to the database that will be prepared before use. 
   * 
   * @access public
   *
   * @param string new query to the database that will be prepared before use 
   * 
   * @see plcMySQLIDBConnector::PrepareQuery() 
   * @see plcMySQLIDBConnector::ExecutePrepGetQuery()
   * @see plcMySQLIDBConnector::ExecutePrepQuery()
   * @see plcMySQLIDBConnector::GetPrepQuery()
   * @see plcMySQLIDBConnector::GetPrepQueryParams()
   * @see plcMySQLIDBConnector::GetPrepResultAssoc()
   * @see plcMySQLIDBConnector::GetPrepResultRow()
   * @see plcMySQLIDBConnector::GetPrepResult()
   * @see plcMySQLIDBConnector::SetPrepQueryParams()             
   *                                  
   */ 
		
	public function SetPrepQuery($usrQuery)
		{
		$this->PrepQuery = $usrQuery;
		}
		
  /**
   * Function that used to set parameters for current query that will be prepared before use.
   * 
   * Simple function that sets parameters for current query to the database that will be prepared before use. 
   * 
   * @access public
   * 
   * @throws plcChassisException        
   *
   * @param mixed ... parameters for current query, if first parameter is set to NULL - no parameters will be used
   * while preparation of the query. First parameter to the function must be string that represents type of of other
   * parameters it must only contain following letters: i, d, s, b.              
   *   
   * @see plcMySQLIDBConnector::PrepareQuery() 
   * @see plcMySQLIDBConnector::ExecutePrepGetQuery()
   * @see plcMySQLIDBConnector::ExecutePrepQuery()
   * @see plcMySQLIDBConnector::GetPrepQuery()
   * @see plcMySQLIDBConnector::GetPrepResultAssoc()
   * @see plcMySQLIDBConnector::GetPrepResultRow()
   * @see plcMySQLIDBConnector::GetPrepResult()
   * @see plcMySQLIDBConnector::SetPrepQuery()
   * @see plcMySQLIDBConnector::SetPrepQueryParams()
   *                                     
   */ 
		
	public function SetPrepQueryParams()
    {   
    $tmpParamCount = func_num_args(); 
    
    $tmpParamList = array();
    $tmpSplitString = '';
    
    $Counter1 = 0;
     
    if ($tmpParamCount == 0)
      {
      return FALSE;
      }
    else
      {
      $tmpParamList = func_get_args();
      
      if ($tmpParamList[0] === NULL)
        {
        $this->PrepQueryParams = NULL;
        return TRUE;
        }
       else
        {
        if(is_string($tmpParamList[0]) === FALSE)
          {
          throw new plcChassisException('Error while setting parameteres for preparation of the SQL query.', 1216, null , 'First parameter must be a string that represents types of other parametres.');
          }
        else
          {
          $tmpParamList[0] = strtolower($tmpParamList[0]);          
          $tmpSplitString = str_split($tmpParamList[0]);
          
          if (count($tmpSplitString) !=  ($tmpParamCount - 1))
            {
            throw new plcChassisException('Error while setting parameteres for preparation of the SQL query.', 1217, null , 'Number of parameters types (from a string) does not match actual parameters number.');
            }
          else
            {
            for ($Counter1 = 0; $Counter1 < count($tmpSplitString); $Counter1++)
              {
              switch($tmpSplitString[$Counter1])
                {
                case 'i':
                
                if (is_numeric($tmpParamList[$Counter1+1]) !== TRUE)
                  {   
                  throw new plcChassisException('Error while setting parameteres for preparation of the SQL query.', 1219, null , 'Type mismatch on '.($Counter1+1).' parameter, for query: '.$this->PrepQuery.'.');
                  }
                break;
                
                case 'd':
                
                if (is_double($tmpParamList[$Counter1+1]) !== TRUE)
                  {  
                  throw new plcChassisException('Error while setting parameteres for preparation of the SQL query.', 1219, null , 'Type mismatch on '.($Counter1+1).' parameter, for query: '.$this->PrepQuery.'.');
                  }               
                break;
                
                case 's':
                
                if (is_string($tmpParamList[$Counter1+1]) !== TRUE)
                  {  
                  throw new plcChassisException('Error while setting parameteres for preparation of the SQL query.', 1219, null , 'Type mismatch on '.($Counter1+1).' parameter, for query: '.$this->PrepQuery.'.');
                  }                 
                break;
                
                case 'b':
                
                break;
                
                default:
                
                throw new plcChassisException('Error while setting parameteres for preparation of the SQL query.', 1218, null , 'There is no such type as "'.$tmpSplitString[$Counter1].'".');
                break;
                }
              }
            
            $this->PrepQueryParams = $tmpParamList;  
            return TRUE;
            }
          }
        }        
      }
    }
		
	/* Set functions ends here */
	}

?>