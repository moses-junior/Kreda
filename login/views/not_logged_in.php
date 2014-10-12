<?php
$pfad="../";

?>
<!DOCTYPE HTML>

<html>
   <head>
	<title>Kreda - LogIn</title>
	<meta name="author" content="Micha Schubert" />
	<meta name="robots" content="noindex, nofollow" />
	<meta http-equiv="Content-Script-Type" content="text/javascript" />
	<meta http-equiv="Content-Style-Type" content="text/css" />
	<link rel="shortcut icon" href="<?php echo $pfad; ?>look/favicon.ico" type="image/x-icon" />
	<link rel="stylesheet" type="text/css" href="<?php echo $pfad; ?>look/format.css" />
	<!--<script type="text/javascript" src="<?php echo $pfad; ?>javascript/erweitern.js"></script>-->
  </head>
  <body>
	  <div class="inhalt">
<?php

// show potential errors / feedback (from login object)
if (isset($login)) {
    if ($login->errors) {
        foreach ($login->errors as $error) {
            echo $error."<br />";
        }
    }
    if ($login->messages) {
        foreach ($login->messages as $message) {
            echo $message."<br />";
        }
    }
}
?>

<!-- login form box -->
<fieldset><legend>LogIn</legend>
<form method="post" action="index.php" name="loginform" id="loginform" autocomplete="off">

    <label for="login_input_password">Passwort:</label>
    <input id="login_input_password" class="login_input" type="password" name="user_password" autocomplete="off" required autofocus /><br />

    <label for="user_name">Yubikey-OTP:</label>
    <input id="login_input_username" class="login_input" type="password" name="user_name" size="25" required /><br />

<!--
    <label for="login_input_password">Passwort</label>
    <input id="login_input_password" class="login_input" type="password" name="user_password" autocomplete="off" required />
    
    <label for="login_input_username">Yubikey</label>
    <input id="login_input_username" class="login_input" type="text" name="user_name" autocomplete="off" required autofocus />
	
	onclick="auswertung=new Array(new Array(0, 'user_name','yubikey')); pruefe_formular(auswertung); return false;"
-->
    <input type="submit" name="login" value="Log in" /><!-- onclick="yubitest(); return false;"-->

</form>
</fieldset>
<!--<a href="register.php">Registriere neuen Account</a>-->
</div>
<script>
   function yubitest() {
		if (document.getElementById('login_input_username').value.length!=44) {
			document.getElementById('login_input_username').style.border = 'solid red 1px';
			return false;
		}
		else
			document.getElementById('loginform').form.submit();
	}
	
	window.onload = function() {
		var input = document.getElementById("login_input_password").focus();
	}</script>
</body>
</html>
