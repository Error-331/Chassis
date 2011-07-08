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
 * Class plcTranslitEncoder is a part of PHP framework - Chassis.   
 * 
 * @package     Chassis
 * @author      Selihov Sergei Stanislavovich <red331@mail.ru> 
 * @copyright   Copyright (c) 2010-2011 Selihov Sergei Stanislavovich.
 * @license     http://www.gnu.org/licenses/gpl.html  GNU GENERAL PUBLIC LICENSE (Version 3)
 *    
 */
 
/**
 * Classes for work with locales.
 *  
 * @subpackage Language
 * @author Selihov Sergei Stanislavovich <red331@mail.ru>   
 */
 
/**
 * Documents the plcTranslitEncoder class.
 *  
 * @subpackage plcTranslitEncoder
 * @author Selihov Sergei Stanislavovich <red331@mail.ru>   
 */

class plcTranslitEncoder
	{
	var $TranslitTable;

	function plcTranslitEncoder()
		{
		$this->SetSimpleTranslitTable();
		}

	function RussianToTranslit($usrString, $usrEncoding = 'UTF-8')
		{
		return strtr($usrString, $this->TranslytSymbolTable);
		}

	function SetSimpleTranslitTable()
		{
		$this->TranslytSymbolTable = array(
					    'a' => 'а',
					    'б' => 'b',
					    'в' => 'v',
					    'г' => 'g',
					    'д' => 'd',
					    'е' => 'e',
					    'ё' => 'e',
					    'ж' => 'j',
					    'з' => 'z',	
					    'и' => 'i',
					    'й' => 'y',	
					    'к' => 'k',	
					    'л' => 'l',	
					    'м' => 'm',	
					    'н' => 'n',	
					    'о' => 'o',	
					    'п' => 'p',	
					    'р' => 'r',
					    'с' => 's',
					    'т' => 't', 
					    'у' => 'u',
					    'ф' => 'f', 
					    'х' => 'h',
					    'ц' => 'c', 
					    'ч' => 'ch',
					    'ш' => 'sh',
					    'щ' => 'shch',
					    'ъ' => '',
					    'ы' => 'y',
					    'ь' => '',
					    'э' => 'e',
					    'ю' => 'yu',
					    'я' => 'ya',
					    'А' => 'A',
					    'Б' => 'B',
					    'В' => 'V',
					    'Г' => 'G',
					    'Д' => 'D',
					    'Е' => 'Е',
					    'Ё' => 'E',
					    'Ж' => 'J',
					    'З' => 'Z',	
					    'И' => 'I',
					    'Й' => 'Y',	
					    'К' => 'K',
					    'Л' => 'L',	
					    'М' => 'M',	
					    'Н' => 'N',	
					    'О' => 'O',	
					    'П' => 'P',
					    'Р' => 'R',
					    'C' => 'S',
					    'Т' => 'T', 
					    'У' => 'U',
					    'Ф' => 'F', 
					    'Х' => 'H',
					    'Ц' => 'C', 
					    'Ч' => 'Ch',
					    'Ш' => 'Sh',
					    'Щ' => 'Shch',
					    'Ъ' => '',
					    'Ы' => 'Y',
					    'Ь' => '',
					    'Э' => 'E',
					    'Ю' => 'Yu',
					    'Я' => 'Ya'
  			   		    );
		}

	function SetGoogleYandexTranslitTable()
		{
		$this->TranslytSymbolTable = array(
					    'a' => 'A',
					    'б' => 'B',
					    'в' => 'V',
					    'г' => 'G',
					    'д' => 'D',
					    'е' => 'E',
					    'ё' => 'E',
					    'ж' => 'J',
					    'з' => 'Z',	
					    'и' => 'I',
					    'й' => 'Y',	
					    'к' => 'K',	
					    'л' => 'L',	
					    'м' => 'M',	
					    'н' => 'N',	
					    'о' => 'O',	
					    'п' => 'P',	
					    'р' => 'R',
					    'с' => 'S',
					    'т' => 'T', 
					    'у' => 'U',
					    'ф' => 'F', 
					    'х' => 'H',
					    'ц' => 'C', 
					    'ч' => 'CH',
					    'ш' => 'SH',
					    'щ' => 'SHCH',
					    'ъ' => '',
					    'ы' => 'Y',
					    'ь' => '',
					    'э' => 'E',
					    'ю' => 'YU',
					    'я' => 'YA',
					    'А' => 'A',
					    'Б' => 'B',
					    'В' => 'V',
					    'Г' => 'G',
					    'Д' => 'D',
					    'Е' => 'E',
					    'Ё' => 'E',
					    'Ж' => 'J',
					    'З' => 'Z',	
					    'И' => 'I',
					    'Й' => 'Y',	
					    'К' => 'K',
					    'Л' => 'L',	
					    'М' => 'M',	
					    'Н' => 'N',	
					    'О' => 'O',	
					    'П' => 'P',
					    'Р' => 'R',
					    'C' => 'S',
					    'Т' => 'T', 
					    'У' => 'U',
					    'Ф' => 'F', 
					    'Х' => 'H',
					    'Ц' => 'C', 
					    'Ч' => 'CH',
					    'Ш' => 'SH',
					    'Щ' => 'SHCH',
					    'Ъ' => '',
					    'Ы' => 'Y',
					    'Ь' => '',
					    'Э' => 'E',
					    'Ю' => 'YU',
					    'Я' => 'YA'
  			   		    );
		}
	}

?>