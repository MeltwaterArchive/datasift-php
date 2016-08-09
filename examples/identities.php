<?php

// Include the DataSift library
require dirname(__FILE__).'/../lib/datasift.php';

// Include the configuration - put your username and API key in this file
require dirname(__FILE__).'/../config.php';

// Authenticate
echo "Creating user...\n";
$user = new DataSift_User(USERNAME, API_KEY);
$service = 'facebook';
$label = 'test-' . time('U');

try {
    $identity = new DataSift_Account_Identity($user);
    $token = new DataSift_Account_Identity_Token($user);
    $limit = new DataSift_Account_Identity_Limit($user);

    $res = $identity->getAll();
    echo "\nGetting identities...\n";

    foreach ($res['identities'] as $r) {
        echo $r['id'] . " - " . $r['label'] ."\n";
    }
    unset($res);

    echo "\nCreating identity...\n";
    $res = $identity->create($label, true);
    echo $res['id'] . " - " . $res['label'] ."\n";
    $id = $res['id'];
    unset($res);

    echo "\nGetting identity...\n";
    $res = $identity->get($id);
    echo $res['id'] . " - " . $res['label'] ."\n";
    unset($res);

    echo "\nUpdating identity...\n";
    $res = $identity->update($id, $label . '-updated', true, 'active');
    echo $res['id'] . " - " . $res['label'] ."\n";
    unset($res);

    echo "\nCreating token....\n";
    $res = $token->create($id, $service, md5('test'));
    echo $res['service'] . " - " . $res['token'] . "\n";
    unset($res);

    echo "\nGetting token...\n";
    $res = $token->get($id, $service);
    echo $res['service'] . " - " . $res['token'] . "\n";
    unset($res);

    echo "\nGetting tokens...\n";
    $res = $token->getAll($id);

    foreach ($res['tokens'] as $r) {
        echo $r['service'] . " - " . $r['token'] . "\n";        
    }
    unset($res);

    echo "\nUpdating token...\n";
    $res = $token->update($id, $service, md5('test'));
    echo $res['service'] . " - " . $res['token'] . "\n";
    unset($res);

    echo "\nCreating limit...\n";
    $res = $limit->create($id, $service, 2000, 3000);
    echo $res['id'] . " - " . $res['service'] . " - " . $res['total_allowance'] . " - " . $res['analyze_queries'] . "\n";
    unset($res);

    echo "\nGetting limit...\n";
    $res = $limit->get($id, $service);
    echo $res['id'] . " - " . $res['service'] . " - " . $res['total_allowance'] . " - " . $res['analyze_queries'] . "\n";
    unset($res);

    echo "\nGetting limits...\n";
    $res = $limit->getAll($service);

    $r = $res['limits'];
    echo $r['id'] . " - " . $r['service'] . " - " . $r['total_allowance'] . " - " . $res['analyze_queries'] . "\n";
    unset($res);

    echo "\nUpdating limit...\n";
    $res = $limit->update($id, $service, 10000);
    echo $res['id'] . " - " . $res['service'] . " - " . $res['total_allowance'] . " - " . $res['analyze_queries'] . "\n";
    unset($res);

    echo "\nDeleting limit...\n";
    $res = $limit->delete($id, $service);

    if($res === true) {
        echo "Deleted\n";
    }
    unset($res);

    echo "\nDeleting token...\n";
    $res = $token->delete($id, 'facebook');

    if($res === true) {
        echo "Deleted\n";
    }
    unset($res);
    
    echo "\nDeleting identity...\n";
    $res = $identity->delete($id);

    if($res === true) {
        echo "Deleted\n";
    }
    unset($res);

    echo "\nGetting identities...\n";
    $res = $identity->getAll();

    foreach ($res['identities'] as $r) {
        echo $r['id'] . " - " . $r['label'] ."\n";
    }
    unset($res);
} catch (Exception $e){
    print_r($e->getMessage());
}

?>
