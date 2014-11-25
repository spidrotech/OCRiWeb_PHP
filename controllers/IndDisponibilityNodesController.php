<?php
	require_once("OcriControllerChart.php");
	class IndDisponibilityNodesController extends OcriControllerChart{
	
		protected $rootName;
		
		public function getResult($parameters){
			$this->setData($parameters);
			if(count($parameters['results'])>0){
				$parameters['chartPath']=$this->createChart($parameters);
				$display=$this->getView('IndDisponibilityNodesTable', $parameters);
			}else $display = "Veuillez vérifier les paramètres, la requête ne retourne aucun résultat";
			
			return $display;
		}
		
		private function setData(&$parameters){
			$parameters['results'] = $this->adaptator->disponibilityNodes($parameters['cluster'],$parameters['monthStart'],$parameters['monthEnd'],$parameters['year'],$parameters['period']);
			$parameters['yearEnd']=$this->adaptator->endYear($parameters['monthStart'],$parameters['monthEnd'],$parameters['year']);
			$parameters['dateFormat']=($parameters['period']=='month')?"m-y":"d-m";
			$this->rootName.= 'indDisponibilityNodes_'.$parameters['period'].'_'.$parameters['cluster']."_".$parameters['year'].'-'.$parameters['monthStart'].'_'.$parameters['yearEnd'].'-'.$parameters['monthEnd'];
		}
		
		private function createChart($parameters){
			/* Create and populate the pData object */
			$myData = new pData();
			foreach($parameters['results'] as $row){
				$steps[]					=date($parameters['dateFormat'], strtotime($row['step']));
				$occupation[]			=$row['dispo']*100;
			}
			
			$labelSkip=intval(count($steps)/60);
			
			//axe Y;
			$myData->addPoints($steps,"Jours");
			$myData->setSerieDescription("Jours","Jours");
			$myData->setAbscissa("Jours");
			
			//axe X
			$myData->setAxisName(0,"% Disponibilité");
			$AxisBoundaries = array(0=>array("Min"=>0,"Max"=>100));
			$ScaleSettings  = array("Mode"=>SCALE_MODE_START0,"LabelSkip"=>$labelSkip,"LabelRotation"=>90,"ManualScale"=>$AxisBoundaries,"GridR"=>180,"GridG"=>180,"GridB"=>180);
			//$myData->setAxisDisplay(0,AXIS_FORMAT_CURRENCY);
			
			//Disponibiity
			$myData->addPoints($occupation,"Occupation");
			$myData->setSerieDescription("Occupation","Disponibilité des noeuds");
			$myData->setPalette("Occupation",array("R"=>55,"G"=>91,"B"=>127));
			$myData->setSerieWeight("Occupation",1);
			
			$chartPath=$this->rootName.'.png';
			
			/* Create the pChart object */
			$myPicture = new pImage(1100,600,$myData);

			/* Write the picture title */ 
			$myPicture->setFontProperties(array("FontName"=>"librairies/pChart/fonts/Forgotte.ttf","FontSize"=>12));
			$myPicture->drawText(60,35,"
				Disponibilité des noeuds pour ".$parameters['cluster']." du ".$parameters['monthStart']."-".$parameters['year'].' au '.$parameters['monthEnd']."-".$parameters['yearEnd'] ,
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
			$titles=array("Jour","Disponibilité");
			//columns title
			fputcsv($handle,$titles,';');
			//data
			foreach($parameters['results'] as $row){
				$row['step']=date($this->csvDateFormat, strtotime($row['step']));
				$row['dispo']=number_format($row['dispo'],4,',',' ');
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