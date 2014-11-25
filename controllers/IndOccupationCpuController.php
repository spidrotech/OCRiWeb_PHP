<?php
	require_once("OcriControllerChart.php");
	class IndOccupationCpuController extends OcriControllerChart{
	
		protected $rootName;
		public function getResult($parameters){
			$this->setData($parameters);
			if(count($parameters['results'])>0){
				$parameters['chartPath']=$this->createChart($parameters);
				$display=$this->getView('IndOccupationCpuTable', $parameters);
			}else $display = "Veuillez vérifier les paramètres, la requête ne retourne aucun résultat";
			
			return $display;
		}
		
		private function setData(&$parameters){
			$parameters['mode']=isset($parameters['mode'])?true:false;
			$parameters['results'] = $this->adaptator->occupation($parameters['cluster'],$parameters['monthStart'],$parameters['monthEnd'],$parameters['year'],$parameters['period'],$parameters['mode']);
			$parameters['yearEnd']=$this->adaptator->endYear($parameters['monthStart'],$parameters['monthEnd'],$parameters['year']);
			$this->rootName.= 'indOccupationCpuTable_'.$parameters['period'].'_'.$parameters['cluster']."_".$parameters['year'].'-'.$parameters['monthStart'].'_'.$parameters['yearEnd'].'-'.$parameters['monthEnd'];
		}
		
		private function createChart($parameters){
			/* Create and populate the pData object */
			$myData = new pData();
			$mode = $parameters['mode'];
			foreach($parameters['results'] as $row){
				$steps[]					=date("d-m", strtotime($row['step']));
				$jobs[]					=$row['count'];
				$occupation[]			=$row['occupation']*100;
				if($mode) $indispo[] 	=($row['occupation']+$row['indispo'])*100;
			}
			
			$labelSkip=intval(count($steps)/60);
			
			//axe Y;
			$myData->addPoints($steps,"Jours");
			$myData->setSerieDescription("Jours","Jours");
			$myData->setAbscissa("Jours");
			
			//axe X
			$myData->setAxisName(0,"% Occupation");
			$AxisBoundaries = array(0=>array("Min"=>0,"Max"=>100));
			$ScaleSettings  = array("Mode"=>SCALE_MODE_START0,"LabelSkip"=>$labelSkip,"LabelRotation"=>90,"ManualScale"=>$AxisBoundaries,"GridR"=>180,"GridG"=>180,"GridB"=>180);
			//$myData->setAxisDisplay(0,AXIS_FORMAT_CURRENCY);
			
			if($mode){
				//occupation+indispo
				$myData->addPoints($indispo,"Indispo");
				$myData->setSerieDescription("Indispo","Occupation avec l'indisponibilité des noeuds de calculs (Réelle)");
				$myData->setPalette("Indispo",array("R"=>205,"G"=>0,"B"=>0));
				$myData->setSerieWeight("Indispo",1);
				$myData->setSerieTicks("Indispo",3);
			}
			//Occupation
			$myData->addPoints($occupation,"Occupation");
			$myData->setSerieDescription("Occupation","Occupation des jobs (Théorique)");
			$myData->setPalette("Occupation",array("R"=>55,"G"=>91,"B"=>127));
			$myData->setSerieWeight("Occupation",1);
			
			$chartPath=$this->rootName.'.png';
			
			/* Create the pChart object */
			$myPicture = new pImage(1100,600,$myData);

			/* Write the picture title */ 
			$myPicture->setFontProperties(array("FontName"=>"librairies/pChart/fonts/Forgotte.ttf","FontSize"=>12));
			$myPicture->drawText(60,35,"
				Taux d'occupation des CPUs pour ".$parameters['cluster']." du ".$parameters['monthStart']."-".$parameters['year'].' au '.$parameters['monthEnd']."-".$parameters['yearEnd'] ,
				array("FontSize"=>20,"Align"=>TEXT_ALIGN_BOTTOMLEFT)
			);

			/* Do some cosmetic and draw the chart */
			$myPicture->setGraphArea(60,40,1070,520);
			//$myPicture->drawFilledRectangle(60,40,770,570,array("R"=>255,"G"=>255,"B"=>255,"Surrounding"=>-200,"Alpha"=>10));
			$myPicture->drawScale($ScaleSettings);
			$myPicture->setShadow(TRUE,array("X"=>2,"Y"=>2,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));
			$myPicture->setFontProperties(array("FontName"=>"librairies/pChart/fonts/Forgotte.ttf","FontSize"=>14));
			$myPicture->drawLineChart();
			$myPicture->setShadow(FALSE);
			/* Write the chart legend */ 
			$myPicture->drawLegend(300,575,array("Style"=>LEGEND_ROUND,"Mode"=>LEGEND_HORIZONTAL));
			$myPicture->Render($chartPath);
			
			return $chartPath;
		}
		
		private function createCsv($parameters){
			$handle = fopen($this->rootName.'.csv', "w");
			$titles=array("Jour","#Jobs","Occupation");
			if($mode) $titles[]="Indisponibilité";
			//columns title
			fputcsv($handle,$titles,';');
			//data
			foreach($parameters['results'] as $row){
				$row['step']=date($this->csvDateFormat, strtotime($row['step']));
				$row['occupation']=number_format($row['occupation'],4,',',' ');
				if($parameters['mode']) $row['indispo']=number_format($row['indispo'],4,',',' ');
				fputcsv($handle,$row,';');
			}
			fclose($handle);
		}
		
		public function exportToZip($parameters){
			$this->setData($parameters);
			$this->createCsv($parameters);
			$this->createChart($parameters);
			$zipPath = $this->rootName.'.zip'; // chemin système (local) vers le fichier
			//the function take for principle that the chart and the result are already displayed
			$this->createZip($zipPath,array($this->rootName.'.csv',$this->rootName.'.png'));
			$this->headerForZip($zipPath);
			exit; // nécessaire pour être certain de ne pas envoyer de fichier corrompu
		}
	}
?>