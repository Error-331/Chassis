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
 * Class plcComepayModel is a part of PHP framework - Chassis.   
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
 * Classes for work with Comepay payment system.
 *  
 * @subpackage Comepay
 * @author Selihov Sergei Stanislavovich <red331@mail.ru>   
 */
 
/**
 * Documents the plcUkrGarantWebMoneyModel class.
 * 
 * Following class is a main model class for work with Comepay payment system.
 *    
 * @subpackage plcComepayModel
 * @author Selihov Sergei Stanislavovich <red331@mail.ru>   
 */

require_once('Chassis/ErrorHandling/plcPaymentSystemException.php');
require_once('Chassis/PaymentSystems/plcPaymentSystemAbstractModel.php');

class plcComepayModel extends plcPaymentSystemAbstractModel
    {    
    /* Core methods starts here */
    
    public function __construct($usrController, $usrDBConnector) 
        {
        $this->CurController = $usrController;
        $this->DBConnector = $usrDBConnector;
        }
        
    public function __destruct() 
        { 
        }
        
    protected function ConvertTimeStamp($usrTime = '')
        {
        return date('YmdHis', intval($usrTime));
        }
     
    // FALSE or string    
    public function ConvertToTimeStampStr($usrDate = '')
        {        
        if (empty($usrDate) === TRUE || strlen($usrDate) < 14) {return FALSE;}
        else 
            {
            $tmpHours = intval(substr($usrDate, 8,2));
            $tmpMins = intval(substr($usrDate, 10,2));
            $tmpSecs = intval(substr($usrDate, 12,2));
        
            $tmpDay = intval(substr($usrDate, 6,2));
            $tmpMonth = intval(substr($usrDate, 4,2));
            $tmpYear = intval(substr($usrDate, 0,4));
           
            $tmpTimeStamp = mktime($tmpHours, $tmpMins, $tmpSecs, $tmpMonth, $tmpDay, $tmpYear);
            if ($tmpTimeStamp === FALSE) {return FALSE;}
            else
                {
                return $tmpTimeStamp;
                }
            }
        }
                
    public function LoadDistinctionsData()
        {
        $tmpResult = FALSE;
        $tmpSubResult = FALSE;
        $tmpReportId = NULL;
        
        if (isset($_GET['id_report']) === FALSE || empty($_GET['id_report']) === TRUE) {return array('error', array('301', '"id_report" field is not set in the request'));}
        
        $tmpReportId = intval($_GET['id_report']);
        $tmpResult = $this->GetReportData($tmpReportId);
        
        if ($tmpResult === FALSE) {return arrar('error', array('302','Cannot find report record for id_report - "'.$tmpReportId.'"'));}
        
        try 
            {
            $tmpSubResult = $this->GetTransDistinctions($tmpResult);
            }
        catch (plcChassisException $usrError)
            {
            return array('error', array('303','Cannot get distinctions data ('.$usrError->getCode().' - "'.$usrError->getMessage().'")'));
            }

        if ($tmpSubResult === FALSE) {return array('error', array('303', 'Cannot get distinctions data'));}
        else {return array('success', $tmpSubResult);}                
        }
        
    public function CheckRegisterData()
        {
        $tmpResult = FALSE;
        $tmpSubResult = FALSE;
        $tmpReportId = NULL;
        
        if (isset($_GET['id_report']) === FALSE || empty($_GET['id_report']) === TRUE) {return array('201','"id_report" field is not set in the request');}
        
        $tmpReportId = intval($_GET['id_report']);
        $tmpResult = $this->GetReportData($tmpReportId);
        
        if ($tmpResult === FALSE) {return array('202','Cannot find report record for id_report - "'.$tmpReportId.'"');}
        
        try 
            {
            $tmpSubResult = $this->GetTransDistinctions($tmpResult);
            }
        catch (plcChassisException $usrError)
            {
            return array('203','Cannot get distinctions data ('.$usrError->getCode().' - "'.$usrError->getMessage().'")');
            }
        
        if ($tmpSubResult === FALSE) {return TRUE;}
        else {return FALSE;}
        }
        
    public function ParseSavePayReg()
        {
        $tmpResult = FALSE;
        $tmpXMLReader = NULL;
        $tmpElmArray = array();
        $tmpPayArray  =array();
        
        $tmpPayArrCnt = 0;
        $tmpStr = '';
         
        $tmpKey = '';
        $tmpValue = '';
        
        $tmpVersion = NULL;
        $tmpIdReport = NULL;
        $tmpStartDate = NULL;
        $tmpEndDate = NULL;
             
        $tmpPayRegData = NULL;
        $tmpPostContents = file_get_contents('php://input');
        $tmpPostContents = trim($tmpPostContents);
        
        $tmpData = NULL;
        $tmpLastRegId = 0;
        
        $tmpRegGate = NULL;
        $tmpRegRecGate = NULL;
        
        $Counter1 = 0;

        $_GET['version'] = '1.0'; 

        if ($tmpPostContents === FALSE) {return array('102','Can not get POST data');}
        else if (empty($tmpPostContents) === TRUE) {return array('103', 'POST data is empty');}

        $tmpXMLReader = new XMLReader();
        
        //echo(LIBXML_NOERROR); exit();
        
        $tmpResult = @$tmpXMLReader->xml($tmpPostContents, 'UTF-8', LIBXML_NOERROR|LIBXML_NOWARNING);
        if ($tmpResult === FALSE) {return array('104', 'Cannot parse xml data');}
        
        //$tmpXMLReader->setParserProperty(XMLReader::VALIDATE, true);
        //if ($tmpXMLReader->isValid() === FALSE) {return array('105', 'Not valid XML data');} 
                  
        /* XML read starts here */
        
        while (@$tmpXMLReader->read()) 
            {
            //echo($tmpXMLReader->name);
            //echo('<br/>');
            
            if ($tmpXMLReader->nodeType == XMLReader::ELEMENT)
                {
                array_push($tmpElmArray, $tmpXMLReader->name);
                                          
                if ($tmpXMLReader->name == 'payment')
                    {
                    $tmpPayArrCnt = count($tmpPayArray);   
                    if ($tmpPayArrCnt > 0 && count($tmpPayArray[$tmpPayArrCnt - 1]) < 5)
                        {
                        $tmpStr = '';
                        foreach ($tmpPayArray[$tmpPayArrCnt - 1] as $tmpKey => $tmpValue)
                            {
                            $tmpStr.= $tmpKey.' - "'.$tmpValue.'"; '; 
                            }
                        
                        return array('111', 'Missing field for payment. Fields: '.$tmpStr);
                        }
                        
                    $tmpPayArray[] = array();    
                    }
                }
                
                
            if ($tmpXMLReader->nodeType == XMLReader::TEXT)
                {
                switch(end($tmpElmArray))
                    {
                    /* Main tags check starts here */
                    
                    case 'version':
                    $_GET['version'] = $tmpXMLReader->value;
                    $tmpVersion = $tmpXMLReader->value;    
                    break;
                
                    case 'id_report':
                    $_GET['id_report'] = $tmpXMLReader->value;
                    $tmpIdReport = $tmpXMLReader->value;      
                    break;
                
                    case 'start_date':
                    $tmpStartDate = $tmpXMLReader->value;      
                    break;     
                
                    case 'end_date': 
                    $tmpEndDate = $tmpXMLReader->value;      
                    break;              
                
                    /* Main tags check ends here */
                
                    /* Payment tags check starts here */
                
                    case 'id_payment':
                    $tmpPayArray[count($tmpPayArray) - 1]['id_payment'] =  $tmpXMLReader->value;     
                    break;
                
                    case 'date':
                    $tmpPayArray[count($tmpPayArray) - 1]['date'] =  $tmpXMLReader->value;     
                    break;     
                
                    case 'account':
                    $tmpPayArray[count($tmpPayArray) - 1]['account'] =  $tmpXMLReader->value;     
                    break;      
                
                    case 'sum':
                    $tmpPayArray[count($tmpPayArray) - 1]['sum'] =  $tmpXMLReader->value;     
                    break;       
                
                    case 'service':
                    $tmpPayArray[count($tmpPayArray) - 1]['service'] =  $tmpXMLReader->value;     
                    break;                                 
                
                    /* Payment tags check ends here */
                    }
                }
                
            if ($tmpXMLReader->nodeType == XMLReader::END_ELEMENT)
                {
                if (end($tmpElmArray) == $tmpXMLReader->name)
                    {
                    if ($tmpXMLReader->name == 'payment')
                        {
                        $tmpPayArrCnt = count($tmpPayArray);   
                        if ($tmpPayArrCnt > 0 && count($tmpPayArray[$tmpPayArrCnt - 1]) < 5)
                            {
                            $tmpStr = '';
                            foreach ($tmpPayArray[$tmpPayArrCnt - 1] as $tmpKey => $tmpValue)
                                {
                                $tmpStr.= $tmpKey.' - "'.$tmpValue.'"; '; 
                                }
                        
                            return array('111', 'Missing field for payment. Fields: '.$tmpStr);
                            }                          
                        }                    
                                       
                    array_pop($tmpElmArray);
                    }
                else
                    {                   
                    return array('106', 'Missing closing tag for "'.end($tmpElmArray).'"');
                    }
                }
            }
        
        /* XML read ends here */
            
        if (is_null($tmpVersion) === TRUE || empty($tmpVersion) === TRUE) {return array('107', '"version" tag is missing');}
        if (is_null($tmpIdReport) === TRUE || empty($tmpIdReport) === TRUE) {return array('108', '"version" tag is missing');}
        if (is_null($tmpStartDate) === TRUE || empty($tmpStartDate) === TRUE) {return array('109', '"start_date" tag is missing');}
        if (is_null($tmpEndDate) === TRUE || empty($tmpEndDate) === TRUE) {return array('110', '"start_date" tag is missing');}
        
        if (count($tmpPayArray) <= 0) {return array('112', 'No payment records found');}
        
        $tmpXMLReader->close();
        
        /* Register save starts here */
                
        $tmpStartDate = $this->ConvertToTimeStampStr($tmpStartDate);
        $tmpEndDate = $this->ConvertToTimeStampStr($tmpEndDate);
        
        if ($tmpStartDate === FALSE) {return array('113', 'Register start date is not valid');}
        if ($tmpEndDate === FALSE) {return array('114', 'Register end date is not valid');}
        
        try
            {
            $tmpRegGate = new plcMySQLDBTableDataGateway($this->DBConnector, 'aiop_comepay_registers', 'id');
            
            $tmpData = array(
                            'version' => $tmpVersion,
                            'report_id' => intval($tmpIdReport),
                            'start_date' => $tmpStartDate,
                            'end_date' => $tmpEndDate,
                            ); 
            
            if ($tmpRegGate->Insert($tmpData) !== TRUE){return array('115', 'Register data can not be saved');}                       
            }
        catch (plcChassisException $usrError)
            {
            return array('116', 'Error while saving register data ('.$usrError->getCode().' - "'.$usrError->getMessage().'")');
            }
        
        /* Register save ends here */
            
        /* Register payments data save starts here */
        
        $tmpLastRegId = $tmpRegGate->GetLastInsertId();
        if ($tmpLastRegId === FALSE) {return array('117', 'Can not get register id');}
        
        try
            {
            $tmpRegRecGate = new plcMySQLDBTableDataGateway($this->DBConnector, 'aiop_comepay_register_payments', 'register_id');
            
            for ($Counter1 = 0; $Counter1 < count($tmpPayArray); $Counter1++)
                {
                $tmpPayArray[$Counter1]['date'] = $this->ConvertToTimeStampStr($tmpPayArray[$Counter1]['date']);
                if ($tmpPayArray[$Counter1]['date'] === FALSE) 
                    {
                    $tmpRegGate->Delete('id = ?', array($tmpLastRegId));
                    $tmpRegRecGate->Delete('register_id = ?', array($tmpLastRegId));
                    return array('119', 'Payment date is not valid');                   
                    }
                
                $tmpData = array(
                                'register_id' => $tmpLastRegId,
                                'payment_id' => intval($tmpPayArray[$Counter1]['id_payment']),
                                'date' => $tmpPayArray[$Counter1]['date'],
                                'account' => intval($tmpPayArray[$Counter1]['account']),
                                'sum' => floatval($tmpPayArray[$Counter1]['sum']),
                                ); 
                
                if ($tmpRegRecGate->Insert($tmpData) !== TRUE)
                    {
                    $tmpRegGate->Delete('id = ?', array($tmpLastRegId));
                    $tmpRegRecGate->Delete('register_id = ?', array($tmpLastRegId));
                    return array('120', 'Cannot save register payment data');                   
                    } 
                }            
            }
        catch(plcChassisException $usrError)
            {
            return array('118', 'Error while saving rigister payments data ('.$usrError->getCode().' - "'.$usrError->getMessage().'")');
            }

        /* Register payments data save ends here */
            
        return TRUE;
        }
        
    public function DeletePayment($usrTransId, $usrPayId)
        {
        $tmpTransGate = new plcMySQLDBTableDataGateway($this->DBConnector, 'aiop_transactions', array('payment_system_transaction_id', 'payment_system_id'));         
        $tmpTransGate->Delete('id = ? AND payment_system_transaction_id = ?', array($usrTransId, $usrPayId));
        }
    
    // true fals array    
    public function Check($usrAcc = '', $usrSum = '')
        {
        $tmpResult = FALSE;
        $tmpErrArr = array();
        
        $tmpClinetsGate = new plcMySQLDBTableDataGateway($this->DBConnector, 'aiop_clients'); 
       
        if(empty($usrAcc) === FALSE && empty($usrSum) === FALSE) 
            {    
            try
                {
                $tmpResult = $tmpClinetsGate->Find($usrAcc);                        
                if ($tmpResult === FALSE) {return array(504);} 
                if ($tmpResult->status == 'deleted') {return array(534);}
                
                return TRUE;
                }
            catch (plcChassisException $usrError)
                {
                $tmpErrArr[] = 599;
                $tmpErrArr[] = $usrError->getCode();
                $tmpErrArr[] = $usrError->getMessage();
                
                return $tmpErrArr; 
                }                 
            }  
        else if (empty($usrAcc) === FALSE && empty($usrSum) === TRUE)
            {
            try
                {
                $tmpResult = $tmpClinetsGate->Find($usrAcc);                        
                if ($tmpResult === FALSE) {return array(504);} 
                if ($tmpResult->status == 'deleted') {return array(534);}
                
                return TRUE;
                }
            catch (plcChassisException $usrError)
                {
                $tmpErrArr[] = 599;
                $tmpErrArr[] = $usrError->getCode();
                $tmpErrArr[] = $usrError->getMessage();
                
                return $tmpErrArr; 
                }              
            } 
        else
            {
            return array(508);
            }  
        }
        
    public function Payment($usrPayid, $usrAcc, $usrSum, $usrDate)
        {
        $tmpResult = FALSE;
        $tmpErrArr = array();
        
        $tmpAcc = NULL;
        $tmpSum = NULL;
        
        $tmpData = array();
        
        $tmpTransGate = new plcMySQLDBTableDataGateway($this->DBConnector, 'aiop_transactions', array('payment_system_transaction_id', 'payment_system_id')); 
        
        try
            {
            $tmpResult = $tmpTransGate->Find(array($usrPayid, $this->CurController->GetCurSysId()));
            
            if ($tmpResult !== FALSE) {return array(516, 
                                                    $tmpResult->payment_system_transaction_id, 
                                                    $tmpResult->transaction_id, 
                                                    $tmpResult->summ, 
                                                    $tmpResult->date
                                                   );     
                                      }             
            $tmpData = array(
                            'transaction_id' => $usrAcc,
                            'payment_system_transaction_id' => $usrPayid,
                            'payment_system_id' => $this->CurController->GetCurSysId(),
                            'summ' => $usrSum,
                            'date' => $this->ConvertToTimeStampStr($usrDate)
                            ); 
            
            if ($tmpTransGate->Insert($tmpData) !== TRUE)
                {
                $tmpErrArr[] = 599;
                $tmpErrArr[] = 'Unknown error';
                $tmpErrArr[] = 'Can not add transaction to database';
                
                return $tmpErrArr;  
                }

            $this->CurController->SetTransId($tmpTransGate->GetLastInsertId());
            return TRUE;
            }
        catch(plcChassisException $usrError)
            {
            $tmpErrArr[] = 599;
            $tmpErrArr[] = $usrError->getCode();
            $tmpErrArr[] = $usrError->getMessage();
                
            return $tmpErrArr;            
            }   
        }
    
    /* Core methods ends here */
        
    /* Get functions starts here */
    
    public function GetReportData($usrId = 0)
        {
        $tmpRegGate = new plcMySQLDBTableDataGateway($this->DBConnector, 'aiop_comepay_registers', array('report_id')); 
        return $tmpRegGate->Find($usrId);       
        }
    
    // FALSE - no data array -data
    // throws error    
    public function GetTransDistinctions($usrReportRow)
        {
        $tmpRegData = NULL;
        $tmpTransData = NULL;
        $tmpDist = array();
        
        $tmpDistFlag = FALSE;
        
        $Counter1 = 0;
        
        $this->DBConnector->SetQuery('SELECT * 
                                      FROM `aiop_comepay_register_payments` 
                                      WHERE 
                                      `register_id` = '.$usrReportRow->id.' 
                                      ORDER BY `date` DESC');
        
        $tmpRegData = $this->DBConnector->GetResultAssoc();

        $this->DBConnector->SetQuery('SELECT * 
                                      FROM `aiop_transactions` 
                                      WHERE 
                                      `date` >= '.$usrReportRow->start_date.' AND
                                      `date` <= '.$usrReportRow->end_date.'   
                                      ORDER BY `date` DESC');
        
        $tmpTransData = $this->DBConnector->GetResultAssoc();

        if ($tmpRegData === FALSE && $tmpTransData === FALSE) {return FALSE;}
        else if ($tmpRegData !== FALSE && $tmpTransData === FALSE) 
            {
            for ($Counter1 = 0; $Counter1 < count($tmpRegData); $Counter1++) 
                {
                $tmpDist[$Counter1] = array();                
                $tmpDist[$Counter1]['comepay'] = $tmpRegData[$Counter1];             
                $tmpDist[$Counter1]['alionpay'] = array('payment_id' => 0, 'date' => 0, 'account' => 0, 'sum' => 0, 'service' => 0);
                }
                
            return $tmpDist;
            }
        else if ($tmpRegData === FALSE && $tmpTransData !== FALSE) {return FALSE;}
        else
            {  
            for ($Counter1 = 0; $Counter1 < count($tmpRegData); $Counter1++)
                {                            
                if (isset($tmpTransData) === FALSE) 
                    {
                    $tmpDist[] = array();
                    $tmpDist[count($tmpDist) - 1]['comepay'] = $tmpRegData[$Counter1];             
                    $tmpDist[count($tmpDist) - 1]['alionpay'] = array('payment_id' => 0, 'date' => 0, 'account' => 0, 'sum' => 0, 'service' => 0);
                    
                    $tmpDistFlag = FALSE; 
                    continue;
                    }
                    
                if ($tmpRegData[$Counter1]['payment_id'] != $tmpTransData[$Counter1]['payment_system_transaction_id']) {$tmpDistFlag = TRUE;}                  
                if ($tmpRegData[$Counter1]['date'] != $tmpTransData[$Counter1]['date']) {$tmpDistFlag = TRUE;}                   
                if ($tmpRegData[$Counter1]['account'] != $tmpTransData[$Counter1]['transaction_id']) {$tmpDistFlag = TRUE;}    
                if ($tmpRegData[$Counter1]['sum'] != $tmpTransData[$Counter1]['summ']) {$tmpDistFlag = TRUE;}
                if ($tmpRegData[$Counter1]['service'] != $tmpTransData[$Counter1]['service']) {$tmpDistFlag = TRUE;}
                 
                if ($tmpDistFlag === TRUE)
                    {
                    $tmpDist[] = array();
                    $tmpDist[count($tmpDist) - 1]['comepay'] = $tmpRegData[$Counter1];             
                    $tmpDist[count($tmpDist) - 1]['alionpay'] = array('payment_id' => $tmpTransData[$Counter1]['payment_system_transaction_id'], 
                                                                      'date' =>  $tmpTransData[$Counter1]['date'], 
                                                                      'account' => $tmpTransData[$Counter1]['transaction_id'], 
                                                                      'sum' => $tmpTransData[$Counter1]['summ'], 
                                                                      'service' => $tmpTransData[$Counter1]['service']);
                    }
                
                $tmpDistFlag = FALSE;    
                }
                
            if (count($tmpDist) > 0) {return $tmpDist;}
            else {return FALSE;}
            }
        
        return FALSE;
        }
        
    /* Get functions ends here */
    
    }
?>
