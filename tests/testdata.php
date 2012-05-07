<?php

function testdata($var)
{
	static $_data = array(
		'definition' => 'interaction.content contains "datasift"',
		'definition_hash' => '947b690ec9dca525fb8724645e088d79',

		'invalid_definition' => 'interactin.content contains "datasift"',

		'stream_id' => 10121,
		'stream_name' => 'DataSift API Test',
		'stream_description' => 'This stream is used by the official DataSift API tests.',

		'stream_versions' => array(
				1 => array(
						'message' => '',
						'definition' => 'interaction.content contains "datasift"',
						'definition_hash' => '947b690ec9dca525fb8724645e088d79',
						'created_at' => 1305817598,
					),
				2 => array(
						'message' => 'Added Klout score condition.',
						'definition' => 'interaction.content contains "datasift" and klout.score > 50',
						'definition_hash' => '2554e156165157ff7a55879615756f49',
						'created_at' => 1305927524,
					),
				3 => array(
						'message' => 'Removed the Klout score condition.',
						'definition' => 'interaction.content contains "datasift"',
						'definition_hash' => '947b690ec9dca525fb8724645e088d79',
						'created_at' => 1305927551,
					),
			),
		);

	return (isset($_data[$var]) ? $_data[$var] : null);
}
