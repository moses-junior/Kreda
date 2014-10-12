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

//header('Content-Type: text/html; charset=UTF-8');
//$pfad="../";
//include($pfad."funktionen.php");
//$user=new user();
//$schule=$user->my["letzte_schule"];


if (file_exists($xmlFile)) {
	$xml = simplexml_load_file($xmlFile);
	
	// welche Faecher sind einzutragen
	$faecher=array();
	$fach_orginalname=array();
	foreach ( $xml->unterricht->un as $unterricht ) {
		if (is_numeric(substr($unterricht->un_fach,-1,1)))
			$fachname=substr($unterricht->un_fach,0,-1);
		else
			$fachname=$unterricht->un_fach;
		$fachname=strtoupper(iconv("UTF-8", "ASCII//TRANSLIT", trim($fachname)));
		$faecher[$fachname]=array("id"=>-1,"name"=>$fachname);
		$fach_orginalname[trim($unterricht->un_fach)]=$fachname;
	}
	
	// faecher-IDs zuordnen
	$faecher_der_schule=db_conn_and_sql("SELECT * FROM faecher WHERE schule=0 OR schule=".$schule);
	while($fach=sql_fetch_assoc($faecher_der_schule)) {
		$fachname=strtoupper(iconv("UTF-8", "ASCII//TRANSLIT", $fach["kuerzel"]));
		if (isset($faecher[$fachname]))
			$faecher[$fachname]["id"]=$fach["id"];
	}
	
	//foreach($faecher as $f_name)
	//	echo $f_name["id"].": ".$f_name["name"]."<br />";
	
	// welche Klassen sind einzutragen
	$klassen_ar=array();
	$klassen_orginalname=array();
	foreach ($xml->unterricht->un as $unterricht ) {
		$kl_st=$unterricht->un_stufe;
		foreach ($unterricht->un_klassen as $klassen) {
			$kl_endung=strtoupper(iconv("UTF-8", "ASCII//TRANSLIT", substr($klassen->un_kl, strlen($kl_st), strlen($klassen->un_kl))));
			$klassen_ar[$kl_st.$kl_endung]=array("endung"=>$kl_endung, "stufe"=> $kl_st, "id"=>-1);
			$klassen_orginalname[trim($klassen->un_kl)]=$kl_st.$kl_endung;
		}
	}
	
	// Klassen-IDs zuordnen
	$klassen_der_schule=db_conn_and_sql("SELECT * FROM klasse WHERE schule=".$schule);
	while($klasse=sql_fetch_assoc($klassen_der_schule)) {
		$kl_endung=strtoupper(iconv("UTF-8", "ASCII//TRANSLIT", str_replace(array("&alpha;","&beta;","&gamma;","&delta;","&epsilon;"),array("a", "b", "c", "d","e"),$klasse["endung"])));
		if (is_numeric($kl_endung))
			$kl_name=($aktuelles_jahr-$klasse["einschuljahr"]+1)."/".($kl_endung+0);
		else
			$kl_name=($aktuelles_jahr-$klasse["einschuljahr"]+1).$kl_endung;
		if (isset($klassen_ar[$kl_name]))
			$klassen_ar[$kl_name]["id"]=$klasse["id"];
	}
	
	//foreach($klassen_ar as $k_name) {
	//	echo $k_name["id"].": ".$k_name["stufe"].$k_name["endung"]."<br />";
	//}
	
	$import_lehrer=array();
	foreach ( $xml->lehrer->le as $lehrer ) {
		//echo ''. $lehrer->le_kurzform .': ' . $lehrer->le_vorname . ' ' . $lehrer->le_nachname . ' ' . $lehrer->le_geschlecht . ' - ';
		$import_lehrer[trim($lehrer->le_kurzform)]=array("id"=>-1, "kuerzel"=>$lehrer->le_kurzform);
	}
	
	$lehrauftrag=array();
	$errors=array();
	foreach ( $xml->unterricht->un as $unterricht ) {
		$nicht_eingetragen=false;
		if ($faecher[$fach_orginalname[trim($unterricht->un_fach)]]["id"]==-1)
			$nicht_eingetragen=true;
		$errorzeile="";
		$zugehoerige_lehrer=array();
		foreach ($unterricht->un_lehrer as $lehrer) {
			$errorzeile .= $lehrer->un_le.' ';
			$zugehoerige_lehrer[]=$lehrer->un_le;
		}
		$zugehoerige_lehrer=implode(", ",$zugehoerige_lehrer);
		
		$klasse=array();
		foreach ($unterricht->un_klassen as $klassen) {
			$errorzeile .= $klassen->un_kl.', ';
			if ($klassen_ar[$klassen_orginalname[trim($klassen->un_kl)]]["id"]==-1)
				$nicht_eingetragen=true;
			else
				$klasse[]=$klassen_ar[$klassen_orginalname[trim($klassen->un_kl)]]["id"];
		}
		
		$errorzeile.=$unterricht->un_fach .' '; //. $unterricht->un_stunden .' '. $unterricht->un_stufe .': ';
		
		if ($nicht_eingetragen)
			$errors[] = $errorzeile;
		else
			foreach($klasse as $kl)
				$lehrauftrag_indiware[]=array("fach"=>$faecher[$fach_orginalname[trim($unterricht->un_fach)]]["id"], "lehrer"=>$zugehoerige_lehrer, "klasse"=>$kl);
	}
	if (count($errors)>0) {
		echo '<div class="hinweis"><b>Nicht voreingetragen:</b><br />';
		echo implode(" | ", $errors);
		echo '</div>';
	}
	
	//echo "<br />Eingetragen:<br />";
	//foreach($lehrauftrag_indiware as $la)
	//	echo $la["fach"]."-".$la["klasse"]."-".$la["lehrer"]."<br />";

} else {
    exit("Datei $xmlFile kann nicht geöffnet werden.");
}
?> 
