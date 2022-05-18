<?php
    session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet"> 
    <link rel="stylesheet" href="style.css" >
    <title>Battleships</title>
</head>
<body>
    <?php if(isset($_SESSION['user'])){?>
        <div id="user-actions">
            <img id="usr-img" src="user.png"><span id="usr-name"><?php echo $_SESSION['user'] ?></span>
            <img id="hnr-img" src="honor.png"><span id="usr-cast"><?php echo $_SESSION['cast'] ?></span>
        </div>
    <?php } ?>
    <header>
        <img id="header-logo" src="logo.png">
        <h1>Battleships</h1>
        <ul>
            <li>
                <a href="index.php" class="button">Početna</a>
            </li>
            <li>
                <a href="play.php" class="button">Igraj</a>
            </li>
            <li>
                <?php
                    // Logika za account
                    if(!isset($_SESSION['user'])){
                ?>
                    <a href="accountform.php?action=login" class="button">Prijavi se</a>
                <?php }else{ ?>
                    <a href="logout.php" class="button">Odjavi se</a>
                <?php } ?>
            </li>
        </ul>
    </header>