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
 * Class plcSessionIdEncoderMD5 is a part of PHP framework - Chassis.   
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
 * Documents the plcSessionIdEncoderMD5 class.
 * 
 * Following class is used as part of Strategy design pattern and is intended for session id encode.
 *   
 * @subpackage plcSessionIdEncoderMD5
 * @author Selihov Sergei Stanislavovich <red331@mail.ru>   
 */
 
require_once('Chassis/ErrorHandling/plcChassisException.php');
require_once('Chassis/Encription/plcEncoderMD5.php');
require_once('Chassis/SessionHandle/SessionIdEncoder.php');

class plcSessionIdEncoderMD5 extends plcEncoderMD5 implements SessionIdEncoder 
  {
  /* Core functions starts here */
  
  public function __construct()
    {
    $this->UseRndNum = TRUE;
    $this->UseTime = TRUE;
    } 
      
  /* Core functions ends here */
  }  

?>