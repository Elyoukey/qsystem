<?php
ini_set("display_errors", E_ALL);

include ('../bootstrap.php');

$currentPlayer = (int)$_GET['currentplayer'];
$gameHash = $_GET['gamehash'];


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
    die('"Error pinging a non existant game');
}

// Update all user not pinging since more than 10 sec
$stmt = $db->prepare( 'UPDATE players SET connected = 0 WHERE ping < NOW() - INTERVAL 20 second ' );
$stmt->execute();

// Update game state with disconected users
$stmt = $db->prepare('SELECT number FROM players WHERE gamehash=? AND connected=0 ');
$stmt->bind_param('s', $gameHash);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc() ) {
    $gameState->players[$row['number']]->connected = false;


    //put cards back in deck
    $backcards = $gameState->players[$row['number']]->handList;
    foreach ($backcards as $c){
        $gameState->deckList[]=$c;
    }
    $gameState->players[$row['number']]->handList = array();

}

// Next player
if($gameState->players[(int)$gameState->activePlayer]->connected == false){
    $activePlayer = (int)$gameState->activePlayer;
    $activePlayer++;
    if($activePlayer >= sizeof( $gameState->players ) ){
        $activePlayer = 0;
    }
    $gameState->activePlayer = $activePlayer;
}

// update ping time for current user
$stmt = $db->prepare( 'UPDATE players SET connected = 1 , ping = now() WHERE gamehash=? AND number=? ' );
$stmt->bind_param('si', $gameHash,$currentPlayer);
$stmt->execute();
$gameState->players[$currentPlayer]->connected = true;
include ('./includes/writeGamestate.php');

echo json_encode($gameState);

?>