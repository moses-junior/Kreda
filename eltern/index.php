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
?>
<!DOCTYPE HTML>

<html>
   <head>
	<title>Zensuren</title>
	<meta name="author" content="automatisch generiert durch Kreda" />
	<meta name="robots" content="noindex, nofollow" />
	<meta http-equiv="Content-Script-Type" content="text/javascript" />
	<meta http-equiv="Content-Style-Type" content="text/css" />
	<meta http-equiv="content-type" content="application/xhtml+xml; charset=ISO-8859-1" />
</head>
<body>
<?php
header('Content-Type: text/html; charset=ISO-8859-1');

if (empty($_POST["username"])) {
	echo '<form action="./index.php" method="post">
		<label for="username">Benutzername: <input type="text" name="username" /></label><br />
		<label for="password">Passwort: <input type="password" name="password" /></label><br />
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
	
	if (!$username_gefunden or $password!=$my_schueler["pwd"])
		die ("Dieser Benutzer existiert nicht, oder das Passwort ist nicht korrekt.");
	
	if (!$username_gefunden)
		die ("Dieser Benutzer existiert nicht, oder wurde nicht freigegeben.");
	
	if ($password!=$my_schueler["pwd"])
		die ("Passwort ist nicht korrekt.");
	
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
		echo $my_schueler["html"];
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
}
?>
</body>
</html>
