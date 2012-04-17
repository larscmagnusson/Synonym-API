$(document).ready(function(){
		
	var parameters;		// Parametrarnas värden
	
	$('.parameter').QueryBuilder({'onchange':function(query){
			// Denna funktion anropas när en parameter i QueryBuilder ändrats.
			
			$('input#query-output').val("http://oddcorn.com.hemsida.eu/lars/synonym_api/api?"+query.string);
			parameters = query.object;	// Gör parametrarna tillgängliga utanför detta scope
		}
	});
	
	// Gör anrop till API't med genererad query
	$('#submit-btn').click(function(){
		$.ajax({
			url:'http://oddcorn.com.hemsida.eu/lars/synonym_api/api',
			data:parameters,
			complete:function(data){
				$('#data-viewer #bg-fader').fadeIn(100,function(){
					$('#data-viewer pre').text(swedish_char_decode(data.responseText));
				}).fadeOut(100);
			}
		});
	});
	
	// Visar info om varje parameter vid mouseover
	$('.info .icon').mouseover(function(){
		$('.info .text:visible').fadeOut(200);
		$(this).siblings('.text').fadeIn(200);
	});
	
	// Döljer infon som visas vid mouseover
	$('.info .text').mouseout(function(){
		$(this).fadeOut(200);
	});
	
	// Döljer eventuella parameter-info-box som visas när man klickar någonstans
	$('body').click(function(){
		$('.info .text:visible').fadeOut(200);
	});
	
	// Visar API-nyckel fältet vid klick.
	$('#api-key h4').click(function(){
		$(this).hide();
		$(this).siblings('input[type=text]').fadeIn(200).blur(function(){
			$(this).hide();
			$(this).siblings('h4').show();
			
			$('#check').html( ($(this).val() !== "") ? "✓" : "✗");
		})
	});
	
});

// Funktion för att fixa encoding på ÅÄÖ
function swedish_char_decode(str){
	
	str = str.replace(/\\u00e5/g, "å");
	str = str.replace(/\\u00c5/g, "Å");
	str = str.replace(/\\u00e4/g, "ä");
	str = str.replace(/\\u00c4/g, "Ä");
	str = str.replace(/\\u00f6/g, "ö");
	str = str.replace(/\\u00d6/g, "Ö");
	
	str = str.replace(/&#xE5;/g, "å");
	str = str.replace(/&#xC5;/g, "Å");
	str = str.replace(/&#xE4;/g, "ä");
	str = str.replace(/&#xC4;/g, "Ä");
	str = str.replace(/&#xF6;/g, "ö");
	str = str.replace(/&#xD6;/g, "Ö");
	
	return str;
}