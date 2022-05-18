<?php
include_once "includes/header.inc.php"
?>

<?php

if (isset($_POST['playername']))
{

    include_once("db.php");

    $user1 = $_SESSION['user'];
    $user2 = $_POST['playername'];

    $sql = "SELECT * FROM users WHERE username='$user2' LIMIT 1;"; // LIMIT 1 za svaki slucaj
    $row = mysqli_fetch_assoc($conn -> query($sql));
    if($row['bid'] != -1 || $_POST['playername'] == $_SESSION['user'])
    {
        $error = "User is already in a game!";
    }
    else 
    {
        // prvo dodajemo novi red u tabeli trenutnih igri ili "borbi"
        $def_formation = "eeeeeeeeeeeeeeeeeeeeeeeee"; // prazno polje
        $sql = "INSERT INTO battles (user1, user2, formation1, formation2, shots1, shots2, turn) VALUES ('$user1', '$user2', '$def_formation', '$def_formation', '$def_formation', '$def_formation', 0)";
        $result = $conn -> query($sql);

        // Onda pronalazimo id te igre i postavljamo bid dva korisnika na nj
        $sql = "SELECT * FROM battles WHERE user1='$user1' LIMIT 1;"; // LIMIT 1 za svaki slucaj
        $row = mysqli_fetch_assoc($conn -> query($sql));

        $bid = $row['id'];
        $_SESSION['bid'] = $bid;

        $sql = "UPDATE users SET bid='$bid' WHERE username='$user1';";
        $result = $conn -> query($sql);
        $sql = "UPDATE users SET bid='$bid' WHERE username='$user2';";
        $result = $conn -> query($sql);

        header("Location: gamesetup.php");
        exit;
    }

}

?>

<form method="post" action="findgame.php">
    <?php if (isset($error)): ?>
        <span><?php echo $error ?></span>
    <?php endif ?>
    <input type="text" name="playername"><br>

    <input type="submit" value="Request Game">
</form>

<?php
include_once "includes/footer.inc.php"
?>