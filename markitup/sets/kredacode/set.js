// ----------------------------------------------------------------------------
// markItUp!
// ----------------------------------------------------------------------------
// Copyright (C) 2008 Jay Salvat
// http://markitup.jaysalvat.com/
// ----------------------------------------------------------------------------
// BBCode tags example
// http://en.wikipedia.org/wiki/Bbcode
// ----------------------------------------------------------------------------
// Feel free to add more tags
// ----------------------------------------------------------------------------
mySettings = {
   previewInWindow: 'width=500, height=300, resizable=yes, scrollbars=yes',
	previewParserPath:	'', // path to your BBCode parser
	//previewTemplatePath: '~/parsers/markitup.bbcode-parser.php',
	markupSet: [
		{name:'Fett', key:'F', openWith:'[b]', closeWith:'[/b]', className:"bold", placeHolder:'_'},
		{name:'Kursiv', key:'K', openWith:'[i]', closeWith:'[/i]', className:"italic", placeHolder:'_'},
		{name:'Unterstrichen', key:'U', openWith:'[u]', closeWith:'[/u]', className:"underline", placeHolder:'_'},
		{separator:'---------------' },
		{name:'Grafik', key:'G', className:"grafic",
			beforeInsert:function() {
				$( '#pictureframe' ).dialog('open');
			}
		},
		{name:'Datei', key:'D', className:"file",
			beforeInsert:function() {
				$( '#fileframe' ).dialog('open');
			}, dropMenu: [
				{name:'URL', openWith:'[url;[![Link:!:http://]!];', closeWith:']', placeHolder:'Beschreibung' }
			]
		},
		{separator:'---------------' },
		{name:'Farben', className:"colors", dropMenu: [
			{name:'Gelb', openWith:'[yellow]', closeWith:'[/yellow]', className:"col1-1", placeHolder:'_' },
			{name:'Orange', openWith:'[orange]', closeWith:'[/orange]', className:"col1-2", placeHolder:'_' },
			{name:'Rot', openWith:'[red]', closeWith:'[/red]', className:"col1-3", placeHolder:'_' },
			{name:'Blau', openWith:'[blue]', closeWith:'[/blue]', className:"col2-1", placeHolder:'_' },
			{name:'Gr&uuml;n', openWith:'[green]', closeWith:'[/green]', className:"col2-3", placeHolder:'_' },
			{name:'Braun', openWith:'[brown]', closeWith:'[/brown]', className:"col3-3", placeHolder:'_' },
			{name:'Grau', openWith:'[gray]', closeWith:'[/gray]', className:"col3-2", placeHolder:'_' }
		]},
		{separator:'---------------' },
		{name:'Aufz&auml;hlungsliste - 1. Ebene', openWith:'* ', className:"bullet", placeHolder:'_', dropMenu: [
			{name:'2. Ebene', openWith:'** ', placeHolder:'_' },
			{name:'3. Ebene', openWith:'*** ', placeHolder:'_' }
		]},
		{name:'Nummerierte Liste: 1), 2), 3), ...', openWith:'1) ', className:"numeric", placeHolder:'_', dropMenu: [
			{name:'a), b), c), ...', openWith:'a) ', placeHolder:'_' },
			{name:'A), B), C), ...', openWith:'A) ', placeHolder:'_' },
			{name:'I), II), III), IV), ...', openWith:'I) ', placeHolder:'_' }
		]}, 
		//{name:'List item', openWith:'** ', placeHolder:'_'},
		{separator:'---------------' },
		{name:'Tabellen-Generator', 
			className:'tablegenerator', 
			placeholder:"  ",
			replaceWith:function(h) {
				cols = prompt("Anzahl Spalten?");
				rows = prompt("Anzahl Zeilen?");
				html = "";
				for (r = 0; r < rows; r++) {
					for (c = 0; c < cols; c++) {
						html += "||"+(h.placeholder||"");	
					}
					html += "||\n";
				}
				return html;
			}
		},
		{name:'Formel', key: 'M', openWith:'`', closeWith:'`', placeHolder:'_', className:"equation", dropMenu: [
			{name:'Bruch', openWith:'(', closeWith:')/()', className:"math-bruch", placeHolder:'x+1' },
			{name:'Potenz', openWith:'^(', closeWith:')', className:"math-pot", placeHolder:'m+n' },
			{name:'Index', openWith:'_(', closeWith:')', className:"math-ind", placeHolder:'mn' },
			{name:'Wurzel', openWith:'sqrt(', closeWith:')', className:"math-sqrt", placeHolder:'x' },
			{name:'n-te Wurzel', openWith:'root(n)(', closeWith:')', className:"math-root", placeHolder:'x' },
			{name:'Grenzwert', openWith:'lim_(', closeWith:')', className:"math-lim", placeHolder:'x->oo' },
			{name:'Summe', openWith:'sum_(n=', closeWith:')^oo', className:"math-sum", placeHolder:'1' },
			{name:'Integral', openWith:'int_a^b', closeWith:'dx', className:"math-int", placeHolder:'f(x)' },
			{name:'n &uuml;ber k', openWith:'((', closeWith:'),(k))', className:"math-noverk", placeHolder:'n' },
			{name:'Matrix', openWith:'[[', closeWith:',b],[c,d]]', className:"math-matr", placeHolder:'a' },
			{name:'Hilfe', className:"math-help",beforeInsert:function() {
					window.open("http://www1.chapman.edu/~jipsen/mathml/asciimathsyntax.html", "AsciiMathML-Syntax", "width=1000,height=600,left=400,top=200,scrollbars=yes");
				}
			},
			{name:'Auswahl berechnen', 
				className:'calculator',
				replaceWith:function(markItUp) { 
					try { 
						return eval(markItUp.selection); 
					}
					catch(e){} 
				}
			}
		]},
		{name:'Zusatz', className:"plugin", dropMenu: [
			{name:'umrandet', openWith:'[bor]', closeWith:'[/bor]', className:"border", placeHolder:'_'},
			{name:'liniertes Papier', openWith:'[ruled;', closeWith:'x1;middle]', className:"ruled", placeHolder:'15'},
			{name:'kariertes Papier', openWith:'[boxed;', closeWith:'x1;middle]', className:"boxed", placeHolder:'15'},
			{name:'Millimeterpapier', openWith:'[millimeter;', closeWith:'x1;middle]', className:"millimeter", placeHolder:'15'},
			{name:'Gruppe A/B', openWith:'[#', closeWith:'#B#]', className:"grouping", placeHolder:'Text für Gruppe A'},
			{name:'GeoNEXT-Applet', openWith:'[applet:geonext]', closeWith:'[/applet]', className:"geonext", placeHolder:'<param...> <param...>'},
			{name:'GeoGebra-Applet', openWith:'[applet:geogebra]', closeWith:'[/applet]', className:"geogebra", placeHolder:'<param...> <param...>'},
			{name:'Programm-Code', openWith:'[code]', closeWith:'[/code]', className:"code", placeHolder:'_'}
		]},
		{separator:'---------------' },
		{name:'Vorschau', className:"preview", call:'preview' }
	]
}
