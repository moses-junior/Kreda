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

if($_GET["eintragen"]=="true") {
	// eintragen
	if ($_POST["id"]>0) {
		db_conn_and_sql("UPDATE kollege SET name=".apostroph_bei_bedarf($_POST["name"]).", vorname=".apostroph_bei_bedarf($_POST["vorname"]).", geburtstag=".apostroph_bei_bedarf(datum_punkt_zu_strich($_POST["geburtstag"])).", telefon=".apostroph_bei_bedarf($_POST["telefon"]).", email=".apostroph_bei_bedarf($_POST["email"]).", adresse=".apostroph_bei_bedarf($_POST["adresse"]).", ort=".apostroph_bei_bedarf($_POST["ort"]).", schule=".leer_NULL($_POST["schule"]).", kommentar=".apostroph_bei_bedarf($_POST["kommentar"])." WHERE id=".injaway($_POST["id"]));
	}
	else {
		db_conn_and_sql("INSERT INTO kollege (name, vorname, geburtstag, telefon, email, adresse, ort, schule, kommentar, user) VALUES(".apostroph_bei_bedarf($_POST["name"]).", ".apostroph_bei_bedarf($_POST["vorname"]).", ".apostroph_bei_bedarf(datum_punkt_zu_strich($_POST["geburtstag"])).", ".apostroph_bei_bedarf($_POST["telefon"]).", ".apostroph_bei_bedarf($_POST["email"]).", ".apostroph_bei_bedarf($_POST["adresse"]).", ".apostroph_bei_bedarf($_POST["ort"]).", ".leer_NULL($_POST["schule"]).", ".apostroph_bei_bedarf($_POST["kommentar"]).", ".$_SESSION['user_id'].");");
		echo "Kollege eingetragen.<br />";
	}
}

if($_GET["eintragen"]=="loeschen" and proofuser("kollege", $_GET["id"])) {
	// loeschen
	db_conn_and_sql("DELETE FROM kollege WHERE id=".injaway($_GET["id"]));
}

?>
	<form action="<?php echo $pfad.$formularziel; ?>&amp;eintragen=true" method="post" accept-charset="ISO-8859-1">
	<?php if($_GET["eintragen"]=="bearbeiten" and proofuser("kollege", $_GET["id"])) {
			$bearbeiten=db_conn_and_sql("SELECT * FROM kollege WHERE id=".injaway($_GET["id"]));
			echo '<input type="hidden" name="id" value="'.$_GET["id"].'" />';
		} ?>
		<fieldset><legend>Kollege <?php if($_GET["eintragen"]=="bearbeiten") echo ' bearbeiten'; else echo ' neu <img id="img_kollege" src="'.$pfad.'icons/clip_closed.png" alt="clip" onclick="javascript:clip(\'kollege\', \''.$pfad.'\')" />'; ?></legend>
		<span id="span_kollege" <?php if($_GET["eintragen"]!="bearbeiten") echo ' style="display: none;"'; ?>>
		<label for="vorname">Vorname, Name<em>*</em>:</label> <input type="text" name="vorname" size="20" maxlength="30"<?php if($_GET["eintragen"]=="bearbeiten") echo ' value="'.html_umlaute(sql_result($bearbeiten, 0, "kollege.vorname")).'"'; ?> /> <input type="text" name="name" size="20" maxlength="40"<?php if($_GET["eintragen"]=="bearbeiten") echo ' value="'.html_umlaute(sql_result($bearbeiten, 0, "kollege.name")).'"'; ?> /><br />
		<label for="geburtstag">Geburtstag:</label> <input type="date" id="geburtstag" name="geburtstag" size="7" maxlength="10"<?php if($_GET["eintragen"]=="bearbeiten") echo ' value="'.datum_strich_zu_punkt(sql_result($bearbeiten, 0, "kollege.geburtstag")).'"'; ?> /><br />
		<label for="telefon">Tel. / eMail:</label> <input type="text" name="telefon" size="15" maxlength="50"<?php if($_GET["eintragen"]=="bearbeiten") echo ' value="'.html_umlaute(sql_result($bearbeiten, 0, "kollege.telefon")).'"'; ?> /> <input type="text" name="email" size="20" maxlength="50"<?php if($_GET["eintragen"]=="bearbeiten") echo ' value="'.html_umlaute(sql_result($bearbeiten, 0, "kollege.email")).'"'; ?> /><br />
		<label for="adresse">Adresse / Ort:</label> <input type="text" name="adresse" size="25" maxlength="50"<?php if($_GET["eintragen"]=="bearbeiten") echo ' value="'.html_umlaute(sql_result($bearbeiten, 0, "kollege.adresse")).'"'; ?> /> <input type="text" name="ort" size="20" maxlength="50"<?php if($_GET["eintragen"]=="bearbeiten") echo ' value="'.html_umlaute(sql_result($bearbeiten, 0, "kollege.ort")).'"'; ?> /><br />
		<label for="schule">Schule<em>*</em>:</label> <select name="schule"><option value="">-</option><?php
			$schulen=db_conn_and_sql("SELECT * FROM schule, schule_user WHERE schule_user.schule=schule.id AND schule_user.user=".$_SESSION['user_id']." ORDER BY schule_user.aktiv DESC");
			for ($i=0;$i<sql_num_rows($schulen);$i++) {
				echo '<option value="'.sql_result($schulen, $i, "schule.id").'"';
				if (($_GET["eintragen"]=="bearbeiten" and sql_result($schulen, $i, "schule.id")==sql_result($bearbeiten, 0, "kollege.schule"))) echo ' selected="selected"';
				echo ' title="'.sql_result($schulen, $i, "schule.name").'">'.sql_result($schulen, $i, "schule.kuerzel").'</option>';
			}
			?>
			</select><br />
		<label for="kommentar">Kommentar:</label> <textarea name="kommentar" cols="40" rows="5"><?php if($_GET["eintragen"]=="bearbeiten") echo html_umlaute(sql_result($bearbeiten, 0, "kollege.kommentar")); ?></textarea><br />
		<input type="button" class="button" value="<?php if($_GET["eintragen"]=="bearbeiten") echo 'ver&auml;ndern'; else echo 'hinzuf&uuml;gen'; ?>" onclick="auswertung=new Array(new Array(0, 'name','nicht_leer')); if (document.getElementById('geburtstag').value!='') auswertung.push(new Array(0, 'geburtstag','datum','1930-01-01','<?php echo ($aktuelles_jahr-15); ?>-12-31')); pruefe_formular(auswertung);" />
		</span>
		</fieldset>
	</form>
	<?php
		
		$result=db_conn_and_sql("SELECT * FROM kollege LEFT JOIN schule ON kollege.schule=schule.id WHERE kollege.user=".$_SESSION['user_id']." ORDER BY kollege.name, kollege.vorname");
		$meine_schulen_result = db_conn_and_sql("SELECT schule_user.schule FROM schule_user WHERE schule_user.aktiv=1 AND schule_user.user=".$_SESSION["user_id"]);
		$meine_schulen_sql="";
		for ($i=0; $i<sql_num_rows($meine_schulen_result); $i++) {
			if ($i>0)
				$meine_schulen_sql.=" OR ";
			$meine_schulen_sql.="schule_user.schule=".sql_result($meine_schulen_result,$i,"schule_user.schule");
		}
		$kollegen_an_meiner_schule=db_conn_and_sql("SELECT DISTINCT users.* FROM users, schule_user WHERE schule_user.user=users.user_id AND (".$meine_schulen_sql.") AND schule_user.usertyp<6 ORDER BY users.surname, users.forename");
		
		if (sql_num_rows($result)>0 or sql_num_rows($kollegen_an_meiner_schule)>0) {
			echo '<h3>Eingetragene Kollegen</h3>
				<table class="tabelle"><tr><th>Name</th><th>Adresse</th><th>Kontakt</th><th>Schule</th><th>Kommentar</th></tr>';
			
			while ($kollege=sql_fetch_assoc($kollegen_an_meiner_schule)) {
				echo '<tr><td>'.html_umlaute($kollege["surname"]);
				if ($kollege["forename"]!="") echo ", ".html_umlaute($kollege["forename"]);
				//if (sql_result($kollegen_an_meiner_schule,$i, "benutzer.geburtstag")!="") echo '<br />'.datum_strich_zu_punkt(sql_result($kollegen_an_meiner_schule, $i, "benutzer.geburtstag"));
				echo '</td>
					<td>';
				if ($kollege["adress"]!="" and $kollege["city"]!="")
					echo html_umlaute($kollege["adress"]).'<br />'.html_umlaute($kollege["postal_code"]).' '.html_umlaute($kollege["city"]);
				//if (sql_result($kollegen_an_meiner_schule,$i, "benutzer.adresse")!="") echo html_umlaute(sql_result($kollegen_an_meiner_schule,$i, "benutzer.adresse")).'<br />';
				//echo html_umlaute(sql_result($kollegen_an_meiner_schule,$i, "kollege.ort"));
				echo '</td><td>';
				//if (sql_result($kollegen_an_meiner_schule,$i, "benutzer.telefon")!="") echo html_umlaute(sql_result($kollegen_an_meiner_schule,$i, "benutzer.telefon")).'<br />';
				if ($kollege["user_email"]!="")
					echo '<a href="mailto:'.html_umlaute($kollege["user_email"]).'">'.html_umlaute($kollege["user_email"]).'</a>';
				if ($kollege["tel1"]!="")
					echo '<br />'.html_umlaute($kollege["tel1"])." | ".html_umlaute($kollege["tel2"]);
				echo '</td><td></td><td>-</td></tr>';
			}
			
			for ($i=0; $i<sql_num_rows($result); $i++) {
				echo '<tr><td>'.html_umlaute(sql_result($result,$i, "kollege.name"));
				if (sql_result($result,$i, "kollege.vorname")!="") echo ", ".html_umlaute(sql_result($result,$i, "kollege.vorname"));
				if (sql_result($result,$i, "kollege.geburtstag")!="") echo '<br />'.datum_strich_zu_punkt(sql_result($result, $i, "kollege.geburtstag"));
				echo '</td>
					<td>';
				if (sql_result($result,$i, "kollege.adresse")!="") echo html_umlaute(sql_result($result,$i, "kollege.adresse")).'<br />';
				echo html_umlaute(sql_result($result,$i, "kollege.ort")).'</td><td>';
				if (sql_result($result,$i, "kollege.telefon")!="") echo html_umlaute(sql_result($result,$i, "kollege.telefon")).'<br />';
				if (sql_result($result,$i, "kollege.email")!="") echo '<a href="mailto:'.html_umlaute(sql_result($result,$i, "kollege.email")).'">'.html_umlaute(sql_result($result,$i, "kollege.email")).'</a>';
				echo '</td><td>';
				echo html_umlaute(sql_result($result,$i, "schule.kuerzel")).'</td>
					<td>'.syntax_zu_html(sql_result($result, $i, "kollege.kommentar"),1, 0, $pfad, 'A').'
						<a href="'.$pfad.$formularziel.'&amp;eintragen=bearbeiten&amp;id='.sql_result($result, $i, "kollege.id").'" class="icon"><img src="'.$pfad.'icons/edit.png" alt="bearbeiten" /></a>
						<a href="'.$pfad.$formularziel.'&amp;eintragen=loeschen&amp;id='.sql_result($result, $i, "kollege.id").'" onclick="if (confirm(\'Der Kollege wird endg&uuml;ltig gel&ouml;scht. Wollen Sie das wirklich?\')==false) return false;" class="icon"><img src="'.$pfad.'icons/delete.png" alt="loeschen" /></a>
					</td></tr>';
			}
			echo '</table>';
		}
?>
