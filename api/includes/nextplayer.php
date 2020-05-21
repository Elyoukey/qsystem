<?php
/* next player */
$activePlayer = (int)$gameState->activePlayer;
$activePlayer++;
if($activePlayer >= sizeof( $gameState->players ) ){
    $activePlayer = 0;
}
$gameState->activePlayer = $activePlayer;
