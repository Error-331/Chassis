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
 * Class plcXMLParserHelper is a part of PHP framework - Chassis.   
 * 
 * @package     Chassis
 * @author      Selihov Sergei Stanislavovich <red331@mail.ru> 
 * @copyright   Copyright (c) 2010-2011 Selihov Sergei Stanislavovich.
 * @license     http://www.gnu.org/licenses/gpl.html  GNU GENERAL PUBLIC LICENSE (Version 3)
 *    
 */
 
/**
 * Classes for work with XML files.
 *  
 * @subpackage XML
 * @author Selihov Sergei Stanislavovich <red331@mail.ru>   
 */
 
/**
 * Documents the plcXMLParserHelper class.
 *  
 * @subpackage plcXMLParserHelper
 * @author Selihov Sergei Stanislavovich <red331@mail.ru>   
 */
 
require_once('Chassis/ErrorHandling/plcChassisException.php');

class plcXMLParserHelper
	{	
	protected $XMLDoc = NULL;
	protected $XMLFilePath = NULL;
	
	protected $XMLReplaceEntities = array(
	                                      '"' => '&quot;',
	                                      '&amp;' => '&',
	                                      '&' => '&amp;',
	                                      '\'' => '&apos;',
	                                      '<' => '&lt;',
	                                      '>' => '&gt;'	                                      
                                        );
		
	/* Core functions starts here */
	
	public function __construct()
    {
    $this->XMLDoc = new DOMDocument();
    }
    
  public function __destruct()
    {
    
    }
  
  // ASCII compatible   
  protected function ReplaceXMLEntities($usrText)
    {
    if (isset($usrText) === FALSE || empty($usrText) === TRUE){return $usrText;}
    else
      {
      foreach ($this->XMLReplaceEntities as $tmpEnd => $tmpReplace)
        {
        $usrText = str_ireplace($tmpEnd, $tmpReplace, $usrText);
        }
        
      return $usrText;
      }
    }
    
  public function CheckXMLDoc()
    {
    if (is_null($this->XMLDoc) === TRUE)
      {
      throw new plcChassisException('XML document object is not created yet.', '9106', null, 'Can not proceed without XML document object.');
      }
    else
      {
      return TRUE;
      }
    }
    
  public function AddAttrToNode($usrNode, $usrAttrName, $usrAttrValue)
    {
    $this->CheckXMLDoc();
    
    $usrAttrValue = $this->ReplaceXMLEntities($usrAttrValue);
    
    $tmpAttrNode = $this->XMLDoc->createAttribute($usrAttrName);
    $tmpTextNode = $this->XMLDoc->createTextNode($usrAttrValue);
    
    if ($tmpAttrNode === FALSE || $tmpTextNode === FALSE) {return FALSE;}
    
    $tmpAttrNode->appendChild($tmpTextNode);
    $usrNode->appendChild($tmpAttrNode);    

    return TRUE;
    }

  public function AddTagToDoc($usrTagName, $usrTagValue = NULL, $usrAddCarriageRet = TRUE)
    {
    $this->CheckXMLDoc();
    
    $usrTagValue = $this->ReplaceXMLEntities($usrTagValue);
    
    if ($usrTagValue == NULL)
      {
      $tmpNewNode = $this->XMLDoc->createElement($usrTagName); 
      $this->XMLDoc->appendChild($tmpNewNode);      
      }
    else
      {
      $tmpNewNode = $this->XMLDoc->createElement($usrTagName, $usrTagValue);
      $this->XMLDoc->appendChild($tmpNewNode);
      }
    
    if ($usrAddCarriageRet === TRUE)
      {
      $tmpTextNode = $this->XMLDoc->createTextNode("\n\t");
      $this->XMLDoc->appendChild($tmpTextNode);
      }  
            
    return $tmpNewNode; 
    }
    
  public function AddTagToNode($usrNode, $usrTagName, $usrTagValue = NULL, $usrAddCarriageRet = TRUE)
    {   
    $this->CheckXMLDoc();
    
    $usrTagValue = $this->ReplaceXMLEntities($usrTagValue);
    
    if ($usrTagValue == NULL)
      {
      $tmpNewNode = $this->XMLDoc->createElement($usrTagName); 
      $usrNode->appendChild($tmpNewNode);      
      }
    else
      {
      $tmpNewNode = $this->XMLDoc->createElement($usrTagName, $usrTagValue);
      $usrNode->appendChild($tmpNewNode);
      }
    
    if ($usrAddCarriageRet === TRUE)
      {
      $tmpTextNode = $this->XMLDoc->createTextNode("\n\t");
      $usrNode->appendChild($tmpTextNode);
      }  
            
    return $tmpNewNode;       
    } 
  
  protected function DeleteNodeRec(&$usrNode)
    {
    while ($usrNode->firstChild) 
      {
      while ($usrNode->firstChild->firstChild) 
        {
        $this->DeleteNodeRec($usrNode->firstChild);
        }
    
      $usrNode->removeChild($usrNode->firstChild);
      }    
    }
    
  public function DeleteNode($usrNode)
    {
    if (is_null($usrNode) === TRUE){return NULL;}    
    $tmpParNode = $usrNode->parentNode;
    
    $this->DeleteNodeRec($usrNode);
    $tmpParNode->removeChild($usrNode);
    }  
    
  public function DeleteChildNodes(&$usrContextNode = NULL)
    {
    if (is_null($usrContextNode) === TRUE){return NULL;}
    $this->DeleteNodeRec($usrContextNode);
  
    return TRUE;     
    } 
    
  public function LoadXMLFile($usrFile)
    {
    $tmpResult = FALSE;
    
    if (file_exists($usrFile) === FALSE)
      {
      throw new plcChassisException('Xml file does not exist.', '9101', null, 'Could not find "'.$usrFile.'" file.');
      }
      
    if (is_readable($usrFile) === FALSE)
      {
      throw new plcChassisException('Xml file is not readable.', '9102', null, 'Could not read "'.$usrFile.'" file.');
      }
      
    $tmpResult = $this->XMLDoc->load($usrFile);
    
    if ($tmpResult === FALSE)
      {
      throw new plcChassisException('Error occured during xml file read.', '9103', null, 'Error occured during reading of "'.$usrFile.'" file. Could not proceed feature.');
      }
      
    $this->XMLFilePath = $usrFile;

    return TRUE;
    }
    
  public function LoadXMLString($usrXML)
    {
    if (empty($usrXML) === TRUE || isset($usrXML) === FALSE)
      {
      return FALSE;
      }    
    
    if ($this->XMLDoc->loadXML($usrXML) === TRUE)
      {
      $this->XMLFilePath = NULL;
      return TRUE;
      }
    else
      {
      return FALSE;
      }
    }
    
  public function SaveXMLFile($usrFile)
    {
    $tmpResult = FALSE;
    
    $this->CheckXMLDoc();
    
    if (is_null($usrFile) !== TRUE)
      {
      $tmpResult = $this->XMLDoc->save($usrFile);
      
      if ($tmpResult === FALSE) {throw new plcChassisException('Could not save XML file.', '9104', null, 'Error occured during saving of "'.$usrFile.'" file.');}     
      }    
    else {throw new plcChassisException('XML file save path is not specified.', '9105', null, 'User does not provide XML file save path, could not save current changes.');}
    
    return TRUE;
    }      
    
  /* Core functions ends here */
  
  /* Get functions starts here */
  
  public function GetXMLFilePath()
    {
    return $this->XMLFilePath;
    }
    
  protected function GetNodeAttr($usrAttrName, $usrNode)
    {
    $Counter1 = 0;
    $tmpNodeAttrs = NULL;
    
    $usrAttrName = strtolower($usrAttrName);
    
    if (is_null($usrNode) === TRUE) {return NULL;}

    if($usrNode->hasAttributes())
      {
      $tmpNodeAttrs = $usrNode->attributes;
      
      if(is_null($tmpNodeAttrs) !== TRUE)
        {
        foreach ($tmpNodeAttrs as $tmpIndex => $tmpAttrVal)
          {
          if (strtolower($tmpIndex) == $usrAttrName)
            {
            return $tmpAttrVal;
            }          
          }
        }
      else
        {
        return NULL;
        }
      } 
      
    return NULL;
    }
  
  public function GetNodeAttrValByName($usrAttrName, $usrNode)
    {
    $tmpAttrNode = $this->GetNodeAttr($usrAttrName, $usrNode);
    if (is_null($tmpAttrNode) === TRUE){return NULL;}
    else {return $tmpAttrNode->nodeValue;}
    }    
  
  public function GetChildNodesByName($usrTagName, $usrContextNode = NULL)
    {
    $tmpResult = NULL;
    $tmpNode = NULL;
    $Counter1 = 0;
    
    $usrTagName = strtolower($usrTagName);
    
    if (is_null($usrContextNode) === TRUE) {return NULL;}

    for ($Counter1 = 0; $Counter1 < $usrContextNode->childNodes->length; $Counter1++)
      {
      $tmpNode = $usrContextNode->childNodes->item($Counter1);
      
      if ($usrTagName == strtolower($tmpNode->nodeName))
        {
        if ($tmpResult == NULL){$tmpResult = array();}
          
        $tmpResult[count($tmpResult)] = $tmpNode;  
        }  
      }
    
    return $tmpResult;
    }
    
  public function GetNodeByTagName($usrTagName, $usrContextNode = NULL, $usrIsFirst = TRUE)
    {
    $tmpResult = NULL;
    $tmpResultArray = array();
    $tmpSubResultArray = array();
    
    $tmpNode = NULL;
    $Counter1 = 0;
    
    if (is_string($usrTagName) === FALSE) {return NULL;}
    
    $usrTagName = strtolower($usrTagName);
    
    if ($usrContextNode != NULL)
      {
      if ($usrContextNode->childNodes->length == 0)
        {      
        return NULL;
        }
      
      for ($Counter1 = 0; $Counter1 < $usrContextNode->childNodes->length; $Counter1++)
        {
        $tmpNode = $usrContextNode->childNodes->item($Counter1); 

        if ($usrTagName == strtolower($tmpNode->nodeName))
          {
          $tmpResultArray[] = $tmpNode;
          } 
          
        if ($tmpNode->childNodes->length > 0)
          {   
          $tmpSubResultArray[] = $this->GetNodeByTagName($usrTagName, $tmpNode, $usrIsFirst);          
          }                        
        }
        
      if ($usrIsFirst === TRUE)
        {
        if (count($tmpResultArray) > 0) {return $tmpResultArray[0];}
        else if (count($tmpSubResultArray) > 0) {return $tmpSubResultArray[0];}
        else {return NULL;}
        }
      else
        {
        if (count($tmpResultArray) > 0) {return $tmpResultArray[count($tmpResultArray) - 1];}
        else if (count($tmpSubResultArray) > 0) {return $tmpSubResultArray[count($tmpSubResultArray) - 1];}
        else {return NULL;}
        }         
      }
    else
      {         
      if (strtolower($this->XMLDoc->documentElement->nodeName) != $usrTagName)
        {
        return $this->GetNodeByTagName($usrTagName, $this->XMLDoc->documentElement, $usrIsFirst);
        }
      else
        {
        return $this->XMLDoc->documentElement->nodeName;
        }     
      } // if no context provided    
    } 
  
  public function GetNodeByTagNameFirst($usrTagName, $usrContextNode = NULL)
    {
    return $this->GetNodeByTagName($usrTagName, $usrContextNode, TRUE);
    }
    
  public function GetNodeByTagNameLast($usrTagName, $usrContextNode = NULL)
    {
    return $this->GetNodeByTagName($usrTagName, $usrContextNode, FALSE);
    } 
      
  public function GetNodeByTagNameInsert($usrTagName, $usrContextNode = NULL, $usrTagValue = NULL, $usrAddCarriageRet = TRUE)
    {
    $tmpResult = FALSE;
    $tmpNode = $this->GetNodeByTagName($usrTagName, $usrContextNode);
    
    if (is_null($tmpNode) === TRUE)
      {
      if (is_null($usrContextNode) === TRUE)
        {
        $tmpResult = $this->AddTagToDoc($usrTagName, $usrTagValue = NULL, $usrAddCarriageRet = TRUE);
        }
      else
        {
        $tmpResult = $this->AddTagToNode($usrContextNode, $usrTagName, $usrTagValue = NULL, $usrAddCarriageRet = TRUE);
        }
        
      if ($tmpResult === FALSE){return NULL;} 
      else {return $tmpResult;}   
      }
    else
      {
      return $tmpNode;
      }   
    }
    
  public function GetFirstChildTag($usrContextNode)
    {
    $tmpNode = NULL;
    
    $Counter1 = 0;
    
    if (empty($usrContextNode) === TRUE || isset($usrContextNode) === FALSE || is_null($usrContextNode) === TRUE)
      {
      return NULL;
      }
      
    for ($Counter1 = 0; $Counter1 < $usrContextNode->childNodes->length; $Counter1++)
      {
      $tmpNode = $usrContextNode->childNodes->item($Counter1);
      
      if ($tmpNode->nodeType === XML_ELEMENT_NODE)
        {
        return $tmpNode; 
        }
      }
    
    return NULL;
    }
    
  public function GetChildNodesByAttrVal($usrAttrName, $usrAttrVal = NULL, $usrContextNode = NULL)
    {
    $tmpNode = NULL;
    $tmpAttrVal = NULL;
    $tmpResultNodes = array();
    
    $Counter1 = 0;
    
    if (empty($usrAttrName) === TRUE || is_null($usrContextNode) === TRUE) {return NULL;}
    
    for ($Counter1 = 0; $Counter1 < $usrContextNode->childNodes->length; $Counter1++)
      {
      $tmpNode = $usrContextNode->childNodes->item($Counter1);
      $tmpAttrVal = $this->GetNodeAttrValByName($usrAttrName, $tmpNode);
      
      if (is_null($usrAttrVal) === TRUE)
        {
        if (is_null($tmpAttrVal) === TRUE){continue;}
        else
          {
          $tmpResultNodes[] = $tmpNode;
          }
        }
      else
        {
        if (is_null($tmpAttrVal) === TRUE){continue;}
        else
          {
          $tmpAttrVal = strtolower($tmpAttrVal);
          if ($tmpAttrVal == $usrAttrVal)
            {
            $tmpResultNodes[] = $tmpNode;
            }
          else
            {
            continue;
            }
          } 
        }
      }
    
    if (count($tmpResultNodes) <= 0)
      {
      return NULL;
      }
    else
      {
      return $tmpResultNodes;
      } 
    }
      
  /* Get functions ends here */ 
  
  /* Set functions starts here */
  
  public function SetNodeAttr($usrNode, $usrAttrName, $usrAttrValue)  
    {
    $this->CheckXMLDoc();
    
    $usrAttrValue = $this->ReplaceXMLEntities($usrAttrValue);
    
    $tmpAttrNode = $this->GetNodeAttr($usrAttrName, $usrNode);
    
    if (is_null($tmpAttrNode) === TRUE){return $this->AddAttrToNode($usrNode, $usrAttrName, $usrAttrValue);}
    else 
      { 
      $tmpAttrNode->childNodes->item(0)->nodeValue = $usrAttrValue;

      return TRUE;
      } 
    }   
  
  /* Set functions ends here */
	}

?>