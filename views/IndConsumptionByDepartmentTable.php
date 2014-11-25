<image id="indConsumptionByDepartmentChart" class="chartIndicator" src="<?php echo $chartPath ?>">
<table border="1" id="indConsumptionByDepartmentTable" class="resultsTable">
	<caption>Répartition mensuelle des heures consommées par département pour <?php echo $cluster." du ".$monthStart."-".$year.' au '.$monthEnd."-".$yearEnd ?></caption>
	<thead>
		<tr>
			<th rowspan="2">Mois</th>
			<?php foreach(array_keys($results['listDepartment']) as $department){?>
			<th colspan="2"><?php echo $department ?></th>
			<?php } ?>
		</tr>
		<tr>
			<?php foreach(array_keys($results['listDepartment']) as $department){?>
			<th>Heures CPU</th>
			<th>Part (%)</th>
			<?php } ?>
		</tr>		
	</thead>
	<tbody>
	<?php foreach($results['listMonth'] as $month=>$total){ ?>
		<tr>
			<td><?php echo date("m-y", strtotime($month)); ?></td>
			<?php foreach($results['listDepartment'] as $department=>$resultsByMonth){?>
			<td><?php echo number_format($resultsByMonth[$month]['hours'],2,',',' '); ?></td>
			<td><?php echo number_format($resultsByMonth[$month]['%'],2,',',' '); ?></td>
			<?php } ?>
		</tr>
	<?php } ?>
	</tbody>
</table>