/*
$.getScript( "https://ads.superawesome.tv/v2/impression?placement=37105&creative=231851&line_item=45542")
  .fail(function( jqxhr, settings, exception ) {
    console.error("Error on impression script loading", exception)
});

$(function () {
               $("#bg-left-side-play")
                               .css("background-image","url('//cdn.binw.net/assets/bw/DogDiaries_Left_1.png')")
        .css("left","-368px")
        .click(function() {
                                  gotoSA("https://ads.superawesome.tv/v2/click?placement=37105&creative=231850&line_item=45542");
                                });

    $("#bg-right-side-play")
                .css("background-image","url('//cdn.binw.net/assets/bw/DogDiaries_Right_1.png')")
        .css("right","-369px")
        .click(function() {
                                  gotoSA("https://ads.superawesome.tv/v2/click?placement=37105&creative=231851&line_item=45542");
                                }); 

    $(".lateral-background")
                .width(370)
                .height(1225)
                .css("top","-110px")
                .css("cursor","pointer");    

    $("#container-content-play")
                .find("img.grass")
                .attr("src","//cdn.binw.net/assets/bw/DogDiariesBottom.png");

    $(".wrapper")
                .css("background-color","#F37332");
});

function gotoSA(url){
    var win = window.open(url, '_blank');
    if (win){
        win.focus();
    }
}
