<image id="indDisponibilityNodesChart" class="chartIndicator" src="<?php echo $chartPath ?>">
<table border="1" id="indDisponibilityNodesTable" class="resultsTable">
	<caption>Disponibilité des noeuds pour <?php echo $cluster." du ".$monthStart."-".$year.' au '.$monthEnd."-".$yearEnd ?></caption>
	<thead><tr>
		<th>Jour</th>
		<th>Disponibilité</th>
	</tr></thead>
	<tbody>
	<?php foreach($results as $row){ ?>
		<tr>
			<td><?php echo date($dateFormat, strtotime($row['step'])); ?></td>
			<td><?php echo number_format($row['dispo']*100,2,',',' '); ?>%</td>
		</tr>
	<?php } ?>
	</tbody>
</table>