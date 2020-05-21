<?php
session_start();
ini_set("display_errors", E_ALL);

$files = array_diff(scandir('./games/'), array('.', '..'));
$mission = ( empty($_GET['mission']) )?'':$_GET['mission'];

?><!doctype html>

<html lang="en">
<head>
    <meta charset="utf-8">

    <title>Q-system online implementation | Welcome</title>
    <meta name="description" content="Q-system board game implementation">
    <meta name="author" content="Elyoukey">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <link rel="stylesheet" href="css/styles.css?v=1.0">
    <script src="js/scripts.js?v=1.0"></script>
</head>

<body style="text-align: center; padding-top: 100px;">
<div class="introtext">
    <p>Welcome to the Q-system</p>
    <p>This game is a collaborative investigation.</p>
    <p>To succeed, all players must work together.</p>
    <p>Using a few simple rules, <br/>you will have to analyse clues, share your theories and use your mind to resolve the case.</p>
</div>
<form action="lobby.php" method="post">
    <div>
        <label>Enter your name:</label><br/>
        <input type="text" name="playername" required="required"/>
    </div>
    <div>
        <label>Game name:</label>
    </div>
    <div class="tips">
        A unique gamename. Share it with your other team mates.

    </div>
    <div>
        <input type="text" name="gamename" value="<?php echo (empty($_GET['gamename']))?'':$_GET['gamename'];?>" required="required"/>
    </div>
<div>
    <label>Choose case:</label>
</div>
    <div>
        <select name="deckname">
            <option value="plantation" <?php if($mission=='plantation'){echo "selected='selected'";}?> >Plantation</option>
            <option value="runaway_journeys" <?php if($mission=='runaway_journeys'){echo "selected='selected'";}?> >Runaway journeys</option>
            <option value="trial_in_greatbritain" <?php if($mission=='trial_in_greatbritain'){echo "selected='selected'";}?> >Trials in GreatBritain</option>
            <option value="quarantine_and_auctions" <?php if($mission=='quarantine_and_auctions'){echo "selected='selected'";}?> >Quarantine and Auctions</option>
            <option value="middle_passage" <?php if($mission=='middle_passage'){echo "selected='selected'";}?>  >Middle passage</option>
        </select>
    </div>
<div>
    <div>
        <input type="submit" class="btn btn-success" value="Enter"/>

    </div>

</div>
</form>
</body>
</html>