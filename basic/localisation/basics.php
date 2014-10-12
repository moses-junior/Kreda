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

$feriennamen=array('Sommer', 'Herbst', 'Weihnachten', 'Winter', 'Ostern', 'Pfingsten');
$monatsnamen_lang=array(1=>'Januar', 2=>'Februar', 3=>'M&auml;rz', 4=>'April', 5=>'Mai', 6=>'Juni', 7=>'Juli', 8=>'August', 9=>'September', 10=>'Oktober', 11=>'November', 12=>'Dezember');
$monatsnamen_kurz=array(1=>'Jan', 2=>'Feb', 3=>'M&auml;r', 4=>'Apr', 5=>'Mai', 6=>'Jun', 7=>'Jul', 8=>'Aug', 9=>'Sep', 10=>'Okt', 11=>'Nov', 12=>'Dez');
$wochennamen_lang=array(0=>'Sonntag', 1=>'Montag', 2=>'Dienstag', 3=>'Mittwoch', 4=>'Donnerstag', 5=>'Freitag', 6=>'Samstag');
$wochennamen_kurz=array(0=>'So', 1=>'Mo', 2=>'Di', 3=>'Mi', 4=>'Do', 5=>'Fr', 6=>'Sa');

$kopfnoten_arten=array(
	array("id"=>0, "name"=>"1-5 (mit Tendenz)", "start"=>1, "ende"=>5, "tendenz"=>1, "kommazahl"=>0),
	array("id"=>1, "name"=>"1-5 (mit Kommazahlen)", "start"=>1, "ende"=>5, "tendenz"=>0, "kommazahl"=>1),
	array("id"=>2, "name"=>"1-4 Sternchen", "start"=>1, "ende"=>5, "tendenz"=>0, "kommazahl"=>0)
);

$farbenarray=array(
	// weis-grautoene
	array('f5f5f5','whitesmoke'),
	array('dcdcdc','gainsboro'),
	array('d3d3d3','lightgrey'),
	array('c0c0c0','silver'),
	// orange-rot
	array('fff5ee','seashell'),
	array('ffa07a','lightsalmon'),
	array('fa8072','salmon'),
	array('e9967a','darksalmon'),
	array('ff7f50','coral'),
	array('ffa500','orange'),
	array('ff5c00','darkorange'),
	array('ff4500','orangered'),
	// gruen - grueengelb - oliv
	array('f5fffa','mintcream'),
	array('f0fff0','honeydew'),
	array('98fb98','palegreen'),
	array('90ee90','lightgreen'),
	array('32cd32','limegreen'),
	array('adff2f','greenyellow'),
	// blau - blaugruen
	array('f0fffff','azure'),
	array('f0f8ff','aliceblue'),
	array('add8e6','lightblue'),
	array('87ceeb','skyblue'),
	array('b0c4de','lightsteelblue'),
	array('00bfff','deepskyblue'),
	array('b0e0e6','powderblue'),
	array('4682b4','steelblue'),
	array('40e0d0','turquoise'),
	//beige-braun
	array('fffaf0','floralwhite'),
	array('fdf5e6','oldlace'),
	array('faebd7','antiquewhite'),
	array('f5deb3','wheat'),
	array('deb887','burlywood'),
	array('fff8dc','cornsilk'),
	array('ffe4b5','moccasin'),
	array('ffdead','navajowhite'),
	array('ffdab9','peachpuff'));
	// alt
/*	array('fee','snow'),
	array('e6e6fa','lavender'),
	array('eed','beige'),
	array('eef','ghostwhite'),
	array('ffe','ivory'),
	array('fde','lavenderblush'),
	array('cff','cff'),array('fcf','fcf'), array('ccf','ccf'), array('ffc','ffc'), array('cfc','cfc'), array('fcc','fcc'), array('ccc','ccc'));*/
?>
