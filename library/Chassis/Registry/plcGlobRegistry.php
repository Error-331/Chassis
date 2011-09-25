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
 * Class plcGlobRegistry is a part of PHP framework - Chassis.   
 * 
 * @package     Chassis
 * @author      Selihov Sergei Stanislavovich <red331@mail.ru> 
 * @copyright   Copyright (c) 2010-2011 Selihov Sergei Stanislavovich.
 * @license     http://www.gnu.org/licenses/gpl.html  GNU GENERAL PUBLIC LICENSE (Version 3)
 *    
 */
 
/**
 * Classes for work with registries.
 *  
 * @subpackage Registry
 * @author Selihov Sergei Stanislavovich <red331@mail.ru>   
 */
 
/**
 * Documents the plcGlobRegistry class.
 * 
 * Following class is global registry class for Chassis framework.
 * 
 * Following registry options need to be set at startup for the proper work of chassis classes:
 * 
 * default_mysql_host - default mysql host
 * default_mysql_user - default mysql user
 * default_mysql_password - default mysql password
 * default_mysql_database - default mysql database
 * 
 * default_sqlite_file - default sqlite database file
 * default_sqlite_flags - default sqlite database flags
 * default_sqlite_encryptionkey - default sqlite encryption key
 * 
 * default_cache_folder - default folder for cache storage 
 * default_cache_lifetime - default cache lifetime duration
 *     
 * @subpackage plcGlobRegistry
 * @author Selihov Sergei Stanislavovich <red331@mail.ru>
 *    
 */


class plcGlobRegistry
    { 
    /**
     * @access private
     * @var array registry storage
     */	    
    
    private static $Storage = array();
    
    /* Core methods starts here */
    
    private function __construct() 
        {
        }
        
    public function __destruct()
        { 
        }
        
    /* Core methods ends here */
        
    /* Get methods starts here */   
        
    /**
     * Function that used to return registry value.
     *   
     * Simple function that return registry value. 
     * 
     * @access public
     * 
     * @param string registry key       
     *          
     * @return mixed returns FALSE on fail and registry value if the registry key is matched.
     *                           
     */           
        
    public static function GetOption($usrKey = '')
        {
        if (is_string($usrKey) === FALSE || empty($usrKey) === TRUE) {return FALSE;}
        if (array_key_exists($usrKey, self::$Storage) === TRUE){return self::$Storage[$usrKey];}
        }
        
    /* Get methods ends here */
        
    /* Set methods starts here */ 
        
    /**
     * Function that used to set registry value.
     *   
     * Simple function that sets (changes) current registry value. 
     * 
     * @access public
     * 
     * @param string registry key   
     * @param mixed registry value     
     *          
     * @return bool returns TRUE on success and FALSE on fail.
     *                           
     */        
      
    public static function SetOption($usrKey = '', $usrValue = '')
        {
        if (is_string($usrKey) === FALSE || empty($usrKey) === TRUE) {return FALSE;}
        if (isset($usrValue) === FALSE || empty($usrValue) === TRUE) {return FALSE;}
        
        self::$Storage[$usrKey] = $usrValue;
        return TRUE;
        }
        
    /* Set methods ends here */
    }

?>
