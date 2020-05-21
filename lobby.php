<?php
session_start();
include ('./bootstrap.php');

ini_set("display_errors", E_ALL);

$playerName = filter_var( $_POST['playername'], FILTER_SANITIZE_STRING);
$deckName = filter_var( $_POST['deckname'], FILTER_SANITIZE_STRING);
$activeMissions = array('plantation',
    'runaway_journeys',
    'trial_in_greatbritain',
    'quarantine_and_auctions',
    'middle_passage');
if( !in_array( $deckName , $activeMissions)){
    die('Choix de mission non valide');
}
$gameName = filter_var( $_POST['gamename'], FILTER_SANITIZE_STRING);
$gameHash = md5($_POST['gamename']);
$_SESSION['playername'] = $playerName;
$_SESSION['gamehash'] = $gameHash;

// create new player
$newPlayer = new stdClass();
$newPlayer->name = $playerName;
$newPlayer->connected = true;
$newPlayer->handList = array();

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
    $playerNumber = sizeof( $gameState->players );
    $gameState->players[] = $newPlayer;
    $_SESSION['currentplayer'] = $playerNumber;

}else{ //game does not exists yet

    // create new game
    $gameState = new stdClass();
    $gameState->gamename = $gameName;
    $gameState->deckName = $deckName;
    $gameState->deckList = array();
    for ($i = 2; $i <= 32; $i++) {
        $gameState->deckList[] = $i;
    }
    $gameState->discardList = array();
    $gameState->displayList = array(1);
    $gameState->cardTotal = 32;
    $gameState->players = array( $newPlayer );
    $gameState->activePlayer = "0";

    $playerNumber = 0;
    $_SESSION['currentplayer'] = $playerNumber;
}

$currentPlayer = sizeof($gameState->players)-1;

//players draw 3 cards
for( $i= 0; $i<3;$i++){
    // remove card from decklist
    $randCardKey = rand(0, sizeof($gameState->deckList)-1);
    $newCard = $gameState->deckList[$randCardKey];
    array_splice($gameState->deckList, $randCardKey, 1);

    // replace card on hand
    $gameState->players[$currentPlayer]->handList[$i] = $newCard;
}

//save player
$query = '
          INSERT INTO players ( 
            gamehash,
            number,
            name,
            ping,
            connected       
            )
            VALUES
            (
            ?,?,?,now(),1
            )
           ';
$stmt = $db->prepare( $query );
$stmt->bind_param('sis',
    $gameHash,
    $playerNumber,
    $playerName
);
$stmt->execute();

//save game state
$jsonGamestate = json_encode($gameState);
$query = '
          INSERT INTO games ( 
            gamehash,
            gamestate
            )
            VALUES
            (
            ?,?
            )
            ON DUPLICATE KEY UPDATE
            gamestate = ? 
           ';
$stmt = $db->prepare( $query );
$stmt->bind_param('sss',
    $gameHash,
    $jsonGamestate,
    $jsonGamestate
);
$stmt->execute();

// redirect to board
header('Location: board.php?currentplayer='.$currentPlayer.'&gamehash='.$gameHash);

?>
