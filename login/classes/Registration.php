<?php

/**
 * Class registration
 * handles the user registration
 */
class Registration
{
    /**
     * @var object $db_connection The database connection
     */
    private $db_connection = null;
    /**
     * @var array $errors Collection of error messages
     */
    public $errors = array();
    /**
     * @var array $messages Collection of success / neutral messages
     */
    public $messages = array();

    /**
     * the function "__construct()" automatically starts whenever an object of this class is created,
     * you know, when you do "$registration = new Registration();"
     */
    public function __construct()
    {
        if (isset($_POST["register"])) {
            $this->registerNewUser();
        }
    }

    /**
     * handles the entire registration process. checks all error possibilities
     * and creates a new user in the database if everything is fine
     */
    private function registerNewUser()
    {
        die("Derzeit deaktiviert.");
        if (empty($_POST['user_name'])) {
            $this->errors[] = "leerer Benutzername";
        } elseif (empty($_POST['user_password_new']) || empty($_POST['user_password_repeat'])) {
            $this->errors[] = "leeres Passwort";
        } elseif ($_POST['user_password_new'] !== $_POST['user_password_repeat']) {
            $this->errors[] = "Passwort und Passwortbest&auml;tigung stimmen nicht &uuml;berein";
        } elseif (strlen($_POST['user_password_new']) < 6) {
            $this->errors[] = "Passwort muss aus mindestens 6 Zeichen bestehen";
        } elseif (strlen($_POST['user_name']) > 64 || strlen($_POST['user_name']) < 3) {
            $this->errors[] = "Benutzername muss zwischen 3 und 64 Zeichen lang sein";
        } elseif (!preg_match('/^[a-z\d]{2,64}$/i', $_POST['user_name'])) {
            $this->errors[] = "Im Benutzername d&uuml;rfen nur Zahlen und Buchstaben a-Z vorkommen und muss 3 - 64 Zeichen lang sein";
        } elseif (empty($_POST['user_email'])) {
            $this->errors[] = "E-Mail darf nicht leer sein";
        } elseif (strlen($_POST['user_email']) > 64) {
            $this->errors[] = "E-Mail darf nicht l&auml;nger als 64 Zeichen sein";
        } elseif (!filter_var($_POST['user_email'], FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = "E-Mail-Adresse nicht im akzeptierten Format";
        } elseif (!empty($_POST['user_name'])
            && strlen($_POST['user_name']) <= 64
            && strlen($_POST['user_name']) >= 3
            && preg_match('/^[a-z\d]{3,64}$/i', $_POST['user_name'])
            && !empty($_POST['user_email'])
            && strlen($_POST['user_email']) <= 64
            && filter_var($_POST['user_email'], FILTER_VALIDATE_EMAIL)
            && !empty($_POST['user_password_new'])
            && !empty($_POST['user_password_repeat'])
            && ($_POST['user_password_new'] === $_POST['user_password_repeat'])
        ) {
            // create a database connection
            $this->db_connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

            // change character set to utf8 and check it
            if (!$this->db_connection->set_charset("utf8")) {
                $this->errors[] = $this->db_connection->error;
            }

            // if no connection errors (= working database connection)
            if (!$this->db_connection->connect_errno) {

                // escaping, additionally removing everything that could be (html/javascript-) code
                $user_name = $this->db_connection->real_escape_string(strip_tags($_POST['user_name'], ENT_QUOTES));
                $user_email = $this->db_connection->real_escape_string(strip_tags($_POST['user_email'], ENT_QUOTES));

                $user_password = $_POST['user_password_new'];

                // crypt the user's password with PHP 5.5's password_hash() function, results in a 60 character
                // hash string. the PASSWORD_DEFAULT constant is defined by the PHP 5.5, or if you are using
                // PHP 5.3/5.4, by the password hashing compatibility library
                $user_password_hash = password_hash($user_password, PASSWORD_DEFAULT);

                // check if user or email address already exists
                $sql = "SELECT * FROM users WHERE user_name = '" . $user_name . "' OR user_email = '" . $user_email . "';";
                $query_check_user_name = $this->db_connection->query($sql);

                if ($query_check_user_name->num_rows == 1) {
                    $this->errors[] = "Benutzername / E-Mail-Adresse bereits belegt.";
                } else {
                    // write new user's data into database
                    $sql = "INSERT INTO users (user_name, user_password_hash, user_email)
                            VALUES('" . $user_name . "', '" . $user_password_hash . "', '" . $user_email . "');";
                    $query_new_user_insert = $this->db_connection->query($sql);
                    $user_id=mysqli_insert_id($this->db_connection);
                    $sql = "INSERT INTO user_pwd (user, gilt_seit, user_password_hash)
                            VALUES(" . $user_id . ", '" . date("Y-m-d h:i:s") . "', '" . $user_password_hash . "');";
                    
                    $query_new_user_insert_pwd = $this->db_connection->query($sql);

                    // if user has been added successfully
                    if ($query_new_user_insert and $query_new_user_insert_pwd) {
                        $this->messages[] = "Ihr Account wurde erfolgreich angelegt. Sie k&ouml;nnen sich nun einloggen.";
                    } else {
                        $this->errors[] = "Die Registrierung ist fehlgeschlagen. Bitte gehen Sie zur&uuml;ck und versuchen es nochmal.";
                    }
                }
            } else {
                $this->errors[] = "Keine Datenbank-Verbindung.";
            }
        } else {
            $this->errors[] = "Ein unbekannter Fehler trat auf.";
        }
    }
}
