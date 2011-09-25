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
 * Class plcComepayView is a part of PHP framework - Chassis.   
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
 * Documents the plcComepayView class.
 * 
 * Following class is a main model class for work with for work with Comepay payment system.
 *    
 * @subpackage plcPaymentSystemAbstractView
 * @author Selihov Sergei Stanislavovich <red331@mail.ru>   
 */

require_once('Chassis/ErrorHandling/plcPaymentSystemException.php');
require_once('Chassis/PaymentSystems/plcPaymentSystemAbstractView.php');

class plcComepayView extends plcPaymentSystemAbstractView
    {   
    /**
     * @access protected
     * @var array Comepay error list.
     */	 
    
    protected $ErrorList = array(
    
    '500' => array('is_fatal' => TRUE, 'error_desc' => 'Неверный номер абонента', 'error_com' => 'Номер абонента не соответствует требованиям Поставщика'),     
    '501' => array('is_fatal' => TRUE, 'error_desc' => 'Недопустимый параметр', 'error_com' => 'Параметр, переданный в запросе, не допустим'), 
    '502' => array('is_fatal' => TRUE, 'error_desc' => 'Платѐж отклонѐн', 'error_com' => 'Платеж отклонен вышестоящим Поставщиком'),  
    '503' => array('is_fatal' => FALSE, 'error_desc' => 'Сервис недоступен', 'error_com' => 'В данный момент запрос обработать невозможно'), 
    '504' => array('is_fatal' => TRUE, 'error_desc' => 'Абонент не найден', 'error_com' => 'Номер абонент соответствует требованиям Поставщика, но не найден в базе'),
    '506' => array('is_fatal' => FALSE, 'error_desc' => 'Неверная дата операции', 'error_com' => ''),
    '508' => array('is_fatal' => FALSE, 'error_desc' => 'Неверный формат сообщения', 'error_com' => 'Формат сообщения не соответствует данному регламенту, т.е. не указаны обязательные для данного типа запроса поля'),
    '509' => array('is_fatal' => FALSE, 'error_desc' => 'Превышено время ожидания', 'error_com' => 'Запрос выполняется Поставщиком слишком долго'),       
    '511' => array('is_fatal' => TRUE, 'error_desc' => 'Недостаточно средств', 'error_com' => 'Недостаточно средств на балансе Оператора для осуществления операции'), 
    '513' => array('is_fatal' => TRUE, 'error_desc' => 'Номер не принадлежит тестовой емкости', 'error_com' => 'Номер счета не находится в тестовой емкости Поставщика. Используется при отладке взаимодействия'), 
    '516' => array('is_fatal' => TRUE, 'error_desc' => 'Дублирование платежа', 'error_com' => 'Платеж с указанным id_payment уже добавлен и успешно обработан с <result>0</result>'),
    '517' => array('is_fatal' => TRUE, 'error_desc' => 'Домашний оператор не принимает платеж', 'error_com' => 'Домашний оператор абонента не может обработать запрос. Используется, если существует разветвленная сеть биллинговых центров с несколькими точками приема транзакций'),
    '518' => array('is_fatal' => FALSE, 'error_desc' => 'Лицевой счет абонента недоступен', 'error_com' => 'Номер абонент соответствует требованиям Поставщика, найден в базе, но операции по нему не могут совершаться в данный момент по техническим причинам'),       
    '534' => array('is_fatal' => TRUE, 'error_desc' => 'Счѐт абонента не активен или заблокирован', 'error_com' => 'Номер абонент соответствует требованиям Поставщика, найден в базе, но операции по нему не могут совершаться в данный момент по причине блокировки его Поставщиком'),
    '535' => array('is_fatal' => TRUE, 'error_desc' => 'Прием платежа запрещен,обратитесь к оператору', 'error_com' => 'Номер абонент соответствует требованиям Поставщика, найден в базе, но операции по нему не могут совершаться в данный момент и для продолжения работы абонента с Поставщиком ему необходимо обратиться к Поставщику'),
    '541' => array('is_fatal' => TRUE, 'error_desc' => 'Данная услуга не подключена', 'error_com' => 'Тип услуги, переданный Поставщику, не подключен у абонента'),        
    '546' => array('is_fatal' => TRUE, 'error_desc' => 'Неверный тип услуги', 'error_com' => 'Тип услуги отсутствует у Поставщик'),
    '599' => array('is_fatal' => TRUE, 'error_desc' => 'Другая ошибка Поставщика', 'error_com' => ''), // check        
    '801' => array('is_fatal' => TRUE, 'error_desc' => 'Реестр не загружен', 'error_com' => ''), // check
    '802' => array('is_fatal' => FALSE, 'error_desc' => 'Реестр обрабатывается', 'error_com' => 'Реестр находится в процессе обработки'),
    '803' => array('is_fatal' => TRUE, 'error_desc' => 'Реестр обработан с ошибкой', 'error_com' => ''), // check
    '804' => array('is_fatal' => TRUE, 'error_desc' => 'Реестр обработан, есть расхождения', 'error_com' => 'Реестр обработан. Есть расхождения с данными Оператора'), 
    '805' => array('is_fatal' => TRUE, 'error_desc' => 'Ошибка при загрузке списка расхождений', 'error_com' => '') // check
    );    
       
    /* Core methods starts here */
    
    /* Core methods ends here */
 
    /* Get methods starts here */
        
    /**
     * Function that returns related information for corresponding error code.
     * 
     * Simple function that returns related information for corresponding error code. 
     * 
     * @access public
     * 
     * @param string error code
     * @param array additional params
     * 
     * @return string|bool related error information on success and FALSE on fail. 
     *                                      
     */          
        
    public function GetErrorResponse($usrErrCode = '', $usrParams = array())
        {    
        if (array_key_exists($usrErrCode, $this->ErrorList) === TRUE)
            {
            $tmpResp = '<?xml version="1.0" encoding="utf-8" ?>';
            $tmpResp .= '<response>';
        
            /* Operation specific fields starts here */
            if ($usrErrCode == 516)
                {
                $tmpResp .= '<id_payment>'.$usrParams[0].'</id_payment>';
                $tmpResp .= '<account>'.$usrParams[1].'</account>';
                $tmpResp .= '<sum>'.$usrParams[2].'</sum>';
                $tmpResp .= '<date>'.$usrParams[3].'</date>';
                }
            else
                {
                foreach ($_GET as $tmpKey => $tmpValue)
                    {
                    $tmpResp .= '<'.$tmpKey.'>'.$tmpValue.'</'.$tmpKey.'>';
                    }
                }
            
            /* Operation specific fields starts here */
            
            if ($this->ErrorList[$usrErrCode]['is_fatal'] === TRUE)
                {
                $tmpResp .= '<result fatal="true">'.$usrErrCode.'</result>'; 
                }
            else
                {
                $tmpResp .= '<result fatal="false">'.$usrErrCode.'</result>'; 
                }           
            
            
            /* Secondary error fields set starts here */
            
            if ($usrErrCode == '599' || $usrErrCode == '801' || $usrErrCode == '802' || $usrErrCode == '803' || $usrErrCode == '805')
                {
                $tmpResp .= '<ext-result>'.$usrParams[0].'</ext-result>';
                $tmpResp .= '<ext-description>'.$usrParams[1].'<ext-description>';   
                }
            else
                {
                if ($this->ErrorList[$usrErrCode]['is_fatal'] === FALSE || $usrErrCode == '804')
                    {
                    $tmpResp .= '<ext-result>1</ext-result>';
                    $tmpResp .= '<ext-description>'.$this->ErrorList[$usrErrCode]['error_desc'].'<ext-description>';  
                    }
                }
            
            /* Secondary error fields set ends here */    
                    
            $tmpResp .= '</response>';
            return $tmpResp;
            }
        else
            {
            return FALSE;
            }          
        }
        
    public function GetDivergenceResponse($usrData)
        {
        $tmpResp = '';
        
        $tmpResp .= '<?xml version="1.0" encoding="utf-8" ?>';
        $tmpResp .= '<response>';
        
        $tmpResp .= '<operation>'.$_GET['operation'].'</operation>';
        $tmpResp .= '<id_report>'.$_GET['id_report'].'</id_report>';
        $tmpResp .= '<result>0</result>';

        /* Comepay results starts here  */
        
        $tmpResp .= '<payments>';
        
        for ($Counter1 = 0; $Counter1 < count($usrData); $Counter1++)
            {
            $tmpResp .= '<payment>';
            
            $tmpResp .= '<id_payment>'.strval($usrData[$Counter1]['comepay']['payment_id']).'</id_payment>';
            $tmpResp .= '<date>'.strval($usrData[$Counter1]['comepay']['date']).'</date>';
            $tmpResp .= '<account>'.strval($usrData[$Counter1]['comepay']['account']).'</account>';
            $tmpResp .= '<sum>'.strval($usrData[$Counter1]['comepay']['sum']).'</sum>';
            $tmpResp .= '<service>'.strval($usrData[$Counter1]['comepay']['service']).'</service>';
            
            $tmpResp .= '</payment>';
            }
        
        $tmpResp .= '</payments>';
        
        /* Comepay results ends here  */
        
        /* Alionpay results starts here  */
        
        $tmpResp .= '<ext-payments>';
        
        for ($Counter1 = 0; $Counter1 < count($usrData); $Counter1++)
            {
            $tmpResp .= '<ext-payment>';
            
            $tmpResp .= '<ext-id_payment>'.strval($usrData[$Counter1]['alionpay']['payment_id']).'</ext-id_payment>';
            $tmpResp .= '<ext-date>'.strval($usrData[$Counter1]['alionpay']['date']).'</ext-date>';
            $tmpResp .= '<ext-account>'.strval($usrData[$Counter1]['alionpay']['account']).'</ext-account>';
            $tmpResp .= '<ext-sum>'.strval($usrData[$Counter1]['alionpay']['sum']).'</ext-sum>';
            $tmpResp .= '<ext-service>'.strval($usrData[$Counter1]['alionpay']['service']).'</ext-service>';
            
            $tmpResp .= '</ext-payment>';
            }
        
        $tmpResp .= '</ext-payments>';   
        
        /* Alionpay results ends here  */

        
        $tmpResp .= '</response>';

        
        return $tmpResp;
        }
        
    public function GetCheckResultResponse()
        {
        $tmpResp = '';
             
        $tmpResp .= '<?xml version="1.0" encoding="utf-8" ?>';
        $tmpResp .= '<response>';
        $tmpResp .= '<operation>get_check_result</operation>';
        $tmpResp .= '<id_report>'.$_GET['id_report'].'</id_report>';
        $tmpResp .= '<result>0</result>';
        $tmpResp .= '</response>';
   
        return $tmpResp;
        }
        
    public function GetUploadPaymentsResponse()
        {
        $tmpResp = '';
        
        $tmpResp .= '<?xml version="1.0" encoding="utf-8" ?>';
        $tmpResp .= '<response>';
        $tmpResp .= '<operation>upload_payments</operation>';
        $tmpResp .= '<version>'.$_GET['version'].'</version>';
        $tmpResp .= '<id_report>'.$_GET['id_report'].'</id_report>';
        $tmpResp .= '<result>0</result>';
        $tmpResp .= '</response>';

        return $tmpResp;
        }
                
    public function GetPaymentResponse()
        {
        $tmpResp = '';         
        
        $tmpResp .= '<?xml version="1.0" encoding="utf-8" ?>';
        $tmpResp .= '<response>';
        $tmpResp .= '<operation>payment</operation>';
        $tmpResp .= '<id_payment>'.$_GET['id_payment'].'</id_payment>';
        $tmpResp .= '<ext-id_payment>'.$this->Controller->GetTransId().'</ext-id_payment>';
        $tmpResp .= '<date>'.$_GET['date'].'</date>';
        $tmpResp .= '<account>'.$_GET['account'].'</account>';
        $tmpResp .= '<sum>'.$_GET['sum'].'</sum>';
        $tmpResp .= '<result>0</result>';
        $tmpResp .= '</response>';

        return $tmpResp;           
        }
        
    /**
     * 
     * Function that generates and returns response for the server of check operation.
     * 
     * Simple function that generates and returns response for the server of check operation.
     * The response is generated as XML data.
     * 
     * @access public
     * 
     * @return string XML response.
     *                                        
     */  
        
     public function GetCheckResponse()
        {
        $tmpResp = '';         
        $Counter1 = 0;
        
        $tmpResp = '<?xml version="1.0" encoding="utf-8" ?>';
        $tmpResp .= '<response>';
        
        foreach ($_GET as $tmpKey => $tmpValue)
            {
            $tmpResp .= '<'.$tmpKey.'>'.$tmpValue.'</'.$tmpKey.'>';
            }
            
        $tmpResp .= '<result>0</result>';
        $tmpResp .= '</response>';

        return $tmpResp;                   
        }       
        
    /* Get methods ends here */
        
    /* Set methods starts here */
    /* Set methods ends here */     
    
    }
    
?>
