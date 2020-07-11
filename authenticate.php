<?php

    require_once('helper/helper.php');
    require_once('db/Mysql.php');

    
    $db_con = new Mysql();
    $db_con->dbConnect();

    if(!$db_con->isConnected()){
        die("Connection failed to database");
    }

    $data_code_device = file_get_contents('php://input');

    //echo $data_sensor;
    $curr_time = date("Y-m-d");
    if(json_decode($data_code_device)){
        $body = json_decode($data_code_device);
        $code = $body->code;


       $result = $db_con->get('device',TRUE,array('code_registration' => "'".$code."'") );
       if($result !== NULL){
            echo json_encode(array('device_id' => $result->deviceId,'access_key' => $result->access_key));
       }else {
          http_response_code(404);
         echo "Missing code";
       }

    } else {
        http_response_code(400);
        echo "Invalid Request";
    }


?>