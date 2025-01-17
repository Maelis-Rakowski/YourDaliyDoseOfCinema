//Pas de syntaxe ajax avancée car le document n'est pas encore chargé
document.addEventListener('DOMContentLoaded',function(){
    // Ajoute un eventclick sur le bouton avec l'id "envoyer"
    // Ce bouton se trouve dans le viewTmdb du formulaire de recherche du film
    $("#movieInput").on('input',function(e){ 
        e.preventDefault(); // On empêche le navigateur d'envoyer le formulaire, on fait le post nous même
        $.post(//Syntaxe améliorée de la fonction $.ajax() de base commentée dessous
                'tmdb/callTMDBJson', // Appelle la fonction callTMDBJson du controller tmdb
                {
                    movieInput : $("#movieInput").val(), // Récupère la valeur du formulaire où l'id est movieInput
                    //Il s'agit du nom rentré par l'admin
                }         
                ).done(function(reponse_html){//Quand la requête post est terminée,appel de la fonction done()
                    //Le paramètre reponse_html est le echo (entre autre le return) de la méthode callTMDBJson
                    $('#datas').html(reponse_html);//Remplit la balise id "datas" de la vue avec la réponse html du controller
                })
    });
});

function addMovie(idmovieToAdd){
    $.post('tmdb/addMovie',
    {
        idmovie : idmovieToAdd,
    }         
    ).done(function(reponse_html){
        $('#answer' + idmovieToAdd).empty();
        $('#answer' + idmovieToAdd).html(reponse_html);
    })
}

