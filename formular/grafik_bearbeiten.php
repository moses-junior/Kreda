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

if (!proofuser("grafik", $_GET["bild_id"]))
	die("Sie sind hierzu nicht berechtigt.");

if ($_GET["eintragen"]=="true") {
    // Falls der Lernbereich geändert wird, muss das Bild verschoben werden!
    $alter_lernbereich = db_conn_and_sql("SELECT * FROM grafik WHERE grafik.id=".injaway($_GET["bild_id"]));
    
	db_conn_and_sql("UPDATE `grafik` SET `alt`=".apostroph_bei_bedarf($_POST["alt"]).", `lernbereich`=".leer_NULL($_POST['lernbereich'])." WHERE `id`=".injaway($_GET["bild_id"]));
	
	db_conn_and_sql("DELETE FROM `themenzuordnung` WHERE `typ`=3 AND `id`=".injaway($_GET["bild_id"]));
	$thema=0;
	while($_POST["thema_".$thema]!="-" and $thema<10) {
		db_conn_and_sql("INSERT INTO `themenzuordnung` (`typ`,`id`,`thema`) VALUES (3,".injaway($_GET["bild_id"]).",".injaway($_POST["thema_".$thema]).");");
		$thema++;
	}
	
	$tempname = $_FILES['file']['tmp_name'];
	$name = $_FILES['file']['name'];
	$tempname_klein = $_FILES['file_klein']['tmp_name'];
	$name_klein = $_FILES['file_klein']['name'];
    
    // Falls der Lernbereich geändert wird, muss das Bild verschoben werden!
    if (sql_result($alter_lernbereich, 0, "grafik.lernbereich")!=$_POST["lernbereich"]) {
		$db=new db;
        $old_verzeichnis=$db->url(sql_result($alter_lernbereich, 0, "grafik.lernbereich"));
        $new_verzeichnis=$db->url($_POST["lernbereich"]);
        
        if ($old_verzeichnis != $new_verzeichnis) {
            $dateiname=pfad_und_dateiname($_POST["lernbereich"],'grafik', sql_result($alter_lernbereich, 0, 'grafik.url'), $pfad.$old_verzeichnis.sql_result($alter_lernbereich, 0, 'grafik.url'));
            unlink($pfad.$old_verzeichnis.sql_result($alter_lernbereich, 0, 'grafik.url'));
            // Thumbnail verschieben
            if (file_exists($pfad.$old_verzeichnis.'tmb_'.sql_result($alter_lernbereich, 0, 'grafik.url'))) {
                copy($pfad.$old_verzeichnis.'tmb_'.sql_result($alter_lernbereich, 0, 'grafik.url'), $pfad.$new_verzeichnis.'tmb_'.$dateiname["datei"]);
                unlink($pfad.$old_verzeichnis.'tmb_'.sql_result($alter_lernbereich, 0, 'grafik.url'));
            }
        }
    }
    
	if(!empty($_FILES['file']['name']) || $_FILES['file']['name']!="") {
		//altes loeschen
		$db=new db;
		$bildarray=$db->grafik($_GET["bild_id"]);
		unlink($pfad.$bildarray["url_decode"]);
        // Thumbnail loeschen (falls existent)
        if (file_exists($pfad.$bildarray["tmb_url_decode"]))
            unlink($pfad.$bildarray["tmb_url_decode"]);
		
		//neues speichern
		$dateiname=pfad_und_dateiname($_POST["lernbereich"],'grafik',$name,$tempname,$pfad);
		
		db_conn_and_sql("UPDATE `grafik` SET `url`=".apostroph_bei_bedarf($dateiname["datei"])." WHERE `id`=".injaway($_GET["bild_id"]));
		thumbnail_erstellen($dateiname["pfad"], $dateiname["datei"]);
	}

}
$grafik=db_conn_and_sql("SELECT * FROM `grafik`,`themenzuordnung` WHERE `themenzuordnung`.`typ`=3 AND `themenzuordnung`.`id`=`grafik`.`id` AND `grafik`.`id`=".injaway($_GET["bild_id"]));

$titelleiste="Grafik bearbeiten";
include $pfad."header.php"; ?>
  <body>
	<div id="mf">
		<ul class="r">
			<li><a href="javascript:window.close();" class="icon"><img src="<?php echo $pfad; ?>icons/fenster_schliessen.png" alt="schliessen" /> Fenster schlie&szlig;en</a></li>
		</ul>
	</div>  
	<form action="<?php echo $pfad; ?>formular/grafik_bearbeiten.php?bild_id=<?php echo $_GET["bild_id"]; ?>&amp;eintragen=true" method="post" enctype="multipart/form-data" accept-charset="ISO-8859-1">
      <fieldset><legend>Bild bearbeiten</legend>
	<ol class="divider"><li>  
		<div class="hinweis">Wenn ein neuer Ort eingetragen wird, wird die vorhandene Datei &uuml;berschrieben</div><br />
      <label for="file">Ort<em>*</em>:</label> <input type="file" name="file" size="50" /><br />
      <label for="alt">Beschreibung<em>*</em>:</label> <input type="text" name="alt" size="50" maxlength="60" value="<?php echo html_umlaute(sql_result($grafik,0,"grafik.alt")); ?>" /></li>
	<li>  
		<label for="lernbereich">Lernbereich<em>*</em>:</label> <select name="lernbereich"><?php echo $db->lernbereichoptions(sql_result($grafik,0,"grafik.lernbereich")); ?>
      </select>
	  <br />
			<?php $selected_tags='';
			for($thema=0; sql_result($grafik,$thema,"themenzuordnung.thema")>0; $thema++)
				$selected_tags[$thema]=sql_result($grafik,$thema,"themenzuordnung.thema");
			echo themen_auswahl($pfad, 'thema', $selected_tags);
			?>
      </li></ol>
		<input type="button" class="button" value="speichern" onclick="auswertung=new Array(new Array(0, 'alt','nicht_leer'), new Array(0, 'thema_0','nicht_leer','-'), new Array(0, 'lernbereich','nicht_leer','-')); pruefe_formular(auswertung);" />
      </fieldset>
      </form><?php
		$db=new db;
		$bildarray=$db->grafik(injaway($_GET["bild_id"]));
		echo '<img src="'.$pfad.$bildarray["url"].'" alt="bild" title="altes Bild" style="border: 2px green dashed;" />'; ?>
	</body>
</html>
