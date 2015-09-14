<?php


	namespace apiClient\Exceptions;

	/**
	 * Class HttpServerException404
	 * @package Craft
	 * @author james@smackagency.com
	 */
	class HttpServerException404 extends \CException {
		function __construct($message = 'Not Found') {
			parent::__construct($message, 404);
		}
	}
