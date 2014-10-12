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

include($pfad."header.php");
include($pfad."funktionen.php");

?>

<script>
	function insertFile(id) {
		try {
			parent.$.markItUp( { replaceWith:'[file;'+id+']' } );
            //parent.$.picturelibdialog.dialog('close');
		} catch(e) {
			alert("No markItUp! Editor found");
		}
	}
	function insertFileWS(id) {
		try {
			parent.$.markItUp( { replaceWith:'[filews;'+id+']' } );
		} catch(e) {
			alert("No markItUp! Editor found");
		}
	}
</script>
<body>
	<input type="checkbox" class="toggle" id="link_neu" name="link_neu" value="1"<?php if (!$_GET["bestehende"]) echo ' checked="checked"'; ?> onchange="if (this.checked) {
                window.document.getElementById('fieldset_neuer_Link').style.display = 'block';
                window.document.getElementById('fieldset_bestehender_Link').style.display = 'none';}
            else {
                window.document.getElementById('fieldset_bestehender_Link').style.display = 'block';
                window.document.getElementById('fieldset_neuer_Link').style.display = 'none';};" />
            <label for="link_neu" style="width: 150px;"><img src="<?php echo $pfad; ?>icons/neu.png" alt="neu" title="neue Datei" style="float: left;" /> neue Datei</label>
        <fieldset id="fieldset_neuer_Link"<?php if ($_GET["bestehende"]) echo ' style="display: none;"'; ?>><legend>neue Datei</legend>
        
		<?php eintragung_grafik_link($pfad, "", "file"); ?>
        </fieldset>
        <fieldset id="fieldset_bestehender_Link"<?php if (!$_GET["bestehende"]) echo ' style="display: none;"'; ?>><legend>bestehende Datei</legend>
     <?php echo eingetragenes_zeigen("link",2,$pfad.'lessons/filelib.php?bestehende=true',"","",$pfad); ?>
    </fieldset>

</body>
</html>
