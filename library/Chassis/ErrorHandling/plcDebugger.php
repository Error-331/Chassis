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
 * Class plcDebugger is a part of PHP framework - Chassis.   
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
 * Documents the plcDebugger class.
 * 
 * Following class is a deprecated representation of error debugger for Chassis framework.
 *   
 * @subpackage plcDebugger
 * @author Selihov Sergei Stanislavovich <red331@mail.ru>   
 */
 
require_once('Chassis/ErrorHandling/plcAbstractDebugger.php');

class plcDebugger extends plcAbstractDebugger
	{
	function __construct(&$usrError)
		{
		}
		
  /**
   * Function that is used to perform debugging operations.
   * 
   * Simple function that is used to perform debugging operations. 
   * 
   * @access public    
   *                                   
   */ 

	public function Debug()
		{
		echo('Debugging');
		}
	}


?>