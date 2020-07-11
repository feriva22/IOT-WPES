<?php
    //import required data
    require_once('helper/helper.php');
    require_once('db/Mysql.php');
   
    $secret = 'inisecret';

    $db_con = new Mysql();
    $db_con->dbConnect();

    $user_secret = data_post('secret');
    $device_id = data_post('device');
    $sensor1 = data_post('sensor1');
    $sensor2 = data_post('sensor2');
    
    if($user_secret == NULL)
        json_response('success','Empty secret!');
    if($secret !== $user_secret)
        json_response('success','Wrong secret!');
    
    $result = $db_con->get('sensor',TRUE,array('id' => $device_id));
    if($result == NULL){
        json_response('error','No Device ID on System');
    }

    $update = array(
        'last_update' => date('Y-m-d H:i:s'),
        'sensor1' => $sensor1,
        'sensor2' => $sensor2
    );
    
    if($db_con->update_multiple_column($update,'sensor','id',$device_id)){
        json_response('success','Sukses update data');
    }else{
        json_response('success','Gagal update ');
    }
    
    

?>