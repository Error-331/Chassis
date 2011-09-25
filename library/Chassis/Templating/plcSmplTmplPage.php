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
 * Class plcSmplTmplPage is a part of PHP framework - Chassis.   
 * 
 * @package     Chassis
 * @author      Selihov Sergei Stanislavovich <red331@mail.ru> 
 * @copyright   Copyright (c) 2010-2011 Selihov Sergei Stanislavovich.
 * @license     http://www.gnu.org/licenses/gpl.html  GNU GENERAL PUBLIC LICENSE (Version 3)
 *    
 */
 
/**
 * Classes for work with templates.
 *  
 * @subpackage Templating
 * @author Selihov Sergei Stanislavovich <red331@mail.ru>   
 */
 
/**
 * Documents the plcSmplTmplPage class.
 * 
 * Simple class that renders single XHTML Chassis template.
 * 
 * Tag list
 * ========
 * 
 * Custom replace tag
 * ------------------
 * 
 * Replaces chassis tag with custom user data.
 * 
 * Tag notation:  
 * 
 * <chassis type="var" name="Chas1"><chassis>
 * 
 * or
 * 
 * <chassis name="Chas1"/>
 * 
 * User parameters:
 * 
 * User is required to set custom value for the corresponding name (attribute name).
 * 
 * Tag replace
 * -----------
 * 
 * Replaces chassis tag with corresponding user tag.
 * 
 * Tag notation: 
 * 
 * <chassis type="replace" name="Chas1"/> 
 * 
 * User parameters:
 * 
 * tag - name of the tag (required);
 * content - custom content of the tag (optional);
 * is_simple - indicates whether there is a closing tag or not (optional);
 * is_closing - indicates whether it is a closing tag or not (optional);
 * attributes - array of tag attributes (optional);
 * 
 * Bool tag
 * --------
 * 
 * Displays or not displays content based on the boolean value.
 * 
 * Tag notation:
 * 
 * <chassis type="bool" name="Chas1">
 * .
 * .
 * .
 * </chassis>
 * 
 * User parameters:
 * 
 * User is required to set custom value (as boolean) for the corresponding name (attribute name).
 * 
 * Loop tag
 * --------
 * 
 * Replaces chassis tag with custom user content provided as array.
 * 
 * Tag notation: 
 * 
 * <chassis type="loop" name="Chas1">
 * .
 * .
 * .
 * </chassis>
 * 
 * User parameters:
 * 
 * User is required to set custom value (as array) for the corresponding name (attribute name).
 * 
 * Loop even/odd tag (used inside loop only)
 * -----------------------------------------
 * 
 * Displays or not displays content based on the current loop iteration.
 * 
 * Tag notation:
 * 
 * <chassis type="loop_odd">
 * .
 * .
 * .
 * </chassis>
 * 
 * or
 * 
 * <chassis type="loop_even">
 * .
 * .
 * .
 * </chassis>
 * 
 * User parameters:
 * 
 * User is required to set boolean value for the corresponding name (attribute name).
 *   
 * @subpackage plcSmplTmplPage
 * @author Selihov Sergei Stanislavovich <red331@mail.ru>   
 * 
 */

require_once('Chassis/ErrorHandling/plcTemplateSystemException.php');
require_once('Chassis/Cache/plcSmplFileCache.php');

class plcSmplTmplPage
    {   
    /**
     * @access protected
     * @var string path to current page template.
     */
    
    protected $PagePath = '';
       
    /**
     * @access protected
     * @var array template variables.
     */    
    
    protected $TempVars = array();
    
    /**
     * @access protected
     * @var bool indicates whether or not use caching mechanism.
     */      
    
    protected $UseCache = FALSE;
    
    /* Core methods starts here */
    
    /**
     * Main constructor function.
     * 
     * Main constructor function initialises the work of the object.
     * 
     * @access public
     * 
     * @param string path to the page template
     * @param array template variables 
     * @param bool indicates whether to use caching mechanism
     * 
     * @throws plcTemplateSystemException
     *                                      
     */      
    
    public function __construct($usrPagePath = '', $usrTempVars = array(), $usrUseCache = FALSE)
        {
        $this->SetPagePath($usrPagePath);
        $this->SetPageVars($usrTempVars);
        $this->SetUseCache($usrUseCache);
        }
        
    public function __destruct()
        {   
        }
        
    /**
     * Function that loads attributes from the current tag and returns them as array.
     * 
     * Utility function that loads attributes from the current tag and returns them as array.
     * 
     * @access protected
     * 
     * @param object XML reader object
     * 
     * @return array of tag attributes with corresponding values.                               
     *       
     */          
    
    protected function LoadCurTagAttrs($usrXMLReader)
        {
        $tmpArr = array();
        
        if ($usrXMLReader->hasAttributes)
            {
            while($usrXMLReader->moveToNextAttribute())
                { 
                $tmpArr[strtolower($usrXMLReader->name)] = $usrXMLReader->value;                           
                }             
            }  
            
        return $tmpArr;
        }
        
    /**
     * Function that displays attributes with corresponding values of the current tag.
     * 
     * Utility function that displays attributes with corresponding values of the current tag.
     * 
     * @access protected
     * 
     * @param object XML reader object
     * @param bool indicates whether attributes are processed in context of chassis tag or not
     *                                      
     */         
        
    protected function DisplayAttrs($usrXMLReader, $usrIsNotChassis = TRUE)
        {
        $tmpAttrs = $this->LoadCurTagAttrs($usrXMLReader);
        
        foreach($tmpAttrs as $tmpKey => $tmpVal)
            {
            if ($usrIsNotChassis === FALSE && strtolower($tmpKey) === 'xmlns') {continue;}          
            echo(' '.$tmpKey.'="'.$tmpVal.'"');
            }            
        }  
        
    /**
     * Function that processes chassis tags in the template and visualize them.
     * 
     * Utility function that processes chassis tags in the template and visualize them.
     * 
     * @access protected
     * 
     * @param object XML reader object
     * @param array user defined template variables
     * 
     * @return bool TRUE on success and FALSE on FAIL.
     *                                      
     */         
        
    protected function DisplayChassisTag($usrXMLReader, $usrVars)
        {
        $tmpXMLReader = NULL;
        $tmpReadRes = FALSE;
        
        $tmpContents = '';
        $tmpAttrs = array();
        
        $Counter1 = 0;
        
        if ($usrXMLReader->nodeType == XMLReader::ELEMENT)
            {   
            $tmpContents = $usrXMLReader->readInnerXML();
            $tmpContents = '<chassis_service>'.$tmpContents.'</chassis_service>'; 
            $tmpAttrs = $this->LoadCurTagAttrs($usrXMLReader);
            
            if (array_key_exists('type', $tmpAttrs) === FALSE){$tmpAttrs['type'] = 'var';}
            
            switch($tmpAttrs['type'])
                {
                case 'loop':
                
                if (empty($tmpContents) === TRUE)
                    {
                    throw new plcTemplateSystemException('Contents of the loop tag is empty.', 11105, null, 'No inner XML for the chassis loop tag.'); 
                    }
                    
                if (array_key_exists('name', $tmpAttrs) === FALSE) {break;}
                if (array_key_exists($tmpAttrs['name'], $usrVars) === FALSE) {break;}
                $tmpXMLReader = new XMLReader();
                
                foreach($usrVars[$tmpAttrs['name']] as $tmpKey => $tmpVal)
                    {
                    $tmpReadRes = @$tmpXMLReader->xml($tmpContents, 'UTF-8', LIBXML_NOERROR|LIBXML_NOWARNING);
                
                    if ($tmpReadRes === FALSE)
                        {
                        throw new plcTemplateSystemException('Cannot read XML from string.', 11106, null, 'Invalid XML data or empty string.');
                        } 
                                
                    $Counter1 += 1;
                            
                    $tmpVal['chassis_odd_even'] = $Counter1 % 2;      
                    $this->Display($tmpXMLReader, $tmpVal);
                    }
              
                $tmpXMLReader->close();                      
                break;
                
                case 'loop_odd':

                if (array_key_exists('chassis_odd_even', $usrVars) === FALSE) {break;}    
                if ($usrVars['chassis_odd_even'] == 1)
                    {
                    $tmpXMLReader = new XMLReader();
                    $tmpReadRes = @$tmpXMLReader->xml($tmpContents, 'UTF-8', LIBXML_NOERROR|LIBXML_NOWARNING);
                    $this->Display($tmpXMLReader, $usrVars);
                    $tmpXMLReader->close();
                    }
                break; 
                
                case 'loop_even':
                if (array_key_exists('chassis_odd_even', $usrVars) === FALSE) {break;}    
                
                if ($usrVars['chassis_odd_even'] == 0)
                    {
                    $tmpXMLReader = new XMLReader();
                    $tmpReadRes = @$tmpXMLReader->xml($tmpContents, 'UTF-8', LIBXML_NOERROR|LIBXML_NOWARNING);
                    $this->Display($tmpXMLReader, $usrVars);
                    $tmpXMLReader->close();
                    }
                break;                
                
                case 'bool':
                    
                if (array_key_exists('name', $tmpAttrs) === FALSE) {break;}
                if (array_key_exists($tmpAttrs['name'], $usrVars) === FALSE) {break;}
                
                if ($usrVars[$tmpAttrs['name']] === TRUE) 
                    {
                    $tmpXMLReader = new XMLReader();
                    $tmpReadRes = @$tmpXMLReader->xml($tmpContents, 'UTF-8', LIBXML_NOERROR|LIBXML_NOWARNING);
                    $this->Display($tmpXMLReader);
                    $tmpXMLReader->close();
                    }
                
                break;  
                
                case 'replace':
                if (array_key_exists('name', $tmpAttrs) === FALSE) {break;} 
                if (array_key_exists($tmpAttrs['name'], $usrVars) === FALSE) {break;}                  
                if (array_key_exists('tag', $usrVars[$tmpAttrs['name']]) === FALSE) {break;}
                
                if (array_key_exists('is_closing', $usrVars[$tmpAttrs['name']]) === TRUE) 
                    {
                    if ($usrVars[$tmpAttrs['name']]['is_closing'] === TRUE)
                        {
                        echo('</'.$usrVars[$tmpAttrs['name']]['tag'].'>');
                        break;
                        }
                    
                    }
                
                echo('<');
                echo($usrVars[$tmpAttrs['name']]['tag']);
                
                if (array_key_exists('attributes', $usrVars[$tmpAttrs['name']]) === TRUE) 
                    {
                    foreach($usrVars[$tmpAttrs['name']]['attributes'] as $tmpKey => $tmpVal)
                        {
                        echo(' '.$tmpKey.'='.'"'.$tmpVal.'"');
                        }
                    }
                    
                if (array_key_exists('content', $usrVars[$tmpAttrs['name']]) === TRUE)
                    {
                    echo('>');
                    echo($usrVars[$tmpAttrs['name']]['content']);
                    echo('</'.$usrVars[$tmpAttrs['name']]['tag'].'>');
                    }
                else
                    {
                    if (array_key_exists('is_simple', $usrVars[$tmpAttrs['name']]) === TRUE)
                        {
                        if ($usrVars[$tmpAttrs['name']]['is_simple'] === TRUE)
                            {
                            echo('/>');
                            }
                        else
                            {
                            echo('>');
                            }
                        }
                    else
                        {
                        echo('>');
                        }
                    }
                    
                break;    
                
                case 'var':
                default:
                    
                if (array_key_exists('name', $tmpAttrs) === FALSE) {break;} 
                if (array_key_exists($tmpAttrs['name'], $usrVars) === FALSE) {break;}
                 
                echo($usrVars[$tmpAttrs['name']]);                    
                break;    
                }
                
            return TRUE;    
            }
             
        return FALSE;    
        }
        
    /**
     * Function renders XHTML template and visualize it.
     * 
     * Main visualization function of the class. Two function parameters ($usrXMLReader and $usrVars) 
     * are reserved for internal use only - please do not use them, use constructor function or 
     * SetPageVars() method to set template variables.
     * 
     * @access public
     * 
     * @param object XML reader object
     * @param array user defined template variables
     * 
     * @return bool TRUE on success and FALSE on FAIL.
     * 
     * @see plcSmplTmplPage::SetPageVars()
     *                                      
     */          
                
    public function Display($usrXMLReader = NULL, $usrVars = NULL)
        {
        $tmpResult = FALSE;
        $tmpIsEmptyTag = FALSE;
        
        $tmpIsOpenLoc = FALSE;
        $tmpCacheMech = NULL;
        
         /* Creation of new XML reader starts here */
               
        if (is_null($usrXMLReader) === TRUE)
            { 
            if ($this->UseCache === TRUE)
                {
                $tmpCacheMech = new plcSmplFileCache();
                if ($tmpCacheMech->IsCached() === TRUE)
                    {
                    $tmpCacheMech->ShowCached();
                    return TRUE;
                    }  
                else
                    {
                    $tmpCacheMech->StartCahing();
                    }
                }            
                      
            $usrXMLReader = new XMLReader();
            
            $tmpResult = $usrXMLReader->open($this->PagePath, 'UTF-8', LIBXML_NOERROR|LIBXML_NOWARNING);
            if ($tmpResult === FALSE) 
                {
                throw new plcTemplateSystemException('Cannot open XHTML template.', 11104, null, 'Error while opening XHTML template: "'.$this->PagePath.'".');          
                }   
                           
            $tmpIsOpenLoc = TRUE;                            
            }
            
        /* Creation of new XML reader ends here */
            
        /* Template vars set starts here */
            
        if (is_null($usrVars) === TRUE)
            {
            $tmpIsOpenLoc = TRUE;
            $usrVars = $this->TempVars;
            }
            
        /* Template vars set ends here */    
                
        /* XHTML template reading starts here */
        
        while ($usrXMLReader->read()) 
            { 
            if (strtolower($usrXMLReader->name) == 'chassis_service')
                {
                continue;
                }
            
            if (strtolower($usrXMLReader->name) == 'chassis')
                {
                $this->DisplayChassisTag($usrXMLReader, $usrVars);                
                $usrXMLReader->next();
                }     
            
            /* Start element node processing starts here */
            
            if ($usrXMLReader->nodeType == XMLReader::ELEMENT)
                {                        
                if ($usrXMLReader->hasAttributes)
                    {                    
                    echo('<'.$usrXMLReader->name.'');              
                    $tmpIsEmptyTag = $usrXMLReader->isEmptyElement;
                    
                    /* Attributes processing starts here */
                   
                    $this->DisplayAttrs($usrXMLReader, $tmpIsOpenLoc);
                   
                    if ($tmpIsEmptyTag)
                        {
                        echo('/>');
                        }
                    else
                        {
                        echo('>');
                        }                 
                                     
                    /* Attributes processing ends here */
                    }
                else
                    {
                    if ($usrXMLReader->isEmptyElement)
                        {
                        echo('<'.$usrXMLReader->name.'/>');
                        }   
                    else
                        {
                        echo('<'.$usrXMLReader->name.'>');
                        }                   
                    }                      
                }
               
            /* Start element node processing ends here */   
            
            /* Text node processing starts here */    
            if ($usrXMLReader->nodeType == XMLReader::TEXT)
                {
                echo($usrXMLReader->value);                            
                } 
                
            /* Text node processing ends here */                
                
            /* End element node processing starts here */
                
            if ($usrXMLReader->nodeType == XMLReader::END_ELEMENT)
                {                          
                echo('</'.$usrXMLReader->name.'>');
                }  
                
            /* End element node processing starts here */
            }                         
        
        /* XHTML template reading ends here */
        
        if ($tmpIsOpenLoc === TRUE)
            {
            if (is_null($tmpCacheMech) !== TRUE) {$tmpCacheMech->EndCaching();}
     
            $tmpIsOpenLoc = FALSE;
            $usrXMLReader->close();
            }          
        }
                 
    /* Core methods ends here */
    
    /* Get methods starts here */
        
    /**
     * Method that returns path to current XHTML template.
     * 
     * Simple function that returns path to current XHTML template.
     * 
     * @access public
     * 
     * @return string path to the current XHTML template.
     *                                      
     */         
               
    public function GetPagePath()
        {
        return $this->PagePath;
        }
        
    /**
     * 
     * Method that returns variables for the current XHTML template.
     * 
     * Simple function that returns variables for the current XHTML template.
     * 
     * @access public
     * 
     * @return array variables for the current XHTML template.
     *                                      
     */  
        
    public function GetPageVars()
        {
        return $this->TempVars;
        } 
        
    /**
     * Function that used to return value that indicates whether to use caching mechanism.
     * 
     * Simple function that returns value that indicates whether to use caching mechanism.
     * 
     * @access public
     * 
     * @return bool TRUE caching mechanism will be used, FALSE caching mechanism will not be used
     *                                       
     */          
        
    public function GetUseCache($usrUseCache)
        {
        return $this->UseCache;
        }        
                     
    /* Get methods ends here */    
        
    /* Set methods starts here */
        
    /**
     * Function that used to set path to the current XTML template.
     * 
     * Simple function that sets path to current XHTML template.
     * 
     * @access public
     * 
     * @param string path to the XHTML template
     * 
     * @throws plcTemplateSystemException
     * 
     * @return bool TRUE on success and FALSE on FAIL
     *                                      
     */         
     
    public function SetPagePath($usrPath = '')
        {
        if (empty($usrPath) === TRUE){return FALSE;}
        
        if (is_string($usrPath) === FALSE)
            {
            throw new plcTemplateSystemException('Page path parameter is not a string.', 11101, null, 'Page path parameter must be string (pointing to xhtml template).');           
            }
        
        if (is_readable($usrPath) === FALSE)
            {
            throw new plcTemplateSystemException('Page template is not readable or does not exist.', 11102, null, 'Page template is not readable: "'.$usrPath.'"'); 
            } 
            
        $this->PagePath = $usrPath;
        return TRUE;
        }
     
        
    /**
     * Function that used to set variables for the current XHTML template.
     * 
     * Simple function that sets variables for the current XHTML template.
     * 
     * @access public
     * 
     * @param array user defined variables for the current XHTML template
     * 
     * @throws plcTemplateSystemException
     * 
     * @return bool TRUE on success and FALSE on FAIL
     *                                      
     */  
        
    public function SetPageVars($usrTempVars)
        {
        if (is_array($usrTempVars) === FALSE)
            {           
            throw new plcTemplateSystemException('Template variables parameter is not array.', 11103, null, 'Template variables parameter must be array.'); 
            }
        
        if (count($usrTempVars) <= 0) {return FALSE;}
        
        $this->TempVars = $usrTempVars;      
        return TRUE;
        }
        
    /**
     * Function that used to set value that indicates whether to use caching mechanism.
     * 
     * Simple function that sets value that indicates whether to use caching mechanism.
     * 
     * @access public
     * 
     * @param bool TRUE to use caching mechanism and FALSE to not to use
     *                                       
     */          
        
    public function SetUseCache($usrUseCache)
        {
        if ($usrUseCache === TRUE) {$this->UseCache = TRUE;}
        if ($usrUseCache === FALSE){$this->UseCache = FALSE;}
        }
        
    /* Set methods ends here */    
    }

?>
