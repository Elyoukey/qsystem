<?php
$fp = fopen($pingFilename, "w+");

if (flock($fp, LOCK_EX)) { // acquière un verrou exclusif
    ftruncate($fp, 0);     // effacement du contenu
    fwrite($fp,  json_encode($pingState));
    fflush($fp);            // libère le contenu avant d'enlever le verrou
    flock($fp, LOCK_UN);    // Enlève le verrou
} else {
    echo "Impossible de verrouiller le fichier !";
}
fclose($fp);
?>