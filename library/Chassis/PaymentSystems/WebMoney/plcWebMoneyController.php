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
 * Class plcWebMoneyController is a part of PHP framework - Chassis.   
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
 * Documents the plcWebMoneyController class.
 * 
 * Following class is a main controller class for work with webmoney payment system.
 *    
 * @subpackage plcWebMoneyController
 * @author Selihov Sergei Stanislavovich <red331@mail.ru>   
 */

require_once('Chassis/ErrorHandling/plcPaymentSystemException.php');
require_once('Chassis/PaymentSystems/plcPaymentSystemAbstractController.php');

require_once('Chassis/PaymentSystems/WebMoney/plcWebMoneyModel.php');
require_once('Chassis/PaymentSystems/WebMoney/plcWebMoneyView.php');

class plcWebMoneyController extends plcPaymentSystemAbstractController
    {
    /**
     * @access protected
     * @var string current payment system alias.
     */	  
    
    protected $PSysAlias = 'WebMoney';
    
    /**
     * @access protected
     * @var bool indicates whether to use WebMoney keeper classic requests or not (if FALSE keeper light
     * requests will be used).
     */	     
    
    protected $KeeperClassicReq = FALSE;
    
    /* Core methods starts here */
    
    /**
     * Main constructor function.
     * 
     * Main constructor function initialises the work of the object.
     * 
     * @access public
     * 
     * @param int user id (the payer id)
     * @param int user currency id (the payer currency id)
     * @param int shop id (the payment reciever id)
     * @param int shop currency id (the payment reciever currency id)
     * 
     * @throws plcPaymentSystemException
     *                                      
     */      
    
    public function __construct($usrUserId = 0, $usrShopId = 0, $usrPathMainINI = '', $usrPathPSysINI = '')
        {   
        parent::__construct($usrUserId, $usrShopId, $usrPathMainINI, $usrPathPSysINI);
         
        $this->Model = new plcWebMoneyModel($this);
        $this->View = new plcWebMoneyView($this);        
        }
        
        
    public function __destruct()
        {           
        }  
        
    /* Core methods ends here */   
        
    /* Get methods starts here */
        
    public function GetKeeperClassicReq()
        {
        return $this->KeeperClassicReq;
        }          
                
    /* Get methods ends here */
        
    /* Set methods starts here */ 
        
    public function SetKeeperClassicReq($usrVal)
        {
        if ($usrVal === TRUE) {$this->KeeperClassicReq = TRUE;}
        if ($usrVal === FALSE){$this->KeeperClassicReq = FALSE;}
        }        
        
    /* Set methods ends here */
    }
    
?>
