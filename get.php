<?php
    //import required data
    require_once('helper/helper.php');
    require_once('db/Mysql.php');
   
    $device_id = data_get('device');

    /*
    if($device_id == NULL){
        json_response('error','No Device ID');
    }
    */

    $db_con = new Mysql();
    $db_con->dbConnect();

    if(!$db_con->isConnected()){
        die("Connection failed to database");
    }

    $result = $db_con->get('sensor',TRUE,($device_id !== NULL) ? array('id' => $device_id) : NULL);
    
    if($result !== NULL){
        json_response('success','Success Get Data',$result);
    }else{
        json_response('Success','0 Results');
    }

?>