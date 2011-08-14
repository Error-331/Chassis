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
 * Class plcMySQLDBRowDataGateway is a part of PHP framework - Chassis.   
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
 * Documents the plcMySQLDBRowDataGateway class.
 * 
 * Following class is intended for simplification of tasks connected to the work with MYSQL database row.
 * This class implements Row Data Gateway Pattern.
 *   
 * @subpackage plcMySQLDBRowDataGeteway
 * @author Selihov Sergei Stanislavovich <red331@mail.ru>   
 */

require_once('Chassis/DBConnectors/Interfaces.php');
require_once('Chassis/DBConnectors/plcMySQLIDBConnector.php');
require_once('Chassis/ErrorHandling/plcChassisException.php');

class plcMySQLDBRowDataGateway
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
     * @var array current table metadata (pulled usend DESCRIBE) 
     */	      
    
    protected $MetaData = NULL; 
    
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
     * @param array|string primary key value.
     * 
     * @throws plcChassisException         
     *                         
     */     
    
    public function __construct($usrDBConnector, $usrTableName = '', $usrPrimKey = '', $usrPrimKeyVal = '')
        {
        if ($this->SetDBConnector($usrDBConnector) === FALSE)
            {
            throw new plcChassisException('Invalid database connector object.', 1601, null ,'User provided invalid connector object that do not supports necessary interfaces.');
            }        
        
        if ($this->SetTableName($usrTableName) === FALSE)
            {
            throw new plcChassisException('Invalid table name.', 1602, null ,'User provided invalid table name.');
            }
            
        if ($this->SetPrimKey($usrPrimKey) === FALSE)
            {
            throw new plcChassisException('Invalid primary key.', 1603, null ,'User provided invalid primary key for current table.');
            } 
            
        if ($this->LoadRow($usrPrimKeyVal) === FALSE)
            {
            throw new plcChassisException('Invalid primary key value.', 1604, null ,'User provided invalid primary key value.');
            }              
        }
        
    public function __destruct()
        {
        
        }
        
    /**
     * Function that loads table metadata.
     * 
     * Function that is used to load current table metadata.
     * 
     * @access protected 
     * 
     * @throws plcChassisException 
     * 
     * @return bool returns TRUE on success and FALSE on fail.            
     *                         
     */ 
        
    protected function LoadTableMeta()
        {
        $tmpResult = FALSE;
        if (is_null($this->TableName) === TRUE || is_null($this->DBConnector) === TRUE) {return FALSE;}
        
        $this->DBConnector->SetQuery('DESCRIBE `'.$this->TableName.'`');
        $tmpResult = $this->DBConnector->GetResultAssoc('DESCRIBE `'.$this->TableName.'`');
        
        if ($tmpResult === FALSE) {return FALSE;}
        else
            {
            $this->MetaData = $tmpResult;
            return TRUE;
            }
        }
        
    /**
     * Function that loads row data.
     * 
     * Function that is used to load row data based on primary key data.
     * 
     * @access public
     * 
     * @param int|float|string|array values for current primary key. 
     * 
     * @throws plcChassisException 
     * 
     * @return bool returns TRUE on success and FALSE on fail.            
     *                         
     */ 
        
    public function LoadRow($usrVals = '')
        {
        $tmpResult = FALSE;
        
        $tmpType = FALSE;
        
        $tmpTypes = '';
        $tmpKeys = '';
              
        $Counter1 = 0;
        
        if (is_null($this->TableName) === TRUE || is_null($this->DBConnector || is_null($this->PrimKey) === TRUE || empty($usrVals)) === TRUE) {return FALSE;}
        if ($this->LoadTableMeta() === FALSE) {return FALSE;}
        
        if (is_array($this->PrimKey) === FALSE && is_array($usrVals) === FALSE) 
            {
            /* Row data load starts here */
            
            $tmpType = $this->GetParamTypeLetter($usrVals);
            if ($tmpType === FALSE)
                {
                throw new plcChassisException('Could not determine value type.', 1605, null ,'User provided invalid variable data to the GetParamTypeLetter() function.');
                } 
                
            $tmpTypes .= $tmpType;                
            $tmpKeys .= $this->PrimKey.' = ?';
            $usrVals = array($usrVals);            
      
            $this->DBConnector->SetPrepQuery('SELECT * FROM `'.$this->TableName.'` WHERE '.$tmpKeys);
            if ($this->DBConnector->SetPrepQueryParamsArr($tmpTypes, $usrVals) === FALSE){return FALSE;}
            
            $tmpResult = $this->DBConnector->GetPrepResultRow();
            if ($tmpResult === FALSE) {return FALSE;}
            
            /* Row data load ends here */
                
            for ($Counter1 = 0; $Counter1 < count($this->MetaData); $Counter1++)
                {
                if (is_string($tmpResult[$this->MetaData[$Counter1]['Field']]) === TRUE)
                    {
                    eval('$this->'.$this->MetaData[$Counter1]['Field'].' = "'.$tmpResult[$this->MetaData[$Counter1]['Field']].'";');
                    }
                else
                    {
                    eval('$this->'.$this->MetaData[$Counter1]['Field'].' = '.$tmpResult[$this->MetaData[$Counter1]['Field']].';');
                    }                                              
                }
                
            return TRUE;
            }
        else
            {
            /* Row data load starts here */
            
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
                
            $this->DBConnector->SetPrepQuery('SELECT * FROM `'.$this->TableName.'` WHERE '.$tmpKeys);
            if ($this->DBConnector->SetPrepQueryParamsArr($tmpTypes, $usrVals) === FALSE){return FALSE;}
            
            $tmpResult = $this->DBConnector->GetPrepResultRow();
            if ($tmpResult === FALSE) {return FALSE;}
            
            /* Row data load ends here */
            
            for ($Counter1 = 0; $Counter1 < count($this->MetaData); $Counter1++)
                {
                if (is_string($tmpResult[$this->MetaData[$Counter1]['Field']]) === TRUE)
                    {
                    eval('$this->'.$this->MetaData[$Counter1]['Field'].' = "'.$tmpResult[$this->MetaData[$Counter1]['Field']].'";');
                    }
                else
                    {
                    eval('$this->'.$this->MetaData[$Counter1]['Field'].' = '.$tmpResult[$this->MetaData[$Counter1]['Field']].';');
                    }                                              
                }
                
            return TRUE;
            }
            
        return FALSE;
        }
        
    /**
     * Function that saves row data to the database.
     * 
     * Function that is used to load row data to the database.
     * 
     * @access public
     *
     * @throws plcChassisException 
     * 
     * @return bool returns TRUE on success and FALSE on fail.            
     *                         
     */ 
        
    public function Save()
        {
        $tmpResult = FALSE;
       
        $tmpVal = NULL;
        $tmpType = FALSE;
        
        $tmpTypes = '';
        $tmpKeys = '';
              
        $Counter1 = 0;        
        
        if (is_null($this->TableName) === TRUE || is_null($this->DBConnector || is_null($this->PrimKey) === TRUE) === TRUE) {return FALSE;}
        if ($this->LoadTableMeta() === FALSE) {return FALSE;}
        
        for ($Counter1 = 0; $Counter1 < count($this->MetaData); $Counter1++)
            {
            if ($Counter1 > 0)
                {
                $tmpKeys .= ', ';
                }            
            
            eval('$tmpVal = $this->'.$this->MetaData[$Counter1]['Field'].';');    
    
            $tmpType = $this->GetParamTypeLetter($tmpVal);
            if ($tmpType === FALSE)
                {
                throw new plcChassisException('Could not determine value type.', 1605, null ,'User provided invalid variable data to the GetParamTypeLetter() function.');
                }
                            
            $tmpTypes .= $tmpType;    
            $tmpKeys .= $this->MetaData[$Counter1]['Field'].' = ?';
            $tmpVals[] = $tmpVal;                       
            }
            
        /* Where condition add starts here */    
        
        $tmpKeys .= ' WHERE ';    
            
        if (is_array($this->PrimKey) === TRUE)
            {
            for ($Counter1 = 0; $Counter1 < count($this->PrimKey); $Counter1++)
                {
                if ($Counter1 > 0)
                    {
                    $tmpKeys .= ' AND ';
                    }            
            
                eval('$tmpVal = $this->'.$this->PrimKey[$Counter1].';');    
    
                $tmpType = $this->GetParamTypeLetter($tmpVal);
                if ($tmpType === FALSE)
                    {
                    throw new plcChassisException('Could not determine value type.', 1605, null ,'User provided invalid variable data to the GetParamTypeLetter() function.');
                    }
                            
                $tmpTypes .= $tmpType;    
                $tmpKeys .= $this->PrimKey[$Counter1].' = ?';
                $tmpVals[] = $tmpVal;                       
                }            
            }
        else
            {
            eval('$tmpVal = $this->'.$this->PrimKey.';'); 
            
            $tmpType = $this->GetParamTypeLetter($tmpVal);
            if ($tmpType === FALSE)
                {
                throw new plcChassisException('Could not determine value type.', 1605, null ,'User provided invalid variable data to the GetParamTypeLetter() function.');
                }
                
            $tmpTypes .= $tmpType;    
            $tmpKeys .= $this->PrimKey.' = ?';
            $tmpVals[] = $tmpVal; 
            }
            
        /* Where condition add ends here */
            
        $this->DBConnector->SetPrepQuery('UPDATE `'.$this->TableName.'` SET '.$tmpKeys);
        if ($this->DBConnector->SetPrepQueryParamsArr($tmpTypes, $tmpVals) === FALSE){return FALSE;}
        
        return $this->DBConnector->ExecutePrepQuery();    
        }
        
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
