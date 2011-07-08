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
 * Class plcErrorLogException is a part of PHP framework - Chassis.   
 * 
 * @package     Chassis
 * @author      Selihov Sergei Stanislavovich <red331@mail.ru> 
 * @copyright   Copyright (c) 2010-2011 Selihov Sergei Stanislavovich.
 * @license     http://www.gnu.org/licenses/gpl.html  GNU GENERAL PUBLIC LICENSE (Version 3)
 *    
 */
 
/**
 * Classes for error logging.
 *  
 * @subpackage ErrorLog
 * @author Selihov Sergei Stanislavovich <red331@mail.ru>   
 */
 
/**
 * Documents the plcErrorLogException class.
 * 
 * Following class is intended for logging errors into text files. All error objects must be descendents 
 * of class plcChassisException.
 *   
 * @subpackage plcErrorLogSimple
 * @author Selihov Sergei Stanislavovich <red331@mail.ru>   
 */
 
require_once('Chassis/ErrorHandling/plcChassisException.php');
require_once('Chassis/ErrorLog/plcAbstractErrorLog.php');

class plcErrorLogException extends plcAbstractErrorLog
  {
  /**
   * @access protected
   * @var mixed instance of the current class 
   */	
  protected static $Instance = FALSE;
  
  /**
   * @access protected
   * @var string path to directory where all error logs will be saved 
   */	  
  protected $CurDir;
  
  private function __construct()
    {
    }
     
  /**
   * Function that is used to log single error object.
   * 
   * Simple function is used to log single error object.
   * 
   * @access public       
   * 
   * @param object that is descendent of plcAbstractError class. 
   * 
   * @return bool returns true on success and false on fail.            
   *                             
   */ 
    
  public function LogError($usrError)
    {
    $tmpResult = '';
    $tmpFileName = date('Y-m-d').'_log.txt';
    $tmpText = '';

    if (empty($usrError) || (isset($usrError) === FALSE))
      {
      return false;
      }
       
    if (empty($this->CurDir) || !file_exists($this->CurDir)) 
      {
      return false;
      }
    
    $tmpText =  "Time:".date('G:i:s Y-m-d')."\n";
    $tmpText .= "Type: Error(plcErrorLogException) \n";  
    $tmpText .= "Error_num:".$usrError->getCode()."\n";
    $tmpText .= "Error_mes:".$usrError->getMessage()."\n";
    $tmpText .= "Error_info:".$usrError->GetFullInfo()."\n";
    $tmpText .= "===============================\n";
     
    if (file_exists($this->CurDir.'\\'.$tmpFileName) === TRUE)
      {
      if (is_writable($this->CurDir.'\\'.$tmpFileName) === TRUE)
        {
        $tmpResult = file_put_contents($this->CurDir.'\\'.$tmpFileName, $tmpText, FILE_APPEND);
        }
      else
        {
        return false;
        }
      }
    else
      {
      $tmpResult = file_put_contents($this->CurDir.'\\'.$tmpFileName, $tmpText, FILE_APPEND);
      
      if ($tmpResult === FALSE)
        {
        return false;
        }
      }
      
    return true;
    }
    
  /**
   * Function that is used to return instance of the current class.
   * 
   * Simple function is used to return instance of the current class.
   * 
   * @access public       
   * 
   * @return object instance of the current class.        
   *                             
   */ 
    
	public function GetInstance() 
		{
		if (plcErrorLogException::$Instance === FALSE) 
			{
			plcErrorLogException::$Instance = new plcErrorLogException;
			}

		return plcErrorLogException::$Instance;
		}
		
  /**
   * Function that is used to set path to a directory where all error logs will be saved.
   * 
   * Simple function is used to set path to a directory where all error logs will be saved.
   * 
   * @access public 
   * 
   * @param string path to a directory.      
   * 
   * @throws plcChassisException            
   * 
   * @return bool TRUE on success.        
   *                             
   */ 
    
  public function SetDir($usrDir)
    {
    if(is_dir($usrDir) === FALSE)
      {
      throw new plcChassisException('Log directory is not a directory', 3201, null ,'Directory '.$usrDir.' is not a directory.');
      }
    if (is_writable($usrDir) !== TRUE)
      {
      throw new plcChassisException('Log directory is not writable.', 3202, null ,'Directory '.$usrDir.' is not writable.');
      } 
      
    $this->CurDir = $usrDir;
     
    return TRUE;  
    }
  }

?>