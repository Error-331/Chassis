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
 * Class plcPaymentSystemFactory is a part of PHP framework - Chassis.   
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
 * Documents the plcPaymentSystemFactory class.
 * 
 * Following class is a factory class for different payment systems presented in chassis framework.
 *    
 * @subpackage plcPaymentSystemFactory
 * @author Selihov Sergei Stanislavovich <red331@mail.ru>   
 */

class plcPaymentSystemFactory 
    {
    /* Core methods starts here */
    
    private function __construct()
        {      
        }
        
    public function __destruct()
        {     
        }
    
    /* Core methods ends here */
        
    /* Get methods starts here */
        
    /**        
     * Function that used to return required payment system controller class.
     *   
     * Simple function that returns required payment system controller class.
     * Possible values for $usrSysName and corresponding settings for $usrSysSettings:
     * 
     * UKRGARANTWM - UkrGarant WebMoneyGate ()
     * 
     * @access public
     * 
     * @param string payament system name  
     * @param array payament system settings  
     * 
     * @throws plcChassisException     
     *          
     * @return bool|object returns FALSE on fail and payment system controller object on success.
     *                           
     */         
        
    public static function GetPaymentSystemController($usrSysName = '', $usrSysSettings = array())
        {
        $tmpReadyController = FALSE;
        $tmpReadyModel = FALSE;
        $tmpReadyView = FALSE;
              
        if (is_string($usrSysName) === FALSE || empty($usrSysName) === TRUE) {return FALSE;}
        $usrSysName = strtoupper($usrSysName);   
        
        switch($usrSysName)
            {
            case 'UKRGARANTWM':
                
             
                
            break;    
            }
        }
        
    /* Get methods ends here */    
    }
    
    
?>
