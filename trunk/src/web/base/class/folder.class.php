<?php
// -----------------------------------------------------------------------------------------------------------------------
//
//
//
//
//
// -----------------------------------------------------------------------------------------------------------------------
class Folder
{
var $files = array();
var $folders = array();
var $arExcludedFiles = array('.', '..');

	//-------------------------------------------------------------------
	//
	//
	//-------------------------------------------------------------------
	function getFolderContent($path, $excludes = '') {

	if($excludes != '') {
		$arExcludedFiles = array_merge($this->arExcludedFiles, $excludes);
	} else {
		$arExcludedFiles = $this->arExcludedFiles;
	}

		$handle = opendir ("$path/.");
	    while (false != ($file = readdir ($handle))) {
	    	if (in_array($file, $arExcludedFiles) == FALSE){
	    		if (is_file("$path/$file")== TRUE) {
	               $myFile = new File("$path/$file"); 
	               $this->files[] = $myFile;
	    		}		
	    	}	
	    }
	}
	
	//-------------------------------------------------------------------
	//
	//
	//-------------------------------------------------------------------
	function getFolders($path, $excludes = '') {

	if($excludes != '') {
		$arExcludedFiles = array_merge($this->arExcludedFiles, $excludes);
	} else {
		$arExcludedFiles = $this->arExcludedFiles;
	}
		$handle = opendir ("$path/.");
	    while (false != ($file = readdir ($handle))) {
	    	if (in_array($file, $arExcludedFiles) == FALSE){
	    		if (is_dir("$path/$file")== TRUE) {
					$this->folders[] = $file;
	    		}		
	    	}	
	    }
	}
}
