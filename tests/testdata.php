<?php

function testdata($var1, $var2 = false)
{
	static $_data = array(
		'definition' => 'interaction.content contains "datasift"',
		'definition_hash' => '2f3304a18d4ed5e5d20b8e62c2403551',

		'invalid_definition' => 'interactin.content contains "datasift"',

		'historic_playback_id'	=> 'b665761917bbcb7afd3105761917bbcb',
		'historic_name'			=> 'my historic',
		'historic_start_date'	=> 1335869526,
		'historic_end_date'		=> 1335870126,
		'historic_sources'		=> array('twitter', 'facebook'),
		'historic_sample'		=> 10,
		'historic_id'			=> '4ef7c852a96d6352764f',
		'historic_dpus'			=> 2334.6916666667,
		'historic_status'		=> 'queued',
		'historic_availability' => array(
			'start' => 12345678,
			'end' => 124356376,
			'sources' => array(
				'twitter' => array(
					'status' => '99%',
					'versions' => array(3),
					'augmentations' => array(
						'klout' => '50%',
						'links' => '100%'
					),
				),
				'facebook' => array(
					'status' => '99%',
					'versions' => array(2,3),
					'augmentations' => array(
						'links' => '95%'
					),
				),
			),
		),

		'push_id'					=> 'b665761917bbcb7afd3102b4a645b41e',
		'push_created_at'			=> 1335869526,
		'push_hash_stream_type'		=> 'stream',
		'push_hash_historic_type'	=> 'historic',
		'push_hash'					=> '947b690ec9dca525fb8724645e088d79',
		'push_hash_type'			=> 'stream',
		'push_name'					=> 'mypush',
		'push_status'				=> 'active',
		'push_output_type'			=> 'http',
		'push_output_params'		=> array(
			'delivery_frequency'	=> 10,
			'url'					=> 'http://www.example.com/push_endpoint',
			'auth.type'				=> 'basic',
			'auth.username'			=> 'frood',
			'auth.password'			=> 'towel42',
		),
		'push_last_request'			=> 1343121477,
		'push_last_success'			=> 1343121477,
	);

	if ($var2 !== false) {
		if (isset($_data[$var1][$var2])) {
			return $_data[$var1][$var2];
		} else {
			throw new Exception('Undefined testdata: '.$var1.' => '.$var2);
		}
	} else {
		if (isset($_data[$var1])) {
			return $_data[$var1];
		} else {
			throw new Exception('Undefined testdata: '.$var1);
		}
	}
}
