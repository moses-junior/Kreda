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

/*echo 'test: ';
if (isset($_POST["jahr_beginn_".$schule_n])) echo 'passed ';*/

	// Halbjahreswechsel und freie Tage koennen nur vom Eigentuemer der Schule bearbeitet werden oder vom Admin
	if (userrigths("admin",0) or ($_POST["schule"]>0 and userrigths("schuljahresdaten", $_POST["schule"])==2))
	{
		$jahr_belegt=db_conn_and_sql("SELECT * FROM schuljahr WHERE schule=".injaway($_POST["schule"])." AND jahr=".injaway($_POST["schuljahr"]));
		if(sql_num_rows($jahr_belegt)==1) {
			db_conn_and_sql("UPDATE `schuljahr` SET `halbjahreswechsel`=".apostroph_bei_bedarf(datum_punkt_zu_strich($_POST["hjw"]))." WHERE `schule`=".leer_NULL($_POST["schule"])." AND `jahr`=".leer_NULL($_POST["schuljahr"]));
		}
		else  {
			db_conn_and_sql("INSERT INTO `schuljahr` (`jahr`,`halbjahreswechsel`,`schule`) VALUES ( ".leer_NULL($_POST["schuljahr"]).", ".apostroph_bei_bedarf(datum_punkt_zu_strich($_POST["hjw"])).", ".leer_NULL($_POST["schule"]).");");
		}
		if ($_POST["bundesland"]>0)
			db_conn_and_sql("UPDATE `schule` SET `bundesland`=".leer_NULL($_POST["bundesland"])." WHERE `schule`=".leer_NULL($_POST["schule"]));
		


		//Zusaetzliche freie Tage
		$i=0;
		while($_POST["frei_name_".$i]!="") {
			if($_POST["frei_belegt_".$i]!="") {
				db_conn_and_sql("UPDATE `bewegliche_feiertage` SET `beschreibung`=".apostroph_bei_bedarf($_POST["frei_name_".$i]).", `von`=".apostroph_bei_bedarf(datum_punkt_zu_strich($_POST["frei_datum_".$i])).", `bis`=".apostroph_bei_bedarf(datum_punkt_zu_strich($_POST["frei_bis_".$i])).", `schuljahr`=".injaway($_POST["schuljahr"]).", `schule`=".injaway($_POST["schule"]).", `fehltage`=".leer_NULL($_POST["fehltage_".$i])." WHERE `id`=".injaway($_POST["frei_belegt_".$i]));
			}
			else {
				db_conn_and_sql("INSERT INTO `bewegliche_feiertage` (`beschreibung`, `von`, `bis`, `schuljahr`, `schule`, `fehltage`) VALUES (".apostroph_bei_bedarf($_POST["frei_name_".$i]).", ".apostroph_bei_bedarf(datum_punkt_zu_strich($_POST["frei_datum_".$i])).", ".apostroph_bei_bedarf(datum_punkt_zu_strich($_POST["frei_bis_".$i])).", ".injaway($_POST["schuljahr"]).", ".injaway($_POST["schule"]).", ".leer_NULL($_POST["fehltage_".$i]).")");
			}
			$i++;
		}
	}

header("Location: ".$pfad."index.php?tab=einstellungen&auswahl=schuljahr&jahr=".$_POST["schuljahr"]);
exit;
?>
