<?php

if (!defined('INTELICHAT_PLUGIN')) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

if(!class_exists("InteliChatAPI")) {

	class InteliChatAPI {

		const API_HOST = 'inteli.chat/app/api/api.php';

		private static function getDataAPI($apiKey, $mode) {
			$response = wp_remote_get('https://' . self::API_HOST . '?request=' . $mode . '&filter=facebook_bots&apikey=' . $apiKey,
				array('timeout'=> 120, 'httpversion' => '1.1', 'sslverify'=>false)
			);

			if(is_wp_error($response)) {
				return null;
			}

			return json_decode(wp_remote_retrieve_body($response), true);
		}

		public static function getBotList($apiKey) {
			return self::getDataAPI($apiKey, 'get_bots');
		}
	}

}
