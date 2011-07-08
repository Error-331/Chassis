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
 * Class plcASCIIENTextList is a part of PHP framework - Chassis.   
 * 
 * @package     Chassis
 * @author      Selihov Sergei Stanislavovich <red331@mail.ru> 
 * @copyright   Copyright (c) 2010-2011 Selihov Sergei Stanislavovich.
 * @license     http://www.gnu.org/licenses/gpl.html  GNU GENERAL PUBLIC LICENSE (Version 3)
 *    
 */
 
/**
 * Data mining.
 *  
 * @subpackage DataMining
 * @author Selihov Sergei Stanislavovich <red331@mail.ru>   
 */
 
/**
 * Documents the plcASCIIENTextList class.
 *
 * @subpackage plcASCIIENTextList
 * @author Selihov Sergei Stanislavovich <red331@mail.ru>   
 */
 
require_once('Chassis/ErrorHandling/plcChassisException.php');

class plcASCIIENTextList
  {
  protected $CurList = array();

  protected $ASCIIDigitStart = 48;
  protected $ASCIIDigitEnd = 57;

  protected $ASCIIUpStart = 65;
  protected $ASCIIUpEnd = 90;
  
  protected $ASCIILowStart = 97;
  protected $ASCIILowEnd = 122;
  
  protected $CurNode = NULL;
  protected $CurIndex = 0;
  
	/* Core functions starts here */
	
	public function __construct()
		{
		$this->CurList[0] = array();
		
    $this->CurList[0]['Key'] = NULL;
    $this->CurList[0]['Index'] = 0;
    $this->CurList[0]['ChildCount'] = 0;
    $this->CurList[0]['Childs'] = NULL;	
    $this->CurList[0]['Parent'] = NULL;	
    $this->CurList[0]['IsSpecial'] = FALSE;
    $this->CurList[0]['IsRoot'] = TRUE;
		}

  public function __destruct() 
		{
   	}	
  
  protected function ReCreateParentKeyRec(&$usrNode)
    {
    $tmpNewKey = '';
    $Counter1 = 0;
    
    if ($usrNode['ChildCount'] > 0)
      {
      for($Counter1 = 0; $Counter1 < $usrNode['ChildCount']; $Counter1++)
        {
        $tmpNewKey .= $usrNode['Childs'][$Counter1]['Key'];
        }
  
      $usrNode['Key'] = $tmpNewKey;
      }
    
    if (is_null($usrNode['Parent']) !== TRUE)
      {
      $this->ReCreateParentKeyRec($usrNode['Parent']);
      }
    
    return TRUE;
    }
    
  public function ResetCurIndex()
    {
    $this->CurNode = NULL;
    $this->CurIndex = 0;
    }
  
  protected function IsNextElementRec($usrNode = NULL)
    {
    $tmpParent = NULL;
    
    if (is_null($usrNode) === TRUE){return FALSE;}
    else
      {
      if (is_null($usrNode['Parent']) === TRUE){return FALSE;}
      else
        {   
        $tmpParent = $usrNode['Parent'];
        
        if (($usrNode['Index']) >= $tmpParent['ChildCount'])
          {
          return $this->IsNextElementRec(&$tmpParent);
          }
        else
          {
          return TRUE;
          }           
        }
      }
    }
    
  protected function NextElementRec($usrNode = NULL)
    {
    $tmpParent = NULL;
    
    if (is_null($usrNode) === TRUE){return FALSE;}
    else
      {
      if (is_null($usrNode['Parent']) === TRUE){return FALSE;}
      else
        {   
        $tmpParent = $usrNode['Parent'];

        if (($usrNode['Index']) >= $tmpParent['ChildCount'])
          {
          return $this->NextElementRec(&$tmpParent);
          }
        else
          {
          $this->CurNode = &$tmpParent['Childs'][$usrNode['Index'] + 1];        
          return TRUE;
          }           
        }
      }
    }
    
  protected function PrevElementRec($usrNode = NULL)
    {
    $tmpParent = NULL;
    
    if (is_null($usrNode) === TRUE){return FALSE;}
    else
      {
      if (is_null($usrNode['Parent']) === TRUE){return FALSE;}
      else
        {   
        $tmpParent = $usrNode['Parent'];
        
        if (($usrNode['Index'] + 1) <= 0)
          {
          return $this->PrevElementRec(&$tmpParent);
          }
        else
          {
          $this->CurNode = &$tmpParent['Childs'][$usrNode['Index'] - 1];       
          return TRUE;
          }           
        }
      }    
    }
    
  public function IsNextElement()
    {
    return $this->IsNextElementRec(&$this->CurNode);
    }       
    
  public function NextElement()
    {
    return $this->NextElementRec(&$this->CurNode);
    }
    
  public function PrevElement()
    {
    return $this->PrevElementRec(&$this->CurNode);
    }	
      	
  public function ReplaceKey($usrRepl, &$usrNode = NULL)
    {   
    if (is_null($usrNode) === TRUE){return FALSE;}
    $usrNode['Key'] = $usrRepl;
    
    if (is_null($usrNode['Parent']) !== TRUE){$this->ReCreateParentKeyRec($usrNode['Parent']);} 
    return TRUE;  
    } 
    
  public function Search($usrKey, $usrCaseSens = FALSE, $usrReplace = FALSE, &$usrNode = NULL)
    {
    $tmpResult = FALSE;
    $tmpOutput = array();
    $tmpCompKey = '';
    
    $tmpCurChildNode = NULL;
    
    $Counter1 = 0;
    
    if (empty($usrKey) === TRUE || isset($usrKey) === FALSE)
      {
      throw new plcChassisException('Invalid search key.', 10103, null ,'User provided invalid search key or it is empty.');
      }    
    
     if (is_null($this->CurList) !== TRUE && is_null($usrNode) === TRUE)
      {
      $usrNode = &$this->CurList[0];
      }
      
    if ($usrNode['ChildCount'] == 0)
      {
      return FALSE;
      }
      
    for ($Counter1 = 0; $Counter1 < $usrNode['ChildCount']; $Counter1++)
      {
      $tmpCurChildNode = &$usrNode['Childs'][$Counter1];
           
      /* Compersion starts here */
      
      if ($usrCaseSens === FALSE)
        {
        $tmpCompKey = strtolower($tmpCurChildNode['Key']);
        $usrKey = strtolower($usrKey);
        }
      else
        {
        $tmpCompKey = $tmpCurChildNode['Key'];
        }
    
      /* Compersion ends here */      

      if ($tmpCompKey == $usrKey)
        {       
        $tmpOutput['Key'] = $tmpCurChildNode['Key'];
        $tmpOutput['Node'] = &$tmpCurChildNode;
        
        /* Replace starts here */
        
        if ($usrReplace !== FALSE)
          {
          $this->ReplaceKey($usrReplace, $tmpCurChildNode);
          }
        
        /* Replace ends here */
        
        return $tmpOutput;
        } 
      
      if ($tmpCurChildNode['ChildCount'] > 0)
        {      
        $tmpResult = $this->Search($usrKey, $usrCaseSens, $usrReplace, $tmpCurChildNode);
        if ($tmpResult !== FALSE){return $tmpResult;}
        }   
      }
      
    return FALSE;
    }      	
     
  public function SearchAll($usrKey, $usrCaseSens = FALSE, $usrReplace = FALSE, &$usrNode = NULL)
    {
    $tmpResult = FALSE;
    $tmpOutput = array();
    $tmpCompKey = '';
    
    $tmpCurChildNode = NULL;
    
    $Counter1 = 0;
    $Counter2 = 0;
    
    if (empty($usrKey) === TRUE || isset($usrKey) === FALSE)
      {
      throw new plcChassisException('Invalid search key.', 10103, null ,'User provided invalid search key or it is empty.');
      }    
    
     if (is_null($this->CurList) !== TRUE && is_null($usrNode) === TRUE)
      {
      $usrNode = &$this->CurList[0];
      }
      
    if ($usrNode['ChildCount'] == 0)
      {
      return FALSE;
      }
      
    for ($Counter1 = 0; $Counter1 < $usrNode['ChildCount']; $Counter1++)
      {
      $tmpCurChildNode = &$usrNode['Childs'][$Counter1];
      
      /* Compersion starts here */
      
      if ($usrCaseSens === FALSE)
        {
        $tmpCompKey = strtolower($tmpCurChildNode['Key']);
        $usrKey = strtolower($usrKey);
        }
      else
        {
        $tmpCompKey = $tmpCurChildNode['Key'];
        }
      
      if ($tmpCompKey == $usrKey)
        {
        $tmpOutput[] = array();             

        $tmpOutput[count($tmpOutput) - 1]['Key'] = $tmpCurChildNode['Key'];
        $tmpOutput[count($tmpOutput) - 1]['Node'] = &$tmpCurChildNode;
        
        /* Replace starts here */
        
        if ($usrReplace !== FALSE)
          {
          $this->ReplaceKey($usrReplace, $tmpCurChildNode);
          }
        
        /* Replace ends here */        
        } 
      
      /* Compersion ends here */
      
      if ($tmpCurChildNode['ChildCount'] > 0)
        {      
        $tmpResult = $this->SearchAll($usrKey, $usrCaseSens, $usrReplace, $tmpCurChildNode);
        if ($tmpResult !== FALSE)
          {
          /* Array merge starts here */
          
          for ($Counter2 = 0; $Counter2 < count($tmpResult); $Counter2++)
            {
            $tmpOutput[] = array();            
            $tmpOutput[count($tmpOutput) - 1]['Key'] = $tmpResult[$Counter2]['Key'];
            $tmpOutput[count($tmpOutput) - 1]['Node'] = $tmpResult[$Counter2]['Node'];
            }
          
          /* Array merge ends here */
          }
        }   
      }
    
    if (count($tmpOutput) > 0)
      {
      return $tmpOutput;
      }
    else
      {
      return FALSE;  
      }   
    } 
  
  // $usrKeyArr format:
  // [index][Key] - keyword to search
  // [index][Dist] - near distance to search (default 0, right next to axis)
  // [index][IsAxis] - word to wich search near (if 2 or more axis found - return FALSE)
  // [index][Replace] -  with which to replace (if FALSE - will not be raplaced)
  //
  // Output format:
  // [index] - [Node1][Node2][Node3]...
  // [NodeN] - [Node][Key]    
    
  public function SearchAllNear($usrKeyArr, $usrCaseSens = FALSE, $usrReplace = FALSE, &$usrNode = NULL)
    {
    $tmpTextList = new plcASCIIENTextList();
    
    $tmpResultsArr = array();
    $tmpAxisWSResults = array();
    
    $tmpAxisWord = FALSE;
    $tmpNearWords = array(); 
    
    $tmpVicinityResult = FALSE;
    $tmpVicinityMatches = array();
    $tmpLongDist = 0;
    
    $tmpCnt2Searh = FALSE;
    $tmpCnt3Searh = FALSE;
    
    $Counter1 = 0;
    $Counter2 = 0;
    $Counter3 = 0;
   
    /* Axis word check starts here */
    
    for ($Counter1 = 0; $Counter1 < count($usrKeyArr); $Counter1++)
      {
      if ($usrKeyArr[$Counter1]['IsAxis'] === TRUE && $tmpAxisWord !== FALSE){return FALSE;}
      else if ($usrKeyArr[$Counter1]['IsAxis'] === TRUE && $tmpAxisWord === FALSE) {$tmpAxisWord = $usrKeyArr[$Counter1]['Key'];}
      else
        {
        if ($usrKeyArr[$Counter1]['Dist'] < 0){$usrKeyArr[$Counter1]['Dist'] = 0;}
        if ($tmpLongDist < $usrKeyArr[$Counter1]['Dist']){$tmpLongDist = $usrKeyArr[$Counter1]['Dist'];}
        
        $tmpNearWords[] =  $usrKeyArr[$Counter1];
        }
      }
      
    /* Axis word check ends here */
 
    if ($tmpAxisWord === FALSE){return FALSE;}  
    
    $tmpAxisWSResults = $this->SearchAll($tmpAxisWord, $usrCaseSens, FALSE);
    if ($tmpAxisWSResults === FALSE){return FALSE;}
    else
      {
      if (count($tmpNearWords) <= 0) 
        {
        for ($Counter1 = 0; $Counter1 < count($tmpAxisWSResults); $Counter1++)
          {
          $tmpResultsArr[] = array();
          $tmpResultsArr[count($tmpResultsArr) - 1][] = $tmpAxisWSResults[$Counter1];
          }
        
        return $tmpResultsArr;
        }
      else
        {
        /* Vicinity sentence get starts here */
        
        for ($Counter1 = 0; $Counter1 < count($tmpAxisWSResults); $Counter1++)
          {
          $tmpVicinityResult = $this->GetWordVicinity($tmpAxisWSResults[$Counter1]['Node'], $tmpLongDist);
          if ($tmpVicinityResult === FALSE){return FALSE;}
          else
            {
            $tmpVicinityMatches[] = $tmpVicinityResult; 
            }
          
          }
        
        /* Vicinity sentence get ends here */

        /* Non axis keywords search starts here */
        
        for ($Counter1 = 0; $Counter1 < count($tmpVicinityMatches); $Counter1++)
          {
          $tmpCnt2Searh = FALSE;
          
          for($Counter2 = 0; $Counter2 < count($tmpNearWords); $Counter2++)
            {
            $tmpCnt3Searh = FALSE;
            
            for ($Counter3 = 0; $Counter3 < count($tmpVicinityMatches[$Counter1]); $Counter3++)
              {
              try
                {
                $tmpTextList->MakeListFromText($tmpVicinityMatches[$Counter1][$Counter3]['Key']);
                $tmpCnt3Searh = $tmpTextList->Search($tmpNearWords[$Counter2]['Key'], FALSE, FALSE);
                }
              catch(plcChassisException $usrError)
                {
                continue;
                }

              if($tmpCnt3Searh !== FALSE) {break;}             
              } 
            
            if ($tmpCnt3Searh === FALSE){$tmpCnt2Searh = FALSE; break;}
            else {$tmpCnt2Searh = TRUE;}
              
            } 
            
          if ($tmpCnt2Searh === TRUE){$tmpResultsArr[] = $tmpVicinityMatches[$Counter1];}
          }
        
        /* Non axis keywords search ends here */
        }   
      }
      
    if (count($tmpResultsArr) > 0){return $tmpResultsArr;}
    else {return FALSE;}  
    } 
            
  protected function IsSpecial($usrSymb)
    {
    $tmpSympLen = strlen($usrSymb);

    $Counter1 = 0;

    if ($tmpSympLen == 1)
      {
      if ((ord($usrSymb) >= $this->ASCIIUpStart && ord($usrSymb) <= $this->ASCIIUpEnd) || 
          (ord($usrSymb) >= $this->ASCIILowStart && ord($usrSymb) <= $this->ASCIILowEnd) ||
          (ord($usrSymb) >= $this->ASCIIDigitStart && ord($usrSymb) <= $this->ASCIIDigitEnd)
          )
        {
        return FALSE;
        }
      else
        {
        return TRUE;
        }      
      }
    else
      {
      for ($Counter1 = 0; $Counter1 < $tmpSympLen; $Counter1++)
        {
        if ((ord($usrSymb[$Counter1]) >= $this->ASCIIUpStart && ord($usrSymb[$Counter1]) <= $this->ASCIIUpEnd) || 
            (ord($usrSymb[$Counter1]) >= $this->ASCIILowStart && ord($usrSymb[$Counter1]) <= $this->ASCIILowEnd) ||
            (ord($usrSymb[$Counter1]) >= $this->ASCIIDigitStart && ord($usrSymb[$Counter1]) <= $this->ASCIIDigitEnd)
            )
          {
          return FALSE;
          }
        }
        
      return TRUE;
      }
    }         
    
  protected function SplitIntoWords (&$usrNode)
    {
    $Counter1 = 0;
    $tmpBuffer = NULL;
    
    $tmpChrOcc = FALSE;
    $tmpSpcOcc = FALSE;
    
    $tmpChilds = array();
    
    $tmpIndexCnt = 0;
    
    for ($Counter1 = 0; $Counter1 < strlen($usrNode['Key']); $Counter1++)
      {
      if ($this->IsSpecial($usrNode['Key'][$Counter1]) === TRUE)
        {
        $tmpSpcOcc = TRUE;
        
        if (is_null($tmpBuffer) !== TRUE)
          {
          $tmpChrOcc = TRUE;
          
          $tmpChilds[] = array();
          $tmpChilds[count($tmpChilds) - 1]['Key'] = $tmpBuffer;
          $tmpChilds[count($tmpChilds) - 1]['Index'] = $tmpIndexCnt;
          $tmpChilds[count($tmpChilds) - 1]['ChildCount'] = 0;
          $tmpChilds[count($tmpChilds) - 1]['Childs'] = NULL;
          $tmpChilds[count($tmpChilds) - 1]['Parent'] = &$usrNode;
          $tmpChilds[count($tmpChilds) - 1]['IsSpecial'] = FALSE;
          $tmpChilds[count($tmpChilds) - 1]['IsRoot'] = FALSE;
          
          $tmpBuffer = NULL;    
          $tmpIndexCnt += 1;
          }
          
        $tmpChilds[] = array();
        $tmpChilds[count($tmpChilds) - 1]['Key'] = $usrNode['Key'][$Counter1];
        $tmpChilds[count($tmpChilds) - 1]['Index'] = $tmpIndexCnt;
        $tmpChilds[count($tmpChilds) - 1]['ChildCount'] = 0;
        $tmpChilds[count($tmpChilds) - 1]['Childs'] = NULL;
        $tmpChilds[count($tmpChilds) - 1]['Parent'] = &$usrNode;
        $tmpChilds[count($tmpChilds) - 1]['IsSpecial'] = TRUE;     
        $tmpChilds[count($tmpChilds) - 1]['IsRoot'] = FALSE;   
        }
      else
        {
        if (is_null($tmpBuffer) !== TRUE)
          {
          $tmpBuffer .= $usrNode['Key'][$Counter1]; 
          }
        else
          {
          $tmpBuffer = $usrNode['Key'][$Counter1]; 
          }
        }
        
      if (($Counter1 + 1) >= strlen($usrNode['Key']) && is_null($tmpBuffer) !== TRUE)
        {
        $tmpChrOcc = TRUE;
        
        $tmpChilds[] = array();
        $tmpChilds[count($tmpChilds) - 1]['Key'] = $tmpBuffer;
        $tmpChilds[count($tmpChilds) - 1]['Index'] = $tmpIndexCnt;
        $tmpChilds[count($tmpChilds) - 1]['ChildCount'] = 0;
        $tmpChilds[count($tmpChilds) - 1]['Childs'] = NULL;
        $tmpChilds[count($tmpChilds) - 1]['Parent'] = &$usrNode;
        $tmpChilds[count($tmpChilds) - 1]['IsSpecial'] = FALSE;
        $tmpChilds[count($tmpChilds) - 1]['IsRoot'] = FALSE;
          
        $tmpBuffer = NULL;
        }
        
      $tmpIndexCnt += 1;
      }

    if (count($tmpChilds) > 0 && $tmpSpcOcc === TRUE && $tmpChrOcc === TRUE)
      {
      return $tmpChilds;
      }
    else
      {
      return FALSE;
      }   
    }
            	
	public function MakeListFromText($usrText)
    {    
    $tmpWords = NULL;
    $tmpSubWords = FALSE;
    
    $tmpFinalArr = array();
    $Counter1 = 0;
    
    $tmpIndexCnt = 0;
    
    $this->ResetCurIndex();
    
		if (is_string($usrText) === FALSE){throw new plcChassisException('Invalid user text.', 10101, null ,'User provided none-text data.');}
		if (strlen($usrText) == 0){throw new plcChassisException('Zero-length text.', 10102, null ,'Usert provided zero-length text.');}
		
		$tmpWords = explode(' ', $usrText);
    
    // Word element properties:
    //
    // Key
    // Index
    // ChildCount
    // Childs
    // Parent
    // IsSpecial
    // IsRoot

    for ($Counter1 = 0; $Counter1 < count($tmpWords); $Counter1++)
      {
      $tmpFinalArr[] = array();
      $tmpFinalArr[count($tmpFinalArr) - 1]['Key'] = $tmpWords[$Counter1];
      $tmpFinalArr[count($tmpFinalArr) - 1]['Index'] = $tmpIndexCnt;
      $tmpFinalArr[count($tmpFinalArr) - 1]['IsRoot'] = FALSE;
      
      /* Word parse starts here */
      
      if (strlen($tmpFinalArr[count($tmpFinalArr) - 1]['Key']) > 0)
        {
        $tmpSubWords = $this->SplitIntoWords($tmpFinalArr[count($tmpFinalArr) - 1]);
        if ($tmpSubWords !== FALSE)
          {
          $tmpFinalArr[count($tmpFinalArr) - 1]['ChildCount'] = count($tmpSubWords);
          $tmpFinalArr[count($tmpFinalArr) - 1]['Childs'] = $tmpSubWords;      
          }  
        else
          {
          $tmpFinalArr[count($tmpFinalArr) - 1]['ChildCount'] = 0;
          $tmpFinalArr[count($tmpFinalArr) - 1]['Childs'] = NULL;   
          }
        }
      else
        {
        $tmpFinalArr[count($tmpFinalArr) - 1]['ChildCount'] = 0;
        $tmpFinalArr[count($tmpFinalArr) - 1]['Childs'] = NULL;    
        }
      
      /* Word parse ends here */
      
      $tmpFinalArr[count($tmpFinalArr) - 1]['Parent'] = &$this->CurList[0];      
      if ($this->IsSpecial($tmpFinalArr[count($tmpFinalArr) - 1]['Key']) === TRUE)
        {
        $tmpFinalArr[count($tmpFinalArr) - 1]['IsSpecial'] = TRUE;  
        }   
      else
        {
        $tmpFinalArr[count($tmpFinalArr) - 1]['IsSpecial'] = FALSE;  
        }  
           
      $tmpIndexCnt += 1;   
      } 

    $this->CurList[0]['ChildCount'] = count($tmpFinalArr); 
    $this->CurList[0]['Childs'] = $tmpFinalArr;
    
    /* Pointer set starts here */
    
    if ($this->CurList[0]['ChildCount'] > 0)
      {
      $this->CurNode = &$tmpFinalArr[0];
      $this->CurIndex = 0;
      }
    
    /* Pointer set ends here */
    }            	
     
  /* Core functions ends here */ 
  
	/* Get functions starts here */	
	
	protected function &GetRootParent($usrNode = NULL)
    {
    $tmpResult = NULL;
 
    if (is_null($usrNode) === TRUE){return NULL;}
    else
      {
      if (is_null($usrNode['Parent']) === TRUE || $usrNode['IsRoot'] === TRUE){return NULL;}
      else
        {   
        if ($usrNode['Parent']['IsRoot'] === TRUE){return $usrNode;}
        
        $tmpResult = $this->GetRootParent(&$usrNode['Parent']);
        if(is_null($tmpResult) === TRUE){return $usrNode['Parent'];}
        else {return $tmpResult;}
        }                                         
      }
    }
    
  public function GetWordVicinity($usrNode, $usrDistance)
    {
    $tmpResultArr = array();
    $tmpRootNode = NULL;
    
    $tmpStartPos = 0;
    $tmpEndPos = 0;
    
    $Counter1 = 0;
    $Counter2 = 0;

    if (is_null($usrNode) === TRUE) {return FALSE;}
    if ($usrDistance == 0) {return $usrNode;}

    $tmpRootNode = $this->GetRootParent(&$usrNode);
    
    /* Start position search starts here */
    
    $Counter1 = $tmpRootNode['Index']; 
    $Counter2 = 0;
    
    while ($Counter1 >= 0)
      {
      $Counter1 -= 1; 
      
      if ($this->CurList[0]['Childs'][$Counter1]['IsSpecial'] === FALSE){$Counter2 += 1;}      
      if ($Counter2 == $usrDistance){break;}
      } 
    
    if ($Counter1 < 0){$tmpStartPos = 0;}
    else {$tmpStartPos = $Counter1;}   
      
    /* Start position search ends here */
    
    /* End position search starts here */
    
    $Counter1 = $tmpRootNode['Index']; 
    $Counter2 = 0;
    
    while ($Counter1 < $this->CurList[0]['ChildCount'])
      {
      $Counter1 += 1;
      
      if ($this->CurList[0]['Childs'][$Counter1]['IsSpecial'] === FALSE){$Counter2 += 1;}      
      if ($Counter2 == $usrDistance){break;}     
      }
      
    if ($Counter1 >= $this->CurList[0]['ChildCount']){$tmpEndPos = ($this->CurList[0]['ChildCount'] - 1);}
    else {$tmpEndPos = $Counter1;}  
    
    /* End position search ends here */

    for ($Counter1 = $tmpStartPos; $Counter1 <= $tmpEndPos; $Counter1++)
      {
      $tmpResultArr[] = &$this->CurList[0]['Childs'][$Counter1];
      }  
   
    if (count($tmpResultArr) <= 0){return FALSE;}
    else {return $tmpResultArr;}
    }

	public function GetTextList()
		{
		return $this->CurList;
		}
		
  public function GetText()
    {
    $tmpText = '';
    $Counter1 = 0;
    
    $tmpNodes = &$this->CurList[0]['Childs'];

    for ($Counter1 = 0; $Counter1 < $this->CurList[0]['ChildCount']; $Counter1++)
      {
      $tmpText .= $tmpNodes[$Counter1]['Key'];

      if (($Counter1 + 1) < $this->CurList[0]['ChildCount'])
        {
        $tmpText .= ' ';
        }
      }
      
    return $tmpText;    
    }
    
  public function GetCurElementPlain()
    {
    return $this->CurNode;
    }	
    
  public function GetCurElement()
    {
    if (is_null($this->CurNode) === TRUE){return NULL;}
    else
      {
      $tmpNewElement = new plcASCIIENTextList();
      $tmpNewElement->MakeListFromText($this->CurNode['Key']);
    
      return $tmpNewElement; 
      }
    }
    
  public function GetCurElementChilds()
    {
    $tmpResultArray = array();
    $Counter1 = 0;
    
    if (is_null($this->CurNode) === TRUE){return NULL;}
    else if (is_null($this->CurNode['Childs']) === TRUE){return NULL;}
    else
      {
      for ($Counter1 = 0; $Counter1 < count($this->CurNode['ChildCount']); $Counter1++)
        {
        $tmpResultArray[] = new plcASCIIENTextList();
        $tmpResultArray[count($tmpResultArray) - 1]->MakeListFromText($this->CurNode['Childs'][Counter1]);
        }
      }
      
    return $tmpResultArray;
    }

	/* Get functions ends here */   
  }

?>