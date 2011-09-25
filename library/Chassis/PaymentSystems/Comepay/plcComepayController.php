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
 * Class plcComepayController is a part of PHP framework - Chassis.   
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
 * Documents the plcComepayController class.
 * 
 * Following class is a main controller class for work with Comepay payment system.
 *    
 * @subpackage plcComepayController
 * @author Selihov Sergei Stanislavovich <red331@mail.ru>   
 */

require_once('Chassis/ErrorHandling/plcPaymentSystemException.php');
require_once('Chassis/PaymentSystems/plcPaymentSystemAbstractController.php');

require_once('Chassis/PaymentSystems/WebMoney/plcComepayControllerModel.php');
require_once('Chassis/PaymentSystems/WebMoney/plcComepayControllerView.php');

class plcComepayController extends plcPaymentSystemAbstractController
    {    
    /**
     * @access protected
     * @var string current operation type ('check', 'payment' etc.).
     */	  
    
    protected $CurOp = '';
    
    /**
     * @access protected
     * @var string current error code.
     */	      
    
    protected $CurErrorCode = '';
    
    /**
     * @access protected
     * @var int maximum allowed request processing time (in seconds).
     */	      
    
    protected $MaxActAllowedTime = 60;  
        
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
         
        $this->Model = new plcComepayModel($this);
        $this->View = new plcComepayView($this);        
        }    
    
    
    public function __destruct()
        {           
        }

    /**
     * Function that returns current timestamp as float value.
     * 
     * Simple function that returns current timestamp as float value.
     * 
     * @access protected
     * 
     * @return float timestamp. 
     *                                      
     */  
    
    protected function FMircoTime() 
        {
        list($tmpUSec, $tmpSec) = explode(" ", microtime());
        return ((float)$tmpUSec + (float)$tmpSec);        
        }
    
    /*
     * Used to roll back transactions (delete them)
     */
    
    protected function RollBackPayment()
        {
        if ($this->CurOp == 'payment' && is_null($this->TransId) !== TRUE && is_null($this->PaymentId) !== TRUE)
            {
            $this->CurModel->DeletePayment($this->TransId, $this->PaymentId);
            }
        }
            
    /* Core methods ends here */
    
    /* Action methods starts here */
    
    // $usrParam - must contain two parameters, first goes to ext-result and second goes to ext-description

    /**
     * Action method that is invoked when error is occured.
     * 
     * Simple function that returns related information for corresponding error code.  Note that this
     * function also set corresponding class variable with user provided error code.
     * 
     * @access protected
     * 
     * @param string error code
     * @param array additional params
     * 
     * @return string|bool related error information on success and FALSE on fail. 
     *                                      
     */          
        
    protected function onError($usrErrCode = '', $usrParams = array()) 
        {
        if (isset($usrErrCode) === FALSE || empty($usrErrCode) === TRUE) {return FALSE;}
        $usrErrCode = strval($usrErrCode);
        $this->CurErrorCode = $usrErrCode;
    
        return $this->CurView->GetErrorResponse($this->CurErrorCode, $usrParams);       
        }
    
    /**
     * Action method that is invoked when timeout occurred while processing request from the remote server.
     * 
     * Simple function that returns related information for error associated with request timeout.
     * 
     * @access protected
     * 
     * @return string|bool related error information on success and FALSE on fail. 
     *                                      
     */     
    
    protected function onRequestTimeOut() 
        {   
        $tmpErrorParams = array('operation' => $this->CurOp);        
        return $this->onError(509, $tmpErrorParams);           
        }
        
    /**
     * Action method that is invoked when undefined operation requested by remote server.
     * 
     * Simple function that returns related information for error associated with undefined operation.
     * 
     * @access protected
     * 
     * @return string|bool related error information on success and FALSE on fail. 
     *                                      
     */          
    
    protected function onUndefinedOperation() 
        {
        $tmpErrorParams = array('operation' => $this->CurOp);          
        return $this->onError(508, $tmpErrorParams);     
        }
    
    // 300 - Undefined error
    // 301 - "id_report" field is not set in the request
    // 302 - Cannot find report record for id_report - "$tmpReportId"
    // 303 - Cannot get distinctions data
    
    // array - error|success, data
    protected function onGetDivergence()
        {
        $tmpResult = $this->CurModel->LoadDistinctionsData();
        
        if (is_array($tmpResult) !== TRUE) 
            {
            var_dump($tmpResult);
            return $this->onError(805, array('300', 'Undefined error'));
            }
        else
            {
            if ($tmpResult[0] == 'success')
                {
                return $this->CurView->GetDivergenceResponse($tmpResult[1]);
                }
            else
                {
                return $this->onError(805, array($tmpResult[1][0], $tmpResult[1][1]));
                }
            }
        }
    
    // 200 - Undefined error    
    // 201 - "id_report" field is not set in the request
    // 202 - Cannot find report record for id_report - "$tmpReportId"
    // 203 - Cannot get distinctions data
    
    // TRUE -no distinctions, FALSE - found distinctions, array - error 
    protected function onGetCheckResult()
        {
        $tmpResult = $this->CurModel->CheckRegisterData();
        
        if ($tmpResult === TRUE)
            {
            return $this->CurView->GetCheckResultResponse();
            }
        else if (is_array($tmpResult) === TRUE)
            {
            return $this->onError(803, $tmpResult);
            }
        else if ($tmpResult == FALSE)
            {
            return $this->onError(804);
            }          
        else
            {
            return $this->onError(803, array('200', 'Undefined error'));
            }
        }
    
    // 100 - Undefined error    
    // 101 - Undefined internal error
    // 102 - Can not get post data
    // 103 - POST data is empty
    // 104 - Cannot parse xml data
    // 105 - Not valid XML data
    // 106 - Missing closing tag for "$var"
    // 107 - "version" tag is missing
    // 108 - "id_report" tag is missing
    // 109 - "start_date" tag is missing
    // 110 - "end_date" tag is missing
    // 111 - Missing field for payment. Fields: $var
    // 112 - No payment records found
    // 113 - Register start date is not valid
    // 114 - Register end date is not valid
    // 115 - Register data can not be saved
    // 116 - Error while saving register data
    // 117 - Can not get register id
    // 118 - Error while saving rigister payments data
    // 119 - Payment date is not valid 
    // 120 - Cannot save register payment data
    
    // uses aiop_comepay_registers
    // TRUE -success, array - error else - undefined error
    
    protected function onUploadPayments()
        {
        $tmpResult = $this->CurModel->ParseSavePayReg();
       
        if ($tmpResult === TRUE)
            {
            return $this->CurView->GetUploadPaymentsResponse();
            }
        else if (is_array($tmpResult) === TRUE)
            {
            return $this->onError(801, $tmpResult);
            }
        else
            {
            return $this->onError(801, array('101', 'Undefined internal error'));
            }        
        }
 
    protected function onPayment() 
        { 
        $tmpResult = FALSE;    
        $tmpErrParams = array();
        
                   
    if (isset($_GET['id_payment']) === FALSE) {return $this->onError(508);} else {$this->PaymentId = $_GET['id_payment'];}
    if (isset($_GET['account']) === FALSE) {return $this->onError(508);} else {$this->PayerId = $_GET['account'];}
    if (isset($_GET['sum']) === FALSE) {return $this->onError(508);} else {$this->PayerSum = $_GET['sum'];}
    if (isset($_GET['date']) === FALSE) {return $this->onError(508);} else {$this->PayDate = $_GET['date'];}
    
    $tmpResult = $this->CurModel->Payment($this->PaymentId, $this->PayerId, $this->PayerSum, $this->PayDate);
    
    if ($tmpResult === TRUE) {return $this->CurView->GetPaymentResponse();}
    else if (is_array($tmpResult) === TRUE && $tmpResult[0] != 599 && $tmpResult[0] != 516) {return $this->onError($tmpResult[0]);}
    else {return $this->onError(array_shift($tmpResult), $tmpResult);}                
    }    
    
    // for now we will use clients id as check criteria
    
    
    /**
     * 
     * Function that checks whether payment must be conducted according to the current deal id or not.
     * 
     * This function that checks whether payment must be conducted according to the current deal id or not.
     * In case of any error it will send corresponding response to the comepay server. If deal id is 
     * successfully checked corresponding response will be send via current view.
     * 
     * @access protected
     *                                        
     */     
    
    protected function onCheck() 
        {
        $tmpResult = FALSE;    
        $tmpErrParams = array();
        
        $tmpDealId = 0;
    
        if (isset($_GET['account']) === FALSE) {$tmpDealId = '';} else {$tmpDealId = intval($_GET['account']);}
      
        $tmpResult = $this->CurModel->Check($tmpDealId);
    
        if ($tmpResult === TRUE) {return $this->CurView->GetCheckResponse();}
        else if (is_array($tmpResult) === TRUE && $tmpResult[0] != 599) {return $this->onError($tmpResult[0]);}
        else {return $this->onError(array_shift($tmpResult), $tmpResult);}  
        }
          
    /**
     * 
     * Main function for handling requests.
     * 
     * This function must be called whenever payment module needs to process external request. 
     * 
     * @access public
     * 
     * @return bool returns TRUE on success and FALSE on fail.
     *                                        
     */ 
    
    public function onRequest()
        {
        $tmpSTime = 0;
        $tmpETime = 0;
        $tmpDTime = 0;
    
        $tmpResult = FALSE;
        $tmpSubResult = FALSE;
    
        $tmpSTime = $this->FMircoTime();
    
        /* Request type switch starts here */
    
        if(isset($_GET['operation']) === FALSE || empty($_GET['operation']) === TRUE)
            {
            $this->CurOp = '';
            $tmpResult = $this->onUndefinedOperation();
            } 
        else
            {
            $this->CurOp = strtolower($_GET['operation']);
        
            switch ($this->CurOp) 
                {
                case 'check':
                $tmpResult = $this->onCheck();
                break;
    
                case 'payment':
                $tmpResult = $this->onPayment();
                break;
        
                case 'upload_payments':
                $tmpResult = $this->onUploadPayments();
                break;
        
                case 'get_check_result':
                $tmpResult = $this->onGetCheckResult();
                break;     
        
                case 'get_divergence':
                $tmpResult = $this->onGetDivergence();    
                break;
    
                default:
                $tmpResult = $this->onUndefinedOperation();
                break;        
                }  
            }
    
        /* Request type switch ends here */
    
        $tmpETime = $this->FMircoTime();       
        $tmpDTime = $tmpETime - $tmpSTime;

        if ($tmpDTime > $this->MaxActAllowedTime) {$tmpResult = $this->onRequestTimeOut();}
        
        $this->Model->LogApiRequest(array('ErrorCode' => $this->GetCurErrorCode()), 'i', $this->Model->GetCurPageURL()); 
           
        // Response sends here
        if (is_string($tmpResult) === TRUE)
            {
            return $tmpResult;
            }
        else
            {
            return FALSE;
            }
        }
    
    /* Action methods ends here */
    
    /* Get methods starts here */
        
    /**
     * Function that returns current error code.
     * 
     * Simple function that returns current error code.
     * 
     * @access public
     * 
     * @return string current error code.   
     *                                    
     */           
        
    public function GetCurErrorCode()
        {
        return $this->CurErrorCode;
        }
            
    /* Get methods ends here */
        
    /* Set methods starts here */         
    /* Set methods ends here */

}

?>
