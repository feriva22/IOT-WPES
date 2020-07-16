<?php
include 'protected.php';
include_once '../db/Mysql.php';
require_once '../helper/helper.php';


$MysqlService = new Mysql();
$conn = $MysqlService->dbConnect();

$table_name = 'sensor';

//check request method
$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'GET') {
	$params = $_GET;
	if(count($params) == 0){
		$data_query = $MysqlService->get($table_name,FALSE);
		json_response('ok','Success Get data',$data_query);
	} 
	else {
		$id_device = $_GET['id_device'];
		$sensor_type = $_GET['sensor_type'];
		$start_date = (isset($_GET['start_date']) && $_GET['start_date'] !== "null" ? $_GET['start_date'] : null);
		$end_date = (isset($_GET['end_date']) && $_GET['end_date'] !== "null" ? $_GET['end_date'] : date('Y-m-d H:i:s'));
		$limit = isset($_GET['limit']) ? $_GET['limit'] : null;

		$sql = "SELECT * FROM `$table_name` where device_iddevice = $id_device ".
					  "AND type_sensor = '$sensor_type'".
					  (isset($start_date) ? "AND time > '$start_date'" : " ").
					  "AND time < '$end_date' ORDER BY time desc ".
					  (isset($limit) ? "LIMIT $limit" : "");
		$data_query = $MysqlService->customSelectQuery($sql);

		json_response('ok','Success Get data',$data_query);
	}
}
else if($method === 'PUT'){
	$_payload = json_decode(file_get_contents("php://input"));
	if($payload !== NULL){
		$MysqlService->insert($table_name,[
			'type_sensor' => $_payload->type_sensor,
			'value' => $payload->value,
			'device_iddevice' => $payload->device_id,
			'time' => date('Y-m-d H:i:s')
		]);
	} else {
		json_response('ok','Invalid parameter',null,null,400);
	}
} else {
	http_response_code(400);
	echo json_encode(array('message' => 'Not allowed Method'));
}
?>