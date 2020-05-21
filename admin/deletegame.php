<?php
ini_set("display_errors", E_ALL);
include ('../bootstrap.php');

$gameHash = $_GET['gamehash'];

// delete game
$query = 'DELETE FROM  games WHERE gamehash = ? ';
$stmt = $db->prepare( $query );
$stmt->bind_param('s',
    $gameHash
);
$stmt->execute();

// delete players
$query = 'DELETE FROM  players WHERE gamehash = ? ';
$stmt = $db->prepare( $query );
$stmt->bind_param('s',
    $gameHash
);
$stmt->execute();

// redirect to board
header('Location: index.php');
