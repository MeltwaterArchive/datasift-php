<?php

//include the compile.php file which creates a stream and provides us with it's hash etc.
include 'compile.php';

//definition and its properties are defined in compile.php
/**
 * Get data that has been buffered
 * 
 * The first call to getBuffered really just starts the buffering in most cases and no data will be returned
 * so we wait and make a request a few seconds later until we get some data
 */
$buf = array();
while (count($buf = $definition->getBuffered()) == 0) {
	print "\nNo data available in buffer. Sleeping for 5 seconds...\n";
	sleep(5);
}
//$buf should now have an array of interactions so we can access them as a set of associative array elements as in:
print "\n-- Contents of the first interaction available from the buffer --\n";
print $buf[0]['interaction']['content']; //where [0] is the first interaction

/**
 * The above call to getBuffered() has as many interaction as were available when the request was made, up to 200 max
 * The Datasift API allows you to limit the maximum amount of interactions returned as follows
 */
print "\n-- Limited amount of interactions --\n";
print_r($buf = $definition->getBuffered(2)); //get only 2 items,assign them to the $buf var and print both of them

/**
 * You can further specify from which interaction you wish to start, i.e. paging over items available in the buffer
 */
$id = $buf[1]['interaction']['id']; //get the id of the last interaction we got
print "\n-- Limited amount of interactions AND specifying the interaction id to start from as $id--\n";
print_r($definition->getBuffered(2, $id));
