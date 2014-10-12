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
	$titelleiste="Lehrplan bearbeiten";
	include($pfad."header.php");
	include $pfad."funktionen.php";
	if (!proofuser("lehrplan", $_GET["id"]))
		die("Das ist nicht Ihr Lehrplan.");
	?>
  <body>
	<div id="mf">
		<ul class="r">
			<li><a href="javascript:window.close();" class="icon"><img src="<?php echo $pfad; ?>icons/fenster_schliessen.png" alt="schliessen" /> Fenster schlie&szlig;en</a></li>
		</ul>
	</div>
	<div class="inhalt">
<?php
if ($_GET["eintragen"]=="true") {
    if ($_POST['aktiv']==1)
        $aktiv=1;
    else
        $aktiv=0;
	if (proofuser("lehrplan", $_GET["id"])) {
		db_conn_and_sql("UPDATE `lehrplan` SET `bemerkung`=".apostroph_bei_bedarf($_POST['bemerkung']).", `jahr`=".leer_NULL($_POST['jahr']).", `von`=".leer_NULL($_POST['von']).", `bis`=".leer_NULL($_POST['bis']).", `zusatz`=".apostroph_bei_bedarf($_POST['zusatz'])." WHERE `id`=".injaway($_GET["id"]));
		db_conn_and_sql("UPDATE lp_user SET aktiv=".$aktiv." WHERE lehrplan=".injaway($_GET["id"])." AND user=".$_SESSION['user_id']);
	}
	echo '<html><head><script type="text/javascript">opener.location.reload(); window.close();</script></head><body>Sie k&ouml;nnen das Fenster jetzt schlie&szlig;en.</body></html>';
}
else {
	if (proofuser("lehrplan", $_GET["id"]))
		$lehrplan=db_conn_and_sql("SELECT * FROM `lehrplan`, `lp_user` WHERE `lp_user`.`lehrplan`=`lehrplan`.`id` AND `lehrplan`.`id`=".injaway($_GET["id"]));
?>
        <div class="tooltip" id="tt_abzweig">
            <p>Gibt es im Lehrplan eine Verzweigung (z.B. Deutsch Leistungskurs Klasse 11 und Deutsch Grundkurs Klasse 11), bei der verschiedene Lernbereichsrichtungen aufkommen, erstellen Sie einen weiteren Lehrplan (z.B. von Klassenstufe 11 bis 12) mit der Abzweigung "LK".</p>
            <p><div class="hinweis">Nutzen Sie am besten K&uuml;rzel aus maximal zwei Buchstaben.</div></p></div>
	<form action="<?php echo $pfad; ?>formular/lehrplan_bearbeiten.php?eintragen=true&amp;id=<?php echo $_GET["id"]; ?>" method="post" accept-charset="ISO-8859-1">
      <fieldset><legend>Lehrplan bearbeiten</legend>
      <ol class="divider">
        <li>
            <label for="jahr">Lehrplan-Jahr<em>*</em>:</label> <input type="text" name="jahr" size="5" maxlength="4" value="<?php echo html_umlaute(sql_result($lehrplan,0,"lehrplan.jahr")); ?>" /><br />
            <label for="bemerkung">Bemerkung:</label> <input type="text" name="bemerkung" size="30" maxlength="200" value="<?php echo html_umlaute(sql_result($lehrplan,0,"lehrplan.bemerkung")); ?>" /></li>
        <li>
            <label for="von">Klassenstufen<em>*</em>:</label> <input type="text" name="von" size="2" maxlength="2" value="<?php echo sql_result($lehrplan,0,"lehrplan.von"); ?>" /> - <input type="text" name="bis" size="2" maxlength="2" value="<?php echo sql_result($lehrplan,0,"lehrplan.bis"); ?>" /><br />
            <label for="zusatz">Abzweigung: <img src="<?php echo $pfad; ?>icons/information.png" class="hilfe_tooltip" alt="Hilfe" onmouseover="showWMTT('tt_abzweig')" onmouseout="hideWMTT()" /></label> <input type="text" name="zusatz" value="<?php echo sql_result($lehrplan,0,"lehrplan.zusatz"); ?>" size="2" maxlength="10" /><br />
            <label for="aktiv">aktiv<em>*</em>:</label> <input type="checkbox" value="1" name="aktiv"<?php if (sql_result($lehrplan,0,"lp_user.aktiv")==1) echo ' checked="checked"'; ?>" />
            </li>
		</ol>
        <br />
      <?php if (sql_result($lehrplan,0,"lehrplan.aktiv")==0) { ?>
      <button onclick="document.location.href='<?php echo $pfad; ?>formular/lehrplan_delete.php?id=<?php echo $_GET["id"]; ?>'; return false;" title="kann je nach Anzahl der Eintragungen bzw. Rechnerleistung einige Zeit in Anspruch nehmen"><img src="<?php echo $pfad; ?>icons/delete.png" alt="delete" /> Lehrplan l&ouml;schen</button>
      <?php } ?>
      <button style="float: right;" onclick="auswertung=new Array(new Array(0, 'jahr','natuerliche_zahl'), new Array(0, 'von','natuerliche_zahl'), new Array(0, 'bis','natuerliche_zahl')); pruefe_formular(auswertung); return false;"><img src="<?php echo $pfad; ?>icons/page_save.png" alt="save" /> speichern</button>
		
      </fieldset>
      </form>
<?php } ?>
</div>
</body>
</html>
