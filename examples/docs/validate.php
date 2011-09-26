<?php

require '../../lib/datasift.php';
include "config.php";
//Step 1 - create a user object
$user = new DataSift_User(USERNAME, API_KEY);

//Make a successful validation call
try {
    print_r($user);
//Step 2 - directly use callAPI method to get the validate method
    $user->callAPI('validate', array('csdl' => 'interaction.content contains "google"'));

    print_r($user);
} catch (Exception $e) {
    print $e->getMessage();
}

//On success produces
/**
DataSift_User Object
(   ...
    [_rate_limit:protected] => -1
    [_rate_limit_remaining:protected] => -1
    ...
)
DataSift_User Object
(
    ...
    [_rate_limit:protected] => 5000
    [_rate_limit_remaining:protected] => 4975
    ...
)
 */

//Make a validation call that should fail
try {
    print_r($user);
//Step 2 - directly use callAPI method to get the validate method
    $user->callAPI('validate', array('csdl' => 'interaction.conhtent contains "google"'));

    print_r($user);
} catch (Exception $e) {
    print $e->getMessage();
}
/**
DataSift_User Object
(
    ...
    [_rate_limit:protected] => -1
    [_rate_limit_remaining:protected] => -1
    ...
)
The Exception message produced will be similar to the following
    "The target interaction.conhtent does not exist"
 */