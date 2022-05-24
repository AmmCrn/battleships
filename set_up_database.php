<?php

require("db.php");

$sql = "CREATE TABLE users( id INT(11) PRIMARY KEY NOT NULL AUTO_INCREMENT, username VARCHAR(255), password VARCHAR(255), bid VARCHAR(255), cast INT(11) );";
$result = $conn -> query($sql);

$sql = "CREATE TABLE battles ( id INT(11) PRIMARY KEY NOT NULL AUTO_INCREMENT, user1 VARCHAR(255), user2 VARCHAR(255), formation1 VARCHAR(255), formation2 VARCHAR(255), shots1 VARCHAR(255), shots2 VARCHAR(255), turn int(1), ready1 int(1), ready2 int(1));";
$result = $conn -> query($sql);

?>