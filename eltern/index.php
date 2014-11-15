<?php
//  This file is part of Kreda.
//
//  Kreda is free software: you can redistribute it and/or modify
//  it under the terms of the GNU Affero Public License as published by
//  the Free Software Foundation, either version 3 of the License, or
//  (at your option) any later version.
//
//  Kreda is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  GNU Affero Public License for more details.
//
//  You should have received a copy of the GNU Affero Public License
//  along with Kreda.  If not, see <http://www.gnu.org/licenses/>.
//
//  Diese Datei ist Teil von Kreda.
//
//  Kreda ist Freie Software: Sie können es unter den Bedingungen
//  der GNU Affero Public License, wie von der Free Software Foundation,
//  Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
//  veröffentlichten Version, weiterverbreiten und/oder modifizieren.
//
//  Kreda wird in der Hoffnung, dass es nützlich sein wird, aber
//  OHNE JEDE GEWÄHELEISTUNG, bereitgestellt; sogar ohne die implizite
//  Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
//  Siehe die GNU Affero Public License für weitere Details.
//
//  Sie sollten eine Kopie der GNU Affero Public License zusammen mit diesem
//  Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
header('Content-Type: text/html; charset=ISO-8859-1');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Zensuren - Ansicht</title>
    <meta name="author" content="Micha Schubert, Christopher Wolff">
    <meta name="robots" content="noindex, nofollow">
	<meta http-equiv="content-type" content="application/xhtml+xml; charset=ISO-8859-1" />
    <link rel="shortcut icon" href="./favicon.ico" type="image/x-icon">
    <!--Stylheets-->
    <link rel="stylesheet" media="screen" type="text/css" href="./format.css">
    <link rel="stylesheet" media="print, embossed" type="text/css" href="./format_drucken.css">
    <!--jqueryLiberay-->
    <script type="text/javascript" src="./jquery-1.js"></script>
    <script type="text/javascript" src="./jquery.js"></script>
    <link type="text/css" href="./jquery.css" rel="Stylesheet">
    <!--jqueryLiberay-->
    <script src="./chart/morris.js"></script>
    <script src="./chart/morris.min.js"></script>
    <script src="./vektor/raphael-min.js"></script>
    <link rel="stylesheet" type="text/css" href="./chart/morris.css">
    <!--Externe Daten-->
    <script src="./tipsy.js"></script>

</head>
<body>
	<div class="wrapper">
	<div class="header">
		<table class="schulstempel" width="100%">
			<tr>
				<th>
					<img src="logo.gif" alt="logo">
				</th>
				<th>
					Evangelische Schulgemeinschaft Erzgebirge <br />
					Stra&szlig;e der Freundschaft 11 <br />
					09456 Annaberg-Buchholz <br />
				</th>
			</tr>
		</table>
	</div>
	<div class="inhalt">
<?php
header('Content-Type: text/html; charset=ISO-8859-1');

if (empty($_POST["username"])) {
	echo '<form action="./index.php" method="post">
		<label for="username" style="width: 160px; float: left;">Benutzername:</label> <input type="text" name="username" /><br />
		<label for="password" style="width: 160px; float: left;">Passwort:</label> <input type="password" name="password" /><br />
		<input type="submit" value="absenden" />
	</form>';
}
else {
	include("./export/schuelerdaten.php");
	$username=$_POST["username"];
	$password=md5($_POST["password"]);
	
	$username_gefunden=false;
	
	foreach ($schuelerarray as $my_schueler) {
		if ($username==$my_schueler["name"]) {
			$username_gefunden=true;
			break;
		}
	}
	
	// rudimentaere Bruteforce-Vermeidung
	session_start();
	if ($_SESSION["schueler_pwd_gesperrt_bis"]!="" and $_SESSION["schueler_pwd_gesperrt_bis"]>date("H:i:s"))
		die("Sie sind noch bis ".$_SESSION["schueler_pwd_gesperrt_bis"]." Uhr gesperrt, wegen falscher Passworteingabe");
	
	//if (!$username_gefunden)
	//	die ("Dieser Benutzer existiert nicht, oder wurde nicht freigegeben.");
	
	// 
	//if (md5($password.$export_pwd)!=$my_schueler["pwd"])
	//	die ("Passwort ist nicht korrekt.");
	
	if (!$username_gefunden or md5($password.$export_pwd)!=$my_schueler["pwd"]) {
		$_SESSION["schueler_pwd_gesperrt_bis"]=date("H:i:s", mktime()+5);
		die ("Dieser Benutzer existiert nicht, oder das Passwort ist nicht korrekt.");
	}
	
	// ----------------- Dechiffrierung Initialisieren ------------------------------
	// Open the cipher
	//$td = mcrypt_module_open('tripledes', '', 'ecb', '');
	// Create the IV and determine the keysize length, use MCRYPT_RAND on Windows instead
	//$iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
	//$ks = mcrypt_enc_get_key_size($td);
	
	// Initialize encryption module for decryption
	//if (mcrypt_generic_init($td, $key, $iv) != 1) {
		
		// create key
		// TODO: very secret key to password + random_des_exports
		//$key = substr(md5($export_pwd), 0, $ks);
		
		// Initialize encryption
		//mcrypt_generic_init($td, $key, $iv);
		
		// Decrypt encrypted string
		$ks=24;
		$key = substr(md5($password.md5($export_pwd)), 0, $ks);

		$decrypted_data = mcrypt_ecb (MCRYPT_3DES, $key, base64_decode(substr(stripslashes($my_schueler["html"]), 0, -1)), MCRYPT_DECRYPT);
		// debugging:
		//echo "<br />encrypt Pwd: ".$key."<br>Export Salt: ".$export_pwd."<br>md5 des eingegebenen Pwd: ".$password."<br>Salted Schuelerpwd der Datei: ".$my_schueler["pwd"]."<br /><br /><br />";
		//echo strlen($my_schueler["html"])."<br /><br /><br />";
		//echo strlen(stripslashes($my_schueler["html"]))."<br /><br /><br />";
		//echo strlen(substr(stripslashes($my_schueler["html"]), 0, -1))."<br /><br /><br />";
		//echo strlen($decrypted_data)."<br /><br /><br />";
		//echo strlen(mcrypt_ecb (MCRYPT_3DES, $key, base64_decode(substr(stripslashes($my_schueler["html"]), 0, -1)), MCRYPT_DECRYPT))."<br /><br /><br />";
		echo $decrypted_data."<br /><br /><br />";
		
		
		//echo $my_schueler["html"];
		//$decrypted = mdecrypt_generic($td, base64_decode($schuelerarray[497]));
		
		// Terminate decryption handle and close module
		//mcrypt_generic_deinit($td);
		//mcrypt_module_close($td);
	//}
	//else
	//	echo "Fehler";
			
	// Show string
	//echo $export_pwd;
	//echo $schuelerarray[497];
	//echo $decrypted . "\n";
	echo '<p>Stand: '.$export_date.'</p>';
}
?>
</div>
</div>
</body>
</html>
