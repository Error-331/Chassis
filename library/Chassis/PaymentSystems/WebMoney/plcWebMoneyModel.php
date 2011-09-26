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
 * Class plcWebMoneyModel is a part of PHP framework - Chassis.   
 * 
 * @package     Chassis
 * @author      Selihov Sergei Stanislavovich <red331@mail.ru> 
 * @copyright   Copyright (c) 2010-2011 Selihov Sergei Stanislavovich.
 * @license     http://www.gnu.org/licenses/gpl.html  GNU GENERAL PUBLIC LICENSE (Version 3)
 *    
 */

/**
 * Classes for work with payment systems.
 *  
 * @subpackage PaymentSystems
 * @author Selihov Sergei Stanislavovich <red331@mail.ru>   
 */
 
/**
 * Classes for work with WebMoney payment system.
 *  
 * @subpackage WebMoney
 * @author Selihov Sergei Stanislavovich <red331@mail.ru>   
 */
 
/**
 * Documents the plcWebMoneyModel class.
 * 
 * Following class is a main model class for work with webmoney payment system.
 *    
 * @subpackage plcUkrGarantWebMoneyModel
 * @author Selihov Sergei Stanislavovich <red331@mail.ru>   
 */

require_once('Chassis/ErrorHandling/plcPaymentSystemException.php');
require_once('Chassis/PaymentSystems/plcPaymentSystemAbstractModel.php');

class plcWebMoneyModel extends plcPaymentSystemAbstractModel
    {     
    /* Core methods starts here */
    
    /**
     * Main constructor function.
     * 
     * Main constructor function initialises the work of the object.
     * 
     * @access public
     * 
     * @param object controller object
     * 
     * @throws plcChassisException, plcPaymentSystemException  
     *                                      
     */     
    
    public function __construct($usrController)
        {
        parent::__construct($usrController);          
        }
        
    public function __destruct()
        {
        if(is_null($this->CURLRes) !== TRUE)
                {
		curl_close($this->CURLRes);
                }            
        }        
        
    /* Core methods ends here */    
    }
    
?>
