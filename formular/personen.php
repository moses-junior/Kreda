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

if($_GET["eintragen"]=="true" and userrigths("personenverwaltung", $my_schule)) {
	$id=db_conn_and_sql("INSERT INTO users (title, male, forename, surname, adress, postal_code, city, user_email, tel1, tel2, tel3, birthdate, comments, user_registration_datetime)
		VALUES (".apostroph_bei_bedarf($_POST["title"]).", ".leer_NULL($_POST["male"]).", ".apostroph_bei_bedarf($_POST["forename"]).", ".apostroph_bei_bedarf($_POST["surname"]).", ".apostroph_bei_bedarf($_POST["adress"]).", ".apostroph_bei_bedarf($_POST["postal_code"]).", ".apostroph_bei_bedarf($_POST["city"]).", ".apostroph_bei_bedarf($_POST["user_email"]).", ".apostroph_bei_bedarf($_POST["tel1"]).", ".apostroph_bei_bedarf($_POST["tel2"]).", ".apostroph_bei_bedarf($_POST["tel3"]).", ".apostroph_bei_bedarf(datum_punkt_zu_strich($_POST["birthdate"])).", ".apostroph_bei_bedarf($_POST["comments"]).", '".date("Y-m-d h:i:s")."');");
	db_conn_and_sql("INSERT INTO schule_user (schule, user, aktiv, usertyp) VALUES (".$my_schule.", ".$id.", 1, 0);");
}

if($_GET["eintragen"]=="bearbeiten" and userrigths("personenverwaltung", $my_schule)) {
	db_conn_and_sql("UPDATE users SET title=".apostroph_bei_bedarf($_POST["title"]).", male=".leer_NULL($_POST["male"]).", forename=".apostroph_bei_bedarf($_POST["forename"]).", surname=".apostroph_bei_bedarf($_POST["surname"]).", adress=".apostroph_bei_bedarf($_POST["adress"]).", postal_code=".apostroph_bei_bedarf($_POST["postal_code"]).", city=".apostroph_bei_bedarf($_POST["city"]).", user_email=".apostroph_bei_bedarf($_POST["user_email"]).", tel1=".apostroph_bei_bedarf($_POST["tel1"]).", tel2=".apostroph_bei_bedarf($_POST["tel2"]).", tel3=".apostroph_bei_bedarf($_POST["tel3"]).", birthdate=".apostroph_bei_bedarf(datum_punkt_zu_strich($_POST["birthdate"])).", comments=".apostroph_bei_bedarf($_POST["comments"])." WHERE user_id=".injaway($_POST["user_id"]));
}

if($_GET["eintragen"]=="loeschen" and userrigths("personenverwaltung", $my_schule)) {
	
}

	$sortiert=explode("_",$_GET["sort"]);
	if ($sortiert[1]=="za")
		$reihenfolge=" DESC";
	else
		$reihenfolge="";
	switch($sortiert[0]) {
		case "adresse": $order="city"; break;
		case "name": $order="surname".$reihenfolge.", forename"; break;
		case "email": $order="user_email"; break;
		default: $order="surname, forename";
	}
	$order.=$reihenfolge;
	
	$personen=db_conn_and_sql("SELECT *
		FROM schule_user, users
		WHERE schule_user.user=users.user_id
			AND schule_user.schule=".$my_schule."
		ORDER BY ".$order);

	?>
<form action="<?php echo $pfad.$formularziel."&amp;eintragen="; if ($_GET["bearbeiten"]>0) echo "bearbeiten"; else echo "true"; ?>" method="post">
	<fieldset>
		<legend><?php if ($_GET["bearbeiten"]>0) echo "Person bearbeiten"; else echo "neue Person"; ?></legend>
		<?php if ($_GET["bearbeiten"]>0) {
			echo '<input type="hidden" name="user_id" value="'.$_GET["bearbeiten"].'" />';
			$meine_person=db_conn_and_sql("SELECT * FROM users WHERE user_id=".injaway($_GET["bearbeiten"]));
			$meine_person=sql_fetch_assoc($meine_person);
		}
		?>
		<table class="tabelle">
			<tr><th>Name</th><th>Adresse</th><th>Kontakt</th><th>Bemerkungen</th></tr>
			<tr>
				<td><input type="text" name="title" placeholder="Titel" size="2" maxlength="15" <?php if ($_GET["bearbeiten"]>0) echo 'value="'.$meine_person["title"].'"'; ?>/>
					<input type="radio" name="male" value="1" title="Geschlecht"<?php if($_GET["bearbeiten"]>0 and $meine_person["male"]==1) echo ' checked="checked"'; ?> /> m |
					<input type="radio" name="male" value="0"<?php if($_GET["bearbeiten"]>0 and $meine_person["male"]==0) echo ' checked="checked"'; ?> /> w<br />
					<input type="text" name="forename" placeholder="Vorname" size="5" required="required" maxlength="50" <?php if ($_GET["bearbeiten"]>0) echo 'value="'.$meine_person["forename"].'"'; ?>/><br />
					<input type="text" name="surname" placeholder="Name" size="5" required="required" maxlength="50" <?php if ($_GET["bearbeiten"]>0) echo 'value="'.$meine_person["surname"].'"'; ?>/><br />
					<input type="date" name="birthdate" placeholder="Geburtsdatum" size="7" maxlength="10" <?php if ($_GET["bearbeiten"]>0) echo 'value="'.datum_strich_zu_punkt($meine_person["birthdate"]).'"'; ?>/><br />
					</td>
				<td><input type="text" name="adress" placeholder="Stra&szlig;e HN" size="16" maxlength="50" <?php if ($_GET["bearbeiten"]>0) echo 'value="'.$meine_person["adress"].'"'; ?>/><br />
					<input type="text" name="postal_code" placeholder="PLZ" size="2" maxlength="5" <?php if ($_GET["bearbeiten"]>0) echo 'value="'.$meine_person["postal_code"].'"'; ?>/>
					<input type="text" name="city" placeholder="Wohnort" size="7" maxlength="50" <?php if ($_GET["bearbeiten"]>0) echo 'value="'.$meine_person["city"].'"'; ?>/><br />
					<input type="email" name="user_email" placeholder="E-Mail" size="20" maxlength="50" <?php if ($_GET["bearbeiten"]>0) echo 'value="'.$meine_person["user_email"].'"'; ?>/><br />
					<!--<input type="text" name="ortsteil" placeholder="Ortsteil" size="7" maxlength="50" />--></td>
				<td>
					<input type="text" name="tel1" placeholder="Telefon 1" size="10" maxlength="15" <?php if ($_GET["bearbeiten"]>0) echo 'value="'.$meine_person["tel1"].'"'; ?>/><br />
					<input type="text" name="tel2" placeholder="Telefon 2" size="10" maxlength="15" <?php if ($_GET["bearbeiten"]>0) echo 'value="'.$meine_person["tel2"].'"'; ?>/><br />
					<input type="text" name="tel3" placeholder="Telefon 3" size="10" maxlength="15" <?php if ($_GET["bearbeiten"]>0) echo 'value="'.$meine_person["tel3"].'"'; ?>/><br /></td>
				<td>
					<textarea name="comments" placeholder="Bemerkungen"><?php if ($_GET["bearbeiten"]>0) echo $meine_person["comments"]; ?></textarea><br />
					<select name="rolle1"><option>Sorgeberechtigt</option><option>Spender</option><option>Praxispartner</option><option>Betrieb</option><option>Gast</option><option>Lehrer</option><option>Verwaltung</option><option>Schulleitung</option></select><br />
					<select name="rolle2"><option>-</option><option>Sorgeberechtigt</option><option>Spender</option><option>Praxispartner</option><option>Betrieb</option><option>Gast</option><option>Lehrer</option><option>Verwaltung</option><option>Schulleitung</option></select><br />
					</td>
			</tr>
		</table>
		<?php
			if ($my_schule>0)
				echo '<input type="hidden" name="schule" value="'.$my_schule.'" />';
		?>
		<input type="submit" value="<?php if ($_GET["bearbeiten"]>0) echo "&auml;ndern"; else echo "hinzuf&uuml;gen"; ?>" />
	</fieldset>
</form>


<table class="tabelle">
	<tr><th>Name <?php echo sortieren("name", $_GET["sort"], $pfad, $formularziel); ?></th><th>Adresse <?php echo sortieren("adresse", $_GET["sort"], $pfad, $formularziel); ?></th><th>Kontakt <?php echo sortieren("email", $_GET["sort"], $pfad, $formularziel); ?></th><th>Bemerkungen</th><th>Aktionen</th></tr>
<?php
	while ($person=sql_fetch_assoc($personen)) {
		echo '	<tr><td>'.html_umlaute($person["title"]).' '.html_umlaute($person["surname"]).', '.html_umlaute($person["forename"]).'</td><td>'.html_umlaute($person["adress"]).'<br />'.html_umlaute($person["postal_code"]).' '.html_umlaute($person["city"]).'</td><td>';
		$kontakt=array();
		if (isset($person["user_email"])) $kontakt[]=html_umlaute($person["user_email"]);
		if (isset($person["tel1"])) $kontakt[]=html_umlaute($person["tel1"]);
		if (isset($person["tel2"])) $kontakt[]=html_umlaute($person["tel2"]);
		if (isset($person["tel3"])) $kontakt[]=html_umlaute($person["tel3"]);
		echo implode(" | ",$kontakt).'</td><td>';
		$bemerkung=array();
		if (isset($person["birthdate"])) $bemerkung[]=html_umlaute(datum_strich_zu_punkt($person["birthdate"]));
		if (isset($person["comments"])) $bemerkung[]=html_umlaute($person["comments"]);
		switch ($person["usertyp"]) {
			case 2: $bemerkung[]="Lehrer"; break;
			case 4: $bemerkung[]="Schulleiter"; break;
			case 5: $bemerkung[]="Verwaltung"; break;
			case 7: $bemerkung[]="Sorgeberechtigt"; break;
		}
		echo implode("<br />",$bemerkung).'</td><td><a href="'.$pfad.$formularziel.'&amp;bearbeiten='.$person["user_id"].'" class="icon"><img src="'.$pfad.'icons/edit.png" alt="bearbeiten" /></a></td></tr>'."\n";
	}
?>
</table>
