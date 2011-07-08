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
 * Class plcErrorManager is a part of PHP framework - Chassis.   
 * 
 * @package     Chassis
 * @author      Selihov Sergei Stanislavovich <red331@mail.ru> 
 * @copyright   Copyright (c) 2010-2011 Selihov Sergei Stanislavovich.
 * @license     http://www.gnu.org/licenses/gpl.html  GNU GENERAL PUBLIC LICENSE (Version 3)
 *    
 */
 
/**
 * Classes for error handling.
 *  
 * @subpackage ErrorHandling
 * @author Selihov Sergei Stanislavovich <red331@mail.ru>   
 */
 
/**
 * Documents the plcErrorManager class.
 * 
 * Following class is a deprecated representation of error manager for Chassis framework. This classes may be used to rise and
 * to handle errors instead of standart exception model. This class implements singleton design pattern.
 *   
 * @subpackage plcErrorManager
 * @author Selihov Sergei Stanislavovich <red331@mail.ru>   
 */

require_once('Chassis/ErrorHandling/plcError.php');
require_once('Chassis/ErrorHandling/plcDebugger.php');
require_once('Chassis/ErrorHandling/plcAbstractErrorManager.php');

class plcErrorManager extends plcAbstractErrorManager
	{
  /**
   * @access protected
   * @var object holds instance of the current class 
   */	
	
	protected static $Instance = FALSE;
	
  /**
   * @access protected
   * @var array of error codes to ignore 
   */	

	protected $ErrorIgnores = array();
	
  /**
   * @access protected
   * @var array of error codes to expect 
   */	
	
	protected $ErrorExcepts = array();
	
  /**
   * @access protected
   * @var array of options of how to handle error of each type  
   */	

	protected $ErrorHandlingModes = array(PLC_NOTICE => array('mode' => 'echo'),
				                                PLC_WARNING => array('mode' => 'echo'),
				                                PLC_ERROR => array('mode' => 'die')
				                               );

	private function __construct()
		{
		}
		
  /**
   * Function that is used to test whether usr provided object is error object or not.
   * 
   * Simple function that is used to test whether usr provided object is error object (descendents of class plcAbstractError) 
   * or not.
   * 
   * @access public 
   * 
   * @param object to be tested.      
   *    
   * @return bool TRUE on success and FALSE on fail.  
   *                              
   */ 

	public function IsError($usrObject)
		{
		if(!is_object($usrObject))
			{
			return FALSE;
			}

		if(strtolower(get_class($usrObject)) != strtolower('plcAbstractError') && is_subclass_of($usrObject, 'plcAbstractError'))
			{
			return TRUE;
			}
		
		return FALSE;
		}
		
  /**
   * Function that is used to raise error (creates object that is descendent of class plcAbstractError).
   * 
   * Simple function that is used to raise error (creates object that is descendent of class plcAbstractError). This function
   * also handles error based on class stored options.   
   * 
   * @access public 
   * 
   * @param const error level name.
   * @param int error code number.  
   * @param string short error information.
   * @param string full error information.      
   *    
   * @return object that is descendent of class plcAbstractError (error object).
   * 
   * @see plcErrorManager::HandleError()        
   *                              
   */

	public function Raise ($usrLevel, $usrCode, $usrMessage, $usrInfo = null)
		{
		if(in_array($usrCode, $this->ErrorIgnores))
			{
			return false;
			}

		$usrError = new plcError($usrLevel, $usrCode, $usrMessage, $usrInfo);

		if (!empty($this->ErrorExcepts))
			{
			$tmpExpected = array_pop($this->ErrorExcepts);

			if($usrCode == $tmpExpected)
				{
				return false;
				}

			array_push($this->ErrorExcepts, $tmpExpected);
			}

		$tmpHandling = $this->GetErrorHandling($usrLevel);
		return $this->HandleError($tmpHandling, $usrError);
		}
		
  /**
   * Function that is used to raise notice (error object which error level is set to PLC_NOTICE).
   * 
   * Simple function that is used to raise notice (error object which error level is set to PLC_NOTICE). 
   * 
   * @access public 
   * 
   * @param int error code number.  
   * @param string short error information.
   * @param string full error information.      
   *    
   * @return object that is descendent of class plcAbstractError (error object).
   * 
   * @see plcErrorManager::Raise()   
   * @see plcErrorManager::HandleError()        
   *                              
   */

	public function RaiseNotice($usrCode, $usrMessage, $usrInfo = null)
		{
		return $this->Raise(PLC_NOTICE, $usrCode, $usrMessage, $usrInfo);
		}
		
  /**
   * Function that is used to raise warning (error object which error level is set to PLC_WARNING).
   * 
   * Simple function that is used to raise warning (error object which error level is set to PLC_WARNING). 
   * 
   * @access public 
   * 
   * @param int error code number.  
   * @param string short error information.
   * @param string full error information.      
   *    
   * @return object that is descendent of class plcAbstractError (error object).
   * 
   * @see plcErrorManager::Raise()   
   * @see plcErrorManager::HandleError()        
   *                              
   */

	public function RaiseWarning($usrCode, $usrMessage, $usrInfo = null)
		{
		return $this->Raise(PLC_WARNING, $usrCode, $usrMessage, $usrInfo);
		}
		
  /**
   * Function that is used to raise error (error object which error level is set to PLC_ERROR).
   * 
   * Simple function that is used to raise error (error object which error level is set to PLC_ERROR). 
   * 
   * @access public 
   * 
   * @param int error code number.  
   * @param string short error information.
   * @param string full error information.      
   *    
   * @return object that is descendent of class plcAbstractError (error object).
   * 
   * @see plcErrorManager::Raise()   
   * @see plcErrorManager::HandleError()        
   *                              
   */

	public function RaiseError($usrCode, $usrMessage, $usrInfo = null)
		{
		return $this->Raise(PLC_ERROR, $usrCode, $usrMessage, $usrInfo);
		}
		
  /**
   * Function that is used to register new error level.
   * 
   * Simple function that is used to register new error level. 
   * 
   * @access public 
   * 
   * @param const error level name.   
   * @param string error handle mode.  
   * @param object debugger object (if $usrMode is set to 'callback').      
   *    
   * @return bool FALSE if such error level already exist.
   *  
   * @see plcErrorManager::SetErrorHandling()        
   *                              
   */

	public function RegisterErrorLevel($usrLevel, $usrMode, $usrObject)
		{
		if (isset($this->ErrorHandlingModes[$usrLevel]))
			{
			return false;
			}

		$this->ErrorHandlingModes[$usrLevel] = array();

		$this->SetErrorHandling($usrLevel, $usrMode, $usrObject);
		}
		
  /**
   * Function that is used to add ignore code to the object storage.
   * 
   * Simple function that is used to add ignore code to the object storage. If error was raised with such a code it will be
   * ignored by the error manager object.    
   * 
   * @access public 
   * 
   * @param int error code to ignore.       
   *    
   * @return bool true if error code was successufly add.
   *                                    
   */

	public function AddErrorIgnore($usrCode)
		{
		if(!is_array($usrCode))
			{
			$usrCode = array($usrCode);
			}
	
		$usrCode = array_merge($this->ErrorIgnores, $usrCode );
		$this->ErrorIgnores = array_unique($usrCode);

		return true;
		}

  /**
   * Function that is used to remove ignore code to the object storage.
   * 
   * Simple function that is used to remove ignore code to the object storage. If error was raised with such a code it will not 
   * be ignored by the error manager object.    
   * 
   * @access public 
   * 
   * @param int error code to ignore.       
   *    
   * @return bool true if error code was successufly removed.
   *                                    
   */
	
	public function RemoveErrorIgnore($usrCode)
		{
		if(!is_array($usrCode))
			{
			$usrCode = array($usrCode);
			}
		
		foreach($usrCode as $tmpCode)
			{
			$tmpKey	= array_search($tmpCode, $this->ErrorIgnores);

			if($tmpKey === false)
				{
				continue;
				}
			
			unset($this->ErrorIgnores[$tmpKey]);
			}

		
		$this->ErrorIgnores = array_values($this->ErrorIgnores);
		
		return true;
		}
		
  /**
   * Function that is used to clear all ignore codes from the object storage.
   * 
   * Simple function that is used to clear all ignore codes from the object storage. 
   * 
   * @access public 
   *       
   * @return bool true.
   *                                    
   */

	public function ClearErrorIgnore()
		{
		$this->ErrorIgnores = array();

		return true;
		}
		
  /**
   * Function that is used to add new error code to error expect stack.
   * 
   * Simple function that is used to add new error code to error expect stack. 
   * 
   * @access public 
   * 
   * @param int error code to add.       
   *       
   * @return bool true.
   *                                    
   */

	public function PushErrorExpect($usrCode)
		{
		array_push($this->ErrorExcepts, $usrCode);
		
		return true;
		}
		
  /**
   * Function that is used to remove error code from the error expect stack.
   * 
   * Simple function that is used to remove error code from the error expect stack. 
   * 
   * @access public      
   *       
   * @return bool true.
   *                                    
   */

	public function PopErrorExpect()
		{
		array_pop($this->ErrorExcepts);
		
		return true;
		}

  /**
   * Function that is used to handle current error.
   * 
   * Simple function that is used to handle current error based on a provided error handle mode. 
   * 
   * @access protected
   * 
   * @param string error handle mode.
   * @param object error object.                
   *       
   * @return object that is descendent of class plcAbstractError (error object).
   * 
   * @see plcErrorManager::HandleErrorIgnore()
   * @see plcErrorManager::HandleErrorEcho()    
   * @see plcErrorManager::HandleErrorVerbose() 
   * @see plcErrorManager::HandleErrorTrigger()
   * @see plcErrorManager::HandleErrorCallback() 
   * @see plcErrorManager::HandleErrorDie()                    
   *                                    
   */

	protected function HandleError($usrHandleMode, $usrError)
		{
		switch($usrHandleMode['mode'])
			{
			case 'ignore':

			return $this->HandleErrorIgnore($usrError);
			break;

			case 'echo':
			return $this->HandleErrorEcho($usrError);
			break;

			case 'verbose':

			return $this->HandleErrorVerbose($usrError);
			break;

			case 'trigger':

			return $this->HandleErrorTrigger($usrError);
			break;

			case 'callback':

			return $this->HandleErrorCallback($usrError);
			break;

			case 'die':

			return $this->HandleErrorDie($usrError);
			break;
			}
		}

  /**
   * Function that is used to handle error object.
   * 
   * Simply ignores error object and returns it. 
   * 
   * @access protected
   * 
   * @param object error object.                
   *       
   * @return object that is descendent of class plcAbstractError (error object).
   * 
   * @see plcErrorManager::HandleError()                  
   *                                    
   */

	protected function HandleErrorIgnore($usrError)
		{
		return $usrError;
		}
		
  /**
   * Function that is used to handle error object.
   * 
   * Prints short information about error object. 
   * 
   * @access protected
   * 
   * @param object error object.                
   *       
   * @return object that is descendent of class plcAbstractError (error object).
   * 
   * @see plcErrorManager::HandleError()                  
   *                                    
   */

	protected function HandleErrorEcho($usrError)
		{
		$tmpErrorLevel = $usrError->GetErrorLevel();
		$tmpErrorCode = $usrError->GetErrorCode();
		$tmpErrorMessage = $usrError->GetErrorMessage();

		?>

                <div style="width: 100%;">
                 <div style="display: block; border: 1px solid #33CCFF; padding: 3px;">
                  <span style="font-family: courier; font-size: 16px; font-weight: bold; color: #990099;"><?php echo($tmpErrorLevel);  ?>:</span>
                  <span style="font-family: times; font-size: 14px; font-weight: normal; color: #CC0033;"><?php echo($tmpErrorCode); ?>; &nbsp;</span>
                  <span style="font-family: times; font-size: 14px; font-weight: normal; color: #330066;"><?php echo($tmpErrorMessage); ?></span>
                 </div>
                </div>

		<?php

		return $usrError;
		}
		
  /**
   * Function that is used to handle error object.
   * 
   * Prints full information about error object. 
   * 
   * @access protected
   * 
   * @param object error object.                
   *       
   * @return object that is descendent of class plcAbstractError (error object).
   * 
   * @see plcErrorManager::HandleError()                  
   *                                    
   */

	protected function HandleErrorVerbose($usrError)
		{
		$tmpErrorLevel = $usrError->GetErrorLevel();
		$tmpErrorCode = $usrError->GetErrorCode();
		$tmpErrorMessage = $usrError->GetErrorMessage();

		$tmpErrorInfo = $usrError->GetErrorInfo();
		$tmpErrorFile = $usrError->GetErrorFileName();
		$tmpErroLine = $usrError->GetErrorLineNumber();


		?>

                <div style="width: 100%;">
                 <div style="display: block; border: 1px solid #33CCFF; padding: 3px;">
                  <span style="font-family: courier; font-size: 16px; font-weight: bold; color: #990099;"><?php echo($tmpErrorLevel);  ?>:</span>
                  <span style="font-family: times; font-size: 14px; font-weight: normal; color: #CC0033;"><?php echo($tmpErrorCode); ?>; &nbsp;</span>
                  <span style="font-family: times; font-size: 14px; font-weight: normal; color: #330066;"><?php echo($tmpErrorMessage); ?></span>

                  <br>

                  <span style="font-family: times; font-size: 14px; font-weight: normal; color: #CC9933;">Info:&nbsp;</span>
                  <span style="font-family: times; font-size: 14px; font-weight: normal; color: #FF0033;"><?php echo($tmpErrorInfo); ?></span>

                  <br>

                  <span style="font-family: times; font-size: 14px; font-weight: normal; color: #CC9933;">File:&nbsp;</span>
                  <span style="font-family: times; font-size: 14px; font-weight: normal; color: #FF0033;"><?php echo($tmpErrorFile); ?></span>


                  <br>

                  <span style="font-family: times; font-size: 14px; font-weight: normal; color: #CC9933;">Line:&nbsp;</span>
                  <span style="font-family: times; font-size: 14px; font-weight: normal; color: #FF0033;"><?php echo($tmpErroLine); ?></span>
                 </div>
                </div>

		<?php

		return $usrError;
		}
		
  /**
   * Function that is used to handle error object.
   * 
   * Triggers PHP error. 
   * 
   * @access protected
   * 
   * @param object error object.                
   *       
   * @return object that is descendent of class plcAbstractError (error object).
   * 
   * @see plcErrorManager::HandleError()                  
   *                                    
   */

	protected function HandleErrorTrigger($usrError)
		{
		switch($usrError->GetErrorLevel())
			{
			case 'PLC_NOTICE':

			$PHPErrorLevel = E_USER_NOTICE;
			break;

			case 'PLC_WARNING':

			$PHPErrorLevel = E_USER_WARNING;
			break;

			case 'PLC_ERROR':

			$PHPErrorLevel = E_USER_ERROR;
			break;

			default:

			$PHPErrorLevel = E_USER_ERROR;
			break;
			}
	
		trigger_error($usrError->GetErrorMessage(), $PHPErrorLevel);

		return $usrError;
		}
		
  /**
   * Function that is used to handle error object.
   * 
   * Calls Debug() method of provided earliar debug object. 
   * 
   * @access protected
   * 
   * @param object error object.                
   *       
   * @return object that is descendent of class plcAbstractError (error object).
   * 
   * @see plcErrorManager::HandleError()                  
   *                                    
   */

	protected function HandleErrorCallback($usrError)
		{
		$tmpLevel = $usrError->GetErrorLevel();

		$this->ErrorHandlingModes[$tmpLevel]['object']->Debug();
		
		return $usrError;
		}
		
  /**
   * Function that is used to handle error object.
   * 
   * Prints short info about error object and stops execution of the script. 
   * 
   * @access protected
   * 
   * @param object error object.                
   *       
   * @see plcErrorManager::HandleError()                  
   *                                    
   */

	protected function HandleErrorDie($usrError)
		{
		$tmpErrorLevel = $usrError->GetErrorLevel();
		$tmpErrorCode = $usrError->GetErrorCode();
		$tmpErrorMessage = $usrError->GetErrorMessage();

		?>

                <div style="width: 100%;">
                 <div style="display: block; border: 1px solid #33CCFF; padding: 3px;">
                  <span style="font-family: courier; font-size: 16px; font-weight: bold; color: #990099;"><?php echo($tmpErrorLevel);  ?>:</span>
                  <span style="font-family: times; font-size: 14px; font-weight: normal; color: #CC0033;"><?php echo($tmpErrorCode); ?>; &nbsp;</span>
                  <span style="font-family: times; font-size: 14px; font-weight: normal; color: #330066;"><?php echo($tmpErrorMessage); ?></span>

		  <br>

                  <span style="font-family: times; font-size: 14px; font-weight: normal; color: red;">Stopping execution</span>
                 </div>
                </div>

		<?php

		die();
		}
		
  /**
   * Function that is used to return current instace of the plcErrorManager class.
   * 
   * Simple function that is used to return current instace of the plcErrorManager class (singleton pattern).  
   * 
   * @access public
   *                        
   * @return object current instace of the plcErrorManager class.       
   *                                    
   */

	public function GetInstance() 
		{
		if (plcErrorManager::$Instance === FALSE) 
			{
			plcErrorManager::$Instance = new plcErrorManager;
			}

		return plcErrorManager::$Instance;
		}
		
  /**
   * Function that is used to return current error handling mode for user provided error level.
   * 
   * Simple function that is used to return current error handling mode for user provided error level.  
   * 
   * @access public
   * 
   * @param const error level.       
   *                    
   * @return string error handling mode for.       
   *                                    
   */

	public function GetErrorHandling($usrLevel)
		{
		return $this->ErrorHandlingModes[$usrLevel];
		}
		
  /**
   * Function that is used to return current array of error codes to ignore.
   * 
   * Simple function that is used to return current array of error codes to ignore.  
   * 
   * @access public   
   *                    
   * @return array of codes to ignore.       
   *                                    
   */

	public function GetIgnore()
		{
		return $this->ErrorIgnores;
		}
		
  /**
   * Function that is used to return current array of error codes to expect.
   * 
   * Simple function that is used to return current array of error codes to expect.  
   * 
   * @access public   
   *                    
   * @return array of codes to expect.       
   *                                    
   */

	public function GetErrorExpect()
		{
		return $this->ErrorExcepts;
		}
		
  /**
   * Function that is used to set error handling mode for user provided error level.
   * 
   * Simple function that is used to set error handling mode for user provided error level.  
   * 
   * @access public  
   * 
   * @param const error level name.   
   * @param string error handle mode.  
   * @param object debugger object (if $usrMode is set to 'callback').         
   *                    
   * @return bool false on fail.       
   *                                    
   */

	public function SetErrorHandling($usrLevel, $usrMode, $usrObject="")
		{
		$tmpMode = array('ignore', 'echo', 'verbose', 'trigger', 'callback', 'die');

		if (!in_array($usrMode, $tmpMode))
			{
			return false;
			}

		foreach($this->ErrorHandlingModes as $tmpLevel => $tmpValue)
			{
			if ($usrLevel == $tmpLevel)
				{
				if ($usrMode == 'callback')
					{
					if((isset($usrObject)) && (strtolower(get_class($usrObject)) == strtolower('plcDebugger') || is_subclass_of($usrObject, 'plcAbstractDebugger')))
						{
						$this->ErrorHandlingModes[$usrLevel]['mode'] = $usrMode;
						$this->ErrorHandlingModes[$usrLevel]['object'] = $usrObject;
						}
					else
						{
						return false;
						}
					}
				else
					{
					$this->ErrorHandlingModes[$usrLevel]['mode'] = $usrMode;
					}
				}
			}
		}
	}

?>