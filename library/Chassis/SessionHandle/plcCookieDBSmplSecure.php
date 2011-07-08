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
 * Class plcCookieDBSmplSecure is a part of PHP framework - Chassis.   
 * 
 * @package     Chassis
 * @author      Selihov Sergei Stanislavovich <red331@mail.ru> 
 * @copyright   Copyright (c) 2010-2011 Selihov Sergei Stanislavovich.
 * @license     http://www.gnu.org/licenses/gpl.html  GNU GENERAL PUBLIC LICENSE (Version 3)
 *    
 */
 
/**
 * Classes for work with browser sessions.
 *  
 * @subpackage SessionHandle
 * @author Selihov Sergei Stanislavovich <red331@mail.ru>   
 */
 
/**
 * Documents the plcCookieDBSmplSecure class.
 * 
 * Following class is intended for simplification of tasks connected to the work with session handle on server via
 * browser cookies. This class depends on plcMySQLDBConnector for connection to the mysql database which stores
 * user session information. Note that specific table for session information must be already present in the database and
 * contain following fields (MySQL):
 * 
 * id - int(11) - auto_increment - primary key that points to one of many sessions stored in the database; 
 * userid - int(11) - foreign key that is used to refference misc user info in another table (no information is used from another table);
 * sessionstart - bigint(11) - timestamp of when current session have started; 
 * sessionend - bigint(11) - timestamp of when current session have ended;   
 * session_id - varchar(200) - unique session id that is stored in browser cookies file; 
 * session_expire - int(11) - timestamp that indicates whether or not user session must be cleaned by garbage collector; 
 * session_data - text - misc data that is connected with the current session and is not used internally by current class;
 * session_ip - varchar(20) - ip address of the user associated with the current session;
 * issessionblock - tinyint(1) - shows whether or not current session is blocked;    
 * 
 * Class plcCookieDBSmplSecure implements Singleton design pattern so that there is only one copy of session handle object at a 
 * time and strategy pattern to dynamicly swap session id encoding algorithm.  
 *   
 * @subpackage plcCookieDBSmplSecure
 * @author Selihov Sergei Stanislavovich <red331@mail.ru>   
 */
 
require_once('Chassis/ErrorHandling/plcChassisException.php');
require_once('Chassis/SessionHandle/plcAbstractCookieDB.php');

class plcCookieDBSmplSecure extends plcAbstractCookieDB
  {
  /**
   * @access protected
   * @var mixed stores instance of the current class (Singleton design pattern) 
   */	  
  
  protected static $Instance = FALSE;
  
  /**
   * @access protected
   * @var int period of time after which session garbage collector will be launched 
   */	  
  
  protected $CurPHPSesLifeTime;
  
  /**
   * @access protected
   * @var string period of time after which session will be expired and blocked 
   */	 
  
  protected $CurSesLifeTime;
  
  /**
   * @access protected
   * @var string period of time after which all expired sessions will be deleted from the database and dumped to special text file 
   */	   
  
  protected $CurSesGarbageLifeTime;
    
  /**
   * @access protected
   * @var object of type plcMySQLDBConnector for database connection 
   */	
  
  protected $DBConnector;
  
  /**
   * @access protected
   * @var string name of the table used for session storage
   */	
  
  protected $CurTableName;
  
  /**
   * @access protected
   * @var string path to a folder where expired sessions will be dumped
   */	
  
  protected $OldSessionDumpFolder;
 
  /**
   * @access protected
   * @var array of various options for use in the current class (currently not used)
   */	
  
  protected $CurOptions;
  
  /**
   * @access protected
   * @var bool shows whether there is an associated record in the database with the current non-expired and non-blocked session
   */	
  
  protected $UserLogedIn;
  
  /**
   * @access protected
   * @var string ip address of the user from whom current session cookies was recieved
   */	
  
  protected $UserIP;
  
  /**
   * @access protected
   * @var int id of the user from whom current session cookies was recieved
   */	

  protected $UserId;
  
  /**
   * @access protected
   * @var string id of the current session
   */	
  
  protected $SessionId;
  
  /**
   * @access protected
   * @var object used for session id encoding process, must implement SessionIdEncoder interface
   */	
  
  protected $EncodingAlgorithm;
  
	/* Core functions starts here */
  
  private function __construct()
    {    
    $this->CurPHPSesLifeTime = get_cfg_var("session.gc_maxlifetime");   
    $this->CurSesLifeTime = '30 minutes';
    $this->CurSesGarbageLifeTime = '3 days';
    
    $this->UserLogedIn = FALSE; 
    $this->UserIP = $_SERVER['REMOTE_ADDR'];
    $this->UserId = 0;
    }
    
  /**
   * Function that used to initialize instance of the current class.
   * 
   * Function initializes instance of the current class by setting general options. This is the first function that you must call
   * after creating instance of the class.   
   * 
   * @access public 
   * 
   * @param object of type plcMySQLDBConnector that is used to connect to the database. 
   * @param string name of the database table that is used to store session data.
   * @param array of various options (currently not used).
   * @param string path to a folder where expired sessions will be dumped. 
   * @param object that implements SessionIdEncoder interface.
   * 
   * @throws plcChassisException                    
   * 
   * @return bool returns TRUE on succes.         
   *                            
   */     
    
  public function Initialize (plcPrepStmtMYSQLDBConnector $usrDBConnector, $usrTableName, $usrOptions = '', $usrOldSesDumpFolder = '', SessionIdEncoder $usrEncodingAlgorithm)
    {       
    if (isset($usrDBConnector) === FALSE || empty($usrDBConnector))
      {
      throw new plcChassisException('Database object is not set.', 4101, null ,'Database object is not set or empty.');
      }    
      
    if (isset($usrTableName) === FALSE || empty($usrTableName))
      {
      throw new plcChassisException('Session table does not specified.', 4102, null ,'Session table does not specified or empty.');
      } 
     
    $this->DBConnector = $usrDBConnector;  
    $this->CurTableName = $usrTableName;
    $this->CurOptions = $usrOptions;
    $this->OldSessionDumpFolder = $usrOldSesDumpFolder;
    $this->EncodingAlgorithm = $usrEncodingAlgorithm;
    
    return TRUE;      
    }
    
  /**
   * Function that checks if there is a valid non-expired session record for current id.
   * 
   * This function simply searches for a session record in the database by supplied session id.
   * 
   * @access protected
   * 
   * @param string valid session id. 
   * 
   * @throws plcChassisException            
   * 
   * @return array|bool returns associative array that represents current session record from the database, 
   * FALSE if there is no record that represents valid session.       
   *                                 
   */ 
   
  protected function CheckValidSession($usrId)
    {            
    $this->DBConnector->SetPrepQuery('SELECT * FROM `'.$this->CurTableName.'` 
                                  WHERE 
                                  session_id = ? AND 
                                  sessionend < ? AND
                                  session_ip = ? AND
                                  issessionblock = 0 LIMIT 0, 1');
                                      
    $this->DBConnector->SetPrepQueryParams('sis', $usrId, (time()+strtotime('+'.$this->CurSesLifeTime)), $this->UserIP);                              
    return $this->DBConnector->GetPrepResultRow();
    }
    
  /**
   * Function that encodes session id by selected encoding algorithm.
   * 
   * This is the main encoding function for session handle class. It implements Strategy design patern paradigma so that
   * various algorithms may be used to encode session id.   
   * 
   * @access protected
   * 
   * @param array of data to be used in encoding process.
   * 
   * @throws plcChassisException             
   * 
   * @return string|bool returns encoded string on success and FALSE on fail.       
   *                                 
   */ 
    
  protected function EncodeSessionId($usrData)
    {    
    if (empty($this->EncodingAlgorithm) === TRUE)
      {
      return FALSE;
      }
    else
      {
      $this->EncodingAlgorithm->SetData($usrData);
      return $this->EncodingAlgorithm->Encode();          
      }
    }
    
 /**
   * Function that creates new session record and launches the session.
   * 
   * This function creates new session record as well as new id for it and launches the new session.
   * 
   * @access public
   * 
   * @param string misc session data. 
   * @param int user id. 
   * 
   * @throws plcChassisException               
   * 
   * @return bool returns TRUE on succesfull creation of the new session record.         
   *                                 
   */ 
    
  public function CreateSessionId($usrData, $usrId)
    {
    $tmpSessionId = '';
        
    $this->UserId = $usrId;
    $tmpSessionId = $this->EncodeSessionId($usrData);
      
    if ($tmpSessionId === FALSE)
      {      
      throw new plcChassisException('Session id is not created.', 4103, null ,'Session id is empty, thus encode fails.');
      }
      
    $this->DBConnector->SetPrepQuery('INSERT INTO `'.$this->CurTableName.'` 
                                 (
                                 userid, 
                                 sessionstart, 
                                 sessionend, 
                                 session_id, 
                                 session_expire, 
                                 session_ip, 
                                 issessionblock 
                                 )
                                 VALUES
                                 (
                                 ?,
                                 ?,
                                 ?,
                                 ?,
                                 ?,
                                 ?,
                                 0
                                 )');
    
    $this->DBConnector->SetPrepQueryParams('iiisis', $this->UserId, time(), strtotime('+'.$this->CurSesLifeTime), $tmpSessionId, (time()+$this->CurPHPSesLifeTime), $this->UserIP);                              
    $this->DBConnector->ExecutePrepQuery();
    
    session_id($tmpSessionId);
    $this->StartSession();
    
    return TRUE;                        
    }
    
  /**
   * Function that used to logout current user.
   * 
   * Simple function that logs out current user by blocking his session.
   * 
   * @access public 
   * 
   * @throws plcChassisException       
   * 
   * @return bool returns TRUE on succesfull logout.      
   *                            
   */ 
    
  public function LogoutUser()
    {
    $this->DBConnector->SetPrepQuery('UPDATE `'.$this->CurTableName.'` 
                                  SET 
                                  `'.$this->CurTableName.'`.issessionblock = 1
                                  WHERE
                                  `'.$this->CurTableName.'`.session_id = ?');                                  
                                   
    $this->DBConnector->SetPrepQueryParams('s', $this->GetSessionId());                              
    $this->DBConnector->ExecutePrepQuery();                              
    session_destroy();                                                              
      
    return TRUE;                                       
    }
    
  /**
   * Function that used to block all expired sessions in the database.
   * 
   * Current function blocks all expired session by setting 'issessionblock' value to 1 of each session record that is expired.
   * 
   * @access public 
   * 
   * @throws plcChassisException       
   * 
   * @return bool returns TRUE on succes.      
   *                            
   */ 
    
  public function BlockExpiredSessions()
    {    
    $this->DBConnector->SetPrepQuery('UPDATE `'.$this->CurTableName.'` 
                                     SET `'.$this->CurTableName.'`.issessionblock = 1
                                     WHERE
                                     `'.$this->CurTableName.'`.sessionend < ?');                                 
    
    $this->DBConnector->SetPrepQueryParams('i', time());                              
    $this->DBConnector->ExecutePrepQuery();   
                                        
    return TRUE;                                       
    }
    
  /**
   * Function that used to update current session.
   * 
   * Simple function that updates current session by regenerating session id and extending session life period.
   * 
   * @access public 
   * 
   * @param array of string values that is used by encoding algorithm to generate random session id. 
   * @param int user id.  
   * 
   * @throws plcChassisException                 
   * 
   * @return bool returns TRUE on succes.      
   *    
   * @see plcCookieDBSmplSecure::EncodeSessionId()                               
   */ 
    
  public function UpdateSession($usrData, $usrId)
    {
    $tmpSessionId = '';
    
    $this->UserId = $usrId;
    $tmpSessionId = $this->EncodeSessionId($usrData);
          
    if ($tmpSessionId === FALSE)
      {      
      throw new plcChassisException('Session id is not created.', 4103, null ,'Session id is empty, thus encode fails.');
      }    

    session_destroy();
    session_id($tmpSessionId);  
     
    $this->DBConnector->SetPrepQuery('UPDATE `'.$this->CurTableName.'` 
                                  SET 
                                  `'.$this->CurTableName.'`.sessionend = ?,
                                  `'.$this->CurTableName.'`.session_expire = ?,
                                  `'.$this->CurTableName.'`.session_id = ?
                                  WHERE
                                  `'.$this->CurTableName.'`.session_id = ?');  
    
    $this->DBConnector->SetPrepQueryParams('iiss', strtotime('+'.$this->CurSesLifeTime), strtotime('+'.$this->CurSesLifeTime), $tmpSessionId, $this->GetSessionId());                                                               
    $this->DBConnector->ExecutePrepQuery();                                                                                                                                                          
    $this->StartSession();
                                                                
    return TRUE;
    }
    
  /* Core functions ends here */
  
  /* Session specific functions starts here */
    
  /**
   * Function that used to startup current session.
   * 
   * Simple function that make necessary  preparations and launches the session. This is the first function in session
   * handle routine that you must call.   
   * 
   * @access public 
   *                            
   */ 
  
  public function StartSession()
    {
    session_set_save_handler(
    array(&$this, 'SessionOpen'),
    array(&$this, 'SessionClose'),
    array(&$this, 'SessionRead'),
    array(&$this, 'SessionWrite'),
    array(&$this, 'SessionDestroy'),
    array(&$this, 'SessionGC')
    );
    
    @session_start();
    }
    
  /**
   * Function that used to open session (only for internal usage by class).
   * 
   * This function is nearly dummy as its only purpose to call BlockExpiredSessions() function. You can overload this function 
   * in future and add aditional session preparation statements. Please do not call this function directly.  
   * 
   * @access public 
   * 
   * @param string path to a file where session information will be saved (dummy parametre). 
   * @param string name of session (dummy parametre).         
   *   
   * @see plcCookieDBSmplSecure::BlockExpiredSessions()                             
   */ 
    
  public function SessionOpen($usrSavePath, $usrSesName)
    { 
    $this->BlockExpiredSessions();  
    }
    
  /**
   * Function that is used to close session (only for internal usage by class).
   * 
   * This function is nearly dummy and only need to be present because of session implementation ruls. You can overload this 
   * function in future and add aditional statements befor session is completely closed. Please do not call this function directly.  
   * 
   * @access public 
   *                            
   */ 
    
  public function SessionClose()
    {
        
    }
    
  /**
   * Function that is used to read information about session from the database and store it in the current class object.
   * 
   * Simple function that reads necessary information about current session from the database and stores it in the current
   * class object.           
   * 
   * @access public
   * 
   * @param string valid session id.       
   * 
   * @throws plcChassisException      
   *                            
   */ 
    
  public function SessionRead($usrId)
    { 
    $this->SessionId = $usrId;
    
    $tmpResult = FALSE;     
    $tmpResult = $this->CheckValidSession($usrId);
          
    if ($tmpResult === FALSE)
      {
      $this->SetUserLogedIn(FALSE);
      }
    else
      {
      $this->SetUserLogedIn(TRUE);
      
      $this->UserId = $tmpResult[0]['userid'];
      }         
    }
    
  /**
   * Function that is used to write information connected with the current session.
   * 
   * Simple function that stores information connected with the current session in the database. Information is passed
   * by function argument. Currently this function does nothing usefull and is subject for feature modification. 
   * 
   * @param string valid session id.
   * @param string user session data.               
   * 
   * @access public 
   * 
   * @throws plcChassisException      
   *                            
   */ 
    
  public function SessionWrite($usrId, $usrData)
    {   
    $tmpResult = FALSE;
    /* Fix for session_start-destory DB link starts here */
       
    $tmpResult = $this->DBConnector->ReConnectToDB();
      
    /* Fix for session_start-destory DB link ends here */      
    }
    
  /**
   * Function that is called before session is being destroyed.
   * 
   * Simple function that is called before session is destroyed. Currently doing nothing usefull and is subject for feature modification.
   * 
   * @param string valid session id.          
   * 
   * @access public 
   *                            
   */ 
    
  public function SessionDestroy($usrId)
    {
    }
    
  /**
   * Function that is called by php session garbage collector.
   * 
   * This function dumps all records of expired sessions into the specific text file and deletes them from the database.
   * 
   * @param string maximum session life time period (dummy parametre).          
   * 
   * @access public
   * 
   * @throws plcChassisException       
   * 
   * @return bool returns TRUE on succes.       
   *                            
   */ 
    
  public function SessionGC($usrSesMaxLifeTime = '')
    {
    $tmpResult = '';
    $tmpFileName = date('Y-m-d').'_session_dump.txt';
    $tmpText = '';
    $Counter1 = 0;
    
    $this->DBConnector->SetPrepQuery('SELECT *  
                                      FROM `'.$this->CurTableName.'`
                                      WHERE
                                      session_expire <= ?');                                 
    
    $this->DBConnector->SetPrepQueryParams('i', strtotime('-'.$this->CurSesGarbageLifeTime));                              
    $tmpResult = $this->DBConnector->GetPrepResultAssoc();     
                             
    if ($tmpResult === FALSE)
      {
      return TRUE;
      }
      
    /* Old sessions records dump starts here */
      
    if (isset($this->OldSessionDumpFolder) && !empty($this->OldSessionDumpFolder))
      {
      if(is_dir($this->OldSessionDumpFolder) === FALSE)
        {
        throw new plcChassisException('Dump directory is not a directory.', 4104, null ,'Directory '.$this->OldSessionDumpFolder.' is not a directory.');
        }
      else if (is_writable($this->OldSessionDumpFolder) !== TRUE)
        {
        throw new plcChassisException('Dump directory is not writable.', 4105, null ,'Directory '.$this->OldSessionDumpFolder.' is not writable.');
        }
      else
        {
        for ($Counter1 = 0; $Counter1 < count($tmpResult); $Counter1++)
          {
          $tmpText .= "id: ".$tmpResult[$Counter1]['id']."\n";
          $tmpText .= "User id: ".$tmpResult[$Counter1]['userid']."\n";
          $tmpText .= "Session start: ".date('G:i:s Y-m-d', intval($tmpResult[$Counter1]['sessionstart']))."\n";
          $tmpText .= "Session end: ".date('G:i:s Y-m-d', intval($tmpResult[$Counter1]['sessionend']))."\n";
          $tmpText .= "Session id: ".$tmpResult[$Counter1]['session_id']."\n";
          $tmpText .= "Session expire: ".date('G:i:s Y-m-d', intval($tmpResult[$Counter1]['session_expire']))."\n";
          $tmpText .= "Session data: ".$tmpResult[$Counter1]['session_data']."\n";
          $tmpText .= "Session IP: ".$tmpResult[$Counter1]['session_ip']."\n";
          $tmpText .= "Is session block: ".$tmpResult[$Counter1]['issessionblock']."\n";
          $tmpText .= "===============================\n";
          }

        if (file_exists($this->OldSessionDumpFolder.'\\'.$tmpFileName) === TRUE)
          {
          if (is_writable($this->OldSessionDumpFolder.'\\'.$tmpFileName) === TRUE)
            {
            $tmpResult = file_put_contents($this->OldSessionDumpFolder.'\\'.$tmpFileName, $tmpText, FILE_APPEND);
            }
          else
            {
            throw new plcChassisException('Can not append old session dump to a file.', 4106, null ,'Old session dump file '.$this->OldSessionDumpFolder.'\\'.$tmpFileName.' is not writable.');
            }
          }
        else
          {
          $tmpResult = file_put_contents($this->OldSessionDumpFolder.'\\'.$tmpFileName, $tmpText, FILE_APPEND);
      
          if ($tmpResult === FALSE)
            {
            throw new plcChassisException('Can not create old session dump file.', 4107, null ,'Can not create old session dump file - '.$this->OldSessionDumpFolder.'\\'.$tmpFileName);
            }
          }
        
        
        }   
      }
    else
      { 
      throw new plcChassisException('Old session dump folder is not set.', 4108, null ,'Object can not make old sessions recorcds dump to log file. Old session records will be lost forever.');      
      }
      
    /* Old sessions records dump ends here */   
      
    $this->DBConnector->SetPrepQuery('DELETE  
                                      FROM `'.$this->CurTableName.'`
                                      WHERE
                                      session_expire <= ?');
    
    $this->DBConnector->SetPrepQueryParams('i', strtotime('-'.$this->CurSesGarbageLifeTime));                               
    $this->DBConnector->ExecutePrepQuery();
               
    return TRUE;  
    }
    
  /* Session specific functions ends here */
                                         
  /* Get functions starts here */
  
  /**
   * Function that returns current instance of the class.
   * 
   * This function implements singleton design pattern to return instance of the current class.
   *        
   * @access public 
   * 
   * @return object of type plcCookieDBSmplSecure.       
   *                            
   */ 
    
	public function GetInstance() 
		{
		if (plcCookieDBSmplSecure::$Instance === FALSE) 
			{
			plcCookieDBSmplSecure::$Instance = new plcCookieDBSmplSecure;
			}

		return plcCookieDBSmplSecure::$Instance;
		}
		
  /**
   * Function that indicates whether current user is logged in or not.
   * 
   * Simple function that represents whether user logged in or not.
   *        
   * @access public 
   * 
   * @return bool TRUE if user is logged in and FALSE if user is not logged in.       
   *                            
   */ 
		
	public function GetUserLogedIn()
	 {
	 return $this->UserLogedIn;
   }
   
  /**
   * Function that returns current user id.
   * 
   * Simple function that returns current user id but not session id.
   *        
   * @access public 
   * 
   * @return int provided earlier user id on success and zero if user id was not yet provided.       
   *                            
   */ 
   
  public function GetUserId()
    {
    return $this->UserId;
    }
    
  /**
   * Function that returns current session id.
   * 
   * Simple function that returns current session id but not user id.
   *        
   * @access public 
   * 
   * @return int provided earlier session id on success and empty result if it was not yet been provided.       
   *                            
   */ 
    
  public function GetSessionId()
    {
    return $this->SessionId;
    }
		
	/* Get functions ends here */
	
	/* Set functions starts here */
	
  /**
   * Function that sets flag based on whether user logged in or not.
   * 
   * Simple function that sets flag based on whether user logged in or not.
   *        
   * @access public 
   * 
   * @param bool user login state.    
   *                            
   */ 
	
	public function SetUserLogedIn($usrState)
    {
    $this->UserLogedIn = $usrState;
    }
	
	/* Set functions ends here */
  }
  
?>