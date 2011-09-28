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
 * Class plcEncoderMD4 is a part of PHP framework - Chassis.   
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
 * Documents the plcEncoderMD4 class.
 * 
 * Following class is intended for simplification of encryption by md4 algorithm. This class is based on
 * class by DKameleon (http://dkameleon.com) from http://my-tools.net/md4php/ .
 *   
 * @subpackage plcEncoderMD4
 * @author Selihov Sergei Stanislavovich <red331@mail.ru>   
 */
 
require_once('Chassis/ErrorHandling/plcChassisException.php');
require_once('Chassis/Encription/plcEncoderAbstract.php');

class plcEncoderMD4 extends plcEncoderAbstract
    {  
    /**
     * @access protected
     * @var bool safe add mode switch 
     */	 
    
    protected $SafeAddMode = FALSE; 
    
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
     * @throws plcChassisException
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
     * Function that initializes current class.
     * 
     * Simple function that initializes current class.
     *        
     * @access public 
     * 
     * @throws plcChassisException       
     * 
     * @return bool TRUE on success.       
     *                            
     */        
        
    protected function Initialize()
        {
        $tmpResult = '';
        $tmpUserData = array();
          
        $tmpUserData = $this->CurData;
        $this->CurData = '12345678';
        
        $tmpResult = $this->Encode();
        if ($tmpResult == '012d73e0fab8d26e0f4d65e36077511e') {return TRUE;}

        $this->SafeAddMode = TRUE;   
        $tmpResult = $this->Encode();
        if ($tmpResult == '012d73e0fab8d26e0f4d65e36077511e') {return TRUE;}
     
        $this->CurData = $tmpUserData;  
        
        throw new plcChassisException('Fail to initialize class.', 8301, null , 'Can not initialize class.');           
        }
        
    /**
     * Utility function.
     *    
     * @access protected 
     * 
     * @param int
     * @param int
     *      
     * @return int       
     *                            
     */          

    protected function safe_add($usrX, $usrY) 
        {
        $tmpLSW = 0;
        $tmpMSW = 0;

        if ($this->SafeAddMode == FALSE) 
            {
            return ($usrX + $usrY) & 0xFFFFFFFF;
            }

        $tmpLSW = ($usrX & 0xFFFF) + ($usrY & 0xFFFF);
        $tmpMSW = ($usrX >> 16) + ($usrY >> 16) + ($tmpLSW >> 16);

	return ($tmpMSW << 16) | ($tmpLSW & 0xFFFF);
	}
        
    /**
     * Utility function.
     *    
     * @access protected 
     * 
     * @param int
     * @param int
     *      
     * @return int       
     *                            
     */         
        
    private function zeroFill($usrA, $usrB) 
        {
        $tmpZ = hexdec(80000000);
        
	if ($tmpZ & $usrA)
            {
            $usrA >>= 1;
            $usrA &= (~$tmpZ);
            $usrA |= 0x40000000;
            $usrA >>= ($usrB-1);
            } 
        else 
            {
            $usrA >>= $usrB;
            }
            
        return $usrA;
	}  
        
    /**
     * Utility function.
     *    
     * @access protected 
     * 
     * @param int
     * @param int
     *      
     * @return int       
     *                            
     */          
        
    protected function rol($num, $cnt) 
        {
        return ($num << $cnt) | ($this->zeroFill($num, (32 - $cnt)));
	}
        
    /**
     * Utility function.
     *    
     * @access protected 
     * 
     * @param int
     * @param int
     * @param int
     * @param int
     * @param int
     * @param int
     *      
     * @return int       
     *                            
     */         
        
    protected function cmn($usrQ, $usrA, $usrB, $usrX, $usrS, $usrT) 
        {
        return $this->safe_add($this->rol($this->safe_add($this->safe_add($usrA, $usrQ), $this->safe_add($usrX, $usrT)), $usrS), $usrB);
	} 
        
    /**
     * Utility function.
     *    
     * @access protected 
     * 
     * @param int
     * @param int
     * @param int
     * @param int
     * @param int
     * @param int
     *      
     * @return int       
     *                            
     */         
        
    protected function hhMD4($usrA, $usrB, $usrC, $usrD, $usrX, $usrS) 
        {
        return $this->cmn($usrB ^ $usrC ^ $usrD, $usrA, 0, $usrX, $usrS, 1859775393);
	}  
        
    /**
     * Utility function.
     *    
     * @access protected 
     * 
     * @param int
     * @param int
     * @param int
     * @param int
     * @param int
     * @param int
     *      
     * @return int       
     *                            
     */          
        
    protected function ggMD4($usrA, $usrB, $usrC, $usrD, $usrX, $usrS) 
        {
        return $this->cmn(($usrB & $usrC) | ($usrB & $usrD) | ($usrC & $usrD), $usrA, 0, $usrX, $usrS, 1518500249);
	} 
        
    /**
     * Utility function.
     *    
     * @access protected 
     * 
     * @param int
     * @param int
     * @param int
     * @param int
     * @param int
     * @param int
     *      
     * @return int       
     *                            
     */          
        
    protected function ffMD4($usrA, $usrB, $usrC, $usrD, $usrX, $usrS) 
        {
        return $this->cmn(($usrB & $usrC) | ((~$usrB) & $usrD), $usrA, 0, $usrX, $usrS, 0);
	}        
        
    /**
     * Utility function.
     *    
     * @access protected 
     *      
     * @return array       
     *                            
     */            
        
    protected function str2blks() 
        {
        $tmpStr = $this->CurPreparedData; 

        $tmpNBLK = 0;
        $tmpBLKS = array();
        
        $Counter1 = 0;
            
        $tmpNBLK = ((strlen($tmpStr) + 8) >> 6) + 1;
		
        for($Counter1 = 0; $Counter1 < $tmpNBLK * 16; $Counter1++) 
            {
            $tmpBLKS[$Counter1] = 0;
            }
        
        
        for($Counter1 = 0; $Counter1 < strlen($tmpStr); $Counter1++)
            {
            $tmpBLKS[$Counter1 >> 2] |= ord($tmpStr{$Counter1}) << (($Counter1 % 4) * 8);
            }
			
	$tmpBLKS[$Counter1 >> 2] |= 0x80 << (($Counter1 % 4) * 8);
	$tmpBLKS[$tmpNBLK * 16 - 2] = strlen($tmpStr) * 8;
	return $tmpBLKS;
	}        
    
    /**
     * Function that encodes data using md4 algorithm and returns the result.
     * 
     * Simple function that encodes data using md4 algorithm and returns the result.
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
        
        $tmpResult = '';      
        $Counter1 = 0;
        
        $tmpX = $this->str2blks();
        $tmpA =  1732584193;
        $tmpB = -271733879;
        $tmpC = -1732584194;
	$tmpD =  271733878;

        for($Counter1 = 0; $Counter1 < count($tmpX); $Counter1 += 16) 
            {
            $tmpOldA = $tmpA;
            $tmpOldB = $tmpB;
            $tmpOldC = $tmpC;
            $tmpOldD = $tmpD;

            $tmpA = $this->ffMD4($tmpA, $tmpB, $tmpC, $tmpD, $tmpX[$Counter1+ 0], 3 );
            $tmpD = $this->ffMD4($tmpD, $tmpA, $tmpB, $tmpC, $tmpX[$Counter1+ 1], 7 );
            $tmpC = $this->ffMD4($tmpC, $tmpD, $tmpA, $tmpB, $tmpX[$Counter1+ 2], 11);
            $tmpB = $this->ffMD4($tmpB, $tmpC, $tmpD, $tmpA, $tmpX[$Counter1+ 3], 19);
            $tmpA = $this->ffMD4($tmpA, $tmpB, $tmpC, $tmpD, $tmpX[$Counter1+ 4], 3 );
            $tmpD = $this->ffMD4($tmpD, $tmpA, $tmpB, $tmpC, $tmpX[$Counter1+ 5], 7 );
            $tmpC = $this->ffMD4($tmpC, $tmpD, $tmpA, $tmpB, $tmpX[$Counter1+ 6], 11);
            $tmpB = $this->ffMD4($tmpB, $tmpC, $tmpD, $tmpA, $tmpX[$Counter1+ 7], 19);
            $tmpA = $this->ffMD4($tmpA, $tmpB, $tmpC, $tmpD, $tmpX[$Counter1+ 8], 3 );
            $tmpD = $this->ffMD4($tmpD, $tmpA, $tmpB, $tmpC, $tmpX[$Counter1+ 9], 7 );
            $tmpC = $this->ffMD4($tmpC, $tmpD, $tmpA, $tmpB, $tmpX[$Counter1+10], 11);
            $tmpB = $this->ffMD4($tmpB, $tmpC, $tmpD, $tmpA, $tmpX[$Counter1+11], 19);
            $tmpA = $this->ffMD4($tmpA, $tmpB, $tmpC, $tmpD, $tmpX[$Counter1+12], 3 );
            $tmpD = $this->ffMD4($tmpD, $tmpA, $tmpB, $tmpC, $tmpX[$Counter1+13], 7 );
            $tmpC = $this->ffMD4($tmpC, $tmpD, $tmpA, $tmpB, $tmpX[$Counter1+14], 11);
            $tmpB = $this->ffMD4($tmpB, $tmpC, $tmpD, $tmpA, $tmpX[$Counter1+15], 19);

            $tmpA = $this->ggMD4($tmpA, $tmpB, $tmpC, $tmpD, $tmpX[$Counter1+ 0], 3 );
            $tmpD = $this->ggMD4($tmpD, $tmpA, $tmpB, $tmpC, $tmpX[$Counter1+ 4], 5 );
            $tmpC = $this->ggMD4($tmpC, $tmpD, $tmpA, $tmpB, $tmpX[$Counter1+ 8], 9 );
            $tmpB = $this->ggMD4($tmpB, $tmpC, $tmpD, $tmpA, $tmpX[$Counter1+12], 13);
            $tmpA = $this->ggMD4($tmpA, $tmpB, $tmpC, $tmpD, $tmpX[$Counter1+ 1], 3 );
            $tmpD = $this->ggMD4($tmpD, $tmpA, $tmpB, $tmpC, $tmpX[$Counter1+ 5], 5 );
            $tmpC = $this->ggMD4($tmpC, $tmpD, $tmpA, $tmpB, $tmpX[$Counter1+ 9], 9 );
            $tmpB = $this->ggMD4($tmpB, $tmpC, $tmpD, $tmpA, $tmpX[$Counter1+13], 13);
            $tmpA = $this->ggMD4($tmpA, $tmpB, $tmpC, $tmpD, $tmpX[$Counter1+ 2], 3 );
            $tmpD = $this->ggMD4($tmpD, $tmpA, $tmpB, $tmpC, $tmpX[$Counter1+ 6], 5 );
            $tmpC = $this->ggMD4($tmpC, $tmpD, $tmpA, $tmpB, $tmpX[$Counter1+10], 9 );
            $tmpB = $this->ggMD4($tmpB, $tmpC, $tmpD, $tmpA, $tmpX[$Counter1+14], 13);
            $tmpA = $this->ggMD4($tmpA, $tmpB, $tmpC, $tmpD, $tmpX[$Counter1+ 3], 3 );
            $tmpD = $this->ggMD4($tmpD, $tmpA, $tmpB, $tmpC, $tmpX[$Counter1+ 7], 5 );
            $tmpC = $this->ggMD4($tmpC, $tmpD, $tmpA, $tmpB, $tmpX[$Counter1+11], 9 );
            $tmpB = $this->ggMD4($tmpB, $tmpC, $tmpD, $tmpA, $tmpX[$Counter1+15], 13);

            $tmpA = $this->hhMD4($tmpA, $tmpB, $tmpC, $tmpD, $tmpX[$Counter1+ 0], 3 );
            $tmpD = $this->hhMD4($tmpD, $tmpA, $tmpB, $tmpC, $tmpX[$Counter1+ 8], 9 );
            $tmpC = $this->hhMD4($tmpC, $tmpD, $tmpA, $tmpB, $tmpX[$Counter1+ 4], 11);
            $tmpB = $this->hhMD4($tmpB, $tmpC, $tmpD, $tmpA, $tmpX[$Counter1+12], 15);
            $tmpA = $this->hhMD4($tmpA, $tmpB, $tmpC, $tmpD, $tmpX[$Counter1+ 2], 3 );
            $tmpD = $this->hhMD4($tmpD, $tmpA, $tmpB, $tmpC, $tmpX[$Counter1+10], 9 );
            $tmpC = $this->hhMD4($tmpC, $tmpD, $tmpA, $tmpB, $tmpX[$Counter1+ 6], 11);
            $tmpB = $this->hhMD4($tmpB, $tmpC, $tmpD, $tmpA, $tmpX[$Counter1+14], 15);
            $tmpA = $this->hhMD4($tmpA, $tmpB, $tmpC, $tmpD, $tmpX[$Counter1+ 1], 3 );
            $tmpD = $this->hhMD4($tmpD, $tmpA, $tmpB, $tmpC, $tmpX[$Counter1+ 9], 9 );
            $tmpC = $this->hhMD4($tmpC, $tmpD, $tmpA, $tmpB, $tmpX[$Counter1+ 5], 11);
            $tmpB = $this->hhMD4($tmpB, $tmpC, $tmpD, $tmpA, $tmpX[$Counter1+13], 15);
            $tmpA = $this->hhMD4($tmpA, $tmpB, $tmpC, $tmpD, $tmpX[$Counter1+ 3], 3 );
            $tmpD = $this->hhMD4($tmpD, $tmpA, $tmpB, $tmpC, $tmpX[$Counter1+11], 9 );
            $tmpC = $this->hhMD4($tmpC, $tmpD, $tmpA, $tmpB, $tmpX[$Counter1+ 7], 11);
            $tmpB = $this->hhMD4($tmpB, $tmpC, $tmpD, $tmpA, $tmpX[$Counter1+15], 15);

            $tmpA = $this->safe_add($tmpA, $tmpOldA);
            $tmpB = $this->safe_add($tmpB, $tmpOldB);
            $tmpC = $this->safe_add($tmpC, $tmpOldC);
            $tmpD = $this->safe_add($tmpD, $tmpOldD);
            }
		
        $tmpX = pack('V4', $tmpA, $tmpB, $tmpC, $tmpD);
	return bin2hex($tmpX);        
        }
    
    /* Core methods ends here */  
    }

?>
