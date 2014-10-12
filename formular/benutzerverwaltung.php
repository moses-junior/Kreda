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

$user=new user();
$my_schule=$user->my["letzte_schule"];

if (userrigths("benutzerverwaltung", $my_schule)!=2)
	die("Sie haben nicht die erforderlichen Rechte, um Angestellte zu verwalten.");

function randomPassword() {
    $alphabet = "abcdefghijkmnpqrstuwxyzABCDEFGHKLMNPQRSTUWXYZ123456789"; // ohne o, O, 0, I, J
    $pass = array(); //remember to declare $pass as an array
    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
    for ($i = 0; $i < 8; $i++) {
        $n = rand(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }
    return implode($pass); //turn the array into a string
}

// FIXME: delete me
if ($_GET["benutzer"]==true)
	$_SESSION["user_id"]=injaway($_GET["user_id"]);

if($_GET["eintragen"]=="true" or $_GET["eintragen"]=="bearbeiten") {
	$user_name = $_POST['user_name'];
	$user_id = injaway($_POST["person"]);
	$user_email = $_POST['user_email'];
	
	$sql = "SELECT * FROM users LEFT JOIN schule_user ON users.user_id=schule_user.user WHERE user_id=".$user_id." ORDER BY schule_user.usertyp";
	$this_user=db_conn_and_sql($sql);
	$this_user=sql_fetch_assoc($this_user);
	
	if ($user_id>0) {
        if (empty($_POST['user_name'])) {
            $errors[] = "leerer Benutzername";
        } elseif (strlen($_POST['user_name']) > 64 || strlen($_POST['user_name']) < 3) {
            $errors[] = "Benutzername muss zwischen 3 und 64 Zeichen lang sein";
        } elseif (!preg_match('/^[a-z\d]{2,64}$/i', $_POST['user_name'])) {
            $errors[] = "Im Benutzername d&uuml;rfen nur Zahlen und Buchstaben a-Z vorkommen und muss 3 - 64 Zeichen lang sein";
        } elseif (empty($_POST['user_email'])) {
            $errors[] = "E-Mail darf nicht leer sein";
        } elseif (strlen($_POST['user_email']) > 64) {
            $errors[] = "E-Mail darf nicht l&auml;nger als 64 Zeichen sein";
        } elseif (!filter_var($_POST['user_email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "E-Mail-Adresse nicht im akzeptierten Format";
        } elseif (!empty($_POST['user_name'])
            && strlen($_POST['user_name']) <= 64
            && strlen($_POST['user_name']) >= 3
            && preg_match('/^[a-z\d]{3,64}$/i', $_POST['user_name'])
            && !empty($_POST['user_email'])
            && strlen($_POST['user_email']) <= 64
            && filter_var($_POST['user_email'], FILTER_VALIDATE_EMAIL)
        ) {
                // check if user or email address already exists
                $sql = "SELECT * FROM users WHERE user_id<>".injaway($_POST["person"])." AND (user_name = " . apostroph_bei_bedarf($user_name) . " OR user_email = " . apostroph_bei_bedarf($user_email) . ");";
                $query_check_user_name = db_conn_and_sql($sql);

                if (sql_num_rows($query_check_user_name) == 1) {
                    $errors[] = "Benutzername / E-Mail-Adresse bereits belegt.";
                } else {
                    // write new user's data into database
                    $sql = "UPDATE users SET user_name=".apostroph_bei_bedarf($user_name).", user_email=". apostroph_bei_bedarf($user_email) . ", token_id=". apostroph_bei_bedarf($_POST["token_id"]) . " WHERE user_id=".injaway($_POST["person"]).";";
                    
                    $user_aktualisieren = db_conn_and_sql($sql);
                    
                    //if ($this_user["usertyp"]==0)
					$sql = "UPDATE schule_user SET usertyp=".injaway($_POST["rolle"])." WHERE usertyp=".$this_user["usertyp"]." AND user=". $user_id . " AND schule=".$this_user["schule"].";";
					//else
					//	$sql = "INSERT INTO schule_user (usertyp, user, aktiv, schule) VALUES (".injaway($_POST["rolle"]).", ". $user_id . ", 1, ".$this_user["schule"].");";
                    
                    $schule_user_aktualisieren = db_conn_and_sql($sql);
                    
                    // if user has been added successfully
                    if ($user_aktualisieren and $schule_user_aktualisieren) {
						$messages[]='Benutzername: "'.$user_name.'" aktiv';
                    } else {
                        $errors[] = "Die Registrierung ist fehlgeschlagen. Bitte gehen Sie zur&uuml;ck und versuchen es nochmal.";
                    }
                }
        } else {
            $errors[] = "Ein unbekannter Fehler trat auf.";
        }
	}
	else
		$errors[] = "kein Benutzer ausgew&auml;hlt.";
}

if (($_POST["passwort"]==1 or $_GET["passwort"]=="random") and empty($errors)) {
	if (version_compare(PHP_VERSION, '5.3.7', '<')) {
		exit("Sorry, Simple PHP Login does not run on a PHP version smaller than 5.3.7 !");
	} else if (version_compare(PHP_VERSION, '5.5.0', '<')) {
		// if you are using PHP 5.3 or PHP 5.4 you have to include the password_api_compatibility_library.php
		// (this library adds the PHP 5.5 password hashing functions to older versions of PHP)
		require_once($pfad."login/libraries/password_compatibility_library.php");
	}
	require_once($pfad."login/classes/Registration.php");
	
	$user_id = injaway($_POST["person"]);
	if ($_GET["passwort"]=="random")
		$user_id=injaway($_GET["user_id"]);
	
	$user_password = randomPassword();
    
    // crypt the user's password with PHP 5.5's password_hash() function, results in a 60 character
    // hash string. the PASSWORD_DEFAULT constant is defined by the PHP 5.5, or if you are using
    // PHP 5.3/5.4, by the password hashing compatibility library
    $user_password_hash = password_hash($user_password, PASSWORD_DEFAULT);
    
    $sql = "SELECT * FROM benutzer WHERE id=".$user_id.";";
    
    $tabelle_benutzer_insert=true;
    $tabelle_benutzer = db_conn_and_sql($sql);
    if (sql_num_rows($tabelle_benutzer)<1) {
		$db = new db();
		$sql = "INSERT INTO benutzer (id, aktuelles_schuljahr, druckansicht, letzte_schule) VALUES (".$user_id.", ".$aktuelles_jahr.", '%Hausaufgaben
%Tests
%Struktur
%Notizen
%Ziele
|| %Zeit//%minuten//%Hefter || %Inhalt %Kommentar ||
%Hausaufgabenvergabe
%Testankuendigung', ".$my_schule.");";
		$tabelle_benutzer_insert=db_conn_and_sql($sql);
	}
	if ($tabelle_benutzer_insert) {
		$sql = "INSERT INTO user_pwd (user, gilt_seit, user_password_hash)
            VALUES(" . injaway($user_id) . ", '" . date("Y-m-d H:i:s") . "', '" . $user_password_hash . "');";
		$query_new_user_insert_pwd = db_conn_and_sql($sql);
		$messages[]='Der Nutzer kann sich nun mit dem Passwort: "'.$user_password.'" anmelden.';
	}
	else
		$errors[]='Beim Anlegen des Passworts ist ein Fehler aufgetreten';
}

if($_GET["eintragen"]=="loeschen" and userrigths("benutzerverwaltung", $my_schule)) {
	// loeschen
	//db_conn_and_sql("DELETE FROM users WHERE user_id=".injaway($_GET["id"]));
}

if (!empty($errors) or !empty($messages)) {
	echo '<div class="hinweis">';
	if (!empty($errors))
		foreach ($errors as $error)
			echo $error.'<br />';
	if (!empty($messages))
		foreach ($messages as $message)
			echo $message.'<br />';
	echo '</div>';
}

	if ($my_schule>0)
		echo '<input type="hidden" name="schule" value="'.$my_schule.'" />';
	
	$sortiert=explode("_",$_GET["sort"]);
	if ($sortiert[1]=="za")
		$reihenfolge=" DESC";
	else
		$reihenfolge="";
	switch($sortiert[0]) {
		case "kuerzel": $order="user_name"; break;
		case "name": $order="surname".$reihenfolge.", forename"; break;
		case "email": $order="user_email"; break;
		case "login": $order="last_login"; break;
		default: $order="surname, forename";
	}
	$order.=$reihenfolge;
	$angestellte=db_conn_and_sql("SELECT *
		FROM users, schule_user LEFT JOIN rollen ON schule_user.usertyp=rollen.id
		WHERE users.user_id=schule_user.user
			AND schule_user.usertyp<6
			AND schule_user.schule=".injaway($my_schule)."
		ORDER BY ".$order);
	
	function rollen_select($name, $vorauswahl) {
		$rollen=db_conn_and_sql("SELECT * FROM rollen");
		$return='<select name="'.$name.'">';
		while ($rolle=sql_fetch_assoc($rollen)) {
			if ($vorauswahl==$rolle["id"])
				$selected=' selected="selected"';
			else
				$selected='';
			$return.='<option value="'.$rolle["id"].'"'.$selected.'>'.html_umlaute($rolle["bezeichnung"]).'</option>';
		}
		$return.='</select>';
		return $return;
	}
	
	if ($_GET["bearbeiten"]=="true") {
		$bearbeiten=true;
		$pers_bearb=db_conn_and_sql("SELECT *
				FROM schule_user, users
				WHERE schule_user.user=users.user_id
					AND schule_user.schule=".$my_schule."
					AND users.user_id=".injaway($_GET["user_id"]));
		$pers_bearb=sql_fetch_assoc($pers_bearb);
	}
	else
		$bearbeiten=false;
	?>
	<form action="<?php echo $pfad.$formularziel.'&amp;eintragen='; if ($bearbeiten) echo 'bearbeiten'; else echo 'true'; ?>" method="post">
	<?php if ($bearbeiten) echo '<input type="hidden" name="person" value="'.$pers_bearb["user_id"].'" />'; ?>
	<fieldset><legend>neu / bearbeiten</legend>
	<table class="tabelle">
		<tr><th>Benutzername</th><th>Name, Vorname</th><th>E-Mail</th><th>Rolle</th><th>Passwort</th><th></th></tr>
		<tr><td><input id="login_input_username" class="login_input" type="text" pattern="[a-zA-Z0-9]{2,64}" name="user_name" required="required" placeholder="K&uuml;rzel" size="1" maxlength="7" <?php if ($bearbeiten) echo 'value="'.$pers_bearb["user_name"].'"'; ?> /></td>
			<td><?php if ($bearbeiten) echo $pers_bearb["surname"].", ".$pers_bearb["forename"];
				else { ?>
				<select name="person" onchange="document.getElementById('login_input_email').value=mails[document.getElementsByName('person')[0].value];"><option>-</option>
				<?php $personen=db_conn_and_sql("SELECT *
				FROM schule_user, users
				WHERE schule_user.user=users.user_id
					AND schule_user.schule=".$my_schule."
					AND schule_user.usertyp=0
				ORDER BY surname, forename");
				$js_mails='';
				while($person=sql_fetch_assoc($personen)) {
					echo '<option value="'.$person["user_id"].'">'.$person["surname"].', '.$person["forename"].'</option>';
					$js_mails.='mails['.$person["user_id"].']="'.$person["user_email"].'"; ';
				}
				?></select>
				<?php } ?></td>
			<td><input id="login_input_email" class="login_input" type="email" name="user_email" required="required" <?php if ($bearbeiten) echo 'value="'.$pers_bearb["user_email"].'"'; ?> /></td>
			<td><?php if ($bearbeiten) echo rollen_select("rolle",$pers_bearb["usertyp"]); else echo rollen_select("rolle",0); ?></td>
			<td><input type="checkbox" value="1" name="passwort" /> Passwort <?php if ($bearbeiten) echo 'neu setzen'; else echo 'zusenden'; ?><br />
				<input type="text" name="token_id" size="8" maxlength="12" placeholder="Yubikey-ID"<?php if ($bearbeiten) echo ' value="'.$pers_bearb["token_id"].'"'; ?></td>
			<td><input type="submit" value="<?php if ($bearbeiten) echo '&auml;ndern'; else echo 'hinzuf&uuml;gen'; ?>" /></td></tr>
	</table>
	</fieldset>
	</form>
	
	<script>mails=new Array(); <?php echo $js_mails; ?></script>
	<table class="tabelle">
		<tr><th>K&uuml;rzel <?php echo sortieren("kuerzel", $_GET["sort"], $pfad, $formularziel); ?></th><th>Name, Vorname <?php echo sortieren("name", $_GET["sort"], $pfad, $formularziel); ?></th><th>E-Mail <?php echo sortieren("email", $_GET["sort"], $pfad, $formularziel); ?></th><th>Rolle(n)</th><th>letzter Login <?php echo sortieren("login", $_GET["sort"], $pfad, $formularziel); ?></th><th>Aktionen</th></tr>
		<?php
		while ($angestellt=sql_fetch_assoc($angestellte)) {
			echo '<tr><td>'.html_umlaute($angestellt["user_name"]).'</td>
				<td>'.html_umlaute($angestellt["surname"]).', '.html_umlaute($angestellt["forename"]).'</td>
				<td>'.html_umlaute($angestellt["user_email"]).'</td>
				<td>'.$angestellt["bezeichnung"].'</td>
				<td>';
			if ($angestellt["last_login"]!="0000-00-00 00:00:00")
				echo datum_strich_zu_punkt_uebersichtlich($angestellt["last_login"], true, false).' '.substr($angestellt["last_login"], 11,5);
			echo '</td>
			<td>';
			if ($_SESSION["user_id"]==1)
				echo '<a href="'.$pfad.$formularziel.'&amp;benutzer=true&amp;user_id='.$angestellt["user_id"].'" title="user werden" class="icon"><img src="'.$pfad.'icons/pfeil_rechts.png" alt="pfeil" /></a>';
			echo '<a href="'.$pfad.$formularziel.'&amp;bearbeiten=true&amp;user_id='.$angestellt["user_id"].'" title="bearbeiten" class="icon"><img src="'.$pfad.'icons/edit.png" alt="edit" /></a>
			<a href="'.$pfad.$formularziel.'&amp;passwort=random&amp;user_id='.$angestellt["user_id"].'" onclick="if(!confirm(\'Sind Sie sicher, dass der Benutzer ein neues Passwort erhalten soll?\')) return false;" title="neues Passwort setzen" class="icon"><img src="'.$pfad.'icons/password.png" alt="pwd" /></a>
			<a href="'.$pfad.$formularziel.'&amp;entfernen=true&amp;user_id='.$angestellt["user_id"].'" onclick="if(!confirm(\'Sind Sie sicher, dass dem Benutzer das Anmelderecht entzogen werden soll?\')) return false;" title="entfernen (Anmelderecht entfernen)" class="icon"><img src="'.$pfad.'icons/delete.png" alt="del" /></a></td></tr>
			';
		}
		?>
	</table>
	
	<?php
		/*?>
		<label for="titel">Titel<em>*</em>:</label> <input type="text" name="titel" size="20" maxlength="250"<?php if($_GET["eintragen"]=="bearbeiten") echo ' value="'.html_umlaute(sql_result($bearbeiten, 0, "konferenz.titel")).'"'; ?> /><br />
		<label for="datum">Datum<em>*</em>:</label> <input type="text" class="datepicker" name="datum" size="7" maxlength="10"<?php if($_GET["eintragen"]=="bearbeiten") echo ' value="'.datum_strich_zu_punkt(sql_result($bearbeiten, 0, "konferenz.datum")).'"'; ?> />
		<label for="zeit">Zeit<em>*</em>:</label> <input type="time" name="zeit" size="5" maxlength="7"<?php if($_GET["eintragen"]=="bearbeiten") echo ' value="'.substr(sql_result($bearbeiten, 0, "konferenz.zeit"),0,5).'"'; ?> /><br />
		<label for="schule">Schule<em>*</em>:</label> <select name="schule"><?php
			$schulen=db_conn_and_sql("SELECT * FROM schule, schule_user WHERE schule_user.schule=schule.id AND schule_user.user=".$_SESSION['user_id']." ORDER BY schule_user.aktiv DESC");
			if ($_GET["auswahl"]>0 and proofuser("klasse", $_GET["auswahl"]))
				$schule_vorauswahl=sql_result(db_conn_and_sql("SELECT * FROM klasse WHERE klasse.id=".injaway($_GET["auswahl"])),0,"klasse.schule");
			else
				$schule_vorauswahl='';
			for ($i=0;$i<sql_num_rows($schulen);$i++) {
				echo '<option value="'.sql_result($schulen, $i, "schule.id").'"';
				if(($_GET["eintragen"]=="bearbeiten" and sql_result($schulen, $i, "schule.id")==sql_result($bearbeiten, 0, "konferenz.schule"))
					or ($_GET["eintragen"]!="bearbeiten" and $schule_vorauswahl==sql_result($schulen, $i, "schule.id"))) echo ' selected="selected"';
				echo ' title="'.sql_result($schulen, $i, "schule.name").'">'.sql_result($schulen, $i, "schule.kuerzel").'</option>';
			}
			?>
			</select><br />
		<label for="ort">Ort<em>*</em>:</label> <input type="text" name="ort" size="12" maxlength="250"<?php if($_GET["eintragen"]=="bearbeiten") echo ' value="'.html_umlaute(sql_result($bearbeiten, 0, "konferenz.ort")).'"'; ?> /><br />
		<label for="inhalt">Inhalt<em>*</em>:</label> <textarea name="inhalt" class="markItUp" rows="10" cols="100"><?php if($_GET["eintragen"]=="bearbeiten") echo html_umlaute(sql_result($bearbeiten, 0, "konferenz.inhalt")); ?></textarea><br />
		<input type="button" class="button" value="<?php if($_GET["eintragen"]=="bearbeiten") echo 'speichern'; else echo 'hinzuf&uuml;gen'; ?>" onclick="auswertung=new Array(new Array(0, 'datum','datum','<?php echo ($aktuelles_jahr-1); ?>-01-01','<?php echo ($aktuelles_jahr+1); ?>-12-31')); pruefe_formular(auswertung);" />
		*/ ?>
	
