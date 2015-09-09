<?php
if (function_exists('date_default_timezone_set')) {
	date_default_timezone_set('UTC');
}

class PylonTest extends PHPUnit_Framework_TestCase
{
	protected $config = false;
	protected $user = false;
	protected $source_id = false;
	protected $source = false;

	protected function setUp()
	{
		require_once(dirname(__FILE__).'/../lib/datasift.php');
		require_once(dirname(__FILE__).'/../config.php');
		$this->user = new DataSift_User(USERNAME, API_KEY);
		$this->user->setApiClient('DataSift_MockApiClient');
		DataSift_MockApiClient::setResponse(false);
	}

	public function testGet(){
		$response = array(
			'response_code' => 200,
			'data'          => array(
				'volume'						=> '12300',
				'start'							=> 1436085514,
    			'end'							=> 1436089932,
    			'status'						=> 'stopped',
    			'name'			 				=> 'birthday sample',
    			'reached_capacity'				=> false,
    			'identity_id'					=> '58d783dd98dd6b8bc7a39d73928fa7cf',
    			'hash'			 				=> '37fdfa811a6fb20785eecb9de9dd2d3e',
    			'remaining_index_capacity'		=> 1000000,
    			'remaining_account_capacity'	=> 927200
			),
			'rate_limit'           => 200,
			'rate_limit_remaining' => 150,
		);

		DataSift_MockApiClient::setResponse($response);

		$get = DataSift_Pylon::get($this->user, '37fdfa811a6fb20785eecb9de9dd2d3e');

		$this->assertEquals($get['hash'], '37fdfa811a6fb20785eecb9de9dd2d3e', 'Hash did not match');
		$this->assertEquals($get['name'], 'birthday sample', 'Name did not match');
		
	}

	public function testGetAll(){

		$response = array(
			'response_code'	=> 200,
			'data'			=> array(
	    		'count' => 2,
	    		'page'  => 1,
	    		'pages' => 1,
	    		'per_page'  => 25,
	    		'subscriptions' => array(
	        		array(
	            		'volume'						=> 12300,
	            		'start'							=> 1436085514,
	            		'end'							=> 1436089932,
	            		'status'						=> 'stopped',
	            		'name'							=> 'example1',
	            		'reached_capacity'  			=> false,
	            		'identity_id'   				=> '58d783dd98dd6b8bc7a39d73928fa7cf',
	            		'hash'							=> '37fdfa811a6fb20785eecb9de9dd2d3e',
	            		'remaining_index_capacity'  	=> 998400,
	            		'remaining_account_capacity'	=> 927200
	        		),
	        		array(
	            		'volume'						=> 14700,
	            		'start' 						=> 1436087600,
	            		'end'							=> 1436089999,
	            		'status'					    => 'stopped',
	            		'name'							=> 'example2',
	            		'reached_capacity'				=> false,
	            		'identity_id'					=> '58d783dd98dd6b8bc7a39d73928fa7cf',
	            		'hash'							=> '9jrh3nq811a6fb20785eecb9de9dd2d3f',
	            		'remaining_index_capacity'		=> 986500,
	            		'remaining_account_capacity'	=> 927200
	        		)
	    		)
			)
		);

		DataSift_MockApiClient::setResponse($response);

		$get = DataSift_Pylon::get($this->user);

		$this->assertCount(2, $get['subscriptions'], 'Expecting 2 subscription arrays');
		$this->assertEquals($get['subscriptions'][0]['hash'], '37fdfa811a6fb20785eecb9de9dd2d3e', 'Hash did not match');
		$this->assertEquals($get['subscriptions'][1]['hash'], '9jrh3nq811a6fb20785eecb9de9dd2d3f', 'Hash did not match');
		$this->assertEquals($get['subscriptions'][0]['name'], 'example1', 'Name did not match');
		
	}

	public function testCanValidate(){
		$response = array(
			'response_code' => 200,
			'data'          => array(
				'created_at'					=> 1424280706,
				'operator_grouping'				=> array(
					'return'	=> array(
						'keywords'	=>	0,
						'complex'	=>	0,
						'medium'	=>	5
					),
					'tag'		=> array(
						'keywords'	=>	0,
						'complex'	=>	0,
						'medium'	=>	0
					)
				)
			),
			'rate_limit'           => 200,
			'rate_limit_remaining' => 150,
		);

		DataSift_MockApiClient::setResponse($response);

		$csdl = '(fb.content any "coffee" OR fb.hashtags in "tea") AND fb.language in "en"';

		$validate = DataSift_Pylon::validate($this->user, $csdl);

		$this->assertEquals($validate['created_at'], 1424280706, 'Created at did not match');
		$this->assertEquals($validate['operator_grouping']['return']['medium'], 5, 'Name did not match');
		
	}

	public function testFailValidate(){
		$csdl = '(fb.contwent any "coffee" OR fb.hashtags in "tea") AND fb.language in "en"';

		$response = array(
			'response_code' => 400,
			'data'          => array(
				'error'					=> 'The target fb.contwent does not exist',
			),
			'rate_limit'           => 200,
			'rate_limit_remaining' => 150,
		);

		DataSift_MockApiClient::setResponse($response);

		$this->setExpectedException('DataSift_Exception_InvalidData');

		$csdl = '(fb.contwent any "coffee" OR fb.hashtags in "tea") AND fb.language in "en"';

		$validate = DataSift_Pylon::validate($this->user, $csdl);
		
	}

	public function testCanCompile(){
		$response = array(
			'response_code' => 200,
			'data'          => array(
				'created_at'	=> 1424280706,
				'hash'			=> '1a4268c9b924d2c48ed1946d6a7e6272'
			),
			'rate_limit'           => 200,
			'rate_limit_remaining' => 150,
		);

		DataSift_MockApiClient::setResponse($response);

		$csdl = '(fb.content any "coffee" OR fb.hashtags in "tea") AND fb.language in "en"';

		$validate = DataSift_Pylon::validate($this->user, $csdl);

		$this->assertEquals($validate['created_at'], 1424280706, 'Created at did not match');
		$this->assertEquals($validate['hash'], '1a4268c9b924d2c48ed1946d6a7e6272', 'Name did not match');
		
	}

	public function testFailCompile(){
		$csdl = '(fb.contwent any "coffee" OR fb.hashtags in "tea") AND fb.language in "en"';

		$response = array(
			'response_code' => 400,
			'data'          => array(
				'error'					=> 'The target fb.contwent does not exist',
			),
			'rate_limit'           => 200,
			'rate_limit_remaining' => 150,
		);

		DataSift_MockApiClient::setResponse($response);

		$this->setExpectedException('DataSift_Exception_InvalidData');

		$csdl = '(fb.contwent any "coffee" OR fb.hashtags in "tea") AND fb.language in "en"';

		$pylon = new DataSift_Pylon($this->user);

		$pylon->compile($csdl); 
		
	}

	public function testStart(){

		$response = array(
			'response_code'			=> 204
		);

		DataSift_MockApiClient::setResponse($response);

		$pylon = new DataSift_Pylon($this->user);

		$hash = '1a4268c9b924d2c48ed1946d6a7e6272';

		$pylon->start($hash, 'My recording name');
		
	}

	public function testNoHashStart(){

		$pylon = new DataSift_Pylon($this->user);

		$this->setExpectedException('DataSift_Exception_InvalidData');

		$hash = '';

		$pylon->start($hash, 'My recording name');
		
	}

	public function testStop(){

		$response = array(
			'response_code'			=> 204
		);

		DataSift_MockApiClient::setResponse($response);

		$pylon = new DataSift_Pylon($this->user);

		$hash = '1a4268c9b924d2c48ed1946d6a7e6272';

		$pylon->stop($hash);
		
	}

	public function testNoHashStop(){

		$pylon = new DataSift_Pylon($this->user);

		$this->setExpectedException('DataSift_Exception_InvalidData');

		$hash = '';

		$pylon->stop($hash);
		
	}

	public function testTimeSeries(){

		$response = array(
			'response_code' => 200,
			'data'          => array(
				'analysis'		=> array(
					'analysis_type' => 'timeSeries',
				    'parameters' 	=> array(
				        'interval' 		=> 'hour',
				        'span' 			=> 4
				    ),
				    'redacted'	=> false,
					'results'	=> array(
						array('interactions' => 2200, 	'unique_authors' => 2000, 	'key' => 1435651200),
						array('interactions' => 1500, 	'unique_authors' => 1400, 	'key' => 1435665600),
						array('interactions' => 4400, 	'unique_authors' => 4400, 	'key' => 1435680000),
						array('interactions' => 52200, 	'unique_authors' => 52000, 	'key' => 1435694400),
						array('interactions' => 18800, 	'unique_authors' => 18800, 	'key' => 1435708800),
						array('interactions' => 2200, 	'unique_authors' => 2000, 	'key' => 1435723200),
						array('interactions' => 2100, 	'unique_authors' => 2000, 	'key' => 1435737600)
					)
				),
				'interactions'		=>	185200,
				'unique_authors'	=>	152700
			),
			'rate_limit'           => 200,
			'rate_limit_remaining' => 150,
		);

		DataSift_MockApiClient::setResponse($response);


		$pylon = new DataSift_Pylon($this->user);

		$parameters = array(
		    'analysis_type' => 'timeSeries',
		    'parameters' => array(
		        'interval' 	=> 'hour',
		        'span' 		=> 4
		    )
		);

		$analyze = $pylon->analyze($parameters);

		$this->assertEquals($analyze['interactions'], 185200, 'Interaction count did not match');
		$this->assertEquals($analyze['unique_authors'], 152700, 'Unique authors did not match');
		$this->assertEquals($analyze['analysis']['analysis_type'], 'timeSeries', 'Analysis type did not match');
		$this->assertArrayHasKey('interactions', $analyze['analysis']['results'][0], 'interactions not found in results');
		$this->assertArrayHasKey('unique_authors', $analyze['analysis']['results'][0], 'interactions not found in results');
		$this->assertArrayHasKey('interactions', $analyze['analysis']['results'][1], 'interactions not found in results');
		$this->assertArrayHasKey('unique_authors', $analyze['analysis']['results'][1], 'interactions not found in results');

	}

	public function testFreqDist(){

		$response = array(
			'response_code' => 200,
			'data'          => array(
				'analysis'		=> array(
					'analysis_type' => 'freqDist',
				    'parameters' 	=> array(
				        'interval' 		=> 'fb.author.gender',
				        'threshold'		=> 3
				    ),
				    'redacted'	=> false,
					'results'	=> array(
						array('interactions' => 2200, 	'unique_authors' => 2000, 	'key' => 'female'),
						array('interactions' => 1500, 	'unique_authors' => 1400, 	'key' => 'male'),
						array('interactions' => 4400, 	'unique_authors' => 4400, 	'key' => 'unknown')
					)
				),
				'interactions'		=>	185200,
				'unique_authors'	=>	152700
			),
			'rate_limit'           => 200,
			'rate_limit_remaining' => 150,
		);

		DataSift_MockApiClient::setResponse($response);


		$pylon = new DataSift_Pylon($this->user);

		$parameters = array(
		    'analysis_type' => 'freqDist',
		    'parameters' => array(
		        'interval' 	=> 'fb.author.gender',
		        'threshold'	=> 4
		    )
		);

		$analyze = $pylon->analyze($parameters);

		$this->assertEquals($analyze['interactions'], 185200, 'Interaction count did not match');
		$this->assertEquals($analyze['unique_authors'], 152700, 'Unique authors did not match');
		$this->assertEquals($analyze['analysis']['analysis_type'], 'freqDist', 'Analysis type did not match');
		$this->assertArrayHasKey('interactions', $analyze['analysis']['results'][0], 'interactions not found in results');
		$this->assertArrayHasKey('unique_authors', $analyze['analysis']['results'][0], 'interactions not found in results');
		$this->assertArrayHasKey('interactions', $analyze['analysis']['results'][1], 'interactions not found in results');
		$this->assertArrayHasKey('unique_authors', $analyze['analysis']['results'][1], 'interactions not found in results');
		
	}

	public function testAnalyseFilter(){
		$response = array(
			'response_code' => 200,
			'data'          => array(
				'analysis'		=> array(
					'analysis_type' => 'freqDist',
				    'parameters' 	=> array(
				        'interval' 		=> 'fb.author.gender',
				        'threshold'		=> 3
				    ),
				    'redacted'	=> false,
					'results'	=> array(
						array('interactions' => 2200, 	'unique_authors' => 2000, 	'key' => 'female'),
						array('interactions' => 1500, 	'unique_authors' => 1400, 	'key' => 'male'),
						array('interactions' => 4400, 	'unique_authors' => 4400, 	'key' => 'unknown')
					)
				),
				'interactions'		=>	185200,
				'unique_authors'	=>	152700
			),
			'rate_limit'           => 200,
			'rate_limit_remaining' => 150,
		);

		DataSift_MockApiClient::setResponse($response);


		$pylon = new DataSift_Pylon($this->user);

		$parameters = array(
		    'analysis_type' => 'freqDist',
		    'parameters' => array(
		        'interval' 	=> 'fb.author.gender',
		        'threshold'	=> 4
		    )
		);

		$filter = 'fb.content contains "coffee"';

		$analyze = $pylon->analyze($parameters, $filter);

		$this->assertEquals($analyze['interactions'], 185200, 'Interaction count did not match');
		$this->assertEquals($analyze['unique_authors'], 152700, 'Unique authors did not match');
		$this->assertArrayHasKey('interactions', $analyze['analysis']['results'][0], 'interactions not found in results');
		$this->assertArrayHasKey('unique_authors', $analyze['analysis']['results'][0], 'interactions not found in results');
		$this->assertArrayHasKey('interactions', $analyze['analysis']['results'][1], 'interactions not found in results');
		$this->assertArrayHasKey('unique_authors', $analyze['analysis']['results'][1], 'interactions not found in results');
	}

	public function testAnalyseTimeFrame(){
		$response = array(
			'response_code' => 200,
			'data'          => array(
				'analysis'		=> array(
					'analysis_type' => 'freqDist',
				    'parameters' 	=> array(
				        'interval' 		=> 'fb.author.gender',
				        'threshold'		=> 3
				    ),
				    'redacted'	=> false,
					'results'	=> array(
						array('interactions' => 2200, 	'unique_authors' => 2000, 	'key' => 'female'),
						array('interactions' => 1500, 	'unique_authors' => 1400, 	'key' => 'male'),
						array('interactions' => 4400, 	'unique_authors' => 4400, 	'key' => 'unknown')
					)
				),
				'interactions'		=>	185200,
				'unique_authors'	=>	152700
			),
			'rate_limit'           => 200,
			'rate_limit_remaining' => 150,
		);

		DataSift_MockApiClient::setResponse($response);


		$pylon = new DataSift_Pylon($this->user);

		$parameters = array(
		    'analysis_type' => 'freqDist',
		    'parameters' => array(
		        'interval' 	=> 'fb.author.gender',
		        'threshold'	=> 4
		    )
		);

		$analyze = $pylon->analyze($parameters, '', 1435662000, 1435748400);

		$this->assertEquals($analyze['interactions'], 185200, 'Interaction count did not match');
		$this->assertEquals($analyze['unique_authors'], 152700, 'Unique authors did not match');
		$this->assertArrayHasKey('interactions', $analyze['analysis']['results'][0], 'interactions not found in results');
		$this->assertArrayHasKey('unique_authors', $analyze['analysis']['results'][0], 'interactions not found in results');
		$this->assertArrayHasKey('interactions', $analyze['analysis']['results'][1], 'interactions not found in results');
		$this->assertArrayHasKey('unique_authors', $analyze['analysis']['results'][1], 'interactions not found in results');
	}

	public function testAnalyseInvalidTimeFrame(){
		$response = array(
			'response_code' => 400,
			'data'          => array(
				'error'					=> 'Start cannot be after end',
			),
			'rate_limit'           => 200,
			'rate_limit_remaining' => 150,
		);

		DataSift_MockApiClient::setResponse($response);


		$pylon = new DataSift_Pylon($this->user);

		$parameters = array(
		    'analysis_type' => 'freqDist',
		    'parameters' => array(
		        'interval' 	=> 'fb.author.gender',
		        'threshold'	=> 4
		    )
		);

		$this->setExpectedException('DataSift_Exception_InvalidData');

		$analyze = $pylon->analyze($parameters, '', 1435748400, 1435662000);

	}

	public function testAnalyseInvalidHash(){
		$response = array(
			'response_code' => 400,
			'data'          => array(
				'error'					=> 'Start cannot be after end',
			),
			'rate_limit'           => 200,
			'rate_limit_remaining' => 150,
		);

		DataSift_MockApiClient::setResponse($response);


		$pylon = new DataSift_Pylon($this->user);

		$parameters = array(
		    'analysis_type' => 'freqDist',
		    'parameters' => array(
		        'interval' 	=> 'fb.author.gender',
		        'threshold'	=> 4
		    )
		);

		$this->setExpectedException('DataSift_Exception_InvalidData');

		$analyze = $pylon->analyze($parameters, '', 1435748400, 1435662000);

	}

	public function testTags(){
		$response = array(
			'response_code' => 200,
			'data'          => array(
				'interaction.tag_tree.automotive.media', 
				'interaction.tag_tree.motogp.manufacturer', 
				'interaction.tag_tree.motogp.rider'	
			),
			'rate_limit'           => 200,
			'rate_limit_remaining' => 150,
		);

		DataSift_MockApiClient::setResponse($response);


		$pylon = new DataSift_Pylon($this->user);

		$hash = "1a4268c9b924d2c48ed1946d6a7e6272";

		$tags = $pylon->tags($hash);

		$this->assertCount(3, $tags, 'Amount of tags did not match');

	}

	public function testTagsEmptyHash(){
		$pylon = new DataSift_Pylon($this->user);

		$hash = "";

		$this->setExpectedException('DataSift_Exception_InvalidData');

		$tags = $pylon->tags($hash);
	}

	public function testEmptyTags(){
		$response = array(
			'response_code' => 200,
			'data'          => array(),
			'rate_limit'           => 200,
			'rate_limit_remaining' => 150,
		);

		DataSift_MockApiClient::setResponse($response);

		$pylon = new DataSift_Pylon($this->user);

		$hash = "1a4268c9b924d2c48ed1946d6a7e6272";

		$tags = $pylon->tags($hash);

		$this->assertCount(0, $tags, 'Amount of tags did not match');

	}

}