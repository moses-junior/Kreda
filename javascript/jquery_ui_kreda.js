		$(function() {
            // allgemeine Datepicker
            var append_to_regional = $.datepicker.regional['de'];
            append_to_regional.showOtherMonths=true;
            append_to_regional.selectOtherMonths=true;
			$(".datepicker").datepicker(append_to_regional);
            
            // Button-Elemente
            $("button, input:submit, input:button").button();
            $(".toggle").button();
            
            // Select-Elemente
/*            $('select.jqui_selector').selectmenu({
                style: 'dropdown',
                maxHeight: 300,
                width: 300,
				menuWidth: 400,
				format: addressFormatting
			});*/
            
            // Dialoge
            $( '#pictureframe' ).dialog({
               autoOpen: false,
					height: 650,
               width: 880,
					open: function ()
					{
               	document.getElementById('pictureframe').style.display='block';
               },
					title: 'Grafik einf&uuml;gen',
					modal: true
				});

            $( '#fileframe' ).dialog({
               autoOpen: false,
					height: 650,
               width: 880,
					open: function ()
					{
   					document.getElementById('fileframe').style.display='block';
               },
					title: 'Datei einf&uuml;gen',
					modal: true
				});

/*            $('a.ajax').click(function() {
                var url = this.href;
                var dialog = $('<div style="display:hidden"></div>').appendTo('body');
                // load remote content
                dialog.load(
                    url, 
                    {},
                    function (responseText, textStatus, XMLHttpRequest) {
                        dialog.dialog();
                    }
                );
                //prevent the browser to follow the link
                return false;
            });*/
        
		});
        
        //a custom format option callback - spaeter loeschen!
		var addressFormatting = function(text){
			var newText = text;
			//array of find replaces
			var findreps = [
				{find:/^([^\_]+) \_ /g, rep: '<span class="ui-selectmenu-item-header">$1</span>'},
				{find:/([^\|><]+) \| /g, rep: '<span class="ui-selectmenu-item-content">$1</span>'},
				{find:/([^\|><\(\)]+)$/g, rep: '<span class="ui-selectmenu-item-content">$1</span>'}
			];
			
			for(var i in findreps){
				newText = newText.replace(findreps[i].find, findreps[i].rep);
			}
			return newText;
		}

