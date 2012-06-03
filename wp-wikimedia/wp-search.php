<?php

function search($file, $media_wikimedia_iframe_src) {
	
		$adresse = "http://commons.wikimedia.org/w/api.php?action=query&list=search&format=xml&srsearch=" . $file . "&srnamespace=6&srprop=size%7Cwordcount%7Ctimestamp%7Cscore%7Csnippet%7Ctitlesnippet%7Credirecttitle%7Credirectsnippet%7Csectiontitle%7Csectionsnippet%7Chasrelated&srlimit=50";	
		
		//RECUPERATION DU XML
		$page = file_get_contents($adresse);
		if(!$result = simplexml_load_string($page))
		{
			echo '<p> Erreur, veuiller mettre un user agent dans php.ini </p>';
		}
		else
		{
			//si on est dans une page sup a 1...
			if(isset($_GET['next']) && !isset($_POST['recherche']))
				$debut = intval($_GET['next']);
			else
				$debut = 0;
				
			
			$result = $result->query[0]->search[0];
			for($i=$debut*20, $col=0;$result->p[$i] && $i<($debut*20)+20;$i++)
			{
				$url = 'http://commons.wikimedia.org/wiki/' . str_replace(' ', '_', $result->p[$i]['title']);	
				$title = str_replace(' ', '_', $result->p[$i]['title']);
				//AFFICHAGE DES NOMS DE FICHIERS
				if($col == 4)
				{
					echo '</tr><tr>';
					for($j=$i-$col;$j<$i;$j++)
					{
						$nametmp = substr($result->p[$j]['title'], 5);
						if (strlen($nametmp) > 39)
							$nametmp = substr($nametmp, 0, 39);
						echo '<td>' . wordwrap($nametmp, 20, '<br />', true) . '</td>';
					}
					echo '</tr><tr>';
					
					$col = 0;
				}
				
				//VERIFICATION TAILLE IMAGE ET CREATION LIEN IMAGE
				$title1 = substr($title, 5);
				$title = urlencode($title);
				$adresseurl = "http://commons.wikimedia.org/w/api.php?action=query&prop=imageinfo&format=xml&iiprop=url|size&titles=" . $title;
				$pageurl = file_get_contents($adresseurl);
				$resulturl = simplexml_load_string($pageurl);
				$resulturl = $resulturl->query[0]->pages[0]->page[0]->imageinfo[0];
				$p = $resulturl->ii[0]['url'];
				$descriptionurl = $resulturl->ii[0]['descriptionurl'];
				if(intval($resulturl->ii[0]['width'])>=120 && intval($resulturl->ii[0]['height'] >= 120))
				{
					$p = substr($p, 0 ,  strpos($p, 'commons/') + 8) 
									 . 'thumb/' . substr($p, strpos($p, 'commons/')+8) . '/120px-' . $title;		
				}
					
				//SUPPERSSION CARACTERES GENANTS
				$descriptionurl = str_replace('\\', '', $descriptionurl);
				$descriptionurl = str_replace('', '_', $descriptionurl);
				$url = str_replace('\\', '', $url);
				
				$url = urlencode($url);
				
				//AFFICHAGE IMAGE
				if(substr($p, -3, 3) == "ogg")
					$p = plugin_dir_url(__FILE__) . "/fileicon-ogg.png";
				echo '<td name ="tdwiki" id="tdwiki"><a href="' . $media_wikimedia_iframe_src . '&amp;TB_iframe=true&amp;height=500&amp;width=640&amp;licence=' . $descriptionurl . '&amp;fichier='. $url. '&amp;name=' . $title1 . '"><img src="' . $p . '" class="wikimedia_image" ></a></td>';
				
				//AFFICHAGE DERNIERE LIGNE TABLEAU
				if(($i - $debut*20) == 19 || !$result->p[$i+1])
				{
					echo '</tr><tr>';
					for($j=$i-$col;$j<=$i;$j++)
						echo '<td>' . wordwrap(substr($result->p[$j]['title'], 5), 20, '<br />', true) . '</td>';
				}
				
				$col++;
			}
			echo '</tr></table>';
			//LIEN SUIVANT PRECEDENT
			echo '<table width="96%"><tr><td align="left">&nbsp;&nbsp;&nbsp;&nbsp;';
			if(isset($_GET['next']) && intval($_GET['next']) -1>=0)
			{
				echo '<a href="' . $media_wikimedia_iframe_src . '&amp;TB_iframe=true&amp;height=500&amp;width=640&amp;next=' . (intval($_GET['next']) -1) . '&amp;recherche=' . $file . '&amp;search=search">' . utf8_encode('Précèdent') . '</a>';
				
			}
			if((isset($_GET['next']) && intval($_GET['next']) -1<1) || !isset($_GET['next']))
			{
				echo '</td><td align="right">';
				echo '<a href="' . $media_wikimedia_iframe_src . '&amp;TB_iframe=true&amp;height=500&amp;width=640&amp;next=' . (intval($_GET['next']) +1) . '&amp;recherche=' . $file . '&amp;search=search" align="right" >Suivant</a>';
				echo '</td></tr></table>'; 
			}
		}
	}
	
?>