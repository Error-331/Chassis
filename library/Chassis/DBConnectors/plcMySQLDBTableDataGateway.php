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
     * 
     * @throws plcChassisException         
     *                         
     */      
    
    public function __construct($usrDBConnector, $usrTableName = '', $usrPrimKey = '')
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
                            
            $tmpTypes .= $tmpType;    
            $tmpKeys .= $tmpKey;
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
     * @param array of values to be inserted.
     * @param string condition - which rows will be updated.
     * @param array values for condition.
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
                            
            $tmpTypes .= $tmpType;    
            $tmpKeys .= $tmpKey.' = ?';
            $tmpVals[] = $tmpVal;
            
            $Counter1 += 1;
            }  
        
        /* Where condition add starts here */    
            
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
     * 
     * @throws plcChassisException 
     * 
     * @return object returns plcMySQLDBRowDataGateway object on success and FALSE on fail.        
     *                         
     */        
         
    public function Find($usrVal = '')
        {   
        return new plcMySQLDBRowDataGateway($this->DBConnector, $this->TableName, $this->PrimKey, $usrVal);
        }
     
    /**
     * Row selection function in form of associative array.
     * 
     * Function that is used to select row from the current table in form of associative array.
     * 
     * @access public 
     *    
     * @param int|float|string|array values for current primary key.
     * 
     * @throws plcChassisException 
     * 
     * @return array returns associative array on success and FALSE on fail.        
     *                         
     */ 
        
    public function FindAssoc($usrVals = '')
        {
        $tmpType = FALSE;
        
        $tmpTypes = '';
        $tmpKeys = '';
        
        $Counter1 = 0;
        
        if (empty($usrVals) === TRUE) {return FALSE;}
        
        if (is_array($this->PrimKey) === TRUE && is_array($usrVals) === TRUE)
            {
            if (count($this->PrimKey) != count($usrVals)) {return FALSE;}
            
            for ($Counter1 = 0; $Counter1 < count($this->PrimKey); $Counter1++)
                {
                if ($Counter1 > 0) {$tmpKeys .= ' AND ';}
                
                $tmpType = $this->GetParamTypeLetter($usrVals[$Counter1]);
                if ($tmpType === FALSE)
                    {
                    throw new plcChassisException('Could not determine value type.', 1504, null ,'User provided invalid variable data to the GetParamTypeLetter() function.');
                    }   
                    
                $tmpTypes .= $tmpType;                
                $tmpKeys .= $this->PrimKey[$Counter1].' = ?';
                }
            }
        else if (is_array($this->PrimKey) === FALSE && is_array($usrVals) === FALSE)
            {
            $tmpType = $this->GetParamTypeLetter($usrVals);
            if ($tmpType === FALSE)
                {
                throw new plcChassisException('Could not determine value type.', 1504, null ,'User provided invalid variable data to the GetParamTypeLetter() function.');
                } 
                
            $tmpTypes .= $tmpType;                
            $tmpKeys .= $this->PrimKey.' = ?';
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
                    $this->PrimKey[] = $tmpResult[$Counter1]['Column_name'];
                    }
                    
                return TRUE;    
                }
            }
        else
            {
            if (is_string($usrPrimKey) === TRUE) {$this->PrimKey = $usrPrimKey; return TRUE;}
            else if(is_array($usrPrimKey) === TRUE){$this->PrimKey = $usrPrimKey; return TRUE;}
            else {return FALSE;}
            }        
        }
        
    /* Set methods ends here */
    }

?>
