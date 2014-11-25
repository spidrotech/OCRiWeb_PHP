<image id="indOccupationCpuChart" class="chartIndicator" src="<?php echo $chartPath ?>">
<table border="1" id="indOccupationCpuTable" class="resultsTable">
	<caption>Taux d'occupation des CPUs pour <?php echo $cluster." du ".$monthStart."-".$year.' au '.$monthEnd."-".$yearEnd ?></caption>
	<thead><tr>
		<th>Jour</th>
		<th>#Jobs</th>
		<th>Occupation</th>
		<?php if($mode){ ?>
		<th>Indisponibilité</th>
		<?php } ?>
	</tr></thead>
	<tbody>
	<?php foreach($results as $row){ ?>
		<tr>
			<td><?php echo date("d-m", strtotime($row['step'])); ?></td>
			<td><?php echo $row['count']; ?></td>
			<td><?php echo number_format($row['occupation']*100,2,',',' '); ?>%</td>
			<?php if($mode){ ?>
			<td><?php echo number_format($row['indispo']*100,2,',',' '); ?>%</td>
			<?php } ?>
		</tr>
	<?php } ?>
	</tbody>
</table>