<?php
ini_set("display_errors", E_ALL);
include ('../bootstrap.php');


$playerId = (int)$_GET['pid'];
$playerNumber = (int)$_GET['pNumber'];
$gameHash = $_GET['gamehash'];

global $db;

// retrieve existing gamestate
$stmt = $db->prepare( 'SELECT gamestate FROM games  WHERE gamehash=?' );
$stmt->bind_param('s', $gameHash);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $jsonState = $row['gamestate'];
    // retrieve existing game
    $gameState = json_decode($jsonState);

}else{ //game does not exists yet
    die('"Error non existant game');
}


$backcards = $gameState->players[$playerNumber]->handList;
foreach ($backcards as $c){
    $gameState->deckList[]=$c;
}
$gameState->players[$playerNumber]->handList = array();

//save game state
$jsonGamestate = json_encode($gameState);
$query = 'UPDATE games SET gamestate = ? WHERE gamehash = ? ';
$stmt = $db->prepare( $query );
$stmt->bind_param('ss',
    $jsonGamestate,
    $gameHash
);
$stmt->execute();

// redirect to board
header('Location: index.php');
