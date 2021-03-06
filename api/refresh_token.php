<?php
include_once '../db/Mysql.php';
require "../vendor/autoload.php";
require '../helper/helper.php';
use \Firebase\JWT\JWT;


cors();

$secret_key = "AIZAKMIANJRITLAHKAOO";
$issuer_claim = "wemos.mooo.com";
$audience_claim = "HESOYAM";

$jwt = null;
$MysqlService = new Mysql();
$conn = $MysqlService->dbConnect();

$data = json_decode(file_get_contents("php://input"));

$refresh_token = $data->refresh_token;

if(isset($refresh_token) && $refresh_token !== ""){
	$table_name = 'users';

	$data_query = $MysqlService->get($table_name,TRUE,array(
		'refresh_token' => "'{$refresh_token}'"
	));

	if($data_query !== NULL){
		//if expired
		if(strtotime($data_query->refresh_tokenexpired) < time()){
			http_response_code(401);
			echo json_encode(array(
				"message" => "Access denied.",
        		"error" => "Expired Refresh Token"
			));
			exit();
		}

		//if refresh token not expired
		$issuedate_claim = time();
		$notbefore_claim = $issuedate_claim; //not before in seconds
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

		http_response_code(201);

		$jwt = JWT::encode($token,$secret_key);
		$refresh_token = uniqid("",TRUE);
		$refresh_tokenexp = $issuedate_claim + 7200; //1 hour for refresh token
		$MysqlService->update_multiple_column([
			'refresh_token' => "$refresh_token",
			'refresh_tokenexpired' => date('Y-m-d H:i:s',$refresh_tokenexp)
		],$table_name,'iduser',$data_query->iduser);

		echo json_encode(array(
			"message" => "Successfull Create Token",
			"id" => $data_query->iduser,
			"access_token" => $jwt,
			"refresh_token" => $refresh_token,
			"email" => $data_query->email,
			"expireAt" => $expire_claim
		));
	} else {
		http_response_code(401);
		echo json_encode(array("message" => "Invalid Token"));
	}
} else {
	http_response_code(400);
	echo json_encode(array("message" => "Missing Token"));
}