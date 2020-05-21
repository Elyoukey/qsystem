function $( query ){
    return document.querySelectorAll( query );
}

document.addEventListener("DOMContentLoaded", function() {

    refreshState(true);
    autoRefresh = setInterval(refreshState, 2000);
    init_lift();
    //init_touch();
});

var autoRefresh;
var gameString;
var newCardPosX = '0';
var newCardPosY = '100';

function refreshState(force){
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function(){
        if(xhr.readyState !== 4) {
            return
        }
        switch (xhr.status) {
            case 200:
                if(gameString==xhr.responseText && !force)return;
                gameString=xhr.responseText;
                var gamestate = JSON.parse(xhr.responseText);
                break;
            case 401:
                $('#errors')[0].innerHTML = '<div class="alert alert-warning">Veuillez vous connecter ou créer un compte avant d\'accéder au test.</div>';
                break;
            default:
                $('#errors')[0].innerHTML = '<div class="alert alert-warning">Erreur</div>';
                break;
        }

        /*display own hand*/
        var cardList = gamestate.players[currentPlayer].handList;
        for(var i=0;i<cardList.length;i++){
            if( cardList[i] != ''){
                $('#card'+i)[0].innerHTML = '';
                var content = $('#allCard_'+cardList[i])[0].innerHTML;
                content = atob(content);
                var card = document.createElement('div');
                card.className = 'card';
                card.id = 'handCard_'+i;
                card.innerHTML = '<span class="title">#'+ cardList[i]+'</span>' +
                    '<button class="googles" onclick="zoom(\'handCard_'+ i +'\')">&#x1F50E;</button>';
                card.innerHTML += '<hr/>';
                card.innerHTML += '<div class="content">'+content+'</div>';

                $('#card'+i)[0].append(card);
            }else{
                $('#cardwrapper'+i+'')[0].innerHTML = 'No more cards in the deck. <br/>But all players must reveal or discard all cards in their hands.';

            }
        }

        /*display deck*/
        $('#deckPileTotal')[0].innerHTML = gamestate.deckList.length;
        cards = '';
        for(j=0;j<gamestate.deckList.length;j++){
            cards += '<div class="card-counter"></div>';
        }
        $('#deckPileIcons')[0].innerHTML = cards;

        /*discarPileTotal */
        $('#discardPileTotal')[0].innerHTML = gamestate.discardList.length;
        cards = '';
        for(j=0;j<gamestate.discardList.length;j++){
                cards += '<div class="card-counter"></div>';
        }
        $('#discardPileIcons')[0].innerHTML = cards;

        /*display table*/
        var res = '';
        // add missing cards
        for(var i=0; i < gamestate.displayList.length;i++){
            if($('#card_'+gamestate.displayList[i]).length > 0)continue;
            if( gamestate.displayList[i] == null )continue;

            var content = $('#allCard_'+gamestate.displayList[i])[0].innerHTML;
            content = atob(content);

            var card = document.createElement('div');
            card.style.top = newCardPosY+'px';
            card.style.left = newCardPosX+'px';
            card.style.zIndex = topZIndex;
            card.id = 'card_'+ gamestate.displayList[i];
            card.innerHTML = '<span class="title">#'+ gamestate.displayList[i]+'</span> ' +
                '<button class="googles" onclick="zoom(\'card_'+ gamestate.displayList[i] +'\')">&#x1F50E;</button>';
            card.innerHTML += '<hr/>';
            card.innerHTML += '<div class="content">'+content+'</div>';
            card.className = 'card pulse';
            newCardPosY = parseInt(newCardPosY) + 20;
            topZIndex++;
            $('#tablePile')[0].append(card);
            dragElement(card);
        }


        /*player list*/
        var res = '';
        for( var i=0;i<gamestate.players.length;i++){
            var cConnected = ( gamestate.players[i].connected )? 'connected':'disconnected';
            var cActive = ( gamestate.activePlayer == i )?'active':'';
            var cards = '';
            for(j=0;j<gamestate.players[i].handList.length;j++){
                if( gamestate.players[i].handList[j] != ""){
                    cards += '<div class="card-counter"></div>';
                }
            }
            res += '<li class="'+ cConnected + ' ' + cActive + '">';
            res += gamestate.players[i].name+ cards
            res += '</li>';
        }
        $('#playerList')[0].innerHTML = res;

        /* active/desactive buttons for currentPlayer */
        var handbuttons = $('#handPile input');
        if( currentPlayer == gamestate.activePlayer){
            for( var i = 0; i< handbuttons.length; i++){
                handbuttons[i].disabled = false;
            }
        }else{
            for( var i = 0; i< handbuttons.length; i++){
                handbuttons[i].disabled = true;
            }
        }

        /* display active player */
        $('#activePlayer')[0].innerHTML = 'It is <span class="turn-name">' + gamestate.players[gamestate.activePlayer].name + '</span>\'s turn';

        /* show questionnaire when all cards have been played*/
        if( gamestate.displayList.length+gamestate.discardList.length >= gamestate.cardTotal ){
            $('#handPile')[0].className='hidden';
            if(gamestate.discardList.length<6){
                $('#failedGame')[0].className='shown';
                $('#questions')[0].className='shown';
                $('#answers')[0].className='shown';
                $('#answers')[0].style.display='block'
            }else{
                $('#endGame')[0].className='shown';
                $('#questions')[0].className='hidden';
            }

        }

    }
    if(typeof gamehash === 'undefined'){$('#errors')[0].innerHTML = '<div class="alert alert-warning">Erreur hash non initialisé.</div>';}
    xhr.open("GET", "./api/ping.php?currentplayer="+currentPlayer+'&gamehash='+gamehash, true);
    xhr.send("");
}

function display( card ) {
    $('#sampleCard')[0].className = '';
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function(){
        if(xhr.readyState !== 4) { return }
        $('#sampleCard')[0].className ='animate-display';
        refreshState(true);
    }
    if(typeof gamehash === 'undefined'){$('#errors')[0].innerHTML = '<div class="alert alert-warning">Erreur hash non initialisé.</div>';}
    xhr.open("GET", "./api/display.php?currentplayer="+currentPlayer+"&gamehash="+gamehash+"&card="+card, true);
    xhr.send("");
}

function discard( card ) {
    $('#sampleCard')[0].className = '';
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function(){
        if(xhr.readyState !== 4) { return }
        $('#sampleCard')[0].className ='animate-discard';
        refreshState(true);
    }
    if(typeof gamehash === 'undefined'){$('#errors')[0].innerHTML = '<div class="alert alert-warning">Erreur hash non initialisé.</div>';}
    xhr.open("GET", "./api/discard.php?currentplayer="+currentPlayer+"&gamehash="+gamehash+"&card="+card, true);
    xhr.send("");
}

function zoom(cardid){
    if($('#'+cardid+ ' img').length <= 0 )return;

    $('#'+cardid)[0].classList.add('zoomed');

    var img = $('#'+cardid+ ' img')[0].cloneNode(true);
    img.style.maxHeight = '90%';
    img.style.maxWidth = '90%';
    var zoom = $('#zoom')[0];
    zoom.innerHTML = '';
    zoom.append(img);
    zoom.style.display='block';
    zoom.style.zIndex = topZIndex+1;
}

function closeZoom(){
    var zoomed = document.getElementsByClassName('zoomed');
    for( var i=0;i<zoomed.length;i++){
        var elmnt = zoomed[i];
        elmnt.classList.remove('zoomed');
    }
    $('#zoom')[0].style.display = 'none';
}