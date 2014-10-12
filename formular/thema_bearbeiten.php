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
	$titelleiste="Thema bearbeiten";
	include($pfad."header.php");
	include $pfad."funktionen.php";
?>
  <body>
	<div id="mf">
		<ul class="r">
			<li><a href="javascript:window.close();" class="icon"><img src="<?php echo $pfad; ?>icons/fenster_schliessen.png" alt="schliessen" /> Fenster schlie&szlig;en</a></li>
		</ul>
	</div>
	<div class="inhalt">
<?php
if (!proofuser("thema",$_GET["thema_id"]))
	die("Sie sind hierzu nicht berechtigt.");

if ($_GET["eintragen"]=="true") {
	if ($_POST['oberthema']!=$_GET["thema_id"])
		db_conn_and_sql("UPDATE `thema` SET `bezeichnung`=".apostroph_bei_bedarf($_POST['bezeichnung']).", `fach`=".injaway($_POST['fach']).", `oberthema`=".leer_NULL($_POST['oberthema'])." WHERE `id`=".injaway($_GET["thema_id"]));
	echo '<html><head><script type="text/javascript">opener.location.reload(); window.close();</script></head><body>Sie k&ouml;nnen das Fenster jetzt schlie&szlig;en.</body></html>';
}
else {
$thema=db_conn_and_sql("SELECT * FROM `thema` WHERE `id`=".injaway($_GET["thema_id"]));
?>
<form action="<?php echo $pfad; ?>formular/thema_bearbeiten.php?eintragen=true&amp;thema_id=<?php echo $_GET["thema_id"]; ?>" method="post" accept-charset="ISO-8859-1">
      <fieldset><legend>Thema bearbeiten</legend>
		<p><div class="hinweis">Achtung: Sie sollten das Thema sinnhaft gleich halten, damit die ihm bereits zugeordneten Objekte nicht falsch werden. Legen Sie dazu bei Bedarf lieber ein neues Thema an.</div></p>
		

      <ol class="divider"><li><label for="bezeichnung">Bezeichnung<em>*</em>:</label><input type="text" name="bezeichnung" size="25" maxlength="30" value="<?php echo html_umlaute(sql_result($thema,0,"thema.bezeichnung")); ?>" /></li>
      <li><label for="oberthema">Unterthema von:</label><select name="oberthema" onchange="this.value==''?document.getElementById('fachauswahl').style.display='block':document.getElementById('fachauswahl').style.display='none';"><option value="">kein Unterthema</option>
		<?php echo $db->themenoptions(sql_result($thema,0,"thema.oberthema"), $_GET["thema_id"]); ?>
	  </select><br />
		<span id="fachauswahl"<?php if(sql_result($thema,0,"thema.oberthema")>0) echo ' style="display:none;"'; ?>>
      <label for="fach">Fach<em>*</em>:</label><select name="fach"><?php $result=db_conn_and_sql("SELECT * FROM `faecher` WHERE `faecher`.`user`=0 OR (`faecher`.`user`=".$_SESSION['user_id']." AND `faecher`.`anzeigen`=1) ORDER BY `faecher`.`name`");
	  for ($i=0;$i<sql_num_rows($result);$i++) { ?>
               <option value="<?php echo sql_result($result,$i,'faecher.id'); ?>"<?php if(sql_result($result,$i,'faecher.id')==sql_result($thema,0,"thema.fach")) echo ' selected="selected"'; ?>><?php echo html_umlaute(sql_result($result,$i,'faecher.name')); ?></option><?php } ?>
            </select>
		</span>
		</li>
		</ol>
        <br />
      <button onclick="fenster('<?php echo $pfad; ?>formular/thema_delete.php?id=<?php echo $_GET["thema_id"]; ?>', 'Thema l&ouml;schen'); return false;"><img src="<?php echo $pfad; ?>icons/delete.png" alt="delete" /> l&ouml;schen</button>
      <button style="float: right;" onclick="auswertung=new Array(new Array(0, 'bezeichnung','nicht_leer')); pruefe_formular(auswertung);"><img src="<?php echo $pfad; ?>icons/page_save.png" alt="save" /> speichern</button>
		
      </fieldset>
      </form>
<?php } ?>
</div>
</body>
</html>
