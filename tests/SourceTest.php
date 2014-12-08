<?php
if (function_exists('date_default_timezone_set')) {
	date_default_timezone_set('UTC');
}

class SourceTest extends PHPUnit_Framework_TestCase
{
	protected $config = false;
	protected $user = false;
	protected $source_id = false;
	protected $source = false;

	protected function setUp()
	{
		require_once(dirname(__FILE__).'/../lib/datasift.php');
		require_once(dirname(__FILE__).'/../config.php');
		require_once(dirname(__FILE__).'/testdata.php');
		$this->user = new DataSift_User(USERNAME, API_KEY);
		$this->user->setApiClient('DataSift_MockApiClient');
		DataSift_MockApiClient::setResponse(false);
	}

	protected function setCreateResponse()
	{
		$response = array(
			'response_code' => 200,
			'data'          => array(
				'id'					=> testdata('source_id'),
				'name'					=> testdata('source_name'),
				'created_at'			=> testdata('source_created_at'),
				'status'				=> testdata('source_status'),
				'auth'					=> array(
					'identity_id'			=> testdata('source_auth', 'identity_id'),
					'expires_at'			=> testdata('source_auth', 'expires_at'),
					'parameters' 			=> testdata('source_auth', 'parameters'),
				),
				'resources'					=> testdata('source_resources'),
				'parameters' => array(
					'comments' 			=> testdata('source_parameters', 'comments'),
					'likes' 			=> testdata('source_parameters', 'likes'),
					'page_likes'		=> testdata('source_parameters', 'page_likes'),
					'posts_by_others' 	=> testdata('source_parameters', 'posts_by_others'),
				)

			),
			'rate_limit'           => 200,
			'rate_limit_remaining' => 150,
		);
		DataSift_MockApiClient::setResponse($response);
	}

	public function testCreateSource(){
		$this->markTestSkipped('Broken');

		$source = new DataSift_Source($this->user, array(
			'name' => 'My PHP managed source',
			'source_type' => 'facebook_page',
			'parameters' => $params,
			'auth' => $auth,
			'resources' => $resources,
		));
		
		$source->save();
		
	}

	
}
