<?php

/**
 * Class login
 * handles the user's login and logout process
 */

class Login
{
    /**
     * @var object The database connection
     */
    private $db_connection = null;
    /**
     * @var array Collection of error messages
     */
    public $errors = array();
    /**
     * @var array Collection of success / neutral messages
     */
    public $messages = array();

    /**
     * the function "__construct()" automatically starts whenever an object of this class is created,
     * you know, when you do "$login = new Login();"
     */
    public function __construct()
    {
        // create/read session, absolutely necessary
        session_start();

        // check the possible login actions:
        // if user tried to log out (happen when user clicks logout button)
        if (isset($_GET["logout"])) {
            $this->doLogout();
        }
        // login via post data (if user just submitted a login form)
        elseif (isset($_POST["login"])) {
            $this->dologinWithPostData();
        }
    }

    /**
     * log in with post data
     */
    private function dologinWithPostData()
    {
        // check login form contents
        if (empty($_POST['user_name'])) {
            $this->errors[] = "Benutzername leer.";
        } elseif (empty($_POST['user_password'])) {
            $this->errors[] = "Passwort leer.";
        } elseif (!empty($_POST['user_name']) && !empty($_POST['user_password'])) {

            // create a database connection, using the constants from config/db.php (which we loaded in index.php)
            $this->db_connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

            // change character set to utf8 and check it
            //if (!$this->db_connection->set_charset("utf8")) {
            //    $this->errors[] = $this->db_connection->error;
            //}

            // if no connection errors (= working database connection)
            if (!$this->db_connection->connect_errno) {

                // escape the POST stuff
                $user_name = $this->db_connection->real_escape_string($_POST['user_name']);
                $otp=$user_name;
                $token_id=substr ($user_name, 0, 12);

                // database query, getting all the info of the selected user (allows login via email address in the
                // username field)
                $sql = "SELECT users.user_name, users.user_email, user_pwd.user_password_hash, users.user_id, user_pwd.gilt_seit, verbleibende_versuche, gesperrt_bis, users.token_id
                        FROM users, user_pwd
                        WHERE (users.user_name = '" . $user_name . "' OR users.user_email = '" . $user_name . "' OR users.token_id='" . $token_id . "')
                           AND users.user_id=user_pwd.user
                        ORDER BY gilt_seit DESC;";
                
                $result_of_login_check = $this->db_connection->query($sql);

                // if this user exists
                if ($result_of_login_check->num_rows > 0) {

                    // get result row (as an object)
                    $result_row = $result_of_login_check->fetch_object();
                    
                    // check if user has a Yubikey-ID (if yes, then verify password and Yubikey-OTP
                    if ($result_row->token_id) {
						if (password_verify($_POST['user_password'], $result_row->user_password_hash)) {
							if (substr ($otp, 0, 12) == $token_id)
							{
								require_once "./yubikeyPHPclass/Yubikey.php";
								
								$token = new Yubikey(YUBIKEY_API_ID, YUBIKEY_SIGNATURE_KEY);
								
								$token->setCurlTimeout(20);
								$token->setTimestampTolerance(500);
								
								if ($token->verify($otp))
								{
									// Last LogIn schreiben
									$sql="UPDATE users SET last_login='".date("Y-m-d H:i:s")."' WHERE user_id=".$result_row->user_id;
									$this->db_connection->query($sql);
									
									// write user data into PHP SESSION (a file on your server)
									$_SESSION['user_name'] = $result_row->user_name;
									$_SESSION['user_email'] = $result_row->user_email;
									$_SESSION['user_login_status'] = 1;
									$_SESSION['user_id'] = $result_row->user_id; // required for Kreda
								}
								else
								{
									$this->errors[]="Yubikey-&Uuml;berpr&uuml;fung ist fehlgeschlagen: ".$token->getLastResponse()." - CURL Timeout: ".$token->getCurlTimeout()." - Timestamp Tolerance: ".$token->getTimestampTolerance();
								}
							}
							else
							{
								$this->errors[]="Der Yubikey geh&ouml;rt Ihnen nicht.";
							}
						}
						else
						{
							$this->errors[]="Das Passwort ist falsch."; // TODO erweitern
						}
					}
                    else
                    {
						// check if User is paused
						if (date("Y-m-d H:i:s") > $result_row->gesperrt_bis) {
							
							// using PHP 5.5's password_verify() function to check if the provided password fits
							// the hash of that user's password
							if (password_verify($_POST['user_password'], $result_row->user_password_hash)) {
								// Last LogIn schreiben
								$sql="UPDATE users SET last_login='".date("Y-m-d H:i:s")."' WHERE user_id=".$result_row->user_id;
								$this->db_connection->query($sql);
								
								// nach dem Einloggen werden verbleibende_versuche wieder erneuert
								$sql="UPDATE user_pwd SET verbleibende_versuche=5 WHERE user=".$result_row->user_id." AND gilt_seit='".$result_row->gilt_seit."'";
								$this->db_connection->query($sql);
	
								// write user data into PHP SESSION (a file on your server)
								$_SESSION['user_name'] = $result_row->user_name;
								$_SESSION['user_email'] = $result_row->user_email;
								$_SESSION['user_login_status'] = 1;
								$_SESSION['user_id'] = $result_row->user_id; // required for Kreda
	
							} else {
								// TODO: aeltere Passworte pruefen
								// noch x Versuche
								$sql="SELECT verbleibende_versuche FROM user_pwd WHERE user=".$result_row->user_id." AND gilt_seit='".$result_row->gilt_seit."'";
								$result_verbleibende_versuche = $this->db_connection->query($sql);
								$result_verbleibende_versuche = $result_verbleibende_versuche->fetch_object();
								$verbleibende_versuche = $result_verbleibende_versuche->verbleibende_versuche;
								if ($verbleibende_versuche>0) {
									$sql="UPDATE user_pwd SET verbleibende_versuche=".($verbleibende_versuche-1)." WHERE user=".$result_row->user_id." AND gilt_seit='".$result_row->gilt_seit."'";
									$this->db_connection->query($sql);
									$this->errors[] = "Noch ".$verbleibende_versuche." Versuche.";
								}
								else {
									$sperrung_mktime = mktime(date("H"), date("i")+5, 0, date("m"), date("d"), date("Y"));
									$sperrung_sql = date("Y-m-d H:i:s",$sperrung_mktime);
									$sql="UPDATE user_pwd SET gesperrt_bis='".$sperrung_sql."' WHERE user=".$result_row->user_id." AND gilt_seit='".$result_row->gilt_seit."'";
									$this->db_connection->query($sql);
									$this->errors[] = "Aufgrund mehrerer falscher Passworteingaben sind Sie bis ".date("H:i",$sperrung_mktime)." gesperrt.";
								}
								
								$this->errors[] = "Falsches Passwort. Geben Sie das Passwort nochmal ein.";
							}
						}
						else {
							$this->errors[] = "Sie sind noch bis ".$result_row->gesperrt_bis." gesperrt.";
						}
					}
                } else {
                    $this->errors[] = "Dieser Benutzer existiert nicht.";
                }
            } else {
                $this->errors[] = "Datenbank-Verbindungsproblem.";
            }
        }
    }

    /**
     * perform the logout
     */
    public function doLogout()
    {
        // delete the session of the user
        $_SESSION = array();
        session_destroy();
        // return a little feeedback message
        $this->messages[] = "Sie wurden ausgeloggt.";

    }

    /**
     * simply return the current state of the user's login
     * @return boolean user's login status
     */
    public function isUserLoggedIn()
    {
        if (isset($_SESSION['user_login_status']) AND $_SESSION['user_login_status'] == 1) {
            return true;
        }
        // default return
        return false;
    }
}

