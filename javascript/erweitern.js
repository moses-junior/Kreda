function formatText(el,tag){
   var selectedText = document.selection?document.selection.createRange().text:el.value.substring(el.selectionStart,el.selectionEnd);
   if(selectedText!=''){
      var newText='['+tag+']'+selectedText+'[/'+tag+']';
      el.value=el.value.replace(selectedText,newText)
   }
}


/* datum punkt zu strich */
function datum_punkt_zu_strich (datum) {
	datum_array=datum.split(".");
	if (datum_array[2]) {
		if (datum_array[0].length==1) datum_array[0]='0' + datum_array[0];
		if (datum_array[1].length==1) datum_array[1]='0' + datum_array[1];
		if (datum_array[2].length==2) datum_array[2]='20' + datum_array[2];
		
		/*alert(parseInt(datum_array[2]) + '-' + (parseInt(datum_array[1])+0) + '-' + (parseInt(datum_array[0])+0));*/
		return datum_array[2] + '-' + datum_array[1] + '-' + datum_array[0];
	}
	else return 'kein_datum';
}

/* Formulare mit Javascript pruefen */
// auswertung in der Form: Formularnummer (meist 0), name des Formularelements, typ, weitere einschraenkungen
function pruefe_formular(auswertung) {
	var ok=1;
	var i=0;
	while (auswertung[i]) {
		var zaehler=0;
		var formular = document.getElementsByTagName('form')[auswertung[i][0]];
		while (formular[zaehler] && formular[zaehler].name!=auswertung[i][1]) zaehler++;
		switch (auswertung[i][2]) {
			case 'datum':
				if (datum_punkt_zu_strich(formular[zaehler].value)<auswertung[i][3] || datum_punkt_zu_strich(formular[zaehler].value)>auswertung[i][4]) {
					formular[zaehler].style.border = "solid red 1px";
					/* alert('Das Datum muss richtig eingegeben werden: ' + formular[zaehler].value); */
					ok=0;
				}
				else formular[zaehler].style.border = "solid green 1px";
				break;
		
			case 'zeit':
				if (formular[zaehler].value<auswertung[i][3] || formular[zaehler].value>auswertung[i][4]) {
					formular[zaehler].style.border = "solid red 1px";
					/* alert('Das Datum muss richtig eingegeben werden: ' + formular[zaehler].value); */
					ok=0;
				}
				else formular[zaehler].style.border = "solid green 1px";
				break;
			
			case 'yubikey':
				if (formular[zaehler].value.length!=44) {
					formular[zaehler].style.border = "solid red 1px";
					/* alert(auswertung[i][1] + ' darf nicht leer sein.'); */
					ok=0;
				}
				else formular[zaehler].style.border = "solid green 1px";
				break;
							
			case 'nicht_leer':
				if (formular[zaehler].value=='' || (auswertung[i][3] && formular[zaehler].value==auswertung[i][3])) {
					formular[zaehler].style.border = "solid red 1px";
					/* alert(auswertung[i][1] + ' darf nicht leer sein.'); */
					ok=0;
				}
				else formular[zaehler].style.border = "solid green 1px";
				break;
		
			case 'email':
				if (formular[zaehler].value.search('@') >= 1 &&
						formular[zaehler].value.lastIndexOf('.') > formular[zaehler].value.search('@') &&
						formular[zaehler].value.lastIndexOf('.') >= formular[zaehler].value.length-5)
					formular[zaehler].style.border = "solid green 1px";
				else {
					formular[zaehler].style.border = "solid red 1px";
					/* alert(auswertung[i][1] + ' darf nicht leer sein.'); */
					ok=0;
				}
				break;
			
			case 'pos_komma_zahl':
				if (isNaN(formular[zaehler].value.replace(/,/, '.')) || formular[zaehler].value<0 || formular[zaehler].value=='') {
					formular[zaehler].style.border = "solid red 1px";
					ok=0;
				}
				else formular[zaehler].style.border = "solid green 1px";
				break;
			
			case 'komma_zahl':
				if (isNaN(formular[zaehler].value.replace(/,/, '.')) || formular[zaehler].value=='') {
					formular[zaehler].style.border = "solid red 1px";
					ok=0;
				}
				else formular[zaehler].style.border = "solid green 1px";
				break;
			
			case 'natuerliche_zahl':
				if (isNaN(formular[zaehler].value) || formular[zaehler].value<0 || formular[zaehler].value=='') {
					formular[zaehler].style.border = "solid red 1px";
					ok=0;
				}
				else formular[zaehler].style.border = "solid green 1px";
				break;
						
			case 'ganze_zahl':
				if (isNaN(formular[zaehler].value) || formular[zaehler].value=='') {
					formular[zaehler].style.border = "solid red 1px";
					ok=0;
				}
				else formular[zaehler].style.border = "solid green 1px";
				break;
				
			case 'einzelpunkte': /* fuer notenbeschreibung */
				einzelpunktarray=formular[zaehler].value.split("/");
				ar_zaehler=0;
				fehleingabe=0;
				while (einzelpunktarray[ar_zaehler]) {
					if ((isNaN(einzelpunktarray[ar_zaehler].replace(/,/, '.')) || einzelpunktarray[ar_zaehler]=='') && einzelpunktarray[ar_zaehler]!='-') fehleingabe=1;
					ar_zaehler++; }
				if (fehleingabe) {
					formular[zaehler].style.border = "solid red 1px";
					ok=0;
				}
				else formular[zaehler].style.border = "solid green 1px";
				break;
						
		}
		i++;
	}
	if (ok==1)
		formular.submit();
}

/* Fuer Plan bearbeiten */
function zeit_aktualisieren() {
	var i=0;
    var zeit=parseInt(document.getElementById("zeit_start").value)
	while(document.getElementById("zeit_"+i) && i<200) {
		zeit+=parseInt(document.getElementById("zeit_"+i).value);
		i++;
	}
    document.getElementById("zeit_aktu").value=zeit;
	document.getElementById("zeit_schluss").value=45*document.getElementById("stunden").value-zeit;
	/* mit XHTML - geht aber noch nicht:
	var i=0;
	while (document.getElementsByTagName('input')[i].name!='zeit_start') i++;
    var zeit=parseInt(document.getElementsByTagName('input')[i].value);
	while(document.getElementsByTagName('input')[i] && document.getElementsByTagName('input')[i].name=='zeit_'+i && i<200) {
		zeit+=parseInt(document.getElementsByTagName('input')[i].value);
		i++;
	}
	alert(zeit);
    document.getElementById('zeit_aktu').value=zeit;
	document.getElementsByName('zeit_schluss')[0].value=45*document.getElementsByName('stunden')[0].value-zeit;*/
}

/* Fuer Noten bearbeiten */

function mitzaehlen_anzeigen(auswahl) {
	i=0;
	while (i<100) {
		if (document.getElementById('mitzaehlen_'+i)) document.getElementById('mitzaehlen_'+i).style.display=auswahl;
		i++;
	}
}

function clip (id, pfad_zu_bild) {
	if (document.getElementById("span_" + id).style.display == 'none') {
		document.getElementById("img_" + id).src = pfad_zu_bild + "icons/clip_open.png";
		document.getElementById("span_" + id).style.display = "inline"; }
	else {
		document.getElementById("img_" + id).src = pfad_zu_bild + "icons/clip_closed.png";
		document.getElementById("span_" + id).style.display = "none"; }
}

	 function fenster(url,ueberschrift) {
        IDFenster = window.open(url, ueberschrift, "width=1100,height=600,left=100,top=200,resizable=yes,scrollbars=yes");
        IDFenster.focus();
     }
	 
     function IDs_eintragen(was,pfad,bereich,ziel_input) {
        var inhalt_ids = document.getElementById(ziel_input);
        inhalt_ids.setAttribute("value", "");
        var inhalt_ids_echt = document.getElementById(ziel_input+"_inhalt");
		while (inhalt_ids_echt.hasChildNodes()) inhalt_ids_echt.removeChild(inhalt_ids_echt.firstChild);
        inhalt_ids_echt.setAttribute("value", "");
        fenster(pfad+"material.php?was="+was+"&bereich="+bereich+"&ziel_input="+ziel_input, "ID-Uebersicht");
     }
	
	// MarkItUp
    function load_markitup() {
	    
        // Add markItUp! to your textarea in one line
        // $('textarea').markItUp( { Settings }, { OptionalExtraSettings } );
        $('.markItUp').markItUp(mySettings);
        $('#markup_aufgabentext').markItUp(mySettings);
        $('#markup_aufgabenloesung').markItUp(mySettings);
        
        /*
        // You can add content from anywhere in your page
        // $.markItUp( { Settings } );	
        $('.add').click(function() {
            $.markItUp( { 	openWith:'<opening tag>',
                            closeWith:'<\/closing tag>',
                            placeHolder:"New content"
                        }
                    );
            return false;
        });
        
        // And you can add/remove markItUp! whenever you want
        // $(textarea).markItUpRemove();
        $('.toggle').click(function() {
            if ($("#markItUp.markItUpEditor").length === 1) {
                $("#markItUp").markItUpRemove();
                $("span", this).text("get markItUp! back");
            } else {
                $('#markItUp').markItUp(mySettings);
                $("span", this).text("remove markItUp!");
            }
            return false;
        });
        */
	
	
	
    }
     
     
     // depreaced
     function Abschnitte_eintragen(pfad, ausgangsblock, refresh_close) {
        var inhalt = document.getElementById("inhalt");
        inhalt.setAttribute("value", "");
        var einfueg = document.getElementById("abschnitt_einfueger");
        while (einfueg.childNodes[0]) einfueg.removeChild(einfueg.firstChild);
        IDFenster = window.open(pfad+"abschnittsplanung.php?block="+ausgangsblock+"&refresh="+refresh_close, "ID-Uebersicht", "width=700,height=600,left=100,top=200,resizable=yes,scrollbars=yes");
        IDFenster.focus();
     }

	  function inhaltstyp(typ) {
	    var zeile = 1;
	    var einzeltyp = Math.floor(typ);
	    var abschnitt_typ = Math.round((typ-einzeltyp)*10);

       var P = document.getElementById("inhaltstyp_mehr_" + zeile);
       while (P.childNodes[0]) P.removeChild(P.firstChild);
       
       switch(abschnitt_typ) {
         case 1:
         var ebene = document.createElementNS("http://www.w3.org/1999/xhtml","select");
         var ebene_name = document.createAttribute("name");
         P.appendChild(ebene);

         ebene_name.nodeValue = "ueberschrift_ebene_" + zeile;
         ebene.setAttributeNode(ebene_name);

         var ebene_1 = document.createElementNS("http://www.w3.org/1999/xhtml","option");
         var ebene_1_value = document.createAttribute("value");
         var ebene_2 = document.createElementNS("http://www.w3.org/1999/xhtml","option");
         var ebene_2_value = document.createAttribute("value");
         var ebene_3 = document.createElementNS("http://www.w3.org/1999/xhtml","option");
         var ebene_3_value = document.createAttribute("value");
         var ebene_4 = document.createElementNS("http://www.w3.org/1999/xhtml","option");
         var ebene_4_value = document.createAttribute("value");
         var ebene_5 = document.createElementNS("http://www.w3.org/1999/xhtml","option");
         var ebene_5_value = document.createAttribute("value");
         ebene.appendChild(ebene_1);
         ebene.appendChild(ebene_2);
         ebene.appendChild(ebene_3);
         ebene.appendChild(ebene_4);
         ebene.appendChild(ebene_5);
         ebene_1_value.nodeValue = "1";
         ebene_2_value.nodeValue = "2";
         ebene_3_value.nodeValue = "3";
         ebene_4_value.nodeValue = "4";
         ebene_5_value.nodeValue = "5";
         ebene_1.setAttributeNode(ebene_1_value);
         ebene_2.setAttributeNode(ebene_2_value);
         ebene_3.setAttributeNode(ebene_3_value);
         ebene_4.setAttributeNode(ebene_4_value);
         ebene_5.setAttributeNode(ebene_5_value);
         var ebene_1_text = document.createTextNode('1.');
         var ebene_2_text = document.createTextNode('1.1.');
         var ebene_3_text = document.createTextNode('1.1.1.');
         var ebene_4_text = document.createTextNode('1.1.1.1.');
         var ebene_5_text = document.createTextNode('1.1.1.1.1.');
         ebene_1.appendChild(ebene_1_text);
         ebene_2.appendChild(ebene_2_text);
         ebene_3.appendChild(ebene_3_text);
         ebene_4.appendChild(ebene_4_text);
         ebene_5.appendChild(ebene_5_text);

         var typ = document.createElementNS("http://www.w3.org/1999/xhtml","select");
         var typ_name = document.createAttribute("name");
         P.appendChild(typ);

         typ_name.nodeValue = "ueberschrift_typ_" + zeile;
         typ.setAttributeNode(typ_name);

         var typ_1 = document.createElementNS("http://www.w3.org/1999/xhtml","option");
         var typ_1_value = document.createAttribute("value");
         var typ_2 = document.createElementNS("http://www.w3.org/1999/xhtml","option");
         var typ_2_value = document.createAttribute("value");
         var typ_3 = document.createElementNS("http://www.w3.org/1999/xhtml","option");
         var typ_3_value = document.createAttribute("value");
         var typ_4 = document.createElementNS("http://www.w3.org/1999/xhtml","option");
         var typ_4_value = document.createAttribute("value");
         typ.appendChild(typ_1);
         typ.appendChild(typ_2);
         typ.appendChild(typ_3);
         typ.appendChild(typ_4);
         typ_1_value.nodeValue = "1";
         typ_2_value.nodeValue = "a";
         typ_3_value.nodeValue = "A";
         typ_4_value.nodeValue = "I";
         typ_1.setAttributeNode(typ_1_value);
         typ_2.setAttributeNode(typ_2_value);
         typ_3.setAttributeNode(typ_3_value);
         typ_4.setAttributeNode(typ_4_value);
         var typ_1_text = document.createTextNode('1 (Zahlen)');
         var typ_2_text = document.createTextNode('a (Kleinbuchstaben)');
         var typ_3_text = document.createTextNode('A (Grossbuchstaben)');
         var typ_4_text = document.createTextNode('I (roemische Zahlen)');
         typ_1.appendChild(typ_1_text);
         typ_2.appendChild(typ_2_text);
         typ_3.appendChild(typ_3_text);
         typ_4.appendChild(typ_4_text);


		var input = document.createElementNS("http://www.w3.org/1999/xhtml","input");
         var input_type = document.createAttribute("type");
         input_type.nodeValue = "text";
         var input_name = document.createAttribute("name");
         input_name.nodeValue = "ueberschrift_text_" + zeile;
         var input_size = document.createAttribute("size");
         input_size.nodeValue = "30";
         var input_maxlength = document.createAttribute("maxlength");
         input_maxlength.nodeValue = "100";
         input.setAttributeNode(input_type);
         input.setAttributeNode(input_name);
         input.setAttributeNode(input_size);
         input.setAttributeNode(input_maxlength);
         P.appendChild(input);
         break;
         case 2:
         var input = document.createElementNS("http://www.w3.org/1999/xhtml","input");
         var input_type = document.createAttribute("type");
         input_type.nodeValue = "text";
         var input_name = document.createAttribute("name");
         input_name.nodeValue = "test_ids";
         var input_id = document.createAttribute("id");
         input_id.nodeValue = "test_ids";
         var input_value = document.createAttribute("value");
         input_value.nodeValue = "";
         var input_read = document.createAttribute("readonly");
         input_read.nodeValue = "readonly";
         var input_size = document.createAttribute("size");
         input_size.nodeValue = "5";
         input.setAttributeNode(input_type);
         input.setAttributeNode(input_name);
         input.setAttributeNode(input_id);
         input.setAttributeNode(input_value);
         input.setAttributeNode(input_read);
         input.setAttributeNode(input_size);
         P.appendChild(input);

/* brauch mer net
         var input = document.createElementNS("http://www.w3.org/1999/xhtml","input");
         var input_type = document.createAttribute("type");
         input_type.nodeValue = "hidden";
         var input_name = document.createAttribute("name");
         input_name.nodeValue = "beispiel";
         var input_value = document.createAttribute("value");
         input_value.nodeValue = einzeltyp-1;
         input.setAttributeNode(input_type);
         input.setAttributeNode(input_name);
         input.setAttributeNode(input_value);
         P.appendChild(input);*/
         
         var button = document.createElementNS("http://www.w3.org/1999/xhtml","input");
         var button_type = document.createAttribute("type");
         button_type.nodeValue = "button";
         var button_name = document.createAttribute("name");
         button_name.nodeValue = "test_auswahl_" + zeile;
         var button_value = document.createAttribute("value");
         button_value.nodeValue = "Auswahl";
         var button_onclick = document.createAttribute("onclick");
         button_onclick.nodeValue = "IDs_eintragen('test','./','','test_ids')";
         button.setAttributeNode(button_type);
         button.setAttributeNode(button_name);
         button.setAttributeNode(button_value);
         button.setAttributeNode(button_onclick);
         P.appendChild(button);
         
         document.abschnitt.hefter_1[0].checked=true;
         document.abschnitt.medium_1.options[2].selected=true;
         document.abschnitt.sozialform_1.options[1].selected=true;
         
         break;
         case 3:
         var input = document.createElementNS("http://www.w3.org/1999/xhtml","input");
         var input_type = document.createAttribute("type");
         input_type.nodeValue = "text";
         var input_name = document.createAttribute("name");
         input_name.nodeValue = "aufgabe_ids";
         var input_id = document.createAttribute("id");
         input_id.nodeValue = "aufgabe_ids";
         var input_value = document.createAttribute("value");
         input_value.nodeValue = "";
         var input_read = document.createAttribute("readonly");
         input_read.nodeValue = "readonly";
         var input_size = document.createAttribute("size");
         input_size.nodeValue = "5";
         input.setAttributeNode(input_type);
         input.setAttributeNode(input_id);
         input.setAttributeNode(input_name);
         input.setAttributeNode(input_value);
         input.setAttributeNode(input_read);
         input.setAttributeNode(input_size);
         P.appendChild(input);

         var button = document.createElementNS("http://www.w3.org/1999/xhtml","input");
         var button_type = document.createAttribute("type");
         button_type.nodeValue = "button";
         var button_name = document.createAttribute("name");
         button_name.nodeValue = "aufgabe_auswahl_" + zeile;
         var button_value = document.createAttribute("value");
         button_value.nodeValue = "Auswahl";
         var button_onclick = document.createAttribute("onclick");
         button_onclick.nodeValue = "IDs_eintragen('aufgabe','./','','aufgabe_ids')";
         button.setAttributeNode(button_type);
         button.setAttributeNode(button_name);
         button.setAttributeNode(button_value);
         button.setAttributeNode(button_onclick);
         P.appendChild(button);
         
		 var neu = document.createElementNS("http://www.w3.org/1999/xhtml","a");
         var neu_href = document.createAttribute("href");
         neu_href.nodeValue = './formular/aufgabe_neu.php'+window.location.search+'&einzeltyp='+einzeltyp;
         neu.setAttributeNode(neu_href);
		 neu.appendChild(document.createTextNode("neue Aufgabe erstellen")); 
         P.appendChild(neu);
		 
		 if (einzeltyp==1) {
         document.abschnitt.hefter_1[2].checked=true;
         document.abschnitt.medium_1.options[0].selected=true;
         document.abschnitt.sozialform_1.options[1].selected=true; }
         
         /*var checkbox = document.createElementNS("http://www.w3.org/1999/xhtml","input");
         var checkbox_type = document.createAttribute("type");
         checkbox_type.nodeValue = "checkbox";
         var checkbox_name = document.createAttribute("name");
         checkbox_name.nodeValue = "beispiel_" + zeile;
         var checkbox_value = document.createAttribute("value");
         checkbox_value.nodeValue = "Beispiel";
         checkbox.setAttributeNode(checkbox_type);
         checkbox.setAttributeNode(checkbox_name);
         checkbox.setAttributeNode(checkbox_value);
         P.appendChild(checkbox);*/
         break;
         case 4:
         var input = document.createElementNS("http://www.w3.org/1999/xhtml","input");
         var input_type = document.createAttribute("type");
         input_type.nodeValue = "text";
         var input_name = document.createAttribute("name");
         input_name.nodeValue = "link_ids";
         var input_id = document.createAttribute("id");
         input_id.nodeValue = "link_ids";
         var input_value = document.createAttribute("value");
         input_value.nodeValue = "";
         var input_read = document.createAttribute("readonly");
         input_read.nodeValue = "readonly";
         var input_size = document.createAttribute("size");
         input_size.nodeValue = "5";
         input.setAttributeNode(input_type);
         input.setAttributeNode(input_name);
         input.setAttributeNode(input_id);
         input.setAttributeNode(input_value);
         input.setAttributeNode(input_read);
         input.setAttributeNode(input_size);
         P.appendChild(input);

         var button = document.createElementNS("http://www.w3.org/1999/xhtml","input");
         var button_type = document.createAttribute("type");
         button_type.nodeValue = "button";
         var button_name = document.createAttribute("name");
         button_name.nodeValue = "link_auswahl_" + zeile;
         var button_value = document.createAttribute("value");
         button_value.nodeValue = "Auswahl";
         var button_onclick = document.createAttribute("onclick");
         button_onclick.nodeValue = "IDs_eintragen('link','./','','link_ids')";
         button.setAttributeNode(button_type);
         button.setAttributeNode(button_name);
         button.setAttributeNode(button_value);
         button.setAttributeNode(button_onclick);
         P.appendChild(button);
         P.appendChild(document.createTextNode(" Bemerkung: "));
         
         var input = document.createElementNS("http://www.w3.org/1999/xhtml","input");
         var input_type = document.createAttribute("type");
         input_type.nodeValue = "text";
         var input_name = document.createAttribute("name");
         input_name.nodeValue = "bemerkung";
         var input_max = document.createAttribute("maxlength");
         input_read.nodeValue = "35";
         var input_size = document.createAttribute("size");
         input_size.nodeValue = "50";
         input.setAttributeNode(input_type);
         input.setAttributeNode(input_name);
         input.setAttributeNode(input_max);
         input.setAttributeNode(input_size);
         P.appendChild(input);
         
         if (einzeltyp==1) {
           document.abschnitt.hefter_1[2].checked=true;
           document.abschnitt.medium_1.options[2].selected=true;
           document.abschnitt.sozialform_1.options[1].selected=true;
         }
         break;
         case 5:
         var input = document.createElementNS("http://www.w3.org/1999/xhtml","input");
         var input_type = document.createAttribute("type");
         input_type.nodeValue = "text";
         var input_name = document.createAttribute("name");
         input_name.nodeValue = "grafik_ids";
         var input_id = document.createAttribute("id");
         input_id.nodeValue = "grafik_ids";
         var input_value = document.createAttribute("value");
         input_value.nodeValue = "";
         var input_read = document.createAttribute("readonly");
         input_read.nodeValue = "readonly";
         var input_size = document.createAttribute("size");
         input_size.nodeValue = "5";
         input.setAttributeNode(input_type);
         input.setAttributeNode(input_name);
         input.setAttributeNode(input_id);
         input.setAttributeNode(input_value);
         input.setAttributeNode(input_read);
         input.setAttributeNode(input_size);
         P.appendChild(input);

         var button = document.createElementNS("http://www.w3.org/1999/xhtml","input");
         var button_type = document.createAttribute("type");
         button_type.nodeValue = "button";
         var button_name = document.createAttribute("name");
         button_name.nodeValue = "grafik_auswahl_" + zeile;
         var button_value = document.createAttribute("value");
         button_value.nodeValue = "Auswahl";
         var button_onclick = document.createAttribute("onclick");
         button_onclick.nodeValue = "IDs_eintragen('grafik','./','','grafik_ids')";
         button.setAttributeNode(button_type);
         button.setAttributeNode(button_name);
         button.setAttributeNode(button_value);
         button.setAttributeNode(button_onclick);
         P.appendChild(button);
         
/*         P.appendChild(document.createTextNode(" Position: "));

         var select = document.createElementNS("http://www.w3.org/1999/xhtml","select");
         var select_name = document.createAttribute("name");
         select_name.nodeValue = "position";
         select.setAttributeNode(select_name);
         P.appendChild(select);
         
         var option = document.createElementNS("http://www.w3.org/1999/xhtml","option");
         var option_value = document.createAttribute("value");
         option_value.nodeValue = "0";
         option.setAttributeNode(option_value);
         select.appendChild(option);
         option.appendChild(document.createTextNode("über Text"));
         
         var option = document.createElementNS("http://www.w3.org/1999/xhtml","option");
         var option_value = document.createAttribute("value");
         option_value.nodeValue = "1";
         option.setAttributeNode(option_value);
         select.appendChild(option);
         option.appendChild(document.createTextNode("links"));

         var option = document.createElementNS("http://www.w3.org/1999/xhtml","option");
         var option_value = document.createAttribute("value");
         option_value.nodeValue = "2";
         option.setAttributeNode(option_value);
         select.appendChild(option);
         option.appendChild(document.createTextNode("rechts"));

         var option = document.createElementNS("http://www.w3.org/1999/xhtml","option");
         var option_value = document.createAttribute("value");
         option_value.nodeValue = "3";
         option.setAttributeNode(option_value);
         select.appendChild(option);
         option.appendChild(document.createTextNode("drunter"));
         
         P.appendChild(document.createTextNode(" Breite: "));
         
         var input = document.createElementNS("http://www.w3.org/1999/xhtml","input");
         var input_type = document.createAttribute("type");
         input_type.nodeValue = "text";
         var input_name = document.createAttribute("name");
         input_name.nodeValue = "breite";
         var input_max = document.createAttribute("maxlength");
         input_read.nodeValue = "4";
         var input_size = document.createAttribute("size");
         input_size.nodeValue = "3";
         input.setAttributeNode(input_type);
         input.setAttributeNode(input_name);
         input.setAttributeNode(input_max);
         input.setAttributeNode(input_size);
         P.appendChild(input);
         P.appendChild(document.createTextNode(" cm"));*/
         
         break;
         case 6:
         var input = document.createElementNS("http://www.w3.org/1999/xhtml","input");
         var input_type = document.createAttribute("type");
         input_type.nodeValue = "text";
         var input_name = document.createAttribute("name");
         input_name.nodeValue = "material_ids";
         var input_id = document.createAttribute("id");
         input_id.nodeValue = "material_ids";
         var input_value = document.createAttribute("value");
         input_value.nodeValue = "";
         var input_read = document.createAttribute("readonly");
         input_read.nodeValue = "readonly";
         var input_size = document.createAttribute("size");
         input_size.nodeValue = "5";
         input.setAttributeNode(input_type);
         input.setAttributeNode(input_name);
         input.setAttributeNode(input_id);
         input.setAttributeNode(input_value);
         input.setAttributeNode(input_read);
         input.setAttributeNode(input_size);
         P.appendChild(input);

         var button = document.createElementNS("http://www.w3.org/1999/xhtml","input");
         var button_type = document.createAttribute("type");
         button_type.nodeValue = "button";
         var button_name = document.createAttribute("name");
         button_name.nodeValue = "material_auswahl_" + zeile;
         var button_value = document.createAttribute("value");
         button_value.nodeValue = "Auswahl";
         var button_onclick = document.createAttribute("onclick");
         button_onclick.nodeValue = "IDs_eintragen('material','./','','material_ids')";
         button.setAttributeNode(button_type);
         button.setAttributeNode(button_name);
         button.setAttributeNode(button_value);
         button.setAttributeNode(button_onclick);
         P.appendChild(button);
         
         document.abschnitt.hefter_1[0].checked=true;
         document.abschnitt.medium_1.options[5].selected=true;
         break;
         case 7:
/*         var input = document.createElementNS("http://www.w3.org/1999/xhtml","input");
         var input_type = document.createAttribute("type");
         input_type.nodeValue = "hidden";
         var input_name = document.createAttribute("name");
         input_name.nodeValue = "sonstiges_typ_" + zeile;
         var input_value = document.createAttribute("value");
         input_value.nodeValue = einzeltyp;
         input.setAttributeNode(input_type);
         input.setAttributeNode(input_name);
         input.setAttributeNode(input_value);
         P.appendChild(input);*/

         var textarea = document.createElementNS("http://www.w3.org/1999/xhtml","textarea");
         var textarea_name = document.createAttribute("name");
         textarea_name.nodeValue = "sonstiges_inhalt";
         var textarea_cols = document.createAttribute("cols");
         textarea_cols.nodeValue = "50";
         var textarea_rows = document.createAttribute("rows");
         textarea_rows.nodeValue = "7";
         textarea.setAttributeNode(textarea_name);
         textarea.setAttributeNode(textarea_cols);
         textarea.setAttributeNode(textarea_rows);
         P.appendChild(textarea);
         
         switch (einzeltyp) {
           case 1:
             document.abschnitt.hefter_1[0].checked=true;
             document.abschnitt.medium_1.options[4].selected=true;
             document.abschnitt.sozialform_1.options[3].selected=true;
           break;
           case 2:
             document.abschnitt.hefter_1[0].checked=true;
             document.abschnitt.medium_1.options[4].selected=true;
             document.abschnitt.sozialform_1.options[3].selected=true;
           break;
         }
         break;
       }
       
       //P.childNodes[0].nodeValue = Button1text;
       //P.appendChild(Button1);
     }

     //var zeile = 2;

	  function schreib_in_einzelstundentabelle() {
	    var zeile = document.getElementById("einzelstunde");
	    var trs = zeile.getElementsByTagName("tr");
	    zeile=(trs.length-1);
       var TR = document.getElementById("einzelstunde").insertRow(zeile);
       
       var TD1 = document.createElementNS("http://www.w3.org/1999/xhtml","td");
       var position = document.createElementNS("http://www.w3.org/1999/xhtml","input");
       var position_type = document.createAttribute("type");
       position_type.nodeValue = "text";
       var position_name = document.createAttribute("name");
       position_name.nodeValue = "position_" + zeile;
       var position_value = document.createAttribute("value");
       position_value.nodeValue = (zeile-1);
       var position_size = document.createAttribute("size");
       position_size.nodeValue = "1";
       var position_maxlength = document.createAttribute("maxlength");
       position_maxlength.nodeValue = "2";
       // spaeter mit Buttons und Javascript
       position.setAttributeNode(position_type);
       position.setAttributeNode(position_name);
       position.setAttributeNode(position_value);
       position.setAttributeNode(position_size);
       position.setAttributeNode(position_maxlength);
       TD1.appendChild(position);
       TR.appendChild(TD1);

       var TD2 = document.createElementNS("http://www.w3.org/1999/xhtml","td");
       var zeit = document.createElementNS("http://www.w3.org/1999/xhtml","input");
       var zeit_type = document.createAttribute("type");
       zeit_type.nodeValue = "text";
       var zeit_name = document.createAttribute("name");
       zeit_name.nodeValue = "zeit_" + zeile;
       var zeit_value = document.createAttribute("value");
       zeit_value.nodeValue = "0";
       var zeit_size = document.createAttribute("size");
       zeit_size.nodeValue = "1";
       var zeit_maxlength = document.createAttribute("maxlength");
       zeit_maxlength.nodeValue = "2";
       // spaeter mit Buttons und Javascript
       zeit.setAttributeNode(zeit_type);
       zeit.setAttributeNode(zeit_name);
       zeit.setAttributeNode(zeit_value);
       zeit.setAttributeNode(zeit_size);
       zeit.setAttributeNode(zeit_maxlength);
       TD2.appendChild(zeit);
       TR.appendChild(TD2);
  
       var TD3 = document.createElementNS("http://www.w3.org/1999/xhtml","td"); 
         var ebene = document.createElementNS("http://www.w3.org/1999/xhtml","select");
         var ebene_name = document.createAttribute("name");
         var ebene_onchange = document.createAttribute("onChange");
         TD3.appendChild(ebene);

         ebene_name.nodeValue = "inhaltstyp_" + zeile;
         ebene_onchange.nodeValue = "inhaltstyp("+zeile+",this.value)";
         ebene.setAttributeNode(ebene_name);
         ebene.setAttributeNode(ebene_onchange);

         var ebene_1 = document.createElementNS("http://www.w3.org/1999/xhtml","option");
         var ebene_1_value = document.createAttribute("value");
         var ebene_2 = document.createElementNS("http://www.w3.org/1999/xhtml","option");
         var ebene_2_value = document.createAttribute("value");
         var ebene_3 = document.createElementNS("http://www.w3.org/1999/xhtml","option");
         var ebene_3_value = document.createAttribute("value");
         var ebene_4 = document.createElementNS("http://www.w3.org/1999/xhtml","option");
         var ebene_4_value = document.createAttribute("value");
         var ebene_5 = document.createElementNS("http://www.w3.org/1999/xhtml","option");
         var ebene_5_value = document.createAttribute("value");
         var ebene_6 = document.createElementNS("http://www.w3.org/1999/xhtml","option");
         var ebene_6_value = document.createAttribute("value");
         var ebene_7 = document.createElementNS("http://www.w3.org/1999/xhtml","option");
         var ebene_7_value = document.createAttribute("value");
         var ebene_8 = document.createElementNS("http://www.w3.org/1999/xhtml","option");
         var ebene_8_value = document.createAttribute("value");
         var ebene_9 = document.createElementNS("http://www.w3.org/1999/xhtml","option");
         var ebene_9_value = document.createAttribute("value");
         var ebene_10 = document.createElementNS("http://www.w3.org/1999/xhtml","option");
         var ebene_10_value = document.createAttribute("value");
         var ebene_11 = document.createElementNS("http://www.w3.org/1999/xhtml","option");
         var ebene_11_value = document.createAttribute("value");
         var ebene_12 = document.createElementNS("http://www.w3.org/1999/xhtml","option");
         var ebene_12_value = document.createAttribute("value");
         var ebene_13 = document.createElementNS("http://www.w3.org/1999/xhtml","option");
         var ebene_13_value = document.createAttribute("value");
         var ebene_14 = document.createElementNS("http://www.w3.org/1999/xhtml","option");
         var ebene_14_value = document.createAttribute("value");
         var ebene_15 = document.createElementNS("http://www.w3.org/1999/xhtml","option");
         var ebene_15_value = document.createAttribute("value");
         var ebene_16 = document.createElementNS("http://www.w3.org/1999/xhtml","option");
         var ebene_16_value = document.createAttribute("value");
         var ebene_17 = document.createElementNS("http://www.w3.org/1999/xhtml","option");
         var ebene_17_value = document.createAttribute("value");
         ebene.appendChild(ebene_1);
         ebene.appendChild(ebene_2);
         ebene.appendChild(ebene_3);
         ebene.appendChild(ebene_4);
         ebene.appendChild(ebene_5);
         ebene.appendChild(ebene_6);
         ebene.appendChild(ebene_7);
         ebene.appendChild(ebene_8);
         ebene.appendChild(ebene_9);
         ebene.appendChild(ebene_10);
         ebene.appendChild(ebene_11);
         ebene.appendChild(ebene_12);
         ebene.appendChild(ebene_13);
         ebene.appendChild(ebene_14);
         ebene.appendChild(ebene_15);
         ebene.appendChild(ebene_16);
         ebene.appendChild(ebene_17);
         ebene_1_value.nodeValue = "0";
         ebene_2_value.nodeValue = "1.4";
         ebene_3_value.nodeValue = "2.3";
         ebene_4_value.nodeValue = "4.7";
         ebene_5_value.nodeValue = "2.7";
         ebene_6_value.nodeValue = "1.7";
         ebene_7_value.nodeValue = "2.4";
         ebene_8_value.nodeValue = "1.5";
         ebene_9_value.nodeValue = "3.4";
         ebene_10_value.nodeValue = "1.6";
         ebene_11_value.nodeValue = "3.7";
         ebene_12_value.nodeValue = "6.7";
         ebene_13_value.nodeValue = "1.2";
         ebene_14_value.nodeValue = "1.1";
         ebene_15_value.nodeValue = "1.3";
         ebene_16_value.nodeValue = "5.7";
         ebene_17_value.nodeValue = "7.7";
         ebene_1.setAttributeNode(ebene_1_value);
         ebene_2.setAttributeNode(ebene_2_value);
         ebene_3.setAttributeNode(ebene_3_value);
         ebene_4.setAttributeNode(ebene_4_value);
         ebene_5.setAttributeNode(ebene_5_value);
         ebene_6.setAttributeNode(ebene_6_value);
         ebene_7.setAttributeNode(ebene_7_value);
         ebene_8.setAttributeNode(ebene_8_value);
         ebene_9.setAttributeNode(ebene_9_value);
         ebene_10.setAttributeNode(ebene_10_value);
         ebene_11.setAttributeNode(ebene_11_value);
         ebene_12.setAttributeNode(ebene_12_value);
         ebene_13.setAttributeNode(ebene_13_value);
         ebene_14.setAttributeNode(ebene_14_value);
         ebene_15.setAttributeNode(ebene_15_value);
         ebene_16.setAttributeNode(ebene_16_value);
         ebene_17.setAttributeNode(ebene_17_value);
         var ebene_1_text = document.createTextNode('wählen...');
         var ebene_2_text = document.createTextNode('Arbeitsblatt');
         var ebene_3_text = document.createTextNode('Beispielaufgabe');
         var ebene_4_text = document.createTextNode('Definition');
         var ebene_5_text = document.createTextNode('Diskussion');
         var ebene_6_text = document.createTextNode('Erläuterung');
         var ebene_7_text = document.createTextNode('Folie');
         var ebene_8_text = document.createTextNode('Grafik');
         var ebene_9_text = document.createTextNode('Link');
         var ebene_10_text = document.createTextNode('Material');
         var ebene_11_text = document.createTextNode('Merke');
         var ebene_12_text = document.createTextNode('Programmcode');
         var ebene_13_text = document.createTextNode('Test');
         var ebene_14_text = document.createTextNode('Überschrift');
         var ebene_15_text = document.createTextNode('Übungsaufgabe');
         var ebene_16_text = document.createTextNode('umrandet');
         var ebene_17_text = document.createTextNode('sonstiger Text');
         ebene_1.appendChild(ebene_1_text);
         ebene_2.appendChild(ebene_2_text);
         ebene_3.appendChild(ebene_3_text);
         ebene_4.appendChild(ebene_4_text);
         ebene_5.appendChild(ebene_5_text);
         ebene_6.appendChild(ebene_6_text);
         ebene_7.appendChild(ebene_7_text);
         ebene_8.appendChild(ebene_8_text);
         ebene_9.appendChild(ebene_9_text);
         ebene_10.appendChild(ebene_10_text);
         ebene_11.appendChild(ebene_11_text);
         ebene_12.appendChild(ebene_12_text);
         ebene_13.appendChild(ebene_13_text);
         ebene_14.appendChild(ebene_14_text);
         ebene_15.appendChild(ebene_15_text);
         ebene_16.appendChild(ebene_16_text);
         ebene_17.appendChild(ebene_17_text);

         var inhalt = document.createElementNS("http://www.w3.org/1999/xhtml","p");
         var inhalt_id = document.createAttribute("id");
         inhalt_id.nodeValue = "inhaltstyp_mehr_" + zeile;
         inhalt.setAttributeNode(inhalt_id);
         TD3.appendChild(inhalt);
       TR.appendChild(TD3);
  
       var TD4 = document.createElementNS("http://www.w3.org/1999/xhtml","td");
       var radio1 = document.createElementNS("http://www.w3.org/1999/xhtml","input");
       var radio1_type = document.createAttribute("type");
       radio1_type.nodeValue = "radio";
       var radio1_name = document.createAttribute("name");
       radio1_name.nodeValue = "hefter_" + zeile;
       var radio1_value = document.createAttribute("value");
       radio1_value.nodeValue = "0";
       radio1.setAttributeNode(radio1_type);
       radio1.setAttributeNode(radio1_name);
       radio1.setAttributeNode(radio1_value);
       var radio2 = document.createElementNS("http://www.w3.org/1999/xhtml","input");
       var radio2_type = document.createAttribute("type");
       radio2_type.nodeValue = "radio";
       var radio2_name = document.createAttribute("name");
       radio2_name.nodeValue = "hefter_" + zeile;
       var radio2_value = document.createAttribute("value");
       radio2_value.nodeValue = "1";
       var radio2_checked = document.createAttribute("checked");
       radio2_checked.nodeValue = "checked";
       radio2.setAttributeNode(radio2_type);
       radio2.setAttributeNode(radio2_name);
       radio2.setAttributeNode(radio2_value);
       radio2.setAttributeNode(radio2_checked);
       var radio3 = document.createElementNS("http://www.w3.org/1999/xhtml","input");
       var radio3_type = document.createAttribute("type");
       radio3_type.nodeValue = "radio";
       var radio3_name = document.createAttribute("name");
       radio3_name.nodeValue = "hefter_" + zeile;
       var radio3_value = document.createAttribute("value");
       radio3_value.nodeValue = "2";
       radio3.setAttributeNode(radio3_type);
       radio3.setAttributeNode(radio3_name);
       radio3.setAttributeNode(radio3_value);
       TD4.appendChild(radio1);
       TD4.appendChild(document.createTextNode(' nein'));
       TD4.appendChild(document.createElementNS("http://www.w3.org/1999/xhtml","br"));
       TD4.appendChild(radio2);
       TD4.appendChild(document.createTextNode(' Merkteil'));
       TD4.appendChild(document.createElementNS("http://www.w3.org/1999/xhtml","br"));
       TD4.appendChild(radio3);
       TD4.appendChild(document.createTextNode(' Übungsteil'));
       TR.appendChild(TD4);
       
       var TD5 = document.createElementNS("http://www.w3.org/1999/xhtml","td");
         var medium = document.createElementNS("http://www.w3.org/1999/xhtml","select");
         var medium_name = document.createAttribute("name");
         TD5.appendChild(medium);

         medium_name.nodeValue = "medium_" + zeile;
         medium.setAttributeNode(medium_name);

         var medium_1 = document.createElementNS("http://www.w3.org/1999/xhtml","option");
         var medium_1_value = document.createAttribute("value");
         var medium_2 = document.createElementNS("http://www.w3.org/1999/xhtml","option");
         var medium_2_value = document.createAttribute("value");
         var medium_3 = document.createElementNS("http://www.w3.org/1999/xhtml","option");
         var medium_3_value = document.createAttribute("value");
         var medium_4 = document.createElementNS("http://www.w3.org/1999/xhtml","option");
         var medium_4_value = document.createAttribute("value");
         var medium_5 = document.createElementNS("http://www.w3.org/1999/xhtml","option");
         var medium_5_value = document.createAttribute("value");
         medium.appendChild(medium_1);
         medium.appendChild(medium_2);
         medium.appendChild(medium_3);
         medium.appendChild(medium_4);
         medium.appendChild(medium_5);
         medium_1_value.nodeValue = "1";
         medium_2_value.nodeValue = "2";
         medium_3_value.nodeValue = "3";
         medium_4_value.nodeValue = "4";
         medium_5_value.nodeValue = "5";
         medium_1.setAttributeNode(medium_1_value);
         medium_2.setAttributeNode(medium_2_value);
         medium_3.setAttributeNode(medium_3_value);
         medium_4.setAttributeNode(medium_4_value);
         medium_5.setAttributeNode(medium_5_value);
         var medium_1_text = document.createTextNode('Tafel');
         var medium_2_text = document.createTextNode('Folie');
         var medium_3_text = document.createTextNode('Arbeitsblatt');
         var medium_4_text = document.createTextNode('Beamer');
         var medium_5_text = document.createTextNode('Sprache');
         medium_1.appendChild(medium_1_text);
         medium_2.appendChild(medium_2_text);
         medium_3.appendChild(medium_3_text);
         medium_4.appendChild(medium_4_text);
         medium_5.appendChild(medium_5_text);
         
         TD5.appendChild(document.createTextNode(' /'));
         TD5.appendChild(document.createElementNS("http://www.w3.org/1999/xhtml","br"));

         var sozform = document.createElementNS("http://www.w3.org/1999/xhtml","select");
         var sozform_name = document.createAttribute("name");
         TD5.appendChild(sozform);

         sozform_name.nodeValue = "sozform_" + zeile;
         sozform.setAttributeNode(sozform_name);

         var sozform_1 = document.createElementNS("http://www.w3.org/1999/xhtml","option");
         var sozform_1_value = document.createAttribute("value");
         var sozform_2 = document.createElementNS("http://www.w3.org/1999/xhtml","option");
         var sozform_2_value = document.createAttribute("value");
         var sozform_3 = document.createElementNS("http://www.w3.org/1999/xhtml","option");
         var sozform_3_value = document.createAttribute("value");
         var sozform_4 = document.createElementNS("http://www.w3.org/1999/xhtml","option");
         var sozform_4_value = document.createAttribute("value");
         var sozform_5 = document.createElement("option");
         var sozform_5_value = document.createAttribute("value");
         sozform.appendChild(sozform_1);
         sozform.appendChild(sozform_2);
         sozform.appendChild(sozform_3);
         sozform.appendChild(sozform_4);
         sozform.appendChild(sozform_5);
         sozform_1_value.nodeValue = "1";
         sozform_2_value.nodeValue = "2";
         sozform_3_value.nodeValue = "3";
         sozform_4_value.nodeValue = "4";
         sozform_5_value.nodeValue = "5";
         sozform_1.setAttributeNode(sozform_1_value);
         sozform_2.setAttributeNode(sozform_2_value);
         sozform_3.setAttributeNode(sozform_3_value);
         sozform_4.setAttributeNode(sozform_4_value);
         sozform_5.setAttributeNode(sozform_5_value);
         var sozform_1_text = document.createTextNode('frontal');
         var sozform_2_text = document.createTextNode('Einzelarbeit');
         var sozform_3_text = document.createTextNode('Gruppenarbeit');
         var sozform_4_text = document.createTextNode('Lehrer-Schüler-Gespräch');
         var sozform_5_text = document.createTextNode('...');
         sozform_1.appendChild(sozform_1_text);
         sozform_2.appendChild(sozform_2_text);
         sozform_3.appendChild(sozform_3_text);
         sozform_4.appendChild(sozform_4_text);
         sozform_5.appendChild(sozform_5_text);
       TR.appendChild(TD5);

       var TD6 = document.createElement("td");
       var ziel = document.createElement("input");
       var ziel_type = document.createAttribute("type");
       ziel_type.nodeValue = "text";
       var ziel_name = document.createAttribute("name");
       ziel_name.nodeValue = "ziel_" + zeile;
       var ziel_value = document.createAttribute("value");
       ziel_value.nodeValue = "";
       var ziel_size = document.createAttribute("size");
       ziel_size.nodeValue = "20";
       var ziel_maxlength = document.createAttribute("maxlength");
       ziel_maxlength.nodeValue = "50";
       ziel.setAttributeNode(ziel_type);
       ziel.setAttributeNode(ziel_name);
       ziel.setAttributeNode(ziel_value);
       ziel.setAttributeNode(ziel_size);
       ziel.setAttributeNode(ziel_maxlength);
       TD6.appendChild(ziel);
       TR.appendChild(TD6);
     }


// ------ draw bars of homework-table -----------------------
   var timer = null;
   
    function draw_bars(tabArr) {
		// finde den maximalen Wert in der Tabelle
		max=0;
		for (var i=0; i<Zeilen; i++)
		    for (var j=1; j<Spalten; j++)
			if (parseInt(tabArr[i * Spalten + j])>max) {
				max=Math.abs(parseInt(tabArr[i * Spalten + j]));
			}
		var canvas = document.getElementById('bar_1_0');
		if(canvas && canvas.getContext) {
			var ctx = canvas.getContext('2d');
   
			if(ctx) {
				for (var i=0; i<Zeilen; i++)
					for (var j=1; j<Spalten; j++)
						if (Math.abs(parseInt(tabArr[i * Spalten + j]))>0) {
							canvas = document.getElementById('bar_'+j+'_'+i);
							ctx = canvas.getContext('2d');
							ctx.fillStyle = "darkred";
							ctx.fill();
							ctx.fillRect(5, 15, 70*Math.abs(tabArr[i * Spalten + j][0]/max), 3); // x, y, w, h
							ctx.fillStyle = 'black';
							ctx.font = '10pt Calibri';
							ctx.fillText(tabArr[i * Spalten + j][1], 5, 12);
					}
			}
		}
	}
	
   function draw (id, farbe, breit, max) {
	  var canvas = document.getElementById('bar_'+id+'_0');
      if(canvas && canvas.getContext) {
         var ctx = canvas.getContext('2d');
   
         if(ctx) {
			var count=0;
            timer = setInterval(function() {
               if (count > 19) {
                  clearInterval(timer); // Zeitgeber stoppen
               }
               count=count+1;
               var canvas;
               var ctx;
               //var diffTime = new Date().getTime() - startTime;
               //var scale = diffTime / (1000 * 1.5); // 1.5 sek
               var i=0;
               while (i<breit.length) {
                  canvas = document.getElementById('bar_'+id+'_'+i);
                  ctx = canvas.getContext('2d');
                  ctx.fillStyle = farbe;
                  ctx.fill();
                  ctx.fillRect(23, 5, 70*(breit[i]/max)/20*count, 10); // x, y, w, h
                  i++;
               }
            }, 1000 / 50);
            var i=0;
            while (i<breit.length) {
               canvas = document.getElementById('bar_'+id+'_'+i);
               ctx = canvas.getContext('2d');
               ctx.fillStyle = 'black';
               ctx.font = '10pt Calibri';
               ctx.fillText(breit[i], 5, 10);
               i++;
            }
         }
      }

   }


// sortable homework-table

var Spaltenueberschriftformatierungen = new Array("", "", "", "", "", "", "");

var Spaltenformatierungen = new Array("", "", "", "", "", "", "");

var Tabellenformatierung = "class=\"tabelle\" border=\"1\" style=\"border:solid 1px #808080; clear: both;\" cellspacing=\"0\"";
var Ordinalzahlenspalte = 1;
var Ordinalzahlenspaltenformatierung = "";

var IconNormalAuf = "<img src=\"./icons/normal_auf.gif\" width=\"14\" height=\"12\" border=\"0\" alt=\"\">";
var IconNormalAb = "<img src=\"./icons/normal_ab.gif\" width=\"14\" height=\"12\" border=\"0\" alt=\"\">";
var IconSortiertAuf = "<img src=\"./icons/sortiert_auf.gif\" width=\"14\" height=\"12\" border=\"0\" alt=\"\">";
var IconSortiertAb = "<img src=\"./icons/sortiert_ab.gif\" width=\"14\" height=\"12\" border=\"0\" alt=\"\">";

var Sortierzeile = "";

function Erzeuge_Sortierzeile(Nummer,Richtung) {
 Sortierzeile = "<tr>";
 if(Ordinalzahlenspalte)
   Sortierzeile += "<th " + Ordinalzahlenspaltenformatierung + "> <\/th>";
 for(var j = 0; j < Spalten; ++j) {
  Sortierzeile += "<th " + Spaltenformatierungen[j] + ">";
  if(Richtung == "aufsteigend" && j == Nummer) {
   Sortierzeile += IconSortiertAuf + " ";
   Sortierzeile += "<a href=\"javascript:Sortiere_nach_Spalte(" + j + ",'" + Spaltensortierungen[j] + "',\'absteigend\')\">" + IconNormalAb + "</a>";
  }
  else if(Richtung == "absteigend" && j == Nummer) {
   Sortierzeile += "<a href=\"javascript:Sortiere_nach_Spalte(" + j + ",'" + Spaltensortierungen[j] + "',\'aufsteigend\')\">" + IconNormalAuf + "</a>";
   Sortierzeile += " " + IconSortiertAb;
  }
  else {
   Sortierzeile += "<a href=\"javascript:Sortiere_nach_Spalte(" + j + ",'" + Spaltensortierungen[j] + "',\'aufsteigend\')\">" + IconNormalAuf + "</a> ";
   Sortierzeile += "<a href=\"javascript:Sortiere_nach_Spalte(" + j + ",'" + Spaltensortierungen[j] + "',\'absteigend\')\">" + IconNormalAb + "</a><\/td>";
  }
 Sortierzeile += "<\/th>";
 }
 Sortierzeile += "<tr>";
}

function Sortiere_nach_Spalte(Nummer,Art,Richtung) {
 var Spaltendaten = new Array();
 var Vergleichsdaten = new Array();
 var SortierIndex = new Array();
 for(var i = 0; i < Zeilen; ++i)
  Spaltendaten[i] = Vergleichsdaten[i] = Tabellendaten[i * Spalten + Nummer][0];
 if(Art == "alphabetisch") Spaltendaten.sort();
 if(Art == "numerisch") Spaltendaten.sort(Numsort);
 if(Richtung == "absteigend") Spaltendaten.reverse();
 for(i = 0; i < Zeilen; ++i)
  for(var j = 0; j < Zeilen; ++j)
   if(Spaltendaten[i] == Vergleichsdaten[j])
    SortierIndex[i] = j;
 var Speicher;
 for(i = 0; i < Zeilen; ++i)
  for(j = 0; j < Spalten; ++j)
   sortierte_Tabellendaten[i * Spalten + j] = Tabellendaten[SortierIndex[i] * Spalten + j];
 Erzeuge_Sortierzeile(Nummer,Richtung);
 Schreibe_Tabelle(sortierte_Tabellendaten);
}

function Schreibe_Tabelle(Array) {
 var Tabelleninhalt = "";
 Tabelleninhalt += "<table " + Tabellenformatierung + ">";
 Tabelleninhalt += "<thead><tr>";
 if(Ordinalzahlenspalte)
   Tabelleninhalt += "<th " + Ordinalzahlenspaltenformatierung + "> <\/th>";
 for(var j = 0; j < Spalten; ++j)
  Tabelleninhalt += "<th " + Spaltenueberschriftformatierungen[j] + ">" + Spaltenueberschriften[j] + "<\/th>";
 Tabelleninhalt += "<\/tr>";
 Tabelleninhalt += Sortierzeile;
 Tabelleninhalt += "<\/thead>";
 Tabelleninhalt += "<tfoot><\/tfoot>";
 Tabelleninhalt += "<tbody>";
 for(var i = 0; i < Zeilen; ++i) {
  Tabelleninhalt += "<tr>";
  if(Ordinalzahlenspalte)
   Tabelleninhalt += "<td " + Ordinalzahlenspaltenformatierung + ">" + (i+1) + ".<\/td>";
  for(var j = 0; j < Spalten; ++j) {
   Tabelleninhalt += "<td " + Spaltenformatierungen[j] + " title=\"" + Array[i * Spalten + j][2] + "\">";
   Tabelleninhalt += (j<1)?Array[i * Spalten + j][1]:" "
   Tabelleninhalt += "<canvas id=\"bar_"+j+"_"+i+"\" width=\"100\" height=\"19\"><\/canvas><\/td>";
  }
  Tabelleninhalt += "<\/tr>";
 }
 Tabelleninhalt += "<\/tbody>";
 Tabelleninhalt += "<\/table>";
 if(document.getElementById)
  document.getElementById("Tabelle").innerHTML = Tabelleninhalt;
 else if(document.all)
  document.all.Tabelle.innerHTML = Tabelleninhalt;
 else if(document.layers) {
  document.Tabelle.document.open();
  document.Tabelle.document.write(Tabelleninhalt);
  document.Tabelle.document.close();
 }
 draw_bars(Array);
}

function Numsort(a,b)
{ return a-b; }



