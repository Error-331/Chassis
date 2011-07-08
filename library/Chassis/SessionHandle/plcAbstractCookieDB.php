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
 * Class plcAbstractCookieDB is a part of PHP framework - Chassis.   
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
 * Documents the plcAbstractCookieDB class.
 * 
 * Following class is abstract class for plcAbstractCookieDB.
 *   
 * @subpackage plcAbstractCookieDB
 * @author Selihov Sergei Stanislavovich <red331@mail.ru>   
 */

abstract class plcAbstractCookieDB
	{
  abstract public function Initialize (plcPrepStmtMYSQLDBConnector $usrDBConnector, $usrTableName, $usrOptions = '', $usrOldSesDumpFolder = '', SessionIdEncoder $usrEncodingAlgorithm);
  abstract protected function CheckValidSession($usrId);
  abstract protected function EncodeSessionId($usrData);
  abstract public function CreateSessionId($usrData, $usrId);
  abstract public function LogoutUser();
  abstract public function BlockExpiredSessions();
  abstract public function UpdateSession($usrData, $usrId);
  
  abstract public function StartSession();
  abstract public function SessionOpen($usrSavePath, $usrSesName);
  abstract public function SessionClose();
  abstract public function SessionRead($usrId);
  abstract public function SessionWrite($usrId, $usrData);
  abstract public function SessionDestroy($usrId);
  abstract public function SessionGC($usrSesMaxLifeTime = '');
  
  abstract public function GetInstance();
  abstract public function GetUserLogedIn();
  abstract public function GetUserId();
  abstract public function GetSessionId();
  
  abstract public function SetUserLogedIn($usrState);
	}

?>