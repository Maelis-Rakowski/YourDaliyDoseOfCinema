<div>
    <!-- Chargement du script -->
    <script src="/scripts/tmdb.js"></script>
    <?php $this->_t="TMDB Request"?>
    <div id="tmdb">
        <h2>Guess the movie of the day !</h2>
        <!-- Forumulaire post -->
    </div>
    <div class="container p-2">
        <form method="post">
            <input id="movieInput" type="text" name="movie_title">
            <button type="button" id="envoyer">Search</button>
        </form>
    </div>
</div>

<div id='datas'>
<!-- Ce div est remplit par la requete de l'utilisateur avec ajax 
dans ControllerAdminTmdb->callTMDBJson()
-->
</div>
