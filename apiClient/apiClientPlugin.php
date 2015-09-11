<?php
	namespace Craft;

	/**
	 * Class apiClientPlugin
	 * @package Craft
	 * @author james@smackagency.com
	 */

	class apiClientPlugin extends BasePlugin
	{

		public function init()
		{
			require_once __DIR__ .'/vendor/autoload.php';
		}

		public function getName()
		{
			return 'apiClient';
		}

		public function getVersion()
		{
			return '1.0.0';
		}

		public function getDeveloper()
		{
			return 'SMACK';
		}

		public function getDeveloperUrl()
		{
			return 'http://smackagency.com';
		}

		public function hasCpSection()
		{
			return false;
		}
	}
