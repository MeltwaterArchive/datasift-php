<?php
/**
 * DataSift client
 *
 * The DataSift_ApiClient class wraps access to the DataSift API.
 *
 * This software is the intellectual property of MediaSift Ltd., and is covered
 * by retained intellectual property rights, including copyright.
 *
 * @category  DataSift
 * @package   PHP-client
 * @author    Stuart Dallas <stuart@3ft9.com>
 * @copyright 2011 MediaSift Ltd.
 * @license   http://www.debian.org/misc/bsd.license BSD License (3 Clause)
 * @link      http://www.mediasift.com
 */

class DataSift_ApiClient
{
    const HTTP_OK = 200;
    const HTTP_CREATED = 201;
    const HTTP_NO_CONTENT = 204;
    const HTTP_NOT_FOUND = 404;
    const HTTP_CONFLICT = 409;
    const HTTP_GONE = 410;
    
    /**
     * Make a call to a DataSift API endpoint.
     *
     * @param DataSift_User $user          The user's username.
     * @param string        $endPoint      The endpoint of the API call.
     * @param array         $headers       The headers to be sent.
     * @param array         $successCode   The codes defined as a success for the call.
     * @param array         $params        The parameters to be passed along with the request.
     * @param string        $userAgent     The HTTP User-Agent header.
     *
     * @return array The response from the server.
     * @throws DataSift_Exception_APIError
     * @throws DataSift_Exception_RateLimitExceeded
     * @throws DataSift_Exception_NotYetImplemented
     */
    static public function call(
        DataSift_User $user, 
        $endPoint,
        $method,
        $params = array(),
        $headers = array(),
        $userAgent = DataSift_User::USER_AGENT,
        $qs = array(),
        $ingest = false
    ) {
        $decodeCode = array(
            self::HTTP_OK, self::HTTP_NO_CONTENT
        );
        
        // Curl is required
        if (!function_exists('curl_init')) {
            throw new DataSift_Exception_NotYetImplemented('Curl is required for DataSift_ApiClient');
        }

        if (empty($headers)) {
            $headers = array(
                'Auth: '.$user->getUsername().':'.$user->getAPIKey(), 
                'Expect:', 'Content-Type: application/json'
            );
        }

        $ssl = $user->useSSL();

        // Build the full endpoint URL
        if ($ingest) {
            $url = 'http'.($ssl ? 's' : '').'://'.$user->getIngestUrl() . $endPoint;
        }
        else {
            $url = 'http'.($ssl ? 's' : '').'://'.$user->getApiUrl(). $user->getApiVersion() . '/'. $endPoint;
        }

        $ch = self::initialize($method, $ssl, $url, $headers, $params, $userAgent, $qs, $ingest);
        
        $res = curl_exec($ch);
        $info = curl_getinfo($ch);

        curl_close($ch);
        $res = self::parseHTTPResponse($res);

        if ($user->getDebug()) {
            $log['headers'] = $res['headers'];
            $log['status'] = $info['http_code'];
            $log['body'] = self::decodeBody($res);
            $user->setLastResponse($log);
        }

        $retval = array(
            'response_code'        => $info['http_code'],
            'data'                 => (strlen($res['body']) == 0 ? array() : self::decodeBody($res)),
            'rate_limit'           => (isset($res['headers']['x-ratelimit-limit']) ? $res['headers']['x-ratelimit-limit'] : -1),
            'rate_limit_remaining' => (isset($res['headers']['x-ratelimit-remaining']) ? $res['headers']['x-ratelimit-remaining'] : -1),
        );

        return $retval;
    }
    
    /**
     * Initalize the cURL connection.
     * 
     * @param string    $method    The HTTP method to use.
     * @param boolean   $ssl       Is SSL Enabled.
     * @param string    $url       The URL of the call.
     * @param array     $headers   The headers to be sent.
     * @param array     $params    The parameters to be passed along with the request.
     * @param string    $userAgent The HTTP User-Agent header.
     * 
     * @return resource The cURL resource
     * @throws DataSift_Exception_NotYetImplemented
     */
    static private function initialize($method, $ssl, $url, $headers, $params, $userAgent, $qs, $raw = false)
    {
        $ch = curl_init();

        switch (strtolower($method)) {
            case 'post': {
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, ($raw ? $params : json_encode($params)));
                break;
            }
        
            case 'get': {
                curl_setopt($ch, CURLOPT_HTTPGET, true);
                break;
            }
            
            case 'put': {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
                break;
            }
            
            case 'delete': {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
            }
            
            default: {
                throw new DataSift_Exception_NotYetImplemented('Method not of valid type');
            }
        }

        $url = self::appendQueryString($url, $qs);

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
        
        if($ssl) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); 
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_SSLVERSION, 'CURL_SSLVERSION_TLSv1_2');
        }
        
        return $ch;
    }

    static public function appendQueryString($url, $qs)
    {
        if(count($qs) > 0) {
            return $url . '?' . http_build_query($qs);
        }
        
        return $url;
    }

    /**
     * Decode the JSON response depending on the format.
     *
     * @param array $res The parsed HTTP response.
     *
     * @return array An array of the decoded JSON response
    */
    static protected function decodeBody(array $res)
    {
        $format = isset($res['headers']['x-datasift-format']) ? $res['headers']['x-datasift-format'] : $res['headers']['content-type'];
        $retval = array();

        if (strtolower($format) == 'json_new_line') {
            foreach (explode("\n", $res['body']) as $json_string) {
                $retval[] = json_decode($json_string, true);
            }
        } else {
            $retval = json_decode($res['body'], true);
        }

        return $retval;
    }

    /**
     * Parse an HTTP response. Separates the headers from the body and puts
     * the headers into an associative array.
     *
     * @param string $str The HTTP response to be parsed.
     *
     * @return array An array containing headers => array(header => value), and body.
     */
    static private function parseHTTPResponse($str)
    {
        $retval = array(
            'headers' => array(),
            'body'    => '',
        );
        $lastfield = false;
        $fields    = explode("\n", preg_replace('/\x0D\x0A[\x09\x20]+/', ' ', $str));
        foreach ($fields as $field) {
                if (strlen(trim($field)) == 0) {
                    $lastfield = ':body';
                } elseif ($lastfield == ':body') {
                    $retval['body'] .= $field."\n";
                } else {
                    if (($field[0] == ' ' or $field[0] == "\t") and $lastfield !== false) {
                        $retval['headers'][$lastfield] .= ' '.$field;
                    } elseif (preg_match('/([^:]+): (.+)/m', $field, $match)) {
                        $match[1] = strtolower($match[1]);
                        if (isset($retval['headers'][$match[1]])) {
                            if (is_array($retval['headers'][$match[1]])) {
                                $retval['headers'][$match[1]][] = $match[2];
                            } else {
                                $retval['headers'][$match[1]] = array($retval['headers'][$match[1]], $match[2]);
                            }
                        } else {
                            $retval['headers'][$match[1]] = trim($match[2]);
                        }
                    }
                }
        }
        return $retval;
    }
}
