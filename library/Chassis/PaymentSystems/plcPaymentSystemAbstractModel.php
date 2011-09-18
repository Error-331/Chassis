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
 * Class plcPaymentSystemAbstractModel is a part of PHP framework - Chassis.   
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
 * Documents the plcPaymentSystemAbstractModel class.
 * 
 * Following class is an abstract class for payment system model class.
 *    
 * @subpackage plcPaymentSystemAbstractModel
 * @author Selihov Sergei Stanislavovich <red331@mail.ru>   
 */

require_once('Chassis/DBConnectors/plcDBConnectorFactory.php');

abstract class plcPaymentSystemAbstractModel
    {
    /**
     * @access protected
     * @var object current controller class.
     */	     
    
    protected $Controller = NULL;
    
    /**
     * @access protected
     * @var object table gate used to do operations with specific to the module payments table.
     */	     
    
    protected $PaymentsSpecTableGate = NULL;   
    
    /**
     * @access protected
     * @var object table gate used to do operations with API calls log table.
     */	     
    
    protected $APILogTableGate = NULL; 
    
    /**
     * @access protected
     * @var object table gate used to do operations with deals table.
     */	      
    
    protected $DealsTableGate = NULL;
    
    /**
     * @access protected
     * @var object table gate used to do operations with transactions table.
     */	     
    
    protected $TransactionTableGate = NULL;
          
    /**
     * @access protected
     * @var object table gate used to do operations with shops table.
     */	     
    
    protected $ShopsTableGate = NULL; 
    
    /**
     * @access protected
     * @var object table gate used to do operations with clients table.
     */	     
    
    protected $ClientsTableGate = NULL;
    
    /**
     * @access protected
     * @var string GET data that is being sent to the external API.
     */	  
    
    protected $OutboundGET = '';
    
    /**
     * @access protected
     * @var string POST data that is being sent to the external API.
     */	      
    
    protected $OutboundPOST = '';
    
    /**
     * @access protected
     * @var string COOKIES data that is being sent to the external API.
     */	    
    
    protected $OutboundCookies = '';
      
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
        $tmpConfArr = array();
        $tmpTableGate = NULL;
        $tmpResultSet = FALSE;
        
        $tmpArray = array();
        
        $this->Controller = $usrController;      
        
        $tmpMainSettings = $this->Controller->GetMainINISettings();
        $tmpSettings = $this->Controller->GetINISettings();

        if (array_key_exists('host', $tmpMainSettings['model']) === TRUE) {$tmpConfArr['host'] = $tmpMainSettings['model']['host'];}
        if (array_key_exists('user', $tmpMainSettings['model']) === TRUE) {$tmpConfArr['user'] = $tmpMainSettings['model']['user'];}
        if (array_key_exists('password', $tmpMainSettings['model']) === TRUE) {$tmpConfArr['password'] = $tmpMainSettings['model']['password'];}
        if (array_key_exists('database', $tmpMainSettings['model']) === TRUE) {$tmpConfArr['database'] = $tmpMainSettings['model']['database'];}
        
        /* Database table gates initialisation starts here */
        
        $tmpConfArr['tablename'] = $tmpSettings['model']['spec_payments_table'];
        $tmpConfArr['primkey'] = $tmpSettings['model']['spec_payments_table_primkey'];
        $this->PaymentsSpecTableGate = plcDBConnectorFactory::GetDBConnector('MYSQLTABLE', $tmpConfArr);   
        $this->PaymentsSpecTableGate->SetFieldsAliases($tmpSettings['spec_payments_table_aliases']);
 
        $tmpConfArr['tablename'] = $tmpMainSettings['model']['api_req_table'];
        $tmpConfArr['primkey'] = $tmpMainSettings['model']['api_req_table_primkey'];
        $this->APILogTableGate = plcDBConnectorFactory::GetDBConnector('MYSQLTABLE', $tmpConfArr);   
        $this->APILogTableGate->SetFieldsAliases($tmpMainSettings['api_req_table_aliases']); 
        
        $tmpConfArr['tablename'] = $tmpMainSettings['model']['deals_table'];
        $tmpConfArr['primkey'] = $tmpMainSettings['model']['deals_primkey'];
        $this->DealsTableGate = plcDBConnectorFactory::GetDBConnector('MYSQLTABLE', $tmpConfArr);   
        $this->DealsTableGate->SetFieldsAliases($tmpMainSettings['deals_table_aliases']); 
        
        $tmpConfArr['tablename'] = $tmpMainSettings['model']['transactions_table'];
        $tmpConfArr['primkey'] = $tmpMainSettings['model']['transactions_primkey'];
        $this->TransactionTableGate = plcDBConnectorFactory::GetDBConnector('MYSQLTABLE', $tmpConfArr);   
        $this->TransactionTableGate->SetFieldsAliases($tmpMainSettings['trans_table_aliases']); 
        
        $tmpConfArr['tablename'] = $tmpMainSettings['model']['shop_list_table'];
        $tmpConfArr['primkey'] = $tmpMainSettings['model']['shop_list_primkey'];
        $this->ShopsTableGate = plcDBConnectorFactory::GetDBConnector('MYSQLTABLE', $tmpConfArr);   
        $this->ShopsTableGate->SetFieldsAliases($tmpMainSettings['shop_list_table_aliases']); 
        
        if (array_key_exists('clients_list_table', $tmpMainSettings['model']) === TRUE && 
            array_key_exists('clients_list_primkey', $tmpMainSettings['model']) === TRUE &&
            array_key_exists('clients_list_table_aliases', $tmpMainSettings) === TRUE)
            {
            $tmpConfArr['tablename'] = $tmpMainSettings['model']['clients_list_table'];
            $tmpConfArr['primkey'] = $tmpMainSettings['model']['clients_list_primkey'];
            $this->ClientsTableGate = plcDBConnectorFactory::GetDBConnector('MYSQLTABLE', $tmpConfArr);   
            $this->ClientsTableGate->SetFieldsAliases($tmpMainSettings['clients_list_table_aliases']);                        
            }
                       
        /* Database table gates initialisation ends here */
        
        /* Payament system id set starts here */
        
        $tmpConfArr['tablename'] = $tmpMainSettings['model']['psys_table'];
        $tmpConfArr['primkey'] = $tmpMainSettings['model']['psys_table_primkey'];
              
        $tmpTableGate = plcDBConnectorFactory::GetDBConnector('MYSQLTABLE', $tmpConfArr);
        $tmpTableGate->SetFieldsAliases($tmpMainSettings['psys_table_aliases']);
        
        $tmpResultSet = $tmpTableGate->FindAssoc($this->Controller->GetPaymentSystemAlias(), $tmpMainSettings['psys_table_aliases']['PSYS_ALIAS']);
        
        if ($tmpResultSet === FALSE)
            {
            throw new plcPaymentSystemException('Cannot load payment system id.', 11102, null, 'Cannot load payment system id for alias: "'.$this->Controller->GetPaymentSystemAlias().'"');
            }
                      
        $this->Controller->SetPaymentSystemId(intval($tmpResultSet[$tmpTableGate->GetFieldByAlias('PSYS_ID')]));   
                    
        /* Payment system id set ends here */
        
        /* Payment system accepted currency ids set starts here */
               
        $tmpTableGate = plcDBConnectorFactory::GetDBConnector('MYSQLI', $tmpConfArr);
        $tmpTableGate->SetQuery('SELECT '.$tmpMainSettings['psys_accept_currency_table_aliases']['PSYSACCCUR_CUR_ID'].
                                ' FROM '.$tmpMainSettings['model']['psysacccur_table'].
                                ' WHERE '.$tmpMainSettings['psys_accept_currency_table_aliases']['PSYSACCCUR_PSYS_ID'].' = '.$this->Controller->GetPaymentSystemId());
                
        $tmpResultSet = $tmpTableGate->GetResultAssoc();
        if ($tmpResultSet === FALSE)
            {
            throw new plcPaymentSystemException('Cannot load accepted currencies ids for payment system.', 11103, null, 'Cannot load accepted currencies ids for payment system with ID: "'.$this->Controller->GetPaymentSystemId().'"');
            }
        else
            {
            if (count($tmpResultSet) == 1)
                {
                $this->Controller->SetAcceptedCurrency(intval($tmpResultSet[0][$tmpMainSettings['psys_accept_currency_table_aliases']['PSYSACCCUR_CUR_ID']]));
                }
            else
                {
                foreach($tmpResultSet as $tmpKey => $tmpValue)
                    {
                    $tmpArray[] = $tmpValue; 
                    }
                    
                $this->Controller->SetAcceptedCurrency($tmpArray);
                }       
            }
        
        /* Payment system accepted currency ids set ends here */
                               
        /* Shop accepted currency id set starts here */  

        $this->LoadShopCurrencyId();
                                   
        /* Shop accepted currency id set ends here */     
        }
        
    public function __destruct()
        {
        }
        
    /**
     * Function reads POST input from php stream and converts it into associative array.
     * 
     * Simple function that is used to read POST input from php stream and convert it into associative array.
     * 
     * @access public
     * 
     * @return array associative array with corresponding POST variables.  
     *                                    
     */          
        
    public function LoadPOSTFromStream()
        {     
        $tmpFinArr = array();
        $tmpMidArr = array();
        
        $tmpPostContents = file_get_contents('php://input');
        if (empty($tmpPostContents) === TRUE) {return array();}
                
        $tmpPostContents = trim($tmpPostContents);
        $tmpPostContents = explode('&', $tmpPostContents);
        
        for ($Counter1 = 0; $Counter1 < count($tmpPostContents); $Counter1++)
            {
            $tmpMidArr = explode('=', $tmpPostContents[$Counter1]);
            $tmpFinArr[$tmpMidArr[0]] = $tmpMidArr[1];
            }
      
        return $tmpFinArr;     
        }        
        
    /**
     * Simple function that tries to load currency accepted by current shop.
     * 
     * Note that this function will be called every time when the shop id will be changed by the call to 
     * controller SetShopId() function.
     * 
     * @access public
     * 
     * @throws plcChassisException
     * 
     * @return bool returns TRUE on success and FALSE on fail.
     *                                   
     */                 
        
    public function LoadShopCurrencyId()
        {
        $tmpShopId = $this->Controller->GetShopId();
        $tmpMainSettings = $this->Controller->GetMainINISettings();
        $tmpResultSet = FALSE;
        
        if (empty($tmpShopId) === TRUE || is_int($tmpShopId) === FALSE || $tmpShopId === 0) {return FALSE;}
        else
            {
            $tmpResultSet = $this->ShopsTableGate->FindAssoc($tmpShopId, $tmpMainSettings['shop_list_table_aliases']['SHOP_LIST_ID']);
            
            if ($tmpResultSet !== FALSE)
                {
                $this->Controller->SetShopCurrencyId(intval($tmpResultSet[$tmpMainSettings['shop_list_table_aliases']['SHOP_LIST_CURRENCY_ID']])); 
                return TRUE;
                }   
            else
                {
                return FALSE;
                }         
            }
        }
        
    /**
     * Currency conversion function
     * 
     * Simple function that performs currency conversion for user defined sum.
     * 
     * @access public
     * 
     * @param int currency id to convert from
     * @param int currency id to convert to
     * @param int|float user defined sum
     * @param int timestamp of date when last conversion was performed
     * 
     * @return int|float returns converted sum.
     *                                   
     */          
        
    public function ConvertCurrency($usrCurIdFrom, $usrCurIdTo, $usrSum, $usrDate = 0)
        {
        if (is_int($usrCurIdFrom) === FALSE || is_int($usrCurIdTo) === FALSE || is_numeric($usrSum) === FALSE) {return $usrSum;}
        if ($usrCurIdFrom == FALSE || $usrCurIdTo == 0 || $usrSum == 0) {return $usrSum;}
        
        if ($usrCurIdFrom == $usrCurIdTo)
            {
            return $usrSum;
            }
            
        return $usrSum;
        } 
        
    /**
     * Function that adds or subtracts commision from the sum that user has provided.
     * 
     * Simple function that adds or subtracts commision from the sum that user has provided. Note that 
     * the commision percentage and commission recipient depends on shop settings and default settings.
     * 
     * @access public
     * 
     * @param int|float sum to which commision will be added or subtracted
     * @param bool indicates whether commission will be applied before or after transaction
     * 
     * @return int|float resulting sum.
     *                                   
     */           
        
    public function CalcCommision($usrSum, $usrBefore = TRUE)
        {
        $tmpMainSettings = $this->Controller->GetMainINISettings();
        $tmpShopRow = NULL;
        
        $tmpComPayer = 'none';
        $tmpPerc = 0;
        
        $tmpDefComPayer = strtolower($tmpMainSettings['controller']['def_commission_payer']);
        $tmpDefPerc = floatval($tmpMainSettings['controller']['def_commission_perc']);
        
        $tmpShopRow = $this->ShopsTableGate->Find($this->Controller->GetShopId());
        $tmpComPayer = $tmpShopRow->SHOP_LIST_COMMISSION_PAYER;
        $tmpPerc = $tmpShopRow->SHOP_LIST_COMMISSION_PERC;
         
        if (empty($tmpComPayer) === TRUE) {$tmpComPayer = $tmpDefComPayer;}
        if ($tmpPerc <= 0){$tmpPerc = $tmpDefPerc;}
        
        $tmpComPayer = strtolower($tmpComPayer);
        
        switch($tmpComPayer)
            {
            case 'shop':
            
            if ($usrBefore === FALSE) {$usrSum -= (($usrSum * $tmpPerc) / 100);} 
            break;
            
            case 'buyer':
            
            if ($usrBefore === TRUE) {$usrSum += (($usrSum * $tmpPerc) / 100);}                 
            break;
        
            case 'both':
            
            if ($usrBefore === TRUE) {$usrSum += (($usrSum * $tmpPerc) / 100);} 
            else {$usrSum -= (($usrSum * $tmpPerc) / 100);}
            break;
            }
            
        return $usrSum;    
        }       
        
    /**
     * Function that returns commision fee from the transaction sum.
     * 
     * Simple function that returns commision fee from the transaction sum. Note that provides sum must 
     * be in the same currency as the current deal.
     * 
     * @access public
     * 
     * @param int|float sum of which commision fee must be find 
     * 
     * @return int|float resulting commission fee.
     *                                   
     */         
        
    public function CalcCommisionFee($usrSum) 
        {
        $tmpMainSettings = $this->Controller->GetMainINISettings();
        $tmpShopRow = NULL;
        
        $tmpComPayer = 'none';
        $tmpPerc = 0;
        
        $tmpDefComPayer = strtolower($tmpMainSettings['controller']['def_commission_payer']);
        $tmpDefPerc = floatval($tmpMainSettings['controller']['def_commission_perc']);
        
        $tmpShopRow = $this->ShopsTableGate->Find($this->Controller->GetShopId());
        $tmpDealRow = $this->DealsTableGate->Find($this->Controller->GetDealId());
        
        $tmpComPayer = $tmpShopRow->SHOP_LIST_COMMISSION_PAYER;
        $tmpPerc = $tmpShopRow->SHOP_LIST_COMMISSION_PERC;
         
        if (empty($tmpComPayer) === TRUE) {$tmpComPayer = $tmpDefComPayer;}
        if ($tmpPerc <= 0){$tmpPerc = $tmpDefPerc;}
        
        $tmpComPayer = strtolower($tmpComPayer);      
        
        switch($tmpComPayer)
            {
            case 'shop':
                
            return (($usrSum * $tmpPerc) / 100);    
            break;
            
            case 'buyer':
                
            if ($usrSum <= $tmpDealRow->DEALS_PAY_SUM) {return 0;}                   
            return $usrSum - $tmpDealRow->DEALS_PAY_SUM;               
            break;
        
            case 'both':
            
            if ($usrSum <= $tmpDealRow->DEALS_PAY_SUM) {return 0;} 
            
            return ($usrSum - $tmpDealRow->DEALS_PAY_SUM) + (($tmpDealRow->DEALS_PAY_SUM * $tmpPerc) / 100); 
            break;
            
            case 'none':
            default:
                
            return 0;    
            break;
            }      
        }
        
    /**
     * Function that logs API requests (both inbound and outbound).
     * 
     * Simple function that logs API requests (both inbound and outbound). Note that if request data is 
     * successfully stored in the database current object will invoke SetAPIReqId() method of the 
     * controller thus overwriting previously set ID value. If the data is not correctly stored in the 
     * database or error have occured SetAPIReqId() method will be invoked with zero value.
     * 
     * @access public
     * 
     * @param array additional data
     * @param string identifier of request direction ("i" - inbound, "o" - outbound, "u" - undefined)
     * @param string URL to which request was made
     * 
     * @return bool returns TRUE on success and FALSE on fail.
     *                                   
     */        
        
    public function LogApiRequest($usrAddData = '', $usrDirection = 'u', $usrURL = '')
        {
        $tmpResult = '';
        $tmpData = NULL; 
        $tmpPOSTArr = $this->LoadPOSTFromStream();
        
        $tmpGETStr = '';
        $tmpPOSTStr = '';  
        $tmpCOOKIESStr = '';
        $tmpDirection = 'undefined';
        
        $tmpTransRow = NULL;
        
        $Counter1 = 0;
                   
        if (is_string($usrAddData) === FALSE)
            {
            $usrAddData = serialize($usrAddData);
            } 
            
        /* Request direction preparation starts here */
        
        if (is_string($usrDirection) === FALSE)
            {
            $usrDirection = 'u';
            }
            
        $usrDirection = strtolower($usrDirection);  
                   
        switch($usrDirection)
            {
            case 'i':
            $tmpDirection = 'inbound';
            break;
        
            case 'o':
            $tmpDirection = 'outbound';
            break;
        
            default:
            $tmpDirection = 'undefined';    
            break;              
            }
        
        /* Request direction preparation ends here */ 
            
        /* URL preparation starts here */
            
        if (is_string($usrURL) === FALSE)
            {
            $usrURL = '';
            }
            
        /* URL preparation ends here */
        
        /* POST stringification starts here */
    
        if ($tmpDirection == 'outbound')
            {
            $tmpPOSTStr = $this->OutboundPOST; 
            }
        else
            {
            foreach ($tmpPOSTArr as $tmpKey => $tmpVal)
                {
                if ($Counter1 != 0) {$tmpKey = '&'.$tmpKey;}
        
                $tmpPOSTStr .= $tmpKey.'='.$tmpVal;      
                $Counter1 += 1;
                }
            
            $Counte1 = 0;                        
            }   
            
        /* POST stringification ends here */  
            
        /* GET stringification starts here */    
            
        if ($tmpDirection == 'outbound')
            {
            $tmpGETStr = $this->OutboundGET; 
            }
        else
            {
            foreach ($_GET as $tmpKey => $tmpVal)
                {
                if ($Counter1 != 0) {$tmpKey = '&'.$tmpKey;}
        
                $tmpGETStr .= $tmpKey.'='.$tmpVal;      
                $Counter1 += 1;
                }
            
            $Counte1 = 0;                             
            }
            
        /* GET stringification ends here */    
            
        /* COOKIES stringification starts here */
            
        if ($tmpDirection == 'outbound')
            {
            $tmpCOOKIESStr = $this->OutboundCookies; 
            }   
        else
            {
            foreach ($_COOKIE as $tmpKey => $tmpVal)
                {
                if ($Counter1 != 0) {$tmpKey = '&'.$tmpKey;}
        
                $tmpCOOKIESStr .= $tmpKey.'='.$tmpVal;      
                $Counter1 += 1;
                }                      
            }
            
        /* COOKIES stringification ends here */ 
                                           
        $tmpData = array(
                         'API_LOG_PSYS_NAME' => $this->Controller->GetPaymentSystemAlias(),
                         'API_LOG_URL' => $usrURL,
                         'API_LOG_POST' => $tmpPOSTStr,
                         'API_LOG_GET' => $tmpGETStr,
                         'API_LOG_COOKIE' => $tmpCOOKIESStr,
                         'API_LOG_DEAL_ID' => is_null($this->Controller->GetDealId()) ? 0 : $this->Controller->GetDealId(),
                         'API_LOG_TRANS_ID' => is_null($this->Controller->GetTransId()) ? 0 : $this->Controller->GetTransId(),
                         'API_LOG_DATE' => time(),
                         'API_LOG_CUSTOM_DATA' => $usrAddData,
                         'API_LOG_DIRECTION' => $tmpDirection
                        );
        
        try
            {
            $this->APILogTableGate->Insert($tmpData);
            $tmpResult = $this->APILogTableGate->GetLastInsertId();
            
            if ($tmpResult === FALSE) 
                {
                $this->Controller->SetAPIReqId(0);
                return FALSE;               
                }
            else
                {
                $this->Controller->SetAPIReqId($tmpResult);
                
                /* Current transaction update starts here */
                
                if ($tmpData['API_LOG_TRANS_ID'] != 0)
                    {              
                    $tmpTransRow = $this->TransactionTableGate->Find($tmpData['API_LOG_TRANS_ID']);
                    if ($tmpTransRow != FALSE)
                        {                        
                        $tmpTransRow->TRANS_API_REQUEST_ID = $tmpResult;
                        $tmpTransRow->Save(); 
                        }
                    }
                
                /* Current transaction update ends here */
                
                return TRUE;
                }
            }
        catch(Exception $usrError)
            {
            $this->Controller->SetAPIReqId(0);
            return FALSE;
            }               
        }        
        
    /**
     * Function that is used to create new deal id and store all the necessary data in the database.
     * 
     * This function should be used when the system instantiates new deal. If deal information was
     * successfully stored in the database - new deal id will automaticly be set in the controller property.
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
     * @return bool returns TRUE on success.
     *                                   
     */         
        
    public function NewDeal($usrSum, $usrSelCur, $usrOrderId, $usrLang, $usrSign, $usrDealType, $usrCustomFields = array(), $usrCustomShopURLS = array(), $usrEmail = '', $usrDesc = '')
        {  
        $tmpShopRow = NULL;
        $tmpPSysCur = NULL;
        
        $tmpShopId = 0;
        $tmpClientId = 0;
        
        $tmpUserSum = 0;
        
        /* User selected currency check starts here */
        
        if (isset($usrSelCur) === FALSE || empty($usrSelCur) === TRUE || is_int($usrSelCur) === FALSE || $usrSelCur == 0)
            {
            throw new plcPaymentSystemException('Invalid selected payment currency.', 11110, null, 'Invalid selected payment currency or it is not set.');
            }
            
        $tmpPSysCur = $this->Controller->GetAcceptedCurrency();
            
        if (is_array($tmpPSysCur) === TRUE)
            {
            if (in_array($usrSelCur, $tmpPSysCur) === FALSE)
                {
                throw new plcPaymentSystemException('Payment system does not support selected currency.', 11111, null, 'Payment system does not support selected currency: "'.$usrSelCur.'"');
                }
            }
        else
            {
            if ($usrSelCur != $tmpPSysCur)
                {
                throw new plcPaymentSystemException('Payment system does not support selected currency.', 11111, null, 'Payment system does not support selected currency: "'.$usrSelCur.'"');
                } 
            }
            
        /* User selected currency check ends here */    
        
        /* Custom fields preparation starts here */
        
        if (is_array($usrCustomFields) === FALSE)
            {
            $usrCustomFields = array('field1' => $usrCustomFields);
            }
         
        $usrCustomFields = base64_encode(serialize($usrCustomFields));
        
        /* Custom fields preparation ends here */
        
        /* Custom URLs preparation starts here */
        
        try
            {
            $tmpShopRow = $this->ShopsTableGate->FindAssoc($this->Controller->GetShopId());
            }
        catch(plcChassisException $usrError) 
            {
            $tmpShopRow = FALSE;
            }
            
        if ($tmpShopRow === FALSE)
            {                     
            $tmpShopRow = array();
            $tmpShopRow[$this->ShopsTableGate->GetFieldByAlias('SHOP_LIST_SUCCESS_URL')] = '';
            $tmpShopRow[$this->ShopsTableGate->GetFieldByAlias('SHOP_LIST_REJECTED_URL')] = '';
            $tmpShopRow[$this->ShopsTableGate->GetFieldByAlias('SHOP_LIST_API_URL')] = '';
            $tmpShopRow[$this->ShopsTableGate->GetFieldByAlias('SHOP_LIST_PENDING_URL')] = '';
            
            $tmpShopRow[$this->ShopsTableGate->GetFieldByAlias('SHOP_LIST_SUCCESS_METHOD')] = '';
            $tmpShopRow[$this->ShopsTableGate->GetFieldByAlias('SHOP_LIST_REJECTED_METHOD')] = '';
            $tmpShopRow[$this->ShopsTableGate->GetFieldByAlias('SHOP_LIST_API_METHOD')] = '';
            $tmpShopRow[$this->ShopsTableGate->GetFieldByAlias('SHOP_LIST_PENDING_METHOD')] = '';            
            }
                        
        if (array_key_exists('SUCCESS_URL', $usrCustomShopURLS) === FALSE) {$usrCustomShopURLS['SUCCESS_URL'] = $tmpShopRow[$this->ShopsTableGate->GetFieldByAlias('SHOP_LIST_SUCCESS_URL')];}
        if (array_key_exists('REJECTED_URL', $usrCustomShopURLS) === FALSE) {$usrCustomShopURLS['REJECTED_URL'] = $tmpShopRow[$this->ShopsTableGate->GetFieldByAlias('SHOP_LIST_REJECTED_URL')];}
        if (array_key_exists('API_URL', $usrCustomShopURLS) === FALSE) {$usrCustomShopURLS['API_URL'] = $tmpShopRow[$this->ShopsTableGate->GetFieldByAlias('SHOP_LIST_API_URL')];}
        if (array_key_exists('PENDING_URL', $usrCustomShopURLS) === FALSE) {$usrCustomShopURLS['PENDING_URL'] = $tmpShopRow[$this->ShopsTableGate->GetFieldByAlias('SHOP_LIST_PENDING_URL')];}
        
        if (array_key_exists('SUCCESS_METHOD', $usrCustomShopURLS) === FALSE) {$usrCustomShopURLS['SUCCESS_METHOD'] = $tmpShopRow[$this->ShopsTableGate->GetFieldByAlias('SHOP_LIST_SUCCESS_METHOD')];}
        if (array_key_exists('REJECTED_METHOD', $usrCustomShopURLS) === FALSE) {$usrCustomShopURLS['REJECTED_METHOD'] = $tmpShopRow[$this->ShopsTableGate->GetFieldByAlias('SHOP_LIST_REJECTED_METHOD')];}
        if (array_key_exists('API_METHOD', $usrCustomShopURLS) === FALSE) {$usrCustomShopURLS['API_METHOD'] = $tmpShopRow[$this->ShopsTableGate->GetFieldByAlias('SHOP_LIST_API_METHOD')];}
        if (array_key_exists('PENDING_METHOD', $usrCustomShopURLS) === FALSE) {$usrCustomShopURLS['PENDING_METHOD'] = $tmpShopRow[$this->ShopsTableGate->GetFieldByAlias('SHOP_LIST_PENDING_METHOD')];}
        
        /* Custom URLs preparation ends here */
        
        $tmpShopId = $this->Controller->GetShopId();
        $tmpClientId = $this->Controller->GetUserId();
      
        if (empty($tmpShopId) === TRUE || is_int($tmpShopId) === FALSE || $tmpShopId == 0)  
            {
            throw new plcPaymentSystemException('Invalid or empty shop id.', 11106, null, 'Invalid or empty shop id is set as object value.');
            }   
            
        /* Currency conversion and add interest starts here */
            
        $tmpUserSum = $this->ConvertCurrency($this->Controller->GetShopCurrencyId(), $usrSelCur, $usrSum);    
        $tmpUserSum = $this->CalcCommision($tmpUserSum, TRUE);
          
        /* Currency conversion and add interest ends here */
                                                     
        $tmpData = array(
        "DEALS_STATUS" => "new",
        "DEALS_SHOP_ID" => $tmpShopId,
        "DEALS_PAY_SUM" => $usrSum,
        "DEALS_PAY_CURRENCY" => $this->Controller->GetShopCurrencyId(),
        "DEALS_USER_SUM" => $tmpUserSum,
        "DEALS_USER_CURRENCY" => $usrSelCur,                       
        "DEALS_ORDER_ID" => $usrOrderId,
        "DEALS_DESC" => $usrDesc,
        "DEALS_LANG" => $usrLang,
        "DEALS_CUSTOM_FIELDS" => $usrCustomFields,
        "DEALS_SIGN" => $usrSign,
        "DEALS_SUCCESS_URL" =>  $usrCustomShopURLS['SUCCESS_URL'],   
        "DEALS_REJECTED_URL" => $usrCustomShopURLS['REJECTED_URL'],   
        "DEALS_API_URL" => $usrCustomShopURLS['API_URL'],
        "DEALS_PENDING_URL" => $usrCustomShopURLS['PENDING_URL'],
        "DEALS_SUCCESS_METHOD" => $usrCustomShopURLS['SUCCESS_METHOD'],
        "DEALS_REJECTED_METHOD" => $usrCustomShopURLS['REJECTED_METHOD'],     
        "DEALS_API_METHOD" => $usrCustomShopURLS['API_METHOD'],
        "DEALS_PENDING_METHOD" => $usrCustomShopURLS['PENDING_METHOD'],                      
        "DEALS_PAYER_EMAIL" => $usrEmail,
        "DEALS_CREATE_DATE" => time(),
        "DEALS_ACTION_DATE" => time(),    
        "DEALS_TYPE" => $usrDealType
        );
        
        if (empty($tmpClientId) !== TRUE && is_int($tmpClientId) === TRUE)
            {
            $tmpData["DEALS_CLIENT_ID"] = $tmpClientId;
            } 
        
        if ($this->DealsTableGate->Insert($tmpData) === FALSE) 
            {
            throw new plcPaymentSystemException('Cannot create deal record.', 11109, null, 'Error occurred while creating new deal record.');
            }
        else
            {
            $this->Controller->SetDealId($this->DealsTableGate->GetLastInsertId());
            return TRUE;
            }            
        }
                      
    /**
     * Function that creates new transaction.
     * 
     * This function should be used when the transaction accured between the payment system, 
     * shop (service provider) and client. If transaction information was successfully stored in the 
     * database - new transaction id will automaticly be set in the controller property. After a 
     * number of required transactions received (buyer successfully pay his bill) shop balance will be  
     * filled up.
     * 
     * @access public
     * 
     * @param string payment system transaction number
     * @param int|float sum that was received from the payment system
     * @param int timestamp of the transaction (if not set present date will be used)
     * 
     * @throws plcChassisException, plcPaymentSystemException
     * 
     * @return bool returns new TRUE on success and FALSE on fail.
     *                                   
     */       
             
    public function NewTransaction($usrPTransNum, $usrSum, $usrDate = '')
        {
        /* Data preparation starts here */
        
        $tmpMainConf = $this->Controller->GetMainINISettings();
        $tmpDBConnector = NULL;
        
        $tmpDealRow = FALSE;
        $tmpShopRow = FALSE;
        
        $tmpDealId = $this->Controller->GetDealId();
        $tmpShopId = $this->Controller->GetShopId();
        
        $tmpShopCurId = $this->Controller->GetShopCurrencyId();
        $tmpUserCurId = 0;     
        
        $tmpSum = 0;
        $tmpTransSum = 0;
        $tmpConvSumOld = 0;
        $tmpComFee = 0;
        
        $tmpParams = array();
        
        if ($tmpDealId == 0 || $tmpShopId == 0 || $tmpShopCurId == 0) {return FALSE;} 
              
        $tmpDealRow = $this->DealsTableGate->Find($tmpDealId, 'DEALS_ID');
        if ($tmpDealRow === FALSE) {return FALSE;}
        
        $tmpShopRow = $this->ShopsTableGate->Find($tmpShopId, 'SHOP_LIST_ID');
        if ($tmpShopRow === FALSE) {return FALSE;}
        
        $tmpUserCurId = $tmpDealRow->DEALS_USER_CURRENCY;        
                    
        if (empty($usrDate) === TRUE || is_numeric($usrDate) === FALSE)
            {
            $usrDate = time();
            }
            
        /* Data preparation ends here */     
     
        /* Transaction check starts here */
            
        if ($usrSum < $tmpDealRow->DEALS_USER_SUM)
            {
            /* Transactions sum generation starts here */
            
            $tmpDBConnector = plcDBConnectorFactory::GetDBConnector('MYSQLI');
            
            $tmpDBConnector->SetPrepQuery('SELECT SUM(`'.$tmpMainConf['trans_table_aliases']['TRANS_PAYMENT_SUM'].'`) 
                                           FROM `'.$tmpMainConf['model']['transactions_table'].'` 
                                           WHERE '.$tmpMainConf['trans_table_aliases']['TRANS_DEAL_ID'].' = ?');

            if ($tmpDBConnector->SetPrepQueryParamsArr('i', array(0 => $tmpDealId)) !== FALSE)
                {
                $tmpTransSum = $tmpDBConnector->GetPrepResult();
        
                if($tmpTransSum === FALSE || is_null($tmpTransSum) === TRUE)
                    {
                    $tmpTransSum = 0;
                    }       
                }
                
            if (($usrSum + $tmpTransSum) < $tmpDealId->DEALS_USER_SUM)
                {
                /* Saving transaction (partly paid deal) starts here */
                                                                             
                $tmpParams['TRANS_ENTRY_SUM'] = 0;   
                $tmpParams['TRANS_ENTRY_CURRENCY'] = $tmpShopCurId;
                                
                /* Saving transaction (partly paid deal) ends here */
                }
            else 
                {
                /* Saving transaction (fully paid deal) starts here */
                
                $tmpTransSum = $usrSum + $tmpTransSum;
                $tmpConvSumOld = $this->ConvertCurrency($tmpUserCurId, $tmpShopCurId, $tmpTransSum, $tmpDealRow->DEALS_CREATE_DATE);
                
                $tmpComFee = CalcCommisionFee($tmpConvSumOld);
                                
                $tmpParams['TRANS_ENTRY_SUM'] =  ($this->ConvertCurrency($tmpUserCurId, $tmpShopCurId, $tmpTransSum) - $tmpComFee);   
                $tmpParams['TRANS_ENTRY_CURRENCY'] = $tmpShopCurId;                
                
                /* Saving transaction (fully paid deal) ends here */
                }
               
            /* Transactions sum generation ends here */
            }
        else
            {
            /* Saving transaction (fully paid deal) starts here */
                
            $tmpConvSumOld = $this->ConvertCurrency($tmpUserCurId, $tmpShopCurId, $usrSum, $tmpDealRow->DEALS_CREATE_DATE);
                
            $tmpComFee = $this->CalcCommisionFee($tmpConvSumOld);
                                
            $tmpParams['TRANS_ENTRY_SUM'] =  ($this->ConvertCurrency($tmpUserCurId, $tmpShopCurId, $usrSum) - $tmpComFee);   
            $tmpParams['TRANS_ENTRY_CURRENCY'] = $tmpShopCurId;                
                
            /* Saving transaction (fully paid deal) ends here */            
            }
            
        /* Transaction check ends here */
            
        /* Transaction insert starts here */
       
        $tmpParams['TRANS_SHOP_ID'] = $tmpShopId;     
        $tmpParams['TRANS_PAYMENT_SYSTEM_TRANSACTION_ID'] = $usrPTransNum; 
        $tmpParams['TRANS_PAYMENT_SYSTEM_ID'] = $this->Controller->GetPaymentSystemId();
        $tmpParams['TRANS_PAYMENT_SUM'] = $usrSum;
        $tmpParams['TRANS_PAYMENT_CURRENCY'] = $tmpUserCurId;
        $tmpParams['TRANS_ACCOUNT_SUM'] = $this->ConvertCurrency($tmpUserCurId, $tmpShopCurId, $usrSum);
        $tmpParams['TRANS_ACCOUNT_CURRENCY'] = $tmpShopCurId;
        $tmpParams['TRANS_DATE'] = $usrDate;
        $tmpParams['TRANS_DEAL_ID'] = $tmpDealId;
        $tmpParams['TRANS_STATUS'] = 'paid';
        $tmpParams['TRANS_API_REQUEST_ID'] = 0;
       
        if ($this->TransactionTableGate->Insert($tmpParams) === FALSE) {return FALSE;}
        else
            {
            $this->Controller->SetTransId($this->TransactionTableGate->GetLastInsertId());
            return TRUE;
            }             
            
    /* Transaction insert ends here */                                                  
    }
           
    /* Core methods ends here */
       
    /* Get methods starts here */
       
    /**
     * Function that returns URL of the page to which current request was made.
     * 
     * Simple function that returns URL of the page to which current request was made.
     * 
     * @access public
     * 
     * @return string URL of the page.   
     *                                    
     */         
       
    public function GetCurPageURL()
        {
        $tmpPageURL = 'http';
            
        if (@$_SERVER["HTTPS"] == "on") {$tmpPageURL .= "s";}
        $tmpPageURL .= "://";
        
        if ($_SERVER["SERVER_PORT"] != "80") 
            {
            $tmpPageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
            } 
        else 
            {
            $tmpPageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
            }
            
        return $tmpPageURL;
        }       
       
    /* Get methods ends here */   
    }
    
?>
