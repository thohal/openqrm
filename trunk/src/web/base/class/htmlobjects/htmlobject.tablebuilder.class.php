<?php
/**
 * @package htmlobjects
 */

//----------------------------------------------------------------------------------------
/**
 * Tablebuilder
 *
 * @package htmlobjects
 * @author Alexander Kuballa <akuballa@users.sourceforge.net>
 * @copyright Copyright (c) 2009, Alexander Kuballa
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 1.0
*/
//----------------------------------------------------------------------------------------

class htmlobject_tablebuilder extends htmlobject_table
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
* $head['date']['hidden'] = true;
*
* $table = new htmlobject_tablebuilder();
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
* $table = new htmlobject_tablebuilder();
* $table->body = $body;
* </code>
* @access public
* @var array
*/
var $body = array();
/**
* actions row of table
* <code>
* $actions = array();
* $actions[] = 'delete';
* $actions[] = 'sort';
*
* $table = new htmlobject_tablebuilder();
* $table->actions = $actions;
* </code>
* @access public
* @var array
*/
var $actions = array();
/**
* name for submit actions
* @access public
* @var string
*/
var $actions_name = 'action';

/**
*  ------------------------------------------------------------- Identifier Section
*/
/**
* field to add value to checkbox
* @access public
* @var string
*/
var $identifier = '';
/**
* type of identifier input
* @access public
* @var enum $identifier_type possible values [checkbox, radio]
*/
var $identifier_name = 'identifier';
/**
* type of identifier input
* @access public
* @var enum $identifier_type possible values [checkbox, radio]
*/
var $identifier_type = 'checkbox';
/**
* array of identifiers to be checked
* @access public
* @var array()
*/
var $identifier_checked = array();
/**
* array of identifiers to be disabled
* @access public
* @var array()
*/
var $identifier_disabled = array();

/**
*  ------------------------------------------------------------- CSS Section
*/
/**
* global prefix for css classes
* @access public
* @var string
*/
var $css_prefix = 'htmlobject_';

/**
*  ------------------------------------------------------------- Form Section
*/
/**
* url to process request
*  Form disabled if empty
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
* <code>
* // to change initial value to 10
* $table = new htmlobject_table_builder('somefield','', 10);
* </code>
* @access private
* @var string
*/
var $limit = 20;
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
* extra params for table head sort function
* disabled if not set
* @access public
* @var string
*/
var $sort_params;
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
* translation 
* @access public
* @var array
*/
var $lang = array(
	'button_refresh' => 'refresh',
	'label_sort'     => 'order by',
	'label_offset'   => 'offset',
	'label_limit'    => 'limit',
	'option_nolimit' => 'none',
	'select_label'   => 'Select:',
	'select_all'     => 'all',
	'select_none'    => 'none',
	'select_invert'  => 'inverted',
	'no_data'        => 'no data',
);
/**
*  ------------------------------------------------------------- Private Section
*/
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
/**
* additional headrows 
* @access private
* @var array
*/
var $_headrow = array();
/**
* additional bottomrows 
* @access private
* @var array
*/
var $_bottomrow = array();
/**
* internal prefix for posted vars 
* @access private
* @var array
*/
var $_var_prefix;


	//----------------------------------------------------------------------------------------
	/**
	* init htmlobject_table_builder vars from Request
	* @access public
	* @param string $sort key for initial sort - sort functionality is disabled if empty
	* @param enum $order  initial sort order [ASC, DESC]
	* @param int $limit  initial limit
	* @param int $offset  initial offset
	* @param string $var_prefix  prefix for posted vars
	*/
	//----------------------------------------------------------------------------------------
	function htmlobject_tablebuilder($sort = '', $order = '', $limit = '', $offset = '', $var_prefix = 'table_') {

		$this->_var_prefix = $var_prefix;		
		
		$r_limit = $this->get_request($this->_var_prefix.'limit');		
		if($r_limit != '') {
			$this->limit = $r_limit;
		}
		else if($limit != '') {
			$this->limit = $limit;
		}

		$r_max = $this->get_request($this->_var_prefix.'max');
		if($r_max != '') {
			$this->max = $r_max;
		}

		$r_offset = $this->get_request($this->_var_prefix.'offset');
		if($r_offset != '') {
			$this->offset = $r_offset;
		} 
		else if ($offset != '') {
			$this->offset = $offset;
		}
		
		$r_order = $this->get_request($this->_var_prefix.'order');
		if($r_order != '') {
			$this->order = $r_order;
		}
		if($order != '' && $this->order == '') {
			$this->order = $order;
		}

		$r_sort = $this->get_request($this->_var_prefix.'sort');
		if($r_sort != '') {
			$this->sort = $r_sort;
		}
		else if($sort != '') {
			$this->sort = $sort;
		}

		//------------------------------------------------------------------- set new offset
		$action = $this->get_request($this->_var_prefix.'action');
		if($action != '') {		
		    switch ($action) {
			    case '<': $this->offset = $this->offset - $this->limit; break;
			    case '<<': $this->offset = 0; break;
			    case '>': $this->offset = $this->offset + $this->limit; break;
			    case '>>': $this->offset = $this->max - $this->limit; break;
			    case $this->lang['button_refresh']: break;
		    }
		}
		//------------------------------------------- check offset
		if($this->offset >= $this->max ) {
			$this->offset = $this->max - $this->limit;
		}
		if($this->offset < 0 ) {
			$this->offset = 0;
		}
		if($this->limit == 0 || $this->limit >= $this->max){
			$this->offset = 0;
		}
	}
	
	//----------------------------------------------------------------------------------------
	/**
	* init basic values _body, _num_cols
	* @access public
	*/
	//----------------------------------------------------------------------------------------	
	function init_table() {

		$minus = 0;
		// Execute head array special key values
		foreach($this->head as $key => $value) {
			//  special key hidden
			if(@array_key_exists('hidden', $this->head[$key]) == true) {
				if($this->head[$key]['hidden'] === true) {
					$minus = $minus+1;
				}
			}
		}
		$this->_num_cols = count($this->head) - $minus;
		if($this->identifier != '') { $this->_num_cols = $this->_num_cols +1; }
		
		// Sortfunction eabled?
		if($this->sort != '') {
			// use autosort ?
			if($this->autosort == true) { $this->arr_sort(); }
			// max still untouched?
			if($this->max == 0) { $this->max = count($this->body); }
			// Input bigger than Output?
			if(count($this->body) > $this->limit && $this->limit != 0) {
				// max smaller than  limit + offset?
				if(($this->offset + $this->limit) < $this->max ) {			
					$max = $this->offset + $this->limit;
				} else { $max = $this->max;	}
				// Transfer Input to Output				
				for($i = $this->offset; $i < $max; $i++) {
					$this->_body[$i] = $this->body[$i];
				}
			} else { $this->_body = $this->body; }
		} else { $this->_body = $this->body; }
		// save memory
		$this->body = null;
		// reinit to avoid wrong offset
		unset($_REQUEST[$this->_var_prefix.'max']);
		$this->htmlobject_tablebuilder($this->sort, $this->order, $this->limit, $this->offset, $this->_var_prefix);

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
			$tr->css = $this->css_prefix.'tr headrow';
			$tr->id = 'tr_'. uniqid();

			foreach($this->head as $key_2 => $value) {

				if(!isset($value['title'])) {
					$value['title'] = '';
				}

				$hidden = false;
				if(@array_key_exists('hidden', $this->head[$key_2]) == true) {
					if($this->head[$key_2]['hidden'] === true) {
						$hidden = true;
					}
				}
				
				$sortable = true;
				if(@array_key_exists('sortable', $this->head[$key_2]) == true) {
					if($this->head[$key_2]['sortable'] === false) {
						$sortable = false;
					}
				}

				if($hidden === false) {
					if($value['title'] == '') { 
						$str = '&#160;'; 
					} else {
						if($this->sort != '' && $sortable ===  true && isset($this->sort_params)) {
							$order_param = '';
							$linkclass = '';
							if($this->sort == $key_2) {
								if($this->order == 'ASC') {
									$order_param = '&amp;'.$this->_var_prefix.'order=DESC';
									$linkclass = ' class="desc"';
								} else {
									$linkclass = ' class="asc"';
								}
							}
							$params = str_replace('&', '&amp;', $this->sort_params);
							$str = '<a href="?'.$this->_var_prefix.'sort='.$key_2.$params.$order_param.'"'.$linkclass.'>'.$value['title'].'</a>';
						} else {
							$str = $value['title'];		
						}
					}
					$td = new htmlobject_td();
					$td->type = 'th';
					$td->css = $this->css_prefix.'th '.$key_2;
					$td->text = $str;
					$tr->add($td);
				}
			}
			if($this->identifier != '') {
				$td = new htmlobject_td();
				$td->type = 'th';
				$td->css = $this->css_prefix.'th '.$this->identifier_name;
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
	* @param multi $key (index of $this->_body)
	* @param array $val
	* @param string $i (odd/even)
	* @return object|string htmlobject_tr or empty string
	*/
	//----------------------------------------------------------------------------------------		
	function get_table_body($line, $val, $i) {
		$ident = 'id'. uniqid();
		
		$tr = new htmlobject_tr();
		$tr->css = $this->css_prefix.'tr ' .$i;
		$tr->id = 'tr_'. uniqid();
		$tr->handler = $this->get_js_tr($ident);

		$data = array();
		foreach( $this->head as $key => $value ) {
			$data[$key] = $val[$key];
		}		

		foreach($data as $key_2 => $v) {
			if($v == '') { $v = '&#160;'; }
			$hidden = false;
			if(@array_key_exists('hidden', $this->head[$key_2]) == true) {
				if($this->head[$key_2]['hidden'] === true) {
					$hidden = true;
				}
			}
					
			if($hidden === false) {
				$td = new htmlobject_td();
				$td->type = 'td';
				$td->css = $this->css_prefix.'td '.$key_2;
				$td->text = $v;
				$tr->add($td);
			}
		}
		//--------------------------------------------------------------- identifier
		if($this->identifier != '') {
			$tr->add($this->get_indentifier($line, $ident));
		}
		return $tr;
	}
	//----------------------------------------------------------------------------------------
	/**
	* returns table actions row
	* @access public
	* @return object|string htmlobject_tr or empty string
	*/
	//----------------------------------------------------------------------------------------	
	function get_table_actions () {
	$tr = '';
		if(isset($this->actions[0]) && isset($this->_body)) {
			$tr = new htmlobject_tr();
			$tr->css = $this->css_prefix.'tr';
			$tr->id = 'tr_'. uniqid();
		
			$td = new htmlobject_td();
			$td->colspan = $this->_num_cols;
			$td->type = 'td';
			$td->css = $this->css_prefix.'td actions';
			$str = '<div class="actiontable">';
			
			foreach($this->actions as $key_2 => $v) {
				if(!is_array($v)) {
					$html = new htmlobject_input();
					$html->id = uniqid('p');
					$html->name = $this->actions_name;
					$html->value = $v;
					$html->type = 'submit';
					$str .= $html->get_string();
				}
				if(is_array($v)) {
					$html = new htmlobject_button();
					$html->id = uniqid('p');
					$html->name = $this->actions_name;
					$html->value = $v['value'];
					$html->label = $v['label'];
					$html->type = 'submit';
					$str .= $html->get_string();
				}
			}

			$str .= '</div>';
			$td->text = $this->get_select().''.$str.'<div style="line-height:0px;clear:both;">&#160;</div>';
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
		$tr->css = $this->css_prefix.'tr';
		$tr->id = 'tr_'. uniqid();
		
		$td = new htmlobject_td();
		$td->colspan = $this->_num_cols;
		$td->type = 'td';
		$td->css = $this->css_prefix.'td head';
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
		$tr->css = $this->css_prefix.'tr';
		$tr->id = 'tr_'. uniqid();
		
		$td = new htmlobject_td();
		$td->colspan = $this->_num_cols;
		$td->type = 'td';
		$td->css = $this->css_prefix.'td head';
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
	$_str = '';
		if($this->sort != '') {
			foreach($this->head as $key_2 => $v) {
				if(!isset($v['title'])) {
					$v['title'] = '';
				}
				if(isset($v['sortable']) == false) {
					$v['sortable'] = true;
				} 
				if($v['sortable'] == true) {
					$value[] = array("value" => $key_2, "label" => $v['title']);
				}
			}
			$sort = new htmlobject_select();
			$sort->id = uniqid('p');
			$sort->name = $this->_var_prefix.'sort';
			$sort->text_index = array("value" => "value", "text" => "label");
			$sort->text = $value;
			$sort->selected = array($this->sort);
			$str_sort = '<label for="'.$sort->id.'">'.$this->lang['label_sort'].$sort->get_string().'</label>';
			
			$order = new htmlobject_select();
			$order->id = uniqid('p');
			$order->name = $this->_var_prefix.'order';
			$order->text_index = array("value" => "value", "text" => "text");
			$order->text = array(array("value" => "ASC", "text" => "ASC"),array("value" => "DESC", "text" => "DESC"));
			$order->selected = array($this->order);
			$str_order = $order->get_string();

			if (count($this->limit_select) <= 0) {
			$this->limit_select = array(
				array("value" => 0, "text" => $this->lang['option_nolimit']),
				array("value" => 10, "text" => 10),
				array("value" => 20, "text" => 20),
				array("value" => 30, "text" => 30),
				array("value" => 40, "text" => 40),
				array("value" => 50, "text" => 50),
				);
			}
			$limit_input = new htmlobject_select();
			$limit_input->id = uniqid('p');
			$limit_input->name = $this->_var_prefix.'limit';
			$limit_input->text_index = array("value" => "value", "text" => "text");
			$limit_input->text = $this->limit_select;
			$limit_input->selected = array($this->limit);
			$str_limit = '<label for="'.$limit_input->id.'">'.$this->lang['label_limit'].$limit_input->get_string().'</label>';
			
			$offset_input = new htmlobject_input();
			$offset_input->id = uniqid('p');
			$offset_input->name = $this->_var_prefix.'offset';
			$offset_input->value = "$this->offset";
			$offset_input->type = 'text';
			$offset_input->size = 3;
			$str_offset = '<label for="'.$offset_input->id.'">'.$this->lang['label_offset'].$offset_input->get_string().'</label>';
			
			$max_input = new htmlobject_input();
			$max_input->name = $this->_var_prefix.'max';
			$max_input->value = $this->max;
			$max_input->type = 'hidden';
			
			$action = new htmlobject_input();
			$action->id = uniqid('p');
			$action->name =  $this->_var_prefix.'action';
			$action->value = $this->lang['button_refresh'];
			$action->type = 'submit';
			
			$_str = '<div class="sort_box">';
			$_str .= $max_input->get_string().
						$str_sort.
						$str_order.
						$str_offset.
						$str_limit.
						$action->get_string();
			$_str .= '<div style="line-height:0px;clear:both;">&#160;</div>';
			$_str .= '</div>';
		}
	return $_str;
	}
	//----------------------------------------------------------------------------------------
	/**
	* returns page turn functions
	* @access public
	* @return string
	*/
	//----------------------------------------------------------------------------------------
	function get_pageturn() {
	$_str = '';
		if($this->sort != '') {
			
			$first = new htmlobject_input();
			$first->id = uniqid('p'); 
			$first->name =  $this->_var_prefix.'action';
			$first->value = '<<';
			$first->type = 'submit';
			
			$prev = new htmlobject_input();
			$prev->id = uniqid('p');
			$prev->name =  $this->_var_prefix.'action';
			$prev->value = '<';
			$prev->type = 'submit';
						
			$next = new htmlobject_input();
			$next->id = uniqid('p');
			$next->name =  $this->_var_prefix.'action';
			$next->value = '>';
			$next->type = 'submit';

			$last = new htmlobject_input();
			$last->id = uniqid('p');
			$last->name =  $this->_var_prefix.'action';
			$last->value = '>>';
			$last->type = 'submit';
			
			if($this->limit == 0) { $this->limit = $this->max; }
			
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

			$_str .= '<div class="pageturn_box">';
			$_str .= '<table class="pageturn_table" cellpadding="0" cellspacing="0"><tr>';
			$_str .= '  <td class="pageturn_left">'.$first->get_string().$prev->get_string().'</td>';
			$_str .= '  <td class="pageturn_middle">';
			$_str .= '    <span>'.( $this->offset + 1 ).'</span> - '; 
			$_str .= '    <span>'.$max.'</span> / ';
			$_str .= '    <span>'.$this->max.'</span>';
			$_str .= '  </td>';
			$_str .= '  <td class="pageturn_right">'.$next->get_string().$last->get_string().'</td>';
			$_str .= '</tr></table>';
			$_str .= '</div>';
		}
	return $_str;
	}	
	//----------------------------------------------------------------------------------------
	/**
	* returns identifier multi select functions 
	* @access public
	* @return string
	*/
	//----------------------------------------------------------------------------------------
	function get_select() {
	$_str = '';
		if($this->identifier_type == 'checkbox' && $this->identifier != '') {
			$_str .= '<div class="selecttable" id="'.$this->_var_prefix.'SelectTable" style="display:none;">';
			$_str .= $this->lang['select_label'];
			$_str .= ' <a href="javascript:'.$this->_var_prefix.'select(\'all\');">'.$this->lang['select_all'].'</a>'."\n";
			$_str .= ' <a href="javascript:'.$this->_var_prefix.'select(\'none\');">'.$this->lang['select_none'].'</a>'."\n";
			$_str .= ' <a href="javascript:'.$this->_var_prefix.'select(\'invert\');">'.$this->lang['select_invert'].'</a>'."\n";
			$_str .= '<script type="text/javascript">'."\n";
			$_str .= 'document.getElementById("'.$this->_var_prefix.'SelectTable").style.display = "inline"'."\n";
			$_str .= 'function '.$this->_var_prefix.'select(arg) {'."\n";
			$_str .= '  if(arg == "all") {'."\n";
			$_str .= '    for(i = 0; i < document.getElementsByName("'.$this->identifier_name.'[]").length; i++)  {'."\n";
			$_str .= '      document.getElementsByName("'.$this->identifier_name.'[]")[i].checked = true;'."\n";			
			$_str .= '    }'."\n";			
			$_str .= '  }'."\n";
			$_str .= '  if(arg == "none") {'."\n";
			$_str .= '    for(i = 0; i < document.getElementsByName("'.$this->identifier_name.'[]").length; i++)  {'."\n";
			$_str .= '      document.getElementsByName("'.$this->identifier_name.'[]")[i].checked = false;'."\n";			
			$_str .= '    }'."\n";			
			$_str .= '  }'."\n";
			$_str .= '  if(arg == "invert") {'."\n";
			$_str .= '    for(i = 0; i < document.getElementsByName("'.$this->identifier_name.'[]").length; i++)  {'."\n";
			$_str .= '      if(document.getElementsByName("'.$this->identifier_name.'[]")[i].checked == false) {'."\n";
			$_str .= '        document.getElementsByName("'.$this->identifier_name.'[]")[i].checked = true;'."\n";			
			$_str .= '      } else {'."\n";
			$_str .= '        document.getElementsByName("'.$this->identifier_name.'[]")[i].checked = false;'."\n";
			$_str .= '      }'."\n";
			$_str .= '    }'."\n";
			$_str .= '  }'."\n";
			$_str .= '}'."\n";
			$_str .= '</script>'."\n";
			$_str .= '</div>'."\n";
		}
	return $_str;
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
		if($this->identifier != '' && in_array($this->identifier, array_keys($this->_body[$key]))) {
			$html = new htmlobject_input();
			$html->id = $ident;
			$html->name = $this->identifier_name.'[]';
			$html->value = $this->_body[$key][$this->identifier];
			$html->type = $this->identifier_type;
			if(in_array($this->_body[$key][$this->identifier], $this->identifier_checked)) {
				$html->checked = true;
			}
			if(in_array( $this->_body[$key][$this->identifier], $this->identifier_disabled)) {
				$html = new htmlobject_div();
				$html->text = '&#160;';
			}
					
			$td = new htmlobject_td();
			$td->type = 'td';
			$td->css = $this->css_prefix.'td identifier '.$this->identifier_name;
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
	$_str = '';
		$id_1 = '';
		$id_2 = '';
		if($this->identifier != '') {
			$id_1 = 'try { document.getElementById(arg).checked = true; } catch(e) {}';
			$id_2 = 'try { document.getElementById(arg).checked = false; } catch(e) {}';
		}
		$_str .= "\n";
		$_str .= '<script type="text/javascript">'."\n";
		$_str .= 'function tr_hover(element) {'."\n";
		$_str .= '	x = element.className.match(/tr_hover/g);'."\n";
		$_str .= '	if(x == null) {	element.className = element.className + " tr_hover"; }'."\n";
		$_str .= '	else { element.className = element.className.replace(/ tr_hover/g, "");	}'."\n";
		$_str .= '}'."\n";
		$_str .= 'function tr_click(element, arg) {'."\n";
		$_str .= '	x = element.className.match(/tr_click/g);'."\n";
		$_str .= '	if(x == null) {	element.className = element.className + " tr_click";'; 
		$_str .= '	'.$id_1.' }'."\n";
		$_str .= '	else { element.className = element.className.replace(/ tr_click/g, "");';	
		$_str .= '	'.$id_2.' }'."\n";
		$_str .= '}'."\n";
		$_str .= '</script>'."\n";
	return $_str;
	}
	//----------------------------------------------------------------------------------------
	/**
	* returns JS for tr
	* @access public
	* @return string
	*/
	//----------------------------------------------------------------------------------------	
	function  get_js_tr($ident) {
		$script = 'onmouseover="tr_hover(this);" onmouseout="tr_hover(this);"';
		#if($this->identifier != '') {
			$script .= ' onclick="tr_click(this, \''.$ident.'\')"';
		#}
		return $script;
	}

	//----------------------------------------------------------------------------------------
	/**
	* builds html table object
	* @access public
	* @return object
	*/
	//----------------------------------------------------------------------------------------
	function get_object() {

		$this->init_table();

		if( is_array($this->_body) && count($this->_body) > 0) {

			$table              = new htmlobject_table();
			$table->css         = $this->css;
			$table->style       = $this->style;
			$table->title       = $this->title;
			$table->handler     = $this->handler;
			$table->id          = $this->id;
			$table->align       = $this->align;
			$table->border      = $this->border;
			$table->bgcolor     = $this->bgcolor;
			$table->cellpadding = $this->cellpadding;
			$table->cellspacing = $this->cellspacing;
			$table->frame       = $this->frame;
			$table->rules       = $this->rules;
			$table->summary     = $this->summary;
			$table->width       = $this->width;		

			// build table
			// build additional table head
			foreach ($this->_headrow as $row) {
				$row->arr_tr[0]->colspan = $this->_num_cols;
				$table->add($row);
			}
			// build sort functions	
			if($this->sort != '') {
				$td = new htmlobject_td();
				$td->colspan = $this->_num_cols;
				$td->type = 'td';
				$td->css = $this->css_prefix.'td pageturn_head';
				$td->text = $this->get_sort().$this->get_pageturn();

				$tr = new htmlobject_tr();
				$tr->css = $this->css_prefix.'tr pageturn_head';
				$tr->id = 'tr_'. uniqid();
				$tr->add($td);
				$table->add($tr);
			}		
			// build table head		
			$table->add($this->get_table_head());
	
			// build table body
			$i = 'odd';
			foreach ($this->_body as $key => $value) {
				$table->add($this->get_table_body($key, $value, $i));
				if($i == 'odd') $i = 'even';
				else  $i = 'odd';
			}
			// build table actions
			$table->add($this->get_table_actions());

			// insert bottom pageturn
			if(count($this->_body) > 9 && $this->limit < $this->max && $this->sort != '') {
				$td = new htmlobject_td();
				$td->colspan = $this->_num_cols;
				$td->type = 'td';
				$td->css = $this->css_prefix.'td pageturn_bottom';
		
				$tr = new htmlobject_tr();
				$tr->css = $this->css_prefix.'tr pageturn_bottom';
				$tr->id = 'tr_'. uniqid();		
				$td->text = $this->get_pageturn();
				$tr->add($td);
				$table->add($tr);
			}
		
			foreach ($this->_bottomrow as $row) {
				$row->arr_tr[0]->colspan = $this->_num_cols;
				$table->add($row);
			}
			return $table;
		} else {
			return null;
		}

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
	function get_string( ) {
	$_str = '';
		$table = $this->get_object();
		if($table) {
			$_str = $this->get_js();
			// build form
			($this->form_action != '') ? $_str .= '<form action="'.$this->form_action.'" method="GET">' : null;
			$_str .= $table->get_string();
			($this->form_action != '') ? $_str .= '</form>' : null;
		} else {
			$_str = '<div>'.$this->lang['no_data'].'</div>';
		}

	return $_str;
	}

}//-- end class
?>
