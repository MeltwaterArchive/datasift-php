<?php
/**
 * DataSift client
 *
 * This software is the intellectual property of MediaSift Ltd., and is covered
 * by retained intellectual property rights, including copyright.
 *
 * @category  DataSift
 * @package   PHP-client
 * @author    Stuart Dallas <stuart@3ft9.com>
 * @copyright 2011 MediaSift Ltd.
 * @license   http://www.debian.org/misc/bsd.license BSD License (3 Clause)
 * @link      http://www.mediasift.com
 */

/**
 * This example gets the DPU associated with the stream given on the command
 * line or piped/typed into STDIN. It  presents it in a nice ASCII table.
 * Note that the CSDL must be enclosed in quotes if given on the command line.
 *
 * php dpu.php 'interaction.content contains "football"'
 *  or
 * cat football.csdl | php dpu.php
 *
 * NB: Most of the error handling (exception catching) has been removed for
 * the sake of simplicity. Nearly everything in this library may throw
 * exceptions, and production code should catch them. See the documentation
 * for full details.
 */
if (function_exists('date_default_timezone_set')) {
	date_default_timezone_set('UTC');
}

// Include the DataSift library
require dirname(__FILE__).'/../lib/datasift.php';

// Include the configuration - put your username and API key in this file
require dirname(__FILE__).'/../config.php';

// Throw away the script name
array_shift($_SERVER['argv']);

// If we have no further command line args
if (count($_SERVER['argv']) > 0) {
	$csdl = array_shift($_SERVER['argv']);
} else {
	// Read it from stdin
	$csdl = '';
	while (!feof(STDIN)) {
		$csdl .= fread(STDIN, 4096);
	}
}

$csdl = trim($csdl);
if (strlen($csdl) == 0) {
	die("CSDL is empty\n\n");
}

// Authenticate
echo "Creating user...\n";
$user = new DataSift_User(USERNAME, API_KEY);

// Create the definition
echo "Creating definition...\n";
$definition = new DataSift_Definition($user, $csdl);

// Get the DPU. This will compile the definition, so we catch potential
// errors from that.
echo "Getting DPU...\n";
try {
	$dpu = $definition->getDPUBreakdown();
} catch (DataSift_Exception_CompileFailed $e) {
	die("CSDL compilation failed: ".$e->getMessage()."\n\n");
}

// Format the DPU details for output in a table
$dputable = array();
$maxlength = array('target' => strlen('Target'), 'times used' => strlen('Times used'), 'complexity' => strlen('Complexity'));
foreach ($dpu['detail'] as $tgt => $c) {
	$maxlength['target'] = max($maxlength['target'], strlen($tgt));
	$maxlength['times used'] = max($maxlength['times used'], strlen(number_format($c['count'])));
	$maxlength['complexity'] = max($maxlength['complexity'], strlen(number_format($c['dpu'], 2)));

	$dputable[] = array(
		'target'     => $tgt,
		'times used' => number_format($c['count']),
		'complexity' => number_format($c['dpu'], 2),
	);

	foreach ($c['targets'] as $tgt => $d) {
		$maxlength['target']     = max($maxlength['target'], 2 + strlen($tgt));
		$maxlength['times used'] = max($maxlength['times used'], strlen(number_format($d['count'])));
		$maxlength['complexity'] = max($maxlength['complexity'], strlen(number_format($d['dpu'], 2)));

		$dputable[] = array(
			'target'     => '  '.$tgt,
			'times used' => number_format($d['count']),
			'complexity' => number_format($d['dpu'], 2),
		);
	}
}

$maxlength['complexity'] = max($maxlength['complexity'], strlen(number_format($dpu['dpu'], 2)));

echo "\n";
echo '/-'.str_repeat('-', $maxlength['target']).'---';
echo str_repeat('-', $maxlength['times used']).'---';
echo str_repeat('-', $maxlength['complexity'])."-\\\n";

echo '| '.str_pad('Target', $maxlength['target']).' | ';
echo str_pad('Times Used', $maxlength['times used']).' | ';
echo str_pad('Complexity', $maxlength['complexity'])." |\n";

echo '|-'.str_repeat('-', $maxlength['target']).'-+-';
echo str_repeat('-', $maxlength['times used']).'-+-';
echo str_repeat('-', $maxlength['complexity'])."-|\n";

foreach ($dputable as $row) {
	echo '| '.str_pad($row['target'], $maxlength['target']).' | ';
	echo str_pad($row['times used'], $maxlength['times used'], ' ', STR_PAD_LEFT).' | ';
	echo str_pad($row['complexity'], $maxlength['complexity'], ' ', STR_PAD_LEFT)." |\n";
}

echo '|-'.str_repeat('-', $maxlength['target']).'---';
echo str_repeat('-', $maxlength['times used']).'---';
echo str_repeat('-', $maxlength['complexity'])."-|\n";

echo '| '.str_repeat(' ', $maxlength['target'] + 3);
echo str_pad('Total', $maxlength['times used'], ' ', STR_PAD_LEFT).' = ';
echo str_pad(number_format($dpu['dpu'], 2), $maxlength['complexity'], ' ', STR_PAD_LEFT)." |\n";

echo '\\-'.str_repeat('-', $maxlength['target']).'---';
echo str_repeat('-', $maxlength['times used']).'---';
echo str_repeat('-', $maxlength['complexity'])."-/\n";

echo "\n";
