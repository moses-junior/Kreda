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

if (!proofuser("plan", $_GET["plan"]))
	die("Sie sind hierzu nicht berechtigt.");

if ($_GET["aendern"]=="true") {
	$plan_id=injaway($_GET['plan']);
	function on_2_true($input) {
		if ($input=="on") return "1"; else return "0";
	}
	switch ($_GET["aktion"]) {
		case "hoch":
			$plan=$db->plan($plan_id);
			$eintraege=fachklassen_zeitplanung(sql_result($plan,0,"plan.fach_klasse"),sql_result($plan,0,"plan.schuljahr"));
			$j=1;
			while(($eintraege[$_GET["eintrag"]-$j]["typ"]=="feiertag" or $eintraege[$_GET["eintrag"]-$j]["typ"]=="ausfall") and ($_GET["eintrag"]-$j)>=0) $j++;
			
			switch ($_GET["wie"]) {
				case "einzel": db_conn_and_sql("UPDATE `plan`
					SET `datum`='".date("Y-m-d",$eintraege[$_GET["eintrag"]-$j]["datum"])."', `startzeit`='".$eintraege[$_GET["eintrag"]-$j]["zeit"]."'
					WHERE `id`=".$plan_id); break;
				case "mehrere":
					$aktueller_eintrag=$_GET["ziel"];
					while ($aktueller_eintrag<$_GET["eintrag"]) {
						$j=1;
						while(($eintraege[$aktueller_eintrag+$j]["typ"]=="feiertag" or $eintraege[$aktueller_eintrag+$j]["typ"]=="ausfall")) $j++;
						db_conn_and_sql("UPDATE `plan`
							SET `datum`='".date("Y-m-d",$eintraege[$aktueller_eintrag]["datum"])."', `startzeit`='".$eintraege[$aktueller_eintrag]["zeit"]."'
							WHERE `id`=".$eintraege[$aktueller_eintrag+$j]["plan_id"]);
						$aktueller_eintrag+=$j;
					}
				break;
				case "tauschen": db_conn_and_sql("UPDATE `plan`
					SET `datum`='".date("Y-m-d",$eintraege[$_GET["eintrag"]-$j]["datum"])."', `startzeit`='".$eintraege[$_GET["eintrag"]-$j]["zeit"]."'
					WHERE `id`=".$plan_id);
					db_conn_and_sql("UPDATE `plan`
					SET `datum`='".date("Y-m-d",$eintraege[$_GET["eintrag"]]["datum"])."', `startzeit`='".$eintraege[$_GET["eintrag"]]["zeit"]."'
					WHERE `id`=".$eintraege[$_GET["eintrag"]-$j]["plan_id"]); break;
			}
		break;
		case "runter":
			$plan=$db->plan($plan_id);
			$eintraege=fachklassen_zeitplanung(sql_result($plan,0,"plan.fach_klasse"),sql_result($plan,0,"plan.schuljahr"));
			$k=1;
			while(($eintraege[$_GET["eintrag"]+$k]["typ"]=="feiertag" or $eintraege[$_GET["eintrag"]+$k]["typ"]=="ausfall") and $k<20) $k++;
			
			switch ($_GET["wie"]) {
				case "einzel": db_conn_and_sql("UPDATE `plan`
					SET `datum`='".date("Y-m-d",$eintraege[$_GET["eintrag"]+$k]["datum"])."', `startzeit`='".$eintraege[$_GET["eintrag"]+$k]["zeit"]."'
					WHERE `id`=".$plan_id); break;
				case "mehrere":
					$aktueller_eintrag=$_GET["ziel"];
					while ($aktueller_eintrag>$_GET["eintrag"]) {
						$j=1;
						while(($eintraege[$aktueller_eintrag-$j]["typ"]=="feiertag" or $eintraege[$aktueller_eintrag-$j]["typ"]=="ausfall")) $j++;
						db_conn_and_sql("UPDATE `plan`
							SET `datum`='".date("Y-m-d",$eintraege[$aktueller_eintrag]["datum"])."', `startzeit`='".$eintraege[$aktueller_eintrag]["zeit"]."'
							WHERE `id`=".$eintraege[$aktueller_eintrag-$j]["plan_id"]);
						$aktueller_eintrag-=$j;
					}
				break;
				case "tauschen": db_conn_and_sql("UPDATE `plan`
					SET `datum`='".date("Y-m-d",$eintraege[$_GET["eintrag"]+$k]["datum"])."', `startzeit`='".$eintraege[$_GET["eintrag"]+$k]["zeit"]."'
					WHERE `id`=".$plan_id);
					db_conn_and_sql("UPDATE `plan`
					SET `datum`='".date("Y-m-d",$eintraege[$_GET["eintrag"]]["datum"])."', `startzeit`='".$eintraege[$_GET["eintrag"]]["zeit"]."'
					WHERE `id`=".$eintraege[$_GET["eintrag"]+$k]["plan_id"]); break;
			}
		break;
		case "bearbeiten":
			db_conn_and_sql("UPDATE `plan`
				SET `block_1`=".leer_NULL($_POST['block1']).",`block_2`=".leer_NULL($_POST['block2']).", `notizen`=".apostroph_bei_bedarf($_POST['notizen']).",`alternativtitel`=".apostroph_bei_bedarf($_POST['alternativtitel']).", `ausfallgrund`=".apostroph_bei_bedarf($_POST["ausfallgrund"])."
				WHERE `id`=".$plan_id);
            // vorerst rausgenommen: `vorbereitet`='".on_2_true($_POST['vorbereitet'])."', `nachbereitung`=".on_2_true($_POST['nachbereitung']).", 
            // bemerkung hab ich doch gar nicht als Textfeld?!? , `bemerkung`=".apostroph_bei_bedarf($_POST['bemerkung'])."
		break;
		case "loeschen":
            $deleter=del_array2echo(delete_db_object("plan", array($plan_id), $pfad, false), "sql");
            foreach ($deleter as $del_line)
                db_conn_and_sql($del_line);
		break;
		case "ausfall":
			echo "HIER KOMMT MAN NICHT HIN";
		break;
	}
if($_GET["machs"]=="kurz") {
	header("Location: ../index.php?tab=stundenplanung&auswahl=fkplan&fk=".sql_result($plan,0,"plan.fach_klasse"));
	exit;
}
?>
<html><head><script type="text/javascript">opener.location.reload(); window.close();</script></head><body>Sie k&ouml;nnen das Fenster jetzt schlie&szlig;en.</body></html>
<?php
}
else {
	$titelleiste="Unterrichtsstunden-Infos &auml;ndern";
	include $pfad."header.php"; ?>
	<body>
	<div class="inhalt">
	<fieldset><legend>Unterrichtsstunden-Infos &auml;ndern</legend>
  <?php $plan=$db->plan(injaway($_GET['plan']));
	switch ($_GET["aktion"]) {
		/*case "hoch":
			$eintraege=fachklassen_zeitplanung(sql_result($plan,0,"plan.fach_klasse"),sql_result($plan,0,"plan.schuljahr"));
			echo '<form action="./fk_zeitplan_aktion.php?aendern=true&amp;aktion=hoch&amp;plan='.sql_result($plan,0,"id").'&amp;eintrag='.$_GET["eintrag"].'" method="get">
			<select name="wie">';
			$j=1;
			while(($eintraege[$_GET["eintrag"]-$j]["typ"]=="feiertag" or $eintraege[$_GET["eintrag"]-$j]["typ"]=="ausfall") and ($_GET["eintrag"]-$j)>=0) $j++;
			if ($eintraege[$_GET["eintrag"]-$j]["typ"]!="frei_fuer_eintragung") {
				while(($eintraege[$_GET["eintrag"]-$j]["typ"]!="frei_fuer_eintragung") and ($_GET["eintrag"]-$j)>=0) $j++;
				if (($_GET["eintrag"]-$j)>=0) echo '<option value="alle_bis_freiraum">alle bis zum n&auml;chsten Freiraum hochschieben</option>';
				echo '<option value="tauschen">tauschen</option>';
			}
			else echo '<option value="einzel">einzeln</option>';
			echo '
				</select><br />
				um <input type="text" name="felder" size="1" maxlength="2" /> Felder hochschieben (noch nicht funktionst&uuml;chtig)<br />
				<input type="submit" value="los" /></form>';
		break;
		case "runter":
			$eintraege=fachklassen_zeitplanung(sql_result($plan,0,"plan.fach_klasse"),sql_result($plan,0,"plan.schuljahr"));
			echo '<form action="./fk_zeitplan_aktion.php?aendern=true&amp;aktion=runter&amp;plan='.sql_result($plan,0,"id").'&amp;eintrag='.$_GET["eintrag"].'" method="get">
			<select name="wie">';
			$k=1;
			while(($eintraege[$_GET["eintrag"]+$k]["typ"]=="feiertag" or $eintraege[$_GET["eintrag"]+$k]["typ"]=="ausfall") and $k<20) $k++;
			if ($eintraege[$_GET["eintrag"]+$k]["typ"]!="frei_fuer_eintragung") echo '<option value="alle_bis_freiraum">alle bis zum n&auml;chsten Freiraum runterschieben</option>
				<option value="tauschen">tauschen</option>';
			else echo '<option value="einzel">einzeln</option>';
			echo '
				<option value="alle_runter">alle unteren runterschieben - geht noch nicht</option>
				</select><br />
				um <input type="text" name="felder" size="1" maxlength="2" /> Felder runterschieben (noch nicht funktionst&uuml;chtig)<br />
				<input type="submit" value="los" /></form>';
		break;*/
		case "bearbeiten":
			echo eintragung_fk_zp(sql_result($plan,0,"id"));
		break;
		case "loeschen":
			echo '<form action="'.$pfad.'formular/fk_zeitplan_aktion.php?aendern=true&amp;aktion=loeschen&amp;plan='.sql_result($plan,0,"id").'" method="post">
				Hiermit wird der eingetragene Plan inklusive aller damit verbundenen Abschnitte gel&ouml;scht (die Abschnitte in den Bl&ouml;cken bleiben nat&uuml;rlich bestehen).<br />
                '.del_array2echo(delete_db_object("plan", array(sql_result($plan,0,"id")), $pfad, false), "info").'
				Wollen Sie wirklich fortfahren?<br />
				<input type="button" class="button" value="nein" onclick="window.close()" />
				<input type="submit" class="button"  value="ja" />
			</form>';
		break;
		case "ausfall": // HIER KOMMT MAN GAR NICHT HIN. SOLLTE DER BEDARF DA SEIN, KÖNNTE MAN HIER WEITER MACHEN
			echo '<form action="'.$pfad.'formular/fk_zeitplan_aktion.php?aendern=true&amp;aktion=ausfall&amp;plan='.sql_result($plan,0,"id").'" method="get">
				Ausfallgrund: <input type="text" name="grund" size="10" maxlength="50" /><br />
				<select>
					<option value="1_runter">alle darunter liegenden Eintr&auml;ge um eine Stelle runterschieben</option>
					<option value="loesch">diesen Eintrag l&ouml;schen und alle anderen Eintr&auml;ge an deren Position lassen</option>
				</select><br />
				<input type="submit" value="ausf&uuml;hren" /> HIER KOMMT MAN GAR NICHT HIN. SOLLTE DER BEDARF DA SEIN, KÖNNTE MAN HIER WEITER MACHEN.
				</form>'; //um <input type="text" name="felder" size="1" maxlength="2" /> Felder runterschieben<br />
		break;
	}
   ?>
		</fieldset>
	</div>
  </body>
</html><?php
}
?>
