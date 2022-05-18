<?php

session_start();

session_destroy();
/*
// ne koristim session_destroy() za slucaj da
// treba ostati neka varijabla u session
$_SESSION['user'] = NULL;
$_SESSION['cast'] = NULL;
$_SESSION['bid'] = NULL;
*/


header("Location: index.php");

?>