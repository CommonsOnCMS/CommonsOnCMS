<?php

function querry($file, $media_wikimedia_iframe_src) {
	
		$adresse = "http://commons.wikimedia.org/w/api.php?action=query&list=allimages&format=xml&aiprefix=" . $file . "&ailimit=500&aiprop=url|dimensions";	
		
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
					{
						$nametmp = $result->img[$j]['name'];
						if (strlen($nametmp) > 39)
							$nametmp = substr($nametmp, 0, 39);
						echo '<td>' . wordwrap($nametmp, 20, '<br />', true) . '</td>';
					}
					echo '</tr><tr>';
					
					$col = 0;
				}
				
				//VERIFICATION TAILLE IMAGE ET CREATION LIEN IMAGE
				//echo $result->img[$j]['width'] . ' ';
				//echo $result->img[$j]['height'] . '<br />';
				if(intval($result->img[$i]['width'])>=120 && intval($result->img[$i]['height'] >= 120))
				{
					$img = substr($url, 0 ,  strpos($url, 'commons/') + 8) 
									. 'thumb/' . substr($url, strpos($url, 'commons/')+8) . '/120px-' . $result->img[$i]['name'];
					//echo 'ok';
				}
				else
				{
					$img = $url;
					//echo 'ko';
				}
					
				//SUPPERSSION CARACTERE GENANT
				$descriptionurl = str_replace('\\', '', $descriptionurl);
				$url = str_replace('\\', '', $url);
				
				//AFFICHAGE IMAGE
				echo '<td name ="tdwiki" id="tdwiki"><a href="' . $media_wikimedia_iframe_src . '&amp;TB_iframe=true&amp;height=500&amp;width=640&amp;licence=' . $descriptionurl . '&amp;fichier='. $url. '&amp;name=' . $result->img[$i]['name'] . '"><img src="' . $img . '" class="wikimedia_image" ></a></td>';
				
				//AFFICHAGE DERNIERE LIGNE TABLEAU
				if(($i - $debut*20) == 19)
				{
					echo '</tr><tr>';
					for($j=$i-$col;$j<=$i;$j++)
						echo '<td>' . wordwrap($result->img[$j]['name'], 20, '<br />', true) . '</td>';
					echo '</tr></table>';
				}
				
				$col++;
			}
			
			//LIEN SUIVANT PRECEDENT
			echo '<table width="96%"><tr><td align="left">&nbsp;&nbsp;&nbsp;&nbsp;';
			if(isset($_GET['next']) && intval($_GET['next']) -1>=0)
			{
				echo '<a href="' . $media_wikimedia_iframe_src . '&amp;TB_iframe=true&amp;height=500&amp;width=640&amp;next=' . (intval($_GET['next']) -1) . '&amp;recherche=' . $file . '&amp;search=querry">' . utf8_encode('Précèdent') . '</a>';
				
			}
			echo '</td><td align="right">';
			echo '<a href="' . $media_wikimedia_iframe_src . '&amp;TB_iframe=true&amp;height=500&amp;width=640&amp;next=' . (intval($_GET['next']) +1) . '&amp;recherche=' . $file . '&amp;search=querry" align="right" >Suivant</a>';
			echo '</td></tr></table>'; 
		}
	}
	
?>