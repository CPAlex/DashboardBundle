/**
 * Created by gallaial on 30/07/2015.
 */
function affichetest()
{

        $('#appear').css({'display': 'block'});

}
  $( "#bouton2" ).click(function( event ) {
        console.log("Test");
        event.preventDefault();
        console.log("Test");
  });

//function redirectToListPapersByUserId() {
//
//}

$('#liste1').change(function() {
    alert("OK");
    // set the window's location property to the value of the option the user has selected
    window.location = $(this).val();
});

$('#liste2').change(function() {
    // set the window's location property to the value of the option the user has selected
    window.location = $(this).val();
});

$('#toto').change(function() {
    // set the window's location property to the value of the option the user has selected
    window.location = $(this).val();
});

function bonjour(){

    var liste1, value1,liste2,value2;
    liste1 = document.getElementById("liste1");
    liste2 = document.getElementById("liste2");
    value1 = liste1.options[liste1.selectedIndex].value;
    value2 = liste2.options[liste2.selectedIndex].value;
    alert("l'id de l'utilisateur est "+value1);
    alert("l'id de l'exercice est 1223 "+value2);
    return value1;
}

