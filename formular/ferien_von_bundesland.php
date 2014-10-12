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
$pfad='../';
include $pfad."funktionen.php";

if ($_GET["eintragen"]=="true") {
	if (userrigths("schuljahresdaten", 0)!=2)
		die("Sie haben nicht das Recht, die Ferien f&uuml;r dieses Bundesland zu &auml;ndern.");
	$jahr=injaway($_POST["schuljahr"]);
	$bundesland_nr=injaway($_POST["bundesland"]);
}
else {
	$jahr=injaway($_GET["jahr"]);
	$bundesland_nr=injaway($_GET["bundesland"]);
}

$result_ferien = db_conn_and_sql ( 'SELECT `ferien`.*
	FROM `ferien`
	WHERE `ferien`.`schuljahr` ='.$jahr.'
		AND `ferien`.`bundesland`='.$bundesland_nr.'
	ORDER BY `welche`' );
$result_next_year = db_conn_and_sql ( 'SELECT `ferien`.*
	FROM `ferien`
	WHERE `ferien`.`schuljahr` ='.($jahr+1).'
		AND `ferien`.`bundesland`='.$bundesland_nr.'
	ORDER BY `welche`');

switch ($_GET["eintragen"]) {
	case "true":
		// Ferien koennen nur vom Eigentuemer der Schule (TODO) bearbeitet werden oder vom Admin
		if (($_POST["bundesland"]<16 and userrigths("admin",0)) or ($_POST["bundesland"]>=16 and userrigths("schuljahresdaten", $_POST["bundesland"])==2))
		{
			// sommerferien
			$schuljahresbeginn_row=sql_fetch_assoc($result_ferien);
			$schuljahresende_row=sql_fetch_assoc($result_next_year);
			if ($schuljahresbeginn_row["welche"]==0 and ($schuljahresbeginn_row["beginn"]>"2000-00-00" or $schuljahresbeginn_row["ende"]>"2000-00-00")) {
				db_conn_and_sql("UPDATE `ferien` SET `ende`=".apostroph_bei_bedarf(datum_punkt_zu_strich($_POST["jahr_beginn"]))." WHERE `welche`=0 AND `schuljahr`=".$jahr." AND `bundesland`=".$bundesland_nr);
			}
			else  {
				db_conn_and_sql("INSERT INTO `ferien` (`welche`, `ende`, `schuljahr`, `bundesland`) VALUES (0, ".apostroph_bei_bedarf(datum_punkt_zu_strich($_POST["jahr_beginn"])).", ".$jahr.", ".$bundesland_nr.");");
			}
			if ($schuljahresende_row["welche"]==0 and ($schuljahresende_row["beginn"]>"2000-00-00" or $schuljahresende_row["ende"]>"2000-00-00")) {
				db_conn_and_sql("UPDATE `ferien` SET `beginn`=".apostroph_bei_bedarf(datum_punkt_zu_strich($_POST["jahr_ende"]))." WHERE `welche`=0 AND `schuljahr`=".($jahr+1)." AND `bundesland`=".$bundesland_nr);
			}
			else  {
				db_conn_and_sql("INSERT INTO `ferien` (`welche`, `beginn`, `schuljahr`, `bundesland`) VALUES (0, ".apostroph_bei_bedarf(datum_punkt_zu_strich($_POST["jahr_ende"])).", ".($jahr+1).", ".$bundesland_nr.");");
			}
			
			//Ferien
			for($i=1;$i<count($feriennamen); $i++) {
				if($_POST["ferien_belegt_".$i]=="true")
					db_conn_and_sql("UPDATE `ferien` SET `beginn`=".apostroph_bei_bedarf(datum_punkt_zu_strich($_POST["von_".$i])).", `ende`=".apostroph_bei_bedarf(datum_punkt_zu_strich($_POST["bis_".$i]))." WHERE `schuljahr`=".$jahr." AND `welche`=".$i." AND `bundesland`=".$bundesland_nr);
				else
					db_conn_and_sql("INSERT INTO `ferien` (`welche`,`schuljahr`,`beginn`,`ende`, `bundesland`) VALUES (".$i.", ".$jahr.", ".apostroph_bei_bedarf(datum_punkt_zu_strich($_POST["von_".$i])).", ".apostroph_bei_bedarf(datum_punkt_zu_strich($_POST["bis_".$i])).", ".$bundesland_nr.")");
			}
		}
		?>
		<html><head><script type="text/javascript">opener.location.reload(); window.close();</script></head><body>Sie k&ouml;nnen das Fenster jetzt schlie&szlig;en.</body></html>
		<?php
		break;
/*	case "loeschen":
		if (proofuser("aufsicht",$_GET["aufsicht_id"]))
		db_conn_and_sql("DELETE FROM aufsicht WHERE id=".injaway($_GET["aufsicht_id"]));
		?>
		<html><head><script type="text/javascript">opener.location.reload(); window.close();</script></head><body>Sie k&ouml;nnen das Fenster jetzt schlie&szlig;en.</body></html>
		<?php
	break; */
	default:
		
		$titelleiste="Ferien f&uuml;r das Bundesland ".$bundesland[$bundesland_nr]["name"]." im Jahr ".$jahr."/".($jahr+1);
		include $pfad."header.php";
		?>
			<body>
			<div class="inhalt">
			<form action="<?php echo $pfad; ?>formular/ferien_von_bundesland.php?eintragen=true" method="POST" accept-charset="ISO-8859-1">
				<?php
				echo '<input type="hidden" name="schuljahr" value="'.$jahr.'" />';
				echo '<input type="hidden" name="bundesland" value="'.$bundesland_nr.'" />';
				?>
				<fieldset><legend><?php echo $titelleiste; ?></legend>
        <script>
            $(function() {
                var ferien=0;
                var ferien_string='';
                while (document.getElementById('von_'+ferien+'')) {
                    if (ferien>0)
                        ferien_string+=', ';
                    ferien_string+='#von_'+ferien+', #bis_'+ferien+'';
                    ferien++;
                }
                
                // datepicker fuer Schuljahr und Ferien
                $("#jahr_beginn, #jahr_ende").datepicker( "option", "onSelect", function(selectedDate) {
                        var option = this.id == "jahr_beginn" ? "minDate" : "maxDate",
                            instance = $(this).data("datepicker"),
                            date = $.datepicker.parseDate(
                                instance.settings.dateFormat ||
                                $.datepicker._defaults.dateFormat,
                                selectedDate, instance.settings );
                        if (this.id == "jahr_beginn")
                            $("#jahr_ende").datepicker( "option", option, date );
                        else
                            $("#jahr_beginn").datepicker( "option", option, date );
                        $(ferien_string).datepicker( "option", option, date );
                        $("#hjw").datepicker( "option", option, date );
                });
                
                
                $(ferien_string).datepicker( "option", "onSelect", function(selectedDate) {
                        // wird bei mehr als 9 Ferien problematisch
                        var option = (this.id.substring(0,4) == "von_") ? "minDate" : "maxDate",
                            instance = $(this).data("datepicker"),
                            date = $.datepicker.parseDate(
                                instance.settings.dateFormat ||
                                $.datepicker._defaults.dateFormat,
                                selectedDate, instance.settings );
                        if (this.id.substring(0,4) == "von_")
                            $("#bis_"+this.id.substring(4, 5)+"").datepicker( "option", option, date );
                        if (this.id.substring(0,4) == "bis_")
                            $("#von_"+this.id.substring(4, 5)+"").datepicker( "option", option, date );
                });
                
                // Initialwerte der Ferien
                if ($( "#jahr_beginn" ).datepicker("getDate")!=null) {
                    $( "#jahr_ende" ).datepicker("option", "minDate", $( "#jahr_beginn" ).datepicker("getDate") );
                }
                if ($( "#jahr_ende" ).datepicker("getDate")!=null) {
                    $( "#jahr_beginn" ).datepicker("option", "maxDate", $( "#jahr_ende" ).datepicker("getDate") );
                }
				
                var ferien=0;
                while (document.getElementById('von_'+ferien+'')) {
                    if ($( "#jahr_beginn" ).datepicker("getDate")!=null) {
                        $( "#von_"+ferien+"" ).datepicker("option", "minDate", $( "#jahr_beginn" ).datepicker("getDate") );
                        $( "#bis_"+ferien+"" ).datepicker("option", "minDate", $( "#jahr_beginn" ).datepicker("getDate") );
                    }
                    if ($( "#jahr_ende" ).datepicker("getDate")!=null) {
                        $( "#von_"+ferien+"" ).datepicker("option", "maxDate", $( "#jahr_ende" ).datepicker("getDate") );
                        $( "#bis_"+ferien+"" ).datepicker("option", "maxDate", $( "#jahr_ende" ).datepicker("getDate") );
                    }
                    ferien++;
                }
                
            });
        </script>
        
        Von: <input type="text" class="datepicker" id="jahr_beginn" name="jahr_beginn" size="8" maxlength="10" value="<?php echo datum_strich_zu_punkt(sql_result ( $result_ferien, 0, 'ferien.ende' )); ?>" />
        Bis: <input type="text" class="datepicker" id="jahr_ende" name="jahr_ende" size="8" maxlength="10" value="<?php echo datum_strich_zu_punkt(sql_result ( $result_next_year, 0, 'ferien.beginn' )); ?>" /> <a href="http://www.schulferien.org/Schulferien_nach_Schuljahren/<?php echo $aktuelles_jahr.'_'.($aktuelles_jahr+1).'/'.$aktuelles_jahr.'_'.($aktuelles_jahr+1);?>.html" target="_blank">Ferientermine-Webseite (extern)</a>
        <br /><br />
        
        <table class="tabelle" cellspacing="0">
          <tr>
            <th>Ferien</th>
            <th>von</th>
            <th>bis</th>
          </tr>
          <?php
			$k=1;
				for ($i=1;$i<count($feriennamen); $i++) {
					//if (($i+1)==sql_result ( $result_ferien, $k, 'ferien.welche' ))
          ?>
          <tr>
            <td><input type="hidden" name="ferien_belegt_<?php echo $i; ?>" value="<?php if ($i==sql_result ( $result_ferien, $k, 'ferien.welche' ) and sql_result ( $result_ferien, $k, 'ferien.beginn' )!="") echo "true"; ?>" /><?php echo $feriennamen[$i]; ?></td>
            <td><input type="text" class="datepicker" id="von_<?php echo $i; ?>" name="von_<?php echo $i; ?>" size="8" maxlength="10" value="<?php if (($i)==sql_result ( $result_ferien, $k, 'ferien.welche' )) echo datum_strich_zu_punkt(sql_result ( $result_ferien, $k, 'ferien.beginn' )); ?>" /></td>
            <td><input type="text" class="datepicker" id="bis_<?php echo $i; ?>" onfocus="if (this.value=='') this.value=document.getElementById('von_<?php echo $i; ?>').value;" name="bis_<?php echo $i; ?>" size="8" maxlength="10" value="<?php if ($i==sql_result ( $result_ferien, $k, 'ferien.welche' )) echo datum_strich_zu_punkt(sql_result ( $result_ferien, $k, 'ferien.ende' )); ?>" /></td>
          </tr>
          <?php
			if ($i==sql_result ( $result_ferien, $k, 'ferien.welche' ))
				$k++;
		} ?>
        </table>
				
				<button style="float: right;"><img src="<?php echo $pfad; ?>icons/page_save.png" alt="save" /> speichern</button>
			</fieldset>
			</form>
			</div>
			</body>
		</html>
	<?php
	break;
}
?>
