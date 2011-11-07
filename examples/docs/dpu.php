<?php

include 'stream.php';

// Get the DPU. This will compile the definition, so we catch potential
// errors from that.
$dpu = array();
try {
	$dpu = $definition->getDPUBreakdown();
}
catch (DataSift_Exception_CompileFailed $e) {
	die("CSDL compilation failed: " . $e->getMessage() . "\n\n");
}

// Format the DPU details for output in a table
$dputable = array();
$maxlength = array('target' => strlen('Target'), 'times used' => strlen('Times used'), 'complexity' => strlen('Complexity'));
foreach ($dpu['dpu'] as $tgt => $c) {
	$maxlength['target'] = max($maxlength['target'], strlen($tgt));
	$maxlength['times used'] = max($maxlength['times used'], strlen(number_format($c['count'])));
	$maxlength['complexity'] = max($maxlength['complexity'], strlen(number_format($c['dpu'])));

	$dputable[] = array(
		'target' => $tgt,
		'times used' => number_format($c['count']),
		'complexity' => number_format($c['dpu']),
	);

	foreach ($c['targets'] as $tgt => $d) {
		$maxlength['target'] = max($maxlength['target'], 2 + strlen($tgt));
		$maxlength['times used'] = max($maxlength['times used'], strlen(number_format($d['count'])));
		$maxlength['complexity'] = max($maxlength['complexity'], strlen(number_format($d['dpu'])));

		$dputable[] = array(
			'target' => '  ' . $tgt,
			'times used' => number_format($d['count']),
			'complexity' => number_format($d['dpu']),
		);
	}
}

$maxlength['complexity'] = max($maxlength['complexity'], strlen(number_format($dpu['total'])));

echo "\n";
echo '/-' . str_repeat('-', $maxlength['target']) . '---';
echo str_repeat('-', $maxlength['times used']) . '---';
echo str_repeat('-', $maxlength['complexity']) . "-\\\n";

echo '| ' . str_pad('Target', $maxlength['target']) . ' | ';
echo str_pad('Times Used', $maxlength['times used']) . ' | ';
echo str_pad('Complexity', $maxlength['complexity']) . " |\n";

echo '|-' . str_repeat('-', $maxlength['target']) . '-+-';
echo str_repeat('-', $maxlength['times used']) . '-+-';
echo str_repeat('-', $maxlength['complexity']) . "-|\n";

foreach ($dputable as $row) {
	echo '| ' . str_pad($row['target'], $maxlength['target']) . ' | ';
	echo str_pad($row['times used'], $maxlength['times used'], ' ', STR_PAD_LEFT) . ' | ';
	echo str_pad($row['complexity'], $maxlength['complexity'], ' ', STR_PAD_LEFT) . " |\n";
}

echo '|-' . str_repeat('-', $maxlength['target']) . '---';
echo str_repeat('-', $maxlength['times used']) . '---';
echo str_repeat('-', $maxlength['complexity']) . "-|\n";

echo '| ' . str_repeat(' ', $maxlength['target'] + 3);
echo str_pad('Total', $maxlength['times used'], ' ', STR_PAD_LEFT) . ' = ';
echo str_pad($dpu['total'], $maxlength['complexity'], ' ', STR_PAD_LEFT) . " |\n";

echo '\\-' . str_repeat('-', $maxlength['target']) . '---';
echo str_repeat('-', $maxlength['times used']) . '---';
echo str_repeat('-', $maxlength['complexity']) . "-/\n";

echo "\n";

if ($dpu['total'] > 1000) {
	$tiernum = 3;
	$tierdesc = 'high complexity';
}
elseif ($dpu['total'] > 100) {
	$tiernum = 2;
	$tierdesc = 'medium complexity';
}
else {
	$tiernum = 1;
	$tierdesc = 'simple complexity';
}

echo 'A total DPU of ' . number_format($dpu['total']) . ' puts this stream in tier ' . $tiernum . ', ' . $tierdesc . "\n\n";
