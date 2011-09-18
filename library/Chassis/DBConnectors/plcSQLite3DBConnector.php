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
 * Class plcSQLite3DBConnector is a part of PHP framework - Chassis.   
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
 * Documents the plcSQLite3DBConnector class.
 * 
 * Following class is intended for simplification of tasks connected to the work with SQLite database. This class
 * utilizes facade design pattern to provide unified interface for PHP implementaion of SQLite extension. 
 *  
 *   
 * @subpackage plcSQLite3DBConnector
 * @author Selihov Sergei Stanislavovich <red331@mail.ru>   
 */
 
require_once('Chassis/ErrorHandling/plcChassisException.php');
require_once('Chassis/DBConnectors/Interfaces.php');

class plcSQLite3DBConnector implements pliSharedDBConnector//, pliSmplSQLDBConnector, pliSmplSQLiteDBConnector
	{	
	
  /**
   * @access protected
   * @var object instance of the current class 
   */		
  	
	protected static $CurInstance = NULL; 
	
  /**
   * @access protected
   * @var object instance of SQLite3 class 
   */		
	
	protected $SQLite3Obj = NULL;	
	
  /**
   * @access protected
   * @var object instance of sqlite statement class 
   */			
	
	protected $SQLite3StmtObj = NULL;
	
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
   * @var const database file open mode for internal use
   */			
	
	protected $CurOpenModeConst = SQLITE3_OPEN_CREATE;
	
  /**
   * @access protected
   * @var mixed current encryption key used when encrypting and decrypting an SQLite database
   */		
	
	protected $CurEncryptionKey = NULL;
		
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
    $tmpDBObj = FALSE; 
    $usrFlags = strtolower($usrFlags);
    
    $this->CloseDBLink();
    
    if (empty($usrFileName) === TRUE || is_string($usrFileName) === FALSE)
      {
      throw new plcChassisException('Invalid file name.', 1401, null ,'User provided emtpy filename or it is not of string type.');
      }
    
    $this->DBFilePath = $usrFileName;    
    $this->SetOpenMode($usrFlags);
    $this->CurEncryptionKey = $usrEncryptionKey;
    
   // $tmpDBObj = new SQLite3($usrFileName, $usrFlags, $usrEncryptionKey);  
    
    if ($tmpDBObj === FALSE) 
      {
      throw new plcChassisException('Could not connect to database.', 1406, null ,'File name, file open mode or encryption key is incorrect.');
      }
    else
      {
      $this->SQLite3StmtObj = $tmpDBObj;
      }
		      
    return TRUE;	
    }
    
    /**
     * Function that indicates whether current object is connected to the database or not.
     * 
     * Simple function that indicates whether object is connected to the database or not.
     * 
     * @access public 
     *        
     * @return bool returns TRUE if object is connected to the database and FALSE if not.
     *                         
     */  

    public function IsConnected()
        {
        if (is_null($this->SQLite3Obj) === TRUE) {return FALSE;}
        else {return TRUE;}
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
     * @see plcSQLite3DBConnector::ConnectToDB()  
     * @see plcSQLite3DBConnector::ReConnectToDB()                             
     */ 

    public function CloseDBLink()
        {
        if ($this->SQLite3StmtObj != NULL)
            {
            @$this->SQLite3StmtObj->close();
            $this->SQLite3StmtObj = NULL;
            }
		
        if ($this->SQLite3Obj != NULL)
            {
            @$this->SQLite3Obj->close();
            $this->SQLite3Obj = NULL;
            return TRUE;	
            }   
        else
            {
            return FALSE;
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
   * Function that used to return path to a database file.
   * 
   * Simple function that returns path to a database file. 
   * 
   * @access public
   *           
   * @return string path to a database file. 
   *                             
   */    
    
  public function GetDBFile()
    {
    return $this->DBFilePath; 
    }  
    
  /**
   * Function that used to return open mode for current database file.
   * 
   * Simple function that returns open mode for current database file. 
   * 
   * @access public
   *      
   * @return string database file open mode. 
   *                             
   */ 
    
  public function GetOpenMode()
    {
    return $this->CurOpenModeStr;
    } 
    
  /**
   * Function that used to return current encryption key.
   * 
   * Simple function that returns current encryption key used when encrypting and decrypting an SQLite database. 
   * 
   * @access public
   *    
   * @return misxed current encryption key. 
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
    if (empty($usrDBFilePath) === TRUE || is_string($usrDBFilePath) === FALSE)
      {
      throw new plcChassisException('Invalid file name.', 1401, null ,'User provided emtpy filename or it is not of string type.');
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
    $tmpSQLiteFlags = SQLITE3_OPEN_CREATE; 
    $usrFlags = strtolower($usrFlags);
   
    if (strpos($usrFlags, 'rw') !== FALSE)
      {
      $tmpSQLiteFlags = SQLITE3_OPEN_READWRITE;
      
      if (strpos($usrFlags, 'c') !== FALSE)
        {
        $tmpSQLiteFlags = SQLITE3_OPEN_READWRITE|SQLITE3_OPEN_CREATE;
        } 
      else
        {
        if ($this->DBFilePath == NULL)
          {
          throw new plcChassisException('Database file name is not set yet.', 1402, null ,'You must first set database file before feature actions.');
          }
        
        if (file_exists($this->DBFilePath) === FALSE)
          {
          throw new plcChassisException('Database file is not exist.', 1403, null ,'Database file: "'.$this->DBFilePath.'" does not exist.');
          }
              
        if (is_readable($this->DBFilePath) !== TRUE)
          {
          throw new plcChassisException('Database file is not readable.', 1404, null ,'Database file: "'.$this->DBFilePath.'" is not readable.');
          }
          
        if (is_writable($this->DBFilePath) !== TRUE)
          {
          throw new plcChassisException('Database file is not writable.', 1405, null ,'Database file: "'.$this->DBFilePath.'" is not writable.');
          }                    
        }
      }
    else if (strpos($usrFlags, 'r') !== FALSE)
      {
      $tmpSQLiteFlags = SQLITE3_OPEN_READONLY;
      
      if (strpos($usrFlags, 'c') !== FALSE)
        {
        $tmpSQLiteFlags = SQLITE3_OPEN_READONLY|SQLITE3_OPEN_CREATE;
        } 
      else
        { 
        if ($this->DBFilePath == NULL)
          {
          throw new plcChassisException('Database file name is not set yet.', 1402, null ,'You must first set database file before feature actions.');
          }
        
        if (file_exists($this->DBFilePath) === FALSE)
          {
          throw new plcChassisException('Database file is not exist.', 1403, null ,'Database file: "'.$this->DBFilePath.'" does not exist.');
          }
              
        if (is_readable($this->DBFilePath) !== TRUE)
          {
          throw new plcChassisException('Database file is not readable.', 1404, null ,'Database file: "'.$this->DBFilePath.'" is not readable.');
          }
        }
      }
    else if (strpos($usrFlags, 'c') !== FALSE)
      {
      $tmpSQLiteFlags = SQLITE3_OPEN_CREATE; 
      $usrFlags = "c";
      }
    else
      {
      $tmpSQLiteFlags = SQLITE3_OPEN_CREATE; 
      $usrFlags = "c";
      }
       
    $this->CurOpenModeStr = $usrFlags;
    $this->CurOpenModeConst = $tmpSQLiteFlags;
      
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
  
  /* Set functions ends here */
	} 
	
?>