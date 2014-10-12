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
include($pfad."funktionen.php");

$titelleiste="Auswertung Kopfnoten";
include $pfad."header.php";

$user=new user();
$schule=$user->my["letzte_schule"];
?>
<style type="text/css">
	@page { size:21.0cm 14.85cm; margin-top:1.5cm; margin-bottom:2cm  }
	@page :left { margin-left:1.5cm; margin-right:2cm  }
	@page :right { margin-left:2cm; margin-right:1.5cm  }
	@media print { THEAD {display: table-header-group} }
</style>
<div class="inhalt">
<?php

// TODO: darf ich das?
// Uebersicht eines einzelnen Rahmens
if ($_GET["klasse"]>0) {
	if (userrigths("kopfnoten_klasse", $_GET["klasse"])==0) // Leserecht reicht
		die("Sie haben nicht das Recht, die Kopfnotenauswertung der Klasse zu sehen.");
}
else if (userrigths("kopfnotenrahmen", $_GET["auswertung"])==0)
	die("Sie haben nicht das Recht, die schulweite Auswertung des Kopfnotenrahmens zu sehen.");

if ($_GET["auswertung"]>0) {
	$auswertung_rahmendaten=db_conn_and_sql("SELECT * FROM kopfnote_rahmen WHERE kopfnote_rahmen.id=".injaway($_GET["auswertung"]));
	$auswertung_rahmendaten=sql_fetch_assoc($auswertung_rahmendaten);
	echo '<span style="float:right; color: gray;">Stand: '.date("d.m.Y").'</span>
		<h1>'.$auswertung_rahmendaten["name"].' ('.datum_strich_zu_punkt_uebersichtlich($auswertung_rahmendaten["bearbeitung_bis"], false, true).')</h1>';
	
	$auswertung_kategorien=db_conn_and_sql("SELECT * FROM kopfnotenkat_rahmen LEFT JOIN kopfnoten_kategorie ON kopfnoten_kategorie.id=kopfnotenkat_rahmen.kn_kat WHERE rahmen=".injaway($_GET["auswertung"])." ORDER BY position");
	$kat_header=array();
	$kat=array();
	while ($auswertung=sql_fetch_assoc($auswertung_kategorien)) {
		$kat_header[]=$auswertung["name"];
		$kat[]=array("id"=>$auswertung["id"], "name"=>$auswertung["name"]);
	}
	
	if ($_GET["klasse"]>0)
		$klasse=' AND klasse.id='.injaway($_GET["klasse"]);
	else
		$klasse='';
	
	$auswertung_result=db_conn_and_sql("SELECT *, schueler.id AS s_id, klasse.id AS k_id
		FROM kopfnote
			LEFT JOIN fach_klasse ON fach_klasse.id=kopfnote.fach_klasse
			LEFT JOIN faecher ON faecher.id=fach_klasse.fach
			LEFT JOIN schueler ON schueler.id=kopfnote.schueler
			LEFT JOIN klasse ON klasse.id=schueler.klasse
		WHERE kopfnote.rahmen=".injaway($_GET["auswertung"]).$klasse."
		ORDER BY klasse.einschuljahr DESC, klasse.endung, schueler.position, schueler.name, schueler.vorname, kopfnote.kategorie, faecher.kuerzel");
	echo '<table class="tabelle">';
	echo '<thead><tr><th></th><th>'.implode('</th><th>', $kat_header).'</th></tr></thead>';
	$s_id_old=0;
	$klasse_old=0;
	$kn_zaehler=0;
	while ($auswertung=sql_fetch_assoc($auswertung_result) or $kn_zaehler==sql_num_rows($auswertung_result)) {
		// Falls der naechste Schueler dran ist
		if ($s_id_old!=$auswertung["s_id"]) {
			// falls das der allererste Eintrag ist (mit dem anders umgegangen wird), wird das Schreiben der Tabellenzeile uebersprungen
			if ($s_id_old!=0) {
				foreach($kat as $my_kat)
					if (count($kategorieeintraege_werte[$my_kat["id"]])>0) {
						$durchschnitt=array_sum($kategorieeintraege_werte[$my_kat["id"]]);
						$durchschnitt=$durchschnitt/count($kategorieeintraege_werte[$my_kat["id"]]);
						$einzeleintraege[]='<span title="'.implode(" | ",$kategorieeintraege_faecher[$my_kat["id"]]).'">'.kommazahl(round($durchschnitt,2)).' <span style="color: gray; font-size: 6pt;">'.count($kategorieeintraege_werte[$my_kat["id"]]).'</span></span>';
					}
					else
						$einzeleintraege[]='';
				if ($klasse_old!=0 and $klasse_old!=$auswertung["k_id"])
					$page_break=' style="page-break-after:always"';
				else
					$page_break='';
				$klasse_old=$auswertung["k_id"];
				echo '<tr'.$page_break.'><td>'.$schueler_kennzeichnung.'</td><td>'.implode("</td><td>", $einzeleintraege).'</td></tr>';
			}
			$schueler_kennzeichnung=($aktuelles_jahr-$auswertung["einschuljahr"]+1).' '.$auswertung["endung"].', '.$auswertung["name"].', '.$auswertung["vorname"];
			$i_kat=0;
			$kategorieeintraege_faecher=array();
			$kategorieeintraege_werte=array();
			$einzeleintraege=array();
		}
		
		switch ($auswertung["tendenz"]) {
			case  1: $tend="+"; break;
			case -1: $tend="-"; break;
			default: $tend="";
		}
		
		$kategorieeintraege_faecher[$auswertung["kategorie"]][]=$auswertung["kuerzel"].": ".kommazahl($auswertung["wert"]/10).$tend;
		$kategorieeintraege_werte[$auswertung["kategorie"]][]=$auswertung["wert"]/10;
		
		$s_id_old=$auswertung["s_id"];
		$kn_zaehler++;
	}
	echo '</table>';
}
?>
</div>
</body>
</html>
