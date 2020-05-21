<?php
    ini_set("display_errors", E_ALL);

    $_SESSION['currentplayer'] = null;
    $_SESSION['gamehash'] = null;
    // redirect to board
    header('Location: index.php');

?>
