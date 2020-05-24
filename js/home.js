function $( query ){
    return document.querySelectorAll( query );
}

document.addEventListener("DOMContentLoaded", function() {
    $('#deckname')[0].addEventListener('change',showCredits);
});

function showCredits() {
    var $credits = $('.credits');
    for(var i=0;i<$credits.length;i++){
        $credits[i].style.display='none';
    }
    $('#credits_'+this.value)[0].style.display='block';

}