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
 * Class plcPaymentSystemAbstractView is a part of PHP framework - Chassis.   
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
 * Documents the plcPaymentSystemAbstractView class.
 * 
 * Following class is an abstract class for payment system view class.
 *    
 * @subpackage plcPaymentSystemAbstractView
 * @author Selihov Sergei Stanislavovich <red331@mail.ru>   
 */

require_once('Chassis/DBConnectors/plcDBConnectorFactory.php');

abstract class plcPaymentSystemAbstractView
    {
    /**
     * @access protected
     * @var object current controller class.
     */	     
    
    protected $Controller = NULL;
    
    /* Core methods starts here */
    
    public function __construct($usrController)
        {
        $this->Controller = $usrController;
        }
        
    public function __destruct()
        {
        }   
        
    /* Core methods ends here */     
    }
    
?>
