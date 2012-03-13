<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Ernesto J. Rodriguez (Certun)
 * File: Encounter.php
 * Date: 1/21/12
 * Time: 3:26 PM
 */
if(!isset($_SESSION)){
    session_name ("MitosEHR");
    session_start();
    session_cache_limiter('private');
}

include_once('Patient.php');
include_once('User.php');
include_once('dbHelper.php');

class Encounter {
    /**
     * @var dbHelper
     */
    private $db;
    /**
     * @var User
     */
    private $user;
    /**
     * @var Patient
     */
    private $patient;

    function __construct()
    {
        $this->db = new dbHelper();
        $this->user = new User();
        $this->patient = new Patient();
        return;
    }
    /**
     * @return array
     * NOTES: What is ck?
     *  Naming: "checkOpenEncounters"
*/
    public function checkOpenEncounters()
    {
        $pid =  $_SESSION['patient']['pid'];

        $fields[]   = "*";
        $where[]    = "pid = '" . $pid . "'";
        $where[]    = "close_date IS NULL";

        $this->db->setSQL( $this->db->sqlSelectBuilder("form_data_encounter", $fields, "", $where) );
        $total = $this->db->rowCount();
        if($total >= 1){
            return array('encounter' => true);
        }else{
            return array('encounter' => false);
        }
    }

    /**
     * @param stdClass $params
     * @return array
     *  Naming: "getPatientEncounters"
     */
    public function getEncounters(stdClass $params)
    {
        if(isset($params->sort)){
            $ORDER = 'ORDER BY ' . $params->sort[0]->property . ' ' . $params->sort[0]->direction;
        } else {
            $ORDER = 'ORDER BY start_date DESC';
        }

        $pid = $_SESSION['patient']['pid'];
        $this->db->setSQL("SELECT * FROM form_data_encounter WHERE pid = '$pid' ".$ORDER);
        $rows = array();
        foreach($this->db->fetchRecords(PDO::FETCH_ASSOC) as $row){
            $row['status'] = ($row['close_date']== null)? 'open' : 'close';
        	array_push($rows, $row);
        }

        return $rows;

    }

    /**
     * @param stdClass $params
     * @return array
     *  Naming: "createPatientEncounters"
     */
    public function createEncounter(stdClass $params)
    {
        $params->pid        = $_SESSION['patient']['pid'];
        $params->open_uid   = $_SESSION['user']['id'];

        $data = get_object_vars($params);
        foreach($data as $key => $val){
            if($val == '') {
                unset($data[$key]);
            }
        }

        $data['start_date'] = $this->parseDate($data['start_date']);

        $sql = $this->db->sqlBind($data, "form_data_encounter", "I");
        $this->db->setSQL($sql);
        $this->db->execLog();
        $eid = $this->db->lastInsertId;

        $default = array('pid'=>$params->pid, 'eid'=>$eid);

        $this->db->setSQL($this->db->sqlBind($default, "form_data_review_of_systems", "I"));
        $this->db->execOnly();
        $this->db->setSQL($this->db->sqlBind($default, "form_data_review_of_systems_check", "I"));
        $this->db->execOnly();
        $this->db->setSQL($this->db->sqlBind($default, "form_data_soap", "I"));
        $this->db->execOnly();
        $this->db->setSQL($this->db->sqlBind($default, "form_data_dictation", "I"));
        $this->db->execOnly();

        $params->eid = intval($eid);

        return array('success'=>true,'encounter'=>$params);
    }

    /**
     * @param stdClass $params
     * @return array|mixed
     *  Naming: "getPatientEncounters"
     */
    public function getEncounter(stdClass $params)
    {
        $this->db->setSQL("SELECT * FROM form_data_encounter WHERE eid = '$params->eid'");
        $encounter = $this->db->fetchRecord(PDO::FETCH_ASSOC);

        $encounter['vitals']                = $this->getVitalsByPid($encounter['pid']);
        $encounter['reviewofsystems']       = $this->getReviewOfSystemsByEid($params->eid);
        $encounter['reviewofsystemschecks'] = $this->getReviewOfSystemsChecksByEid($params->eid);
        $encounter['soap']                  = $this->getSoapByEid($params->eid);
        $encounter['speechdictation']       = $this->getDictationByEid($params->eid);


        if($encounter != null){
            return array("success" => true, 'encounter' => $encounter);
        }else{
            return array("success" => false);
        }
    }
    /**
     * @param stdClass $params
     * @return array|mixed
     */
    public function updateEncounter(stdClass $params)
    {
        return array("success" => true, 'encounter' => $params);
    }

    /**
     * @param stdClass $params
     * @return array
     *  Naming: "getPatientVitals"
     */
    public function closeEncounter(stdClass $params)
    {
        $data['close_date'] = $params->close_date;
        $data['close_uid'] = $_SESSION['user']['id'];

        if($this->user->verifyUserPass($params->signature)){
            $sql = $this->db->sqlBind($data, "form_data_encounter", "U", "eid='".$params->eid."'");
            $this->db->setSQL($sql);
            $this->db->execLog();
            return array('success'=> true);
        }else{
            return array('success'=> false);
        }
    }
    /**
     * @param $pid
     * @return array
     */
    public function getVitalsByPid($pid)
    {
        $this->db->setSQL("SELECT * FROM form_data_vitals WHERE pid = '$pid' ORDER BY date DESC");
        $rows = array();
        foreach($this->db->fetchRecords(PDO::FETCH_ASSOC) as $row){
            $row['height_in'] = intval($row['height_in']);
            $row['height_cn'] = intval($row['height_cn']);
            $row['administer'] = $this->user->getUserNameById($row['uid']);
            array_push($rows, $row);
        }
        return $rows;
    }
    /**
     * @param $eid
     * @return array
     */
    public function getVitalsByEid($eid)
    {
        $this->db->setSQL("SELECT * FROM form_data_vitals WHERE eid = '$eid' ORDER BY date DESC");
        $rows = array();
        foreach($this->db->fetchRecords(PDO::FETCH_ASSOC) as $row){
            $row['height_in'] = intval($row['height_in']);
            $row['height_cn'] = intval($row['height_cn']);
            $row['administer'] = $this->user->getUserNameById($row['uid']);
            array_push($rows, $row);
        }
        return $rows;
    }

    /**
     * @param stdClass $params
     * @return array
     */
    public function getVitals(stdClass $params)
    {
        $pid =  (isset($params->pid)) ? $params->pid : $_SESSION['patient']['pid'];
        $vitals = $this->getVitalsByPid($pid);
        if(count($vitals) >= 1){
            return $vitals;
        }else{
            return array("success" => true);
        }
    }
    /**
     * @param stdClass $params
     * @return stdClass
     */
    public function addVitals(stdClass $params)
    {
        $data = get_object_vars($params);
        unset($data['administer']);
        $data['date'] = $this->parseDate($data['date']);
        $sql = $this->db->sqlBind($data, 'form_data_vitals', 'I');
        $this->db->setSQL($sql);
        $this->db->execLog();
        return $params;
    }
    /**
     * @param $eid
     * @return array
     */
    public function getSoapByEid($eid)
    {
        $this->db->setSQL("SELECT * FROM form_data_soap WHERE eid = '$eid' ORDER BY date DESC");
        return $this->db->fetchRecords(PDO::FETCH_ASSOC);
    }
    /**
     * @param $eid
     * @return array
     */
    public function getReviewOfSystemsChecksByEid($eid)
    {
        $this->db->setSQL("SELECT * FROM form_data_review_of_systems_check WHERE eid = '$eid' ORDER BY date DESC");
        return $this->db->fetchRecords(PDO::FETCH_ASSOC);
    }
    /**
     * @param $eid
     * @return array
     */
    public function getReviewOfSystemsByEid($eid)
    {
        $this->db->setSQL("SELECT * FROM form_data_review_of_systems WHERE eid = '$eid' ORDER BY date DESC");
        $record = $this->db->fetchRecord();
        foreach($record as $key => $val){
            $record[$key] =  ($val == null)? 'null' : $val;
        }
        return $record;
    }
    /**
     * @param $eid
     * @return array
     */
    public function getDictationByEid($eid)
    {
        $this->db->setSQL("SELECT * FROM form_data_dictation WHERE eid = '$eid' ORDER BY date DESC");
        return $this->db->fetchRecords(PDO::FETCH_ASSOC);
    }
    /**
     * @param stdClass $params
     * @return stdClass
     */
    public function updateSoapById(stdClass $params){
        $data = get_object_vars($params);
        unset($data['id']);
        $this->db->setSQL($this->db->sqlBind($data, 'form_data_soap', 'U', "id='".$params->id."'"));
        $this->db->execLog();
        return $params;
    }
    /**
     * @param stdClass $params
     * @return stdClass
     */
    public function updateReviewOfSystemsChecksById(stdClass $params)
    {
        $data = get_object_vars($params);
        unset($data['id']);
        $this->db->setSQL($this->db->sqlBind($data, 'form_data_review_of_systems_check', 'U', "id='".$params->id."'"));
        $this->db->execLog();
        return $params;
    }
    /**
     * @param stdClass $params
     * @return stdClass
     */
    public function updateReviewOfSystemsById(stdClass $params)
    {
        $data = get_object_vars($params);
        unset($data['id']);
        $this->db->setSQL($this->db->sqlBind($data, 'form_data_review_of_systems', 'U', "id='".$params->id."'"));
        $this->db->execLog();
        return $params;
    }
    /**
     * @param stdClass $params
     * @return stdClass
     */
    public function updateDictationById(stdClass $params)
    {
        $data = get_object_vars($params);
        unset($data['id']);
        $this->db->setSQL($this->db->sqlBind($data, 'form_data_dictation', 'U', "id='".$params->id."'"));
        $this->db->execLog();
        return $params;
    }
    /**
     * @param $eid
     * @return array
     *  Naming: "closePatientEncounter"
     */
    public function getProgressNoteByEid($eid)
    {
        $this->db->setSQL("SELECT * FROM form_data_encounter WHERE eid = '$eid'");
        $encounter = $this->db->fetchRecord(PDO::FETCH_ASSOC);
        $encounter['start_date']            = date('F j, Y, g:i a',strtotime($encounter['start_date']));
        $encounter['patient_name']          = $this->patient->getPatientFullNameByPid($encounter['pid']);
        $encounter['open_by']               = $this->user->getUserNameById($encounter['open_uid']);
        $encounter['signed_by']             = $this->user->getUserNameById($encounter['close_uid']);

        /**
         * Add vitals to progress note
         */
        $vitals = $this->getVitalsByEid($eid);
        if(count($vitals)) $encounter['vitals'] = $vitals;

        /**
         * Add Review of Systems to progress note
         */
        $ros = $this->getReviewOfSystemsByEid($eid);
        $foo = array();
        foreach($ros as $key => $value){
            if($key != 'id' && $key != 'pid' && $key != 'eid' && $key != 'uid' && $key != 'date'){
                if($value != null && $value != 'null'){
                    $value = ($value == 1 || $value == '1')? 'Yes' : 'No';
                    $foo[] = array('name' => $key, 'value' => $value);
                }
            }

        }
        if(!empty($foo)) $encounter['reviewofsystems'] = $foo;

        /**
         * Add Review of Systems Checks to progress note
         */
        $rosck = $this->getReviewOfSystemsChecksByEid($eid);
        $foo = array();
        foreach($rosck[0] as $key => $value){
            if($key != 'id' && $key != 'pid' && $key != 'eid' && $key != 'uid' && $key != 'date'){
                if($value != null && $value != 'null' && $value != '0' || $value != 0){
                    $value = ($value == 1 || $value == '1')? 'Yes' : 'No';
                    $foo[] = array('name' => $key, 'value' => $value);
                }
            }
        }
        if(!empty($foo)) $encounter['reviewofsystemschecks'] = $foo;


        /**
         * Add SOAP to progress note
         */
        $encounter['soap'] = $this->getSoapByEid($eid);

        /**
         *
         */
        $speech = $this->getDictationByEid($eid);
        if($speech[0]['dictation']){
            $encounter['speechdictation'] = $speech;
        }

        /**
         * return the encounter array of data
         */
        return $encounter;
    }
    /**
     * @param $date
     * @return mixed
     */
    public function parseDate($date)
    {
        return str_replace('T', ' ', $date);
    }

}

//$e = new Encounter();
//echo '<pre>';
//print_r($e->getProgressNoteByEid(7));
//print_r($e->getProgressNoteByEid(8));
