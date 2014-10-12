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


// aktive Fach-Klasse-Kombinationen laden
class subject_classes
{
    var $cont=array();
    var $active = 0;
    var $nach_ids = array();
    
    function subject_classes($aktujahr) {
		$db = new db;
        $subjectclass_result=db_conn_and_sql("SELECT *, fach_klasse.id AS fk_id, klasse.id AS k_id
			FROM klasse, faecher, fach_klasse LEFT JOIN lehrauftrag ON fach_klasse.id=lehrauftrag.fach_klasse AND lehrauftrag.schuljahr=".$aktujahr."
			WHERE fach_klasse.fach=faecher.id AND fach_klasse.klasse=klasse.id AND fach_klasse.anzeigen=1 AND fach_klasse.user=".$_SESSION['user_id']."
			ORDER BY klasse.einschuljahr DESC, klasse.endung, faecher.kuerzel, fach_klasse.gruppen_name");
        $last=db_conn_and_sql("SELECT letzte_fachklasse FROM benutzer WHERE id=".$_SESSION['user_id']);
        $last=sql_fetch_assoc($last);
        $last=$last["letzte_fachklasse"];
        
        $i=0;
        while ($subjectclass_row=sql_fetch_assoc($subjectclass_result)) {
            if (is_numeric($subjectclass_row["endung"]))
                $endung="/".$subjectclass_row["endung"];
            else
                $endung=$subjectclass_row["endung"];
            if ($subjectclass_row["gruppen_name"]!="")
                $gruppenname=" ".$subjectclass_row["gruppen_name"]; //htmlumlaute entfernt, um alpha, beta, ... zu ermoeglichen
            else
                $gruppenname="";
            
            if ($subjectclass_row["lfd_nr"]!="")
				$lehrauftrag=true;
			else
				$lehrauftrag=false;
            
            // TODO lieber mit cont["$id"=>...] ?
            $this->cont[$i]=array(
                "id"=>$subjectclass_row["fk_id"],
                "klasse_id"=>$subjectclass_row["k_id"],
                "farbe"=>$subjectclass_row["farbe"],
                "lehrauftrag"=>$lehrauftrag,
                "klassenanzeige"=>$subjectclass_row["klassenanzeige"],
                "klassenstufe"=>($aktujahr-$subjectclass_row["einschuljahr"]+1),
                "name"=>html_umlaute($subjectclass_row["kuerzel"])." ".($aktujahr-$subjectclass_row["einschuljahr"]+1).$endung.$gruppenname);
            if ($this->cont[$i]["klassenanzeige"]==0) {
				$this->cont[$i]["name"]=html_umlaute($subjectclass_row["kuerzel"]).$gruppenname;
				// %k durch Klassenstufe ersetzen
				$this->cont[$i]["name"]=str_replace(array("%k"), $this->cont[$i]["klassenstufe"], $this->cont[$i]["name"]);
			}
			$this->cont[$i]["farbanzeige"] = '<span style="background-color: #'.$this->cont[$i]["farbe"].'">'.$this->cont[$i]["name"].'</span>';
            if ($last==$subjectclass_row["fk_id"])
                $this->active=$i;
            
            // zweites Array (nach IDs aufzurufen) erstellen
            $this->nach_ids[$subjectclass_row["fk_id"]] = $this->cont[$i];
            $i++;
        }
    }
    
    function length() {
        return count($this->cont);
    }
}

// Schulklassen laden
class school_classes
{
    var $cont=array();
    var $active = 0;
    
    function school_classes($aktujahr) {
		$db = new db;
        $schoolclass_result=db_conn_and_sql( 'SELECT *, klasse.id AS klasse_id
			FROM `schule`, `schule_user`, `klasse`
			WHERE `einschuljahr`>('.$aktujahr.')-12
				AND `klasse`.`schule`=`schule`.`id`
				AND `schule`.`id`=`schule_user`.`schule`
				AND `schule_user`.`user`='.$_SESSION['user_id'].'
			ORDER BY schule_user.aktiv DESC, klasse.schule DESC, klasse.einschuljahr DESC, klasse.endung' );
        $last=db_conn_and_sql("SELECT fach_klasse.klasse FROM benutzer, fach_klasse WHERE benutzer.letzte_fachklasse=fach_klasse.id AND benutzer.id=".$_SESSION['user_id']);
        $last=sql_fetch_assoc($last);
        $last=$last["klasse"];
        $this->cont=array();
        $i=0;
        
        while ($my_schoolclass=sql_fetch_assoc($schoolclass_result)) {
            if (is_numeric($my_schoolclass["endung"]))
                $endung="/".$my_schoolclass["endung"];
            else
                $endung=$my_schoolclass["endung"];
            
            // TODO lieber mit cont["$id"=>...] ?
            $this->cont[]=array(
                "id"=>$my_schoolclass["klasse_id"],
                "klassenstufe"=>($aktujahr-$my_schoolclass["einschuljahr"]+1),
                "name"=>($aktujahr-$my_schoolclass["einschuljahr"]+1).$endung,
                "schule"=>$my_schoolclass["schule"],
                "kl_sitzplan"=>$my_schoolclass["kl_sitzplan"],
                "klassenlehrer"=>$my_schoolclass["klassenlehrer"],
                "klassenlehrer2"=>$my_schoolclass["klassenlehrer2"]);
            if ($last==$my_schoolclass["klasse_id"])
                $this->active=$i;
            
            // zweites Array (nach IDs aufzurufen) erstellen
            $this->nach_ids[$my_schoolclass["klasse_id"]] = $this->cont[$i];
            
            $i++;
        }
    }
    
    function length() {
        return count($this->cont);
    }
}

// Benutzer - also nur ein Eintrag
class user
{
    function user() {
		$db = new db;
        $user_result=db_conn_and_sql( 'SELECT *
			FROM `benutzer`, `users`
			WHERE `users`.`user_id`=`benutzer`.`id`
				AND `benutzer`.`id`='.$_SESSION['user_id'] );
		$user_row=sql_fetch_assoc($user_result);
        
        $this->my =array(
            "id"=>$user_row["id"],
            "name"=>html_umlaute($user_row["surname"]),
            "vorname"=>html_umlaute($user_row["forename"]),
            "strasse"=>html_umlaute($user_row["adress"]),
            "plz"=>html_umlaute($user_row["postal_code"]),
            "ort"=>html_umlaute($user_row["city"]),
            "tel1"=>html_umlaute($user_row["tel1"]),
            "tel2"=>html_umlaute($user_row["tel2"]),
            "tel3"=>html_umlaute($user_row["tel3"]),
            "aktuelles_schuljahr"=>$user_row["aktuelles_schuljahr"],
            "druckansicht"=>html_umlaute($user_row["druckansicht"]),
            "ansicht_2"=>html_umlaute($user_row["ansicht_2"]),
            "merkhefter"=>$user_row["merkhefter"],
            "letzte_schule"=>$user_row["letzte_schule"],
            "letzter_lernbereich"=>$user_row["letzter_lernbereich"],
            "letzte_fachklasse"=>$user_row["letzte_fachklasse"],
            "lb_faktor"=>$user_row["lb_faktor"],
            "username"=>html_umlaute($user_row["username"]),
            //"md5password"=>$user_row["md5password"],
            "email"=>html_umlaute($user_row["email"]),
            //"log"=>html_umlaute($user_row["log"]),
            //"bundesland"=>$user_row["bundesland"],
            //"verbleibende_versuche"=>html_umlaute($user_row["verbleibende_versuche"]),
            //"gesperrt_bis"=>html_umlaute($user_row["gesperrt_bis"]),
            "zensurenpunkte"=>$user_row["zensurenpunkte"],
            "zensurenkommentare"=>$user_row["zensurenkommentare"],
            "zensuren_unt_ber"=>$user_row["zensuren_unt_ber"],
            "zensuren_nicht_zaehlen"=>$user_row["zensuren_nicht_zaehlen"],
            "dienstberatungen"=>$user_row["dienstberatungen"],
            "schuljahresplanung"=>$user_row["schuljahresplanung"],
            "statistiken"=>$user_row["statistiken"],
            "ustd_planung"=>$user_row["ustd_planung"],
            "sitzplan"=>$user_row["sitzplan"],
            "admin"=>$user_row["admin"],
            "token_id"=>$user_row["token_id"]);
		
        if ($user_row["letzte_schule"]>0) {
			$rechte=db_conn_and_sql("SELECT schule_user.usertyp FROM schule_user WHERE schule=".$user_row["letzte_schule"]." AND user=".$user_row["id"]);
			while($recht=sql_fetch_assoc($rechte)) {
				if ($recht["usertyp"]==2)
					$this->my["lehrer"]=true;
				if ($recht["usertyp"]==4)
					$this->my["schulleitung"]=true;
				if ($recht["usertyp"]==5)
					$this->my["verwaltung"]=true;
				if ($recht["usertyp"]==6)
					$this->my["einzelnutzer"]=true;
			}
		}
    }
}

?>
