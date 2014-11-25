<?php foreach($chartPath as $path) { ?>
<image class="chartIndicator" src="<?php echo $path ?>">
<?php } ?>
<table border="1" id="indOccupationCpuPartitionTable" class="resultsTable">
	<caption>Suivi Journalier du taux d'occupation des CPUs pour <?php echo $cluster ?> <br> Du <?php echo $month.'-'.$year ?> </caption>
	<thead>
		<tr>
			<th rowspan="2">Jour</th>
			<?php foreach($results[1] as $partition=>$taux){ ?>
			<th colspan="2"><?php echo $partition ?></th>
			<?php } ?>
		</tr>
		<tr>
			<?php foreach($results[1] as $partition=>$taux){ ?>
			<th>Occupation(%)</th>
			<th>Indisponibilité(%)</th>
			<?php } ?>
		</tr>
	</thead>
	<tbody>
	<?php foreach($results as $step=>$row){ ?>
		<tr>
			<td><?php echo $step; ?></td>
			<?php foreach($results[$step] as $taux){ ?>
			<td><?php echo number_format($taux['occupation']*100,2,',',' ') ?></td>
			<td><?php echo number_format($taux['indispo']*100,2,',',' ') ?></td>
			<?php } ?>
		</tr>
	<?php } ?>
	</tbody>
</table>