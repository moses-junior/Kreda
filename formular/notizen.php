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


switch ($_GET["eintragen"]) {
	case "true":
		// eintragen
		if ($_POST["id"]>0) {
			if (proofuser("notiz", $_POST["id"]))
				db_conn_and_sql("UPDATE notiz SET datum=".apostroph_bei_bedarf(datum_punkt_zu_strich($_POST["datum"])).", inhalt=".apostroph_bei_bedarf($_POST["inhalt"]).", fertig=".($_POST["fertig"]+0)." WHERE id=".injaway($_POST["id"]));
		}
		else {
			db_conn_and_sql("INSERT INTO notiz (datum, inhalt, fertig, user) VALUES(".apostroph_bei_bedarf(datum_punkt_zu_strich($_POST["datum"])).", ".apostroph_bei_bedarf($_POST["inhalt"]).", ".($_POST["fertig"]+0).", ".$_SESSION['user_id'].");");
		}
	break;

	case "loeschen":
		// loeschen
		if (proofuser("notiz", $_GET["id"]))
			db_conn_and_sql("DELETE FROM notiz WHERE id=".injaway($_GET["id"]));
	break;
	
	case "nicht_fertig":
		if (proofuser("notiz", $_GET["id"]))
			db_conn_and_sql("UPDATE notiz SET fertig=0 WHERE id=".injaway($_GET["id"]));
	break;

	case "fertig":
		if (proofuser("notiz", $_GET["id"]))
			db_conn_and_sql("UPDATE notiz SET fertig=1 WHERE id=".$_GET["id"]);
	break;
}

?>
	<form action="<?php echo $pfad.$formularziel; ?>?eintragen=true" method="post" accept-charset="ISO-8859-1">
	<?php if($_GET["eintragen"]=="bearbeiten") {
			$bearbeiten=db_conn_and_sql("SELECT * FROM notiz WHERE id=".injaway($_GET["id"])." AND user=".$_SESSION['user_id']);
			echo '<input type="hidden" name="id" value="'.injaway($_GET["id"]).'" />';
		} ?>
		<fieldset><legend><img src="<?php echo $pfad; ?>icons/note.png" alt="notiz" /> <?php if($_GET["eintragen"]=="bearbeiten") echo ' bearbeiten'; else echo ' neu <img id="img_notiz" src="'.$pfad.'icons/clip_closed.png" alt="clip" onclick="javascript:clip(\'notiz\', \''.$pfad.'\')" />'; ?></legend>
		<span id="span_notiz" <?php if($_GET["eintragen"]!="bearbeiten") echo ' style="display: none;"'; ?>>
		<label for="inhalt">Inhalt<em>*</em>:</label> <textarea name="inhalt" class="markItUp" id="inhalt" rows="4" cols="40"><?php if($_GET["eintragen"]=="bearbeiten") echo html_umlaute(sql_result($bearbeiten, 0, "notiz.inhalt")); ?></textarea><br />
		<label for="datum" style="width: auto;">Datum:</label> <input type="text" class="datepicker" name="datum" size="7" maxlength="10"<?php if($_GET["eintragen"]=="bearbeiten") echo ' value="'.datum_strich_zu_punkt(sql_result($bearbeiten, 0, "notiz.datum")).'"'; ?> />
		<label for="fertig">abgeschlossen:</label> <input type="checkbox" name="fertig" value="1" <?php if($_GET["eintragen"]=="bearbeiten" and sql_result($bearbeiten, 0, "notiz.fertig")) echo ' checked="checked"'; ?> /><br />
		<button onclick="auswertung=new Array(new Array(0, 'inhalt','nicht_leer')); pruefe_formular(auswertung);"><img src="<?php echo $pfad; ?>icons/page_save.png" alt="save" /> <?php if($_GET["eintragen"]=="bearbeiten") echo 'ver&auml;ndern'; else echo 'hinzuf&uuml;gen'; ?></button>
		</span>
		</fieldset>
	</form>
	<?php
		$result=db_conn_and_sql("SELECT * FROM notiz WHERE user=".$_SESSION['user_id']." ORDER BY fertig, datum DESC");
		
		if (sql_num_rows($result)>0) {
			echo '
				<table class="notizen">';
			for ($i=0; $i<$result->num_rows; $i++) {
				echo '<tr onmouseover="document.getElementById(\'notiz_bearbeiten_'.$i.'\').style.visibility=\'visible\'; this.childNodes[1].style.backgroundColor=\'lightblue\'; this.childNodes[3].style.backgroundColor=\'lightblue\'; this.childNodes[5].style.backgroundColor=\'lightblue\';" onmouseout="document.getElementById(\'notiz_bearbeiten_'.$i.'\').style.visibility=\'hidden\'; this.childNodes[1].style.backgroundColor=\'transparent\'; this.childNodes[3].style.backgroundColor=\'transparent\'; this.childNodes[5].style.backgroundColor=\'transparent\';">
					<td style="width: 90px;">';
				if (sql_result($result, $i, "notiz.fertig"))
					echo '<a href="'.$pfad.$formularziel.'?eintragen=nicht_fertig&amp;id='.sql_result($result, $i, "notiz.id").'" title="abgeschlossen" class="icon"><img src="'.$pfad.'icons/haekchen.png" alt="h&auml;kchen" style="float: left;" /></a>';
				else
					echo '<a href="'.$pfad.$formularziel.'?eintragen=fertig&amp;id='.sql_result($result, $i, "notiz.id").'" title="noch nicht abgeschlossen" class="icon"><img src="'.$pfad.'icons/abhaken.png" alt="leere Box" style="float: left;" /></a>';
				echo '&nbsp;'.datum_strich_zu_punkt_uebersichtlich(sql_result($result, $i, "notiz.datum"),"wochentag_kurz",0).'</td>
					<td style="padding-left: 5px;">'.syntax_zu_html(sql_result($result, $i, "notiz.inhalt"),1,0,$pfad,'A').'</td>
					<td id="notiz_bearbeiten_'.$i.'" style="visibility: hidden; width: 40px;">
						<a href="'.$pfad.$formularziel.'?eintragen=bearbeiten&amp;id='.sql_result($result, $i, "notiz.id").'" class="icon"><img src="'.$pfad.'icons/edit.png" alt="bearbeiten" /></a>
						<a href="'.$pfad.$formularziel.'?eintragen=loeschen&amp;id='.sql_result($result, $i, "notiz.id").'" onclick="if (confirm(\'Die Notiz wird endg&uuml;ltig gel&ouml;scht. Wollen Sie das wirklich?\')==false) return false;" class="icon"><img src="'.$pfad.'icons/delete.png" alt="loeschen" /></a>
					</td></tr>';
			}
			echo '</table>';
		}
?>
