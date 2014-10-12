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


	// dieses Skript nimmt sich nicht viel mit normalem mysql dump - also unnoetig - ich werde es wieder entfernen
	$pfad="../";
	include($pfad."funktionen.php");
	
function backup_full_db() {
	$db_anbindung_for_js=db_anbindung();
	$connect = mysql_connect($db_anbindung_for_js["server"], $db_anbindung_for_js["benutzer"], $db_anbindung_for_js["passwort"]);
	$sql = "SHOW TABLES FROM ".$db_anbindung_for_js["db_name"];
	$result = db_conn_and_sql($sql);
	
	$tabellennamen=''; $x=0;
	while ($row = mysql_fetch_row($result)) {
		$tabellennamen[$x] = $row[0];
		$x++;
	}

	$dateiinhalt='';
	
	for ($i=0; $i<count($tabellennamen);$i++) {
		$result = db_conn_and_sql ( "SELECT * FROM ".$tabellennamen[$i]);
		$menge = mysql_num_fields ( $result );
		$dateiinhalt.=$tabellennamen[$i]."[AUTOINCREMENt-Wert] ";
		
		for ($n=0;$n<sql_num_rows($result);$n++) {
			$dateiinhalt.="(";
			for ( $x = 0; $x < $menge; $x++ ) {
				if ($x>0)
					$dateiinhalt.= ",";
				if (mysql_field_type ( $result, $x )=="int" or mysql_field_type ( $result, $x )=="real")
					$dateiinhalt.= leer_NULL(sql_result($result,$n,$tabellennamen[$i].".".mysql_field_name ( $result, $x )));
				else {
					$rueckgabe=apostroph_bei_bedarf(html_umlaute(sql_result($result,$n,$tabellennamen[$i].".".mysql_field_name ( $result, $x ))));
					//for ($k=0;$k<count($vorkommniszeichen); $k++)
					//	$rueckgabe=str_replace($vorkommniszeichen[$k], $ersetzungszeichen[$k],$rueckgabe);
					$dateiinhalt.= $rueckgabe;
				}
			}
			$dateiinhalt.= "),";
		}
		$dateiinhalt.="\n";
	}
	return $dateiinhalt;
}

$Datei=$pfad."backup/db_backup.txt";

@chmod ($Datei, 0777);
$dateihandle = fopen($Datei,"w");

fputs($dateihandle, backup_full_db());
fclose($dateihandle);
@chmod ($Datei, 0755); //700
clearstatcache();
	echo "done";

/*
indexeddb?!?


struktur_version als feste Datei
-> DROPS selbst berechnen

in der Form Zeilenweise:
bewertungstabelle(AUTOINCREMENT-Wert): (1,'allgemein',0,1),(2,'mittelschule',0,1),
HTMLSpecialchars berücksichtigen (Zeilenumbruch -> \n; ' -> \'
Rückumwandlung?
Bestätigung über vollständiges Backup (prüfen, ob 76 Zeilen vorkommen)
Komprimierung wäre toll!
funktioniert ftp?

anmelden...
https
mysql-injections
Dateien
*/

?>
