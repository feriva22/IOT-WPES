<?php

include 'Dbconfig.php';

class Mysql extends Dbconfig{

private $connection;


public $databaseName;
public $hostName;
public $userName;
public $passCode;
public $port;

function Mysql()    {
    $this->connection = NULL;

    $dbParam = new Dbconfig();
    $this->databaseName = $dbParam->dbName;
    $this->hostName = $dbParam->serverName;
    $this->userName = $dbParam->userName;
    $this->passCode = $dbParam->passCode;
    $this->port = $dbParam->port;

}

function dbConnect(){
	$this->connection = null;

    $this->connection = mysqli_connect($this->hostName,$this->userName,$this->passCode,
                                            $this->databaseName,$this->port);
    return $this->connection;
}

function dbDisconnect() {
    $this->connection = NULL;
    $this->sqlQuery = NULL;
    $this->dataSet = NULL;
    $this->databaseName = NULL;
    $this->hostName = NULL;
    $this->userName = NULL;
    $this->passCode = NULL;
}

function customSelectQuery($query,$single = FALSE){
    $result = $this->connection->query($query);
    $data = array();
    if($result->num_rows > 0){
        while ($obj = mysqli_fetch_object($result)) {
            $data[] = $obj;
        }
        if($single){
            return count($data) == 1 ? $data[0] : $data;
        }
        return $data;
    }
    return array(); 
}

function get($tableName,$single = FALSE,$where = NULL)   {
    if($where !== NULL){
        $output = implode(' AND ', array_map(
            function ($v, $k) { return sprintf("%s = %s", $k, $v); },
            $where,
            array_keys($where)
        ));
    }

	$sql = "SELECT * FROM  ".$this->databaseName.".".$tableName." ". ($where !== NULL ? "WHERE $output" : "");
    $data = array();
    $result = $this->connection->query($sql);
    if($result->num_rows > 0){
        while ($obj = mysqli_fetch_object($result)) {
            $data[] = $obj;
        }
        if($single){
            return count($data) == 1 ? $data[0] : $data;
        }
        return $data;
    }
    return NULL; 
}

function insert($tableName,$insData) {
    $columns = implode(", ",array_keys($insData));
    $escaped_values = array_values($insData);
    $values  = implode(", ", $escaped_values);
	$sql = "INSERT INTO $tableName ($columns) VALUES ($values)";
    $this->connection->query($sql);
    return $this->connection->insert_id;
}

function update_multiple_column($updData, $tableName, $pk_id, $pk_value){
    $output = implode(', ', array_map(
        function ($v, $k) { return sprintf("%s='%s'", $k, $v); },
        $updData,
        array_keys($updData)
    ));


	$sql = "UPDATE $tableName SET $output WHERE $pk_id = $pk_value";
    return $this->connection->query($sql);
}

function update_single_column($column,$value,$tableName,$pk_id,$pk_value){
    $this->update_multiple_column(array($column => $value), $tableName,$pk_id,$pk_value);
}

function delete($tableName,$condition){
    $sql = "DELETE FROM $tableName WHERE $condition";
    $this->connection->query($sql);
}
}
?>