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
?>
<!DOCTYPE HTML>

<html>
 <head>
	<title>Elternansicht</title>
    <meta name="author" content="Micha Schubert, Christopher Wolff">
    <meta name="robots" content="noindex, nofollow">
	<meta http-equiv="content-type" content="application/xhtml+xml; charset=ISO-8859-1" />
    <link rel="shortcut icon" href="./favicon.ico" type="image/x-icon">
    <!--Stylheets-->
    <link rel="stylesheet" media="screen" type="text/css" href="./format.css">
    <link rel="stylesheet" media="print, embossed" type="text/css" href="./format_drucken.css">
    <!--jqueryLiberay-->
    <script type="text/javascript" src="./jquery-1.js"></script>
    <script type="text/javascript" src="./jquery.js"></script>
    <link type="text/css" href="./jquery.css" rel="Stylesheet">
    <!--jqueryLiberay-->
    <script src="./chart/morris.js"></script>
    <script src="./chart/morris.min.js"></script>
    <script src="./vektor/raphael-min.js"></script>
    <link rel="stylesheet" type="text/css" href="./chart/morris.css">
    <!--Externe Daten-->
    <script src="./tipsy.js"></script>
 </head>
<?php
session_start();

include $pfad."funktionen.php";

$my_user = new user();
$schul_id = $my_user->my["letzte_schule"];
$jahr=$aktuelles_jahr; // TODO: aktuelles Jahr der Schule
$start_ende=schuljahr_start_ende($jahr, $schul_id);

?>
	<body>
	<div class="wrapper">
	<div class="header">
		<table class="schulstempel" width="100%">
			<tr>
				<th>
					<img src="logo.gif" alt="logo">
				</th>
				<th>
					Evangelische Schulgemeinschaft Erzgebirge <br />
					Stra&szlig;e der Freundschaft 11 <br />
					09456 Annaberg-Buchholz <br />
				</th>
			</tr>
		</table>
	</div>
	<div class="inhalt">
		<?php
		$schueler_id = $_GET["schueler"];
		$schueler_name = db_conn_and_sql("SELECT * FROM schueler WHERE id=".$schueler_id);
		
		if (userrigths("schuelerdaten", sql_result($schueler_name,0,"klasse"))<1)
			die("Sie haben nicht die erforderlichen Rechte, um die Sch&uuml;lerdaten anzusehen.");
		
		echo pictureOfPupil(sql_result ( $schueler_name, 0, 'schueler.name' ), sql_result ( $schueler_name, 0, 'schueler.vorname' ), sql_result ( $schueler_name, 0, 'schueler.number' ), sql_result ( $schueler_name, 0, 'schueler.username' ), $pfad, 'style="float: right"');
		echo '<h1>'.html_umlaute(sql_result($schueler_name,0,"vorname")).' '.html_umlaute(sql_result($schueler_name,0,"name")).', '.$school_classes->nach_ids[sql_result($schueler_name,0,"klasse")]["name"].'</h1>';
		echo '<h2>Kontaktdaten</h2>';
		echo '		<div id="kontakte_eingeblendet">';
		echo 'Adresse: '.html_umlaute(sql_result($schueler_name,0,"strasse")).'<br />'.html_umlaute(sql_result($schueler_name,0,"ort")).'<br />Mail: '.html_umlaute(sql_result($schueler_name,0,"email")).'<br />Tel: '.html_umlaute(sql_result($schueler_name,0,"telefon"));
		echo '<br /><a onclick="document.getElementById(\'kontakte_eingeblendet\').style.display=\'none\'; document.getElementById(\'kontakte_ausgeblendet\').style.display=\'block\'; return false;">[Ausblenden]</a>	
		</div>
		<div id="kontakte_ausgeblendet" style="display:none;">
			<a onclick="document.getElementById(\'kontakte_ausgeblendet\').style.display=\'none\'; document.getElementById(\'kontakte_eingeblendet\').style.display=\'block\'; return false;">[Einblenden]</a>
		</div>';
		
		echo '<h2>Zensuren</h2>';
		
		echo '<table class="tabelle"><tr><th>Fach</th><th>Noten</th><th>Durchschnitt</th></tr>';
		$fach_klassen_des_schuelers = db_conn_and_sql("SELECT notenbeschreibung.fach_klasse
			FROM noten, notenbeschreibung
			WHERE notenbeschreibung.id=noten.beschreibung
				AND noten.schueler=".$schueler_id."
				AND noten.datum>='".$start_ende["start"]."'
				AND noten.datum<='".$start_ende["ende"]."'
			GROUP BY notenbeschreibung.fach_klasse");
		for ($fks=0; $fks<sql_num_rows($fach_klassen_des_schuelers);$fks++) {
			$fkname=sql_result(db_conn_and_sql("SELECT * FROM faecher, fach_klasse WHERE fach_klasse.fach=faecher.id AND fach_klasse.id=".sql_result($fach_klassen_des_schuelers,$fks,"notenbeschreibung.fach_klasse")),0,"name");
			// TODO zu aufwaendige Berechnungen; zurueckgegeben wird nicht beim Durchschnitt beruecksichtigt -> eigene funktion noten_von_schueler schreiben
			$notenansicht = noten_von_fachklasse(sql_result($fach_klassen_des_schuelers,$fks,"notenbeschreibung.fach_klasse"), $aktuelles_jahr, "none");
			echo '<tr>';
			echo '<td class="first">'.$fkname.'</td>';
			echo '<td>';
			// Schueler finden:
			$i=0;
			while ($i<200 and $notenansicht['schueler'][$i]['id']!=$schueler_id)
				$i++;
			
			for($j=0;$j<count($notenansicht['notenbeschreibung']);$j++)
				if ($notenansicht['schueler'][$i]['noten'][$j]["mitzaehlen"]!=0 and date("Y-m-d")>=$notenansicht['notenbeschreibung'][$j]["zurueckgegeben"])
				{
					echo '<span style="text-align:center;" title="'.$notenansicht['notenbeschreibung'][$j]['notentyp_kuerzel']." ".$notenansicht['notenbeschreibung'][$j]['beschreibung']."<br />";
					echo "<table><tr>";
					foreach ($notenansicht['notenbeschreibung'][$j]['notenspiegel'] as $n) {
						echo "<td>".$n['note']."</td>";
					}
					echo '</tr><tr>';
					foreach ($notenansicht['notenbeschreibung'][$j]['notenspiegel'] as $n)
						echo "<td>".$n['anzahl_schueler']."</td>";
					echo '</tr></table>';
					if ($notenansicht['notenbeschreibung'][$j]['durchschnitt']>0)
						echo '&Oslash; '.number_format ($notenansicht['notenbeschreibung'][$j]['durchschnitt'], 2, ',', '.' );
					echo ''."<br />";

					echo $notenansicht['schueler'][$i]['noten'][$j]['datum'].' | '.$notenansicht['schueler'][$i]['noten'][$j]['kommentar'];
					if ($notenansicht['schueler'][$i]['noten'][$j]['halbjahresnote']!=$notenansicht['notenbeschreibung'][$j]['halbjahresnote'] and $notenansicht['schueler'][$i]['noten'][$j]['wert']>0) {
						echo ' | ';
						if ($notenansicht['schueler'][$i]['noten'][$j]['halbjahresnote'])
							echo 'geht schon in Berechnung zur Halbjahresnote ein';
						else
							echo 'geht erst in Berechnung zur Ganzjahresnote ein';
					}
					echo '">
						<span';
					if ($notenansicht['schueler'][$i]['noten'][$j]['halbjahresnote']!=$notenansicht['notenbeschreibung'][$j]['halbjahresnote'] and $notenansicht['schueler'][$i]['noten'][$j]['wert']>0)
						echo ' class="grade_on_wrong_side"';
					if ($notenansicht['schueler'][$i]['noten'][$j]['kommentar']!="")
						echo ' style="font-weight: bold;"';
					echo '>'.$notenansicht['schueler'][$i]['noten'][$j]['wert'].$notenansicht['schueler'][$i]['noten'][$j]['notenzusatz'].'</span>';
					if ($notenansicht['schueler'][$i]['noten'][$j]['punktzahl_mit_komma']!="" and ($notenansicht['notenbeschreibung'][$j]['gesamtpunktzahl']>0 or $notenansicht['schueler'][$i]['noten'][$j]['einzelpunkte'][0]['pkt']>0))
					{
						echo '<span style="font-size:9px; color: #555;"> <sup>'.$notenansicht['schueler'][$i]['noten'][$j]['punktzahl_mit_komma'].'</sup>/<sub>';
						echo $notenansicht['schueler'][$i]['noten'][$j]['gesamtpunktzahl'];
						echo '</sub></span>';
					}
					echo '</span> | ';
				}
			echo '</td>';
			echo '<td class="last">';
			if ($notenansicht['notenbeschreibung'][0]['punkte_oder_zensuren']==1)
				echo '<span title="Berechnung:'."<br />".$notenansicht['schueler'][$i]['halbjahres_schnitt_berechnung'].'">'.$notenansicht['schueler'][$i]['halbjahres_schnitt_komma'].'</span> | <span title="'.$notenansicht['berechnungsvorlage']."\n".$notenansicht['schueler'][$i]['halbjahr_2_schnitt_berechnung'].'">'.$notenansicht['schueler'][$i]['halbjahr_2_schnitt_komma'].'</span></td>';
			else
				echo '<span title="'.$notenansicht['berechnungsvorlage']."\n".$notenansicht['schueler'][$i]['ganzjahres_schnitt_berechnung'].'">'.$notenansicht['schueler'][$i]['ganzjahres_schnitt_komma'].'</span></td>';
			echo '</tr>';
		}
		echo '</table>';
		
		// Fehlzeiten
		echo '<h2>Fehlzeiten</h2>';
		$fehlzeiten_result = db_conn_and_sql("SELECT * FROM schueler_fehlt WHERE schueler_fehlt.startdatum>='".$start_ende["start"]."' AND schueler_fehlt.startdatum<='".$start_ende["ende"]."' AND schueler=".$schueler_id);
		if (sql_num_rows($fehlzeiten_result)<1)
			echo 'Keine Fehlzeiten eingetragen.';
		else for ($i=0; $i<sql_num_rows($fehlzeiten_result); $i++) {
			if (sql_result($fehlzeiten_result,$i,"schueler_fehlt.entschuldigt")==0)
				$entschuldigt="Unentschuldigt";
			if (sql_result($fehlzeiten_result,$i,"schueler_fehlt.entschuldigt")==1)
				$entschuldigt="Entschuldigt";
			if (sql_result($fehlzeiten_result,$i,"schueler_fehlt.entschuldigt")==2)
				$entschuldigt="Krank";
			echo ($i+1).': '.datum_strich_zu_punkt(sql_result($fehlzeiten_result,$i,"schueler_fehlt.startdatum")).' - '.datum_strich_zu_punkt(sql_result($fehlzeiten_result,$i,"schueler_fehlt.enddatum")).': '.$entschuldigt.'<br />';
		}
		
		?>
		<table style="border-spacing:10px;">
			<tr>
				<td>
					<p>
						<a href="javascript:window.print()" class="button"><img src="druck_icon.png" alt="Drucken" width="32px"></a>
					</p>
				</td>
				<td>
					<p>
						<a href="" class="button"><img src="sichern_icon.png" alt="Drucken" width="32px"></a>
					</p>
				</td>
			</tr>
		</table>
		
	</div>
	</div>
</body>
</html>
