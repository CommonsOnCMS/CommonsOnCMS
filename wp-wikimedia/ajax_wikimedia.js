 jQuery(document).ready(function($) {
	jQuery('#bdown').click( function() {
		document.getElementById('resultdownload').innerHTML += "<img src=\"" + document.getElementById('pathplug').value + "\" />";
		jQuery.ajax({
			type: 'POST',
			url: wp_wikimedia_script.ajaxurl,
			dataType: 'json',
			data: {
				action : wp_wikimedia_script.action,
				wpwikimedianonce : wp_wikimedia_script.nonce,
				fichier :  document.getElementById("resol").value,
				path : document.getElementById('pathplug').value
			},
			success : function(data){
				if (data.error == 0) {
					var newOption = document.createElement("option");
					newOption.setAttribute("value",data.pathf);
					newOption.innerHTML="local";
					newOption.defaultSelected = true;
					document.getElementById("resol").appendChild(newOption);
					document.getElementById('resultdownload').innerHTML = " done!";
				}
				else {
					document.getElementById('resultdownload').innerHTML = " KO!";
				}
			},
			error : function(XMLHttpRequest, textStatus, errorThrown) {
				document.getElementById('resultdownload').innerHTML = " KO!";
			} // End of success function
		});
	});
});

