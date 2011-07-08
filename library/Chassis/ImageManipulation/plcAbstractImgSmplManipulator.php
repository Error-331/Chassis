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
 * Class plcAbstractImgSmplManipulator is a part of PHP framework - Chassis.   
 * 
 * @package     Chassis
 * @author      Selihov Sergei Stanislavovich <red331@mail.ru> 
 * @copyright   Copyright (c) 2010-2011 Selihov Sergei Stanislavovich.
 * @license     http://www.gnu.org/licenses/gpl.html  GNU GENERAL PUBLIC LICENSE (Version 3)
 *    
 */
 
/**
 * Classes for work with images.
 *  
 * @subpackage ImageManipulation
 * @author Selihov Sergei Stanislavovich <red331@mail.ru>   
 */
 
/**
 * Documents the plcAbstractImgSmplManipulator class.
 * 
 * Following class is abstract class for plcAbstractImgSmplManipulator.
 *   
 * @subpackage plcAbstractImgSmplManipulator
 * @author Selihov Sergei Stanislavovich <red331@mail.ru>   
 */

abstract class plcAbstractImgSmplManipulator
	{
	abstract public function EmptyPNGFilters();
	abstract public function IsExist($usrFileName);
	abstract public function IsReadable($usrFileName);
	abstract public function IsWritable($usrFileName);
	
	abstract public function ResizeImage($usrFileName, $usrNewFileName,$usrWidth, $usrHeight);
	
	abstract public function GetPNGFilters();
	abstract public function GetPNGQuality();
	abstract public function GetJPGQuality();
	abstract public function GetFileExtension($usrFileName);
	abstract public function GetImgCopy($usrFileName);
	
	abstract public function SetJPGQuality($usrQuality);

  abstract protected function WriteFilePNG($usrRes, $usrFileName);	
	abstract protected function WriteFileGIF($usrRes, $usrFileName);
	abstract protected function WriteFileJPG($usrRes, $usrFileName);
	abstract protected function WriteFileExt($usrRes, $usrFileName);
	}

?>