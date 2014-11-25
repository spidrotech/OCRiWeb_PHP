<?php
	require_once("librairies/pChart/class/pData.class.php");
	require_once("librairies/pChart/class/pDraw.class.php");
	require_once("librairies/pChart/class/pImage.class.php");
	require_once("librairies/pChart/class/pCache.class.php");
	abstract class OcriControllerChart{
		protected $adaptator;
		protected $rootName;
		protected $fontSizeLegend;
		protected $fontSizeScale;
		protected $csvDateFormat;
		public function OcriControllerChart($adaptator,$config){
			$this->adaptator=$adaptator;
			$this->rootName='results/';
			$this->fontSizeLegend=$config['font_size_legend'];
			$this->fontSizeScale=$config['font_size_scale'];
			$this->csvDateFormat=$config['date_format'];
		}
		public function getForm($output) {
			$date=getdate();
			$output['currentYear']=$date["year"] ;
			$output['clusterList']=$this->adaptator->clusterList();
			$formName	= str_replace("Controller","Form",get_class($this));
			
			return $this->getView($formName, $output);
		}

		protected abstract function getResult($parameters);
		protected abstract function exportToZip($parameters);
		
		protected function getView($viewName,$output){
			//creation des variables de manière dynamique pour faciliter leur usage
			foreach($output as $variable => $value) $$variable = $value;
			$path = "views/".$viewName.'.php';
			if (is_file($path)) {
				ob_start();
				include $path;
			}else{
				throw new Exception("No corresponding view: $viewName, path: $path");
			}
			return ob_get_clean();
		}
		
		protected function createZip($filename, $filesToZip){
			$zip = new ZipArchive();
			if ($zip->open($filename, ZipArchive::OVERWRITE)!==TRUE) {
				exit("Impossible d'ouvrir le fichier <$filename>\n");
			}
			
			foreach($filesToZip as $file){
				$zip->addFile($file);
			}

			$zip->close();
		}
		
		protected function headerForZip($zipPath){
			$file_name = basename($zipPath);
			
			header( "Location: $zipPath" );
			/*ini_set('zlib.output_compression', 0);
			$date = gmdate(DATE_RFC1123);

			header('Pragma: public');
			header('Cache-Control: must-revalidate, pre-check=0, post-check=0, max-age=0');

			header('Content-Tranfer-Encoding: none');
			header('Content-Length: '.filesize($zipPath));
			header('Content-MD5: '.base64_encode(md5_file($zipPath)));
			header('Content-Type: application/octetstream; name="'.$file_name.'"');
			header('Content-Disposition: attachment; filename="'.$file_name.'"');

			header('Date: '.$date);
			header('Expires: '.gmdate(DATE_RFC1123, time()+1));
			header('Last-Modified: '.gmdate(DATE_RFC1123, filemtime($zipPath)));

			readfile($zipPath);*/
		}
	}
?>