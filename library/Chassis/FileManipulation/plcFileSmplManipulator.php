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
 * Class plcFileSmplManipulator is a part of PHP framework - Chassis.   
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
 * Documents the plcFileSmplManipulator class.
 * 
 * Following class is intended for simplification of tasks connected to the work with file/folder operations and many more.
 *   
 * @subpackage plcFileSmplManipulator
 * @author Selihov Sergei Stanislavovich <red331@mail.ru>   
 */
 
require_once('Chassis/FileManipulation/plcAbstractFileSmplManipulator.php');
require_once('Chassis/ErrorHandling/plcError.php');
require_once('Chassis/ErrorHandling/plcDebugger.php');
require_once('Chassis/ErrorHandling/plcErrorManager.php');

class plcFileSmplManipulator extends plcAbstractFileSmplManipulator
	{
  /**
   * @access protected
   * @var object of type plcErrorManager 
   */
	
	protected $ErrorManager;
	
	function __construct()
		{		
		$this->ErrorManager = plcErrorManager::GetInstance();
		}

  function __destruct() 
		{
   	}
   	
  /* Helper functions starts here */
  
  /**
   * Function that deletes folder and all it contents.
   * 
   * It is a helper function for DeleteDirRec function.
   * 
   * @access protected 
   * @param array specific array with key/value pairs with information of current filesystem object (file, folder, link etc).
   * 
   * @return bool|object returns TRUE on succesfully delete of filesystem object, FALSE if specified file system object can not be deleted, object of type plcError on error.
   * 
   * @see plcFileSmplManipulator::GetDirContents()                         
   */  
  
  protected function DeleteDirRecursion($usrDirContents)
    {
    $tmpResult = '';  
    
    $Counter1 = 0;
    
    for($Counter1 = 0; $Counter1 < count($usrDirContents); $Counter1++)
      {
      if ($usrDirContents[$Counter1]['type'] == 'file')
        {
        $tmpResult = $this->DeleteFile($usrDirContents[$Counter1]['path']);
        
        if ($this->ErrorManager->IsError($tmpResult) || $tmpResult === FALSE)
          {
          return $tmpResult; 
          }
        }
      else if ($usrDirContents[$Counter1]['type'] == 'dir' && $usrDirContents[$Counter1]['name'] != '.' && $usrDirContents[$Counter1]['name'] != '..')
        {
        if ($usrDirContents[$Counter1]['children'] != 'none' && $usrDirContents[$Counter1]['children'] != 'unknown')
          {
          $tmpResult = $this->DeleteDirRecursion($usrDirContents[$Counter1]['children']);
          
          if ($this->ErrorManager->IsError($tmpResult) || $tmpResult === FALSE)
            {
            return $tmpResult; 
            }
          }
          
        $tmpResult = $this->DeleteDir($usrDirContents[$Counter1]['path']);
        
        if ($this->ErrorManager->IsError($tmpResult) || $tmpResult === FALSE)
          {
          return $tmpResult; 
          }
        }
      }
      
    return TRUE;
    }
  
  /* Helper functions ends here */
   	
  /* Utility functions starts here */
  
  /**
   * Function that checks if a folder is empty.
   * 
   * Simple check function if a folder contains filesystem objects (file, folder, link etc).
   * 
   * @access public 
   * @param string path to a folder to check.
   * 
   * @return bool|object returns TRUE if folder is empty, FALSE if folder is not empty or is not readable or is not a folder, object of type plcError on error.                       
   */  
  
  public function IsDirEmpty($usrFileName)
    {
    $tmpResult = '';    
    $tmpResult = $this->IsReadable($usrFileName);
    
    if ($tmpResult !== TRUE)
      {
      return $tmpResult; 
      }
      
    $tmpResult = $this->IsDir($tmpDirPath);
    
    if ($tmpResult !== TRUE)
      {
      return $tmpResult; 
      }
      
    $tmpResult =@ scandir($tmpDirPath);
    
    if ($tmpResult === FALSE)
      {
      $tmpResult = $this->ErrorManager->RaiseError(6106, "Can not read directory contents.", "Can not read contents of specified directory - '".$tmpDirPath."' .");
      return $tmpResult;  
      } 
      
    if (count($tmpResult) > 2)
      {
      return TRUE;
      }
    else
      {
      return FALSE;
      }    
    }
    
  /**
   * Function that checks if a given path points to a link.
   * 
   * Simple check function if a given path points to a link.
   * 
   * @access public 
   * @param string path to a filesystem object.
   * 
   * @return bool returns TRUE if a given path points to a link, FALSE if a path points to other type of object or path points to non existing object.                       
   */ 
  
  public function IsLink($usrFileName)
    {
    $tmpResult = '';    
    $tmpResult = $this->IsExist($usrFileName);
    
    if ($tmpResult !== TRUE)
      {
      return $tmpResult; 
      }
    
    if(is_link($usrFileName) === FALSE)
      {
      return FALSE;
      }
    else
      {
      return TRUE;
      }
    }
    
  /**
   * Function that checks if a given path points to a file.
   * 
   * Simple check function if a given path points to a file.
   * 
   * @access public 
   * @param string path to a filesystem object.
   * 
   * @return bool returns TRUE if a given path points to a file, FALSE if a path points to other type of object or path points to non existing object.                       
   */ 
  
  public function IsFile($usrFileName)
    {
    $tmpResult = '';    
    $tmpResult = $this->IsExist($usrFileName);
    
    if ($tmpResult !== TRUE)
      {
      return $tmpResult; 
      }
    
    if(is_file($usrFileName) === FALSE)
      {
      return FALSE;
      }
    else
      {
      return TRUE;
      }
    }
    
  /**
   * Function that checks if a given path points to a folder.
   * 
   * Simple check function if a given path points to a folder.
   * 
   * @access public 
   * @param string path to a filesystem object.
   * 
   * @return bool returns TRUE if a given path points to a folder, FALSE if a path points to other type of object or path points to non existing object.                       
   */ 
  
  public function IsDir($usrFileName)
    {
    $tmpResult = '';    
    $tmpResult = $this->IsExist($usrFileName);
    
    if ($tmpResult !== TRUE)
      {
      return $tmpResult; 
      }
    
    if(is_dir($usrFileName) === FALSE)
      {
      return FALSE;
      }
    else
      {
      return TRUE;
      }
    }
    
  /**
   * Function that checks if a given path points to an existing object.
   * 
   * Simple check function if a given path points to an existing object.
   * 
   * @access public 
   * @param string path to a filesystem object.
   * 
   * @return bool returns TRUE if a given path points to an existing object, FALSE if a path points to non existing object.                       
   */
          
  public function IsExist($usrFileName)
    {
    $tmpResult = '';
    
    if (file_exists($usrFileName) === FALSE)
      {
      return FALSE;
      }
    else
      {
      return TRUE;
      }
    }
    
  /**
   * Function that checks if a given path points to a readable object.
   * 
   * Simple check function if a given path points to a readable object.
   * 
   * @access public 
   * @param string path to a filesystem object.
   * 
   * @return bool returns TRUE if a given path points to a readable object, FALSE if a path points to a  non readable object.                       
   */ 
  
  public function IsReadable($usrFileName)
    {
    $tmpResult = '';    
    $tmpResult = $this->IsExist($usrFileName);
    
    if ($tmpResult !== TRUE)
      {
      return $tmpResult; 
      }
    
    if(is_readable($usrFileName) === FALSE)
      {
      return FALSE;
      }
    else
      {
      return TRUE;
      }
    }
    
  /**
   * Function that checks if a given path points to a writable object.
   * 
   * Simple check function if a given path points to a writable object.
   * 
   * @access public 
   * @param string path to a filesystem object.
   * 
   * @return bool returns TRUE if a given path points to a writable object, FALSE if a path points to a  non writable object.                       
   */ 
    
  public function IsWritable($usrFileName)
    {
    $tmpResult = '';    
    $tmpResult = $this->IsExist($usrFileName);
    
    if ($tmpResult !== TRUE)
      {
      return $tmpResult; 
      }
    
    if(is_writable($usrFileName) === FALSE)
      {
      return FALSE;
      }
    else
      {
      return TRUE;
      }
    }  
  
  /* Utility functions ends here */
  
  /* Delete functions starts here */
  
  /**
   * Function that deletes a file specified by the given path.
   * 
   * Simple function that deletes a specified file.
   * 
   * @access public 
   * @param string path to a filesystem object.
   * 
   * @return bool|object returns TRUE if a file was deleted successfully, object of type plcError if file can not be deleted.                       
   */ 
     
  public function DeleteFile($usrFileName)
    {
    $tmpResult = '';
      
    $tmpResult =@ unlink($usrFileName);
    
    if($tmpResult === FALSE)
      {
      $tmpResult = $this->ErrorManager->RaiseError(6109, "Can not delete a file.", "File '".$usrFileName."' can not be deleted.");
      return $tmpResult;       
      }
      
    return TRUE;
    }
    
  /**
   * Function that deletes a folder specified by the given path.
   *                                                                    
   * Simple function that deletes a specified folder. Note that if the folder contains nested folders and files this function will
   * return error object. Instead you should use DeleteDirRec() function.   
   * 
   * @access public 
   * @param string path to a filesystem object.
   * 
   * @return bool|object returns TRUE if a folder was deleted successfully, object of type plcError if folder can not be deleted.
   * 
   * @see plcFileSmplManipulator::DeleteDirRec()                               
   */ 
    
  public function DeleteDir($tmpDirPath)
    {
    $tmpResult = '';    
    $tmpResult =@ rmdir($tmpDirPath);
    
    if($tmpResult === FALSE)
      {
      $tmpResult = $this->ErrorManager->RaiseError(6110, "Can not delete a file.", "Directory '".$tmpDirPath."' can not be deleted.");
      return $tmpResult;       
      }
    
    return TRUE;    
    }
    
  /**
   * Function that deletes a folder and all its contents specified by the given path.
   *                                                                    
   * Simple function that deletes a specified folder and all its contents. 
   * 
   * @access public 
   * @param string path to a filesystem object.
   * 
   * @return bool|object returns TRUE if a folder was deleted successfully, FALSE if folder or some of its childs can not be deleted, object of type plcError if error occured.
   * 
   * @see plcFileSmplManipulator::DeleteDirRec()                               
   */ 
  
  public function DeleteDirRec($tmpDirPath)
    {
    $tmpResult = '';
    $tmpDirContents = '';
    
    $Counter1 = 0;
    
    $tmpResult = $this->IsReadable($tmpDirPath);
    
    if ($this->ErrorManager->IsError($tmpResult) || $tmpResult === FALSE)
      {
      return $tmpResult; 
      }   
    
    $tmpDirContents = $this->GetDirContentsRec($tmpDirPath); 
    
    if ($this->ErrorManager->IsError($tmpDirContents) || $tmpResult === FALSE)
      {
      return $tmpDirContents; 
      } 
      
    $tmpResult = $this->DeleteDirRecursion($tmpDirContents);

    if ($this->ErrorManager->IsError($tmpResult) || $tmpResult === FALSE)
      {
      return $tmpResult; 
      } 
      
    $tmpResult = $this->DeleteDir($tmpDirPath);  
    
    if ($this->ErrorManager->IsError($tmpResult) || $tmpResult === FALSE)
      {
      return $tmpResult; 
      }  
    
    return TRUE;       
    }
  
  /* Delete functions ends here */
  
  /* Get functions starts here */
  
  /**
   * Function returns extension of specified file.
   *                                                                    
   * Simple function that returns extension of specified file. 
   * 
   * @access public 
   * @param string path to a filesystem object.
   * 
   * @return string|object returns string containing extension of the specified file or object of type plcError if file has no extension.                             
   */ 
  
  public function GetFileExtension($usrFileName)
    {
    $tmpResult = '';
    $tmpImageExtension = '';   
   
    $tmpImageExtension = strrpos($usrFileName, '.');
    
    if ($tmpImageExtension === FALSE)
      {
      $tmpResult = $this->ErrorManager->RaiseError(6104, "File name has no extension.", "File name '".$tmpImageName."' have no extension.");
      return $tmpResult; 
      }  
    
    $tmpImageExtension = substr($usrFileName, $tmpImageExtension+1);  
    $tmpImageExtension = strtolower($tmpImageExtension);
    
    return $tmpImageExtension;
    }
    
  /**
   * Function returns contents of specified folder by given path.
   *                                                                    
   * Function returns an associative array based on folder contents. Array keys are as follows:   
   * 
   * Array[$ElementNumber]['type'] - type of the filesystem object, can be dir, file, link or unknown; 
   * Array[$ElementNumber]['name'] - name of the filesystem object (name of the file, name of the folder etc);  
   * Array[$ElementNumber]['path'] - relative path to the filesystem object;  
   * Array[$ElementNumber]['children'] - if type of the filesytem object is dir, contains an array of child elements or unknown 
   *                                     if child elements was not yet scaned (note that calling this function will always set 'children' to 'unknown').
   * 
   * To get full tree of elements of specified folder please use GetDirContentsRec() function.                     
   * 
   * @access public 
   * @param string path to a filesystem object.
   * 
   * @return bool|array|object returns array of filesystem objects on success, FALSE on error during scan, object of type plcError on critical error. 
   * 
   * @see plcFileSmplManipulator::GetDirContentsRec()                               
   */ 
    
  public function GetDirContents($tmpDirPath)
    {
    $tmpResult = '';  
    
    $tmpDirContents = '';
    $tmpDirRawContents = '';
    
    $Counter1 = 0;
    $Counter2 = 0;
    
    $tmpResult = $this->IsReadable($tmpDirPath);
    
    if ($this->ErrorManager->IsError($tmpResult) || $tmpResult === FALSE)
      {
      return $tmpResult; 
      }
    
    $tmpResult = $this->IsDir($tmpDirPath);
    
    if ($this->ErrorManager->IsError($tmpResult) || $tmpResult === FALSE)
      {
      return $tmpResult; 
      } 
      
    $tmpDirRawContents =@ scandir($tmpDirPath);
    
    if ($tmpDirRawContents === FALSE)
      {
      $tmpResult = $this->ErrorManager->RaiseError(6106, "Can not read directory contents.", "Can not read contents of specified directory - '".$tmpDirPath."' .");
      return $tmpResult;  
      }
      
    for ($Counter1 = 0; $Counter1 < count($tmpDirRawContents); $Counter1++)
      { 
      if (($tmpResult = $this->IsDir($tmpDirPath.'/'.$tmpDirRawContents[$Counter1])) === TRUE)
        {
        $tmpDirContents[$Counter2]['type'] = 'dir';
        $tmpDirContents[$Counter2]['name'] = $tmpDirRawContents[$Counter1]; 
        $tmpDirContents[$Counter2]['path'] = $tmpDirPath.'/'.$tmpDirRawContents[$Counter1];
        $tmpDirContents[$Counter1]['children'] = 'unknown';
        } 
        
      else if(($tmpResult = $this->IsFile($tmpDirPath.'//'.$tmpDirRawContents[$Counter1])) === TRUE)
        {
        $tmpDirContents[$Counter2]['type'] = 'file';
        $tmpDirContents[$Counter2]['name'] = $tmpDirRawContents[$Counter1];
        $tmpDirContents[$Counter2]['path'] = $tmpDirPath.'/'.$tmpDirRawContents[$Counter1]; 
        }
        
      else if(($tmpResult = $this->IsLink($tmpDirPath.'//'.$tmpDirRawContents[$Counter1])) === TRUE)
        {
        $tmpDirContents[$Counter2]['type'] = 'link';
        $tmpDirContents[$Counter2]['name'] = $tmpDirRawContents[$Counter1];
        $tmpDirContents[$Counter2]['path'] = $tmpDirPath.'/'.$tmpDirRawContents[$Counter1]; 
        }

      else
        {
        $tmpDirContents[$Counter2]['type'] = 'unknown';
        $tmpDirContents[$Counter2]['name'] = $tmpDirRawContents[$Counter1];
        $tmpDirContents[$Counter2]['path'] = $tmpDirPath.'/'.$tmpDirRawContents[$Counter1];  
        }     

       
      $Counter2 = $Counter2 + 1;
      }
  
    return $tmpDirContents;     
    }
    
  /**
   * Function returns contents of specified folder and all its descendants  by given path.
   *                                                                    
   * Function returns an associative array based on folder contents. Array keys are as follows:   
   * 
   * Array[$ElementNumber]['type'] - type of the filesystem object, can be dir, file, link or unknown; 
   * Array[$ElementNumber]['name'] - name of the filesystem object (name of the file, name of the folder etc);  
   * Array[$ElementNumber]['path'] - relative path to the filesystem object;  
   * Array[$ElementNumber]['children'] - if type of the filesytem object is dir, contains an array of child elements or 'none' if folder has no children. 
   *                                                         
   * @access public 
   * @param string path to a filesystem object.
   * 
   * @return bool|array|object returns array of filesystem objects on success, FALSE on error during scan, object of type plcError on critical error.                               
   */ 
    
  public function GetDirContentsRec($tmpDirPath)
    {
    $tmpResult = '';     
    $tmpDirContents = '';
        
    $Counter1 = 0;
    
    $tmpDirContents = $this->GetDirContents($tmpDirPath);
    
    if ($this->ErrorManager->IsError($tmpDirContents) || $tmpResult === FALSE)
      {
      return $tmpDirContents; 
      }
      
    for ($Counter1 = 0; $Counter1 < count($tmpDirContents); $Counter1++)
      {
      if ($tmpDirContents[$Counter1]['type'] == 'dir' && $tmpDirContents[$Counter1]['name'] != '.' && $tmpDirContents[$Counter1]['name'] != '..')
        {
        $tmpResult = $this->GetDirContentsRec($tmpDirContents[$Counter1]['path']);
        
        if ($this->ErrorManager->IsError($tmpResult) || $tmpResult === FALSE)
          {
          return $tmpResult; 
          } 

        if (count($tmpResult) == 2)
          {
          $tmpDirContents[$Counter1]['children'] = 'none';
          }
        else
          {
          $tmpDirContents[$Counter1]['children'] = $tmpResult;
          }   
        }
      } 
      
    return $tmpDirContents;    
    }
    
  /**
   * Function returns a flat list of files that are located in the specified folder and all its descendents.
   *                                                                    
   * Function returns an associative array of files based on folder contents. Array keys are as follows: 
   * 
   * Array[$ElementNumber]['type'] - 'file'; 
   * Array[$ElementNumber]['name'] - name of the file;  
   * Array[$ElementNumber]['path'] - relative path to the file;  
   * 
   * Function generates a flat list of files thus if a file located in the subfolder it still be listed as such that located in the parent folder.      
   *                                                         
   * @access public 
   * @param string path to a filesystem object.
   * 
   * @return bool|array|object returns array of filesystem objects on success, FALSE on error during scan, object of type plcError on critical error.                               
   */ 
    
  public function GetFlattenFileListRec($tmpDirPath)
    {
    $tmpResult = '';     
    $tmpDirContents = '';
    $tmpFilesList = array();
        
    $Counter1 = 0;
    $Counter2 = 0;
              
    $tmpDirContents = $this->GetDirContents($tmpDirPath);
    
    if ($this->ErrorManager->IsError($tmpDirContents) || $tmpResult === FALSE)
      {
      return $tmpDirContents; 
      }
      
    for ($Counter1 = 0; $Counter1 < count($tmpDirContents); $Counter1++)
      { 
      if ($tmpDirContents[$Counter1]['type'] == 'dir' && $tmpDirContents[$Counter1]['name'] != '.' && $tmpDirContents[$Counter1]['name'] != '..')
        {
        $tmpResult = $this->GetFlattenFileListRec($tmpDirContents[$Counter1]['path']);
        
        if ($this->ErrorManager->IsError($tmpResult) || $tmpResult === FALSE)
          {
          return $tmpResult; 
          } 

        if (count($tmpResult) != 0)
          {
          for ($Counter2 = 0; $Counter2 < count($tmpResult); $Counter2++)
            {
            if ($tmpResult[$Counter2]['type'] == 'file')
              {
              array_push($tmpFilesList, $tmpResult[$Counter2]);
              }
            }         
          }   
        }
      else if($tmpDirContents[$Counter1]['type'] == 'file')
        {
        array_push($tmpFilesList, $tmpDirContents[$Counter1]);
        }
      } 
      
    return $tmpFilesList;   
    }
    
  
  /* Get functions ends here */
  
  /* Set functions starts here */
   
  /* Set functions ends here */
	}

?>