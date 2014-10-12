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


// TODO: Zu viel exportiert:
// Fehlzeiten von immer
// alle notenbeschreibungen -> Noten auch

	//$pfad='../';
    //include $pfad."funktionen.php";
    
    function idBereitsImArray ($inputarray, $vergleichswert) {
        for ($m=0; $m<count($inputarray); $m++)
            if ($inputarray[$m]==$vergleichswert)
                return true;
        
        return false;
    }
    
    function tablename2statements($tablename, $ids, $key) {
		$return="";
        if ($ids==null AND (
				$tablename=="sonstiges" or
				$tablename=="aufgaben" or
				$tablename=="link" or
				$tablename=="grafik" or
				$tablename=="material" or
				$tablename=="test" or
				$tablename=="test_aufgabe" or
				$tablename=="grafik_abschnitt" or
				$tablename=="grafik_aufgabe" or
				$tablename=="material_abschnitt" or
				$tablename=="buch" or
				$tablename=="abschnitt" or
				$tablename=="abschnittsplanung"))
            return $return;
        $result = mysql_query ( "SELECT * FROM ".$tablename);
        $menge = mysql_num_fields ( $result );
        $insert_statement="";
        
        for ( $x = 0; $x < $menge; $x++ )
        {
            if ($x==0) $insert_statement.="stats.push(\"INSERT INTO ".$tablename." (";
            else $insert_statement.= ", ";
            $insert_statement.=mysql_field_name ( $result, $x );
        }
        $insert_statement.=") VALUES (";
        
        // TODO Leerzeichen entfernen
        // TODO avoid ‚Abfall‘; /[stern] [stern]/ (die Zeichen sind gemeint - gibt es noch mehr?) Funktioniert leider noch nicht! plan.id=474
        //$vorkommniszeichen=array("\'", "´", "`", '‚', '‘', "[stern]/", "/[stern]", '&quot;');
        //$ersetzungszeichen=array("&# 39;", "&# 39;", "&# 39;", "&# 39;", "&# 39;", "[stern] /", "/ [stern]", '\&quot;');
        $vorkommniszeichen=array("\'", "&quot;", "*/", "/*");
        $ersetzungszeichen=array("&#148;", "&#148;", "* /", "/ *");
        
        if ($ids==null) {
            for ($n=0;$n<mysql_num_rows($result);$n++) {
				$return.= $insert_statement;
				//if ($n>0)
				//	$return.= ",";
				//$return.= "(";
                for ( $x = 0; $x < $menge; $x++ )
                {
                    if ($x>0) $return.= ", ";
                    if (mysql_field_type ( $result, $x )=="int" or mysql_field_type ( $result, $x )=="real")
                        $return.= leer_NULL(mysql_result($result,$n,$tablename.".".mysql_field_name ( $result, $x )));
                    else {
						$rueckgabe=html_umlaute(mysql_result($result,$n,$tablename.".".mysql_field_name ( $result, $x )));
						// Workaround: Wenn am Ende z.B. H:\ steht, hebt der Backslash das Apostroph auf
                        if (substr($rueckgabe, -1, 1)=="\\")
							$rueckgabe.=" ";
                        $rueckgabe=apostroph_bei_bedarf($rueckgabe);
                        for ($k=0;$k<count($vorkommniszeichen); $k++)
                            $rueckgabe=str_replace($vorkommniszeichen[$k], $ersetzungszeichen[$k],$rueckgabe);
                        $return.= $rueckgabe;
                    }
                }
                //$return.= ")";
				$return.= ")\");
";
            }
        }
        else {
			//$print_statement=false;
			//$whole_statement=$insert_statement;
			for($i=0; $i<count($ids); $i++) {
				// eigentlich ist das "in Anführungszeichen schreiben" gar nicht nötig zu unterscheiden - alles "mit" geht auch
				if ($key=="startdatum")
					$einzelquery=mysql_query("SELECT * FROM ".$tablename." WHERE ".$key."='".$ids[$i]."'");
				else
					$einzelquery=mysql_query("SELECT * FROM ".$tablename." WHERE ".$key."=".$ids[$i]);
					
				if (@mysql_num_rows($einzelquery)>0)
					//	$print_statement=true;
				for ($n=0;$n<mysql_num_rows($einzelquery);$n++) {
					$return.= $insert_statement;
					//if ($n>0 or $i>0)
					//	$whole_statement.=",";
					//$whole_statement.="(";
			
					for ( $x = 0; $x < $menge; $x++ )
					{
						if ($x>0)
							$return.= ", ";
						if (mysql_field_type ( $result, $x )=="int" or mysql_field_type ( $result, $x )=="real")
							$return.= leer_NULL(mysql_result($einzelquery,$n,$tablename.".".mysql_field_name ( $result, $x )));
						else {
							$rueckgabe=html_umlaute(mysql_result($einzelquery,$n,$tablename.".".mysql_field_name ( $result, $x )));
							// Workaround: Wenn am Ende z.B. H:\ steht, hebt der Backslash das Apostroph auf
							if (substr($rueckgabe, -1, 1)=="\\")
								$rueckgabe.=" ";
							$rueckgabe=apostroph_bei_bedarf($rueckgabe);
							for ($k=0;$k<count($vorkommniszeichen); $k++)
								$rueckgabe=str_replace($vorkommniszeichen[$k], $ersetzungszeichen[$k],$rueckgabe);
							$return.= $rueckgabe;
						}
					}
					//$whole_statement.=")";
					$return.= ")\");
";
				}
			}
			//$whole_statement.="\");
			//";
			//if ($print_statement)
			//	$return.= $whole_statement;
        }
        return $return;
    }



function db_infos_javascript() {
	$return='function get_kreda_infos() {
var kreda_db = new Array();
';
    
	$connect = mysql_connect(DB_HOST, DB_USER, DB_PASS);
	$db_selected = mysql_select_db(DB_NAME, $connect);
	// deprecated: $result = mysql_list_tables ($db_anbindung_for_js["db_name"], $connect );
	// $menge = mysql_num_rows ( $result );*/
	$sql = "SHOW TABLES FROM ".DB_NAME;
	$result = mysql_query($sql); //, $db_selected
	
	$tabellennamen=''; $x=0;
	while ($row = mysql_fetch_row($result)) {
		$tabellennamen[$x] = $row[0];
		$x++;
	}
	/*for ( $x = 0; $x < $menge; $x++ ) {
		$tabellennamen[$x] = mysql_tablename ( $result, $x );
	}*/
	$i=-1;
	for ($ii=0; $ii<count($tabellennamen);$ii++) if ($tabellennamen[$ii]!="users" and $tabellennamen[$ii]!="user_pwd") {
		$i++;
		$return.= 'kreda_db['.$i.'] = new Object();
kreda_db['.$i.']["name"] = "'.$tabellennamen[$ii].'";
kreda_db['.$i.']["data"] = new Array();
';
		$sql = 'SELECT * FROM `'.$tabellennamen[$ii].'`';
		$result = mysql_query ( $sql );
		$menge = mysql_num_fields ( $result );
		
		//$primary_keys='';
		//for ( $x = 0; $x < $menge; $x++ )
		//if (stristr (mysql_field_flags ( $result, $x ), "primary key"))
		//	$primary_keys[]=$x;
		
		for ( $x = 0; $x < $menge; $x++ )
		{
			$typ=mysql_field_type ( $result, $x ).'('.mysql_field_len ( $result, $x ).')';
			//if ($typ=="real(7)") $typ="decimal(5,2)";
			//if ($typ=="real(6)") $typ="decimal(5,1)";
			//if (mysql_field_type ( $result, $x )=="blob") $typ="text";
			//if (mysql_field_type ( $result, $x )=="date") $typ="date";
			//if (mysql_field_type ( $result, $x )=="time") $typ="time";
			//if (mysql_field_type ( $result, $x )=="string") $typ="varchar(".mysql_field_len ( $result, $x ).")";
			
			if (mysql_field_type ( $result, $x )=="blob") $typ="TEXT";
			if (mysql_field_type ( $result, $x )=="date") $typ="TEXT";
			if (mysql_field_type ( $result, $x )=="time") $typ="TEXT";
			if (mysql_field_type ( $result, $x )=="string") $typ="VARCHAR(".mysql_field_len ( $result, $x ).")";
	
			if (mysql_field_type ( $result, $x )=="real") $typ="REAL";
			if (mysql_field_type ( $result, $x )=="int") $typ="INTEGER";
			if (mysql_field_type ( $result, $x )=="int" && mysql_field_len ( $result, $x )<10)
				if (mysql_field_len ( $result, $x )<7)
					if (mysql_field_len ( $result, $x )<5)
						$typ="INTEGER"; //"tinyint(".mysql_field_len ( $result, $x ).")";
					else
						$typ="INTEGER"; //"smallint(".mysql_field_len ( $result, $x ).")";
				else
					$typ="INTEGER";//"mediumint(".mysql_field_len ( $result, $x ).")";
			$return.='kreda_db['.$i.']["data"]['.$x.'] = new Object();
		kreda_db['.$i.']["data"]['.$x.']["fieldname"] = "'.mysql_field_name ( $result, $x ) . '";
		kreda_db['.$i.']["data"]['.$x.']["fieldsettings"] = "'.$typ.' '.str_replace("AUTO INCREMENT", "AUTOINCREMENT", str_replace("ZEROFILL", "", str_replace("UNSIGNED", "", str_replace("MULTIPLE KEY", "", str_replace("BINARY", "", str_replace("BLOB", "", str_replace("_"," ",strtoupper(mysql_field_flags ( $result, $x ))))))))) . '"
		';
		}
	}
	$return.='
return kreda_db;
}

function statements() {
    var stats = new Array();';
    
    // ab hier funktionen erforderlich (siehe oben)
    
    global $aktuelles_jahr;
    
	$schule=db_conn_and_sql("SELECT schule FROM schule_user WHERE schule_user.usertyp>0 AND schule_user.usertyp<5 AND schule_user.user=".$_SESSION["user_id"]);
	$schule=sql_fetch_assoc($schule);
	$schule=$schule["schule"];
	$start_ende=schuljahr_start_ende($aktuelles_jahr,$schule);
	
    $plan = mysql_query("SELECT DISTINCT plan.id FROM plan, fach_klasse
		WHERE '".$start_ende["start"]."'<=plan.datum
			AND '".$start_ende["ende"]."'>=plan.datum
			AND plan.fach_klasse=fach_klasse.id
			AND fach_klasse.user=".$_SESSION['user_id']); // TEST: AND (schuljahr.schule=0 OR schuljahr.schule=schule_user.schule); schule.bundesland nutzen statt benutzer.bundesland
    for ($i=0; $i<mysql_num_rows($plan); $i++)
    {
        $plaene[]=mysql_result($plan,$i,"plan.id");
    }
    if (isset($plaene)) {
		$return.=tablename2statements("verwarnungen", $plaene, "plan");
		$return.=tablename2statements("mitarbeit", $plaene, "plan");
		$return.=tablename2statements("plan", $plaene, "id");
	}
    
    //$schujahr_query=mysql_query("SELECT *
	//	FROM benutzer, schuljahr, schule_user
	//	WHERE benutzer.id =".$_SESSION['user_id']."
	//		AND benutzer.aktuelles_schuljahr = schuljahr.jahr
	//		AND schule_user.schule = schuljahr.schule
	//		AND schule_user.user =".$_SESSION['user_id']."
	//	ORDER BY schuljahr.beginn");
	
    //$schuljahrbeginn=mysql_result($schuljahr_query,0,"schuljahr.beginn"); // empty result?!? stimmt doch gar nicht...
    $schuljahrbeginn=$start_ende["start"];
    $fehlzeiten_query=mysql_query("SELECT DISTINCT startdatum FROM schueler_fehlt,schueler,fach_klasse
		WHERE fach_klasse.user=".$_SESSION['user_id']."
			AND fach_klasse.klasse=schueler.klasse
			AND schueler.id=schueler_fehlt.schueler
			AND schueler_fehlt.startdatum>='".$schuljahrbeginn."'
			AND schueler_fehlt.startdatum<='".$start_ende["ende"]."'");
    if (mysql_num_rows($fehlzeiten_query)>0) {
        for ($i=0; $i<mysql_num_rows($fehlzeiten_query);$i++) {
            $fehlzeiten[]=mysql_result($fehlzeiten_query, $i, "schueler_fehlt.startdatum");
        }
        $return.=tablename2statements("schueler_fehlt", $fehlzeiten, "startdatum");
    }
    
    $konferenzen_query=mysql_query("SELECT konferenz.id FROM konferenz WHERE datum>('".$schuljahrbeginn."' - INTERVAL 60 DAY) AND user=".$_SESSION['user_id']);
    for ($i=0; $i<mysql_num_rows($konferenzen_query);$i++) {
        $return.=tablename2statements("konferenz", array(mysql_result($konferenzen_query, $i, "konferenz.id")), "id");
    }
    
    $fachklassen="";
    $fachkl=mysql_query("SELECT fach_klasse.id FROM fach_klasse WHERE fach_klasse.anzeigen=1 AND fach_klasse.user=".$_SESSION['user_id']);
    for ($i=0; $i<mysql_num_rows($fachkl); $i++) {
        $fachklassen[$i]=mysql_result($fachkl,$i,"fach_klasse.id");
    }
    
    if (mysql_num_rows($fachkl)>0)
		$return.=tablename2statements("fach_klasse", $fachklassen, "id");
    
    $anstehende_berichtigungen='';
    $gruppen_array='';
    $notenbeschreibungen_array='';
    $notenbeschreibungen_updates='';
    $noten_array='';
    for ($i=0; $i<count($fachklassen); $i++) if ($fachklassen[$i]>0) {
        //$gruppe=mysql_query("SELECT * FROM gruppe WHERE gruppe.fach_klasse=".$fachklassen[$i]);
        $gruppen_array[]=$fachklassen[$i];
        $notenbeschr=mysql_query("SELECT notenbeschreibung.id, notenbeschreibung.berichtigung, notenbeschreibung.unterschrift, IF(notenbeschreibung.datum IS NULL,plan.datum,notenbeschreibung.datum) AS MyDatum
            FROM notenbeschreibung LEFT JOIN plan ON notenbeschreibung.plan=plan.id
            WHERE (('".$start_ende["start"]."'<=notenbeschreibung.datum AND '".$start_ende["ende"]."'>=notenbeschreibung.datum)
                        OR ('".$start_ende["start"]."'<=plan.datum AND '".$start_ende["ende"]."'>=plan.datum))
                    AND notenbeschreibung.fach_klasse=".$fachklassen[$i]);
        for ($k=0; $k<mysql_num_rows($notenbeschr);$k++) {
            $notenbeschreibungen_array[]=mysql_result($notenbeschr,$k, "notenbeschreibung.id");
            
            if ((mysql_result($notenbeschr,$k, "notenbeschreibung.unterschrift")==0 AND mysql_result($notenbeschr,$k, "notenbeschreibung.unterschrift")!='')
					OR (mysql_result($notenbeschr,$k, "notenbeschreibung.berichtigung")==0 AND mysql_result($notenbeschr,$k, "notenbeschreibung.berichtigung")!=''))
                $anstehende_berichtigungen[]=mysql_result($notenbeschr,$k, "notenbeschreibung.id");
            
            $notenbeschreibungen_updates.=' stats.push("UPDATE notenbeschreibung SET datum=\''.mysql_result($notenbeschr,$k, "MyDatum").'\' WHERE id='.mysql_result($notenbeschr,$k, "notenbeschreibung.id").'");';
            $noten_array[]=mysql_result($notenbeschr,$k, "notenbeschreibung.id");
            //$noten=mysql_query("SELECT noten.id FROM noten WHERE noten.beschreibung=".mysql_result($notenbeschr,$k, "notenbeschreibung.id"));
        }
    }
	if (isset($gruppen_array))
		$return.=tablename2statements("gruppe", $gruppen_array, "fach_klasse");
	if (isset($notenbeschreibungen_array)) {
		$return.=tablename2statements("notenbeschreibung", $notenbeschreibungen_array, "id");
		$return.= $notenbeschreibungen_updates;
	}
	if (isset($noten_array))
		$return.=tablename2statements("noten", $noten_array, "beschreibung");
    
    $faecher=mysql_query("SELECT DISTINCT fach_klasse.fach FROM fach_klasse WHERE fach_klasse.anzeigen=1 AND fach_klasse.user=".$_SESSION['user_id']);
    for ($i=0; $i<mysql_num_rows($faecher); $i++) {
        $fach[$i]=mysql_result($faecher,$i,"fach_klasse.fach");
    }
    $return.=tablename2statements("faecher", $fach, "id");
    
    $aktive_klassen=mysql_query("SELECT DISTINCT fach_klasse.klasse FROM fach_klasse WHERE fach_klasse.anzeigen=1 AND fach_klasse.user=".$_SESSION['user_id']);
    for ($i=0; $i<mysql_num_rows($aktive_klassen); $i++) {
        $klassen[$i]=mysql_result($aktive_klassen,$i,"fach_klasse.klasse");
        $aktive_schueler=mysql_query("SELECT schueler.id FROM schueler WHERE schueler.klasse=".$klassen[$i]." AND schueler.aktiv=1");
        for ($n=0; $n<mysql_num_rows($aktive_schueler);$n++)
            $schueler[]=mysql_result($aktive_schueler,$n,"schueler.id");
    }
    
    $schulen='';
    foreach($klassen as $my_class) {
        $result_schule=mysql_query("SELECT klasse.schule FROM klasse WHERE id=".$my_class);
        $die_schule=mysql_result($result_schule,0,"klasse.schule");
        $gibts_noch_nicht=true;
        for($i=0;$i<count($schulen);$i++) {
            if($die_schule==$schulen[$i]) $gibts_noch_nicht=false;
        }
        if ($gibts_noch_nicht)
            $schulen[]=$die_schule;
    }
    
    if (isset($schule))
		$return.=tablename2statements("schule", $schulen, "id");
    if (isset($klassen))
		$return.=tablename2statements("klasse", $klassen, "id");
    if (isset($schueler))
		$return.=tablename2statements("schueler", $schueler, "id");
    
    $return.=tablename2statements("benutzer", array($_SESSION['user_id']), "id");
    // Sollte eigentlich drin bleiben, aber muss jetzt vorerst entfernt werden, wegen schuljahr umbau
    //$return.=tablename2statements("schuljahr", array(mysql_result(mysql_query("SELECT DISTINCT schuljahr.jahr
	//	FROM benutzer, schuljahr, schule_user
	//	WHERE benutzer.id =".$_SESSION['user_id']."
	//		AND benutzer.aktuelles_schuljahr = schuljahr.jahr
	//		AND schule_user.schule = schuljahr.schule
	//		AND schule_user.user =".$_SESSION['user_id']."
	//	ORDER BY schuljahr.beginn"), 0, "schuljahr.jahr")), "jahr"); // GEHT DAS NOCH?!?
    
    // hausaufgaben
    $abschnitte='';
    $hausaufgaben_query=mysql_query('SELECT * FROM hausaufgabe, plan, fach_klasse WHERE hausaufgabe.kontrolliert<1 AND hausaufgabe.plan=plan.id AND plan.fach_klasse=fach_klasse.id AND fach_klasse.user='.$_SESSION['user_id']);
    for ($i=0; $i<mysql_num_rows($hausaufgaben_query); $i++) {
        $hausaufgaben[]=mysql_result($hausaufgaben_query, $i, "hausaufgabe.id");
		$ha_abschnitte=mysql_query('SELECT * FROM hausaufgabe_abschnitt WHERE hausaufgabe='.mysql_result($hausaufgaben_query, $i, "hausaufgabe.id"));
        for ($n=0; $n<mysql_num_rows($ha_abschnitte); $n++)
            if (!idBereitsImArray($abschnitte, mysql_result($ha_abschnitte, $n, "hausaufgabe_abschnitt.abschnitt")))
               $abschnitte[]=mysql_result($ha_abschnitte, $n, "hausaufgabe_abschnitt.abschnitt");
    }
	if (isset($hausaufgaben)) {
		$return.=tablename2statements("hausaufgabe", $hausaufgaben, "id");
		$return.=tablename2statements("hausaufgabe_abschnitt", $hausaufgaben, "hausaufgabe");
		$return.=tablename2statements("hausaufgabe_vergessen", $hausaufgaben, "hausaufgabe");
    }
    
    if ($anstehende_berichtigungen!='')
        $return.=tablename2statements("berichtigung_vergessen", $anstehende_berichtigungen, "notenbeschreibung");
    
    // Unterricht
    $plan='';
    $unterrichtsstunden=mysql_query('SELECT * FROM plan, fach_klasse WHERE fach_klasse.user='.$_SESSION['user_id'].' AND fach_klasse.id=plan.fach_klasse AND plan.nachbereitung<>1 AND plan.vorbereitet=1 AND plan.datum>"'.date("Y-m-d",mktime(0,0,0,date("m"), date("d"), date("Y"))-14*24*60*60).'"');
    for ($i=0; $i<mysql_num_rows($unterrichtsstunden); $i++) {
        $plan[$i]=mysql_result($unterrichtsstunden, $i, "plan.id");
        $abschnitte_query=mysql_query("SELECT * FROM abschnittsplanung WHERE plan=".$plan[$i]);
        
        for ($n=0; $n<mysql_num_rows($abschnitte_query); $n++) {
            if (!idBereitsImArray($abschnitte, mysql_result($abschnitte_query, $n, "abschnittsplanung.abschnitt")))
               $abschnitte[]=mysql_result($abschnitte_query, $n, "abschnittsplanung.abschnitt");
            
            $aufgaben_query=mysql_query("SELECT * FROM aufgabe, aufgabe_abschnitt WHERE aufgabe.id=aufgabe_abschnitt.aufgabe AND aufgabe_abschnitt.abschnitt=".mysql_result($abschnitte_query, $n, "abschnittsplanung.abschnitt")." AND aufgabe.user=".$_SESSION['user_id']);
            for ($m=0; $m<mysql_num_rows($aufgaben_query); $m++)
                if (!idBereitsImArray($aufgaben, mysql_result($aufgaben_query, $m, "aufgabe.id")))
                    $aufgaben[]=mysql_result($aufgaben_query, $m, "aufgabe.id");
            
            $test_query=mysql_query("SELECT * FROM test, test_abschnitt WHERE test.id=test_abschnitt.test AND test_abschnitt.abschnitt=".mysql_result($abschnitte_query, $n, "abschnittsplanung.abschnitt")." AND test.user=".$_SESSION['user_id']);
            for ($m=0; $m<mysql_num_rows($test_query); $m++) {
                if (!idBereitsImArray($tests, mysql_result($test_query, $m, "test.id"))) {
					$tests[]=mysql_result($test_query, $m, "test.id");
					$aufgaben_query=mysql_query("SELECT * FROM aufgabe, test_aufgabe WHERE aufgabe.id=test_aufgabe.aufgabe AND test_aufgabe.test=".mysql_result($test_query, $m, "test.id"));
					for ($k=0; $k<mysql_num_rows($aufgaben_query); $k++)
						if (!idBereitsImArray($aufgaben, mysql_result($aufgaben_query, $k, "aufgabe.id")))
							$aufgaben[]=mysql_result($aufgaben_query, $k, "aufgabe.id");
				}
            }
            
            //$grafik_query=mysql_query("SELECT * FROM grafik, grafik_abschnitt WHERE grafik.id=grafik_abschnitt.grafik AND grafik_abschnitt.abschnitt=".mysql_result($abschnitte_query, $n, "abschnittsplanung.abschnitt"));
            // AENDERUNG: alle Grafiken reingenommen - Alternative: nur die Grafiken, die in sonstiges.text, aufgabe.text od. aufgabe.loesung drin stehen
            //$grafik_query=mysql_query("SELECT * FROM grafik");
            //for ($m=0; $m<mysql_num_rows($grafik_query); $m++)
            //    $grafiken[]=mysql_result($grafik_query, $m, "grafik.id");
            
            $material_query=mysql_query("SELECT * FROM material, material_abschnitt WHERE material.id=material_abschnitt.material AND material_abschnitt.abschnitt=".mysql_result($abschnitte_query, $n, "abschnittsplanung.abschnitt")." AND material.user=".$_SESSION['user_id']);
            for ($m=0; $m<mysql_num_rows($material_query); $m++)
        // TODO: für andere auch noch reduzieren
                if (!idBereitsImArray($materialien,mysql_result($material_query, $m, "material.id")))
                    $materialien[]=mysql_result($material_query, $m, "material.id");
            
            $tests_query;
        }
        // benoetigte Buecher
        for($m=0;$m<count($aufgaben);$m++) {
            //$grafik_query=mysql_query("SELECT * FROM grafik_aufgabe WHERE grafik_aufgabe.aufgabe=".$aufgaben[$m]);
            $buch_query=mysql_query("SELECT * FROM buch_aufgabe WHERE buch_aufgabe.aufgabe=".$aufgaben[$m]);
            //for ($k=0; $k<mysql_num_rows($grafik_query); $k++)
            //    if (!idBereitsImArray($grafiken, mysql_result($grafik_query, $k, "grafik_aufgabe.grafik")))
            //        $grafiken[]=mysql_result($grafik_query, $k, "grafik_aufgabe.grafik");
            for ($k=0; $k<mysql_num_rows($buch_query); $k++)
                if (!idBereitsImArray($buecher, mysql_result($buch_query, $k, "buch_aufgabe.buch")))
                    $buecher[]=mysql_result($buch_query, $k, "buch_aufgabe.buch");
        }
            
    }
    
    // grafiken aus Aufgaben heraussuchen
    for($m=0;$m<count($aufgaben);$m++) {
		$grafic_query=mysql_query("SELECT aufgabe.text, aufgabe.loesung FROM aufgabe WHERE aufgabe.id=".$aufgaben[$m]." AND aufgabe.user=".$_SESSION['user_id']);
		// wechsel: zunaechst aufgabe.text, dann aufgabe.loesung durchsuchen; 0: aufgabetext-grafik 1: aufgabetext-link 2: aufgabeloesung-grafik 3: aufgabeloesung-link
		for ($wechsel=0; $wechsel<4; $wechsel++) {
			if ($wechsel==0 or $wechsel==1)
				$einzeltext=mysql_result($grafic_query,0,"aufgabe.text");
			else
				$einzeltext=mysql_result($grafic_query,0,"aufgabe.loesung");
			if ($wechsel==0 or $wechsel==2)
				$search_after='grafic';
			else
				$search_after='file';
			
			// while string '[grafik:' (suchen) in text, loesung
			while (preg_match("#(.*)\[".$search_after.";(.*)\](.*)#is",$einzeltext)) {
				if ($wechsel==0 or $wechsel==2)
					$next_sign=';';
				else
					$next_sign=']';
				$searchtext=explode('['.$search_after.';', $einzeltext);
				$grafic_link_id=explode($next_sign, $searchtext[1]);
				if (!idBereitsImArray($grafiken, $grafic_link_id[0]) and ($wechsel==0 or $wechsel==2))
					$grafiken[]=$grafic_link_id[0];
				if (!idBereitsImArray($links, $grafic_link_id[0]) and ($wechsel==1 or $wechsel==3))
                    $links[]=$grafic_link_id[0];
				// erstes Element raus
				array_shift($searchtext);
				$einzeltext=implode('['.$search_after.';',$searchtext);
			}
		}
	}
	
    // grafiken, links aus sonstiges (Texten) heraussuchen
    for($m=0;$m<count($abschnitte);$m++) {
		$grafic_query=mysql_query("SELECT sonstiges.inhalt FROM sonstiges WHERE sonstiges.abschnitt=".$abschnitte[$m]);
		if (mysql_num_rows($grafic_query)>0)
		for ($scroll=0;$scroll<mysql_num_rows($grafic_query);$scroll++) {
			$einzeltext=mysql_result($grafic_query,$scroll,"sonstiges.inhalt");
			for ($wechsel=0; $wechsel<2; $wechsel++) {
				if ($wechsel==0)
					$search_after='grafic';
				else
					$search_after='file';
				
				// while string '[grafik:' (suchen)
				while (preg_match("#(.*)\[".$search_after.";(.*)\](.*)#is",$einzeltext)) {
					$searchtext=explode('['.$search_after.';', $einzeltext);
					if ($wechsel==0)
						$next_sign=';';
					else
						$next_sign=']';
					$grafic_link_id=explode($next_sign, $searchtext[1]);
					if (!idBereitsImArray($grafiken, $grafic_link_id[0]) and $wechsel==0)
						$grafiken[]=$grafic_link_id[0];
					if (!idBereitsImArray($links, $grafic_link_id[0]) and $wechsel==1)
						$links[]=$grafic_link_id[0];
					// erstes Element raus
					array_shift($searchtext);
					$einzeltext=implode('['.$search_after.';', $searchtext);
				}
			}
		}
	}
	
	if (isset($plan))
		$return.=tablename2statements("abschnittsplanung", $plan, "plan");
	if (isset($abschnitte)) {
	    $return.=tablename2statements("abschnitt", $abschnitte, "id");
		$return.=tablename2statements("material_abschnitt", $abschnitte, "abschnitt");
		$return.=tablename2statements("material", $materialien, "id");
		$return.=tablename2statements("test_abschnitt", $abschnitte, "abschnitt");
		$return.=tablename2statements("test_aufgabe", $tests, "test");
		$return.=tablename2statements("test", $tests, "id");
		$return.=tablename2statements("ueberschrift", $abschnitte, "abschnitt");
		$return.=tablename2statements("sonstiges", $abschnitte, "abschnitt");
	}
    if (isset($aufgaben)) {
        $return.=tablename2statements("aufgabe_abschnitt", $abschnitte, "abschnitt");
        $return.=tablename2statements("aufgabe", $aufgaben, "id");
        $return.=tablename2statements("buch_aufgabe", $aufgaben, "aufgabe");
        $return.=tablename2statements("buch", $buecher, "id");
    }
    //$return.=tablename2statements("link_abschnitt", $abschnitte, "abschnitt");
	if (isset($links))
	    $return.=tablename2statements("link", $links, "id");
    //$return.=tablename2statements("grafik_abschnitt", $abschnitte, "abschnitt");
    //$return.=tablename2statements("grafik_aufgabe", $grafiken, "grafik");
	if (isset($grafiken))
	    $return.=tablename2statements("grafik", $grafiken, "id");
    
    $export_datei='';
    $db=new db;
    $pfad="../"; // von appcache-manifest-Datei aus
    for ($m=0;$m<count($links);$m++) {
        $my_file=$db->link_id($links[$m]);
		$export_datei.=$pfad.$my_file["url_decode"]."\n";
	}
    for ($m=0;$m<count($grafiken);$m++) {
        $my_file=$db->grafik($grafiken[$m]);
		$export_datei.=$pfad.$my_file["url_decode"]."\n";
	}
	/*
	$Datei=$pfad."offline/appcache_files.txt";
	
	@chmod ($Datei, 0777);
	$dateihandle = fopen($Datei,"w");
	
	fputs($dateihandle, $export_datei);
	fclose($dateihandle);
	@chmod ($Datei, 0755); //700
	clearstatcache();
	*/
	
	
	$lernbereichs_query=mysql_query("SELECT lernbereich.id FROM lernbereich, lp_user WHERE lp_user.user=".$_SESSION['user_id']." AND lp_user.lehrplan=lernbereich.lehrplan");
    for ($i=0; $i<mysql_num_rows($lernbereichs_query); $i++) {
        $lernbereiche[$i]=mysql_result($lernbereichs_query,$i,"lernbereich.id");
    }
	$lehrplan_query=mysql_query("SELECT lehrplan.id FROM lehrplan, lp_user WHERE lp_user.user=".$_SESSION['user_id']." AND lp_user.lehrplan=lehrplan.id");
    for ($i=0; $i<mysql_num_rows($lehrplan_query); $i++) {
        $lehrplaene[$i]=mysql_result($lehrplan_query,$i,"lehrplan.id");
    }
	$notentyp_query=mysql_query("SELECT notentypen.id FROM notentypen WHERE notentypen.schule=".$schule." OR notentypen.id<11");
    for ($i=0; $i<mysql_num_rows($notentyp_query); $i++) {
        $notentypen[$i]=mysql_result($notentyp_query,$i,"notentypen.id");
    }
	
	if (isset($lernbereiche))
		$return.=tablename2statements("lernbereich", $lernbereiche, "id");
	if (isset($lehrplaene))
	    $return.=tablename2statements("lehrplan", $lehrplaene, "id");
    $return.=tablename2statements("schulart", null, "id");
    $return.=tablename2statements("notentypen", $notentypen, "id");
    
    $sitzplan='';
    $sitzplaene_query=mysql_query('SELECT * FROM sitzplan WHERE sitzplan.aktiv=1 AND (sitzplan.id=1 OR sitzplan.schule='.$schule.')');
    for ($i=0; $i<mysql_num_rows($sitzplaene_query); $i++)
        $sitzplan[]=mysql_result($sitzplaene_query, $i, "sitzplan.id");
    
	if (isset($sitzplan)) {
	    $return.=tablename2statements("sitzplan", $sitzplan, "id");
		$return.=tablename2statements("sitzplan_objekt", $sitzplan, "sitzplan");
	}
    
    $sitzplan_klasse='';
    $sitzplanposition='';
    $klassen_string='sitzplan_klasse.klasse='.implode(" OR sitzplan_klasse.klasse=", $klassen);
    
    $sitzplan_klasse_query=mysql_query('SELECT * FROM sitzplan_klasse WHERE '.$klassen_string);
    for ($i=0; $i<mysql_num_rows($sitzplan_klasse_query); $i++) {
        $sitzplan_klasse[]=mysql_result($sitzplan_klasse_query, $i, "sitzplan_klasse.id");
    }
	if (isset($sitzplan_klasse)) {
		$return.=tablename2statements("sitzplan_klasse", $sitzplan_klasse, "id");
		$return.=tablename2statements("sitzplan_platz", $sitzplan_klasse, "sitzplan_klasse");
	}
	
//[x] fach-klasse (aktive), gruppe, faecher
// [x] klasse, schule, 
// [x] schueler, schueler_fehlt
// [x] sitzplan, sitzplan_klasse, sitzplan_objekt, sitzplan_platz (aktive)
// [] plan, plan_auswertung, plan_dauer
// [x] abschnitt
// [x] abschnittsplanung
// [x] aufgabe, aufgabe_abschnitt
// [x] grafik, grafik_abschnitt, grafik_aufgabe
// [x] buch, buch_aufgabe
// [x] link, link_abschnitt
// [x] material, material_abschnitt
// [x] sonstiges
// [x] test, test_abschnitt, test_aufgabe
// [x] ueberschrift
// [x] berichtigung_vergessen, notenbeschreibung
// [x] hausaufgabe, hausaufgabe_abschnitt, hausaufgabe_vergessen
// verwarnungen
// [x] benutzer (druckansicht)
//handlungsmuster, medium, phase, sozialform
    //$return.= '';
//[x] noten
//notengruppe, [] notentypen
//
//aufsicht, stundenplan, raum, stundenzeiten, stundenzeiten_beschreibung
//
//kollege

//[x] konferenz
//
//notiz
//
//block, block_abschnitt
//lehrplan, lernbereich
//
//[x] schuljahr
//--
//elternkontakt
//bewegliche_feiertage, ferien, feste_feiertage, woche_ab
//bewertung_note, notenberechnung
//note_aufgabe
//brief
//schulart, schule_schulart
//thema, themenzuordnung
//buch_klassenstufe

$return.='
return stats;
}';
	return array("javascript"=>$return, "appcache_files"=>$export_datei);
}
?>
