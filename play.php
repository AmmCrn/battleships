<?php

include_once("includes/header.inc.php");

// PLAY LOGIC GOES HERE, LOTS OF CODE, LOTS OF SUFFERING

if(isset($_SESSION['user'])){
    include_once("db.php");

    // prvo provjeri da li je promjenjeno stanje bid
    $sql = "SELECT * FROM users WHERE username='{$_SESSION['user']}' LIMIT 1;";
    $row = mysqli_fetch_assoc($conn -> query($sql));
    if($row['bid'] != -1) {
        $_SESSION['bid'] = $row['bid'];
        $sql = "SELECT * FROM battles WHERE id={$row['bid']};";
        $row = mysqli_fetch_assoc($conn -> query($sql));
        if ($row['ready1'] == 0 || $row['ready2'] == 0){
            header("Location: gamesetup.php");
            exit;
        } // else play game?
    } else {
        $_SESSION['bid'] = NULL;
    }

    // ako nismo u jednoj igri odi na stranicu da se nadje
    if(!isset($_SESSION['bid'])) {
        header("Location: findgame.php");
        exit;
    }
} else {
    header("Location: accountform.php");
    exit;
}

// 2d koordinate na 1d
function tto($x, $y){
    return $x*5+$y; // moramo biti veoma oprezni sta je x a sta y
}

// $row je idalje ostao od provjeravanja stanja bid
if($_SESSION['user'] == $row['user1']){
    $player = "1";
}elseif($_SESSION['user'] == $row['user2']){
    $player = "2";
}
$opponent = ($player == "1") ? "2" : "1";

$_SESSION['formation'] = $row['formation' . $player];
$_SESSION['shots'] = $row['shots' . $player];

if ($player == "1") $_SESSION['pturn'] = ($row['turn'] == 0) ? 1 : 0;
elseif ($player == "2") $_SESSION['pturn'] = ($row['turn'] == 1) ? 1 : 0;

if(isset($_POST['cur_action']) && $_POST['cur_action'] == "shoot"){
    // game code! !!! YAY ! ! ! !

    // x and y are inverted, i don't know why
    $sx = (int)$_POST['y']; // seleected x
    $sy = (int)$_POST['x']; // seleected y

    $pos = substr($row['formation' . $opponent], tto( $sx, $sy ), 1);
    $success = ($pos == "b" || $pos == "f") ? "g" : "f";
    $shots = substr_replace($row['shots' . $player], $success, tto( $sx, $sy ), 1);
    $sql = "UPDATE battles set shots$player='$shots' WHERE id={$_SESSION['bid']};";
    $result = $conn -> query($sql);
    if($success == "g"){
        $formation = substr_replace($row['formation' . $opponent], "f", tto( $sx, $sy ), 1);
        $sql = "UPDATE battles SET formation$opponent='$formation' WHERE id={$_SESSION['bid']};";
        $result = $conn -> query($sql);
    }
    $turn = ($player == "1") ? 1 : 0;
    $sql = "UPDATE battles SET turn=$turn WHERE id={$_SESSION['bid']};";
    $result = $conn -> query($sql);

    // whatever the case it is no longer our turn
    $_SESSION['pturn'] = 0;
}

// Update informacija kao sto su formacije, pucnjevi i ciji je potez
$sql = "SELECT * FROM battles WHERE id={$_SESSION['bid']};";
$row = mysqli_fetch_assoc($conn -> query($sql));

$_SESSION['formation'] = $row['formation' . $player];
$_SESSION['shots'] = $row['shots' . $player];

if ($player == "1") $_SESSION['pturn'] = ($row['turn'] == 0) ? 1 : 0;
elseif ($player == "2") $_SESSION['pturn'] = ($row['turn'] == 1) ? 1 : 0;

// WIN / LOSE CONDITIONS
// Ako nemamo vise polje broda na nasem polju onda smo izgubili
// Ako protivnik nema vise polje broda onda smo pobijedili
if(!strpos($row['formation' . $player], "b") && substr($row['formation' . $player], 0, 1) != "b"){
    $outcome = "lost";
}elseif(!strpos($row['formation' . $opponent], "b") && substr($row['formation' . $opponent], 0, 1) != "b"){
    $outcome = "won";
}

// Ako je igra zavrsena tj jedan od igraca nema broda na svom polju
if(isset($outcome) && !empty($outcome)){
    // Pronadji trenutnog korisnika u bazi da obradimo cast
    $sql = "SELECT * FROM users WHERE username='{$_SESSION['user']}' LIMIT 1;";
    $u = mysqli_fetch_assoc($conn -> query($sql));

    // Dodaj/oduzmi cast i smakni id trenutne igre sa igraca
    $cast = ($outcome == "won") ? (int)$u['cast'] + 5 : (int)$u['cast'] - 5;
    $sql = "UPDATE users SET cast=$cast, bid=-1 WHERE username='{$_SESSION['user']}';";
    $result = $conn -> query($sql);

    // Postavi u bazi da je ovaj igrac zavrsen
    $sql = "UPDATE battles SET user$player='done' WHERE id={$_SESSION['bid']}";
    $result = $conn -> query($sql);

    // Ako su oba igraca zavrsili igru mozemo obrisati igru iz baze
    $sql = "SELECT * FROM battles WHERE id={$_SESSION['bid']}";
    $row = mysqli_fetch_assoc($conn -> query($sql));
    if ($row['user1'] == "done" && $row['user2'] == "done"){
        $sql = "DELETE FROM battles WHERE id={$_SESSION['bid']}";
        $result = $conn -> query($sql);
    }

    $_SESSION['bid'] = NULL;
    $_SESSION['cast'] += ($outcome == "won") ? 5 : -5;
    $_SESSION['formation'] = NULL;
    $_SESSION['shots'] = NULL;
    $_SESSION['pturn'] = NULL;
    $_SESSION['ships'] = NULL;

    header("Location: $outcome.php");
    exit;
}

?>

<p>
Objašnjenje:<br>
x i y ose su od 0 do 4<br>
y osa se krece odozgo prema dolje<br>
plavo polje znaci da je tvoj brod jos tu i ziv<br>
crveno polje znaci da je dio broda potopljen ili u slucaju pucnjeva, da ste promasili<br>
zeleno polje znaci da ste pogodili neprijateljov brod<br>
morate sami refresh-ovati stranicu da bi dosli do svog poteza :(<br>
</p>

<span>Tvoje polje:</span>

<table>
    <?php
    // Postavljanje tabele polja
    for($i = 0; $i < 5; $i++):
    ?>
    <tr>
        <?php for($j = 0; $j < 5; $j++):?>
            <td class="<?php echo substr($_SESSION['formation'], tto($i,$j), 1) ?>"></td>
        <?php endfor ?>
    </tr>
<?php endfor ?>
</table>

<span>Tvoji pucnjevi:</span>

<table>
    <?php
    // Postavljanje tabele polja
    for($i = 0; $i < 5; $i++):
    ?>
    <tr>
        <?php for($j = 0; $j < 5; $j++):?>
            <td class="<?php echo substr($_SESSION['shots'], tto($i,$j), 1) ?>"></td>
        <?php endfor ?>
    </tr>
<?php endfor ?>
</table>

<?php
if (isset($_SESSION['pturn']) && $_SESSION['pturn'] == 1):
?>
<form method="post" action="play.php">
    <label id="x">X:2</label>
    <input name="x" type="range" min="0" max="4"
     oninput="document.getElementById('x').innerText = 'X:' + this.value;">

    <label id="y">Y:2</label>
    <input name="y" type="range" min="0" max="4"
     oninput="document.getElementById('y').innerText = 'Y:' + this.value;">

    <input type="hidden" name="cur_action" value="shoot">
    <input type="submit" value="Shoot!">
</form>
<?php
else:
?>
<span style="color: red">Neprijetaljov je red!</span>
<?php
endif
?>
<a href="play.php" class="button" style="display: inline-block;">Refresh</a>
<?php
include_once("includes/footer.inc.php")
?>