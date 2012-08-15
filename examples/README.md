# DataSift PHP Client Library Examples

The examples in this folder have been designed to demonstrate the most common
use cases. This file contains a brief description of each example.

Please note that running these examples will incur DPU costs against your account.

Unless stated otherwise all of the examples use the username and API key in config.php.

## consume-stream.php

This example shows the basic structure of a streaming client. It takes one or more stream hashes on the command line and consumes them all down a single multi-stream connection displaying the details of events as they occur.

    php consume-stream.php 0a4c11d2e90ddee8483e2c68061cbbf5

## deletes.php

Correct handling of delete notifications is a requirement of your data usage agreement with DataSift. This example demonstrates how delete notifications will be received by your code.

    php deletes.php

## dpu.php

All usage of the DataSift platform incurs costs in units of DPU. This example shows how to retrieve a breakdown of the DPU cost for a given stream. You can either supply the CSDL on the command line (as the first argument so it must be properly escaped) or you can pipe it to the command.

    cat football.csdl | php dpu.php

## football.php

You might want to collect a sample of data from a given stream, and that's what the football example demonstrates. The stream looks for anything containing the word "football" and outputs them as they are received. Once it has received ten interactions it stops the consumer and exits.

    php football.php

## football-buffered.php

This example does the same as football.php but it uses API calls to get the data rather than a streaming connection.

    php football-buffered.php

## twitter-track.php

Once upon a time, when the Twitter streaming API was barely out of diapers, they introduced a feature that enabled you to receive everything that mentioned one or more words. This example implements that functionality.

Call the script with the words or phrases you're interested in as command line arguments and it will display matching tweets as they are received.

    php twitter-track.php olympics london2012 "london 2012" "boris johnson" locog

## historics.php

This script is a utility for calling the individual commands in the historics folder. To use it you pass in your username and API key, followed by the command you want to run and the arguments that command expects.

    php historics.php <username> <api_key> <command> <arg1> <arg2> .. <argn>

### Historics commands

* **create\_from\_csdl**

   Creates a new historic query from a file containing CSDL. Call this command without any arguments for usage information, or refer to the usage function in the code.

        php historics.php your_username your_api_key create_from_csdl \
            csdl.txt "2012-08-01 12:00:00" "2012-08-01 13:00:00" twitter \
            "nexus 7" 100

* **create\_from\_hash**

  Creates a new historic query from a stream hash. Call this command without any arguments for usage information, or refer to the usage function in the code.

        php historics.php your_username your_api_key create_from_hash \
            0a4c11d2e90ddee8483e2c68061cbbf5 "2012-08-01 12:00:00" \
            "2012-08-01 13:00:00" twitter "nexus 7" 100

* **delete**

  Delete one or more Historics queries.

        php historics.php your_username your_api_key delete 58a83a9bdc2880de7fe6

* **list**

  List the Historics queries in your account.

        php historics.php your_username your_api_key list

* **start**

  Start a Historics query.

        php historics.php your_username your_api_key start 58a83a9bdc2880de7fe6

* **stop**

  Stop a Historics query.

        php historics.php your_username your_api_key stop 58a83a9bdc2880de7fe6

* **view**

  View the details of a Historics query.

        php historics.php your_username your_api_key view 58a83a9bdc2880de7fe6

## push.php

This script is a utility for calling the individual commands in the push folder. To use it you pass in your username and API key, followed by the command you want to run and the arguments that command expects.

    php push <username> <api_key> <command> <arg1> <arg2> .. <argn>

### Push commands

* **push\_from\_hash**

  Creates a new Push subscription from a stream hash. Call this command without any arguments for usage information, or refer to the usage function in the code.

        php push.php your_username your_api_key push_from_hash http stream \
            0a4c11d2e90ddee8483e2c68061cbbf5 \"Nexus 7 Push\" \
            delivery_frequency=10 url=http://www.example.com/push_endpoint \
            auth.type=basic auth.username=myuser auth.password=mypassword

* **push\_stream\_from\_csdl**

  Creates a new stream from the supplied CSDL, and creates and activates a Push subscription to receive the data. Call this command without any arguments for usage information, or refer to the usage function in the code.

        php push.php your_username your_api_key push_from_hash http stream \
            0a4c11d2e90ddee8483e2c68061cbbf5 \"Nexus 7 Push\" \
            delivery_frequency=10 url=http://www.example.com/push_endpoint \
            auth.type=basic auth.username=myuser auth.password=mypassword

* **push\_historic\_from\_csdl**

  Creates a new Historics query from the supplied CSDL, creates a Push subscription to receive the data, and starts the Historics query.

        php push.php your_username your_api_key push_historic_from_csdl \
            csdl.txt 20120801120000 20120801130000 twitter 100 \
            "Nexus 7 Historic Push" http delivery_frequency=10 \
            url=http://www.example.com/push_endpoint auth.type=none

* **delete**

  Deletes one or more existing Push subscriptions.

        php push.php your_username your_api_key delete \
            20cf023d838fcfc573a5d991f1b8a911

* **list**

  Lists the Push subscriptions in your account.

        php push.php your_username your_api_key list

* **pause**

  Pause one or more Push subscriptions in your account.

        php push.php your_username your_api_key pause \
            20cf023d838fcfc573a5d991f1b8a911

* **resume**

  Resume one or more Push subscriptions in your account.

        php push.php your_username your_api_key resume \
            20cf023d838fcfc573a5d991f1b8a911

* **stop**

  Stops one or more Push subscriptions in your account.

        php push.php your_username your_api_key stop \
            20cf023d838fcfc573a5d991f1b8a911

* **view**

  View the details of one or more Push subscriptions in your account.

        php push.php your_username your_api_key view \
            20cf023d838fcfc573a5d991f1b8a911

* **viewlog**

  View recent log entries for your Push subscriptions. If no subscription ID is passed then all log entries are shown, otherwise only log entries relating to that subscription are retrieved.

        php push.php your_username your_api_key viewlog

        php push.php your_username your_api_key viewlog \
            20cf023d838fcfc573a5d991f1b8a911
