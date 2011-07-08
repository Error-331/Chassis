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
 * Class plcChassisException is a part of PHP framework - Chassis.   
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
 * Documents the plcChassisException class.
 * 
 * Following class is base representation of error for all Chassis framework. 
 *   
 * @subpackage plcChassisException
 * @author Selihov Sergei Stanislavovich <red331@mail.ru>   
 */
 
class plcChassisException extends Exception
  {
  /**
   * @access protected
   * @var string stores full information text about current error 
   */	  
  protected $CurFullInfo;
  
  /* Core functions starts here */
  
  public function __construct($usrMessage, $usrCode = 0, Exception $usrPrevious = null, $usrFullInfo = 'Error full info.') 
    {
    $this->CurFullInfo = $usrFullInfo;
    
    if ($usrPrevious !== NULL)
      {
      parent::__construct($usrMessage, $usrCode, $usrPrevious);
      }
    else
      {
      parent::__construct($usrMessage, $usrCode);
      }
    }
    
  /* Core functions ends here */
  
  /* Get functions starts here */
  
  /**
   * Function that returns full information text about current error.
   * 
   * Simple function that returns full information text about current error.
   *        
   * @access public 
   * 
   * @return string full information text about current error.       
   *                            
   */   
  
  public function GetFullInfo()
    {
    return $this->CurFullInfo;
    }
  
  /* Get functions ends here */
  }

?>