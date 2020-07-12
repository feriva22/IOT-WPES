<?php
include_once '../db/Mysql.php';
require '../helper/helper.php';

cors(); //enable cors

$fullName = '';
$email = '';
$password = '';
$conn = null;

$MysqlService = new Mysql();
$conn = $MysqlService->dbConnect();
$data = json_decode(file_get_contents("php://input"));

$fullName = $data->fullname;
$email = $data->email;
$password = $data->password;
$password_hash = password_hash($password,PASSWORD_BCRYPT);

$table_name = 'users';
$current_time = date('Y-m-d H:i:s');

$id_users = $MysqlService->insert($table_name, [
	'full_name' => "'{$fullName}'",
	'email' => "'{$email}'",
	'password' => "'{$password_hash}'",
	'created_at' => "'{$current_time}'"
]);

if($id_users != 0){
	http_response_code(200);
	echo json_encode(array("message" => "User successfully created"));
} else {
	http_response_code(400);
	echo json_encode(array("message" => "Unable create account"));
}


?>