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
class File
{
/**
   *  path to file
   *  @var string
   */
var $path = '';
/**
   *  filename
   *  @var string
   */
var $name = '';
/**
   *  directory
   *  @var string
   */
var $dir = '';
/**
   *  fileextension
   *  @var string
   */
var $extension = '';
/**
   *  filetime
   *  @var date
   */
var $date = '';
/**
   *  filetime (short)
   *  @var date
   */
var $date_short = '';
/**
   *  filetype
   *  @var string
   */
var $filetype = '';
/**
   *  filesize in kilobyte
   *  @var double
   */
var $filesize = 0;
/**
   *  height (if > 0 = picture)
   *  @var int
   */
var $height = 0;
/**
   *  width (if > 0 = picture)
   *  @var int
   */
var $width = 0;
/**
   *  picture
   *  @var string
   */
var $pictype = '';

   /**
          *
          * @access public
          * @return object
          */
	function File($path) {
	$this->path = $path;
	$path_parts = pathinfo($path);
	$this->name = $path_parts["basename"];
	$this->dir = $path_parts["dirname"];
		if(isset($path_parts["extension"])) {
			$this->extension = strtolower($path_parts["extension"]);
		}
	$this->filesize = round (filesize ($path)/100) /10;
	$this->date = date("d.m.Y - H:i", filemtime ($path));
	$this->date_short = date("d.m.Y", filemtime ($path));
	$this->filetype = filetype($path);
		if($this->filesize != 0) {
			$imgsize = getimagesize($path);
			$this->width = $imgsize[0];
			$this->height = $imgsize[1];
			$this->pictype = $imgsize[2];
		}
	}
	
	//-------------------------------------------------------------------
	//
	//
	//-------------------------------------------------------------------	
	function Move($target) {
	$strMsg = "";
		if(!copy($this->path, $target)){ $strMsg .= 'failed to copy '.$this->name.'<br>'; }
		if(!unlink($this->path)) {$strMsg .= 'failed to delete '.$this->name.'<br>';}
	return $strMsg;
	}
}
?>