<?php
include_once "includes/header.inc.php"
?>

<?php

    // Ako je korisnik već prijavljen nema potrebe da dostigne ovu stranicu
    if (isset($_SESSION['user'])){
        header("Location: index.php");
    }

    if (isset($_POST['form_action'])){

        require_once ("db.php");
        $errors = []; // da ne bude svaki put "pogrešna kombinacija" itd.

        if ($_POST['form_action'] == "Login"){
            // Login logic
            $usr = $_POST['usr'];
            $psw = md5($_POST['psw']);
            $sql = "SELECT * FROM users WHERE username='$usr' AND password='$psw' LIMIT 1;";
            $result = $conn -> query($sql);
            $row = mysqli_fetch_assoc($result);
            if(isset($row)){ 
                $_SESSION['user'] = $_POST['usr'];
                $_SESSION['cast'] = $row['cast']; // honor points
                if ($row['bid'] != -1) $_SESSION['bid'] = $row['bid'];
                header("Location: index.php");
            }else{
                // će se poslije prikazati na stranici putem foreach() petlje
                $errors[] = "Kombinacija korisnika/šifre je netačna ili korisnik ne postoji.";
            }
        }else{
            // Register logic
            $sql = "SELECT * FROM users WHERE username='{$_POST['usr']}' LIMIT 1;";
            $result = $conn -> query($sql);
            $row = mysqli_fetch_assoc($result);
            if(!isset($row)){ 
                
                // Ako korisnik ne postoji već, dodaj novog u bazu
                $usr = $_POST['usr'];
                $psw = md5($_POST['psw']);
                $sql = "INSERT INTO users(username, password, bid, cast) VALUES ('$usr', '$psw', -1, 0);";
                $result = $conn -> query ($sql);

                // Log-in i kreni na index
                $_SESSION['user'] = $_POST['usr'];
                $_SESSION['cast'] = 0;
                header("Location: index.php");
            }else{
                // će se poslije prikazati na stranici putem foreach() petlje
                $errors[] = "Taj korisnik već postoji";
            }
        }
    }

    if(isset($_GET['action']) && $_GET['action'] == 'register')
        $formaction = "Register";
    else $formaction = "Login";

?>

<form id="login-form" method="post" action="accountform.php?action=<?php echo strtolower($formaction) ?>">
    <?php 
        if(isset($errors)) foreach($errors as $e):
    ?>
        <div class="error"><p><img src="x.png"><?php echo $e ?></p></div>
    <?php endforeach ?>
    <img src="user.png"><input name="usr" type="text" placeholder="Username" required>
    <br>
    <img src="lock.png"><input name="psw" type="password" placeholder="Password" required>
    <br>
    <input type="hidden" name="form_action" value="<?php echo $formaction ?>">
    <input class="button" type="submit" value="<?php echo $formaction; ?>">

    <br><br><br>
    <?php
        if($formaction == 'Register'){
    ?>
        <span>Imate račun? <a href="accountform.php?action=login">Prijavite se.</a></span>
    <?php }else{ ?>
        <span>Nemate račun? <a href="accountform.php?action=register">Registrirajte se.</a></span>
    <?php } ?>
</form>



<?php
include_once "includes/footer.inc.php"
?>