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
 * Class plcCookieSmpl is a part of PHP framework - Chassis.   
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
 * Documents the plcCookieSmpl class.
 * 
 * Following class is intended for simplification of tasks connected to the work with session handle on server via
 * browser cookies. 
 * 
 * Class plcCookieDBSmplSecure implements Singleton design pattern so that there is only one copy of session handle object at a 
 * time and strategy pattern to dynamicly swap session id encoding algorithm.  
 *   
 * @subpackage plcCookieSmpl
 * @author Selihov Sergei Stanislavovich <red331@mail.ru>   
 */
 
require_once('Chassis/ErrorHandling/plcChassisException.php');
require_once('Chassis/SessionHandle/plcAbstractCookie.php');
 
class plcCookieSmpl extends plcAbstractCookie
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
   * @var string ip address of the user from whom current session cookies was recieved
   */	
  
  protected $UserIP;
    
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
  
  private function __construct(SessionIdEncoder $usrEncodingAlgorithm = null)
    {
    $this->CurPHPSesLifeTime = get_cfg_var("session.gc_maxlifetime");   
    $this->CurSesLifeTime = '30 minutes';
    
    $this->UserIP = $_SERVER['REMOTE_ADDR'];
    }
    
  /**
   * Function that used to initialize instance of the current class.
   * 
   * Function initializes instance of the current class by setting encoding algorithm class. This is the first function that you must call
   * after creating instance of the class.   
   * 
   * @access public 
   * 
   * @param object that implements SessionIdEncoder interface              
   * 
   * @return bool returns TRUE on succes.         
   *                            
   */     
    
  public function Initialize (SessionIdEncoder $usrEncodingAlgorithm = null)
    {       
    $this->EncodingAlgorithm = $usrEncodingAlgorithm;
    
    return TRUE;      
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
    $tmpResult = '';
    
    $usrData = array(0 => $usrData);
    
    if (empty($this->EncodingAlgorithm) === TRUE || $this->EncodingAlgorithm === null)
      {
      return FALSE;
      }
    else
      {
      $this->EncodingAlgorithm->SetData($usrData);
      $tmpResult = $this->EncodingAlgorithm->Encode();
      
      return $tmpResult;        
      }
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
    @session_start();
    $this->SessionId = session_id();  
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
   * @return object of type plcCookieSmpl.       
   *                            
   */ 
    
	public function GetInstance() 
		{
		if (plcCookieSmpl::$Instance === FALSE) 
			{
			plcCookieSmpl::$Instance = new plcCookieSmpl;
			}

		return plcCookieSmpl::$Instance;
		}
		
  /**
   * Function that returns default session life time.
   * 
   * Simple function that returns default session life time (gc_maxlifetime).
   *        
   * @access public 
   * 
   * @return string session life time.       
   *                            
   */ 
		
  public function GetPHPSesLifeTime()
    {
    return $this->CurPHPSesLifeTime;
    }
		
  /**
   * Function that returns session save folder.
   * 
   * Simple function that returns session save folder. Used for default session functionality.
   *        
   * @access public 
   * 
   * @return string|bool path to session save folder and FALSE on failure.       
   *                            
   */ 
		
	public function GetSassionSavePath()
    {
    $tmpResult = ini_get("session.save_path");
    
    if ($tmpResult === null || empty($tmpResult) === TRUE || isset($tmpResult) === FALSE)
      {
      return FALSE;
      }
    else
      {
      return $tmpResult;
      }
    }
    
  /**
   * Function that returns current session id.
   * 
   * Simple function that returns current session id but not user id.
   *        
   * @access public 
   * 
   * @throws plcChassisException      
   * 
   * @return string provided earlier session id on success.       
   *                            
   */ 
    
  public function GetSessionId()
    {
    if ($this->SessionId === null || empty($this->SessionId) === TRUE || isset($this->SessionId) === FALSE)
      {
      throw new plcChassisException('Session is not set yet.', 4101, null ,'Session is not set yet or session is not yet started.');
      }    
    
    return $this->SessionId;
    }
    
  /**
   * Function that returns current remote user IP address.
   * 
   * Simple function that returns current remote user IP address.
   *        
   * @access public    
   * 
   * @return string current remote user IP address.       
   *                            
   */     
    
  public function GetUserIP()
    {
    return $this->UserIP;
    }
    
  /**
   * Function that returns current encoding algorithm for session id.
   * 
   * Simple function that returns current encoding algorithm for session id.
   *        
   * @access public    
   * 
   * @return object current encoding algorithm for session id.       
   *                            
   */  
    
  public function GetEncodingAlgorithm()
    {
    return $this->EncodingAlgorithm;
    }
    
  /**
   * Function that returns session variable.
   * 
   * Simple function that returns session variable.
   *        
   * @access public 
   * 
   * @throws plcChassisException         
   * 
   * @param string session variable name.     
   *                            
   */  
    
  public function GetSessionVar($usrVarName)
    {
    if (empty($usrVarName) === TRUE || isset($usrVarName) === FALSE || is_string($usrVarName) === FALSE)
      {
      throw new plcChassisException('Session variable name is empty or invalid type.', 4102, null ,'User does not specified session variable name or it is not of string type.');
      }
      
    return $_SESSION[$usrVarName];
    }
  
  /* Get functions ends here */
  
  /* Set functions starts here */
  
  /**
   * Function that sets current session id.
   * 
   * Simple function that sets current session id.
   *        
   * @access public 
   * 
   * @throws plcChassisException          
   * 
   * @return bool returns TRUE on success.       
   *                            
   */  
  
  public function SetSessionId($usrSessionId)
    {
    $tmpSessionData = array();
    $tmpSessionId = $this->EncodeSessionId($usrSessionId);
    
    if ($tmpSessionId === FALSE)
      {
      $tmpSessionId = $usrSessionId;
      }
    
    $tmpSessionData = $_SESSION;
 
    session_destroy();
    session_id($tmpSessionId);  
     
    $this->SessionId = $usrSessionId;
    $this->StartSession(); 

    $_SESSION = $tmpSessionData; 

    return TRUE;
    }
    
  /**
   * Function that sets current encoding algorithm for session id.
   * 
   * Simple function that sets current encoding algorithm for session id.
   *        
   * @access public 
   * 
   * @param object of SessionIdEncoder interface.    
   *                            
   */     
    
  public function SetEncodingAlgorithm(SessionIdEncoder $usrEncodingAlgorithm)
    {
    $this->EncodingAlgorithm = $usrEncodingAlgorithm;
    }

  /**
   * Function that sets session variable.
   * 
   * Simple function that sets session variable.
   *        
   * @access public 
   * 
   * @throws plcChassisException         
   * 
   * @param string session variable name.   
   * @param string session variable value.     
   *                            
   */  
    
  public function SetSessionVar($usrVarName, $usrVarValue)
    {
    if (empty($usrVarName) === TRUE || isset($usrVarName) === FALSE || is_string($usrVarName) === FALSE)
      {
      throw new plcChassisException('Session variable name is empty or invalid type.', 4102, null ,'User does not specified session variable name or it is not of string type.');
      }
    
    if (empty($usrVarValue) === TRUE || isset($usrVarValue) === FALSE)
      {
      throw new plcChassisException('Session variable value is empty.', 4103, null ,'User does not specified session variable value.');
      }
    
    $_SESSION[$usrVarName] = $usrVarValue;
    }
              
  /* Set functions ends here */
  }

?>