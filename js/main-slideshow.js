///// --- Slideshow script; denepnds on IE-specific polyfill for setInterval arguments, and hidden property and visibility change event variables/code, both defined in layout_two_columns.html template file for CES
var slideUpTime = 6000;
var transitionTime = 2400;
var timerIDs = new Array();
var lastSlides = new Array();

function stopAnimation(id){
    if (typeof timerIDs[id] !== 'undefined'){
        window.clearInterval(timerIDs[id]);
        timerIDs[id] = 0;
    } 
}

function resumeAnimation(id){
    if (!timerIDs[id] && $('#' + id + ' div').length > 1){
        play(id);
        timerIDs[id] = window.setInterval(play, slideUpTime, id);
    }
}

function play(id){
    if ( $('#' + id + ' div').length > 1 ){
        lastSlides[id] = $('#' + id + ' div:last');
        lastSlides[id].stop().animate({opacity: 0}, transitionTime, "swing", function (){$('#' + id + ' div:first').before(lastSlides[id]);lastSlides[id].css({opacity: 1});});
    }
    else{
        stopAnimation(id);
    }
}

function playAll() {
    $(".slideshow").each(function(){
            resumeAnimation($(this).attr("id"));
    });
}

function stopAll(){
    $(".slideshow").each(function(){
            stopAnimation($(this).attr("id"));
    });
}

$(document).ready(function(){slideUpTime = $("#category-slideshow").length ? 5000 : slideUpTime});

$(window).load(function(){
    $('.slideshow div:hidden').fadeIn(transitionTime);
    playAll();
    $(".slideshow").hover(function(){stopAnimation($(this).attr("id"));}, function(){resumeAnimation($(this).attr("id"));});
    $(document, window).on(visibilityChange, function(){document[hidden] ? stopAll : playAll;}, false);
    $(window, document, "body").focus(playAll).blur(stopAll);    
});