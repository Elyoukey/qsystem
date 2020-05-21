<?php
include('../bootstrap.php');

// retrieve existing gamestate
global $db;
?><html lang="en">
<head>
    <meta charset="utf-8">

    <title>Q-system online implementation</title>
    <meta name="description" content="Monitor - Q-system board game implementation">
    <meta name="author" content="Elyoukey">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <link rel="stylesheet" href="../css/styles.css?v=1.0">
    <script>
        var gamehash="<?php echo $gameHash;?>";
        var currentPlayer = "<?php echo $currentPlayer;?>";
    </script>
</head>
<body>


<?php
//get all games
$stmt = $db->prepare('SELECT * FROM games');
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc() ):
    $gamestate = json_decode( $row['gamestate']);
?>
    <hr/>
    <h2><?php echo $row['id'];?> - <?php echo $gamestate->gamename;?> <span style="font-size: smaller">(<?php echo $row['gamehash'];?>)</span> </h2>
    <div>
        <a href="deletegame.php?gamehash=<?php echo $row['gamehash']?>">Delete game</a>
    </div>
    <div>
        DeckName : <?php echo $gamestate->deckName;?>
    </div>

    <table class="table table-hover table-sm text-center table-striped">
        <thead class="thead-dark text-center" >
            <tr>
                <th>#</th>
                <th>Cards</th>
                <?php  for($i=1;$i<=32;$i++ ):?>
                <th>
                    <?php echo $i;?>
                </th>
                <?php endfor;?>
                <th>Last ping</th>
            </tr>
        </thead>
        <tr>
            <td></td>
            <td class="text-nowrap">Deck Pile</td>
            <?php  for($i=1;$i<=32;$i++ ):?>
                <td>
                    <?php if(in_array($i,$gamestate->deckList)){
                        echo $i;
                    }?>
                </td>
            <?php endfor;?>
        </tr>
        <tr>
            <td></td>
            <td class="text-nowrap">Display Pile</td>
            <?php  for($i=1;$i<=32;$i++ ):?>
                <td>
                    <?php if(in_array($i,$gamestate->displayList)){
                        echo $i;
                    }?>
                </td>
            <?php endfor;?>
        </tr>
        <tr>
            <td></td>
            <td class="text-nowrap">Discard Pile</td>
            <?php  for($i=1;$i<=32;$i++ ):?>
                <td>
                    <?php if(in_array($i,$gamestate->discardList)){
                        echo $i;
                    }?>
                </td>
            <?php endfor;?>
        </tr>
        <?php
        // get players
        $stmtP = $db->prepare('SELECT * FROM players WHERE gamehash=? ');
        $stmtP->bind_param('s', $row['gamehash']);
        $stmtP->execute();
        $resultP = $stmtP->get_result();
        while ($rowP = $resultP->fetch_assoc() ):
            ?>
            <tr>
                <td>
                    <?php echo $rowP['number'];?>
                </td>
                <td <?php echo ( ($rowP['connected'])?'style="color:green"':'style="color:darkred"' ) ;?>>
                    <span class="text-nowrap">
                        <?php echo $rowP['name'];?>
                    </span>
                </td>
            <?php  for($i=1;$i<=32;$i++ ):?>
                <td>
                    <?php if(in_array( $i,$gamestate->players[$rowP['number']]->handList )) {
                        echo $i;
                    };?>
                </td>
            <?php endfor;?>
                <td class="text-nowrap">
                    <?php echo $rowP['ping'];?>
                </td>
            </tr>
        <?php endwhile;?>
    </table>
    <hr/>
<?php endwhile; ?>
</body>
</html>
