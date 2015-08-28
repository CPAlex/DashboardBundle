
function test(){
$( "#bouton2" ).click(function( event ) {
  event.preventDefault();
  console.log("Test");
});
}
		//preventDefault

function postForm(url){
        // Je r�cup�re les valeurs
        var selectuser = $('#liste1').val();
        var selectexo = $('#liste2').val();
		test();

        // Je v�rifie une premi�re fois pour ne pas lancer la requ�te HTTP
        // si je sais que mon PHP renverra une erreur
        if(selectuser === '' || selectexo === '') {
            alert('Erreur');
        } else {
            // Envoi de la requ�te HTTP en mode asynchrone
            $.ajax({
                url: url, // Le nom du fichier indiqu� dans le formulaire
                type: "post", // La m�thode indiqu�e dans le formulaire (get ou post)
                data: {
                    selectuser:selectuser,
                    selectexo:selectexo
                    },
				dataType: "Json",
				success : function(response) {
                    console.log((response.data).length);
                    $("tbody").empty();
                    var moyenne=0;
                    var taux=0;
                    var bestnote= 0,bestuser='undefined';
                    $("#moyenne").empty();
                    for(i=0;i<(response.data).length;i++) {
                        var score = (response.data[i].score.scorePaper / response.data[i].score.maxExoScore) * 20;
                        var debut= new Date(response.data[i].start.date);
                        var twoDigitMonth = ((debut.getMonth().length+1) === 1)? (debut.getMonth()+1) : '0' + (debut.getMonth()+1);
                        var twoDigitHours = ("0"+ debut.getHours()).slice(-2);
                        var debutformate = debut.getDate() + "/" + twoDigitMonth + "/" + debut.getFullYear()+" - "+twoDigitHours+"h"+debut.getMinutes()+"m"+debut.getSeconds()+"s";
                        var fin= new Date(response.data[i].end.date);
                        var twoDigitMonthFin = ((fin.getMonth().length+1) === 1)? (fin.getMonth()+1) : '0' + (fin.getMonth()+1);
                        var twoDigitHoursFin = ("0"+ fin.getHours()).slice(-2);
                        var finformate = fin.getDate() + "/" + twoDigitMonthFin + "/" + fin.getFullYear()+" - "+twoDigitHoursFin+"h"+fin.getMinutes()+"m"+fin.getSeconds()+"s";
                        $("tbody").append( '<tr>'+'<td id=td1 class="classic">' + response.data[i].userfirstname + " " + response.data[i].userlastname +
                        '</td>' + '<td id=td2 class="classic">' + response.data[i].exercise + '</td>'
                        + '<td id=td3 class="classic">' + debutformate + '</td>' //start.date
                        + '<td id=td4 class="classic">' + finformate + '</td>'//end.date
                        + '<td id=td5 class="classic">' + score + "/20" + '</td>'+'</tr>')
                        moyenne=score+moyenne;  //Somme des scores
                        if(score>=10)
                        {
                            taux=taux+1;
                        }
                        if(score>bestnote)
                        {
                            bestnote=score;
                            bestuser=response.data[i].userfirstname +' '+ response.data[i].userlastname;
                        }

                    }
                    if(response.nbcopie>0)
                    {
                    $("#nbcopie").empty().replaceWith('<p id=nbcopie style="margin-left:15px" style="color:dimgray">'+'Nombre de copie : '+JSON.stringify(response.nbcopie)+'</p>');
                        $('#note').empty().replaceWith('<p id= note style="margin-left:15px" style="color:dimgray">'+'Le meilleur élève est '+bestuser+' avec la note de : '+bestnote+'/20'+'</p>');
                        $('#taux').empty().replaceWith('<p id=taux style="margin-left:15px" style="color:dimgray">'+'Taux de réussite : '+(taux/(response.data).length)*100+' % '+'</p>');
                    }
                    $("#tentative").empty();
                    if(selectexo!=0 && selectuser!=0)
                    {
                        $("#tentative").empty().replaceWith('<p id=tentative style="margin-left:15px" style="color:dimgray">'+'Nombre tentative : '+JSON.stringify(response.tentative)+'</p>');
                    }
                    if(selectexo!=0) {
                        $('#moyenne').empty().replaceWith('<p id="moyenne" style="margin-left:15px" style="color:dimgray">'+'Moyenne :' + moyenne / JSON.stringify(response.nbcopie) + '/20'+'</p>');
                    }


            }
            });
		}
		return false;
	}
