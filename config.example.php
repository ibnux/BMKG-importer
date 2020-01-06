<?php

include "medoo.php";

//Konfigurasi database, struktur ada di bmkg.sql
$mysqluser = "bmkg";
$mysqlpass = "bmkg2019";
$mysqldb = "bmkg_cuaca";

//untuk koneksi ke database
$db = new medoo([
	// required
	'database_type' => 'mysql',
	'database_name' => $mysqldb,
	'server' => 'localhost',
	'username' => $mysqluser,
	'password' => $mysqlpass,
	'charset' => 'utf8',
 
	// [optional]
	'port' => 3306,
 
	// driver_option for connection, read more from http://www.php.net/manual/en/pdo.setattribute.php
	'option' => [
		PDO::ATTR_CASE => PDO::CASE_NATURAL
	]
]);