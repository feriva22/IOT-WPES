<?php

    $data_action = file_get_contents('php://input');


    //echo $data_sensor;
    $curr_time = date("Y-m-d");
    if(json_decode($data_action)){
        $body = json_decode($data_action);
        $type = $body->type;
        $device_id = $body->device_id;

        echo json_encode(['action ' => $type, 'request_device_id' => $device_id]);
    } else {
        http_response_code(400);
        echo "Invalid Request";
    }

?>