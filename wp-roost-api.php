<?php
/**
 * WP Roost API
 *
 * @package WP-Roost-API
 */

/* Exit if accessed directly. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/* Check if class exists. */
if ( ! class_exists( 'RoostAPI' ) ) {

	/**
	 * Roost API Class.
	 */
	class RoostAPI {

		/**
		 * APP Key.
		 *
		 * @var string
		 */
		static private $app_key;

		/**
		 * APP Secret.
		 *
		 * @var string
		 */
		static private $app_secret;


		/**
		 * __construct function.
		 *
		 * @access public
		 * @param mixed $app_key
		 * @param mixed $app_secret
		 * @return void
		 */
		public function __construct( $app_key, $app_secret ) {

			static::$appkey = $app_key;
			static::$app_secret = $app_secret;
		}

		/**
		 * roost_remote_request function.
		 *
		 * @access public
		 * @static
		 * @param mixed $remote_data
		 * @return void
		 */
		public static function roost_remote_request( $remote_data ) {
			$auth_creds = '';
			if ( ! empty( $remote_data['appkey'] ) ) {
				$auth_creds = 'Basic ' . base64_encode( $remote_data['appkey'] .':'.$remote_data['appsecret'] );
			}
			$remote_url = 'https://go.goroost.com/api/' . $remote_data['remoteAction'];

			$headers = array(
				'Authorization'  => $auth_creds,
				'Accept'       => 'application/json',
				'Content-Type'   => 'application/json',
				'Content-Length' => strlen( $remote_data['remoteContent'] ),
			);

			$remote_payload = array(
				'method'    => $remote_data['method'],
				'headers'   => $headers,
				'body'      => $remote_data['remoteContent'],
			);
			$response = wp_remote_request( esc_url_raw( $remote_url ), $remote_payload );
			return $response;
		}

		/**
		 * decode_data function.
		 *
		 * @access public
		 * @static
		 * @param mixed $remote_data
		 * @return void
		 */
		public static function decode_data( $remote_data ) {
			$xfer = self::roost_remote_request( $remote_data );
			$nxfer = wp_remote_retrieve_body( $xfer );
			$lxfer = json_decode( $nxfer, true );
			return $lxfer;
		}

		/**
		 * api_check function.
		 *
		 * @access public
		 * @static
		 * @return void
		 */
		public static function api_check() {
			$remote_data = array(
				'method' => 'GET',
				'remoteAction' => 'app',
				'appkey' => '',
				'appsecret' => '',
				'remoteContent' => '',
			);
			$response = self::roost_remote_request( $remote_data );
			return $response;
		}

		/**
		 * login function.
		 *
		 * @access public
		 * @static
		 * @param mixed $roost_user
		 * @param mixed $roost_pass
		 * @param mixed $roost_token
		 * @return void
		 */
		public static function login( $roost_user, $roost_pass, $roost_token ) {
			$remote_content = array(
				'username' => $roost_user,
				'password' => $roost_pass,
				'roost_token' => $roost_token,
			);
			$remote_data = array(
				'method' => 'POST',
				'remoteAction' => 'accounts/details',
				'appkey' => $roost_user,
				'appsecret' => $roost_pass,
				'roost_token' => $roost_token,
				'remoteContent' => json_encode( $remote_content ),
			);
			$response = self::decode_data( $remote_data );
			return $response;
		}

		/**
		 * get_server_settings function.
		 *
		 * @access public
		 * @static
		 * @param mixed $appKey
		 * @param mixed $appSecret
		 * @return void
		 */
		public static function get_server_settings( $appKey, $appSecret ) {
			$remote_data = array(
				'method' => 'POST',
				'remoteAction' => 'app',
				'appkey' => $appKey,
				'appsecret' => $appSecret,
				'remoteContent' => '',
			);
			$response = self::decode_data( $remote_data );
			return $response;
		}

		/**
		 * get_graph_data function.
		 *
		 * @access public
		 * @static
		 * @param mixed $app_key
		 * @param mixed $app_secret
		 * @param mixed $type
		 * @param mixed $range
		 * @param mixed $value
		 * @param mixed $offset
		 * @return void
		 */
		public static function get_graph_data( $app_key, $app_secret, $type, $range, $value, $offset ) {
			$remote_data = array (
				'method' => 'POST',
				'remoteAction' => 'stats/graph?type=' . $type . '&range=' . $range . '&value=' . $value . '&tzOffset=' . $offset,
				'appkey' => $app_key,
				'appsecret' => $app_secret,
				'remoteContent' => '',
			);
			$response = self::decode_data( $remote_data );
			return $response;
		}

		/**
		 * get_stats function.
		 *
		 * @access public
		 * @static
		 * @param mixed $app_key
		 * @param mixed $app_secret
		 * @return void
		 */
		public static function get_stats( $app_key, $app_secret ) {
			$remote_data = array (
				'method' => 'POST',
				'remoteAction' => 'stats/app',
				'appkey' => $app_key,
				'appsecret' => $app_secret,
				'remoteContent' => '',
			);
			$response = self::decode_data( $remote_data );
			return $response;
		}

		/**
		 * save_remote_settings function.
		 *
		 * @access public
		 * @static
		 * @param mixed $app_key
		 * @param mixed $app_secret
		 * @param mixed $data
		 * @return void
		 */
		public static function save_remote_settings( $app_key, $app_secret, $data ) {
			if ( ! empty( $data['website_url'] ) ) {
				$remote_content['serviceWorkerHostPath'] = $data['html_url'];
				$remote_content['serviceWorkerRelativePath'] = $data['worker_url'];
				$remote_content['manifestRelativePath'] = $data['manifest_url'];
				$remote_content['websiteURL'] = $data['website_url'];
			}

			if ( ! empty( $data['bell_state'] ) ) {
				$remote_content['bellState'] = $data['bell_state'];
			}

			if ( ! empty( $remote_content ) ) {
				$remote_data = array(
					'method' => 'PUT',
					'remoteAction' => 'app',
					'appkey' => $app_key,
					'appsecret' => $app_secret,
					'remoteContent' => json_encode( $remote_content ),
				);
				self::roost_remote_request( $remote_data );
			}
		}

		/**
		 * send_notification function.
		 *
		 * @access public
		 * @static
		 * @param mixed $alert
		 * @param mixed $url
		 * @param mixed $image_url
		 * @param mixed $app_key
		 * @param mixed $app_secret
		 * @param mixed $device_tokens
		 * @param mixed $segments
		 * @return void
		 */
		public static function send_notification( $alert, $url, $image_url, $app_key, $app_secret, $device_tokens, $segments ) {
			$alert = Roost::filter_string( $alert );
			$remote_content = array(
				'alert' => $alert,
			);
			if ( null === $remote_content['alert'] ) {
				$remote_content['alert'] = '';
			}
			if ( $url ) {
				$remote_content['url'] = $url;
			}
			if ( $image_url ) {
				$remote_content['imageURL'] = $image_url;
			}
			if ( $device_tokens ) {
				$remote_content['device_tokens'] = $device_tokens;
			}
			if ( $segments ) {
				$remote_content['segments'] = $segments;
			}
			$remote_data = array(
				'method' => 'POST',
				'remoteAction' => 'push',
				'appkey' => $app_key,
				'appsecret' => $app_secret,
				'remoteContent' => json_encode($remote_content),
			);
			$response = self::decode_data( $remote_data );
			return $response;
		}


	}
}
