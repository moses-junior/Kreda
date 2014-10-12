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
?>
    <style>
        div.tab, div.tab_2, div.tab_3 {display: none;}
        div.navigation, div.navigation_2, div.navigation_3 { /*border-top-width: 1px;
            -moz-border-top-right-radius: 7px; -webkit-border-top-right-radius: 7px; border-top-right-radius: 7px;
            -moz-border-top-left-radius: 7px; -webkit-border-top-left-radius: 7px; border-top-left-radius: 7px;*/
            margin-top: -53px; }
        
        img.programm { top:21px; }
 	</style>
<?php
    // wenn in der GET-Adresse eine neue FK ausgewählt werden soll, wird sie hier in die DB geschrieben
    if (isset($_GET["new_sc"]))
		db_conn_and_sql("UPDATE benutzer SET letzte_fachklasse=".injaway($_GET["new_sc"])." WHERE id=".$_SESSION['user_id']);
    
    if (isset($_GET["new_school"]))
		db_conn_and_sql("UPDATE benutzer SET letzte_schule=".injaway($_GET["new_school"])." WHERE id=".$_SESSION['user_id']);
    
    $my_user = new user();
    
    $subject_classes=new subject_classes($aktuelles_jahr);
    
    // aktuelle Unterrichtsstunde herausfinden oder die naechste zu haltende Stunde der aktuellen Fachklasse heraussuchen
    if ($subject_classes->cont[$subject_classes->active]["id"]>0) {
		$aktueller_plan=db_conn_and_sql("SELECT plan.id FROM plan WHERE plan.fach_klasse=".$subject_classes->cont[$subject_classes->active]["id"]." AND plan.datum>".$CURDATE." ORDER BY plan.datum LIMIT 1");
		$aktueller_plan_row=sql_fetch_assoc($aktueller_plan);
	}
    if (sql_num_rows($aktueller_plan)>=1) {
		$next_lesson_id=$next_lesson_id["id"];
	}
    if ($_GET["plan"]>0) { //  and !isset($_GET["new_sc"]) ist glaub ich ueberfluessig
        $link_of_next_lesson='index.php?tab=stundenplanung&amp;auswahl=fkplan&amp;fk='.$_GET["fk"].'&amp;plan='.$_GET["plan"];
        $next_lesson_id=$_GET["plan"];
    }
    else
        $link_of_next_lesson='index.php?tab=stundenplanung&amp;auswahl=fkplan&amp;fk='.$subject_classes->cont[$subject_classes->active]["id"].'&amp;plan='.@sql_result($aktueller_plan,0,"plan.id");
    if (sql_num_rows($aktueller_plan)==0)
		$ustd_planung_deaktivieren=true;
	else
		$ustd_planung_deaktivieren=false;


    $menue_punkte=array(
                "start"        =>array("name"=>"Startseite", "pic"=>"startseite.png", "link"=>"index.php"),
                //array("name"=>"Drucken", "pic"=>"drucken.png", "link"=>"index.php", "inactive"=>1),
                //array("name"=>"DB Exportieren", "pic"=>"backup.png", "link"=>"index.php?tab=einstellungen&amp;aufgabe=backup"),
                //array("name"=>"DB Importieren (Lokal)", "pic"=>"backup.png", "link"=>"index.php?tab=einstellungen&amp;aufgabe=restore", "onclick"=>"if (!confirm('Jegliche Daten werden mit dem Backup &uuml;berschrieben. Wollen Sie das wirklich?')) return false;"),
                //array("name"=>"DB Importieren (FTP)", "pic"=>"backup.png", "link"=>"index.php?tab=einstellungen&amp;aufgabe=restore_ftp", "onclick"=>"if (!confirm('Jegliche Daten werden mit dem Backup &uuml;berschrieben. Wollen Sie das wirklich?')) return false;"),
                "benutzereinstellungen"=>array("name"=>"Benutzereinstellungen", "pic"=>"einstellungen.png", "link"=>"index.php?tab=einstellungen&amp;auswahl=allgemein"),
                "ferien"       =>array("name"=>"Ferien / freie Tage", "pic"=>"ferien.png", "link"=>"index.php?tab=einstellungen&amp;auswahl=schuljahr&amp;jahr=".$my_user->my["aktuelles_schuljahr"]),
				"schuljahr"    =>array("name"=>"feste Feiertage", "pic"=>"ferien.png", "link"=>"index.php?tab=einstellungen&amp;auswahl=schuljahr&amp;jahr=allgemein"),
                "faecher"      =>array("name"=>"F&auml;cher", "pic"=>"fach.png", "link"=>"index.php?tab=einstellungen&amp;auswahl=faecher"),
                "schulen"      =>array("name"=>"Schulen", "pic"=>"schule.png", "link"=>"index.php?tab=einstellungen&amp;auswahl=schulen&amp;erstellen=raum"),
                "zensuren_einstellungen"=>array("name"=>"Zensuren / Bewertung", "pic"=>"zensureneinstellungen.png", "link"=>"index.php?tab=einstellungen&amp;auswahl=noten_bew"),
                "sitzordnungen"=>array("name"=>"Sitzordnungen", "pic"=>"sitzplan.png", "link"=>"index.php?tab=einstellungen&amp;auswahl=sitzplan"),
                "programm"     =>array("name"=>"Programm", "pic"=>"programm.png", "link"=>"index.php?tab=einstellungen&amp;auswahl=programm"),
                "logout"       =>array("name"=>$_SESSION['user_name']." abmelden", "pic"=>"fenster_schliessen.png", "link"=>"login/index.php?logout"),

                "stundenplan"     =>array("name"=>"Stundenplan", "pic"=>"stundenplan.png", "link"=>"index.php?tab=stundenplan&amp;auswahl=stundenplan"),
                "kalender"        =>array("name"=>"Kalender", "pic"=>"kalender.png", "link"=>"index.php?tab=stundenplan&amp;auswahl=kalender"),
                "dienstberatungen"=>array("name"=>"Dienstberatung", "pic"=>"konferenz.png", "link"=>"index.php?tab=stundenplan&amp;auswahl=konferenz"),
                "kollegen"        =>array("name"=>"Kollegen", "pic"=>"kollegen.png", "link"=>"index.php?tab=stundenplan&amp;auswahl=kollegen"),

                "kl_uebersicht"   =>array("name"=>"Klassen&uuml;bersicht", "pic"=>"klasse.png", "link"=>"index.php?tab=klassen&zweit=alle"),
                "fk_schueler"     =>array("name"=>"Sch&uuml;ler", "pic"=>"schueler.png", "link"=>"index.php?tab=klassen&amp;option=schueleruebersicht"),
                "ha_statistik" =>array("name"=>"Hausaufgabenstatistik", "pic"=>"statistik.png", "link"=>"index.php?tab=klassen&amp;option=statistik"),
                "fach_klassen" =>array("name"=>"Fach-Klassen", "pic"=>"kurs.png", "link"=>"index.php?tab=klassen&amp;option=fk"),
                "sitzplaene"   =>array("name"=>"Sitzpl&auml;ne", "pic"=>"sitzplan.png", "link"=>"index.php?tab=klassen&amp;option=sitzplan"),
                "fehlzeiten"   =>array("name"=>"Fehlzeiten", "pic"=>"fehlzeit.png", "link"=>"index.php?tab=klassen&amp;option=fehlzeiten"),
                "elternabend"  =>array("name"=>"Elternabend", "pic"=>"elternabend.png", "link"=>"index.php?tab=klassen&amp;option=elternabend"),
                "schuelerliste"=>array("name"=>"Sch&uuml;lerliste", "pic"=>"schuelerliste.png", "link"=>"index.php?tab=klassen&amp;option=schueleruebersicht&amp;option_2=schuelerliste"),
                "klassenbuch"  =>array("name"=>"Klassenbuchansicht", "pic"=>"klassenbuch.png", "link"=>"formular/schueler_druckansicht.php?fach_klasse=".$subject_classes->cont[$subject_classes->active]["id"], "newwin"=>1),

                "noten_uebersicht"=>array("name"=>"Zensurentabelle", "pic"=>"zensurentabelle.png", "link"=>"index.php?tab=noten&amp;auswahl=".$subject_classes->cont[$subject_classes->active]["id"]),
                "berechnung"      =>array("name"=>"Berechnung", "pic"=>"berechnung.png", "link"=>"index.php?tab=noten&amp;eintragen=true&amp;auswahl=".$subject_classes->cont[$subject_classes->active]["id"]),
                "jahresuebersicht"=>array("name"=>"Jahres&uuml;bersicht", "pic"=>"zensurenuebersicht.png", "link"=>"index.php?tab=noten&amp;option=jahresuebersicht"),
                "le_stichtagsnote"=>array("name"=>"Stichtagsnoten", "pic"=>"stichtagsnote.png", "link"=>"index.php?tab=noten&amp;option=stichtagsnote"),
                "le_kopfnote"	  =>array("name"=>"Kopfnoten", "pic"=>"kopfnote.png", "link"=>"index.php?tab=noten&amp;option=kopfnote"),
                
                "material"=>array("name"=>"Material", "pic"=>"material.png", "link"=>"index.php?tab=material", "sub"=>array(
                    "themen"  =>array("name"=>"Themen", "pic"=>"thema.png", "link"=>"index.php?tab=material&amp;auswahl=themen"),
                    "aufgaben"=>array("name"=>"Aufgaben", "pic"=>"aufgaben.png", "link"=>"index.php?tab=material&amp;auswahl=aufgaben"),
                    "tests"   =>array("name"=>"Tests", "pic"=>"test.png", "link"=>"index.php?tab=material&amp;auswahl=test"),
                    "dateien" =>array("name"=>"Arbeitsbl&auml;tter / Dateien", "pic"=>"arbeitsblatt.png", "link"=>"index.php?tab=material&amp;auswahl=link"),
                    "buecher" =>array("name"=>"B&uuml;cher", "pic"=>"buch.png", "link"=>"index.php?tab=material&amp;auswahl=buch"),
                    "grafiken"=>array("name"=>"Grafiken", "pic"=>"grafik.png", "link"=>"index.php?tab=material&amp;auswahl=grafik"),
                    "material"=>array("name"=>"sonstiges Material", "pic"=>"sonstiges_material.png", "link"=>"index.php?tab=material&amp;auswahl=sonstiges"),
                    "suche"   =>array("name"=>"Materialsuche", "pic"=>"suchen.png", "link"=>"index.php?tab=material&amp;auswahl=suche", "inactive"=>1)
                )),
                "lehrplaene"     =>array("name"=>"Lehrpl&auml;ne / Fundus", "pic"=>"fundus.png", "link"=>"index.php?tab=stundenplanung&amp;auswahl=lernbereiche&amp;aktion=alle"),
                "ustd_uebersicht"     =>array("name"=>"&Uuml;bersicht", "pic"=>"vorbereitet_nicht.png", "link"=>"index.php?tab=stundenplanung&amp;auswahl=fkplan"),
                "stoffverteilung"=>array("name"=>"Stoffverteilung", "pic"=>"ok.png", "link"=>"index.php?tab=stundenplanung&amp;auswahl=fkplan&amp;fk=".$subject_classes->cont[$subject_classes->active]["id"]),
                "lb_uebersicht"  =>array("name"=>"Lernbereichs&uuml;bersicht", "link"=>"formular/lb_uebersicht.php?fk=".$subject_classes->cont[$subject_classes->active]["id"]),
                "ustd"           =>array("name"=>"Ustd.-Planung", "pic"=>"einzelstunde.png", "link"=>$link_of_next_lesson."&amp;ansicht=planung", "sub"=>array(
                    "bearbeiten"    =>array("name"=>"Bearbeiten", "pic"=>"edit.png", "link"=>$link_of_next_lesson."&amp;ansicht=planung"),
                    "druckansicht"  =>array("name"=>"Druckansicht", "pic"=>"drucken.png", "link"=>$link_of_next_lesson."&amp;ansicht=druck"),
                    "zweitansicht"  =>array("name"=>"Zweitansicht", "link"=>$link_of_next_lesson."&amp;ansicht=zweitansicht"),
                    "durchfuehrung" =>array("name"=>"Durchf&uuml;hransicht", "pic"=>"durchfuehrung.png", "link"=>"javascript:fenster('".$pfad."lessons/durchfuehransicht_lehrer.php?plan=".$next_lesson_id."','Durchf%C3%BChransicht');"),
                    "schuelerhefter"=>array("name"=>"Sch&uuml;lerhefter", "pic"=>"hefter.png", "link"=>$link_of_next_lesson."&amp;ansicht=hefter")
                )),
                "ha_uebersicht"  =>array("name"=>"HA-&Uuml;bersicht", "pic"=>"hausaufgaben.png", "link"=>"index.php?tab=stundenplanung&amp;auswahl=hausaufgaben&amp;ha_fk=aktuell"),
                "unt_statistik"  =>array("name"=>"Unterrichtsstatistik", "pic"=>"statistik_auswertung.png", "link"=>"index.php?tab=stundenplanung&amp;auswahl=planstatistik"),
                
                "erstellen"   =>array("name"=>"erstellen", "pic"=>"neu.png", "link"=>"index.php?tab=klassen&amp;zweit=alle"),

                "kontakte"   =>array("name"=>"Kontakte", "pic"=>"person.png", "link"=>"index.php?tab=kontakte"),
                "schueler"   =>array("name"=>"Sch&uuml;ler", "pic"=>"schueler.png", "link"=>"index.php?tab=schueler"),
                "eltern"     =>array("name"=>"Eltern", "pic"=>"elternkontakt.png", "link"=>"index.php?tab=eltern"),

                "angestellte"=>array("name"=>"Angestellte", "pic"=>"kollegen.png", "link"=>"index.php?tab=angestellte"),
                "lehrauftrag"=>array("name"=>"Lehrauftr&auml;ge", "pic"=>"fach_klasse.png", "link"=>"index.php?tab=lav"),
                "stichtag"   =>array("name"=>"Stichtagsnotenauftrag", "pic"=>"stichtagsnote.png", "link"=>"index.php?tab=stichnoten"),
                "kopfnote"   =>array("name"=>"Kopfnotenauftrag", "pic"=>"kopfnote.png", "link"=>"index.php?tab=kopfnoten"),

                "ueber"     =>array("name"=>"&Uuml;ber...", "pic"=>"hinweis.png", "link"=>"info.php", "newwin"=>1),
                "direkt"    =>array("name"=>"Direkthilfe", "link"=>"", "inactive"=>1),
                "starthilfe"=>array("name"=>"Starthilfe", "pic"=>"hilfe.png", "link"=>"formular/hilfe.php?inhalt=start", "newwin"=>1),
                "kreda-wiki"=>array("name"=>"Kreda-Wiki", "pic"=>"buch.png", "link"=>"http://moses.kilu.de/kreda-wiki", "newwin"=>1)
        );

    $menubar=array(
            "start"=>array("name"=>"Start", "pic"=>"startseite.png", "link"=>"index.php", "sub"=>array(
                "start"=>$menue_punkte["start"],
                "benutzereinstellungen"=>$menue_punkte["benutzereinstellungen"],
                "logout"=>$menue_punkte["logout"]
            )),
            "pinnwand"=>array("name"=>"Pinnwand", "pic"=>"pinwand.png", "link"=>"index.php?tab=stundenplan&amp;auswahl=stundenplan", "sub"=>array(
                "stundenplan"=>$menue_punkte["stundenplan"],
                "kalender"=>$menue_punkte["kalender"],
                "dienstberatungen"=>$menue_punkte["dienstberatungen"],
                "kollegen"=>$menue_punkte["kollegen"]
            )),
            "klassen"=>array("name"=>"Klassen", "pic"=>"klasse.png", "link"=>"index.php?tab=klassen", "sub"=>array(
                "kl_uebersicht"=>$menue_punkte["kl_uebersicht"],
                "fk_schueler"=>$menue_punkte["fk_schueler"],
                "ha_statistik"=>$menue_punkte["ha_statistik"],
                "fach_klassen"=>$menue_punkte["fach_klassen"],
                "sitzplaene"=>$menue_punkte["sitzplaene"],
                "fehlzeiten"=>$menue_punkte["fehlzeiten"],
                "elternabend"=>$menue_punkte["elternabend"],
                "schuelerliste"=>$menue_punkte["schuelerliste"],
                "klassenbuch"=>$menue_punkte["klassenbuch"]
            )),
            "zensuren"=>array("name"=>"Zensuren", "pic"=>"zensuren.png", "link"=>"index.php?tab=noten", "sub"=>array(
                "noten_uebersicht"=>$menue_punkte["noten_uebersicht"],
                "berechnung"=>$menue_punkte["berechnung"],
                "jahresuebersicht"=>$menue_punkte["jahresuebersicht"],
                "le_stichtagsnote"=>$menue_punkte["le_stichtagsnote"],
                "le_kopfnote"=>$menue_punkte["le_kopfnote"],
                "zensuren_einstellungen"=>$menue_punkte["zensuren_einstellungen"],
                "stichtagsnoten"=>$menue_punkte["stichtag"],
                "kopfnoten"=>$menue_punkte["kopfnote"],
                //zeugnis
            )),
            "unterricht"=>array("name"=>"Unterricht", "pic"=>"unterricht.png", "link"=>"index.php?tab=stundenplanung&amp;auswahl=fkplan", "sub"=>array(
                "material"=>$menue_punkte["material"],
                "lehrplaene"=>$menue_punkte["lehrplaene"],
                "ustd_uebersicht"=>$menue_punkte["ustd_uebersicht"],
                "stoffverteilung"=>$menue_punkte["stoffverteilung"],
                "lb_uebersicht"=>$menue_punkte["lb_uebersicht"],
                "ustd"=>$menue_punkte["ustd"],
                "ha_uebersicht"=>$menue_punkte["ha_uebersicht"],
                "unt_statistik"=>$menue_punkte["unt_statistik"]
            )),
            "grunddaten"=>array("name"=>"Grunddaten", "link"=>"", "sub"=>array(
                "schulen"=>$menue_punkte["schulen"],
                // schulen (abgang/aufnahme)
                // firmen
                "ferien"=>$menue_punkte["ferien"],
				"schuljahr"=>$menue_punkte["schuljahr"],
                "faecher"=>$menue_punkte["faecher"],
                "sitzordnungen"=>$menue_punkte["sitzordnungen"],
                "programm"=>$menue_punkte["programm"],
            )),
            "verwaltung"=>array("name"=>"Verwaltung", "link"=>"", "sub"=>array(
				"klassen"=>$menue_punkte["kl_uebersicht"],
                "schueler"=>$menue_punkte["schueler"],
				//"stammgruppen"=>$menue_punkte["stammgruppen"],
                "lehrauftrag"=>$menue_punkte["lehrauftrag"],
                "fehlzeiten"=>$menue_punkte["fehlzeiten"],
                "eltern"=>$menue_punkte["eltern"],
                "schuelerliste"=>$menue_punkte["schuelerliste"],
                "kontakte"=>$menue_punkte["kontakte"],
                "angestellte"=>$menue_punkte["angestellte"],
            )),
            "fach_klassen"=>array("name"=>"Ausgew&auml;hlte Fach-Klasse", "pic"=>"kurs.png", "link"=>"", "sub"=>array(
                "erstellen"=>$menue_punkte["erstellen"]
            )),
            "hilfe"=>array("name"=>"Hilfe", "link"=>"", "sub"=>array(
                "ueber"=>$menue_punkte["ueber"],
                "direkt"=>$menue_punkte["direkt"],
                "starthilfe"=>$menue_punkte["starthilfe"],
                "kreda-wiki"=>$menue_punkte["kreda-wiki"]
            ))
        );
    
    // Anzeige-Einschraenkungen des Benutzers
    if (!$my_user->my["sitzplan"]) {
		//unset($menubar["start"]["sub"]["einstellungen"]["sub"]["sitzordnungen"]);
		unset($menubar["klassen"]["sub"]["sitzplaene"]);
	}
    if (!$my_user->my["schuljahresplanung"]) {
		unset($menubar["pinnwand"]["sub"]["stundenplan"]);
		unset($menubar["unterricht"]["sub"]["stoffverteilung"]);
		unset($menubar["unterricht"]["sub"]["lb_uebersicht"]);
		unset($menubar["unterricht"]["sub"]["uebersicht"]);
		unset($menubar["unterricht"]["sub"]["ustd"]);
	}
    if (!$my_user->my["dienstberatungen"]) {
		unset($menubar["pinnwand"]["sub"]["dienstberatungen"]);
		unset($menubar["klassen"]["sub"]["elternabend"]);
	}
    if (!$my_user->my["statistiken"]) {
		unset($menubar["klassen"]["sub"]["ha_statistik"]);
		unset($menubar["klassen"]["sub"]["fehlzeiten"]);
		unset($menubar["unterricht"]["sub"]["ustd_statistik"]);
		unset($menubar["zensuren"]["sub"]["jahresuebersicht"]);
	}
    if (!$my_user->my["ustd_planung"]) {
		unset($menubar["unterricht"]);
	}
    
    // Anzeige-Einschraenkung aufgrund Benutzerstatus
    if (!$my_user->my["admin"]) {
		unset($menubar["grunddaten"]["sub"]["programm"]);
		
		if (!$my_user->my["verwaltung"]) {
			// schulen, firmen
			unset($menubar["verwaltung"]["sub"]["fehlzeiten"]);
			unset($menubar["verwaltung"]["sub"]["schuelerliste"]);
		}
		if (!$my_user->my["schulleitung"]) {
			unset($menubar["grunddaten"]["sub"]["schulen"]);
			unset($menubar["grunddaten"]["sub"]["sitzordnungen"]);
			unset($menubar["verwaltung"]["sub"]["lehrauftrag"]);
			unset($menubar["verwaltung"]["sub"]["angestellte"]);
			unset($menubar["zensuren"]["sub"]["zensuren_einstellungen"]);
			unset($menubar["zensuren"]["sub"]["stichtagsnoten"]);
			unset($menubar["zensuren"]["sub"]["kopfnoten"]);
		}
		if (!$my_user->my["schulleitung"] and !$my_user->my["verwaltung"]) {
			unset($menubar["grunddaten"]);
			unset($menubar["verwaltung"]);
		}
	}
	
	// Verwaltungsanzeigen
	if (!$my_user->my["lehrer"] and !$my_user->my["schulleitung"]) {
		unset($menubar["pinnwand"]);
		unset($menubar["klassen"]);
		$menubar["klassen"]["sub"]["schueler"]["inactive"]=1;
		$menubar["klassen"]["sub"]["fehlzeiten"]["inactive"]=1;
		unset($menubar["zensuren"]);
		unset($menubar["unterricht"]);
		unset($menubar["fach_klassen"]["sub"]);
	}
    
	// prueft, ob zuvor schonmal ein new_sc in der GET-Adresse steht (wenn ja, vermeidung von mehrfachen new_sc)
    $serverURI=$_SERVER['REQUEST_URI'];
    if (stripos ($serverURI, "new_sc="))
		$serverURI = substr_replace($serverURI,"",stripos ($serverURI, "new_sc=")-1);
    if (stripos ($serverURI, "new_school="))
		$serverURI = substr_replace($serverURI,"",stripos ($serverURI, "new_school=")-1);
        
    $schule_i=0;
    if ($my_user->my["admin"]) { // Admin darf alle Schulen verwalten
		$schulwaehler=db_conn_and_sql("SELECT schule.* FROM schule WHERE aktiv=1");
		while ($schuleintrag=sql_fetch_assoc($schulwaehler)) {
			$menubar["fach_klassen"]["sub"][$schule_i]=array("name"=>$schuleintrag["kuerzel"]);
			if ($my_user->my["letzte_schule"]==$schuleintrag["id"])
				$selected_school_short=$schuleintrag["kuerzel"];
			// prueft, ob die GET-Adresse zuvor mit ? oder & gekennzeichnet sein muss
			if (stripos ($serverURI, "?"))
				$menubar["fach_klassen"]["sub"][$schule_i]["link"] = $serverURI."&amp;new_school=".$schuleintrag["id"];
			else
				$menubar["fach_klassen"]["sub"][$schule_i]["link"] = $serverURI."?new_school=".$schuleintrag["id"];
			$schule_i++;
		}
	}
	else {
		// Schulleiter und Verwaltung benoetigen schulauswahlrechte
		$schulwaehler=db_conn_and_sql("SELECT DISTINCT schule.* FROM schule, schule_user
			WHERE schule.id=schule_user.schule
				AND schule_user.user=".$_SESSION["user_id"]."
				AND (schule_user.usertyp=4 OR schule_user.usertyp=5)");
		while ($schuleintrag=sql_fetch_assoc($schulwaehler)) {
			$menubar["fach_klassen"]["sub"][$schule_i]=array("name"=>$schuleintrag["kuerzel"]);
			if ($my_user->my["letzte_schule"]==$schuleintrag["id"])
				$selected_school_short=$schuleintrag["kuerzel"];
			// prueft, ob die GET-Adresse zuvor mit ? oder & gekennzeichnet sein muss
			if (stripos ($serverURI, "?"))
				$menubar["fach_klassen"]["sub"][$schule_i]["link"] = $serverURI."&amp;new_school=".$schuleintrag["id"];
			else
				$menubar["fach_klassen"]["sub"][$schule_i]["link"] = $serverURI."?new_school=".$schuleintrag["id"];
			$schule_i++;
		}
	}
	
    // TODO nur Lehrer haben Fach-Klassen (-> unset)
    
    // Stoffverteilung und lernbereichsübersicht deaktivieren, wenn kein Stundenplaneintrag existiert
    if ($subject_classes->cont[$subject_classes->active]["id"]>0 and db_conn_and_sql("SELECT * FROM stundenplan WHERE stundenplan.fach_klasse=".$subject_classes->cont[$subject_classes->active]["id"]." AND stundenplan.schuljahr=".$aktuelles_jahr)->num_rows<1) {
		$menubar["unterricht"]["sub"]["lb_uebersicht"]["inactive"]=true;
		$menubar["unterricht"]["sub"]["stoffverteilung"]["inactive"]=true;
	}
    
	// prueft, ob ein lehrauftrag zugeordnet wurde - falls nicht, werden Zensuren deaktiviert
	if (!$subject_classes->cont[$subject_classes->active]["lehrauftrag"]) {
		$menubar["zensuren"]["sub"]["noten_uebersicht"]["inactive"]=true;
		$menubar["zensuren"]["sub"]["berechnung"]["inactive"]=true;
	}
	
    // wenn keine ustd geplant ist, soll dies deaktiviert werden
    if ($ustd_planung_deaktivieren)
		$menubar["unterricht"]["sub"]["ustd"]["inactive"]=true;
    
    // Fach-Klassen-Waehler fuellen
    // nicht zugeordnete Lehrauftraege
    $lehrauftraege_result=db_conn_and_sql("SELECT lehrauftrag.*, faecher.kuerzel
		FROM lehrauftrag, faecher, klasse
		WHERE klasse.id=lehrauftrag.klasse
			AND lehrauftrag.fach=faecher.id
			AND lehrauftrag.schuljahr=".$aktuelles_jahr."
			AND lehrauftrag.user=".$_SESSION["user_id"]."
			AND lehrauftrag.fach_klasse IS NULL
		ORDER BY lehrauftrag.fach, klasse.einschuljahr DESC, klasse.endung");
    $lehrauftraege=array();
    while ($la=sql_fetch_assoc($lehrauftraege_result))
		$lehrauftraege[]=array("name"=>$school_classes->nach_ids[$la["klasse"]]["name"]." ".$la["kuerzel"], "klasse_id"=>$la["klasse"], "lehrauftrag_nr"=>$la["lfd_nr"], "fach_id"=>$la["fach"]);
	
    for ($k=0; $k<count($lehrauftraege); $k++) {
       $menubar["fach_klassen"]["sub"][$k+$schule_i]=array("name"=>'<span class="fk" style="color: #555;">'.$lehrauftraege[$k]["name"].'</span>', "link"=>$pfad."index.php?tab=klassen&amp;option=fk&amp;auswahl=".$lehrauftraege[$k]["klasse_id"]."&amp;lehrauftrag=".$lehrauftraege[$k]["lehrauftrag_nr"]."&amp;fach=".$lehrauftraege[$k]["fach_id"]);
    }
    
    // vorhandene Fach-Klasse-Kombinationen
    for ($i=0; $i<$subject_classes->length(); $i++) {
        $menubar["fach_klassen"]["sub"][$i+$k+$schule_i]=array("name"=>'<span class="fk" style="border-radius: 3px; background-color: #'.$subject_classes->cont[$i]["farbe"].';">'.$subject_classes->cont[$i]["name"].'</span>'); //, "link"=>"basic/select_subcla.php?new_sc=".$subject_classes->cont[$i]["id"], "newwin"=>1
        // wenn kein Lehrauftrag besteht und Zensuren ausgewaehlt sind, wird die Fach-Klasse inaktiv gesetzt
        if ($_GET["tab"]=="noten" and injaway($_GET["auswahl"])>0 and !$subject_classes->cont[$i]["lehrauftrag"])
			$menubar["fach_klassen"]["sub"][$i+$k+$schule_i]["inactive"]=true;
        
        // prueft, ob die GET-Adresse zuvor mit ? oder & gekennzeichnet sein muss
        // FIXME: (evtl.?) & in $serverURI bleibt & und wird nicht in &amp; umgewandelt
        if (stripos ($serverURI, "?"))
			$menubar["fach_klassen"]["sub"][$i+$k+$schule_i]["link"] = $serverURI."&amp;new_sc=".$subject_classes->cont[$i]["id"];
		else
			$menubar["fach_klassen"]["sub"][$i+$k+$schule_i]["link"] = $serverURI."?new_sc=".$subject_classes->cont[$i]["id"];
		
		// prueft, ob man sich in einer Ustd.Planung befindet - wenn ja wird das Ziel ueberschrieben
		if ($_GET["auswahl"]=="fkplan" and isset($_GET["ansicht"]) and isset($_GET["plan"]))
			$menubar["fach_klassen"]["sub"][$i+$k+$schule_i]["link"] = "index.php?tab=stundenplanung&amp;auswahl=fkplan&amp;new_sc=".$subject_classes->cont[$i]["id"];
    }
    
    if ($subject_classes->length()>0)
		$menubar["fach_klassen"]["name"]='<span class="fk" style="border-radius: 3px; background-color: #'.$subject_classes->cont[$subject_classes->active]["farbe"].';">'.$subject_classes->cont[$subject_classes->active]["name"].'</span>';
	else
		$menubar["fach_klassen"]["name"]='<span class="fk" style="border-radius: 3px; background-color: #CFA;">'.$selected_school_short.'</span>';
    
?>
<div class="nicht_drucken">
	<!--<div id="menbar_workaround" style="height: 89px"></div>-->
	<div class="mlmenu horizontal bluewhite arrow inaccessible delay">
	<ul><?php
	foreach ($menubar as $menu_l1) { ?>
	<li<?php if ($menu_l1["inactive"]) echo ' class="ui-state-disabled"'; ?>>
        <a href="<?php echo $menu_l1["link"]; ?>"<?php
        if (isset($menu_l1["onclick"])) echo ' onclick="return false; '.$menbar[$i]["onclick"].'"';
		else echo ' onclick="return false;"'; ?>><?php
		if (isset($menu_l1["pic"])) echo '<img src="'.$pfad.'icons/'.$menu_l1["pic"].'" alt="'.$menu_l1["pic"].'" /> ';
        echo $menu_l1["name"]; ?></a><?php
        if (isset($menu_l1["sub"])) {
            echo '<ul name="hideonload" class="hideonload">';
            foreach ($menu_l1["sub"] as $menu_l2) { ?>
				<li<?php if ($menu_l2["inactive"]) echo ' class="ui-state-disabled"';
					?>><a href="<?php if (!$menu_l2["inactive"]) echo $menu_l2["link"]; ?>"<?php
						if ($menu_l2["inactive"] or isset($menu_l2["sub"])) echo ' onclick="return false;"';
						else if ($menu_l2["newwin"]==1) echo ' onclick="fenster(this.href, \'unwichtiger Titel\'); return false;"';
						if (isset($menu_l2["onclick"]))
							echo ' onclick="'.$menu_l2["onclick"].'"'; ?>><?php
						if (isset($menu_l2["pic"]))
							echo '<img src="'.$pfad.'icons/'.$menu_l2["pic"].'" alt="'.$menu_l2["pic"].'" /> ';
					echo $menu_l2["name"]; ?></a><?php
				
				if (isset($menu_l2["sub"])) {
                    echo '<ul>';
                    foreach ($menu_l2["sub"] as $menu_l3) { ?>
                        <li><a href="<?php echo $menu_l3["link"]; ?>"><?php if (isset($menu_l3["pic"])) echo '<img src="'.$pfad.'icons/'.$menu_l3["pic"].'" alt="'.$menu_l3["pic"].'" /> '; ?><?php echo $menu_l3["name"]; ?></a>
                        </li>
                    <?php }
                    echo '</ul>';
                    } ?>
                </li>
            <?php }
            echo '</ul>';
            } ?>
	</li>
    <?php } ?>
</ul>
</div>
</div>

<?php /* ?>
<script>
	var Unity = external.getUnityObject(1.0);
	
	function unityReady() {
    <?php
    function umlaute_zeigen($input) {
		$ersetzungszeichen_von=array("&amp;","&auml;","&ouml;","&uuml;","&Auml;","&Ouml;","&Uuml;");
		$ersetzungszeichen_nach=array("&","ä","ö","ü","Ä","Ö","Ü");
		$input=str_replace($ersetzungszeichen_von,$ersetzungszeichen_nach,$input);
		return $input;
	}
	
    for ($i=0; $i<count($menubar); $i++) {
		$menuname=umlaute_zeigen($menu_l1["name"]);
		if (strpos($menu_l1["name"], '<', 1)>0)
			$menuname="Fach-Klassen-Auswahl";
		if (!$menu_l1["inactive"]) { ?>
			//Unity.addAction("/<?php echo $menuname; ?>", function () { alert("link: <?php echo $menu_l1["link"]; ?>"); });
		<?php }
		
        if (isset($menu_l1["sub"])) {
            for ($n=0; $n<count($menu_l1["sub"]); $n++) {
				if (!$menu_l2["inactive"]) {
					$submenuname=umlaute_zeigen($menu_l2["name"]);
					if (strpos($menu_l2["name"], '<', 1)>0)
						$submenuname=substr($submenuname, strpos($submenuname, '>')+1, strpos($submenuname, '<', 1)-strpos($submenuname, '>')-1);
					?>
					Unity.addAction("/<?php echo $menuname."/".$submenuname; ?>", function () {
						<?php
						if ($menu_l2["newwin"]==1)
							echo "fenster('".umlaute_zeigen($menu_l2["link"])."', 'unwichtiger Titel');";
						else if (isset($menu_l2["onclick"]))
							echo $menu_l2["onclick"]; // TODO: Backup funktioniert nicht!
						else { ?>
							window.location.href ="<?php echo umlaute_zeigen($menu_l2["link"]); ?>";
						<?php } ?>
					});
					<?php
					if (isset($menu_l2["sub"])) {
                    for ($k=0; $k<count($menu_l2["sub"]); $k++) { ?>
						Unity.addAction("/<?php echo $menuname."/".$submenuname."/".umlaute_zeigen($menu_l3["name"]); ?>", function () { window.location.href ="<?php echo umlaute_zeigen($menu_l3["link"]); ?>"; });
                    <?php }
                    }
				}
            }
        }
	} ?>
	}
	
	Unity.init({name: "Kreda",
				iconUrl: "http://www.kirche-neudorf.de/logo_kreda_128.png",
				onInit: unityReady });
</script>
<?php */ ?>
