<?php

	namespace Craft;

	use apiClient\Utilities\apiLog;
	use apiClient\Exceptions\HttpServerException;
	use apiClient\Exceptions\HttpServerException404;
	use apiClient\Exceptions\RestClientException;

	/**
	 * Class apiClientService
	 * @package Craft
	 * @author james@smackagency.com
	 */
	class apiClientService extends BaseApplicationComponent
	{
		public $curl;
		public $curl_options;
		public $response_object;
		public $response_info;

		private $apiLog;
		private $invoker;

		const CURlOPTS = 'Unable to set curl options';
		const GENERIC = 'Generic or multiple server errors';
		const CONNECTION = 'General connection to server error';
		const ENDPOINT = 'Resource not at this address';
		const NULL = 'Empty response from server';

		/**
		 * creates a curl resource and sets some global options
		 * @param $url
		 * @param $invokedBy
		 * @throws RestClientException
		 */
		function __construct($url, $invokedBy)
		{

			//create the logging obj
			$this->apiLog = new apiLog;

			//define who's using this client
			$this->invoker = $invokedBy;

			//create the curl resource
			$this->curl = curl_init($url);

			//set some global options
			$this->setOpts($curl_options = [
				CURLOPT_USERAGENT => 'SmackAgency API Client (v1.0)',
				CURLOPT_HEADER => false,
				CURLINFO_HEADER_OUT => true,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_FOLLOWLOCATION => true,
			]);
		}

		/**
		 * destroy the curl resource
		 */
		function __destruct()
		{
			curl_close($this->curl);
			unset($this->apiLog, $this->invoker);
		}

		/**
		 * Perform a GET call to server
		 *
		 * Additionally in $response_object and $response_info are the
		 * response from server and the response info as it is returned
		 * by curl_exec() and curl_getinfo() respectively.
		 *
		 * @return array
		 * @throws HttpServerException
		 * @throws HttpServerException404
		 * @throws RestClientException
		 */
		public function get()
		{
			$this->setOpts($curl_options = [
				CURLOPT_POST => false,
				CURLOPT_HTTPGET => true
			]);

			return $this->_http_parse_message(curl_exec($this->curl));
		}

		/**
		 * Perform a POST call to the server
		 *
		 * Additionally in $response_object and $response_info are the
		 * response from server and the response info as it is returned
		 * by curl_exec() and curl_getinfo() respectively.
		 *
		 * @param $payload
		 * @return array
		 * @throws HttpServerException
		 * @throws HttpServerException404
		 * @throws RestClientException
		 */
		public function post($payload)
		{
			$this->setOpts($curl_options = [
				CURLOPT_POST => 1,
				CURLOPT_POSTFIELDS => $payload
			]);

			return $this->_http_parse_message(curl_exec($this->curl));
		}

		/**
		 * Perform a PUT call to the server
		 *
		 * Additionally in $response_object and $response_info are the
		 * response from server and the response info as it is returned
		 * by curl_exec() and curl_getinfo() respectively.
		 *
		 * @param $payload
		 * @return array
		 * @throws HttpServerException
		 * @throws HttpServerException404
		 * @throws RestClientException
		 */
		public function put($payload)
		{
			$this->setOpts($curl_options = [
				CURLOPT_CUSTOMREQUEST => 'PUT',
				CURLOPT_POSTFIELDS => $payload
			]);

			return $this->_http_parse_message(curl_exec($this->curl));
		}

		/**
		 * Perform a DELETE call to server
		 *
		 * Additionally in $response_object and $response_info are the
		 * response from server and the response info as it is returned
		 * by curl_exec() and curl_getinfo() respectively.
		 *
		 * @param $payload
		 * @return array
		 * @throws HttpServerException
		 * @throws HttpServerException404
		 * @throws RestClientException
		 */
		public function delete($payload)
		{
			$this->setOpts($curl_options = [
				CURLOPT_CUSTOMREQUEST => 'DELETE',
				CURLOPT_POSTFIELDS => $payload
			]);

			return $this->_http_parse_message(curl_exec($this->curl));
		}

		/**
		 * sets curl options
		 * @param array $curl_options
		 * @return bool
		 * @throws RestClientException
		 */
		public function setOpts(array $curl_options)
		{
			if (!curl_setopt_array($this->curl, $curl_options)) {
				$exception = new RestClientException("Error setting cURL request options.");
				$this->apiLog->logEvent($exception, $severity = E_ERROR, $force = true, $category = self::CURlOPTS,
					$plugin = $this->invoker);
				throw $exception;
			} else {
				return true;
			}
		}

		/**
		 * parse http server responses from api
		 * @param $api_response
		 * @return array
		 * @throws HttpServerException
		 * @throws HttpServerException404
		 */
		private function _http_parse_message($api_response)
		{
			if (!$api_response) {
				$exception = new HttpServerException(curl_error($this->curl), -1);
				$this->apiLog->logEvent($exception, $severity = E_ERROR, $force = true, $category = self::NULL,
					$plugin = $this->invoker);
				throw $exception;
			}
			$this->response_info = curl_getinfo($this->curl);
			$code = $this->response_info['http_code'];
			if ($code === 404) {
				$exception = new HttpServerException404(curl_error($this->curl));
				$this->apiLog->logEvent($exception, $severity = E_ERROR, $force = true, $category = self::ENDPOINT,
					$plugin = $this->invoker);
				throw $exception;
			} elseif ($code >= 400 && $code <= 600) {
				$exception = new HttpServerException('Server response status was: ' . $code .
					' with response: [' . $api_response . ']', $code);
				$this->apiLog->logEvent($exception, $severity = E_ERROR, $force = true, $category = self::CONNECTION,
					$plugin = $this->invoker);
				throw $exception;
			} elseif (!in_array($code, range(200, 207), true)) {
				$exception = new HttpServerException('Server response status was: ' . $code .
					' with response: [' . $api_response . ']', $code);
				$this->apiLog->logEvent($exception, $severity = E_ERROR, $force = true, $category = self::GENERIC,
					$plugin = $this->invoker);
				throw $exception;
			} else {
				return array(
					'api_response' => $api_response,
					'server_status' => $this->response_info[ 'http_code' ]
				);
			}
		}
	}