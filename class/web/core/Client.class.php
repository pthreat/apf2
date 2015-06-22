<?php

	namespace apf\web\core{

		class Client{

			public static function getIp(){

				return $_SERVER["REMOTE_ADDR"];

			}

			public static function getHost(){

				return $_SERVER["REMOTE_HOST"];

			}

			public static function getBrowser(){

				return $_SERVER["HTTP_USER_AGENT"];

			}

			public static function getReferer(){

				return $_SERVER["HTTP_REFERER"];

			}

		}

	}

?>
