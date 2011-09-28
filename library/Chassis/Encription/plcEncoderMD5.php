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
 * Class plcEncoderMD5 is a part of PHP framework - Chassis.   
 * 
 * @package     Chassis
 * @author      Selihov Sergei Stanislavovich <red331@mail.ru> 
 * @copyright   Copyright (c) 2010-2011 Selihov Sergei Stanislavovich.
 * @license     http://www.gnu.org/licenses/gpl.html  GNU GENERAL PUBLIC LICENSE (Version 3)
 *    
 */
 
/**
 * Classes for encryption work.
 *  
 * @subpackage Encryption
 * @author Selihov Sergei Stanislavovich <red331@mail.ru>   
 */
 
/**
 * Documents the plcEncoderMD5 class.
 * 
 * Following class is intended for simplification of encryption by md5 algorithm.
 *   
 * @subpackage plcEncoderMD5
 * @author Selihov Sergei Stanislavovich <red331@mail.ru>   
 */
 
require_once('Chassis/ErrorHandling/plcChassisException.php');
require_once('Chassis/Encription/plcEncoderAbstract.php');

class plcEncoderMD5 extends plcEncoderAbstract
    {    
    /* Core methods starts here */
    
    /**
     * Main constructor function.
     * 
     * Main constructor function initialises the work of the object.
     * 
     * @access public
     * 
     * @param mixed user data that will be encoded.
     * @param bool indicates whether to use random number in encoding process.
     * @param bool indicates whether to use current timestamp in encoding process.
     *                                     
     */     
  
    public function __construct($usrData = array(), $usrUseRndNum = FALSE, $useTime = FALSE)
        {
        parent::__construct($usrData, $usrUseRndNum, $useTime);
        }
        
    public function __destruct()
        {        
        }        
    
    /**
     * Function that encodes data using md5 algorithm and returns the result.
     * 
     * Simple function that encodes data using md5 algorithm and returns the result.
     *        
     * @access public 
     * 
     * @throws plcChassisException       
     * 
     * @return string of encoded data on success.       
     *                            
     */
  
    public function Encode()
        {    
        parent::Encode();

        $tmpResult = md5($this->CurPreparedData);   
        return $tmpResult;
        }
    
    /* Core methods ends here */
    }

?>