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
 * Class plcDBConnectorFactory is a part of PHP framework - Chassis.   
 * 
 * @package     Chassis
 * @author      Selihov Sergei Stanislavovich <red331@mail.ru> 
 * @copyright   Copyright (c) 2010-2011 Selihov Sergei Stanislavovich.
 * @license     http://www.gnu.org/licenses/gpl.html  GNU GENERAL PUBLIC LICENSE (Version 3)
 *    
 */
 
/**
 * Classes for work with databases.
 *  
 * @subpackage DBConnectors
 * @author Selihov Sergei Stanislavovich <red331@mail.ru>   
 */
 
/**
 * Documents the plcDBConnectorFactory class.
 * 
 * Following class implements a factory method for database connector classes of Chassis framework.
 *   
 * @subpackage plcDBConnectorFactory
 * @author Selihov Sergei Stanislavovich <red331@mail.ru>   
 */

require_once('Chassis/Registry/plcGlobRegistry.php');

class plcDBConnectorFactory
    {
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
     * Function that used to return required database connector object as singleton.
     *   
     * Simple function that returns required database connector object as singleton.
     * Possible values for $usrConName and corresponding settings for $usrConSettings:
     * 
     * MYSQL (host, user, password, database) 
     * MYSQLI (host, user, password, database)
     * 
     * SQLITE (filename, flags, encryptionkey)
     * SQLITE3 (filename, flags, encryptionkey)
     * 
     * MYSQLTABLE (dbconnector - optional, tablename, primkey, aliases - optional)
     * MYSQLROW (dbconnector - optional, tablename, primkey, primkeyval)
     * 
     * If corresponding settings will not be present in $usrConSettings parameter, default settings
     * from global registry will be used.
     * 
     * @access public
     * 
     * @param string database connector type name  
     * @param array database connector connection settings  
     * 
     * @throws plcChassisException     
     *          
     * @return bool|object returns FALSE on fail and database connector object on success.
     *                           
     */        
        
    public static function GetDBConnector($usrConName = '', $usrConSettings = array())  
        {
        $tmpReadyObject = FALSE;
              
        if (is_string($usrConName) === FALSE || empty($usrConName) === TRUE) {return FALSE;}
        $usrConName = strtoupper($usrConName);
        
        if (array_key_exists('host', $usrConSettings) === FALSE) {$usrConSettings['host'] = plcGlobRegistry::GetOption('default_mysql_host');}    
        if (array_key_exists('user', $usrConSettings) === FALSE) {$usrConSettings['user'] = plcGlobRegistry::GetOption('default_mysql_user');} 
        if (array_key_exists('password', $usrConSettings) === FALSE) {$usrConSettings['password'] = plcGlobRegistry::GetOption('default_mysql_password');}
        if (array_key_exists('database', $usrConSettings) === FALSE) {$usrConSettings['database'] = plcGlobRegistry::GetOption('default_mysql_database');}        
        
        if (array_key_exists('filename', $usrConSettings) === FALSE) {$usrConSettings['filename'] = plcGlobRegistry::GetOption('default_sqlite_file');}
        if (array_key_exists('flags', $usrConSettings) === FALSE) {$usrConSettings['flags'] = plcGlobRegistry::GetOption('default_sqlite_flags');}
        if (array_key_exists('encryptionkey', $usrConSettings) === FALSE) {$usrConSettings['encryptionkey'] = plcGlobRegistry::GetOption('default_sqlite_encryptionkey');}

        switch($usrConName)
            {
            case 'MYSQL':
            require_once('Chassis/DBConnectors/plcMySQLDBConnector.php');
                              
            $tmpReadyObject = plcMySQLDBConnector::GetInstance();
            
            if ($tmpReadyObject->IsConnected() === FALSE)
                { 
                $tmpReadyObject->ConnectToDB($usrConSettings['host'], $usrConSettings['user'], $usrConSettings['password']);
                $tmpReadyObject->SetDatabaseName($usrConSettings['database']);
                }   
            
            return $tmpReadyObject; 
            break;
        
            case 'MYSQLI':
            require_once('Chassis/DBConnectors/plcMySQLIDBConnector.php');
                
            $tmpReadyObject = plcMySQLIDBConnector::GetInstance();
            
            if ($tmpReadyObject->IsConnected() === FALSE)
                { 
                $tmpReadyObject->ConnectToDB($usrConSettings['host'], $usrConSettings['user'], $usrConSettings['password']);
                $tmpReadyObject->SetDatabaseName($usrConSettings['database']);
                }   
            
            return $tmpReadyObject; 
            break; 
            
            case 'SQLITE':
            require_once('Chassis/DBConnectors/plcSQLiteDBConnector.php');  
                
            $tmpReadyObject = plcSQLiteDBConnector::GetInstance();
            
            if ($tmpReadyObject->IsConnected() === FALSE)
                { 
                $tmpReadyObject->ConnectToDB($usrConSettings['filename'], $usrConSettings['flags'], $usrConSettings['encryptionkey']);
                }  
                
            return $tmpReadyObject; 
            break;            
            
            case 'SQLITE3':
            require_once('Chassis/DBConnectors/plcSQLite3DBConnector.php');  
                
            $tmpReadyObject = plcSQLite3DBConnector::GetInstance();
            
            if ($tmpReadyObject->IsConnected() === FALSE)
                { 
                $tmpReadyObject->ConnectToDB($usrConSettings['filename'], $usrConSettings['flags'], $usrConSettings['encryptionkey']);
                }  
                
            return $tmpReadyObject; 
            break;
            
            case 'MYSQLTABLE':
            require_once('Chassis/DBConnectors/plcMySQLDBTableDataGateway.php'); 

            if(array_key_exists('aliases', $usrConSettings) === FALSE) {$usrConSettings['aliases'] = array();}    
            if (array_key_exists('dbconnector', $usrConSettings) === FALSE)
                {
                $tmpReadyObject = new plcMySQLDBTableDataGateway(plcDBConnectorFactory::GetDBConnector('MYSQLI', $usrConSettings), 
                                                                 $usrConSettings['tablename'], 
                                                                 $usrConSettings['primkey'],
                                                                 $usrConSettings['aliases']
                                                                );                  
                }
           else
                {
                $tmpReadyObject = new plcMySQLDBTableDataGateway($usrConSettings['dbconnector'], 
                                                                 $usrConSettings['tablename'], 
                                                                 $usrConSettings['primkey'],
                                                                 $usrConSettings['aliases']
                                                                );                   
                }

           
            return $tmpReadyObject;
            break; 
            
            case 'MYSQLROW':
            require_once('Chassis/DBConnectors/plcMySQLDBRowDataGateway.php'); 
  
            if (array_key_exists('dbconnector', $usrConSettings) === FALSE)
                {
                $tmpReadyObject = new plcMySQLDBRowDataGateway(plcDBConnectorFactory::GetDBConnector('MYSQLI', $usrConSettings), 
                                                                 $usrConSettings['tablename'], 
                                                                 $usrConSettings['primkey'],
                                                                 $usrConSettings['primkeyval']
                                                                );                 
                }
           else
                {
                $tmpReadyObject = new plcMySQLDBRowDataGateway($usrConSettings['dbconnector'], 
                                                                 $usrConSettings['tablename'], 
                                                                 $usrConSettings['primkey'],
                                                                 $usrConSettings['primkeyval']
                                                                );                   
                }

           
            return $tmpReadyObject;
            break;               
                        
            default:
            return FALSE;
            break;
            }
        }
        
    /**
     * Function that used to return required new database connector object.
     *   
     * Simple function that returns required new database connector object.
     * Possible values for $usrConName and corresponding settings for $usrConSettings:
     * 
     * MYSQL (host, user, password, database) 
     * MYSQLI (host, user, password, database)
     * 
     * SQLITE (filename, flags, encryptionkey)
     * SQLITE3 (filename, flags, encryptionkey)
     * 
     * MYSQLTABLE (dbconnector - optional, tablename, primkey, aliases - optional)
     * MYSQLROW (dbconnector - optional, tablename, primkey, primkeyval)
     * 
     * If corresponding settings will not be present in $usrConSettings parameter, default settings
     * from global registry will be used.
     * 
     * @access public
     * 
     * @param string database connector type name  
     * @param array database connector connection settings  
     * 
     * @throws plcChassisException     
     *          
     * @return bool|object returns FALSE on fail and database connector object on success.
     *                           
     */        
        
    public static function GetNewDBConnector($usrConName = '', $usrConSettings = array())  
        {
        $tmpReadyObject = FALSE;
              
        if (is_string($usrConName) === FALSE || empty($usrConName) === TRUE) {return FALSE;}
        $usrConName = strtoupper($usrConName);
        
        if (array_key_exists('host', $usrConSettings) === FALSE) {$usrConSettings['host'] = plcGlobRegistry::GetOption('default_mysql_host');}    
        if (array_key_exists('user', $usrConSettings) === FALSE) {$usrConSettings['user'] = plcGlobRegistry::GetOption('default_mysql_user');} 
        if (array_key_exists('password', $usrConSettings) === FALSE) {$usrConSettings['password'] = plcGlobRegistry::GetOption('default_mysql_password');}
        if (array_key_exists('database', $usrConSettings) === FALSE) {$usrConSettings['database'] = plcGlobRegistry::GetOption('default_mysql_database');}        
        
        if (array_key_exists('filename', $usrConSettings) === FALSE) {$usrConSettings['filename'] = plcGlobRegistry::GetOption('default_sqlite_file');}
        if (array_key_exists('flags', $usrConSettings) === FALSE) {$usrConSettings['flags'] = plcGlobRegistry::GetOption('default_sqlite_flags');}
        if (array_key_exists('encryptionkey', $usrConSettings) === FALSE) {$usrConSettings['encryptionkey'] = plcGlobRegistry::GetOption('default_sqlite_encryptionkey');}
        
        switch($usrConName)
            {
            case 'MYSQL':
            require_once('Chassis/DBConnectors/plcMySQLDBConnector.php');
                              
            $tmpReadyObject = plcMySQLDBConnector::GetNewInstance();
            
            $tmpReadyObject->ConnectToDB($usrConSettings['host'], $usrConSettings['user'], $usrConSettings['password']);
            $tmpReadyObject->SetDatabaseName($usrConSettings['database']);  
            
            return $tmpReadyObject; 
            break;
        
            case 'MYSQLI':
            require_once('Chassis/DBConnectors/plcMySQLIDBConnector.php');
                
            $tmpReadyObject = plcMySQLIDBConnector::GetNewInstance();
            
            $tmpReadyObject->ConnectToDB($usrConSettings['host'], $usrConSettings['user'], $usrConSettings['password']);
            $tmpReadyObject->SetDatabaseName($usrConSettings['database']);
         
            return $tmpReadyObject; 
            break; 
            
            case 'SQLITE':
            require_once('Chassis/DBConnectors/plcSQLiteDBConnector.php');  
                
            $tmpReadyObject = plcSQLiteDBConnector::GetNewInstance();            
            $tmpReadyObject->ConnectToDB($usrConSettings['filename'], $usrConSettings['flags'], $usrConSettings['encryptionkey']);
                 
            return $tmpReadyObject; 
            break;            
            
            case 'SQLITE3':
            require_once('Chassis/DBConnectors/plcSQLite3DBConnector.php');  
                
            $tmpReadyObject = plcSQLite3DBConnector::GetNewInstance();          
            $tmpReadyObject->ConnectToDB($usrConSettings['filename'], $usrConSettings['flags'], $usrConSettings['encryptionkey']);
                
            return $tmpReadyObject; 
            break;
            
            case 'MYSQLTABLE':
            require_once('Chassis/DBConnectors/plcMySQLDBTableDataGateway.php'); 

            if(array_key_exists('aliases', $usrConSettings) === FALSE) {$usrConSettings['aliases'] = array();}    
            if (array_key_exists('dbconnector', $usrConSettings) === FALSE)
                {
                $tmpReadyObject = new plcMySQLDBTableDataGateway(plcDBConnectorFactory::GetDBConnector('MYSQLI'), 
                                                                 $usrConSettings['tablename'], 
                                                                 $usrConSettings['primkey'],
                                                                 $usrConSettings['aliases']
                                                                );                  
                }
           else
                {
                $tmpReadyObject = new plcMySQLDBTableDataGateway($usrConSettings['dbconnector'], 
                                                                 $usrConSettings['tablename'], 
                                                                 $usrConSettings['primkey'],
                                                                 $usrConSettings['aliases']
                                                                );                   
                }

           
            return $tmpReadyObject;
            break; 
            
            case 'MYSQLROW':
            require_once('Chassis/DBConnectors/plcMySQLDBRowDataGateway.php'); 
  
            if (array_key_exists('dbconnector', $usrConSettings) === FALSE)
                {
                $tmpReadyObject = new plcMySQLDBRowDataGateway(plcDBConnectorFactory::GetDBConnector('MYSQLI'), 
                                                                 $usrConSettings['tablename'], 
                                                                 $usrConSettings['primkey'],
                                                                 $usrConSettings['primkeyval']
                                                                );                 
                }
           else
                {
                $tmpReadyObject = new plcMySQLDBRowDataGateway($usrConSettings['dbconnector'], 
                                                                 $usrConSettings['tablename'], 
                                                                 $usrConSettings['primkey'],
                                                                 $usrConSettings['primkeyval']
                                                                );                   
                }

           
            return $tmpReadyObject;
            break;               
                        
            default:
            return FALSE;
            break;
            }
        }         
        
    /* Get methods ends here */     
    }
?>
