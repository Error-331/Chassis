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
 * Class plcSmplFileCache is a part of PHP framework - Chassis.   
 * 
 * @package     Chassis
 * @author      Selihov Sergei Stanislavovich <red331@mail.ru> 
 * @copyright   Copyright (c) 2010-2011 Selihov Sergei Stanislavovich.
 * @license     http://www.gnu.org/licenses/gpl.html  GNU GENERAL PUBLIC LICENSE (Version 3)
 *    
 */
 
/**
 * Classes for work with cache.
 *  
 * @subpackage Cache
 * @author Selihov Sergei Stanislavovich <red331@mail.ru>   
 */
 
/**
 * Documents the plcSmplFileCache class.
 * 
 * Following class is implementation of simple caching mechainsme.
 *   
 * @subpackage plcSmplFileCache
 * @author Selihov Sergei Stanislavovich <red331@mail.ru>   
 */

require_once('Chassis/ErrorHandling/plcTemplateSystemException.php');
require_once('Chassis/Registry/plcGlobRegistry.php');

class plcSmplFileCache
    {
    /**
     * @access protected
     * @var string folder(directory where all the cache files will be stored).
     */	      
    
    protected $CacheFolder = '';
    
    /**
     * @access protected
     * @var string cache lifetime.
     */	       
    
    protected $LifeTime = '+ 20 seconds';
    
    /**
     * @access protected
     * @var bool indicates whether process of caching is being held or not.
     */	    
    
    protected $IsCaching = FALSE;
    
    /* Core methods starts here */
    
    /**
     * Main constructor function.
     * 
     * Main constructor function initialises the work of the object.
     * 
     * @access public
     * 
     * @param string path to the folder where all the cache will be stored
     * @param string cache lifetime, uses format similar to the strtotime() php function
     * 
     * If corresponding parameters will not be present, default parameters from global registry will be used. 
     * 
     * @throws plcChassisException
     *                                      
     */     
    
    public function __construct($usrCacheFld = '', $usrCacheLTime = '') 
        {
        if(empty($usrCacheFld) === TRUE) {$this->SetCacheFolder(plcGlobRegistry::GetOption('default_cache_folder'));}
        else {$this->SetCacheFolder($usrCacheFld);}
        
        if(empty($usrCacheLTime) === TRUE) {$this->SetCacheLifeTime(plcGlobRegistry::GetOption('default_cache_lifetime'));}
        else {$this->SetCacheLifeTime($usrCacheLTime);}        
        }
      
    public function __destruct() 
        {
        $this->EndCaching();
        }
        
    /**
     * Method that indicates whether there is cached content for the current URL or not.
     * 
     * Simple function that indicates whether there is cached content for the current URL or not.
     * 
     * @access public
     * 
     * @throws plcChassisException
     * 
     * @return bool TRUE if there is a cached copy which is not outdated and FALSE otherwise
     *                                      
     */           
        
    public function IsCached()
        {     
        $tmpCDir = '';  
              
        $tmpFileTime = 0;     
        $tmpCDirCnt = 0;
        
        $Counter1 = 0;
        
        if (empty($this->CacheFolder) === TRUE)
          {
          throw new plcChassisException('Cache storage folder is not set.', 11106, null ,'Can not work with cache without cache storage directory.');
          }
          
        if (empty($this->CacheFolder) === TRUE)
          {
          throw new plcChassisException('Cache lifetime is not set.', 11110, null ,'Can not work with cache without cache lifetime duration value.');
          }          
          
        $tmpCDir = $this->CacheFolder; 
        
        $tmpCDirCnt = strlen($tmpCDir);
        if ($this->CacheFolder[$tmpCDirCnt - 1] == '/' || $this->CacheFolder[$tmpCDirCnt - 1] == '\\') {$tmpCDir = substr($tmpCDir, 0, -1);}
        
        $tmpCDir .= DIRECTORY_SEPARATOR.md5($this->GetCurrentURL()).'html';
        
        if (file_exists($tmpCDir) === FALSE)
            {
            return FALSE;        
            } 
        else
            {
            $tmpFileTime = filemtime($tmpCDir);
            if ($tmpFileTime === FALSE)
                {
                throw new plcChassisException('Can not get date of creation of the file.', 11107, null, 'Creation date of the file "'.$tmpCDir.'" is undefined.');
                }
                
            if (time() >= strtotime($this->LifeTime, $tmpFileTime)) 
                {
                return FALSE;
                } 
              else
                {
                return TRUE;
                } 
            }                   
        }
        
    /**
     * Method that starts caching mechanism forcing it to catch all the following content in the cache buffer.
     * 
     * All the data followed after StartCahing() and EndCaching() will be stored in the corresponding
     * cache file. 
     * 
     * @access public
     * 
     * @throws plcChassisException
     * 
     * @return bool TRUE on success
     *                                      
     */         
        
    public function StartCahing()
        {
        if (ob_start() === FALSE)
          {
          throw new plcChassisException('Can not start caching mechanism.', 11108, null, 'Internal error occurred while trying to start caching mechanism.');
          }
          
        $this->IsCaching = TRUE;  
        }
        
    /**
     * Method that stops caching mechanism forcing it to store all data in the cache file.
     * 
     * All the data followed after StartCahing() and EndCaching() will be stored in the corresponding
     * cache file. 
     * 
     * @access public
     * 
     * @throws plcChassisException
     * 
     * @return bool TRUE on success and FALSE on FAIL
     *                                      
     */         
        
    public function EndCaching()
        {
        if ($this->IsCaching === FALSE) {return FALSE;}
        $this->IsCaching = FALSE;
              
        $tmpCDir = '';                
        $tmpFileTime = 0;     
        $tmpCDirCnt = 0;
        
        $Counter1 = 0;
        
        if (empty($this->CacheFolder) === TRUE)
          {
          throw new plcChassisException('Cache storage folder is not set.', 11106, null ,'Can not work with cache without cache storage directory.');
          }
                 
        if (empty($this->CacheFolder) === TRUE)
          {
          throw new plcChassisException('Cache lifetime is not set.', 11110, null ,'Can not work with cache without cache lifetime duration value.');
          }             
          
        $tmpCDir = $this->CacheFolder;        
        $tmpCDirCnt = strlen($tmpCDir);
        
        if ($this->CacheFolder[$tmpCDirCnt - 1] == '/' || $this->CacheFolder[$tmpCDirCnt - 1] == '\\') {$tmpCDir = substr($tmpCDir, 0, -1);}
        
        $tmpCDir .= DIRECTORY_SEPARATOR.md5($this->GetCurrentURL()).'html';
        
        if (file_exists($tmpCDir) === FALSE)
            {
            file_put_contents($tmpCDir, ob_get_contents());        
            }
        else
            {
            $tmpFileTime = filemtime($tmpCDir);
            if ($tmpFileTime === FALSE)
                {
                throw new plcChassisException('Can not get date of creation of the file.', 11107, null, 'Creation date of the file "'.$tmpCDir.'" is undefined.');
                }
                
            if (time() >= strtotime($this->LifeTime, $tmpFileTime)) 
                {
                file_put_contents($tmpCDir, ob_get_contents());
                } 
            } 
              
        ob_end_flush();
        return TRUE;
        }
        
    /**
     * Method that displays cached content and stops execution of the script.
     * 
     * Simple function that displays cached content and stops execution of the script thus preventing it
     * from showing dublicate content.
     * 
     * @access public
     * 
     * @throws plcChassisException
     *                                      
     */          
        
    public function ShowCachedExit()
        {
        $this->ShowCached();
        exit();
        }
        
    /**
     * Method that displays cached content.
     * 
     * Simple function that displays cached content.
     * 
     * @access public
     * 
     * @throws plcChassisException
     *                                      
     */         
        
    public function ShowCached()
        {
        $tmpCDir = '';  
              
        $tmpFileTime = 0;     
        $tmpCDirCnt = 0;
        
        $Counter1 = 0;
        
        if (empty($this->CacheFolder) === TRUE)
            {
            throw new plcChassisException('Cache storage folder is not set.', 11106, null ,'Can not work with cache without cache storage directory.');
            }
          
        $tmpCDir = $this->CacheFolder; 
        
        $tmpCDirCnt = strlen($tmpCDir);
        if ($this->CacheFolder[$tmpCDirCnt - 1] == '/' || $this->CacheFolder[$tmpCDirCnt - 1] == '\\') {$tmpCDir = substr($tmpCDir, 0, -1);}
        
        $tmpCDir .= DIRECTORY_SEPARATOR.md5($this->GetCurrentURL()).'html';
        
        if (file_exists($tmpCDir) === FALSE)
            {
            throw new plcChassisException('Cache file not found.', 11108, null, 'Cache file for the URL "'.$this->GetCurrentURL().'" is not found.');        
            } 
        else
            {
            if (is_readable($tmpCDir) === FALSE)
                {
                throw new plcChassisException('Cache file is not readable.', 11109, null, 'Cache file for the URL "'.$this->GetCurrentURL().'" is not readable.');
                }              

            require_once($tmpCDir);
            }         
        }    
        
    /* Core methods ends here */
        
    /* Get methods starts here */
            
    /**
     * Method that returns current cache storage folder.
     * 
     * Simple function that returns current cache storage folder.
     * 
     * @access public
     * 
     * @return string path to the current cache storage folder.
     *                                      
     */           
        
    public function GetCacheFolder()
        {
        return $this->CacheFolder;
        }
        
    /**
     * Method that returns current lifetime of the cache.
     * 
     * Simple function that returns current lifetime of the cache.
     * 
     * @access public
     * 
     * @return string current lifetime of the cache (format is similar to the strtotime() php function).
     *                                      
     */          
        
    public function GetCacheLifeTime()
        {
        return $this->LifeTime;
        }
        
    /**
     * 
     * Method that returns current request URL.
     * 
     * Simple function that returns current request URL.
     * 
     * @access public
     * 
     * @return string current request URL.
     *                                      
     */         
        
    public function GetCurrentURL()
        { 
        $tmpPageURL = 'http';
        if (@$_SERVER["HTTPS"] == "on") 
            {
            $tmpPageURL .= "s";
            }
            
        $tmpPageURL .= "://";
        
        if ($_SERVER["SERVER_PORT"] != "80") 
            {
            $tmpPageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
            } 
        else 
            {
            $tmpPageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
            }
            
        return $tmpPageURL;         
        }        
                
    /* Get methods ends here */  
        
    /* Set methods starts here */
        
    /**
     * Method that used to set current cache folder.
     * 
     * Simple function that used to set current cache folder.
     * 
     * @access public
     * 
     * @param string path to the cache storage folder
     * 
     * @throws plcChassisException
     * 
     * @return bool TRUE on success and FALSE on FAIL
     *                                      
     */         
        
    public function SetCacheFolder($usrFolder = '')
        {
        if (is_string($usrFolder) === FALSE)
            {
            throw new plcChassisException('Cache folder parameter must be a string.', 11101, null ,'User provided invalid cache folder path parameter.');
            }   
            
        if (empty($usrFolder) === TRUE) {return FALSE;} 
        
        if (is_readable($usrFolder) === FALSE)
            {
            throw new plcChassisException('Cache folder is not readable.', 11102, null ,'Cache folder must be readable in order to be able load cached content.');
            }
            
        if (is_writable($usrFolder) === FALSE)
            {
            throw new plcChassisException('Cache folder is not writable.', 11103, null ,'Cache folder must be writable in order to be able to save cached content.');
            }            
                              
        $this->CacheFolder = $usrFolder;
        return TRUE;   
        } 
        
    /**
     * Method that used to set current lifetime of the cache.
     * 
     * Simple function that used to set current lifetime of the cache.
     * 
     * @access public
     * 
     * @param string lifetime of the cache (format is similar to the strtotime() php function)
     * 
     * @throws plcChassisException
     * 
     * @return bool TRUE on success and FALSE on FAIL
     *                                      
     */         
        
    public function SetCacheLifeTime($usrLifeTime)
        {
        if (is_string($usrLifeTime) === FALSE)
            {
            throw new plcChassisException('Cache lifetime parameter is not a string.', 11104, null, 'User provided invalid cache lifetime parameter.');
            } 
                  
        if (empty($usrLifeTime) === TRUE) {return FALSE;}
        
        if (strtotime($usrLifeTime) === FALSE)
            {
            throw new plcChassisException('Cache lifetime parameter is not valid.', 11105, null, 'Cache lifetime parameter must follow strtotime() php function format.');
            }         
        
        $this->LifeTime = $usrLifeTime;
        return TRUE;
        }          
              
    /* Set methods ends here */    
    }

?>
