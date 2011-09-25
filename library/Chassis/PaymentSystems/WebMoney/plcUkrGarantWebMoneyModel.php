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
 * Class plcUkrGarantWebMoneyModel is a part of PHP framework - Chassis.   
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
 * Documents the plcUkrGarantWebMoneyModel class.
 * 
 * Following class is a main model class for work with webmoney payment system through UkrGarant
 * gate system.
 *    
 * @subpackage plcUkrGarantWebMoneyModel
 * @author Selihov Sergei Stanislavovich <red331@mail.ru>   
 */

require_once('Chassis/ErrorHandling/plcPaymentSystemException.php');
require_once('Chassis/PaymentSystems/plcPaymentSystemAbstractModel.php');

class plcUkrGarantWebMoneyModel extends plcPaymentSystemAbstractModel
    {      
    /**
     * @access protected
     * @var string user agent parameter that will be used in HTTP(HTTPS) request.
     */	       
    
    protected $UserAgent = 'PHP_UkrGarant_Client/1.0';
    
    /**
     * @access protected
     * @var string UkrGarant domain.
     */	     
    
    protected $GateDomain = 'gate.ukrgarant.com';
    
    /**
     * @access protected
     * @var string URL address to which "getInvoice" (LoadInvoice() - method) request will be send.
     */	     
    
    //protected $LoadInvoiceURL = 'https://gate.ukrgarant.com/terminal/paySystem/getInvoice.aspx';
    protected $LoadInvoiceURL = 'http://alionpay.localhost.com/payment_systems/ukrgarant/testing.php';
   
    /**
     * @access protected
     * @var string URL address to which "getDetail" (LoadInvoiceDetails() - method) request will be send.
     */	     
 
    //protected $LoadInvoiceDetailsURL = 'https://gate.ukrgarant.com/terminal/paySystem/getDetail.aspx';
    protected $LoadInvoiceDetailsURL = 'http://alionpay.localhost.com/payment_systems/ukrgarant/testing.php';

    /**
     * @access protected
     * @var string URL address to which "getChange" (LoadInvoiceChange() - method) request will be send.
     */	     
 
    //protected $LoadInvoiceChangeURL = 'https://gate.ukrgarant.com/terminal/paySystem/getChange.aspx';
    protected $LoadInvoiceChangeURL = 'http://alionpay.localhost.com/payment_systems/ukrgarant/testing.php';    
    
    /**
     * @access protected
     * @var string URL address to which "CancelInvoice" (CancelInvoice() - method) request will be send.
     */	     
 
    //protected $CancelInvoiceURL = 'https://gate.ukrgarant.com/terminal/paySystem/CancelInvoice.aspx ';
    protected $CancelInvoiceURL = 'http://alionpay.localhost.com/payment_systems/ukrgarant/testing.php';      
    
    /**
     * @access protected
     * @var string bill duration (must follow strtotime() date format without plus or minus signs).
     */	    
    
    protected $BillDuration = '3 days';
    
    /**
     * @access protected
     * @var int CURL timeout.
     */	     
    
    protected $CurTimeOut = 60; // 60000 ms 
    
    /**
     * @access protected
     * @var resource CURL handle.
     */	     
    
    protected $CURLRes = NULL;    
    
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
        
        $tmpCURLRes = curl_init();
        if ($tmpCURLRes === FALSE)  
            {
            throw new plcPaymentSystemException('Cannot initialise CURL.', 11207, null ,'Error while initialising CURL library or it is not installed.');
            }
		
        $this->CURLRes = $tmpCURLRes;  	
        curl_setopt($this->CURLRes, CURLOPT_TIMEOUT, $this->CurTimeOut);          
        }
    
    public function __destruct()
        {
        if(is_null($this->CURLRes) !== TRUE)
                {
		curl_close($this->CURLRes);
                }            
        }
        
    /*
     * Function that used to generate random secret key.
     * 
     * Simple function that generates random secret key. 
     * 
     * @access public
     * 
     * @param int maximum length of secret key (global maximum length is set to 20)
     *     
     * @return string secret key.   
     *                               
     */         
                        
    protected function GenSecretKey($usrLength = 5)
        {
        $tmpSecretKey = '';
        $tmpPossibleChars = '1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        $tmpChar = '';
        $tmpMaxLen = strlen($tmpPossibleChars);
        
        $Counter1 = 0;
        
        if (is_int($usrLength) === FALSE || $usrLength == 0) {$usrLength = 5;}        
        if ($usrLength > $tmpMaxLen) {$usrLength = $tmpMaxLen;}
        
        while ($Counter1 < $usrLength) 
            { 
            $tmpChar = substr($tmpPossibleChars, mt_rand(0, $tmpMaxLen-1), 1);
        
            if (!strstr($tmpSecretKey, $tmpChar)) 
                { 
                $tmpSecretKey .= $tmpChar;
                $Counter1++;
                }

            }  
            
        return $tmpSecretKey;
        }        
                
    /**
     * Function that loads user phone number from the database.
     * 
     * Simple function that loads user phone number from the database..
     * 
     * @access public
     * 
     * @throws plcChassisException
     * 
     * @return string|bool returns user phone number on success and FALSE if it is not found.
     *                                        
     */          
        
    public function LoadClientPhoneNum()
        {    
        if (is_null($this->ClientsTableGate) === TRUE) {return FALSE;}
        
        $tmpMainSet = $this->Controller->GetMainINISettings();
        $tmpResultSet = $this->ClientsTableGate->FindAssoc($this->Controller->GetUserId(), $tmpMainSet['clients_list_table_aliases']['CLIENT_LIST_ID']);
        
        if ($tmpResultSet === FALSE) {return FALSE; }

        if (empty($tmpResultSet[$tmpMainSet['clients_list_table_aliases']['CLIENTS_LIST_PHONE']]) === TRUE) {return FALSE;}
        else 
            {
            return $tmpResultSet[$tmpMainSet['clients_list_table_aliases']['CLIENTS_LIST_PHONE']];
            } 
        }
        
    /* 
     * Simple function that checks webmoney purse format.
     * 
     * Function that validates provided webmoney purse name (not WMID).
     * 
     * Purse format: symbol that represents purse type + 12 numeric symbols
     * Example: Z23847900834
     * 
     * Purses types:
     * 
     * WMZ - Z (Dollar)
     * WMR - R (Ruble)
     * WME - E (Euro)
     * WMU - U (Hryvnia)
     * WMY - Y (Uzbek som)
     * WMB - B (Belarusian ruble)
     * WMG - G (Gold)
     * WMD - D (Loans)
     * WMC - C (Сredit)
     * 
     * @access public
     * 
     * @param string purse name (like 'Z238479008345'). 
     * 
     * @throws plcPaymentSystemException
     * 
     * @return bool returns TRUE on valid purse name and FALSE on invalid purse name.
     * 
     */
        
    public function CheckPurseFormat($usrPurse = '')
        {
        $tmpResult = 0;
        
        if (empty($usrPurse) === TRUE || is_string($usrPurse) === FALSE || strlen($usrPurse) != 13)
            {
            return FALSE;
            }
                      
        //$tmpResult = preg_match('/^(Z|R|E|U|Y|B|G|D|C)\d{12}$/', $usrPurse); // for all supported purse types
        $tmpResult = preg_match('/^U\d{12}$/', $usrPurse); // for UA only

        if ($tmpResult == 0) {return FALSE;}
        else if ($tmpResult > 0) {return TRUE;}
        else 
            {
            throw new plcPaymentSystemException('Error while checking purse name.', 11201, null ,'Undefined error occurred while checking purse name: "'.strval($usrPurse).'".');
            }          
        }
        
    /** 
     * Function used to send requests to the remote server (remote API).
     * 
     * Current function uses CURL functions and current class preferences to send requests to the
     * remote server (remote API).
     * 
     * @access protected
     * 
     * @param string URL to which request will be send 
     * @param array data to be sent as POST request
     * 
     * @throws plcPaymentSystemException
     * 
     * @return string response from the remote server.
     * 
     */
        
    protected function SendRequest($usrURL = '', $usrPOSTData = array())
        {
        $tmpHeader = array();
        $tmpPOSTString = '';
        
        $tmpValue = '';
        $tmpKey = '';
        
        $tmpReqResult = FALSE;
        
        $Counter1 = 0;
        
        foreach ($usrPOSTData as $tmpKey => $tmpValue)
            {
            if ($Counter1 > 0) {$tmpPOSTString .= '&';}
                      
            $tmpPOSTString .= $tmpKey.'='.$tmpValue;
            $Counter1 += 1;
            }
            
        $tmpHeader[] = 'POST '.parse_url($usrURL, PHP_URL_PATH).' HTTP/1.0';
        $tmpHeader[] = 'User-Agent: '.$this->UserAgent;
        $tmpHeader[] = 'Host: '.$this->GateDomain;
        $tmpHeader[] = 'Content-Type: text/xml';
        $tmpHeader[] = 'Content-Length: '.strlen($tmpPOSTString);
            
        curl_setopt($this->CURLRes, CURLOPT_HEADER, FALSE);
        curl_setopt($this->CURLRes, CURLOPT_HTTPHEADER, $tmpHeader); 
        curl_setopt($this->CURLRes, CURLOPT_URL, $usrURL);
        curl_setopt($this->CURLRes, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($this->CURLRes, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($this->CURLRes, CURLOPT_POST, 1);
        curl_setopt($this->CURLRes, CURLOPT_POSTFIELDS, $tmpPOSTString);
        
        $this->OutboundPOST = $tmpPOSTString;
            
        $tmpReqResult = curl_exec($this->CURLRes);
        if($tmpReqResult === FALSE) 
            {
            throw new plcPaymentSystemException('Fail to send a request to the server.', 11209, null ,'Faild to send a request to: "'.$usrURL.'".');
            }
        else
            {
            return $tmpReqResult;          
            }       
        }
        
    /**
     * Function that receives billing number for client.
     * 
     * This function will send request to the UkrGarant server to receve billing number for client.
     * HTTP (HTTPS) request will be carryd using curl library. Makes request to API.
     * 
     * Possible statuses that will be returned in response(<result> - tag):
     * 
     * 0 - Request successfully completed
     * 10 - bad signature
     * 
     * 100 - bad parameter list
     * 101 - wrong bad parameter list
     * 102 - wrong LMI_PAYMENT_AMOUNT
     * 103 - wrong LMI_WMCHECK_NUMBER
     * 104 - wrong LMI_PAYMENT_NO
     * 105 - wrong LMI_PAYMENT_DESC
     * 106 - wrong LMI_PAYSYSTEM_ID
     * 107 - wrong LMI_EXPIRE_DATE
     * 108 - wrong LMI_HASH
     * 
     * 300 - internal error
     * 
     * @access public 
     * 
     * @param string name purse which will receive payment from the client (LMI_PAYEE_PURSE).
     * @param int|float billing sum (LMI_PAYMENT_AMOUNT).
     * @param string client phone number (LMI_WMCHECK_NUMBER).
     * @param int payment system billing number (LMI_PAYMENT_NO) - deal id.
     * @param string additional billing description (LMI_PAYMENT_DESC).
     * 
     * @throws plcPaymentSystemException 
     * 
     * @return string returns invoice number on success.
     * 
     */    
        
    public function LoadInvoice($usrPurse = '', $usrSum = 0, $usrPhoneNum = '', $usrPSBillNum = 0, $usrAddBillDesc = '')
        {
        $tmpResult = FALSE;
        
        $tmpRespResult = NULL;
        $tmpRespDesc = '';
        $tmpRespInvoiceNum = NULL;
        $tmpSecKey = '';
        
        $tmpData = array();
        $tmpXMLReader = NULL;
        
        $tmpElmArray = array();
        
        if ($this->CheckPurseFormat($usrPurse) === FALSE)
            {
            throw new plcPaymentSystemException('Invalid purse name.', 11202, null ,'User provided invalid purse name: "'.strval($usrPurse).'".');
            }
            
        if (is_numeric($usrSum) === FALSE || $usrSum == 0)
            {
            throw new plcPaymentSystemException('Invalid user sum.', 11203, null ,'User provided invalid sum: "'.strval($usrSum).'".');
            }
        
        if (empty($usrPhoneNum) === TRUE)
            {
            throw new plcPaymentSystemException('Invalid user phone number.', 11204, null ,'User provided invalid phone number: "'.strval($usrPhoneNum).'".');
            }
            
        if (is_numeric($usrPSBillNum) === FALSE)
            {
            throw new plcPaymentSystemException('Invalid billing number.', 11205, null ,'User provided invalid billing number: "'.strval($usrPhoneNum).'".');
            }
        
        $tmpSecKey = $this->GenSecretKey(5);   
            
        $tmpData['LMI_PAYEE_PURSE'] = $usrPurse;
        $tmpData['LMI_PAYMENT_AMOUNT'] = $usrSum;
        $tmpData['LMI_WMCHECK_NUMBER'] = $usrPhoneNum;
        $tmpData['LMI_PAYMENT_NO'] = $usrPSBillNum;
        $tmpData['LMI_PAYMENT_DESC'] = $usrAddBillDesc;
        $tmpData['LMI_PAYSYSTEM_ID'] = $this->Controller->GetPaymentSystemId();
        $tmpData['LMI_EXPIRE_DATE'] = strval(strtotime($this->BillDuration));
        $tmpData['LMI_HASH'] = md5($usrPurse.$usrSum.$usrPhoneNum.$usrPSBillNum.strval($this->Controller->GetPaymentSystemId()).$tmpSecKey);
            
        $tmpResult = $this->SendRequest($this->LoadInvoiceURL, $tmpData);     
        
        /* XML response processing starts here */
            
        $tmpXMLReader = new XMLReader();

        $tmpResult = trim($tmpResult);

        $tmpResult = @$tmpXMLReader->xml($tmpResult, 'UTF-8', LIBXML_NOERROR|LIBXML_NOWARNING);
        if ($tmpResult === FALSE) 
            {
            throw new plcPaymentSystemException('Can not parse response from the server.', 11210, null ,'Error occurred while parsing XML response.');
            }
       
        while ($tmpXMLReader->read()) 
            {     
            /* First tag processing starts here */
            
            if ($tmpXMLReader->nodeType == XMLReader::ELEMENT)
                {
                array_push($tmpElmArray, $tmpXMLReader->name);                                        
                }
                
            /* First tag processing ends here */   
                
            /* Tag value processing starts here */    
                
            if ($tmpXMLReader->nodeType == XMLReader::TEXT)
                {
                switch(end($tmpElmArray))
                    {
                    case 'result':
                    $tmpRespResult = intval($tmpXMLReader->value);
                    break;
                
                    case 'resdesc':
                    $tmpRespDesc = $tmpXMLReader->value;
                    break;               
                
                    case 'LMI_INVOICE_NUMBER':
                    $tmpRespInvoiceNum = $tmpXMLReader->value;
                    break;
                    }              
                }
                
            /* Tag value processing ends here */  
                
            /* End tag processing starts here */    
                
            if ($tmpXMLReader->nodeType == XMLReader::END_ELEMENT)
                {
                if (end($tmpElmArray) == $tmpXMLReader->name)
                    {                                                         
                    array_pop($tmpElmArray);
                    }
                else
                    {                   
                    throw new plcPaymentSystemException('Closing tag is missing.', 11211, null ,'Closing tag for element "'.end($tmpElmArray).'" is not found.');
                    }
                }  
                
            /* End tag processing starts here */                                       
            }
            
        if (is_null($tmpRespResult) === TRUE)
            {
            throw new plcPaymentSystemException('Undefined result tag value.', 11212, null ,'Result tag value is NULL.');
            }
            
        switch($tmpRespResult)
            {
            case 0:
                
            if (is_null($tmpRespInvoiceNum) === TRUE)
                {
                throw new plcPaymentSystemException('Invoice number is not set.', 11213, null ,'New invoice number is not set or is invalid.');
                }
            
            $this->Controller->SetSecretKey($tmpSecKey);   
                 
            return $tmpRespInvoiceNum;    
            break; 
            
            case 10:   
            case 100:
            case 101:
            case 102:
            case 103:
            case 104:
            case 105:
            case 106:
            case 107:
            case 108:
            case 300:    
            throw new plcPaymentSystemException('Get invoice error.', 11214, null, 'Get invoice error number "'.$tmpRespResult.'".');    
            break;  
        
            default:
            throw new plcPaymentSystemException('Invalid response code.', 11215, null, 'Invalid error code "'.$tmpRespResult.'".');    
            break;
            }
            
        /* XML response processing ends here */    
            
        throw new plcPaymentSystemException('Cannot load invoice number.', 11221, null, 'Undefined error occured while loading invoice number.');    
        } 
        
    /**
     * Function that loads invoice details from the remote server as well as invoice details from the 
     * local database.
     *
     * Simple function that loads invoice details from the remote server as well as invoice details from the 
     * local database.
     * 
     * @access public
     * 
     * @param string UkrGarant invoice number
     * 
     * @throws plcPaymentSystemException
     * 
     * @return array returns array with invocie data.
     *                                   
     */           
        
    public function LoadInvoiceDetails($usrInvoiceNum)
        {
        $tmpConf = $this->Controller->GetINISettings();
        
        $tmpResult = FALSE;
        $tmpXMLReader = NULL;
        
        $tmpInvoiceRow = FALSE;
        
        $tmpSecKey = '';
        $tmpData = array(); 
        $tmpElmArray = array();
        $tmpResultArr = array();
        
        $tmpPayCnt = -1;
        
        $tmpSecKey = $this->GenSecretKey(5);
      
        $tmpData['LMI_INVOICE_NUMBER'] = $usrInvoiceNum;    
        $tmpData['LMI_HASH'] = md5($tmpSecKey.$usrInvoiceNum); 
        
        $tmpResult = $this->SendRequest($this->LoadInvoiceDetailsURL, $tmpData);
        
        /* XML response processing starts here */
            
        $tmpXMLReader = new XMLReader();

        $tmpResult = trim($tmpResult);

        $tmpResult = @$tmpXMLReader->xml($tmpResult, 'UTF-8', LIBXML_NOERROR|LIBXML_NOWARNING);
        if ($tmpResult === FALSE) 
            {
            throw new plcPaymentSystemException('Can not parse response from the server.', 11210, null ,'Error occurred while parsing XML response.');
            }
       
        while ($tmpXMLReader->read()) 
            { 
            /* First tag processing starts here */
            
            if ($tmpXMLReader->nodeType == XMLReader::ELEMENT)
                {
                array_push($tmpElmArray, $tmpXMLReader->name);  
                
                switch(end($tmpElmArray))
                    {
                    case 'Pays':

                    $tmpResultArr['Pays'] = array();    
                    break;  
                
                    case 'Pay':
                    $tmpPayCnt += 1;
                    $tmpResultArr['Pays'][$tmpPayCnt] = array();
                    break;                
                    }
                }
                
            /* First tag processing ends here */   
                
            /* Tag value processing starts here */    
                
            if ($tmpXMLReader->nodeType == XMLReader::TEXT)
                {
                switch(end($tmpElmArray))
                    {
                    case 'result': 
                    
                    $tmpResultArr['result'] = intval($tmpXMLReader->value);                    
                    break;
                                  
                    case 'resdesc': 
                    case 'InvoiceNumber': 
                    case 'PaymentNumber': 
                    
                    $tmpResultArr[end($tmpElmArray)] = $tmpXMLReader->value;                    
                    break;   
                
                    case 'InvoiceStatus':
                    $tmpResultArr[end($tmpElmArray)] = intval($tmpXMLReader->value);     
                    break;
                
                    case 'Amount':
                    case 'Balance':
                    $tmpResultArr[end($tmpElmArray)] = floatval($tmpXMLReader->value);    
                    break;
                                               
                    case 'CreateDate':
                    case 'ProcessDate':

                    if ($tmpPayCnt >= 0) {$tmpResultArr['Pays'][$tmpPayCnt][end($tmpElmArray)] = $tmpXMLReader->value;}    
                    break;
                    
                    case 'PayType':
                    if ($tmpPayCnt >= 0) {$tmpResultArr['Pays'][$tmpPayCnt][end($tmpElmArray)] = intval($tmpXMLReader->value);}  
                    break;
                    
                    case 'Sum':
                        
                    if ($tmpPayCnt >= 0) {$tmpResultArr['Pays'][$tmpPayCnt][end($tmpElmArray)] = floatval($tmpXMLReader->value);}     
                    break;    
                    }              
                }
                
            /* Tag value processing ends here */  
                
            /* End tag processing starts here */    
                
            if ($tmpXMLReader->nodeType == XMLReader::END_ELEMENT)
                {
                if (end($tmpElmArray) == $tmpXMLReader->name)
                    {                                                         
                    array_pop($tmpElmArray);
                    }
                else
                    {                   
                    throw new plcPaymentSystemException('Closing tag is missing.', 11211, null ,'Closing tag for element "'.end($tmpElmArray).'" is not found.');
                    }
                }  
                
            /* End tag processing starts here */           
            }
                         
        switch($tmpResultArr['result'])
            {
            case 0:                   
            break; 
            
            case 10:
            case 100:
            case 101:
            case 102:
            case 300:
            throw new plcPaymentSystemException('Get invoice details error.', 11222, null, 'Get invoice details error number "'.$tmpResultArr['result'].'".');    
            break;  
        
            default:
            throw new plcPaymentSystemException('Invalid response code.', 11215, null, 'Invalid error code "'.$tmpResultArr['result'].'".');    
            break;
            }
            
        /* XML response processing ends here */    
            
        /* Load invoice info from database starts here */  
        
        $tmpInvoiceRow = $this->PaymentsSpecTableGate->Find($usrInvoiceNum, $tmpConf['spec_payments_table_aliases']['UKRGARANT_INVOICE_NUMBER']);    
        if ($tmpInvoiceRow !== FALSE)
            {
            $tmpResultArr['UKRGARANT_ID'] = $tmpInvoiceRow->UKRGARANT_ID; 
            $tmpResultArr['UKRGARANT_INVOICE_NUMBER'] = $tmpInvoiceRow->UKRGARANT_INVOICE_NUMBER;
            $tmpResultArr['UKRGARANT_WM_TRANS_NO'] = $tmpInvoiceRow->UKRGARANT_WM_TRANS_NO;
            $tmpResultArr['UKRGARANT_PAYEE_PURSE'] = $tmpInvoiceRow->UKRGARANT_PAYEE_PURSE;
            $tmpResultArr['UKRGARANT_SUM'] = $tmpInvoiceRow->UKRGARANT_SUM;
            $tmpResultArr['UKRGARANT_DEAL_ID'] = $tmpInvoiceRow->UKRGARANT_DEAL_ID;
            $tmpResultArr['UKRGARANT_PAYER_PURSE'] = $tmpInvoiceRow->UKRGARANT_PAYER_PURSE;
            $tmpResultArr['UKRGARANT_PAYER_WMID'] = $tmpInvoiceRow->UKRGARANT_PAYER_WMID;
            $tmpResultArr['UKRGARANT_PAYER_PHONE'] = $tmpInvoiceRow->UKRGARANT_PAYER_PHONE;
            $tmpResultArr['UKRGARANT_HASH'] = $tmpInvoiceRow->UKRGARANT_HASH;
            $tmpResultArr['UKRGARANT_WM_TRANS_DATE'] = $tmpInvoiceRow->UKRGARANT_WM_TRANS_DATE;
            $tmpResultArr['UKRGARANT_DESC'] = $tmpInvoiceRow->UKRGARANT_DESC;
            $tmpResultArr['UKRGARANT_SECRET'] = $tmpInvoiceRow->UKRGARANT_SECRET;                                                  
            }
        else
            {
            $tmpResultArr['UKRGARANT_ID'] = 0; 
            $tmpResultArr['UKRGARANT_INVOICE_NUMBER'] = '';
            $tmpResultArr['UKRGARANT_WM_TRANS_NO'] = '';
            $tmpResultArr['UKRGARANT_PAYEE_PURSE'] = '';
            $tmpResultArr['UKRGARANT_SUM'] = 0;
            $tmpResultArr['UKRGARANT_DEAL_ID'] = 0;
            $tmpResultArr['UKRGARANT_PAYER_PURSE'] = '';
            $tmpResultArr['UKRGARANT_PAYER_WMID'] = '';
            $tmpResultArr['UKRGARANT_PAYER_PHONE'] = '';
            $tmpResultArr['UKRGARANT_HASH'] = '';
            $tmpResultArr['UKRGARANT_WM_TRANS_DATE'] = '';
            $tmpResultArr['UKRGARANT_DESC'] = '';
            $tmpResultArr['UKRGARANT_SECRET'] = '';             
            }
        
        /* Load invoice info from database ends here */     
                  
        return $tmpResultArr;
        } 
        
    /**
     * Function that loads change amount as check.
     *
     * Simple function that Function that loads change amount as check.
     * 
     * @access public
     * 
     * @param string UkrGarant invoice number
     * 
     * @throws plcPaymentSystemException
     * 
     * @return array returns array with check details.
     *                                   
     */           
        
    public function LoadInvoiceChange($usrInvoiceNum)
        {
        $tmpResult = FALSE;
        $tmpXMLReader = NULL;
        
        $tmpSecKey = '';
        $tmpData = array(); 
        $tmpElmArray = array();
        $tmpResultArr = array();
              
        $tmpSecKey = $this->GenSecretKey(5);
      
        $tmpData['LMI_INVOICE_NUMBER'] = $usrInvoiceNum;    
        $tmpData['LMI_HASH'] = md5($tmpSecKey.$usrInvoiceNum); 
        
        $tmpResult = $this->SendRequest($this->LoadInvoiceChangeURL , $tmpData);
        
        /* XML response processing starts here */
            
        $tmpXMLReader = new XMLReader();

        $tmpResult = trim($tmpResult);

        $tmpResult = @$tmpXMLReader->xml($tmpResult, 'UTF-8', LIBXML_NOERROR|LIBXML_NOWARNING);
        if ($tmpResult === FALSE) 
            {
            throw new plcPaymentSystemException('Can not parse response from the server.', 11210, null ,'Error occurred while parsing XML response.');
            }
       
        while ($tmpXMLReader->read()) 
            { 
            /* First tag processing starts here */
            
            if ($tmpXMLReader->nodeType == XMLReader::ELEMENT)
                {
                array_push($tmpElmArray, $tmpXMLReader->name);  
                }
                
            /* First tag processing ends here */   
                
            /* Tag value processing starts here */    
                
            if ($tmpXMLReader->nodeType == XMLReader::TEXT)
                {
                switch(end($tmpElmArray))
                    {
                    case 'result': 
                    
                    $tmpResultArr['result'] = intval($tmpXMLReader->value);                    
                    break;
                
                    case 'resdesc': 
                    
                    $tmpResultArr['resdesc'] = $tmpXMLReader->value;                    
                    break;                
                
                    case 'change':
                        
                    $tmpResultArr['change'] = floatval($tmpXMLReader->value);  
                    break;
                    }              
                }
                
            /* Tag value processing ends here */  
                
            /* End tag processing starts here */    
                
            if ($tmpXMLReader->nodeType == XMLReader::END_ELEMENT)
                {
                if (end($tmpElmArray) == $tmpXMLReader->name)
                    {                                                         
                    array_pop($tmpElmArray);
                    }
                else
                    {                   
                    throw new plcPaymentSystemException('Closing tag is missing.', 11211, null ,'Closing tag for element "'.end($tmpElmArray).'" is not found.');
                    }
                }  
                
            /* End tag processing starts here */           
            }
                         
        switch($tmpResultArr['result'])
            {
            case 0:                   
            break; 
            
            case 10:
            case 100:
            case 101: 
            case 102:
            case 200:
            case 201:
            case 202:
            case 300:
            throw new plcPaymentSystemException('Get invoice change error.', 11223, null, 'Get invoice change error number "'.$tmpResultArr['result'].'".');    
            break;  
        
            default:
            throw new plcPaymentSystemException('Invalid response code.', 11215, null, 'Invalid error code "'.$tmpResultArr['result'].'".');    
            break;
            }
            
        /* XML response processing ends here */    
                              
        return $tmpResultArr;
        }
        
    /**
     * Function that cancels invoice processing on remote server.
     *
     * Simple function that cancels invoice processing on remote server. This function also changes 
     * status field of the corresponding record in the database.
     * 
     * @access public
     * 
     * @param string UkrGarant invoice number
     * 
     * @throws plcChassisException, plcPaymentSystemException
     * 
     * @return array returns array with details about cancelled invoice.
     *                                   
     */             
        
    public function CancelInvoice($usrInvoiceNum)
        {
        $tmpResult = FALSE;
        $tmpXMLReader = NULL;
        
        $tmpSecKey = '';
        $tmpData = array(); 
        $tmpElmArray = array();
        $tmpResultArr = array();
              
        $tmpSecKey = $this->GenSecretKey(5);
      
        $tmpData['LMI_INVOICE_NUMBER'] = $usrInvoiceNum;    
        $tmpData['LMI_HASH'] = md5($tmpSecKey.$usrInvoiceNum); 
        
        $tmpResult = $this->SendRequest($this->CancelInvoiceURL , $tmpData);
        
        /* XML response processing starts here */
            
        $tmpXMLReader = new XMLReader();

        $tmpResult = trim($tmpResult);

        $tmpResult = @$tmpXMLReader->xml($tmpResult, 'UTF-8', LIBXML_NOERROR|LIBXML_NOWARNING);
        if ($tmpResult === FALSE) 
            {
            throw new plcPaymentSystemException('Can not parse response from the server.', 11210, null ,'Error occurred while parsing XML response.');
            }
       
        while ($tmpXMLReader->read()) 
            { 
            /* First tag processing starts here */
            
            if ($tmpXMLReader->nodeType == XMLReader::ELEMENT)
                {
                array_push($tmpElmArray, $tmpXMLReader->name);  
                }
                
            /* First tag processing ends here */   
                
            /* Tag value processing starts here */    
                
            if ($tmpXMLReader->nodeType == XMLReader::TEXT)
                {
                switch(end($tmpElmArray))
                    {
                    case 'result': 
                    
                    $tmpResultArr['result'] = intval($tmpXMLReader->value);                    
                    break;
                
                    case 'resdesc': 
                    
                    $tmpResultArr['resdesc'] = $tmpXMLReader->value;                    
                    break;                              
                    }              
                }
                
            /* Tag value processing ends here */  
                
            /* End tag processing starts here */    
                
            if ($tmpXMLReader->nodeType == XMLReader::END_ELEMENT)
                {
                if (end($tmpElmArray) == $tmpXMLReader->name)
                    {                                                         
                    array_pop($tmpElmArray);
                    }
                else
                    {                   
                    throw new plcPaymentSystemException('Closing tag is missing.', 11211, null ,'Closing tag for element "'.end($tmpElmArray).'" is not found.');
                    }
                }  
                
            /* End tag processing starts here */           
            }
                         
        switch($tmpResultArr['result'])
            {
            case 0:                   
            break; 
                  
            case 10:
            case 100:
            case 101: 
            case 102:
            case 200:
            case 201:
            case 202:
            case 203:
            case 204:
            case 300:
            throw new plcPaymentSystemException('Cancel invoice error.', 11224, null, 'Cancel invoice error number "'.$tmpResultArr['result'].'".');    
            break;  
        
            default:
            throw new plcPaymentSystemException('Invalid response code.', 11215, null, 'Invalid error code "'.$tmpResultArr['result'].'".');    
            break;
            }
            
        /* XML response processing ends here */    
                              
        return $tmpResultArr;       
        }
        
    /** 
     * Function used to save preliminary invoice information.
     * 
     * Current function saves UkrGarant invoice information (preliminary info).
     * 
     * @access public
     * 
     * @param string invoice number 
     * @param string payee purse
     * @param int deal id
     * @param string payer phone
     * @param string transaction description
     * 
     * @throws plcChassisException
     * 
     * @return bool returns TRUE on success and FALSE on fail.
     * 
     */        
        
    public function PreSaveInvoice($usrInvoiceNum, $usrPayeePurse, $usrDealId, $usrPhone, $usrDesc)
        {
        $tmpData = array(
                        'UKRGARANT_INVOICE_NUMBER' => $usrInvoiceNum,
                        'UKRGARANT_PAYEE_PURSE' => $usrPayeePurse,
                        'UKRGARANT_DEAL_ID' => $usrDealId,
                        'UKRGARANT_PAYER_PHONE' => $usrPhone,
                        'UKRGARANT_DESC' => $usrDesc,
                        'UKRGARANT_SECRET' => $this->Controller->GetSecretKey(),
                        'UKRGARANT_STATUS' => 'new'
                        );
            
        return $this->PaymentsSpecTableGate->Insert($tmpData);
        }
        
    /** 
     * Function used to updates invoice information.
     * 
     * Current function updates UkrGarant invoice information.
     * 
     * @access public
     * 
     * @param array POST data that was recieved on invoice payment request 
     * 
     * @return bool returns TRUE on success and FALSE on fail.
     * 
     */          
        
    public function UpdateInvoice($usrInvoiceInfo)
        {
        $tmpData = array(
                        'UKRGARANT_WM_TRANS_NO' => $usrInvoiceInfo['LMI_SYS_TRANS_NO'],
                        'UKRGARANT_PAYEE_PURSE' => $usrInvoiceInfo['LMI_PAYEE_PURSE'],
                        'UKRGARANT_SUM' => $usrInvoiceInfo['LMI_PAYMENT_AMOUNT'],
                        'UKRGARANT_DEAL_ID' => $usrInvoiceInfo['LMI_PAYMENT_NO'],
                        'UKRGARANT_PAYER_PURSE' => $usrInvoiceInfo['LMI_PAYER_PURSE'],
                        'UKRGARANT_PAYER_WMID' => $usrInvoiceInfo['LMI_PAYER_WM'],
                        'UKRGARANT_PAYER_PHONE' => $usrInvoiceInfo['LMI_WMCHECK_NUMBER'],
                        'UKRGARANT_HASH' => $usrInvoiceInfo['LMI_HASH'],
                        'UKRGARANT_WM_TRANS_DATE' => $usrInvoiceInfo['LMI_SYS_TRANS_DATE'],
                        'UKRGARANT_DESC' => $usrInvoiceInfo['LMI_PAYMENT_DESC'],
                        'UKRGARANT_STATUS' => 'processed'
                        );
        try
            {
            return $this->PaymentsSpecTableGate->Update($tmpData, $this->PaymentsSpecTableGate->GetFieldByAlias('UKRGARANT_INVOICE_NUMBER').' = ?', $usrInvoiceInfo['LMI_INVOICE_NUMBER']);
            }
        catch(Exception $usrError)
            {
            return FALSE;
            }
        }
        
    /*
     * Function that validates incoming invoice information.
     * 
     * Simple function that validates incoming invoice information. This function also sets
     * following parameters of the controller:
     * 
     * Invoice number
     * Deal id
     * Secret key
     * Shop id
     * Shop currency id
     * 
     * @access public
     * 
     * @param array invoice payment notify data, for positive result must contain following data:
     * 
     * LMI_INVOICE_NUMBER - invoice number
     * LMI_SYS_TRANS_NO - WebMoney transaction ID (wmTranId)
     * LMI_PAYEE_PURSE - WebMoney Payee purse (WMU)
     * LMI_PAYMENT_AMOUNT - received sum
     * LMI_PAYMENT_NO - deal id
     * LMI_PAYER_PURSE - payer purse (WMU)
     * LMI_PAYER_WM - payer WMID
     * LMI_WMCHECK_NUMBER - payer phone number
     * LMI_HASH - MD5 hash (LMI_INVOICE_NUMBER + LMI_SYS_TRANS_NO + LMI_PAYEE_PURSE + LMI_PAYMENT_AMOUNT + LMI_PAYMENT_NO  + SecretKey)
     * LMI_SYS_TRANS_DATE - date of transaction (WebMoney date of transaction)
     * LMI_PAYMENT_DESC - invoice description   
     * 
     * @throws plcChassisException
     *    
     * @return bool TRUE on success and FALSE on fail.   
     *                               
     */           
        
    public function ValidateInvReq($usrInvData)
        {       
        if (empty($usrInvData['LMI_INVOICE_NUMBER']) === TRUE) {return FALSE;}
        if (empty($usrInvData['LMI_PAYMENT_NO']) === TRUE) {return FALSE;}
        
        $tmpInvoiceRow = $this->PaymentsSpecTableGate->Find($usrInvData['LMI_INVOICE_NUMBER'], 'UKRGARANT_INVOICE_NUMBER');     
        if ($tmpInvoiceRow === FALSE){return FALSE;}
        
        $tmpDealRow = $this->DealsTableGate->Find($usrInvData['LMI_PAYMENT_NO'], 'DEALS_ID');
        if ($tmpDealRow === FALSE) {return FALSE;}
        
        if ($tmpInvoiceRow->UKRGARANT_DEAL_ID != $tmpDealRow->DEALS_ID) {return FALSE;}
        
        $this->Controller->SetInvoiceNum($tmpInvoiceRow->UKRGARANT_INVOICE_NUMBER);
        $this->Controller->SetDealId(intval($tmpDealRow->DEALS_ID));
        
        $this->Controller->SetSecretKey($tmpInvoiceRow->UKRGARANT_SECRET);
        $this->Controller->SetShopId(intval($tmpDealRow->DEALS_SHOP_ID));

        return TRUE;
        }
                          
    /* Core methods ends here */ 
        
    /* Get methods starts here */
        
    /*
     * Function that used to return user agent name which will be used while sending requests to the remote server.
     * 
     * Simple function that returns user agent name which will be used while sending requests to the remote server. 
     * 
     * @access public
     *    
     * @return string user agent name.   
     *                               
     */           
       
    public function GetUserAgent()
        {
        return $this->UserAgent;
        } 
        
    /*
     * Function that used to return domain name which will be used while sending requests to the remote server.
     * 
     * Simple function that returns domain name which will be used while sending requests to the remote server. 
     * 
     * @access public
     *        
     * @return string domain name.   
     *                               
     */         
        
    public function GetGateDomain()
        {
        return $this->GateDomain;
        }
        
    /**
     * Function that used to return URL to which request will be made with purpose of loading new invoice number.
     * 
     * Simple function that returns URL to which request will be made with purpose of loading new invoice number. 
     * 
     * @access public
     *     
     * @return string URL to which request will be made with purpose of loading new invoice number.   
     *                               
     */         
        
    public function GetLoadInvoiceURL()
        {
        return $this->LoadInvoiceURL;
        }
        
    /**
     * Function that used to return URL to which request will be made with purpose of loading specific invoice details.
     * 
     * Simple function that returns URL to which request will be made with purpose of loading specific invoice details.
     * 
     * @access public
     *     
     * @return string URL to which request will be made with purpose of loading specific invoice details 
     *                               
     */             
        
    public function GetLoadInvoiceDetailsURL()
        {
        return $this->LoadInvoiceDetailsURL;
        }
        
    /**
     * Function that used to return URL to which request will be made with purpose of loading specific invoice check details.
     * 
     * Simple function that returns URL to which request will be made with purpose of loading specific invoice check details.
     * 
     * @access public
     *     
     * @return string URL to which request will be made with purpose of loading specific invoice check details 
     *                               
     */             
        
    public function GetLoadInvoiceChangeURL()
        {
        return $this->LoadInvoiceChangeURL;
        } 
        
    /**
     * Function that used to return URL to which request will be made with purpose of canceling specific invoice.
     * 
     * Simple function that returns URL to which request will be made with purpose of canceling specific invoice.
     * 
     * @access public
     *     
     * @return string URL to which request will be made with purpose of canceling specific invoice 
     *                               
     */             
        
    public function GetCancelInvoiceURL()
        {
        return $this->CancelInvoiceURL;
        }           
        
    /*
     * Function that used to return current billing duration.
     * 
     * Simple function that returns current billing duration. 
     * 
     * @access public
     *       
     * @return string current billing duration.   
     *                               
     */         
                
    public function GetBillDuration()
        {
        return $this->BillDuration;
        }
        
    /*
     * Function that used to return timeout value.
     * 
     * Simple function that returns timeout value. 
     * 
     * @access public
     *     
     * @return int returns timeout value.   
     *                               
     */          
        
    public function GetTimeOut()
        {
        return $this->CurTimeOut;
        }        
        
    /* Get methods ends here */
        
    /* Set methods starts here */
        
    /*
     * Function that used to set user agent name which will be used while sending requests to the remote server.
     * 
     * Simple function that sets (changes) user agent name which will be used while sending requests to the remote server. 
     * 
     * @access public
     * 
     * @param string user agent name 
     * 
     * @throws plcPaymentSystemException     
     *    
     * @return bool returns TRUE on success and FALSE on fail.   
     *                               
     */          
            
    public function SetUserAgent($usrUserAgent)
        {
        if (is_string($usrUserAgent) === FALSE || empty($usrUserAgent)) {return FALSE;}
        else
            {
            $this->UserAgent = $usrUserAgent;
            return TRUE;
            }
        }
        
    /*
     * Function that used to set domain name which will be used while sending requests to the remote server.
     * 
     * Simple function that sets (changes) domain name which will be used while sending requests to the remote server. 
     * 
     * @access public
     * 
     * @param string domain name 
     * 
     * @throws plcPaymentSystemException     
     *    
     * @return bool returns TRUE on success and FALSE on fail.   
     *                               
     */           
        
    public function SetGateDomain($usrDomain = '')
        {
        $tmpResult = NULL;
        if (is_string($usrDomain) === FALSE || empty($usrDomain) === TRUE) {return FALSE;}
        
        $tmpResult = parse_url($usrDomain);      
        if (is_null($tmpResult) === TRUE || $tmpResult === FALSE) {return FALSE;}
        else 
            {
            if (isset($tmpResult["host"]) === TRUE)
                {
                $this->GateDomain = $tmpResult["host"]; return TRUE;
                }
            else if (isset($tmpResult["path"]) === TRUE)
                {     
                $this->GateDomain = $tmpResult["path"]; return TRUE;
                }   
            else
                {
                return FALSE;
                }                       
            }
        }
        
    /*
     * Function that used to set current billing duration.
     * 
     * Simple function that sets (changes) current billing duration. 
     * 
     * @access public
     * 
     * @param string user billing duration in format that accepts strtotime() function.
     *               Examples: "+ 1 day", "+ 1 week" etc.   
     * 
     * @throws plcPaymentSystemException     
     *    
     * @return bool returns TRUE on success.   
     *                               
     */         
        
    public function SetBillDuration($usrBillDur = '')
        {
        $tmpResult = FALSE;
        
        if (empty($usrBillDur) === TRUE || is_string($usrBillDur) === FALSE)
            {
            throw new plcPaymentSystemException('Invalid billing duration.', 11206, null, 'User provided invalid billing duration: "'.strval($usrBillDur).'".');
            }
            
        $usrBillDur = @strtotime($usrBillDur);
        
        if ($usrBillDur == FALSE)
            {
            throw new plcPaymentSystemException('Invalid billing duration.', 11206, null ,'User provided invalid billing duration: "'.strval($usrBillDur).'".');
            }
            
        return TRUE;
        }
        
    /*
     * Function that used to set timeout before stop waiting for response from remote server.
     * 
     * Simple function that sets (changes) timeout before stop waiting for response from remote server. 
     * 
     * @access public
     * 
     * @param int timeout in seconds 
     * 
     * @throws plcPaymentSystemException     
     *    
     * @return bool returns TRUE on success.   
     *                               
     */          
        
    public function SetTimeOut($usrTimeOut)
        {
        if(empty($usrTimeOut) === TRUE || isset($usrTimeOut) === FALSE || is_int($usrTimeOut) === FALSE)
            {
            return FALSE;
            }
    
        if (is_null($this->CURLRes) === TRUE)
            {
            throw new plcPaymentSystemException('CURL is not initialized yet.', 11208, null ,'CURL library is not initialized or is not installed.');
            } 
     
        $this->CurTimeOut = $usrTimeOut;  
        curl_setopt($this->CURLRes, CURLOPT_TIMEOUT, $usrTimeOut);
        return TRUE;
        } 
        
    /* Set methods ends here */    
    }

    
    
?>
