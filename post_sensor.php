<?php
	include_once 'db/Mysql.php';
    $data_sensor = file_get_contents('php://input');

	$MysqlService = new Mysql();
	$conn = $MysqlService->dbConnect();
	$table_name = 'sensor';

    //echo $data_sensor;
    $curr_time = date("Y-m-d H:i:s");
    if(json_decode($data_sensor)){
        $body = json_decode($data_sensor);
        $water_level = $body->water_level;
        $turbidity = $body->turbidity;
		$device_id = $body->device_id;
		
		$idWater = $MysqlService->insert($table_name,[
			'type_sensor' => "'WATER_LEVEL'",
			'time' => "'{$curr_time}'",
			'device_iddevice' => $device_id,
			'value' => "'{$water_level}'"
		]);

		$idTurbidity = $MysqlService->insert($table_name,[
			'type_sensor' => "'TURBIDITY'",
			'time' => "'{$curr_time}'",
			'device_iddevice' => $device_id,
			'value' => "'{$turbidity}'"
		]);

        echo json_encode(['water_level' => $water_level, 'turbidity' => $turbidity, 'request_device_id' => $device_id]);
    } else {
        http_response_code(400);
        echo "Invalid Request";
    }

?>