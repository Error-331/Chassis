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
 * Class plcAbstractErrorManager is a part of PHP framework - Chassis.   
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
 * Documents the plcAbstractErrorManager class.
 * 
 * Following class is abstract class for plcAbstractErrorManager.
 *   
 * @subpackage plcAbstractErrorManager
 * @author Selihov Sergei Stanislavovich <red331@mail.ru>   
 */

abstract class plcAbstractErrorManager
	{
	abstract public function IsError($usrObject);

	abstract public function Raise ($usrLevel, $usrCode, $usrMessage, $usrInfo = null);
	abstract public function RaiseNotice($usrCode, $usrMessage, $usrInfo = null);
	abstract public function RaiseWarning($usrCode, $usrMessage, $usrInfo = null);
	abstract public function RaiseError($usrCode, $usrMessage, $usrInfo = null);

	abstract public function RegisterErrorLevel($usrLevel, $usrMode, $usrObject);
	abstract public function AddErrorIgnore($usrCode);
	
	abstract public function RemoveErrorIgnore($usrCode);
	abstract public function ClearErrorIgnore();

	abstract public function PushErrorExpect($usrCode);
	abstract public function PopErrorExpect();

	abstract protected function HandleError($usrHandleMode, $usrError);
	abstract protected function HandleErrorIgnore($usrError);
	abstract protected function HandleErrorEcho($usrError);
	abstract protected function HandleErrorVerbose($usrError);
	abstract protected function HandleErrorTrigger($usrError);
	abstract protected function HandleErrorCallback($usrError);
	abstract protected function HandleErrorDie($usrError);

	abstract public function GetInstance() ;

	abstract public function GetErrorHandling($usrLevel);
	abstract public function GetIgnore();
	abstract public function GetErrorExpect();

	abstract public function SetErrorHandling($usrLevel, $usrMode, $usrObject="");
	}

?>