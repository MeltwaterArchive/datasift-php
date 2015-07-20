<?php
/**
 * This simple example demonstrates how create and use the pylon end
 * points.
 *
 * @category  DataSift
 * @package   PHP-client
 * @author    Paul Mozo <paul.mozo@datasift.com>
 * @copyright 2013 MediaSift Ltd.
 * @license   http://www.debian.org/misc/bsd.license BSD License (3 Clause)
 * @link      http://www.mediasift.com
 */

// Include the DataSift library
require dirname(__FILE__) . '/../lib/datasift.php';

// Include the configuration - put your username and API key in this file
require dirname(__FILE__) . '/../config.php';

// Authenticate
echo "Creating user...\n";
$user = new DataSift_User(USERNAME, API_KEY, false);

$csdl = '(fb.content any "coffee" OR fb.hashtags in "tea") AND fb.language in "en"';

//Validate the CSDL
$validate = DataSift_Pylon::validate($user, $csdl);

echo "Definition has been successfully validated, DPUs: {$validate['dpu']} created at: {$validate['created_at']} \n\n";

//Create the PYLON object
$pylon_name = "My pylon test";

$pylon = new DataSift_Pylon($user);

$pylon->setName($pylon_name);

//Add CSDL to the PYLON
$pylon->setCsdl($csdl);

$pylon->compile(); 

//Start the pylon
$pylon->start();

//Stop after 10 seconds
sleep(10);
$pylon->stop();

//Set the analyze parameters
$parameters = array(
    'analysis_type' => 'freqDist',
    'parameters' => array(
        'threshold' => 3,
        'target' => 'fb.author.gender'
    ),
    'child' => array(
        'analysis_type' => 'freqDist',
        'parameters' => array(
            'threshold' => 3,
            'target' => 'fb.author.age'
        ),
        'child' => array(
            'analysis_type' => 'freqDist',
            'parameters' => array(
                'threshold' => 3,
                'target' => 'fb.author.highest_education'
            )
        )
    )
);

//Choose a filter
$filter = 'fb.content contains "starbucks"';

//Analyze the recording
$analyze = $pylon->analyze($parameters, $filter);

$pylon->reload();

echo "There were a total of {$analyze['interactions']} interactions that matched this filter\n";

$reloaded_pylon = DataSift_Pylon::fromHash($user, $pylon->getHash());
