<?php
//--------------------------------------------------------------------------------------------------------------------------
// data_destroy.ejs.php
// v0.0.1
// Under GPLv3 License
//
// Integrated by: Gi Technologies. in 2011
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
// Database object
// *************************************************************************************
$mitos_db = new dbHelper();

// *************************************************************************************
// Flag the message to delete
// *************************************************************************************
$data = json_decode ( $_REQUEST['row'] );
$delete_id = $data->item_id;

// *************************************************************************************
// Finally build the Delete SQL Statement and inject it to the SQL Database
// *************************************************************************************
$sql = "DELETE FROM layout_options WHERE item_id='" . $delete_id . "'";
$mitos_db->setSQL($sql);
$ret = $mitos_db->execLog();

if ( $ret == "" ){
	echo '{ success: false, errors: { reason: "'. $ret[2] .'" }}';
} else {
	echo "{ success: true }";
}

?>