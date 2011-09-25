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
 * Class plcTemplateSystemException is a part of PHP framework - Chassis.   
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
 * Documents the plcTemplateSystemException class.
 * 
 * Following class is base representation of error for template system based classes. 
 *   
 * @subpackage plcTemplateSystemException
 * @author Selihov Sergei Stanislavovich <red331@mail.ru>   
 */

require_once('Chassis/ErrorHandling/plcChassisException.php'); 
 
class plcTemplateSystemException extends plcChassisException
    {
    }

?>
