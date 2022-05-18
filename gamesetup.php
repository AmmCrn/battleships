<?php
include_once "includes/header.inc.php"
?>

<?php 

if(!isset($_SESSION['user']) || !isset($_SESSION['bid'])){
    header("Location: index.php");
    exit;
}

include_once("db.php");

$sql = "SELECT * FROM battles WHERE id={$_SESSION['bid']} LIMIT 1;";
$row = mysqli_fetch_assoc($conn -> query($sql));

if($row['ready1'] == 1 && $row['ready2'] == 1){
    $_SESSION['ready'] = 1;
    header("Location: play.php");
    exit;
}

if(!isset($_SESSION['formation'])){
    if($_SESSION['user'] == $row['user1'])
        $_SESSION['formation'] = $row['formation1'];
    else
        $_SESSION['formation'] = $row['formation2'];
}

if(!isset($_SESSION['ships'])){
    $_SESSION['ships'] = 3;
}

// 2d koordinate na 1d
function tto($x, $y){
    return $x*5+$y; // moramo biti veoma oprezni sta je x a sta y
}

//DEV: reset
//    $_SESSION['formation'] = "eeeeeeeeeeeeeeeeeeeeeeeee";
//    $_SESSION['ships'] = 3;

if(isset($_POST['cur_action'])){
    // provjeri je li moguce postaviti brod tu i onda ga postavi
    // ali samo u $_SESSION, u bazu ce se unijeti kad zavrsimo
    if($_POST['cur_action'] == "setting"){
        
        // x and y are inverted, i dont know why
        $sx = (int)$_POST['y']; // seleected x
        $sy = (int)$_POST['x']; // seleected y

        $pos1 = substr($_SESSION['formation'],
         tto( $sx, $sy ), 1);

        // polje udesno
        if($_POST['dir'] == 'h'){
            $ipos2 = tto( $sx, $sy+1);
            $pos2 = substr($_SESSION['formation'], $ipos2, 1);
        // polje dolje
        }else{
            $ipos2 = tto( $sx+1, $sy );
            $pos2 = substr($_SESSION['formation'], $ipos2, 1);
        }
        
        if($pos1 == 'e' && $pos2 == 'e' && !($ipos2 > 24) &&
            (($_POST['dir'] == 'h' && !($sy+1 > 4) )
                ||
            ($_POST['dir'] == 'v' && !($sx+1 > 4)))
        ){
            $_SESSION['formation'] = substr_replace($_SESSION['formation'], "b", tto( $sx, $sy ), 1);
            $_SESSION['formation'] = substr_replace($_SESSION['formation'], "b", $ipos2, 1);
            $_SESSION['ships']--;

            //ako smo zavrsili sa postavljanjem
            if($_SESSION['ships'] == 0){
                $f = $_SESSION['formation'];
                $bid = $_SESSION['bid'];

                $sql = "SELECT * FROM battles WHERE id={$_SESSION['bid']} LIMIT 1;";
                $row = mysqli_fetch_assoc($conn -> query($sql));

                if($_SESSION['user'] == $row['user1']){
                    $sql = "UPDATE battles SET formation1='$f', ready1=1 WHERE id=$bid;";
                    $result = $conn -> query($sql);
                }else{
                    $sql = "UPDATE battles SET formation2='$f', ready2=1 WHERE id=$bid;";
                    $result = $conn -> query($sql);
                }
            }
        }else{
            $error = "Can't place ship there!";
        }
    }
}

?>

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

<span>
<?php
if(isset($error))
echo $error;
?>
</span>

<?php
// korisnicke kontrole postavljanja polja
// dozvoljena su tri broda velicine 1x2
if(isset($_SESSION['ships']) && $_SESSION['ships'] > 0):
?>
<form method="post" action="gamesetup.php">
    <span>Preostali brodovi: <?php echo $_SESSION['ships']; ?></span><br>
    <label id="x">X:2</label>
    <input name="x" type="range" min="0" max="4"
     oninput="document.getElementById('x').innerText = 'X:' + this.value;">

    <label id="y">Y:2</label>
    <input name="y" type="range" min="0" max="4"
     oninput="document.getElementById('y').innerText = 'Y:' + this.value;">

    <select name="dir">
        <option value="v">Vertical (prema dolje)</option>
        <option value="h">Horizontal (prema desno)</option>
    </select>

    <input type="hidden" name="cur_action" value="setting">
    <input type="submit">
</form>
<?php endif ?>


<?php
    // display a message if the opponent is ready
    $sql = "SELECT * FROM battles WHERE id={$_SESSION['bid']} LIMIT 1;";
    $row = mysqli_fetch_assoc($conn -> query($sql));
    if($_SESSION['user'] == $row['user1'])
    {
        //check if user2 is ready
        if($row['ready2'] == 1){
            echo "<span style='color: green'>Opponent is ready!</span>";
        }else{
            echo "<span style='color: red'>Opponent is not ready.</span>";
        }
    }else{
        //check if user1 is ready
        if($row['ready1'] == 1){
            echo "<span style='color: green'>Opponent is ready!</span>";
        }else{
            echo "<span style='color: red'>Opponent is not ready.</span>";
        }
    }

    //if both users are ready, proceed to game
    if($row['ready1'] == 1 && $row['ready2'] == 1){
        // Why is this code here hahahah what's it supposed to do?
    }
?>
<?php
include_once "includes/footer.inc.php"
?>