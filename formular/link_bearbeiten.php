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
include "../funktionen.php";
$pfad="../";

if (!proofuser("link", $_GET["link_id"]))
	die("Sie sind hierzu nicht berechtigt.");

if ($_GET["eintragen"]=="true") {
    // Falls der Lernbereich geändert wird, muss das Bild verschoben werden!
    $alter_lernbereich = db_conn_and_sql("SELECT * FROM link WHERE link.id=".injaway($_GET["link_id"]));
    
	if ($_POST["lokal"]==1) $lokal=''; else $lokal=1;
	db_conn_and_sql("UPDATE `link` SET `lokal`=".leer_NULL($lokal).", `beschreibung`=".apostroph_bei_bedarf($_POST['beschreibung']).", `typ`=".injaway($_POST['typ']).", `lernbereich`=".injaway($_POST['lernbereich'])." WHERE `id`=".injaway($_GET["link_id"]));
	
	db_conn_and_sql("DELETE FROM `themenzuordnung` WHERE `typ`=4 AND `id`=".injaway($_GET["link_id"]));
	$thema=0;
	while($_POST["thema_".$thema]!="-" and $thema<10) {
		db_conn_and_sql("INSERT INTO `themenzuordnung` (`typ`,`id`,`thema`) VALUES (4,".injaway($_GET["link_id"]).",".injaway($_POST["thema_".$thema]).");");
		$thema++;
	}
	
	$tempname = $_FILES['file']['tmp_name'];
	$name = $_FILES['file']['name'];

    // Falls der Lernbereich geändert wird, muss die Datei verschoben werden!
    if (sql_result($alter_lernbereich, 0, "link.lernbereich")!=$_POST["lernbereich"]) {
		$db=new db;
        $old_verzeichnis=$db->url(sql_result($alter_lernbereich, 0, "link.lernbereich"));
        $new_verzeichnis=$db->url($_POST["lernbereich"]);
        
        if ($old_verzeichnis != $new_verzeichnis) {
            $dateiname=pfad_und_dateiname($_POST["lernbereich"], $_POST['typ'], sql_result($alter_lernbereich, 0, 'link.url'), $pfad.$old_verzeichnis.sql_result($alter_lernbereich, 0, 'link.url'));
            unlink($pfad.$old_verzeichnis.sql_result($alter_lernbereich, 0, 'link.url'));
        }
    }
    
    
	if(!$_POST['lokal'] and !empty($_FILES['file']['name'])) {
		//altes loeschen
		$db=new db;
		$linkarray=$db->link_id(injaway($_GET["link_id"]));
		unlink($pfad.$linkarray["url_decode"]);
		
		//neues speichern
		$dateiname=pfad_und_dateiname($_POST["lernbereich"],$_POST["typ"],$name,$tempname,$pfad);
		
		db_conn_and_sql("UPDATE `link` SET `url`=".apostroph_bei_bedarf($dateiname["datei"])." WHERE `id`=".injaway($_GET["link_id"]));
		
	}
	if($_POST['lokal'] and $_POST["url"]!="") db_conn_and_sql("UPDATE `link` SET `url`=".apostroph_bei_bedarf($_POST["url"])." WHERE `id`=".injaway($_GET["link_id"]));
}

	$titelleiste="Arbeitsblatt / Folie / Link bearbeiten";
	include $pfad."header.php"; ?>
	<body>
	<div id="mf">
		<ul class="r">
			<li><a href="javascript:window.close();" class="icon"><img src="<?php echo $pfad; ?>icons/fenster_schliessen.png" alt="schliessen" /> Fenster schlie&szlig;en</a></li>
		</ul>
	</div>
	<?php $db=new db;
		$linkarray=$db->link_id($_GET["link_id"]); ?>
	<form action="<?php echo $pfad; ?>formular/link_bearbeiten.php?link_id=<?php echo $_GET["link_id"]; ?>&amp;eintragen=true" method="post" enctype="multipart/form-data" accept-charset="ISO-8859-1">
      <fieldset><legend>Arbeitsblatt / Folie / Link bearbeiten</legend>
		<div class="hinweis">Wenn ein neuer Ort eingetragen wird, wird die vorhandene Datei &uuml;berschrieben</div>
      <label for="typ">Typ<em>*</em>:</label> <select name="typ">
      <option value="1"<?php if($linkarray["typ"]==1) echo ' selected="selected"'; ?>>Arbeitsblatt</option>
      <option value="2"<?php if($linkarray["typ"]==2) echo ' selected="selected"'; ?>>Folie</option>
      <option value="3"<?php if($linkarray["typ"]==3) echo ' selected="selected"'; ?>>Link</option>
      </select>
      <input type="checkbox" name="lokal" id="lokal"<?php if(!$linkarray["lokal"]) echo ' checked="checked"'; ?> value="1" onclick="document.getElementById('file').style.display=this.checked==1?'none':'inline'; document.getElementById('url').style.display=this.checked==1?'inline':'none';" /> Internetadresse<br />
      <label for="file">Ort:</label> <input type="file" id="file" name="file" size="50"<?php if(!$linkarray["lokal"]) echo ' style="display: none;"'; ?> /><input type="text" name="url" id="url" size="35" maxlength="50" <?php if($linkarray["lokal"]) echo ' style="display: none;" value="http://www."'; else echo ' value="'.$linkarray["url"].'"'; ?> /><br />
      <label for="beschreibung">Beschreibung<em>*</em>:</label> <input type="text" name="beschreibung" size="50" maxlength="80" value="<?php echo $linkarray["beschreibung"]; ?>" /><br />
		<?php $selected_tags='';
			for($thema=0; $linkarray["thema"][$thema]["id"]>0; $thema++)
				$selected_tags[$thema]=$linkarray["thema"][$thema]["id"];
			echo themen_auswahl($pfad, 'thema', $selected_tags);
		?>
		<br />
      <label for="lernbereich">Lernbereich<em>*</em>:</label> <select name="lernbereich"><?php echo $db->lernbereichoptions($linkarray["lernbereich"]); ?>
      </select><br />
				<input type="button" class="button" value="speichern" onclick="auswertung=new Array(new Array(0, 'beschreibung','nicht_leer'), new Array(0, 'thema_0','nicht_leer','-'), new Array(0, 'lernbereich','nicht_leer','-')); if (document.getElementById('lokal').checked) auswertung[1]=new Array(0, 'url','nicht_leer','http://www.'); pruefe_formular(auswertung);" />
      </fieldset>
      </form><?php
		if ($linkarray["lokal"]) $ort=$pfad.$linkarray["url"]; else $ort=$linkarray["url"];
		echo '<a href="'.$ort.'" title="alter Link">'.$linkarray["url"].'</a>'; ?>
	</body>
</html>
