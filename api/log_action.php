<?php
include 'protected.php';
include_once '../db/Mysql.php';
require_once '../helper/helper.php';


$MysqlService = new Mysql();
$conn = $MysqlService->dbConnect();

$table_name = 'log_action';

//check request method
$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'GET') {
	$params = $_GET;
	if(count($params) == 0){
		$data_query = $MysqlService->get($table_name,FALSE);
		json_response('ok','Success Get data',$data_query);
	} 
	else {
		$data_query = $MysqlService->get($table_name,FALSE,$params);
		json_response('ok','Success Get data',$data_query);
	}
}
else if($method === 'PUT'){
	$_payload = json_decode(file_get_contents("php://input"));
	if($payload !== NULL){
		$MysqlService->insert($table_name,[
			'value' => $payload->action,
			'device_id' => $payload->device_id,
			'time' => date('Y-m-d H:i:s')
		]);
	} else {
		json_response('ok','Invalid parameter',null,null,400);
	}
}else {
	http_response_code(400);
	echo json_encode(array('message' => 'Not allowed Method'));
}
?>