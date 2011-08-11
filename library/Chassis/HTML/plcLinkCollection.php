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
 * Class plcLinkCollection is a part of PHP framework - Chassis.   
 * 
 * @package     Chassis
 * @author      Selihov Sergei Stanislavovich <red331@mail.ru> 
 * @copyright   Copyright (c) 2010-2011 Selihov Sergei Stanislavovich.
 * @license     http://www.gnu.org/licenses/gpl.html  GNU GENERAL PUBLIC LICENSE (Version 3)
 *    
 */
 
/**
 * Classes for work with HTML.
 *  
 * @subpackage HTML
 * @author Selihov Sergei Stanislavovich <red331@mail.ru>   
 */
 
/**
 * Documents the plcLinkCollection class.
 * 
 * Following class represents collection of links.
 *   
 * @subpackage plcMySQLDBConnector
 * @author Selihov Sergei Stanislavovich <red331@mail.ru>   
 */
 
require_once('Chassis/ErrorHandling/plcChassisException.php');

class plcLinkCollection
	{
	protected $Text = NULL;
	
  /* Core functions starts here */
  
  public function __construct()
    {
    }
    
  public function __destruct()
    {
    }
    
    
  public function FindLinks()
    {
    if (is_null($this->Text) === TRUE){return FALSE;}
    }
  
  /* Core functions ends here */
  
  /* Get functions starts here */
  
  public function GetText()
    {
    return $this->Text;
    }
  
  /* Get functions ends here */
  
  /* Set functions starts here */
  
  public function SetText($usrText = '')
    {
    if (empty($usrText) === TRUE || is_string($usrText) === FALSE) {return FALSE;}   
    $this->Text = $usrText;
    }
  
  /* Set functions ends here */
  	
  }

?>