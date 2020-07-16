<?php

include_once '../db/Mysql.php';
require "../vendor/autoload.php";
require "../helper/helper.php";
use \Firebase\JWT\JWT;

cors(); //for enable cors

$email = '';
$password = '';

$MysqlService = new Mysql();
$conn = $MysqlService->dbConnect();

$data = json_decode(file_get_contents("php://input"));
if($data == NULL){
	http_response_code(400);
	echo json_encode(array('message' => 'invalid parameters'));
	exit();
}
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

	//set last_login 
	$MysqlService->update_single_column('last_login',date('Y-m-d H:i:s'),$table_name,'iduser',$data_query->iduser);

	$secret_key = 'AIZAKMIANJRITLAHKAOO';
	$issuer_claim = "wemos.mooo.com";
	$audience_claim = "HESOYAM";
	$issuedate_claim = time();
	$notbefore_claim = $issuedate_claim; //start usage of token
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
		"id" => $data_query->iduser,
		"email" => $email,
		"expireAt" => $expire_claim
	));

} else {
	http_response_code(403);
	echo json_encode(array("result" => "OK","message" => "Login Failed"));
}

?>