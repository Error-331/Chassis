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
 * Class plcPDFDocHelper is a part of PHP framework - Chassis.   
 * 
 * @package     Chassis
 * @author      Selihov Sergei Stanislavovich <red331@mail.ru> 
 * @copyright   Copyright (c) 2010-2011 Selihov Sergei Stanislavovich.
 * @license     http://www.gnu.org/licenses/gpl.html  GNU GENERAL PUBLIC LICENSE (Version 3)
 *    
 */
 
/**
 * Classes for working with PDF files.
 *  
 * @subpackage PDF
 * @author Selihov Sergei Stanislavovich <red331@mail.ru>   
 */
 
/**
 * Documents the plcPDFDocHelper class.
 *   
 * @subpackage plcPDFDocHelper
 * @author Selihov Sergei Stanislavovich <red331@mail.ru>   
 */
 
require_once('Chassis/ErrorHandling/plcChassisException.php');
require_once('Chassis/FileManipulation/plcFileSmplManipulator.php');

class plcPDFDocHelper
  {
  /**
   * @access protected
   * @var resource for current operations with PDF document
   */	  
  
  protected $PDFRes = NULL;

  /**
   * @access protected
   * @var bool indicates whether PDF file is open or not
   */	  
  
  protected $IsFileOpen = FALSE;
  
  /**
   * @access protected
   * @var string path to current PDF file
   */	  
  
  protected $CurFilePath = NULL;
  
  /**
   * @access protected
   * @var int current document page count
   */	  
  
  protected $PagesCount = 0;
  
  /**
   * @access protected
   * @var bool indicates whether PDF page is open or not
   */	
  
  protected $IsPageOpen = FALSE;

  /**
   * @access protected
   * @var float width of the current page
   */	
  
  protected $CurPageWidth = 0;
  
  /**
   * @access protected
   * @var float hight of the current page
   */	

  protected $CurPageHeight = 0;
  
  /**
   * @access protected
   * @var string name of the current font
   */	
  
  protected $CurFont = NULL;
  
  /**
   * @access protected
   * @var float size of the current font
   */	

  protected $CurFontSize = 0;
  
  /**
   * @access protected
   * @var array representation of CMYK color (stroke)
   */	     
   
  protected $ColorCMYKS = array('c' => 0, 'm' => 0, 'y' => 0, 'k' => 0);
  
  /**
   * @access protected
   * @var array representation of CMYK color (fill)
   */	     
   
  protected $ColorCMYKF = array('c' => 0, 'm' => 0, 'y' => 0, 'k' => 0);
  
  /**
   * @access protected
   * @var object of current file manipulator class
   */	  
  
  protected $FileMan = NULL;
  
  /* Core functions starts here */

  public function __construct()
    {
    $this->PDFRes = PDF_new();    
    $this->FileMan = new plcFileSmplManipulator(); 
    }
    
  public function __destruct()
    { 
    try
      {
      if (is_null($this->PDFRes) !== TRUE)
        {
        $this->CloseFile();
        PDF_delete($this->PDFRes);
        }
      } 
    catch (Exception $usrError)
      {
      var_dump($usrError);
      }
    } 
  
  public function ClosePage()
    {
    if ($this->IsFileOpen === TRUE && $this->IsPageOpen === TRUE)
      {
      pdf_end_page($this->PDFRes);
      $this->IsPageOpen = FALSE;
      }
    }
    
  public function CloseFile()
    {    
    $this->ClosePage();  
    
    if ($this->IsFileOpen === TRUE && $this->PagesCount > 0)
      {      
      PDF_close($this->PDFRes);
      
      $this->IsFileOpen = FALSE;
      $this->CurFilePath = NULL;
      $this->PagesCount = 0;  
      }
      
    return TRUE;
    }
    
  public function CreatePage($usrWidth = '', $usrHeight = '')
    {
    $tmpResult = FALSE;
    
    if (empty($usrWidth) === TRUE || empty($usrHeight) === TRUE) {return FALSE;}
    if ($this->IsFileOpen === FALSE) {return FALSE;}
    
    $this->ClosePage();   
    $tmpResult = PDF_begin_page($this->PDFRes, $usrWidth, $usrHeight);
    
    if ($tmpResult === FALSE) {return FALSE;}
    else 
      {
      $this->IsPageOpen = TRUE;
      $this->PagesCount += 1;
      
      $this->CurPageWidth = $usrWidth;
      $this->CurPageHeight = $usrHeight;
      
      return TRUE;
      }
    }
    
  public function CreatePageFormat($usrFormat = '')
    {
    if (empty($usrFormat) === TRUE) {return FALSE;}
    if (is_string($usrFormat) === FALSE) {return FALSE;}
    
    $usrFormat = strtolower($usrFormat);
    
    switch ($usrFormat)
      {      
      case 'a0';           
      return $this->CreatePage(2380, 3368); 
      break;
      
      case 'a1';      
      return $this->CreatePage(1684, 2380); 
      break;
      
      case 'a2';           
      return $this->CreatePage(1190, 1684); 
      break;
      
      case 'a3':    
      return $this->CreatePage(842, 1190); 
      break;
      
      case 'a4':       
      return $this->CreatePage(595, 842); 
      break; 
      
      case 'a5':            
      return $this->CreatePage(421, 595); 
      break;
      
      case 'a6':               
      return $this->CreatePage(297, 421); 
      break;
      
      case 'b5':        
      return $this->CreatePage(501, 709); 
      break;
      
      case 'letter':      
      return $this->CreatePage(612, 792); 
      break;
      
      case 'legal':     
      return $this->CreatePage(612, 1008); 
      break;
      
      case 'ledger':
      case 'tabloid':       
      return $this->CreatePage(1224, 792); 
      break;
      
      case '11x17':           
      return $this->CreatePage(792, 1224); 
      break;
      
      default: 
      return FALSE;
      break;
      }
    }
    
  protected function ConvertRGBToCMYK($usrRed = 0, $usrGreen = 0, $usrBlue = 0)
    {
    if (is_numeric($usrRed) === FALSE || is_numeric($usrGreen) === FALSE || is_numeric($usrBlue) === FALSE) {return FALSE;}
    if (($usrRed < 0 || $usrRed > 255) || ($usrGreen < 0 || $usrGreen > 255) || ($usrBlue < 0 || $usrBlue > 255)) {return FALSE;}
       
    $tmpC = 1 - ($usrRed / 255);
    $tmpM = 1 - ($usrGreen / 255);
    $tmpY = 1 - ($usrBlue / 255);

    $tmpMin = min($tmpC, $tmpM, $tmpY);
    
    if ($tmpMin == 1)
      {
      return array('c' => 0, 'm' => 0, 'y' => 0, 'k' => 1);
      }
      
    $tmpK = $tmpMin;
    $tmpB = 1 - $tmpK;
    
    return array('c' => ($tmpC - $tmpK) / $tmpB, 'm' => ($tmpM - $tmpK) / $tmpB, 'y' => ($tmpY - $tmpK) / $tmpB, 'k' => $tmpK);    
    } 
    
  protected function ConvertCMYKToRGB($usrC = 0, $usrM = 0, $usrY = 0, $usrK = 0)
    {
    if (is_numeric($usrC) === FALSE || is_numeric($usrM) === FALSE || is_numeric($usrY) === FALSE || is_numeric($usrK) === FALSE) {return FALSE;}
    if (($usrC < 0 || $usrC > 255) || ($usrM < 0 || $usrM > 255) || ($usrY < 0 || $usrY > 255) || ($usrK < 0 || $usrK > 255)) {return FALSE;}
    
    if ((($usrC + $usrK) > 100) || (($usrM + $usrK) > 100) || (($usrY + $usrK) > 100))
      {
      $tmpR = -99;
      $tmpG = -99;
      $tmpB = -99;
      }

    $tmpMax = max($usrC, $usrM, $usrY);    
    
    if ($tmpMax == $usrC) {$tmpR = 0;}
    if ($tmpMax == $usrM) {$tmpG = 0;}
    if ($tmpMax == $usrK) {$usrY = 0;}
    
    $tmpK = 100 - $tmpMax;
    
    if ($tmpR != 0) {$tmpR = (1 - (($usrC + $tmpK )/100)) * 255;}
    else {$tmpR = (1 - (($usrC + $usrK)/100)) * 255;}
    
    if ($tmpG != 0) {$tmpG = (1 - (($usrM + $tmpK)/100)) * 255;}
    else {$tmpG = (1 - (($usrM + $usrK)/100)) * 255;}
    
    if ($tmpB != 0) {$tmpB = (1 - (($usrY + $tmpK)/100)) * 255;}
    else {$tmpB = (1 - (($usrY + $usrK)/100)) * 255;}  
    
    return array('r' => $tmpR, 'g' => $tmpG, 'b' => $tmpB);
    } 
       
  /* Core functions starts here */
  
  /* Coordinates functions starts here */
  
  public function UseTopLeftCoord()
    {
    if ($this->IsPageOpen !== FALSE) {return FALSE;}
        
    PDF_set_parameter($this->PDFRes, "topdown", "true");
    PDF_set_parameter($this->PDFRes, "usercoordinates", "true");
    }
    
  public function UseBottomLeftCoord()
    {
    if ($this->IsPageOpen !== FALSE) {return FALSE;}
        
    PDF_set_parameter($this->PDFRes, "topdown", "false");
    PDF_set_parameter($this->PDFRes, "usercoordinates", "true");
    }
  
  /* Coordinates functions ends here */
  
  /* Text functions starts here */
  
  protected function PrepareFont()
    {    
    $tmpFont = NULL;
    $tmpResult = FALSE;
    
    if (is_null($this->CurFont) === TRUE || $this->CurFontSize == 0) {return FALSE;}

    try
      {
      $tmpFont = pdf_findfont($this->PDFRes, $this->CurFont, "host", 1);
      }
    catch (Exception $usrError)
      {
      return FALSE;
      }
    
    return pdf_setfont($this->PDFRes, $tmpFont, $this->CurFontSize);    
    }
    
  protected function FormatTextBoxMiddle($usrText = '', $usrX = 0, $usrY = 0, $usrW = 0, $usrH = 0)
    {
    $tmpHLines = 0;
    $tmpTextLen = 0;
    $tmpLineDiff = 0;
    
    $tmpTextLen = strlen($usrText);
    
    if ($tmpTextLen <=0) {return FALSE;}
    if ($this->CurFontSize <= 0) {return FALSE;}

    $tmpHLines = (int)($usrH / $this->CurFontSize);
    if ($tmpHLines < 3) {return FALSE;}

    $tmpWLines = ceil(($tmpTextLen * $this->CurFontSize)  / $usrW);

    if ($tmpWLines >= $tmpHLines) {return FALSE;}
    
    $tmpLineDiff = (int)(($tmpHLines - $tmpWLines) / 2);
    $tmpLineDiff += 1;
    $usrY += ($tmpLineDiff * $this->CurFontSize);
    //$usrH -= ($tmpLineDiff * $this->CurFontSize);

    return array('x' => $usrX, 'y' => $usrY, 'w' => $usrW, 'h' => $usrH);
    }
  
  public function PrintTextXY($usrText = '', $usrX = 0, $usrY = 0)
    {
    if (empty($usrText) === TRUE || is_string($usrText) === FALSE) {return FALSE;}
    if (is_numeric($usrX) === FALSE || is_numeric($usrY) === FALSE) {return FALSE;}
    if ($this->IsPageOpen === FALSE) {return FALSE;}
    
    if ($this->PrepareFont() === FALSE) {return FALSE;}
    
    try
      {
      return pdf_show_xy($this->PDFRes, $usrText, $usrX, $usrY); 
      } 
    catch (Exception $usrError)
      {
      return FALSE;
      }   
    }
  
  //throws error  
  public function PrintImageXY($usrImage = '', $usrX = 0, $usrY = 0, $usrScale = 1)
    {
    $tmpFileExt = '';
    $tmpImage = 0;
    $tmpResult = FALSE;
    
    if (empty($usrImage) === TRUE || is_string($usrImage) === FALSE) {return FALSE;}
    if (is_numeric($usrX) === FALSE || is_numeric($usrY) === FALSE) {return FALSE;}    
    if (empty($usrScale) === TRUE){$usrScale = 1;}
    if ($this->IsPageOpen === FALSE) {return FALSE;}
    
    if ($this->FileMan->IsReadable($usrImage) === FALSE){return FALSE;}
    
    try
      {
      $tmpFileExt = $this->FileMan->GetFileExtension($usrImage);
      }
    catch(plcChassisException $usrError)
      {
      return FALSE;
      }
      
    $tmpFileExt = strtolower($tmpFileExt);
    
    switch ($tmpFileExt)
      {
      case 'jpg':
      case 'jpeg':
      
      $tmpFileExt = 'jpeg';
      break;
      
      case 'gif':
      break;
    
      case 'png':
      break;
      
      case 'tiff':
      break;
      
      default:
      return FALSE;
      break;
      }
    
    try
      {
      $tmpImage = PDF_open_image_file($this->PDFRes, $tmpFileExt, $usrImage, "", 0);
      if (!$tmpImage){return FALSE;}
      }
    catch(Exception $usrError)
      {
      var_dump($usrError);
      return FALSE;
      }

    try
      {
      $tmpResult = PDF_place_image($this->PDFRes, $tmpImage, $usrX, $usrY, $usrScale);
      }
    catch(Exception $usrError)
      {
      PDF_close_image($this->PDFRes, $tmpImage);
      return FLASE;
      }
    
    if ($tmpResult === TRUE)
      {
      PDF_close_image($this->PDFRes, $tmpImage);
      return TRUE;
      }
    else
      {
      PDF_close_image($this->PDFRes, $tmpImage);
      return FLASE;
      }
    }
 
  // 1 - "left"
  // 2 - "right"
  // 3 - "justify"
  // 4 - "fulljustify"
  // 5 - "center"
  // 6 - "left-middle"
  // 7 - "right-middle"
  // 8 - "justify-middle"
  // 9 - "fulljustify-middle"
  // 10 - "center-middle"
   
  public function PrintTextBoxXY($usrText = '', $usrX = 0, $usrY = 0, $usrW = 0, $usrH = 0, $usrA = 1)
    {
    $tmpResult = FALSE;
    
    if (empty($usrText) === TRUE || is_string($usrText) === FALSE) {return FALSE;}
    if (is_numeric($usrX) === FALSE || is_numeric($usrY) === FALSE || is_numeric($usrW) == FALSE || is_numeric($usrH) == FALSE || is_int($usrA) === FALSE) {return FALSE;}
    if ($usrW < 0 || $usrH < 0 || $usrA < 1 || $usrA > 10) {return FALSE;}    
    if ($this->IsPageOpen === FALSE) {return FALSE;}

    if ($this->PrepareFont() === FALSE) {return FALSE;}   
    
    switch($usrA)
      {
      case 6:  
      $tmpResult = $this->FormatTextBoxMiddle($usrText, $usrX, $usrY, $usrW, $usrH); 

      case 1:      
      $usrA = "left";
      break;
      
      case 7:  
      $tmpResult = $this->FormatTextBoxMiddle($usrText, $usrX, $usrY, $usrW, $usrH); 
      
      case 2:      
      $usrA = "right";
      break;  
      
      case 8:  
      $tmpResult = $this->FormatTextBoxMiddle($usrText, $usrX, $usrY, $usrW, $usrH); 
      
      case 3:      
      $usrA = "justify";
      break;
      
      case 9:  
      $tmpResult = $this->FormatTextBoxMiddle($usrText, $usrX, $usrY, $usrW, $usrH);     
      
      case 4:      
      $usrA = "fulljustify";
      break; 
      
      case 10:  
      $tmpResult = $this->FormatTextBoxMiddle($usrText, $usrX, $usrY, $usrW, $usrH);  
      
      case 5:      
      $usrA = "center";
      break;   
      
      default:
      return FALSE;
      break;
      }
      
    if ($tmpResult !== FALSE)
      {
      $usrX = $tmpResult['x'];
      $usrY = $tmpResult['y'];
      $usrW = $tmpResult['w'];
      $usrH = $tmpResult['h'];
      } 
      
    try
      {
      return pdf_show_boxed($this->PDFRes, $usrText, $usrX, $usrY, $usrW, $usrH, $usrA, "");
      } 
    catch (Exception $usrError)
      {
      return FALSE;
      } 
    }
  
  /* Text functions ends here */
  
  /* Graphic functions starts here */
  
  public function DrawLine($usrXF  = 0, $usrYF  = 0, $usrXT  = 0, $usrYT  = 0)
    {
    if ($this->IsPageOpen === FALSE) {return FALSE;}
    if (is_numeric($usrXF) === FALSE || is_numeric($usrYF) === FALSE || is_numeric($usrXT) === FALSE || is_numeric($usrYT) === FALSE) {return FALSE;}
    
    try
      {
      pdf_setcolor($this->PDFRes, "stroke", "cmyk", $this->ColorCMYKS['c'], $this->ColorCMYKS['m'], $this->ColorCMYKS['y'], $this->ColorCMYKS['k']);   
      pdf_moveto($this->PDFRes, $usrXF, $usrYF);  
      pdf_lineto($this->PDFRes, $usrXT, $usrYT); 
      return pdf_stroke($this->PDFRes);  
      }
    catch(Exception $usrError)
      {
      return FALSE;
      }   
    }
    
  public function DrawCircle($usrX = 0, $usrY = 0, $usrR = 1)
    {
    if ($this->IsPageOpen === FALSE) {return FALSE;}
    if (is_numeric($usrX) === FALSE || is_numeric($usrY) === FALSE || is_numeric($usrR) === FALSE) {return FALSE;}
    if ($usrR <= 0) {return FALSE;}
        
    try
      {
      pdf_setcolor($this->PDFRes, "stroke", "cmyk", $this->ColorCMYKS['c'], $this->ColorCMYKS['m'], $this->ColorCMYKS['y'], $this->ColorCMYKS['k']); 
      pdf_setcolor($this->PDFRes, "fill", "cmyk", $this->ColorCMYKF['c'], $this->ColorCMYKF['m'], $this->ColorCMYKF['y'], $this->ColorCMYKF['k']); 
      PDF_circle ($this->PDFRes, $usrX, $usrY, $usrR);
      return pdf_fill_stroke($this->PDFRes);
      }
    catch(Exception $usrError)
      {
      return FALSE;
      }  
    }
    
  public function DrawRect($usrX = 0, $usrY = 0, $usrW = 0, $usrH = 0)
    {
    if ($this->IsPageOpen === FALSE) {return FALSE;}
    if (is_numeric($usrX) === FALSE || is_numeric($usrY) === FALSE || is_numeric($usrW) === FALSE || is_numeric($usrH) === FALSE) {return FALSE;}
    
    try
      {
      pdf_setcolor($this->PDFRes, "stroke", "cmyk", $this->ColorCMYKS['c'], $this->ColorCMYKS['m'], $this->ColorCMYKS['y'], $this->ColorCMYKS['k']); 
      pdf_setcolor($this->PDFRes, "fill", "cmyk", $this->ColorCMYKF['c'], $this->ColorCMYKF['m'], $this->ColorCMYKF['y'], $this->ColorCMYKF['k']); 
      PDF_rect ($this->PDFRes, $usrX, $usrY, $usrW, $usrH);
      return pdf_fill_stroke($this->PDFRes);
      }
    catch(Exception $usrError)
      {
      return FALSE;
      }   
    }
  
  /* Graphic functions ends here */
  
  /* Get functions starts here */
  
  public function GetCurFile()
    {
    return $this->CurFilePath;
    }
    
  public function GetPagesCount()
    {
    return $this->PagesCount;
    }
 
  public function GetPageWidth()
    {
    return $this->CurPageWidth;
    } 
    
  public function GetPageHeight()
    {
    return $this->CurPageHeight;
    }   
        
  public function GetCurFont()
    {
    return $this->CurFont;
    }
    
  public function GetCurFontSize()
    {
    return $this->CurFontSize;
    }
        
  public function GetCMYKColorStroke()
    {
    return $this->ColorCMYKS;
    }
    
  public function GetCMYKColorFill()
    {
    return $this->ColorCMYKF;
    }
    
  public function GetRGBColorStroke()
    {
    return $this->ConvertCMYKToRGB($this->ColorCMYKS['c'], $this->ColorCMYKS['m'], $this->ColorCMYKS['y'], $this->ColorCMYKS['k']);
    }
    
  public function GetRGBColorFill()
    {
    return $this->ConvertCMYKToRGB($this->ColorCMYKF['c'], $this->ColorCMYKF['m'], $this->ColorCMYKF['y'], $this->ColorCMYKF['k']);
    }
      
  /* Get functions ends here */
  
  /* Set functions starts here */
  
  public function SetCreator($usrCreator = '')
    {
    if (empty($usrCreator) === TRUE || is_string($usrCreator) === FALSE) {return FALSE;}
    
    return pdf_set_info($this->PDFRes, 'Creator', $usrCreator);
    }
    
  public function SetAuthor($usrAuthor = '')
    {
    if (empty($usrAuthor) === TRUE || is_string($usrAuthor) === FALSE) {return FALSE;}
    
    return pdf_set_info($this->PDFRes, 'Author', $usrAuthor);
    }
    
  public function SetTitle($usrTitle = '')
    {
    if (empty($usrTitle) === TRUE || is_string($usrTitle) === FALSE) {return FALSE;}
    
    return pdf_set_info($this->PDFRes, 'Title', $usrTitle);
    }
  
  //throws error
  public function SetCurFile($usrFile = '')
    {    
    if (empty($usrFile) === TRUE){return FALSE;}
    
    $this->CloseFile();    
    if ($this->FileMan->IsExist($usrFile) === TRUE) {$this->FileMan->DeleteFile($usrFile);}
       
    $this->IsFileOpen = pdf_open_file($this->PDFRes, $usrFile);

    if ($this->IsFileOpen){$this->IsFileOpen = TRUE; $this->CurFilePath = $usrFile;}
    else {$this->CurFilePath = NULL;}
    
    return $this->IsFileOpen;
    }
        
  public function SetFontFile($usrFileName = '', $usrFontName = '')
    {
    if (empty($usrFileName) === TRUE || empty($usrFontName) === TRUE) {return FALSE;}
    if (is_string($usrFileName) === FALSE || is_string($usrFontName) === FALSE) {return FALSE;}
    if ($this->FileMan->IsReadable($usrFileName) === FALSE) {return FALSE;}   
     
    pdf_set_parameter($this->PDFRes, "FontOutline", $usrFontName.'='.$usrFileName); 
    return TRUE;
    }
    
  public function SetCurFont($usrFontName = '')
    {    
    if (empty($usrFontName) === TRUE) {return FALSE;}
    if (is_string($usrFontName) === FALSE) {return FALSE;}
    
    $this->CurFont = $usrFontName;
    return TRUE;
    }
    
  public function SetCurFontSize($usrSize = 0)
    {
    if (empty($usrSize) === TRUE) {return FALSE;}
    
    $this->CurFontSize = $usrSize;
    return TRUE;
    }
        
  public function SetCMYKColorStroke($usrC = 0, $usrM = 0, $usrY = 0, $usrK = 0)
    {
    if (is_numeric($usrC) === FALSE || is_numeric($usrM) === FALSE || is_numeric($usrY) === FALSE || is_numeric($usrK) === FALSE) {return FALSE;}
    if (($usrC < 0 || $usrC > 255) || ($usrM < 0 || $usrM > 255) || ($usrY < 0 || $usrY > 255) || ($usrK < 0 || $usrK > 255)) {return FALSE;}
    
    $this->ColorCMYKS['c'] = $usrC;
    $this->ColorCMYKS['m'] = $usrM;
    $this->ColorCMYKS['y'] = $usrY;
    $this->ColorCMYKS['k'] = $usrK;
    
    return TRUE;
    }
    
  public function SetCMYKColorFill($usrC = 0, $usrM = 0, $usrY = 0, $usrK = 0)
    {
    if (is_numeric($usrC) === FALSE || is_numeric($usrM) === FALSE || is_numeric($usrY) === FALSE || is_numeric($usrK) === FALSE) {return FALSE;}
    if (($usrC < 0 || $usrC > 255) || ($usrM < 0 || $usrM > 255) || ($usrY < 0 || $usrY > 255) || ($usrK < 0 || $usrK > 255)) {return FALSE;}
    
    $this->ColorCMYKF['c'] = $usrC;
    $this->ColorCMYKF['m'] = $usrM;
    $this->ColorCMYKF['y'] = $usrY;
    $this->ColorCMYKF['k'] = $usrK;
    
    return TRUE;
    }
    
  public function SetRGBColorStroke($usrRed = 0, $usrGreen = 0, $usrBlue = 0)
    {
    if (is_numeric($usrRed) === FALSE || is_numeric($usrGreen) === FALSE || is_numeric($usrBlue) === FALSE) {return FALSE;}
    if (($usrRed < 0 || $usrRed > 255) || ($usrGreen < 0 || $usrGreen > 255) || ($usrBlue < 0 || $usrBlue > 255)) {return FALSE;}
    
    $tmpColor = $this->ConvertRGBToCMYK($usrRed, $usrGreen, $usrBlue);
    if ($tmpColor !== FALSE) {$this->ColorCMYKS = $tmpColor;}
    else {return FALSE;}
    
    return TRUE;
    }
    
  public function SetRGBColorFill($usrRed = 0, $usrGreen = 0, $usrBlue = 0)
    {
    if (is_numeric($usrRed) === FALSE || is_numeric($usrGreen) === FALSE || is_numeric($usrBlue) === FALSE) {return FALSE;}
    if (($usrRed < 0 || $usrRed > 255) || ($usrGreen < 0 || $usrGreen > 255) || ($usrBlue < 0 || $usrBlue > 255)) {return FALSE;}
    
    $tmpColor = $this->ConvertRGBToCMYK($usrRed, $usrGreen, $usrBlue);
    if ($tmpColor !== FALSE) {$this->ColorCMYKF = $tmpColor;}
    else {return FALSE;}
    
    return TRUE;
    }
        
  /* Get functions ends here */
  }

?>