<?php
/*
Plugin Name:  Intelichat Messenger Button
Plugin URI:   https://inteli.chat/en
Description:  An easy and simple way to add <b>Intelichat</b> chatbots to your Wordpress. This plugin enables direct configuration of a chatbot to be launched from a messenger button. <b>Intelichat</b> is a platform to easily create chatbots without programming.
Version:      1.0.0
Author:       Intelichat
Author URI:   http://www.qualitor.com.br/en
Author MAIL:  help@inteli.chat
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:  intelichat_messenger_button

Intelichat is a platform for creating chatbots and virtual assistants, which can be done by drawing flows without programming. 
Chatbots can be generated for use on the web or via Facebook Messenger.
It has several different resources, such as knowledge base for publication and distribution of information, interactive publication and qualifications, which support evaluations, questionnaires and surveys.
 
Intelichat Messenger Button is a free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
Intelichat Messenger Button is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with Intelichat Messenger Button. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
*/

if (!function_exists('add_action')) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

if (!defined('INTELICHAT_PLUGIN')) { define( 'INTELICHAT_PLUGIN', true); }
define( 'INTELICHAT_M_PLUGIN_VERSION', '1.0.0' );
define( 'INTELICHAT_M_PLUGIN_URL', plugin_dir_url(__FILE__));
define( 'INTELICHAT_M_PLUGIN_DIR', plugin_dir_path(__FILE__) );

require_once(INTELICHAT_M_PLUGIN_DIR . 'class/intelichat-api.class.php' );
require_once(INTELICHAT_M_PLUGIN_DIR . 'class/intelichat-messenger-button.class.php' );

register_activation_hook( __FILE__, array( 'InteliChatMessengerButton', 'plugin_activation'));
register_deactivation_hook( __FILE__, array('InteliChatMessengerButton', 'plugin_deactivation'));
register_uninstall_hook(__FILE__, array('InteliChatMessengerButton', 'plugin_uninstall'));

add_action('init', array('InteliChatMessengerButton', 'init'));
