<?php

namespace DataSift\Tests;

use DataSift_Source;
use DataSift_User;

class SourceTest extends \PHPUnit_Framework_TestCase
{
    protected $config = false;
    protected $user = false;
    protected $source_id = false;
    protected $source = false;

    protected function setUp()
    {
        require_once(dirname(__FILE__) . '/../lib/datasift.php');
        require_once(dirname(__FILE__) . '/../config.php');
        require_once(dirname(__FILE__) . '/testdata.php');
        $this->user = new DataSift_User(USERNAME, API_KEY);
        $this->user->setApiClient('\DataSift\Tests\MockApiClient');
        MockApiClient::setResponse(false);
    }

    protected function createSource()
    {
        $response = array(
            'response_code' => 200,
            'data' => array(
                'id' => '78b3601ef667466d95f19570dcb74699',
                'name' => 'My PHP managed source',
                'created_at' => 1435869526,
                'status' => 'active',
                'auth' => array(
                    array(
                        'identity_id' => '7b1be3a398e646bbb3c7a5cb9717ba45',
                        'expires_at' => 1495869526,
                        'parameters' => array('value' => '363056350669209|09af1ce9c5d8d23147ec4eeb9a33aac2')
                    )
                ),
                'resources' => array(
                    array(
                        'resource_id' => '30bc448896de44b88604ac223cb7f26f',
                        'status' => 'valid',
                        'parameters' => array(
                            'url' => 'http://www.facebook.com/theguardian',
                            'title' => 'The Guardian',
                            'id' => 10513336322
                        )
                    )
                ),
                'parameters' => array(
                    'comments' => true,
                    'likes' => true,
                    'page_likes' => true,
                    'posts_by_others' => true
                )

            ),
            'rate_limit' => 200,
            'rate_limit_remaining' => 150,
        );

        MockApiClient::setResponse($response);

        $source = new DataSift_Source($this->user, array(
            'name' => 'My PHP managed source',
            'source_type' => 'facebook_page',
            'parameters' => array(
                'comments' => true,
                'likes' => true,
                'page_likes' => true,
                'posts_by_others' => true,
            ),
            'auth' => array(
                array(
                    'expires_at' => 1495869526,
                    'parameters' => array('value' => '363056350669209|09af1ce9c5d8d23147ec4eeb9a33aac2')
                )
            ),
            'resources' => array(
                array(
                    'parameters' => array(
                        'url' => 'http://www.facebook.com/theguardian',
                        'title' => 'The Guardian',
                        'id' => 10513336322
                    )
                )
            ),
        ));

        $source->save();

        return $source;
    }

    public function testCreateSource()
    {
        $source = $this->createSource();
        $resources = $source->getResources();
        $auth = $source->getAuth();
        $this->assertEquals($source->getId(), '78b3601ef667466d95f19570dcb74699', 'Source ID did not match');
        $this->assertEquals($source->getName(), 'My PHP managed source', 'Name did not match');
        $this->assertEquals($resources[0]['parameters']['id'], 10513336322, 'Resource ID did not match');
        $this->assertEquals(
            $auth[0]['parameters']['value'],
            '363056350669209|09af1ce9c5d8d23147ec4eeb9a33aac2',
            'Auth token did not match'
        );
    }

    public function testUpdateSource()
    {
        $source = $this->createSource();

        $source->setResources(
            array(
                array(
                    'parameters' => array(
                        'url' => 'http://www.facebook.com/theguardian',
                        'title' => 'The Guardian',
                        'id' => 10513336322
                    )
                ),
                array(
                    'parameters' => array(
                        'url' => 'http://www.facebook.com/thesun',
                        'title' => 'The Sun',
                        'id' => 10513536389
                    )
                )
            )
        );

        $source->setName('My Updated PHP managed source');

        $response = array(
            'response_code' => 200,
            'data' => array(
                'id' => '78b3601ef667466d95f19570dcb74699',
                'name' => 'My Updated PHP managed source',
                'created_at' => 1435869526,
                'status' => 'active',
                'auth' => array(
                    array(
                        'identity_id' => '7b1be3a398e646bbb3c7a5cb9717ba45',
                        'expires_at' => 1495869526,
                        'parameters' => array('value' => '363056350669209|09af1ce9c5d8d23147ec4eeb9a33aac2')
                    )
                ),
                'resources' => array(
                    array(
                        'resource_id' => '30bc448896de44b88604ac223cb7f26f',
                        'status' => 'valid',
                        'parameters' => array(
                            'url' => 'http://www.facebook.com/theguardian',
                            'title' => 'The Guardian',
                            'id' => 10513336322
                        )
                    ),
                    array(
                        'resource_id' => '70dc448296de44l88604gc713cb7f26f',
                        'status' => 'valid',
                        'parameters' => array(
                            'url' => 'http://www.facebook.com/thesun',
                            'title' => 'The Sun',
                            'id' => 10513536389
                        )
                    )
                ),
                'parameters' => array(
                    'comments' => true,
                    'likes' => true,
                    'page_likes' => true,
                    'posts_by_others' => true
                )

            ),
            'rate_limit' => 200,
            'rate_limit_remaining' => 150,
        );

        MockApiClient::setResponse($response);

        $source->save();

        $resources = $source->getResources();
        $auth = $source->getAuth();

        $this->assertEquals($source->getId(), '78b3601ef667466d95f19570dcb74699', 'Source ID did not match');
        $this->assertEquals($source->getName(), 'My Updated PHP managed source', 'Name did not match');
        $this->assertEquals($resources[0]['parameters']['id'], 10513336322, 'Resource ID did not match');
        $this->assertEquals($resources[1]['parameters']['id'], 10513536389, 'Resource ID did not match');
        $this->assertEquals(
            $auth[0]['parameters']['value'],
            '363056350669209|09af1ce9c5d8d23147ec4eeb9a33aac2',
            'Auth token did not match'
        );
    }

    public function testSourceList()
    {
        $response = array(
            'response_code' => 200,
            'data' => array(
                'count' => 1,
                'sources' =>
                    array(
                        array(
                            'id' => '78b3601ef667466d95f19570dcb74699',
                            'name' => 'My Updated PHP managed source',
                            'created_at' => 1435869526,
                            'status' => 'active',
                            'auth' => array(
                                array(
                                    'identity_id' => '7b1be3a398e646bbb3c7a5cb9717ba45',
                                    'expires_at' => 1495869526,
                                    'parameters' => array('value' => '363056350669209|09af1ce9c5d8d23147ec4eeb9a33aac2')
                                )
                            ),
                            'resources' => array(
                                array(
                                    'resource_id' => '30bc448896de44b88604ac223cb7f26f',
                                    'status' => 'valid',
                                    'parameters' => array(
                                        'url' => 'http://www.facebook.com/theguardian',
                                        'title' => 'The Guardian',
                                        'id' => 10513336322
                                    )
                                )
                            ),
                            'parameters' => array(
                                'comments' => true,
                                'likes' => true,
                                'page_likes' => true,
                                'posts_by_others' => true
                            )

                        )
                    ),
            ),
            'rate_limit' => 200,
            'rate_limit_remaining' => 150,
        );

        MockApiClient::setResponse($response);

        $sources = DataSift_Source::listSources($this->user);

        $source1 = $sources['sources'][0];

        $this->assertEquals($source1->getId(), '78b3601ef667466d95f19570dcb74699', 'Source ID differs to test data');
        $this->assertEquals($sources['count'], 1, 'Count differs to test data');
    }

    public function testGetSource()
    {
        $response = array(
            'response_code' => 200,
            'data' => array(
                'id' => '78b3601ef667466d95f19570dcb74699',
                'name' => 'My PHP managed source',
                'created_at' => 1435869526,
                'status' => 'active',
                'auth' => array(
                    array(
                        'identity_id' => '7b1be3a398e646bbb3c7a5cb9717ba45',
                        'expires_at' => 1495869526,
                        'parameters' => array('value' => '363056350669209|09af1ce9c5d8d23147ec4eeb9a33aac2')
                    )
                ),
                'resources' => array(
                    array(
                        'resource_id' => '30bc448896de44b88604ac223cb7f26f',
                        'status' => 'valid',
                        'parameters' => array(
                            'url' => 'http://www.facebook.com/theguardian',
                            'title' => 'The Guardian',
                            'id' => 10513336322
                        )
                    )
                ),
                'parameters' => array(
                    'comments' => true,
                    'likes' => true,
                    'page_likes' => true,
                    'posts_by_others' => true
                )

            ),
            'rate_limit' => 200,
            'rate_limit_remaining' => 150,
        );

        MockApiClient::setResponse($response);

        $source = DataSift_Source::get($this->user, '78b3601ef667466d95f19570dcb74699');

        $resources = $source->getResources();
        $auth = $source->getAuth();

        $this->assertEquals($source->getId(), '78b3601ef667466d95f19570dcb74699', 'Source ID did not match');
        $this->assertEquals($source->getName(), 'My PHP managed source', 'Name did not match');
        $this->assertEquals($resources[0]['parameters']['id'], 10513336322, 'Resource ID did not match');
        $this->assertEquals(
            $auth[0]['parameters']['value'],
            '363056350669209|09af1ce9c5d8d23147ec4eeb9a33aac2',
            'Auth token did not match'
        );
    }

    public function testAddRemoveAuth()
    {
        $source = $this->createSource();

        $new_auth = array(
            'identity_id' => '7b1be3a398e646bbb3c7a5cb9717ba45',
            'expires_at' => 1495869526,
            'parameters' => array('value' => '363056350669209|09af1ce9c5d8d23147ec4eeb9a33aac2')
        );

        $response = array(
            'response_code' => 200,
            'data' => array(
                'auth' => array(
                    array(
                        'identity_id' => '7b1be3a398e646bbb3c7a5cb9717ba45',
                        'expires_at' => 1495869526,
                        'parameters' => array('value' => '363056350669209|09af1ce9c5d8d23147ec4eeb9a33aac2')
                    ),
                    $new_auth
                )
            ),
            'rate_limit' => 200,
            'rate_limit_remaining' => 150,
        );

        MockApiClient::setResponse($response);

        $source->addAuth($new_auth);

        $auth = $source->getAuth();

        $this->assertCount(2, $source->getAuth(), 'Expecting 2 auth objects to be returned');
        $this->assertArrayHasKey('identity_id', $auth[0], 'First auth had no ID');
        $this->assertArrayHasKey('identity_id', $auth[1], 'Second auth had no ID');

        //Now remove the original auth

        $response = array(
            'response_code' => 200,
            'data' => array(
                'auth' => array(
                    $new_auth
                )
            ),
            'rate_limit' => 200,
            'rate_limit_remaining' => 150,
        );

        MockApiClient::setResponse($response);

        $source->removeAuth(array('7b1be3a398e646bbb3c7a5cb9717ba45'));

        $auth = $source->getAuth();

        $this->assertCount(1, $source->getAuth(), 'Expecting 1 auth object to be returned');
        $this->assertArrayHasKey('identity_id', $auth[0], 'First auth had no ID');
    }

    public function testAddRemoveResource()
    {
        $source = $this->createSource();

        $new_resource = array(
            'parameters' => array(
                'url' => 'http://www.facebook.com/thesun',
                'title' => 'The Sun',
                'id' => 10513536389
            )
        );

        $response = array(
            'response_code' => 200,
            'data' => array(
                'resources' => array(
                    array(
                        'resource_id' => '30bc448896de44b88604ac223cb7f26f',
                        'status' => 'valid',
                        'parameters' => array(
                            'url' => 'http://www.facebook.com/theguardian',
                            'title' => 'The Guardian',
                            'id' => 10513336322
                        )
                    ),
                    $new_resource
                )
            ),
            'rate_limit' => 200,
            'rate_limit_remaining' => 150,
        );

        MockApiClient::setResponse($response);

        $source->addResource($new_resource);

        $resources = $source->getResources();

        $this->assertCount(2, $source->getResources(), 'Expecting 2 auth objects to be returned');
        $this->assertArrayHasKey('id', $resources[0]['parameters'], 'First auth had no ID');
        $this->assertArrayHasKey('id', $resources[1]['parameters'], 'Second auth had no ID');

        //Now remove the original auth

        $response = array(
            'response_code' => 200,
            'data' => array(
                'resources' => array(
                    $new_resource
                )
            ),
            'rate_limit' => 200,
            'rate_limit_remaining' => 150,
        );

        MockApiClient::setResponse($response);

        $resources = $source->getResources();

        $source->removeResource(array('30bc448896de44b88604ac223cb7f26f'));
        $this->assertCount(1, $source->getResources(), 'Expecting 1 auth object to be returned');
        $this->assertArrayHasKey('id', $resources[0]['parameters'], 'First auth had no ID');
    }
}
