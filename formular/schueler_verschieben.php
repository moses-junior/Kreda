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
include "../funktionen.php";

if (userrigths("schueler_verschieben", $_GET["wen"])!=2)
	die("Sie haben nicht die erforderlichen Rechte, um den Sch&uuml;ler zu verschieben.");

if ($_GET["eintragen"]=='true') {
	db_conn_and_sql("UPDATE `schueler` SET `klasse`=".injaway($_POST["ziel_klasse"])." WHERE `id`=".injaway($_GET["wen"]));
	db_conn_and_sql("DELETE FROM `gruppe` WHERE `schueler`=".injaway($_GET["wen"]));
	
	echo '<html><head><script type="text/javascript">opener.location.reload(); window.close();</script></head><body>Sie k&ouml;nnen das Fenster jetzt schlie&szlig;en.</body></html>';
}	
else {
	$klassen=db_conn_and_sql("SELECT * FROM `klasse`, `schule`, `schule_user`
		WHERE `klasse`.`schule`=`schule`.`id`
			AND (".$aktuelles_jahr."-`klasse`.`einschuljahr`+1)<13
			AND `schule_user`.`schule`=`schule`.`id`
			AND `schule_user`.`user`=".$_SESSION['user_id']."
		ORDER BY `schule_user`.`aktiv` DESC, `schule`.`kuerzel`, `klasse`.`einschuljahr` DESC, `klasse`.`endung`");
	include($pfad."header.php");
?>
<body>
	<div class="inhalt">
		<form action="schueler_verschieben.php?wen=<?php echo $_GET["wen"]; ?>&amp;ursprungsklasse=<?php echo $_GET["ursprungsklasse"]; ?>&amp;eintragen=true" method="post" accept-charset="ISO-8859-1">
			in Klasse <select name="ziel_klasse">
			<?php for ($i=0; $i<sql_num_rows($klassen);$i++) {
				echo '<option value="'.sql_result($klassen,$i,"klasse.id").'"';
				if (sql_result($klassen,$i,"klasse.id")==$_GET["ursprungsklasse"])
					echo ' selected="selected"';
				echo '>'.html_umlaute(sql_result($klassen,$i,"schule.kuerzel")).': '.$school_classes->nach_ids[sql_result($klassen,$i,"klasse.id")]["name"].'</option>';
			}
			?>
			</select>
			<input type="submit" class="button" value="verschieben" />
		</form>
	</div>
</body>
</html>
<?php } ?>
