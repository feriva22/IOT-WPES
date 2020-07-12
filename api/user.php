<?php
include 'protected.php';
include_once '../db/Mysql.php';
require_once '../helper/helper.php';


$MysqlService = new Mysql();
$conn = $MysqlService->dbConnect();

$table_name = 'users';

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
else if($method === 'POST'){
	echo 'request post';
} 
else if($method === 'PATCH'){
	$params = $_GET['iduser'];
	$payload = json_decode(file_get_contents("php://input"));
	if(isset($params) && $params !== "" && $payload !== NULL){
		//no validation now
		$MysqlService->update_multiple_column((array) $payload,$table_name,'iduser',intval($params));
		json_response('ok','Success update data');
	} else {
		json_response('ok','Invalid Parameter',null,null,400);
	}
}
else if($method === 'DELETE'){
	$params = $_GET['iduser'];
	if(isset($params) && $params !== "" ){
		//no validation now
		$data_query = $MysqlService->get($table_name,TRUE,['iduser' => intval($params)]);
		if($data_query !== NULL){
			$MysqlService->delete($table_name,'iduser = '.intval($params));
			json_response('ok','Success delete data');
		} else {
			json_response('ok','User ID not found',null,null,404);
		}
	} else {
		json_response('ok','Invalid Parameter',null,null,400);
	}
}
else {
	http_response_code(400);
	echo json_encode(array('message' => 'Not allowed Method'));
}

?>