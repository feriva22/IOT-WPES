<?php
	include_once 'db/Mysql.php';
    $data_action = file_get_contents('php://input');

	$MysqlService = new Mysql();
	$conn = $MysqlService->dbConnect();
	$table_name = 'log_action';


    //echo $data_sensor;
    $curr_time = date("Y-m-d H:i:s");
    if(json_decode($data_action)){
        $body = json_decode($data_action);
        $type = $body->type;
		$device_id = $body->device_id;

		$id_users = $MysqlService->insert($table_name,[
			'action' => "'{$type}'",
			'time' => "'{$curr_time}'",
			'device_id' => $device_id
		]);
        echo json_encode(['action ' => $type, 'request_device_id' => $device_id]);
    } else {
        http_response_code(400);
        echo "Invalid Request";
    }

?>