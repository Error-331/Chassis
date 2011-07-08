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
 * Class plcAbstractErrorLog is a part of PHP framework - Chassis.   
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
 * Documents the plcAbstractErrorLog class.
 * 
 * Following class is abstract class for plcAbstractErrorLog.
 *   
 * @subpackage plcAbstractErrorLog
 * @author Selihov Sergei Stanislavovich <red331@mail.ru>   
 */

abstract class plcAbstractErrorLog
	{
	abstract public function LogError($usrError);
	
	abstract public function GetInstance();
	
	abstract public function SetDir($usrDir);
	}

?>