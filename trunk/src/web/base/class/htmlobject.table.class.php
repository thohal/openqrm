<?php
//---------------------------------------------------------------
/**
* builds html table
* @access public
* @param  $name string
* @param  $value array(array(label=>, value=>, ...)
* @return string
*/
class htmlobject_db_table extends htmlobject_table
{
#var $id = '';
var $head = array();
var $body = array();
var $bottom = array();
var $identifier = '';
var $form_action = '';

var $limit = 10;
var $limit_select = array(
	array("value" => 10, "text" => 10),
	array("value" => 20, "text" => 20),
	array("value" => 30, "text" => 30),
	array("value" => 40, "text" => 40),
	array("value" => 50, "text" => 50),
	);
var $offset = 0;
var $max = 0;

var $sort = '';
var $order = '';

var $lang_button_refresh = 'refresh';
var $lang_label_sort = 'order by';
var $lang_label_offset = 'offset';
var $lang_label_limit = 'limit';

var $_num_rows;
var $_num_cols;
var $_body = array();

	function htmlobject_db_table($field = '') {

	//- limit

		if(isset($_REQUEST['limit']) &&  $_REQUEST['limit'] != '') {
			$this->limit = $_REQUEST['limit'];
		}

	//- max

		if(isset($_REQUEST['max']) &&  $_REQUEST['max'] != '') {
			$this->max = $_REQUEST['max'];
		}

	//- order

		if(isset($_REQUEST['order']) &&  $_REQUEST['order'] != '') {
			$this->order = $_REQUEST['order'];
		}

	//- sort

		if(isset($_REQUEST['sort']) &&  $_REQUEST['sort'] != '') {
			$this->sort = $_REQUEST['sort'];
		}
		if($field != '' && $this->sort == '') {
			$this->sort = $field;
		}

	//- offset

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
	function htmlobject_db_table_init() {
		$this->_num_rows = count($this->body);
		$this->_num_cols = count($this->head);
		if($this->identifier != '') { $this->_num_cols = $this->_num_cols +1; }
	}
	
	//----------------------------------------------------------------------------------------
	function get_string() {
	$_strReturn = '';	
	$this->htmlobject_db_table_init();
	if($this->limit > 0) {
	
		if($this->max === 0) {
			$this->max = count($this->body);
		}

		
		//--------------------------------------------------------------------------------------- copy array
		if(count($this->body) > $this->limit) {
			if(($this->offset + $this->limit) < $this->max ) {			
				$max = $this->offset + $this->limit;
			} else {
				$max = $this->max;
			}
			for($i = $this->offset; $i < $max; $i++) {
				$this->_body[$i] = $this->body[$i];
			}
		} else {
			$this->_body = $this->body;
		}
	}
	//--------------------------------------------------------------------------------------- build table head	
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
	parent::add($tr);
	}	
	//--------------------------------------------------------------------------------------- build table body
	$i = 0;
	foreach ($this->_body as $key_1 => $val) {	
		$tr = new htmlobject_tr();
		$tr->css = 'htmlobject_tr';
		$tr->id = 'tr_'. uniqid();
		
		$tr->handler = 'onmouseover="tr_hover(this);" onmouseout="tr_hover(this);" onclick="tr_click(this, '.$i.')"';		

		foreach($val as $key_2 => $v) {
			if($v == '') { $v = '&#160;'; }
			
			$td = new htmlobject_td();
			$td->type = 'td';
			$td->css = 'htmlobject_td '.$key_2;
			$td->text = $v;
			$tr->add($td);
		}
		
		//--------------------------------------------------------------- identifier
		if($this->identifier != '') {

			$html = new htmlobject_input();
			$html->id = 'identifier_'.$i;
			$html->name = 'identifier[]';
			$html->value = $this->body[$key_1][$this->identifier];
			$html->type = 'checkbox';
				
			$td = new htmlobject_td();
			$td->type = 'td';
			$td->css = 'htmlobject_td identifier';
			$td->text = $html->get_string();
			$tr->add($td);
		}			

	parent::add($tr);
	$i++;
	}
	//--------------------------------------------------------------------------------------- build table bottom			
	if(isset($this->bottom[0])) {
		$tr = new htmlobject_tr();
		$tr->css = 'htmlobject_tr';
		$tr->id = 'tr_'. uniqid();
	
		$td = new htmlobject_td();
		$td->colspan = $this->_num_cols;
		$td->type = 'td';
		$td->css = 'htmlobject_td '.$key_2;
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
		parent::add($tr);
	}
	//--------------------------------------------------------------------------------------- build form
	$_strReturn = $this->get_js();
	$_strReturn .= '<form action="'.$this->form_action.'" method="GET">';
	if($this->limit > 0) {
		$_strReturn .= $this->get_form_navi();
	}
	$_strReturn .= parent::get_string();
	$_strReturn .= '</form>';
	return $_strReturn;
	}

	//----------------------------------------------------------------------------------------	
	function arr_sort() {
		
		if($this->sort != '') {
			$sort_by = $this->sort;
		} else {
			$sort_by = $this->sort;
		}
		if($this->order != '') {
			if($this->order == 'ASC') $sort_order = SORT_ASC;
			if($this->order == 'DESC') $sort_order = SORT_DESC;
		} else {
			$sort_order = SORT_ASC;
		}
		foreach($this->body as $val) {
			$column[] = $val[$sort_by];
		}
		array_multisort($column, $sort_order, $this->body);

	}
	
	//----------------------------------------------------------------------------------------	
	function get_form_navi() {
	
	$strReturn = '';
	$value = array();
	
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
		
		$order = new htmlobject_select();
		$order->id = 'p'.uniqid();
		$order->name = 'order';
		$order->text_index = array("value" => "value", "text" => "text");
		$order->text = array(array("value" => "ASC", "text" => "ASC"),array("value" => "DESC", "text" => "DESC"));
		$order->selected = array($this->order);	
	
		$limit_input = new htmlobject_select();
		$limit_input->name = 'limit';
		$limit_input->text_index = array("value" => "value", "text" => "text");
		$limit_input->text = $this->limit_select;
		$limit_input->selected = array($this->limit);

		
		$offset_input = new htmlobject_input();
		$offset_input->name = 'offset';
		$offset_input->value = "$this->offset";
		$offset_input->type = 'text';
		$offset_input->size = 3;
		
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

		$strReturn .= '<table cellpadding="0" cellspacing="0"><tr>';
		$strReturn .= '<td>';
		$strReturn .= 	$max_input->get_string().
				$this->lang_label_sort.
				$sort->get_string().
				$order->get_string().
				$this->lang_label_offset.
				$offset_input->get_string().
				$this->lang_label_limit.
				$limit_input->get_string().
				$action->get_string();

		$strReturn .= '</td></tr><tr><td align="right">';		
		$strReturn .= '<table cellpadding="0" cellspacing="0"><tr>';
		$strReturn .= '<td>';	
		$strReturn .= $first->get_string().$prev->get_string();
		$strReturn .= '</td>';
		$strReturn .= '<td width="160" align="center">
		<span class="">'.( $this->offset + 1 ).'</span> - 
		<span class="">'.$max.'</span> / 
		<span class="">'.$this->max.'</span>
		</td>';
		$strReturn .= '<td>'.$next->get_string().$last->get_string().'</td>';
		$strReturn .= '</tr></table>';
		$strReturn .= '</td></tr></table>';
		
	return $strReturn;
	}
	
//----------------------------------------------------------------------------------------	
function  get_js() {
$_strReturn = '';
	
	if($this->identifier != '') {
		$id_1 = 'document.getElementById("identifier_"+i).checked = true;';
		$id_2 = 'document.getElementById("identifier_"+i).checked = false;';
	}
		
$_strReturn .= '
<script>
function tr_hover(element) {
	x = element.className.match(/tr_hover/g);
	if(x == null) {	
		element.className = element.className + " tr_hover";
		element.style.cursor = "pointer";				
	}
	else { 
		element.className = element.className.replace(/ tr_hover/g, "");
		element.style.cursor = "default";				
	}
}
function tr_click(element, i) {
	x = element.className.match(/tr_click/g);
	if(x == null) {
		element.className = element.className + " tr_click";
		'.$id_1.'
	} else {
		element.className = element.className.replace(/ tr_click/g, "");
		'.$id_2.'
	}
}
</script>
';
return $_strReturn;
}

//-- end class
}
?>