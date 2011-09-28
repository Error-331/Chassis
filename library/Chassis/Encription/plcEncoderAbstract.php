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
 * Class plcEncoderAbstract is a part of PHP framework - Chassis.   
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
 * Documents the plcEncoderAbstract class.
 * 
 * Following class is a base abstract class for all encoding classes.
 *   
 * @subpackage plcEncoderAbstract
 * @author Selihov Sergei Stanislavovich <red331@mail.ru>   
 */

require_once('Chassis/ErrorHandling/plcChassisException.php');

abstract class plcEncoderAbstract
    {
    /**
     * @access protected
     * @var array data to be used in encoding process 
     */	  
  
    protected $CurData = array(); 
  
    /**
     * @access protected
     * @var string that will be used in encoding process and is composed of user data and various random info 
     */	  
  
    protected $CurPreparedData = ''; 
  
    /**
     * @access protected
     * @var bool indicates whether to use or not to use random number in encoding process
     */	  
  
    protected $UseRndNum = FALSE;
  
    /**
     * @access protected
     * @var bool indicates whether to use or not to use timestamp in encoding process
     */	  
  
    protected $UseTime = FALSE;  
  
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
        $this->SetData($usrData);
        $this->SetUseRndNum($usrUseRndNum);
        $this->SetUseTime($useTime);
        
        $this->PrepareData();
        }
        
    public function __destruct()
        {        
        }
    
    /**
     * Function that prepares data for encoding process.
     * 
     * This function add various random data to the user specified data and converts it to a string for feature use in 
     * encoding process. 
     *        
     * @access protected 
     * 
     * @throws plcChassisException  
     * 
     * @return bool TRUE on succesfull preparation.       
     *  
     * @see plcEncoderAbstract::SetUseRndNum()
     * @see plcEncoderAbstract::SetUseTime()     
     *                                   
     */ 
    
    protected function PrepareData()
        {    
        if (empty($this->CurData) === TRUE)
            {
            throw new plcChassisException('User data is not set.', 8101, null , 'Can not prepare user data without actual user data.');
            }
            
        $this->CurPreparedData = '';    
      
        foreach ($this->CurData as $DataPart)
            {
            $this->CurPreparedData .= $DataPart;
            }
      
        if ($this->UseRndNum === TRUE)
            {
            $this->CurPreparedData .= rand(1, 100);
            }
      
        if ($this->UseTime === TRUE)
            {
            $this->CurPreparedData .= time();
            }
      
        return TRUE;
        } 
    
    /**
     * Function that encodes prepared user data and returns the result.
     * 
     * Simple function that encodes prepared user data and returns the result. Note that all subclasses 
     * must redefine this method (in which first call must be made to the parent method).
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
        $this->PrepareData();
    
        if (empty($this->CurPreparedData) === TRUE)
            {
            throw new plcChassisException('Prepared data is absent.', 8102, null ,'Data for encoding process is not yet set.');
            }
        }
    
    /* Core methods ends here */
  
    /* Get methods starts here */
  
    /**
     * Function that returns data that will be used in encoding process.
     * 
     * Simple function that returns data(previously set by user) that will be used in encoding process.
     *        
     * @access public 
     * 
     * @return mixed user data that will be used in encoding process.       
     *                            
     */ 

    public function GetData()
        {
        return $this->CurData;
        }
    
    /**
     * Function that returns prepared data that will be used in encoding process.
     * 
     * Simple function that returns data(previously set by user and with additional random info) that will be used in 
     * encoding process.
     *        
     * @access public 
     * 
     * @throws plcChassisException 
     * 
     * @return string prepared user data that will be encoded.       
     *  
     * @see plcEncoderAbstract::PrepareData() 
     *                                   
     */ 

    public function GetPreparedData()
        {
        $this->PrepareData();
        return $this->CurPreparedData;
        }
  
    /* Get methods ends here */
  
    /* Set methods starts here */
  
    /**
     * Function that sets data that will be future used in encoding process.
     * 
     * Simple function that stores data that will be future used in encoding process.
     *        
     * @access public 
     * 
     * @param mixed user data that will be encoded.    
     *                            
     */ 
  
    public function SetData($usrDataArray)
        {
        if (isset($usrDataArray) === TRUE)
            {
            if (is_array($usrDataArray) === FALSE)
                {
                $this->CurData = array();
                $this->CurData[] =$usrDataArray;
                }
            else
                {
                $this->CurData = $usrDataArray;
                }                         
            }  
        }
    
    /**
     * Function that sets flag that indicates whether random numbers will be used in preparation of user data.
     * 
     * Function that sets flag that indicates whether random numbers will be used in preparation of user data.
     *        
     * @access public 
     * 
     * @param bool sets flag to TRUE or FALSE .
     * 
     * @see plcEncoderAbstract::PrepareData()           
     *                            
     */ 
    
    public function SetUseRndNum($usrState)
        {
        if ($usrState === TRUE) {$this->UseRndNum = TRUE;}
        if ($usrState === FALSE) {$this->UseRndNum = FALSE;}
        }
    
    /**
     * Function that sets flag that indicates whether time stamp will be used in preparation of user data.
     * 
     * Function that sets flag that indicates whether time stamp will be used in preparation of user data.
     *        
     * @access public 
     * 
     * @param bool sets flag to TRUE or FALSE .
     * 
     * @see plcEncoderAbstract::PrepareData()           
     *                            
     */ 
    
    public function SetUseTime($usrState)
        {
        if ($usrState === TRUE) {$this->UseTime = TRUE;}
        if ($usrState === FALSE) {$this->UseTime = FALSE;}        
        }
  
    /* Set methods ends here */    
    }

?>
