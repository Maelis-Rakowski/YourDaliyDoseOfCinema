<?php
    require_once FILE::build_path(array('view','view.php'));
    class ControllerAdminTmdb {
        //variable of the view to generate
        private $_view;
        private $apiKey = '0168e4ae77bb634f0e51abb40d08f608';
        public function __construct() {        
        }

        public function readAll() {
            $datamovies = [];
            $this->_view = new View(array('view', 'admin', 'tmdb', 'viewTmdb.php'));
            $this->_view->generate(array('datamovies'=>$datamovies));
            
        }

        public function callTMDB(){
            
            $url = 'https://api.themoviedb.org/3/movie/details?api_key='.$this->apiKey.'&language=en-EN';

            // Faire la requête à l'API
            $response = file_get_contents($url);

            // Décoder la réponse JSON
            $datamovies = json_decode($response, true);
            return $datamovies;
        }

        public function callTMDBJson(){
           
            //Generate the view without data
            $query = $_POST['movieInput'];
            $query = str_replace(' ', '%20', $query);
            $url = 'https://api.themoviedb.org/3/search/movie?api_key='.$this->apiKey.'&query='.$query.'&include_adult=false&language=en-US';

            // Faire la requête à l'API
            $response = file_get_contents($url);
            //!!!!!!!!!!!!!!!!!!!!!!!
            // IL NE FAUT PAS FAIRE LA REQUETE SUR LES DETAILS QUAND ON LISTE MAIS UNIQUEMENT QUAND ON AJOUTE
            // CAR çA PREND TROP DE TEMPS DE FAIRE LA REQUETE DETAILS SUR TOUS LES FILMS
            //!!!!!!!!!!!!!!!!!!!!!!!!!!
            $datamovies = json_decode($response, true);

            if (isset($datamovies['results'])) {

                // Filtrer les films avec un vote_count > 500
                $filteredMovies = array_filter($datamovies['results'], function($movie) {
                    return $movie['vote_count'] > 500;
                });

                // Trier les résultats filtrés par nombre de votes
                usort($filteredMovies, function($a, $b) {
                    return $b['vote_count'] - $a['vote_count'];
                });

                // Remplacer les résultats dans datamovies par les films filtrés et triés
                $datamovies['results'] = $filteredMovies;
            }


            $this->_view = new View(array('view', 'admin', 'tmdb', 'viewList.php'));
            $this->_view->generate(array('datamovies'=>$datamovies),false);
        }

        public function addMovie(){
            $idmovie = (int)$_POST['idmovie'];
            $movie = json_decode(file_get_contents('https://api.themoviedb.org/3/movie/' . $idmovie . '?api_key=' . $this->apiKey), true);
            
            $countries = $movie['production_countries'];

            $credits = json_decode(file_get_contents('https://api.themoviedb.org/3/movie/' . $idmovie . '/credits?api_key=' . $this->apiKey), true);
            
            $answer = MovieModel::addMovie($movie);
            //Gestion d'erreur d'existance
            if($answer == -1){
                echo '<p class="text-danger error form-text">this movie already exists in the database<p>';
                return;
            }

            $movieID = Model::getPDO()->lastInsertId();
            
            // Utilisation de la fonction générique pour les réalisateurs
            MovieModel::handleEntity(
                array_filter($credits['crew'], function($crew) {
                    return $crew['job'] === "Director";
                }),
                'id',
                'directors',
                'idtmdb',
                function($crew) {
                    MovieModel::createDirector($crew['id'], $crew['name']);
                },
                function($movieID, $directorID) {
                    MovieModel::createMovieDirector($movieID, $directorID);
                },
                $movieID
            );
            
            // Utilisation de la fonction générique pour les pays
            MovieModel::handleEntity(
                $movie['production_countries'],
                'name',
                'countries',
                'name',
                function($country) {
                    MovieModel::createCountry($country['name']);
                },
                function($movieID, $countryID) {
                    MovieModel::createMovieCountry($movieID, $countryID);
                },
                $movieID
            );
            
            // Utilisation de la fonction générique pour les genres
            MovieModel::handleEntity(
                $movie['genres'],
                'name',
                'genres',
                'genre',
                function($genre) {
                    MovieModel::createGenre($genre['name']);
                },
                function($movieID, $genreID) {
                    MovieModel::createMovieGenre($movieID, $genreID);
                },
                $movieID
            );

            echo '<p class="error form-text text-success">Movie added successfully</p>';
        }
    }
?>