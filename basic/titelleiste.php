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


// Navigationsleiste
$navi='';
switch ($_GET["tab"]) {
	case "stundenplan":
		$navi[0] = "Pinwand";
		if ($_GET["auswahl"]=="stundenplan") $navi[1]="Stundenplan";
		if ($_GET["auswahl"]=="kalender") $navi[1]="Kalender";
		if ($_GET["auswahl"]=="konferenz") $navi[1]="Konferenz";
		if ($_GET["auswahl"]=="kollegen") $navi[1]="Kollegen";
		break;
	case "klassen":
		$navi[0] = "Klassen";
		if ($_GET["auswahl"]>0) {
			//$result=db_conn_and_sql("SELECT * FROM `klasse` WHERE `id`=".$_GET["auswahl"]);
			$navi[1] = $school_classes->nach_ids[$_GET["auswahl"]]["name"];
			switch ($_GET["option"]) {
				case "statistik": $navi[2]="Hausaufgabenstatistik"; break;
				case "fk": $navi[2]="Fach-Klasse-Kombinationen"; break;
				case "sitzplan": $navi[2]="Sitzpl&auml;ne"; break;
				case "fehlzeiten": $navi[2]="Fehlzeiten"; break;
				case "elternabend": $navi[2]="Elternabende / Elternbriefe"; break;
				default: $navi[2]="Sch&uuml;ler&uuml;bersicht"; break;
			}
		}
		if ($_GET["zweit"]=="alle") $navi[1] = "alle Klassen";
		if ($_GET["zweit"]=="schuelersuche") $navi[1] = "Sch&uuml;ler suchen";
		break;
	case "noten":
		$navi[0] = "Zensuren";
		if ($_GET["auswahl"]>0) {
			$navi[1] = $subject_classes->cont[$subject_classes->active]["name"];
			if ($_GET["eintragen"]=="true") $navi[2]="Zensurenberechnung festlegen";
			else $navi[2]="&Uuml;bersicht";
		}
		break;
	case "material":
		$navi[0] = "Materialsammlung";
		switch ($_GET["auswahl"]) {
			case "themen": $navi[1]="Themen"; break;
			case "aufgaben": $navi[1]="Aufgaben"; break;
			case "test": $navi[1]="Tests";
				if ($_GET["welcher"]>0) {
					//$result=db_conn_and_sql("SELECT * FROM notentypen, lernbereich, lehrplan, faecher, test
					//	LEFT JOIN themenzuordnung ON themenzuordnung.id=test.id AND themenzuordnung.typ=5
					//	LEFT JOIN thema ON themenzuordnung.thema=thema.id
					//	WHERE test.notentyp=notentypen.id
					//		AND test.lernbereich=lernbereich.id
					//		AND lernbereich.lehrplan=lehrplan.id
					//		AND lehrplan.fach=faecher.id
					//		AND test.id=".$_GET["welcher"]);
					//$navi[2]=html_umlaute(sql_result($result,0,"notentypen.kuerzel"))." ".html_umlaute(sql_result($result,0,"faecher.kuerzel"))." ".sql_result($result,0,"lernbereich.klassenstufe").": ";
					//if (sql_result($result,0,"test.titel")!="") $navi[2].=html_umlaute(sql_result($result,0,"test.titel"));
					//else for ($i=0; $i<sql_num_rows($result); $i++) $navi[2].=html_umlaute(sql_result($result,$i,"thema.bezeichnung"))." "; // nicht soo toll
				}
				break;
			case "link": $navi[1]="AB / Folie / Link"; break;
			case "buch": $navi[1]="B&uuml;cher"; break;
			case "grafik": $navi[1]="Grafiken"; break;
			case "sonstiges": $navi[1]="Sonstige Materialien"; break;
			case "suche": $navi[1]="Materialsuche"; break;
		}
		break;
	case "stundenplanung":
		$navi[0] = "Unterrichtsplanung";
		switch ($_GET["auswahl"]) {
			case "lernbereiche": $navi[1]="Fundus";
				if ($_GET["lehrplan"]>0) {
					//$navi[2]=html_umlaute(sql_result(db_conn_and_sql("SELECT faecher.kuerzel FROM `lehrplan`, `faecher` WHERE `lehrplan`.`fach`=`faecher`.`id` AND `lehrplan`.`id`=".$_GET["lehrplan"]),0,"faecher.kuerzel"))." ".$_GET["klasse"];
					if ($_GET["block"]>0) {
						//$navi[3]=html_umlaute(sql_result(db_conn_and_sql("SELECT block.name FROM block WHERE block.id=".$_GET["block"]),0,"block.name"));
					}
				}
				break;
			case "fkplan": $navi[1]="Stundenplanung";
				if ($_GET["fk"]>0) {
					$navi[2]=$subject_classes->cont[$subject_classes->active]["name"];
					if ($_GET["plan"]>0) $navi[3]="Einzelstundenansicht";
					else $navi[3]="Stoffverteilungsplan";
				}
				break;
			case "hausaufgaben": $navi[1]="Hausaufgaben-&Uuml;bersicht";
				if ($_GET["fk"]>0) {
					$navi[2]=$subject_classes->cont[$subject_classes->active]["name"];
				}
				break;
			case "planstatistik": $navi[1]="Unterrichtstunden-Statistik";
				if ($_GET["fk"]>0) {
					$navi[2]=$subject_classes->cont[$subject_classes->active]["name"];
				}
				break;
		}
		break;
	case "einstellungen":
		$navi[0] = "Einstellungen";
		switch($_GET["auswahl"]) {
			case "schuljahr": $navi[1]="Schuljahresdaten";
				if ($_GET["jahr"]!="allgemein") $navi[2]=$_GET["jahr"]."/".($_GET["jahr"]+1);
				else $navi[2]="Allgemeine Einstellungen";
				break;
			case "faecher": $navi[1]="F&auml;cher"; break;
			case "schulen": $navi[1]="Schulen"; break;
			case "noten_bew": $navi[1]="Zensuren / Bewertung"; break;
			case "sitzplan": $navi[1]="Sitzplan"; break;
			case "allgemein": $navi[1]="Allgemeines"; break;
			case "programm": $navi[1]="Programm"; break;
		}
		break;
	default: $navi[0] = "Startseite"; break;
}

$titelleiste = $navi[0];
if (isset($navi[1])) {
	$titelleiste .= ' - '.$navi[1];
	if (isset($navi[2])) {
		$titelleiste .= ' - '.$navi[2];
		if (isset($navi[3])) {
			$titelleiste .= ' - '.$navi[3];
		}
	}
}
?>
