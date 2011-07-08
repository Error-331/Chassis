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
 * Various interfaces for SessionHandle subpackage.
 *
 */

interface SessionIdEncoder
  {
  public function Encode();
  
  public function GetData();
  public function GetPreparedData();
  
  public function SetData($usrDataArray);
  }

?>