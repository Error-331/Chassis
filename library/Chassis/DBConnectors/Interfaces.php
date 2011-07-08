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
 * Various interfaces for DBConnectors subpackage.
 *
 */
 
interface pliSharedDBConnector
  {
  public static function GetInstance();
  public static function GetNewInstance();
  }

interface pliSmplSQLDBConnector
	{
	public function ReConnectToDB(); // throws plcChassisException
	public function ExecuteQuery();  // throws plcChassisException
	public function CloseDBLink();
	public function EscapeString($usrString); // throws plcChassisException

	public function GetQuery();
	
	public function GetLastInsertId(); // throws plcChassisException
  public function GetNumRows();
  public function GetNumFields();
   
	public function GetResultAssoc(); // throws plcChassisException
	public function GetResultRow(); // throws plcChassisException
	public function GetResult(); // throws plcChassisException	
	
	public function SetQuery($usrQuery);
	}
	
interface pliSQLBufferedResultIterator
  {
  public function ResetDataPointer();
  public function FreeResult();
  public function LoadResult(); // throws plcChassisException
  
  public function IsHasNextRow();
  public function IsHasNextField();
  
  public function NextRow();
  public function PrevRow();
  
  public function NextField();
  public function PrevField();
  
  public function GetRow();
  public function GetField();
  public function GetName();
  public function GetFieldLen();
  
  public function GetRowNum();
  public function GetFieldNum();
  }
	
interface pliSmplMYSQLDBConnector
  {
  public function ConnectToDB($usrServer, $usrUser, $usrPassword); // throws plcChassisException
  
  public function GetHost();
  public function GetUser();
  public function GetPassword();
	public function GetDatabaseName();	
  
	public function SetHost($usrHost); // throws plcChassisException
  public function SetUser($usrUser); // throws plcChassisException
  public function SetPassword($usrPassword); // throws plcChassisException
	public function SetDatabaseName($usrDBName); // throws plcChassisException	
  }
	
interface pliSmplSQLiteDBConnector
  {
  public function ConnectToDB($usrFileName, $usrFlags = "c", $usrEncryptionKey = NULL); // throws plcChassisException
  
  public function GetDBFile();
  public function GetOpenMode();
  public function GetCurEncKey();
  
  public function SetDBFile($usrDBFilePath); // throws plcChassisException
  public function SetOpenMode ($usrFlags = "c"); // throws plcChassisException
  public function SetCurEncKey($usrEncryptionKey);
  }
	
interface pliPrepStmtSQLDBConnector
	{	
	public function ExecutePrepQuery(); // throws plcChassisException
		
	public function GetPrepQuery();
	public function GetPrepQueryParams();
	
	public function GetPrepResultAssoc(); // throws plcChassisException
	public function GetPrepResultRow(); // throws plcChassisException
	public function GetPrepResult(); // throws plcChassisException	
	
  public function SetPrepQuery($usrQuery);
  public function SetPrepQueryParams();  // throws plcChassisException
	}

?>