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
 * Class plcAbstractError is a part of PHP framework - Chassis.   
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
 * Documents the plcAbstractError class.
 * 
 * Following class is abstract class for plcAbstractError.
 *   
 * @subpackage plcAbstractError
 * @author Selihov Sergei Stanislavovich <red331@mail.ru>   
 */

abstract class plcAbstractError
	{
	abstract protected function CreateErrorObject($usrLevel, $usrCode, $usrMessage, $usrInfo = null);

	abstract public function GetErrorLevel();
	abstract public function GetErrorCode();
	abstract public function GetErrorMessage();
	abstract public function GetErrorInfo();
	abstract public function GetErrorFileName();
	abstract public function GetErrorLineNumber();
	abstract public function GetErrorBacktrace();
	}

?>