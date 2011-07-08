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
 * Class plcAbstractCookie is a part of PHP framework - Chassis.   
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
 * Documents the plcAbstractCookie class.
 * 
 * Following class is abstract class for plcAbstractCookie.
 *   
 * @subpackage plcAbstractCookie
 * @author Selihov Sergei Stanislavovich <red331@mail.ru>   
 */

abstract class plcAbstractCookie
	{	
	abstract public function Initialize (SessionIdEncoder $usrEncodingAlgorithm = null);
	abstract protected function EncodeSessionId($usrData);
	abstract public function StartSession();
	
	abstract public function GetInstance();
	abstract public function GetPHPSesLifeTime();
	abstract public function GetSassionSavePath();
	abstract public function GetSessionId();	
	abstract public function GetUserIP();
	abstract public function GetEncodingAlgorithm();
	abstract public function GetSessionVar($usrVarName);
	
	abstract public function SetSessionId($usrSessionId);
	abstract public function SetEncodingAlgorithm(SessionIdEncoder $usrEncodingAlgorithm);
	abstract public function SetSessionVar($usrVarName, $usrVarValue);
	}

?>