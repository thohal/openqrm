<?php
/**
 * @package Htmlobjects
 */
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


/**
 * @package Htmlobjects
 * @author Alexander Kuballa <akuballa@users.sourceforge.net>
 * @version 1.0
 */
class htmlobject_box extends htmlobject
{

/**
* Label (Title) of box
* @access public
* @var string
*/
var $label = '';
/**
* Attribute name (should the the same as Attribute id)
* @access public
* @var object
*/
var $content = '';
/**
* Attribute name (should the the same as Attribute id)
* @access public
* @var string
*/
var $css_left = 'left';
/**
* Attribute name (should the the same as Attribute id)
* @access public
* @var string
*/
var $css_right = 'right';

/**
* internal use only
*/

	function init_htmlobject_box() {
		if ($this->label == '')		{ $this->label = '&#160;'; }
		if ($this->content == '')	{ $this->content = '&#160;'; }
		if ($this->css_left != '') 	{ $this->css_left = ' class="'.$this->css_left.'"'; }
		if ($this->css_right != '') { $this->css_right = ' class="'.$this->css_right.'"'; }
	}

	function get_string() {
	$_strReturn = '';
		$this->init_htmlobject();
		$this->init_htmlobject_box();
		$_strReturn = '
		<div'.$this->_init_htmlobject.'>
		<div'.$this->css_left.'><label for="'.$this->content->id.'">'.$this->label.'</label></div>
		<div'.$this->css_right.'>'.$this->content->get_string().'</div>
		<div style="line-height:0px;height:0px;clear:both;" class="floatbreaker">&#160;</div>
		</div>
		';
	return $_strReturn;
	}
}

?>
