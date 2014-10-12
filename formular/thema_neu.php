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

$pfad="../";
$titelleiste="Neues Thema eingeben";
include $pfad."header.php";
include $pfad."funktionen.php";
?>
<body>
<div class="inhalt">
	<div id="mf">
		<ul class="r">
			<li><a href="javascript: opener.location.reload(); window.close();" class="icon">
				<img src="<?php echo $pfad; ?>icons/fenster_schliessen.png" alt="x" /> Fenster schlie&szlig;en</a></li>
		</ul>
	</div>

<?php
if ($_GET["eintragen"]=="true") {
	// wenn oberthema ausgewaehlt, dann ist das fach des oberthemas das fach
	if ($_POST['oberthema']>0) $fach=sql_result(db_conn_and_sql("SELECT * FROM thema WHERE thema.id=".injaway($_POST['oberthema'])),0,"thema.fach");
	else $fach=injaway($_POST['fach']);
	
	db_conn_and_sql("INSERT INTO `thema` (`bezeichnung`, `fach`, `oberthema`, `user`) VALUES
	(".apostroph_bei_bedarf($_POST['bezeichnung']).", ".$fach.", ".leer_NULL($_POST['oberthema']).", ".$_SESSION['user_id'].");");
	echo 'Thema '.$_POST['bezeichnung'].' erfolgreich eingetragen.';
}
 ?>
<form action="<?php echo $pfad; ?>formular/thema_neu.php?eintragen=true" method="post" accept-charset="ISO-8859-1">
      <fieldset><legend>Neues Thema eintragen</legend>
      <ol class="divider"><li><label for="bezeichnung">Bezeichnung<em>*</em>:</label><input type="text" name="bezeichnung" size="25" maxlength="30" /></li>
      <li><label for="oberthema">Unterthema von:</label><select name="oberthema" onchange="this.value==''?document.getElementById('fachauswahl').style.display='block':document.getElementById('fachauswahl').style.display='none';"><option value="">kein Unterthema</option>
	<?php echo $db->themenoptions(0); ?>
	  </select>
	  <br />
		<span id="fachauswahl">  
		<label for="fach">Fach<em>*</em>:</label><select name="fach"><?php $result=db_conn_and_sql("SELECT * FROM `faecher` WHERE `faecher`.`user`=0 OR (`faecher`.`user`=".$_SESSION['user_id']." AND `faecher`.`anzeigen`=1) ORDER BY `faecher`.`name`");
			for ($i=0;$i<sql_num_rows($result);$i++) { ?>
               <option value="<?php echo sql_result($result,$i,'faecher.id'); ?>"><?php echo html_umlaute(sql_result($result,$i,'faecher.name')); ?></option><?php } ?>
            </select>
		</span></li>
		</ol>
      <input type="button" class="button" value="eintragen" onclick="auswertung=new Array(new Array(0, 'bezeichnung','nicht_leer')); pruefe_formular(auswertung);" />
      </fieldset>
      </form>
</div>
</body>
</html>
