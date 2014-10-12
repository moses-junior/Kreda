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

//session_start();

// Pruefung, ob Benutzer angemeldet ist - hier und in header.php
if(!isset($_SESSION['user_id']))
{
   echo "Bitte erst <a href=\"".$pfad."login/index.php\">einloggen</a> <script>window.location=\"".$pfad."login/index.php\"</script>";
   exit;
}

error_reporting(E_ALL & ~E_NOTICE);
ini_set("display_errors", 1);

require_once($pfad."config/db.php");


// Erstellt Connect zu Datenbank her
$db = new db;
//$result = $db->schuljahr();

$benutzer=db_conn_and_sql("SELECT * FROM `benutzer` WHERE `id`=".$_SESSION['user_id']);
if (sql_num_rows($benutzer)==1) {
	$aktuelles_jahr=sql_fetch_assoc($benutzer);
	$aktuelles_jahr=$aktuelles_jahr["aktuelles_schuljahr"];
}
else
	die("Kein Benutzer ausgew&auml;hlt");

// -------------- Instanzen der Klassen laden -----------------------
if ($_SESSION['user_id']>0) {
	include($pfad."basic/model.php");
	$subject_classes = new subject_classes($aktuelles_jahr);
	$school_classes = new school_classes($aktuelles_jahr);
}

$CURDATE='CURDATE()'; // Bsp-DB: "'2009-10-09'"
$timestamp=time(); // Bsp-DB: mktime(10,30,15,10,9,2009)

include($pfad."basic/localisation/basics.php");
include($pfad."basic/localisation/province.php");




function html_umlaute($input) {
	if (substr(phpversion(),0,3)>='5.4')
		return htmlspecialchars($input, ENT_SUBSTITUTE, "ISO-8859-1");
	else
		return htmlspecialchars($input);
}

// deprecated
function db_anbindung () {
	$pfad='./';
	if (!file_exists($pfad.'mysql.pwd')) $pfad='../';
	if (!file_exists($pfad.'mysql.pwd')) $pfad='../../';
	if (file_exists($pfad.'mysql.pwd')) {
		@chmod ($pfad.'mysql.pwd', 0777);
		if (!($dza=fopen($pfad.'mysql.pwd', "r")))
			die ("Datei ".$pfad.'mysql.pwd'.' konnte nicht ge&ouml;ffnet werden!');
		$teil=fgets($dza);
		$dateiarray = explode('###',$teil);
    }
	rewind($dza);
	
	fclose($dza);
	chmod ($pfad.'mysql.pwd', 0700);
	clearstatcache();
	
	return array("benutzer"=>$dateiarray[0], "passwort"=>$dateiarray[1], "server"=>$dateiarray[2], "db_name"=>$dateiarray[3], "ftp_server"=>$dateiarray[4], "ftp_port"=>$dateiarray[5], "ftp_user"=>$dateiarray[6], "ftp_pwd"=>$dateiarray[7], "ftp_path"=>$dateiarray[8]);
}

// not implemented in mysqli
function sql_result($result, $row, $field=0) {
	if (DB_ENGINE == "Postgre") {
		if ($result===false) return false;
		if ($row>=pg_num_rows($result)) return false;
		if (is_string($field) && !(strpos($field,".")===false)) {
			$t_field=explode(".",$field);
			$field=-1;
			for ($id=0;$id<pg_num_fields($result);$id++) {
				if (pg_field_table($result,$id)==$t_field[0] && pg_field_name($result,$id)==$t_field[1]) {
					$field=$id;
					break;
				}
			}
			if ($field==-1)
				return false;
		}
		pg_result_seek($result,$row);
		$line=pg_fetch_array($result);
		return isset($line[$field])?$line[$field]:false;
	}
	else {
		if ($result===false) return false;
		//echo "<br>".$result." - ".$row." - ".$field;
		if ($row>=mysqli_num_rows($result)) return false;
		if (is_string($field) && !(strpos($field,".")===false)) {
			$t_field=explode(".",$field);
			$field=-1;
			$t_fields=mysqli_fetch_fields($result);
			for ($id=0;$id<mysqli_num_fields($result);$id++) {
				if ($t_fields[$id]->table==$t_field[0] && $t_fields[$id]->name==$t_field[1]) {
					$field=$id;
					break;
				}
			}
			if ($field==-1) return false;
		}
		mysqli_data_seek($result,$row);
		$line=mysqli_fetch_array($result);
		return isset($line[$field])?$line[$field]:false;
	}
}

function sql_reset_pointer($result) {
	if (DB_ENGINE == "Postgre")
		pg_result_seek($result, 0);
	else
		mysqli_data_seek($result, 0);
}

function sql_num_rows($result) {
	if (DB_ENGINE == "Postgre")
		return pg_num_rows($result);
	else
		return $result->num_rows;
}

function sql_fetch_assoc($result) {
	if (DB_ENGINE == "Postgre")
		return pg_fetch_assoc($result);
	else
		return mysqli_fetch_assoc($result);
}

// wird mit Postgre nicht aufgerufen
function db_selection($connid)
{
	$db_select = mysqli_select_db($connid, DB_NAME);
	if (!$db_select) {
		die("Database selection failed: ".mysqli_error());
	}
}

function db_connect()
{
	//$db_anbindung=db_anbindung();
	if (DB_ENGINE == "Postgre") {
		if (!$connid = pg_connect("host=".DB_HOST." dbname=".DB_NAME." user=".DB_USER." password=".DB_PASS));
			die('Fehler beim Verbinden...<br />'.DB_HOST.'
				Verbindungsparameter in config/db.php einstellen.<br />
				Falls noch nicht geschehen, muss eine Datenbank erstellt werden.<br />
				Danach muss sie mit den Grunddaten aus _install initialisiert werden.<br />');
	}
	else {
		if(!$connid = mysqli_connect(DB_HOST, DB_USER, DB_PASS))
			die('Fehler beim Verbinden...<br />'.DB_HOST.'
				Verbindungsparameter in config/db.php einstellen.<br />
				Falls noch nicht geschehen, muss eine Datenbank erstellt werden.<br />
				Danach muss sie mit den Grunddaten aus _install initialisiert werden.<br />');
		db_selection($connid);
	}
    return $connid;
}
  
function db_conn_and_sql($sql)
{
	$connid = db_connect();
	
	if (DB_ENGINE == "Postgre") {
		if (!$ergebnis = pg_query($connid, $sql))
			echo "Fehler beim Senden der Abfrage: ".$sql."<br />".pg_last_error($connid)."<br />";
		if (substr($sql, 0, 6)=="INSERT") {
			$insert_query = pg_query("SELECT lastval();");
			$insert_row = pg_fetch_row($insert_query);
			$insert_id = $insert_row[0];
			return $insert_id;
		}
		else {
			mysqli_close($connid);
			return $ergebnis;
		}
	}
	else {
		if (!$ergebnis = mysqli_query($connid, $sql))
			echo "Fehler beim Senden der Abfrage: ".$sql."<br />".mysqli_error($connid)."<br />";
		if (substr($sql, 0, 6)=="INSERT") {
			return mysqli_insert_id($connid);
		}
		else {
			mysqli_close($connid);
			return $ergebnis;
		}
	}
}


class db
{
  var $connid;
  var $erg;
  var $pfad;

  function select_db()
  {
	  $db_select = mysqli_select_db($this->connid, DB_NAME);
      if (!$db_select) {
         die("Database selection failed: ".mysqli_error());
      }
  }
  
  function db()
  {
	//$db_anbindung=db_anbindung();
	
    if(!$this->connid = mysqli_connect(DB_HOST, DB_USER, DB_PASS))
      die('Fehler beim Verbinden...<br />'.DB_HOST.'
		Verbindungsparameter in config/db.php einstellen.<br />
		Falls noch nicht geschehen, muss eine Datenbank "lehrer" erstellt werden.<br />
		Danach muss sie mit den Grunddaten aus _install initialisiert werden.<br />');
    $this->select_db();
    
    return $this->connid;
  }
  
  function sql($sql)
  {
    if (!$this->erg = mysqli_query($this->connid, $sql))
      echo "Fehler beim Senden der Abfrage: ".$sql."<br />".mysqli_error($this->connid)."<br />";
    return $this->erg;
  }
	// URL aus Lernbereich erzeugen ist unguenstig, wenn Schulart umbenannt wird (z.B: MS -> OS)
	function url($lernbereich) {
		$result=db_conn_and_sql('SELECT *
                       FROM `lernbereich`,`lehrplan`,`schulart`,`faecher`
                       WHERE `lernbereich`.`id`='.$lernbereich.'
                         AND `lernbereich`.`lehrplan`=`lehrplan`.`id`
                         AND `lehrplan`.`schulart` = `schulart`.`id`
                         AND `lehrplan`.`fach` = `faecher`.`id`' ); //ORDER BY `lehrplan`.`schulart`,`lehrplan`.`bundesland`,`lehrplan`.`jahr`,`lehrplan`.`fach`,`lernbereich`.`klassenstufe`,`lernbereich`.`nummer`
		return "daten/".html_umlaute(sql_result($result, 0, 'faecher.kuerzel')).'/'.html_umlaute(sql_result($result, 0, 'schulart.kuerzel')).'/'.sql_result($result, 0, 'lernbereich.klassenstufe').'/';
	}
	
	function schuljahr() { $schuljahr=new db; return $schuljahr->sql( 'SELECT * FROM `schuljahr` ORDER BY `jahr`, `schule`' );}
	
	function aktuelles_jahr() {
		$schuljahr=new db;
		return sql_result($schuljahr->sql("SELECT * FROM `benutzer` WHERE `id`=".$_SESSION['user_id']),0,'benutzer.aktuelles_schuljahr');
	}
	
	function lernbereiche() {
		global $bundesland; // nicht getestet
		$lernbereiche=$this->sql('SELECT lernbereich.id AS lb_id, lehrplan.id AS lp_id, lp_user.aktiv AS lp_user_aktiv, faecher.name AS f_name, faecher.kuerzel AS f_kuerzel, lehrplan.jahr, lehrplan.zusatz, schulart.kuerzel AS sa_kuerzel, lehrplan.bundesland, lernbereich.name AS lb_name, lernbereich.nummer AS lb_nummer, lernbereich.wahl, lernbereich.klassenstufe, lernbereich.beschreibung, lernbereich.ustd
			FROM `lernbereich`,`lehrplan`,`schulart`,`faecher`,`lp_user`
            WHERE `lernbereich`.`lehrplan`=`lehrplan`.`id`
				AND `lehrplan`.`schulart` = `schulart`.`id`
                AND `lehrplan`.`fach` = `faecher`.`id`
                AND `lehrplan`.`id`=`lp_user`.`lehrplan`
                AND `lp_user`.`user`='.$_SESSION['user_id'].'
            ORDER BY `lehrplan`.`schulart`,`lehrplan`.`bundesland`,`lehrplan`.`jahr` DESC, `lehrplan`.`fach`, `lehrplan`.`zusatz`, `lernbereich`.`klassenstufe`,`lernbereich`.`nummer`' );
		while ($lernbereich_row=sql_fetch_assoc($lernbereiche))
			$lb_array[$lernbereich_row["lb_id"]]=array('id'=>$lernbereich_row["lb_id"],
				'aktiv'=>$lernbereich_row["lp_user_aktiv"],
				'lehrplan_id'=>$lernbereich_row["lp_id"],
				'fach_lang'=>html_umlaute($lernbereich_row["f_name"]),
				'fach_kurz'=>html_umlaute($lernbereich_row["f_kuerzel"]),
				'jahr'=>$lernbereich_row["jahr"],
				'zusatz'=>html_umlaute($lernbereich_row["zusatz"]),
				'schulart_kurz'=>html_umlaute($lernbereich_row["sa_kuerzel"]),
				'bundesland'=>$bundesland[$lernbereich_row["bundesland"]]['kuerzel'],
				'lernbereich_id'=>$lernbereich_row["lb_id"],
				'lernbereich_name'=>html_umlaute($lernbereich_row["lb_name"]),
				'lernbereich_nummer'=>$lernbereich_row["lb_nummer"],
				'lernbereich_wahl'=>$lernbereich_row["wahl"],
				'lernbereich_klassenstufe'=>$lernbereich_row["klassenstufe"],
				'lernbereich_beschreibung'=>html_umlaute($lernbereich_row["beschreibung"]),
				'lernbereich_ustd'=>$lernbereich_row["ustd"]);
		return $lb_array;
	}
	function lernbereichoptions($selected) {
		global $bundesland;
		$lbs=$this->lernbereiche();
		$gruppe=0; $aktuelles_kam=false;
		if ($lbs!="")
        foreach ($lbs as $lernbereich) {
			if ($lernbereich["aktiv"]) {
				if ($gruppe!=$lernbereich["lehrplan_id"].'-'.$lernbereich["lernbereich_klassenstufe"]) {
					 $wahl=1;
					if ($gruppe!=0) $inhalt.='</optgroup>';
					$gruppe=$lernbereich["lehrplan_id"].'-'.$lernbereich["lernbereich_klassenstufe"];
					$inhalt.='<optgroup label="'.$lernbereich["fach_kurz"].' Kl. '.$lernbereich["lernbereich_klassenstufe"].' ('.$lernbereich["schulart_kurz"].' '.$lernbereich["zusatz"].' '.html_umlaute($bundesland[$lernbereich["bundesland"]]['kuerzel']).' '.$lernbereich["jahr"].')">';
				}
				$inhalt.='<option value="'.$lernbereich["lernbereich_id"].'"';
				if($lernbereich["lernbereich_id"]==$selected) {$inhalt.=' selected="selected"'; $aktuelles_kam=true;}
				$inhalt.='>LB ';
				if ($lernbereich["lernbereich_wahl"]) { $inhalt.='W'.$wahl; $wahl++; }
				else $inhalt.=$lernbereich["lernbereich_nummer"];
				$inhalt.=': '.$lernbereich["lernbereich_name"].'</option>'; // ohne html_umlaute (in diesem Schritt schon geschehen)
			}
		}
		if ($gruppe!=0)
			$inhalt.='</optgroup>';
		if (!$aktuelles_kam and $selected!=0)
			$inhalt.='<option value="'.$selected.'" selected="selected">'.$lbs[$selected]["schulart_kurz"].' '.html_umlaute($lbs[$selected]["bundesland"]).' '.$lbs[$selected]["jahr"].' '.$lbs[$selected]["fach_kurz"].' Kl. '.$lbs[$selected]["lernbereich_klassenstufe"].' LB '.$lbs[$selected]['lernbereich_nummer'].' '.$lbs[$selected]['lernbereich_name'].'</option>';
		return $inhalt;
	}
	
	function plan($id) {
		$plan=new db; return $plan->sql(  'SELECT *
                                     FROM `plan`
                                     WHERE `plan`.`id`='.$id);
	}
	
	function themen() {
		$sql_themen=$this->sql( 'SELECT *, thema.id AS th_id, faecher.anzeigen, thema.fach, thema.oberthema, faecher.kuerzel, thema.bezeichnung FROM `thema`,`faecher` WHERE `thema`.`fach`=`faecher`.`id` AND `thema`.`user`='.$_SESSION['user_id'].' ORDER BY `thema`.`oberthema`,`thema`.`fach`, `thema`.`bezeichnung`');
		$array="";
		while($thema_row=sql_fetch_assoc($sql_themen)) {
			if ($thema_row["oberthema"]=="")
				$array[$thema_row["th_id"]]=array('id'=>$thema_row["th_id"], 'aktiv'=>$thema_row["anzeigen"], 'fach_id'=>$thema_row["fach"], 'fach_kuerzel'=>html_umlaute($thema_row["kuerzel"]), 'bezeichnung'=>html_umlaute($thema_row["bezeichnung"]));
			else
				$array[$thema_row["oberthema"]][$thema_row["th_id"]]=array('id'=>$thema_row["th_id"], 'aktiv'=>$thema_row["anzeigen"], 'fach_id'=>$thema_row["fach"], 'fach_kuerzel'=>html_umlaute($thema_row["kuerzel"]), 'bezeichnung'=>html_umlaute($thema_row["bezeichnung"]));
		}
		return $array;
	}
		
	function rekursiv_themenoption($themen, $selected, $tiefe, $oberthema_id, $thema_ausschliessen=0) {
		global $aktuelles_kam;
		foreach($themen[$oberthema_id] as $unterthema)
			if (isset($unterthema["id"]) && $unterthema["id"]!=$unterthema["fach_kuerzel"] and $thema_ausschliessen!=$unterthema["id"]) {
				$zusatz_inhalt.= '<option value="'.$unterthema["id"].'"';
				if($unterthema["id"]==$selected) {$zusatz_inhalt.=' selected="selected"'; $aktuelles_kam=true; }
				$zusatz_inhalt.=' style="border-left: solid green '.($tiefe*10).'px;">'.$unterthema["bezeichnung"].'</option>';
				if (count($themen[$unterthema["id"]])>0)
					$zusatz_inhalt.=$this->rekursiv_themenoption($themen, $selected, $tiefe+1, $unterthema["id"]);
			}
		return $zusatz_inhalt;
	}

	function themenoptions($selected, $thema_ausschliessen=0) {
		$fach=0; $aktuelles_kam=false;
		$themen=$this->themen();
		global $aktuelles_kam;
		
		if ($themen!="")
        foreach ($themen as $oberthema) {
			if ($oberthema["aktiv"]) {
				if ($fach!=$oberthema["fach_id"]) {
					if ($fach!=0) $inhalt.='</optgroup>';
					$inhalt.='<optgroup label="'.$oberthema["fach_kuerzel"].'">';
					$fach=$oberthema["fach_id"];
				}
                if ($thema_ausschliessen!=$oberthema["id"]) {
                    $inhalt.='<option value="'.$oberthema["id"].'"';
                    if($oberthema["id"]==$selected) {
                        $inhalt.=' selected="selected"';
                        $aktuelles_kam=true;
                    }
                    $inhalt.='>'.$oberthema["bezeichnung"].'</option>';
                    if (count($themen[$oberthema["id"]])>4)
                        $inhalt.=$this->rekursiv_themenoption($themen, $selected, 1, $oberthema["id"], $thema_ausschliessen);
                }
			}
		}
		if ($fach!=0) $inhalt.='</optgroup>';
		if (!$aktuelles_kam and $selected!=0) $inhalt.='<option value="'.$selected.'" selected="selected">'.$themen[$selected]["fach_kuerzel"].': '.$themen[$selected]["bezeichnung"].'</option>';
		return $inhalt;
	}
	
  function link_id($id)  {
	$result= $this->sql("SELECT *
									FROM `link`,`lernbereich`,`themenzuordnung`,`thema`,`faecher`,`lehrplan`
									WHERE `link`.`lernbereich`=`lernbereich`.`id`
										AND `lernbereich`.`lehrplan`=`lehrplan`.`id`
										AND `lehrplan`.`fach`=`faecher`.`id`
										AND `themenzuordnung`.`thema`=`thema`.`id`
										AND `themenzuordnung`.`typ`=4
										AND `themenzuordnung`.`id`=`link`.`id`
										AND `link`.`id`=".$id."
										AND `link`.`user`=".$_SESSION['user_id']);
	$linkarray=array('id'=>sql_result($result, $i, 'link.id'),
							'lokal'=>sql_result($result, $i, 'link.lokal'),
							'lernbereich'=>sql_result($result, 0, 'lernbereich.id'),
							'url'=>$this->url(sql_result($result, 0, 'lernbereich.id')).urlencode(sql_result($result, $i, 'link.url')),
							'url_decode'=>$this->url(sql_result($result, 0, 'lernbereich.id')).sql_result($result, $i, 'link.url'),
							'typ'=>sql_result($result, $i, 'link.typ'),
							'beschreibung'=>html_umlaute(sql_result($result, $i, 'link.beschreibung')),
							'klassenstufe'=>sql_result($result, $i, 'lernbereich.klassenstufe'),
							'fach'=>html_umlaute(sql_result($result, $i, 'faecher.kuerzel')));
	$thema=0;
	while($thema<sql_num_rows($result)) {
		$linkarray['thema'][]=array('bezeichnung'=>html_umlaute(sql_result($result, $thema, 'thema.bezeichnung')), 'id'=>sql_result($result, $thema, 'themenzuordnung.thema'));
		$thema++;
	}
	if(!sql_result($result, $i, 'link.lokal')) $linkarray["url"]=sql_result($result, $i, 'link.url');
	return $linkarray;
  }
  function links() {
	$result=$this->sql("SELECT *
							FROM `themenzuordnung`,`thema`, `link` LEFT JOIN  `link_abschnitt` ON `link_abschnitt`.`link`=`link`.`id`
								LEFT JOIN `block_abschnitt` ON `block_abschnitt`.`abschnitt`=`link_abschnitt`.`abschnitt`
								LEFT JOIN `block` ON `block_abschnitt`.`block`=`block`.`id`
								LEFT JOIN `lernbereich` ON `block`.`lernbereich`=`lernbereich`.`id`
								LEFT JOIN `lehrplan` ON `lernbereich`.`lehrplan`=`lehrplan`.`id`
							WHERE `themenzuordnung`.`thema`=`thema`.`id`
								AND `themenzuordnung`.`typ`=4
								AND `themenzuordnung`.`id`=`link`.`id`
								AND `link`.`user`=".$_SESSION['user_id']."
							GROUP BY `link`.`id`
							ORDER BY `themenzuordnung`.`thema`,`link`.`typ`");
    for ($i=0;$i<sql_num_rows($result);$i++) {
		$return[$i]=$this->link_id(sql_result($result,$i,'link.id'));
		$return[$i]['block']=@sql_result($result, $i, 'block.id');
	}
	return $return;
  }
  function grafik($id)  {
	$result= $this->sql("SELECT *
									FROM `grafik`,`lernbereich`,`themenzuordnung`,`thema`,`faecher`,`lehrplan`
									WHERE `grafik`.`lernbereich`=`lernbereich`.`id`
										AND `lernbereich`.`lehrplan`=`lehrplan`.`id`
										AND `lehrplan`.`fach`=`faecher`.`id`
										AND `themenzuordnung`.`thema`=`thema`.`id`
										AND `themenzuordnung`.`typ`=3
										AND `themenzuordnung`.`id`=`grafik`.`id`
										AND `grafik`.`id`=".$id);
										//AND `grafik`.`user`=".$_SESSION['user_id']);
	$grafikarray= array('id'=>sql_result($result, $i, 'grafik.id'),
                           'alt'=>html_umlaute(sql_result($result, $i, 'grafik.alt')),
						   'lernbereich'=>sql_result($result, 0, 'lernbereich.id'),
                           'url'=>$this->url(sql_result($result, 0, 'lernbereich.id')).urlencode(sql_result($result, $i, 'grafik.url')),
                           'url_decode'=>$this->url(sql_result($result, 0, 'lernbereich.id')).sql_result($result, $i, 'grafik.url'),
                           'tmb_url'=>$this->url(sql_result($result, 0, 'lernbereich.id')).'tmb_'.urlencode(sql_result($result, $i, 'grafik.url')),
                           'tmb_url_decode'=>$this->url(sql_result($result, 0, 'lernbereich.id')).'tmb_'.sql_result($result, $i, 'grafik.url'),
						   'klassenstufe'=>sql_result($result, $i, 'lernbereich.klassenstufe'),
                           'fach'=>html_umlaute(sql_result($result, $i, 'faecher.kuerzel')));
	$thema=0;
	while($thema<sql_num_rows($result)) {
		$grafikarray['thema'][]=array('bezeichnung'=>html_umlaute(sql_result($result, $thema, 'thema.bezeichnung')), 'id'=>sql_result($result, $thema, 'themenzuordnung.thema'));
		$thema++;
	}
	return $grafikarray;
  }
  function grafiken() {
	// das geht denk ich gar nicht. wg DISTINCT; hab ich das ueberhaupt jemals gebraucht?  
	$result=$this->sql("SELECT DISTINCT grafik.id
                           FROM `grafik`,`themenzuordnung`
                           WHERE `themenzuordnung`.`typ`=3
								AND `themenzuordnung`.`id`=`grafik`.`id` ORDER BY `themenzuordnung`.`thema`
								AND `grafik`.`user`=".$_SESSION['user_id']);
    for ($i=0;$i<sql_num_rows($result);$i++) $return[$i]=$this->grafik(sql_result($result,$i,'grafik.id'));
	return $return;
  }
  function buch($id) {
	$buch= $this->sql("SELECT `buch`.*, `faecher`.`kuerzel`, GROUP_CONCAT(`buch_klassenstufe`.`klassenstufe`) AS `zeug`, `buch_klassenstufe`.`klassenstufe`
                                 FROM `buch`,`faecher`,`buch_klassenstufe`
                                 WHERE `buch`.`fach`=`faecher`.`id`
									AND `buch_klassenstufe`.`buch` = `buch`.`id`
									AND `buch`.`id`=".$id."
									AND `buch`.`user`=".$_SESSION['user_id']."
								GROUP BY `buch`.`id`");
	return array('id'=>sql_result($buch, 0, 'buch.id'),
                           'name'=>html_umlaute(sql_result($buch, 0, 'buch.name')),
                           'kuerzel'=>html_umlaute(sql_result($buch, 0, 'buch.kuerzel')),
                           'untertitel'=>html_umlaute(sql_result($buch, 0, 'buch.untertitel')),
                           'verlag'=>html_umlaute(sql_result($buch, 0, 'buch.verlag')),
                           'isbn'=>html_umlaute(sql_result($buch, 0, 'buch.isbn')),
                           'klassenstufen_gesamt'=>sql_result($buch, 0, 'zeug'),
						   'aktiv'=>sql_result($buch, 0, 'buch.aktiv'),
                           'fach'=>html_umlaute(sql_result($buch, 0, 'faecher.kuerzel')));
						// 'letztes_thema'=>sql_result($buch, 0, 'buch.letztes_thema'), 'letzter_lernbereich'=>sql_result($buch, 0, 'buch.letzter_lernbereich'),
	}
  function buecher() { $buecher=db_conn_and_sql("SELECT `buch`.*, `buch`.`kuerzel` AS `buch_kuerzel`, `faecher`.`kuerzel`, GROUP_CONCAT(`buch_klassenstufe`.`klassenstufe`) AS `klassenstufen`, `buch_klassenstufe`.`klassenstufe`
                                 FROM `buch`,`faecher`,`buch_klassenstufe`
                                 WHERE `buch`.`fach`=`faecher`.`id`
									AND `buch_klassenstufe`.`buch` = `buch`.`id`
									AND `buch`.`user`=".$_SESSION['user_id']."
                                 GROUP BY `buch_klassenstufe`.`buch`
                                 ORDER BY `buch`.`aktiv` DESC, `buch`.`fach`,`buch_klassenstufe`.`klassenstufe`,`buch`.`name`");
    while ($buch=sql_fetch_assoc($buecher))
		$return[]=array('id'=>$buch["id"],
                        'name'=>html_umlaute($buch["name"]),
                        'kuerzel'=>html_umlaute($buch["buch_kuerzel"]),
                        'untertitel'=>html_umlaute($buch["untertitel"]),
                        'verlag'=>html_umlaute($buch["verlag"]),
                        'isbn'=>html_umlaute($buch["isbn"]),
                        'klassenstufen_gesamt'=>$buch["klassenstufen"],
						'aktiv'=>$buch["aktiv"],
                        'fach'=>html_umlaute($buch["kuerzel"]));
	return $return;
	}
  function material($id) {
	$material= $this->sql("SELECT `material`.*
                                 FROM `material`
                                 WHERE `material`.`id`=".$id."
									AND `material`.`user`=".$_SESSION['user_id']);
	$materialarray=array('id'=>sql_result($material, $i, 'material.id'),
                           'name'=>html_umlaute(sql_result($material, $i, 'material.name')),
                           'beschreibung'=>html_umlaute(sql_result($material, $i, 'material.beschreibung')),
                           'aufbewahrungsort'=>html_umlaute(sql_result($material, $i, 'material.aufbewahrungsort')));
	$result=db_conn_and_sql("SELECT * FROM `thema`, `themenzuordnung`
                           WHERE `thema`.`id`=`themenzuordnung`.`thema`
								AND `themenzuordnung`.`typ`=6
								AND `themenzuordnung`.`id`=".$id);
	$thema=0;
	while($thema<sql_num_rows($result)) {
		$materialarray['thema'][]=array('bezeichnung'=>html_umlaute(sql_result($result, $thema, 'thema.bezeichnung')), 'id'=>sql_result($result, $thema, 'themenzuordnung.thema'));
		$thema++;
	}
	return $materialarray;
	}
  function materialien() { $material=new db; $result=$material->sql("SELECT `material`.*
                                 FROM `material`
                                 WHERE `material`.`user`=".$_SESSION['user_id']);
    for ($i=0;$i<sql_num_rows($result);$i++) $return[$i]=$this->material(sql_result($result,$i,'material.id'));
	return $return;
	}
	
	function aufgabe($id) { $result=$this->sql("SELECT aufgabe.*, lernbereich.id AS lb_id, lernbereich.nummer, lernbereich.name, lernbereich.klassenstufe, faecher.kuerzel, lehrplan.schulart, thema.bezeichnung, themenzuordnung.thema
																		FROM `aufgabe`,`lernbereich`,`themenzuordnung`,`thema`, `lehrplan`, `faecher`
																		WHERE `aufgabe`.`lernbereich`=`lernbereich`.`id`
																			AND `aufgabe`.`id`=`themenzuordnung`.`id`
																			AND `themenzuordnung`.`typ`=1
																			AND `lehrplan`.`fach`=`faecher`.`id`
																			AND `themenzuordnung`.`thema`=`thema`.`id`
																			AND `lernbereich`.`lehrplan`=`lehrplan`.`id`
																			AND `aufgabe`.`id`=".$id);
																			//AND `aufgabe`.`user`=".$_SESSION['user_id']);
		$my_aufgabe=sql_fetch_assoc($result);
		$aufgabe=array('text'=>$my_aufgabe["text"], // geht durch syntax - deshalb kein html_umlaute() - das selbe bei loesung
								'loesung'=>$my_aufgabe["loesung"],
								'punkte'=>$my_aufgabe["punkte"],
								'lernbereich_id'=>$my_aufgabe["lb_id"],
								'lernbereich_nummer'=>$my_aufgabe["nummer"],
								'lernbereich'=>html_umlaute($my_aufgabe["name"]),
								'klassenstufe'=>$my_aufgabe["klassenstufe"],
								'fach'=>html_umlaute($my_aufgabe["kuerzel"]),
								'schulart'=>html_umlaute($my_aufgabe["schulart"]),
								'bearbeitungszeit'=>$my_aufgabe["bearbeitungszeit"],
								'cm'=>$my_aufgabe["cm"],
								'kariert'=>$my_aufgabe["kariert"],
								'bildbeschriftung'=>$my_aufgabe["bildbeschriftung"],
								'bildanordnung'=>$my_aufgabe["bildanordnung"],
								'bemerkung'=>html_umlaute($my_aufgabe["bemerkung"]),
								'schwierigkeitsgrad'=>$my_aufgabe["schwierigkeitsgrad"],
								'teilaufgaben_nebeneinander'=>$my_aufgabe["teilaufgaben_nebeneinander"],
                                'id'=>$id);
		$thema=0;
		$aufgabe['thema'][]=array('bezeichnung'=>html_umlaute($my_aufgabe["bezeichnung"]), 'id'=>$my_aufgabe["thema"]);
		while($my_aufgabe=sql_fetch_assoc($result))
			$aufgabe['thema'][]=array('bezeichnung'=>html_umlaute($my_aufgabe["bezeichnung"]), 'id'=>$my_aufgabe["thema"]);
		
		//$bilder=$this->sql("SELECT * FROM `grafik_aufgabe`,`grafik` WHERE `grafik_aufgabe`.`grafik`=`grafik`.`id` AND `grafik_aufgabe`.`aufgabe`=".$id);
		//if (sql_num_rows($bilder)>0)
		//for ($i=0;$i<sql_num_rows($bilder);$i++)
		//	$aufgabe['bilder'][$i]=array('id'=>sql_result($bilder, $i, 'grafik.id'),'alt'=>html_umlaute(sql_result($bilder, $i, 'grafik.alt')), 'url'=>$this->url(sql_result($bilder, $i, 'grafik.lernbereich')).urlencode(sql_result($bilder, $i, 'grafik.url')), 'breite'=>sql_result($bilder, $i, 'grafik_aufgabe.groesse'));
		$buecher=$this->sql("SELECT * FROM `buch_aufgabe`,`buch` WHERE `buch_aufgabe`.`buch`=`buch`.`id` AND `buch_aufgabe`.`aufgabe`=".$id);
		$i=0;
		while($buch=sql_fetch_assoc($buecher)) {
			$aufgabe['buch'][$i]=$this->buch($buch["id"]);
			$aufgabe['buch'][$i]['seite']=html_umlaute($buch["seite"]);
			$aufgabe['buch'][$i]['nummer']=html_umlaute($buch["nummer"]);
			$i++;
		}
		return $aufgabe;
	}
	
	function aufgaben() {
		$aufgabe=new db; $result=$aufgabe->sql("SELECT DISTINCT aufgabe.id
							FROM `aufgabe`,`themenzuordnung`
							WHERE `aufgabe`.`id`=`themenzuordnung`.`id`
								AND `themenzuordnung`.`typ`=1
								AND `aufgabe`.`user`=".$_SESSION['user_id']."
							ORDER BY `themenzuordnung`.`thema`");
		for ($i=0;$i<sql_num_rows($result);$i++) $return[$i]=$this->aufgabe(sql_result($result,$i,'aufgabe.id'));
		return $return;
	}
	
	function test($id) {
		$result=$this->sql("SELECT * , GROUP_CONCAT(`thema`.`bezeichnung` SEPARATOR ', ') AS `themen`
						FROM `themenzuordnung`,`thema`,`test` LEFT JOIN `notentypen` ON `test`.`notentyp`=`notentypen`.`id`
						WHERE `themenzuordnung`.`thema`=`thema`.`id`
							AND `themenzuordnung`.`id`=`test`.`id`
							AND `themenzuordnung`.`typ`=5
							AND `test`.`id`=".$id."
							AND `test`.`user`=".$_SESSION['user_id']."
						GROUP BY `test`.`id`");
	$url=0; if(sql_result($result, 0, 'test.url')!="") $url=1;
    $test=array(  'id'=>sql_result($result, 0, 'test.id'),
                            'notentyp_id'=>sql_result($result, 0, 'test.notentyp'),
	                        'notentyp'=>html_umlaute(sql_result($result, 0, 'notentypen.kuerzel')),
	                        'url'=>$this->url(sql_result($result, 0, 'test.lernbereich')).urlencode(sql_result($result, 0, 'test.url')),
	                        'url_decode'=>$this->url(sql_result($result, 0, 'test.lernbereich')).sql_result($result, 0, 'test.url'),
							'url_vorhanden'=>$url,
							'alternativtitel'=>html_umlaute(sql_result($result, 0, 'titel')),
							'themen'=>html_umlaute(sql_result($result, 0, 'themen')),
							'lernbereich'=>sql_result($result, 0, 'test.lernbereich'),
							'platz_lassen'=>sql_result($result, 0, 'test.platz_lassen'),
							'bearbeitungszeit'=>sql_result($result, 0, 'test.bearbeitungszeit'),
							'bemerkung'=>html_umlaute(sql_result($result, 0, 'test.bemerkung')),
							'hilfsmittel'=>html_umlaute(sql_result($result, 0, 'test.hilfsmittel')),
							'titel'=>html_umlaute(sql_result($result, 0, 'test.titel')),
							'punkte'=>sql_result($result, 0, 'test.punkte'),
							'arbeitsblatt'=>sql_result($result, 0, 'test.arbeitsblatt'),
							'vorspann'=>html_umlaute(sql_result($result, 0, 'test.vorspann')));
		$aufgaben_result=db_conn_and_sql("SELECT * FROM `test_aufgabe` WHERE `test`=".$id." ORDER BY test_aufgabe.position");
		if (sql_num_rows($aufgaben_result)>0)
            for ($i=0;$i<sql_num_rows($aufgaben_result);$i++) {
                $test['aufgaben'][$i]=$this->aufgabe(sql_result($aufgaben_result,$i,'test_aufgabe.aufgabe'));
                $test['aufgaben'][$i]['position_A']=sql_result($aufgaben_result,$i,'test_aufgabe.position');
                $test['aufgaben'][$i]['position_B']=sql_result($aufgaben_result,$i,'test_aufgabe.position_b');
                $test['aufgaben'][$i]['zusatzaufgabe']=sql_result($aufgaben_result,$i,'test_aufgabe.zusatzaufgabe');
                $test['aufgaben'][$i]['neue_seite_A']=sql_result($aufgaben_result,$i,'test_aufgabe.neue_seite');
                $test['aufgaben'][$i]['neue_seite_B']=sql_result($aufgaben_result,$i,'test_aufgabe.neue_seite_b');
            }
		return $test;
	}
	
	function abschnitt($id) {
	$result=$this->sql("SELECT *
		FROM `abschnitt`,`block_abschnitt`, `block`,`lernbereich`
		WHERE `block_abschnitt`.`abschnitt`=`abschnitt`.`id`
			AND `block_abschnitt`.`block`=`block`.`id`
			AND `block`.`lernbereich`=`lernbereich`.`id`
			AND `abschnitt`.`id`=".$id."
			AND `block`.`user`=".$_SESSION['user_id']);
	if ($id!=0) { // fuer den Fall, dass der Abschnitt ein Einmalabschnitt ist
        $abschnitt['id']=sql_result($result, 0, 'abschnitt.id');
        $abschnitt['klassenstufe']=sql_result($result, 0, 'lernbereich.klassenstufe');
        $abschnitt['lehrplan']=sql_result($result, 0, 'lernbereich.lehrplan');
        $abschnitt['block']=sql_result($result, 0, 'block_abschnitt.block');
        $abschnitt['minuten']=sql_result($result, 0, 'abschnitt.minuten');
        $abschnitt['ziel']=html_umlaute(@sql_result($result, 0, 'abschnitt.ziel'));
        $abschnitt['medium']=sql_result($result, 0, 'abschnitt.medium');
        $abschnitt['sozialform']=sql_result($result, 0, 'abschnitt.sozialform');
        $abschnitt['position']=sql_result($result, 0, 'block_abschnitt.position');
        $abschnitt['nachbereitung']=html_umlaute(@sql_result($result, 0, 'abschnitt.nachbereitung'));
        $abschnitt['hefter']=sql_result($result, 0, 'abschnitt.hefter');
        $abschnitt['handlungsmuster']=sql_result($result, 0, 'abschnitt.handlungsmuster');
        $abschnitt['methode']=sql_result($result, 0, 'abschnitt.methode');
        $abschnitt['positionen']=sql_result($result, 0, 'abschnitt.inhaltspositionen');
    }
    
    $result=$this->sql("SELECT * FROM `ueberschrift` WHERE `ueberschrift`.`abschnitt`=".$id." ORDER BY `ueberschrift`.`ebene` ASC");
	if (sql_num_rows($result)>0) {
		$diese_ueb=db_conn_and_sql("SELECT * FROM `block_abschnitt`,`block`,`lernbereich` WHERE `block_abschnitt`.`block`=`block`.`id` AND `block`.`lernbereich`=`lernbereich`.`id` AND `block_abschnitt`.`abschnitt`=".$id);
		$ebenen_sort=db_conn_and_sql("SELECT * FROM `ueberschrift`,`block_abschnitt`,`block` AS `block1`, `block` AS `block2`, `lernbereich`
			WHERE `lernbereich`.`klassenstufe`=".sql_result($diese_ueb,0,"lernbereich.klassenstufe")."
				AND `lernbereich`.`lehrplan`=".sql_result($diese_ueb,0,"lernbereich.lehrplan")."
				AND `ueberschrift`.`abschnitt`=`block_abschnitt`.`abschnitt`
				AND (`block_abschnitt`.`block`=`block1`.`id` OR `block_abschnitt`.`block`=`block2`.`id`)
				AND `block2`.`block_hoeher`=`block1`.`id`
				AND `block1`.`lernbereich`=`lernbereich`.`id`
				AND `block2`.`lernbereich`=`lernbereich`.`id`
			ORDER BY `lernbereich`.`nummer`,`block1`.`position`,`block2`.`position`,`block_abschnitt`.`position`");
		//Aushilf:
		for ($i=0;$i<sql_num_rows($result);$i++) {
			$hilfs_uebschrift=""; for ($hilf=0; $hilf<sql_result($result, $i, 'ueberschrift.ebene');$hilf++) $hilfs_uebschrift.="x.";
			$abschnitt['ueberschrift'][$i]=array('ebene'=>sql_result($result, $i, 'ueberschrift.ebene'), 'typ'=>sql_result($result, $i, 'ueberschrift.typ'), 'nummer'=>$hilfs_uebschrift, 'text'=>html_umlaute(sql_result($result, $i, 'ueberschrift.text')), 'id'=>sql_result($result, $i, 'ueberschrift.id'));
		}
		// KANN SEIN, DASS ES PROBLEME BEI BLOEKEN DER ERSTEN EBENE GIBT!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
		//for ($i=0;$i<sql_num_rows($ebenen_sort);$i++) echo sql_result($ebenen_sort,$i,"lernbereich.nummer")."B1:".sql_result($ebenen_sort,$i,"block1.position")." B2:".sql_result($ebenen_sort,$i,"block2.position")." A:".sql_result($ebenen_sort,$i,"abschnitt.position")." U:".sql_result($ebenen_sort,$i,"ueberschrift.ebene")." ".sql_result($ebenen_sort,$i,"ueberschrift.text")."<br />";
		
		/*for ($i=0;$i<5;$i++) $ebene[$i]=0;
		$i=0;
		$abbruch=1;
		while($abbruch) {
			$ebene[sql_result($ebenen_sort,$i,"ueberschrift.ebene")-1]++;
			$typ[sql_result($ebenen_sort,$i,"ueberschrift.ebene")]=sql_result($ebenen_sort,$i,"ueberschrift.typ");
			for ($j=sql_result($ebenen_sort,$i,"ueberschrift.ebene")+1;$j<=5;$j++) $ebene[$j]=0;
			//echo "e[".(sql_result($ebenen_sort,$i,"ueberschrift.ebene")-1)."]=".$ebene[sql_result($ebenen_sort,$i,"ueberschrift.ebene")-1]." t[".sql_result($ebenen_sort,$i,"ueberschrift.ebene")."]=".$typ[sql_result($ebenen_sort,$i,"ueberschrift.ebene")]." Ebenen: ".$ebene[0].$ebene[1].$ebene[2].$ebene[3]."<br>";
			if(sql_result($ebenen_sort,$i,"abschnitt.id")==$id) $abbruch=0;
			$i++;
			if($abbruch==0 and @sql_result($ebenen_sort,$i,"abschnitt.id")==$id) $abbruch=1;
		}
		for ($i=0;$i<sql_num_rows($result);$i++) {
			$abschnitt['ueberschrift'][$i]=array('ebene'=>sql_result($result, $i, 'ueberschrift.ebene'), 'typ'=>sql_result($result, $i, 'ueberschrift.typ'), 'nummer'=>'', 'text'=>sql_result($result, $i, 'ueberschrift.text'), 'id'=>sql_result($result, $i, 'ueberschrift.id'));
			for($j=0;$j<sql_result($result, $i, 'ueberschrift.ebene');$j++)
				switch ($typ[$j]) {case "a": $abschnitt['ueberschrift'][$i]['nummer'].=chr(96+$ebene[$j])."."; break; case "A": $abschnitt['ueberschrift'][$i]['nummer'].=chr(64+$ebene[$j])."."; break; case "I": $abschnitt['ueberschrift'][$i]['nummer'].=arab2roman($ebene[$j])."."; break; default: $abschnitt['ueberschrift'][$i]['nummer'].=$ebene[$j]."."; break; }
		}*/
	}
    
    $result=$this->sql("SELECT * FROM `sonstiges` WHERE `abschnitt`=".$id);
	if (sql_num_rows($result)>0) for ($i=0;$i<sql_num_rows($result);$i++) $abschnitt['sonstiges'][$i]=array('typ'=>sql_result($result, $i, 'sonstiges.typ'), 'inhalt'=>html_umlaute(sql_result($result, $i, 'sonstiges.inhalt')), 'id'=>sql_result($result, $i, 'sonstiges.id'));
    
    $result=$this->sql("SELECT * FROM `material_abschnitt`,`material` WHERE `material_abschnitt`.`material`=`material`.`id` AND `material_abschnitt`.`abschnitt`=".$id);
	if (sql_num_rows($result)>0) for ($i=0;$i<sql_num_rows($result);$i++) $abschnitt['material'][$i]=array('name'=>html_umlaute(sql_result($result, $i, 'material.name')), 'beschreibung'=>html_umlaute(sql_result($result, $i, 'material.beschreibung')), 'aufbewahrungsort'=>html_umlaute(sql_result($result, $i, 'material.aufbewahrungsort')), 'id'=>sql_result($result, $i, 'material.id'));
	
    $result=$this->sql("SELECT * FROM `grafik_abschnitt`,`grafik` WHERE `grafik_abschnitt`.`grafik`=`grafik`.`id` AND `grafik_abschnitt`.`abschnitt`=".$id);
	if (sql_num_rows($result)>0) for ($i=0;$i<sql_num_rows($result);$i++) $abschnitt['grafik'][$i]=array('alt'=>html_umlaute(sql_result($result, $i, 'grafik.alt')), 'url'=>$this->url(sql_result($result, $i, 'grafik.lernbereich')).sql_result($result, $i, 'grafik.url'), 'breite'=>sql_result($result, $i, 'grafik_abschnitt.groesse'), 'position'=>sql_result($result, $i, 'grafik_abschnitt.position'), 'id'=>sql_result($result, $i, 'grafik.id'));
	
    $result=$this->sql("SELECT * FROM `link_abschnitt`,`link` WHERE `link_abschnitt`.`link`=`link`.`id` AND `link_abschnitt`.`abschnitt`=".$id);
	if (sql_num_rows($result)>0) for ($i=0;$i<sql_num_rows($result);$i++) {
		$abschnitt['link'][$i]=$this->link_id(sql_result($result, $i, 'link.id'));
		$abschnitt['link'][$i]['bemerkung']=html_umlaute(sql_result($result, $i, 'link_abschnitt.bemerkung'));
		// GROOOOOOOOOOOOOOSSSSSSSSSSSSSSSSSEE AEDERUNG - WENNS GEHT, AUCH BEI GRAFIK UND TEST
		//$abschnitt['link'][$i]=array('typ'=>sql_result($result, $i, 'link.typ'),'lokal'=>sql_result($result, $i, 'link.lokal'),'beschreibung'=>sql_result($result, $i, 'link.beschreibung'), 'url'=>$this->url(sql_result($result, $i, 'link.lernbereich')).sql_result($result, $i, 'link.url'), 'bemerkung'=>sql_result($result, $i, 'link_abschnitt.bemerkung'), 'id'=>sql_result($result, $i, 'link.id'));
	}
	
    $result=$this->sql("SELECT * FROM `aufgabe_abschnitt`,`aufgabe` WHERE `aufgabe_abschnitt`.`aufgabe`=`aufgabe`.`id` AND `aufgabe_abschnitt`.`abschnitt`=".$id);
	if (sql_num_rows($result)>0) for ($i=0;$i<sql_num_rows($result);$i++) {
		$abschnitt['aufgabe'][$i]=$this->aufgabe(sql_result($result, $i, 'aufgabe.id'));
		$abschnitt['aufgabe'][$i]['beispiel']=sql_result($result, $i, 'aufgabe_abschnitt.beispiel');
		}
	
    $result=$this->sql("SELECT * FROM `test_abschnitt` WHERE `test_abschnitt`.`abschnitt`=".$id);
	if (sql_num_rows($result)>0) for ($i=0;$i<sql_num_rows($result);$i++) $abschnitt['test'][$i]=$this->test(sql_result($result, $i, 'test_abschnitt.test'));
	
	for ($i=0;$i<count($abschnitt['ueberschrift']);$i++)
		$abschnitt['inhalt'].="&Uuml;berschrift (".$abschnitt['ueberschrift'][$i]['ebene']."): ".$abschnitt['ueberschrift'][$i]['text']."<br />";
	for ($i=0;$i<count($abschnitt['sonstiges']);$i++)
		$abschnitt['inhalt'].="Typ: ".$abschnitt['sonstiges'][$i]['typ']." - ".syntax_zu_html($abschnitt['sonstiges'][$i]['inhalt'],1,0,'./','A')."<br /> ";
	for ($i=0;$i<count($abschnitt['material']);$i++)
		$abschnitt['inhalt'].="Material: ".$abschnitt['material'][$i]['name']."<br />";
	for ($i=0;$i<count($abschnitt['grafik']);$i++)
		$abschnitt['inhalt'].="Bild: ".$abschnitt['grafik'][$i]['alt']."<br />";
	for ($i=0;$i<count($abschnitt['link']);$i++) {
		switch ($abschnitt['link'][$i]['typ']) {
			case "1": $abschnitt['inhalt'].="AB: "; break;
			case "2": $abschnitt['inhalt'].="Folie: "; break;
			case "3": $abschnitt['inhalt'].="Link/Datei: "; break;
		}
		$abschnitt['inhalt'].=$abschnitt['link'][$i]['beschreibung']."<br />";
	}
	for ($i=0;$i<count($abschnitt['aufgabe']);$i++)
		$abschnitt['inhalt'].="Aufgabe (".$abschnitt['aufgabe'][$i]['buch'][0]['kuerzel']." S. ".$abschnitt['aufgabe'][$i]['buch'][0]['seite']."/".$abschnitt['aufgabe'][$i]['buch'][0]['nummer']."): ".syntax_zu_html($abschnitt['aufgabe'][$i]['text'],$abschnitt['aufgabe'][$i]['teilaufgaben_nebeneinander'],0,'./','A')." - L&ouml;sung: ".syntax_zu_html($abschnitt['aufgabe'][$i]['loesung'],$abschnitt['aufgabe'][$i]['teilaufgaben_nebeneinander'],0,'./','A')."<br />";
	for ($i=0;$i<count($abschnitt['test']);$i++)
		$abschnitt['inhalt'].="Test: ".$this->pfad.$abschnitt['test'][$i]['notentyp']." - ".$abschnitt['test'][$i]['themen']." - ".$abschnitt['test'][$i]['bearbeitungszeit']." min";
		
	return $abschnitt;
	
  }
  
	function blockselect($vorauswahl, $selectname, $belassen=false) {
        $hilf=-1;
		$inhalt='<select name="'.$selectname.'">';
      $bloecke = db_conn_and_sql("SELECT lehrplan.zusatz, block.id AS block_id, block.name AS block_name, schulart.kuerzel AS sa_kuerzel, faecher.kuerzel AS fach_kuerzel, lernbereich.klassenstufe, lernbereich.nummer AS lb_nummer, lernbereich.id AS lb_id, lernbereich.name AS lb_name, COUNT(`block_abschnitt`.`abschnitt`) AS `anzahl`
                    FROM `block`
                        LEFT JOIN `block_abschnitt` ON `block_abschnitt`.`block`=`block`.`id`,`lernbereich`,`lehrplan`,`schulart`,`faecher`
                    WHERE `lernbereich`.`lehrplan`=`lehrplan`.`id`
                        AND `block`.`lernbereich`=`lernbereich`.`id`
                        AND `lehrplan`.`fach` = `faecher`.`id`
                        AND `lehrplan`.`schulart`=`schulart`.`id`
                        AND `block`.`user`=".$_SESSION['user_id']."
                    GROUP BY `block`.`id`
                    ORDER BY `lehrplan`.`schulart`, `lehrplan`.`fach`,`lernbereich`.`klassenstufe`,`lehrplan`.`zusatz`,`lernbereich`.`nummer`,`block`.`block_hoeher`, `block`.`position`");
      $lb=0;
      while ($block=sql_fetch_assoc($bloecke)) {
      	if ($block["lb_id"]!=$lb) {
         	if ($lb!=0)
            	$inhalt.='</optgroup>';
            $lb=$block["lb_id"];
            if (strlen($block["zusatz"])>0)
				$zusatz=" (".html_umlaute($block["zusatz"]).")";
			else $zusatz="";
            $inhalt.='<optgroup label="'.html_umlaute($block["sa_kuerzel"]).' '.html_umlaute($block["fach_kuerzel"]).' - Kl. '.$block["klassenstufe"].' LB '.$block["lb_nummer"].'. '.html_umlaute($block["lb_name"]).$zusatz.'">';
         }
         $inhalt.='<option value="'.$block["block_id"].'"';
        if ($block["block_id"]==$vorauswahl) {
             $hilf=$h;
             if (!$belassen)
                $inhalt.=' selected="selected"';
        }
        $inhalt.=' onclick="document.getElementsByName(\'lernbereich\')[0].value='.$lb.';" >'.html_umlaute($block["block_name"]).' ('.$block["anzahl"].')</option>';
      }
      $inhalt.='</optgroup>';
      // wenn ein neuer Block gewaehlt wird, wird auch die Position neu berechnet (ans Ende des Blocks setzen); deshalb: ist belassen=true oder der Block nicht gefunden worden, dann nicht aendern
        if ($belassen or $hilf==-1)
            $inhalt.='<option value="-1" selected="selected">nicht &auml;ndern</option>';
      $inhalt.='</select>';
      return $inhalt;
   }
}


// ------------------------------ Funktionen zum Loeschen ------------------------------
	function delete_db_object($tablename, $ids, $pfad, $themenzuordnungen_geloescht) {
		include($pfad."formular/db_tables_array.php");
        
        if ($tablename=="themenzuordnung")
            $themenzuordnungen_geloescht=true;

		//for($i=0;$i<count($ids); $i++) echo $ids[$i].", ";
		
		$my_delete_table = $tables_array[$tablename];
		
		$result_ids_line='';
		$write_and=false;
		//print_r($ids); echo $tablename.":".count($my_delete_table["ids"])."+".count($my_delete_table["fremd"]);
		for($i=0; $i<count($my_delete_table["ids"]); $i++)
			if ($ids[$i]!="all" and $ids[$i]>0) {
				if ($write_and)
					$result_ids_line.=' AND ';
				$write_and=true;
				$result_ids_line.=$my_delete_table["ids"][$i].'='.$ids[$i];
			}
		for($i=0; $i<count($my_delete_table["fremd"]); $i++)
			if ($ids[count($my_delete_table["ids"])+$i]!="all" and $ids[count($my_delete_table["ids"])+$i]>0) {
				if ($write_and)
					$result_ids_line.=' AND ';
				$write_and=true;
				$result_ids_line.=$my_delete_table["fremd"][$i].'='.$ids[count($my_delete_table["ids"])+$i];
			}
		
        // ohne Zusaetze soll nichts geloescht werden (Fehlervorbeugung)
		if ($result_ids_line=="")
            $result_ids_line="1=0";
        
		$result=db_conn_and_sql("SELECT * FROM ".$tablename." WHERE ".$result_ids_line);
		$vorher_del=array();
		// abhaengiges loeschen
		//if ($result_ids_line!="" and sql_num_rows($result)>0)
        //    echo "SELECT * FROM ".$tablename." WHERE ".$result_ids_line." -> ".sql_num_rows($result)."<br>";
        //$result='';
        
        if (!isset($my_delete_table["fremd"])) // Workaround, damit array_merge funktioniert
            $my_delete_table["fremd"]=array();
        $merged_idsNfremd=array_merge($my_delete_table["ids"], $my_delete_table["fremd"]);
        
        if (sql_num_rows($result)>0)
        for ($i=0; $i<sql_num_rows($result); $i++)
			for($n=0; $n<count($my_delete_table["abhaengig"]); $n++) {
				$abhaengig_ids=array();
				//$abhaengig_ids[0]=$ids[$my_delete_table["abhaengig"][$n]["idNfremd"][0]];
				for ($k=0; $k<count($my_delete_table["abhaengig"][$n]["idNfremd"]);$k++) { // geaendert am 18.8.2011 - pruefen
					//echo $my_delete_table["abhaengig"][$n]["idNfremd"][$k].",";
					//echo "<br>".$k.$tablename.".".$merged_idsNfremd[$k].":".$my_delete_table["abhaengig"][$n]["idNfremd"][$k];
                    
                    // Themenzuordnung bei Aufgabe, Block, Grafik, Link, Material, Test
                    // echo $k; print_r($my_delete_table["abhaengig"][$n]["bedingung"]);
                    
					if ($my_delete_table["abhaengig"][$n]["idNfremd"][$k]!=="all" and strlen($my_delete_table["abhaengig"][$n]["idNfremd"][$k])>0) { // letzteres ist Workaround
                        //echo "-".strlen($my_delete_table["abhaengig"][$n]["idNfremd"][$k])." ".$my_delete_table["abhaengig"][$n]["idNfremd"][$k]." ".$ids[$my_delete_table["abhaengig"][$n]["idNfremd"][$k]];
						if ($ids[$my_delete_table["abhaengig"][$n]["idNfremd"][$k]]!=="all" and strlen($ids[$my_delete_table["abhaengig"][$n]["idNfremd"][$k]])>0) {
                            $abhaengig_ids[$k] = $ids[$my_delete_table["abhaengig"][$n]["idNfremd"][$k]];
                        }
						else {
                            if ($tablename=="themenzuordnung")
                                $themenzuordnung_typ=sql_result($result, $i, "themenzuordnung.typ");
							if ($tablename!="themenzuordnung"
                                    or ($my_delete_table["abhaengig"][$n]["tablename"]=="aufgabe" and $themenzuordnung_typ==1)
                                    or ($my_delete_table["abhaengig"][$n]["tablename"]=="block" and $themenzuordnung_typ==2)
                                    or ($my_delete_table["abhaengig"][$n]["tablename"]=="grafik" and $themenzuordnung_typ==3)
                                    or ($my_delete_table["abhaengig"][$n]["tablename"]=="link" and $themenzuordnung_typ==4)
                                    or ($my_delete_table["abhaengig"][$n]["tablename"]=="material" and $themenzuordnung_typ==5)
                                    or ($my_delete_table["abhaengig"][$n]["tablename"]=="test" and $themenzuordnung_typ==6)
                                    )
                                $abhaengig_ids[$k] = sql_result($result, $i, $tablename.".".$merged_idsNfremd[$my_delete_table["abhaengig"][$n]["idNfremd"][$k]]);
							//echo $my_delete_table["abhaengig"][$n]["tablename"]."--".$themenzuordnung_typ.$tablename.".".$merged_idsNfremd[$my_delete_table["abhaengig"][$n]["idNfremd"][$k]].":".sql_result($result, $i, $tablename.".".$merged_idsNfremd[$my_delete_table["abhaengig"][$n]["idNfremd"][$k]]).$my_delete_table["abhaengig"][$n]["ids"][$k]."<br>";
						}
                        
                        if ($my_delete_table["abhaengig"][$n]["tablename"]=="themenzuordnung" and $tablename!="thema") {
                            $abhaengig_ids[0]=$my_delete_table["abhaengig"][$n]["bedingung"][0];
                            //echo "----".$abhaengig_ids[$k]."-".$abhaengig_ids[0]."-".$my_delete_table["abhaengig"][$n]["bedingung"][0]."<br>";
                        }
                        /*switch ($my_delete_table["abhaengig"][$n]["bedingung"][0]) {
                            case "typ=1": $abhaengig_ids[0]=1; echo "hi"; break;
                        }*/
					}
				}
				//echo $my_delete_table["abhaengig"][$n]["tablename"]." - ";
				//print_r($abhaengig_ids); echo $themenzuordnung_typ."<br>";
                if ($tablename=="themenzuordnung")
                    $themenzuordnung_typ=sql_result($result, $i, "themenzuordnung.typ");
                else
                    $themenzuordnung_typ='';
                if (!$themenzuordnungen_geloescht or $my_delete_table["abhaengig"][$n]["tablename"]!="themenzuordnung")
                    if ($tablename!="themenzuordnung"
                        or ($my_delete_table["abhaengig"][$n]["tablename"]=="aufgabe" and $themenzuordnung_typ==1)
                        or ($my_delete_table["abhaengig"][$n]["tablename"]=="block" and $themenzuordnung_typ==2)
                        or ($my_delete_table["abhaengig"][$n]["tablename"]=="grafik" and $themenzuordnung_typ==3)
                        or ($my_delete_table["abhaengig"][$n]["tablename"]=="link" and $themenzuordnung_typ==4)
                        or ($my_delete_table["abhaengig"][$n]["tablename"]=="material" and $themenzuordnung_typ==5)
                        or ($my_delete_table["abhaengig"][$n]["tablename"]=="test" and $themenzuordnung_typ==6)
                        /*or $themenzuordnung_typ==''*/)
                    $vorher_del = array_merge($vorher_del, delete_db_object($my_delete_table["abhaengig"][$n]["tablename"], $abhaengig_ids, $pfad, $themenzuordnungen_geloescht));
				//$echo_del .= delete_db_object($my_delete_table["abhaengig"][$n]["tablename"], $abhaengig_ids, $pfad, $modus);
			}
		//echo "SELECT * FROM ".$tablename." WHERE ".$result_ids_line.": ".count($vorher_del)."<br />";
		$vorher_del = array_merge($vorher_del, array(array("tablename"=>$tablename, "result_ids_line"=>$result_ids_line, "count"=>sql_num_rows($result), "description"=>$my_delete_table["description"])));
		return $vorher_del;
	}
	
	
    
    // Aufruf beispiele:
    // del_array2echo(delete_db_object("abschnittsplanung", array(85), $pfad, false), "sql");
    // del_array2echo(delete_db_object("themenzuordnung", array("all", "all", 50), $pfad, false), "info");
    
	function del_array2echo ($del_array, $modus) {
        $return='';
		//echo $del_array;
        //print_r($del_array);
        if ($modus=="sql")
            for($i=0; $i<count($del_array); $i++)
                if ($del_array[$i]["count"]>0)
                    $return[]="DELETE FROM ".$del_array[$i]["tablename"]." WHERE ".$del_array[$i]["result_ids_line"].";";
        
        if ($modus=="info") {
            // TODO sortieren nach Tabellennamen (Will ich das???)
            for($i=0; $i<count($del_array)-1; $i++)
                for($k=$i; $k<count($del_array); $k++)
                    if ($del_array[$i]["tablename"]>$del_array[$k]["tablename"]) {
                        // tauschen
                        $hilf=$del_array[$i];
                        $del_array[$i]=$del_array[$k];
                        $del_array[$k]=$hilf;
                    }
            
            // gleiche Tabellennamen zusammenfassen
            for($i=0; $i<count($del_array); $i++) {
                $counter=1;
                while (isset($del_array[$i]["tablename"]) && $del_array[$i]["tablename"]==$del_array[$i+$counter]["tablename"]) {
                    if ($del_array[$i+$counter]!="deleted")
                        $del_array[$i]["count"] = $del_array[$i]["count"] + $del_array[$i+$counter]["count"];
                    $del_array[$i+$counter]="deleted";
                    $counter++;
                }
            }
            
            // anzeigen
            $return.="gel&ouml;scht werden: <ul>";
            for($i=0; $i<count($del_array); $i++)
                if ($del_array[$i]!="deleted" and $del_array[$i]["count"]>0) { // and isset($del_array[$i]["description"])
                    $return.="<li";
                    if (!isset($del_array[$i]["description"]))
                        $return.=' style="color: gray;"';
                    $return.=">".$del_array[$i]["count"]." ".$del_array[$i]["description"]." (".$del_array[$i]["tablename"].")</li>";
                }
            $return.="</ul>";
        }
        return $return;
	}
	


// ------------------------------ kurze allgemeine Funktionen ------------------------------------

    function arab2roman($ar) // wandelt Integerzahl $ar in roemische Zahlzeichen um (sofern $ar <= 3999)
    {
        $q[1] = "I"; $q[2] = "V"; $q[3] = "X"; $q[4] = "L"; $q[5] = "C"; $q[6] = "D"; $q[7] = "M";
        $rom = "";
        if ($ar>3999) $rom = "Zahl zu gross!";
        else
        {
            $s=1;
            while ($ar>0)
            {
                $st=$ar-10*floor($ar/10);
                $ar=($ar-$st)/10;
                $x="";
                $gf=0;
                if ($st>4)
                {
                    if ($st<9) $x=$q[2*$s].$x;
                    $st=$st-5;
                    $gf=1;
                }
                if ($st==4) $x=$x.$q[2*$s-1].$q[2*$s+$gf];
                else while ($st>0)
                {
                    $st--;
                    $x=$x.$q[2*$s-1];
                }
                $rom=$x.$rom;
                $s++;
            }
        }
        return ($rom);
    }
    
    function datum_strich_zu_punkt ($datum) {
        $hilf = explode('-',$datum);
        if (isset($hilf[1])) return $hilf[2].'.'.$hilf[1].'.'.$hilf[0];
        else return $datum;
    }
    
    function datum_zu_woche ($datum) {
        $hilf = explode('-',$datum);
        return date("W",mktime(1,0,0,(int) $hilf[1],(int) $hilf[2],(int) $hilf[0]));
    }
    
    function datum_strich_zu_wochentag($datum, $art) {
        $hilf = explode('-',$datum);
        if (isset($hilf[1])) {
            $wochennamen_kurz=array(0=>'Sonntag', 1=>'Montag', 2=>'Dienstag', 3=>'Mittwoch', 4=>'Donnerstag', 5=>'Freitag', 6=>'Samstag');
            if ($art=="kurzform")
				$wochennamen_kurz=array(0=>'So', 1=>'Mo', 2=>'Di', 3=>'Mi', 4=>'Do', 5=>'Fr', 6=>'Sa');
            
            return $wochennamen_kurz[date("w",mktime(1,1,1,$hilf[1],$hilf[2],$hilf[0]))];
        }
        else
			return $datum;
    }
    
    function datum_strich_zu_punkt_uebersichtlich ($datum, $wochentag, $jahresangabe) {
        $return='';
        if ($wochentag=="wochentag_kurz")
        	  $return.=datum_strich_zu_wochentag($datum, "kurzform").' ';
        $hilf = explode('-',$datum);
        if (isset($hilf[1])) {
            $return.=($hilf[2]+0).'.'.($hilf[1]+0).'.';
            if ($jahresangabe) $return.=substr($hilf[0],2,2);
        }
        else $return=$datum;
        return $return;
    }
    
    function zeit_formatieren ($zeit) {
        return (substr($zeit,0,2)+0).'&thinsp;<sup style="text-decoration: underline; font-size: 7pt;">'.substr($zeit,3,2).'</sup>';
    }

    function datum_punkt_zu_strich ($datum) {
        $hilf = explode('.',$datum);
        if (isset($hilf[1])) {
            if ($hilf[0]<10) $hilf[0]='0'.($hilf[0]+0);
            if ($hilf[1]<10) $hilf[1]='0'.($hilf[1]+0);
            if ($hilf[2]<99) $hilf[2]='20'.$hilf[2];
            return $hilf[2].'-'.$hilf[1].'-'.$hilf[0];
        }
        else return $datum;
    }
    
    function kommazahl ($input) {
        $output=str_replace(".", ",", ($input+0));
        //if (floor($input)==$input)
        return $output;
    }
    
    function punkt_statt_komma_zahl ($input) {
        $output=str_replace(",", ".", $input)+0;
        return $output;
    }
    
    function isdate($input) {
		if (strlen($input)==10 and preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $input))
			return true;
		else
			return false;
	}
	
	function injaway ($input) {
		$db=db_connect();
		if (isdate($input))
			return $input;
		else
			return intval(mysqli_real_escape_string($db, $input));
	}
	
    function apostroph_bei_bedarf ($text) {
		$db=db_connect();
        if ($text=="") return "NULL";
        //else return "'".str_replace("'","\'",$text)."'";
        else return "'".mysqli_real_escape_string($db, trim($text))."'";
    }
    
    function leer_NULL ($zahl) {
        if ($zahl=="") return "NULL";
        else return injaway($zahl);
    }
	
    // wird von iconv benoetigt
    setlocale(LC_CTYPE, 'de_DE.UTF-8');
	function pictureOfPupil ($surname, $forename, $number, $username, $path, $options) {
		$filename='';
		if (empty($username)) {
			$forename_ascii=iconv("ISO-8859-1", "ASCII//TRANSLIT", $forename);
			$surname_ascii =iconv("ISO-8859-1", "ASCII//TRANSLIT", $surname);
			if ($number>0)
				$filename=strtolower(substr($forename_ascii,0,1)).strtolower(substr($surname_ascii,0,2)).$number;
		}
		else
			$filename=$username;
		
		if ($filename!='') {
			$pictureFile=$path.'daten/pupilpictures/'.$filename.'.jpg';
			if (file_exists($pictureFile))
				return '<img src="'.$pictureFile.'" alt="bild" '.$options.' />';
			else return '';
		}
		else
			return '';
	}
	
	
	function sortieren($spalte, $sortier_get, $pfad, $ziel) {
		$sortier_nach=explode("_", $sortier_get);
		if ($sortier_nach[1]=="az" and $sortier_nach[0]==$spalte)
			$richtung="za";
		else
			$richtung="az";
		
		if ($sortier_nach[0]==$spalte) {
			if ($sortier_nach[1]=="az") $icon="sortiert_ab.png"; else $icon="sortiert_auf.png";
		}
		else
			$icon="sortiert_nicht.png";
		
		$return='<a href="'.$pfad.$ziel.'&amp;sort='.$spalte.'_'.$richtung.'" class="icon"><img src="'.$pfad.'icons/'.$icon.'" alt="sort" /></a>';
		return $return;
	}
	
	
	// gibt wahr zurueck, wenn das Element dem Benutzer gehoert
	function proofuser($was, $id) {
		$return=false;
		$id=injaway($id);
		switch ($was) {
			case "abschnitt": if (sql_result(db_conn_and_sql("SELECT block.user FROM block_abschnitt, block WHERE block_abschnitt.abschnitt=".$id." AND block_abschnitt.block=block.id"),0,"user")==$_SESSION['user_id']) $return=true; break;
			case "aufgabe": if (sql_result(db_conn_and_sql("SELECT user FROM aufgabe WHERE aufgabe.id=".$id),0,"user")==$_SESSION['user_id']) $return=true; break;
			case "aufsicht": if (sql_result(db_conn_and_sql("SELECT aufsicht.user FROM aufsicht WHERE aufsicht.id=".$id),0,"user")==$_SESSION['user_id']) $return=true; break;
			case "bewegliche_feiertage": if (sql_result(db_conn_and_sql("SELECT user FROM bewegliche_feiertage, schule_user WHERE bewegliche_feiertage.id=".$id." AND bewegliche_feiertage.schule=schule_user.schule"),0,"user")==$_SESSION['user_id']) $return=true; break;
			case "block": if (sql_result(db_conn_and_sql("SELECT user FROM block WHERE block.id=".$id),0,"user")==$_SESSION['user_id']) $return=true; break;
			case "buch": if (sql_result(db_conn_and_sql("SELECT user FROM buch WHERE buch.id=".$id),0,"user")==$_SESSION['user_id']) $return=true; break;
			case "elternkontakt": if (sql_result(db_conn_and_sql("SELECT elternkontakt.user FROM elternkontakt WHERE elternkontakt.id=".$id),0,"elternkontakt.user")==$_SESSION['user_id']) $return=true; break;
			case "fach_klasse": if (sql_result(db_conn_and_sql("SELECT user FROM fach_klasse WHERE fach_klasse.id=".$id),0,"user")==$_SESSION['user_id']) $return=true; break;
			case "grafik": if (sql_result(db_conn_and_sql("SELECT user FROM grafik WHERE grafik.id=".$id),0,"user")==$_SESSION['user_id']) $return=true; break;
			case "hausaufgabe": if (sql_result(db_conn_and_sql("SELECT fach_klasse.user FROM fach_klasse, plan, hausaufgabe WHERE hausaufgabe.id=".$id." AND hausaufgabe.plan=plan.id AND plan.fach_klasse=fach_klasse.id"),0,"fach_klasse.user")==$_SESSION['user_id']) $return=true; break;
			// old: case "klasse": if (sql_result(db_conn_and_sql("SELECT fach_klasse.user FROM fach_klasse, klasse WHERE klasse.id=".$id." AND klasse.id=fach_klasse.klasse"),0,"fach_klasse.user")==$_SESSION['user_id']) $return=true; break;
			case "klasse": if (sql_result(db_conn_and_sql("SELECT schule_user.user FROM schule_user, klasse WHERE klasse.id=".$id." AND klasse.schule=schule_user.schule AND schule_user.user=".$_SESSION['user_id']),0,"schule_user.user")==$_SESSION['user_id']) $return=true; break;
			case "kollege": if (sql_result(db_conn_and_sql("SELECT user FROM kollege WHERE kollege.id=".$id),0,"user")==$_SESSION['user_id']) $return=true; break;
			case "konferenz": if (sql_result(db_conn_and_sql("SELECT user FROM konferenz WHERE konferenz.id=".$id),0,"user")==$_SESSION['user_id']) $return=true; break;
			case "lehrplan": if (sql_result(db_conn_and_sql("SELECT lp_user.user FROM lp_user WHERE lp_user.lehrplan=".$id." AND lp_user.user=".$_SESSION['user_id']),0,"user")==$_SESSION['user_id']) $return=true; break;
			case "lernbereich": if (sql_result(db_conn_and_sql("SELECT lp_user.user FROM lp_user,lernbereich WHERE lp_user.lehrplan=lernbereich.lehrplan AND lernbereich.id=".$id),0,"user")==$_SESSION['user_id']) $return=true; break;
			case "link": if (sql_result(db_conn_and_sql("SELECT user FROM link WHERE link.id=".$id),0,"user")==$_SESSION['user_id']) $return=true; break;
			case "liste": if (sql_result(db_conn_and_sql("SELECT user FROM liste, fach_klasse WHERE liste.fach_klasse=fach_klasse.id AND liste.id=".$id),0,"user")==$_SESSION['user_id']) $return=true; break;
			case "material": if (sql_result(db_conn_and_sql("SELECT user FROM material WHERE material.id=".$id),0,"user")==$_SESSION['user_id']) $return=true; break;
			case "notenbeschreibung": if (sql_result(db_conn_and_sql("SELECT user FROM notenbeschreibung, fach_klasse WHERE notenbeschreibung.id=".$id." AND notenbeschreibung.fach_klasse=fach_klasse.id"),0,"user")==$_SESSION['user_id']) $return=true; break;
			case "notenberechnungsvorlage": if (sql_result(db_conn_and_sql("SELECT user FROM notenberechnungsvorlage WHERE notenberechnungsvorlage.id=".$id),0,"user")==$_SESSION['user_id']) $return=true; break;
			case "notiz": if (sql_result(db_conn_and_sql("SELECT user FROM notiz WHERE notiz.id=".$id),0,"user")==$_SESSION['user_id']) $return=true; break;
			case "plan": if (sql_result(db_conn_and_sql("SELECT fach_klasse.user FROM fach_klasse, plan WHERE plan.id=".$id." AND plan.fach_klasse=fach_klasse.id"),0,"fach_klasse.user")==$_SESSION['user_id']) $return=true; break;
			//case "schueler": if (sql_result(db_conn_and_sql("SELECT fach_klasse.user FROM schueler, fach_klasse WHERE schueler.id=".$id." AND schueler.klasse=fach_klasse.klasse"),0,"fach_klasse.user")==$_SESSION['user_id']) $return=true; break;
			case "schueler": if (sql_result(db_conn_and_sql("SELECT schule_user.user FROM schueler, schule_user, klasse WHERE schueler.id=".$id." AND schueler.klasse=klasse.id AND klasse.schule=schule_user.schule AND schule_user.user=".$_SESSION['user_id']),0,"schule_user.user")==$_SESSION['user_id']) $return=true; break;
			case "schule": if (sql_result(db_conn_and_sql("SELECT user FROM schule_user WHERE schule_user.user=".$_SESSION['user_id']." AND schule_user.schule=".$id),0,"user")==$_SESSION['user_id']) $return=true; break;
			// TODO: duerfte nicht mehr gehen - noch noetig?
			case "schuljahr": if (sql_result(db_conn_and_sql("SELECT user FROM schule_user, schuljahr WHERE schuljahr.jahr=".$id." AND schuljahr.schule=schule_user.schule"),0,"user")==$_SESSION['user_id']) $return=true; break;
			case "sitzplan_klasse": if (sql_result(db_conn_and_sql("SELECT user FROM sitzplan_klasse WHERE sitzplan_klasse.id=".$id),0,"sitzplan_klasse.user")==$_SESSION['user_id']) $return=true; break;
			case "sitzplan": if (sql_result(db_conn_and_sql("SELECT user FROM sitzplan WHERE sitzplan.id=".$id),0,"user")==$_SESSION['user_id']) $return=true; break;
			case "sonstiges": if (sql_result(db_conn_and_sql("SELECT block.user FROM sonstiges, block_abschnitt, block WHERE sonstiges.id=".$id." AND sonstiges.abschnitt=block_abschnitt.abschnitt AND block_abschnitt.block=block.id"),0,"user")==$_SESSION['user_id']) $return=true; break;
			case "stundenplan": if (sql_result(db_conn_and_sql("SELECT user FROM stundenplan, fach_klasse WHERE stundenplan.id=".$id." AND stundenplan.fach_klasse=fach_klasse.id"),0,"user")==$_SESSION['user_id']) $return=true; break;
			case "test": if (sql_result(db_conn_and_sql("SELECT user FROM test WHERE test.id=".$id),0,"user")==$_SESSION['user_id']) $return=true; break;
			case "thema": if (sql_result(db_conn_and_sql("SELECT user FROM thema WHERE thema.id=".$id),0,"user")==$_SESSION['user_id']) $return=true; break;
			case "ueberschrift": if (sql_result(db_conn_and_sql("SELECT block.user FROM ueberschrift, block_abschnitt, block WHERE ueberschrift.id=".$id." AND ueberschrift.abschnitt=block_abschnitt.abschnitt AND block_abschnitt.block=block.id"),0,"user")==$_SESSION['user_id']) $return=true; break;
		}
		return $return;
	}

	// gibt 1 (read) bzw. 2 (write) zurueck, wenn das Element gelesen/geschrieben werden darf (sonst 0)
	function userrigths($was, $id) {
		$return=0;
		$id=injaway($id);
		
		// --- zugehoerige Schule herausfinden ---
		$schule=0;
		switch ($was) {
			case "admin":				 break;
			case "feste_feiertage":		 $schule=$id; break;
			case "schuljahresdaten":	 $schule=$id; break;
			case "ab-wochentausch":		 $schule=$id; break;
			case "schuldaten":			 $schule=$id; break;
			case "faecher":				 $schule=$id; break;
			case "benutzerverwaltung":   $schule=$id; break;
			case "personenverwaltung":   $schule=$id; break;
			case "stichtagsnoten":		 $schule=$id; break;
			case "stichtagsnotenrahmen": $schule=sql_fetch_assoc(db_conn_and_sql("SELECT schule FROM stichtagsnote_rahmen WHERE id=".$id)); $schule=$schule["schule"]; break;
			case "kopfnoten":			 $schule=$id; break;
			case "kopfnotenrahmen":		 $schule=sql_fetch_assoc(db_conn_and_sql("SELECT schule FROM kopfnote_rahmen WHERE id=".$id)); $schule=$schule["schule"]; break;
			case "kopfnoten_klasse":     $schule=sql_fetch_assoc(db_conn_and_sql("SELECT schule FROM klasse WHERE klasse.id=".$id)); $schule=$schule["schule"]; break;
			case "zensurentypen":		 $schule=$id; break;
			case "notenberechnungsvorlagen": $schule=$id; break;
			case "sitzplan_von_kl":		 $schule=sql_fetch_assoc(db_conn_and_sql("SELECT schule FROM klasse WHERE klasse.id=".$id)); $schule=$schule["schule"]; break;
			case "sitzanordnungen":		 $schule=$id; break;
			case "sitzanordnung":		 $schule=sql_fetch_assoc(db_conn_and_sql("SELECT schule FROM sitzplan WHERE sitzplan.id=".$id)); $schule=$schule["schule"]; break;
			//case "schuljahr": $schul_result=db_conn_and_sql("SELECT schule FROM schuljahr WHERE schuljahr.id=".$id); break;
			// klassendaten (endung und einschuljahr aendern) nur durch admin, einzelnutzer und schulleiter
			// schuelername, adresse, bemerkungen, ...
			case "schuelerdaten":        $schule=sql_fetch_assoc(db_conn_and_sql("SELECT schule FROM klasse WHERE klasse.id=".$id)); $schule=$schule["schule"]; break;
			case "schueler_verwaltung":  $schule=$id; break;
			// schueler loeschen
			case "einzelschueler":       $schule=sql_result(db_conn_and_sql("SELECT schule FROM schueler, klasse WHERE klasse.id=schueler.klasse AND schueler.id=".$id),0,"schule"); break;
			// verschieben
			case "schueler_verschieben": $schule=sql_result(db_conn_and_sql("SELECT schule FROM schueler, klasse WHERE klasse.id=schueler.klasse AND schueler.id=".$id),0,"schule"); break;
			
			// fachklasse_loeschen nur durch admin, einzelnutzer und schulleiter
			case "fachklasse_loeschen":  $schule=sql_fetch_assoc(db_conn_and_sql("SELECT schule FROM fach_klasse, klasse WHERE klasse.id=fach_klasse.klasse AND fach_klasse.id=".$id)); $schule=$schule["schule"]; break;
		}
		// 1 gast; 2 lehrer; 3 fachleiter; 4 schulleiter; 5 verwaltung; 6 einzelnutzer
		$benutzertyp_result=db_conn_and_sql("SELECT usertyp FROM schule_user WHERE user=".$_SESSION['user_id']." AND schule=".$schule);
		$typ["admin"]=0;
		$typ["schulleitung"]=0;
		$typ["fachleiter"]=0;
		$typ["fachlehrer"]=0;
		$typ["lehrer"]=0;
		$typ["gast"]=0;
		$typ["einzelnutzer"]=0;
		$typ["verwaltung"]=0;
		$typ["klassenlehrer"]=0;
		
		// --- Benutzertyp anhand der Schule herausfinden ---
		if (sql_result(db_conn_and_sql("SELECT admin FROM benutzer WHERE benutzer.id=".$_SESSION['user_id']),0,"admin")==1)
			$typ["admin"]=1;
		if ($schule!=0 and sql_result($benutzertyp_result,0,"usertyp")==1)
			$typ["gast"]=1;
		if ($schule!=0 and sql_result($benutzertyp_result,0,"usertyp")==2)
			$typ["lehrer"]=1;
		if ($schule!=0 and sql_result($benutzertyp_result,0,"usertyp")==3) {
			$typ["fachleiter"]=1;
			$typ["lehrer"]=1;
		}
		if ($schule!=0 and sql_result($benutzertyp_result,0,"usertyp")==4) {
			$typ["schulleitung"]=1;
			$typ["lehrer"]=1;
		}
		if ($schule!=0 and sql_result($benutzertyp_result,0,"usertyp")==5)
			$typ["verwaltung"]=1;
		if ($schule!=0 and sql_result($benutzertyp_result,0,"usertyp")==6)
			$typ["einzelnutzer"]=1;
		
		// --- Klassenlehrer herausfinden ---
		switch ($was) {
			//case "klassendaten":
			case "schueler_verschieben": $id=sql_result(db_conn_and_sql("SELECT klasse FROM schueler WHERE id=".$id),0,"klasse"); break;
			case "kopfnoten_klasse":
			case "sitzplan_von_kl":
			case "schuelerdaten":
				$klassenlehrer_result=db_conn_and_sql("SELECT klassenlehrer, klassenlehrer2 FROM klasse WHERE klasse.id=".$id);
				if (sql_result($klassenlehrer_result,0,"klassenlehrer")==$_SESSION['user_id'] or sql_result($klassenlehrer_result,0,"klassenlehrer2")==$_SESSION['user_id'])
					$typ["klassenlehrer"]=1;
				break;
		}
		
		// --- Rechte setzen ( 1= read; 2= write )---
		//if ($typ["admin"]) echo "adm";
		//if ($typ["schulleitung"]) echo "sl";
		//if ($typ["einzelnutzer"]) echo "en";
		//if ($typ["klassenlehrer"]) echo "kl";
		//if ($typ["fachlehrer"]) echo "fl";
		//if ($typ["lehrer"]) echo "L";
		//if ($typ["fachleiter"]) echo "FL";
		
		switch ($was) {
			// allgemeine Einstellungen
			//case "DB-Anbindung":  break;
			case "admin":					if ($typ["admin"]) $return=2; break;
			case "feste_feiertage":			if ($typ["admin"] or $typ["schulleitung"] or $typ["einzelnutzer"]) $return=2; break;
			case "schuljahresdaten":		if ($typ["admin"] or $typ["schulleitung"] or $typ["einzelnutzer"]) $return=2; break;
			case "schuldaten":				if ($typ["admin"] or $typ["schulleitung"] or $typ["einzelnutzer"]) $return=2; break;
			case "faecher":					if ($typ["admin"] or $typ["schulleitung"] or $typ["einzelnutzer"]) $return=2; break;
			case "ab-wochentausch":			if ($typ["admin"] or $typ["schulleitung"] or $typ["einzelnutzer"]) $return=2; break;
			//case "allgemeine SJ-Einstellungen":
			case "benutzerverwaltung":      if ($typ["admin"] or $typ["schulleitung"]) $return=2; break;
			case "personenverwaltung":      if ($typ["admin"] or $typ["schulleitung"] or $typ["verwaltung"]) $return=2; break;
			case "zensurentypen":			if ($typ["admin"] or $typ["schulleitung"] or $typ["einzelnutzer"]) $return=2; break;
			case "notenberechnungsvorlagen": if ($typ["admin"] or $typ["schulleitung"] or $typ["einzelnutzer"]) $return=2; break;
			
			// Klassen - Schueler
			case "klassendaten":        	if ($typ["admin"] or $typ["schulleitung"] or $typ["einzelnutzer"]) $return=2; break; // verwaltung?
			case "kopfnoten_klasse":
			case "schuelerdaten":    	    if ($typ["lehrer"] or $typ["fachlehrer"] or $typ["verwaltung"]) $return=1;
											if ($typ["admin"] or $typ["schulleitung"] or $typ["klassenlehrer"] or $typ["einzelnutzer"]) $return=2; break;
			case "schueler_verwaltung":		if ($typ["admin"] or $typ["schulleitung"] or $typ["verwaltung"]) $return=2; break;
								
			case "einzelschueler":      	if ($typ["admin"] or $typ["schulleitung"] or $typ["einzelnutzer"]) $return=2; break;
			case "schueler_verschieben":	if ($typ["admin"] or $typ["schulleitung"] or $typ["einzelnutzer"] or $typ["klassenlehrer"]) $return=2; break;
			
			// Fachklassen
			case "fachklasse_loeschen":  	if ($typ["admin"] or $typ["schulleitung"] or $typ["einzelnutzer"]) $return=2; break;
			// Fehlzeiten
			// Elternabend
			
			// Zensuren
			case "kopfnoten":				if ($typ["lehrer"] or $typ["fachlehrer"]) $return=1;
											if ($typ["admin"] or $typ["schulleitung"]) $return=2; break;
			case "kopfnotenrahmen":			if ($typ["lehrer"] or $typ["fachlehrer"]) $return=1;
											if ($typ["admin"] or $typ["schulleitung"]) $return=2; break;
			case "stichtagsnoten":			if ($typ["lehrer"] or $typ["fachlehrer"]) $return=1;
											if ($typ["admin"] or $typ["schulleitung"]) $return=2; break;
			case "stichtagsnotenrahmen":	if ($typ["lehrer"] or $typ["fachlehrer"]) $return=1;
											if ($typ["admin"] or $typ["schulleitung"]) $return=2; break;
			
			// Sitzplan
			case "sitzplan_von_kl":			if ($typ["lehrer"] or $typ["fachlehrer"] or $typ["verwaltung"]) $return=1;
											if ($typ["admin"] or $typ["schulleitung"] or $typ["klassenlehrer"]) $return=2; break;
			case "sitzanordnungen":
			case "sitzanordnung":  			if ($typ["admin"] or $typ["schulleitung"] or $typ["einzelnutzer"]) $return=2; break;
											
			// Unterrichtsplanung
			
		}
		return $return;
	}
	
    // ---------------- Zip-Funktionen --------------------------
    function unzip($zipfile)
    {
        $zip = zip_open($zipfile);
        while ($zip_entry = zip_read($zip))    {
            zip_entry_open($zip, $zip_entry);
            if (substr(zip_entry_name($zip_entry), -1) == '/') {
                $zdir = substr(zip_entry_name($zip_entry), 0, -1);
                if (file_exists($zdir)) {
                    //trigger_error('Directory "<b>' . $zdir . '</b>" exists', E_USER_ERROR);
                    echo 'Directory "<b>' . $zdir . '</b>" exists<br />';
                    return false;
                }
                mkdir($zdir);
            }
            else {
                $name = zip_entry_name($zip_entry);
                if (file_exists($name)) {
                    //trigger_error('File "<b>' . $name . '</b>" exists', E_USER_ERROR);
                    echo 'File "<b>' . $name . '</b>" exists<br />';
                    return false;
                }
                $fopen = fopen($name, "w");
                fwrite($fopen, zip_entry_read($zip_entry, zip_entry_filesize($zip_entry)), zip_entry_filesize($zip_entry));
            }
            zip_entry_close($zip_entry);
        }
        zip_close($zip);
        return true;
    }
    
    
    // deprecated - datenbankstruktur ist sowieso ganz anders
    function fk_zu_schuljahrid($fk) {
		$db=new db;
		$jahr=$db->aktuelles_jahr();
		$sj_id_query=db_conn_and_sql("SELECT `schuljahr`.`id`
			FROM `fach_klasse`,`klasse`,`schuljahr`
			WHERE `fach_klasse`.`id`=".$fk."
				AND `fach_klasse`.`klasse`=`klasse`.`id`
				AND (`klasse`.`schule`=`schuljahr`.`schule` OR `schuljahr`.`schule`=0)
				AND `schuljahr`.`jahreszahl`=".$jahr."
			ORDER BY `schuljahr`.`schule`DESC");
		$sj_id=sql_fetch_assoc($sj_id_query);
		return $sj_id["id"];
	}
	
	function handle_backedup_files($conn_id, $ftp_data_path, $pfad, $grundpfad, $lb, $id, $url, $hashes_on_server, $hash_i) {
		$pfadebenen=lernbereich2pfadebenen($lb);
		$active_hash=md5_file($pfad."daten/".$pfadebenen[0]."/".$pfadebenen[1]."/".$pfadebenen[2]."/".$url);
		
		// in vorhandener hashes-Datei derzeitige ID suchen
		while ($id>$hashes_on_server[$hash_i][0] and $hash_i<count($hashes_on_server)) {
			echo "L&ouml;sche ".$hashes_on_server[$hash_i][1]." auf FTP-Server<br />"; //sql_result($grafics,$i,"grafik.id").">".$hashes_on_server[$hash_i][0]."-".$hash_i."<".count($hashes_on_server)
			ftp_delete($conn_id, $ftp_data_path."/".$hashes_on_server[$hash_i][1]);
			$hash_i++;
		}
		//echo $hash_i." - ".$i."<br />";
		if ($id<$hashes_on_server[$hash_i][0] or $hash_i>=count($hashes_on_server)) {
			echo $url." neu erstellen (hochladen)<br />";
			pfad_und_datei_ftpupload($conn_id, $ftp_data_path, $pfad, $grundpfad, $pfadebenen, $url);
		}
		if ($id==$hashes_on_server[$hash_i][0]) {
			if (trim($hashes_on_server[$hash_i][2])!=trim($active_hash)) {
				echo $url." unterscheidet sich von der lokalen Version der Datei - Serverdatei l&ouml;schen, neu hochladen<br />"; // $hashes_on_server[$hash_i][2]."!=".$active_hash
				ftp_delete($conn_id, $ftp_data_path."/".$hashes_on_server[$hash_i][1]);
				pfad_und_datei_ftpupload($conn_id, $ftp_data_path, $pfad, $grundpfad, $pfadebenen, $url);
			}
			//else
			//	echo "alles ok mit ".sql_result($grafics,$i,"grafik.url")."<br />";
			$hash_i++;
		}
		
		return array($id.";".$pfadebenen[0]."/".$pfadebenen[1]."/".$pfadebenen[2]."/".$url.";".$active_hash."\n", $hash_i);
	}
	
	function pfad_und_datei_ftpupload($conn_id,$ftp_path,$pfad,$grundpfad,$pfadebenen,$url) {
		if (ftp_mkdir ($conn_id, $ftp_path.'/'.$grundpfad.'/'.$pfadebenen[0]) !== FALSE)
				echo '';
			//else
			//	echo 'Anlegen eines neuen Verzeichnisses war NICHT erfolgreich!<br />';
			if (ftp_mkdir ($conn_id, $ftp_path.'/'.$grundpfad.'/'.$pfadebenen[0].'/'.$pfadebenen[1]) !== FALSE)
				echo '';
			//else
			//	echo 'Anlegen eines neuen Verzeichnisses war NICHT erfolgreich!<br />';
			if (ftp_mkdir ($conn_id, $ftp_path.'/'.$grundpfad.'/'.$pfadebenen[0].'/'.$pfadebenen[1].'/'.$pfadebenen[2]) !== FALSE)
				echo '';
			//else
			//	echo 'Anlegen eines neuen Verzeichnisses war NICHT erfolgreich!<br />';
			ftp_put ($conn_id, $ftp_path.'/'.$grundpfad.'/'.$pfadebenen[0].'/'.$pfadebenen[1].'/'.$pfadebenen[2]."/tmb_".$url, $pfad.$grundpfad.'/'.$pfadebenen[0].'/'.$pfadebenen[1].'/'.$pfadebenen[2]."/tmb_".$url, FTP_BINARY);
			if (ftp_put ($conn_id, $ftp_path.'/'.$grundpfad.'/'.$pfadebenen[0].'/'.$pfadebenen[1].'/'.$pfadebenen[2]."/".$url, $pfad.$grundpfad.'/'.$pfadebenen[0].'/'.$pfadebenen[1].'/'.$pfadebenen[2]."/".$url, FTP_BINARY) === TRUE)
				echo '';
			else
				echo 'Der Upload war NICHT erfolgreich!<br />';
	}
	
	function lernbereich2pfadebenen($lb) {
		$result=db_conn_and_sql ( 'SELECT faecher.kuerzel AS f_kuerzel, schulart.kuerzel AS s_kuerzel, lernbereich.klassenstufe
                       FROM `lernbereich`,`lehrplan`,`schulart`,`faecher`
                       WHERE `lernbereich`.`id`='.$lb.'
                         AND `lernbereich`.`lehrplan`=`lehrplan`.`id`
                         AND `lehrplan`.`schulart` = `schulart`.`id`
                         AND `lehrplan`.`fach` = `faecher`.`id`
                       ORDER BY `lehrplan`.`schulart`,`lehrplan`.`bundesland`,`lehrplan`.`jahr`,`lehrplan`.`fach`,`lernbereich`.`klassenstufe`,`lernbereich`.`nummer`' );
        $row=sql_fetch_assoc($result);
		$ebene1=html_umlaute($row["f_kuerzel"]);
		$ebene2=html_umlaute($row["s_kuerzel"]);
		$ebene3=$row["klassenstufe"];
		return array($ebene1, $ebene2, $ebene3);
	}

	function pfad_und_dateiname($lernbereich, $typ, $orginaldateiname,$tempname, $pfad="../") {
        if (!isset($pfad))
            $pfad='../';
		// damit wieder eingestellte Dateien nicht noch weitere solche Anfaenge bekommen (zB 2_2_2_hallo.pdf):
		if (substr($orginaldateiname,0,2)=='1_' or substr($orginaldateiname,0,2)=='2_' or substr($orginaldateiname,0,2)=='3_')
			$orginaldateiname=substr($orginaldateiname,2);
		if (substr($orginaldateiname,0,7)=='grafik_')
			$orginaldateiname=substr($orginaldateiname,7);
		
		$orginaldateiname=iconv("ISO-8859-1", "ASCII//TRANSLIT", $orginaldateiname);
		
		$ebene=lernbereich2pfadebenen($lernbereich);
		$grundpfad=$pfad.'daten';
		@mkdir ($grundpfad.'/'.$ebene[0], 0755);
		@mkdir ($grundpfad.'/'.$ebene[0].'/'.$ebene[1], 0755);
		@mkdir ($grundpfad.'/'.$ebene[0].'/'.$ebene[1].'/'.$ebene[2], 0755);
		
		if (!file_exists($grundpfad.'/'.$ebene[0].'/'.$ebene[1].'/'.$ebene[2].'/'.$typ.'_'.$orginaldateiname))
            $num=0;
		else
            $num=1;
		while (file_exists($grundpfad.'/'.$ebene[0].'/'.$ebene[1].'/'.$ebene[2].'/'.$typ.'_'.$num.'_'.$orginaldateiname))
            $num++;
        
		if ($num==0)
            $dateiname_kurz=$typ.'_'.$orginaldateiname;
		else
            $dateiname_kurz=$typ.'_'.$num.'_'.$orginaldateiname;
		
		copy("$tempname", $grundpfad.'/'.$ebene[0].'/'.$ebene[1].'/'.$ebene[2].'/'.$dateiname_kurz);
		chmod($grundpfad.'/'.$ebene[0].'/'.$ebene[1].'/'.$ebene[2].'/'.$dateiname_kurz,0755);
		
		/*$dateiname_kl=$grundpfad.'/'.$ebene1.'/'.$ebene2.'/'.$ebene3.'/thumb_'.$dateiname_kurz;
		if (!empty($tempname_klein)) {
			copy("$tempname_klein", $dateiname_kl);
			chmod($dateiname_kl,0755);
		}
		*/
		return array("pfad"=>$grundpfad.'/'.$ebene[0].'/'.$ebene[1].'/'.$ebene[2].'/',
							"datei"=>$dateiname_kurz,
							"mit_pfad"=>$grundpfad.'/'.$ebene[0].'/'.$ebene[1].'/'.$ebene[2].'/'.$dateiname_kurz);
	}
    
    //deprecated (bestimmt nicht mehr in Verwendung)
    function refresh_files($grafic_or_file, $type, $id, $text) {
        // Text durchsuchen
        $grafics_in_text=explode("[grafic;", $text);
        $files_in_text=explode("[file;", $text);
        
        $grafic_files=array();
        $the_files=array();
        
        // gefundene Grafiken in ein Array schreiben
        if (count($grafics_in_text)>0)
            for ($i=1; $i<count($grafics_in_text); $i++) {
                $my_grafic=explode(";", $grafics_in_text[$i]);
                $grafic_files[]=$my_grafic[0];
            }
        
        // gefundene Dateien in ein Array schreiben
        if (count($files_in_text)>0)
            for ($i=1; $i<count($files_in_text); $i++) {
                $my_file=explode("]", $files_in_text[$i]);
                $the_files[]=$my_file[0];
            }
        
        // Typ: Text
        if ($type=="text") {
            // alle einem Text zugeordneten Grafiken / Dateien loeschen
            if (count($grafic_files)>0 and ($grafic_or_file=="both" or $grafic_or_file=="grafic")) {
                db_conn_and_sql("DELETE FROM grafik_abschnitt WHERE abschnitt=".$id);
                for ($n=0; $n<count($grafic_files); $n++)
                    db_conn_and_sql("INSERT INTO grafik_abschnitt (grafik, abschnitt) VALUES (".$grafic_files[$n].", ".$id.");");
            }
            if (count($the_files)>0 and ($grafic_or_file=="both" or $grafic_or_file=="file")) {
                db_conn_and_sql("DELETE FROM link_abschnitt WHERE abschnitt=".$id);
                for ($n=0; $n<count($the_files); $n++)
                    db_conn_and_sql("INSERT INTO link_abschnitt (link, abschnitt) VALUES (".$the_files[$n].", ".$id.");");
            }
        }
        
        // Typ: Aufgabe
        if ($type=="task") {
            // alle einem Text zugeordneten Grafiken / Dateien loeschen
            if (count($grafic_files)>0 and ($grafic_or_file=="both" or $grafic_or_file=="grafic")) {
                db_conn_and_sql("DELETE FROM grafik_aufgabe WHERE aufgabe=".$id);
                for ($n=0; $n<count($grafic_files); $n++)
                    db_conn_and_sql("INSERT INTO grafik_aufgabe (grafik, aufgabe) VALUES (".$grafic_files[$n].", ".$id.");");
            }
        }
    }
    
    function modulo($zahl1, $zahl2) {
        return round((($zahl1/$zahl2)-floor($zahl1/$zahl2))*$zahl2);
    }
    
    function ostern ($schuljahr) {
        $a=modulo($schuljahr,19);
        $b=round($schuljahr / 100);
        $c=modulo($schuljahr,100);
        $d=floor($b / 4);
        $e=modulo($b,4);
        $f=floor(($b + 8) / 25);
        $g=floor(($b - $f + 1) / 3);
        $h=modulo((19 * $a + $b - $d - $g + 15), 30);
        $i=floor($c / 4);
        $k=modulo($c, 4);
        $l=modulo((32 + 2 * $e + 2 * $i - $h - $k), 7);
        $m=floor(($a + 11 * $h + 22 * $l) / 451);
        $n=floor(($h + $l - 7 * $m + 114) / 31);
        $p=modulo(($h + $l - 7 * $m + 114), 31);
        $date=$p + 1;
        return mktime(0,0,0,$n,$date,$schuljahr);
    }
    
    function besondere_tage($schuljahr, $schule) {
		// buss und bettag
        $bubt=mktime(0,0,0,11,22,$schuljahr);
        while(date("w",$bubt)!=3)
			$bubt-=60*60*24;
		
        $ostern=ostern($schuljahr+1);
        
        $feste_feiertage_query=db_conn_and_sql("SELECT feste_feiertage.name
			FROM feste_feiertage, feiertage_schule
			WHERE feiertage_schule.ff=feste_feiertage.id
				AND feiertage_schule.aktiv=1
				AND feiertage_schule.schule=".$schule);
		$i=0;
        while ($feste_feiertage_row=sql_fetch_assoc($feste_feiertage_query)) {
            $tag[$i]['name']=$feste_feiertage_row["name"];
            switch ($tag[$i]['name']) { // ID waere besser, aber so ists uebersichtlicher
                case "Mari&auml; Himmelfahrt":    $tag[$i]['datum']=mktime(0,0,0,8,15,$schuljahr); break;
                case "Tag der Deutschen Einheit": $tag[$i]['datum']=mktime(0,0,0,10,3,$schuljahr); break;
                case "Reformationstag":           $tag[$i]['datum']=mktime(0,0,0,10,31,$schuljahr); break;
                case "Allerheiligen":             $tag[$i]['datum']=mktime(0,0,0,11,1,$schuljahr); break;
                case "Bu&szlig;- und Bettag":     $tag[$i]['datum']=$bubt; break;
                case "Heilige Drei K&ouml;nige":  $tag[$i]['datum']=mktime(0,0,0,1,6,$schuljahr+1); break;
                case "Karfreitag":                $tag[$i]['datum']=$ostern-60*60*24*2; break;
                case "Ostermontag":               $tag[$i]['datum']=$ostern+60*60*24; break;
                case "Christi Himmelfahrt":       $tag[$i]['datum']=$ostern+60*60*24*39; break;
                case "Pfingstmontag":             $tag[$i]['datum']=$ostern+60*60*24*50; break;
                case "Fronleichnam":              $tag[$i]['datum']=$ostern+60*60*24*60; break;
                case "Tag der Arbeit":            $tag[$i]['datum']=mktime(0,0,0,5,1,$schuljahr+1); break;
                default:                          $tag[$i]['datum']=mktime(0,0,0,1,1,$schuljahr+1); break;
            }
            $i++;
        }
        return $tag;
    }
	
	function naechster_wochentag_von_strichdatum($strichdatum) {
		$elemente=explode("-",$strichdatum);
		$nextdate=mktime(0,0,0, $elemente[1] , $elemente[2] , $elemente[0] )+3600*24;
		// falls Samstag oder Sonntag ist, noch ein Tag dazu
		while (date("w", $nextdate)==0 or date("w", $nextdate)==6)
			$nextdate+=3600*24;
		return date("Y-m-d", $nextdate);
	}
	
	function schuljahr_start_ende($jahr, $schule) {
        $start_ende_result=db_conn_and_sql("SELECT beginn, ende FROM ferien, schule
			WHERE schule.id=".$schule."
				AND (schuljahr=".$jahr." OR schuljahr=".($jahr+1).")
				AND ferien.welche=0
				AND ferien.bundesland=schule.bundesland");
		$start=sql_fetch_assoc($start_ende_result);
		$ende=sql_fetch_assoc($start_ende_result);
		if ($start["ende"]!="")
			$start["ende"]=naechster_wochentag_von_strichdatum($start["ende"]);
		return array("start"=>$start["ende"], "ende"=>$ende["beginn"]);
	}
	
    function schuljahr_uebersicht($schuljahr,$schule) {
		$bundesland=sql_result(db_conn_and_sql("SELECT bundesland FROM schule WHERE id=".$schule),0,"bundesland");
		
        $feriennamen=array('Herbst','Weihnachts','Winter','Oster','Pfingst','Pfingst');
        $start_ende=schuljahr_start_ende($schuljahr, $schule);
        
        $schuljahr_jahreszahl=$schuljahr;
        $tag=besondere_tage($schuljahr_jahreszahl, $schule);
        $hilf=explode("-",$start_ende["start"]);
        $durchzaehlen=@mktime(1,0,0,$hilf[1],$hilf[2],$hilf[0]);
        
        $hilf=explode("-",$start_ende["ende"]);
        if ($hilf[2]>0)
			$ende=mktime(1,0,0,$hilf[1],$hilf[2],$hilf[0]);
        $ferien_result=db_conn_and_sql("SELECT `ferien`.* FROM `ferien`, `schule`
			WHERE `ferien`.`bundesland`=`schule`.`bundesland` AND `schule`.`id`=".$schule);
        
        $i=0;
        while($ferien_row=sql_fetch_assoc($ferien_result)) {
            $hilf=explode("-",$ferien_row["beginn"]); $ferien[$i]["beginn"]=mktime(0,0,0,$hilf[1],$hilf[2],$hilf[0]);
            $hilf=explode("-",$ferien_row["ende"]); $ferien[$i]["ende"]=mktime(0,0,0,$hilf[1],$hilf[2],$hilf[0])+60*60*20;
            $ferien[$i]["name"]=$feriennamen[$ferien_row["welche"]-1]."ferien";
            $i++;
        }
        $beweglich_result=db_conn_and_sql("SELECT * FROM `bewegliche_feiertage` WHERE `bewegliche_feiertage`.`schuljahr`=".$schuljahr_jahreszahl." AND `bewegliche_feiertage`.`schule`=".$schule);
        $i=0;
        while($beweglich_row=sql_fetch_assoc($beweglich_result)) {
            $hilf=explode("-",$beweglich_row["von"]);
            $beweglich[$i]["beginn"]=mktime(0,0,0,$hilf[1],$hilf[2],$hilf[0]);
            $hilf=explode("-",$beweglich_row["bis"]);
            if ($hilf[0]<1)
				$beweglich[$i]["ende"]=$beweglich[$i]["beginn"]+60*60*20;
			else
				$beweglich[$i]["ende"]=mktime(0,0,0,$hilf[1],$hilf[2],$hilf[0])+60*60*20;
            $beweglich[$i]["name"]=html_umlaute($beweglich_row["beschreibung"]);
            $beweglich[$i]["fehltage"]=$beweglich_row["fehltage"];
            $i++;
        }
        $ab_wochenneustart=db_conn_and_sql("SELECT * FROM woche_ab WHERE schuljahr=".$schuljahr." AND schule=".$schule." ORDER BY datum");
        $ab_wochenneustart_row=sql_fetch_assoc($ab_wochenneustart);
        $wochenneustart_zaehler=0; $woche_typ=1;
        
        $woche=0; $lfd_woche=1;
        //auf Montag setzen und schon mitzaehlen
        // von Fehlzeiten aus gehts nicht, weil von Pfad abhaengig
        if(!isset($ferien))
			echo 'Sie m&uuml;ssen zun&auml;chst die <a href="index.php?tab=einstellungen&amp;auswahl=schuljahr&amp;jahr='.$schuljahr.'">Ferien f&uuml;r Ihr Bundesland eintragen</a>.';
        while (date("w",$durchzaehlen)!=1)
			$durchzaehlen+=60*60*24;
        while ($durchzaehlen<$ende) {
            // A/B-woche ausrechnen
            if($wochenneustart_zaehler<sql_num_rows($ab_wochenneustart) and date("Y-m-d",$durchzaehlen)>$ab_wochenneustart_row["datum"]) {
                $woche_typ++; if($woche_typ==2) $woche_typ=0;
                $wochenneustart_zaehler++;
                $ab_wochenneustart_row=sql_fetch_assoc($ab_wochenneustart);
            }
            if (modulo(date("W",$durchzaehlen),2)==$woche_typ)
                $wochentage[1][$woche]["a_woche"]=1;
                else $wochentage[1][$woche]["a_woche"]=0;
            
            // Datum fuer Mo-Fr festlegen
            $wochentage[1][$woche]["datum"]=$durchzaehlen;  
            $wochentage[2][$woche]["datum"]=$durchzaehlen+60*60*24;
            $wochentage[3][$woche]["datum"]=$durchzaehlen+60*60*24*2;
            $wochentage[4][$woche]["datum"]=$durchzaehlen+60*60*24*3;
            $wochentage[5][$woche]["datum"]=$durchzaehlen+60*60*24*4;
            for ($i=1;$i<=5;$i++) {
                //$wochentage[$i][$woche]["gerade"]=modulo(date("W",$durchzaehlen)/2); das ist dann doch quatsch
                $wochentage[$i][$woche]["unterricht"]=1;
                foreach($tag as $value)
					if (date("Y-m-d",$wochentage[$i][$woche]["datum"])==date("Y-m-d",$value["datum"]))
						$wochentage[$i][$woche]["unterricht"]=$value["name"];
                if(isset($ferien))
					foreach($ferien as $value)
						if ($wochentage[$i][$woche]["datum"]>=$value["beginn"] and $wochentage[$i][$woche]["datum"]<$value["ende"])
							$wochentage[$i][$woche]["unterricht"]=$value["name"];
                if (isset($beweglich))
					foreach($beweglich as $value)
						if ($wochentage[$i][$woche]["datum"]>=$value["beginn"] and $wochentage[$i][$woche]["datum"]<$value["ende"]) {
							$wochentage[$i][$woche]["unterricht"]=$value["name"];
							if ($value["fehltage"])
								$wochentage[$i][$woche]["fehltage_mitzaehlen"]=1;
						}
            }
            
            if($wochentage[1][$woche]["unterricht"]==1 or $wochentage[2][$woche]["unterricht"]==1 or $wochentage[3][$woche]["unterricht"]==1 or $wochentage[4][$woche]["unterricht"]==1 or $wochentage[5][$woche]["unterricht"]==1) {
				$wochentage[1][$woche]["lfd_woche"]=$lfd_woche;
				$lfd_woche++;
			}
            
            $durchzaehlen+=60*60*24*7;
            $woche++;
        }
        return $wochentage;  
    }
    
	function gehoert_zur_gruppe($fach_klasse, $schueler) {
		$schueler = db_conn_and_sql ('SELECT `schueler`.`id`
				FROM `schueler`,`gruppe`
				WHERE `gruppe`.`schueler`=`schueler`.`id`
					AND `gruppe`.`fach_klasse`='.$fach_klasse.'
					AND `schueler`.`id`='.$schueler);
		if (sql_num_rows(db_conn_and_sql("SELECT `schueler`.`id`
                FROM `schueler`, `gruppe`
                WHERE `gruppe`.`schueler`=`schueler`.`id`
                    AND `gruppe`.`fach_klasse`=".$fach_klasse))==0
				or sql_num_rows($schueler)>0)
			return 1;
		else
			return 0;
	}
	
	function schueler_von_fachklasse($fach_klasse) {
            $schueler = db_conn_and_sql("SELECT schueler.*
                FROM schueler, gruppe
                WHERE gruppe.fach_klasse=".$fach_klasse."
                    AND gruppe.schueler=schueler.id AND schueler.aktiv=1
                ORDER BY gruppe.position, schueler.klasse, schueler.position, schueler.name,schueler.vorname");
                    
            if (sql_num_rows($schueler)<1)
                $schueler = db_conn_and_sql ('SELECT *
                    FROM `fach_klasse`, `schueler`
                    WHERE `schueler`.`klasse`=`fach_klasse`.`klasse`
                        AND `fach_klasse`.`id`='.$fach_klasse.' AND schueler.aktiv=1
                    ORDER BY `schueler`.`position`, `schueler`.`name`,`schueler`.`vorname`');
         return $schueler;
    }
    
    function berechnung_notendurchschnitt_1($gruppen_schnitt, $wert, $faktor, $notengruppen_faktor) {
		$gruppen_schnitt["berechnung_zaehler_alles_1"].=$gruppen_schnitt["+"].$wert;
		$gruppen_schnitt["berechnung_nenner_alles_1"]+=$gruppen_schnitt["+"].kommazahl($faktor);
		$gruppen_schnitt["berechnung_zaehler"].=$gruppen_schnitt["+"]."(".$wert."*".kommazahl($faktor).")";
		$gruppen_schnitt["berechnung_nenner"].=$gruppen_schnitt["+"].kommazahl($faktor);
		$gruppen_schnitt["berechnung_ngfaktor"]=kommazahl($notengruppen_faktor);
		$gruppen_schnitt["+"]="+";
		return $gruppen_schnitt;
	}
    
    function berechnung_notendurchschnitt_ausgabe($schnitt_berechnung, $einzelgruppe, $alles_faktor_1) {
  		if ($einzelgruppe["berechnung_ngfaktor"]==1)
			$ng_faktor="";
		else
			$ng_faktor=" (".$einzelgruppe["berechnung_ngfaktor"]."-fach)";
		if ($alles_faktor_1)
			$schnitt_berechnung.="<br />Gruppe".$ng_faktor.": (".$einzelgruppe["berechnung_zaehler_alles_1"].")/".$einzelgruppe["berechnung_nenner_alles_1"]." = ".kommazahl(round(($einzelgruppe["notengruppen_faktor"]*($einzelgruppe["zaehler"]/$einzelgruppe["nenner"])/$einzelgruppe["notengruppen_faktor"]),2));
		else
			$schnitt_berechnung.="<br />Gruppe (".$einzelgruppe["berechnung_ngfaktor"]."-fach): (".$einzelgruppe["berechnung_zaehler"].")/(".$einzelgruppe["berechnung_nenner"].") = ".kommazahl(round(($einzelgruppe["notengruppen_faktor"]*($einzelgruppe["zaehler"]/$einzelgruppe["nenner"])/$einzelgruppe["notengruppen_faktor"]),2));
		return $schnitt_berechnung;
	}
    
    function notendurchschnitt_berechnen($durchschnitt_hier, $schueler_grundlagen_hier, $fach_id) { //, $neu_berechnen
		$schnitt_HJ1=array("zaehler"=>"0", "nenner"=>"0");
		$schnitt_HJ2=array("zaehler"=>"0", "nenner"=>"0");
		$schnitt_GJ =array("zaehler"=>"0", "nenner"=>"0");
		$gruppen_schnitt_HJ1='';
		$gruppen_schnitt_HJ2='';
		$gruppen_schnitt_GJ ='';
		
		//$einzelnoten_array=array();
		//if (count($durchschnitt_hier)>0)
		//foreach ($durchschnitt_hier as $value) {
		//	$einzelnoten_array[]=$value["notenbeschreibung_id"].";".$value["wert"].";".$value["punktzahl_mit_komma"].";".$value["gesamtpunktzahl"].";".$value["datum"].";".$value["halbjahresnote"].";".$value["notenzusatz"].";".$value["mitzaehlen"].";".$value["kommentar"];
		//}
		
		if (count($durchschnitt_hier)>0)
		foreach ($durchschnitt_hier as $value) if ($value["mitzaehlen"]) {
			// TODO: irgendwie $value["zurueckgegeben"] einbeziehen und mehrere Durchschnitte berechnen
			
			// fuer die Ganzjahresnote von Bedeutung?
			if ($value["halbjahresnote"]) {
				// Notengruppen? notengruppe notengruppen_faktor
				if ($value["notengruppe"]>0) {
					$gruppen_schnitt_HJ1[$value["notengruppe"]]["notengruppen_faktor"]=$value["notengruppen_faktor"];
					$gruppen_schnitt_HJ1[$value["notengruppe"]]["zaehler"]+=$value["faktor"]*$value["wert"];
					$gruppen_schnitt_HJ1[$value["notengruppe"]]["nenner"]+=$value["faktor"];
				}
				else {
					$schnitt_HJ1["zaehler"]+=$value["faktor"]*$value["wert"];
					$schnitt_HJ1["nenner"]+=$value["faktor"];
				}
			}
			else {
				if ($value["notengruppe"]>0) {
					$gruppen_schnitt_HJ2[$value["notengruppe"]]["notengruppen_faktor"]=$value["notengruppen_faktor"];
					$gruppen_schnitt_HJ2[$value["notengruppe"]]["zaehler"]+=$value["faktor"]*$value["wert"];
					$gruppen_schnitt_HJ2[$value["notengruppe"]]["nenner"]+=$value["faktor"];
				}
				else {
					$schnitt_HJ2["zaehler"]+=$value["faktor"]*$value["wert"];
					$schnitt_HJ2["nenner"]+=$value["faktor"];
				}
			}
			
			if ($value["notengruppe"]>0) {
				$gruppen_schnitt_GJ[$value["notengruppe"]]["notengruppen_faktor"]=$value["notengruppen_faktor"];
				$gruppen_schnitt_GJ[$value["notengruppe"]]["zaehler"]+=$value["faktor"]*$value["wert"];
				$gruppen_schnitt_GJ[$value["notengruppe"]]["nenner"]+=$value["faktor"];
				
				// Ausgabe Berechnung
				if (!isset($gruppen_schnitt_GJ[$value["notengruppe"]]["alles_faktor_1"]))
					$gruppen_schnitt_GJ[$value["notengruppe"]]["alles_faktor_1"]=true;
				if ($value["faktor"]!=1)
					$gruppen_schnitt_GJ[$value["notengruppe"]]["alles_faktor_1"]=false;
				
				if ($value["halbjahresnote"]) {
					$gruppen_schnitt_HJ1[$value["notengruppe"]]=berechnung_notendurchschnitt_1($gruppen_schnitt_HJ1[$value["notengruppe"]], $value["wert"], $value["faktor"], $value["notengruppen_faktor"]);
				}
				else {
					$gruppen_schnitt_HJ2[$value["notengruppe"]]=berechnung_notendurchschnitt_1($gruppen_schnitt_HJ2[$value["notengruppe"]], $value["wert"], $value["faktor"], $value["notengruppen_faktor"]);
				}
				$gruppen_schnitt_GJ[$value["notengruppe"]]=berechnung_notendurchschnitt_1($gruppen_schnitt_GJ[$value["notengruppe"]], $value["wert"], $value["faktor"], $value["notengruppen_faktor"]);
			}
			else { // alle Zensuren gleichwertig
				$schnitt_GJ["zaehler"]+=$value["faktor"]*$value["wert"];
				$schnitt_GJ["nenner"]+=$value["faktor"];
			}
			
		}
		
		// Notengruppen dazu
		if ($gruppen_schnitt_HJ1!="" and isset($gruppen_schnitt_HJ1))
			foreach ($gruppen_schnitt_HJ1 as $einzelgruppe) {
				$schnitt_HJ1["zaehler"]+=$einzelgruppe["notengruppen_faktor"]*($einzelgruppe["zaehler"]/$einzelgruppe["nenner"]);
				$schnitt_HJ1["nenner"]+=$einzelgruppe["notengruppen_faktor"];
				
				// Berechnung Ausgabe
				$schnitt_HJ1["berechnung"]=berechnung_notendurchschnitt_ausgabe($schnitt_HJ1["berechnung"], $einzelgruppe, $gruppen_schnitt_GJ[$value["notengruppe"]]["alles_faktor_1"]);
			}
		if ($gruppen_schnitt_HJ2!="" and isset($gruppen_schnitt_HJ2))
			foreach ($gruppen_schnitt_HJ2 as $einzelgruppe) {
				$schnitt_HJ2["zaehler"]+=$einzelgruppe["notengruppen_faktor"]*($einzelgruppe["zaehler"]/$einzelgruppe["nenner"]);
				$schnitt_HJ2["nenner"]+=$einzelgruppe["notengruppen_faktor"];
				
				// Berechnung Ausgabe
				$schnitt_HJ2["berechnung"]=berechnung_notendurchschnitt_ausgabe($schnitt_HJ2["berechnung"], $einzelgruppe, $gruppen_schnitt_GJ[$value["notengruppe"]]["alles_faktor_1"]);
			}
		
		if ($gruppen_schnitt_GJ!="")
		foreach ($gruppen_schnitt_GJ as $einzelgruppe) {
			$schnitt_GJ["zaehler"]+=$einzelgruppe["notengruppen_faktor"]*($einzelgruppe["zaehler"]/$einzelgruppe["nenner"]);
			$schnitt_GJ["nenner"]+=$einzelgruppe["notengruppen_faktor"];
			
			// Berechnung Ausgabe
			$schnitt_GJ["berechnung"]=berechnung_notendurchschnitt_ausgabe($schnitt_GJ["berechnung"], $einzelgruppe, $gruppen_schnitt_GJ[$value["notengruppe"]]["alles_faktor_1"]);
		}
		
		// Ausgabe in array
		if ($schnitt_HJ1["nenner"]>0) {
			$schueler_grundlagen_hier['halbjahres_schnitt']=$schnitt_HJ1["zaehler"]/$schnitt_HJ1["nenner"];
			$schueler_grundlagen_hier['halbjahres_schnitt_komma']=kommazahl(round($schueler_grundlagen_hier['halbjahres_schnitt'],2));
			$schueler_grundlagen_hier['halbjahres_schnitt_berechnung']=$schnitt_HJ1["berechnung"];
		}
		else $schueler_grundlagen_hier['halbjahres_schnitt_komma']="-";
		if ($schnitt_HJ2["nenner"]>0) {
			$schueler_grundlagen_hier['halbjahr_2_schnitt']=$schnitt_HJ2["zaehler"]/$schnitt_HJ2["nenner"];
			$schueler_grundlagen_hier['halbjahr_2_schnitt_komma']=kommazahl(round($schueler_grundlagen_hier['halbjahr_2_schnitt'],2));
			$schueler_grundlagen_hier['halbjahr_2_schnitt_berechnung']=$schnitt_HJ2["berechnung"];
		}
		else $schueler_grundlagen_hier['halbjahr_2_schnitt_komma']="-";
		if ($schnitt_GJ["nenner"]>0) {
			$schueler_grundlagen_hier['ganzjahres_schnitt']=$schnitt_GJ["zaehler"]/$schnitt_GJ["nenner"];
			$schueler_grundlagen_hier['ganzjahres_schnitt_komma']=kommazahl(round($schueler_grundlagen_hier['ganzjahres_schnitt'],2));
			$schueler_grundlagen_hier['ganzjahres_schnitt_berechnung']=$schnitt_GJ["berechnung"];
		}
		else $schueler_grundlagen_hier['ganzjahres_schnitt_komma']="-";
		
		//if ($neu_berechnen) {
		//	db_conn_and_sql("DELETE FROM notenstand WHERE schueler=".$schueler_grundlagen_hier["id"]." AND fach=".$fach_id);
		//	db_conn_and_sql("INSERT INTO notenstand (schueler, fach, schuljahr, datum, wert, berechnung, einzelnoten)
		//		VALUES (".$schueler_grundlagen_hier["id"].", ".$fach_id.", ".(2014).", '2015-12-31', ".$schueler_grundlagen_hier['ganzjahres_schnitt'].", ".apostroph_bei_bedarf($schueler_grundlagen_hier['ganzjahres_schnitt_berechnung']).", ".apostroph_bei_bedarf(implode("|%|",$einzelnoten_array)).");");
		//}
		
		return $schueler_grundlagen_hier;
	}
	
	function noten_von_fachklasse ($fach_klasse, $aktuelles_jahr) { // , $neu_berechnen=true
		//$startzeit=microtime(true); // DEL
			$fach_klasse=injaway($fach_klasse);
			$beschreibung='';
			$halbjahresumbruch=false;
			$notenberechnungsvorlagenname=db_conn_and_sql("SELECT name FROM notenberechnungsvorlage, fach_klasse WHERE fach_klasse.notenberechnungsvorlage=notenberechnungsvorlage.id AND fach_klasse.id=".$fach_klasse);
			$notenberechnungsvorlagenname=sql_fetch_assoc($notenberechnungsvorlagenname);
			$notenberechnungsvorlagenname=$notenberechnungsvorlagenname["name"];
			
			$schule=db_conn_and_sql("SELECT klasse.schule FROM klasse, fach_klasse WHERE klasse.id=fach_klasse.klasse AND fach_klasse.id=".$fach_klasse);
			$schule=sql_fetch_assoc($schule);
			$schule=$schule["schule"];
			//		AND `schuljahr`.`jahr`=".$jahr." AND `schuljahr`.`schule`=".$schule."
	        
	        // Beginn und Ende des Schuljahres herausfinden
	        $start_ende=schuljahr_start_ende($aktuelles_jahr, $schule);
			$beginn=$start_ende["start"];
			$ende=$start_ende["ende"];
			
			// notenspalten von fach_klassen, die den selben Lehrauftrag bekommen haben
			$mein_lehrauftrag=db_conn_and_sql("SELECT * FROM lehrauftrag
				WHERE lehrauftrag.schuljahr=".$aktuelles_jahr."
					AND lehrauftrag.fach_klasse=".$fach_klasse);
			$fach_klassen_mit_selben_lehrauftrag="";
			if (sql_num_rows($mein_lehrauftrag)>0) {
				$mein_lehrauftrag=sql_fetch_assoc($mein_lehrauftrag);
				$lehrauftraege_anderer_lehrer=db_conn_and_sql("SELECT lehrauftrag.fach_klasse, users.user_name
					FROM lehrauftrag, users
					WHERE lehrauftrag.user=users.user_id
						AND lehrauftrag.schuljahr=".$mein_lehrauftrag["schuljahr"]."
						AND lehrauftrag.fach=".$mein_lehrauftrag["fach"]."
						AND lehrauftrag.klasse=".$mein_lehrauftrag["klasse"]."
						AND lehrauftrag.fach_klasse IS NOT NULL
						AND lehrauftrag.fach_klasse<>".$fach_klasse);
				while ($fach_klassen_anderer_lehrer=sql_fetch_assoc($lehrauftraege_anderer_lehrer))
					$fach_klassen_mit_selben_lehrauftrag.=" OR `notenbeschreibung`.`fach_klasse`=".$fach_klassen_anderer_lehrer["fach_klasse"];
			}
			$notenbeschreibung=db_conn_and_sql("SELECT notenbeschreibung.id, notenbeschreibung.beschreibung, notenbeschreibung.kommentar, notentypen.kuerzel, notenbeschreibung.zurueckgegeben, notenbeschreibung.gesamtpunktzahl, notenbeschreibung.halbjahresnote, notenbeschreibung.mitzaehlen, notenbeschreibung.test, notenbeschreibung.fach_klasse, notenbeschreibung.notenspiegel, notenbeschreibung.durchschnitt,
					IF(`notenbeschreibung`.`datum` IS NULL,`plan`.`datum`,`notenbeschreibung`.`datum`) as `MyDatum`
				FROM `notentypen`,`notenbeschreibung` LEFT JOIN `plan` ON `notenbeschreibung`.`plan`=`plan`.`id`
				WHERE (`notenbeschreibung`.`fach_klasse`=".$fach_klasse.$fach_klassen_mit_selben_lehrauftrag.")
					AND `notenbeschreibung`.`notentyp`=`notentypen`.`id`
					AND (('".$beginn."'<=`notenbeschreibung`.`datum` AND '".$ende."'>=`notenbeschreibung`.`datum`)
					OR ('".$beginn."'<=`plan`.`datum` AND '".$ende."'>=`plan`.`datum`))
				ORDER BY `notenbeschreibung`.`halbjahresnote` DESC, `MyDatum`");
			//echo "SELECT notenbeschreibung.id, notenbeschreibung.beschreibung, notenbeschreibung.kommentar, notentypen.kuerzel, notenbeschreibung.zurueckgegeben, notenbeschreibung.gesamtpunktzahl, notenbeschreibung.halbjahresnote, notenbeschreibung.mitzaehlen, notenbeschreibung.test, notenbeschreibung.fach_klasse,
			//		IF(notenbeschreibung.datum IS NULL,plan.datum,notenbeschreibung.datum) as MyDatum
			//	FROM notentypen,notenbeschreibung LEFT JOIN plan ON notenbeschreibung.plan=plan.id
			//	WHERE (notenbeschreibung.fach_klasse=".$fach_klasse.$fach_klassen_mit_selben_lehrauftrag.")
			//		AND notenbeschreibung.notentyp=notentypen.id
			//		AND (('".$beginn."'<=notenbeschreibung.datum AND '".$ende."'>=notenbeschreibung.datum)
			//		OR ('".$beginn."'<=plan.datum AND '".$ende."'>=plan.datum))
			//	ORDER BY notenbeschreibung.halbjahresnote DESC, MyDatum";
			$schueler=schueler_von_fachklasse($fach_klasse);
			$schueler_array=array();
			while($schueler_row=sql_fetch_assoc($schueler))
				$schueler_array[]=$schueler_row;
			
			// zuvor vergebene Noten an nicht mehr aktuelle (Gruppenwechsel) Schueler -> schueler dazunehmen
			$schueler_add=db_conn_and_sql("SELECT DISTINCT schueler.id, schueler.name, schueler.vorname, schueler.position, schueler.username, schueler.number, schueler.rufname
				FROM schueler, noten, notenbeschreibung
				WHERE noten.beschreibung=notenbeschreibung.id
					AND notenbeschreibung.fach_klasse=".$fach_klasse."
					AND schueler.id=noten.schueler
					AND '".$beginn."'<=`noten`.`datum`
					AND '".$ende."'>=`noten`.`datum`");
			
			while ($schueler_row=sql_fetch_assoc($schueler_add)) {
				$schueler_nicht_vorhanden=true;
				foreach ($schueler_array as $vorhanden)
					if ($vorhanden["id"]==$schueler_row["id"])
						$schueler_nicht_vorhanden=false;
				if ($schueler_nicht_vorhanden)
					$schueler_array[]=$schueler_row;
			}
			
			// fuer Fremd-Notenspalten alle eigenen Schueler laden
			$meine_schueler=array();
			foreach ($schueler_array as $einzelschueler)
				$meine_schueler[]="noten.schueler=".$einzelschueler["id"];
			$meine_schueler=implode(" OR ",$meine_schueler);
			
			$i=0;
			$beschreibung=array();
			while($notenspalte = sql_fetch_assoc($notenbeschreibung)) {
				if ($fach_klasse==$notenspalte["fach_klasse"]) {
					$meine_fachklasse=true;
					$user=false;
				}
				else {
					$meine_fachklasse=false;
					$user=db_conn_and_sql("SELECT user_name FROM users, fach_klasse WHERE users.user_id=fach_klasse.user AND fach_klasse.id=".$notenspalte["fach_klasse"]);
					$user=sql_fetch_assoc($user);
					$user=$user["user_name"];
				}
				
				// falls in der Fremd-Notenspalte keine eigenen Schueler enthalten sind -> loeschen
				if (!$meine_fachklasse) {
					$anzahl_zugehoeriger_schueler=db_conn_and_sql("SELECT noten.schueler
						FROM noten, notenbeschreibung
						WHERE noten.beschreibung=notenbeschreibung.id
							AND notenbeschreibung.id=".$notenspalte["id"]."
							AND (".$meine_schueler.")");
					if (sql_num_rows($anzahl_zugehoeriger_schueler)==0)
						continue;
				}
				
				if (sql_num_rows($punkte_noten)<1) {
					$punkte_noten=db_conn_and_sql('SELECT *
						FROM `notenbeschreibung`, `bewertung_note`, `bewertungstabelle`
						WHERE `bewertung_note`.`bewertungstabelle`=`notenbeschreibung`.`bewertungstabelle`
							AND `bewertungstabelle`.`id`=`bewertung_note`.`bewertungstabelle`
							AND `notenbeschreibung`.`id`='.$notenspalte["id"].'
						ORDER BY `bewertung_note`.`note`');
					$punkte_noten_array='';
					while($punkte_noten_row=sql_fetch_assoc($punkte_noten))
						$punkte_noten_array[]=$punkte_noten_row;
				}
				$datum=explode("-",$notenspalte["MyDatum"]);
				
				$beschreibung[$i]=array(
					'id'=>$notenspalte["id"],
					'link'=>'formular/noten_bearbeiten.php?beschreibung='.$notenspalte["id"].'&amp;schuljahr='.$aktuelles_jahr,
					'datum'=>$datum[2].".".$datum[1].".",
					'beschreibung'=>html_umlaute($notenspalte["beschreibung"]),
					'kommentar'=>html_umlaute($notenspalte["kommentar"]),
					'notentyp_kuerzel'=>html_umlaute($notenspalte["kuerzel"]),
					'notenspiegel'=>false,
					'zurueckgegeben'=>$notenspalte["zurueckgegeben"], // nur fuer elternansicht, kann spaeter wieder raus
					'halbjahresnote'=>$notenspalte["halbjahresnote"],
					'gesamtpunktzahl'=>$notenspalte["gesamtpunktzahl"],
					'nichtInGruppe'=>false,
					'punkte_oder_zensuren'=>$punkte_noten_array[0]["punkte"],
					'mitzaehlen'=>$notenspalte["mitzaehlen"],
					'meine_fach_klasse'=>$meine_fachklasse,
					'lehrer_kuerzel'=>$user
					);
				
				// Test ausgewaehlt: Gruppe A und B Punktzahlen errechnen
				if ($notenspalte["mitzaehlen"]!=NULL and isset($notenspalte["test"])) {
					$test_gesamtpunktzahl=db_conn_and_sql("SELECT * FROM `test_aufgabe`,`aufgabe`
                        WHERE `test_aufgabe`.`aufgabe`=`aufgabe`.`id`
                            AND `test_aufgabe`.`zusatzaufgabe`!=1
                            AND `test_aufgabe`.`test`=".$notenspalte["test"]);
					$test_pkt_gruppe=array("A"=>0,"B"=>0);
					while($test_punkte_row = sql_fetch_assoc($test_gesamtpunktzahl)) {
						if ($test_punkte_row["position"]>0)
							$test_pkt_gruppe["A"]+=$test_punkte_row["punkte"];
						if ($test_punkte_row["position_b"]>0)
							$test_pkt_gruppe["B"]+=$test_punkte_row["punkte"];
					}
					$beschreibung[$i]['gesamtpunktzahl']   = $test_pkt_gruppe["A"];
					$beschreibung[$i]['gesamtpunktzahl_b'] = $test_pkt_gruppe["B"];
				}
				if ($neu_berechnen) {
					$schnitt=db_conn_and_sql("SELECT `noten`.`wert`, SUM(`noten`.`mitzaehlen`) AS `anzahl`
						FROM `noten`
						WHERE `noten`.`beschreibung`=".$notenspalte["id"]."
						GROUP BY `noten`.`wert`
						ORDER BY `noten`.`wert`");
					if (sql_num_rows($schnitt)>0) {
						$mal_zahl=0; $schueler_anzahl=0; $notenspiegel_array=array();
						while($schnitt_row = sql_fetch_assoc($schnitt)) {
							// Es gab bis __ Punkte eine [Note]
							foreach($punkte_noten_array as $punkte_noten_row)
								if ($punkte_noten_row["note"]==$schnitt_row["wert"]) {
									$beschreibung[$i]['notenspiegel'][$schnitt_row["wert"]]=array(
										'note'=>$schnitt_row["wert"],
										// Schuelerfreundlicher: round(2*sql_result($punkte_noten,$m,'bewertung_note.prozent_bis')*$beschreibung[$i]['gesamtpunktzahl']/100)/2
										'punkte_bis'=>number_format (round(2*$punkte_noten_row["prozent_bis"]*$beschreibung[$i]['gesamtpunktzahl']/100+0.499)/2, 1, ',', '.' ),
										'punkte_bis_zahl'=>round(2*$punkte_noten_row["prozent_bis"]*$beschreibung[$i]['gesamtpunktzahl']/100+0.499)/2,
										'anzahl_schueler'=>$schnitt_row["anzahl"]
										);
									$notenspiegel_array[]=$schnitt_row["wert"].';'.$schnitt_row["wert"].';'.$beschreibung[$i]['notenspiegel'][$schnitt_row["wert"]]["punkte_bis"].';'.$schnitt_row["anzahl"];
								}
							$schueler_anzahl+=$schnitt_row["anzahl"];
							$mal_zahl+=$schnitt_row["anzahl"]*$schnitt_row["wert"];
						}
						if ($schueler_anzahl>0)
							$beschreibung[$i]['durchschnitt']=$mal_zahl/$schueler_anzahl;
					}
					// notenspiegel speichern (falls Neuberechnung nicht sein soll)
					if (count($notenspiegel_array)>0)
						db_conn_and_sql("UPDATE notenbeschreibung SET durchschnitt=".$mal_zahl/$schueler_anzahl.", notenspiegel=".apostroph_bei_bedarf(implode("|",$notenspiegel_array))." WHERE id=".$notenspalte["id"]);
					else
						db_conn_and_sql("UPDATE notenbeschreibung SET durchschnitt=NULL, notenspiegel=NULL WHERE id=".$notenspalte["id"]);
				}
				else { // notenspiegel und durchschnitt aus DB-Tabelle laden
					$beschreibung[$i]['notenspiegel']=array();
					$beschreibung[$i]['durchschnitt']=$notenspalte["durchschnitt"];
					$spiegelspalte=explode("|",$notenspalte["notenspiegel"]);
					foreach ($spiegelspalte as $ssp)
						$beschreibung[$i]['notenspiegel'][$ssp[0]]=array('note'=>$ssp[0],'punkte_bis'=>$ssp[1],'anzahl_schueler'=>$ssp[2]);
				}
				$i++;
			}
			//echo "Dauer Schuelerzuordnung: ".(microtime(true)-$startzeit)." sec<br />"; // DEL
			//$startzeit=microtime(true); // DEL
			
			for($i=0;$i<count($schueler_array);$i++) {
				if (trim($schueler_array[$i]["rufname"]==""))
					$rufname=html_umlaute($schueler_array[$i]["vorname"]);
				else
					$rufname=html_umlaute($schueler_array[$i]["rufname"]);
				$schueler_grundlagen[$i]=array(
					'id'=>$schueler_array[$i]["id"],
					'position'=>$schueler_array[$i]["position"],
					'name'=>html_umlaute($schueler_array[$i]["name"]),
					'vorname'=>$rufname,
					'number'=>html_umlaute($schueler_array[$i]["number"]),
					'username'=>html_umlaute($schueler_array[$i]["username"])
					);
				
				//if ($neu_berechnen) {
				if (count($beschreibung)>0)
				for($j=0;$j<count($beschreibung);$j++) {
					$result = db_conn_and_sql ('SELECT notenbeschreibung.mitzaehlen, notenberechnung.gruppe, noten.wert, notenberechnung.faktor, notenbeschreibung.notentyp, noten.halbjahresnote, noten.punkte, noten.geamtpunktzahl, noten.zusatzpunkte, noten.gruppe_b, noten.datum, noten.kommentar, noten.zusatz, noten.id AS noten_id, noten.mitzaehlen AS noten_mitzaehlen, notengruppe.faktor AS ngfaktor
						FROM `notenbeschreibung`
							LEFT JOIN `fach_klasse`
								ON `fach_klasse`.`id`=`notenbeschreibung`.`fach_klasse`
							LEFT JOIN `notenberechnung`	
								ON `fach_klasse`.`notenberechnungsvorlage`=`notenberechnung`.`vorlage` AND `notenberechnung`.`notentyp`=`notenbeschreibung`.`notentyp`
							LEFT JOIN `notengruppe`
								ON `notengruppe`.`id`=`notenberechnung`.`gruppe`,
						`noten`
						WHERE `noten`.`beschreibung` = `notenbeschreibung`.`id`
							AND `noten`.`schueler`='.$schueler_array[$i]["id"].'
							AND `notenbeschreibung`.`id`='.$beschreibung[$j]["id"].'
						LIMIT 1');
					
					if (sql_num_rows($result)>0) {
						$berechnungen_row=sql_fetch_assoc($result);
						
						if ($beschreibung[$j]['mitzaehlen']==-1)
							$mitzaehlen=$berechnungen_row["noten_mitzaehlen"];
						else
							$mitzaehlen=$beschreibung[$j]['mitzaehlen'];
						if ($berechnungen_row["gruppe"]<1)
							$beschreibung[$j]['nichtInGruppe']=true;
						$durchschnitt[$i][$j]=array(
							"notenbeschreibung_id"=>$beschreibung[$j]["id"], // nur fuer Speichern in notenstand noetig
							"wert"=>$berechnungen_row["wert"],
							"gesamtpunktzahl"=>$berechnungen_row["geamtpunktzahl"], // kein Schreibfehler
							"faktor"=>$berechnungen_row["faktor"],
							"notengruppe"=>$berechnungen_row["gruppe"],
							"typ"=>$berechnungen_row["notentyp"],
							"mitzaehlen"=>$mitzaehlen,
							"halbjahresnote"=>$berechnungen_row["halbjahresnote"]
						);
						if ($durchschnitt[$i][$j]["faktor"]=="")
							$durchschnitt[$i][$j]["faktor"]=1;
						if ($durchschnitt[$i][$j]["notengruppe"]>0)
							$durchschnitt[$i][$j]["notengruppen_faktor"]=$berechnungen_row["ngfaktor"];
						$punktzahl_hilf=-1;
						if ($berechnungen_row["wert"]!="") {
							if ($berechnungen_row["punkte"]+$berechnungen_row["zusatzpunkte"]==floor($berechnungen_row["punkte"]+$berechnungen_row["zusatzpunkte"]))
								$punktzahl_hilf=number_format ($berechnungen_row["punkte"]+$berechnungen_row["zusatzpunkte"], 0);
							else
								$punktzahl_hilf=number_format ($berechnungen_row["punkte"]+$berechnungen_row["zusatzpunkte"], 1, ',', '.' );
							$durchschnitt[$i][$j]["punktzahl_mit_komma"]=$punktzahl_hilf;
							$durchschnitt[$i][$j]["gruppe_b"]=$berechnungen_row["gruppe_b"];
							//$durchschnitt[$i][$j]["punktzahl"]=number_format (@sql_result ( $result, 0, 'noten.punkte' )+@sql_result ( $result, 0, 'noten.zusatzpunkte' ), 0); // ACHTUNG: Rundet auf Ganze??? Was ist mit Kommazahlen?
                            $durchschnitt[$i][$j]["punkte"]=$berechnungen_row["punkte"];
							$durchschnitt[$i][$j]["zusatzpunkte"]=$berechnungen_row["zusatzpunkte"];
							$durchschnitt[$i][$j]["datum"]=datum_strich_zu_punkt($berechnungen_row["datum"]);
							$durchschnitt[$i][$j]["kommentar"]=html_umlaute($berechnungen_row["kommentar"]);
						}
						if ($berechnungen_row["zusatz"]=="-1")
							$durchschnitt[$i][$j]["notenzusatz"]="-";
						if ($berechnungen_row["zusatz"]=="1") {
							if ($berechnungen_row["wert"]=="1")
								$durchschnitt[$i][$j]["notenzusatz"]="+"; // bei Note 1 gabs auch mal "*" statt "+"
                            else
								$durchschnitt[$i][$j]["notenzusatz"]="+";
						}
						$test_punktzahl=0;
						$note_aufgabe = db_conn_and_sql ('SELECT *
							FROM `note_aufgabe`
							WHERE `note_aufgabe`.`note`='.$berechnungen_row["noten_id"]);
						while($note_aufgabe_row = sql_fetch_assoc($note_aufgabe))
							$durchschnitt[$i][$j]["einzelpunkte"][]=array(
								"pkt"=>$note_aufgabe_row["punkte"],
								"aufg_id"=>$note_aufgabe_row["aufgabe"]
							);
					}
				}
				//}
				//else { // nicht neu berechnen
				//	$notenstand=db_conn_and_sql("SELECT einzelnoten FROM notenstand WHERE schueler=".$schueler_grundlagen[$i]["id"]." AND fach=".$mein_lehrauftrag["fach"]);
				//	$notenstand=sql_fetch_assoc($notenstand);
				//	$durchschnitt[$i]=array();
				//	$einzelnoten_array=explode("|%|",$notenstand["einzelnoten"]);
				//	foreach ($einzelnoten_array as $einzelnote_hier) {
				//		$en=explode(";", $einzelnote_hier);
				//		$durchschnitt[$i][]=array(
				//			"notenbeschreibung_id"=>$en[0], // nur fuer Speichern in notenstand noetig
				//			"wert"=>$en[1],
				//			"punktzahl_mit_komma"=>$en[2],
				//			"gesamtpunktzahl"=>$en[3],
				//			"datum"=>$en[4],
				//			"halbjahresnote"=>$en[5],
				//			"notenzusatz"=>$en[6],
				//			"mitzaehlen"=>$en[7],
				//			"kommentar"=>$en[8]
				//		);
				//	}
				//}
				$schueler_grundlagen[$i]['noten']=$durchschnitt[$i];
				
				// ----------- Notendurchschnitt berechnen und in notenstand schreiben --------------
				$schueler_grundlagen[$i]=notendurchschnitt_berechnen($durchschnitt[$i], $schueler_grundlagen[$i], $mein_lehrauftrag["fach"]); //, $neu_berechnen
			}
			//echo "Dauer Berechnung: ".(microtime(true)-$startzeit)." sec<br />"; // DEL
			return array('notenbeschreibung'=>$beschreibung, 'schueler'=>$schueler_grundlagen, 'berechnungsvorlage'=>$notenberechnungsvorlagenname);
		}
		
function notenhash_von_fach_klasse($fach_klasse, $schuljahr, $schreiben=false) {
	// TODO: Hashbildung nur mit Yubikey
	$schule=db_conn_and_sql("SELECT klasse.schule FROM klasse, fach_klasse WHERE klasse.id=fach_klasse.klasse AND fach_klasse.id=".$fach_klasse);
	$schule=sql_fetch_assoc($schule);
	$schule=$schule["schule"];
    // Beginn und Ende des Schuljahres herausfinden
    $start_ende=schuljahr_start_ende($schuljahr, $schule);
    
	// zuvor vergebene Noten an nicht mehr aktuelle (Gruppenwechsel) Schueler -> schueler dazunehmen
	$alle_noten=db_conn_and_sql("SELECT noten.id, noten.wert, noten.zusatz, noten.datum, noten.schueler, noten.mitzaehlen
		FROM noten, notenbeschreibung
		WHERE noten.beschreibung=notenbeschreibung.id
			AND notenbeschreibung.fach_klasse=".$fach_klasse."
			AND '".$start_ende["start"]."'<=`noten`.`datum`
			AND '".$start_ende["ende"]."'>=`noten`.`datum`");
	
	// bei weniger als 6 Zensuren soll kein Hash gebildet werden
	if (sql_num_rows($alle_noten)>5) {
		$noten_string_for_hash="";
		while ($note=sql_fetch_assoc($alle_noten))
			$noten_string_for_hash.=$note["id"].$note["wert"].$note["zusatz"].$note["mitzaehlen"].$note["datum"].$note["schueler"];
		
		if ($schreiben) {
			$timestamp=date("Y-m-d H:i:s");
			$salttimes=rand(10,30);
		}
		else {
			$old_hash=db_conn_and_sql("SELECT notenhash FROM fach_klasse WHERE id=".$fach_klasse);
			$old_hash=sql_fetch_assoc($old_hash);
			$old_hash=$old_hash["notenhash"];
			
			$old_hash=explode(";", $old_hash);
			$timestamp=$old_hash[0];
			$salttimes=$old_hash[1];
			$vergleichshash=$old_hash[2];
		}
		
		$salt=$timestamp.SERVERSALT;
		$salted_hash=$noten_string_for_hash;
		
		// mehrmaliges Salzen des Hashes
		for($i=0;$i<$salttimes;$i++)
			$salted_hash=sha1($salt.$noten_string_for_hash);
		
		// Hash in fach-klasse schreiben
		if($schreiben) {
			db_conn_and_sql("UPDATE fach_klasse SET notenhash='".$timestamp.";".$salttimes.";".$salted_hash."' WHERE id=".$fach_klasse);
			return array(true);
		}
		else {
			if ($vergleichshash==$salted_hash)
				return array(true, $timestamp);
			else
				return array(false, $timestamp);
		}
	}
	else
		return array(true, false); // passed, wegen zu weniger Zensuren
}

function fachklassen_zeitplanung($fachklasse,$jahr) {
	$wochenstunden_result=db_conn_and_sql("SELECT *
		FROM `stundenplan`,`stundenzeiten`,`fach_klasse`,`klasse`
        WHERE `stundenplan`.`stundenzeit`=`stundenzeiten`.`id`
			AND `stundenplan`.`fach_klasse`=".$fachklasse."
			AND `fach_klasse`.`id`=".$fachklasse."
			AND `fach_klasse`.`klasse`=`klasse`.`id`
			AND `klasse`.`schule`=`stundenzeiten`.`schule`
			AND `stundenplan`.`schuljahr`=".$jahr."
        ORDER BY `stundenplan`.`wochentag`,`stundenzeiten`.`beginn`");
		
	$db=new db;
	$jahr=$db->aktuelles_jahr();
	$schule=db_conn_and_sql("SELECT klasse.schule FROM klasse, fach_klasse WHERE klasse.id=fach_klasse.klasse AND fach_klasse.id=".$fachklasse);
	$schule=sql_fetch_assoc($schule);
	$schule=$schule["schule"];

	$wochentage=schuljahr_uebersicht($jahr, $schule);
	$eintraege='';
	$fortlaufende_nummer=-1;
	$lernbereich_anzahl=0;
    $lernbereichs_faktor=sql_result(db_conn_and_sql("SELECT lb_faktor FROM benutzer WHERE id=".$_SESSION['user_id']), 0, "lb_faktor");
	$lernbereich_nummer=0;
	$lernbereiche=db_conn_and_sql('SELECT lernbereich.*, lernbereich.name AS lb_name
        FROM `lernbereich`,`lehrplan`,`schulart`,`faecher`,`fach_klasse`,`klasse`
        WHERE `lernbereich`.`lehrplan`=`lehrplan`.`id`
			AND `lehrplan`.`schulart` = `schulart`.`id`
			AND `lehrplan`.`fach` = `faecher`.`id`
			AND `fach_klasse`.`klasse`=`klasse`.`id`
			AND `fach_klasse`.`lehrplan`=`lehrplan`.`id`
			AND `fach_klasse`.`id`='.$fachklasse.'
			AND `lernbereich`.`klassenstufe`=('.$jahr.'-`klasse`.`einschuljahr`+1)
        ORDER BY `lernbereich`.`nummer`' );
	$lernbereich_row = sql_fetch_assoc($lernbereiche);
	$plan=db_conn_and_sql("SELECT plan.*, block1.id AS block1_id, block2.id AS block2_id, block1.name AS block1_name, block2.name AS block2_name
		FROM `plan`
			LEFT JOIN `block` AS `block1` ON `plan`.`block_1`=`block1`.`id`
			LEFT JOIN `block` AS `block2` ON `plan`.`block_2`=`block2`.`id`
		WHERE `plan`.`fach_klasse`=".$fachklasse."
			AND `plan`.`schuljahr`=".$jahr."
		ORDER BY `plan`.`datum`, `plan`.`startzeit`");
	
	$normal_tag=array();
	while($wochenstunden_row = sql_fetch_assoc($wochenstunden_result)) {
		if ($i==0)
			$stundenzeiten_result=db_conn_and_sql("SELECT * FROM `stundenzeiten`
				WHERE ".$wochenstunden_row["schule"]."=`stundenzeiten`.`schule`
				ORDER BY `stundenzeiten`.`beginn`");
		
		$m=0;
		sql_reset_pointer($stundenzeiten_result);
		while ($m<sql_num_rows($stundenzeiten_result)
				and $stundenzeiten_row = sql_fetch_assoc($stundenzeiten_result)
				and $stundenzeiten_row["id"]!=$wochenstunden_row["stundenzeit"])
			$m++;
		$m++;
		
		$stundenzeiten_row = sql_fetch_assoc($stundenzeiten_result);
		
		$eintrag=array("gerade"=>modulo(($wochenstunden_row["gerade_woche"]+1), 2), // gerade waere 0 soll aber 1 sein und umgekehrt
			"anzahl"=>1,
			"stunde"=>$wochenstunden_row["beginn"],
			"stundenpos"=>$m);
		if ($wochenstunden_row["gerade_woche"]==2) {
			$eintrag["gerade"]=0;
			$normal_tag[$wochenstunden_row["wochentag"]][]=$eintrag;
			$eintrag["gerade"]=1;
			$normal_tag[$wochenstunden_row["wochentag"]][]=$eintrag;
		}
		else
			$normal_tag[$wochenstunden_row["wochentag"]][]=$eintrag;
		
		//echo "<br />i".$i." wt".$wochenstunden_row["wochentag"]." z".$zaehler." wo".$wochenstunden_row["gerade_woche"];
	}
	
	// Zusammenfassen einzelner Stunden
	$woche=1;
	foreach($normal_tag as $wochentag => $nt) {
		// zunaechst: separate arrays mit geraden und ungeraden wocheneintraegen erstellen
		$geordnet=array(array(), array());
		for($i=0; $i<count($nt); $i++)
			if ($nt[$i]["gerade"])
				$geordnet[1][]=$nt[$i];
			else
				$geordnet[0][]=$nt[$i];
		
		for ($gerade=0; $gerade<2; $gerade++) // erst alle ungeraden, dann alle geraden Eintraege
			for ($i=0; $i<count($geordnet[$gerade]); $i++) {
				$zaehler=1;
				// Falls die naechste Stunde gleich anschliessend ist, wird der zaehler erhoeht
				while ($geordnet[$gerade][$i+$zaehler]["stundenpos"]==$geordnet[$gerade][$i]["stundenpos"]+$zaehler)
					$zaehler++;
				// wenn es mind. eine anschliessende Stunde gibt, soll die Anzahl angepasst, und die folgenden Eintraege geloescht werden
				if ($zaehler>1) {
					$del=0;
					for ($k=0; $k<count($nt); $k++) {
						//echo '<br />'.$normal_tag[$wochentag][$k]["gerade"]."==".$gerade." and ".$normal_tag[$wochentag][$k]["stundenpos"]."==".$geordnet[$gerade][$i]["stundenpos"];
						// Anzahl anpassen
						if ($normal_tag[$wochentag][$k]["gerade"]==$gerade and $normal_tag[$wochentag][$k]["stundenpos"]==$geordnet[$gerade][$i]["stundenpos"]) {
							$del=$zaehler-1;
							$normal_tag[$wochentag][$k]["anzahl"]=$zaehler;
						}
						// Resteintraege loeschen
						if ($del>0 and $normal_tag[$wochentag][$k]["gerade"]==$gerade and $normal_tag[$wochentag][$k]["stundenpos"]==$geordnet[$gerade][$i+$zaehler-$del]["stundenpos"]) {
							array_splice($normal_tag[$wochentag],$k,1);
							$k--;
							$del--;
						}
					}
					$i+=($zaehler-1);
				}
				//echo "<br />::".$zaehler." v".$wochentag." ".$gerade;
				//print_r($geordnet[$gerade][$i]);
			}
	}
	
	$plan_eintrag=sql_fetch_assoc($plan);
	for ($woche=0; $woche<(count($wochentage[5])+1); $woche++)
		for ($mo_bis_fr=0;$mo_bis_fr<=5;$mo_bis_fr++)
			if (isset($wochentage[$mo_bis_fr][$woche]["datum"])) {
				if (date("Y-m-d",$wochentage[$mo_bis_fr][$woche]["datum"])<=$plan_eintrag["datum"] or $plan_eintrag["datum"]=="") {
				//echo '<br />'.date("Y-m-d",$wochentage[$mo_bis_fr][$woche]["datum"]).' un='.$wochentage[$mo_bis_fr][$woche]["unterricht"]." an=".$normal_tag[$mo_bis_fr][0]["anzahl"]." ger=".$normal_tag[$mo_bis_fr][0]["gerade"]." A=".$wochentage[1][$woche]["a_woche"];
				if (isset($wochentage[$mo_bis_fr][$woche]["unterricht"])) { //wenn der tag definiert ist (zum Ausschliessen der nicht stattfindenden letzten Woche oder so)
					$m=0; $stunde_im_stundenplan=false;
					while ($m<count($normal_tag[$mo_bis_fr])) {
						if ($normal_tag[$mo_bis_fr][$m]["anzahl"]>0 and $wochentage[1][$woche]["a_woche"]==$normal_tag[$mo_bis_fr][$m]["gerade"]) //Feste Wocheneinteilung
							$stunde_im_stundenplan=true;
						$m++;
					}
					if ($stunde_im_stundenplan) {
					if ($wochentage[$mo_bis_fr][$woche]["unterricht"]==1) { //wenn kein feiertag/ferien ist...
						//echo date("Y-m-d",$wochentage[$mo_bis_fr][$woche]["datum"])."=".@sql_result($plan,$plan_eintrag,"plan.datum")."<br>";
						$m=0;
						while ($normal_tag[$mo_bis_fr][$m]["anzahl"]>0) {
							if ($wochentage[1][$woche]["a_woche"]==$normal_tag[$mo_bis_fr][$m]["gerade"]) {
								if (date("Y-m-d",$wochentage[$mo_bis_fr][$woche]["datum"])==$plan_eintrag["datum"]
										and $plan_eintrag["startzeit"]<=$normal_tag[$mo_bis_fr][$m]["stunde"]) { // Eintragung da
									$eintraege[]=array('datum'=>$wochentage[$mo_bis_fr][$woche]["datum"],
										'stunden'=>$plan_eintrag["ustd"],
										'typ'=>"eingetragen",
										'vorbereitet'=>$plan_eintrag["vorbereitet"],
										'nachbereitung'=>$plan_eintrag["nachbereitung"],
										'plan_id'=>$plan_eintrag["id"],
										'zeit'=>$plan_eintrag["startzeit"], // Vorher stand das da: $normal_tag[$mo_bis_fr]["stunde"], was hab ich mir dabei gedacht?!? Wenn was nicht klappt -> aendern
										'block_1'=>html_umlaute($plan_eintrag["block1_name"]),
										'block_2'=>html_umlaute($plan_eintrag["block2_name"]),
										'block_1_id'=>$plan_eintrag["block1_id"],
										'block_2_id'=>$plan_eintrag["block2_id"],
										'notizen'=>html_umlaute($plan_eintrag["notizen"]),
										'alternativtitel'=>html_umlaute($plan_eintrag["alternativtitel"]),
										'bemerkung'=>html_umlaute($plan_eintrag["bemerkung"]));
									if($plan_eintrag["ausfallgrund"]!='') {
										$eintraege[count($eintraege)-1]['typ']="ausfall";
										$eintraege[count($eintraege)-1]['grund']=html_umlaute($plan_eintrag["ausfallgrund"]);
									}
									$plan_eintrag=sql_fetch_assoc($plan);
								}
								else {
								$eintraege[]=array('datum'=>$wochentage[$mo_bis_fr][$woche]["datum"],
									'stunden'=>$normal_tag[$mo_bis_fr][$m]["anzahl"],
									'typ'=>"frei_fuer_eintragung",
									'zeit'=>$normal_tag[$mo_bis_fr][$m]["stunde"],
									'fortlaufende_nummer'=>($fortlaufende_nummer+1));
									$fortlaufende_nummer++;
								}
							}
							$m++;
						}
					}
					else {
						$eintraege[]=array('datum'=>$wochentage[$mo_bis_fr][$woche]["datum"],
								'grund'=>$wochentage[$mo_bis_fr][$woche]["unterricht"],
								'typ'=>"feiertag"); // wo wird Ausfall gezaehlt?
					}
					
					//lernbereiche - gibts unten nochmal
					//if ($lernbereich_nummer==-1) {$lernbereich_anzahl=@sql_result($lernbereiche,0,'lernbereich.ustd')*$lernbereichs_faktor; $lernbereich_nummer=0;}
					//echo $lernbereich_anzahl." ".@sql_result($lernbereiche,$lernbereich_nummer,'lernbereich.ustd')." <br />";
					if ($eintraege[count($eintraege)-1]['typ']!="feiertag")
						$lernbereich_anzahl-=$eintraege[count($eintraege)-1]['stunden'];
					if($lernbereich_anzahl<=0 and $lernbereich_row["ustd"]!="") {
                        if ($lernbereichs_faktor!=0) {
                            if ($lernbereich_row["wahl"]) {
                                $wahl_zahler++;
                                $my_lb_numerierung = "W".$wahl_zahler;
                            }
                            else
                                $my_lb_numerierung = ($lernbereich_nummer+1);
                            $eintraege[count($eintraege)-1]['lernbereich']="LB ".$my_lb_numerierung.": ".html_umlaute($lernbereich_row["lb_name"])." (".$lernbereich_row["ustd"].")";
                        }
						$lernbereich_anzahl+=$lernbereich_row["ustd"]*$lernbereichs_faktor;
						$lernbereich_row = sql_fetch_assoc($lernbereiche);
						$lernbereich_nummer++;
					}
					// $anzahl=0; brauch ich nimmer...
				}
				}
			}
			else { //zusatzeintrag
				if ($plan_eintrag["datum"]!="") {
					$hilf=explode("-",$plan_eintrag["datum"]);
					$hilf_zeit=explode("-",$plan_eintrag["startzeit"]);
					$eintraege[] = array('datum'=>mktime($hilf_zeit[0],$hilf_zeit[1],0,$hilf[1],$hilf[2],$hilf[0]),
								'stunden'=>$plan_eintrag["ustd"],
								'typ'=>"zusatz",
								'zeit'=>$plan_eintrag["startzeit"],
								'vorbereitet'=>$plan_eintrag["vorbereitet"],
								'nachbereitung'=>$plan_eintrag["nachbereitung"],
								'notizen'=>html_umlaute($plan_eintrag["notizen"]),
								'alternativtitel'=>$plan_eintrag["alternativtitel"],
								'plan_id'=>$plan_eintrag["id"],
								'block_1'=>html_umlaute($plan_eintrag["block1_name"]),
								'block_2'=>html_umlaute($plan_eintrag["block2_name"]),
								'block_1_id'=>$plan_eintrag["block1_id"],
								'block_2_id'=>$plan_eintrag["block2_id"],
								'bemerkung'=>html_umlaute($plan_eintrag["bemerkung"]));
					$plan_eintrag=sql_fetch_assoc($plan);
					
					//lernbereiche - kopiert von paar Zeilen weiter oben
					//if ($lernbereich_nummer==-1) {$lernbereich_anzahl=@sql_result($lernbereiche,0,'lernbereich.ustd')*$lernbereichs_faktor; $lernbereich_nummer=0;}
					//echo $lernbereich_anzahl." ".@sql_result($lernbereiche,$lernbereich_nummer,'lernbereich.ustd')." <br />";
					if ($eintraege[count($eintraege)-1]['typ']!="feiertag")
						$lernbereich_anzahl-=$eintraege[count($eintraege)-1]['stunden'];
					if($lernbereich_anzahl<=0 and $lernbereich_row["ustd"]!="") {
                        if ($lernbereichs_faktor!=0)
                            $eintraege[count($eintraege)-1]['lernbereich']=($lernbereich_nummer+1).". ".html_umlaute($lernbereich_row["lb_name"])." (".$lernbereich_row["ustd"].")";
						$lernbereich_anzahl+=$lernbereich_row["ustd"]*$lernbereichs_faktor;
						$lernbereich_row = sql_fetch_assoc($lernbereiche);
						$lernbereich_nummer++;
					}
				}
			}
			}
	
	
	return $eintraege;
}

function syntax_zu_html($text, $teilaufgaben_nebeneinander=1, $modus=0, $pfad='./', $aufgabengruppe='A') {
    // text: in HTML umzuwandelnder text
    // teilaufgaben_nebeneinander: bei 1), a), A) und I) werden mehrere Aufteilungen moeglich
    // modus: links_bilder_bearbeiten=1
    // aufgabengruppe: Eigentlich nur bei Aufgaben in Tests relevant: Wenn 'B', wird bei "[#GR_A#GR_B#]" "GR_B" geschrieben; in allen anderen Faellen "GR_A"
    
    $links_bilder_bearbeiten=false;
    $formel_gefunden=false;
    $syntax_gefunden=false;
    $papier_anzeigen=false;
    $anklickbar=false;
	$invisible=false;
    if ($modus!="0" and $modus=='bearbeiten') { // $modus!="0" muss aus unerfindlichen Gruenden gesetzt werden, sonst ist $modus=='bearbeiten' true ?!?
        $links_bilder_bearbeiten=true;
    }
    if ($modus!="0" and $modus=='papier') {
        $papier_anzeigen=true;
    }
    if ($modus!="0" and $modus=='click to show content') {
		$anklickbar=true;
        $links_bilder_bearbeiten=true;
	}
    if ($modus!="0" and $modus=='not visible') {
		$invisible=true;
	}
	
    
    $db = new db;
    
	// weiterfuehren und Nummerierung
	$return='';
	$anzahl_jetzt=$teilaufgaben_nebeneinander;  
	$tabelle=false;
	$aufzaehlung=false;
    $checkboxen=false;
	$auflistung[0]=false;
	$auflistung[1]=false;
	$auflistung[2]=false;
	$auflistung[3]=false;
	$formatierter_text='';
	
    if (preg_match("#(.*)`(.*)#is", $text))
        $formel_gefunden=true;
    
    // Aufgaben in Gruppen ermoeglichen
	if (preg_match("#(.*)\[\#(.*)\#(.*)\#\](.*)#is",$text))
		if ($aufgabengruppe=='B')
            $text=preg_replace('~\[\#(.*)\#(.*)\#\]~U', '\2', $text);
		else
            $text=preg_replace('~\[\#(.*)\#(.*)\#\]~U', '\1', $text);
    
	// im Code-Block soll keine Syntaxumwandlung stattfinden. Deshalb wird jeder Code-Block durch HIER... ersetzt und am Ende an entsprechender Stelle wieder eingesetzt mittels $programm_codes
	if (preg_match_all("|\[code;(.*)\](.*)\[\/code\]|Us",$text, $programm_codes)) {
		$text=preg_replace('~\[code;(.*)\](.*)\[\/code\]~Us', 'HIER_PROGRAMMCODE_EINSETZEN', $text);
		$syntax_gefunden=true;
	}
	
	// [applet:TYP]APPLETINHALT[/applet]
	$applet=explode("[applet:",$text);
	$text='';
	for($i=0;$i<count($applet);$i++)
		if ($i>0 and count(explode("[/applet]",$applet[$i]))>1) {
			// TODO typ geonext oder geogebra herausfinden
			$typ=explode("]",$applet[$i]);
			$typ=$typ[0];
			
			$ende=explode("[/applet]",$applet[$i]);
			$ende[0]=str_replace("\n","",$ende[0]);
			$ende[0]=str_replace("&gt;",">",$ende[0]);
			$ende[0]=str_replace("&lt;","<",$ende[0]);
			$ende[0]=str_replace("&quot;",'"',$ende[0]);
			if ($typ=="geonext") // Java-Datei ist lokal vorhanden
				$formatierter_text.='<a href="'.$pfad.'plugin/show_plug.php?type=geonext&amp;content='.substr($ende[0],8).'" target="_blank">[zeigen]</a>'.nl2br(html_umlaute($ende[1]));
            if ($typ=="geogebra") // Java-Datei wird aus dem Netz geholt
                $formatierter_text.='<a href="'.$pfad.'plugin/show_plug.php?type=geogebra&amp;content='.html_umlaute(substr($ende[0],9)).'" target="_blank">[zeigen]</a>'.nl2br(html_umlaute($ende[1]));
		}
		else $formatierter_text.=nl2br(html_umlaute($applet[0]));
	
	$text=explode("\n",$formatierter_text);
	
  foreach($text as $einzeltext) {
	// zB &middot; ermoeglichen (wurde zunaechst mittels html_umlaute in &amp;middot; umgewandelt. &amp;#269;)
	while (preg_match("#(.*)&amp;(\#{0,1})(\w{2,15})\;(.*)#is",$einzeltext)) {
		$einzeltext=preg_replace('~&amp;(\w{2,15})\;~U', '&\1;', $einzeltext);
		$einzeltext=preg_replace('~&amp;\#(\d{2,5});~U', '&#\1;', $einzeltext);
	}
	//Zeilenumbruch innerhalb Tabellen und Aufzaehlungen/Auflistungen ermoeglichen
	if (preg_match("#(.*)\[nl\](.*)#is",$einzeltext)) $einzeltext=preg_replace('~\[nl\]~U', '<br style="clear: both;" />\1', $einzeltext);
	
	if (preg_match("#(.*)\[b\](.*)\[/b\](.*)#is",$einzeltext)) $einzeltext=preg_replace('~\[b\](.*)\[/b\]~U', '<span style="font-weight: bold;">\1</span>', $einzeltext);
	if (preg_match("#(.*)\[u\](.*)\[/u\](.*)#is",$einzeltext)) $einzeltext=preg_replace('~\[u\](.*)\[/u\]~U', '<span style="text-decoration: underline;">\1</span>', $einzeltext);
	if (preg_match("#(.*)\[i\](.*)\[/i\](.*)#is",$einzeltext)) $einzeltext=preg_replace('~\[i\](.*)\[/i\]~U', '<span style="font-style:italic;">\1</span>', $einzeltext);
	
	if (preg_match("#(.*)\[red\](.*)\[/red\](.*)#is",$einzeltext)) $einzeltext=preg_replace('~\[red\](.*)\[/red\]~U', '<span style="color: red; font-weight: bold;">\1</span>', $einzeltext);
	if (preg_match("#(.*)\[orange\](.*)\[/orange\](.*)#is",$einzeltext)) $einzeltext=preg_replace('~\[orange\](.*)\[/orange\]~U', '<span style="color: orange; font-weight: bold;">\1</span>', $einzeltext);
	if (preg_match("#(.*)\[yellow\](.*)\[/yellow\](.*)#is",$einzeltext)) $einzeltext=preg_replace('~\[yellow\](.*)\[/yellow\]~U', '<span style="color: yellow; font-weight: bold;">\1</span>', $einzeltext);
	if (preg_match("#(.*)\[blue\](.*)\[/blue\](.*)#is",$einzeltext)) $einzeltext=preg_replace('~\[blue\](.*)\[/blue\]~U', '<span style="color: blue; font-weight: bold;">\1</span>', $einzeltext);
	if (preg_match("#(.*)\[green\](.*)\[/green\](.*)#is",$einzeltext)) $einzeltext=preg_replace('~\[green\](.*)\[/green\]~U', '<span style="color: green; font-weight: bold;">\1</span>', $einzeltext);
	if (preg_match("#(.*)\[brown\](.*)\[/brown\](.*)#is",$einzeltext)) $einzeltext=preg_replace('~\[brown\](.*)\[/brown\]~U', '<span style="color: brown; font-weight: bold;">\1</span>', $einzeltext);
	if (preg_match("#(.*)\[gray\](.*)\[/gray\](.*)#is",$einzeltext)) $einzeltext=preg_replace('~\[gray\](.*)\[/gray\]~U', '<span style="color: gray;">\1</span>', $einzeltext);
    
    // Punkte-Kennzeichnung mit [.x.]
	if (preg_match("#(.*)\[\.(.{0,4})\.\](.*)#is",$einzeltext))
		$einzeltext=preg_replace('~\[\.(.{0,4})\.\]~U', '<span style="color: red; font-size:0.6em; vertical-align:top">(\1 P.)</span>', $einzeltext);
	
    // Umrandung
	if (preg_match("#(.*)\[bor\](.*)\[/bor\](.*)#is",$einzeltext))
		$einzeltext=preg_replace('~\[bor\](.*)\[/bor\]~U', '<div style="border: 2px solid orange; width: auto; padding: 3px; margin-left: auto;">\1</div>', $einzeltext);
	
    while (preg_match("#(.*)\[boxed;(.*)x(.*);(.*)\](.*)#is",$einzeltext)) {
        $groesse=explode('[boxed;', $einzeltext);
        $groesse=explode(';', $groesse[1]);
        $position=explode(']', $groesse[1]); $position=$position[0];
        $groesse=explode('x', $groesse[0]);
        $groesse[0]=str_replace(',', '.', $groesse[0]);
        $groesse[1]=str_replace(',', '.', $groesse[1]);
        switch ($position) {
            case "right": $position=' float: right;'; break;
            case "left": $position=' float: left;'; break;
            default: $position=' clear: both;'; break; // clear: both;
        }
        if (!$papier_anzeigen) {
			$groesse[0]=0;
			$groesse[1]=0;
			$position=' float: right;';
		}
        $papier='<div style="max-width:'.$groesse[0].'cm; min-width:'.$groesse[0].'cm; max-height:'.$groesse[1].'cm;'.$position.' overflow:hidden;">';
        $papier.='<img src="'.$pfad.'look/kariert.gif" alt="kariert" class="kariert" />';
        $papier.='</div>';
        $einzeltext=preg_replace('~\[boxed;(.*)\]~U', $papier, $einzeltext, 1);
    }
	while (preg_match("#(.*)\[ruled;(.*)x(.*);(.*)\](.*)#is",$einzeltext)) {
        $groesse=explode('[ruled;', $einzeltext);
        $groesse=explode(';', $groesse[1]);
        $position=explode(']', $groesse[1]); $position=$position[0];
        $groesse=explode('x', $groesse[0]);
        $groesse[0]=str_replace(',', '.', $groesse[0]);
        $groesse[1]=str_replace(',', '.', $groesse[1]);
        if (!$papier_anzeigen) {$groesse[0]=0; $groesse[1]=0; $position=' float: right;';}
        switch ($position) {
            case "right": $position=' float: right;'; break;
            case "left": $position=' float: left;'; break; //float: left;
            default: $position=' clear: both;'; break;
        }
        $papier='<div style="max-width:'.$groesse[0].'cm; min-width:'.$groesse[0].'cm; max-height:'.$groesse[1].'cm;'.$position.' overflow:hidden;">';
        for($j=0;$j<$groesse[1];$j+=0.5)
            if (modulo($j*2, 2)==0) $papier.='<div style="height: 0.5cm;">&nbsp;</div>';
			else $papier.='<div style="height: 0.3cm; border-bottom: 1px solid gray;">&nbsp;</div><div style="height: 0.2cm;">&nbsp;</div>';
        $papier.='</div>';
        $einzeltext=preg_replace('~\[ruled;(.*)\]~U', $papier, $einzeltext, 1);
    }
	while (preg_match("#(.*)\[millimeter;(.*)x(.*);(.*)\](.*)#is",$einzeltext)) {
        $groesse=explode('[millimeter;', $einzeltext);
        $groesse=explode(';', $groesse[1]);
        $position=explode(']', $groesse[1]); $position=$position[0];
        $groesse=explode('x', $groesse[0]);
        $groesse[0]=str_replace(',', '.', $groesse[0]);
        $groesse[1]=str_replace(',', '.', $groesse[1]);
        if (!$papier_anzeigen) {$groesse[0]=0; $groesse[1]=0; $position=' float: right;';}
        switch ($position) {
            case "right": $position=' float: right;'; break;
            case "left": $position=' float: left;'; break;
            default: $position=' clear: both;'; break;
        }
        $papier='<div style="max-width:'.$groesse[0].'cm; min-width:'.$groesse[0].'cm; max-height:'.$groesse[1].'cm;'.$position.' overflow:hidden;">';
        $papier.='<img src="'.$pfad.'look/millimeter.png" alt="millimeter" class="millimeter" />';
        $papier.='</div>';
        $einzeltext=preg_replace('~\[millimeter;(.*)\]~U', $papier, $einzeltext, 1);
    }
    
	if (preg_match("#(.*)\[grafic;(.*);(.*);(.*)\](.*)#is",$einzeltext)) {
        
        // ID rausfiltern und Grafik-Objekt laden
        $grafics_in_text=explode('[grafic;', $einzeltext);
        while(count($grafics_in_text)>1) {
            // TODO: Abbruchbedingung unklar
            if (count(explode("]",$grafics_in_text[1]))>1) {
                // the following part is only for getting the rest after [grafic;...;...] to write it back to $einzeltext after changing to <img...
                $rest=$grafics_in_text; array_shift($rest);
                $rest=explode("]",implode("[grafic;", $rest)); array_shift($rest);
                $rest=implode("]", $rest);
                
                $gra_id=explode(';', $grafics_in_text[1]);
                $my_grafic=$db->grafik($gra_id[0]);
                // laenge ist letzte Angabe - , durch . ersetzen
                $laenge=explode(']', $gra_id[2]);
                $laenge=str_replace(',', '.', $laenge[0]);
                if ($links_bilder_bearbeiten)
                    $edit='<a href="'.$pfad.'formular/grafik_bearbeiten.php?bild_id='.$gra_id[0].'" onclick="window.open(this.href, \'Grafik &auml;ndern\', \'width=700,height=600,left=100,top=200,resizable=yes,scrollbars=yes\'); return false;" title="Grafik bearbeiten" class="icon"><img src="'.$pfad.'icons/edit.png" alt="bearbeiten" /></a>';
                    // <a href="'.$pfad.'formular/grafik_groesse.php?grafik='.$gra_id[0].'&amp;abschnitt=1044" onclick="javascript:window.open(this.href, \'Material &auml;ndern\', \'width=700,height=600,left=100,top=200,resizable=yes,scrollbars=yes\'); return false;" title="Gr&ouml;&szlig;e und Position der Grafik &auml;ndern" class="icon"><img src="'.$pfad.'icons/groesse_aendern.png" alt="groesse_aendern" /></a>
                $einzeltext=$grafics_in_text[0]. '<a href="'.$pfad.$my_grafic["url"].'" title="'.$my_grafic["alt"].'"><img src="'.$pfad.$my_grafic["url"].'" alt="'.$my_grafic["alt"].'" style="width: '.($laenge/15*100*$teilaufgaben_nebeneinander).'%; float: '.$gra_id[1].'; vertical-align:text-top;" /></a> '.$edit.$rest;
            }
            $grafics_in_text=explode('[grafic;', $einzeltext);
        }
    }
    
	if (preg_match("#(.*)\[file;(.*)\](.*)#is",$einzeltext)) {
        $gra_id=explode('[file;', $einzeltext);
        $gra_id=explode(']', $gra_id[1]);
        $my_file=$db->link_id($gra_id[0]);
        if ($my_file["typ"]==1) $typ='AB';
        if ($my_file["typ"]==2) $typ='Folie';
        if ($my_file["typ"]==3) $typ='Datei';
        $typ='<img src="'.$pfad.'icons/arbeitsblatt.png" alt="dokument" /> '.$typ;
        
        if ($links_bilder_bearbeiten)
            $edit=' <a href="'.$pfad.'formular/link_bearbeiten.php?link_id='.$gra_id[0].'" onclick="javascript:window.open(this.href, \'Material &auml;ndern\', \'width=700,height=600,left=100,top=200,resizable=yes,scrollbars=yes\'); return false;" title="Datei bearbeiten" class="icon"><img src="'.$pfad.'icons/edit.png" alt="bearbeiten" /></a>';
        $einzeltext=preg_replace('~\[file;(.*)\]~U', '<b>'.$typ.':</b> <a href="'.$pfad.$my_file["url_decode"].'">'.$my_file["beschreibung"].'</a>'.$edit, $einzeltext);
    }
    
	if (preg_match("#(.*)\[filews;(.*)\](.*)#is",$einzeltext)) {
        $gra_id=explode('[filews;', $einzeltext);
        $gra_id=explode(']', $gra_id[1]);
        $my_filews=db_conn_and_sql("SELECT * FROM test WHERE id=".$gra_id[0]);
        if ($links_bilder_bearbeiten)
            $edit=' <a href="'.$pfad.'formular/test_bearbeiten.php?typ=arbeitsblatt&amp;welcher='.$gra_id[0].'" onclick="javascript:window.open(this.href, \'Arbeitsblatt &auml;ndern\', \'width=700,height=600,left=100,top=200,resizable=yes,scrollbars=yes\'); return false;" title="Datei bearbeiten" class="icon"><img src="'.$pfad.'icons/edit.png" alt="bearbeiten" /></a>';
        $einzeltext=preg_replace('~\[filews;(.*)\]~U', '<b><img src="'.$pfad.'icons/arbeitsblatt.png" alt="dokument" title="Arbeitsblatt mit Aufgaben" />:</b> <a href="'.$pfad.'test_druckansicht.php?welcher='.$gra_id[0].'">'.html_umlaute(sql_result($my_filews,0,"test.titel")).'</a>'.$edit, $einzeltext);
    }
    
	if (preg_match("#(.*)\[url;(.*);(.*)\](.*)#is",$einzeltext))
		$einzeltext=preg_replace('~\[url;(.*);(.*)\]~U', '<a href="\1">\2</a>', $einzeltext);
	
    if ($tabelle==true and substr($einzeltext,0,2)!="||") {$tabelle=false; $return.= '   </table>';}
    
    // Aufzaehlung
    // verschiedene Typen gleich untereinander - fortlaufende Aufzaehlung verhindern ($aufzaehlung wird gleich darauf wieder auf true gesetzt)
    if ($aufzaehlung==true and (
        (substr($einzeltext,0,3)=="a) " and $aufzaehlung_type!='a') or
        (substr($einzeltext,0,3)=="A) " and $aufzaehlung_type!='A') or
        (substr($einzeltext,0,3)=="I) " and $aufzaehlung_type!='I') or
        (substr($einzeltext,0,3)=="1) " and $aufzaehlung_type!='1'))) {
            $aufzaehlung=false;
            $return.="\n".'   </ol>';
    }
    
    if (substr($einzeltext,0,3)=="a) " or substr($einzeltext,0,3)=="A) " or substr($einzeltext,0,3)=="I) " or substr($einzeltext,0,3)=="1) ") {
        if ($auflistung[2]) { $auflistung[2]=false; $return.= "\n".'      </ul>';}
        if ($auflistung[1]) { $auflistung[1]=false; $return.= "\n".'      </ul>';}
        if ($auflistung[0]) { $auflistung[0]=false; $return.= "\n".'      </ul>'; }
    }

    
    if ($aufzaehlung==false and substr($einzeltext,0,3)=="a) ") {
        $aufzaehlung=true;
        $return.="\n".'   <ol type="a" style="margin-top:2px; margin-bottom:5px; margin-left:-25px;">';
        $aufzaehlung_type='a';
    }
    if ($aufzaehlung==false and substr($einzeltext,0,3)=="A) ") {
        $aufzaehlung=true;
        $return.="\n".'   <ol type="A" style="margin-top:2px; margin-bottom:5px; margin-left:-25px;">';
        $aufzaehlung_type='A';
    }
    if ($aufzaehlung==false and substr($einzeltext,0,3)=="1) ") {
        $aufzaehlung=true;
        $return.="\n".'   <ol type="1" style="margin-top:2px; margin-bottom:5px; margin-left:-25px;">';
        $aufzaehlung_type='1';
    }
    if ($aufzaehlung==false and substr($einzeltext,0,3)=="I) ") {
        $aufzaehlung=true;
        $return.="\n".'   <ol type="I" style="margin-top:2px; margin-bottom:5px; margin-left:-25px;">';
        $aufzaehlung_type='I';
    }
    if ($aufzaehlung==true and substr($einzeltext,0,3)!="A) " and substr($einzeltext,0,3)!="a) " and substr($einzeltext,0,3)!="1) " and substr($einzeltext,0,3)!="I) ") {
        $aufzaehlung=false;
        $return.="\n".'   </ol>';
    }
    if ($aufzaehlung==true) {
		// if ($teilaufgaben_nebeneinander==0) $teilaufgaben_nebeneinander=1;
		$return.="\n".'<li style="';
		if ($teilaufgaben_nebeneinander>1) {
			$return.='float: left; ';
			if($anzahl_jetzt==0) {
                $return.='clear: both; ';
                $anzahl_jetzt=$teilaufgaben_nebeneinander;
            }
            $return.='width: '.(95/$teilaufgaben_nebeneinander-10).'%; ';
		}
		//else $return.='clear: both; '; // damit Bilder mit float beruecksichtig werden
		$return.='margin-left: 5px; padding-left: 5px; margin-right: 25px;">'.substr($einzeltext,2).'</li>';
		$anzahl_jetzt--;
	}
	
	// Auflistung
    if ($auflistung[0]==false and substr($einzeltext,0,2)=="* ") {
		$auflistung[0]=true;
		$return.="\n".'   <ul style="margin-top:2px; margin-bottom:5px;">';
	}
    if ($auflistung[1]==false and substr($einzeltext,0,3)=="** ") {
		if ($auflistung[0]==false) { $auflistung[0]=true; $return.="\n".'   <ul style="margin-top:2px; margin-bottom:5px;">'; }
		$auflistung[1]=true;
		$return.= "\n".'      <ul>';
	}
    if ($auflistung[2]==false and substr($einzeltext,0,4)=="*** ") {
		if ($auflistung[0]==false) { $auflistung[0]=true; $return.="\n".'   <ul style="margin-top:2px; margin-bottom:5px;">'; }
		if ($auflistung[1]==false) { $auflistung[1]=true; $return.="\n".'      <ul>'; }
		$auflistung[2]=true;
		$return.= "\n".'         <ul>';
	}
	
    if ($auflistung[2] and substr($einzeltext,0,4)!="*** ") { $auflistung[2]=false; $return.= "\n".'      </ul>';}
    if (!$auflistung[2] and $auflistung[1] and substr($einzeltext,0,3)!="** ") { $auflistung[1]=false; $return.= "\n".'      </ul>';}
	if (!$auflistung[2] and !$auflistung[1] and $auflistung[0] and substr($einzeltext,0,2)!="* ") {
		$auflistung[0]=false; $return.= "\n".'   </ul>';
	}
	if ($auflistung[2]) $return.= "\n".'           <li style="clear: both;">'.substr($einzeltext,4).'</li>';
		else if ($auflistung[1]) $return.= "\n".'        <li style="clear: both;">'.substr($einzeltext,3).'</li>';
			else if ($auflistung[0]) {
				$return.= "\n".'     <li style="clear: both;">';
				if ($anklickbar) $return.='<input type="checkbox" />sdfg';
				$return.=substr($einzeltext,2).'</li>';
			}
    
    // Ankreuz-Fragen mit [x]
    if ($checkboxen==false and substr($einzeltext,0,4)=="[x] ") {
		$checkboxen=true;
		$return.="\n".'   <ul style="margin-top:2px; margin-bottom:5px;list-style-image:url('.$pfad.'look/list_style_image_checkbox.gif)">';
	}
	
	if ($checkboxen==true and substr($einzeltext,0,4)!="[x] ") {
        $checkboxen=false;
        $return.="\n".'   </ul>';
    }
	//else $checkboxen=false;
    if ($checkboxen) {
        $return.= "\n".'     <li style="';
		if ($teilaufgaben_nebeneinander>1) {
			$return.='float: left; ';
			if($anzahl_jetzt==0) {
                $return.='clear: both; ';
                $anzahl_jetzt=$teilaufgaben_nebeneinander;
            }
            $return.='width: '.(95/$teilaufgaben_nebeneinander-5).'%; ';
		}
		$return.='margin-left: 5px; padding-left: 5px; margin-right: 25px;">'.substr($einzeltext,3).'</li>';
    }
    if (preg_match("#(.*)\[x\](.*)#is",$einzeltext)) $einzeltext=preg_replace('~\[x\]~U', '&#10063;', $einzeltext);
    
    
    // Tabelle
    if ($tabelle==false and substr($einzeltext,0,2)=="||") {$tabelle=true; $return.= '   <table class="test_tabelle" cellspacing="0">';}
    if ($tabelle==true) {
		$return.= '<tr>';
		$tds=explode("||",$einzeltext);
		for($i=1;$i<count($tds)-1; $i++) {
			if (substr($tds[$i],0,5)=="&lt;|") $return.='<td rowspan="'.substr($tds[$i],5,1).'">'.substr($tds[$i],10).'</td>';
			else if (substr($tds[$i],0,5)=="&lt;-") $return.='<td colspan="'.substr($tds[$i],5,1).'">'.substr($tds[$i],10).'</td>';
			else $return.= '<td>'.$tds[$i].'</td>';	
		}
		$return.= '</tr>';
    }
    if ($aufzaehlung==false and $auflistung[0]==false and $tabelle==false and $checkboxen==false)
        $return.= $einzeltext; //.'<br style="clear: both;">';
  }
  if ($tabelle==true) $return.= '</table>';
  if ($checkboxen==true) $return.="\n".'</ul>';
  if ($aufzaehlung==true) $return.="\n".'</ol>';
  if ($auflistung[2]==true) $return.="\n".'</ul>';
  if ($auflistung[1]==true) $return.="\n".'</ul>';
  if ($auflistung[0]==true) $return.="\n".'</ul>';
	
	for ($k=0; $k<count($programm_codes[1]); $k++) {
		$programm_codes[2][$k]=str_replace("<", "&lt;", $programm_codes[2][$k]);
		$return=preg_replace('|HIER_PROGRAMMCODE_EINSETZEN|', '<pre class="brush: '.$programm_codes[1][$k].'">'.$programm_codes[2][$k].'</pre>', $return, 1);
	}
  
  // nur wenn Formeln gefunden werden, soll auch der Formeleditor genutzt werden
  if ($formel_gefunden)
    $return.='<script>translateOnLoad = true;</script>';
  if ($syntax_gefunden)
	$return.='<script>SyntaxHighlighter.all();</script>';
  return $return;
}

// -------------------------- wiederkehrende Formular-Elemente --------------------------------
	function bewertungstabelle_select($fach_klasse_auswahl) {
		$inhalt="";
		$benutzte_bewertungstabelle=sql_result(db_conn_and_sql("SELECT fach_klasse.bewertungstabelle
			FROM fach_klasse
			WHERE fach_klasse.id=".$fach_klasse_auswahl),0,"fach_klasse.bewertungstabelle");
		$bewertungstabelle=db_conn_and_sql("SELECT DISTINCT * FROM `bewertungstabelle` LEFT JOIN `schule_user` ON (`bewertungstabelle`.`schule`=`schule_user`.`schule` AND `schule_user`.`user`=".$_SESSION["user_id"]." AND `schule_user`.`aktiv`=1)
			WHERE `bewertungstabelle`.`aktiv`=1
				AND (`bewertungstabelle`.`user`=".$_SESSION['user_id']." OR `schule_user`.`user`=".$_SESSION["user_id"].")
			ORDER BY `bewertungstabelle`.`name`");
		$bewertungstabelle_ausgewaehlt=false;
		for ($j=0;$j<sql_num_rows($bewertungstabelle);$j++) {
				$inhalt.='<option value="'.sql_result($bewertungstabelle,$j,"bewertungstabelle.id").'"';
				if (sql_result($bewertungstabelle,$j,"bewertungstabelle.id")==$benutzte_bewertungstabelle) {
					$inhalt.=' selected="selected"';
					$bewertungstabelle_ausgewaehlt=true;
				}
				$inhalt.='>'.html_umlaute(sql_result($bewertungstabelle,$j,"bewertungstabelle.name")).'</option>';
			}
			
			if (!$bewertungstabelle_ausgewaehlt and $fach_klasse_auswahl!=0)
				$inhalt.='<option value="'.$benutzte_bewertungstabelle.'" selected="selected">'.html_umlaute(sql_result(db_conn_and_sql("SELECT name FROM bewertungstabelle WHERE id=".$benutzte_bewertungstabelle),0,"bewertungstabelle.name")).'</option>';
		return $inhalt;
	}
	
	function notenberechnungsvorlagen_select($fach_klasse_auswahl, $deaktivieren) {
		$inhalt="";
		$benutzte_vorlage_result=db_conn_and_sql("SELECT *
			FROM fach_klasse
			WHERE fach_klasse.id=".$fach_klasse_auswahl);
		$benutzte_vorlage_id=sql_result($benutzte_vorlage_result,0,"fach_klasse.notenberechnungsvorlage");
		if ($benutzte_vorlage_id == NULL)
			$benutzte_vorlage_id=0;
		$eigene_vorlagen=db_conn_and_sql("SELECT *
			FROM notenberechnungsvorlage
			WHERE (notenberechnungsvorlage.aktiv=1 OR notenberechnungsvorlage.id=".$benutzte_vorlage_id.")
				AND notenberechnungsvorlage.schule IS NULL
				AND notenberechnungsvorlage.user=".$_SESSION["user_id"]);
		$vorlagen_der_schule=db_conn_and_sql("SELECT *
			FROM schule_user, schule, notenberechnungsvorlage
			WHERE (notenberechnungsvorlage.aktiv=1 OR notenberechnungsvorlage.id=".$benutzte_vorlage_id.")
				AND schule_user.schule=schule.id
				AND schule_user.schule=notenberechnungsvorlage.schule
				AND schule_user.user=".$_SESSION["user_id"]."
			ORDER BY schule.kuerzel, notenberechnungsvorlage.name");
		if (sql_num_rows($eigene_vorlagen)>0) {
			$inhalt.='<optgroup label="eigene Vorlagen">';
			for ($i=0; $i<sql_num_rows($eigene_vorlagen); $i++) {
				// value bekommt -h (hidden) bzw. -v (visible) fuer den Button
				$inhalt.='<option value="'.sql_result($eigene_vorlagen,$i,"notenberechnungsvorlage.id").'-v"';
				//if ($deaktivieren!==0)
				//	$inhalt.=' onclick="document.getElementById(\''.$deaktivieren.'\').style.visibility=\'visible\'"';
				if (sql_result($eigene_vorlagen,$i,"notenberechnungsvorlage.id")==$benutzte_vorlage_id)
					$inhalt.=' selected="selected"';
				$inhalt.='>'.sql_result($eigene_vorlagen,$i,"notenberechnungsvorlage.name").'</option>';
			}
			$inhalt.='</optgroup>';
		}
		if (sql_num_rows($vorlagen_der_schule)>0) {
			$inhalt.='<optgroup label="Vorlagen der Schule">';
			for ($i=0; $i<sql_num_rows($vorlagen_der_schule); $i++) {
				$inhalt.='<option value="'.sql_result($vorlagen_der_schule,$i,"notenberechnungsvorlage.id");
				if ($deaktivieren!==0 and sql_result($vorlagen_der_schule,$i,"notenberechnungsvorlage.user")==$_SESSION["user_id"])
					$inhalt.='-v';
				if ($deaktivieren!==0 and sql_result($vorlagen_der_schule,$i,"notenberechnungsvorlage.user")!=$_SESSION["user_id"])
					$inhalt.='-h';
				if (sql_result($vorlagen_der_schule,$i,"notenberechnungsvorlage.id")==$benutzte_vorlage_id)
					$inhalt.='" selected="selected';
				$inhalt.='">'.sql_result($vorlagen_der_schule,$i,"notenberechnungsvorlage.name").' ('.sql_result($vorlagen_der_schule,$i,"schule.kuerzel").')</option>';
			}
			$inhalt.='</optgroup>';
		}
		return $inhalt;
	}



// -------------------------- FORMULARE -------------------------------------------------------
function themen_auswahl($pfad, $name, $selected_tags) {
	$db = new db();
	$nicht_anzeigen=false;
	$inhalt='<label for="'.$name.'_0"><img src="'.$pfad.'icons/thema.png" alt="Thema" title="Themen" /><em>*</em>:</label>
		<a href="'.$pfad.'formular/thema_neu.php" onclick="fenster(this.href, \'neues Thema\'); return false;" class="icon" title="Falls ein neues Thema erstellt werden muss, klicken Sie hier. Achtung: der bisher ins Formular eingegebene Inhalt wird dabei zur&uuml;ckgesetzt."><img src="'.$pfad.'icons/neu.png" alt="neu" /></a> ';
	for($thema=0;$thema<10;$thema++) {
		$inhalt.='<select id="'.$name.'_'.$thema.'" name="'.$name.'_'.$thema.'"';
		if ($nicht_anzeigen) $inhalt.=' style="display:none;"';
		if ($thema<9) $inhalt.=' onchange="
			if (this.value!=\'-\') document.getElementById(\''.$name.'_'.($thema+1).'\').style.display=\'inline\';
			else if (document.getElementById(\''.$name.'_'.($thema+1).'\').value==\'-\') document.getElementById(\''.$name.'_'.($thema+1).'\').style.display=\'none\';"';
		$inhalt.='>';
		if (!isset($selected_tags[$thema])) $nicht_anzeigen=true;
		//if ($thema>0) $inhalt.='<option value="-">-</option>';
		$inhalt.='<option value="-">-</option>';
		$inhalt.=$db->themenoptions($selected_tags[$thema]);
		$inhalt.='</select> ';
	}
	return $inhalt;
}

// Thumbnails erstellen (von link_neu.php und grafik_bearbeiten.php verwendet)
function thumbnail_erstellen($vollpfad, $datei) {
	// http://www.webmasterpro.de/coding/article/thumbnails-erstellen-mit-php.html
	// ---- Bilddaten laden ----
	if (function_exists("imagecreatefromjpeg")) {
		$imagefile = $vollpfad.$datei;
		$imagesize = getimagesize($imagefile);
		$imagewidth = $imagesize[0];
		$imageheight = $imagesize[1];
		$imagetype = $imagesize[2];
		
		switch ($imagetype)
		{
			// Bedeutung von $imagetype:
			// 1 = GIF, 2 = JPG, 3 = PNG, 4 = SWF, 5 = PSD, 6 = BMP, 7 = TIFF(intel byte order), 8 = TIFF(motorola byte order), 9 = JPC, 10 = JP2, 11 = JPX, 12 = JB2, 13 = SWC, 14 = IFF, 15 = WBMP, 16 = XBM
			case 1: // GIF
				$image = imagecreatefromgif($imagefile);
				break;
			case 2: // JPEG
				$image = imagecreatefromjpeg($imagefile);
				break;
			case 3: // PNG
				$image = imagecreatefrompng($imagefile);
				break;
			default:
				return 'Unsupported imageformat';
		}
		
		// ---- Thumbnailgroesse berechnen ----
		// Maximalausmaße
		$maxthumbwidth = 80;
		$maxthumbheight = 80;
		// Ausmaße kopieren, wir gehen zuerst davon aus, dass das Bild schon Thumbnailgröße hat
		$thumbwidth = $imagewidth;
		$thumbheight = $imageheight;
		// Breite skalieren falls nötig
		if ($thumbwidth > $maxthumbwidth)
		{
			$factor = $maxthumbwidth / $thumbwidth;
			$thumbwidth *= $factor;
			$thumbheight *= $factor;
		}
		// Höhe skalieren, falls nötig
		if ($thumbheight > $maxthumbheight)
		{
			$factor = $maxthumbheight / $thumbheight;
			$thumbwidth *= $factor;
			$thumbheight *= $factor;
		}
		// Thumbnail erstellen
		$thumb = imagecreatetruecolor($thumbwidth, $thumbheight);
		
		
		// ---- Bild skalieren ----
		imagecopyresampled(
			$thumb,
			$image,
			0, 0, 0, 0, // Startposition des Ausschnittes
			$thumbwidth, $thumbheight,
			$imagewidth, $imageheight
		);
		
		// ---- Thumbnail speichern ----
		$thumbfile = $vollpfad . "tmb_".$datei;
		imagepng($thumb, $thumbfile);
		// Speicher freigeben
		imagedestroy($thumb);
	}
	else
		return "Installieren Sie die GD-Funktionen";
}

function eintragung_grafik_link($pfad, $option, $file_grafic) {
	$link="link";
	if ($file_grafic=="grafic")
		$link="grafik";
	echo '
    <form action="'.$pfad.'formular/link_neu.php?eintragen=true&amp;type='.$file_grafic.'" method="post" enctype="multipart/form-data" accept-charset="ISO-8859-1">
		<label for="link_file">Datei<em>*</em>:</label>
		<input type="file" name="link_file" id="link_file" size="50" onchange="if (document.getElementById(\''.$link.'_beschreibung_fuellen\').value==\'\') document.getElementById(\''.$link.'_beschreibung_fuellen\').value=this.value.replace(/_/g, \' \').slice(0,(document.getElementById(\''.$link.'_beschreibung_fuellen\').value.length-4))" />';
		$benutzer=db_conn_and_sql("SELECT * FROM benutzer WHERE id=".$_SESSION['user_id']);
		$ids = explode(";",sql_result(db_conn_and_sql("SELECT letzte_themen_auswahl FROM fach_klasse WHERE fach_klasse.id=".sql_result($benutzer,0,"letzte_fachklasse")),0,"letzte_themen_auswahl")); array_pop($ids); // ...weil das letzte leer ist
        echo '<br /><label for="'.$link.'_beschreibung';
        echo '">Beschreibung<em>*</em>:</label>
                    <input type="text" name="'.$link.'_beschreibung" id="'.$link.'_beschreibung_fuellen" size="50" maxlength="60" /><br />';
        echo themen_auswahl($pfad, $link.'_thema', $ids);
        echo '<br />
				<label for="'.$link.'_lernbereich">Lernbereich<em>*</em>:</label>
                    <select name="'.$link.'_lernbereich">';
                        $db = new db();
                        echo $db->lernbereichoptions(sql_result(db_conn_and_sql("SELECT letzter_lernbereich FROM fach_klasse WHERE fach_klasse.id=".sql_result($benutzer,0,"letzte_fachklasse")),0,"letzter_lernbereich"));
        echo '      </select>
            <br />';
        if($file_grafic=="file") {
			echo '                <p>
                <label for="link_typ">Typ<em>*</em>:</label> <select id="link_typ" name="link_typ">
					<option value="1">Arbeitsblatt</option>
					<option value="2">Folie</option>
					<option value="3">Link</option>
				</select></p>';
		}
		else {
            if ($option!="without width") {
				echo '<hr />
				<label for="grafik_groesse">Breite<em>*</em>:</label> <input type="text" name="grafik_groesse" size="2" maxlength="4" /> cm';
			}
		}
            echo ' <button title="'.$link.' in Material-Datenbank &uuml;bernehmen und im Editor-Textfeld eintragen" onclick="auswertung=new Array(new Array(1, \''.$link.'_beschreibung\',\'nicht_leer\'), new Array(1, \''.$link.'_thema_0\',\'nicht_leer\',\'-\'), new Array(1, \''.$link.'_lernbereich\',\'nicht_leer\',\'-\')';
				if ($link=="grafic" and $option!="without width")
					echo ", new Array(1, \'grafik_groesse\',\'komma_zahl\')";
				echo '); pruefe_formular(auswertung); return false;">Speichern</button>
            </form>';
}



function eintragung_aufgabe($id, $pfad) {
	$db=new db;
    $themen = $db->themen();
	$buch=$db->buecher();
	$aufgabe_teilaufgaben=1;
	$benutzer=db_conn_and_sql("SELECT * FROM benutzer WHERE id=".$_SESSION['user_id']);
	
	if (sql_result($benutzer,0,"letzte_fachklasse")>0) {
		$themen_ids = explode(";", sql_result(db_conn_and_sql("SELECT letzte_themen_auswahl FROM fach_klasse WHERE fach_klasse.id=".sql_result($benutzer,0,"letzte_fachklasse")),0,"letzte_themen_auswahl")); array_pop($themen_ids); // ...weil das letzte leer ist
	}
	
    if (!isset($pfad))
		$pfad="../";
	
	if ($id=="neu") {
		$aufgabe['lernbereich_id']=0;
	}
	else {
		$aufgabe=$db->aufgabe($id);
		
		$aufgabe_bemerkung=' value="'.$aufgabe['bemerkung'].'"';
		$aufgabe_punkte=' value="'.kommazahl($aufgabe['punkte']).'"';
		$aufgabe_bearbeitungszeit=' value="'.kommazahl($aufgabe['bearbeitungszeit']).'"';
		$aufgabe_cm=' value="'.kommazahl($aufgabe['cm']).'"';
		$aufgabe_teilaufgaben=$aufgabe['teilaufgaben_nebeneinander'];
		
		$buch_seite=' value="'.$aufgabe['buch'][0]['seite'].'"';
		$buch_nummer=' value="'.$aufgabe['buch'][0]['nummer'].'"';
	}
	
	$inhalt="";
	if ($id!="neu")
        $inhalt.='<input type="hidden" name="id" value="'.$id.'" />';
    
    $inhalt.='
		<div class="tooltip" id="tt_punkte">
			Wenn die Aufgabe in einem Test gestellt werden soll, muss die Punktzahl angegeben werden. Aus den einzelnen Aufgabenpunktzahlen wird dann die Gesamtpunktzahl des Tests errechnet.</div>
		<div class="tooltip" id="tt_platz_lassen">
			Wenn die Aufgabe in einem Test gestellt wird, in welchem "Platz unter Aufgabe freilassen" ausgew&auml;hlt ist, wird der entsprechende cm-Wert frei gelassen bzw. mit liniertem, karriertem oder Millimeterpapier bedruckt. Die Werte k&ouml;nnen in 0,5er-Schritten angegeben werden.</div>
		<div class="tooltip" id="tt_teilaufgaben">
			Ist es aus Platzgr&uuml;nden g&uuml;nstiger einzelne kurze Teilaufgaben nebeneinander abzudrucken, kann man hier eine nat&uuml;rliche Zahl gr&szlig;er als 1 eingeben. Dies funktioniert nat&uuml;rlich nur, wenn im Aufgabentext auch Teilaufgaben vorhanden sind. (siehe Hilfe zur Syntax: "Aufz&auml;hlung")</div>
		<select id="art" name="art" onchange="document.getElementById(\'buch\').style.display=this.value==\'text\'?\'none\':\'block\'; document.getElementById(\'buch\').style.display=this.value!=\'text\'?\'block\':\'none\';">
			<option value="text">Text</option>';
			for ($i=0;$i<count($buch);$i++) {
                $inhalt.='
				<option value="'.$buch[$i]['id'].'"';
                if ($aufgabe['buch'][0]['id']==$buch[$i]['id'])
                    $inhalt.=' selected="selected"'; $inhalt.='>'.$buch[$i]['kuerzel'].'</option>'; // onclick="document.getElementById(\'thema_0\').value = '.$buch[$i]['letztes_thema'].'; document.getElementById(\'lernbereich\').value = '.$buch[$i]['letzter_lernbereich'].';"
			}
		$selected_tags=''; $thema=0;
		for($thema=0; @$aufgabe['thema'][$thema]['id']>0; $thema++)
			$selected_tags[$thema]=$aufgabe['thema'][$thema]['id'];
		$inhalt.='
		</select>
		<fieldset id="buch" name="buch"';
        if (!isset($aufgabe['buch']))
            $inhalt.=' style="display: none;"';
        $inhalt.='><legend><img src="'.$pfad.'/icons/buch.png" alt="buch" title="Buch" /></legend>
		<label for="seite">Seite<em>*</em>:</label> <input type="text" name="seite" size="2" maxlength="4"'.$buch_seite.' /> - <label for="nummer">Nr.:</label> <input type="text" name="nummer" size="7" maxlength="90"'.$buch_nummer.' />
		</fieldset><br />';
		if ($id!="neu") $inhalt.=themen_auswahl($pfad, 'thema', $selected_tags);
		else $inhalt.=themen_auswahl($pfad, 'thema', $themen_ids);
		
			
		$inhalt.='<br />
      <label for="lernbereich">Lernbereich<em>*</em>:</label> <select id="lernbereich" name="lernbereich">';
		if ($id!="neu")
			$inhalt.=$db->lernbereichoptions($aufgabe['lernbereich_id']);
		else
			if (sql_result($benutzer,0,"letzte_fachklasse")>0)
				$inhalt.=$db->lernbereichoptions(sql_result(db_conn_and_sql("SELECT letzter_lernbereich FROM fach_klasse WHERE fach_klasse.id=".sql_result($benutzer,0,"letzte_fachklasse")),0,"letzter_lernbereich"));
			else
				$inhalt.=$db->lernbereichoptions(0);
		// Aufgabenloesung ist mit html_umlaute, weil es im Normalfall ueber die Funktion syntax_zu_html abgehandelt wird (html_umlaute enthalten)
      $inhalt.='
      </select><br />
		<div style="float: left; width: 415px;">
		<label for="text" title="bei Buchauswahl nicht erforderlich">Text: <a href="'.$pfad.'formular/hilfe.php?inhalt=syntax" onclick="fenster(this.href, \'Hilfe zur Syntax\'); return false;" class="icon" title="Hilfe zur Syntax"><img src="'.$pfad.'icons/hilfe.png" alt="Hilfe" /></a></label><br /><textarea id="markup_aufgabentext" name="text" rows="5" cols="50">'.html_umlaute($aufgabe['text']).'</textarea></div>
		<div style="float: left; width: 415px;"><label for="loesung">L&ouml;sung:</label><br /><textarea id="markup_aufgabenloesung" name="loesung" rows="5" cols="50">'.html_umlaute($aufgabe['loesung']).'</textarea></div>';
      $inhalt.='
        <br style="clear: both;">
      <label for="bemerkung"><img src="'.$pfad.'icons/kommentar.png" alt="bemerkung" title="Bemerkungen" />:</label> <input type="text" name="bemerkung" size="35" maxlength="60"'.$aufgabe_bemerkung.' /><br />';
      $inhalt.='<label for="punkte">Punkte: <img src="'.$pfad.'icons/information.png" class="hilfe_tooltip" alt="Hilfe" onmouseover="showWMTT(\'tt_punkte\')" onmouseout="hideWMTT()" /></label> <input type="text" name="punkte" size="2" maxlength="3"'.$aufgabe_punkte.' />
		<label for="bearbeitungszeit" style="width: 30px;"><img src="'.$pfad.'/icons/zeit.png" alt="zeit" title="Berabeitungszeit" />:</label> <input type="text" name="bearbeitungszeit" size="2" maxlength="3"'.$aufgabe_bearbeitungszeit.' /> min
		<label for="teilaufgaben_nebeneinander" style="width: 50px;"><img src="'.$pfad.'/icons/aufgabe_in_spalten.png" alt="zeit" title="Anzahl der nebeneinander stehenden Teilaufgaben (a, b, c, ...)" />: <img src="'.$pfad.'icons/information.png" class="hilfe_tooltip" alt="Hilfe" onmouseover="showWMTT(\'tt_teilaufgaben\')" onmouseout="hideWMTT()" /></label> <input type="text" size="1" maxlength="1" name="teilaufgaben_nebeneinander" value="'.$aufgabe_teilaufgaben.'" /><br />
		<script>
			$(document).ready(function() {
				$("#slider").slider({ min: 1, max: 9';
				if ($aufgabe['schwierigkeitsgrad']>0) $inhalt.=', value: '.$aufgabe['schwierigkeitsgrad']; 
				$inhalt.=', change: function(event, ui) { document.getElementById(\'schwierigkeitsgrad_aufgabe\').value=($( "#slider" ).slider( "option", "value" )); }
				});
			});
        </script>
        <label for="schwierigkeitsgrad" style="float: left;">Schwierigkeit:</label> <div id="slider" style="width: 300px; float: left;">
                <span style="float: left; font-size: 8pt;">sehr leicht</span>
                <span style="float: right; font-size: 8pt;">sehr schwer</span>
            </div><input type="hidden" id="schwierigkeitsgrad_aufgabe" name="schwierigkeitsgrad" value="'.$aufgabe['schwierigkeitsgrad'].'" />';
	      /*  for ($i=1;$i<=9;$i++)
				if ($aufgabe['schwierigkeitsgrad']==$i) $inhalt.='<input type="radio" name="schwierigkeitsgrad_loeschen" value="'.$i.'" checked="checked" />';
				else $inhalt.='<input type="radio" name="schwierigkeitsgrad_loeschen" value="'.$i.'" />';*/
      $inhalt.='
      <br />';
	return $inhalt;
}

function eintragung_test () {
	$db= new db;
	$notentyp=db_conn_and_sql("SELECT * FROM `notentypen`");
	$themen=db_conn_and_sql("SELECT * FROM `thema`");
	$lernbereiche = $db->lernbereiche();
	$benutzer=db_conn_and_sql("SELECT * FROM benutzer WHERE id=".$_SESSION['user_id']);
	if (sql_result($benutzer,0,"letzte_fachklasse")>0) {
		$ids = explode(";",sql_result(db_conn_and_sql("SELECT letzte_themen_auswahl FROM fach_klasse WHERE fach_klasse.id=".sql_result($benutzer,0,"letzte_fachklasse")),0,"letzte_themen_auswahl")); array_pop($ids); // ...weil das letzte leer ist
	}
	
	$inhalt='<ol class="divider"><li><label for="test_notentyp">Zensur-Typ<em>*</em>:</label> <select name="test_notentyp">';
	for($i=0;$i<sql_num_rows($notentyp);$i++) $inhalt.='<option value="'.sql_result($notentyp,$i,'notentypen.id').'">'.html_umlaute(sql_result($notentyp,$i,'notentypen.name')).'</option>';
	$inhalt.='</select><br />
    <label for="test_lernbereich">Lernbereich<em>*</em>:</label> <select name="test_lernbereich">';
    if (sql_result($benutzer,0,"letzte_fachklasse")>0)
		$inhalt.=$db->lernbereichoptions(sql_result(db_conn_and_sql("SELECT letzter_lernbereich FROM fach_klasse WHERE fach_klasse.id=".sql_result($benutzer,0,"letzte_fachklasse")),0,"letzter_lernbereich"));
	else
		$inhalt.=$db->lernbereichoptions(0);
	$inhalt.='
      </select></li>
    <li>'.themen_auswahl('./', 'test_thema', $ids).'</li>
	<li><label for="test_vorspann">Test-Vorspann:</label> <textarea name="test_vorspann" cols="50" rows="2"></textarea></li>
	<li><fieldset><legend>Aufgaben</legend>
	<input type="radio" name="test_lokal" value="erstellen" checked="checked" onclick="document.getElementById(\'test_datei\').style.display=this.checked==1?\'none\':\'block\';" /> Aufgaben erstellen / aus Fundus holen<br />
    <input type="radio" name="test_lokal" value="hochladen" onclick="document.getElementById(\'test_datei\').style.display=this.checked==1?\'block\':\'none\';" /> als Datei vorhanden<br />
	<input type="file" id="test_datei" name="test_datei" style="display: none;" /></fieldset></li></ol>';
	return $inhalt;
}

// deprecated
function eintragung_link ($mit_typ) {
	$db = new db;
	if ($mit_typ)
		$inhalt.='<label for="link_typ">Typ<em>*</em>:</label> <select id="link_typ" name="link_typ">
					<option value="1">Arbeitsblatt</option>
					<option value="2">Folie</option>
					<option value="3">Link</option>
				</select>';
	$benutzer=db_conn_and_sql("SELECT * FROM benutzer WHERE id=".$_SESSION['user_id']);
	$letzte_fachklasse=sql_fetch_assoc($benutzer);
	$letzte_fachklasse=$letzte_fachklasse["letzte_fachklasse"];
	
	if ($letzte_fachklasse>0)
		$ids = explode(";",sql_result(db_conn_and_sql("SELECT letzte_themen_auswahl FROM fach_klasse WHERE fach_klasse.id=".$letzte_fachklasse),0,"letzte_themen_auswahl")); array_pop($ids); // ...weil das letzte leer ist
	$inhalt.='<input type="checkbox" name="link_lokal" id="link_lokal" value="1" title="soll eine Internetadresse als Verweisziel dienen oder eine Datei hochgeladen werden?" onclick="document.getElementById(\'link_file\').style.display=this.checked==1?\'none\':\'inline\'; document.getElementById(\'link_url\').style.display=this.checked==1?\'inline\':\'none\';" /> Internetadresse<br />
				<label for="link_file">Ort<em>*</em>:</label> <input type="file" name="link_file" id="link_file" size="50" onchange="if (document.getElementById(\'link_beschreibung_fuellen\').value==\'\') document.getElementById(\'link_beschreibung_fuellen\').value=this.value.replace(/_/g, \' \').slice(0,(document.getElementById(\'link_beschreibung_fuellen\').value.length-4))" /><input type="text" name="link_url" id="link_url" value="http://www." size="35" maxlength="50" style="display: none;" /><br />
				<label for="link_beschreibung">Beschreibung<em>*</em>:</label> <input type="text" name="link_beschreibung" id="link_beschreibung_fuellen" size="50" maxlength="80" /><br />
				'.themen_auswahl('../', 'link_thema', $ids).'<br />
				<label for="link_lernbereich">Lernbereich<em>*</em>:</label> <select name="link_lernbereich">'.$db->lernbereichoptions(sql_result(db_conn_and_sql("SELECT letzter_lernbereich FROM fach_klasse WHERE fach_klasse.id=".sql_result($benutzer,0,"letzte_fachklasse")),0,"letzter_lernbereich")).'
				</select>';
	return "nicht mehr noetig";
}

function eintragung_grafik () {
		$db = new db;
		$benutzer=db_conn_and_sql("SELECT * FROM benutzer WHERE id=".$_SESSION['user_id']);
		$ids = explode(";",sql_result(db_conn_and_sql("SELECT letzte_themen_auswahl FROM fach_klasse WHERE fach_klasse.id=".sql_result($benutzer,0,"letzte_fachklasse")),0,"letzte_themen_auswahl")); array_pop($ids); // ...weil das letzte leer ist
		
		$inhalt.='<label for="grafik_file">Ort<em>*</em>:</label> <input type="file" name="grafik_file" size="50" onchange="if (document.getElementById(\'grafik_beschreibung_fuellen\').value==\'\') document.getElementById(\'grafik_beschreibung_fuellen\').value=this.value.replace(/_/g, \' \').slice(0,(document.getElementById(\'grafik_beschreibung_fuellen\').value.length-4))" /><br />
				<label for="grafik_alt">Beschreibung<em>*</em>:</label> <input type="text" name="grafik_alt" id="grafik_beschreibung_fuellen" size="50" maxlength="60" /><br />
				'.themen_auswahl('../', 'grafik_thema', $ids).'<br />
				<label for="grafik_lernbereich">Lernbereich<em>*</em>:</label> <select name="grafik_lernbereich">'.$db->lernbereichoptions(sql_result(db_conn_and_sql("SELECT letzter_lernbereich FROM fach_klasse WHERE fach_klasse.id=".sql_result($benutzer,0,"letzte_fachklasse")),0,"letzter_lernbereich")).'
				</select>';
	return $inhalt;
}


function eintragung_sonstiges_material () {
	$benutzer=db_conn_and_sql("SELECT * FROM benutzer WHERE id=".$_SESSION['user_id']);
	$letzte_fachklasse=sql_fetch_assoc($benutzer);
	$letzte_fachklasse=$letzte_fachklasse["letzte_fachklasse"];
	$letzte_themen=db_conn_and_sql("SELECT letzte_themen_auswahl FROM fach_klasse WHERE fach_klasse.id=".$letzte_fachklasse);
	$letzte_themen=sql_fetch_assoc($letzte_themen);
	$letzte_themen=$letzte_themen["letzte_themen_auswahl"];
	$ids = explode(";",$letzte_themen); array_pop($ids); // ...weil das letzte leer ist
	$inhalt.='<label for="material_name">Name<em>*</em>:</label> <input type="text" name="material_name" size="30" maxlength="255" /><br />
		<label for="material_beschreibung">Beschreibung:</label> <input type="text" name="material_beschreibung" size="35" maxlength="255" /><br />
		<label for="material_aufbewahrungsort">Aufbewahrungsort:</label> <input type="text" name="material_aufbewahrungsort" size="35" maxlength="255" /><br />
      	'.themen_auswahl('./', 'material_thema', $ids);
	return $inhalt;
}

// TODO zusammenfuehren mit
// Funktion wird bei der Eintragung eines neuen Abschnitts-Containers aus dem Fundus heraus aktiviert und enthaelt lediglich den Inhalt eines Containers
function eintragung_inhaltstypen($abschnitt_veraendern) {
	$pfad="../";
	$inhalt='
		<div class="tooltip" id="tt_inhaltstypen">
		<p>Der Inhalt eines Abschnittscontainers wird hier gew&auml;hlt. Jegliche Materialien werden automatisch in die Materialdatenbank eingeordnet und bleiben auch dann erhalten, wenn der Abschnittscontainer gel&ouml;scht wird.
		Dies betrifft alle Aufgaben, Tests, Grafiken, Arbeitsbl&auml;tter, Folien, Links und sonstige Materialien.</p>
		<p>Mit dem Symbol <img src="'.$pfad.'icons/add.png" alt="add" /> k&ouml;nnen Sie dem Abschnitt mehrere Inhalte zuordnen. So k&ouml;nnen (damit die logische Abgrenzung zum n&auml;chsten Abschnitt gew&auml;hrleistet ist) hier z.B. gleichzeitig eine &Uuml;beschrift, ein Merksatz und zwei Grafiken vorkommen.</p></div>
			<select id="inhaltstyp_1" name="inhaltstyp_1" onchange="var einzeltyp = Math.floor(this.value);
				var abschnitt_typ = Math.round((this.value-einzeltyp)*10);
				document.getElementById(\'span_1\').style.display = \'none\';
				document.getElementById(\'span_2\').style.display = \'none\';
				document.getElementById(\'span_3\').style.display = \'none\';
				document.getElementById(\'span_6\').style.display = \'none\';
				document.getElementById(\'span_7\').style.display = \'none\';
				document.getElementById(\'span_\' + abschnitt_typ).style.display = \'inline\';';
	if ($abschnitt_veraendern) $inhalt.='
				switch (abschnitt_typ) {
					case 1: /* Ueberschrift */
						document.getElementById(\'hefter_1_1\').checked=true;
						document.getElementById(\'medium_1\').options[0].selected=true;
						document.getElementById(\'sozialform_1\').options[0].selected=true;
						break;
					case 2: /* Test */
						document.getElementById(\'hefter_1_0\').checked=true;
						document.getElementById(\'medium_1\').options[2].selected=true;
						document.getElementById(\'sozialform_1\').options[1].selected=true;
						break;
					case 3: /* Beispiel- od. Uebungsaufgabe */
						document.getElementById(\'hefter_1_2\').checked=true;
						document.getElementById(\'medium_1\').options[0].selected=true;
						document.getElementById(\'sozialform_1\').options[1].selected=true;
                        break;
					case 6: /* Material */
						document.getElementById(\'sozialform_1\').options[0].selected=true;
						document.getElementById(\'hefter_1_0\').checked=true;
						document.getElementById(\'medium_1\').options[5].selected=true;
						break;
					case 7: /* Text */
						document.getElementById(\'hefter_1_1\').checked=true;
						document.getElementById(\'medium_1\').options[0].selected=true;
						document.getElementById(\'sozialform_1\').options[0].selected=true;
						break;
				}';
		$inhalt.='">
                                        <option value="0">w&auml;hlen...</option>
	                                    <option value="1.6">Material</option>
	                                    <option value="1.2">Test</option>
	                                    <option value="7.7">Text</option>
	                                    <option value="1.1">&Uuml;berschrift</option>
	                                    <option value="1.3">&Uuml;bungsaufgabe</option>
									</select>
				<img src="'.$pfad.'icons/information.png" class="hilfe_tooltip" alt="Hilfe" onmouseover="showWMTT(\'tt_inhaltstypen\')" onmouseout="hideWMTT()" />
				<span id="span_1" style="display:none;"><br />
					<fieldset>
					<select name="ueberschrift_ebene_1">';
						for ($h=1;$h<=5;$h++) {
							$inhalt.='<option value="'.$h.'">'; for ($j=0; $j<$h; $j++) $inhalt.='1.'; $inhalt.='</option>';
						}
					$inhalt.='
					</select>
					<input type="text" name="ueberschrift_text_1" maxlength="150" /><br />
					<label for="ueberschrift_typ_1">Unter&uuml;berschriften-Typ<em>*</em>:</label>
						<select name="ueberschrift_typ_1">'; $typ=array(array("wert"=>"1","beschreibung"=>"1 (Zahlen)"),array("wert"=>"a","beschreibung"=>"a (Kleinbuchstaben)"),array("wert"=>"A","beschreibung"=>"A (Gro&szlig;buchstaben)"),array("wert"=>"I","beschreibung"=>"I (r&ouml;mische Zahlen)"),array("wert"=>"-","beschreibung"=>"nichts"));
						foreach($typ as $value) $inhalt.='<option value="'.$value["wert"].'">'.$value["beschreibung"].'</option>';
						$inhalt.='</select></fieldset></span>
				<span id="span_2" style="display:none;">
					<input type="checkbox" id="test_neu" name="test_neu" value="1" onchange="if (this.checked) {document.getElementById(\'fieldset_neuer_Test\').style.display = \'block\'; document.getElementById(\'fieldset_bestehender_Test\').style.display = \'none\';} else {document.getElementById(\'fieldset_bestehender_Test\').style.display = \'block\'; document.getElementById(\'fieldset_neuer_Test\').style.display = \'none\';};" /> <img src="'.$pfad.'icons/neu.png" alt="neu" title="neuer Test" />
					<fieldset id="fieldset_neuer_Test" style="display: none;"><legend>neuer Test</legend>
						'.eintragung_test().'
					</fieldset>
					<fieldset id="fieldset_bestehender_Test"><legend>bestehender Test</legend>
						<input type="text" id="test_ids" name="test_ids" style="display: none;" readonly="readonly" size="5" />
						<input type="button" class="button" name="test_auswahl_1" value="Auswahl" onclick="IDs_eintragen(\'test\',\''.$pfad.'\',\'undefined\',\'test_ids\')" /><br />
						<span id="test_ids_inhalt"></span>
					</fieldset>
				</span>
				<span id="span_3" style="display:none;"><input type="checkbox" id="aufgabe_neu" name="aufgabe_neu" value="1" checked="checked" onchange="if (this.checked) {document.getElementById(\'fieldset_neue_aufgabe\').style.display = \'block\'; document.getElementById(\'fieldset_bestehende_aufgabe\').style.display = \'none\';} else {document.getElementById(\'fieldset_bestehende_aufgabe\').style.display = \'block\'; document.getElementById(\'fieldset_neue_aufgabe\').style.display = \'none\';};" /> <img src="'.$pfad.'icons/neu.png" alt="neu" title="neue Aufgabe" />
					<span id="fieldset_neue_aufgabe">
						<fieldset><legend>neue Aufgabe</legend>
						'.eintragung_aufgabe("neu", $pfad).'
						</fieldset>
					</span>
					<fieldset id="fieldset_bestehende_aufgabe" style="display: none;"><legend>bestehende Aufgabe</legend>
						<input type="text" id="aufgabe_ids" name="aufgabe_ids" style="display: none;" readonly="readonly" size="5" />
						<input type="button" class="button" name="aufgabe_auswahl_1" value="Auswahl" onclick="IDs_eintragen(\'aufgabe\',\''.$pfad.'\',\'undefined\',\'aufgabe_ids\')" /><br />
						<span id="aufgabe_ids_inhalt"></span>
					</fieldset><br />
				</span>
				<span id="span_6" style="display:none;">
					<input type="checkbox" id="material_neu" name="material_neu" value="1" onchange="if (this.checked) {document.getElementById(\'fieldset_neues_material\').style.display = \'block\'; document.getElementById(\'fieldset_bestehendes_material\').style.display = \'none\';} else {document.getElementById(\'fieldset_bestehendes_material\').style.display = \'block\'; document.getElementById(\'fieldset_neues_material\').style.display = \'none\';};" /> <img src="'.$pfad.'icons/neu.png" alt="neu" title="neues Material" />
					<fieldset id="fieldset_neues_material" style="display: none;"><legend>neues Material</legend>
						'.eintragung_sonstiges_material().'
					</fieldset>
					<fieldset id="fieldset_bestehendes_material"><legend>bestehendes Material</legend>
						<input type="text" id="material_ids" name="material_ids" style="display: none;" readonly="readonly" size="5" />
						<input type="button" class="button" name="material_auswahl_1" value="Auswahl" onclick="IDs_eintragen(\'material\',\''.$pfad.'\',\'undefined\',\'material_ids\')" /><br />
						<span id="material_ids_inhalt"></span>
					</fieldset>
				</span>
				<span id="span_7" style="display:none;"> <a href="'.$pfad.'formular/hilfe.php?inhalt=syntax" onclick="fenster(this.href, \'Hilfe zur Syntax\'); return false;" class="icon" title="Hilfe zur Syntax"><img src="'.$pfad.'icons/hilfe.png" alt="Hilfe" /></a>
                <br /><textarea name="sonstiges_inhalt" class="markItUp" rows="10" cols="50"></textarea></span>';
	return $inhalt;
}

function eintragung_block ($lb, $lehrplan, $klasse, $neue_pos, $block_1, $pfad) {
	echo '
		<div class="tooltip" id="tt_block_methodisch">
		Die Informationen zu den Verkn&uuml;pfungen zu anderen F&auml;chern/Lernbereichen und den methodisch-didaktischen Gedanken erscheinen in der Lernbereichs-&Uuml;bersicht.</div>
	<form action="'.$pfad.'formular/block_neu.php" method="post" accept-charset="ISO-8859-1">
        <fieldset><legend>';
		if ($block_1==0) echo 'Unterrichtseinheit'; else echo 'Block';
		echo ' hinzuf&uuml;gen</legend>';
		if ($block_1!=0) echo '<input type="hidden" name="block_1" value="'.$block_1.'" />';
		echo '
			<input type="hidden" name="lernbereich" value="'.$lb.'" />
			<input type="hidden" name="lehrplan" value="'.$lehrplan.'" />
			<input type="hidden" name="klasse" value="'.$klasse.'" />
			<input type="hidden" name="position" value="'.$neue_pos.'" />
			<ol class="divider">
			<li><label for="name">Name<em>*</em>:</label> <input type="text" name="name" size="40" maxlength="250" /></li>
			<li><label for="stunden">Stunden<em>*</em>:</label> <input type="text" name="stunden" size="2" maxlength="2" />
			<label for="puffer">+ Puffer:</label> <input type="text" name="puffer" size="2" maxlength="2" /></li>
			<li><label for="methodisch">methodisch-didaktische &Uuml;berlegungen:</label> <textarea cols="80" rows="3" name="methodisch"></textarea><br />
			<label for="verknuepfung_fach">Verkn&uuml;pfung mit Fach/LB:</label> <input type="text" name="verknuepfung_fach" size="45" maxlength="60" /> <img src="'.$pfad.'icons/information.png" class="hilfe_tooltip" alt="Hilfe" onmouseover="showWMTT(\'tt_block_methodisch\')" onmouseout="hideWMTT()" /></li>
			<li><label for="kommentare">Kommentare:</label> <textarea name="kommentare" cols="80" rows="5"></textarea></li>
			<li>'.themen_auswahl('../', 'thema', 0).'</li></ol>
			<input class="button" type="button" onclick="auswertung=new Array(new Array(0, \'name\',\'nicht_leer\'), new Array(0, \'thema_0\',\'nicht_leer\',\'-\'), new Array(0, \'stunden\',\'natuerliche_zahl\')); pruefe_formular(auswertung);" value="speichern" />
        </fieldset>
        </form>';
}

// beim Editieren von Abschnitts-Containern
function eintragung_abschnitt($id,$block,$lehrplan,$klasse,$position) {
	// nur noch fuer abschnitt-aenderungen da
	$db=new db;
	$pfad="./"; $db->pfad=$pfad;

    if ($id=="neu") $formular=$pfad."formular/abschnitt_neu.php?eintragen=true";
	else {
		$pfad="../"; $db->pfad="../";
		$abschnitt=$db->abschnitt($id);
		$formular=$pfad."formular/abschnitt_bearb.php?aendern=true";
	}

	$inhalt='
      <form name="abschnitt" action="'.$formular.'" method="post" accept-charset="ISO-8859-1">
         <input type="hidden" name="block" value="'.$block.'" />
         <input type="hidden" name="lehrplan" value="'.$lehrplan.'" />
         <input type="hidden" name="klasse" value="'.$klasse.'" />';
	if ($id!="neu") {
		$inhalt.='<input type="hidden" name="id" value="'.$id.'" />';
	}
	$inhalt.='<fieldset><legend>Abschnitt bearbeiten</legend>';
	if ($id!="neu")
		$inhalt.='<label for="block_neu">Blockzugeh&ouml;rigkeit:</label> '.$db->blockselect($block, 'block_neu', true);
	$inhalt.='<table class="einzelstunde"><tr><td><input type="hidden" name="position_1" value="'.($position).'" size="1" maxlength="2" />
        <label for="minuten_1" style="width: 65px;">Minuten<em>*</em>:</label><br /><input type="text" name="minuten_1" size="1" maxlength="3" value="'.$abschnitt['minuten'].'" />';
   $inhalt.='<label for="hefter_1" style="width: 70px;">Hefter<em>*</em>:</label><br /><input type="radio" name="hefter_1" value="0"'; if ($abschnitt['hefter']==0) $inhalt.=' checked="checked"'; $inhalt.=' /> -<br />
                                             <input type="radio" name="hefter_1" value="1"'; if ($abschnitt['hefter']==1 or $id=="neu") $inhalt.=' checked="checked"'; $inhalt.=' /> <img src="'.$pfad.'icons/merkteil.png" alt="Merkteil" title="Merkteil" /><br />
                                             <input type="radio" name="hefter_1" value="2"'; if ($abschnitt['hefter']==2) $inhalt.=' checked="checked"'; $inhalt.=' /> <img src="'.$pfad.'icons/uebungsteil.png" alt="&Uuml;bungsteil" title="&Uuml;bungsteil" /></td>
	    <td>';
		if ($id!="neu") {
			$ansicht=einzelstundenansicht($id,"nicht bearbeiten",$pfad);
		   $inhalt.='<p class="einzelstunde">'.$ansicht['inhalt'].'</p></td>';
		}
		else $inhalt.='
        <select name="inhaltstyp_1" onchange="inhaltstyp(this.value);">
                                        <option value="0">w&auml;hlen...</option>
	                                    <option value="1.4">Arbeitsblatt</option>
	                                    <option value="2.3">Beispielaufgabe</option>
	                                    <option value="8.7">Beschreibung</option>
	                                    <option value="4.7">Definition</option>
	                                    <option value="2.7">Diskussion</option>
	                                    <option value="1.7">Erl&auml;uterung</option>
	                                    <option value="2.4">Folie</option>
	                                    <option value="1.5">Grafik</option>
	                                    <option value="3.4">Link</option>
	                                    <option value="1.6">Material</option>
	                                    <option value="3.7">Merke</option>
	                                    <option value="6.7">Programmcode</option>
	                                    <option value="1.2">Test</option>
	                                    <option value="1.1">&Uuml;berschrift</option>
	                                    <option value="1.3">&Uuml;bungsaufgabe</option>
	                                    <option value="5.7">umrandet</option>
	                                    <option value="7.7">sonstiger Text</option></select>
	         <p id="inhaltstyp_mehr_1"></p></td>';
          $inhalt.='<td>
			<p><label for="methods_1">Methode:</label><br /><select id="method_1" name="method_1"><!-- class="jqui_selector"--><option value="">-</option>';
          include($pfad."basic/localisation/methods.php");
	      while( list($key, $val) = each ($methods) )
            {
                $inhalt.='<option value="'.$key.'" title="Anlass: '.html_umlaute($val['occasion']).' | Bedeutung: '.html_umlaute($val['intend']).' | Tipps/Probleme: '.html_umlaute($val['pointer']).'"'; if ($abschnitt['methode']==$key) $inhalt.=' selected="selected"'; $inhalt.='>'.html_umlaute($val['name']).'</option>';
            }
            
        $inhalt.='</select><br /><label for="medium_1">Medium:</label><br /> <select name="medium_1">'; $medium=db_conn_and_sql("SELECT * FROM `medium` ORDER BY `kuerzel`");
	      for ($j=0;$j<sql_num_rows($medium);$j++) {
			  $inhalt.='<option value="'.sql_result($medium,$j,"medium.id").'"'; if ($abschnitt['medium']==sql_result($medium,$j,"medium.id")) $inhalt.=' selected="selected"'; $inhalt.=' >'.html_umlaute(sql_result($medium,$j,"medium.kuerzel")).'</option>';
			}  
		$inhalt.='</select><br />
	        <label for="sozialform_1">Sozialform:</label><br /> <select name="sozialform_1">';
			$soz_form=db_conn_and_sql("SELECT * FROM `sozialform`");
	      for ($j=0;$j<sql_num_rows($soz_form);$j++) {
			$inhalt.='<option value="'.sql_result($soz_form,$j,"sozialform.id").'"'; if ($abschnitt['sozialform']==sql_result($soz_form,$j,"sozialform.id")) $inhalt.=' selected="selected"'; $inhalt.=' >'.html_umlaute(sql_result($soz_form,$j,"sozialform.kuerzel")).'</option>';
		  }  
		$inhalt.='</select><br />
			<label for="handlungsmuster_1">Handlungsmuster:</label><br /> <select name="handlungsmuster_1"><option value="">-</option>';
			$handlungsmuster=db_conn_and_sql("SELECT * FROM `handlungsmuster`");
	      for ($j=0;$j<sql_num_rows($handlungsmuster);$j++) {
			$inhalt.='<option value="'.sql_result($handlungsmuster,$j,"handlungsmuster.id").'"'; if ($abschnitt['handlungsmuster']==sql_result($handlungsmuster,$j,"handlungsmuster.id")) $inhalt.=' selected="selected"'; $inhalt.=' >'.html_umlaute(sql_result($handlungsmuster,$j,"handlungsmuster.name")).'</option>';
		  }  
			$inhalt.='</select></td>
	        <td><label for="ziel_1">Ziel:</label><br /><input type="text" name="ziel_1" maxlength="250" size="35" value="'.$abschnitt['ziel'].'" /><br />
			<label for="bemerkung_1">Kommentar:</label><br /><textarea name="bemerkung_1" cols="40" rows="5">'.$abschnitt['nachbereitung'].'</textarea></td></tr></table>
         <input type="button" class="button" value="eintragen" onclick="auswertung=new Array(new Array(0, \'minuten_1\',\'natuerliche_zahl\')); pruefe_formular(auswertung);" />
		</fieldset>
      </form>';
		return $inhalt;
}

function eintragung_fk_zp($plan_id) {
	$db=new db;
	$plan=$db->plan($plan_id);
	if ($_GET["alle_klassen"]=="true")
		$nur_eine_klassenstufe_anzeigen="";
	else $nur_eine_klassenstufe_anzeigen="AND `lernbereich`.`klassenstufe`=".sql_result($plan,0,"plan.schuljahr")."-`klasse`.`einschuljahr`+1";
	$result=db_conn_and_sql("SELECT *
		FROM `block`,`fach_klasse`,`lehrplan`,`lernbereich`,`klasse`,`plan`
		WHERE `plan`.`fach_klasse`=`fach_klasse`.`id`
			AND `fach_klasse`.`klasse`=`klasse`.`id`
			AND `fach_klasse`.`lehrplan`=`lehrplan`.`id`
			".$nur_eine_klassenstufe_anzeigen."
			AND `block`.`lernbereich`=`lernbereich`.`id`
			AND `lernbereich`.`lehrplan`=`lehrplan`.`id`
			AND `plan`.`id`=".$plan_id."
			AND `block`.`user`=".$_SESSION['user_id']."
		ORDER BY `lernbereich`.`klassenstufe`,`lernbereich`.`nummer`,`block`.`position`");
	$inhalt='<form action="./fk_zeitplan_aktion.php?aendern=true&amp;aktion=bearbeiten&amp;plan='.$plan_id.'&amp;fk='.sql_result($plan,0,"plan.fach_klasse").'" method="post" accept-charset="ISO-8859-1">';
	if (sql_result($plan,0,"plan.ausfallgrund")!="") $inhalt.='Ausfallgrund: <input type="text" name="ausfallgrund" value="'.html_umlaute(sql_result($plan,0,"plan.ausfallgrund")).'" />';
	else {
		/*$inhalt.='<label for="vorbereitet">vorbereitet:</label> <input type="checkbox" name="vorbereitet" '; if(sql_result($plan,0,"plan.vorbereitet")) $inhalt.='checked="checked" ';
		$inhalt.='/><br />
		<label for="nachbereitung">nachbereitet:</label> <input type="checkbox" name="nachbereitung" '; if(sql_result($plan,0,"plan.nachbereitung")) $inhalt.='checked="checked" ';
		$inhalt.='/><br />'; */
		$inhalt.='<label for="block1">Block 1<em>*</em>:</label> <select name="block1"><option value="'.sql_result($plan,0,"block_1").'">nicht &auml;ndern</option>';
		$lb=0; for ($i=0;$i<sql_num_rows($result);$i++) {
			if (sql_result($result,$i,"lernbereich.id")!=$lb) {
				if ($lb!=0) $inhalt.='</optgroup>';
				$lb=sql_result($result,$i,"lernbereich.id");
				$inhalt.='<optgroup label="'.sql_result($result,$i,"lernbereich.nummer").'. '.html_umlaute(sql_result($result,$i,"lernbereich.name"));
				if ($_GET["alle_klassen"]=="true") $inhalt.=' (Kl. '.sql_result($result,$i,"lernbereich.klassenstufe").')';
				$inhalt.='">'; }
		$inhalt.='<option value="'.sql_result($result,$i,"block.id").'"'; if (sql_result($result,$i,"block.id")==sql_result($plan,0,"block_1")) $inhalt.=' selected="selected"';
		$inhalt.=' >'.html_umlaute(sql_result($result,$i,"block.name")).'</option>'; }
		if ($i>1) $inhalt.='</optgroup>';
		$inhalt.='</select>';
		if ($_GET["alle_klassen"]!="true") $inhalt.='<a href="./fk_zeitplan_aktion.php?aktion=bearbeiten&amp;plan='.$plan_id.'&amp;alle_klassen=true">[alle Klassenstufen anzeigen]</a>';
		$inhalt.='<br />
		<label for="block2">Block 2:</label> <select name="block2"><option value="">-</option>';
		$lb=0; for ($i=0;$i<sql_num_rows($result);$i++) {
			if (sql_result($result,$i,"lernbereich.id")!=$lb) {
				if ($lb!=0) $inhalt.='</optgroup>';
				$lb=sql_result($result,$i,"lernbereich.id");
				$inhalt.='<optgroup label="'.sql_result($result,$i,"lernbereich.nummer").'. '.html_umlaute(sql_result($result,$i,"lernbereich.name")).'">'; }
		$inhalt.='<option value="'.sql_result($result,$i,"block.id").'"'; if (sql_result($result,$i,"block.id")==sql_result($plan,0,"block_2")) $inhalt.=' selected="selected"';
		$inhalt.=' >'.html_umlaute(sql_result($result,$i,"block.name")).'</option>'; }
		if ($i>1) $inhalt.='</optgroup>';
		$inhalt.='</select><br />
		<label for="alternativtitel">Alternativ-Titel:</label> <input type="text" name="alternativtitel" value="'.html_umlaute(sql_result($plan,0,"alternativtitel")).'" size="25" maxlength="150" /><br />
		<label for="notizen">Notizen:</label> <textarea name="notizen" cols="30" rows="5">'.html_umlaute(sql_result($plan,0,"notizen")).'</textarea>';
	}
	$inhalt.='<br />
	<input type="submit" class="button" value="eintragen" /></form>';
	return $inhalt;
}

// deprecated (nicht mehr in Verwendung)
function abschnitte_zeigen($block,$pfad) {
	$db = new db;
	$abschnitte=db_conn_and_sql("SELECT * FROM `abschnitt`,`block_abschnitt`,`block`
		WHERE `block_abschnitt`.`abschnitt`=`abschnitt`.`id`
			AND `block_abschnitt`.`block`=".$block."
			AND `block`.`id`=".$block."
			AND `block`.`user`=".$_SESSION['user_id']."
		ORDER BY `block_abschnitt`.`position`");
	if (sql_num_rows($abschnitte)>0) {
	$inhalt='kann ich das nicht RAUSNEHMEN?
	<table id="einzelstunde" class="einzelstunde" cellspacing="0" cellpadding="0">
   <tr><th>Zeit<br />(in min)</th><th title="Position wird automatisch festgelegt - nur bei Bedarf &auml;ndern (tritt nach Speicherung in Kraft)">Aktion</th><th>Inhalt</th><th title="wird der Schritt von den Sch&uuml;lern in den Hefter &uuml;bernommen?">Hefter</th><th>Medium /<br />Sozialform</th><th title="optionale Zielangabe des Abschnitts">Ziel / Bemerkung</th></tr>';
	for ($i=0;$i<sql_num_rows($abschnitte);$i++) { $ansicht=einzelstundenansicht(sql_result($abschnitte,$i,'abschnitt.id'),"nicht bearbeiten",$pfad);
       $abschnitt=$db->abschnitt(sql_result($abschnitte,$i,'abschnitt.id')); /*?>
	<tr><td><input type="hidden" name="abschnittsid_<?php echo $i; ?>" value="<?php echo sql_result($abschnitte,$i,'abschnitt.id'); ?>" />
	        <input type="checkbox" name="verwenden_<?php echo $i; ?>" title="verwenden?" />
	        <input type="text" name="pos_<?php echo $i; ?>" value="<?php echo $i+1; ?>" size="1" maxlength="2" title="spaeter mit Buttons und Javascript" /></td>
	    <td><input type="text" name="zeit_<?php echo $i; ?>" value="<?php echo $ansicht['minuten']; ?>" size="1" maxlength="2" /></td>
	    <td><?php echo $ansicht['inhalt']; ?></td>
	    <td><?php switch ($ansicht['hefter']) { case 0: echo "-"; break; case 1: echo "Merkteil"; break; case 2: echo "&Uuml;bungsteil"; break;} ?></td>
	    <td><?php echo $ansicht['medium']; ?> /<br /><?php echo $ansicht['sozialform']; ?></td>
	    <td><?php echo $ansicht['ziele']; ?></td>
	</tr><?php */
    // --------------------------------abschnitt inhalt kommt ja eh raus. macht naemlich bei Grafik Probleme (behoben?) -------------------------------------------
	$inhalt.='<tr>
	    <td><input type="text" name="zeit_'.sql_result($abschnitte,$i,'abschnitt.id').'" value="'.$ansicht['minuten'].'" size="1" maxlength="3" /></td>
	    <td><input type="button" value="eintragen" onclick="var inhalt = opener.document.getElementById(\'inhalt\'); var einfueg = opener.document.getElementById(\'abschnitt_einfueger\');
				wert=inhalt.getAttribute(\'value\')+'.sql_result($abschnitte,$i,'abschnitt.id').'+\':\'+document.getElementsByName(\'zeit_'.sql_result($abschnitte,$i,'abschnitt.id').'\')[0].value+\';\';
                inhalt.setAttribute(\'value\', wert);
                wert='.sql_result($abschnitte,$i,'abschnitt.id').'+\':\'+document.getElementsByName(\'zeit_'.sql_result($abschnitte,$i,'abschnitt.id').'\')[0].value+\';\';
                /*var Text = document.createTextNode(\''.$abschnitt["inhalt"].'\'); einfueg.appendChild(Text);
                var br = document.createElementNS(\'http://www.w3.org/1999/xhtml\',\'br\'); einfueg.appendChild(br);*/" /></td>
        <td>'.str_replace("'","\'",$ansicht['inhalt']).'</td>
	    <td align="center">';
		switch ($ansicht['hefter']) { case 0: $inhalt.="-"; break; case 1: $inhalt.='<img src="'.$pfad.'icons/merkteil.png" alt="Merkteil" title="Merkteil" />'; break; case 2: $inhalt.='<img src="'.$pfad.'icons/uebungsteil.png" alt="&Uuml;bungsteil" title="&Uuml;bungsteil" />'; break; }
		$inhalt.='</td>
	    <td>'.$ansicht['medium'].' /<br />'.$ansicht['sozialform'].'</td>
	    <td>'.$ansicht['ziele'].'</td>
	</tr>';
	}
	return $inhalt.'</table>';
	}
	else return "keine Abschnitte in diesem Block vorhanden";
}

function eintragung_plan($id) {
	$db =new db;
	$aktuelles_jahr=$db->aktuelles_jahr();
	$subject_classes = new subject_classes($aktuelles_jahr);
	$pfad="./";
	if ($id=="neu") {
		// deleted
		echo "gibts nicht mehr";
	}
	else {
		$formular=$pfad."formular/plan_bearb.php";
		$abschnitte=db_conn_and_sql("SELECT * FROM `abschnittsplanung` WHERE `abschnittsplanung`.`plan`=".$id." ORDER BY `abschnittsplanung`.`position`");
		$plan=db_conn_and_sql("SELECT * FROM `plan` WHERE `id`=".$id);
		$plan=sql_fetch_assoc($plan);
		$hilf=explode(":",$plan["startzeit"]);
		$startzeit[0]=datum_strich_zu_punkt($plan["datum"]);
		$startzeit[1]=$hilf[0].":".$hilf[1];
		$fach_klasse=$plan["fach_klasse"];
		$zusatzziele=html_umlaute($plan["zusatzziele"]);
		$bemerkung=html_umlaute($plan["bemerkung"]);
		$struktur=html_umlaute($plan["struktur"]);
		// fach_klasse_infos ein paar Zeilen weiter unten
		$block1=$plan["block_1"];
		$block2=$plan["block_2"];
	}
	  
	$inhalt='<form action="'.$formular.'" method="post" accept-charset="ISO-8859-1">';
   $inhalt.='
            <div id="pictureframe" style="display: none; width: 800px; heigth: 600px;"><iframe name="pictureframe" width="790" height="580" src="'.$pfad.'lessons/picturelib.php"></iframe></div>
            <div id="fileframe" style="display: none; width: 800px; heigth: 600px;"><iframe name="fileframe" width="790" height="580" src="'.$pfad.'lessons/filelib.php"></iframe></div>';
	if ($id!="neu") $inhalt.='<input type="hidden" name="plan_id" value="'.$id.'" />';
	if ($plan["notizen"]!="") { $clip_allg_inf='open'; $span_display=''; }
	else {$clip_allg_inf='closed'; $span_display=' style="display:none;"';}
	$inhalt.='<input type="hidden" name="schuljahr" value="'.$aktuelles_jahr.'" />
		<fieldset><legend>Allgemeine Informationen <img id="img_allgemeine_infos" src="./icons/clip_'.$clip_allg_inf.'.png" alt="clip" onclick="javascript:clip(\'allgemeine_infos\', \''.$pfad.'\')" /></legend>
			<span id="span_allgemeine_infos"'.$span_display.'>
      <img src="'.$pfad.'/icons/fach_klasse.png" alt="fach_klasse" title="Fach-Klasse" />: '.$subject_classes->nach_ids[$fach_klasse]["farbanzeige"].' <input name="fach_klasse" type="hidden" value="'.$fach_klasse.'" />';
      $inhalt.='
      <label for="stunden" style="width: 35px;">Ustd:</label> <select name="stunden" id="stunden" onchange="getElementById(\'ohne_pause_button\').style.visibility=this.value==1?\'hidden\':\'visible\'; zeit_aktualisieren();">';
	  for ($ustd=1;$ustd<10;$ustd++) {
			if ($ustd==$plan["ustd"])
				$inhalt.='<option value="'.$ustd.'" selected="selected">';
			else
				$inhalt.='<option value="'.$ustd.'">';
		$inhalt.=$ustd.'</option>';
	  }
      $inhalt.='</select>&nbsp;
        <input type="checkbox" id="ohne_pause" name="ohne_pause_checkbox" value="1" onchange="if(this.checked==true) checkboxicon=\''.$pfad.'icons/ohne_pause.png\'; else checkboxicon=\''.$pfad.'icons/einzelstunde.png\'; document.getElementById(\'ohne_pause_button_icon\').src=checkboxicon;"';
      if ($plan["ohne_pause"]==0)
			$inhalt.=' checked="checked"';
      $inhalt.=' /><label for="ohne_pause" id="ohne_pause_button" style="width: 100px;';
      if ($plan["ustd"]==1)
			$inhalt.=' visibility: hidden;';
      $inhalt.='" title="Ustd. mit (aktiviert - Standard) oder ohne Pause (deaktiviert) durchf&uuml;hren"><img id="ohne_pause_button_icon" src="';
      if ($plan["ohne_pause"]==0)
		    $inhalt.=$pfad.'icons/ohne_pause.png';
	  else
		    $inhalt.=$pfad.'icons/einzelstunde.png';
      $inhalt.='" alt="pause" /> Pause</label> &nbsp;
		<label for="datum" style="width: 30px;"><img src="'.$pfad.'/icons/kalender.png" alt="kalender" title="Datum" /><em>*</em>:</label> <input type="text" class="datepicker" name="datum" size="7" maxlength="10" value="'.$startzeit[0].'" />
		<label for="zeit" style="width: 30px;"><img src="'.$pfad.'/icons/uhr.png" alt="uhr" title="Beginn" /><em>*</em>:</label> <input type="time" name="zeit" size="3" maxlength="5" value="'.$startzeit[1].'" /> Uhr';
		$inhalt.='<div style="float: right;">';
		// ------ davor und danach Uebersicht ------
		$eintraege=fachklassen_zeitplanung($fach_klasse,$aktuelles_jahr);
		$anzahl_plaene_davor=3;
		$anzahl_plaene_danach=2;
		$planuebersicht_ids='';
		if (count($eintraege)>1) {
			$jetzt_plan=0;
			while ($jetzt_plan<count($eintraege) and $eintraege[$jetzt_plan]["plan_id"]!=$_GET["plan"]) $jetzt_plan++;
			if (@$eintraege[$jetzt_plan]["plan_id"]==$_GET["plan"]) {
				$i=$jetzt_plan-1;
				while ($i>=0 and $i>=($jetzt_plan-$anzahl_plaene_davor)) {
					if (($eintraege[$i]["typ"]=="eingetragen" or $eintraege[$i]["typ"]=="zusatz")) $planuebersicht_ids[]=$i;
					else $anzahl_plaene_davor++;
					$i--;
				}
				$planuebersicht_ids[]=$jetzt_plan;
				$i=$jetzt_plan+1;
				while ($i+1<count($eintraege) and $i<=($jetzt_plan+$anzahl_plaene_danach)) {
					if (($eintraege[$i]["typ"]=="eingetragen" or $eintraege[$i]["typ"]=="zusatz")) $planuebersicht_ids[]=$i;
					else $anzahl_plaene_danach++;
					$i++;
				}
				$wochennamen_kurz=array(0=>'So', 1=>'Mo', 2=>'Di', 3=>'Mi', 4=>'Do', 5=>'Fr', 6=>'Sa');
				sort($planuebersicht_ids);
				for($i=0;$i<count($planuebersicht_ids);$i++) {
					if ($planuebersicht_ids[$i]!=$jetzt_plan) $inhalt.='<span style="color: gray;">';
                    else $i_gemerkt=$i;
					$inhalt.=$wochennamen_kurz[date("w",$eintraege[$planuebersicht_ids[$i]]["datum"])].' '.date("d.m.",$eintraege[$planuebersicht_ids[$i]]["datum"]).': '.$eintraege[$planuebersicht_ids[$i]]["alternativtitel"].' '.$eintraege[$planuebersicht_ids[$i]]["block_1"].' '.$eintraege[$current_pos]["block_2"];
					if ($planuebersicht_ids[$i]!=$jetzt_plan) $inhalt.='</span>';
					if ($planuebersicht_ids[$i+1]===$jetzt_plan) $inhalt.=' <a href="'.$pfad.'einzelstunde.php?plan_0='.$eintraege[$planuebersicht_ids[$i]]["plan_id"].'" onclick="fenster(this.href,\'letzte Stunde\'); return false;">[ansehen]</a><br />';
					else $inhalt.='<br />';
				}
			}
		}
        // ---- zum Block gehen ----
        $fk_query=db_conn_and_sql("SELECT * FROM fach_klasse, klasse WHERE fach_klasse.klasse=klasse.id AND fach_klasse.id=".$fach_klasse." AND fach_klasse.user=".$_SESSION['user_id']);
        $fk_query=sql_fetch_assoc($fk_query);
        $inhalt.='<br /><a href="'.$pfad.'index.php?tab=stundenplanung&amp;auswahl=lernbereiche&amp;lehrplan='.$fk_query["lehrplan"].'&amp;klasse='.($aktuelles_jahr-$fk_query["einschuljahr"]+1).'&amp;block='.$block1.'&amp;eintragen=abschnitte" title="ausgew&auml;hlten Block um Fundus anzeigen"><img src="'.$pfad.'icons/fundus.png" alt="fundus" /> Block: '.$eintraege[$planuebersicht_ids[$i_gemerkt]]["block_1"].'</a>';
		
        $inhalt.='</div>';
		$inhalt.='<br />
		<label for="notizen" style="width: 25px;"><img src="./icons/note.png" alt="Notizen" title="Notizen" />:</label> <textarea rows="5" cols="70" name="notizen">'.html_umlaute($plan["notizen"]).'</textarea><br />
		</span>
		</fieldset>
		<div class="tooltip" id="tt_abschnitte">
			Um eine Unterrichtsstunde mit Abschnitten zu f&uuml;llen, k&ouml;nnen Sie auf bereits im Fundus vorhandene Abschnitte zur&uuml;ckgreifen oder erstellen einen neuen Abschnitt, der gleichzeitig im Fundus landet.</div>
		<div class="tooltip" id="tt_struktur">
			Diese Strukturinformationen (Ablauf der Stunde) erscheinen im Kopf der Druckansicht dieser Unterrichtsstunde.</div>
		<div class="tooltip" id="tt_schuelergruppe">
			Der hier dargestellte Text entspricht den Informationen der Fach-Klassen-Kombination und wird bei Ver&auml;nderung &uuml;berschrieben.</div>

		<br />
		<input type="hidden" name="block1" value="'.$block1.'" />
		<input type="hidden" name="block2" value="'.$block2.'" />
	<table id="einzelstunde" class="einzelstunde" cellspacing="0" cellpadding="0">
		<tr><th style="width:60px;">Pos.</th><th style="width:60px;"><img src="'.$pfad.'/icons/zeit.png" alt="zeit" title="Zeit (in min)" /></th><th>Inhalt</th><th title="wird der Schritt von den Sch&uuml;lern in den Hefter &uuml;bernommen?">Hefter</th><th>Medium /<br />Sozialform /<br />Handlungsmuster</th><th title="optionale Zielangabe des Abschnitts">Ziel / Bemerkung / Phase</th></tr>';
	$bisherige_gesamtzeit=0;
	if ($id!="neu") {
		$i=0;
	    while ($abschnitt = sql_fetch_assoc($abschnitte)) {
            // bearbeiten nur bei nicht-einmal-Abschnitten
			if ($abschnitt["abschnitt"]!=0)
                $ansicht=einzelstundenansicht($abschnitt["abschnitt"],"bearbeiten",$pfad);
			if ($abschnitt["minuten"]>0)
                $ansicht['minuten']=$abschnitt["minuten"];
			$bisherige_gesamtzeit+=$ansicht['minuten'];
	        $inhalt.='<tr><td align="center">
                <input type="hidden" name="abschnittsid_'.$i.'" value="'.$abschnitt["abschnitt"].'" />
                <input type="hidden" name="abschnittsposition_'.$i.'" value="'.$abschnitt["position"].'" />';
			if ($i>0)
                $inhalt.='<a href="'.$pfad.'formular/plan_position.php?id='.$abschnitt["position"].'&amp;plan='.$id.'&amp;aktion=hoch&amp;fach_klasse='.$_GET["fk"].'" class="icon" title="hochschieben"><img src="'.$pfad.'icons/hoch.png" alt="hoch" /></a><br />';
            $inhalt.=($abschnitt["position"]+1);
            if ($i!=sql_num_rows($abschnitte)-1)
                $inhalt.='<br /><a href="'.$pfad.'formular/plan_position.php?id='.$abschnitt["position"].'&amp;plan='.$id.'&amp;aktion=runter&amp;fach_klasse='.$_GET["fk"].'" class="icon" title="runterschieben"><img src="'.$pfad.'icons/runter.png" alt="runter" /></a>';
            $inhalt.='</td>
	        <td align="center"><input type="text" id="zeit_'.$i.'" name="zeit_'.$i.'" value="'.$ansicht['minuten'].'" size="1" maxlength="3" onchange="zeit_aktualisieren();" /><br />
                <a href="'.$pfad.'formular/plan_position.php?abschnitt='.$abschnitt["abschnitt"].'&amp;plan='.$id.'&amp;position='.$abschnitt["position"].'&amp;aktion=loeschen&amp;fach_klasse='.$_GET["fk"].'" class="icon" title="entfernen"><img src="'.$pfad.'icons/entfernen.png" alt="entfernen" /></a></td>
			<td>';
            // Einmalabschnitte mit Textarea, sonst Inhalt anzeigen und "Inhalt hinzufuegen" ermoeglichen
            if ($abschnitt["abschnitt"]==0)
                $inhalt.='<textarea class="markItUp" name="einmalabschnitt_'.$i.'" cols="45" rows="3">'.html_umlaute($abschnitt["inhalt"]).'</textarea>';
            else
				$inhalt.=$ansicht['inhalt'].'<a href="javascript:fenster(\'./formular/inhalt_hinzufuegen.php?abschnitt='.$abschnitt["abschnitt"].'\');" class="icon" title="weiteren Inhalt hinzuf&uuml;gen"><img src="./icons/add.png" alt="add" /></a> ';
            $inhalt.='<img src="'.$pfad.'icons/kommentar.png" alt="kommentar" title="Bemerkung zum Abschnitt (nur f&uuml;r diese Unterrichtsstunde) verfassen" onclick="getElementById(\'kommentar_'.$i.'\').style.display=\'inline\';" />
				<span id="kommentar_'.$i.'"'; if ($abschnitt["bemerkung"]=="") $inhalt.=' style="display: none;"'; $inhalt.='>: <input type="text" name="plan_bemerkung_'.$i.'" size="40" maxlength="250" value="'.html_umlaute($abschnitt["bemerkung"]).'" /></span>
				</td>
	    	<td align="center">';
            if ($abschnitt["abschnitt"]!=0)
                switch ($ansicht['hefter']) { case 0: $inhalt.="-"; break; case 1: $inhalt.='<img src="'.$pfad.'icons/merkteil.png" alt="Merkteil" title="Merkteil" />'; break; case 2: $inhalt.='<img src="'.$pfad.'icons/uebungsteil.png" alt="&Uuml;bungsteil" title="&Uuml;bungsteil" />'; break;}
            $inhalt.='</td>
	    	<td align="center">';
            if ($abschnitt["abschnitt"]!=0) {
                $inhalt.=$ansicht['medium'].' /<br />'.$ansicht['sozialform'];
                if (isset($ansicht['handlungsmuster']))
                    $inhalt.=' /<br />'.$ansicht['handlungsmuster'];
            }
            $inhalt.='</td>
	    	<td align="center">';
            if ($abschnitt["abschnitt"]!=0) {
                $inhalt.=$ansicht['ziele'].' / '.$ansicht['bemerkung'].' / <select name="phase_'.$i.'"><option value="">-</option>';
                $phasen=db_conn_and_sql("SELECT * FROM `phase`");
                while ($phase = sql_fetch_assoc($phasen)) {
                    $inhalt.='<option value="'.$phase["id"].'" ';
                    if ($abschnitt["phase"]==$phase["id"])
						$inhalt.='selected="selected" ';
                    $inhalt.='>'.html_umlaute($phase["kuerzel"]).'</option>';
                }
                $inhalt.='</select> <!-- Doppelt - index.php -->
                    <a href="'.$pfad.'formular/abschnitt_bearb.php?welcher='.$abschnitt["abschnitt"].'" class="icon" onclick="fenster(this.href, \'Abschnittscontainer bearbeiten\'); return false;" title="bearbeiten"><img src="'.$pfad.'icons/edit.png" alt="bearbeiten" /></a>
                    <a href="'.$pfad.'formular/abschnitt_position.php?abschnitt='.$abschnitt["abschnitt"].'&amp;block='.$block1.'&amp;fk='.$fach_klasse.'&amp;plan='.$id.'&amp;aktion=loeschen" class="icon" title="Abschnitt l&ouml;schen" onclick="if (confirm(\'Der Abschnitt wird endg&uuml;ltig (zusammen mit allen ihm zugeordneten Daten) gel&ouml;scht. Wollen Sie das wirklich?\')==false) return false;"><img src="'.$pfad.'icons/delete.png" alt="l&ouml;schen" /></a>';
            }
			$inhalt.='</td>
			</tr>';
			$i++;
		}
	}
	
	$inhalt.='<tr><td colspan="6"><!--<input type="text" id="inhalt" name="inhalt" size="80" readonly="readonly" />-->
    <script type="text/javascript">
	$(function() {
        var visible_new_section = $(\'#new_section_tr\');
        var visible_new_plan_sec = $(\'#new_plan_sec_tr\');
		
		var ohne_pause_button=$(\'#ohne_pause\').button();
		
        var new_sec=$(\'#new_section\').button();
        var new_plan_sec=$(\'#new_plan_sec\').button();
        new_sec.click(function(event) {
            if (this.checked) {
                if (document.getElementById(\'new_plan_sec\').checked) {
                    document.getElementById(\'new_plan_sec\').checked=false;
                    new_plan_sec.button("refresh");
                    visible_new_plan_sec.hide("fade", 100);
                }
                visible_new_section.show("fade", 500);
            }
            else
                visible_new_section.hide("fade", 500);
        });
        new_plan_sec.click(function(event) {
            if (this.checked) {
                if (document.getElementById(\'new_section\').checked) {
                    document.getElementById(\'new_section\').checked=false;
                    new_sec.button("refresh");
                    visible_new_section.hide("fade", 100);
                }
                visible_new_plan_sec.show("fade", 500);
            }
            else
                visible_new_plan_sec.hide("fade", 500);
        });
        
        
        $( "#new_section_add_content_span" ).buttonset();
	});
	</script>
    
    <!-- Tooltipps fuer neuen Abschnitt -->
	<div class="tooltip" id="tt_inhaltstypen">
		<p>Der Inhalt eines Abschnittscontainers wird hier gew&auml;hlt. Jegliche Materialien werden automatisch in die Materialdatenbank eingeordnet und bleiben auch dann erhalten, wenn der Abschnittscontainer gel&ouml;scht wird.
		Dies betrifft alle Aufgaben, Tests, Grafiken, Arbeitsbl&auml;tter, Folien, Dateien und sonstige Materialien.</p>
		<p>Mit dem Symbol <img src="'.$pfad.'icons/add.png" alt="add" /> k&ouml;nnen Sie dem Abschnitt mehrere Inhalte zuordnen. So k&ouml;nnen (damit die logische Abgrenzung zum n&auml;chsten Abschnitt gew&auml;hrleistet ist) hier z.B. gleichzeitig eine &Uuml;berschrift, ein Merksatz und zwei Grafiken vorkommen.</p></div>
	<div class="tooltip" id="tt_block">
		<p>Um Abschnitte aus dem Fundus sp&auml;ter besser wiederzufinden, ordnen Sie diesen Abschnittscontainer einer Unterrichtseinheit/einem Block zu. Der Abschnitt erscheint dann im Fundus im entsprechenden Block an letzter Stelle. Sollte die Reihenfolge unvern&uuml;nftig sein, k&ouml;nnen Sie diese nach der Eintragung im Fundus korrigieren.</p></div>
	<div class="tooltip" id="tt_zeit">
		<p>Die Zeit eines Abschnitts (in Minuten) sollte f&uuml;r eine durchschnittliche Klasse eingetragen werden, damit Sie einen Richtwert haben.</p>
		<p>Wenn Sie den Abschnitt gleichzeitig einer Unterrichtsstunde zuordnen, und mehr Zeit einplanen wollen, als bei einer Durchschnittsklasse,
		passen Sie die Zeitangabe an, nachdem der Abschnitt eingetragen ist. In der Unterrichts-Nachbereitung werden &uuml;brigens beide Zeitangaben zur eventuellen Korrektur angeboten.</p></div>
	<div class="tooltip" id="tt_inhalt">
		<p>Der Inhalt eines Abschnittskontainers wird diesem hier zugeordnet. Jegliche Materialien werden automatisch in die Materialdatenbank eingeordnet und bleiben auch dann erhalten, wenn der Abschnittskontainer gel&ouml;scht wird.
		Dies betrifft alle Aufgaben, Tests, Grafiken, Arbeitsbl&auml;tter, Folien, Links und sonstige Materialien.</p>
		<p>Mit dem Symbol <img src="'.$pfad.'icons/add.png" alt="add" /> k&ouml;nnen Sie dem Abschnitt mehrere Inhalte zuordnen. So k&ouml;nnen (damit die logische Abgrenzung zum n&auml;chsten Abschnitt gew&auml;hrleistet ist) hier z.B. gleichzeitig eine &Uuml;beschrift, ein Merksatz und zwei Grafiken vorkommen.</p></div>
	<div class="tooltip" id="tt_hefter">
		W&auml;hlen Sie zwischen "nicht aufschreiben", "Merkhefter" und "&Uuml;bungshefter". Je nach Auswahl sieht der Sch&uuml;lerhefter am Ende unterschiedlich aus.
		Wenn Sie nicht verschiedene Hefter nutzen (dies kann in den Einstellungen ver&auml;ndert werden), ist die Auswahl "Merk-" oder "&Uuml;bungshefter" irrelevant.</div>
	<div class="tooltip" id="tt_medium">
		Diese Auswahlen haben auf den Unterricht keinen weiteren Einfluss. Sie haben lediglich informationscharakter und k&ouml;nnen eventuell in sp&auml;teren Versionen statistisch ausgewertet werden.</div>
	<div class="tooltip" id="tt_kommentar">
		<p>Ein Kommentar ist bei der Abschnittsauswahl lesbar. Auch Nachbereitungskommentare werden in dieses Feld geschrieben.</p></div>

        <div id="toolbar" class="ui-widget-header ui-corner-all ui-helper-clearfix">
            <span id="new_section_toolbar_span">
                <button type="button" onclick="window.open(\''.$pfad.'abschnittsplanung.php?block='.(0+$block1).'&amp;refresh=0&amp;plan='.$_GET["plan"].'\', \'ID-Uebersicht\', \'width=700,height=600,left=100,top=200,resizable=yes,scrollbars=yes\');" title="Abschnitte aus Fundus &uuml;bernehmen"><img src="'.$pfad.'icons/fundus.png" alt="fundus" /> vorhandene Abschnitte eintragen</button>
                <input type="checkbox" id="new_section" name="new_section_checkbox" /><label for="new_section" style="width: 200px;"><img src="'.$pfad.'icons/abschnitt.png" alt="abschnitt" /> neuer Abschnitt</label>
                <input type="checkbox" id="new_plan_sec" name="new_plan_sec_checkbox" /><label for="new_plan_sec" style="width: 250px;"><img src="'.$pfad.'icons/abschnitt_one_way.png" alt="abschnitt_one_way" /> neuer Einmal-Abschnitt</label>
                <img src="'.$pfad.'icons/information.png" class="hilfe_tooltip" alt="Hilfe" onmouseover="showWMTT(\'tt_abschnitte\')" onmouseout="hideWMTT()" />
                <!--<button type="button" onclick="window.open(\''.$pfad.'formular/abschnitt_neu.php?block='.(0+$block1).'&amp;plan='.$_GET["plan"].'\', \'Neuer Abschnitt\', \'width=1100,height=600,left=50,top=50,resizable=yes,scrollbars=yes\');" title="neuen Abschnitt erstellen"><img src="'.$pfad.'icons/abschnitt.png" alt="abschnitt" /> neuer Abschnitt</button>-->
            </span>
        </div>
	</td></tr>'; // Block-Auswahl, klasse, lehrplan, position berechnen; exit-ziel und Achtung! bei speichern'.eintragung_abschnitt("neu",$_GET['blocknummer'],$_GET['lehrplan'],$_GET['klasse'],1000).'
	
	$inhalt.='<tr id="new_section_tr" style="display: none"><td align="center">'.($i+1).'</td>
	    <td align="center">
            <label for="minuten_1" style="width: 65px;">Minuten<em>*</em>:</label><br /><input type="text" name="minuten_1" size="1" maxlength="3" value="'.$abschnitt['minuten'].'" /> <img src="'.$pfad.'icons/information.png" class="hilfe_tooltip" alt="Hilfe" onmouseover="showWMTT(\'tt_zeit\')" onmouseout="hideWMTT()" />
        </td>
	    <td style="min-width: 600px;">
            <p>Geh&ouml;rt zu Block: '.$db->blockselect($block1, 'block').'
                    <img src="'.$pfad.'icons/information.png" class="hilfe_tooltip" alt="Hilfe" onmouseover="showWMTT(\'tt_block\')" onmouseout="hideWMTT()" /></p>';
                //$lehrplan=sql_result($bloecke,$hilf,"lehrplan.id");
                //$position=sql_result($bloecke,$hilf,"anzahl")+1;
                // <input type="hidden" name="lehrplan" value="'.$lehrplan.'" />
                $inhalt.='
            <script>
                function show_content(type) {
                    //var einzeltyp = Math.floor(this.value);
                    var abschnitt_typ = 7;
                    switch (type) {
                        case \'header\': abschnitt_typ = 1; break;
                        case \'test\': abschnitt_typ = 2; break;
                        case \'task\': abschnitt_typ = 3; break;
                        case \'material\': abschnitt_typ = 6; break;
                    }
                    document.getElementById(\'span_1\').style.display = \'none\';
                    document.getElementById(\'span_2\').style.display = \'none\';
                    document.getElementById(\'span_3\').style.display = \'none\';
                    document.getElementById(\'span_6\').style.display = \'none\';
                    document.getElementById(\'span_7\').style.display = \'none\';
                    document.getElementById(\'span_\' + abschnitt_typ).style.display = \'inline\';
                    
                    switch (abschnitt_typ) {
                        case 1: // Ueberschrift
                            document.getElementById(\'hefter_1_1\').checked=true;
                            document.getElementById(\'medium_1\').options[7].selected=true;
                            document.getElementById(\'sozialform_1\').options[0].selected=true;
                            break;
                        case 2: // Test
                            document.getElementById(\'hefter_1_0\').checked=true;
                            document.getElementById(\'medium_1\').options[1].selected=true;
                            document.getElementById(\'sozialform_1\').options[1].selected=true;
                            break;
                        case 3: // Aufgabe
                            document.getElementById(\'hefter_1_2\').checked=true;
                            document.getElementById(\'medium_1\').options[3].selected=true;
                            document.getElementById(\'sozialform_1\').options[1].selected=true;
                            break;
                        case 6: // Material
                            document.getElementById(\'sozialform_1\').options[0].selected=true;
                            document.getElementById(\'hefter_1_0\').checked=true;
                            document.getElementById(\'medium_1\').options[5].selected=true;
                            break;
                        case 7: // Text
                            document.getElementById(\'hefter_1_1\').checked=true;
                            document.getElementById(\'medium_1\').options[7].selected=true;
                            document.getElementById(\'sozialform_1\').options[0].selected=true;
                            break;
                    }
                }
            </script>
            
            <div id="new_section_add_content_span" style="padding-bottom: 10px;">
                <input type="radio" name="add_content_ns" value="7" onclick="show_content(\'text\');" id="add_content_text" checked="checked" />
                    <label for="add_content_text" title="Text / Bilder / Dateien" style="width: 90px;"> <img src="'.$pfad.'icons/arbeitsblatt.png" alt="Arbeitsblatt" /> Text</label>
                <input type="radio" name="add_content_ns" value="3" onclick="show_content(\'task\');" id="add_content_task" />
                    <label for="add_content_task" style="width: 120px;"> <img src="'.$pfad.'icons/aufgaben.png" alt="Test" /> Aufgabe</label>
                <input type="radio" name="add_content_ns" value="1" onclick="show_content(\'header\');" id="add_content_header" />
                    <label for="add_content_header" style="width: 140px;"> <img src="'.$pfad.'icons/headline.png" alt="pic" /> &Uuml;berschrift</label>
                <input type="radio" name="add_content_ns" value="2" onclick="show_content(\'test\');" id="add_content_test" />
                    <label for="add_content_test" style="width: 90px;" title="Klassenarbeiten, Leistungskontrollen..."> <img src="'.$pfad.'icons/test.png" alt="Test" /> Test</label>
                <input type="radio" name="add_content_ns" value="6" onclick="show_content(\'material\');" id="add_content_material" />
                    <label for="add_content_material" style="width: 120px;"><img src="'.$pfad.'icons/sonstiges_material.png" alt="sontiges Material" /> Material</label>
				<img src="'.$pfad.'icons/information.png" class="hilfe_tooltip" alt="Hilfe" onmouseover="showWMTT(\'tt_inhaltstypen\')" onmouseout="hideWMTT()" />
            </div>
            ';
            
            // aus funktion eintragung_inhaltstypen entnommen:
            $inhalt.='
				<span id="span_1" style="display:none;"><br />
					<fieldset>
					<label for="ueberschrift_ebene_1">Ebene:</label> <select name="ueberschrift_ebene_1">';
						for ($h=1;$h<=5;$h++) {
							$inhalt.='<option value="'.$h.'">'; for ($j=0; $j<$h; $j++) $inhalt.='1.'; $inhalt.='</option>';
						}
					$inhalt.='
					</select>
					<input type="text" name="ueberschrift_text_1" maxlength="150" /><br />
					<label for="ueberschrift_typ_1" style="width: 200px;">Unter&uuml;berschriften-Typ<em>*</em>:</label>
						<select name="ueberschrift_typ_1">'; $typ=array(array("wert"=>"1","beschreibung"=>"1 (Zahlen)"),array("wert"=>"a","beschreibung"=>"a (Kleinbuchstaben)"),array("wert"=>"A","beschreibung"=>"A (Gro&szlig;buchstaben)"),array("wert"=>"I","beschreibung"=>"I (r&ouml;mische Zahlen)"),array("wert"=>"-","beschreibung"=>"nichts"));
						foreach($typ as $value) $inhalt.='<option value="'.$value["wert"].'">'.$value["beschreibung"].'</option>';
						$inhalt.='</select></fieldset></span>
				<span id="span_2" style="display:none;">
					<input type="checkbox" id="test_neu" name="test_neu" value="1" onchange="if (this.checked) {document.getElementById(\'fieldset_neuer_Test\').style.display = \'block\'; document.getElementById(\'fieldset_bestehender_Test\').style.display = \'none\';} else {document.getElementById(\'fieldset_bestehender_Test\').style.display = \'block\'; document.getElementById(\'fieldset_neuer_Test\').style.display = \'none\';};" /> <img src="'.$pfad.'icons/neu.png" alt="neu" title="neuer Test" />
					<fieldset id="fieldset_neuer_Test" style="display: none;"><legend>neuer Test</legend>
						'.eintragung_test().'
					</fieldset>
					<fieldset id="fieldset_bestehender_Test"><legend>bestehender Test</legend>
						<input type="text" id="test_ids" name="test_ids" style="display: none;" readonly="readonly" size="5" />
						<input type="button" class="button" name="test_auswahl_1" value="Auswahl" onclick="IDs_eintragen(\'test\',\''.$pfad.'\',\'undefined\',\'test_ids\')" /><br />
						<span id="test_ids_inhalt"></span>
					</fieldset>
				</span>
				<span id="span_3" style="display:none;"><label for="aufgabe_neu" style="width: 50px;"><img src="'.$pfad.'icons/neu.png" alt="neu" title="neue Aufgabe" style="float: left;" /></label> <input type="checkbox" id="aufgabe_neu" class="toggle" name="aufgabe_neu" value="1" checked="checked" onchange="if (this.checked) {document.getElementById(\'fieldset_neue_aufgabe\').style.display = \'block\'; document.getElementById(\'fieldset_bestehende_aufgabe\').style.display = \'none\';} else {document.getElementById(\'fieldset_bestehende_aufgabe\').style.display = \'block\'; document.getElementById(\'fieldset_neue_aufgabe\').style.display = \'none\';};" />
					<span id="fieldset_neue_aufgabe">
						<fieldset><legend>neue Aufgabe</legend>
						'.eintragung_aufgabe("neu",$pfad).'
						</fieldset>
					</span>
					<fieldset id="fieldset_bestehende_aufgabe" style="display: none;"><legend>bestehende Aufgabe</legend>
						<input type="text" id="aufgabe_ids" name="aufgabe_ids" style="display: none;" readonly="readonly" size="5" />
						<input type="button" class="button" name="aufgabe_auswahl_1" value="Auswahl" onclick="IDs_eintragen(\'aufgabe\',\''.$pfad.'\',\'undefined\',\'aufgabe_ids\')" /><br />
						<span id="aufgabe_ids_inhalt"></span>
					</fieldset><br />
				</span>
				<span id="span_6" style="display:none;">
					<input type="checkbox" id="material_neu" name="material_neu" value="1" onchange="if (this.checked) {document.getElementById(\'fieldset_neues_material\').style.display = \'block\'; document.getElementById(\'fieldset_bestehendes_material\').style.display = \'none\';} else {document.getElementById(\'fieldset_bestehendes_material\').style.display = \'block\'; document.getElementById(\'fieldset_neues_material\').style.display = \'none\';};" /> <img src="'.$pfad.'icons/neu.png" alt="neu" title="neues Material" />
					<fieldset id="fieldset_neues_material" style="display: none;"><legend>neues Material</legend>
						'.eintragung_sonstiges_material().'
					</fieldset>
					<fieldset id="fieldset_bestehendes_material"><legend>bestehendes Material</legend>
						<input type="text" id="material_ids" name="material_ids" style="display: none;" readonly="readonly" size="5" />
						<input type="button" class="button" name="material_auswahl_1" value="Auswahl" onclick="IDs_eintragen(\'material\',\''.$pfad.'\',\'undefined\',\'material_ids\')" /><br />
						<span id="material_ids_inhalt"></span>
					</fieldset>
				</span>
				<span id="span_7" style="display:block;"> <a href="'.$pfad.'formular/hilfe.php?inhalt=syntax" onclick="fenster(this.href, \'Hilfe zur Syntax\'); return false;" class="icon" title="Hilfe zur Syntax"><img src="'.$pfad.'icons/hilfe.png" alt="Hilfe" /></a>
                <br /><textarea name="sonstiges_inhalt" class="markItUp" rows="10" cols="50"></textarea></span>';
                

                $inhalt.='</td>
                    <td>
                    <label for="hefter_1" style="width: 65px;">Hefter<em>*</em>:</label><br /><input type="radio" id="hefter_1_0" name="hefter_1" value="0" /> -<br />
                                             <input type="radio" id="hefter_1_1" name="hefter_1" value="1" checked="checked" /> <img src="'.$pfad.'icons/merkteil.png" title="Merkteil" /><br />
                                             <input type="radio" id="hefter_1_2" name="hefter_1" value="2" /> <img src="'.$pfad.'icons/uebungsteil.png" title="&Uuml;bungsteil" /><br /> <img src="'.$pfad.'icons/information.png" class="hilfe_tooltip" alt="Hilfe" onmouseover="showWMTT(\'tt_hefter\')" onmouseout="hideWMTT()" /></td>';
                $inhalt.='
                    <td><p><label for="methods_1">Methode:</label><br /><select id="method_1" name="method_1"><!-- class="jqui_selector"--><option value="">-</option>';
                include($pfad."basic/localisation/methods.php");
                while( list($key, $val) = each ($methods) )
                {
                    $inhalt.='<option value="'.$key.'" title="Anlass: '.html_umlaute($val['occasion']).' | Bedeutung: '.html_umlaute($val['intend']).' | Tipps/Probleme: '.html_umlaute($val['pointer']).'"'; if ($abschnitt['methode']==$key) $inhalt.=' selected="selected"'; $inhalt.='>'.html_umlaute($val['name']).'</option>';
                }
            
                $inhalt.='</select></p>
                    <label for="medium_1">Medium: <img src="'.$pfad.'icons/information.png" class="hilfe_tooltip" alt="Hilfe" onmouseover="showWMTT(\'tt_medium\')" onmouseout="hideWMTT()" /></label><br /><select id="medium_1" name="medium_1">';
                $medien=db_conn_and_sql("SELECT * FROM `medium` ORDER BY `kuerzel`");
                while ($medium = sql_fetch_assoc($medien)) {
                    $inhalt.='<option value="'.$medium["id"].'"';
                    if ($medium["kuerzel"]=="Tafel")
						$inhalt.=' selected="selected"';
					$inhalt.='>'.html_umlaute($medium["kuerzel"]).'</option>';
                }  
                $inhalt.='</select></p>
                    <p><label for="sozialform_1">Sozialform:</label><br /><select id="sozialform_1" name="sozialform_1">';
                $soz_formen=db_conn_and_sql("SELECT * FROM `sozialform`");
                while ($soz_form = sql_fetch_assoc($soz_formen)) {
                    $inhalt.='<option value="'.$soz_form["id"].'">'.html_umlaute($soz_form["kuerzel"]).'</option>';
                }  
                $inhalt.='</select></p>
                    <p><label for="handlungsmuster_1">Handlungsmuster:</label><br /><select id="handlungsmuster_1" name="handlungsmuster_1"><option value="">-</option>';
                $handlungsmuster=db_conn_and_sql("SELECT * FROM `handlungsmuster`");
                while ($handlungsmu = sql_fetch_assoc($handlungsmuster)) {
                    $inhalt.='<option value="'.$handlungsmu["id"].'">'.html_umlaute($handlungsmu["name"]).'</option>';
                }
			
                $inhalt.='</select></p></td>
                <td><p><label for="ziel_1">Ziel:</label><br /><input type="text" name="ziel_1" maxlength="250" size="35" value="'.$abschnitt['ziel'].'" /></p>
                    <p><label for="bemerkung_1">Kommentar: <img src="'.$pfad.'icons/information.png" class="hilfe_tooltip" alt="Hilfe" onmouseover="showWMTT(\'tt_kommentar\')" onmouseout="hideWMTT()" /></label><br /><textarea name="bemerkung_1" cols="40" rows="5">'.$abschnitt['nachbereitung'].'</textarea></p></td>
            </tr>';
    
	$inhalt.='<tr id="new_plan_sec_tr" style="display: none"><td align="center">'.($i+1).'<!--<input type="text" name="pos_schluss" value="" size="1" maxlength="2" disabled="disabled" />--></td>
	    <td align="center"><label for="one_way_time" style="width: 100%;">Minuten<em>*</em>:</label> <input type="text" size="1" maxlength="2" name="one_way_time" /></td>
	    <td><label for="one_way_section">Inhalt:</label> <textarea class="markItUp" name="one_way_section" cols="45" rows="3">'.$schluss.'</textarea></td>
	    <td align="center">-</td>
	    <td align="center">-</td>
	    <td align="center">-</td>
	</tr>
   </table><br />
		<span class="hinweis"><img src="'.$pfad.'/icons/zeit.png" alt="zeit" title="Zeit" />:
			<input type="text" id="zeit_aktu" readonly="readonly" value="'.$bisherige_gesamtzeit.'" size="3" style="width: 35px; background-color: #f9fff2; font-size: 11pt; border: 0 solid black;" /> min &nbsp; - &nbsp;
            &uuml;brig: <input type="text" id="zeit_schluss" name="zeit_schluss" value="'.(($plan["ustd"]+0)*45-$bisherige_gesamtzeit).'" size="1" maxlength="2" style="width: 35px; background-color: #f9fff2; border: 0 solid black;" readonly="readonly" />
        </span>
		<p><fieldset><legend>Hausaufgaben <img id="img_hausaufgaben_eintragung" src="./icons/clip_closed.png" alt="clip" onclick="javascript:clip(\'hausaufgaben_eintragung\', \''.$pfad.'\')" /></legend><span id="span_hausaufgaben_eintragung" style="display:none;">';
		$hausaufgabe_da=db_conn_and_sql("SELECT plan.block_1, hausaufgabe.* FROM `hausaufgabe`,`plan` WHERE `hausaufgabe`.`plan`=`plan`.`id` AND (`plan`.`id`=".$id." OR (`hausaufgabe`.`kontrolliert`!=1 AND `plan`.`fach_klasse`=".$fach_klasse."))");
		while ($hausaufgabe_einzel=sql_fetch_assoc($hausaufgabe_da))
				$inhalt.=html_umlaute($hausaufgabe_einzel["bemerkung"]).' / '.html_umlaute($hausaufgabe_einzel["ziel"]).' / bis: '.substr(datum_strich_zu_punkt($hausaufgabe_einzel["abgabedatum"]),0,6).'
					<a href="'.$pfad.'formular/hausaufgaben.php?plan='.$id.'&amp;block='.$hausaufgabe_einzel["block_1"].'&amp;hausaufgabe='.$hausaufgabe_einzel["id"].'" onclick="fenster(this.href,\'Hausaufgaben eintragen\'); return false;" title="bearbeiten" class="icon"><img src="'.$pfad.'icons/edit.png" alt="bearbeiten" /></a>
					<a href="'.$pfad.'formular/hausaufgaben.php?plan='.$id.'&amp;loeschen=true&amp;hausaufgabe='.$hausaufgabe_einzel["id"].'" onclick="fenster(this.href,\'Hausaufgabe l&ouml;schen\'); return false;" title="Hausaufgabe l&ouml;schen" class="icon"><img src="'.$pfad.'icons/delete.png" alt="l&ouml;schen" /></a><br />';
		$inhalt.='
		<button name="hausaufgaben_eintragen" type="button" onclick="fenster(\'./formular/hausaufgaben.php?plan='.$id.'&amp;block='.$block1.'\',\'Hausaufgaben eintragen\');">
			<img src="'.$pfad.'/icons/hausaufgaben.png" alt="hausaufgaben" /> neue Hausaufgabe eintragen</button></span></fieldset></p>
			
			<fieldset><legend>Zusatz-Infos <img id="img_zusatzinfos" src="./icons/clip_closed.png" alt="clip" onclick="javascript:clip(\'zusatzinfos\', \''.$pfad.'\')" /></legend><span id="span_zusatzinfos" style="display:none;">
			<table><tr><th>Generelle Infos:</th><th><img src="'.$pfad.'/icons/ziele.png" alt="ziele" title="Ziele" /> Ziele:</th><th><img src="'.$pfad.'/icons/struktur.png" alt="struktur" title="Struktur / Inhalt" /> Struktur <img src="'.$pfad.'icons/information.png" class="hilfe_tooltip" alt="Hilfe" onmouseover="showWMTT(\'tt_struktur\')" onmouseout="hideWMTT()" /></th><th><img src="'.$pfad.'/icons/gruppe.png" alt="gruppe" title="Sch&uuml;lergruppe" /> Sch&uuml;lergruppe <img src="'.$pfad.'icons/information.png" class="hilfe_tooltip" alt="Hilfe" onmouseover="showWMTT(\'tt_schuelergruppe\')" onmouseout="hideWMTT()" /></th></tr><tr>
				<td><textarea name="bemerkung" cols="35" rows="8">'.$bemerkung.'</textarea></td>
				<td><textarea name="zusatzziele" cols="35" rows="8">'.$zusatzziele.'</textarea></td>
				<td><textarea name="struktur" cols="35" rows="8">'.$struktur.'</textarea></td>
				<td><textarea name="fk_info" cols="35" rows="8">'.$fach_klassen_informationen.'</textarea></td>
			</tr></table></span></fieldset>
		
		<p><input type="checkbox" name="vorbereitet" value="1"';
		if ($plan["vorbereitet"]) $inhalt.=' checked="checked"';
        
                $javascript='
                    auswertung=new Array(new Array(0, \'datum\',\'datum\',\''.($aktuelles_jahr).'-01-01\',\''.($aktuelles_jahr+1).'-12-31\'), new Array(0, \'zeit\',\'zeit\',\'03:00\',\'23:30\'));
                    zaehler=0;
                    while (document.getElementById(\'zeit_\'+zaehler)) {
                        auswertung.push(new Array(0, \'zeit_\'+zaehler, \'natuerliche_zahl\'));
                        zaehler++;
                    }
                    
                    if (document.getElementById(\'new_plan_sec\').checked) {
                        auswertung.push(new Array(0, \'one_way_time\',\'natuerliche_zahl\'));
                        auswertung.push(new Array(0, \'one_way_section\',\'nicht_leer\'));
                    }
                    
                    if (document.getElementById(\'new_section\').checked) {
                        auswertung.push(new Array(0, \'minuten_1\',\'natuerliche_zahl\'));
                        
                        if (document.getElementById(\'add_content_header\').checked)
                            auswertung.push(new Array(0, \'ueberschrift_text_1\',\'nicht_leer\'));
                            
                        if (document.getElementById(\'add_content_test\').checked) {
                            if (document.getElementById(\'test_neu\').checked) {
                                auswertung.push(new Array(0, \'test_thema_0\',\'nicht_leer\',\'-\'), new Array(0, \'test_lernbereich\',\'nicht_leer\', \'-\'));
                            }
                            else
                                auswertung.push(new Array(0, \'test_ids\',\'nicht_leer\'));
                        }
                            
                        if (document.getElementById(\'add_content_task\').checked) {
                            if (document.getElementById(\'aufgabe_neu\').checked) {
                                auswertung.push(new Array(0, \'thema_0\',\'nicht_leer\',\'-\'), new Array(0, \'lernbereich\',\'nicht_leer\',\'-\'));
                                if (document.getElementById(\'art\').value!=\'text\')
                                    auswertung.push(new Array(0, \'seite\',\'nicht_leer\'));
                                else
                                    auswertung.push(new Array(0, \'text\',\'nicht_leer\'));
                            }
                            else
                                auswertung.push(new Array(0, \'aufgabe_ids\',\'nicht_leer\'));
                        }
                            
                        if (document.getElementById(\'add_content_material\').checked) {
                            if (document.getElementById(\'material_neu\').checked) {
                                auswertung.push(new Array(0, \'material_name\',\'nicht_leer\'));
                            }
                            else auswertung.push(new Array(0, \'material_ids\',\'nicht_leer\'));
                        }
                            
                        if (document.getElementById(\'add_content_text\').checked) {
                                auswertung.push(new Array(0, \'sonstiges_inhalt\',\'nicht_leer\'));
                        }
                    }
                    pruefe_formular(auswertung);
                    return false;';
                    
		$inhalt.=' /> vorbereitet <button name="speicher_mich" class="button" onclick="'.$javascript.'"><img src="'.$pfad.'/icons/page_save.png" alt="speichern" /> Speichern</button></p>
      </form>';
	return $inhalt;
}

function eintragung_material($typ, $pfad, $id) {
	$db = new db;
	switch ($typ) {
		case 1:
			if (!proofuser("ueberschrift", $id))
				die("Dazu sind Sie nicht berechtigt.");
		    $result=db_conn_and_sql("SELECT * FROM `ueberschrift` WHERE `ueberschrift`.`id`=".$id);
			$inhalt='<form action="material_bearb.php?aendern=true&amp;typ='.$typ.'&amp;id='.$id.'" method="post" accept-charset="ISO-8859-1">
				<fieldset><legend>&Uuml;berschrift &auml;ndern</legend>
                <input type="hidden" name="id" value="'.$id.'" />
				<select name="ebene">';
				    for ($i=1;$i<=5;$i++) {
						$inhalt.='<option value="'.$i.'"'; if ($i==sql_result($result,0,"ueberschrift.ebene")) $inhalt.=' selected="selected"';
						$inhalt.='>'; for ($j=0; $j<$i; $j++) $inhalt.='1.';
						$inhalt.='</option>';
					}
				$inhalt.='
				</select>
                <input type="text" name="text" value="'.html_umlaute(sql_result($result,0,"ueberschrift.text")).'" maxlength="150" />
				Unter&uuml;berschriften-Typ:
					<select name="typ">'; $typ=array(
						array("wert"=>"1","beschreibung"=>"1 (Zahlen)"),
						array("wert"=>"a","beschreibung"=>"a (Kleinbuchstaben)"),
						array("wert"=>"A","beschreibung"=>"A (Gro&szlig;buchstaben)"),
						array("wert"=>"I","beschreibung"=>"I (r&ouml;mische Zahlen)"),
						array("wert"=>"-","beschreibung"=>"alles weglassen"));
					foreach($typ as $value) {
						$inhalt.='<option value="'.$value["wert"].'"';
						if ($value["wert"]==sql_result($result,0,"ueberschrift.typ")) $inhalt.=' selected="selected"';
						$inhalt.='>'.$value["beschreibung"].'</option>';
					}
					$inhalt.='</select>
                <input type="button" class="button" value="&auml;ndern" onclick="auswertung=new Array(new Array(0, \'text\',\'nicht_leer\')); pruefe_formular(auswertung);" />
				</fieldset>
		    </form>';
		break;
		case 2:
		    $inhalt="Test - noch machen";
		break;
		case 3:
			$inhalt=eintragung_aufgabe($id);
		break;
		case 4:
		    $inhalt="Link - noch machen";
		break;
		case 5:
		    $inhalt="Grafik - noch machen";
		break;
		case 6:
			if (!proofuser("material", $id))
				die("Dazu sind Sie nicht berechtigt.");
			
			$materialarray=$db->material($id);
			$inhalt='<form action="material_bearb.php?aendern=true&amp;typ='.$typ.'&amp;id='.$id.'" method="post" accept-charset="ISO-8859-1">
                <input type="hidden" name="id" value="'.$id.'" />
				<fieldset><legend>Sonstiges Material &auml;ndern</legend>
                    <label for="name">Name<em>*</em>:</label> <input type="text" name="name" value="'.$materialarray["name"].'" size="30" maxlength="255" /><br />
                    <label for="beschreibung">Beschreibung:</label> <input type="text" name="beschreibung" value="'.$materialarray["beschreibung"].'" size="50" maxlength="255" /><br />
                    <label for="aufbewahrungsort">Aufbewahrungsort:</label> <input type="text" name="aufbewahrungsort" value="'.$materialarray["aufbewahrungsort"].'" size="50" maxlength="255" /><br />';
					$selected_tags='';
					for($thema=0; $thema<count($materialarray["thema"]); $thema++)
						$selected_tags[$thema]=$materialarray["thema"][$thema]["id"];
					$inhalt.=themen_auswahl($pfad, 'thema', $selected_tags);
					$inhalt.='<br />
                    <button onclick="fenster(\''.$pfad.'formular/material_delete.php?id='.$id.'\', \'\'); return false;"><img src="'.$pfad.'icons/delete.png" alt="delete" /> l&ouml;schen</button>
                    <button style="float: right;" onclick="auswertung=new Array(new Array(0, \'name\',\'nicht_leer\')); pruefe_formular(auswertung);"><img src="'.$pfad.'icons/page_save.png" alt="save" /> speichern</button>
                </fieldset>
		    </form>';
		break;
		case 7:
			if (!proofuser("sonstiges", $id))
				die("Dazu sind Sie nicht berechtigt.");
		    $result=db_conn_and_sql("SELECT * FROM `sonstiges` WHERE `sonstiges`.`id`=".$id);
			$inhalt='<form action="material_bearb.php?aendern=true&amp;typ='.$typ.'&amp;id='.$id.'" method="post" accept-charset="ISO-8859-1">
				<fieldset><legend>Text &auml;ndern</legend>
				<input type="hidden" name="id" value="'.$id.'" />
				<textarea name="inhalt" class="markItUp" rows="15" cols="80">'.html_umlaute(sql_result($result,0,"sonstiges.inhalt")).'</textarea><br />
                <input type="button" class="button" value="&auml;ndern" onclick="auswertung=new Array(new Array(0, \'inhalt\',\'nicht_leer\')); pruefe_formular(auswertung);" />
				</fieldset>
		    </form>';
		break;
	}
	return $inhalt;
}

// deprecated
function aufgabe_mit_bildern($pfad, $aufgabe, $gruppe, $modus) {
	$inhalt='';
    $text_der_aufgabe=syntax_zu_html($aufgabe['text'],$aufgabe['teilaufgaben_nebeneinander'], $modus, $pfad,$gruppe);
    $inhalt.=$text_der_aufgabe;
	return $inhalt;
}


function test_druckansicht ($id,$datumsstring) {
	$db = new db;
	
	if ($datumsstring=="arbeitsblatt_bearbeiten" and $id=="neu")
		$pfad='../';
	else {
		$pfad='../';
		$test=$db->test($id);
		
		if ($datumsstring==false) $datumsstring="___________________";
		
	}
    $gruppe_B_da=false;
    for ($i=0; $i<count($test['aufgaben']) and !$gruppe_B_da; $i++)
        if ($test['aufgaben'][$i]['position_B']>0) $gruppe_B_da=true;
	
	
	// ----------------------------------------- Bearbeiten ----------------------------------------------------
	if ($datumsstring=="bearbeiten" or $datumsstring=="arbeitsblatt_bearbeiten") {
		$notentyp=db_conn_and_sql("SELECT DISTINCT notentypen.* FROM schule_user, notentypen WHERE notentypen.id<11 OR (notentypen.schule=schule_user.schule AND schule_user.user=".$_SESSION["user_id"].")");
		$lernbereiche = $db->lernbereiche();
	?>
	<div class="tooltip" id="tt_test_titel">
		Falls der Titel frei bleibt, werden die Themen als &Uuml;berschrift verwendet.</div>
	<div class="tooltip" id="tt_test_vorspann">
		Hier aufgeschrieber Text wird <?php if ($datumsstring=="arbeitsblatt_bearbeiten") echo 'auf dem Arbeitsblatt'; else echo 'im Test'; ?> nach dessen &Uuml;berschrift dargestellt.</div>
	<div class="tooltip" id="tt_test_zeit">
		Diese Angabe hat keinen weiteren Einfluss. Sie steht lediglich in der Unterrichtsdruckansicht als Information: "urspr&uuml;ngliche L&auml;nge".</div>
	<div class="tooltip" id="tt_test_hilfsmittel">
		Diese Angabe hat keinen weiteren Einfluss. Sie steht lediglich in der Unterrichtsdruckansicht als Information: "zugelassene Hilfmittel".</div>
	<div class="tooltip" id="tt_test_platz_lassen">
		Wenn dieses H&auml;kchen gesetzt ist, wird in jeder einzelnen Aufgabe gepr&uuml;ft, ob es darin vorgesehen ist, Platz freizulassen bzw. mit liniertem, kariertem oder Millimeterpapier aufzuf&uuml;llen.</div>
	<div class="tooltip" id="tt_test_bemerkung">
		Diese Angabe hat keinen weiteren Einfluss.</div>
	<div class="tooltip" id="tt_test_gruppen">
		<p>Markieren Sie diese Checkbox, um den Test mit zwei Gruppen zu versehen. W&auml;hlen Sie dann die Aufgabenpositionen der einzelnen Gruppen aus. So ist es m&ouml;glich, die Aufgaben lediglich in anderer Reihenfolge auftreten zu lassen, oder aber v&ouml;llig verschiedene Aufgaben auszuw&auml;hlen.</p>
		<p>Ebenfalls k&ouml;nnen Sie nach einer bestimmten Aufgabe einen Seitenumbruch beim Ausdruck erzwingen, damit die darauf folgende Aufgabe vollst&auml;ndig auf der n&auml;chsten Seite erscheint.</p>
		<p>Eine Zusatzaufgabe bringt Extrapunkte, wird also bei Bildung der Gesamtpunktzahl vernachl&auml;ssigt. Diese Auswahl gilt f&uuml;r beide Gruppen.</p></div>
	<form action="<?php echo $pfad; ?>formular/test_bearbeiten.php?aktion=eintragen" method="post" enctype="multipart/form-data" accept-charset="ISO-8859-1">
		<input type="hidden" name="test" value="<?php echo $id; ?>" />
		<input type="hidden" name="gruppen" value="<?php echo $gruppe_B_da; ?>" />
		<input type="hidden" name="test_typ" value="<?php if ($datumsstring=="arbeitsblatt_bearbeiten") $typ='arbeitsblatt'; else $typ='test'; echo $typ; ?>" />
		<fieldset><legend><?php if ($datumsstring=="arbeitsblatt_bearbeiten") echo 'Arbeitsblatt'; else echo 'Test'; ?>-Informationen</legend>
		<ol class="divider"><li>
		<?php
		if ($datumsstring!="arbeitsblatt_bearbeiten") { ?>
			<label for="notentyp">Zensur-Typ<em>*</em>:</label> <select name="notentyp">
				<?php for($i=0;$i<sql_num_rows($notentyp);$i++) { ?>
					<option value="<?php echo sql_result($notentyp,$i,'notentypen.id'); ?>"<?php if ($test['notentyp_id']==sql_result($notentyp,$i,'notentypen.id')) echo ' selected="selected"'; ?>><?php echo html_umlaute(sql_result($notentyp,$i,'notentypen.name')); ?></option>
				<?php } ?>
		</select><br /><?php
		} ?>
		<label for="titel">Titel: <img src="<?php echo $pfad; ?>icons/information.png" class="hilfe_tooltip" alt="Hilfe" onmouseover="showWMTT('tt_test_titel')" onmouseout="hideWMTT()" /></label> <input type="text" name="titel" value="<?php echo $test['titel']; ?>" />
        <?php /* ersetzen */
        if ($datumsstring=="arbeitsblatt_bearbeiten") { ?>
			<br /><label for="tandem">Tandem:</label> <input type="checkbox" name="tandem" <?php if ($gruppe_B_da) echo 'checked="checked" '; ?> /><?php
		} ?>
        </li>
		<li><?php
		$selected_tags='';
		if ($id!="neu") {
			$testthemen=db_conn_and_sql("SELECT * FROM themenzuordnung WHERE typ=5 AND id=".$id);
			for($thema=0; sql_result($testthemen, $thema, "themenzuordnung.thema")>0; $thema++)
				$selected_tags[$thema]=sql_result($testthemen, $thema, "themenzuordnung.thema");
		}
		echo themen_auswahl($pfad, 'test_thema', $selected_tags);
		?><br />
		<label for="test_lernbereich">Lernbereich<em>*</em>:</label> <select name="test_lernbereich"><?php echo $db->lernbereichoptions($test["lernbereich"]); ?>
		</select></li>
		<li>
		<?php if ($test["url_vorhanden"]) {
			echo '<div class="tooltip" id="tt_test_url">Um die urspr&uuml;ngliche Datei ('.html_umlaute($test["url"]).') zu ersetzen, k&ouml;nnen Sie hier eine alternative Datei angeben.</div>'; ?>
			<label for="url">Test-Datei: <img src="<?php echo $pfad; ?>icons/information.png" class="hilfe_tooltip" alt="Hilfe" onmouseover="showWMTT('tt_test_url')" onmouseout="hideWMTT()" /></label> <input type="file" name="test_datei" />
			<?php } else { ?>
			<label for="vorspann">Vorspann: <img src="<?php echo $pfad; ?>icons/information.png" class="hilfe_tooltip" alt="Hilfe" onmouseover="showWMTT('tt_test_vorspann')" onmouseout="hideWMTT()" /></label> <textarea name="vorspann" cols="50" rows="5"><?php echo $test['vorspann']; ?></textarea><?php } ?>
		</li>
		<li>
		<?php if ($datumsstring!="arbeitsblatt_bearbeiten") { ?>
		<label for="gesamtpunkte">Gesamtpunktzahl:</label> <input type="text" name="gesamtpunkte" value=<?php echo '"'.html_umlaute($test['punkte']).'"'; if (count($test['aufgaben'])>=1) echo ' disabled="disabled"'; ?> size="2" maxlength="3" /> (<?php
			$hilf=0;
			for ($i=0;$i<count($test['aufgaben']);$i++) {
				if($test['aufgaben'][$i]['zusatzaufgabe']) echo ' [+ '.($test['aufgaben'][$i]['punkte']+0).']';
				else {
					$hilf+=$test['aufgaben'][$i]['punkte'];
					if($i>0) echo " + "; echo $test['aufgaben'][$i]['punkte']+0;
				}
			}
			echo " = ".$hilf; ?>)
		<span class="hinweis">Bei Angabe von einzelnen Aufgaben wird die Gesamtpunktzahl automatisch errechnet.</span><br />
		<label for="zeit"><img src="<?php echo $pfad; ?>icons/zeit.png" alt="zeit" title="Bearbeitungszeit" />: <img src="<?php echo $pfad; ?>icons/information.png" class="hilfe_tooltip" alt="Hilfe" onmouseover="showWMTT('tt_test_zeit')" onmouseout="hideWMTT()" /></label> <input type="text" name="zeit" value="<?php echo html_umlaute($test['bearbeitungszeit']); ?>" size="2" maxlength="3" /> min (<?php $hilf=0; for ($i=0;$i<count($test['aufgaben']);$i++) {$hilf+=$test['aufgaben'][$i]['bearbeitungszeit']; if($i>0) echo " + "; echo $test['aufgaben'][$i]['bearbeitungszeit']+0;} echo " = ".$hilf; ?>)</li>
		<li>
		<label for="hilfsmittel">Hilfsmittel: <img src="<?php echo $pfad; ?>icons/information.png" class="hilfe_tooltip" alt="Hilfe" onmouseover="showWMTT('tt_test_hilfsmittel')" onmouseout="hideWMTT()" /></label> <input type="text" name="hilfsmittel" value="<?php echo $test['hilfsmittel']; ?>" />
		<br />
		<?php } ?>
		<?php if (!$test["url_vorhanden"]) { ?><label for="platz"><img src="<?php echo $pfad; ?>icons/platz_lassen.png" alt="platz_lassen" title="Platz lassen unter Aufgaben" />: <img src="<?php echo $pfad; ?>icons/information.png" class="hilfe_tooltip" alt="Hilfe" onmouseover="showWMTT('tt_test_platz_lassen')" onmouseout="hideWMTT()" /></label> <input type="checkbox" value="1" name="platz"<?php if($test['platz_lassen']) echo ' checked="checked"'; ?> /><?php } ?></li>
		<li><label for="bemerkung_test"><img src="<?php echo $pfad; ?>icons/kommentar.png" alt="kommentar" title="Bemerkung" />: <img src="<?php echo $pfad; ?>icons/information.png" class="hilfe_tooltip" alt="Hilfe" onmouseover="showWMTT('tt_test_bemerkung')" onmouseout="hideWMTT()" /></label> <textarea name="bemerkung_test" cols="50" rows="5"><?php echo $test['bemerkung']; ?></textarea></li>
		</ol>
	</fieldset><br />
	<?php if (!$test["url_vorhanden"]) {
		if (count($test['aufgaben'])>0) { ?>
	<table class="tabelle" cellspacing="0">
		<tr>
			<?php if ($datumsstring!="arbeitsblatt_bearbeiten" or 1==1) { ?>
			<th>Gruppen<br /><input type="checkbox" <?php if ($gruppe_B_da) echo 'checked="checked" '; ?>onclick="for (i=0;i&lt;<?php echo count($test['aufgaben']); ?>;i++) document.getElementById('BGruppe'+i).style.display=this.checked==1?'block':'none';" title="Gruppe B?" /> <img src="<?php echo $pfad; ?>icons/information.png" class="hilfe_tooltip" alt="Hilfe" onmouseover="showWMTT('tt_test_gruppen')" onmouseout="hideWMTT()" /></th>
			<?php } else echo '<th>Position</th>'; ?>
			<th>Aufgabe/L&ouml;sung</th>
			<?php if ($datumsstring!="arbeitsblatt_bearbeiten") { ?><th>Pkte</th><?php } ?></tr>
	<?php
	for ($i=0;$i<count($test['aufgaben']);$i++) { ?>
      <tr style="page-break-inside: avoid;<?php if ($test['aufgaben'][$i]['neue_seite_'.$gruppe]) echo ' page-break-before: always;'; ?>">
        <td valign="top">
			<input type="hidden" name="aufgabe_id_<?php echo $i; ?>" value="<?php echo $test['aufgaben'][$i]['id']; ?>" />
			<b>A:</b><select name="position_A_<?php echo $i; ?>"><option value="">-</option><?php for ($j=0;$j<count($test['aufgaben']);$j++) { echo '<option value="'.($j+1).'"'; if ($j+1==$test['aufgaben'][$i]['position_A']) echo ' selected="selected"'; echo '>'.($j+1).'</option>'; } ?></select><br /><input type="checkbox" name="seitenumbruch_A_<?php echo $i; ?>" value="1"<?php if ($test['aufgaben'][$i]['neue_seite_A']) echo ' checked="checked"'; ?> title="Seitenumbruch bei Gruppe A" /> <img src="<?php echo $pfad; ?>icons/seitenumbruch.png" alt="seitenumbruch" title="Seitenumbruch bei Gruppe A" />
			<?php if (1==1 or $datumsstring!="arbeitsblatt_bearbeiten") { ?>
			<br />
			<span id="BGruppe<?php echo $i; ?>"<?php if (!$gruppe_B_da) echo ' style="display: none;"'; ?>>
				<b>B:</b><select name="position_B_<?php echo $i; ?>"><option value="">-</option><?php for ($j=0;$j<count($test['aufgaben']);$j++) { echo '<option value="'.($j+1).'"'; if ($j+1==$test['aufgaben'][$i]['position_B']) echo ' selected="selected"'; echo '>'.($j+1).'</option>'; } ?></select><br /><input type="checkbox" name="seitenumbruch_B_<?php echo $i; ?>" value="1"<?php if ($test['aufgaben'][$i]['neue_seite_B']) echo ' checked="checked"'; ?> title="Seitenumbruch bei Gruppe B" /> <img src="<?php echo $pfad; ?>icons/seitenumbruch.png" alt="seitenumbruch" title="Seitenumbruch bei Gruppe B" /><br /></span><input type="checkbox" name="zusatzaufgabe_<?php echo $i; ?>" value="1"<?php if ($test['aufgaben'][$i]['zusatzaufgabe']) echo ' checked="checked"'; ?> title="Zusatzaufgabe" />ZA
			<?php } ?></td>
        <td style="width: 92%;"><?php
			echo syntax_zu_html($test['aufgaben'][$i]['text'],$test['aufgaben'][$i]['teilaufgaben_nebeneinander'], 'bearbeiten', $pfad,'A');
			  ?>
			<br style="clear: both;" />
			<b>L&ouml;sung:</b>  
			<br />
			<?php
                echo syntax_zu_html($test['aufgaben'][$i]['loesung'],$test['aufgaben'][$i]['teilaufgaben_nebeneinander'],'bearbeiten',$pfad,'A');
                if ($test['aufgaben'][$i]['bemerkung']!="")
                    echo $test['aufgaben'][$i]['bemerkung']; ?>  
                <a href="javascript:fenster('<?php echo $pfad; ?>formular/aufgabe_bearb.php?welche=<?php echo $test['aufgaben'][$i]['id']; ?>');" title="bearbeiten" class="icon"><img src="<?php echo $pfad; ?>icons/edit.png" alt="bearbeiten" /></a>
                <a href="<?php echo $pfad; ?>formular/test_bearbeiten.php?test=<?php echo $_GET["welcher"]; ?>&amp;aufgabe=<?php echo $test['aufgaben'][$i]['id']; ?>&amp;aktion=aufgabe_aus_test_entfernen" title="aus Test entfernen" class="icon"><img src="<?php echo $pfad; ?>icons/entfernen.png" alt="entfernen" /></a>
			  </td>
		<?php if ($datumsstring!="arbeitsblatt_bearbeiten") { ?>
		<td valign="bottom"<?php if($test['aufgaben'][$i]['punkte']<=0) echo ' style="background-color: red;"'; ?>>&nbsp;<?php echo abs($test['aufgaben'][$i]['punkte']); ?></td>
		<?php } ?>
	</tr>
	<?php } ?>
	</table>
	<!--<a href="<?php echo $pfad; ?>formular/test_bearbeiten.php?test=<?php echo $_GET["welcher"]; ?>&amp;aktion=aufgabe_hinzufuegen" title="Aufgabe hinzuf&uuml;gen" class="icon"><img src="./icons/add.png" alt="add" /></a> Aufgabe hinzuf&uuml;gen-->
	<?php }
	if ($id!="neu") { ?>
	<fieldset name="fieldset_aufgaben"><legend><img src="<?php echo $pfad; ?>icons/aufgaben.png" alt="aufgabe" /> Aufgabe(n) hinzuf&uuml;gen</legend>
        <input type="text" name="aufgaben_ids" id="aufgaben_ids" style="display: none;" readonly="readonly" size="20" />
        <button type="button" onclick="IDs_eintragen('aufgabe','<?php echo $pfad; ?>','undefined','aufgaben_ids')"><img src="<?php echo $pfad; ?>icons/fundus.png" alt="fundus" /> vorhandene Aufgaben eintragen</button>
        <button type="button" onclick="fenster('<?php echo $pfad; ?>formular/aufgabe_neu.php?test=<?php echo $_GET["welcher"]; ?>&amp;einzeltyp=1', 'Neue Aufgabe');" title="neue Aufgabe erstellen und dem Test hinzuf&uuml;gen"><img src="<?php echo $pfad; ?>icons/neu.png" alt="neu" /> neue Aufgabe</button>
		<span id="aufgaben_ids_inhalt"></span>
	</fieldset>
	<br /><?php } else echo 'Aufgaben k&ouml;nnen nach dem Speichervorgang hinzugef&uuml;gt werden.<br />';
	}
	if ($datumsstring=="arbeitsblatt_bearbeiten" and $id=="neu") $pruefstring="new Array(new Array(1, 'test_thema_0','nicht_leer','-'), new Array(1, 'test_lernbereich','nicht_leer','-'))";
	else  $pruefstring="new Array(new Array(0, 'test_thema_0','nicht_leer','-'), new Array(0, 'test_lernbereich','nicht_leer','-'))";
	?>
    <button onclick="fenster('<?php echo $pfad; ?>formular/test_delete.php?id=<?php echo $_GET["welcher"]; ?>', ''); return false;"><img src="<?php echo $pfad; ?>icons/delete.png" alt="delete" /> l&ouml;schen</button>
	<button style="float: right;" onclick="auswertung=<?php echo $pruefstring; ?>; pruefe_formular(auswertung);"><img src="<?php echo $pfad; ?>icons/page_save.png" alt="save" /> speichern</button>
	</form>

	<?php
	} else {
	// ----------------------------------------- Druckversion ----------------------------------------------------
	$pfad='./';
	?>
	<div id="mf">
		<ul class="r">
			<li><a id="pv" href="javascript:window.print()">diese Seite drucken</a></li>
			<li><a href="javascript:window.back();" class="icon"><img src="<?php echo $pfad; ?>icons/pfeil_links.png" alt="zurueck" /> zur&uuml;ck</a></li>
		</ul>
	</div>
	<?php
	$gruppe='A';
	while ($gruppe=='A' or $gruppe=='B') {
    ?>
    <table style="font-family: Arial, sans-serif; width:100%;<?php if ($gruppe=='B') echo ' page-break-before: always;'; ?>">
        <tr><td rowspan="2"><h1 style="font-size: 14pt;"><?php if ($test["arbeitsblatt"]) echo 'AB'; else echo $test['notentyp']; ?> - <?php if ($test["alternativtitel"]!="") echo $test["alternativtitel"]; else echo $test['themen']; if ($gruppe_B_da) {echo " - "; if ($test["arbeitsblatt"]) echo "Tandem"; else echo "Gruppe"; echo "&nbsp;".$gruppe;} ?></h1></td>
	<?php if (!$test["arbeitsblatt"]) { ?>
	            <td style="text-align: right;">Name:&nbsp;</td><td style="width:18%; text-align: right;">&nbsp;___________________</td></tr>
       <tr><td style="text-align: right; height:2.4em; vertical-align:bottom;">Datum:&nbsp;</td><td style="text-align: right; vertical-align:bottom;">&nbsp;<?php echo $datumsstring; ?></td>
	   <?php } ?>
	</tr></table>
	
	<?php if ($test['vorspann']!="") { ?><p style="font-size: 8pt;"><?php echo syntax_zu_html($test['vorspann']); ?></p><?php } ?>
    <table style="border-spacing: 5px; border-collapse: separate; font-family: sans-serif; width: 100%; font-size: 10pt;">
    <?php
    // sortieren
	for ($i=0;$i<count($test['aufgaben'])-1;$i++)
		for ($j=$i;$j<count($test['aufgaben']);$j++)
			if ($test['aufgaben'][$i]['position_'.$gruppe]>$test['aufgaben'][$j]['position_'.$gruppe] and $test['aufgaben'][$j]['position_'.$gruppe]!="") {
				$hilf=$test['aufgaben'][$i];
				$test['aufgaben'][$i]=$test['aufgaben'][$j];
				$test['aufgaben'][$j]=$hilf;
		    }
			//for ($i=0;$i<count($test['aufgaben']);$i++) echo $test['aufgaben'][$i]['position_'.$gruppe]." ";
	$gesamtpunktzahl=0; $aufgaben_position=1;
    for ($i=0;$i<count($test['aufgaben']);$i++) if ($test['aufgaben'][$i]['position_'.$gruppe]!="") { ?>
      <tr style="page-break-inside: avoid;<?php if ($test['aufgaben'][$i]['neue_seite_'.$gruppe]) echo ' page-break-before: always;'; ?>">
        <td style="vertical-align: top"><?php
            if ($test['aufgaben'][$i]['zusatzaufgabe'])
                echo "ZA.";
            else {
                echo $aufgaben_position.".";
                $aufgaben_position++; /*echo $gruppe." ".$test['aufgaben'][$i]['position_'.$gruppe];*/
                $gesamtpunktzahl+=$test['aufgaben'][$i]['punkte'];
            } ?></td>
        <td style="width: 92%; padding-bottom:5px;">
            <?php if ($test['platz_lassen'])
                echo syntax_zu_html($test['aufgaben'][$i]['text'],$test['aufgaben'][$i]['teilaufgaben_nebeneinander'], 'papier', $pfad, $gruppe);
            else echo syntax_zu_html($test['aufgaben'][$i]['text'],$test['aufgaben'][$i]['teilaufgaben_nebeneinander'], '-', $pfad, $gruppe); ?></td>
		<?php if (!$test["arbeitsblatt"]) { ?>
        <td style="vertical-align: bottom; text-align: right;">&nbsp;<?php if (!$test['platz_lassen'] or $test['aufgaben'][$i]['cm']<0.5) echo '&nbsp;/&nbsp;'.abs($test['aufgaben'][$i]['punkte']); ?></td>
		<?php }
        else {
            $gegengruppe='A'; if ($gruppe=='A') $gegengruppe='B';
            if ($gruppe_B_da)
                echo '<td style="border-left: 1px dashed gray; padding-left: 12px; vertical-align: top;"><b>L&ouml;sung '.$gegengruppe.':</b><br />'.syntax_zu_html($test['aufgaben'][$i]['loesung'],$test['aufgaben'][$i]['teilaufgaben_nebeneinander'], 'kein_papier', $pfad, $gegengruppe).'</td>';
        } ?>
	</tr>
<?php if ($test['platz_lassen'] and $test['aufgaben'][$i]['cm']>=0.5) { ?>
    <tr>
		<td colspan="2">&nbsp;</td>
		<?php
        if (!$test["arbeitsblatt"]) { ?>
		<td style="vertical-align: bottom; text-align: right;">&nbsp;&nbsp;/&nbsp;<?php echo abs($test['aufgaben'][$i]['punkte']); ?></td>
		<?php } ?>
	</tr>
    <?php } } ?>
	
	<?php if (!$test["arbeitsblatt"]) { ?>
		<tr><td colspan="3" style="text-align: right;">Gesamt: &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; / <?php echo $gesamtpunktzahl; ?></td></tr>
	<?php } ?>
    </table>

	<?php if ($gruppe_B_da and $gruppe=='A') $gruppe='B'; else $gruppe=''; } ?>
 
        <h1 style="page-break-before: always;"><?php if (!$test['arbeitsblatt']) echo $test['notentyp'].' - '; if ($test["alternativtitel"]!="") echo $test["alternativtitel"]; else echo $test['themen']; ?> - L&ouml;sung</h1>
    <table style="border-spacing: 10px; border-collapse: separate; font-family: Courier New;">
    <?php
    for ($i=0;$i<count($test['aufgaben']);$i++) { ?>
      <tr style="page-break-inside: avoid;">
        <td style="vertical-align: top;"><b>zu&nbsp;<?php if ($test['aufgaben'][$i]['zusatzaufgabe']) echo "ZA."; else if ($test['aufgaben'][0]['position_B']>0) echo "A".$test['aufgaben'][$i]['position_A'].". / B".$test['aufgaben'][$i]['position_B']."."; else echo ($i+1)."."; ?></b><br />(<?php echo abs($test['aufgaben'][$i]['punkte']); ?> P.)<br />(<?php echo $test['aufgaben'][$i]['bearbeitungszeit']; ?>&nbsp;min)</td>
        <td style="width: 92%;"><!--<pre>--><?php echo syntax_zu_html($test['aufgaben'][$i]['loesung'],$test['aufgaben'][$i]['teilaufgaben_nebeneinander'],'papier',$pfad,$gruppe); ?><!--</pre>--><?php if ($test['aufgaben'][$i]['bemerkung']!="") echo $test['aufgaben'][$i]['bemerkung']; ?></td>
      </tr>
    <?php } ?>
    </table><?php
	}
}

function einzelstunde_druckansicht($plan, $text) {
		echo "<h4>".$plan["fachklassen_name"].": ".$plan["wochentag"].", ".$plan["datum"]."</h4>";
		
		if (isset($plan['hausaufgaben_kontrolle'])) {
			$hausaufgaben.='<img src="./icons/hausaufgaben.png" alt="hausaufgaben" title="Hausaufgabenkontrolle" style="float: left;" />
			<ul style="list-style-image:url('.$pfad.'icons/abhaken.png);">';
			foreach ($plan['hausaufgaben_kontrolle'] as $value) {
				$hausaufgaben.='<li>'.hausaufgabe_zeigen($value)."";
				if ($value["status"]==-1) {
					$vergesser=db_conn_and_sql("SELECT * FROM `hausaufgabe_vergessen`,`schueler` WHERE `hausaufgabe_vergessen`.`erledigt`=0 AND `hausaufgabe_vergessen`.`schueler`=`schueler`.`id` AND `hausaufgabe_vergessen`.`hausaufgabe`=".$value["id"]);
					for ($i=0;$i<sql_num_rows($vergesser);$i++) $hausaufgaben.=' <img src="'.$pfad.'icons/abhaken.png" alt="checkbox" style="vertical-align: middle;" />&nbsp;'.html_umlaute(sql_result($vergesser,$i,"schueler.vorname"));
				}
				else $hausaufgaben.="Vergessen:<br />";
				$hausaufgaben.="</li>";
			}
			$hausaufgaben.='</ul>';
		}
		
		if (isset($plan['test_rueckgabe']) or isset($plan['berichtigung_kontrolle'])) {
			$tests.= '<img src="./icons/test.png" alt="test" title="Tests" style="float: left;" />
				<ul style="list-style-image:url('.$pfad.'icons/abhaken.png);">';
			if (isset($plan['berichtigung_kontrolle']))
			foreach ($plan['berichtigung_kontrolle'] as $berichtigung) {
				$tests.= '<li>'.berichtigung_zeigen($berichtigung).': ';
				$vergesser=db_conn_and_sql("SELECT * FROM `berichtigung_vergessen`,`schueler` WHERE (`berichtigung_vergessen`.`berichtigung_erledigt`=0 OR `berichtigung_vergessen`.`unterschrift_erledigt`=0) AND `berichtigung_vergessen`.`schueler`=`schueler`.`id` AND `berichtigung_vergessen`.`notenbeschreibung`=".$berichtigung["id"]." ORDER BY `schueler`.`vorname`, `schueler`.`position`, `schueler`.`name`");
				if (sql_num_rows($vergesser)>0) {
					for ($i=0;$i<sql_num_rows($vergesser);$i++)
						if (($berichtigung["berichtigung_gefordert"]=="0" and sql_result($vergesser,$i,"berichtigung_vergessen.berichtigung_erledigt")==0) or ($berichtigung["unterschrift_gefordert"]=="0" and sql_result($vergesser,$i,"berichtigung_vergessen.unterschrift_erledigt")==0)) {
							if ($i!=0) $tests.= ' | ';
							if ($berichtigung["berichtigung_gefordert"]=="0" and sql_result($vergesser,$i,"berichtigung_vergessen.berichtigung_erledigt")==0) $tests.= 'B'.sql_result($vergesser,$i,"berichtigung_vergessen.berichtigung_anzahl").'&nbsp;';
							if ($berichtigung["unterschrift_gefordert"]=="0" and sql_result($vergesser,$i,"berichtigung_vergessen.unterschrift_erledigt")==0) $tests.= 'U'.sql_result($vergesser,$i,"berichtigung_vergessen.unterschrift_anzahl").'&nbsp;';
							$tests.= html_umlaute(sql_result($vergesser,$i,"schueler.vorname")).' '.html_umlaute(substr(sql_result($vergesser,$i,"schueler.name"),0,1)).'.';
						}
					$tests.= '<br />';
				}
				else $tests.= "Vergessen:<br />";
				$tests.= "</li>";
			}
			if (isset($plan['test_rueckgabe'])) foreach ($plan['test_rueckgabe'] as $test_rueckgabe) {
				$tests.= '<li>'.test_zeigen($test_rueckgabe).'</li>';
			}
			$tests.= '</ul>';
		}
		
		if ($plan['struktur']!="")
			$struktur= '<img src="./icons/struktur.png" alt="struktur" title="Struktur" />: '.nl2br($plan['struktur']).'<br />';
		if ($plan['ziele']!="")
			$ziele= '<img src="./icons/ziele.png" alt="ziele" title="Ziele" />: '.nl2br($plan['ziele']).'<br />';
		if ($plan['notizen']!="")
			$notizen= '<img src="./icons/note.png" alt="notizen" title="Notizen" />: '.nl2br($plan['notizen']).'<br />';
		
		/*	inklusive der anderen Faecher: 
		"SELECT *
			FROM `notentypen`, `fach_klasse`, `notenbeschreibung` LEFT JOIN `plan` ON `notenbeschreibung`.`plan`=`plan`.`id`
			WHERE `notentypen`.`id`=`notenbeschreibung`.`notentyp`
				AND `fach_klasse`.`klasse`=".$plan["klasse_id"]."
				AND `fach_klasse`.`id`=`notenbeschreibung`.`fach_klasse`
				AND ((`notenbeschreibung`.`datum`>'".datum_punkt_zu_strich($plan["datum"])."' AND `notenbeschreibung`.`datum`<('".datum_punkt_zu_strich($plan["datum"])."'+ INTERVAL 14 DAY))
					OR (`plan`.`datum`>'".datum_punkt_zu_strich($plan["datum"])."' AND `plan`.`datum`<('".datum_punkt_zu_strich($plan["datum"])."'+ INTERVAL 14 DAY)))"*/
		$naechster_test=db_conn_and_sql("SELECT *
			FROM `notentypen`, `notenbeschreibung` LEFT JOIN `plan` ON `notenbeschreibung`.`plan`=`plan`.`id`
			WHERE `notentypen`.`id`=`notenbeschreibung`.`notentyp`
				AND `notenbeschreibung`.`fach_klasse`=".$plan["fach_klasse_id"]."
				AND ((`notenbeschreibung`.`datum`>'".datum_punkt_zu_strich($plan["datum"])."' AND `notenbeschreibung`.`datum`<('".datum_punkt_zu_strich($plan["datum"])."'+ INTERVAL 15 DAY))
					OR (`plan`.`datum`>'".datum_punkt_zu_strich($plan["datum"])."' AND `plan`.`datum`<('".datum_punkt_zu_strich($plan["datum"])."'+ INTERVAL 15 DAY)))");
		if (sql_num_rows($naechster_test)>0) {
			$testankuendigung='<br /><img src="./icons/test.png" alt="test" title="n&auml;chster Test" style="float: left;" />: ';
			for ($i=0;$i<sql_num_rows($naechster_test);$i++) {
				if ($i>0) $testankuendigung.='<br />';
				$testankuendigung.= datum_strich_zu_wochentag(sql_result($naechster_test,$i,"notenbeschreibung.datum").@sql_result($naechster_test,$i,"plan.datum"), "kurzform").', '.datum_strich_zu_punkt(sql_result($naechster_test,$i,"notenbeschreibung.datum")).datum_strich_zu_punkt(@sql_result($naechster_test,$i,"plan.datum")).' '.html_umlaute(sql_result($naechster_test,$i,"notentypen.kuerzel")).' '.html_umlaute(sql_result($naechster_test,$i,"notenbeschreibung.beschreibung"));
			}
		}
		if (isset($plan['hausaufgaben_vergeben'])) {
			$hausaufgabenvergabe.= '<br /><img src="./icons/hausaufgaben.png" alt="Hausaufgabe" title="Hausaufgabe" style="float: left;" />: '; foreach ($plan['hausaufgaben_vergeben'] as $value) $hausaufgabenvergabe.=hausaufgabe_zeigen($value);
		}
	
	$text=explode("\n",$text);
	
	foreach($text as $einzeltext) {
		if (preg_match("#(.*)//(.*)#is",$einzeltext)) $einzeltext=preg_replace('~//~U', '<br />', $einzeltext);
		if (preg_match("#(.*)%Hausaufgabenvergabe(.*)#is",$einzeltext)) $einzeltext=preg_replace('~%Hausaufgabenvergabe~U', $hausaufgabenvergabe, $einzeltext);
		if (preg_match("#(.*)%Hausaufgaben(.*)#is",$einzeltext)) $einzeltext=preg_replace('~%Hausaufgaben~U', $hausaufgaben, $einzeltext);
		if (preg_match("#(.*)%Testankuendigung(.*)#is",$einzeltext)) $einzeltext=preg_replace('~%Testankuendigung~U', $testankuendigung, $einzeltext);
		if (preg_match("#(.*)%Tests(.*)#is",$einzeltext)) $einzeltext=preg_replace('~%Tests~U', $tests, $einzeltext);
		if (preg_match("#(.*)%Struktur(.*)#is",$einzeltext)) $einzeltext=preg_replace('~%Struktur~U', $struktur, $einzeltext);
		if (preg_match("#(.*)%Ziele(.*)#is",$einzeltext)) $einzeltext=preg_replace('~%Ziele~U', $ziele, $einzeltext);
		if (preg_match("#(.*)%Notizen(.*)#is",$einzeltext)) $einzeltext=preg_replace('~%Notizen~U', $notizen, $einzeltext);
		
		if (substr($einzeltext,0,2)=='||') {
			
			if (preg_match("#(.*)//(.*)#is",$einzeltext)) $einzeltext=preg_replace('~<br />~U', $inhalt, $einzeltext);
			$tabelle=explode('||', substr($einzeltext,2)); array_pop($tabelle);
			$einzelstundentabelle.='<table class="einzelstunde" cellspacing="0" cellpadding="0" style="clear: both;">
				<tr>';
			foreach ($tabelle as $tabellenzelle) {
				if (preg_match("#(.*)%Inhalt(.*)#is",$tabellenzelle)) $tabellenzelle='T&auml;tigkeit';
				if (preg_match("#(.*)%Zeit(.*)#is",$tabellenzelle) or preg_match("#(.*)%minuten(.*)#is",$tabellenzelle)) $tabellenzelle='Zeit';
				$einzelstundentabelle.='<th>'.preg_replace('~%~U', '', $tabellenzelle).'</th>';
			}
			$einzelstundentabelle.='</tr>';
			
			for ($i=0;$i<count($plan['abschnitte']);$i++) {
				$zeit=''; $minuten=''; $inhalt=''; $kommentar=''; $medium=''; $sozialform=''; $handlungsmuster=''; $phase=''; $hefter='';
				
				$einzelstundentabelle.='<tr>';
				
				$zeit=$plan['abschnitte'][$i]['zeit'];
				if(!$plan['abschnitte'][$i]['pause'])
                    $minuten='('.$plan['abschnitte'][$i]['minuten'].')';
				
				switch( $plan['abschnitte'][$i]['hefter']) {
					case 1: $hefter='<img src="'.$pfad.'icons/merkteil.png" alt="Merkteil" />'; break;
					case 2: $hefter='<img src="'.$pfad.'icons/uebungsteil.png" alt="&Uuml;bungsteil" />'; break;
				}
				
				$medium=$plan['abschnitte'][$i]['medium'];
				$sozialform=$plan['abschnitte'][$i]['sozialform'];
				$phase=$plan['abschnitte'][$i]['phase'];
                if ($plan['abschnitte'][$i]['ziel']!='') $ziel='<img src="'.$pfad.'icons/ziele.png" alt="Z:" /> '.$plan['abschnitte'][$i]['ziel']; else $ziel='';
                
                include_once($pfad.'basic/localisation/methods.php');
                if ($methods[$plan['abschnitte'][$i]['methode']]['name']!='')
					$methode='M: '.$methods[$plan['abschnitte'][$i]['methode']]['name'];
				else
					$methode='';
				$handlungsmuster=$plan['abschnitte'][$i]['handlungsmuster'];
				
				$inhalt=$plan['abschnitte'][$i]['inhalt'];
				if ($plan['abschnitte'][$i]['nachbereitung']!="")
                    $kommentar='<br /><img src="./icons/kommentar.png" alt="kommentar" title="Kommentar" />: '.$plan['abschnitte'][$i]['nachbereitung'];
				
				foreach ($tabelle as $tabellenzelle) {
					$style='';
					if (preg_match("#(.*)%Inhalt(.*)#is",$tabellenzelle)) $tabellenzelle=preg_replace('~%Inhalt~U', $inhalt, $tabellenzelle);
					if (preg_match("#(.*)%Zeit(.*)#is",$tabellenzelle)) $tabellenzelle=preg_replace('~%Zeit~U', $zeit, $tabellenzelle);
					if (preg_match("#(.*)%Kommentar(.*)#is",$tabellenzelle)) $tabellenzelle=preg_replace('~%Kommentar~U', $kommentar, $tabellenzelle);
					if (preg_match("#(.*)%minuten(.*)#is",$tabellenzelle)) {$tabellenzelle=preg_replace('~%minuten~U', $minuten, $tabellenzelle); $style.='text-align: center;'; }
					if (preg_match("#(.*)%Hefter(.*)#is",$tabellenzelle)) $tabellenzelle=preg_replace('~%Hefter~U', $hefter, $tabellenzelle);
					if (preg_match("#(.*)%Sozialform(.*)#is",$tabellenzelle)) $tabellenzelle=preg_replace('~%Sozialform~U', $sozialform, $tabellenzelle);
					if (preg_match("#(.*)%Medium(.*)#is",$tabellenzelle)) $tabellenzelle=preg_replace('~%Medium~U', $medium, $tabellenzelle);
					if (preg_match("#(.*)%Phase(.*)#is",$tabellenzelle)) $tabellenzelle=preg_replace('~%Phase~U', $phase, $tabellenzelle);
					if (preg_match("#(.*)%Ziel(.*)#is",$tabellenzelle)) $tabellenzelle=preg_replace('~%Ziel~U', $ziel, $tabellenzelle);
					if (preg_match("#(.*)%Methode(.*)#is",$tabellenzelle)) $tabellenzelle=preg_replace('~%Methode~U', $methode, $tabellenzelle);
					if (preg_match("#(.*)%Handlungmuster(.*)#is",$tabellenzelle)) $tabellenzelle=preg_replace('~%Handlungmuster~U', $handlungmuster, $tabellenzelle);
					
					if($plan['abschnitte'][$i]['pause']) $style.= ' background-color: lightgray;'; 
					
					if ($style!='') $style=' style="'.$style.'"';
					
					$einzelstundentabelle.='<td'.$style.'>'.$tabellenzelle.'</td>';
				}
				$einzelstundentabelle.='</tr>';
			}
			$einzelstundentabelle.='</table>';
			$einzeltext=$einzelstundentabelle;
			
		}
		echo $einzeltext;
	}
}


function hausaufgabe_zeigen($hausaufgaben_array) {
	$ausgabe=$hausaufgaben_array["bemerkung"];
	//if ($hausaufgaben_array["zielgruppe"]!="") $ausgabe.=" / ".$hausaufgaben_array["zielgruppe"];
	if ($hausaufgaben_array["ziel"]!="") $ausgabe.=" / ".$hausaufgaben_array["ziel"];
	$ausgabe.=" (bis ".$hausaufgaben_array['wochentag']." ".$hausaufgaben_array['abgabedatum'].")";
	if(count($hausaufgaben_array["aufgaben"])>0) foreach ($hausaufgaben_array["aufgaben"] as $aufgabe) {
		$ausgabe.="<br />".$aufgabe["inhalt"];
		/*foreach($aufgabe["buch"] as $buchaufgabe) $ausgabe.=" <u>".$buchaufgabe["kuerzel"].":</u> ".$buchaufgabe["seite"]."/".$buchaufgabe["nummer"];
		$ausgabe.=" - ".$aufgabe["text"];*/
	}
	return $ausgabe;
}

function test_zeigen ($test_array) {
	$ausgabe=$test_array["notentyp_kuerzel"].' '.$test_array["beschreibung"];
	if ($test_array['kommentar']!="")
        $ausgabe.=' ('.$test_array['kommentar'].')';
	$ausgabe.=' vom: '.$test_array['datum'].' (korrigiert am '.$test_array['korrigiert'].')<br /><table><tr>';
    foreach($test_array['notenspiegel'] as $ns)
        $ausgabe.='<td>'.$ns[0].'</td>';
    $ausgabe.='</tr><tr>';
    foreach($test_array['notenspiegel'] as $ns)
        $ausgabe.='<td>'.$ns[1].'</td>';
	$ausgabe.='</tr></table>&Oslash; '.number_format($test_array['durchschnitt'], 2, ',', false);
	return $ausgabe;
}

function berichtigung_zeigen($berichtigung_array) {
	$ausgabe=$berichtigung_array["notentyp_kuerzel"].' '.$berichtigung_array["beschreibung"].' zur&uuml;ckgegeben: '.$berichtigung_array["zurueckgegeben"].' <b>';
	if ($berichtigung_array["berichtigung_gefordert"]=="0") $ausgabe.='Ber';
	if ($berichtigung_array["berichtigung_gefordert"]=="0" and $berichtigung_array["unterschrift_gefordert"]=="0") $ausgabe.='/';
	if ($berichtigung_array["unterschrift_gefordert"]=="0") $ausgabe.='Unt';
	$ausgabe.="</b>";
	return $ausgabe;
}

function startzeit ($plan) {
	$rahmen = db_conn_and_sql ( "SELECT * FROM `plan` WHERE `id`=".$plan);
  
	$datum=explode("-",sql_result ( $rahmen, 0, 'plan.datum' ));
	$zeit=explode(":",sql_result ( $rahmen, 0, 'plan.startzeit' ));
	$zeit=mktime($zeit[0], $zeit[1], $zeit[2], $datum[1], $datum[2], $datum[0]);
	return $zeit;
}

function abschnittsinhaltspositionen($inhalt,$vorgegebene_positionen) {
	$n=0;
	foreach ($vorgegebene_positionen as $value) {
		$k=0;
		for($i=0;$i<count($inhalt);$i++) {
			if ($inhalt[$i]["typ"]==substr($value,0,1)) {
				if ($k==substr($value,1)) {
					$positionen[$n]=array("typ"=>$inhalt[$i]["typ"],"typ_nummer"=>$k);
					$inhalt[$i]["text"]=""; $n++;
				}
				$k++;
			}
		}
	}
	// Rest
	for($i=0;$i<count($inhalt);$i++) {
		if ($hilf!=$inhalt[$i]["typ"]) { $k=0; $hilf=$inhalt[$i]["typ"]; }
		else $k++;
		if ($inhalt[$i]["text"]!="") {
			$positionen[$n]=array("typ"=>$inhalt[$i]["typ"],"typ_nummer"=>$k);
			$n++;
		}
	}
	return $positionen;
}

function abschnittsinhalt($abschnitt_array,$bearbeitungsmoeglichkeit,$pfad,$plan) {
	if(isset($abschnitt_array['ueberschrift'])) {
		for ($j=0; $j<count($abschnitt_array['ueberschrift']); $j++) {
			if ($plan!=0) {
				$plan_result=db_conn_and_sql("SELECT * FROM `plan` WHERE `plan`.`id`=".$plan);
				$ueberschriften_result=db_conn_and_sql("SELECT * FROM `plan`, `abschnittsplanung`, `ueberschrift`
					WHERE `plan`.`schuljahr`=".sql_result($plan_result,0,"plan.schuljahr")."
						AND `plan`.`fach_klasse`=".sql_result($plan_result,0,"plan.fach_klasse")."
						AND `ueberschrift`.`abschnitt`=`abschnittsplanung`.`abschnitt`
						AND `abschnittsplanung`.`plan`=`plan`.`id`
					ORDER BY `plan`.`datum`, `plan`.`startzeit`, `abschnittsplanung`.`position`, `ueberschrift`.`ebene`");
				
				for ($i=0;$i<5;$i++) $ebene[$i]=0;
				$ebene[0]=0; // ist eigentlich nicht noetig
				$i=0;
				$abbruch=1;
				$bisherige_ueberschriften=''; // verhindert doppeltes Vorkommen und nochmalige Zaehlung
				while($abbruch and $i<200) {
					if ($bisherige_ueberschriften[sql_result($ueberschriften_result,$i,"ueberschrift.id")]!=1) {
						$ebene[sql_result($ueberschriften_result,$i,"ueberschrift.ebene")-1]++;
						$typ[sql_result($ueberschriften_result,$i,"ueberschrift.ebene")]=sql_result($ueberschriften_result,$i,"ueberschrift.typ");
						for ($n=sql_result($ueberschriften_result,$i,"ueberschrift.ebene");$n<=5;$n++) $ebene[$n]=0; // alle ebenen groesser als jetzt werden zurueckgesetzt
						//echo "e[".(sql_result($ueberschriften_result,$i,"ueberschrift.ebene")-1)."]=".$ebene[sql_result($ueberschriften_result,$i,"ueberschrift.ebene")-1]." t[".sql_result($ueberschriften_result,$i,"ueberschrift.ebene")."]=".$typ[sql_result($ueberschriften_result,$i,"ueberschrift.ebene")]." Ebenen: ".$ebene[0].$ebene[1].$ebene[2].$ebene[3]."<br />";
					}
					if(sql_result($ueberschriften_result,$i,"ueberschrift.id")==$abschnitt_array['ueberschrift'][$j]['id']) $abbruch=0;
					$bisherige_ueberschriften[sql_result($ueberschriften_result,$i,"ueberschrift.id")]=1;
					$i++;
					if($abbruch==0 and sql_result($ueberschriften_result,$i,"ueberschrift.id")==$abschnitt_array['ueberschrift'][$j]['id']) $abbruch=1;
				}
				/*$ebene="";
				for ($k=0; $k<$abschnitt_array['ueberschrift'][$j]['ebene']; $k++) $ebene.="1.";*/
				/*$ebene=$abschnitt_array['ueberschrift'][$j]['nummer'];*/
				$bearbeitung=""; $abschnitt_array['ueberschrift'][$j]['nummer']="";
				for($n=0;$n<$abschnitt_array['ueberschrift'][$j]['ebene'];$n++)
					switch ($typ[$n]) {
						case "a": $abschnitt_array['ueberschrift'][$j]['nummer'].=chr(96+$ebene[$n])."."; break;
						case "A": $abschnitt_array['ueberschrift'][$j]['nummer'].=chr(64+$ebene[$n])."."; break;
						case "I": $abschnitt_array['ueberschrift'][$j]['nummer'].=arab2roman($ebene[$n])."."; break;
						case "-": $abschnitt_array['ueberschrift'][$j]['nummer']=""; break;
						default: $abschnitt_array['ueberschrift'][$j]['nummer'].=$ebene[$n]."."; break; }
			}
			$inhalt[]=array("text"=>'<span class="ueberschrift" onclick="this.className=\'ueberschrift_sel\';">'.$abschnitt_array['ueberschrift'][$j]['nummer']." ".$abschnitt_array['ueberschrift'][$j]['text'].'</span>',"typ"=>'u',"typ_als_zahl"=>1,"typ_id"=>$abschnitt_array['ueberschrift'][$j]['id']);
		}
	}
	    if(isset($abschnitt_array['test'])) {
			for ($j=0; $j<count($abschnitt_array['test']);$j++) {
				$bearbeitung="";
				if ($abschnitt_array['test'][$j]['url_vorhanden']) $url=$abschnitt_array['test'][$j]['url'];
				else {
					$url=$pfad.'formular/test_bearbeiten.php?welcher='.$abschnitt_array['test'][$j]['id'];
					if ($plan!=0) {$plan_result=db_conn_and_sql("SELECT * FROM `plan` WHERE `plan`.`id`=".$plan); $url=$pfad.'test_druckansicht.php?welcher='.$abschnitt_array['test'][$j]['id'].'&amp;datum='.datum_strich_zu_punkt(sql_result($plan_result,0,"plan.datum"));}
				}
				$testname = $abschnitt_array['test'][$j]['themen'];
				if ($abschnitt_array['test'][$j]['titel']!="")
					$testname = $abschnitt_array['test'][$j]['titel'];
				$inhalt[]=array("text"=>'<img src="'.$pfad.'icons/test.png" alt="test" /> <a href="'.$url.'" onclick="fenster(this.href, \'Test bearbeiten\'); return false;">'.$abschnitt_array['test'][$j]['notentyp'].' - '.$testname.'</a> <span class="testzeit" onclick="this.className=\'testzeit_sel\';">('.$abschnitt_array['test'][$j]['bearbeitungszeit'].' min)</span>',"typ"=>'t',"typ_als_zahl"=>2,"typ_id"=>$abschnitt_array['test'][$j]['id']);
			}
	    }
	    if(isset($abschnitt_array['aufgabe'])) {
			for ($j=0; $j<count($abschnitt_array['aufgabe']);$j++) {
				$text='';
                // Zeilenumbruch bei mehr als einer Aufgabe
				if ($j>=1) $text.="<br />";
                // bei Buchaufgaben wird schon ein Icon angezeigt - wuerde sonst zu viel Icons geben
                if (!isset($abschnitt_array['aufgabe'][$j]['buch'])) $text.='<img src="'.$pfad.'icons/aufgaben.png" alt="aufgabe" /> ';
				if ($abschnitt_array['aufgabe'][$j]['beispiel']) $text.='<span class="aufgabe_bsp">Bsp:</span> '; else $text.='<span class="aufgabe_bsp">&Uuml;:</span> ';
				if (isset($abschnitt_array['aufgabe'][$j]['buch'])) $text.='<img src="'.$pfad.'icons/buch.png" alt="buch" /> <span class="lehrbuch" onclick="this.className=\'lehrbuch_sel\';">LB: '.$abschnitt_array['aufgabe'][$j]['buch'][0]['kuerzel']." S. ".$abschnitt_array['aufgabe'][$j]['buch'][0]['seite']." Nr. ".$abschnitt_array['aufgabe'][$j]['buch'][0]['nummer']."</span><br />";
				//mehr Aufgaben bei Buch? mit foreach
				if ($abschnitt_array['aufgabe'][$j]['text']!="" or count($abschnitt_array['aufgabe'][$j]['bilder'])>0)
                    $text.= '<span class="aufgabentext" onclick="this.className=\'aufgabentext_sel\';">'.syntax_zu_html($abschnitt_array['aufgabe'][$j]['text'],$abschnitt_array['aufgabe'][$j]['teilaufgaben_nebeneinander'], 0, $pfad, 'A').'</span>';
				/*if (isset($abschnitt_array['aufgabe'][$j]['bilder'])) foreach($abschnitt_array['aufgabe'][$j]['bilder'] as $bild) $text.='<br /><a href="'.$pfad.$bild["url"].'" class="bild_im_plan"><img src="'.$pfad.$bild["url"].'" alt="'.$bild["alt"].'" title="'.$bild["alt"].'" style="width: '.($bild["breite"]*30).'px;" /></a>'; //'position' FEHLT NOCH*/
				if ($abschnitt_array['aufgabe'][$j]['loesung']!="") $text.= '<br style="clear: both;" /><span class="aufgabe_lsg">Lsg:</span> <span class="loesungstext" onclick="this.className=\'loesungstext_sel\';">'.syntax_zu_html($abschnitt_array['aufgabe'][$j]['loesung'],$abschnitt_array['aufgabe'][$j]['teilaufgaben_nebeneinander'],0,$pfad,'A').'</span>';
				if ($abschnitt_array['aufgabe'][$j]['bemerkung']!="") $text.= ' <span class="aufgabenbemerkung">('.$abschnitt_array['aufgabe'][$j]['bemerkung'].')</span>';
				$inhalt[]=array("text"=>$text,"typ"=>'a',"typ_als_zahl"=>3,"typ_id"=>$abschnitt_array['aufgabe'][$j]['id']);
			}
	    }
	    /*if(isset($abschnitt_array['link'])) {
			for ($j=0; $j<count($abschnitt_array['link']);$j++) {
				$text='';
				if ($j>=1) $text.="<br />";
				switch ($abschnitt_array['link'][$j]['typ']) {
					case 1: $text.= "<b>AB:</b> "; break;
					case 2: $text.= "<b>Folie:</b> "; break;
					case 3: $text.= "<b>Link:</b> "; break;
					//default: $ansicht['inhalt'].= "Link"; break;
				}
				$text.= $abschnitt_array['link'][$j]['bemerkung'];
				$text.= ' <a href="'.$abschnitt_array['link'][$j]['url'].'">'.$abschnitt_array['link'][$j]['beschreibung'].'</a>';
				$inhalt[]=array("text"=>$text,"typ"=>'l',"typ_als_zahl"=>4,"typ_id"=>$abschnitt_array['link'][$j]['id']);
			}
	    }*/
	    /*if(isset($abschnitt_array['grafik'])) {
			for ($j=0; $j<count($abschnitt_array['grafik']);$j++) {
				$text='';
				$text.= '<a href="'.$pfad.$abschnitt_array['grafik'][$j]['url'].'" class="bild_im_plan"><img src="'.$pfad.$abschnitt_array['grafik'][$j]['url'].'" style="width: '.(($abschnitt_array['grafik'][$j]['breite'])/15*100).'%;';
				if ($abschnitt_array['grafik'][$j]['position']==1) $text.=' float: left;';
				if ($abschnitt_array['grafik'][$j]['position']==2) $text.=' float: right;';
				$text.= '" alt="'.$abschnitt_array['grafik'][$j]['alt'].'" title="'.$abschnitt_array['grafik'][$j]['alt'].'" /></a>';
				$inhalt[]=array("text"=>$text,"typ"=>'g',"typ_als_zahl"=>5,"typ_id"=>$abschnitt_array['grafik'][$j]['id']);
			}
	    }*/
	    if(isset($abschnitt_array['material'])) {
			for ($j=0; $j<count($abschnitt_array['material']);$j++) {
				$text='';
				$text.= '<img src="'.$pfad.'icons/sonstiges_material.png" alt="material" /> <b>Material:</b> '.$abschnitt_array['material'][$j]['name'];
				$inhalt[]=array("text"=>$text,"typ"=>'m',"typ_als_zahl"=>6,"typ_id"=>$abschnitt_array['material'][$j]['id']);
			}
	    }
	    if(isset($abschnitt_array['sonstiges'])) {
			for ($j=0; $j<count($abschnitt_array['sonstiges']);$j++) {
				$text='';
                $text.= '<span class="sonstiges" onclick="this.className=\'sonstiges_sel\';">'.syntax_zu_html($abschnitt_array['sonstiges'][$j]['inhalt'],1,$bearbeitungsmoeglichkeit,$pfad,'A').'</span>';
				/*switch ($abschnitt_array['sonstiges'][$j]['typ']) {
					case 1: $text.= '<span class="erlaeuterung" onclick="this.className=\'erlaeuterung_sel\';"><span class="erlaeuterung_vorher">Erl&auml;uterung:</span> '.syntax_zu_html($abschnitt_array['sonstiges'][$j]['inhalt'],1).'</span>'; break;
					case 2: $text.= '<span class="diskussion" onclick="this.className=\'diskussion_sel\';"><span class="diskussion_vorher">Diskussion:</span> '.syntax_zu_html($abschnitt_array['sonstiges'][$j]['inhalt'],1).'</span>'; break;
					case 3: $text.= '<span class="merke" onclick="this.className=\'merke_sel\';"><span class="merke_vorher">Merke:</span> '.syntax_zu_html($abschnitt_array['sonstiges'][$j]['inhalt'],1).'</span>'; break;
					case 4: $text.= '<span class="definition" onclick="this.className=\'definition_sel\';"><span class="definition_vorher">Def:</span> '.syntax_zu_html($abschnitt_array['sonstiges'][$j]['inhalt'],1).'</span>'; break;
					case 5: $text.= '<span class="umrandet" onclick="this.className=\'umrandet_sel\';"><div style="border: 2px solid orange; padding: 2px; float:left; margin-right:20px;">'.syntax_zu_html($abschnitt_array['sonstiges'][$j]['inhalt'],1).'</div><br style="clear: both;" /></span>';break;
					case 6: $text.= '<pre><code class="feste_zeichenbreite" onclick="this.className=\'feste_zeichenbreite_sel\';">'.$abschnitt_array['sonstiges'][$j]['inhalt'].'</code></pre>'; break;
					case 7: $text.= '<span class="sonstiges" onclick="this.className=\'sonstiges_sel\';">'.syntax_zu_html($abschnitt_array['sonstiges'][$j]['inhalt'],1,0,$pfad,'A').'</span>'; break;
					case 8: $hilf=explode(":",$abschnitt_array['sonstiges'][$j]['inhalt']);
						$text.= '<span class="beschreibung" onclick="this.className=\'beschreibung_sel\';"><span class="beschreibung_vorher">'.$hilf[0].":</span>".substr(syntax_zu_html($abschnitt_array['sonstiges'][$j]['inhalt'],1),strlen($hilf[0])+1).'</span>'; break;
				}*/
				$inhalt[]=array("text"=>$text,"typ"=>'s',"typ_als_zahl"=>7,"typ_id"=>$abschnitt_array['sonstiges'][$j]['id']);
			}
	    }
	// Reihenfolge
	$positionen=abschnittsinhaltspositionen($inhalt,explode(",", $abschnitt_array["positionen"]));
	$n=0;
	$reihenfolge="";
    if (count($positionen)>0) {
	foreach ($positionen as $value)
        $reihenfolge[]=$value["typ"].$value["typ_nummer"];
	foreach ($positionen as $value) {
		$k=0;
		for($i=0;$i<count($inhalt);$i++)
			if ($inhalt[$i]["typ"]==$value["typ"]) {
				if ($k==$value["typ_nummer"]) {
					$return_inhalt.=$inhalt[$i]["text"];
					if ($bearbeitungsmoeglichkeit=="bearbeiten") {
						$href='formular/material_bearb.php?typ='.$inhalt[$i]["typ_als_zahl"].'&amp;id='.$inhalt[$i]["typ_id"];
						
						// Extrawurst Test, Grafik, Link und Aufgabe
						if ($inhalt[$i]["typ_als_zahl"]==2) $href='formular/test_bearbeiten.php?welcher='.$inhalt[$i]["typ_id"];
						if ($inhalt[$i]["typ_als_zahl"]==3) $href='formular/aufgabe_bearb.php?welche='.$inhalt[$i]["typ_id"];
						if ($inhalt[$i]["typ_als_zahl"]==4) $href='formular/link_bearbeiten.php?link_id='.$inhalt[$i]["typ_id"];
						if ($inhalt[$i]["typ_als_zahl"]==5) $href='formular/grafik_bearbeiten.php?bild_id='.$inhalt[$i]["typ_id"];
						
						// Extrawurst Test:
						if ($inhalt[$i]["typ_als_zahl"]!=8){
							if ($inhalt[$i]["typ_als_zahl"]==5) $return_inhalt.=' <a href="'.$pfad.'formular/grafik_groesse.php?grafik='.$inhalt[$i]["typ_id"].'&amp;abschnitt='.$abschnitt_array["id"].'" onclick="javascript:window.open(this.href, \'Material &auml;ndern\', \'width=700,height=600,left=100,top=200,resizable=yes,scrollbars=yes\'); return false;" title="Gr&ouml;&szlig;e und Position der Grafik &auml;ndern" class="icon"><img src="'.$pfad.'icons/groesse_aendern.png" alt="groesse_aendern" /></a>';
							$return_inhalt.=' <a href="'.$pfad.$href.'" onclick="javascript:window.open(this.href, \'Material &auml;ndern\', \'width=700,height=600,left=100,top=200,resizable=yes,scrollbars=yes\'); return false;" title="Inhalt bearbeiten" class="icon"><img src="'.$pfad.'icons/edit.png" alt="bearbeiten" /></a>';
						}
						//else $return_inhalt.=' <a href="'.$pfad.'index.php?tab=material&amp;auswahl=test&amp;welcher='.$inhalt[$i]["typ_id"].'" title="Test bearbeiten" class="icon"><img src="'.$pfad.'icons/edit.png" alt="bearbeiten" /></a>';
						
						if ($n!=0) {
							$neue_reihenfolge=$reihenfolge; $hilf=$neue_reihenfolge[$n]; $neue_reihenfolge[$n]=$neue_reihenfolge[$n-1]; $neue_reihenfolge[$n-1]=$hilf;
							$return_inhalt.=' <a href="'.$pfad.'formular/abschnitt_i_p.php?abschnitt='.$abschnitt_array["id"].'&amp;aktion=hoch&amp;positionen='.implode(",",$neue_reihenfolge).'&amp;lehrplan='.$_GET['lehrplan'].'&amp;klasse='.$_GET['klasse'].'&amp;block='.$_GET['block'].'&amp;fk='.$_GET['fk'].'&amp;plan='.$_GET['plan'].'" onclick="fenster(this.href,\'titel\'); return false;" class="icon"><img src="'.$pfad.'icons/hoch.png" alt="hoch" title="hochschieben" /></a>';
						}
						if ($n!=(count($inhalt)-1)) {
							$neue_reihenfolge=$reihenfolge; $hilf=$neue_reihenfolge[$n]; $neue_reihenfolge[$n]=$neue_reihenfolge[$n+1]; $neue_reihenfolge[$n+1]=$hilf;
							$return_inhalt.=' <a href="'.$pfad.'formular/abschnitt_i_p.php?abschnitt='.$abschnitt_array["id"].'&amp;aktion=runter&amp;positionen='.implode(",",$neue_reihenfolge).'&amp;lehrplan='.$_GET['lehrplan'].'&amp;klasse='.$_GET['klasse'].'&amp;block='.$_GET['block'].'&amp;fk='.$_GET['fk'].'&amp;plan='.$_GET['plan'].'" onclick="fenster(this.href,\'titel\'); return false;" class="icon"><img src="'.$pfad.'icons/runter.png" alt="hoch" title="runterschieben" /></a>';
						}
						if (count($inhalt)>1) $return_inhalt.=' <a href="'.$pfad.'formular/abschnitt_i_p.php?abschnitt='.$abschnitt_array["id"].'&amp;typ='.$inhalt[$i]["typ"].'&amp;aktion=entfernen&amp;id='.$inhalt[$i]["typ_id"].'&amp;lehrplan='.$_GET['lehrplan'].'&amp;klasse='.$_GET['klasse'].'&amp;block='.$_GET['block'].'&amp;fk='.$_GET['fk'].'&amp;plan='.$_GET['plan'].'" onclick="fenster(this.href,\'titel\'); return false;" class="icon"><img src="'.$pfad.'icons/remove.png" alt="hoch" title="entfernen" /></a>';
					}
					$return_inhalt.='<br />';
					$inhalt[$i]["text"]=""; $n++;
				}
				$k++;
			}
    }
	}
	return $return_inhalt;
}

function planelemente ($plan, $bearbeitungsmoeglichkeit,$pfad) {
    // ist das nicht analog zu function einzelstundenansicht?
	$db=new db;
	$zeit=startzeit($plan);
	$jahr=$db->aktuelles_jahr(); // fuer spaeteransicht verbessern
	$subject_classes = new subject_classes($jahr);
	$wochennamen_kurz=array(0=>'So', 1=>'Mo', 2=>'Di', 3=>'Mi', 4=>'Do', 5=>'Fr', 6=>'Sa');
	
	$uebrig=0;
	$pause=$zeit+45*60;
	$rahmen = db_conn_and_sql ( "SELECT * FROM `plan`,`fach_klasse`, `klasse`,`faecher`
		WHERE `fach_klasse`.`fach`=`faecher`.`id`
			AND `plan`.`fach_klasse`=`fach_klasse`.`id`
			AND `fach_klasse`.`klasse`=`klasse`.`id`
			AND `fach_klasse`.`user`=".$_SESSION['user_id']."
			AND `plan`.`id`=".$plan);
	$ansicht['fach_klasse_id']=sql_result($rahmen, 0, 'fach_klasse.id');
	// TODO name der Fachklasse aus $subject_classes lesen (innerhalb der Funktion aber schwierig)
	$ansicht['fachklasse_gruppenname']=html_umlaute(sql_result($rahmen, 0, 'fach_klasse.gruppen_name'));
	$ansicht['klasse_id']=sql_result($rahmen, 0, 'klasse.id');
	$ansicht['fach']=html_umlaute(sql_result($rahmen, 0, 'faecher.kuerzel'));
	$ansicht['fach_lang']=html_umlaute(sql_result($rahmen, 0, 'faecher.name'));
	$ansicht['fachklassen_name']=$subject_classes->nach_ids[sql_result($rahmen,0,"fach_klasse.id")]["name"];
	$ansicht['klassenstufe']=($jahr-sql_result($rahmen, 0, 'klasse.einschuljahr')+1);
	$ansicht['klasse_endung']=sql_result($rahmen, 0, 'klasse.endung');
	$ansicht['datum']=datum_strich_zu_punkt(sql_result($rahmen, 0, 'plan.datum'));
	$ansicht['wochentag']=$wochennamen_kurz[date("w",mktime(0,0,0,substr(sql_result($rahmen, 0, 'plan.datum'),5,2),substr(sql_result($rahmen, 0, 'plan.datum'),8,2),substr(sql_result($rahmen, 0, 'plan.datum'),0,4)))];
	$ansicht['struktur']=html_umlaute(sql_result($rahmen, 0, 'plan.struktur'));
	$ansicht['ziele']=html_umlaute(sql_result($rahmen, 0, 'plan.zusatzziele'));
	$ansicht['notizen']=html_umlaute(sql_result($rahmen, 0, 'plan.notizen'));
	$ansicht['schuljahr']=sql_result($rahmen, 0, 'plan.schuljahr');
	$ansicht['ohne_pause']=sql_result($rahmen, 0, 'plan.ohne_pause');
	
	// LEFT JOIN `gabe` ON `hausaufgabe`.`id`=`hausaufgabe_abschnitt`.`hausaufgabe`
	$hausaufgaben=db_conn_and_sql("SELECT * FROM `hausaufgabe`, `plan`
		WHERE `hausaufgabe`.`plan`=`plan`.`id`
			AND `hausaufgabe`.`kontrolliert`<>1
			AND `plan`.`fach_klasse`=".sql_result($rahmen, 0, 'plan.fach_klasse'));
	for ($k=0;$k<@sql_num_rows($hausaufgaben);$k++) {
		/*echo sql_result($rahmen, 0, 'plan.datum').">=".sql_result($hausaufgaben, $k, 'plan_hausaufgabe.abgabedatum')."
			or ".sql_result($rahmen, 0, 'plan.id')."==".sql_result($hausaufgaben, $k, 'plan_hausaufgabe.plan');*/
		if (sql_result($rahmen, 0, 'plan.datum')>=sql_result($hausaufgaben, $k, 'hausaufgabe.abgabedatum')
			or sql_result($rahmen, 0, 'plan.id')==sql_result($hausaufgaben, $k, 'hausaufgabe.plan')) {
			$aufgaben=db_conn_and_sql("SELECT * FROM `hausaufgabe_abschnitt` WHERE `hausaufgabe_abschnitt`.`hausaufgabe`=".sql_result($hausaufgaben, $k, 'hausaufgabe.id'));
			$hilf_array=array(
				"id"=>sql_result($hausaufgaben, $k, 'hausaufgabe.id'),
				"abgabedatum"=>datum_strich_zu_punkt(sql_result($hausaufgaben, $k, 'hausaufgabe.abgabedatum')),
				"wochentag"=>datum_strich_zu_wochentag(sql_result($hausaufgaben, $k, 'hausaufgabe.abgabedatum'),'kurzform'),
				//"kommentar"=>sql_result($hausaufgaben, $k, 'hausaufgabe.bemerkung'),
				//"zielgruppe"=>html_umlaute(sql_result($hausaufgaben, $k, 'hausaufgabe.zielgruppe')),
				"bemerkung"=>html_umlaute(sql_result($hausaufgaben, $k, 'hausaufgabe.bemerkung')),
				"status"=>sql_result($hausaufgaben, $k, 'hausaufgabe.kontrolliert'),
				"ziel"=>html_umlaute(sql_result($hausaufgaben, $k, 'hausaufgabe.ziel')));
			for($n=0;$n<sql_num_rows($aufgaben);$n++) $hilf_array["aufgaben"][]=$db->abschnitt(sql_result($aufgaben,$n,"hausaufgabe_abschnitt.abschnitt"));
			if (sql_result($rahmen, 0, 'plan.id')==sql_result($hausaufgaben, $k, 'hausaufgabe.plan')) $ansicht['hausaufgaben_vergeben'][]=$hilf_array;
			else $ansicht['hausaufgaben_kontrolle'][]=$hilf_array;
		}
	}

	/* $test_rueckgabe=db_conn_and_sql("SELECT * FROM `notentypen`, `fach_klasse` AS `fk1`, `fach_klasse` AS `fk2`, `notenbeschreibung` LEFT JOIN `plan` ON `notenbeschreibung`.`plan`=`plan`.`id`
		WHERE `notenbeschreibung`.`notentyp`=`notentypen`.`id`
			AND `notenbeschreibung`.`korrigiert`<='".sql_result($rahmen, 0, 'plan.datum')."'
			AND (`notenbeschreibung`.`zurueckgegeben` IS NULL OR `notenbeschreibung`.`zurueckgegeben`>'".sql_result($rahmen, 0, 'plan.datum')."')
			AND `notenbeschreibung`.`korrigiert` IS NOT NULL
			AND `notenbeschreibung`.`fach_klasse`=`fk1`.`id`
			AND `fk2`.`id`=".sql_result($rahmen, 0, 'plan.fach_klasse')."
			AND `fk1`.`klasse`=`fk2`.`klasse`
		ORDER BY `notenbeschreibung`.`korrigiert`");*/ // wenn auch die Tests der anderen Faecher angezeigt werden sollen, dann geht diese version
	$test_rueckgabe=db_conn_and_sql("SELECT * FROM `notentypen`, `notenbeschreibung` LEFT JOIN `plan` ON `notenbeschreibung`.`plan`=`plan`.`id`
		WHERE `notenbeschreibung`.`notentyp`=`notentypen`.`id`
			AND `notenbeschreibung`.`korrigiert`<='".sql_result($rahmen, 0, 'plan.datum')."'
			AND (`notenbeschreibung`.`zurueckgegeben` IS NULL OR `notenbeschreibung`.`zurueckgegeben`>'".sql_result($rahmen, 0, 'plan.datum')."')
			AND `notenbeschreibung`.`korrigiert` IS NOT NULL
			AND `notenbeschreibung`.`fach_klasse`=".sql_result($rahmen, 0, 'plan.fach_klasse')."
		ORDER BY `notenbeschreibung`.`korrigiert`");
	
	for ($k=0;$k<@sql_num_rows($test_rueckgabe);$k++) {
        /*$test_bewertung=db_conn_and_sql("SELECT * FROM bewertung_note
            WHERE bewertung_note.bewertungstabelle=".sql_result($test_rueckgabe, $k, 'notenbeschreibung.bewertungstabelle')."
            ORDER BY bewertung_note.prozent_bis");
        $punkte_bis benoetigt auch noch test - keine Lust mehr - spaeter mal */
        $test_durchschnitt=db_conn_and_sql("SELECT * FROM noten
            WHERE noten.beschreibung=".sql_result($test_rueckgabe, $k, 'notenbeschreibung.id')."
            ORDER BY noten.wert");
        $klassenspiegel='';
        $durchschnitt=0;
        $einzelnote_i=0;
        for ($note_i=sql_result($test_durchschnitt,0,'noten.wert'); $note_i<=sql_result($test_durchschnitt,sql_num_rows($test_durchschnitt)-1,'noten.wert'); $note_i++) {
            $anzahl_schueler_die_diesen_notenwert_haben=0;
            while ($einzelnote_i<sql_num_rows($test_durchschnitt) && sql_result($test_durchschnitt,$einzelnote_i,'noten.wert')==$note_i) {
                $anzahl_schueler_die_diesen_notenwert_haben++;
                $durchschnitt+=$note_i;
                $einzelnote_i++;
            }
            $klassenspiegel[]=array($note_i, $anzahl_schueler_die_diesen_notenwert_haben);
        }
		$ansicht['test_rueckgabe'][]=array(
			"id"=>sql_result($test_rueckgabe, $k, 'notenbeschreibung.id'),
			"korrigiert"=>datum_strich_zu_punkt(sql_result($test_rueckgabe, $k, 'notenbeschreibung.korrigiert')),
			"zurueckgegeben"=>datum_strich_zu_punkt(sql_result($test_rueckgabe, $k, 'notenbeschreibung.zurueckgegeben')),
			"datum"=>datum_strich_zu_punkt(sql_result($test_rueckgabe, $k, 'notenbeschreibung.datum').sql_result($test_rueckgabe, $k, 'plan.datum')),
			"beschreibung"=>html_umlaute(sql_result($test_rueckgabe, $k, 'notenbeschreibung.beschreibung')),
			"kommentar"=>html_umlaute(sql_result($test_rueckgabe, $k, 'notenbeschreibung.kommentar')),
			"notentyp_kuerzel"=>html_umlaute(sql_result($test_rueckgabe, $k, 'notentypen.kuerzel')),
			"berichtigung_gefordert"=>sql_result($test_rueckgabe, $k, 'notenbeschreibung.berichtigung'),
			"unterschrift_gefordert"=>sql_result($test_rueckgabe, $k, 'notenbeschreibung.unterschrift'),
            "durchschnitt"=>$durchschnitt/sql_num_rows($test_durchschnitt),
            "notenspiegel"=>$klassenspiegel);
	}

	$berichtigung=db_conn_and_sql("SELECT * FROM `notentypen`, `notenbeschreibung` LEFT JOIN `plan` ON `notenbeschreibung`.`plan`=`plan`.`id`
		WHERE `notenbeschreibung`.`notentyp`=`notentypen`.`id`
			AND `notenbeschreibung`.`zurueckgegeben` IS NOT NULL
			AND `notenbeschreibung`.`zurueckgegeben`<'".sql_result($rahmen, 0, 'plan.datum')."'
			AND (`notenbeschreibung`.`berichtigung`=0 OR `notenbeschreibung`.`unterschrift`=0)
			AND `notenbeschreibung`.`fach_klasse`=".sql_result($rahmen, 0, 'plan.fach_klasse')."
		ORDER BY `notenbeschreibung`.`korrigiert`");
	for ($k=0;$k<sql_num_rows($berichtigung);$k++) {
		$ansicht['berichtigung_kontrolle'][]=array(
			"id"=>sql_result($berichtigung, $k, 'notenbeschreibung.id'),
			"korrigiert"=>datum_strich_zu_punkt(sql_result($berichtigung, $k, 'notenbeschreibung.korrigiert')),
			"zurueckgegeben"=>datum_strich_zu_punkt(sql_result($berichtigung, $k, 'notenbeschreibung.zurueckgegeben')),
			"datum"=>datum_strich_zu_punkt(sql_result($berichtigung, $k, 'notenbeschreibung.datum')),
			"beschreibung"=>html_umlaute(sql_result($berichtigung, $k, 'notenbeschreibung.beschreibung')),
			"kommentar"=>html_umlaute(sql_result($berichtigung, $k, 'notenbeschreibung.kommentar')),
			"notentyp_kuerzel"=>html_umlaute(sql_result($berichtigung, $k, 'notentypen.kuerzel')),
			"berichtigung_gefordert"=>sql_result($berichtigung, $k, 'notenbeschreibung.berichtigung'),
			"unterschrift_gefordert"=>sql_result($berichtigung, $k, 'notenbeschreibung.unterschrift'));
		// Achtung: am 05.01.2013 von count($ansicht["berichtigung_kontrolle"]) auf count($ansicht["berichtigung_kontrolle"])-1 geaendert - warum ging das so lang gut?
		if (sql_result($berichtigung, $k, 'plan.datum')!='')
			$ansicht["berichtigung_kontrolle"][count($ansicht["berichtigung_kontrolle"])-1]["datum"]=datum_strich_zu_punkt(sql_result($berichtigung, $k, 'plan.datum'));
		// if (sql_result($berichtigung, $k, 'notenbeschreibung.berichtigung')==NULL) $ansicht['berichtigung_kontrolle'][count($ansicht['berichtigung_kontrolle'])-1]["berichtigung_gefordert"]="NULL";
	}
	
	$result = db_conn_and_sql ( "SELECT * FROM `abschnittsplanung` WHERE `plan`=".$plan." ORDER BY `abschnittsplanung`.`position`");
	$pausenzaehler=0;
	for($i=0;$i<sql_num_rows($result);$i++) {
		if (sql_result($result,$i,'abschnittsplanung.abschnitt')>0)
            $ansicht['abschnitte'][$i+$pausenzaehler]=$db->abschnitt(sql_result($result,$i,'abschnittsplanung.abschnitt'));
		$ansicht['abschnitte'][$i+$pausenzaehler]['zeit']=date("H:i",$zeit);
		$ansicht['abschnitte'][$i+$pausenzaehler]['php_zeit']=$zeit;
		// wenn abschnittsplanung.minuten angegeben, dann ist das prioritativ hoeher (ist aber immer angegeben)
		if (sql_result($result,$i,'abschnittsplanung.minuten')>0)
            $ansicht['abschnitte'][$i+$pausenzaehler]['minuten']=sql_result($result,$i,'abschnittsplanung.minuten');
		
		// TODO Umsetzen von array...
		$medium=db_conn_and_sql("SELECT `medium`.* FROM `abschnitt`,`medium` WHERE `abschnitt`.`medium`=`medium`.`id` AND `abschnitt`.`id`=".sql_result($result,$i,'abschnittsplanung.abschnitt'));
		$soz_form=db_conn_and_sql("SELECT `sozialform`.* FROM `abschnitt`,`sozialform` WHERE `abschnitt`.`sozialform`=`sozialform`.`id` AND `abschnitt`.`id`=".sql_result($result,$i,'abschnittsplanung.abschnitt'));
		$handlungsmuster=db_conn_and_sql("SELECT `handlungsmuster`.* FROM `abschnitt`,`handlungsmuster` WHERE `abschnitt`.`handlungsmuster`=`handlungsmuster`.`id` AND `abschnitt`.`id`=".sql_result($result,$i,'abschnittsplanung.abschnitt'));
		$phase=db_conn_and_sql("SELECT `phase`.* FROM `abschnittsplanung`,`phase` WHERE `abschnittsplanung`.`phase`=`phase`.`id` AND `abschnittsplanung`.`abschnitt`=".sql_result($result,$i,'abschnittsplanung.abschnitt'));
		
        if (sql_result($result,$i,"abschnittsplanung.abschnitt")==0)
            $inhalt_des_abschnitts=syntax_zu_html(html_umlaute(sql_result($result,$i,"abschnittsplanung.inhalt")),1,$bearbeitungsmoeglichkeit,$pfad,'A').'<br />';
        else
            $inhalt_des_abschnitts=abschnittsinhalt($ansicht['abschnitte'][$i+$pausenzaehler],$bearbeitungsmoeglichkeit,$pfad,$plan);
		$ansicht['abschnitte'][$i+$pausenzaehler]['inhalt']=$inhalt_des_abschnitts.' <b>'.html_umlaute(sql_result($result,$i,"abschnittsplanung.bemerkung")).'</b>';
		
        if (sql_result($result,$i,"abschnittsplanung.abschnitt")>0) {
            //$ansicht['abschnitte'][$i+$pausenzaehler]['methode']=html_umlaute(sql_result ($result,$i,'abschnitt.methode'));
            $ansicht['abschnitte'][$i+$pausenzaehler]['medium']=html_umlaute(sql_result ($medium,0,'medium.kuerzel'));
            $ansicht['abschnitte'][$i+$pausenzaehler]['sozialform']=html_umlaute(sql_result ( $soz_form, 0, 'sozialform.kuerzel' ));
            $ansicht['abschnitte'][$i+$pausenzaehler]['handlungsmuster']=html_umlaute(@sql_result ($handlungsmuster,0,'handlungsmuster.kuerzel'));
            $ansicht['abschnitte'][$i+$pausenzaehler]['phase']=html_umlaute(sql_result ($phase,0,'phase.kuerzel'));
        }
		
		//folgendes Riesenstueck nur fuer Pausenberechnung
		if (((($zeit+60*$ansicht['abschnitte'][$i+$pausenzaehler]['minuten'])-$pause)/60)>0 and !$ansicht['ohne_pause']) {
			// was ist bei ((($zeit+60*$ansicht['abschnitte'][$i+$pausenzaehler]['minuten'])-$pause)/60)==0?????????
			$uebrig=((($zeit+60*$ansicht['abschnitte'][$i+$pausenzaehler]['minuten'])-$pause)/60);
			
			$stundenplan=db_conn_and_sql("SELECT * FROM `stundenzeiten`,`fach_klasse`,`klasse`
															WHERE `fach_klasse`.`id`=".sql_result ( $rahmen, 0, 'plan.fach_klasse' )."
																AND `fach_klasse`.`klasse`=`klasse`.`id`
																AND `klasse`.`schule`=`stundenzeiten`.`schule`
															ORDER BY `stundenzeiten`.`beginn`");
			$tag=$zeit-60*date("i",$zeit)-60*60*date("H",$zeit);
			$j=0;
			$hilf  =explode(":",sql_result($stundenplan,$j,'stundenzeiten.beginn'));
			$hilf2=explode(":",sql_result($stundenplan,$j+1,'stundenzeiten.beginn'));
			while ($j<(sql_num_rows($stundenplan)-1) && ($zeit>($tag+60*60*$hilf[0]+60*$hilf[1]))) {
				//echo $hilf[0].":".$hilf[1]." ".$hilf2[0].":".$hilf2[1]." ";
				//echo ($zeit-($tag+60*60*$hilf[0]+60*$hilf[1]))/(60*60)." | ";
				$pausenminuten=(($hilf2[0]*60*60+$hilf2[1]*60)-($hilf[0]*60*60+$hilf[1]*60+45*60))/60;
				//echo "pm: ".$pausenminuten."<br />";
				$j++;
				$hilf  =explode(":",@sql_result($stundenplan,$j,'stundenzeiten.beginn'));
				$hilf2=explode(":",@sql_result($stundenplan,$j+1,'stundenzeiten.beginn'));
			}
			if ($pausenminuten>0 && $pausenminuten<120) {
				$zeit+=$pausenminuten*60;
				$pausenzaehler++;
				$ansicht['abschnitte'][$i+$pausenzaehler]['zeit']=date("H:i",$pause);
				$pause=$zeit+60*$ansicht['abschnitte'][$i+$pausenzaehler]['minuten']+60*$ansicht['abschnitte'][$i+$pausenzaehler-1]['minuten']-60*$uebrig+45*60;
				//echo "Normalpause: ".date("H:i",$zeit)."[-".$pausenminuten."]+".$ansicht['abschnitte'][$i+$pausenzaehler-1]['minuten']."-".$uebrig."+45=".date("H:i",$pause)."<br />";
				$ansicht['abschnitte'][$i+$pausenzaehler]['inhalt']="<b>Pause (".$pausenminuten."')</b> bis: ".date("H:i",$pause-45*60)." - Dann Voriges weiter... (noch ".$uebrig." min)";
				$ansicht['abschnitte'][$i+$pausenzaehler-1]['minuten']-=$uebrig;
				$ansicht['abschnitte'][$i+$pausenzaehler]['minuten']=$uebrig;
				$ansicht['abschnitte'][$i+$pausenzaehler]['pause']=true;
				$ansicht['abschnitte'][$i+$pausenzaehler]['php_zeit']=$zeit+60*$ansicht['abschnitte'][$i+$pausenzaehler]['minuten']+60*$ansicht['abschnitte'][$i+$pausenzaehler-1]['minuten']-60*$uebrig;
				$zeit=$pause-45*60+60*$uebrig;
				if ($uebrig==$ansicht['abschnitte'][$i+$pausenzaehler]['minuten'] and $ansicht['abschnitte'][$i+$pausenzaehler-1]['minuten']==0) { // wenn ein Abschnitt 0 minuten dauert bis zur Pause:
					$ansicht['abschnitte'][$i+$pausenzaehler]['inhalt']="<b>Pause (".$pausenminuten."')</b> bis: ".date("H:i",$pause-45*60);
					// ganzen Abschnitt tauschen
					$hilfsarray=$ansicht['abschnitte'][$i+$pausenzaehler-1]; $ansicht['abschnitte'][$i+$pausenzaehler-1]=$ansicht['abschnitte'][$i+$pausenzaehler]; $ansicht['abschnitte'][$i+$pausenzaehler]=$hilfsarray;
					//php-Zeit tauschen
					$hilfsarray=$ansicht['abschnitte'][$i+$pausenzaehler-1]['php_zeit']; $ansicht['abschnitte'][$i+$pausenzaehler-1]['php_zeit']=$ansicht['abschnitte'][$i+$pausenzaehler]['php_zeit']; $ansicht['abschnitte'][$i+$pausenzaehler]['php_zeit']=$hilfsarray;
					//Zeit tauschen
					$ansicht['abschnitte'][$i+$pausenzaehler-1]['zeit']=$ansicht['abschnitte'][$i+$pausenzaehler]['zeit']; $ansicht['abschnitte'][$i+$pausenzaehler]['zeit']=date("H:i",$pause-45*60);
					//minuten tauschen
					$hilfsarray=$ansicht['abschnitte'][$i+$pausenzaehler-1]['minuten']; $ansicht['abschnitte'][$i+$pausenzaehler-1]['minuten']=$ansicht['abschnitte'][$i+$pausenzaehler]['minuten']; $ansicht['abschnitte'][$i+$pausenzaehler]['minuten']=$hilfsarray;
				}
			}
			else {
				// eine 0-Minuten-Pause
				$pause=$zeit+60*$ansicht['abschnitte'][$i+$pausenzaehler]['minuten']-60*$uebrig+45*60;
				//echo "0-min Pause: ".date("H:i",$zeit)."+".$ansicht['abschnitte'][$i+$pausenzaehler]['minuten']."[".$pausenminuten."]+".$uebrig."+45=".date("H:i",$pause)."<br />";
				$zeit+=60*$ansicht['abschnitte'][$i+$pausenzaehler]['minuten'];
			}
		}
		else $zeit+=60*$ansicht['abschnitte'][$i+$pausenzaehler]['minuten'];
		
  	}
	return $ansicht;
}

function einzelstundenansicht ($abschnitt, $bearbeitungsmoeglichkeit, $pfad) {
	$ansicht='';
	$db=new db;
    $ansicht['array']=$db->abschnitt($abschnitt);
  
	$result = db_conn_and_sql ( "SELECT * FROM `abschnitt` WHERE `abschnitt`.`id`=".$abschnitt);
  
	$ansicht['minuten']=$ansicht['array']['minuten'];
	
	$ansicht['inhalt']=abschnittsinhalt($ansicht['array'], $bearbeitungsmoeglichkeit,$pfad,0);
  

	if (sql_result ( $result, $i, 'abschnitt.medium' )>0)
		$medium=db_conn_and_sql("SELECT * FROM `medium` WHERE `medium`.`id`=".sql_result ( $result, $i, 'abschnitt.medium' ));
	if (sql_result ( $result, $i, 'abschnitt.sozialform' )>0)
		$soz_form=db_conn_and_sql("SELECT * FROM `sozialform` WHERE `sozialform`.`id`=".sql_result ( $result, $i, 'abschnitt.sozialform' ));
	if (sql_result ( $result, $i, 'abschnitt.handlungsmuster' )>0)
		$handlungsmuster=db_conn_and_sql("SELECT * FROM `handlungsmuster` WHERE `handlungsmuster`.`id`=".sql_result ( $result, $i, 'abschnitt.handlungsmuster' ));
    
    //include_once($pfad.'basic/localisation/methods.php');
	$ansicht['hefter']=$ansicht['array']['hefter'];
	$ansicht['medium']=html_umlaute(sql_result ($medium,0,'medium.kuerzel'));
	$ansicht['sozialform']=html_umlaute(sql_result ( $soz_form, 0, 'sozialform.kuerzel' ));
	$ansicht['ziele']=html_umlaute(sql_result ( $result, $i, 'abschnitt.ziel' ));
    //$ansicht['methode']='kjl'.html_umlaute($methods[sql_result ( $result, $i, 'abschnitt.methode' )]);
	$ansicht['bemerkung']=html_umlaute(sql_result ( $result, $i, 'abschnitt.nachbereitung' ));
	$ansicht['handlungsmuster']=html_umlaute(@sql_result ( $handlungsmuster, 0, 'handlungsmuster.name' ));  
  
  
  return $ansicht;
}

function themenarray_von_oberthema($thema_id) {
    $unterthemen_result=db_conn_and_sql("SELECT * FROM thema WHERE oberthema=".$thema_id);
    $merged_array=array();
    if (sql_num_rows($unterthemen_result)==0)
        return array($thema_id);
    else {
        $merged_array=array($thema_id);
        for ($i=0; $i<sql_num_rows($unterthemen_result); $i++)
            $merged_array=array_merge($merged_array, themenarray_von_oberthema(sql_result($unterthemen_result, $i, "thema.id")));
        return $merged_array;
    }
}

function thema_und_unterthemen_von($thema_id) {
    $themen_array=themenarray_von_oberthema($thema_id);
    for($i=0; $i<count($themen_array); $i++) {
        if ($i>0)
            $rueckgabe.=' OR ';
        $rueckgabe.='`themenzuordnung`.`thema`='.$themen_array[$i];
    }
    return ' AND ('.$rueckgabe.')';
}

function eingetragenes_zeigen($was,$nur_anzeigen, $link, $bereich, $ziel_input, $pfad) {
    if (!isset($pfad))
        $pfad='../';
	$db=new db;
switch($was) {
case "abschnitt":
	$abschnitte=db_conn_and_sql("SELECT `block_abschnitt`.`abschnitt` FROM `block_abschnitt` WHERE `block_abschnitt`.`block`=".$bereich);
	if (@sql_num_rows($abschnitte)>0) {
    for ($i=0;$i<sql_num_rows($abschnitte);$i++) $result[]=$db->abschnitt(sql_result($abschnitte,$i,"block_abschnitt.abschnitt"));
	echo '<ul>';
      for ($i=0;$i<count($result);$i++) {
          if (!$nur_anzeigen) $schreib='<input type="button" value="eintragen" onclick="var inhalt_ids = opener.document.getElementById(\''.$ziel_input.'\'); wert=inhalt_ids.getAttribute(\'value\')+'.$result[$i]['id'].'+\';\'; inhalt_ids.setAttribute(\'value\', wert);" />';
		  echo '
             <li>'.$schreib.'
             '.$result[$i]['inhalt'].'</li>'; // ich glaub, hier kommt man nicht mehr hin
	    }
		echo '</ul>';
	}
break;
case "link":
	// Auswahl Thema Schulart, Klasse
	echo '<fieldset><legend>Auswahl</legend>
	<form action="'.$link.'" method="post" accept-charset="ISO-8859-1">';
	echo '<label for="thema">Thema:</label> <select name="thema"><option value="">alle</option>';
	echo $db->themenoptions($_POST["thema"]);
	echo '</select>';

	echo ' Schulart: <select name="schulart"><option value="">alle</option>';
	$schulart=db_conn_and_sql("SELECT * FROM `schulart`");
	for($i=0;$i<sql_num_rows($schulart);$i++) {
		echo '<option value="'.sql_result($schulart,$i,"schulart.id").'"';
		if ($_POST["schulart"]==sql_result($schulart,$i,"schulart.id")) echo ' selected="selected"';
		echo '>'.html_umlaute(sql_result($schulart,$i,"schulart.kuerzel")).'</option>';
	}
	echo '</select>';

	echo ' Kl. <select name="klasse"><option value="">alle</option>';
	for($i=1;$i<14;$i++) {
		echo '<option value="'.$i.'"';
		if ($_POST["klasse"]==$i) echo ' selected="selected"';
		echo '>'.$i.'</option>';
	}
	echo '</select>';
	
	echo '<br /><label for="typ">Typ:</label> <input type="radio" value="1" name="linktyp"'; if($_POST["linktyp"]==1 or $_GET["typ"]==1) echo ' checked="checked"'; echo ' /> Arbeitsblatt <input type="radio" value="2" name="linktyp"'; if($_POST["linktyp"]==2 or $_GET["typ"]==2) echo ' checked="checked"'; echo ' /> Folie <input type="radio" value="3" name="linktyp"'; if($_POST["linktyp"]==3 or $_GET["typ"]==3) echo ' checked="checked"'; echo ' /> Link <input type="radio" value="0" name="linktyp"'; if($_POST["linktyp"]!=1 and $_POST["linktyp"]!="2" and $_POST["linktyp"]!="3" and $_GET["typ"]!=1 and $_GET["typ"]!=2 and $_GET["typ"]!=3) echo ' checked="checked"'; echo ' /> alle';
	echo '<br /><label for="abschnittslink" title="in einem Abschnitt verwendet">Abschnitt:</label> <input type="radio" value="1" name="abschnittslink"'; if($_POST["abschnittslink"]==1) echo ' checked="checked"'; echo ' /> Ja <input type="radio" value="0" name="abschnittslink"'; if($_POST["abschnittslink"]=="0") echo ' checked="checked"'; echo ' /> Nein <input type="radio" value="-1" name="abschnittslink"'; if($_POST["abschnittslink"]!=1 and $_POST["abschnittslink"]!="0") echo ' checked="checked"'; echo ' /> alle';
	echo ' <input type="submit" class="button" value="anzeigen" /></form></fieldset>';
	
	$sql_thema=''; $sql_klasse=''; $sql_schulart=''; $sql_buch=''; $sql_abschnitt=''; $sql_test='';
	if($_POST["thema"]!="") $sql_thema=thema_und_unterthemen_von($_POST["thema"]);
	if($_POST["klasse"]!="") $sql_klasse=' AND `lernbereich`.`klassenstufe`='.$_POST["klasse"];
	if($_POST["schulart"]!="") $sql_schulart=' AND `lehrplan`.`schulart`='.$_POST["schulart"];
	if($_POST["linktyp"]>0) $sql_linktyp=' AND `link`.`typ`='.$_POST["linktyp"];
	if($_POST["abschnittslink"]==1) $sql_abschnitt=' AND `link_abschnitt`.`abschnitt` IS NOT NULL';
	if($_POST["abschnittslink"]=="0") $sql_abschnitt=' AND `link_abschnitt`.`abschnitt` IS NULL';
	
	$anzahl_links_gesamt=sql_num_rows(db_conn_and_sql("SELECT link.id FROM link WHERE user=".$_SESSION['user_id']));
	if($sql_thema.$sql_klasse.$sql_schulart.$sql_abschnitt.$sql_linktyp!="" or $anzahl_links_gesamt<50)
		$links=db_conn_and_sql("SELECT DISTINCT link.id
		FROM `link`
			LEFT JOIN `themenzuordnung` ON `themenzuordnung`.`id`=`link`.`id` AND `themenzuordnung`.`typ`=4
			LEFT JOIN `lernbereich` ON `link`.`lernbereich`=`lernbereich`.`id`
			LEFT JOIN `lehrplan` ON `lernbereich`.`lehrplan`=`lehrplan`.`id`
			LEFT JOIN `link_abschnitt` ON `link_abschnitt`.`link`=`link`.`id`
		WHERE `link`.`user`=".$_SESSION['user_id'].$sql_thema.$sql_klasse.$sql_schulart.$sql_abschnitt.$sql_linktyp."
		ORDER BY `link`.`id` DESC");
	else echo 'Grenzen Sie Ihre Suche ein.<br />';
		echo (sql_num_rows($links)+0).'/'.$anzahl_links_gesamt.' Ergebnisse<br />';
		if (sql_num_rows($links)>0) {
		echo '<ul>';
        for ($i=0;$i<sql_num_rows($links);$i++) {
			$result[$i]=$db->link_id(sql_result($links,$i,"link.id"));
			if ($nur_anzeigen==0)
                $schreib='<input type="button" value="eintragen" onclick="var inhalt_ids = opener.document.getElementById(\''.$ziel_input.'\'); wert=inhalt_ids.getAttribute(\'value\')+'.$result[$i]['id'].'+\';\'; inhalt_ids.setAttribute(\'value\', wert);
                    var inhalt_einfueger = opener.document.getElementById(\''.$ziel_input.'_inhalt\');
                    inhalt_einfueger.appendChild(document.createTextNode(\''.$result[$i]['beschreibung'].'\'));
                    inhalt_einfueger.appendChild(document.createElementNS(\'http://www.w3.org/1999/xhtml\',\'br\'));" />';
            // in Verbindung mit MarkItUp:
            if ($nur_anzeigen+1==3) // doofer Workaround fuer $nur_anzeigen==2
                $schreib='
				     <input type="button" class="button" value="eintragen" onclick="insertFile('.$result[$i]['id'].');" />';
            
			echo '
             <li>'.$schreib.' <a href="'.$pfad.''.$result[$i]['url'].'" title="Klicken Sie hier, um das Objekt zu &ouml;ffnen. Alternativ k&ouml;nnen Sie das Objekt mit \'Maus-Rechtsklick\' - \'Ziel speichern unter...\' an einen beliebigen Ort kopieren.">'.$result[$i]['beschreibung'].'</a>
				<a href="'.$pfad.'formular/link_bearbeiten.php?link_id='.$result[$i]['id'].'" onclick="fenster(this.href, \'Arbeitsblatt / Folie / Link bearbeiten\'); return false;" class="icon"><img src="'.$pfad.'icons/edit.png" alt="bearbeiten" /></a>';
				echo ' - <img src="'.$pfad.'icons/thema.png" title="Themen" alt="themen" />: '; $thema=0; while ($result[$i]['thema'][$thema]['bezeichnung']!="") { if ($thema>0) echo ', '; echo $result[$i]['thema'][$thema]['bezeichnung']; $thema++; }
				echo '</li>';
	    }
		$result_test=db_conn_and_sql("SELECT `test`.*, GROUP_CONCAT(`thema`.`bezeichnung` SEPARATOR ', ') AS `themen`,`lernbereich`.*
                                 FROM `test`
                                    LEFT JOIN `themenzuordnung` ON `themenzuordnung`.`id`=`test`.`id` AND `themenzuordnung`.`typ`=5
                                    LEFT JOIN `thema` ON `themenzuordnung`.`thema`=`thema`.`id`
                                    LEFT JOIN `lernbereich` ON `test`.`lernbereich`=`lernbereich`.`id`
                                    LEFT JOIN `lehrplan` ON `lernbereich`.`lehrplan`=`lehrplan`.`id`
                                WHERE `test`.`arbeitsblatt`=1 AND `test`.`user`=".$_SESSION['user_id'].$sql_thema.$sql_klasse.$sql_schulart.$sql_abschnitt."
                                GROUP BY `themenzuordnung`.`id`
                                ORDER BY `lehrplan`.`fach`, `lernbereich`.`klassenstufe`,`lernbereich`.`nummer`,`themen`");
        if ($result_test) for ($i=0;$i<sql_num_rows($result_test);$i++) {
			 if ($nur_anzeigen==0) $schreib='<input type="button" value="eintragen" onclick="var inhalt_ids = opener.document.getElementById(\''.$ziel_input.'\'); wert=inhalt_ids.getAttribute(\'value\')+'.sql_result($result_test,$i,'test.id').'+\';\'; inhalt_ids.setAttribute(\'value\', wert);
				var inhalt_einfueger = opener.document.getElementById(\''.$ziel_input.'_inhalt\');
				inhalt_einfueger.appendChild(document.createTextNode(\'Arbeitsblatt mit Aufgaben ausgew&auml;hlt\'));
				inhalt_einfueger.appendChild(document.createElementNS(\'http://www.w3.org/1999/xhtml\',\'br\'));" />';
        
            // in Verbindung mit MarkItUp:
            if ($nur_anzeigen+1==3) // doofer Workaround fuer $nur_anzeigen==2
                $schreib='
				     <input type="button" class="button" value="eintragen" onclick="insertFileWS('.sql_result($result_test,$i,'test.id').');" />';
            
             echo '
             <li>'.$schreib.'<a href="'.$pfad.'test_druckansicht.php?welcher='.sql_result($result_test,$i,'test.id').'" onclick="fenster(this.href,\'Arbeitsblatt bearbeiten\'); return false;"><span title="'.html_umlaute(sql_result($result_test,$i,'lernbereich.name')).'">LB '.sql_result($result_test,$i,'lernbereich.nummer').'</span>: '.html_umlaute(sql_result($result_test,$i,'test.titel')).'</a>';
			 echo ' - <img src="'.$pfad.'icons/thema.png" title="Themen" alt="themen" />: '.html_umlaute(sql_result($result_test,$i,'themen')).'
                <a href="'.$pfad.'formular/test_bearbeiten.php?typ=arbeitsblatt&amp;welcher='.sql_result($result_test,$i,'test.id').'" onclick="fenster(this.href,\'Arbeitsblatt bearbeiten\'); return false;" class="icon"><img src="'.$pfad.'icons/edit.png" alt="bearbeiten" /></a></li>';
	    }
		echo '</ul>';
		}
break;
case "buch":
        $result=$db->buecher();
		if (count($result)>0) {
		echo '<ul>';
		for ($i=0;$i<count($result);$i++) {
            if (!$nur_anzeigen) $schreib='<a href="'.$link.'?buch_id='.$result[$i]['id'].'" onclick="fenster(this.href, \'Buch bearbeiten\'); return false;" class="icon"><img src="./icons/edit.png" alt="bearbeiten" /></a>';
			// var inhalt_ids = opener.document.getElementById(\''.$ziel_input.'\'); wert=inhalt_ids.getAttribute(\'value\')+'.$result[$i]['id'].'+\';\'; inhalt_ids.setAttribute(\'value\', wert);
			echo '
             <li><input type="checkbox" disabled="disabled"';
			if ($result[$i]['aktiv']) echo ' checked="checked"'; 
			echo ' /> '.$result[$i]['fach'].' - KlSt: '.$result[$i]['klassenstufen_gesamt'].' - "'.$result[$i]['name'].'"';
			if ($result[$i]['untertitel']!="") echo ' - '.$result[$i]['untertitel'];
			if ($result[$i]['isbn']!="") echo ' - ISBN: '.$result[$i]['isbn'];
			if ($result[$i]["verlag"]!="") echo ' '.$result[$i]['verlag'];
			echo ' '.$schreib.'</li>';
		}
		echo '</ul>';
		}
break;
case "grafik":
	// Auswahl Thema Schulart, Klasse
	echo '<fieldset><legend>Auswahl</legend>
	<form action="'.$link.'" method="post" accept-charset="ISO-8859-1">';
	echo '<label for="text">Suchtext:</label> <input type="text" name="text" /><br />';
	echo '<label for="thema">Thema:</label> <select name="thema"><option value="">alle</option>';
	echo $db->themenoptions($_POST["thema"]);
	echo '</select>';

	echo ' Schulart: <select name="schulart"><option value="">alle</option>';
	$schulart=db_conn_and_sql("SELECT * FROM `schulart`");
	for($i=0;$i<sql_num_rows($schulart);$i++) {
		echo '<option value="'.sql_result($schulart,$i,"schulart.id").'"';
		if ($_POST["schulart"]==sql_result($schulart,$i,"schulart.id")) echo ' selected="selected"';
		echo '>'.html_umlaute(sql_result($schulart,$i,"schulart.kuerzel")).'</option>';
	}
	echo '</select>';

	echo ' Kl. <select name="klasse"><option value="">alle</option>';
	for($i=1;$i<14;$i++) {
		echo '<option value="'.$i.'"';
		if ($_POST["klasse"]==$i) echo ' selected="selected"';
		echo '>'.$i.'</option>';
	}
	echo '</select>';
	
	echo '<br /><label for="aufgabengrafik" title="aus einer Aufgabe">Aufgabe:</label> <input type="radio" value="1" name="aufgabengrafik"'; if($_POST["aufgabengrafik"]==1) echo ' checked="checked"'; echo ' /> Ja <input type="radio" value="0" name="aufgabengrafik"'; if($_POST["aufgabengrafik"]=="0") echo ' checked="checked"'; echo ' /> Nein <input type="radio" value="-1" name="aufgabengrafik"'; if($_POST["aufgabengrafik"]!=1 and $_POST["aufgabengrafik"]!="0") echo ' checked="checked"'; echo ' /> alle';
	echo '<br /><label for="abschnittsgrafik" title="in einem Abschnitt verwendet">Abschnitt:</label> <input type="radio" value="1" name="abschnittsgrafik"'; if($_POST["abschnittsgrafik"]==1) echo ' checked="checked"'; echo ' /> Ja <input type="radio" value="0" name="abschnittsgrafik"'; if($_POST["abschnittsgrafik"]=="0") echo ' checked="checked"'; echo ' /> Nein <input type="radio" value="-1" name="abschnittsgrafik"'; if($_POST["abschnittsgrafik"]!=1 and $_POST["abschnittsgrafik"]!="0") echo ' checked="checked"'; echo ' /> alle';
	echo ' <input type="submit" class="button" value="Bilder anzeigen" /></form></fieldset>';
	
	$sql_thema=''; $sql_klasse=''; $sql_schulart=''; $sql_buch=''; $sql_abschnitt=''; $sql_test=''; $sql_suchtext='';
	if($_POST["text"]!="") $sql_suchtext=' AND `grafik`.`alt` LIKE \'%'.$_POST["text"].'%\'';
	if($_POST["thema"]!="") $sql_thema=thema_und_unterthemen_von($_POST["thema"]);
	if($_POST["klasse"]!="") $sql_klasse=' AND `lernbereich`.`klassenstufe`='.$_POST["klasse"];
	if($_POST["schulart"]!="") $sql_schulart=' AND `lehrplan`.`schulart`='.$_POST["schulart"];
	if($_POST["aufgabengrafik"]==1) $sql_aufgabe=' AND `grafik_aufgabe`.`aufgabe` IS NOT NULL';
	if($_POST["aufgabengrafik"]=="0") $sql_aufgabe=' AND `grafik_aufgabe`.`aufgabe` IS NULL';
	if($_POST["abschnittsgrafik"]==1) $sql_abschnitt=' AND `grafik_abschnitt`.`abschnitt` IS NOT NULL';
	if($_POST["abschnittsgrafik"]=="0") $sql_abschnitt=' AND `grafik_abschnitt`.`abschnitt` IS NULL';
	
	$anzahl_bilder_gesamt=sql_num_rows(db_conn_and_sql("SELECT grafik.id FROM grafik WHERE user=".$_SESSION['user_id']));
		
	if($sql_thema.$sql_aufgabe.$sql_klasse.$sql_schulart.$sql_abschnitt.$sql_suchtext!="" or $anzahl_bilder_gesamt<50)
		$grafiken=db_conn_and_sql("SELECT DISTINCT grafik.id
		FROM `grafik`
			LEFT JOIN `themenzuordnung` ON `themenzuordnung`.`id`=`grafik`.`id` AND `themenzuordnung`.`typ`=3
			LEFT JOIN `grafik_aufgabe` ON `grafik_aufgabe`.`grafik`=`grafik`.`id`
			LEFT JOIN `lernbereich` ON `grafik`.`lernbereich`=`lernbereich`.`id`
			LEFT JOIN `lehrplan` ON `lernbereich`.`lehrplan`=`lehrplan`.`id`
			LEFT JOIN `grafik_abschnitt` ON `grafik_abschnitt`.`grafik`=`grafik`.`id`
		WHERE `grafik`.`user`=".$_SESSION['user_id'].$sql_thema.$sql_aufgabe.$sql_klasse.$sql_schulart.$sql_abschnitt.$sql_suchtext."
		ORDER BY `grafik`.`id` DESC");
	else echo 'Grenzen Sie Ihre Suche ein.<br />';
	
		echo (@sql_num_rows($grafiken)+0).'/'.$anzahl_bilder_gesamt.' Ergebnisse<br />';
		if (@sql_num_rows($grafiken)>0) {
		echo '<ul>';
        for ($i=0;$i<sql_num_rows($grafiken);$i++) {
			$result[$i]=$db->grafik(sql_result($grafiken,$i,"grafik.id"));
			if (!$nur_anzeigen)
                $schreib='<input type="text" id="groesse_'.$result[$i]['id'].'" size="1" maxlength="3" title="Breite in cm angeben. 15cm = volle Breite" /> cm
				     <input type="button" class="button" value="eintragen" onclick="var inhalt_ids = opener.document.getElementById(\''.$ziel_input.'\');
						wert=inhalt_ids.getAttribute(\'value\')+'.$result[$i]['id'].'+\':\'+document.getElementById(\'groesse_'.$result[$i]['id'].'\').value.replace(/,/, \'.\')+\';\';
						if (document.getElementById(\'groesse_'.$result[$i]['id'].'\').value.replace(/,/, \'.\')&gt;0 &amp;&amp; document.getElementById(\'groesse_'.$result[$i]['id'].'\').value.replace(/,/, \'.\')&lt;=15) {
							inhalt_ids.setAttribute(\'value\', wert);
							var inhalt_einfueger = opener.document.getElementById(\''.$ziel_input.'_inhalt\'); var neu = document.createElementNS(\'http://www.w3.org/1999/xhtml\',\'img\');
							var neu_src = document.createAttribute(\'src\'); neu_src.nodeValue = \'../'.$result[$i]['url'].'\'; neu.setAttributeNode(neu_src);
							var neu_alt = document.createAttribute(\'alt\'); neu_alt.nodeValue = \''.$result[$i]['alt'].'\'; neu.setAttributeNode(neu_alt);
							var neu_style = document.createAttribute(\'style\'); neu_style.nodeValue = \'width: \'+document.getElementById(\'groesse_'.$result[$i]['id'].'\').value.replace(/,/, \'.\')+\'cm;\'; neu.setAttributeNode(neu_style);
							/* inhalt_einfueger.appendChild(document.createTextNode(\'Grafik\')); */
							inhalt_einfueger.appendChild(neu);}" />';
            // in Verbindung mit MarkItUp:
            if ($nur_anzeigen+1==3) { // doofer Workaround fuer $nur_anzeigen==2
                $style='';
                $schreib='<input type="text" id="groesse_'.$result[$i]['id'].'" size="1" maxlength="3" title="Breite in cm angeben. 15cm = volle Breite" /> cm
				     <input type="button" class="button" value="eintragen" onclick="insertPicture('.$result[$i]['id'].', \'middle\', document.getElementById(\'groesse_'.$result[$i]['id'].'\').value.replace(/,/, \'.\'));" />';
            }
            else $style=' style="float: left; width: 150px; list-style-type:none;"';
            
            echo '<li'.$style.'>'.$schreib.' <a href="'.$pfad.$result[$i]['url'].'" title="Klicken Sie hier, um das Objekt zu &ouml;ffnen. Alternativ k&ouml;nnen Sie das Objekt mit \'Maus-Rechtsklick\' - \'Ziel speichern unter...\' an einen beliebigen Ort kopieren.">';
            if (file_exists($pfad.$result[$i]['tmb_url']))
                echo '<img src="'.$pfad.$result[$i]['tmb_url'].'" alt="'.$result[$i]['alt'].'" title="'.$result[$i]['alt'].'" style="width:80px; height: 80px;" />';
            else
                echo '<img src="'.$pfad.$result[$i]['url'].'" alt="'.$result[$i]['alt'].'" title="'.$result[$i]['alt'].'" style="width:80px; height: 80px;" />'; // $result[$i]['alt'];
            echo '</a>
                <a href="'.$pfad.'formular/grafik_bearbeiten.php?bild_id='.$result[$i]['id'].'" onclick="fenster(this.href, \'Bild bearbeiten\'); return false;" class="icon"><img src="'.$pfad.'icons/edit.png" alt="bearbeiten" /></a>
				<br /><img src="'.$pfad.'icons/thema.png" title="Themen" alt="themen" />: '; $thema=0; while ($result[$i]['thema'][$thema]['bezeichnung']!="") { if ($thema>0) echo ', '; echo $result[$i]['thema'][$thema]['bezeichnung']; $thema++; }
			echo '</li>';
		}
		echo '</ul><br style="clear: both;" />';
		}
break;
case "material":
        $result=$db->materialien();
		if (@count($result)>0) {
		echo '<ul>';
		
        for ($i=0;$i<count($result);$i++) {
            if (!$nur_anzeigen) $schreib='<input type="button" value="eintragen" onclick="var inhalt_ids = opener.document.getElementById(\''.$ziel_input.'\'); wert=inhalt_ids.getAttribute(\'value\')+'.$result[$i]['id'].'+\';\'; inhalt_ids.setAttribute(\'value\', wert);
				var inhalt_einfueger = opener.document.getElementById(\''.$ziel_input.'_inhalt\');
				inhalt_einfueger.appendChild(document.createTextNode(\''.$result[$i]['name'].'\'));
				inhalt_einfueger.appendChild(document.createElementNS(\'http://www.w3.org/1999/xhtml\',\'br\'));" />';
			echo '
             <li>'.$schreib.'
			 '.$result[$i]['name'];
			echo '
			<a href="'.$pfad.'formular/material_bearb.php?typ=6&amp;id='.$result[$i]['id'].'" onclick="fenster(this.href, \'Material bearbeiten\'); return false;" class="icon"><img src="./icons/edit.png" alt="bearbeiten" /></a>';
			if ($result[$i]['aufbewahrungsort']!="") echo ' - <label style="width: 3em;">Ort:</label> '.$result[$i]['aufbewahrungsort'];
			$thema=0;
			while (isset($result[$i]['thema'][$thema])) {
				if($thema==0) echo ' <img src="'.$pfad.'icons/thema.png" title="Themen" alt="themen" />: ';
				else echo ', ';
				echo $result[$i]['thema'][$thema]['bezeichnung'];
				$thema++;
			}
			echo '</li>';
		}
		echo '</ul>';
		}
break;
case "aufgabe":
	// Auswahl Thema Schulart, Klasse, Schwierigkeit
	echo '<fieldset><legend>Auswahl</legend>
	<form action="'.$link.'" method="post" accept-charset="ISO-8859-1">';
	echo '<label for="thema">Thema:</label> <select name="thema"><option value="">alle</option>';
	echo $db->themenoptions($_POST["thema"]);
	echo '</select>';

	echo ' Schulart: <select name="schulart"><option value="">alle</option>';
	$schulart=db_conn_and_sql("SELECT * FROM `schulart`");
	for($i=0;$i<sql_num_rows($schulart);$i++) {
		echo '<option value="'.sql_result($schulart,$i,"schulart.id").'"';
		if ($_POST["schulart"]==sql_result($schulart,$i,"schulart.id")) echo ' selected="selected"';
		echo '>'.html_umlaute(sql_result($schulart,$i,"schulart.kuerzel")).'</option>';
	}
	echo '</select>';

	echo ' Kl. <select name="klasse"><option value="">alle</option>';
	for($i=1;$i<14;$i++) {
		echo '<option value="'.$i.'"';
		if ($_POST["klasse"]==$i) echo ' selected="selected"';
		echo '>'.$i.'</option>';
	}
	echo '</select>';
	
	echo '<br /><label for="buchaufgabe" title="aus einem Schulbuch">Schulbuch:</label> <input type="radio" value="1" name="buchaufgabe"'; if($_POST["buchaufgabe"]==1) echo ' checked="checked"'; echo ' /> Ja <input type="radio" value="0" name="buchaufgabe"'; if($_POST["buchaufgabe"]=="0") echo ' checked="checked"'; echo ' /> Nein <input type="radio" value="-1" name="buchaufgabe"'; if($_POST["buchaufgabe"]!=1 and $_POST["buchaufgabe"]!="0") echo ' checked="checked"'; echo ' /> alle';
	echo '<br /><label for="testaufgabe" title="aus einem Test">Testaufgabe:</label> <input type="radio" value="1" name="testaufgabe"'; if($_POST["testaufgabe"]==1) echo ' checked="checked"'; echo ' /> Ja <input type="radio" value="0" name="testaufgabe"'; if($_POST["testaufgabe"]=="0") echo ' checked="checked"'; echo ' /> Nein <input type="radio" value="-1" name="testaufgabe"'; if($_POST["testaufgabe"]!=1 and $_POST["testaufgabe"]!="0") echo ' checked="checked"'; echo ' /> alle';
	echo '<br /><label for="abschnittaufgabe" title="in einem Abschnitt verwendet">Abschnitt:</label> <input type="radio" value="1" name="abschnittaufgabe"'; if($_POST["abschnittaufgabe"]==1) echo ' checked="checked"'; echo ' /> Ja <input type="radio" value="0" name="abschnittaufgabe"'; if($_POST["abschnittaufgabe"]=="0") echo ' checked="checked"'; echo ' /> Nein <input type="radio" value="-1" name="abschnittaufgabe"'; if($_POST["abschnittaufgabe"]!=1 and $_POST["abschnittaufgabe"]!="0") echo ' checked="checked"'; echo ' /> alle';
	echo ' <input type="submit" class="button" value="Aufgaben anzeigen" /></form></fieldset>';
	
	$sql_thema=''; $sql_klasse=''; $sql_schulart=''; $sql_buch=''; $sql_abschnitt=''; $sql_test='';
	if($_POST["thema"]!="") $sql_thema=thema_und_unterthemen_von($_POST["thema"]);
	if($_POST["klasse"]!="") $sql_klasse=' AND `lernbereich`.`klassenstufe`='.$_POST["klasse"];
	if($_POST["schulart"]!="") $sql_schulart=' AND `lehrplan`.`schulart`='.$_POST["schulart"];
	if($_POST["buchaufgabe"]==1) $sql_buch=' AND `buch_aufgabe`.`buch` IS NOT NULL';
	if($_POST["buchaufgabe"]=="0") $sql_buch=' AND `buch_aufgabe`.`buch` IS NULL';
	if($_POST["abschnittaufgabe"]==1) $sql_abschnitt=' AND `aufgabe_abschnitt`.`abschnitt` IS NOT NULL';
	if($_POST["abschnittaufgabe"]=="0") $sql_abschnitt=' AND `aufgabe_abschnitt`.`abschnitt` IS NULL';
	if($_POST["testaufgabe"]==1) $sql_test=' AND `test_aufgabe`.`test` IS NOT NULL';
	if($_POST["testaufgabe"]=="0") $sql_test=' AND `test_aufgabe`.`test` IS NULL';
	
	$anzahl_aufgaben_gesamt=sql_num_rows(db_conn_and_sql("SELECT aufgabe.id FROM aufgabe WHERE user=".$_SESSION['user_id']));
		
	if($sql_thema.$sql_buch.$sql_test.$sql_klasse.$sql_schulart.$sql_abschnitt!="" or $anzahl_aufgaben_gesamt<50)
		$aufgaben=db_conn_and_sql("SELECT DISTINCT aufgabe.id
		FROM `aufgabe`
			LEFT JOIN `themenzuordnung` ON `themenzuordnung`.`id`=`aufgabe`.`id` AND `themenzuordnung`.`typ`=1
			LEFT JOIN `buch_aufgabe` ON `buch_aufgabe`.`aufgabe`=`aufgabe`.`id`
			LEFT JOIN `test_aufgabe` ON `test_aufgabe`.`aufgabe`=`aufgabe`.`id`
			LEFT JOIN `lernbereich` ON `aufgabe`.`lernbereich`=`lernbereich`.`id`
			LEFT JOIN `lehrplan` ON `lernbereich`.`lehrplan`=`lehrplan`.`id`
			LEFT JOIN `aufgabe_abschnitt` ON `aufgabe_abschnitt`.`aufgabe`=`aufgabe`.`id`
		WHERE `aufgabe`.`user`=".$_SESSION['user_id'].$sql_thema.$sql_buch.$sql_test.$sql_klasse.$sql_schulart.$sql_abschnitt."
		ORDER BY `aufgabe`.`id` DESC");
	else echo 'Grenzen Sie Ihre Suche ein.<br />';
		echo (@sql_num_rows($aufgaben)+0).'/'.$anzahl_aufgaben_gesamt.' Ergebnisse<br />';
		if (@sql_num_rows($aufgaben)>0) {
		echo '<ul>';
		for ($i=0;$i<sql_num_rows($aufgaben);$i++) {
			$result[$i]=$db->aufgabe(sql_result($aufgaben,$i,"aufgabe.id"));
			if ($thema!=$result[$i]['thema'][0]['id']) {
                $thema=$result[$i]['thema'][0]['id'];
                echo '<b><img src="'.$pfad.'icons/thema.png" title="Themen" alt="themen" />: ';
                $thema_zaehler=0;
                while ($result[$i]['thema'][$thema_zaehler]['bezeichnung']!="") {
                    if ($thema_zaehler>0)
                        echo ', ';
                    echo $result[$i]['thema'][$thema_zaehler]['bezeichnung'];
                    $thema_zaehler++;
                }
                echo "</b>";
            }
            if (!$nur_anzeigen) $schreib='<input type="button" value="eintragen" onclick="var inhalt_ids = opener.document.getElementById(\''.$ziel_input.'\'); wert=inhalt_ids.getAttribute(\'value\')+'.$result[$i]['id'].'+\';\'; inhalt_ids.setAttribute(\'value\', wert);
				var inhalt_einfueger = opener.document.getElementById(\''.$ziel_input.'_inhalt\');
				inhalt_einfueger.appendChild(document.createTextNode(\''.$result[$i]['buch'][0]['kuerzel']." S. ".$result[$i]['buch'][0]['seite']."/".$result[$i]['buch'][0]['nummer'].': Aufgabe ausgew&auml;hlt\'));
				inhalt_einfueger.appendChild(document.createElementNS(\'http://www.w3.org/1999/xhtml\',\'br\'));" />';
			//if (count($result[$i]['bilder'])>0) echo '<div class="tooltip" id="tt_aufgabe_'.$i.'" style="width: 90%">'.aufgabe_mit_bildern($pfad, $result[$i], true, 'A').'</div>';
			echo '
             <li>'.$schreib.'
             Fach: '.$result[$i]['fach'].' Kl. '.$result[$i]['klassenstufe']." - LB: ".$result[$i]['lernbereich_nummer'].". ".$result[$i]['lernbereich'];
			if ($result[$i]['buch'][0]['kuerzel']!="") echo ' - <span style="color: brown; font-size: 8pt;">'.$result[$i]['buch'][0]['kuerzel']." S. ".$result[$i]['buch'][0]['seite']."/".$result[$i]['buch'][0]['nummer'].'</span>';
			echo '<br />';
			//if (count($result[$i]['bilder'])>0) echo '<img src="'.$pfad.'icons/grafik.png" alt="grafik" onmouseover="showWMTT(\'tt_aufgabe_'.$i.'\')" onmouseout="hideWMTT()" /> ';
			echo syntax_zu_html($result[$i]['text'],$result[$i]['teilaufgaben_nebeneinander'],0,$pfad,'A');
			echo '<br style="clear: both;" /><b>Lsg:</b> <span style="color: #aaa;">'.syntax_zu_html($result[$i]['loesung'],$result[$i]['teilaufgaben_nebeneinander'],0,$pfad,'A').'</span> <span style="color: blue;">'.$result[$i]['bemerkung'].'</span><br style="clear: both;" /><a href="./formular/aufgabe_bearb.php?welche='.$result[$i]['id'].'" onclick="fenster(this.href,\'Aufgabe bearbeiten\'); return false;" class="icon" title="bearbeiten"><img src="./icons/edit.png" alt="bearbeiten" /></a></li>';
		}
		echo '</ul>';
		}
break;
case "testaufgabe":
        echo '<table><tr><th>Pos A</th><th>Pos B</th><th title="Seitenwechsel erzwingen">Seite</th><th>eintragen</th><th>Aufgabe</th></tr>';
            $result=db_conn_and_sql("SELECT *
                                 FROM `aufgabe`,`thema`,`lernbereich`
                                 WHERE `aufgabe`.`thema`=`thema`.`id`
                                   AND `aufgabe`.`lernbereich`=`lernbereich`.`id`
                                   AND `aufgabe`.`user`=".$_SESSION['user_id']."
                                 ORDER BY `lernbereich`.`klassenstufe`,`lernbereich`.`nummer`,`thema`.`bezeichnung`");
		if (@sql_num_rows($result)>0) for ($i=0;$i<sql_num_rows($result);$i++) {
			if ($thema!=sql_result($result,$i,'thema.bezeichnung')) {$thema=html_umlaute(sql_result($result,$i,'thema.bezeichnung')); echo '<tr><td colspan="5">Thema: '.$thema.'</td></tr>';}
			echo '<tr><td><input type="text" name="posA_'.sql_result($result,$i,'aufgabe.id').'" size="1" maxlength="2" title="Position bei Gruppe A" /> <input type="checkbox" name="wechselA_'.sql_result($result,$i,'aufgabe.id').'" title="Seitenwechsel erzwingen bei Gruppe A" /></td>
                               <td><input type="text" name="posB_'.sql_result($result,$i,'aufgabe.id').'" size="1" maxlength="2" title="Position bei Gruppe B" /> <input type="checkbox" name="wechselB_'.sql_result($result,$i,'aufgabe.id').'" title="Seitenwechsel erzwingen bei Gruppe B" /></td>
                               <td><input type="checkbox" name="zusatzaufgabe_'.sql_result($result,$i,'aufgabe.id').'" title="Zusatzaufgabe?" /></td>
                               <td><input type="button" value="los" name="verwenden_'.sql_result($result,$i,'aufgabe.id').'" onclick="var inhalt_ids = opener.document.getElementById(\''.$ziel_input.'\');
												var punkte = opener.document.getElementById(\'punkte_anzahl\');
												var zeit = opener.document.getElementById(\'zeit_gesamt\');
												var wert=inhalt_ids.getAttribute(\'value\')+'.sql_result($result,$i,'aufgabe.id').'+\':\'+document.getElementsByName(\'posA_'.sql_result($result,$i,'aufgabe.id').'\')[0].value+\',\'+document.getElementsByName(\'posB_'.sql_result($result,$i,'aufgabe.id').'\')[0].value+\',\'+document.getElementsByName(\'wechselA_'.sql_result($result,$i,'aufgabe.id').'\')[0].checked+\',\'+document.getElementsByName(\'wechselB_'.sql_result($result,$i,'aufgabe.id').'\')[0].checked+\',\'+document.getElementsByName(\'zusatzaufgabe_'.sql_result($result,$i,'aufgabe.id').'\')[0].checked+\';\';
                                                inhalt_ids.setAttribute(\'value\', wert);
												wert=punkte.getAttribute(\'value\')+'.(0+sql_result($result,$i,'aufgabe.punkte')).'+\'+\';
                                                punkte.setAttribute(\'value\', wert);
												wert=zeit.getAttribute(\'value\')+'.(0+sql_result($result,$i,'aufgabe.bearbeitungszeit')).'+\'+\';
                                                zeit.setAttribute(\'value\', wert);
												" /></td>
                               <td>Kl. '.sql_result($result,$i,'lernbereich.klassenstufe').' - LB: '.sql_result($result,$i,'lernbereich.nummer').'. '.html_umlaute(sql_result($result,$i,'lernbereich.name')).'<br />Text: '.syntax_zu_html(sql_result($result,$i,'aufgabe.text'),0,$pfad,'A').'<br />'.html_umlaute(sql_result($result,$i,'aufgabe.bemerkung')).'</td></tr>';
		}
        echo '</table>';
break;
case "test":
            $result=db_conn_and_sql("SELECT `test`.*, GROUP_CONCAT(`thema`.`bezeichnung` SEPARATOR ', ') AS `themen`,`lernbereich`.*, `notentypen`.*
                                 FROM `test`,`themenzuordnung`,`thema`,`lernbereich`,`notentypen`, `lehrplan`, `schulart`
                                 WHERE `themenzuordnung`.`id`=`test`.`id`
									AND `themenzuordnung`.`typ`=5
                                    AND `schulart`.`id`=`lehrplan`.`schulart`
									AND `themenzuordnung`.`thema`=`thema`.`id`
									AND `test`.`lernbereich`=`lernbereich`.`id`
									AND `test`.`notentyp` = `notentypen`.`id`
									AND `lernbereich`.`lehrplan`=`lehrplan`.`id`
									AND `test`.`arbeitsblatt` IS NULL
									AND `test`.`user`=".$_SESSION['user_id']."
                                 GROUP BY `themenzuordnung`.`id`
                                 ORDER BY `schulart`.`kuerzel`, `lehrplan`.`fach`, `lernbereich`.`klassenstufe`,`lernbereich`.`nummer`,`themen`");
		$fach=''; $klasse='';
		if (sql_num_rows($result)>0) {
		echo '<ul>';
        for ($i=0;$i<sql_num_rows($result);$i++) {
			// lieber mit test(id)
			$result_2=db_conn_and_sql ('SELECT *
                       FROM `lernbereich`,`lehrplan`,`schulart`,`faecher`
                       WHERE `lernbereich`.`id`='.sql_result($result,$i,'test.lernbereich').'
                         AND `lernbereich`.`lehrplan`=`lehrplan`.`id`
                         AND `lehrplan`.`schulart` = `schulart`.`id`
                         AND `lehrplan`.`fach` = `faecher`.`id`
                       ORDER BY `schulart`.`kuerzel`,`lehrplan`.`bundesland`,`lehrplan`.`jahr`,`lehrplan`.`fach`,`lernbereich`.`klassenstufe`,`lernbereich`.`nummer`');
			if ($fach!=sql_result($result_2,0,'faecher.id') or $klasse!=sql_result($result,$i,'lernbereich.klassenstufe'))
				echo '<span class="ui-widget-header">'.html_umlaute(sql_result($result_2,0,'schulart.kuerzel')).' '.html_umlaute(sql_result($result_2,0,'faecher.kuerzel')).' - Kl. '.sql_result($result,$i,'lernbereich.klassenstufe').':</span>';
			$fach=sql_result($result_2,0,'faecher.id');
			$klasse=sql_result($result,$i,'lernbereich.klassenstufe');
			 if (!$nur_anzeigen) $schreib='<input type="button" value="eintragen" onclick="var inhalt_ids = opener.document.getElementById(\''.$ziel_input.'\'); wert=inhalt_ids.getAttribute(\'value\')+'.sql_result($result,$i,'test.id').'+\';\'; inhalt_ids.setAttribute(\'value\', wert);
				var inhalt_einfueger = opener.document.getElementById(\''.$ziel_input.'_inhalt\');
				inhalt_einfueger.appendChild(document.createTextNode(\''.html_umlaute(sql_result($result,$i,'notentypen.kuerzel')).' ausgew&auml;hlt\'));
				inhalt_einfueger.appendChild(document.createElementNS(\'http://www.w3.org/1999/xhtml\',\'br\'));" />';
             echo '
             <li>'.$schreib.' <span title="'.html_umlaute(sql_result($result,$i,'lernbereich.name')).'">LB '.sql_result($result,$i,'lernbereich.nummer').'</span>: '.html_umlaute(sql_result($result,$i,'notentypen.kuerzel')).' '.html_umlaute(sql_result($result,$i,'test.titel'));
			if (sql_result($result,$i,'test.url')!="") echo ' <a href="'.$pfad.'daten/'.sql_result($result_2,0,'faecher.kuerzel').'/'.sql_result($result_2,0,'schulart.kuerzel').'/'.sql_result($result_2,0,'lernbereich.klassenstufe')."/".urlencode(sql_result($result,$i,'test.url')).'">'.urlencode(sql_result($result,$i,'test.url')).'</a>';
			 echo ' - <img src="'.$pfad.'icons/thema.png" title="Themen" alt="themen" />: '.html_umlaute(sql_result($result,$i,'themen')).'
			<a href="./formular/test_bearbeiten.php?welcher='.sql_result($result,$i,'test.id').'" onclick="fenster(this.href,\'Test bearbeiten\'); return false;" class="icon"><img src="'.$pfad.'icons/edit.png" alt="bearbeiten" /></a></li>';
		}
		echo '</ul>';
		}
break;
}
}

function sitzplan_objektzuordnung ($faktor) {
	// Richtung (Drehung) siehe Nummernpad
	return array(
	""=>array(
		"name"=>"leer",
		"drehung"=>array(
			"1"=>array("pos_x"=>0, "pos_y"=>-5, "name_x"=>-0.08*$faktor, "name_y"=>0*$faktor, "width"=>$faktor, "name_width"=>$faktor),
			"2"=>array("pos_x"=>0, "pos_y"=>-0.2*$faktor, "name_x"=>0, "name_y"=>(80/297*$faktor), "width"=>$faktor, "name_width"=>$faktor),
			"3"=>array("pos_x"=>0, "pos_y"=>-10, "name_x"=>-0.1*$faktor, "name_y"=>(0.35*$faktor), "width"=>$faktor, "name_width"=>$faktor),
			"4"=>array("pos_x"=>0, "pos_y"=>0, "name_x"=>0, "name_y"=>0.05*$faktor, "width"=>$faktor+(61/297*$faktor), "name_width"=>$faktor),
			"6"=>array("pos_x"=>-0.2*$faktor, "pos_y"=>0, "name_x"=>61/297*$faktor, "name_y"=>0.05*$faktor, "width"=>$faktor+(61/297*$faktor), "name_width"=>$faktor),
			"7"=>array("pos_x"=>0, "pos_y"=>0, "name_x"=>-0.12*$faktor, "name_y"=>0.38*$faktor, "width"=>$faktor, "name_width"=>$faktor),
			"8"=>array("pos_x"=>0, "pos_y"=>0, "name_x"=>0, "name_y"=>0.2*$faktor, "width"=>$faktor, "name_width"=>$faktor),
			"9"=>array("pos_x"=>0, "pos_y"=>-10, "name_x"=>-0.13*$faktor, "name_y"=>0.25*$faktor, "width"=>$faktor, "name_width"=>$faktor))),
	"1"=>array(
		"name"=>"tischplatz",
		"drehung"=>array(
			"1"=>array("pos_x"=>-0.2*$faktor, "pos_y"=>-0.2*$faktor, "name_x"=>0.2*$faktor, "name_y"=>((61+60)/297*$faktor), "width"=>1.41*$faktor, "name_width"=>$faktor),
			"2"=>array("pos_x"=>0, "pos_y"=>-0.2*$faktor, "name_x"=>0, "name_y"=>((61+60)/297*$faktor), "width"=>$faktor, "name_width"=>$faktor),
			"3"=>array("pos_x"=>-0.2*$faktor, "pos_y"=>-0.2*$faktor, "name_x"=>0.2*$faktor, "name_y"=>((61+60)/297*$faktor), "width"=>1.41*$faktor, "name_width"=>$faktor),
			"4"=>array("pos_x"=>0, "pos_y"=>0, "name_x"=>0, "name_y"=>0.05*$faktor, "width"=>$faktor+(61/297*$faktor), "name_width"=>$faktor),
			"6"=>array("pos_x"=>-0.2*$faktor, "pos_y"=>0, "name_x"=>(61/297*$faktor), "name_y"=>0.05*$faktor, "width"=>$faktor+(61/297*$faktor), "name_width"=>$faktor),
			"7"=>array("pos_x"=>-0.2*$faktor, "pos_y"=>-0.2*$faktor, "name_x"=>0.2*$faktor, "name_y"=>((61+60)/297*$faktor), "width"=>1.41*$faktor, "name_width"=>$faktor),
			"8"=>array("pos_x"=>0, "pos_y"=>0, "name_x"=>0, "name_y"=>0.2*$faktor, "width"=>$faktor, "name_width"=>$faktor),
			"9"=>array("pos_x"=>-0.2*$faktor, "pos_y"=>-0.2*$faktor, "name_x"=>0.2*$faktor, "name_y"=>((61+60)/297*$faktor), "width"=>1.41*$faktor, "name_width"=>$faktor))),
	"2"=>array(
		"name"=>"tisch",
		"drehung"=>array(
			"1"=>array("pos_x"=>3, "pos_y"=>-3, "name_x"=>0.18*$faktor, "name_y"=>(60/297*$faktor), "width"=>2.1*$faktor, "name_width"=>$faktor),
			"2"=>array("pos_x"=>0, "pos_y"=>-0.2*$faktor, "name_x"=>0, "name_y"=>(80/297*$faktor), "width"=>2*$faktor, "name_width"=>$faktor),
			"3"=>array("pos_x"=>0, "pos_y"=>-$faktor, "name_x"=>0.2*$faktor, "name_y"=>0.85*$faktor, "width"=>2.1*$faktor, "name_width"=>$faktor),
			"4"=>array("pos_x"=>0, "pos_y"=>0, "name_x"=>0, "name_y"=>0.05*$faktor, "width"=>$faktor+(61/297*$faktor), "name_width"=>$faktor),
			"6"=>array("pos_x"=>-0.2*$faktor, "pos_y"=>0, "name_x"=>61/297*$faktor, "name_y"=>0.05*$faktor, "width"=>$faktor+(61/297*$faktor), "name_width"=>$faktor),
			"7"=>array("pos_x"=>0, "pos_y"=>-$faktor, "name_x"=>0.2*$faktor, "name_y"=>1.08*$faktor, "width"=>2.1*$faktor, "name_width"=>$faktor),
			"8"=>array("pos_x"=>0, "pos_y"=>0, "name_x"=>0, "name_y"=>0.2*$faktor, "width"=>2*$faktor, "name_width"=>$faktor),
			"9"=>array("pos_x"=>0, "pos_y"=>0, "name_x"=>0.2*$faktor, "name_y"=>((61+60)/297*$faktor), "width"=>2.1*$faktor, "name_width"=>$faktor))),
	"3"=>array(
		"name"=>"stuhl",
		"drehung"=>array(
			"2"=>array("pos_x"=>0, "pos_y"=>-10, "name_x"=>0, "name_y"=>((61+60)/297*$faktor), "width"=>$faktor, "name_width"=>$faktor),
			"4"=>array("pos_x"=>0, "pos_y"=>0, "name_x"=>0, "name_y"=>16, "width"=>$faktor, "name_width"=>$faktor),
			"6"=>array("pos_x"=>-10, "pos_y"=>0, "name_x"=>(61/297*$faktor), "name_y"=>16, "width"=>$faktor, "name_width"=>$faktor),
			"8"=>array("pos_x"=>0, "pos_y"=>0, "name_x"=>0, "name_y"=>16, "width"=>$faktor, "name_width"=>$faktor))),
	"4"=>array(
		"name"=>"pc_platz",
		"drehung"=>array(
			"1"=>array("pos_x"=>-0.2*$faktor, "pos_y"=>-0.2*$faktor, "name_x"=>0.2*$faktor, "name_y"=>((61+60)/297*$faktor), "width"=>1.41*$faktor, "name_width"=>$faktor),
			"2"=>array("pos_x"=>0, "pos_y"=>-0.2*$faktor, "name_x"=>0, "name_y"=>((61+60)/297*$faktor), "width"=>$faktor, "name_width"=>$faktor),
			"3"=>array("pos_x"=>-0.2*$faktor, "pos_y"=>-0.2*$faktor, "name_x"=>0.2*$faktor, "name_y"=>((61+60)/297*$faktor), "width"=>1.41*$faktor, "name_width"=>$faktor),
			"4"=>array("pos_x"=>0, "pos_y"=>0, "name_x"=>0, "name_y"=>0.05*$faktor, "width"=>$faktor+(61/297*$faktor), "name_width"=>$faktor),
			"6"=>array("pos_x"=>-0.2*$faktor, "pos_y"=>0, "name_x"=>(61/297*$faktor), "name_y"=>0.05*$faktor, "width"=>$faktor+(61/297*$faktor), "name_width"=>$faktor),
			"7"=>array("pos_x"=>-0.2*$faktor, "pos_y"=>-0.2*$faktor, "name_x"=>0.2*$faktor, "name_y"=>((61+60)/297*$faktor), "width"=>1.41*$faktor, "name_width"=>$faktor),
			"8"=>array("pos_x"=>0, "pos_y"=>0, "name_x"=>0, "name_y"=>0.2*$faktor, "width"=>$faktor, "name_width"=>$faktor),
			"9"=>array("pos_x"=>-0.2*$faktor, "pos_y"=>-0.2*$faktor, "name_x"=>0.2*$faktor, "name_y"=>((61+60)/297*$faktor), "width"=>1.41*$faktor, "name_width"=>$faktor))),
	"5"=>array(
		"name"=>"lehrertisch",
		"drehung"=>array(
			"2"=>array("pos_x"=>0, "pos_y"=>-10, "name_x"=>0, "name_y"=>((61+60)/297*$faktor), "width"=>$faktor, "name_width"=>$faktor),
			"4"=>array("pos_x"=>0, "pos_y"=>0, "name_x"=>0, "name_y"=>16, "width"=>$faktor, "name_width"=>$faktor),
			"6"=>array("pos_x"=>-10, "pos_y"=>0, "name_x"=>(61/297*$faktor), "name_y"=>16, "width"=>$faktor, "name_width"=>$faktor),
			"8"=>array("pos_x"=>0, "pos_y"=>0, "name_x"=>0, "name_y"=>16, "width"=>$faktor, "name_width"=>$faktor)))
	);

}
?>
