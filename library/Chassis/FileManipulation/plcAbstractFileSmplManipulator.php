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
 * Class plcAbstractFileSmplManipulator is a part of PHP framework - Chassis.   
 * 
 * @package     Chassis
 * @author      Selihov Sergei Stanislavovich <red331@mail.ru> 
 * @copyright   Copyright (c) 2010-2011 Selihov Sergei Stanislavovich.
 * @license     http://www.gnu.org/licenses/gpl.html  GNU GENERAL PUBLIC LICENSE (Version 3)
 *    
 */
 
/**
 * Classes for work with files and folders.
 *  
 * @subpackage FileManipulation
 * @author Selihov Sergei Stanislavovich <red331@mail.ru>   
 */
 
/**
 * Documents the plcAbstractFileSmplManipulator class.
 * 
 * Following class is abstract class for plcFileSmplManipulator.
 *   
 * @subpackage plcAbstractFileSmplManipulator
 * @author Selihov Sergei Stanislavovich <red331@mail.ru>   
 */

abstract class plcAbstractFileSmplManipulator
	{
	abstract protected function DeleteDirRecursion($usrDirContents);
	
	abstract public function IsDirEmpty($usrFileName);
	abstract public function IsLink($usrFileName);
	abstract public function IsFile($usrFileName);
	abstract public function IsDir($usrFileName);
	abstract public function IsExist($usrFileName);
	abstract public function IsReadable($usrFileName);
	abstract public function IsWritable($usrFileName);
	
	abstract public function DeleteFile($usrFileName);
	abstract public function DeleteDir($tmpDirPath);
	abstract public function DeleteDirRec($tmpDirPath);
	
	abstract public function GetFileExtension($usrFileName);
	abstract public function GetDirContents($usrFileName);
	abstract public function GetDirContentsRec($usrFileName);
	abstract public function GetFlattenFileListRec($tmpDirPath);
	}

?>