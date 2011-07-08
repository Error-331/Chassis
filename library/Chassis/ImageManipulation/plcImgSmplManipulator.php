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
 * Class plcImgSmplManipulator is a part of PHP framework - Chassis.   
 * 
 * @package     Chassis
 * @author      Selihov Sergei Stanislavovich <red331@mail.ru> 
 * @copyright   Copyright (c) 2010-2011 Selihov Sergei Stanislavovich.
 * @license     http://www.gnu.org/licenses/gpl.html  GNU GENERAL PUBLIC LICENSE (Version 3)
 *    
 */
 
/**
 * Classes for work with images.
 *  
 * @subpackage ImageManipulation
 * @author Selihov Sergei Stanislavovich <red331@mail.ru>   
 */
 
/**
 * Documents the plcImgSmplManipulator class. 
 *   
 * @subpackage plcImgSmplManipulator
 * @author Selihov Sergei Stanislavovich <red331@mail.ru>   
 */

require_once('Chassis/ErrorHandling/plcChassisException.php');

class plcImgSmplManipulator
	{	
	protected $PNGFilters;
	
	protected $PNGQuality;
	protected $JPGQuality;

	function __construct()
		{
		$PNGFilters = array();
		
		$this->PNGQuality = 7;
		$this->JPGQuality = 75;
		}

  function __destruct() 
		{
   	}
   	
  /* Utility functions starts here */
  
  public function EmptyPNGFilters()
    {
    $this->PNGFilters = array();
    }
  
  public function IsExist($usrFileName)
    {    
    if (file_exists($usrFileName) === FALSE)
      {      
      throw new plcChassisException('File does not exist.', 5101, null , 'File "'.$usrFileName.'" does not exist.');
      }
    else
      {
      return TRUE;
      }
    }
  
  public function IsReadable($usrFileName)
    {   
    $this->IsExist($usrFileName);
        
    if(is_readable($usrFileName) === FALSE)
      {
      throw new plcChassisException('File is not readable.', 5102, null , 'File "'.$usrFileName.'" is not readable.');
      }
    else
      {
      return TRUE;
      }
    }
    
  public function IsWritable($usrFileName)
    {  
    $tmpResult = $this->IsExist($usrFileName);
     
    if(is_writable($usrFileName) !== FALSE)
      {      
      throw new plcChassisException('File is not writable.', 5103, null , 'File "'.$usrFileName.'" is not writable.');
      }
    else
      {
      return TRUE;
      }
    }
    
  public function ResizeImage($usrFileName, $usrNewFileName, $usrWidth, $usrHeight)
    {
    $tmpResult = '';
    $tmpImageRes = '';
    $tmpImageCopyRes = '';
    
    $tmpOldWidth = '';
    $tmpOldHeight = '';
    
    $tmpResult = $this->IsReadable($usrFileName);
                
    if ((isset($usrWidth) === FALSE || empty($usrWidth) !== FALSE))
      {
      throw new plcChassisException('User does not specified width.', 5104, null , 'Width parametre is not specified or empty.');    
      }
      
    if ((isset($usrHeight) === FALSE || empty($usrHeight) !== FALSE))
      {
      throw new plcChassisException('User does not specified height.', 5105, null , 'Height parametre is not specified or empty.');      
      }
      
    $usrWidth = intval($usrWidth);
    $usrHeight = intval($usrHeight); 
     
    $tmpImageRes = $this->GetImgCopy($usrFileName); 
    
    $tmpOldWidth = imagesx($tmpImageRes);
    $tmpOldHeight = imagesy($tmpImageRes);
         
    $tmpImageCopyRes = imagecreatetruecolor($usrWidth, $usrHeight);
    
    if ($tmpImageCopyRes === FALSE)
      {
      imagedestroy($tmpImageRes);
            
      throw new plcChassisException('Can not create image resource.', 5108, null , 'Can not create image resource for filename: '.$usrFileName.' .');
      }
      
    $tmpResult = imagecopyresampled($tmpImageCopyRes, $tmpImageRes, 0, 0, 0, 0, $usrWidth, $usrHeight, $tmpOldWidth, $tmpOldHeight);
    
    if ($tmpResult === FALSE)
      {
      imagedestroy($tmpImageRes);
      imagedestroy($tmpImageCopyRes);
       
      throw new plcChassisException('Can not create resized image copy.', 5109, null , 'Can not create resized image copy from '.$usrFileName.' to '.$usrNewFileName.' .');
      } 
    else
      {
      try
        {
        $this->WriteFileExt($tmpImageCopyRes, $usrNewFileName);
        }
      catch(plcChassisException $usrError)
        {
        imagedestroy($tmpImageRes);
        imagedestroy($tmpImageCopyRes);
        
        throw $usrError;
        }
      
      imagedestroy($tmpImageRes);
      imagedestroy($tmpImageCopyRes);
      
      return TRUE;
      }
    }      
  
  public function ResizeImageProportion($usrFileName, $usrNewFileName, $usrMaxWidth, $usrMaxHeight)
    {
    $tmpResult = '';
    $tmpImageRes = '';
    $tmpImageCopyRes = '';
    
    $tmpOldWidth = 1;
    $tmpOldHeight = 1;
    
    $tmpNewWidth = 1;
    $tmpNewHeight = 1;
    
    $tmpXScale = 1;
    $tmpYScale = 1;
    
    $tmpResult = $this->IsReadable($usrFileName);    
    
    if ((isset($usrMaxWidth) === FALSE || empty($usrMaxWidth) !== FALSE))
      {
      throw new plcChassisException('User does not specified maximum width.', 5115, null , 'Maximum width parametre is not specified or empty.');    
      }
      
    if ((isset($usrMaxHeight) === FALSE || empty($usrMaxHeight) !== FALSE))
      {
      throw new plcChassisException('User does not specified maximum height.', 5116, null , 'Maximum height parametre is not specified or empty.');      
      }    
    
    $usrMaxWidth = intval($usrMaxWidth);
    $usrMaxHeight = intval($usrMaxHeight); 
     
    $tmpImageRes = $this->GetImgCopy($usrFileName); 
    
    $tmpOldWidth = imagesx($tmpImageRes);
    $tmpOldHeight = imagesy($tmpImageRes);
      
    /* Resize proportion calculation starts here */
            
    $tmpXScale = $tmpOldWidth/$usrMaxWidth;
    $tmpYScale = $tmpOldHeight/$usrMaxHeight;    
    
    if ($tmpYScale > $tmpXScale)
      {
      $tmpNewWidth = round($tmpOldWidth * (1/$tmpYScale));
      $tmpNewHeight = round($tmpOldHeight * (1/$tmpYScale));
      }
    else 
      {
      $tmpNewWidth = round($tmpOldWidth * (1/$tmpXScale));
      $tmpNewHeight = round($tmpOldHeight * (1/$tmpXScale));
      }    
    
    /* Resize proportion calculation ends here */   
    
    $tmpImageCopyRes = imagecreatetruecolor($tmpNewWidth, $tmpNewHeight);
    
    if ($tmpImageCopyRes === FALSE)
      {
      imagedestroy($tmpImageRes);
            
      throw new plcChassisException('Can not create image resource.', 5108, null , 'Can not create image resource for filename: '.$usrFileName.' .');
      }    
    
    $tmpResult = imagecopyresampled($tmpImageCopyRes, $tmpImageRes, 0, 0, 0, 0, $tmpNewWidth, $tmpNewHeight, $tmpOldWidth, $tmpOldHeight);
    
    if ($tmpResult === FALSE)
      {
      imagedestroy($tmpImageRes);
      imagedestroy($tmpImageCopyRes);
       
      throw new plcChassisException('Can not create resized image copy.', 5109, null , 'Can not create resized image copy from '.$usrFileName.' to '.$usrNewFileName.' .');
      } 
    else
      {
      try
        {
        $this->WriteFileExt($tmpImageCopyRes, $usrNewFileName);
        }
      catch(plcChassisException $usrError)
        {
        imagedestroy($tmpImageRes);
        imagedestroy($tmpImageCopyRes);
        
        throw $usrError;
        }
      
      imagedestroy($tmpImageRes);
      imagedestroy($tmpImageCopyRes);
      
      return TRUE;
      }        
    }
  
  /* Utility functions ends here */
  
  /* Write functions starts here */ 
    
  protected function WriteFilePNG($usrRes, $usrFileName)
    {
    $tmpResult = '';
    
    $tmpResult = @imagepng($usrRes, $usrFileName, $this->GetPNGQuality(), $this->GetPNGFilters());
    
    if ($tmpResult === FALSE)
      {      
      throw new plcChassisException('Can not write PNG file.', 5112, null , 'Can not create PNG file - '.$usrFileName.' .');
      }
    else
      {
      return TRUE;
      }
    }   
  
  protected function WriteFileGIF($usrRes, $usrFileName)
    {
    $tmpResult = '';
    
    $tmpResult = @imagegif($usrRes, $usrFileName);
    
    if ($tmpResult === FALSE)
      {      
      throw new plcChassisException('Can not write GIF file.', 5111, null , 'Can not create GIF file - '.$usrFileName.' .');
      }
    else
      {
      return TRUE;
      }
    } 
  
  protected function WriteFileJPG($usrRes, $usrFileName)
    {    
    $tmpResult = @imagejpeg($usrRes, $usrFileName, $this->GetJPGQuality());
    
    if ($tmpResult === FALSE)
      {      
      throw new plcChassisException('Can not write JPG file.', 5110, null , 'Can not create JPG file - '.$usrFileName.' .');
      }
    else
      {
      return TRUE;
      }
    } 
  
  protected function WriteFileExt($usrRes, $usrFileName)
    {
    $tmpResult = '';
    $tmpImgExtension = '';
        
    $tmpImgExtension = $this->GetFileExtension($usrFileName);
          
    switch($tmpImgExtension)
      {
      case 'jpg':
      
      $tmpResult = $this->WriteFileJPG($usrRes, $usrFileName);
      break;
      
      case 'jpeg':
      
      $tmpResult = $this->WriteFileJPG($usrRes, $usrFileName);
      break;
      
      case 'gif':
      
      $tmpResult = $this->WriteFileGIF($usrRes, $usrFileName);
      break;
      
      case 'png':
      
      $tmpResult = $this->WriteFilePNG($usrRes, $usrFileName);
      break;
      
      default:
            
      throw new plcChassisException('Unrecognized file extension', 5107, null , 'Unrecognized file extension - "'.$tmpImgExtension.'" .');
      break;
      }
      
    return $tmpResult;  
    }
  
  /* Write functions ends here */  
  
  /* Get functions starts here */
  
  public function GetPNGFilters()
    {
    $tmpFilters = 0;
    $Counter1 = 0;
    
    if (count($this->PNGFilters) > 0)
      {
      for ($Counter1 = 0; $Counter1 < count($this->PNGFilters); $Counter1++)
        {
        $tmpFilters = $tmpFilters & $this->PNGFilters[$Counter1];
        }
      }
    
    return $tmpFilters;
    }
  
  public function GetPNGQuality()
    {
    return $this->PNGQuality;
    }
  
  public function GetJPGQuality()
    {
    return $this->JPGQuality;
    }
  
  public function GetFileExtension($usrFileName)
    {
    $tmpResult = '';
    $tmpImageExtension = '';   

    $tmpImageExtension = strrpos($usrFileName, '.');
    
    if ($tmpImageExtension === FALSE)
      {
      throw new plcChassisException('Image file name has no extension.', 5106, null , 'Image file name "'.$usrFileName.'" have no extension.'); 
      }  
    
    $tmpImageExtension = substr($usrFileName, $tmpImageExtension+1);  
    $tmpImageExtension = strtolower($tmpImageExtension);
    
    return $tmpImageExtension;
    }
    
  public function GetImgCopy($usrFileName)
    {
    $tmpResult = '';
    $tmpImgExtension = '';
    
    $tmpResult = $this->IsReadable($usrFileName);  
    $tmpImgExtension = $this->GetFileExtension($usrFileName);
          
    switch($tmpImgExtension)
      {
      case 'jpg':
      
      $tmpResult = imagecreatefromjpeg($usrFileName);
      break;
      
      case 'jpeg':
      
      $tmpResult = imagecreatefromjpeg($usrFileName);
      break;
      
      case 'gif':
      
      $tmpResult = imagecreatefromgif($usrFileName);
      break;
      
      case 'png':
      
      $tmpResult = imagecreatefrompng($usrFileName);
      break;
      
      default:
            
      throw new plcChassisException('Unrecognized file extension.', 5107, null , 'Unrecognized file extension - "'.$tmpImgExtension.'" .');      
      break;
      }
      
    if ($tmpResult === FALSE)
      {      
      throw new plcChassisException('Can not create image resource.', 5108, null , 'Can not create image resource for filename: '.$usrFileName.' .'); 
      }
      
    return $tmpResult;   
    }
  
  /* Get functions ends here */
  
  /* Set functions starts here */
  
  public function SetPNGQuality($usrQuality)
    {
    if ($usrQuality < 0 || $usrQuality > 9)
      {
      throw new plcChassisException('Invalid PNG quality value.', 5113, null , 'Invalid PNG quality value - '.$usrQuality.' .'); 
      }
    else
      {
      $this->PNGQuality = $usrQuality; 
      }
      
    return TRUE;
    }
    
  public function SetJPGQuality($usrQuality)
    {
    if ($usrQuality < 0 || $usrQuality > 100)
      {      
      throw new plcChassisException('Invalid JPG quality value.', 5114, null , 'Invalid JPG quality value - '.$usrQuality.' .'); 
      }
    else
      {
      $this->JPGQuality = $usrQuality; 
      }
      
    return TRUE;
    }
   
  /* Set functions ends here */
	}

?>