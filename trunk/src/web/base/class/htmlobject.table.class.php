<?php
class htmlobject_table_builder extends htmlobject_table
{
/**
* head row of table (th)
* <code>
* $head = array();
* $head['id'] = array();
* $head['id']['title'] = 'id';
* $head['date'] = array();
* $head['date']['title'] = 'Datum';
* $head['date']['sortable'] = false;
* $head['date']['exec'] = 'date("d.m.Y", $exec)';
*
* $table = new htmlobject_table_builder();
* $table->head = $head;
* </code>
* @access public
* @var array
*/
var $head = array();
/**
* table body
* <code>
* $body = array();
* $body[] = array('id' => 'value1', 'date' => 'value2', ...)
* $body[] = array('id' => 'value1', 'date' => 'value2', ...)
*
* $table = new htmlobject_table_builder();
* $table->body = $body;
* </code>
* @access public
* @var array
*/
var $body = array();
/**
* bottom row of table
* <code>
* $bottom = array();
* $bottom[] = 'delete';
* $bottom[] = 'sort';
*
* $table = new htmlobject_table_builder();
* $table->bottom = $bottom;
* </code>
* @access public
* @var array
*/
var $bottom = array();
/**
* field to add value to checkbox
* @access public
* @var string
*/
var $identifier = '';
/**
* url to process request
* <code>
* $thisfile = basename($_SERVER['PHP_SELF']);
* $table = new htmlobject_table_builder();
* $table->form_action = $thisfile;
* </code>
* @access public
* @var string
*/
var $form_action = '';
/**
* first limit
* @access public
* @var string
*/
var $limit = 10;
/**
* select with limit values  
* <code>
* $limit_select = array(
*	array("value" => 10, "text" => 10),
*	array("value" => 20, "text" => 20),
*	array("value" => 30, "text" => 30),
*	);
*
* $table = new htmlobject_table_builder();
* $table->limit_select = $limit_select;
* </code>
* @access public
* @var array
*/
var $limit_select = array();
/**
* offset  
* @access public
* @var int
*/
var $offset = 0;
/**
* maximum 
* @access public
* @var int
*/
var $max = 0;
/**
* field to be sorted
* @access public
* @var int
*/
var $sort = '';
/**
* sort order 
* @access public
* @var enum $order possible values [ASC, DESC]
*/
var $order = 'ASC';
/**
* use array_sort to sort output 
* @access public
* @var bol
*/
var $autosort = false;
/**
* capation of refresh button 
* @access public
* @var string
*/
var $lang_button_refresh = 'refresh';
/**
* label for sort select
* @access public
* @var string
*/
var $lang_label_sort = 'order by';
/**
* label for offset input
* @access public
* @var string
*/
var $lang_label_offset = 'offset';
/**
* label for limit select
* @access public
* @var string
*/
var $lang_label_limit = 'limit';

/**
* number of cols 
* @access private
* @var int
*/
var $_num_cols;
/**
* output body array 
* @access private
* @var array
*/
var $_body = array();
var $_headrow = array();
var $_bottomrow = array();

	//----------------------------------------------------------------------------------------
	/**
	* init htmlobject_table_builder vars from Request
	* @access public
	* @param string $field key for initial sort - sort functionality is disabled if empty
	* @param  enum $order  initial sort order [ASC, DESC]
	*/
	//----------------------------------------------------------------------------------------
	function htmlobject_table_builder($field = '', $order = '') {
	
		if(isset($_REQUEST['limit']) &&  $_REQUEST['limit'] != '') {
			$this->limit = $_REQUEST['limit'];
		}
		if(isset($_REQUEST['max']) &&  $_REQUEST['max'] != '') {
			$this->max = $_REQUEST['max'];
		}
		if(isset($_REQUEST['order']) &&  $_REQUEST['order'] != '') {
			$this->order = $_REQUEST['order'];
		}
		if($order != '' && $this->order == '') {
			$this->order = $order;
		}
		if(isset($_REQUEST['sort']) &&  $_REQUEST['sort'] != '') {
			$this->sort = $_REQUEST['sort'];
		}
		else if($field != '') {
			$this->sort = $field;
		}
		if(isset($_REQUEST['offset']) &&  $_REQUEST['offset'] != '') {
			$this->offset = $_REQUEST['offset'];
		}
		//------------------------------------------------------------------- set new offset
		if(isset($_REQUEST['action']) &&  $_REQUEST['action'] != '') {		
		    switch ($_REQUEST['action']) {
			    case '<': $this->offset = $this->offset - $this->limit; break;
			    case '<<': $this->offset = 0; break;
			    case '>': $this->offset = $this->offset + $this->limit; break;
			    case '>>': $this->offset = $this->max - $this->limit; break;
			    case 'refresh': break;
		    }
		}
		//------------------------------------------- check offset
		if($this->offset >= $this->max ) {
			$this->offset = $this->max - $this->limit;
		}
		if($this->offset < 0 ) {
			$this->offset = 0;
		}
	}
	//----------------------------------------------------------------------------------------
	/**
	* init basic values
	* @access public
	*/
	//----------------------------------------------------------------------------------------	
	function init_table_builder() {
		$this->_num_cols = count($this->head);
		if($this->identifier != '') { $this->_num_cols = $this->_num_cols +1; }
		if($this->sort != '') {
			// use autosort ?
			if($this->autosort == true) { $this->arr_sort(); }
			if($this->max == 0) { $this->max = count($this->body); }
			if(count($this->body) > $this->limit) {
				if(($this->offset + $this->limit) < $this->max ) {			
					$max = $this->offset + $this->limit;
				} else { $max = $this->max;	}
				for($i = $this->offset; $i < $max; $i++) {
					$this->_body[$i] = $this->body[$i];
				}
			} else { $this->_body = $this->body; }
		} else { $this->_body = $this->body; }
	}
	//----------------------------------------------------------------------------------------
	/**
	* sorts array [body] by key [sort]
	* @access public
	*/
	//----------------------------------------------------------------------------------------	
	function arr_sort() {
		if($this->order != '') {
			if($this->order == 'ASC') $sort_order = SORT_ASC;
			if($this->order == 'DESC') $sort_order = SORT_DESC;
		} else {
			$sort_order = SORT_ASC;
		}
		$column = array();
		foreach($this->body as $val) {
			$column[] = $val[$this->sort];
		}
		array_multisort($column, $sort_order, $this->body);
	}
	//----------------------------------------------------------------------------------------
	/**
	* builds table head
	* @access public
	* @return object|string htmlobject_tr or empty string
	*/
	//----------------------------------------------------------------------------------------	
	function get_table_head() {
	$tr = '';
		if(count($this->head) > 0) {
			$tr = new htmlobject_tr();
			$tr->css = 'htmlobject_tr';
			$tr->id = 'tr_'. uniqid();
		
			foreach($this->head as $key_2 => $value) {
				if($value['title'] == '') { $value['title'] = '&#160;'; }
				$td = new htmlobject_td();
				$td->type = 'th';
				$td->css = 'htmlobject_td '.$key_2;
				$td->text = $value['title'];
				$tr->add($td);
			}
			if($this->identifier != '') {
				$td = new htmlobject_td();
				$td->type = 'th';
				$td->css = 'htmlobject_td identifier';
				$td->text = '&#160;';
				$tr->add($td);
			}
		}
	return $tr;
	}
	//----------------------------------------------------------------------------------------
	/**
	* adds one row to table body
	* @access public
	* @param array $val 
	* @return object|string htmlobject_tr or empty string
	*/
	//----------------------------------------------------------------------------------------		
	function get_table_body($key, $val) {
		$ident = 'id'. uniqid();
		
		$tr = new htmlobject_tr();
		$tr->css = 'htmlobject_tr';
		$tr->id = 'tr_'. uniqid();
		$tr->handler = 'onmouseover="tr_hover(this);" onmouseout="tr_hover(this);" onclick="tr_click(this, \''.$ident.'\')"';		

		foreach($val as $key_2 => $v) {
			if($v == '') { $v = '&#160;'; }
				if(array_key_exists('exec', $this->head[$key_2]) == true) {
					#echo $key_2;
				}
				$td = new htmlobject_td();
				$td->type = 'td';
				$td->css = 'htmlobject_td '.$key_2;
				$td->text = $v;
				$tr->add($td);
			}
			//--------------------------------------------------------------- identifier
			if($this->identifier != '') {
				$tr->add($this->get_indentifier($key, $ident));
			}
		return $tr;
	}
	//----------------------------------------------------------------------------------------
	/**
	* returns table bottom row
	* @access public
	* @return object|string htmlobject_tr or empty string
	*/
	//----------------------------------------------------------------------------------------	
	function get_table_bottom () {
	$tr = '';
		if(isset($this->bottom[0])) {
			$tr = new htmlobject_tr();
			$tr->css = 'htmlobject_tr';
			$tr->id = 'tr_'. uniqid();
		
			$td = new htmlobject_td();
			$td->colspan = $this->_num_cols;
			$td->type = 'td';
			$td->css = 'htmlobject_td bottom';
			$str = '';
			foreach($this->bottom as $key_2 => $v) {
				$html = new htmlobject_input();
				$html->name = 'action';
				$html->value = $v;
				$html->type = 'submit';
				$str .= $html->get_string();
			}
			$td->text = $str;
			$tr->add($td);	
		}
	return $tr;	
	}
	//----------------------------------------------------------------------------------------
	/**
	* adds a row above the sort row
	* @access public
	* @param  $str string
	*/
	//----------------------------------------------------------------------------------------
	function add_headrow($str = '') {
			$tr = new htmlobject_tr();
			$tr->css = 'htmlobject_tr';
			$tr->id = 'tr_'. uniqid();
		
			$td = new htmlobject_td();
			$td->colspan = $this->_num_cols;
			$td->type = 'td';
			$td->css = 'htmlobject_td head';
			$td->text = $str;
			$tr->add($td);	

	$this->_headrow[] = $tr;	
	}
	//----------------------------------------------------------------------------------------
	/**
	* adds a row under the bottom row
	* @access public
	* @param  $str string
	*/
	//----------------------------------------------------------------------------------------
	function add_bottomrow($str = '') {
			$tr = new htmlobject_tr();
			$tr->css = 'htmlobject_tr';
			$tr->id = 'tr_'. uniqid();
		
			$td = new htmlobject_td();
			$td->colspan = $this->_num_cols;
			$td->type = 'td';
			$td->css = 'htmlobject_td head';
			$td->text = $str;
			$tr->add($td);	

	$this->_bottomrow[] = $tr;	
	}
	//----------------------------------------------------------------------------------------
	/**
	* adds sort functions to table
	* @access public
	* @return object|string
	*/
	//----------------------------------------------------------------------------------------
	function get_sort() {
	$tr = '';
		if($this->sort != '') {
			foreach($this->head as $key_2 => $v) {
				if(isset($v['sortable']) == false) {
					$v['sortable'] = true;
				} 
				if($v['sortable'] == true) {
					$value[] = array("value" => $key_2, "label" => $v['title']);
				}
			}
			$sort = new htmlobject_select();
			$sort->id = 'p'.uniqid();
			$sort->name = 'sort';
			$sort->text_index = array("value" => "value", "text" => "label");
			$sort->text = $value;
			$sort->selected = array($this->sort);
			$str_sort = '<label for"'.$sort->id.'">'.$this->lang_label_sort.$sort->get_string().'</label>';
			
			$order = new htmlobject_select();
			$order->id = 'p'.uniqid();
			$order->name = 'order';
			$order->text_index = array("value" => "value", "text" => "text");
			$order->text = array(array("value" => "ASC", "text" => "ASC"),array("value" => "DESC", "text" => "DESC"));
			$order->selected = array($this->order);
			$str_order = $order->get_string();

			if (count($this->limit_select) <= 0) {
			$this->limit_select = array(
				array("value" => 10, "text" => 10),
				array("value" => 20, "text" => 20),
				array("value" => 30, "text" => 30),
				array("value" => 40, "text" => 40),
				array("value" => 50, "text" => 50),
				);
			}
			$limit_input = new htmlobject_select();
			$limit_input->id = 'p'.uniqid();
			$limit_input->name = 'limit';
			$limit_input->text_index = array("value" => "value", "text" => "text");
			$limit_input->text = $this->limit_select;
			$limit_input->selected = array($this->limit);
			$str_limit = '<label for"'.$limit_input->id.'">'.$this->lang_label_limit.$limit_input->get_string().'</label>';
			
			$offset_input = new htmlobject_input();
			$offset_input->id = 'p'.uniqid();
			$offset_input->name = 'offset';
			$offset_input->value = "$this->offset";
			$offset_input->type = 'text';
			$offset_input->size = 3;
			$str_offset = '<label for"'.$offset_input->id.'">'.$this->lang_label_offset.$offset_input->get_string().'</label>';
			
			$max_input = new htmlobject_input();
			$max_input->name = 'max';
			$max_input->value = $this->max;
			$max_input->type = 'hidden';
			
			$first = new htmlobject_input();
			$first->name = 'action';
			$first->value = '<<';
			$first->type = 'submit';
			
			$prev = new htmlobject_input();
			$prev->name = 'action';
			$prev->value = '<';
			$prev->type = 'submit';
						
			$next = new htmlobject_input();
			$next->name = 'action';
			$next->value = '>';
			$next->type = 'submit';

			$last = new htmlobject_input();
			$last->name = 'action';
			$last->value = '>>';
			$last->type = 'submit';
			
			$action = new htmlobject_input();
			$action->name = 'action';
			$action->value = $this->lang_button_refresh;
			$action->type = 'submit';
			
			if(( $this->offset + $this->limit ) >= $this->max) {
				$next->style = 'visibility:hidden;';
				$last->style = 'visibility:hidden;';
			}
			if($this->offset <= 0) {
				$first->style = 'visibility:hidden;';
				$prev->style = 'visibility:hidden;';
			}
			
			if(($this->offset + $this->limit) < $this->max ) {
				$max = $this->offset + $this->limit;
			} else {
				$max = $this->max;
			}

			$strR = '<table cellpadding="0" cellspacing="0" id="SortTable"><tr>';
			$strR .= '<td>';
			$strR .= $max_input->get_string().
						$str_sort.
						$str_order.
						$str_offset.
						$str_limit.
						$action->get_string();
			$strR .= '</td>';
			$strR .= '</tr><tr>';
			$strR .= '<td align="right">';		

			$strR .= '<table cellpadding="0" cellspacing="0"><tr>';
			$strR .= '<td>'.$first->get_string().$prev->get_string().'</td>';
			$strR .= '<td width="160" align="center">';
			$strR .= '<span class="">'.( $this->offset + 1 ).'</span> - '; 
			$strR .= '<span class="">'.$max.'</span> / ';
			$strR .= '<span class="">'.$this->max.'</span>';
			$strR .= '</td>';
			$strR .= '<td>'.$next->get_string().$last->get_string().'</td>';
			$strR .= '</tr></table>';
			
			$strR .= '</td></tr></table>';		
		
			$tr = new htmlobject_tr();
			$tr->css = 'htmlobject_tr';
			$tr->id = 'tr_'. uniqid();
		
			$td = new htmlobject_td();
			$td->colspan = $this->_num_cols;
			$td->type = 'td';
			$td->css = 'htmlobject_td sorttable';
			$td->text = $strR;
			$tr->add($td);
		}
	return $tr;
	}
	//----------------------------------------------------------------------------------------
	/**
	* adds identifier td to body row
	* @access public
	* @param  $ident string
	* @return object|string
	*/
	//----------------------------------------------------------------------------------------		
	function get_indentifier($key, $ident) {
	$td = '';
		if($this->identifier != '') {
			$html = new htmlobject_input();
			$html->id = $ident;
			$html->name = 'identifier[]';
			$html->value = $this->body[$key][$this->identifier];
			$html->type = 'checkbox';
					
			$td = new htmlobject_td();
			$td->type = 'td';
			$td->css = 'htmlobject_td identifier';
			$td->text = $html->get_string();
		}
	return $td;
	}
	//----------------------------------------------------------------------------------------
	/**
	* returns JS for tr hover and click function
	* @access public
	* @return string
	*/
	//----------------------------------------------------------------------------------------	
	function  get_js() {
	$_strReturn = '';
		if($this->identifier != '') {
			$id_1 = 'document.getElementById(arg).checked = true;';
			$id_2 = 'document.getElementById(arg).checked = false;';
		}
		$_strReturn .= "\n";
		$_strReturn .= '<script>'."\n";
		$_strReturn .= 'function tr_hover(element) {'."\n";
		$_strReturn .= '	x = element.className.match(/tr_hover/g);'."\n";
		$_strReturn .= '	if(x == null) {	element.className = element.className + " tr_hover"; }'."\n";
		$_strReturn .= '	else { element.className = element.className.replace(/ tr_hover/g, "");	}'."\n";
		$_strReturn .= '}'."\n";
		$_strReturn .= 'function tr_click(element, arg) {'."\n";
		$_strReturn .= '	x = element.className.match(/tr_click/g);'."\n";
		$_strReturn .= '	if(x == null) {	element.className = element.className + " tr_click"; '.$id_1.' }'."\n";
		$_strReturn .= '	else { element.className = element.className.replace(/ tr_click/g, "");	'.$id_2.' }'."\n";
		$_strReturn .= '}'."\n";
		$_strReturn .= '</script>'."\n";
	return $_strReturn;
	}
	//----------------------------------------------------------------------------------------
	/**
	* builds html table
	* @access public
	* @param  $name string
	* @param  $value array(array(label=>, value=>, ...)
	* @return string
	*/
	//----------------------------------------------------------------------------------------
	function get_string() {
	$_strReturn = '';
		// build table
		$this->init_table_builder();
		// build table head
		foreach ($this->_headrow as $key => $row) {
			$row->arr_tr[$key]->colspan = $this->_num_cols;
			htmlobject_table::add($row);
		}
		htmlobject_table::add($this->get_sort());
		htmlobject_table::add($this->get_table_head());
		// build table body
		foreach ($this->_body as $key => $value) {
			htmlobject_table::add($this->get_table_body($key, $value));
		}
		// build table bottom
		htmlobject_table::add($this->get_table_bottom());
		foreach ($this->_bottomrow as $key => $row) {
			$row->arr_tr[$key]->colspan = $this->_num_cols;
			htmlobject_table::add($row);
		}
		// build form
		$_strReturn = $this->get_js();
		$_strReturn .= '<form action="'.$this->form_action.'" method="GET">';
		$_strReturn .= htmlobject_table::get_string();
		$_strReturn .= '</form>';
	return $_strReturn;
	}
}//-- end class


class htmlobject_db_table extends htmlobject_table_builder 
{
	function htmlobject_db_table($field = '', $order = '') {
		parent::htmlobject_table_builder($field, $order);
	}
}

class htmlobject_simple_table extends htmlobject_table_builder 
{
var $identifier_checked = true;
var $identifier_disabled = true;
	
	function get_indentifier($key, $ident) {
	$td = '';
		if($this->identifier != '') {
			$html = new htmlobject_input();
			$html->id = $ident;
			$html->name = 'identifier[]';
			$html->value = $this->body[$key][$this->identifier];
			$html->type = 'checkbox';
			$html->checked = $this->identifier_checked;
			$html->disabled = $this->identifier_disabled;
					
			$td = new htmlobject_td();
			$td->type = 'td';
			$td->css = 'htmlobject_td identifier';
			$td->text = $html->get_string();
		}
	return $td;
	}
}
?>