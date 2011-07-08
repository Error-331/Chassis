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
 * Class plcXMLSitemapManipulator is a part of PHP framework - Chassis.   
 * 
 * @package     Chassis
 * @author      Selihov Sergei Stanislavovich <red331@mail.ru> 
 * @copyright   Copyright (c) 2010-2011 Selihov Sergei Stanislavovich.
 * @license     http://www.gnu.org/licenses/gpl.html  GNU GENERAL PUBLIC LICENSE (Version 3)
 *    
 */
 
/**
 * Classes for work with sitemap files.
 *  
 * @subpackage SitemapManipulation
 * @author Selihov Sergei Stanislavovich <red331@mail.ru>   
 */
 
/**
 * Documents the plcXMLSitemapManipulator class.
 *   
 * @subpackage plcAbstractXMLSitemapManipulator
 * @author Selihov Sergei Stanislavovich <red331@mail.ru>   
 */
                                                           
require_once('Chassis/ErrorHandling/plcChassisException.php');
require_once('Chassis/FileManipulation/plcFileSmplManipulator.php');
require_once('Chassis/ErrorHandling/plcError.php');
require_once('Chassis/ErrorHandling/plcDebugger.php');
require_once('Chassis/ErrorHandling/plcAbstractXMLSitemapManipulator.php');

class plcXMLSitemapManipulator extends plcAbstractXMLSitemapManipulator
  {
  protected $ErrorManager;
  protected $FileManager;
  
  protected $XMLDocument;
  protected $XMLDocumentURLS;
  protected $XMLDocumentImages;
  
  protected $URLCount;
  protected $URLImagesCount;
  
  protected $URLIndex;
  protected $URLImageIndex;
  
  protected $DefSitemapXMLNS;
  protected $DefImageXMLNS;
  
	function __construct()
		{		
		$this->ErrorManager = plcErrorManager::GetInstance();
		$this->FileManager = new plcFileSmplManipulator();
		
    $this->XMLDocument = '';
    $this->XMLDocumentURLS = '';
    $this->XMLDocumentImages = '';
    
    $this->URLCount = 0;
    $this->URLImagesCount = 0;
    
		$this->URLIndex = -1;
		$this->URLImageIndex = -1;
		
		$this->DefSitemapXMLNS = 'http://www.sitemaps.org/schemas/sitemap/0.9';
    $this->DefImageXMLNS = 'http://www.google.com/schemas/sitemap-image/1.1';
		}
		
  function __destruct() 
		{
   	}
   	
  /* File manipulation functions starts here */
  
  public function ResetImagePointer()
    {
    $this->URLImageIndex = 0;
    }
  
  public function ResetSitemapManipulator()
    {
    $this->XMLDocument = '';
    $this->XMLDocumentURLS = '';
    $this->XMLDocumentImages = '';
    
    $this->URLCount = 0;
    $this->URLImagesCount = 0;
    
		$this->URLIndex = 0;
		$this->URLImageIndex = 0;
    }
  
  public function ReadXMLFile($usrXMLFile)
    {
    $tmpResult = '';
    
    $tmpXMLDocument = '';
    $tmpURL = '';
    
    $this->ResetSitemapManipulator();
    
    $tmpResult = $this->FileManager->IsReadable($usrXMLFile);
    
    if ($this->ErrorManager->IsError($tmpResult) || $tmpResult === FALSE)
      {
      return $tmpResult;
      }   
    
    $tmpXMLDocument = new DOMDocument();  
    $tmpResult = $tmpXMLDocument->load($usrXMLFile);

    if ($tmpResult === FALSE)
      {
      $tmpResult = $this->ErrorManager->RaiseError(7101, "Can not read specified XML file.", "File ".$usrXMLFile." can not be read.");
      return $tmpResult;
      }  

    $this->XMLDocument = $tmpXMLDocument;
    $this->XMLDocumentURLS = $this->XMLDocument->getElementsByTagName("url");  
     
    $this->URLCount =  $this->XMLDocumentURLS->length;
       
    if ($this->URLCount != 0)
      {
      $tmpURL = $this->XMLDocumentURLS->item($this->URLIndex);
      
      $this->XMLDocumentImages = $tmpURL->getElementsByTagNameNS($this->DefImageXMLNS, "image");
      $this->URLImagesCount = $this->XMLDocumentImages->length;
      }
    
    return TRUE;
    }
    
  public function SaveToXMLFile($usrXMLFile)
    {
    $tmpResult = '';
    
    if (isset($usrXMLFile) === FALSE || empty($usrXMLFile) === TRUE)
      {
      $tmpResult = $this->ErrorManager->RaiseError(7104, "Specified XML file is empty - cannot save.", "User specified XML file path is either empty or invalid.");
      return $tmpResult;
      }               
      
    if (isset($this->XMLDocument) === FALSE || empty($this->XMLDocument) === TRUE)
      {
      $tmpResult = $this->ErrorManager->RaiseError(7105, "XML document is not exist.", "No XML document loaded or created yet.");
      return $tmpResult;
      }
      
    $tmpResult = $this->XMLDocument->save($usrXMLFile); 
    
    if ($tmpResult === FALSE)
      {
      $tmpResult = $this->ErrorManager->RaiseError(7106, "Error occured during saving XML file.", "Can not save contents to XML file - ".$usrXMLFile);
      return $tmpResult;
      }  
    }
  
  /* File manipulation functions ends here */
  
  /* Conditional functions starts here */
  
  public function CurHasImages()
    {
    if($this->URLImagesCount == 0)
      {
      return FALSE;
      }
    else
      {
      return TRUE;
      }
    }
  
  /* Conditional functions ends here */
  
  /* Add functions starts here */
  
  public function AddImageToURL($usrImgLoc, $usrImgCaption = '', $usrImgGEOLocation = '', $usrImgTitle = '', $usrImgLicense = '')
    {
    $tmpResult = '';
    
    $tmpUrlNode = $this->XMLDocumentURLS->item($this->URLIndex);

    $tmpImgNode = '';
    $tmpImgLocNode = '';
    $tmpImgCaptionNode = '';  
    $tmpImgGEOLocationNode = '';
    $tmpImgTitleNode = '';
    $tmpImgLicenseNode = '';
        
    if (empty($this->XMLDocument) === TRUE)
      {
      $tmpResult = $this->ErrorManager->RaiseError(7102, "XML sitemap file contents is empty.", "XML sitemap file is not specefied and not readed yet.");
      return $tmpResult;
      }
    
    if ($this->URLIndex >= $this->URLCount)
      {
      return FALSE;
      }
    else
      {
      if (isset($usrImgLoc) === FALSE || empty($usrImgLoc) === TRUE)
        {
        $tmpResult = $this->ErrorManager->RaiseError(7103, "Loc value for image:image tag is empty.", "XML sitemap can not add image tag because Loc value for it is empty.");
        return $tmpResult;
        }
  
      $tmpImgNode = $this->XMLDocument->createElementNS($this->DefImageXMLNS, 'image:image');
      $tmpImgLocNode = $this->XMLDocument->createElementNS($this->DefImageXMLNS, 'image:loc', $usrImgLoc);
      $tmpImgNode->appendChild($tmpImgLocNode);
            
      if (empty($usrImgCaption) !== TRUE)
        {
        $tmpImgCaptionNode = $this->XMLDocument->createElementNS($this->DefImageXMLNS, 'image:caption', $usrImgCaption);
        $tmpImgNode->appendChild($tmpImgCaptionNode);
        }
        
      if (empty($usrImgGEOLocation) !== TRUE)
        {
        $tmpImgGEOLocationNode = $this->XMLDocument->createElementNS($this->DefImageXMLNS, 'image:geo_location', $usrImgGEOLocation);
        $tmpImgNode->appendChild($tmpImgGEOLocationNode);
        }
        
      if (empty($usrImgTitle) !== TRUE)
        {
        $tmpImgTitleNode = $this->XMLDocument->createElementNS($this->DefImageXMLNS, 'image:title', $usrImgTitle);
        $tmpImgNode->appendChild($tmpImgTitleNode);
        }
        
      if (empty($usrImgLicense) !== TRUE)
        {
        $tmpImgLicenseNode = $this->XMLDocument->createElementNS($this->DefImageXMLNS, 'image:license', $usrImgLicense);
        $tmpImgNode->appendChild($tmpImgLicenseNode);
        }

      $tmpUrlNode->appendChild($tmpImgNode);
      }
    }
  
  /* Add functions ends here */
  
  /* Get functions starts here */
   
  protected function GetNodeByName($usrName, $usrNodeList)
    {
    $tmpResult = '';
    
    $tmpNodeListLength = $usrNodeList->length;
    $tmpNode = '';
    
    $Counter1 = 0;
    
    if ($tmpNodeListLength == 0)
      {
      return FALSE;
      }
      
    for($Counter1 = 0; $Counter1 < $tmpNodeListLength; $Counter1++)
      {
      $tmpNode = $usrNodeList->item($Counter1);
      
      if (strtolower($tmpNode->nodeName) == $usrName)
        {
        return $tmpNode;
        }
      }
      
    return FALSE;
    }
                        
  public function GetNextURLImage()
    {
    $tmpResult = '';
    $tmpImage = '';
    
    $this->URLImageIndex += 1;  
        
    if (empty($this->XMLDocument) === TRUE)
      {
      $tmpResult = $this->ErrorManager->RaiseError(7102, "XML sitemap file contents is empty.", "XML sitemap file is not specefied and not readed yet.");
      return $tmpResult;
      }
      
    if ($this->URLImageIndex == 0 && $this->URLImagesCount == 0)
      {
      return FALSE;
      }
    
    if ($this->URLImageIndex >= $this->URLImagesCount)
      {
      return FALSE;
      }
    else
      {
      $tmpImage = $this->XMLDocumentImages->item($this->URLImageIndex);
      return $tmpImage;
      }
    }
  
  public function GetNextURL()
    {
    $tmpResult = '';
    $tmpURL = '';
    
    $this->URLImagesCount = 0;
		$this->URLImageIndex = 0;
		
		$this->URLIndex += 1;  
    
    if (empty($this->XMLDocument) === TRUE)
      {
      $tmpResult = $this->ErrorManager->RaiseError(7102, "XML sitemap file contents is empty.", "XML sitemap file is not specefied and not readed yet.");
      return $tmpResult;  
      }
      
    if ($this->URLIndex == 0 && $this->URLCount == 0)
      {
      return FALSE;
      }

    if ($this->URLIndex >= $this->URLCount)
      {
      return FALSE;
      }
    else
      {
      $tmpURL = $this->XMLDocumentURLS->item($this->URLIndex);
      
      $this->XMLDocumentImages = $tmpURL->getElementsByTagNameNS($this->DefImageXMLNS, "image");
      $this->URLImagesCount = $this->XMLDocumentImages->length; 

      return $tmpURL;
      }
    }
    
  public function GetCurURLImageLOCValue()
    {
    $tmpResult = '';
    $tmpImage = '';
    $tmpNode = '';
    
    if (empty($this->XMLDocument) === TRUE)
      {
      $tmpResult = $this->ErrorManager->RaiseError(7102, "XML sitemap file contents is empty.", "XML sitemap file is not specefied and not readed yet.");
      return $tmpResult;
      }
      
    if ($this->XMLDocumentImages === FALSE || empty($this->XMLDocumentImages) === TRUE || isset($this->XMLDocumentImages) === FALSE) 
      {
      return FALSE;
      }

    $tmpImage = $this->XMLDocumentImages->item($this->URLImageIndex);
    $tmpNode = $this->GetNodeByName('image:loc', $tmpImage->childNodes);
      
    if ($tmpNode === FALSE)
      {
      return FALSE;
      }
    else
      {
      return $tmpNode->nodeValue;
      }
    }
    
  public function GetCurLOCValue()
    {
    $tmpResult = '';
    $tmpURL = '';
    $tmpNode = '';
    
    if (empty($this->XMLDocument) === TRUE)
      {
      $tmpResult = $this->ErrorManager->RaiseError(7102, "XML sitemap file contents is empty.", "XML sitemap file is not specefied and not readed yet.");
      return $tmpResult;
      }
      
    if ($this->XMLDocumentURLS === FALSE)
      {
      return FALSE;
      }
      
    $tmpURL = $this->XMLDocumentURLS->item($this->URLIndex);
    $tmpNode = $this->GetNodeByName('loc', $tmpURL->childNodes);
      
    if ($tmpNode === FALSE)
      {
      return FALSE;
      }
    else
      {
      return $tmpNode->nodeValue;
      }
    }
    
  /* Get functions ends here */
  
  /* Set functions starts here */
  
  /* Set functions ends here */
  }

?>