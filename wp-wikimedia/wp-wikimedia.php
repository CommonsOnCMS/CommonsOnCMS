<?php
/*
Plugin Name: Wordpress Wikimedia
Plugin URI: https://www.mediawiki.org/wiki/User:Jean-Frédéric/CommonsOnCMS
Description: Wikimedia.
Author: Jerome Deboffles Mickael Lemaitre
Version: 0.0.2
Author URI: https://www.mediawiki.org/wiki/User:Jean-Frédéric/CommonsOnCMS
*/
include 'wp-querry.php';
include 'wp-opensearch.php';
if (!class_exists("wp_wikimedia")) {  
  
    class wp_wikimedia
    {  
        /** 
         * Constructor 
         */  
        function wp_wikimedia()  
        {  
			
            add_action('media_buttons', array($this, 'addMediaButton'), 20);
			add_action('media_upload_wikimedia', array($this, 'media_upload_wikimedia'));
        }
		
		
		function addMediaButton() {
			global $post_ID, $temp_ID;
			$uploading_iframe_ID = (int) (0 == $post_ID ? $temp_ID : $post_ID);
			$media_upload_iframe_src = "media-upload.php?post_id=$uploading_iframe_ID";

			$media_wikimedia_iframe_src = apply_filters('media_wikimedia_iframe_src', "$media_upload_iframe_src&amp;type=wikimedia&amp;tab=wikimedia");
			$wikimedia_title = __('Add Wikimedia picture', 'wp-wikimedia');

			echo "<a href=\"{$media_wikimedia_iframe_src}&amp;TB_iframe=true&amp;height=500&amp;width=640\" class=\"thickbox\" title=\"$wikimedia_title\">Wikimedia Commons</a>";
		}
		
		
		function modifyMediaTab($tabs) {
			return array(
				'wikimedia' =>  __('Wikimedia Pictures', 'wp-wikimedia')
			);
		}
		
		function media_upload_wikimedia() {
			wp_iframe('media_upload_type_wikimedia');
		}
		
    }  
}  

if (class_exists("wp_wikimedia"))  
{  
    $inst_wp_wikimedia = new wp_wikimedia();  
}

	
	function media_upload_type_wikimedia() {
	
		add_filter('media_upload_tabs', array($this, 'modifyMediaTab'));
		media_upload_header();
	
		// DEFINITION VAR GLOB. IFRAME AND USER AGENT
		global $post_ID, $temp_ID;
		$uploading_iframe_ID = (int) (0 == $post_ID ? $temp_ID : $post_ID);
		$media_upload_iframe_src = "media-upload.php?post_id=$uploading_iframe_ID";
		$media_wikimedia_iframe_src = apply_filters('media_wikimedia_iframe_src', "$media_upload_iframe_src&amp;type=wikimedia&amp;tab=wikimedia");
		
		ini_set ('user_agent', '”Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)”');
		
		// CSS & SCRIPT
		css();
		script();

		if(!isset($_GET['recherche']) && !isset($_POST['recherche']) && !isset($_GET['fichier']))
		{
		?>
			<div id="search-acc">
				<form method="post" action="">
				<input type="text" name="recherche" align="rigth" value="<?php echo $recherche?>"/><input type="submit" value="Recherche" class="button" align="rigth"/>
				<br />
				<select name="search" id="search">
				   <option value="opensearch" <?php if($engine == "opensearch") echo 'selected';?>>OpenSearch</option>
				   <option value="querry" <?php if($engine == "querry") echo 'selected';?>>Querry</option>
				</select>
				</form>
			</div>
		<?php
		}
		else if(!isset($_GET['fichier']))
		{
		
			// CREATION LIEN DE RECHERCHE
			$recherche = "wikimedia";
			if(isset($_GET['recherche']))
				$recherche = $_GET['recherche'];
			if(isset($_POST['recherche']))
				$recherche = $_POST['recherche'];
				
			$recherche = str_replace(' ', '%20', $recherche);
			
			//SELECTION MOTEUR DE RECHERCHE
			$engine="opensearch";
			if(isset($_GET['search']))
				if($_GET['search'] == "querry")
					$engine="querry";
			if(isset($_POST['search']))
				if($_POST['search'] == "querry")
					$engine="querry";
				else
					$engine="opensearch";
			
			?>
				<p></p><div id="search-filter">
					<form method="post" action="">
					<input type="text" name="recherche" align="rigth" value="<?php echo str_replace('%20', ' ', $recherche)?>"/><input type="submit" value="Recherche" class="button" align="rigth"/>
					<select name="search" id="search">
					   <option value="opensearch" <?php if($engine == "opensearch") echo 'selected';?>>OpenSearch</option>
					   <option value="querry" <?php if($engine == "querry") echo 'selected';?>>Querry</option>
					</select>
					</form>
					</div>
				<table name="tabwikimedia" id="tabwikimedia" align="center"><tr>
			<?php
			
			//LANCEMENT DE LA RECHERCHE
			if($engine=="opensearch")
				opensearch($recherche, $media_wikimedia_iframe_src);
			else
				querry($recherche, $media_wikimedia_iframe_src);
				
		}else{
			//IMAGE SELECTIONNEE
			
			//SUPPRESSION CARACTERES GENANT
			$_GET['fichier'] = str_replace('\\', '', $_GET['fichier']);
			$_GET['licence'] = str_replace('\\', '', $_GET['licence']);
			$_GET['name'] = str_replace('\\', '', $_GET['name']);
			
			//RECUPERATION PAGE DE DESCRIPTION
			$page1 = file_get_contents($_GET['licence']);
			
			//RECHERCHE LICENCE PAR XPATH
			$doc = new DOMDocument();
			$doc->loadHTMLFile($_GET['licence']);
		
			$xpath = new DOMXpath($doc);
		
			$elements = $xpath->query("//span[@class='licensetpl_short']");
			$lic = "";
			if (!is_null($elements)) {
				foreach ($elements as $element) {	
					$nodes = $element->childNodes;
					foreach ($nodes as $node) {
						$lic .= $node->nodeValue. " - ";
					}
				}
			}
			//RECHERCHE FULL RESOLUTION PAR XPATH
			$elements = $xpath->query("//div[@class='fullMedia']/a/@href");
		
			if (!is_null($elements)) {
				foreach ($elements as $element) {
					$nodes = $element->childNodes;
					foreach ($nodes as $node) {
						$fullresol = 'http:'. trim($node->nodeValue);
					}
				}
			}
			
			
			//RECHERCHE OTHERS RESOLUTIONS

			//tableau de liens de toutes les resolutions
			$othresol = array();
			
			$elements = $xpath->query("//span[@class='mw-filepage-other-resolutions']/a/@href");
			if (!is_null($elements)) {
				foreach ($elements as $element) {
					$nodes = $element->childNodes;
					foreach ($nodes as $node) {
						$othresol[] = 'http:'. trim($node->nodeValue);
					}
				}
			}
			
			//tableau de nom de resolution
			$othresolname = array();
			
			$elements = $xpath->query("//span[@class='mw-filepage-other-resolutions']/a");
			if (!is_null($elements)) {
				foreach ($elements as $element) {
					$nodes = $element->childNodes;
					foreach ($nodes as $node) {
						$othresolname[] = trim($node->nodeValue);
					}
				}
			}
			
			//LIEN LICENCE licensetpl_link
			$elements = $xpath->query("//span[@class='licensetpl_link']");
			if (!is_null($elements)) {
				foreach ($elements as $element) {
					$nodes = $element->childNodes;
					foreach ($nodes as $node) {
						$lice = $node->nodeValue;
					}
				}
			}
			
			//LIEN RETOUR
			echo '<a href="javascript:window.history.go(-1)">Retour</a>';
			//AFFICHAGE DE L'IMAGE
			if($othresol[0]) // SI il esiste d'autre resolution...
				echo '<p><img src="' . $othresol[0] . '" align="center"></p>';
			else // Sinon on affiche resolution full
				echo '<p><img src="' . $_GET['fichier'] . '"></p>';
			
			
			
			?>
				<div id="div_basic">
				<table id="basic" class="describe">
					<tr>
						<th valign="top" scope="row" class="label">
						<label for="img_alt">
							<span class="alignleft"><?php echo utf8_encode("Résolution");?></span>
						</label>
						</th>
						<td class="field">
							<select name="resol" id="resol">
							<?php 
							$check =false;
							for($i=0;$i<sizeof($othresol);$i++)
							{ 
								echo '<option value="' . $othresol[$i] . '"/>' .  $othresolname[$i] . '</option>';
							}
								echo '<option value="' . $fullresol . '"/>Full resolution</option>';
							?>
							</select>
						</td>
					</tr>
					<tr class="align">
						<th valign="top" scope="row" class="label">
						<label for="img_align_td">
							<span class="alignleft">Alignement</span>
						</label>
					</th>
						<td class="field" id="img_align_td">
							<input type="radio" onclick="wpImage.imgAlignCls('alignnone')" name="img_align" id="alignnone" value="alignnone" CHECKED/>
							<label for="alignnone" class="align image-align-none-label">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Aucun</label>
							<input type="radio" onclick="wpImage.imgAlignCls('alignleft')" name="img_align" id="alignleft" value="alignleft"/>
							<label for="alignleft" class="align image-align-left-label">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Gauche</label>
							<input type="radio" onclick="wpImage.imgAlignCls('aligncenter')" name="img_align" id="aligncenter" value="aligncenter"/>
							<label for="aligncenter" class="align image-align-center-label">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Centre</label>
							<input type="radio" onclick="wpImage.imgAlignCls('alignright')" name="img_align" id="alignright" value="alignright"/>
							<label for="alignright" class="align image-align-right-label">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Droite</label>
						</td>
					</tr>
					<tr>
						<th valign="top" scope="row" class="label">
						<label for="img_title">
							<span class="alignleft">Titre</span>
						</label>
						</th>
						<td class="field">
							<input type="text" id="img_title" name="img_title" value="<?php echo $_GET['name']; ?>" aria-required="true" size="60"/>
						</td>
					</tr>
					<tr>
						<th valign="top" scope="row" class="label">
						<label for="img_alt">
							<span class="alignleft">Texte alternatif</span>
						</label>
						</th>
						<td class="field">
							<input type="text" id="img_alt" name="img_alt" value="" size="60"/>
						</td>
					</tr>
					<tr id="cap_licence">
						<th valign="top" scope="row" class="label">
							<label for="img_cap">
								<span class="alignleft"><?php echo utf8_encode('Licence'); ?> </span>
							</label>
					</th>
						<td class="field">
							<input type="text" id="img_capl" name="img_capl" value="<?php echo $lic; ?>" size="60"/>
						</td>
					</tr>
					<tr>
						<th valign="top" scope="row" class="label">
							<label for="link_href">
							<span class="alignleft" id="lb_link_href">Cible du lien (Licence)</span>
							</label>
						</th>
						<td class="field">
							<input type="text" id="link_hreflicence" name="link_hreflicence" value="<?php echo $lice?>"/>
						</td>
					</tr>
					<tr id="cap_field">
						<th valign="top" scope="row" class="label">
							<label for="img_cap">
								<span class="alignleft"><?php echo utf8_encode('Légende'); ?> </span>
							</label>
					</th>
						<td class="field">
							<input type="text" id="img_cap" name="img_cap" value="" size="60"/>
						</td>
					</tr>
					<tr>
						<th valign="top" scope="row" class="label">
							<label for="link_href">
							<span class="alignleft" id="lb_link_href">Cible du lien (Image)</span>
							</label>
						</th>
						<td class="field">
							<input type="text" id="link_hrefimage" name="link_hrefimage" value="<?php echo $_GET['licence'];?>" size="60"/>
							<input type="hidden" id="link_hreffichier" name="link_hreffichier" value="<?php echo $_GET['fichier'];?>"/>
						
							<br/>
							<p class="help">Saisissez une adresse web </p>
						</td>
					</tr>
				</table>
					<p>
						<input type="button" onclick="request(readData);" value="Picture Download" />
					</p>
					<span id="resultdownload" name="resultdownload"></span>
			</div>
			<?php
			echo '<input type="submit" value="Insert" onclick="send_to_editor(1)">';
		
		}
	}

	
		function css() {
		?>
		<!-- STYLE CSS WIKIMEDIA TAB -->
		<style type="text/css">
		.wikimedia_photo {
			width: 90px;
			padding: 5px 7px;
			float: left;
			height: 110px;
		}
		.wikimedia_image {
			border: 0px;
			width: 75px;
			height: 75px;
			cursor: pointer;
		}
		table#tabwikimedia
		{
			border-collapse: separate;
			border-spacing: 5px 5px;
			text-align: center;
		}
		#search-filter label {
			display: inline;
			font-size: 80%;
		}
		div#search-acc {
			text-align:center;
			margin-bottom = "120px";
		}
		var uploadID = ''; /*setup the var*/
		</style>
		<?php
	}
	
	function script() {
		?>
		
		<script type="text/javascript" src="http://code.jquery.com/jquery-latest.js"></script>
		<!-- SCRIPT TO SEND AREA WORDPRESS -->
		<script type="text/javascript">
		<!-- 
		function send_to_editor(close) {
			
			var titre = document.getElementById('img_title').value;
			var alt = document.getElementById('img_alt').value;
			var legende = document.getElementById('img_cap').value;
			var licence = document.getElementById('img_capl').value;
			var link = document.getElementById('link_hrefimage').value;
			var link2 = document.getElementById('link_hreflicence').value;
			var taille = document.getElementById('resol').value;
			var align;
			var image;
			
			// TAILLE ET ALIGNEMENT
			var inputs = document.getElementsByTagName('input'),
			  inputsLength = inputs.length;
			var j =0
			for (var i = 0 ; i < inputsLength ; i++) {
			  if (inputs[i].type == 'radio' && inputs[i].checked) {
				align = inputs[i].value;
			  }
			}
			
			var ed;
			var total = "<div class=\"wp-caption " + align + "\">";
				total = total + "<p><a href=\"" + link +  "\"><img title=\"" + titre + "\" src=\"" + taille + "\" alt=\"" + alt + "\"></a></p>";

			total = total + "<p class=\"wp-caption-text\"><a href=\"" + link +  "\">" + licence + "</a>" + legende + "</p></div><div></div>";
			image = total;
			if ( typeof top.tinyMCE != 'undefined' && ( ed = top.tinyMCE.activeEditor ) && !ed.isHidden() ) {
				// restore caret position on IE
				if ( top.tinymce.isIE && ed.windowManager.insertimagebookmark )
					ed.selection.moveToBookmark(ed.windowManager.insertimagebookmark);

				if ( image.indexOf('[caption') === 0 ) {
					if ( ed.plugins.wpeditimage )
						image = ed.plugins.wpeditimage._do_shcode(image);
				} else if ( image.indexOf('[gallery') === 0 ) {
					if ( ed.plugins.wpgallery )
						image = ed.plugins.wpgallery._do_gallery(image);
				} else if ( image.indexOf('[embed') === 0 ) {
					if ( ed.plugins.wordpress )
						image = ed.plugins.wordpress._setEmbed(image);
				}

				ed.execCommand('mceInsertContent', false, image);
				$('iframe#tinymce:first').contents().find('img').each(function() { this.src = this.src });

			} else if ( typeof top.edInsertContent == 'function' ) {
				top.edInsertContent(top.edCanvas, image);
			} else {
				top.jQuery( top.edCanvas ).val( top.jQuery( top.edCanvas ).val() + image );
			}
			
			if(close) {
				top.tb_remove();
			}
		}
		
		
		function getXMLHttpRequest() {
			var xhr = null;
			
			if (window.XMLHttpRequest || window.ActiveXObject) {
				if (window.ActiveXObject) {
					try {
						xhr = new ActiveXObject("Msxml2.XMLHTTP");
					} catch(e) {
						xhr = new ActiveXObject("Microsoft.XMLHTTP");
					}
				} else {
					xhr = new XMLHttpRequest(); 
				}
			} else {
				alert("Votre navigateur ne supporte pas l'objet XMLHTTPRequest...");
				return null;
			}
		
				return xhr;
		}
		
		function request(callback) {
			var xhr = getXMLHttpRequest();
			//alert('ok');
			xhr.onreadystatechange = function() {
				if (xhr.readyState == 4 && (xhr.status == 200 || xhr.status == 0)) {
					callback(xhr.responseText);
				}
			};
			
			var image = encodeURIComponent(document.getElementById("resol").value);
			document.getElementById('resultdownload').innerHTML = '<p>Picture downloading...</p>';
			xhr.open("GET", "../wp-content/plugins/wp-wikimedia/XMLHttpRequest_downloadimg.php?fichier=" + image, true);
			xhr.send(null);
		}

		function readData(sData) {
			document.getElementById('resultdownload').innerHTML += sData;
			if(sData.indexOf("error") == -1)
			{
				var oOption = document.createElement("option");
				document.getElementById('resol').innerHTML = 'test.jpg';
			}else{
				alert('error');
			}
		}
		//-->
		</script>
		<?php
	}

?>

