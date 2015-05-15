<?php
/**
 * DataSift client
 *
 * This software is the intellectual property of MediaSift Ltd., and is covered
 * by retained intellectual property rights, including copyright.
 * Distribution of this software is strictly forbidden under the terms of this license.
 *
 * A CLI for the PHP client intended for testing purposes
 *
 * @category  DataSift
 * @package   PHP-client
 * @author    Paul Mozo <paul.mozo@datasift.com>
 * @copyright 2013 MediaSift Ltd.
 * @license   http://www.debian.org/misc/bsd.license BSD License (3 Clause)
 * @link      http://www.mediasift.com
 */

class Cli {
    
    /**
     * Arguments
     * 
     * @var array
     */
    protected $arguments = array();
    
    /**
     * Commands
     * @var array 
     */
    protected $commands = array(
        "pylon" => array(
            "get" => array(    
                "user"              => true,
                "hash"              => false
            ),
            "list" => array(
                "user"              => true,
                "page"              => false, 
                "per_page"          => false,
                "order_by"          => false,
                "order_dir"         => false
            ),
            "validate" => array(    
                "user"              => true,
                "csdl"              => true
            ),
            "compile" => array(
                "csdl"              => true
           ),
            "start" => array(
                "hash"              => true,
                "name"              => false
            ),
            "stop" => array(
                "hash"              => true
            ),
            "analyze" => array(
                "parameters"        => true,
                "filter"            => false, 
                "start"             => false,    
                "end"               => false,
                "hash"              => true
            ),
            "tags" => array(
                "hash"              => true
            )
        ),
        "identity" => array(
            "list" => array(
                "label"             => false,
                "page"              => false,
                "per_page"          => false
            ),
            "get" => array(
                "id"                => true
            ),
            "create" => array(
                "label"             => true,
                "master"            => false,
                "status"            => false
            ),
            "update" => array(
                "id"                => true,
                "label"             => false,
                "master"            => false,
                "status"            => false
            ),
            "delete" => array(
                "id"                => true
            ),
        ),
        "token" => array(
            "list" => array(
                "identity_id"       => true,
                "page"              => false,
                "per_page"          => false
            ),
            "get" => array(
                "identity_id"       => true,
                "service"           => true,
            ),
            "create" => array(
                "identity_id"       => true,
                "service"           => true,
                "token"             => true,
            ),
            "update" => array(
                "identity_id"       => true,
                "service"           => true,
                "token"             => true,
            ),
            "delete" => array(
                "identity_id"       => true,
                "service"           => true,
            ),
        ),
        "limit" => array(
            "list" => array(
                "service"           => true,
                "page"              => false,
                "per_page"          => false
            ),
            "get" => array(
                "identity_id"       => true,
                "service"           => true,
            ),
            "create" => array(
                "identity_id"       => true,
                "service"           => true,
                "total_allowance"   => true
            ),
            "update" => array(
                "identity_id"       => true,
                "service"           => true,
                "total_allowance"   => true
            ),
            "delete" => array(
                "identity_id"       => true,
                "service"           => true
            )
        )
    );
    
    /**
     * Options
     * @var array 
     */
    protected $options = array(
        "authenticate" => array(
            "short"     => "a",
            "long"      => "auth",
            "required"  => true
        ),
        "endpoint" => array(
            "short"     => "e",
            "long"      => "endpoint",
            "required"  => true
        ),
        "command" => array(
            "short"     => "c",
            "long"      => "command",
            "required"  => true
        ),
        "url" => array(
            "short"     => "u",
            "long"      => "url",
            "required"  => true
        ),
        "parameters" => array(
            "short"     => "p",
            "long"      => "param",
            "required"  => true
        ),
        "pretty" => array(
            "short"     => "r",
            "long"      => "pretty",
            "required"  => false,
            "default"   => false
        ),
        "no-ssl" => array(
            "short"     => "ns",
            "long"      => "no-ssl",
            "required"  => false,
            "default"   => true
        )
    );
    
    /**
     * Constructor
     * 
     * @param array $arguments
     */
    public function __construct($arguments)
    {
        $this->setArguments($arguments);
    }
    
    /**
     * Get the arguments
     * 
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }
    
    /**
     * Set the arguments
     * 
     * @param array $arguments
     */
    public function setArguments($arguments)
    {
        array_shift($arguments);
        $this->arguments = $arguments;
    }
    
    /**
     * Get the list of commands
     * 
     * @return array
     */
    public function getCommands()
    {
        return $this->commands;
    }
    
    /**
     * Get the list of options
     * 
     * @return type
     */
    public function getOptions()
    {
        return $this->options;
    }
    
    /**
     * Function to merge any occurances of the same option chosen 
     * with long and short to a single array
     * 
     * @param array $mergeKeys
     * @param array $options
     * @return array
     */
    protected function optionMerge($mergeKeys, $options)
    {
        if (!isset($options[$mergeKeys["short"]]) && !isset($options[$mergeKeys["long"]])) {
            $return = null;
        } elseif (isset($options[$mergeKeys["short"]]) && !isset($options[$mergeKeys["long"]])) {
            $return = $options[$mergeKeys["short"]];
        } elseif (!isset($options[$mergeKeys["short"]]) && isset($options[$mergeKeys["long"]])) {
            $return = $options[$mergeKeys["long"]];
        } elseif (is_array($options[$mergeKeys["short"]]) && !is_array($options[$mergeKeys["long"]])) {    
            $return = $options[$mergeKeys["short"]];
            $return[] = $options[$mergeKeys["long"]];
        } elseif (!is_array($options[$mergeKeys["short"]]) && is_array($options[$mergeKeys["long"]])) {    
            $return = $options[$mergeKeys["long"]];
            $return[] = $options[$mergeKeys["short"]];
        } elseif (!is_array($options[$mergeKeys["short"]]) && !is_array($options[$mergeKeys["long"]])) {    
            $return = array($options[$mergeKeys["long"]], $options[$mergeKeys["short"]]);
        } elseif (is_array($options[$mergeKeys["short"]]) && is_array($options[$mergeKeys["long"]])) {    
            $return = array_merge($options[$mergeKeys["long"]], $options[$mergeKeys["short"]]);
        }

        return $return; 
    }

    /**
     * Sorts the parameters into matched pairs
     * 
     * @param array $params
     * @return array
     */
    protected function sortParams($params)
    {   
        $sortedParam = array();
        
        foreach ($params as $k => $param) {
            if ($k % 2 == 0) {
                $sortedParam[$param] = true;
                $key = $param;
            } else {
                $sortedParam[$key] = $param;
            }
        }
        
        return $sortedParam;
    }

    /**
     * Convert the auth to the array required
     * 
     * @param array $auth
     * @return array
     */
    protected function parseAuth(array $auth)
    {
        return array("username" => $auth[0], "api_key" => $auth[1]);
    }

    /**
     * Process the command line options
     * 
     * @return array
     */
    protected function getCommandLineOptions() 
    {
        $argv = $this->getArguments();
        $arguments = array();

        foreach ($argv as $k => $arg) {
            $short = false;
            $long = (substr($arg, 0, 2) == '--' ? true : false);
            
            if ($long) {
                $trim = 2;
            } else {
                $short = (substr($arg, 0, 1) == '-' ? true : false);
                $trim = 1;
            }
            
            if ($short || $long) {
                $arg = substr($arg, $trim);

                if (!isset($arguments[$arg]) || !is_array($arguments[$arg])) {
                    $arguments[$arg] = array(
                        'default' => true,
                        'values' => null
                    );
                }

                $key = $arg;
            } else {
                if (!$arguments[$key]['values']) {
                    $arguments[$key]['values'] = array();
                }
                
                $map = array('true' => true, 'false' => false);
                if (isset($map[$arg])) {
                    $arg = $map[$arg];
                }

                $arguments[$key]['values'][] = $arg;
            }
        }
        
        foreach ($arguments as $k => $arg) {
            $arguments[$k] = $arg['default'];
            
            if (is_array($arg['values'])) {
                if (count($arg['values']) == 1) {
                    $arguments[$k] = $arg['values'][0];
                } else {
                    $arguments[$k] = $arg['values'];
                }
            }
        }

        return $arguments;
    }

    /**
     * Parse the input parameters correctly 
     * 
     * @return array
     * @throws Exception
     */
    public function parseInput()
    {    
        $options = $this->getOptions();
        $rawOptions = $this->getCommandLineOptions();

        foreach ($options as $optionName => $mergeKeys) {
            $parsedOptions[$optionName] = $this->optionMerge($mergeKeys, $rawOptions);
        }

        //Parse the parameters
        if (isset($parsedOptions['parameters'])) {
            $parsedOptions['parameters'] = $this->sortParams($parsedOptions['parameters']);
        } 

        //Parse the auth
        $parsedOptions['authenticate'] = $this->parseAuth($parsedOptions['authenticate']);

        if (empty($parsedOptions['authenticate'])) {
            throw new Exception("Auth details must be supplied!", 1);    
        }

        //Parse the URL
        if (!isset($parsedOptions['url'])) {
            $parsedOptions['url'] = 'datasift.com/';
        }

        //Check url ends with a /
        if (substr($parsedOptions['url'], -1) != '/') {
            $parsedOptions['url'] = $parsedOptions['url'].'/';
        }

        if (isset($parsedOptions['no-ssl'])) {
            $parsedOptions['ssl'] = false;
        }
        else {
            $parsedOptions['ssl'] = true;
        }

        return $parsedOptions;

    }

    /**
     * Validate the parsed options
     * 
     * @param array $parsedOptions
     * @return boolean
     * @throws Exception
     */
    public function validateCommands($parsedOptions)
    {   
        $commands = $this->getCommands();
        
        //Check the end point is valid in the commands array
        if (!in_array($parsedOptions["endpoint"], array_keys($commands))) {
            throw new Exception("{$parsedOptions["endpoint"]} is not a valid endpoint", 1);        
        }
        //Check the command is valid in the endpoint array
        if (!in_array($parsedOptions["command"], array_keys($commands[$parsedOptions["endpoint"]]))) {
            throw new Exception("{$parsedOptions["command"]} is not valid for the {$parsedOptions["endpoint"]} end point", 1);
        }
        
        return true;

    }

    /**
     * Get the object for target endpoint
     * 
     * @param string $endpoint
     * @param DataSift_User $user
     * @return object
     */
    public function getEndPointObject($endpoint, &$user)
    {
        $object = null;
        
        switch ($endpoint) {
            case 'pylon':
                $object = new DataSift_Pylon($user);
                break;

            case 'identity':
                $object = new DataSift_Account_Identity($user);
                break;
            
            case 'token':
                $object = new DataSift_Account_Identity_Token($user);
                break;
            
            case 'limit':
                $object = new DataSift_Account_Identity_Limit($user);
                break;

            default:
                throw new Exception($endpoint . " is not a valid endpoint", 1); 
        }

        return $object;
    }

    /**
     * Get the parameters for the call
     * 
     * @param array $allowableParams
     * @param array $parameters
     * @param DataSift_User $user
     * @return array
     * @throws Exception
     */
    public function getCallParameters($allowableParams, $parameters, &$user)
    {    
        $returnedParams = array();

        foreach ($allowableParams as $paramName => $required) {    
            if ($paramName == "user") {
                $returnedParams[] = $user;    
            } elseif ($required && !isset($parameters[$paramName])) {
                //Check if parameter is required
                throw new Exception("$paramName is a required parameter for this command", 1);
            } elseif (isset($parameters[$paramName])) {
                $returnedParams[] = $parameters[$paramName];
            }
        }

        return $returnedParams;
    }

    /**
     * Call the command required
     * 
     * @param object $object
     * @param string $command
     * @param array $params
     * @param DataSiftUser $user
     * @param boolean $visual
     * @return mixed
     */
    public function callCommand(&$object, $command, $params, &$user, $pretty = false)
    {
        if($command == 'list') {
            $command = 'getall';
        }

        @call_user_func_array(array(&$object, $command), $params);

        $response = $user->getLastResponse();

        if($pretty == false) {
            $response = json_encode($response);
        }

        return $response;
    }

    /**
     * Print the help
     */
    public function printHelp()
    {
        echo "\nThis CLI tool supports the following options:\n\n";
        
        foreach ($this->getOptions() as $option => $value) {
            echo "For $option use -{$value['short']} or --{$value['long']}\n";
        }
        
        echo "\nThis CLI tool supports the following commands:\n\n";
        
        foreach ($this->getCommands() as $endpoint => $commands) {    
            echo "This CLI supports the $endpoint endpoint with the following commands: ".implode(", ", array_keys($commands))."\n";
        }
    }
}

// Include the DataSift library
require dirname(__FILE__) . '/datasift.php';

try {
    $cli = new Cli($argv);
    $commands = $cli->getCommands();    
    $parsedOptions = $cli->parseInput();

    $cli->validateCommands($parsedOptions);

    //Create Datasift user
    $user = new DataSift_User(
        $parsedOptions['authenticate']['username'],
        $parsedOptions['authenticate']['api_key'], 
        $parsedOptions['ssl'],
        true,
        $parsedOptions['url']
    );

    $object = $cli->getEndPointObject($parsedOptions['endpoint'], $user);

    $command_parameters = $cli->getCallParameters(
        $commands[$parsedOptions['endpoint']][$parsedOptions['command']], 
        $parsedOptions['parameters'], 
        $user
    );

    //Output the last response
    print_r($cli->callCommand(
        $object, 
        $parsedOptions['command'], 
        $command_parameters, 
        $user, 
        $parsedOptions['pretty']
    ));
    echo "\n";
} catch (Exception $e) {
    echo get_class($e) . ": " . $e->getMessage() . "\n";

    $cli->printHelp();
}
