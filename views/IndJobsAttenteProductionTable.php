<?php foreach($chartPath as $path) { ?>
	<image class="chartIndicator" src="<?php echo $path ?>">
<?php } ?>
<table border="1" id="indJobsAttenteProductionTable" class="resultsTable">
	<caption>Suivi horaire des jobs pour <?php echo $cluster ?> <br> Du <?php echo $month.'-'.$year ?> </caption>
	<thead>
		<tr>
			<th rowspan="2">Heure</th>
			<th colspan="2">Production</th>
			<th colspan="2">Attente</th>
		</tr>
		<tr>
			<th># Jobs</th>
			<th># CPU</th>
			<th># Jobs</th>
			<th># CPU</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach($results as $row){ ?>
		<tr>
			<td><?php echo $row['step']; ?></td>
			<td><?php echo $row['jobs_prod'] ?></td>
			<td><?php echo $row['nb_cpus_prod'] ?></td>
			<td><?php echo $row['jobs_wait'] ?></td>
			<td><?php echo $row['nb_cpus_wait'] ?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>