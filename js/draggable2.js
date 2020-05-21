var topZIndex = 10;
function dragElement(elmnt) {
    elmnt.addEventListener('click',liftElement);
    elmnt.addEventListener('touch',liftElement);
}

function liftElement(){
    if( this.classList.contains('zoomed') ){
        this.classList.remove('zoomed');
        return;
    }
    if( !this.classList.contains('lifted') ){
        this.classList.add('lifted');
        this.style.zIndex = topZIndex;
        topZIndex++;
    }
}

function putElement(e){
    e = e || window.event;
    var lifted = document.getElementsByClassName('lifted');
    if(lifted.length > 0 ){
        e.preventDefault();
        e.stopPropagation();
    }
    for( var i=0;i<lifted.length;i++){

        var elmnt = lifted[i];
        var rect = $('#tablePile')[0].getBoundingClientRect();
        var x = e.clientX - rect.left; //x position within the element.
        var y = e.clientY - rect.top + 180 ;  //y position within the element.
        elmnt.style.top     = y + "px";
        elmnt.style.left    = x + "px";
        elmnt.classList.remove('lifted');
    }
}

function init_lift(){
    $('#clicker')[0].addEventListener("click", putElement, true);
}

