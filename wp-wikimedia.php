<?php
/*
Plugin Name: Wordpress Wikimedia
Plugin URI: https://www.mediawiki.org/wiki/User:Jean-Frédéric/CommonsOnCMS
Description: Wikimedia.
Author: Jerome Deboffles Mickael Lemaitre
Version: 0.0.2
Author URI: https://www.mediawiki.org/wiki/User:Jean-Frédéric/CommonsOnCMS
*/

if (!class_exists("wp_wikimedia")) {  
  
    class wp_wikimedia
    {  
        /** 
         * Constructor 
         */  
        function wp_wikimedia()  
        {  
			add_filter('media_upload_tabs', array($this, 'modifyMediaTab'));
            add_action('media_buttons', array($this, 'addMediaButton'), 20);
			add_action('media_upload_wikimedia', array($this, 'media_upload_wikimedia'));
        }
		
		
		function addMediaButton() {
			global $post_ID, $temp_ID;
			$uploading_iframe_ID = (int) (0 == $post_ID ? $temp_ID : $post_ID);
			$media_upload_iframe_src = "media-upload.php?post_id=$uploading_iframe_ID";

			$media_wikimedia_iframe_src = apply_filters('media_wikimedia_iframe_src', "$media_upload_iframe_src&amp;type=wikimedia&amp;tab=wikimedia");
			$wikimedia_title = __('Add Wikimedia picture', 'wp-wikimedia');

			echo "<a href=\"{$media_wikimedia_iframe_src}&amp;TB_iframe=true&amp;height=500&amp;width=640\" class=\"thickbox\" title=\"$wikimedia_title\">WIKIMEDIA</a>";
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

		// DEFINITION VAR GLOB. IFRAME AND USER AGENT
		global $post_ID, $temp_ID;
		$uploading_iframe_ID = (int) (0 == $post_ID ? $temp_ID : $post_ID);
		$media_upload_iframe_src = "media-upload.php?post_id=$uploading_iframe_ID";
		$media_wikimedia_iframe_src = apply_filters('media_wikimedia_iframe_src', "$media_upload_iframe_src&amp;type=wikimedia&amp;tab=wikimedia");
		media_upload_header();
		ini_set ('user_agent', '”Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)”');

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
		var uploadID = ''; /*setup the var*/
		</style>
		<script type="text/javascript" src="http://code.jquery.com/jquery-latest.js"></script>
		<!-- SCRIPT TO SEND AREA WORDPRESS -->
		<script type="text/javascript">
		function send_to_editor(close) {
			
			var titre = document.getElementById('img_title').value;
			var alt = document.getElementById('img_alt').value;
			var legende = document.getElementById('img_cap').value;
			var link = document.getElementById('link_hrefimage').value;
			var link2 = document.getElementById('link_hreflicence').value;
			var taille;
			var align;
			var image;
			
			// TAILLE ET ALIGNEMENT
			var inputs = document.getElementsByTagName('input'),
			  inputsLength = inputs.length;
			var j =0
			for (var i = 0 ; i < inputsLength ; i++) {
			  if (inputs[i].type == 'radio' && inputs[i].checked) {
				if(j==0)
				{
				  taille = inputs[i].value;
				  j++;
				 }
				 else
					align = inputs[i].value;
			  }
			}
			
			var ed;
			var total = "<div class=\"wp-caption " + align + "\">";
				total = total + "<p><a href=\"" + link +  "\"><img title=\"" + titre + "\" src=\"" + taille + "\" alt=\"" + alt + "\"></a></p>";

			total = total + "<p class=\"wp-caption-text\"><a href=\"" + link +  "\">" + legende + "</a></p></div>";
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
		</script>
		
		
		<?php
		if(!isset($_GET['fichier']))
		{
			// CREATION LIEN DE RECHERCHE
			$recherche = "wikimedia";
			if(isset($_GET['recherche']))
				$recherche = $_GET['recherche'];
			if(isset($_POST['recherche']))
				$recherche = $_POST['recherche'];
				
			$recherche = str_replace(' ', '%20', $recherche);
			$adresse = "http://commons.wikimedia.org/w/api.php?action=query&list=allimages&format=xml&aifrom=" . $recherche . "&ailimit=500&aiprop=url|dimensions";	
			
			//RECUPERATION DU XML
			$page = file_get_contents($adresse);
			
			if(!$result = simplexml_load_string($page))
			{
				echo '<p> Erreur, veuiller mettre un user agent dans php.ini </p>';
			}
			else
			{
			
				echo '<p></p><div id="search-filter">
						<form method="post" action="">
						<input type="text" name="recherche" align="rigth" /><input type="submit" value="Recherche" class="button" align="rigth"/>
						</form>
						</div>';
				echo '<table name="tabwikimedia" id="tabwikimedia" align="center"><tr>';
				
				//si on est dans une page sup a 1...
				if(isset($_GET['next']) && !isset($_POST['recherche']))
					$debut = intval($_GET['next']);
				else
					$debut = 0;
					
				
				$result = $result->query[0]->allimages[0];
				for($i=$debut*20, $col=0;$result->img[$i] && $i<($debut*20)+20;$i++)
				{
					$descriptionurl =$result->img[$i]['descriptionurl'];
					$url = $result->img[$i]['url'];	
					
					//AFFICHAGE DES NOMS DE FICHIERS
					if($col == 4)
					{
						echo '</tr><tr>';
						for($j=$i-$col;$j<$i;$j++)
							echo '<td>' . wordwrap($result->img[$j]['name'], 20, '<br />', true) . '</td>';
						echo '</tr><tr>';
						
						$col = 0;
					}
					
					//VERIFICATION TAILLE IMAGE ET CREATION LIEN IMAGE
					if(intval($result->img[$j]['width'])>120 && intval($result->img[$j]['heigth'] > 120))
						$img = substr($url, 0 ,  strpos($url, 'commons/') + 8) 
										. 'thumb/' . substr($url, strpos($url, 'commons/')+8) . '/120px-' . $result->img[$i]['name'];
					else
						$img = $url;
						
					//SUPPERSSION CARACTERE GENANT
					$descriptionurl = str_replace('\\', '', $descriptionurl);
					$url = str_replace('\\', '', $url);
					
					//AFFICHAGE IMAGE
					echo '<td name ="tdwiki" id="tdwiki"><a href="' . $media_wikimedia_iframe_src . '&amp;TB_iframe=true&amp;height=500&amp;width=640&amp;licence=' . $descriptionurl . '&amp;fichier='. $url. '&amp;name=' . $result->img[$i]['name'] . '"><img src="' . $img . '" class="wikimedia_image" ></a></td>';
					
					//AFFICHAGE DERNIERE LIGNE TABLEAU
					if(($i - $debut*20) == 19)
					{
						echo '</tr><tr>';
						for($j=$i-$col-1;$j<$i;$j++)
							echo '<td>' . wordwrap($result->img[$j]['name'], 20, '<br />', true) . '</td>';
						echo '</tr></table>';
					}
					
					$col++;
				}
				
				//LIEN SUIVANT PRECEDENT
				echo '<table width="96%"><tr><td align="left">&nbsp;&nbsp;&nbsp;&nbsp;';
				if(isset($_GET['next']) && intval($_GET['next']) -1>=0)
				{
					echo '<a href="' . $media_wikimedia_iframe_src . '&amp;TB_iframe=true&amp;height=500&amp;width=640&amp;next=' . (intval($_GET['next']) -1) . '&amp;recherche=' . $recherche . '">' . utf8_encode('Précèdent') . '</a>';
					
				}
				echo '</td><td align="right">';
				echo '<a href="' . $media_wikimedia_iframe_src . '&amp;TB_iframe=true&amp;height=500&amp;width=640&amp;next=' . (intval($_GET['next']) +1) . '&amp;recherche=' . $recherche . '" align="right" >Suivant</a>';
				echo '</td></tr></table>'; 
			}
			
			
		}else{
			//IMAGE SELECTIONNEE
			
			//SUPPRESSION CARACTERE GENANT
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
							<?php 
							$check =false;
							for($i=0;$i<sizeof($othresol);$i++)
							{ 
								if($i==0)
								{
									echo '<input type="radio" name="img_res" id="img_res' . $i .'" value="' . $othresol[$i] . '" CHECKED/>
									<label for="alignnone">' . $othresolname[$i] . '&nbsp;&nbsp;&nbsp;</label>';
									$check = true;
								}
								else
								{
									echo '<input type="radio" name="img_res" id="img_res' . $i .'" value="' . $othresol[$i] . '"/>
									<label for="alignnone">' . $othresolname[$i] . '&nbsp;&nbsp;&nbsp;</label>';
								}
							}
							if($check)
								echo '<input type="radio" name="img_res" id="img_res' . 'full' .'" value="' . $fullresol . '"/>
									<label for="alignnone">' . 'full resolution' . '&nbsp;&nbsp;&nbsp;</label>';
							else
								echo '<input type="radio" name="img_res" id="img_res' . 'full' .'" value="' . $fullresol . '" CHECKED/>
									<label for="alignnone">' . 'full resolution' . '&nbsp;&nbsp;&nbsp;</label>';
							?>
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
					<tr id="cap_field">
						<th valign="top" scope="row" class="label">
							<label for="img_cap">
								<span class="alignleft"><?php echo utf8_encode('Légende'); ?> </span>
							</label>
					</th>
						<td class="field">
							<input type="text" id="img_cap" name="img_cap" value="<?php echo $lic; ?>" size="60"/>
						</td>
					</tr>
					<tr>
						<th valign="top" scope="row" class="label">
							<label for="link_href">
							<span class="alignleft" id="lb_link_href">Cible du lien</span>
							</label>
						</th>
						<td class="field">
							<input type="text" id="link_hrefimage" name="link_hrefimage" value="<?php echo $_GET['licence'];?>" size="60"/>
							<input type="hidden" id="link_hreflicence" name="link_hreflicence" value="<?php echo $_GET['licence'];?>"/>
							<input type="hidden" id="link_hreffichier" name="link_hreffichier" value="<?php echo $_GET['fichier'];?>"/>
						
							<br/>
							<p class="help">Saisissez une adresse web </p>
						</td>
					</tr>
				</table>
			</div>
			<?php
			echo '<input type="submit" value="Insert" onclick="send_to_editor(1)">';
		
		}
	}

?>

