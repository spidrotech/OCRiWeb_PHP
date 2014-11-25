<html>
	<head>
		<meta charset='utf-8'>
		<link rel="stylesheet" type="text/css" href="styles/style.css">
	</head>
	<body>

<table border="1" id="indDisponibiliteTable" class="resultsTable">
	<caption>Détails des jobs</caption>
	<thead><tr>
		<th>id</th>
		<th>id_job</th>
		<th>profile</th>
		<th>login</th>
		<th>uid</th>
		<th>gid</th>
		<th>soumission</th>
		<th>lancement</th>
		<th>fin</th>
		<th>#noeuds</th>
		<th>#cpu</th>
		<th>queue</th>
		<th>cluster</th>
		<th>état</th>
		<th>sched_id</th>
	</tr></thead>
	<tbody>
	<?php foreach($results as $row){ ?>
		<tr <?php echo ($row['state']!='COMPLETE')?'class="detailsError"':'' ?>>
			<td><?php echo $row['id']?></td>
			<td><?php echo $row['id_job']?></td>
			<td><?php echo $row['name']?></td>
			<td><?php echo $row['login']?></td>
			<td><?php echo $row['uid']?></td>
			<td><?php echo $row['gid']?></td>
			<td><?php echo $row['submission_datetime']?></td>
			<td><?php echo $row['running_datetime']?></td>
			<td><?php echo $row['end_datetime']?></td>
			<td><?php echo $row['nb_nodes']?></td>
			<td><?php echo $row['nb_cpus']?></td>
			<td><?php echo $row['running_queue']?></td>
			<td><?php echo $row['clustername']?></td>
			<td><?php echo $row['state']?></td>
			<td><?php echo $row['sched_id']?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>
</body>
</html>