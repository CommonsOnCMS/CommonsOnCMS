<?php
function opensearch($file, $media_wikimedia_iframe_src) {
	
	$adresse = "http://commons.wikimedia.org/w/api.php?action=opensearch&format=xml&search=" . $file . "&limit=100&namespace=6";
	//RECUPERATION DU XML
	$page = file_get_contents($adresse);
	if(!$result = simplexml_load_string($page))
	{
		echo '<p> Erreur, veuiller mettre un user agent dans php.ini </p>';
	}
	else
	{
	
		if(isset($_GET['next']) && !isset($_POST['recherche']))
			$debut = intval($_GET['next']);
		else
			$debut = 0;
			
		$result = $result->Section[0];
		for($i=$debut*20, $col=0; $i<($debut*20)+20;$i++)
		{
			
			$descriptionurl = $result->Item[$i]->Url;
			$url = $result->Item[$i]->Image[0]['source'];	
			//echo $descriptionurl . '<br />';
			
			//AFFICHAGE DES NOMS DE FICHIERS
			if($col == 4)
			{
				echo '</tr><tr>';
				for($j=$i-$col;$j<$i;$j++)
				{
					$nametmp = $result->Item[$j]->Text;
					if (strlen($nametmp) > 39)
						$nametmp = substr($nametmp, 0, 39);
					echo '<td>' . wordwrap($nametmp, 20, '<br />', true) . '</td>';
				}
				echo '</tr><tr>';
				
				$col = 0;
			}
			
			$img = $url;
			
			//SUPPERSSION CARACTERE GENANT
			$descriptionurl = str_replace('\\', '', $descriptionurl);
			$url = str_replace('\\', '', $url);
			
			//AFFICHAGE IMAGE
			if($result->Item[$i])
				echo '<td name ="tdwiki" id="tdwiki"><a href="' . $media_wikimedia_iframe_src . '&amp;TB_iframe=true&amp;height=500&amp;width=640&amp;licence=' . $descriptionurl . '&amp;fichier='. $url. '&amp;name=' . $result->img[$i]['name'] . '"><img src="' . $img . '" class="wikimedia_image" ></a></td>';
		
		
			//AFFICHAGE DERNIERE LIGNE TABLEAU
			if(($i - $debut*20) == 19)
			{
				echo '</tr><tr>';
				for($j=$i-$col-1;$j<$i;$j++)
					if($result->Item[$j])
						echo '<td>' . wordwrap($result->Item[$j]->Text, 20, '<br />', true) . '</td>';
					else
						echo '<td></td>';
				echo '</tr>';
			}
			
			$col++;
		}
		echo '</table>';
	}
}
?>