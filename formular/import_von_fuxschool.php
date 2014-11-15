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

$pfad="../";
header('Content-Type: text/html; charset=ISO-8859-15');

//$csvFile  = $pfad.'schueler.csv';
$hasTitle = true;
session_start();
include ($pfad."funktionen.php");
$user=new user();
$schule=$user->my["letzte_schule"];

?>
<!DOCTYPE HTML>

<html>
   <head>
	<title>Sch&uuml;ler-Import von FuxSchool</title>
	<meta name="author" content="automatisch generiert durch Kreda" />
	<meta name="robots" content="noindex, nofollow" />
	<meta http-equiv="Content-Style-Type" content="text/css" />
	<link rel="shortcut icon" href="<?php echo $pfad; ?>look/favicon.ico" type="image/x-icon" />
	<link rel="stylesheet" type="text/css" href="<?php echo $pfad; ?>look/format.css" />
	<meta http-equiv="Content-Script-Type" content="text/javascript" />
	<meta http-equiv="Content-Style-Type" content="text/css" />
	<meta http-equiv="content-type" content="application/xhtml+xml; charset=ISO-8859-15" />
</head>
<body>
<?php
$import_vorbereitungen=false;
$importiere_von_fuxschool=false;
// FuxSchool-Import
if ($_GET["eintragen"]=="import") {
	if(empty($_FILES['import_file']['name']))
		$err[] = "Eine Datei muss ausgew&auml;hlt werden";

	if(empty($err)) {
		$tempname = $_FILES['import_file']['tmp_name'];
		$name = $_FILES['import_file']['name'];
		
		$type = $_FILES['import_file']['type'];
		$size = $_FILES['import_file']['size'];
		
		$csvFile = $_FILES['import_file']['tmp_name'];
		
		if ($_GET["eintragen"]=="import")
			$import_vorbereitungen=true;
		
		// TODO Datei zwischenspeichern
		copy("$tempname", $pfad."daten/importdatei_".$schule.".csv");
	}
	else
		foreach($err as $error)
			echo '<br />'.$error;
}
else {
	if ($_GET["eintragen"]=="import2") {
		$csvFile=$pfad."daten/importdatei_".$schule.".csv";
		$importiere_von_fuxschool=true;
	}
	else {
?>
	<form action="import_von_fuxschool.php?eintragen=import" method="post" enctype="multipart/form-data" accept-charset="ISO-8859-15">
		<fieldset><legend>Importiere Sch&uuml;ler aus FuxSchool-CSV-Datei</legend>
			<label for="import_file">Datei<em>*</em>:</label>
			<input type="file" name="import_file" size="5" /> <input type="submit" value="importieren" />
		</fieldset>
	</form>
<?php
}
}
// TODO: Eltern
//$meine_klassen=array();
$klassen_result=db_conn_and_sql("SELECT id, einschuljahr, endung FROM klasse WHERE schule=".$schule." AND einschuljahr>".($aktuelles_jahr-12)." ORDER BY einschuljahr DESC, endung");

if ($import_vorbereitungen) {
	$select_inhalt='<option value="0">-</option>';
	while($klasse=sql_fetch_assoc($klassen_result))
		$select_inhalt.='<option value="'.$klasse["id"].'">'.($aktuelles_jahr-$klasse["einschuljahr"]+1).' / '.$klasse["endung"].'</option>';
	
	$handle = fopen($csvFile, "r");
	$start = 0;
	$klassen_array=array();
	while (($data = fgetcsv($handle, 2000, ";")) !== FALSE) {
		if ($start == 0 && $hasTitle == true) {
			for ($x=0; $x < count($data); $x++) {
				if (iconv("ISO-8859-15", "ASCII//TRANSLIT", $data[$x])=="Schueler_Klasse")
					$position_klassenspalte=$x;
			}
		}
		else
			if (!empty($data[$position_klassenspalte]))
				$klassen_array[trim($data[$position_klassenspalte])]=array("name"=>trim($data[$position_klassenspalte]));
		$start++;
	}
	echo 'Falls die zuzuordnenden Klassen fehlen, sollten Sie diese zun&auml;chst unter "Klassen" - "&Uuml;bersicht" anlegen.<br />
		Sorgeberechtigte werden erst bei bestehenden Sch&uuml;lern importiert. Importieren Sie also zun&auml;chst die Sch&uuml;ler und in einem zweiten Import-Vorgang die Sorgeberechtigten.<br />
		<form action="import_von_fuxschool.php?eintragen=import2" method="post" enctype="multipart/form-data" accept-charset="ISO-8859-15">';
	$i=0;
	foreach($klassen_array as $kl) {
		echo '<label for="'.str_replace(' ','',$kl["name"]).'">'.htmlspecialchars($kl["name"]).':</label> <select name="'.str_replace(' ','',$kl["name"]).'">'.$select_inhalt.'</select><br />';
		$i++;
	}
	$welche_daten=array("Schueler_Name",
		"Schueler_Vorname",
		"Schueler_Klasse",
		"Schueler_Geschlecht",
		"Schueler_Klasse",
		"Schueler_Plz",
		"Schueler_Strasse",
		"Schueler_Wohnort",
		"Schueler_Geburtsdatum",
		"Schueler_Geburtsort",
		"Schueler_allgemeine_Bemerkungen",
		"Schueler_Krankenkasse",
		"Schueler_Staatsangehoerigkeit",
		"Schueler_Konfession",
		"Schueler_Aufnahme_am",
		"Schueler_Abgang_am",
		"Schueler_abgebende_Schule_ID",
		"Schueler_aufnehmende_Schule_ID",
		"Schueler_Schliessfach_Schluesselnummer",
		"Schueler_Schliessfachnummer",
		"Schueler_Fotoerlaubnis",
		//"Schueler_Bankname",
		//"Schueler_Bankleitzahl",
		//"Schueler_Kontonummer",
		//"Schueler_Kontoinhaber",
		//"Schueler_Iban",
		//"Schueler_Bic",
		"Schueler_Benutzername",
		"Schueler_Passwort",
		"Sorgeberechtigter_Postempfaenger",
		"Sorgeberechtigter1_Name",
		"Sorgeberechtigter1_Vorname",
		"Sorgeberechtigter1_Strasse",
		"Sorgeberechtigter1_Plz",
		"Sorgeberechtigter1_Wohnort",
		"Sorgeberechtigter1_Geschlecht",
		"Sorgeberechtigter1_Titel",
		"Sorgeberechtigter2_Name",
		"Sorgeberechtigter2_Vorname",
		"Sorgeberechtigter2_Strasse",
		"Sorgeberechtigter2_Plz",
		"Sorgeberechtigter2_Wohnort",
		"Sorgeberechtigter2_Geschlecht",
		"Sorgeberechtigter2_Titel",
		"Sorgeberechtigter3_Name",
		"Sorgeberechtigter3_Vorname",
		"Sorgeberechtigter3_Strasse",
		"Sorgeberechtigter3_Plz",
		"Sorgeberechtigter3_Wohnort",
		"Sorgeberechtigter3_Geschlecht",
		"Sorgeberechtigter3_Titel",
		"Kommunikation_Email",
		"Kommunikation_Telefon1",
		"Kommunikation_Telefon2",
		"Kommunikation_Telefon3",
		"Kommunikation_Telefon4",
		"Kommunikation_Telefon5",
		"Kommunikation_Telefon6"
		);
	foreach($welche_daten as $beruecksichtigen)
		echo '<label for="ber_'.$beruecksichtigen.'">'.$beruecksichtigen.'</label> <input type="checkbox" name="ber_'.$beruecksichtigen.'" value="1" checked="checked" /><br />';
	echo '<input type="submit" value="eintragen" /></form>';
	
}

if ($importiere_von_fuxschool) {
	if (file_exists($csvFile)) {
		$handle = fopen($csvFile, "r");
		$start = 0;
		$headers = array();
		$neue_schueler = array();
		$aktualisierte_schueler = array();
		$nicht_aktualisierte_schueler = array();
		$nicht_importierte_schueler=array();
		
		while (($data = fgetcsv($handle, 2000, ";")) !== FALSE) {
			$s_nr=0;
			$klasse_zugeordnet=false;
			$sql_update=array();
			$sql_insert_headers=array();
			$sql_insert_values =array();
			$sql_update_sb[0]=array();
			$sql_insert_headers_sb[0]=array();
			$sql_insert_values_sb[0] =array();
			$sql_update_sb[1]=array();
			$sql_insert_headers_sb[1]=array();
			$sql_insert_values_sb[1] =array();
			
			for ($x=0; $x < count($data); $x++) {
				if ($start == 0 && $hasTitle == true) {
					//echo $data[$x].' - ' . "\n";
					$headers[$x]=iconv("ISO-8859-15", "ASCII//TRANSLIT", $data[$x]);
				}
				else {
					// klasse, Stammgruppe, Betreuer, Staatsangehörigkeit, Fotoerlaubnis, Landkreis, Bundesland
					// konfession, status, einschulung am, einschulungsart, einschulungsart_zusatz, aufnahme_am
					// abgang_am, Grund_des_Abgangs, Vorbildung, Schulpflicht_beginnt_am, Schulpflicht_endet_am,
					// abgebende_Schule_ID, aufnehmende_Schule_ID, Schüler_Schulabschluss,
					// Schüler_Wiederholungen_Hinweise - Schüler_Wiederholungen - Schüler_Schließfach_Schlüsselnummer - Schüler_Schließfachnummer
					// 
					switch ($headers[$x]) {
						case "Schueler_Schuelernummer":			$s_nr=$data[$x]; break;
						case "Schueler_Name":					if ($_POST["ber_Schueler_Name"]==1) { $sql_insert_headers[]="name"; $sql_insert_values[]=apostroph_bei_bedarf($data[$x]); } $schueler_name=$data[$x]; break;
						case "Schueler_Vorname":				if ($_POST["ber_Schueler_Vorname"]==1) { $sql_insert_headers[]="vorname"; $sql_insert_values[]=apostroph_bei_bedarf($data[$x]); } $schueler_name.=", ".$data[$x]; break;
						case "Schueler_Klasse":					if ($_POST["ber_Schueler_Klasse"]==1) { $k_id=$_POST[str_replace(' ','',$data[$x])]; if (isset($k_id) and $k_id!=0) { $sql_insert_headers[]="klasse"; $sql_insert_values[]=injaway($k_id); $klasse_zugeordnet=true; } } break;
						case "Schueler_Geschlecht":				if ($_POST["ber_Schueler_Geschlecht"]==1) { $sql_insert_headers[]="maennlich"; if ($data[$x]=="w") $sql_insert_values[]=0; else $sql_insert_values[]=1; } break;
						case "Schueler_Klasse":					if ($_POST["ber_Schueler_Klasse"]==1) { $schueler_name.=", ".$data[$x]; } break;
						case "Schueler_Strasse":				if ($_POST["ber_Schueler_Strasse"]==1) { $sql_insert_headers[]="strasse"; $sql_insert_values[]=apostroph_bei_bedarf($data[$x]); } break;
						case "Schueler_Plz":					if ($_POST["ber_Schueler_Plz"]==1) { $sql_insert_headers[]="plz"; $sql_insert_values[]=apostroph_bei_bedarf($data[$x]); } break;
						case "Schueler_Wohnort":				if ($_POST["ber_Schueler_Wohnort"]==1) {
																	if ($headers[$x+1]=="Schueler_Ortsteil" and !empty($data[$x+1]))
																		$ort=$data[$x]." OT ".$data[$x+1];
																	else
																		$ort=$data[$x];
																	$sql_insert_headers[]="ort"; $sql_insert_values[]=apostroph_bei_bedarf($ort); } break;
						case "Schueler_Geburtsdatum":			if ($_POST["ber_Schueler_Geburtsdatum"]==1) { $sql_insert_headers[]="geburtstag"; $sql_insert_values[]=apostroph_bei_bedarf(datum_punkt_zu_strich($data[$x])); } break;
						case "Schueler_Geburtsort":				if ($_POST["ber_Schueler_Geburtsort"]==1) { $sql_insert_headers[]="geburtsort"; $sql_insert_values[]=apostroph_bei_bedarf($data[$x]); } break;
						case "Schueler_allgemeine_Bemerkungen": if ($_POST["ber_Schueler_allgemeine_Bemerkungen"]==1) { $sql_insert_headers[]="bemerkungen"; $sql_insert_values[]=apostroph_bei_bedarf($data[$x]); } break;
						case "Schueler_Krankenkasse":			if ($_POST["ber_Schueler_Krankenkasse"]==1) { $sql_insert_headers[]="krankenkasse"; $sql_insert_values[]=apostroph_bei_bedarf($data[$x]); } break;
						case "Schueler_Staatsangehoerigkeit":	if ($_POST["ber_Schueler_Staatsangehoerigkeit"]==1) { $sql_insert_headers[]="staatsangehoerigkeit"; $sql_insert_values[]=apostroph_bei_bedarf($data[$x]); } break;
						case "Schueler_Konfession":				if ($_POST["ber_Schueler_Konfession"]==1) { $sql_insert_headers[]="konfession"; $sql_insert_values[]=apostroph_bei_bedarf($data[$x]); } break;
						case "Schueler_Aufnahme_am":			if ($_POST["ber_Schueler_Aufnahme_am"]==1) { $sql_insert_headers[]="aufnahme_am"; $sql_insert_values[]=apostroph_bei_bedarf($data[$x]); } break;
						case "Schueler_Abgang_am":				if ($_POST["ber_Schueler_Abgang_am"]==1) { $sql_insert_headers[]="abgang_am"; $sql_insert_values[]=apostroph_bei_bedarf($data[$x]); } break;
						case "Schueler_abgebende_Schule_ID":	if ($_POST["ber_Schueler_abgebende_Schule_ID"]==1) { $sql_insert_headers[]="abgebende_schule"; $sql_insert_values[]=apostroph_bei_bedarf($data[$x]); } break;
						case "Schueler_aufnehmende_Schule_ID":	if ($_POST["ber_Schueler_aufnehmende_Schule_ID"]==1) { $sql_insert_headers[]="aufnehmende_schule"; $sql_insert_values[]=apostroph_bei_bedarf($data[$x]); } break;
						case "Schueler_Schliessfach_Schluesselnummer": if ($_POST["ber_Schueler_Schliessfach_Schluesselnummer"]==1) { $sql_insert_headers[]="schliessfach_schl_nr"; $sql_insert_values[]=apostroph_bei_bedarf($data[$x]); } break;
						case "Schueler_Schliessfachnummer":		if ($_POST["ber_Schueler_Schliessfachnummer"]==1) { $sql_insert_headers[]="schliessfachnummer"; $sql_insert_values[]=apostroph_bei_bedarf($data[$x]); } break;
						case "Schueler_Fotoerlaubnis":			if ($_POST["ber_Schueler_Fotoerlaubnis"]==1) { $sql_insert_headers[]="fotoerlaubnis"; $sql_insert_values[]=apostroph_bei_bedarf($data[$x]); } break;
						case "Sorgeberechtigter1_Name":			if ($_POST["ber_Sorgeberechtigter1_Name"]==1) { $sql_insert_headers_sb[0][]="surname"; $sql_insert_values_sb[0][]=apostroph_bei_bedarf($data[$x]); } break;
						case "Sorgeberechtigter1_Vorname":		if ($_POST["ber_Sorgeberechtigter1_Vorname"]==1) { $sql_insert_headers_sb[0][]="forename"; $sql_insert_values_sb[0][]=apostroph_bei_bedarf($data[$x]); } break;
						case "Sorgeberechtigter1_Strasse":		if ($_POST["ber_Sorgeberechtigter1_Strasse"]==1) { $sql_insert_headers_sb[0][]="adress"; $sql_insert_values_sb[0][]=apostroph_bei_bedarf($data[$x]); } break;
						case "Sorgeberechtigter1_Plz":			if ($_POST["ber_Sorgeberechtigter1_Plz"]==1) { $sql_insert_headers_sb[0][]="postal_code"; $sql_insert_values_sb[0][]=apostroph_bei_bedarf($data[$x]); } break;
						case "Sorgeberechtigter1_Wohnort":		if ($_POST["ber_Sorgeberechtigter1_Wohnort"]==1) { 
																	if ($headers[$x+1]=="Sorgeberechtigter1_Ortsteil" and !empty($data[$x+1]))
																		$ort=$data[$x]." OT ".$data[$x+1];
																	else
																		$ort=$data[$x];
																	$sql_insert_headers_sb[0][]="city"; $sql_insert_values_sb[0][]=apostroph_bei_bedarf($ort); } break;
						case "Sorgeberechtigter1_Geschlecht":	if ($_POST["ber_Sorgeberechtigter1_Geschlecht"]==1) { $sql_insert_headers_sb[0][]="male"; if ($data[$x]=="w") $sql_insert_values_sb[0][]=0; else $sql_insert_values_sb[0][]=1; } break;
						case "Sorgeberechtigter1_Titel":		if ($_POST["ber_Sorgeberechtigter1_Titel"]==1) { $sql_insert_headers_sb[0][]="title"; $sql_insert_values_sb[0][]=apostroph_bei_bedarf($data[$x]); } break;
						case "Sorgeberechtigter2_Name":			if ($_POST["ber_Sorgeberechtigter2_Name"]==1) { $sql_insert_headers_sb[1][]="surname"; $sql_insert_values_sb[1][]=apostroph_bei_bedarf($data[$x]); } break;
						case "Sorgeberechtigter2_Vorname":		if ($_POST["ber_Sorgeberechtigter2_Vorname"]==1) { $sql_insert_headers_sb[1][]="forename"; $sql_insert_values_sb[1][]=apostroph_bei_bedarf($data[$x]); } break;
						case "Sorgeberechtigter2_Strasse":		if ($_POST["ber_Sorgeberechtigter2_Strasse"]==1) { $sql_insert_headers_sb[1][]="adress"; $sql_insert_values_sb[1][]=apostroph_bei_bedarf($data[$x]); } break;
						case "Sorgeberechtigter2_Plz":			if ($_POST["ber_Sorgeberechtigter2_Plz"]==1) { $sql_insert_headers_sb[1][]="postal_code"; $sql_insert_values_sb[1][]=apostroph_bei_bedarf($data[$x]); } break;
						case "Sorgeberechtigter2_Wohnort":		if ($_POST["ber_Sorgeberechtigter2_Wohnort"]==1) { 
																	if ($headers[$x+1]=="Sorgeberechtigter2_Ortsteil" and !empty($data[$x+1]))
																		$ort=$data[$x]." OT ".$data[$x+1];
																	else
																		$ort=$data[$x];
																	$sql_insert_headers_sb[1][]="city"; $sql_insert_values_sb[1][]=apostroph_bei_bedarf($ort); } break;
						case "Sorgeberechtigter2_Geschlecht":	if ($_POST["ber_Sorgeberechtigter2_Geschlecht"]==1) { $sql_insert_headers_sb[1][]="male"; if ($data[$x]=="w") $sql_insert_values_sb[1][]=0; else $sql_insert_values_sb[1][]=1; } break;
						case "Sorgeberechtigter2_Titel":		if ($_POST["ber_Sorgeberechtigter2_Titel"]==1) { $sql_insert_headers_sb[1][]="title"; $sql_insert_values_sb[1][]=apostroph_bei_bedarf($data[$x]); } break;
						case "Sorgeberechtigter3_Name":			if ($_POST["ber_Sorgeberechtigter3_Name"]==1) { $sql_insert_headers_sb[2][]="surname"; $sql_insert_values_sb[2][]=apostroph_bei_bedarf($data[$x]); } break;
						case "Sorgeberechtigter3_Vorname":		if ($_POST["ber_Sorgeberechtigter3_Vorname"]==1) { $sql_insert_headers_sb[2][]="forename"; $sql_insert_values_sb[2][]=apostroph_bei_bedarf($data[$x]); } break;
						case "Sorgeberechtigter3_Strasse":		if ($_POST["ber_Sorgeberechtigter3_Strasse"]==1) { $sql_insert_headers_sb[2][]="adress"; $sql_insert_values_sb[2][]=apostroph_bei_bedarf($data[$x]); } break;
						case "Sorgeberechtigter3_Plz":			if ($_POST["ber_Sorgeberechtigter3_Plz"]==1) { $sql_insert_headers_sb[2][]="postal_code"; $sql_insert_values_sb[2][]=apostroph_bei_bedarf($data[$x]); } break;
						case "Sorgeberechtigter3_Wohnort":		if ($_POST["ber_Sorgeberechtigter3_Wohnort"]==1) { 
																	if ($headers[$x+1]=="Sorgeberechtigter3_Ortsteil" and !empty($data[$x+1]))
																		$ort=$data[$x]." OT ".$data[$x+1];
																	else
																		$ort=$data[$x];
																	$sql_insert_headers_sb[2][]="city"; $sql_insert_values_sb[2][]=apostroph_bei_bedarf($ort); } break;
						case "Sorgeberechtigter3_Geschlecht":	if ($_POST["ber_Sorgeberechtigter3_Geschlecht"]==1) { $sql_insert_headers_sb[2][]="male"; if ($data[$x]=="w") $sql_insert_values_sb[2][]=0; else $sql_insert_values_sb[2][]=1; } break;
						case "Sorgeberechtigter3_Titel":		if ($_POST["ber_Sorgeberechtigter3_Titel"]==1) { $sql_insert_headers_sb[2][]="title"; $sql_insert_values_sb[2][]=apostroph_bei_bedarf($data[$x]); } break;
						//case "Schueler_Bankname":				if ($_POST["ber_Schueler_Bankname"]==1) { $sql_insert_headers[]="bank"; $sql_insert_values[]=apostroph_bei_bedarf($data[$x]); } break;
						//case "Schueler_Bankleitzahl":			if ($_POST["ber_Schueler_Bankleitzahl"]==1) { $sql_insert_headers[]="blz"; $sql_insert_values[]=apostroph_bei_bedarf($data[$x]); } break;
						//case "Schueler_Kontonummer":			if ($_POST["ber_Schueler_Kontonummer"]==1) { $sql_insert_headers[]="kto_nr"; $sql_insert_values[]=apostroph_bei_bedarf($data[$x]); } break;
						//case "Schueler_Kontoinhaber":			if ($_POST["ber_Schueler_Kontoinhaber"]==1) { $sql_insert_headers[]="kto_inhaber"; $sql_insert_values[]=apostroph_bei_bedarf($data[$x]); } break;
						//case "Schueler_Iban":					if ($_POST["ber_Schueler_Iban"]==1) { $sql_insert_headers[]="iban"; $sql_insert_values[]=apostroph_bei_bedarf($data[$x]); } break;
						//case "Schueler_Bic":					if ($_POST["ber_Schueler_Bic"]==1) { $sql_insert_headers[]="bic"; $sql_insert_values[]=apostroph_bei_bedarf($data[$x]); } break;
						case "Schueler_Benutzername":			if ($_POST["ber_Schueler_Benutzername"]==1) { $sql_insert_headers[]="username"; $sql_insert_values[]=apostroph_bei_bedarf($data[$x]); } break;
						case "Schueler_Passwort":				if ($_POST["ber_Schueler_Passwort"]==1) { $sql_insert_headers[]="passwort"; $sql_insert_values[]=apostroph_bei_bedarf($data[$x]); } break;
						case "Kommunikation_Email":				if ($_POST["ber_Kommunikation_Email"]==1) { $sql_insert_headers_sb[0][]="user_email"; $sql_insert_values_sb[0][]=apostroph_bei_bedarf($data[$x]); } break;
						case "Kommunikation_Telefon1":			if ($_POST["ber_Kommunikation_Telefon1"]==1) { $sql_insert_headers_sb[0][]="tel1"; $sql_insert_values_sb[0][]=apostroph_bei_bedarf($data[$x]); } break;
						case "Kommunikation_Telefon2":			if ($_POST["ber_Kommunikation_Telefon2"]==1) { $sql_insert_headers_sb[0][]="tel2"; $sql_insert_values_sb[0][]=apostroph_bei_bedarf($data[$x]); } break;
						case "Kommunikation_Telefon3":			if ($_POST["ber_Kommunikation_Telefon3"]==1) { $sql_insert_headers_sb[0][]="tel3"; $sql_insert_values_sb[0][]=apostroph_bei_bedarf($data[$x]); } break;
						case "Kommunikation_Telefon4":			if ($_POST["ber_Kommunikation_Telefon4"]==1) { $sql_insert_headers_sb[1][]="tel1"; $sql_insert_values_sb[1][]=apostroph_bei_bedarf($data[$x]); } break;
						case "Kommunikation_Telefon5":			if ($_POST["ber_Kommunikation_Telefon5"]==1) { $sql_insert_headers_sb[1][]="tel2"; $sql_insert_values_sb[1][]=apostroph_bei_bedarf($data[$x]); } break;
						case "Kommunikation_Telefon6":			if ($_POST["ber_Kommunikation_Telefon6"]==1) { $sql_insert_headers_sb[1][]="tel3"; $sql_insert_values_sb[1][]=apostroph_bei_bedarf($data[$x]); } break;
						
						case "Sorgeberechtigter_Postempfaenger":if ($_POST["ber_"]==1) { $sql_insert_value_post=apostroph_bei_bedarf($data[$x]); } break;
						
						default: echo ""; //$data[$x]. "; \n";
					}
				}
			}
			if ($s_nr>0) {
				$schueler_id=db_conn_and_sql("SELECT schueler.id FROM schueler, klasse WHERE schueler.klasse=klasse.id AND klasse.schule=".$schule." AND schueler.number=".$s_nr);
				// gibt es den Schueler in der Datenbank? -> aktualisiere Daten
				if (sql_num_rows($schueler_id)==1) {
					if (count($sql_insert_headers)>0) {
						$aktualisierte_schueler[]=$schueler_name;
						for ($i=0;$i<count($sql_insert_headers);$i++)
							$sql_update[]=$sql_insert_headers[$i]."=".$sql_insert_values[$i];
						db_conn_and_sql("UPDATE schueler SET ".implode(", ", $sql_update)." WHERE number=".$s_nr." LIMIT 1");
						//echo "UPDATE schueler SET ".implode(", ", $sql_update)." WHERE number=".$s_nr." LIMIT 1<br /><br />";
						
					}
					
					// Sorgeberechtigte anfuegen
					$schueler_id=sql_fetch_assoc($schueler_id);
					$schueler_id=$schueler_id["id"];
					$elternteil_existiert=db_conn_and_sql("SELECT * FROM eltern_schueler LEFT JOIN users ON users.user_id=eltern_schueler.user WHERE eltern_schueler.schueler=".$schueler_id);
					// TODO: elternteil_aktualisieren; wird hier nur neu angelegt
					if (sql_num_rows($elternteil_existiert)==0) {
						for($e=0; $e<=1; $e++) {
							if ($sql_insert_value_post==$e)
								$post=1;
							else
								$post=0;
							$suchfeld=array("surname"=>"", "forename"=>"", "adress"=>"", "postal_code"=>"", "city"=>"");
							for($k=0;$k<count($sql_insert_headers_sb[$e]); $k++) {
								$sql_insert_values_sb[$e][$k]=str_replace("Str.", "Straße", str_replace("str.", "straße", trim($sql_insert_values_sb[$e][$k])));
								switch ($sql_insert_headers_sb[$e][$k]) {
									case "surname": $suchfeld["surname"]=trim($sql_insert_values_sb[$e][$k]); break;
									case "forename": $suchfeld["forename"]=trim($sql_insert_values_sb[$e][$k]); break;
									case "adress": $suchfeld["adress"]=$sql_insert_values_sb[$e][$k]; break;
									case "postal_code": $suchfeld["postal_code"]=trim($sql_insert_values_sb[$e][$k]); break;
									case "city": $suchfeld["city"]=trim($sql_insert_values_sb[$e][$k]); break;
								}
							}
							$suchfeld["adress"]=str_replace("str.", "straße", $suchfeld["adress"])." OR adress=".str_replace("straße", "str.", $suchfeld["adress"])." OR adress=".str_replace("Str.", "Straße", $suchfeld["adress"])." OR adress=".str_replace("Straße", "Str.", $suchfeld["adress"]);
							$eltern_existiert_id=db_conn_and_sql("SELECT user_id
								FROM users
								WHERE surname=".$suchfeld["surname"]."
									AND forename=".$suchfeld["forename"]."
									AND (adress=".$suchfeld["adress"].")
									AND postal_code=".$suchfeld["postal_code"]."
									AND city=".$suchfeld["city"]);
							// Falls der Sorgeberechtigte schon bei einem anderen Schueler eingetragen ist (Vor und Nachname, sowie adresse stimmen ueberein)
							if (sql_num_rows($eltern_existiert_id)>0) {
								$eltern_existiert_id=sql_fetch_assoc($eltern_existiert_id);
								db_conn_and_sql("INSERT INTO eltern_schueler (user, schueler, sorgeberechtigt, postempfaenger) VALUES (".$eltern_existiert_id["user_id"].", ".$schueler_id.", 1, ".$post.");");
								//db_conn_and_sql("INSERT INTO schule_user (schule, user, usertyp) VALUES (".$schule.", ".$eltern_existiert_id["user_id"].", 7);");
							} // Falls der Sorgeberechtigte neu angelegt werden muss
							else if (trim($sql_insert_values_sb[$e][0]).trim($sql_insert_values_sb[$e][1])!="") {
								$user_id=db_conn_and_sql("INSERT INTO users (".implode(", ", $sql_insert_headers_sb[$e]).") VALUES (".implode(", ", $sql_insert_values_sb[$e]).");");
								db_conn_and_sql("INSERT INTO eltern_schueler (user, schueler, sorgeberechtigt, postempfaenger) VALUES (".$user_id.", ".$schueler_id.", 1, ".$post.");");
								db_conn_and_sql("INSERT INTO schule_user (schule, user, usertyp) VALUES (".$schule.", ".$user_id.", 7);");
							}
						}
					}
					
				} // sonst Schueler neu anlegen
				else {
					// TODO: hier noch ohne Sorgeberechtigte
					if ($klasse_zugeordnet) {
						db_conn_and_sql("INSERT INTO schueler (number, ".implode(", ", $sql_insert_headers).") VALUES (".$s_nr.", ".implode(", ", $sql_insert_values).");");
						$neue_schueler[]=$schueler_name." (".$s_nr.")";
					}
					else
						$nicht_importierte_schueler[]=$schueler_name." (".$s_nr.")";
				}
			}
			else
				$nicht_aktualisierte_schueler[]=$schueler_name." (FuxSchool-SNr: ".$s_nr.")";
			
			$start++;
			//echo '<br /><br />' . "\n";
		
		}
	
		fclose($handle);

	} else {
		exit("Datei $csvFile kann nicht ge&ouml;ffnet werden.");
	}
}

if ($_GET["eintragen"]=="import2") {
	unlink($pfad."daten/importdatei_".$schule.".csv");
	
	echo "<p>Aktualisierte Sch&uuml;ler (".count($aktualisierte_schueler)."):<br />".implode("<br />", $aktualisierte_schueler)."</p>";
	echo "<p>Neu importierte Sch&uuml;ler (".count($neue_schueler)."):<br />".implode("<br />", $neue_schueler)."</p>";
	echo "<p>Nicht aktualisierte Sch&uuml;ler (".count($nicht_aktualisierte_schueler)."):<br />".implode("<br />", $nicht_aktualisierte_schueler)."</p>";
	echo "<p>Nicht importierte Sch&uuml;ler wegen fehlender Klassenzuordnung (".count($nicht_importierte_schueler)."):<br />".implode("<br />", $nicht_importierte_schueler)."</p>";
	
	echo 'zur&uuml;ck zur <a href="'.$pfad.'index.php?tab=schueler">Sch&uuml;lerverwaltung</a>';
}
?>
</body>
</html>
