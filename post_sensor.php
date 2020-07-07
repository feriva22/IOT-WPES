<?php

    $data_sensor = file_get_contents('php://input');


    //echo $data_sensor;
    $curr_time = date("Y-m-d");
    if(json_decode($data_sensor)){
        $body = json_decode($data_sensor);
        $water_level = $body->water_level;
        $turbidity = $body->turbidity;
        $device_id = $body->device_id;

        echo json_encode(['water_level' => $water_level, 'turbidity' => $turbidity, 'request_device_id' => $device_id]);
    } else {
        http_response_code(400);
        echo "Invalid Request";
    }

?>