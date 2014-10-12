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
$titelleiste="Sch&uuml;lerzusammensetzung der Fach-Klasse-Kombination &auml;ndern";
include($pfad."header.php");
include $pfad."funktionen.php";
if (!proofuser("fach_klasse",$_GET["id"]))
	die("Sie sind hierzu nicht berechtigt.");

if ($_GET["eintragen"]=="true") {
    db_conn_and_sql("DELETE FROM gruppe WHERE fach_klasse=".injaway($_GET["id"]));
    if ($_POST["sortiert"])
		for ($i=0; $i<count($_GET["pupil"]); $i++)
			db_conn_and_sql("INSERT INTO gruppe (fach_klasse, schueler, position) VALUES (".injaway($_GET["id"]).", ".injaway($_GET["pupil"][$i]).", ".$i.");");
	else
		for ($i=0; $i<count($_GET["pupil"]); $i++)
			db_conn_and_sql("INSERT INTO gruppe (fach_klasse, schueler, position) VALUES (".injaway($_GET["id"]).", ".injaway($_GET["pupil"][$i]).", NULL);");
}

    ?>
    <style>
    form fieldset { float: left; }
	#sortable1, #sortable2 { list-style-type: none; margin: 0; float: left; margin-right: 10px; padding: 5px; width: 240px; }
	#sortable1 li, #sortable2 li { margin: 0 5px 5px 5px; padding: 5px; font-size: 1.2em; width: 220px; }
	</style>
	<script>
	$(function() {
		$( "#sortable1, #sortable2" ).sortable({
			connectWith: ".connectedSortable",
            placeholder: "ui-state-highlight"
		}).disableSelection();
	});
	</script>
<body>
    <?php
        $subject_class_name=$subject_classes->nach_ids[injaway($_GET["id"])]["name"];
        //$subject_class_name=db_conn_and_sql("SELECT * FROM fach_klasse, klasse, faecher WHERE fach_klasse.klasse=klasse.id AND fach_klasse.fach=faecher.id AND fach_klasse.id=".injaway($_GET["id"]));
        //$subject_class_name="".($aktuelles_jahr-sql_result($subject_class_name,0,"klasse.einschuljahr")+1).sql_result($subject_class_name,0,"klasse.endung")." ".sql_result($subject_class_name,0,"faecher.kuerzel")." ".sql_result($subject_class_name,0,"fach_klasse.gruppen_name");
        
        $selected_class=$_GET["class"];
        if ($selected_class<1)
            $selected_class=sql_result(db_conn_and_sql("SELECT fach_klasse.klasse FROM fach_klasse WHERE id=".injaway($_GET["id"])), 0, "fach_klasse.klasse");
        $pupils_in_class=db_conn_and_sql("SELECT schueler.* FROM schueler WHERE aktiv=1 AND klasse=".$selected_class." ORDER BY position, name, vorname");
        $pupils_in_subject_class=schueler_von_fachklasse(injaway($_GET["id"]));
        
        $class_selecter=db_conn_and_sql("SELECT DISTINCT klasse.*, schule.*
            FROM klasse, schule_user, schule
            WHERE klasse.schule=schule.id
                AND schule_user.schule=klasse.schule
                AND schule_user.aktiv=1
                AND schule_user.user=".$_SESSION['user_id']."
            ORDER BY schule_user.aktiv DESC, schule_user.schule, klasse.einschuljahr DESC, klasse.endung");
    ?>
    <div class="inhalt">
		<div class="hinweis">Speichern Sie jedes Mal vor dem Wechsel in eine andere Klasse.</div>
            <form id="form" action="<?php echo $pfad; ?>formular/fach_klasse_group.php?eintragen=true&amp;id=<?php echo $_GET["id"]; ?>" method="post">
                <fieldset><legend>Klasse 
                    <select name="class_selecter" onchange="document.location.href='<?php echo $pfad; ?>formular/fach_klasse_group.php?id=<?php echo $_GET["id"]; ?>&amp;class='+this.value;"><?php
                for ($i=0; $i<sql_num_rows($class_selecter);$i++) { ?>

                    <option value="<?php echo sql_result($class_selecter, $i, "klasse.id");
                    if (sql_result($class_selecter, $i, "klasse.id")==$selected_class)
						echo '" selected="selected';
					?>"><?php echo $school_classes->nach_ids[sql_result ($class_selecter, $i, 'klasse.id')]["name"]; ?></option><?php
                } ?>
                    </select></legend>
                <ul id="sortable1" class="connectedSortable"><?php
                
                $gruppenzuordnung_existiert=true;
                if (sql_num_rows(db_conn_and_sql("SELECT gruppe.fach_klasse FROM gruppe WHERE fach_klasse=".injaway($_GET["id"])))==0)
					$gruppenzuordnung_existiert=false;
                
                for ($i=0; $i<sql_num_rows($pupils_in_class);$i++) {
                    $pupil_is_not_in_subject_class=true;
                    if ($gruppenzuordnung_existiert)
						for ($n=0; $n<sql_num_rows($pupils_in_subject_class);$n++)
							if (sql_result($pupils_in_class, $i, "schueler.id")==sql_result($pupils_in_subject_class, $n, "schueler.id"))
								$pupil_is_not_in_subject_class=false;
					
					if ($pupil_is_not_in_subject_class) {
                    ?>

                    <li class="ui-state-default" id="pupil_<?php echo sql_result($pupils_in_class, $i, "schueler.id"); ?>"><?php echo html_umlaute(sql_result($pupils_in_class, $i, "schueler.name")).", ".html_umlaute(sql_result($pupils_in_class, $i, "schueler.vorname")); ?></li><?php
                    }
                } ?>
                </ul>
            </fieldset>
                
            <fieldset><legend>Fach-Klasse <?php echo $subject_class_name; ?></legend>
                <ul id="sortable2" class="connectedSortable" style="min-height: 150px;"><?php
                if ($gruppenzuordnung_existiert)
                for ($i=0; $i<sql_num_rows($pupils_in_subject_class);$i++) { ?>

                    <li class="ui-state-highlight" id="pupil_<?php echo sql_result($pupils_in_subject_class, $i, "schueler.id"); ?>"><?php echo html_umlaute(sql_result($pupils_in_subject_class, $i, "schueler.name")).", ".html_umlaute(sql_result($pupils_in_subject_class, $i, "schueler.vorname")); ?></li><?php
                } ?>
                </ul>
                <label for="sortiert" style="width: 300px;" title="wird andernfalls nach Klasse und Position im Klassenbuch sortiert">In dieser Reihenfolge eintragen: <input type="checkbox" name="sortiert" value="1" /></label>
            </fieldset>
                <br style="clear: both;" />
                <button style="float: right;" onclick="document.getElementById('form').action += '&amp;'+$( '#sortable2' ).sortable('serialize');"><img src="<?php echo $pfad; ?>icons/page_save.png" alt="save" /> speichern</button>
                <p style="clear: both;">
                    <span class="hinweis">Wird der Fach-Klasse-Kombination kein Sch&uuml;ler zugeordnet, ist darin automatisch die gesamte Klasse.</span>
                </p>
            </form>
        
    </div>



    </body>
</html>
