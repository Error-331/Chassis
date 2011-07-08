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
 * Class plcAbstractXMLSitemapManipulator is a part of PHP framework - Chassis.   
 * 
 * @package     Chassis
 * @author      Selihov Sergei Stanislavovich <red331@mail.ru> 
 * @copyright   Copyright (c) 2010-2011 Selihov Sergei Stanislavovich.
 * @license     http://www.gnu.org/licenses/gpl.html  GNU GENERAL PUBLIC LICENSE (Version 3)
 *    
 */
 
/**
 * Classes for work with sitemap files.
 *  
 * @subpackage SitemapManipulation
 * @author Selihov Sergei Stanislavovich <red331@mail.ru>   
 */
 
/**
 * Documents the plcAbstractXMLSitemapManipulator class.
 * 
 * Following class is abstract class for plcAbstractXMLSitemapManipulator.
 *   
 * @subpackage plcAbstractXMLSitemapManipulator
 * @author Selihov Sergei Stanislavovich <red331@mail.ru>   
 */

abstract class plcAbstractXMLSitemapManipulator
	{
  abstract public function ResetImagePointer();
  abstract public function ResetSitemapManipulator();
  abstract public function ReadXMLFile($usrXMLFile);
  abstract public function SaveToXMLFile($usrXMLFile);
  
  abstract public function CurHasImages();
  
  abstract public function AddImageToURL($usrImgLoc, $usrImgCaption = '', $usrImgGEOLocation = '', $usrImgTitle = '', $usrImgLicense = '');
  abstract protected function GetNodeByName($usrName, $usrNodeList);
  abstract public function GetNextURLImage();
  abstract public function GetNextURL();
  abstract public function GetCurURLImageLOCValue();
  abstract public function GetCurLOCValue();
	}

?>