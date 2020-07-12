<?php

include_once '../db/Mysql.php';
require "../vendor/autoload.php";
use \Firebase\JWT\JWT;

header("Access-Control-Allow-Origin: * ");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


$email = '';
$password = '';

$MysqlService = new Mysql();
$conn = $MysqlService->dbConnect();

$data = json_decode(file_get_contents("php://input"));

$email = $data->email;
$password = $data->password;

$table_name = 'users';

$data_query = $MysqlService->get($table_name,TRUE,array(
	'email' => "'{$email}'"
));


if($data_query !== NULL){
	if(!password_verify($password,$data_query->password)){
		http_response_code(403);
		echo json_encode(array('message' => 'Email or Password Wrong'));
		exit;
	}

	$secret_key = 'AIZAKMIANJRITLAHKAOO';
	$issuer_claim = "wemos.mooo.com";
	$audience_claim = "HESOYAM";
	$issuedate_claim = time();
	$notbefore_claim = $issuedate_claim + 10; //not before in seconds
	$expire_claim = $issuedate_claim + 3600; //expire time in seconds
	$token = array(
		"iss" => $issuer_claim,
		"aud" => $audience_claim,
		"iat" => $issuedate_claim,
		"nbf" => $notbefore_claim,
		"exp" => $expire_claim,
		"data" => array(
			"id" => $data_query->iduser,
			"email" => $data_query->email,
			"fullname" => $data_query->full_name
		)
	);

	http_response_code(200);

	$jwt = JWT::encode($token,$secret_key);
	$refresh_token = uniqid("",TRUE);
	$refresh_tokenexp = $issuedate_claim + 7200; //1 hour for refresh token
	$MysqlService->update_multiple_column([
		'refresh_token' => "$refresh_token",
		'refresh_tokenexpired' => date('Y-m-d H:i:s',$refresh_tokenexp)
	],$table_name,'iduser',$data_query->iduser);

	echo json_encode(array(
		"message" => "Successfull Login",
		"access_token" => $jwt,
		"refresh_token" => $refresh_token,
		"email" => $email,
		"expireAt" => $expire_claim
	));

} else {
	http_response_code(401);
	echo json_encode(array("message" => "Login Failed"));
}

?>