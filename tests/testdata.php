<?php

function testdata($var)
{
	static $_data = array(
		'definition' => 'interaction.content contains "datasift"',
		'definition_hash' => '947b690ec9dca525fb8724645e088d79',

		'invalid_definition' => 'interactin.content contains "datasift"',

		'recording' => array(
			'id' => '47ce46821c942ff42f8e',
			'start_time' => 1313055762,
			'finish_time' => null,
			'name' => 'Inherit everything 123',
			'hash' => '9e2e0ba334ee76aa06ef42d5565dbb70',
		),

		'export' => array(
			'id' => '82',
			'recording_id' => '47ce46821c942ff42f8e',
			'name' => 'Unnamed export 47ce46821c942ff42f8e',
			'start' => 1313052000,
			'end' => 1312974000,
			'status' => 'running',
		),
	);

	return (isset($_data[$var]) ? $_data[$var] : null);
}
