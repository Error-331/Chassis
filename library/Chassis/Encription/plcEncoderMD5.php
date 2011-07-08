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

class plcEncoderMD5
  {  
  /**
   * @access protected
   * @var array data to be used in encoding process 
   */	  
  
  protected $CurData; 
  
  /**
   * @access protected
   * @var string that will be used in encoding process and is composed of user data and various random info 
   */	  
  
  protected $CurPreparedData; 
  
  
  /**
   * @access protected
   * @var bool indicates whether to use or not to use random number in encoding process
   */	  
  
  protected $UseRndNum;
  
  /**
   * @access protected
   * @var bool indicates whether to use or not to use timestamp in encoding process
   */	  
  
  protected $UseTime;  
  
  /* Core functions starts here */
  
  public function __construct()
    {
    $this->CurData = FALSE;
    $this->CurPreparedData = FALSE;
    
    $this->UseRndNum = FALSE;
    $this->UseTime = FALSE;
    }
    
  /**
   * Function that prepares data for encoding process.
   * 
   * This function add various random data to the user specified data and converts it to a string for feature use in 
   * encoding process. 
   *        
   * @access protected 
   * 
   * @return bool TRUE on succesfull preparation and FALSE on fail.       
   *  
   * @see plcEncoderMD5::SetUseRndNum()
   * @see plcEncoderMD5::SetUseTime()     
   *                                   
   */ 
    
  protected function PrepareData()
    {    
    if (empty($this->CurData) === TRUE)
      {
      return FALSE;
      }
      
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
   * Function that encodes data using md5 algorithm and returns the result.
   * 
   * Simple function that encodes data using md5 algorithm and returns the result.
   *        
   * @access public 
   * 
   * @throws plcChassisException       
   * 
   * @return string string of encoded data on success.       
   *                            
   */
  
  public function Encode()
    {    
    $tmpResult = '';
    
    if ($this->CurPreparedData === FALSE)
      {
      throw new plcChassisException('Prepared data is absent.', 8101, null ,'Data for encoding process is not yet set.');
      }
     
    $tmpResult = md5($this->CurPreparedData);
    
    return $tmpResult;
    }
    
  /* Core functions ends here */
  
  /* Get functions starts here */
  
  /**
   * Function that returns data that will be used in encoding process.
   * 
   * Simple function that returns data(previously set by user) that will be used in encoding process.
   *        
   * @access public 
   * 
   * @return array|bool array of data was already been set and FALSE if it was not yet set.       
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
   * @return string|bool string of data if it was previously set and FALSE if it was not yet set.       
   *  
   * @see plcEncoderMD5::PrepareData() 
   *                                   
   */ 

  public function GetPreparedData()
    {
    return $this->CurPreparedData;
    }
  
  /* Get functions ends here */
  
  /* Set functions starts here */
  
  /**
   * Function that sets data that will be future used in encoding process.
   * 
   * Simple function that stores data that will be future used in encoding process.
   *        
   * @access public 
   * 
   * @param array plain array with user data.    
   *                            
   */ 
  
  public function SetData($usrDataArray)
    {
    $this->CurData = $usrDataArray;
    $this->PrepareData();
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
   * @see plcEncoderMD5::PrepareData()           
   *                            
   */ 
    
  public function SetUseRndNum($usrState)
    {
    $this->UseRndNum = $usrState;
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
   * @see plcEncoderMD5::PrepareData()           
   *                            
   */ 
    
  public function SetUseTime($usrState)
    {
    $this->UseTime = $usrState;
    }
  
  /* Set functions ends here */
  }

?>