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
 * Class plcPaymentSystemAbstractController is a part of PHP framework - Chassis.   
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
 * Documents the plcPaymentSystemAbstractController class.
 * 
 * Following class is an abstract class for payment system controller class.
 *    
 * @subpackage plcPaymentSystemAbstractController
 * @author Selihov Sergei Stanislavovich <red331@mail.ru>   
 */


abstract class plcPaymentSystemAbstractController
    {
    /**
     * @access protected
     * @var int payment system id.
     */	    
    
    protected $PaySysId = 0;    
    
    /**
     * @access protected
     * @var string current payment system alias.
     */	  
    
    protected $PSysAlias = 'Payment system';      
    
    /**
     * @access protected
     * @var array parsed settings from main ini file.
     */
    
    protected $INIMainSettings = NULL;    
    
    /**
     * @access protected
     * @var array parsed settings from payment system ini file.
     */
    
    protected $INISettings = NULL;       
    
    /**
     * @access protected
     * @var object current model.
     */	    
    
    protected $Model = NULL;
    
    /**
     * @access protected
     * @var object current view.
     */	    
    
    protected $View = NULL;    
    
    /**
     * @access protected
     * @var int|array range of currency ids that are accepted by payment system.
     */	  
    
    protected $AcceptedCurrency = 0;
    
    /**
     * @access protected
     * @var int user id (the payer id).
     */	      
    
    protected $UserId = 0; 
    
    /**
     * @access protected
     * @var int shop id (the payment reciever id)
     */	      
    
    protected $ShopId = 0;   
    
    /**
     * @access protected
     * @var int shop currency id (the payment reciever currency id)
     */	      
    
    protected $ShopCurrencyId = 0; 
    
    /**
     * @access protected
     * @var int current deal id
     */	       
    
    protected $DealId = 0;
    
    /**
     * @access protected
     * @var int current transaction id
     */	       
    
    protected $TransId = 0;  
    
    /**
     * @access protected
     * @var int id of the last request to the API (inbound, outbound)
     */	     
    
    protected $APIReqId = 0;
    
    /* Core methods starts here */
    
    /**
     * Main constructor function.
     * 
     * Main constructor function initialises the work of the object.
     * 
     * @access public
     * 
     * @param int user id (the payer id)
     * @param int shop id (the payment reciever id)
     * @param string path to the main configuration ini file
     * @param string path to the payment system configuration ini file
     *                                      
     */     
    
    public function __construct($usrUserId = 0, $usrShopId = 0, $usrPathMainINI = '', $usrPathPSysINI = '')
        {                 
        $this->UserId = $usrUserId;
        $this->ShopId = $usrShopId;
        
        $this->LoadMainINISettings($usrPathMainINI);
        $this->LoadPSysINISettings($usrPathPSysINI);
        }
        
    public function __destruct()
        {        
        }
                
    /**
     * Function that used to load payment systems config.ini file.
     * 
     * Simple function that is used to load payment systems config.ini file.
     * 
     * @access protected
     * 
     * @param string full path (including filename) to the ini file (optional, if omitted - function 
     * assumes that file resides in a prent directory of current working directory)
     * 
     * @throws plcPaymentSystemException 
     * 
     * @return TRUE on success.   
     *                                    
     */         
                    
    protected function LoadMainINISettings($usrPath = '')
        {
        if (empty($usrPath) === FALSE)
            {
            $tmpResult = parse_ini_file($usrPath, true);
            }
        else
            {
            $usrPath = substr(getcwd(), 0, strrpos(getcwd(), '/'))."/config.ini";
            $tmpResult = parse_ini_file($usrPath, true);
            }
        
        if ($tmpResult === FALSE)
            {
            throw new plcPaymentSystemException('Cannot parse config.ini file.', 11101, null, 'Cannot parse ini file: "'.$usrPath.'"');
            }
        else
            {
            $this->INIMainSettings = $tmpResult;
            return TRUE;
            } 
        }         
        
    /**
     * Function that used to load payment system config.ini file.
     * 
     * Simple function that is used to load payment system config.ini file.
     * 
     * @access protected
     * 
     * @param string full path (including filename) to the ini file (optional, if omitted - function assumes that file resides in current working directory)
     * 
     * @throws plcPaymentSystemException 
     * 
     * @return TRUE on success.   
     *                                    
     */         
                    
    protected function LoadPSysINISettings($usrPath = '')
        {
        if (empty($usrPath) === FALSE)
            {
            $tmpResult = parse_ini_file($usrPath, true);
            }
        else
            {
            $usrPath = getcwd()."/config.ini";
            $tmpResult = parse_ini_file($usrPath, true);
            }
        
        if ($tmpResult === FALSE)
            {
            throw new plcPaymentSystemException('Cannot parse config.ini file.', 11101, null, 'Cannot parse ini file: "'.$usrPath.'"');
            }
        else
            {
            $this->INISettings = $tmpResult;
            return TRUE;
            } 
        }        
      
    /**
     * Main function of the payment system to receive and process current bill.
     * 
     * To receive and process the bill this function should be called from the client code.
     * 
     * @access public
     * 
     * @param float billing sum from shop (in the shops currency)
     * @param int user selected currency (must be one of those that payment system proposes)
     * @param string internal order id of the shop or service provider
     * @param int id of the language in the client system
     * @param string security signature generated by the shop or service provider
     * @param string deal type
     * @param array user additional fields 
     * @param array custom URL list which are used to redirect user based on different events occurred 
     * during payment process. May contain the following values:
     * 
     * SUCCESS_URL - url to which payer will be redirected on successful payment
     * REJECTED_URL - url to which payer will be redirected on unsuccessful payment attemp  
     * API_URL - API url to which current system will make request to get info on payment status 
     * PENDING_URL - url to which payer will be redirected to receive additional instructions on payment
     * 
     * SUCCESS_METHOD - HTTP method which will be used to redirect user to the SUCCESS_URL (POST or GET, default is GET)
     * REJECTED_METHOD - HTTP method which will be used to redirect user to the REJECTED_URL (POST or GET, default is GET)
     * API_METHOD - HTTP method which will be used to make a request to the API_URL (POST or GET, default is GET)
     * PENDING_METHOD - HTTP method which will be used to redirect user to the PENDING_URL (POST or GET, default is GET)
     * 
     * Note that if all or some of the URLs is not set in the list - class will try to load default URLs
     * based on shop info stored in the database and if default information could not be loaded - redirection
     * will not be used.
     * 
     * @param string payer email
     * @param string description of the deal
     * 
     * @throws plcChassisException, plcPaymentSystemException
     * 
     * @return bool returns TRUE on success and FALSE on fail.
     *                                   
     */     
    
    public function Bill($usrSum, $usrSelCur, $usrOrderId, $usrLang, $usrSign, $usrDealType, $usrCustomFields = array(), $usrCustomShopURLS = array(), $usrEmail = '', $usrDesc = '')
        {
        $this->Model->NewDeal($usrSum, $usrSelCur, $usrOrderId, $usrLang, $usrSign, $usrDealType, $usrCustomFields, $usrCustomShopURLS, $usrEmail, $usrDesc);          
        }
                  
    /* Core methods ends here */
        
    /* Action methods starts here */
        
    /**
     * Function that used to handle undefined operation.
     * 
     * Simple function that handles undefined operation requested by the external server. 
     * 
     * @access protected
     * 
     * @param string name of the undefined operation
     *                                    
     */           
        
    abstract protected function onUndefinedOperation($usrOperation = '');    
        
    /**
     * Main function for handling requests.
     * 
     * This function must be called whenever payment module needs to process external request. 
     * 
     * @access public
     *                                        
     */          
        
    abstract public function onRequest();
        
    /* Action methods ends here */    
    
    /* Get methods starts here */
    
    /**
     * Function that returns current settings from parsed config.ini file for all payment system.
     * 
     * Simple function that returns current settings from parsed config.ini file for all payment system. 
     * 
     * @access public
     * 
     * @return array of parsed INI settings.
     *                                        
     */       
                   
    public function GetMainINISettings()
        {
        return $this->INIMainSettings;
        }                     
              
    /**
     * Function that returns current settings from parsed config.ini file for specific payment system.
     * 
     * Simple function that returns current settings from parsed config.ini file for specific payment system. 
     * 
     * @access public
     * 
     * @return array of parsed INI settings.
     *                                        
     */       
                   
    public function GetINISettings()
        {
        return $this->INISettings;
        }
        
    /**
     * Function that returns current payment system id.
     * 
     * Simple function that returns current payment system id. 
     * 
     * @access public
     * 
     * @return int returns current system id.
     *                                        
     */        
        
    public function GetPaymentSystemId()
        {
        return $this->PaySysId;
        }
        
    /**
     * Function that returns current payment system alias.
     * 
     * Simple function that returns current payment system alias. 
     * 
     * @access public
     * 
     * @return string returns current system alias.
     *                                        
     */     
    
    public function GetPaymentSystemAlias()
        {
        return $this->PSysAlias;
        }
        
    /**
     * Function that returns accepted currency by the current payment system.
     * 
     * Simple function that returns accepted currency by the current payment system.
     * 
     * @access public
     * 
     * @return int|array returns range of currencies that are accepted by payment system.
     *                                        
     */          
        
    public function GetAcceptedCurrency()
        {
        return $this->AcceptedCurrency;
        }
               
    /**
     * Function that returns current user id (the payer id).
     * 
     * Simple function that returns current user id (the payer id).
     * 
     * @access public
     * 
     * @return int returns current user id (the payer id).
     *                                        
     */          
        
    public function GetUserId()
        {
        return $this->UserId;
        }        
                
    /**
     * Function that returns current shop id (the payment reciever id).
     * 
     * Simple function that returns current shop id (the payment reciever id).
     * 
     * @access public
     * 
     * @return int returns current shop id (the payment reciever id).
     *                                        
     */          
        
    public function GetShopId()
        {
        return $this->ShopId;
        }          
        
    /**
     * Function that returns current shop currency id (the payment reciever currency id).
     * 
     * Simple function that returns current shop currency id (the payment reciever currency id).
     * 
     * @access public
     * 
     * @return int returns current shop currency id (the payment reciever currency id).
     *                                        
     */          
        
    public function GetShopCurrencyId()
        {
        return $this->ShopCurrencyId;
        }  
    
    /**
     * Function that returns current deal id (if it was set previously).
     * 
     * Simple function that returns current deal id (if it was set previously).
     * 
     * @access public
     * 
     * @return int|NULL returns current deal id if was set previously and NULL if it was not set yet.
     *                                        
     */         
        
    public function GetDealId()
        {
        return $this->DealId;
        }
        
    /**
     * Function that returns current transaction id (if it was set previously).
     * 
     * Simple function that returns current transaction id (if it was set previously).
     * 
     * @access public
     * 
     * @return int|NULL returns current transaction id if was set previously and NULL if it was not set yet.
     *                                        
     */        
          
    public function GetTransId()
        {
        return $this->TransId;
        }
        
    /**
     * Function that returns current id of last request to the API (inbound and outbound).
     * 
     * Simple function that returns current id of last request to the API (inbound and outbound).
     * 
     * @access public
     * 
     * @return int id of the last request to the API.
     *                                        
     */          
        
    public function GetAPIReqId()
        {
        return $this->APIReqId;
        }        
        
    /* Get methods ends here */
    
    /* Set methods starts here */
    
    /**
     * Function that sets current payment system id.
     * 
     * Simple function that sets current payment system id. 
     * 
     * @access public
     * 
     * @param int new id for payment system
     *                                    
     */        
        
    public function SetPaymentSystemId($usrId)
        {
        $this->PaySysId = $usrId;
        } 
        
    /**
     * Function that sets accepted currency ids for current payment system.
     * 
     * Simple function that sets accepted currency ids for current payment system.
     * 
     * @access public
     * 
     * @param int|array currency ids
     * 
     * @return bool returns TRUE on success and FALSE on fail.
     *                                    
     */          
        
    public function SetAcceptedCurrency($usrAcceptedCurrency)
        {
        if (is_int($usrAcceptedCurrency) === TRUE || is_array($usrAcceptedCurrency) === TRUE)
            {
            $this->AcceptedCurrency = $usrAcceptedCurrency;
            return TRUE;
            }
        else
            {
            return FALSE;
            }      
        }
                 
    /**
     * Function that sets current user id (the payer id).
     * 
     * Simple function that sets current user id (the payer id).
     * 
     * @access public
     * 
     * @param int user id (the payer id)
     *                                        
     */          
        
    public function SetUserId($usrUserId)
        {
        $this->UserId = $usrUserId;
        }    
                                           
    /**
     * Function that sets current shop id (the payment reciever id).
     * 
     * Simple function that sets current shop id (the payment reciever id). Also this function will 
     * attempt to load shop currency id.
     * 
     * @access public
     * 
     * @param int shop id (the payment reciever id).
     *                                        
     */          
        
    public function SetShopId($usrShopId)
        {
        $this->ShopId = $usrShopId;
        $this->Model->LoadShopCurrencyId();
        }          
        
    /**
     * Function that sets current shop currency id (the payment reciever currency id).
     * 
     * Simple function that sets current shop currency id (the payment reciever currency id).
     * 
     * @access public
     * 
     * @param int shop currency id (the payment reciever currency id).
     *                                        
     */          
        
    public function SetShopCurrencyId($usrShopCurrencyId)
        {
        $this->ShopCurrencyId = $usrShopCurrencyId;
        }  

    /**
     * Function that sets current deal id.
     * 
     * Simple function that sets current deal id.
     * 
     * @access public
     * 
     * @param int new deal id.
     *                                        
     */         
        
    public function SetDealId($usrDealId)
        {
        $this->DealId = $usrDealId;
        }     
        
    /**
     * Function that sets current transaction id.
     * 
     * Simple function that sets current transaction id.
     * 
     * @access public
     * 
     * @param int new transaction id.
     *                                        
     */         
        
    public function SetTransId($usrTransId)
        {
        $this->TransId = $usrTransId;
        } 
        
    /**
     * Function that sets current id of last request to the API (inbound and outbound).
     * 
     * Simple function that sets current id of last request to the API (inbound and outbound).
     * 
     * @access public
     * 
     * @param int id of the last request to the API.
     *                                        
     */          
        
    public function SetAPIReqId($usrAPIReqId)
        {
        $this->APIReqId = $usrAPIReqId;
        }
                     
    /* Set methods ends here */
    }

?>
