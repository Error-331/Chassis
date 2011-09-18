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
 * Class plcMySQLDBTableDataGateway is a part of PHP framework - Chassis.   
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
 * Documents the plcMySQLDBTableDataGateway class.
 * 
 * Following class is intended for simplification of tasks connected to the work with MYSQL database table.
 * This class implements Table Data Gateway Pattern.
 *   
 * @subpackage plcMySQLDBTableDataGateway
 * @author Selihov Sergei Stanislavovich <red331@mail.ru>   
 */

require_once('Chassis/DBConnectors/Interfaces.php');
require_once('Chassis/DBConnectors/plcMySQLIDBConnector.php');
require_once('Chassis/DBConnectors/plcMySQLDBRowDataGateway.php');
require_once('Chassis/ErrorHandling/plcChassisException.php');

class plcMySQLDBTableDataGateway
    {
    /**
     * @access protected
     * @var object instance of the database connector class 
     */	    
    
    protected $DBConnector = NULL;
    
    /**
     * @access protected
     * @var string name of the current table 
     */	  
    
    protected $TableName = NULL;
   
    /**
     * @access protected
     * @var array|string primary key of the current table 
     */	      
    
    protected $PrimKey = NULL;
    
    /**
     * @access protected
     * @var array of aliases for correspondence table fields
     */	      
    
    protected $FieldsAliases = array();
    
    /* Core methods starts here */
          
    /**
     * Constructor function.
     * 
     * Constructor function used to set current database connection object, current table name and its primary key.
     * 
     * @access public 
     *    
     * @param object instance of the database connector class.
     * @param string name of the current table.  
     * @param array|string primary key of the current table.
     * @param array of aliases for fields of current table.
     * 
     * @throws plcChassisException         
     *                         
     */      
    
    public function __construct($usrDBConnector, $usrTableName = '', $usrPrimKey = '', $usrAliases = array())
        {            
        if ($this->SetDBConnector($usrDBConnector) === FALSE)
            {
            throw new plcChassisException('Invalid database connector object.', 1501, null ,'User provided invalid connector object that do not supports necessary interfaces.');
            }        
        
        if ($this->SetTableName($usrTableName) === FALSE)
            {
            throw new plcChassisException('Invalid table name.', 1502, null ,'User provided invalid table name.');
            }
            
        if ($this->SetPrimKey($usrPrimKey) === FALSE)
            {
            throw new plcChassisException('Invalid primary key.', 1503, null ,'User provided invalid primary key for current table.');
            } 
            
        $this->SetFieldsAliases($usrAliases);
        }
    
    public function __destruct()
        {
        
        }
                        
    /* Core methods ends here */
        
    /* Table methods starts here */
     
    /**
     * Row insertion function.
     * 
     * Function that is used to insert one row of data to the current table.
     * 
     * @access public 
     *    
     * @param array of values to be inserted.
     * 
     * @throws plcChassisException 
     * 
     * @return bool returns TRUE on success and FALSE on fail.        
     *                         
     */  
        
    public function Insert($usrData = array())
        {   
        $tmpType = FALSE;
        
        $tmpTypes = '';
        $tmpKeys = '';
        $tmpVals = array();
        $tmpMarks = '';
        
        $Counter1 = 0;
        
        if (is_array($usrData) === FALSE) {return FALSE;}
        if (count($usrData) <= 0) {return FALSE;}
        
        foreach ($usrData as $tmpKey => $tmpVal)
            {
            if ($Counter1 > 0)
                {
                $tmpKeys .= ', ';
                $tmpMarks .= ', ';
                }
                
            $tmpType = $this->GetParamTypeLetter($tmpVal);
            if ($tmpType === FALSE)
                {
                throw new plcChassisException('Could not determine value type.', 1504, null ,'User provided invalid variable data to the GetParamTypeLetter() function.');
                }
                
            /* Aliases check starts here */
             
            if (array_key_exists($tmpKey, $this->FieldsAliases) === TRUE)
                {
                $tmpKeys .=  '`'.$this->FieldsAliases[$tmpKey].'`';
                }
            else
                {
                $tmpKeys .= '`'.$tmpKey.'`';
                }
                    
            /* Aliases check ends here */
                            
            $tmpTypes .= $tmpType;    
            $tmpVals[] = $tmpVal;
            $tmpMarks .= '?';
            
            $Counter1 += 1;
            }

        $this->DBConnector->SetPrepQuery('INSERT INTO `'.$this->TableName.'` ('.$tmpKeys.') VALUES('.$tmpMarks.')');
        if ($this->DBConnector->SetPrepQueryParamsArr($tmpTypes, $tmpVals) === FALSE){return FALSE;}
        
        return $this->DBConnector->ExecutePrepQuery();
        }
    
    /**
     * Row update function.
     * 
     * Function that is used to update rows of the current table.
     * 
     * @access public 
     *    
     * @param array of values to be updated with.
     * @param string condition - which rows will be updated.
     * @param mixed values for condition.
     * 
     * @throws plcChassisException 
     * 
     * @return bool returns TRUE on success and FALSE on fail.        
     *                         
     */ 
        
    public function Update($usrData = array(), $usrCond = '', $usrVals = array())
        {  
        $tmpType = FALSE;
        
        $tmpTypes = '';
        $tmpKeys = '';
        $tmpVals = array();
        
        $Counter1 = 0;
        
        if (is_array($usrData) === FALSE) {return FALSE;}
        if (count($usrData) <= 0) {return FALSE;}
        
        foreach ($usrData as $tmpKey => $tmpVal)
            {
            if ($Counter1 > 0)
                {
                $tmpKeys .= ', ';
                }
                
            $tmpType = $this->GetParamTypeLetter($tmpVal);
            if ($tmpType === FALSE)
                {
                throw new plcChassisException('Could not determine value type.', 1504, null ,'User provided invalid variable data to the GetParamTypeLetter() function.');
                }
                
            /* Aliases check starts here */
             
            if (array_key_exists($tmpKey, $this->FieldsAliases) === TRUE)
                {
                $tmpKeys .= '`'.$this->FieldsAliases[$tmpKey].'` = ?';
                }
            else
                {
                $tmpKeys .= '`'.$tmpKey.'` = ?';
                }
                    
            /* Aliases check ends here */                
                            
            $tmpTypes .= $tmpType;    
            $tmpVals[] = $tmpVal;
            
            $Counter1 += 1;
            }  
        
        /* Where condition add starts here */    
         
        if (is_array($usrVals) === FALSE)
            {
            $usrVals = array(0 => $usrVals);     
            }
            
        if (empty($usrCond) !== TRUE)
            {
            if (substr_count($usrCond, '?') != count($usrVals))
                {
                throw new plcChassisException('Invalid arguments count.', 1505, null ,'Count of arguments in condition does not much count of values.');
                }
            else
                {
                for ($Counter1 = 0; $Counter1 < count($usrVals); $Counter1++)
                    {
                    $tmpType = $this->GetParamTypeLetter($usrVals[$Counter1]);
                    if ($tmpType === FALSE)
                        {
                        throw new plcChassisException('Could not determine value type.', 1504, null ,'User provided invalid variable data to the GetParamTypeLetter() function.');
                        }  
                        
                    $tmpTypes .= $tmpType;     
                    }
                
                $tmpKeys .= ' WHERE '.$usrCond;
                $tmpVals = array_merge ($tmpVals, $usrVals); 
                }
            }
            
        /* Where condition add ends here */    

        $this->DBConnector->SetPrepQuery('UPDATE `'.$this->TableName.'` SET '.$tmpKeys);
        if ($this->DBConnector->SetPrepQueryParamsArr($tmpTypes, $tmpVals) === FALSE){return FALSE;}
        
        return $this->DBConnector->ExecutePrepQuery();       
        }
    
    /**
     * Row delete function.
     * 
     * Function that is used to delete rows of the current table.
     * 
     * @access public 
     *    
     * @param string condition - which rows will be deleted.
     * @param array values for condition.
     * 
     * @throws plcChassisException 
     * 
     * @return bool returns TRUE on success and FALSE on fail.        
     *                         
     */ 
        
    public function Delete($usrCond = '', $usrVals = array())
        { 
        $tmpType = FALSE;
        
        $tmpTypes = '';
        $tmpKeys = '';
        $tmpVals = array();
        
        $Counter1 = 0;        
        
        if (empty($usrCond) !== TRUE)
            {
            if (substr_count($usrCond, '?') != count($usrVals))
                {
                throw new plcChassisException('Invalid arguments count.', 1505, null ,'Count of arguments in condition does not much count of values.');
                }
            else
                {
                for ($Counter1 = 0; $Counter1 < count($usrVals); $Counter1++)
                    {
                    $tmpType = $this->GetParamTypeLetter($usrVals[$Counter1]);
                    if ($tmpType === FALSE)
                        {
                        throw new plcChassisException('Could not determine value type.', 1504, null ,'User provided invalid variable data to the GetParamTypeLetter() function.');
                        }  
                        
                    $tmpTypes .= $tmpType;     
                    }
                
                $tmpKeys .= ' WHERE '.$usrCond;
                $tmpVals = array_merge ($tmpVals, $usrVals); 
                }
            }
        else
            {
            return FALSE;
            }
            
        $this->DBConnector->SetPrepQuery('DELETE FROM `'.$this->TableName.'`'.$tmpKeys);
        if ($this->DBConnector->SetPrepQueryParamsArr($tmpTypes, $tmpVals) === FALSE){return FALSE;}

        return $this->DBConnector->ExecutePrepQuery();    
        }
    
        
    /**
     * Row selection function in form of plcMySQLDBRowDataGateway object.
     * 
     * Function that is used to select row from the current table in form of plcMySQLDBRowDataGateway object.
     * 
     * @access public 
     *    
     * @param int|float|string|array values for current primary key.
     * @param string|array custom key based on which search will be performed.
     * 
     * @return object returns plcMySQLDBRowDataGateway object on success and FALSE on fail.        
     *                         
     */        
         
    public function Find($usrVal = '', $usrCustKey = '')
        {   
        $tmpPrimKey = '';
        
        /* Primary key set starts here */
        
        if (empty($usrCustKey) === TRUE) {$tmpPrimKey = $this->PrimKey;}
        else
            {
            $tmpPrimKey = $usrCustKey;
            }
            
        /* Primary key set ends here */        
   
        try
            {
            return new plcMySQLDBRowDataGateway($this->DBConnector, $this->TableName, $tmpPrimKey, $usrVal, $this->FieldsAliases);
            }
        catch(plcChassisException $usrError)
            {
            return FALSE;
            }      
        }
     
    /**
     * Row selection function in form of associative array.
     * 
     * Function that is used to select row from the current table in form of associative array.
     * 
     * @access public 
     *    
     * @param int|float|string|array values for current primary key.
     * @param string|array custom key based on which search will be performed.
     * 
     * @throws plcChassisException 
     * 
     * @return array returns associative array on success and FALSE on fail.        
     *                         
     */ 
        
    public function FindAssoc($usrVals = '', $usrCustKey = '')
        {
        $tmpType = FALSE;
        
        $tmpTypes = '';
        $tmpKeys = '';
        
        $tmpPrimKey = '';
        
        $Counter1 = 0;
        
        /* Primary key set starts here */
        
        if (is_string($usrCustKey) === TRUE)
            {
            if (empty($usrCustKey) === TRUE) {$tmpPrimKey = $this->PrimKey;}
            else
                {
                if (array_key_exists($usrCustKey, $this->FieldsAliases) === TRUE)
                    {
                    $tmpPrimKey = $this->FieldsAliases[$usrCustKey];
                    }
                else 
                    {
                    $tmpPrimKey = $usrCustKey;
                    }
                }
            }
        else if (is_array($usrCustKey) === TRUE)
            {
            for ($Counter1 = 0; $Counter1 < count($usrCustKey); $Counter1++)
                {
                if (array_key_exists($usrCustKey[$Counter1], $this->FieldsAliases) === TRUE)
                    {
                    $tmpPrimKey = $this->FieldsAliases[$usrCustKey[$Counter1]];
                    }
                else 
                    {
                    $tmpPrimKey[] = $usrCustKey[$Counter1];
                    }
                }   
            }
        else
            {
            $tmpPrimKey = $this->PrimKey;
            }
            
        /* Primary key set ends here */
        
        if (empty($usrVals) === TRUE) {return FALSE;}
        
        if (is_array($tmpPrimKey) === TRUE && is_array($usrVals) === TRUE)
            {
            if (count($tmpPrimKey) != count($usrVals)) {return FALSE;}
            
            for ($Counter1 = 0; $Counter1 < count($tmpPrimKey); $Counter1++)
                {
                if ($Counter1 > 0) {$tmpKeys .= ' AND ';}
                
                $tmpType = $this->GetParamTypeLetter($usrVals[$Counter1]);
                if ($tmpType === FALSE)
                    {
                    throw new plcChassisException('Could not determine value type.', 1504, null ,'User provided invalid variable data to the GetParamTypeLetter() function.');
                    }   
                    
                $tmpTypes .= $tmpType;                
                $tmpKeys .= '`'.$tmpPrimKey[$Counter1].'` = ?';
                }
            }
        else if (is_array($tmpPrimKey) === FALSE && is_array($usrVals) === FALSE)
            {
            $tmpType = $this->GetParamTypeLetter($usrVals);
            if ($tmpType === FALSE)
                {
                throw new plcChassisException('Could not determine value type.', 1504, null ,'User provided invalid variable data to the GetParamTypeLetter() function.');
                } 
                
            $tmpTypes .= $tmpType;                
            $tmpKeys .= '`'.$tmpPrimKey.'` = ?';
            $usrVals = array($usrVals);
            }
        else
            {
            return FALSE;
            }

        $this->DBConnector->SetPrepQuery('SELECT * FROM `'.$this->TableName.'` WHERE '.$tmpKeys);
        if ($this->DBConnector->SetPrepQueryParamsArr($tmpTypes, $usrVals) === FALSE){return FALSE;}
            
        return $this->DBConnector->GetPrepResultRow();   
        }
           
    /* Table methods ends here */
        
    /* Get methods starts here */
        
    /**
     * Internal data determination function.
     * 
     * Function that is used to determine allowed data types.
     * 
     * @access protected 
     *    
     * @param mixed user variable data.
     * 
     * @return string|bool returns string on success and FALSE on fail.        
     *                         
     */ 
        
    protected function GetParamTypeLetter($usrParam = '')
        {
        if (is_int($usrParam) === TRUE) {return 'i';}
        else if (is_float($usrParam) === TRUE) {return 'd';}
        else if (is_string($usrParam) === TRUE) {return 's';}
        else {return FALSE;}
        }        

    /**
     * Function that returns current database connector.
     * 
     * Function that is used to return current database connector.
     * 
     * @access public
     *    
     * @return object current database connector object.        
     *                         
     */         
        
    public function GetDBConnector()
        {
        return $this->DBConnector;
        }
        
    /**
     * Function that returns current table name.
     * 
     * Function that is used to return current table name.
     * 
     * @access public
     *    
     * @return string current table name.        
     *                         
     */  
    
    public function GetTableName()
        {
        return $this->TableName;
        }  
        
    /**
     * Function that returns current table primary key.
     * 
     * Function that is used to return current table primary key.
     * 
     * @access public
     *    
     * @return string current table primary key.        
     *                         
     */  
        
    public function GetPrimKey()
        {
        return $this->PrimKey;
        }
        
    /**
     * Function that used to return ID generated for an AUTO_INCREMENT column by the previous query.
     *
     * Simple function that returns ID generated for an AUTO_INCREMENT column by the previous query.
     *
     * @access public
     *
     * @throws plcChassisException
     *
     * @return int|Bool returns ID for an AUTO_INCREMENT column by the previous query on succes and FALSE if ID was not generated.
     */

    public function GetLastInsertId()
        {
        return $this->DBConnector->GetLastInsertId();
        } 
        
    /**
     * Function that returns aliases of fields of current table.
     * 
     * Function that is used to return aliases of fields of current table.
     * 
     * @access public
     *    
     * @return array of aliases.        
     *                         
     */         
        
    public function GetFieldsAliases()
        {        
        return $this->FieldsAliases;
        } 
        
    /**
     * Function that returns field name by its alias.
     * 
     * Function that is used to return field name by its alias.
     * 
     * @access public
     * 
     * @param string alias name.
     *    
     * @return string name of the field if the alias is found and empty string if it is not found.        
     *                         
     */           
        
    public function GetFieldByAlias($usrAlias = '')
        {
        if (is_string($usrAlias) === FALSE || empty($usrAlias) === TRUE) {return '';}
        
        if (array_key_exists($usrAlias, $this->FieldsAliases) === TRUE)
            {
            return $this->FieldsAliases[$usrAlias];
            }
        else
            {
            return '';
            }
        }
        
    /* Get methods ends here */
                
    /* Set methods starts here */
    
    /**
     * Function that sets current database connector object.
     * 
     * Function that is used to set current database connector object.
     * Note that provided database connector object must implement pliSmplSQLDBConnector and pliPrepStmtSQLDBConnector interfaces.
     * 
     * @access public
     * 
     * @param object database connector object.
     *    
     * @return bool returns TRUE on success and FALSE on fail.        
     *                         
     */ 
        
    public function SetDBConnector($usrDBConnector = '')
        {
        $tmpImp = class_implements($usrDBConnector);
        
        if ($tmpImp === FALSE)
            {
            return FALSE; 
            } 
            
        if (in_array('pliSmplSQLDBConnector', $tmpImp) === FALSE || in_array('pliPrepStmtSQLDBConnector', $tmpImp) === FALSE)
            {
            return FALSE;
            }
        else
            {
            $this->DBConnector = $usrDBConnector;
            return TRUE;
            }
        }
        
    /**
     * Function that sets current table name.
     * 
     * Function that is used to set current table name.
     * 
     * @access public
     * 
     * @param string current table name.
     *    
     * @return bool returns TRUE on success and FALSE on fail.        
     *                         
     */ 
    
    public function SetTableName($usrTableName = '')
        {
        if (empty($usrTableName) === TRUE || is_null($this->DBConnector) === TRUE) {return FALSE;}
        
        $this->DBConnector->SetQuery('SHOW TABLES LIKE "'.$usrTableName.'"');
        
        if ($this->DBConnector->GetResult() === FALSE) {return FALSE;}
        else {$this->TableName = $usrTableName; return TRUE;}       
        }
          
    /**
     * Function that sets current table primary key.
     * 
     * Function that is used to set current table primary key.
     * 
     * @access public
     * 
     * @param array|string current table primary key.
     *    
     * @return bool returns TRUE on success and FALSE on fail.        
     *                         
     */ 
        
    public function SetPrimKey($usrPrimKey = '')
        {
        $tmpResult = NULL;
        $Counter1 = 0;
        
        if (is_null($this->TableName) === TRUE || is_null($this->DBConnector) === TRUE) {return FALSE;}
        
        if (isset($usrPrimKey) === FALSE || empty($usrPrimKey) === TRUE)
            {
            $this->DBConnector->SetQuery('SHOW INDEX FROM `'.$this->TableName.'`');
            $tmpResult = $this->DBConnector->GetResultAssoc();
        
            if ($tmpResult === FALSE) {return FALSE;}
            else
                {
                if (count($tmpResult) == 1)
                    {
                    $this->PrimKey = $tmpResult[0]['Column_name'];
                    return TRUE;
                    }
                    
                $this->PrimKey = array();
                
                for ($Counter1 = 0; $Counter1 < count($tmpResult); $Counter1++)
                    {
                    if ($tmpResult[$Counter1]['Key_name'] === 'PRIMARY')
                        {
                        $this->PrimKey[] = $tmpResult[$Counter1]['Column_name'];
                        }                    
                    }
                    
                if (count($this->PrimKey) == 1){$this->PrimKey = $this->PrimKey[0];}
                    
                return TRUE;    
                }
            }
        else
            {
            if (is_string($usrPrimKey) === TRUE) {$this->PrimKey = $usrPrimKey; return TRUE;}
            else if(is_array($usrPrimKey) === TRUE)
                {
                if (count($usrPrimKey) == 1) {$this->PrimKey = $usrPrimKey[0];}
                else
                    {
                    $this->PrimKey = $usrPrimKey;
                    }
                
                return TRUE;               
                }
            else {return FALSE;}
            }        
        }
        
    /**
     * Function that sets aliases for fields of current table.
     * 
     * Function that is used to set aliases for fields of current table.
     * 
     * @access public
     * 
     * @param array associaative array of aliases.
     *    
     * @return bool returns TRUE on success and FALSE on fail.        
     *                         
     */         
        
    public function SetFieldsAliases($usrFieldsAliases = array())
        {
        if (is_array($usrFieldsAliases) === FALSE) {return FALSE;}
        if (count($usrFieldsAliases) <= 0) {return FALSE;}
        
        $this->FieldsAliases = $usrFieldsAliases;
        
        if (is_array($this->PrimKey) === TRUE)
            {
            for ($Counter1 = 0; $Counter1 < count($this->PrimKey); $Counter1++)
                {
                if (array_key_exists($this->PrimKey[$Counter1], $this->FieldsAliases) === TRUE)
                    {
                    $this->PrimKey[$Counter1] = $this->FieldsAliases[$this->PrimKey[$Counter1]];
                    }  
                }          
            }
        else if (is_string($this->PrimKey) === TRUE)
            {
            if (array_key_exists($this->PrimKey, $this->FieldsAliases) === TRUE)
                {
                $this->PrimKey = $this->FieldsAliases[$this->PrimKey];
                }          
            }     
             
        return TRUE;
        }
        
    /* Set methods ends here */
    }

?>
