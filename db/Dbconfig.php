<?php
date_default_timezone_set('Asia/Jakarta');

class Dbconfig {
    protected $serverName;
    protected $userName;
    protected $passCode;
    protected $dbName;
    protected $port;

    function Dbconfig() {
        $this->serverName = 'localhost';
        $this->userName = 'root';
        $this->passCode = '';
        $this->dbName = 'wpes';
        $this->port = 3306;
    }
}
?>