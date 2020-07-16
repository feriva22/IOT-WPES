<?php
include_once '../db/Mysql.php';
require "../vendor/autoload.php";
require "../helper/helper.php";
use \Firebase\JWT\JWT;

cors();


$secret_key = "AIZAKMIANJRITLAHKAOO";
$jwt = null;
$MysqlService = new Mysql();
$conn = $MysqlService->dbConnect();

$data = json_decode(file_get_contents("php://input"));


$authHeader = $_SERVER['HTTP_AUTHORIZATION'];

$arr = explode(" ", $authHeader);
$user_data = null;

$jwt = $arr[1];
if($jwt){
    try {
		$decoded = JWT::decode($jwt, $secret_key, array('HS256'));
		$data_query = $MysqlService->get('users',TRUE,[
			'email' => "'{$decoded->data->email}'"
		]);
		if($data_query == NULL){
			http_response_code(401);
    		echo json_encode(array(
    		    "message" => "Invalid Token."
			));
			exit();
		} else {
			$user_data = $data_query;	
		}
	} catch(\Firebase\JWT\ExpiredException $e){
		http_response_code(401);
		echo json_encode(array(
			"message" => "Access denied.",
			"error" => $e->getMessage(),
			"expired" => true
		));
		exit();
	}
	catch (Exception $e){
    http_response_code(401);
    echo json_encode(array(
        "message" => "Access denied.",
        "error" => $e->getMessage()
	));
	exit();
}
} else {
	http_response_code(400);
    echo json_encode(array(
        "message" => "Token Not Found."
	));
	exit();
}
?>