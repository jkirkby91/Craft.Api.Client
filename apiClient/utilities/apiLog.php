<?php

	namespace apiClient\Utilities;

	/**
	 * Class logger
	 * @author james@smackagency.com
	 */
	class apiLog extends \CLogger
	{

		/**
		 * logs an api event
		 * @param $exception
		 * @param $severity
		 * @param $force
		 * @param $category
		 * @param $plugin
		 */
		public function logEvent($exception, $severity, $force, $category, $plugin)
		{
			\Yii::getLogger()->log($exception, $severity, $force, $category, $plugin);
		}

	}