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
$schule=$user->my["letzte_schule"];

if (userrigths("schueler_verwaltung", $schule)!=2)
	die("Sie haben nicht die erforderlichen Rechte, um Sch&uuml;ler zu bearbeiten.");

if ($_GET["eintragen"]=="true") {
	// if (proofuser("schueler", $_POST["schueler_".$i]))
	if (!empty($_POST['passwort'])) {
		$passwort=", passwort=".apostroph_bei_bedarf(md5($_POST['passwort']));
	}
	else
		$passwort="";
	
		db_conn_and_sql("UPDATE schueler SET
			name=".apostroph_bei_bedarf($_POST['name']).",
			vorname=".apostroph_bei_bedarf($_POST['vorname']).",
			geburtstag=".apostroph_bei_bedarf(datum_punkt_zu_strich($_POST['geburtstag'])).",
			strasse=".apostroph_bei_bedarf($_POST['strasse']).",
			ort=".apostroph_bei_bedarf($_POST['ort']).",
			email=".apostroph_bei_bedarf($_POST['email']).",
			telefon=".apostroph_bei_bedarf($_POST['telefon']).",
			bemerkungen=".apostroph_bei_bedarf($_POST['bemerkungen']).",
			maennlich=".leer_NULL($_POST['geschlecht']).",
			geburtsort=".apostroph_bei_bedarf($_POST['geburtsort']).",
			krankenkasse=".apostroph_bei_bedarf($_POST['krankenkasse']).",
			notfall=".apostroph_bei_bedarf($_POST['notfall']).",
			number=".apostroph_bei_bedarf($_POST['number']).",
			username=".apostroph_bei_bedarf($_POST['username']).$passwort."
		WHERE id=".injaway($_POST['schueler_id']));
}

	$sortiert=explode("_",$_GET["sort"]);
	if ($sortiert[1]=="za")
		$reihenfolge=" DESC";
	else
		$reihenfolge="";
	switch($sortiert[0]) {
		case "nummer": $order="CAST(number AS UNSIGNED)"; break;
		case "klassenstufe": if ($sortiert[1]=="az") $order="einschuljahr DESC, name, vorname"; else $order="einschuljahr ASC, name, vorname"; break;
		case "klasse": if ($sortiert[1]=="az") $order="einschuljahr DESC, endung"; else $order="einschuljahr ASC, endung"; break;
		case "name": $order="name"; break;
		case "vorname": $order="vorname"; break;
		case "geschlecht": $order="maennlich"; break;
		case "strasse": $order="strasse"; break;
		case "ort": $order="ort"; break;
		default: $order="klasse.einschuljahr DESC, klasse.endung, name, vorname";
	}
	$order.=$reihenfolge;
	
	$alle_schueler=db_conn_and_sql("SELECT *, schueler.id AS s_id FROM klasse, schueler
		WHERE klasse.schule=".$schule."
			AND schueler.klasse=klasse.id
		ORDER BY ".$order);
	
	?>
	<a href="<?php echo $pfad."formular/import_von_fuxschool.php"; ?>" style="float: right;">FuxSchool-Import</a>
	<style type="text/css">
		.th1, .td1 {width:70px; }
		.th2, .td2 {width:60px; }
		.th3, .td3 {width:250px;}
		.th4, .td4 {width:300px;}
		.th5, .td5 {width:350px;}
		.th6, .td6 {width:100px; }
		.th7, .td7 {width:100px; }
		#table-wrapper table tr td {  background-color:#DEF; }
		#table-wrapper table tr:hover td {background-color: #AEF;}
		
		
		
#table-wrapper {
  position:relative;
}
#table-scroll {
  height:400px;
  overflow:auto;  
  margin-top:70px;
}
#table-wrapper table {
}
#table-wrapper table * {
  background-color:#DEF;
  color:black;
}
#table-wrapper table thead {
  position:absolute;
  top:-46px;
  z-index:2;
  height:40px;
}
	</style>
	<div id="table-wrapper">
		<div id="table-scroll">
			<table class="tabelle" cellspacing="0">
				<thead>
					<tr>
    				    <th class="th1"><span class="theadertext">Sch&uuml;ler-<br />nr. <?php echo sortieren("nummer", $_GET["sort"], $pfad, $formularziel); ?></span></th>
        				<th class="th2"><span class="theadertext">Kl. <?php echo sortieren("klassenstufe", $_GET["sort"], $pfad, $formularziel); ?><br />Edg. <?php echo sortieren("klasse", $_GET["sort"], $pfad, $formularziel); ?></span></th>
				        <th class="th3"><span class="theadertext">Name <?php echo sortieren("name", $_GET["sort"], $pfad, $formularziel); ?>, Vorname <?php echo sortieren("vorname", $_GET["sort"], $pfad, $formularziel); ?>, G <?php echo sortieren("geschlecht", $_GET["sort"], $pfad, $formularziel); ?></span></th>
        				<th class="th4"><span class="theadertext">Stra&szlig;e <?php echo sortieren("strasse", $_GET["sort"], $pfad, $formularziel); ?></span></th>
				        <th class="th5"><span class="theadertext">Wohnort <?php echo sortieren("ort", $_GET["sort"], $pfad, $formularziel); ?></span></th>
        				<th class="th6"><span class="theadertext">Geburtstag</span></th>
				        <th class="th7"><span class="theadertext">Aktionen</span></th>
      				</tr>
      			</thead>
      			<tbody>
	<?php
	while ($schueler = sql_fetch_assoc($alle_schueler)) { ?>
      <tr>
        <td class="td1" onclick="window.location.href='index.php?tab=schueler&amp;s_id=<?php echo $schueler["s_id"]; ?>';"><?php echo html_umlaute($schueler["number"]); ?><br /><?php echo html_umlaute($schueler["username"]); ?></td>
        <td class="td2" onclick="window.location.href='index.php?tab=schueler&amp;s_id=<?php echo $schueler["s_id"]; ?>';"><?php echo ($aktuelles_jahr-$schueler["einschuljahr"]+1)." ".$schueler["endung"]; /*."&nbsp;(".$schueler["position"].")";*/ ?></td>
        <td class="td3" onclick="window.location.href='index.php?tab=schueler&amp;s_id=<?php echo $schueler["s_id"]; ?>';"><?php echo html_umlaute($schueler["name"]).", ".html_umlaute($schueler["vorname"]).' <img src="'.$pfad.'icons/'; if ($schueler["maennlich"]) echo 'male.png'; else echo "female.png"; echo '" alt="geschlecht" style="height: 15px;" />'; ?></td>
        <td class="td4" onclick="window.location.href='index.php?tab=schueler&amp;s_id=<?php echo $schueler["s_id"]; ?>';"><?php if (!empty($schueler["strasse"])) echo html_umlaute($schueler["strasse"]); ?></td>
        <td class="td5" onclick="window.location.href='index.php?tab=schueler&amp;s_id=<?php echo $schueler["s_id"]; ?>';"><?php if (!empty($schueler["plz"])) echo html_umlaute($schueler["plz"])." "; if (!empty($schueler["ort"])) echo html_umlaute($schueler["ort"]); ?></td>
		<!--<td><?php if (!empty($schueler["telefon"])) echo html_umlaute($schueler["telefon"]); echo html_umlaute($schueler["email"]); ?></td>-->
        <td class="td6" onclick="window.location.href='index.php?tab=schueler&amp;s_id=<?php echo $schueler["s_id"]; ?>';"><?php echo datum_strich_zu_punkt($schueler["geburtstag"]); ?></td>
        <td class="td7"><a href="index.php?tab=schueler&amp;s_id=<?php echo $schueler["s_id"]; ?>" class="icon" title="Sch&uuml;lerdaten bearbeiten"><img src="<?php echo $pfad; ?>icons/edit.png" alt="edit" /></a><?php
            if (!empty($schueler["strasse"]) and !empty($schueler["ort"])) { ?>
				<a href="http://maps.google.de/maps?q=<?php echo str_replace(" ","+",html_umlaute($schueler["strasse"])); ?>%3B+<?php echo str_replace(" ","+",html_umlaute($schueler["ort"])); ?>" onclick="fenster(this.href, 'Karte'); return false;" class="icon" title="bei Google-Maps nachschlagen"><img src="<?php echo $pfad; ?>icons/karte.png" alt="karte" /></a> <?php
			}
		?></td>
      </tr>
	<?php } ?>
	</tbody>
	</table>
	</div>
	</div>
	
	<?php
	if ($_GET["s_id"]>0) {
		$edit_s=db_conn_and_sql("SELECT *, schueler.id AS s_id, klasse.id AS k_id FROM klasse, schueler WHERE klasse.schule=".$schule." AND schueler.klasse=klasse.id AND schueler.id=".injaway($_GET["s_id"]));
		$edit_s=sql_fetch_assoc($edit_s);
	
	$klassen=db_conn_and_sql("SELECT klasse.id FROM `klasse`, `schule_user`
		WHERE `klasse`.`schule`=".$schule."
			AND (".$aktuelles_jahr."-`klasse`.`einschuljahr`+1)<13
			AND `schule_user`.`schule`=".$schule."
			AND `schule_user`.`user`=".$_SESSION['user_id']."
		ORDER BY `schule_user`.`aktiv` DESC, `klasse`.`einschuljahr` DESC, `klasse`.`endung`");
	
	// sorgeberechtigter1, sorgeberechtigter2, sorgeberechtigter3
	$sorgeberechtigte=db_conn_and_sql("SELECT users.*, eltern_schueler.* FROM users, eltern_schueler WHERE eltern_schueler.schueler=".injaway($_GET["s_id"])." AND eltern_schueler.user=users.user_id");
	$sb1=sql_fetch_assoc($sorgeberechtigte);
	$sb2=sql_fetch_assoc($sorgeberechtigte);
	// SB: mail, fax, tel, status (Elt, fam, alleinerz, nur Vater, nur Mutter, Vormund), Postempfaenger
	?>
    <form action="<?php echo $formularziel; ?>&amp;eintragen=true<?php if ($_GET["s_id"]>0) echo '&amp;s_id'.injaway($_GET["s_id"]); ?>" method="post" accept-charset="ISO-8859-1">
    <fieldset style="width: 450px; float: left;"><legend>Sch&uuml;ler Grunddaten</legend>
		<ol class="divider">
		<li>
        <input type="hidden" name="schueler_id" value="<?php echo $edit_s["s_id"]; ?>" />
		<label for="geschlecht">Geschlecht:</label> <input type="radio" name="geschlecht" value="1"<?php if($edit_s["maennlich"]!="0") echo ' checked="checked"'; ?> /> m | <input type="radio" name="geschlecht" value="0"<?php if($edit_s["maennlich"]=="0") echo ' checked="checked"'; ?> /> w<br />
        <label for="name">Name, Vorname<em>*</em>:</label> <input type="text" name="name" value="<?php echo html_umlaute($edit_s["name"]); ?>" size="9" maxlength="50" />
			<input type="text" name="vorname" value="<?php echo html_umlaute($edit_s["vorname"]); ?>" size="9" maxlength="30" />
        <label for="geburtstag">Geb.tag, -ort:</label> <input type="text" name="geburtstag" id="geburtstag" size="5" value="<?php echo datum_strich_zu_punkt($edit_s["geburtstag"]); ?>" placeholder="Geburtsdatum" title="Geburtsdatum" size="10" maxlength="10" /> <input type="text" name="geburtsort" value="<?php echo html_umlaute($edit_s["geburtsort"]); ?>" placeholder="Geburtsort" title="Geburtsort" size="10" maxlength="80" /><br />
		<label for="staatszugehoerigkeit">Staat, Konf.:</label> <input type="text" name="staatszugehoerigkeit" value="<?php if ($edit_s["staatszugehoerigkeit"]!="") echo html_umlaute($edit_s["staatszugehoerigkeit"]); else echo "D"; ?>" placeholder="Staatszugeh&ouml;rigkeit" title="Staatszugeh&ouml;rigkeit" size="1" maxlength="3" />
		<select name="konfession" title="Konfession"><option value="EV">EV</option></select><br />
		</li>
		<li>
        <label for="strasse">Stra&szlig;e:</label> <input type="text" name="strasse" value="<?php echo html_umlaute($edit_s["strasse"]); ?>" placeholder="Stra&szlig;e HN" title="Stra&szlig;e und Hausnummer" size="25" maxlength="80" /><br />
        <label for="plz">PLZ, Ort:</label> <input type="text" name="plz" value="<?php echo html_umlaute($edit_s["plz"]); ?>" placeholder="PLZ" title="Postleitzahl" size="2" maxlength="5" />
			<input type="text" name="ort" value="<?php echo html_umlaute($edit_s["ort"]); ?>" placeholder="Ort" title="Wohnort" size="16" maxlength="80" /><br />
        <label for="telefon">Telefon:</label> <input type="text" name="telefon" value="<?php echo html_umlaute($edit_s["telefon"]); ?>" placeholder="Telefon" title="Telefonnummer des Sch&uuml;lers" size="15" maxlength="80" /><br />
		<label for="email">E-Mail:</label> <input type="text" name="email" id="email" value="<?php echo html_umlaute($edit_s["email"]); ?>" placeholder="E-Mail" title="E-Mail des Sch&uuml;lers" size="15" maxlength="80" />
		</li>
		<li>
        <label for="bemerkungen">Bemerkungen:</label> <textarea name="bemerkungen" cols="30" rows="2" placeholder="Bemerkungen" title="Bemerkungen"><?php echo html_umlaute($edit_s["bemerkungen"]); ?></textarea>
        </li>
        </ol>
	</fieldset>
	<fieldset style="width: 450px; float: left;">
		<legend>Sorgeberechtigte:</legend>
		<ol class="divider">
			<li>
				<label for="sb1_status">Status, Sorgeb.:</label>
					<select name="sb1_status"><option>Eltern</option><option>Familie</option><option>Alleinerz.</option><option>nur Vater</option><option>nur Mutter</option><option>Vormund</option></select>
					<input type="checkbox" name="sb1_sorgeberechtigung" /> <span style="display:inline-block; text-align: left; width: 30px;">&nbsp;</span>
					<select name="sb2_status"><option>Eltern</option><option>Familie</option><option>Alleinerz.</option><option>nur Vater</option><option>nur Mutter</option><option>Vormund</option></select>
					<input type="checkbox" name="sb2_sorgeberechtigung" />
					<br />
				<label for="sb1_male">Geschlecht:</label>
					<input type="radio" name="sb1_male" value="1"<?php if($sb1["male"]!="0") echo ' checked="checked"'; ?> /> m | <input type="radio" name="sb1_male" value="0"<?php if($sb1["male"]=="0") echo ' checked="checked"'; ?> /> w <span style="display:inline-block; text-align: left; width: 60px;">&nbsp;</span>
					<input type="radio" name="sb2_male" value="1"<?php if($sb2["male"]!="0") echo ' checked="checked"'; ?> /> m | <input type="radio" name="sb2_male" value="0"<?php if($sb2["male"]=="0") echo ' checked="checked"'; ?> /> w<br />
				<label for="sb1_title">Titel:</label>
					<input type="text" name="sb1_title" value="<?php echo html_umlaute($sb1["title"]); ?>" style="width: 140px;" />
					<input type="text" name="sb2_title" value="<?php echo html_umlaute($sb2["title"]); ?>" style="width: 140px;" /><br />
				<label for="sb1_surname">Name:</label>
					<input type="text" name="sb1_surname" value="<?php echo html_umlaute($sb1["surname"]); ?>" style="width: 140px;" />
					<input type="text" name="sb2_surname" value="<?php echo html_umlaute($sb2["surname"]); ?>" style="width: 140px;" /><br />
				<label for="sb1_forename">Vorname:</label>
					<input type="text" name="sb1_forename" value="<?php echo html_umlaute($sb1["forename"]); ?>" style="width: 140px;" />
					<input type="text" name="sb2_forename" value="<?php echo html_umlaute($sb2["forename"]); ?>" style="width: 140px;" /><br />
			</li><li>
				<label for="sb1_adress">Stra&szlig;e:</label>
					<input type="text" name="sb1_adress" value="<?php echo html_umlaute($sb1["adress"]); ?>" style="width: 140px;" />
					<input type="text" name="sb2_adress" value="<?php echo html_umlaute($sb2["adress"]); ?>" style="width: 140px;" /><br />
				<label for="sb1_postal_code">PLZ:</label>
					<input type="text" name="sb1_postal_code" value="<?php echo html_umlaute($sb1["postal_code"]); ?>" style="width: 140px;" />
					<input type="text" name="sb2_postal_code" value="<?php echo html_umlaute($sb2["postal_code"]); ?>" style="width: 140px;" /><br />
				<label for="sb1_city">Ort:</label>
					<input type="text" name="sb1_city" value="<?php echo html_umlaute($sb1["city"]); ?>" style="width: 140px;" />
					<input type="text" name="sb2_city" value="<?php echo html_umlaute($sb2["city"]); ?>" style="width: 140px;" /><br />
			</li><li>
				<label for="sb1_user_email">E-Mail:</label>
					<input type="text" name="sb1_user_email" value="<?php echo html_umlaute($sb1["user_email"]); ?>" style="width: 140px;" />
					<input type="text" name="sb2_user_email" value="<?php echo html_umlaute($sb2["user_email"]); ?>" style="width: 140px;" /><br />
				<label for="sb1_tel1">Telefon1:</label>
					<input type="text" name="sb1_tel1" value="<?php echo html_umlaute($sb1["tel1"]); ?>" style="width: 140px;" />
					<input type="text" name="sb2_tel1" value="<?php echo html_umlaute($sb2["tel1"]); ?>" style="width: 140px;" /><br />
				<label for="sb1_tel2">Telefon2:</label>
					<input type="text" name="sb1_tel2" value="<?php echo html_umlaute($sb1["tel2"]); ?>" style="width: 140px;" />
					<input type="text" name="sb2_tel2" value="<?php echo html_umlaute($sb2["tel2"]); ?>" style="width: 140px;" /><br />
				<label for="sb1_tel3">Telefon3:</label>
					<input type="text" name="sb1_tel3" value="<?php echo html_umlaute($sb1["tel3"]); ?>" style="width: 140px;" />
					<input type="text" name="sb2_tel3" value="<?php echo html_umlaute($sb2["tel3"]); ?>" style="width: 140px;" /><br />
			</li><li>
				<label for="krankenkasse">KK:</label> <input type="text" name="krankenkasse" value="<?php echo html_umlaute($edit_s["krankenkasse"]); ?>" placeholder="Krankenkasse" title="Krankenkasse" size="15" maxlength="50" /><br />
				<label for="notfall">im Notfall:</label> <input type="text" name="notfall" value="<?php echo html_umlaute($edit_s["notfall"]); ?>" placeholder="Notfallansprechpartner" title="Notfallansprechpartner" size="15" maxlength="150" /><br />
			</li><li>
				<label for="sb1_kommentar">Kommentar:</label> <br />
			</li>
		</ol>
	</fieldset>
	<fieldset style="width: 450px; float: left;">
		<legend>Schulintern:</legend>
		<ol class="divider">
		<li>
		<label for="number">Sch&uuml;lernummer:</label> <input type="text" name="number" value="<?php echo html_umlaute($edit_s["number"]); ?>" placeholder="Sch&uuml;lernummer" title="Sch&uuml;lernummer" size="1" maxlength="10" /><br />
		<label for="username">Zugang:</label> <input type="text" name="username" value="<?php echo html_umlaute($edit_s["username"]); ?>" placeholder="Benutzername" title="Benutzername" size="6" maxlength="15" />
			<input type="text" name="passwort" placeholder="Passwort" title="nur eintragen, falls es ge&auml;ndert werden soll" size="2" maxlength="35" /><br />
		<label for="klasse">Klasse, Stammg:</label> <select name="ziel_klasse">
			<?php while ($klasse=sql_fetch_assoc($klassen)) {
				echo '<option value="'.$klasse["id"].'"';
				if ($klasse["id"]==$edit_s["k_id"])
					echo ' selected="selected"';
				echo '>'.$school_classes->nach_ids[$klasse["id"]]["name"].'</option>';
			}
			?>
			</select>
			<select name="stammgruppe" title="Stammgruppe"><option>-</option><option value="1">M1</option><option value="2">M2</option><option value="3">O1</option><option value="4">O2</option></select><br />
		<!--<select name="geschlecht"><option value="1">m</option><option value="0"<?php if($edit_s["maennlich"]=="0") echo ' selected="selected"'; ?>>w</option></select><br />-->
		</li>
		<li>
			<?php $schulen_result=db_conn_and_sql("SELECT * FROM schule");
			$schulen=array();
			while ($my_schule=sql_fetch_assoc($schulen_result))
				$schulen[]=array("id"=>$my_schule["id"], "kuerzel"=>$my_schule["kuerzel"], "langform"=>$my_schule["name"], "ort"=>$my_schule["plz_ort"]); ?>
		<label for="abgebende_schule">abgebende Sch.:</label> <select name="abgebende_schule"><option>-</option><?php foreach($schulen as $my_schule) echo '<option value="'.$my_schule["id"].'">'.$my_schule["kuerzel"]."; ".$my_schule["ort"].'</option>'; ?></select><br />
		<label for="aufnahmedatum">Aufnahmedatum:</label> <input type="text" name="aufnahmedatum" /><br />
		<label for="aufnehmende_schule">aufnehmende S.:</label> <select name="aufnehmende_schule"><option>-</option><?php foreach($schulen as $my_schule) echo '<option value="'.$my_schule["id"].'">'.$my_schule["kuerzel"]."; ".$my_schule["ort"].'</option>'; ?></select><br />
		<label for="abgabedatum">Aufnahmedatum:</label> <input type="text" name="abgabedatum" /><br />
		</li>
		<li><label for="fotoerlaubnis">Fotoerlaubnis:</label> <input type="checkbox" name="fotoerlaubnis" value="1"<?php if ($edit_s["fotoerlaubnis"]) echo ' checked="checked"'; ?> />
		<!--Migrantenstatus?--><br />
		</li>
		<li>
			<label for="schnliessfachnummer">Schlie&szlig;fach:</label> <input type="text" name="schliessfachnummer" title="Schnlie&szlig;fach-Nummer" style="width: 70px;" />
				<input type="text" name="schliessfach_schluesselnummer" title="Schlie&szlig;fach-Schl&uuml;sselnummer" style="width: 70px;" /><br />
		</li>
		<li>
			<label for="religionsunterricht">Religionsunt.:</label> <select name="religionsunterricht"><option>Re/E</option></select>
				<input type="number" name="religionsunterricht_jahre" style="width: 30px;" value="0" /> J.<br />
			<label for="fs1">Fremdspr. 1:</label> <select name="fs1"><option>En</option></select>
				<input type="number" name="fs1_jahre" style="width: 30px;" value="0" /> J.<br />
			<label for="fs1">Fremdspr. 2:</label> <select name="fs2"><option>-</option><option>Ru</option><option>La</option></select>
				<input type="number" name="fs2_jahre" style="width: 30px;" value="0" /> J.<br />
			<label for="fs3">Fremdspr. 3:</label> <select name="fs3"><option>-</option><option>Ru</option><option>La</option></select>
				<input type="number" name="fs3_jahre" style="width: 30px;" value="0" /> J.<br />
			<label for="profil1">Profil 1:</label> <select name="profil1"><option>-</option></select>
				<input type="number" name="profil1_jahre" style="width: 30px;" value="0" /> J.<br />
			<label for="profil2">Profil 2:</label> <select name="profil2"><option>-</option></select>
				<input type="number" name="profil2_jahre" style="width: 30px;" value="0" /> J.<br />
			<label for="neigungskurs">Neigungskurs:</label> <select name="neigungskurs"><option>-</option></select>
				<input type="number" name="neigungskurs_jahre" style="width: 30px;" value="0" /> J.<br />
		</li>
		</ol>
	</fieldset>
	<br style="clear: both;" />
	<input type="button" class="button" value="speichern" onclick="auswertung=new Array; auswertung.push(new Array(0, 'name','nicht_leer'), new Array(0, 'vorname','nicht_leer')); if (document.getElementById('geburtstag').value!='') auswertung.push(new Array(0, 'geburtstag','datum','1950-01-01','<?php echo ($aktuelles_jahr-3); ?>-01-01')); if (document.getElementById('email').value!='') auswertung.push(new Array(0, 'email','email')); pruefe_formular(auswertung);" />
	</form>
	<?php } ?>
	
	</div>
	</body>
</html>
