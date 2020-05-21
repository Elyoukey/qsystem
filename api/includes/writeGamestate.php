<?php


//save game state
$jsonGamestate = json_encode($gameState);
$query = 'UPDATE games SET gamestate = ? WHERE gamehash = ? ';
$stmt = $db->prepare( $query );
$stmt->bind_param('ss',
    $jsonGamestate,
    $gameHash
);
$stmt->execute();


?>