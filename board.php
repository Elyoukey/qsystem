<?php
session_start();

ini_set("display_errors", E_ALL);

include ('./bootstrap.php');

$gameHash = $_SESSION['gamehash'];
$currentPlayer = $_SESSION['currentplayer'];

// retrieve existing gamestate
global $db;
$stmt = $db->prepare( 'SELECT gamestate FROM games  WHERE gamehash=?' );
$stmt->bind_param('s', $gameHash);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $jsonState = $row['gamestate'];
    // retrieve existing game
    $gameState = json_decode($jsonState);

}else{ //game does not exists yet
    $_SESSION['currentplayer'] = null;
    $_SESSION['gamehash'] = null;
    header('Location: index.php');
}

$deckName = $gameState->deckName;

$sQuestionnaire = file_get_contents('./decks/'.$deckName.'/questions.txt');
$aQuestionnaire = explode( "\n", file_get_contents('./decks/'.$deckName.'/questions.txt'));

?><html lang="en">
<head>
    <meta charset="utf-8">

    <title>Q-system online implementation</title>
    <meta name="description" content="Q-system board game implementation">
    <meta name="author" content="Elyoukey">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <link rel="stylesheet" href="css/styles.css?v=1.0">
    <script src="js/scripts.js?v=1.5"></script>
    <script src="js/draggable2.js?v=1.5"></script>
    <script>
        var gamehash="<?php echo $gameHash;?>";
        var currentPlayer = "<?php echo $currentPlayer;?>";
    </script>
</head>

<body>
<div class="actions">
    <a class="" href="#" onclick="$('#disclaimer')[0].style.display='block'">show rules again</a>
    |
    <a class="" onclick="document.location.href='logout.php'" href="logout.php" >log out</a>
</div>

<div class="container">
    <div class="row">
        <div class="col-6 current-player">
            Current Player : <?php echo $gameState->players[$currentPlayer]->name; ?>
        </div>
        <div class="col-6 gamename">
            Gamename: <?php echo $gameState->gamename;?>
        </div>
    </div>
</div>

<div class="player-list">
    Players <br/>
    <ul id="playerList"></ul>
</div>

<div class="active-player" id="activePlayer"></div>
<div id="errors"></div>
<hr/>
<div id="clicker">


    <div class="container-fluid">
    <div class="row">
        <div class="col-2  text-center">
            <div class="deck-pile" id="deckPile">
                There is currently <div id="deckPileTotal"></div> cards in the deck.<br/>
                <div id="deckPileIcons"></div>

            </div>
        </div>
        <div class="col-7 text-center table-area">
            <div id="setup" class="setup"><?php
                include('./decks/'.$deckName.'/setup.htm');
                ?>
            </div>
            <div class="alert alert-warning">
                The cards below are on the table, visible by all players.
                <i>You can move them by clicking twice: the first click lifts the card up, the second click puts the card down where you choose.</i>
            </div>
            <div id="tablePile"></div>

        </div>
        <div class="col-3 text-center">
            <div class="discard-pile" id="discarPile">
                There is currently <div id="discardPileTotal"></div> cards in the discard pile.<br/>
                <div id="discardPileIcons">dd</div>
            </div>
            <div class="alert alert-warning text-center">If at the end of the game there is less than 6 cards the game is lost.</div>
            <div class="text-left">
                <h3>Vocabulary</h3>
                <?php
                $vocabularyFile = './decks/'.$deckName.'/vocabulary.htm';
                if(file_exists($vocabularyFile)) { include($vocabularyFile); };
                ?>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12 col-sm-12 col-md-2"></div>
        <div class="col-12 col-sm-12 col-md-7 hand-pile">
            <div id="sampleCard" ><!--used only for animation--></div>
            <div class="text-center" id="handPile">
                <div class="alert alert-warning">
                    This is your hand,<br/>
                    Those cards are visible only to you.<br/>
                    You are <b>not allowed</b> to talk about what is on the following cards.
                </div>
                <div id="cardwrapper0" class="card-wrapper">
                    <div id="card0"></div>
                    <input type="button" onclick="display('0');this.disabled='disabled'" value="Reveal">
                    <input type="button" onclick="discard('0');this.disabled='disabled'" value="Discard">
                </div>
                <div id="cardwrapper1" class="card-wrapper">
                    <div id="card1"></div>
                    <input type="button" onclick="display('1');this.disabled='disabled'" value="Reveal">
                    <input type="button" onclick="discard('1');this.disabled='disabled'" value="Discard">
                </div>
                <div id="cardwrapper2" class="card-wrapper">
                    <div id="card2"></div>
                    <input type="button" onclick="display('2');this.disabled='disabled'" value="Reveal">
                    <input type="button" onclick="discard('2');this.disabled='disabled'" value="Discard">
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-12 col-md-3"></div>
    </div>
        <div id="failedGame" class="hidden">
            <div class="row">
                <div class="col-12 col-sm-12 col-md-2"></div>
                <div class="col-12 col-sm-12 col-md-7">
                    <div class="alert alert-warning">
                        <p>
                            All the cards have been revealed or discarded.
                        <div class="alert alert-failure">
                            Unfortunatly you did not discard enough cards to be allowed to answer the question. :(<br/>
                            But you can look bellow at the full story.
                        </div>
                        </p>
                    </div>
                </div>
                <div class="col-12 col-sm-12 col-md-3"></div>
            </div>
        </div>
        <div id="questions" class="hidden">
            <div class="container">
                <div class="row">
                    <div class="col-5">
                        <?php for($i=0;$i<30;$i+=6):?>
                            <div class="question">
                                <b><?php echo $aQuestionnaire[$i];?></b><br/>
                                <?php for($j=1;$j<5;$j++):?>
                                    <div class="nowrap">
                                        <input style="display: inline" type="radio" name="q_<?php echo $i;?>" id="q_<?php echo $i;?>_a_<?php echo $j;?>"/>
                                        <label for="q_<?php echo $i;?>_a_<?php echo $j;?>">
                                            <?php echo $aQuestionnaire[$j+$i];?>
                                        </label>
                                    </div>

                                <?php endfor;?>
                            </div>
                        <?php endfor;?>
                        <div class="text-center">
                            <button onclick="if(confirm('Are you sure you want to read the full story now ?'))$('#answers')[0].style.display='block'" class="btn btn-warning">Reveal the full story</button>
                        </div>
    <br/>
                    </div>

                    <div class="col-2">
                     </div>
                    <div class="col-5">
                        <div id="answers" style="display:none;">
                            <?php include('./decks/'.$deckName.'/answers.htm');?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="endGame" class="hidden">
            <div class="row">
                <div class="col-12 col-sm-12 col-md-2"></div>
                <div class="col-12 col-sm-12 col-md-7">
                    <div class="alert alert-warning">
                        <p>
                            All the cards have been revealed or discarded. It is time to prepare a theory on what happened.<br/>
                            When you are ready click on show questions
                        </p>
                        <button class="btn btn-success"  onclick="$('#questions')[0].className='shown';">Show questions</button>
                    </div>
                </div>
                <div class="col-12 col-sm-12 col-md-3"></div>
            </div>
        </div>

    </div>

    <div id="disclaimer" class="disclaimer text-center" >
        <div class="disclaimer-content">
            <h2>Rules</h2>
            <p>Each player takes a turn. During his turn a player can make one of the following action:</p>
            <p>
                <b>a/ reveal information:</b> <br/>
                Choose a card from your <b>hand</b> and place it on the table, so all players can read and see the whole information.<br/>
                We recommend you read out loud all information you place on the table for every players to know about it.<br/>
            </p>
            <p>
                <b>b/ discarding information:</b><br/>
                Choose a card from your <b>hand</b> and place it face down in the discard pile.<br/>
                You cannot share information on that card until all cards have been played.<br/>
                <div class="alert alert-warning inline">Be careful at the end of the game, if there is less than <b>6 discarded cards</b> the game is lost.</div>
            </p>
            <p>
                To resolve the case it is essential to share information.
            </p>
            <p>
                Check regularly the clues on the table, they are public and all playeres can read them.
            </p>
            <p>
                You can share and talk about your theories at any moment. But you are <b>NOT allowed</b> to share information that are on the cards you are keeping in your hand.
            </p>
            <p>
                At the end of the game, when all clue cards have been revealed or displayed, <br/>
                you must check carefully at the available information and prepare a theory of what happened, working all together.
            </p>
            <p>
                A questionaire with 5 questions will be displayed <br/>
                During this phase of the game you can speak freely about your discarded cards, or the information you remember of them.
            </p>

            <p>
                Once all your teammates are connected and have read the basic rules, click on the "start" button.
            </p>

            <button type="button" class="btn btn-success" onclick="document.getElementById('disclaimer').style.display='none'">START</button>
        </div>
    </div>
    <div style="display:none">
        <input type="button" onclick="refreshState()" value="refresh"/>
        <input type="button" onclick="clearInterval(autoRefresh)" value="stop autorefresh"/>
    </div>

    <div class="all" id="allCards" style="display:none">
    <?php for($i=1;$i<=32;$i++):?>
        <div id="allCard_<?php echo $i;?>">
            <?php
            echo base64_encode( file_get_contents('./decks/'.$deckName.'/card_'.$i.'.htm' ) );
            ?>
        </div>
    <?php endfor;?>

    </div>
</div>
<div id="zoom" class="disclaimer text-center hidden" onclick="closeZoom()" >
</div>
</body>
</html>