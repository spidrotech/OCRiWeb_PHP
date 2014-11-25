<!DOCTYPE html>
<html>
	<head>
		<meta charset='utf-8'>
		<link rel="stylesheet" type="text/css" href="styles/style.css">
		<!--[if lt IE 9]>
			<script>
				document.createElement('header');
				document.createElement('nav');
				document.createElement('section');
				document.createElement('article');
				document.createElement('aside');
				document.createElement('footer');
				document.createElement('hgroup');
			</script>
		<![endif]-->
	</head>
	<body>
		<menu class="dropdown dropdown-horizontal">
			<li class="dir"><a href="#">Taux d'occupation</a><menu>
				<li><a href="index.php?ind=OccupationCpu&action=form">Suivi de l'occupation CPU</a></li>
				<li><a href="index.php?ind=TopNodes&action=form">Noeuds les plus utilisés</a></li>
				<!--li><a href="index.php?ind=OccupationCpuPartition&action=form">Suivi Journalier de l'occupation CPU par partition</a></li-->
				<li><a href="index.php?ind=OccupationFileSystem&action=form">Taux d'occupation des systèmes de fichier</a></li>
			</menu></li>
			<li class="dir"><a href="#">Suivi des jobs</a><menu>
				<li><a href="index.php?ind=JobsAttenteProduction&action=form">Suivi horaire des jobs en attente et en production</a></li>
				<li><a href="index.php?ind=JobsByFail&action=form">Répartition mensuelle des jobs en échec</a></li>
			</menu></li>
			<li class="dir"><a href="#">Consommation heures CPU</a><menu>
				<li><a href="index.php?ind=ConsumptionByDepartment&action=form">Répartition mensuelle des heures consommées par département</a></li>
				<li><a href="index.php?ind=TopConso&action=form">Plus grands consommateurs</a></li>
				<li><a href="index.php?ind=ConsumptionByQueue&action=form">Nombre d'heures consommées mensuellement par queue</a></li>
			</menu></li>
			<li class="dir"><a href="#">Disponiblité</a><menu>
				<li><a href="index.php?ind=Disponibilite&action=form">Taux de disponibilité de bout en bout</a></li>
				<li><a href="index.php?ind=DisponibilityNodes&action=form">Taux de disponibilité des noeuds de calculs</a></li>
			</menu></li>
			<li class="dir"><a href="#">Temps d'attente</a><menu>
				<li><a href="index.php?ind=WaitingByQueue&action=form">Nombre d'heures d'attente mensuel par queue</a></li>
				<li><a href="index.php?ind=TopJobsByCpu&action=form">Temps d’attente par nombre de CPU utilisés</a></li>
			</menu></li>
		</menu>
		<?php if(isset($form)){ ?> 
		<section id="formSection"><?php echo $form ?></section>
		<?php } ?>
		<?php if(isset($result)){ ?> 
		<section id="resultSection"><?php echo $result ?></section>
		<?php } ?>
		<footer><menu>
				<li><a href="DescriptionIndicateursOCRI.pdf" target="_blank">Description des indicateurs (PDF)<a></li>
				<li><a href="mailto:<?php echo $config['admin_email'] ?>?subject=OCRI">Contacter le support</a></li>
		</menu></footer>
	</body>
</html>