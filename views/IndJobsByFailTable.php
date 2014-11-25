<image id="indJobsByFail" class="chartIndicator" src="<?php echo $chartPath ?>">
<table border="1" id="indJobsByFail" class="resultsTable">
	<caption>Répartition des jobs en échec par cause pour <?php echo $cluster." du ".$monthStart."-".$year.' au '.$monthEnd."-".$yearEnd ?></caption>
	<thead>
		<tr>
			<th>Mois</th>
			<?php foreach(array_keys($results['listState']) as $state){?>
			<th><?php echo $state ?></th>
			<?php } ?>
		</tr>		
	</thead>
	<tbody>
	<?php foreach($results['listMonth'] as $month=>$total){ ?>
		<tr>
			<td><?php echo date("m-y", strtotime($month)); ?></td>
			<?php foreach($results['listState'] as $state=>$resultsByMonth){?>
			<td><?php echo number_format($resultsByMonth[$month],0,',',' '); ?></td>
			<?php } ?>
		</tr>
	<?php } ?>
	</tbody>
</table>