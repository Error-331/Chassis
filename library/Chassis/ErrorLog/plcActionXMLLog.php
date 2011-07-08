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
 * Class plcActionXMLLog is a part of PHP framework - Chassis.   
 * 
 * @package     Chassis
 * @author      Selihov Sergei Stanislavovich <red331@mail.ru> 
 * @copyright   Copyright (c) 2010-2011 Selihov Sergei Stanislavovich.
 * @license     http://www.gnu.org/licenses/gpl.html  GNU GENERAL PUBLIC LICENSE (Version 3)
 *    
 */
 
/**
 * Classes for error and action logging.
 *  
 * @subpackage ErrorLog
 * @author Selihov Sergei Stanislavovich <red331@mail.ru>   
 */
 
/**
 * Documents the plcActionXMLLog class.
 * 
 * Following class is intended for logging actions made by script into XML files. All error objects must be descendents 
 * of class plcChassisException.
 * 
 * All log files follows this standart:
 * 
 * <?xml version="1.0" encoding="UTF-8">
 * <log dateISO="ISO date here">
 *  <section> 
 *   <action timeISO="ISO time here">
 *    <message>
 *    </message>
 *    <description>  - optional
 *    </description>
 *    <error> - optional
 *     <code>
 *     </code>
 *     <message> 
 *     </message>  
 *     <description> 
 *     </description>    
 *    </error>      
 *   </action> 
 *   </delimiter> - optional  
 *   <subsection title="some title">
 *    .
 *    .
 *    .
 *    <subsection title="some title">
 *    </subsection>     
 *   </subsection>     
 *  </section>  
 * </log>  
 * 
 * Custom tags that can occure in message, description or error (code, message and description) tags:    
 *    
 * <lbreak/> - equivalent to HTML <br/> tag;
 *   
 * @subpackage plcActionXMLLog
 * @author Selihov Sergei Stanislavovich <red331@mail.ru>   
 */
 
require_once('Chassis/ErrorHandling/plcChassisException.php');
require_once('Chassis/XML/plcXMLParserHelper.php');

class plcActionXMLLog
  {
  /**
   * @access protected
   * @var mixed instance of the current class 
   */	
  protected static $Instance = FALSE;
  
  /**
   * @access protected
   * @var string path to directory where all error logs will be saved 
   */	  
  protected $CurDir;
   
  /**
   * @access protected
   * @var object XML parser helper that will handle XML log files
   */	  
  protected $XMLParserHlp = NULL;

  /**
   * @access protected
   * @var object XML node to which all sections will be add
   */	    
  protected $CurLogNode = NULL;
   
  /**
   * @access protected
   * @var object XML node to which all actions will be add if no sub section is specified
   */	                 
  protected $CurSectionNode = NULL;
    
  /**
   * @access protected
   * @var object XML node to which all actions will be add
   */	   
  protected $CurSubSectionNode = NULL;
  
  /* Core functions starts here */

  private function __construct()
    {
    $this->XMLParserHlp = new plcXMLParserHelper();
    }
    
  public function __destruct()
    {
    try
      {
      $this->XMLParserHlp->SaveXMLFile($this->CurDir.@date('o-m-d').'.xml');  
      }
    catch(plcChassisException $usrError)
      {
      return FALSE;
      }   
    } 

  /**
   * Function that is used to check whether xml log file and its nodes are loaded.
   * 
   * Simple function that is used to check whether xml log file and its nodes are loaded and ready to add log records. This method attempts to load all the necessary data
   * if it is not loaded already.   
   * 
   * @access protected       
   * 
   * @return bool returns TRUE on success and FALSE on fail.            
   *                             
   */ 
    
  protected function CheckFileNodes()
    {
    $tmpLogNode = NULL;
    $tmpSectionNode = NULL;
    
    $tmpErrorMess = '';
    $tmpFileName = @date('o-m-d').'.xml';
    
    if (empty($this->CurDir) || !file_exists($this->CurDir)) 
      {
      return FALSE;
      } 
      
    /* XML file load starts here */

    if (is_null($this->XMLParserHlp->GetXMLFilePath()) === TRUE)
      {      
      if (file_exists($this->CurDir.$tmpFileName) === TRUE)
        {
        try
          {
          $this->XMLParserHlp->LoadXMLFile($this->CurDir.$tmpFileName);
          }
        catch(plcChassisException $usrError)
          {
          return FALSE;
          }     
        }
      else
        {
        try
          {                    
          $this->XMLParserHlp->SaveXMLFile($this->CurDir.$tmpFileName);  
          }
        catch(plcChassisException $usrError)
          {
          return FALSE;
          }                   
        }          
      }
      
    /* XML file load ends here */ 
    
    /* General node load starts here */
    
    if (is_null($this->CurLogNode) === TRUE)
      {
      $tmpLogNode = $this->XMLParserHlp->GetNodeByTagNameFirst('log', NULL);
      
      if (is_null($tmpLogNode) === TRUE)
        {
        $tmpLogNode = $this->XMLParserHlp->AddTagToDoc('log', NULL, FALSE); 
        $this->XMLParserHlp->AddAttrToNode($tmpLogNode, 'dateISO', @date('o-m-d')); 
        $this->CurLogNode = $tmpLogNode;
        }
      else
        {
        $this->CurLogNode = $tmpLogNode;
        }    
        
      $this->XMLParserHlp->SaveXMLFile($this->CurDir.$tmpFileName);  
      }
           
    if(is_null($this->CurSectionNode) === TRUE)
      {
      $tmpSectionNode = $this->XMLParserHlp->GetNodeByTagNameLast('section', $this->CurLogNode);
      
      if (is_null($tmpSectionNode) === TRUE)
        {
        $this->CurSectionNode = $this->XMLParserHlp->GetNodeByTagNameInsert('section', $this->CurLogNode, NULL, FALSE);
        }
      else
        {
        $this->CurSectionNode = $tmpSectionNode;
        }
        
      $this->CurSubSectionNode = NULL;       
      $this->XMLParserHlp->SaveXMLFile($this->CurDir.$tmpFileName); 
      }
      
    /* General node load ends here */
    
    return TRUE;           
    } 
  
  /**
   * Function that is used to reset current subsection.
   * 
   * Simple function is used to reset current subsection. All subsequent records will be add to parent section.
   * 
   * @access public                  
   *                             
   */   
    
  public function ResetSubSection()
    {
    $this->CurSubSectionNode = NULL;
    }
    
  /**
   * Function that is used to go up one level subsection.
   * 
   * Simple function is used to go up one level subsection. All subsequent records will be add to parent subsection. 
   * If parent subsection does not exist all records will be add to parent section.
   * 
   * @access public 
   * 
   * @return bool returns TRUE on success and FALSE on fail.                        
   *                             
   */     
    
  public function GoUpSubSection()
    {
    $tmpNode = NULL;
    
    if (is_null($this->CurSubSectionNode) === TRUE) {return TRUE;}
    else
      {
      $tmpNode = $this->CurSubSectionNode->parentNode;
      if (is_null($this->CurSubSectionNode) === TRUE){return FALSE;}
      
      if (strtolower($tmpNode->nodeName) === 'subsection'){$this->CurSubSectionNode = $tmpNode;}
      else {$this->CurSubSectionNode = NULL;} 
      }
    }
     
  /**
   * Function that is used to log single error object.
   * 
   * Simple function is used to log single error object.
   * 
   * @access public       
   * 
   * @param object that is descendent of plcAbstractError class. 
   * 
   * @return bool returns TRUE on success and FALSE on fail.            
   *                             
   */ 
    
  public function LogAction($usrActMess = '', $usrActDesc = '', $usrActError = NULL)
    {  
    $tmpErrorNode = NULL;
    $tmpActionNode = NULL;
                   
    if (empty($usrActMess) === TRUE || $this->CheckFileNodes() === FALSE)
      {
      return FALSE;
      }
    else
      {
      if (is_null($this->CurSubSectionNode) === TRUE)
        {
        $tmpActionNode = $this->XMLParserHlp->AddTagToNode($this->CurSectionNode, 'action', NULL, FALSE);
        }
      else
        {
        $tmpActionNode = $this->XMLParserHlp->AddTagToNode($this->CurSubSectionNode, 'action', NULL, FALSE);
        }
      
      $this->XMLParserHlp->AddAttrToNode($tmpActionNode, 'timeISO', substr(@date('c'), strpos(@date('c'), 'T') + 1));        
      $this->XMLParserHlp->AddTagToNode($tmpActionNode, 'message', $usrActMess, FALSE);
      
      if (empty($usrActDesc) !== TRUE) {$this->XMLParserHlp->AddTagToNode($tmpActionNode, 'description', $usrActDesc, FALSE);}
      
      if (is_null($usrActError) !== TRUE)
        {
        $tmpErrorNode =  $this->XMLParserHlp->AddTagToNode($tmpActionNode, 'error', NULL, FALSE);
        $this->XMLParserHlp->AddTagToNode($tmpErrorNode, 'code', $usrActError->getCode(), FALSE); 
        $this->XMLParserHlp->AddTagToNode($tmpErrorNode, 'message', $usrActError->getMessage(), FALSE);
        $this->XMLParserHlp->AddTagToNode($tmpErrorNode, 'description', $usrActError->GetFullInfo(), FALSE); 
        }
                 
      return TRUE;
      }    
    }
    
  /**
   * Function that is used to add delimiter between action records.
   * 
   * Simple function is used to add delimiter between action records.
   * 
   * @access public       
   * 
   * @return bool returns TRUE on success and FALSE on fail.            
   *                             
   */     
    
  public function InsertDelimiter()
    {    
    if ($this->CheckFileNodes() === FALSE)
      {
      return FALSE;
      }
      
    $this->XMLParserHlp->AddTagToNode($this->CurSectionNode, 'delimiter', NULL, FALSE);   
    return TRUE;
    }

  /**
   * Function that is used to add new log section.
   * 
   * Simple function is used to add new log section. All new action records will be inserted in this new section.
   * If new section is add successfully subsection pointer will be reset.
   * 
   * @access public       
   * 
   * @return bool returns TRUE on success and FALSE on fail.            
   *                             
   */ 
    
  public function InsertSection()
    {
    if ($this->CheckFileNodes() === FALSE)
      {
      return FALSE;
      }
      
    $this->CurSectionNode = $this->XMLParserHlp->AddTagToNode($this->CurLogNode, 'section', NULL, FALSE);   
    $this->CurSubSectionNode = NULL;
    
    return TRUE;    
    }
    
  /**
   * Function that is used to add new log subsection.
   * 
   * Simple function is used to add new log subsection. All new action records will be inserted in this new subsection.
   * 
   * @access public       
   * 
   * @return bool returns TRUE on success and FALSE on fail.            
   *                             
   */ 
    
  public function InsertSubSection()
    {
    if ($this->CheckFileNodes() === FALSE)
      {
      return FALSE;
      }
    
    if (is_null($this->CurSubSectionNode) === TRUE)
      {
      $this->CurSubSectionNode = $this->XMLParserHlp->AddTagToNode($this->CurSectionNode, 'subsection', NULL, FALSE);
      }
    else
      {
      $this->CurSubSectionNode = $this->XMLParserHlp->AddTagToNode($this->CurSubSectionNode, 'subsection', NULL, FALSE);
      }
           
    return TRUE;    
    }
    
	/* Core functions ends here */
  
  /* Get functions starts here */    
    
  /**
   * Function that is used to return instance of the current class.
   * 
   * Simple function is used to return instance of the current class.
   * 
   * @access public       
   * 
   * @return object instance of the current class.        
   *                             
   */ 
       
	public function GetInstance() 
		{
		if (plcActionXMLLog::$Instance === FALSE) 
			{
			plcActionXMLLog::$Instance = new plcActionXMLLog;
			}

		return plcActionXMLLog::$Instance;
		}
				
  /* Get functions ends here */	
  
  /* Set functions starts here */	
		
  /**
   * Function that is used to set path to a directory where all error logs will be saved.
   * 
   * Simple function is used to set path to a directory where all error logs will be saved.
   * 
   * @access public 
   * 
   * @param string path to a directory.      
   * 
   * @throws plcChassisException            
   * 
   * @return bool TRUE on success.        
   *                             
   */ 
    
  public function SetDir($usrDir)
    {
    $tmpSlashPos = 0;
    
    if(is_dir($usrDir) === FALSE)
      {
      throw new plcChassisException('Log directory is not a directory.', 3301, null ,'Directory '.$usrDir.' is not a directory.');
      }
    if (is_writable($usrDir) !== TRUE)
      {
      throw new plcChassisException('Log directory is not writable.', 3302, null ,'Directory '.$usrDir.' is not writable.');
      } 
      
    if(strpos($usrDir, '/', strlen($usrDir) - 1) === FALSE)
      {
      $usrDir .= '/';
      }
      
    $this->CurDir = $usrDir;
     
    return TRUE;  
    }
    
  /**
   * Function that is used to set title for current section.
   * 
   * Simple function is used to set title for current section.
   * 
   * @access public 
   * 
   * @param string title for current section.      
   *           
   * @return bool returns TRUE on success and FALSE on fail.         
   *                             
   */     
    
  public function SetSectionTitle($usrTitle)
    {    
    if (is_null($this->CurSectionNode) === TRUE){return FALSE;} 
    return $this->XMLParserHlp->SetNodeAttr($this->CurSectionNode, 'title', $usrTitle); 
    } 
    
  /**
   * Function that is used to set title for current subsection.
   * 
   * Simple function is used to set title for current subsection.
   * 
   * @access public 
   * 
   * @param string title for current subsection.      
   *           
   * @return bool returns TRUE on success and FALSE on fail.         
   *                             
   */       
    
  public function SetSubSectionTitle($usrTitle)
    {
    if (is_null($this->CurSubSectionNode) === TRUE){return FALSE;} 
    return $this->XMLParserHlp->SetNodeAttr($this->CurSubSectionNode, 'title', $usrTitle); 
    }
    
  /* Set functions ends here */	
  }

?>