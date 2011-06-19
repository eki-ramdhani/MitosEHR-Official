<?php
//--------------------------------------------------------------------------------------------------------------------------
// data_create.ejs.php
// v0.0.2
// Under GPLv3 License
//
// Integrated by: GI Technologies Inc. in 2011
//
// Remember, this file is called via the Framework Store, this is the AJAX thing.
//--------------------------------------------------------------------------------------------------------------------------

session_name ( "MitosEHR" );
session_start();
session_cache_limiter('private');

include_once($_SESSION['site']['root']."/library/dbHelper/dbHelper.inc.php");
include_once($_SESSION['site']['root']."/library/I18n/I18n.inc.php");
require_once($_SESSION['site']['root']."/repository/dataExchange/dataExchange.inc.php");

//******************************************************************************
// Reset session count 10 secs = 1 Flop
//******************************************************************************
$_SESSION['site']['flops'] = 0;

// *************************************************************************************
// Parce the data generated by EXTJS witch is JSON
// *************************************************************************************
$data = json_decode ( $_REQUEST['row'] );

$mitos_db = new dbHelper();

// *************************************************************************************
// dataTypes - Defines what type of fields are.
// This is just a reverse thing, translate the dataTypes into numbers.
// *************************************************************************************
$dataTypes = array(
	i18n("List box", 'r') 			=> "1", 
	i18n("Textbox", 'r') 			=> "2",
	i18n("Textarea", 'r') 			=> "3",
	i18n("Text-date", 'r') 			=> "4",
	i18n("Providers", 'r') 			=> "10",
	i18n("Providers NPI", 'r') 		=> "11",
	i18n("Pharmacies", 'r') 		=> "12",
	i18n("Squads", 'r') 			=> "13",
	i18n("Organizations", 'r') 		=> "14",
	i18n("Billing codes", 'r') 		=> "15",
	i18n("Checkbox list", 'r') 		=> "21",
	i18n("Textbox list", 'r') 		=> "22",
	i18n("Exam results", 'r') 		=> "23",
	i18n("Patient allergies", 'r') 	=> "24",
	i18n("Checkbox w/text", 'r') 	=> "25",
	i18n("List box w/add", 'r') 	=> "26",
	i18n("Radio buttons", 'r') 		=> "27",
	i18n("Lifestyle status", 'r') 	=> "28",
	i18n("Static Text", 'r') 		=> "31",
	i18n("Smoking Status", 'r') 	=> "32",
	i18n("Race and Ethnicity", 'r') => "33"
);

// *************************************************************************************
// UOR
// This is just a reverse thing, translate the dataTypes into numbers.
// *************************************************************************************
$uorTypes = array(
	i18n('Unused', 'r') 	=> 0, 
	i18n('Optional', 'r') 	=> 1, 
	i18n('Required', 'r') 	=> 2
);

// *************************************************************************************
// Reverse the list_option to get the number and inject it, into the database
// *************************************************************************************
if ($_SESSION['lang']['code'] == "en_US") { // If the selected language is English, do not translate
	$mitos_db->setSQL("SELECT 
						*
						FROM 
							list_options 
						WHERE 
							list_id = 'lists' AND title='".$data->listDesc."'
						ORDER BY 
							seq");
} else {
	// If a language is selected, translate the list.
	$mitos_db->setSQL("SELECT 
						lo.id,
						lo.list_id,
						lo.option_id, 
						IF(LENGTH(ld.definition),ld.definition,lo.title) AS title ,
						lo.seq,
						lo.is_default,
						lo.option_value,
						lo.mapping,
						lo.notes 
					FROM 
						list_options AS lo 
						LEFT JOIN lang_constants AS lc ON lc.constant_name = lo.title 
						LEFT JOIN lang_definitions AS ld ON ld.cons_id = lc.cons_id AND ld.lang_id = '" . $_SESSION['lang']['code'] . "
					WHERE 
						lo.list_id = 'lists' AND title='".$data->listDesc."'
					ORDER BY 
						IF(LENGTH(ld.definition),ld.definition,lo.title), lo.seq");
}
// *************************************************************************************
// start the array
// *************************************************************************************
$reverse_list = array();
foreach($mitos_db->execStatement() as $row){
	array_push($reverse_list, $row);
}

// *************************************************************************************
// Validate and pass the POST variables to an array
// This is the moment to validate the entered values from the user
// although Sencha EXTJS make good validation, we could check again 
// just in case 
// *************************************************************************************
$row = array();
$row['form_id'] 		= $data->form_id;
$row['field_id'] 		= $data->field_id;
$row['group_name'] 		= $data->group_name;
$row['title'] 			= $data->title;
$row['seq'] 			= $data->seq;
$row['data_type'] 		= $dataTypes[$data->data_type]; // Reverse
$row['uor'] 			= $uorTypes[$data->uor]; // Reverse
$row['fld_length'] 		= $data->fld_length;
$row['max_length'] 		= $data->max_length;
$row['list_id'] 		= $reverse_list[0]['option_id'];
$row['titlecols'] 		= $data->titlecols;
$row['datacols'] 		= $data->datacols;
$row['default_value'] 	= $data->default_value;
$row['edit_options'] 	= $data->edit_options;
$row['description'] 	= $data->description;
$row['group_order'] 	= $data->group_order;

// *************************************************************************************
// Finally that validated POST variables is inserted to the database
// This one make the JOB of two, if it has an ID key run the UPDATE statement
// if not run the INSERT statement
// *************************************************************************************
$sql = $mitos_db->sqlBind($row, "layout_options", "I");
$mitos_db->setSQL($sql);
$ret = $mitos_db->execLog();

if ( $ret[2] <> "" ){
	echo '{ success: false, errors: { reason: "'. $ret[2] .'" }}';
} else {
	echo "{ success: true }";
}
?>