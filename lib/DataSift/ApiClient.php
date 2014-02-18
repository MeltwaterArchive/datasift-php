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

/**
 * The DataSift_ApiClient class wraps access to the DataSift API.
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
	/**
	 * Make a call to a DataSift API endpoint.
	 *
	 * @param string $username The user's username.
	 * @param string $api_key  The user's API key.
	 * @param string $endpoint The endpoint of the API call.
	 * @param array  $params   The parameters to be passed along with the request.
	 * @param string $user_agent The HTTP User-Agent header.
	 *
	 * @return array The response from the server.
	 * @throws DataSift_Exception_APIError
	 * @throws DataSift_Exception_RateLimitExceeded
	 */
	static public function call($user, $endpoint, $params = array(), $user_agent = DataSift_User::USER_AGENT)
	{
		// Curl is required
		if (!function_exists('curl_init')) {
			throw new DataSift_Exception_NotYetImplemented('Curl is required for DataSift_ApiClient');
		}

		// Build the full endpoint URL
		$url = 'http'.($user->useSSL() ? 's' : '').'://'.DataSift_User::API_BASE_URL.$endpoint.'.json';

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Auth: '.$user->getUsername().':'.$user->getAPIKey(), 'Expect:'));
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
		$res = curl_exec($ch);
		$info = curl_getinfo($ch);

		if ($info['http_code'] != 204 && !$res) {
			throw new DataSift_Exception_APIError(curl_error($ch), curl_errno($ch));
		}

		curl_close($ch);
		$res = self::parseHTTPResponse($res);

		$retval = array(
			'response_code'        => $info['http_code'],
			'data'                 => (strlen($res['body']) == 0 ? array() : json_decode($res['body'], true)),
			'rate_limit'           => (isset($res['headers']['x-ratelimit-limit']) ? $res['headers']['x-ratelimit-limit'] : -1),
			'rate_limit_remaining' => (isset($res['headers']['x-ratelimit-remaining']) ? $res['headers']['x-ratelimit-remaining'] : -1),
		);

		if ($info['http_code'] != 204 && !$retval['data']) {
			throw new DataSift_Exception_APIError('Failed to decode the response', -1);
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
