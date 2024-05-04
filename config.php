<?php
$hostName = "localhost";
$databaseUsername = "";
$databasePassword = "";
$databaseNmae = "";
try{
$db = new PDO('mysql:host='.$hostName.';dbname='.$databaseNmae.';charset=utf8',$databaseUsername,$databasePassword);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch(PDOException $ex) {
    //user friendly message
    die ("Cant connect with database server, please feed back admin.");
}
?>