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

session_start();
$pfad="../";
include $pfad."funktionen.php";
include $pfad."login/libraries/password_compatibility_library.php";

$rueckmeldung='';

if (!empty($_POST['user_password_new'])) {
	if ($_POST['user_password_new'] !== $_POST['user_password_repeat']) {
		$errors[] = "Passwort und Passwortbest&auml;tigung stimmen nicht &uuml;berein";
	} elseif (strlen($_POST['user_password_new']) < 6) {
		$errors[] = "Passwort muss aus mindestens 6 Zeichen bestehen";
	}
	
	$old_pwd=db_conn_and_sql("SELECT user_password_hash, gilt_seit FROM user_pwd WHERE user=".$_SESSION["user_id"]." ORDER BY gilt_seit DESC");
	$old_pwd=sql_fetch_assoc($old_pwd);
	if (!password_verify($_POST['old_password'], $old_pwd["user_password_hash"]))
		$errors[]="altes Passwort nicht korrekt";
	if (empty($errors)) {
		$user_password_hash = password_hash($_POST['user_password_new'], PASSWORD_DEFAULT);
		db_conn_and_sql("INSERT INTO user_pwd (user, user_password_hash, gilt_seit) VALUES (".$_SESSION["user_id"].", '".$user_password_hash."', '".date("Y-m-d H:i:s")."')");
		$rueckmeldung='&pwd_changed=1';
		// TODO abgelaufen aktualisieren und neuen INSERT INTO-Eintrag
	}
	else
		foreach($errors as $error)
			echo $error."<br />";
			
}

db_conn_and_sql("UPDATE `benutzer` SET
			`aktuelles_schuljahr`=".apostroph_bei_bedarf($_POST["schuljahr"]).",
			`merkhefter`=".leer_NULL($_POST["hefteransicht"]).",
			`druckansicht`=".apostroph_bei_bedarf($_POST["druckansicht"]).",
			`ansicht_2`=".apostroph_bei_bedarf($_POST["ansicht_2"]).",
			`lb_faktor`=".punkt_statt_komma_zahl($_POST["grobplanungsfaktor"]).",
			`zensurenpunkte`=".leer_NULL($_POST["zensurenpunkte"]).",
			`zensurenkommentare`=".leer_NULL($_POST["zensurenkommentare"]).",
			`zensuren_unt_ber`=".leer_NULL($_POST["zensuren_unt_ber"]).",
			`zensuren_nicht_zaehlen`=".leer_NULL($_POST["zensuren_nicht_zaehlen"]).",
			`dienstberatungen`=".leer_NULL($_POST["dienstberatungen"]).",
			`schuljahresplanung`=".leer_NULL($_POST["schuljahresplanung"]).",
			`statistiken`=".leer_NULL($_POST["statistiken"]).",
			`ustd_planung`=".leer_NULL($_POST["ustd_planung"]).",
			`sitzplan`=".leer_NULL($_POST["sitzplan"])."
			WHERE `id`=".$_SESSION['user_id']);
db_conn_and_sql("UPDATE `users` SET
			`city`=".apostroph_bei_bedarf($_POST["city"]).",
			`adress`=".apostroph_bei_bedarf($_POST["adress"]).",
			`postal_code`=".apostroph_bei_bedarf($_POST["postal_code"]).",
			`tel1`=".apostroph_bei_bedarf($_POST["tel1"]).",
			`tel2`=".apostroph_bei_bedarf($_POST["tel2"])."
			WHERE `user_id`=".$_SESSION['user_id']);

if (empty($errors)) {
	header("Location: ../index.php?tab=einstellungen&auswahl=allgemein".$rueckmeldung);
	exit;
}
else
	echo '<br /><a href="javascipt:window.history.back();">zur&uuml;ck</a>';
?>
