<?php
require_once FILE::build_path(array('view','view.php'));
require_once FILE::build_path(array('model','UserModel.php'));

//Pour la renitialisation du mdp
//<!-- Reset le mot de passe (private key, date de renouvellement de mdp/ lien vers changer mdp dans le mail, dans l'url je mets private key (/moodifiermdp/privatekey) si la private key). faire la différence du temps (30), faire un seed en fonction de la date. Private key email de la personne et timestamp -->
class ControllerLogin {

    //variable of the view to generate
    private $_view;

    public function __construct() {      
    }

    public function readAll() {               
        $this->signUpView();
    }

    //View for SignUp (create new user)
    public function signUpView(){
        //If the user is already connected, it shows the view connected, else signUpView
        if($this->checkSessionAlreadyExists()==true){
            $this->connected();
            exit;
        }
        $this->_view = new View(array('view','login','viewSignUp.php'));
        //Generate the view without data
        $this->_view->generate(array(null));
    }
    
    public function signInView($error = "") {      //si aucun parametre array(null) assigné par default
        //If the user is already connected, it shows the view connected, else signInView
        if($this->checkSessionAlreadyExists()==true){
            $this->connected();
            exit;
        }
        $this->_view = new View(array('view', 'login', 'viewSignIn.php'));
        //Generate the view without data
        $this->_view->generate(array('error'=>$error));
    }

    public function connected(){
        // redirect to home
        header('Location: /home');
        exit();
    }

    public function disconnect(){
        session_unset();
        session_destroy();
        $this->signInView();
    }

    //Register
    public function signUp() {

        $email = $_POST["email"];
        $pseudo = $_POST["pseudo"];
        $password = $_POST["password"];
        $confirmPassword = $_POST["password2"];

        if($password != $confirmPassword) {
            echo("Mot de passe non identique");
        }
        else {
            UserModel::create($email, $pseudo, $password);
            $this->createSession($pseudo, $password, false);
            $this->connected();
        }
    }

    //Verifie que le user n'existe pas deja
    public function doesUserExists() {
        $pseudo = $_POST['pseudo'];
        $users = UserModel::getUserByPseudo($pseudo);
        $response = empty($users);
        echo json_encode($response);
    }

    public function checkEmailExists(){
        $email = $_POST['emailInput'];
        $users = UserModel::getUserByEmail($email);
        $answer = false;
        if($users!=null) {
            $answer = true;
        }
        echo json_encode(["answer"=>$answer]);
    }

    //Try connect
    public function signIn(){
        if(isset($_POST["pseudo"]) && isset($_POST["password"])) {
            $pseudo = $_POST["pseudo"];
            $password = $_POST["password"];
            // Récupérer l'utilisateur correspondant au pseudo
            $users = UserModel::getUserByPseudo($pseudo);
            // Vérifier si l'utilisateur existe
            if(!empty($users)) {
                $user = $users[0];
    
                // Vérifier si le mot de passe correspond
                if(password_verify($password, $user->getPassword())) {
                    // Mot de passe valide, connecter l'utilisateur
                    $this->createSession($pseudo, $user->getPassword(), $user->getIsAdmin());
                    $this->connected();
                } else {
                    // Mot de passe invalide, afficher la vue de connexion
                    $this->signInView("errorConnexion");
                }
            } else {
                // Utilisateur inexistant, afficher la vue de connexion
                $this->signInView("errorConnexion");
            }
        } else {
            // Les champs pseudo et mot de passe n'ont pas été envoyés, afficher la vue de connexion
            $this->signInView("errorConnexion");
        }
    }

    public function checkSessionAlreadyExists(){
        if(isset($_SESSION['pseudo'])){
            return true;
        }
        return false;
    }

    public function createSession($pseudo, $password, $isAdmin){
        $_SESSION['pseudo'] = $pseudo;
        $_SESSION['password'] = $password;
        $_SESSION['isAdmin'] = $isAdmin;
    }

    public function resetPassword(){
        
        $this->_view = new View(array('view','login','viewResetPassword.php'));
        //Generate the view without data
        $this->_view->generate(array(null));
    }

    public function sendEmail() {

        $email = $_POST['emailInput'];
        $users = UserModel::getUserByEmail($email);
        if($users!=null) {
            $user = $users[0];
            $current_date = date('Y-m-d H:i:s'); // Obtenir la date et l'heure actuelles
            $random_string = bin2hex(random_bytes(16)); // Générer une chaîne de caractères aléatoire
            $token_data =  $user->getId() . $current_date . $random_string . $email;
            $token = hash('sha256', $token_data);

            UserModel::updateUserToken($user->getId(), $token, $current_date);
            $this->_view = new View(array('view','login','viewMail.php'));
            //Generate the view without data
            $this->_view->generate(array('token'=>$token, 'email'=>$email));
        }
    }

    function generatePrivateKey($email) {
        // Générer une clé privée en concaténant l'email et la date actuelle
        $privateKey = $email . "_" . date("Y-m-d H:i:s");
    
        // Ajouter un timestamp pour l'expiration après 20 minutes
        $expirationTimestamp = time() + (20 * 60);
        $privateKey .= "_" . $expirationTimestamp;
    
        // Hasher la clé privée pour plus de sécurité
        $hashedKey = hash("sha256", $privateKey);
    
        return $hashedKey;
    }
    
    public function updatePassword(){
        $email = $_POST['email'];
        $users = UserModel::getUserByEmail($email);
    
        if($users==null){
            echo("email non reconnu");
        }
        else {
            $user = $users[0];
        }

        //If confirm password not same as new password, abort
        if($_POST['newPassword']!=$_POST['confirmPassword']){
            echo("Passwords must match");
        }
        else if($_POST['token']!=$user->getToken()){
            echo("Token incorrect");
        }
        else {
            // Récupérer la dernière date et heure de demande de token de l'utilisateur
            $lastRequestedDate = $user->getLastRequestedDate();

            // Créer un objet DateTime pour la dernière date et heure de demande de token
            $lastRequestedDateTime = new DateTime($lastRequestedDate);
            // Ajouter 15 minutes à la dernière date et heure de demande de token
            $lastRequestedDateTime->add(new DateInterval('PT15M'));

            // Créer un objet DateTime pour la date et l'heure actuelles
            $currentDateTime = new DateTime();
            
            // Vérifier si la date et l'heure actuelles sont supérieures à 15 minutes après la dernière demande de token
            if ($currentDateTime > $lastRequestedDateTime) {
                // Le token a expiré
                echo("Token expiré, renouvellez votre demande de mot de passe");
            }
            else {
                //update the new password and then go back to signInView
                UserModel::updateUserPassword($user->getId(), $_POST['newPassword']);
                $this->signInView();
            }
        }
    }
}
?>