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

	// wird nicht verwendet
    $upgrade_array = array(
        array(
            "version"=>"0.97A Beta",
            "description"=>array("punkt 1 fixed", "alles besser"),
            "name"=>"kreda0_97ABeta",
            "delete"=>array(
                "verzeichnis/file.php",
                "verzeichnis/file2.php" ),
            "create"=>array(
                array("location"=>"verzeichnis/file.php", "rights"=>"755")),
            "update"=>array(
                array("location"=>"verzeichnis/file.php", "rights"=>"755"))),
        array(
            "version"=>"0.96D Beta",
            "description"=>array("punkt 1 fixed", "alles besser"),
            "name"=>"kreda0_96DBeta",
            "delete"=>array(
                "verzeichnis/file.php",
                "verzeichnis/file2.php" ),
            "create"=>array(
                array("location"=>"verzeichnis/file.php", "rights"=>"755")),
            "update"=>array(
                array("location"=>"verzeichnis/file.php", "rights"=>"755"))),
        array(
            "version"=>"0.95 Beta")
    );
    
    function upgrade_to_latest_from($from_version, $upgrade_array) {
		$i=count($upgrade_array)-1;
		// Laufvariable zur Ausgangs-Version -1 bewegen
		while ($from_version!=$upgrade_array[$i+1]["version"] and $i>=0)
			$i--;
		
		$todo=array(
			"description"=>'',
            "delete"=>'',
            "create"=>'',
            "update"=>'');
		for ($k=$i; $k>=0; $k--) {
			$todo["description"][$k]="<h3>".$upgrade_array[$k]["version"]."</h3> <ul>";
			for ($n=0; $n<count($upgrade_array[$k]["description"]); $n++)
				$todo["description"][$k].="<li>".$upgrade_array[$k]["description"][$n]."</li>";
			$todo["description"][$k].="</ul>";
			
			for ($n=0; $n<count($upgrade_array[$k]["delete"]); $n++)
				$todo["delete"][]=$upgrade_array[$k]["delete"][$n];
			
			for ($n=0; $n<count($upgrade_array[$k]["create"]); $n++)
				$todo["create"][]=$upgrade_array[$k]["create"][$n];
			
			for ($n=0; $n<count($upgrade_array[$k]["update"]); $n++)
				$todo["update"][]=$upgrade_array[$k]["update"][$n];
		}
		
		 return $todo;
	}
	
	$todo=upgrade_to_latest_from("0.95 Beta", $upgrade_array);
	/*for ($i=0; $i<count($todo["delete"]);$i++)
		echo "del ".$todo["delete"][$i]."<br />";*/
?>
