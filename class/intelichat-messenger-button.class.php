<?php
if (!defined('INTELICHAT_M_PLUGIN_VERSION')) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

if(!class_exists("InteliChatMessengerButton")) {

	class InteliChatMessengerButton {
		
		private static $initiated = false;
		private static $apiKey;
		private static $acceptTerms;
		private static $windowOptions;
		private static $sysOptions = null;
		
		public static function plugin_activation() {
		}
		
		public static function plugin_deactivation() {
		}
		
		public static function plugin_uninstall() {
			delete_option('icw_m_acceptTerms');
			delete_option('icw_m_apiKey');
			delete_option('icw_m_windowSettings');
		}
		
		public static function init()
		{
			if (self::$initiated) { return; }

			self::$initiated = true;
			self::$apiKey = get_option('icw_m_apiKey');
			self::$acceptTerms = get_option('icw_m_acceptTerms');
			
			self::$windowOptions = get_option('icw_m_windowSettings');
			if(!is_array(self::$windowOptions)) { self::$windowOptions = array(
				'ref'=>'',
				'logged_in_greeting'=>'',
				'logged_out_greeting'=>'',
				'greeting_dialog_display'=>'',
				'caption'=>'Intelichat Messenger Button',
				'm_bot'=>array('id'=>null,'url'=>null));
			}
			else {
				if(!isset(self::$windowOptions['m_bot']['id'], self::$windowOptions['m_bot']['url'])) {
					self::$windowOptions['m_bot'] = array('id'=>null,'url'=>null);
				}
			}

			// Add the page to the admin menu
			add_action( 'admin_menu', array('InteliChatMessengerButton', 'add_menu_page'));

			// Register page options
			add_action( 'admin_init', array('InteliChatMessengerButton', 'register_page_options'));

			if(!is_admin() && self::$acceptTerms && !empty(self::$windowOptions['m_bot']['url'])) {
				add_action('wp_footer', array('InteliChatMessengerButton', 'print_footer'));
				add_action('wp_head', array('IntelichatMessengerButton', 'print_header'));
			}
		}

		public static function validate_apiKey($field) {
			return $field;
		}

		public static function print_header() {

			print '<script> window.fbAsyncInit = function() { ';
			print '   FB.init({ ';
			print '       appID : "'.self::$windowOptions['m_bot']['app_id'].'" ,';
			print '       autoLogAppEvents: true, ';
			print '       xfbml : true, ';
			print '       version: "v2.11"   });  }; ';
			print '  (function(d, s, id) { ';
			print '       var js, fjs = d.getElementsByTagName(s)[0]; ';
			print '       if (d.getElementById(id)) {return;} ';
			print '       js = d.createElement(s); js.id = id; ';
			print '       js.src = "https://connect.facebook.net/en_US/sdk/xfbml.customerchat.js"; ';
			print '       fjs.parentNode.insertBefore(js, fjs); ';
			print '   }(document, "script", "facebook-jssdk")); ';
			print '</script>';
		}

                public static function print_footer() {

                        print '<div class="fb-customerchat" ';
			print 'page_id="'.self::$windowOptions['m_bot']['page_id'].'" ';
			print 'minimized="true" ';
			print 'theme_color="#FFFFFF" ';
			print 'ref="'.self::$windowOptions['ref'].'" ';
			print 'logged_in_greeting="'.self::$windowOptions['logged_in_greeting'].'" ';
			print 'logged_out_greeting="'.self::$windowOptions['logged_out_greeting'].'" ';
			print 'greeting_dialog_display="'.self::$windowOptions['greeting_dialog_display'].'" ';
			print '></div>';
                }

		public static function validate_windowSettings($fields) {
			$valid_fields = array();

                        if(!is_array($valid_fields['m_bot'])) {
				if($fields['m_bot'] == "0") {
					$valid_fields['m_bot'] = array('id' => 0, 'url' => '');
				} else {
					$valid_fields['m_bot'] = json_decode(str_replace("'",'"', $valid_fields['m_bot']), true);
					$x = str_replace("'", '"', $fields['m_bot']);
					$j = json_decode($x, true);
					$valid_fields['m_bot'] = $j;
				}
                        }
                        if(!isset($valid_fields['m_bot']['id'], $valid_fields['m_bot']['url'])) {
                                add_settings_error( 'icw_m_settings', 'icw_m_bot_error', 'Invalid value for <b>Bot</b>', 'error' );
                                $valid_fields['m_bot'] = self::$windowOptions['m_bot'];
                        }
			$valid_fields['ref'] = strip_tags( stripslashes( trim( $fields['ref']) ) );
			
			$valid_fields['logged_in_greeting'] = strip_tags( stripslashes( trim( $fields['logged_in_greeting']) ) );
			$valid_fields['logged_out_greeting'] = strip_tags( stripslashes( trim( $fields['logged_out_greeting']) ) );
			$valid_fields['greeting_dialog_display'] = strip_tags( stripslashes( trim( $fields['greeting_dialog_display']) ) );
			$valid_fields['greeting_dialog_display'] = strip_tags( stripslashes( trim( $fields['greeting_dialog_display']) ) );

			return $valid_fields;
		}

		private static function get_image_properties($id, $defaultSrc, $size) {
			$sucess = false;
			$src = $defaultSrc;
			if (!empty($id)) {
				$image_attributes = wp_get_attachment_image_src($id, $size);
				$src = $image_attributes[0];
				$value = $options[$name];
				$sucess = true;
			}
			return array('id'=>$id, 'default'=>$defaultSrc, 'src'=>$src, 'sucess'=>$sucess);
		}

		/**
		 * Function that will add the options page under Setting Menu.
		 */

		public static function add_menu_page()
		{
			add_menu_page(
				'Intelichat Messenger Button Settings', // $page_title
				'Intelichat Messenger Button', // $menu_title
				'manage_options', // $capability
				'intelichat_icw_m_settings', // $menu_slug
				array('InteliChatMessengerButton', 'create_options_html'), // $function
				INTELICHAT_M_PLUGIN_URL . 'public/images/icon.png', // $icon_url
				20 // $position
			);
		}

		public static function display_section() {
		}

		private static function updateSysOptions() {

			if(self::$sysOptions != null) { return; }

			self::$sysOptions = get_option('icw_m_options');
			if(self::$sysOptions == null) { self::$sysOptions == array(); }

			if(!empty(self::$apiKey)) {

				$botList = InteliChatAPI::getBotList(self::$apiKey);
				if($botList != null)
				{
					if(isset($botList['error']))
					{
						// key inválida
						$botList = null;
					}
					self::$sysOptions['botList'] = array('time'=>date('d/m/Y H:i'), 'values'=>$botList);
				}
				else
				{
					// falha ao atualizar lista
					self::$sysOptions['botList']['update_error'] = true;
				}
			}
			else {
				self::$sysOptions['botList'] = array('time'=>date('d/m/Y H:i'));
			}
			update_option('icw_m_options', self::$sysOptions);
		}
		
		public static function create_options_html() {    
			print '<div class="wrap intelichat_settings">';
		?>
			<img height="70px" style="margin-bottom:10px;" src="<?=INTELICHAT_M_PLUGIN_URL . 'public/images/logo.jpg'?>"/>
			<form method="post" action="options.php">
			<?php
				if(!self::$acceptTerms) {
					echo '<h2>Terms and conditions</h2>';
					if(file_exists(INTELICHAT_M_PLUGIN_DIR . 'admin/terms.txt')) {
						$file = fopen (INTELICHAT_M_PLUGIN_DIR . 'admin/terms.txt', 'r');
						while(!feof($file)) {
							echo fgets($file, 1024) . '<br />';
						}
						fclose($file);
					}
					settings_fields('icw_m_terms');
					echo '<br><input type="checkbox" id="icw_m_acceptTerms" name="icw_m_acceptTerms" required /><label for="icw_m_acceptTerms" style="color:#55A;font-weight:bold;">I accept the terms and conditions</label>';
					submit_button('Continue');
				}
				else {
					$page = $_GET['page'];
					if(isset($_GET['tab'])) { $active_tab = $_GET['tab']; }
					else { $active_tab = empty(self::$apiKey) ? 'icw_m_credentials' : 'icw_m_general'; }
					?>

					<h2 class="nav-tab-wrapper" style="margin-bottom:10px;">  
						<a href="?page=<?=$page?>&tab=icw_m_credentials" class="nav-tab <?php echo $active_tab == 'icw_m_credentials' ? 'nav-tab-active' : ''; ?>">Intelichat API Key</a>
						<a href="?page=<?=$page?>&tab=icw_m_general" class="nav-tab <?php echo $active_tab == 'icw_m_general' ? 'nav-tab-active' : ''; ?>">Configuration</a>
					</h2>

					<?php
					self::updateSysOptions();
					settings_errors();
					if( $active_tab == 'icw_m_general' ) {
						settings_fields('icw_m_settings');      
						do_settings_sections('icw_m_settings');
					}
					else {
						settings_fields('icw_m_credentials');      
						do_settings_sections('icw_m_credentials');
						echo "<i>Note: The Intelichat API Key is found in the ‘My profile’ menu of the administration section of Intelichat (upper right menu, with the avatar). <br></i>";
					}

					if(isset(self::$sysOptions['botList']['update_error'])) {
						if(!isset(self::$sysOptions['botList']['values'])) { echo '<div class="ic_error">* This API Key appears to be invalid</div>'; }
						echo '<div class="ic_error">* Sorry, could not update the Bot list...';
						if(isset(self::$sysOptions['botList']['time'])) { echo '<br>* Last update: ' . self::$sysOptions['botList']['time']; }
						echo '</div>';
					}
					else if(!isset(self::$sysOptions['botList']['values'])) { echo '<div class="ic_error">* This API Key is invalid</div>'; }

					submit_button();
				}
			?>
			</form>
		</div> <!-- /wrap -->
		<?php 
		}
		/**
		 * Function that will register admin page options.
		 */
		public static function register_page_options() { 
			register_setting('icw_m_terms', 'icw_m_acceptTerms', array('type'=>'boolean', 'default'=>false));

			add_settings_section('icw_m_credentials_section', 'Intelichat API key', array('InteliChatMessengerButton', 'display_section' ), 'icw_m_credentials'); // id, title, display cb, page
			add_settings_field( 'icw_m_apiKey_field', 'API Key', array('InteliChatMessengerButton', 'icw_m_apiKey_settings_field' ), 'icw_m_credentials', 'icw_m_credentials_section' ); // id, title, display cb, page, section
			register_setting('icw_m_credentials', 'icw_m_apiKey', array('sanitize_callback'=>array('InteliChatMessengerButton', 'validate_apiKey'))); // option group, option name, sanitize cb

			// Add Section for option fields
			add_settings_section('icw_m_window_settings_section', 'Configuration', array('InteliChatMessengerButton', 'display_section' ), 'icw_m_settings'); // id, title, display cb, page

			// Add Title Field
			add_settings_field( 'icw_m_bot_field', 'Bot', array('InteliChatMessengerButton', 'bot_settings_field' ), 'icw_m_settings', 'icw_m_window_settings_section' );
			add_settings_field( 'icw_m_ref_field', 'Reference', array('InteliChatMessengerButton', 'ref_settings_field' ), 'icw_m_settings', 'icw_m_window_settings_section' );
			add_settings_field( 'icw_m_logged_in_greeting_field', 'Logged in greetings', array('InteliChatMessengerButton', 'logged_in_greeting_field' ), 'icw_m_settings', 'icw_m_window_settings_section' ); // id, title, display cb, page, section
			add_settings_field( 'icw_m_logged_out_greeting_field', 'Logged out greetings', array('InteliChatMessengerButton', 'logged_out_greeting_field' ), 'icw_m_settings', 'icw_m_window_settings_section' ); // id, title, display cb, page, section
			add_settings_field( 'icw_m_greeting_dialog_display_field', 'Greetings display format', array('InteliChatMessengerButton', 'greeting_dialog_display_field' ), 'icw_m_settings', 'icw_m_window_settings_section' ); // id, title, display cb, page, section
/*
			add_settings_field( 'icw_window_size_field', 'Window size', array('InteliChatMessengerButton', 'window_size_settings_field' ), 'icw_settings', 'icw_window_settings_section' ); // id, title, display cb, page, section
			add_settings_field( 'icw_buttom_size_field', 'Web Button size', array('InteliChatMessengerButton', 'buttom_size_settings_field' ), 'icw_settings', 'icw_window_settings_section' ); // id, title, display cb, page, section
			add_settings_field( 'icw_buttom_field', 'Web Button', array('InteliChatMessengerButton', 'button_settings_field' ), 'icw_settings', 'icw_window_settings_section' ); // id, title, display cb, page, section
*/
			register_setting('icw_m_settings', 'icw_m_windowSettings', array('sanitize_callback'=>array('InteliChatMessengerButton', 'validate_windowSettings'))); // option group, option name, sanitize cb 
		}

		/**
		 * Functions that display the fields.
		 */
		public static function bot_settings_field() { $find = false;

$vazio = false;
if(empty(self::$sysOptions['botList']['values']))
{ 
  $vazio = true;
}
?>
			<select name="icw_m_windowSettings[m_bot]" >
				<option value='0'>[Nenhum]</option>
				<?php
				if(isset(self::$sysOptions['botList']['values'])) { 
					foreach(self::$sysOptions['botList']['values'] as $value) { ?>
					<option value="{'id':'<?=$value['id']?>','url':'<?=$value['url']?>','page_id':'<?=$value['page_id']?>','app_id':'<?=$value['app_id']?>'}" 

					<?php if(self::$windowOptions['m_bot']['id'] == $value['id']) { $find = true; echo ' selected'; } ?>><?=$value['botname']?>

					</option>
				<?php } } ?>
				<?php if(!$find && !empty(self::$windowOptions['m_bot']['url'])) { ?>
				<?php } ?>
			</select>
			<br>
			<i>Tip: do not forget to authorize your website's domain in Intelichat BOT Facebook publishing options...</i>
			<?php
		}

                public static function greeting_dialog_display_field() { ?>
			<?php if(isset(self::$windowOptions['greeting_dialog_display'])) { ?>
                        <select name="icw_m_windowSettings[greeting_dialog_display]">
                                <option value="show" <?=self::$windowOptions['greeting_dialog_display'] == 'show' ? 'selected' : ''?>>Always show</option>
                                <option value="fade" <?=self::$windowOptions['greeting_dialog_display'] == 'fade' ? 'selected' : ''?>>Fade</option>
                                <option value="hidden" <?=self::$windowOptions['greeting_dialog_display'] == 'hidden' ? 'selected' : ''?>>Hidden</option>
                        </select> 
			<?php } else { ?>
                        <select name="icw_m_windowSettings[greeting_dialog_display]">
                                <option value="show" selected >Always show</option>
                                <option value="fade">Fade</option>
                                <option value="hidden">Hidden</option>
                        </select>

		<?php
			}
                }


		public static function ref_settings_field() {
			if(isset( self::$windowOptions['ref'] ))
			{
				echo '<input type="text" name="icw_m_windowSettings[ref]" value="' . self::$windowOptions['ref'] . '" />';
			} else {
				echo '<input type="text" name="icw_m_windowSettings[ref]" value="" />';
			}
		}

                public static function logged_in_greeting_field() {
			if(isset( self::$windowOptions['logged_in_greeting'] ))
			{
                        	echo '<input type="text" size=80 maxlength=80 name="icw_m_windowSettings[logged_in_greeting]" value="' . self::$windowOptions['logged_in_greeting'] . '" />';
			} else {
				echo '<input type="text" size=80 maxlength=80 name="icw_m_windowSettings[logged_in_greeting]" value="" />';
			}
                }

                public static function logged_out_greeting_field() {
			if(isset(self::$windowOptions['logged_out_greeting'] ))
			{
                        	echo '<input type="text" size=80 maxlength=80 name="icw_m_windowSettings[logged_out_greeting]" value="' . self::$windowOptions['logged_out_greeting'] . '" />';
			} else {
				echo '<input type="text" size=80 maxlength=80 name="icw_m_windowSettings[logged_out_greeting]" value="" />';
			}
                }



		public static function icw_m_apiKey_settings_field() {
			echo '<input type="text" name="icw_m_apiKey" style="width: 360px !important;" value="' . self::$apiKey . '" />';
		}
	}
}
