<?php
ini_set("display_errors", E_ALL);
include ('../bootstrap.php');

$gameHash = $_GET['gamehash'];
$player = $_GET['currentplayer'];
$card = $_GET['card'];

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

/*************display a card*/
$cardNumber = $gameState->players[$player]->handList[$card];

/* put card on table*/
$gameState->discardList[]=$cardNumber;


if( sizeof( $gameState->deckList ) > 0 ){
    /* draw card from decklist */
    $randCardKey = rand(0,sizeof( $gameState->deckList )-1 );
    $newCard = $gameState->deckList[$randCardKey];
    array_splice($gameState->deckList, $randCardKey,1);

    /* replace card on hand */
    $gameState->players[$player]->handList[$card] = $newCard;
}else{
    $gameState->players[$player]->handList[$card] = "";
}

include ('./includes/nextplayer.php');
include ('./includes/writeGamestate.php');