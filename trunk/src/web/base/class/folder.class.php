<?php
/*
  This file is part of openQRM.

    openQRM is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License version 2
    as published by the Free Software Foundation.

    openQRM is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with openQRM.  If not, see <http://www.gnu.org/licenses/>.

    Copyright 2009, Matthias Rechenburg <matt@openqrm.com>
*/

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
		sort($this->folders);
	}
}
