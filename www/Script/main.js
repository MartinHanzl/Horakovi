$(document).ready(function(){
    $("#carky").click(function(){
        $("#nav").slideToggle("slow");
    });
    if($(window).width() >= 992) {
        $("#nav").show();
    } else {
        $("#nav").hide();
    }

    $("#edit-popis").click(function(){
        $("#editPopis").slideToggle();
    });
    $("#edit-cenik").click(function(){
        $("#editCenik").slideToggle();
    });
    $("#edit-galerie").click(function(){
        $("#editGalerie").slideToggle();
    });
    $("#login").click(function(){
        $("#loginDiv").slideToggle();
    });


    $("#close-popis").click(function(){
        $("#editPopis").slideToggle();
    });
    $("#close-cenik").click(function(){
        $("#editCenik").slideToggle();
    });
    $("#close-galerie").click(function(){
        $("#editGalerie").slideToggle();
    });
    $("#close-login").click(function(){
        $("#loginDiv").slideToggle();
    });

    $("#editPopis").hide();$("#editCenik").hide();$("#editGalerie").hide();$("#loginDiv").hide();
});

cosnole.log("Ahoj");