<?php
/**
 * Connection Library Wilapp
 *
 * @package    WordPress
 * @author     David Perez <david@closemarketing.es>
 * @copyright  2020 Closemarketing
 * @version    1.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Wilapp.
 *
 * Connnector to Wilapp.
 *
 * @since 1.0
 */
class Helpers_Wilapp {
	/**
	 * POSTS API from Wilapp
	 *
	 * @param string $apikey API Key.
	 * @param string $module Module.
	 * @param string $query Query.
	 * @return array
	 */
	public function api( $username, $password, $endpoint, $method = 'GET', $query = array() ) {
		if ( ! $username && ! $password ) {
			return array(
				'status' => 'error',
				'data'   => 'No credentials',
			);
		}
		$args = array(
			'method'  => $method,
			'timeout' => 30,
			'body'    => array(
				'email'    => $username,
				'password' => $password,
			)
		);
		if ( ! empty( $query ) ) {
			$args['body'] = array_merge( $args['body'], $query );
		}
		$url         = 'https://api.wilapp.com/v1/' . $endpoint;
		$result      = wp_remote_request( $url, $args );
		$result_body = wp_remote_retrieve_body( $result );
		$body        = json_decode( $result_body, true );

		if ( isset( $body['status'] ) && 400 == $body['status'] ) {
			return array(
				'status' => 'error',
				'data'   => isset( $body['message'] ) ? $body['message'] : '',
			);
		} else {
			return array(
				'status' => 'ok',
				'data'   => isset( $body ) ? $body : '',
			);
		}
	}

	/**
	 * Login settings
	 *
	 * @param array $settings
	 * @return void
	 */
	public function login( $username = '', $password = '' ) {
		if ( empty( $username ) || empty( $password ) ) {
			$settings = get_option( 'wilapp_options' );
			$username = isset( $settings['username'] ) ? $settings['username'] : '';
			$password = isset( $settings['password'] ) ? $settings['password'] : '';
		}

		return $this->api( $username, $password, 'user/login', 'POST' );
	}

	/**
	 * Creates and generates PDF for signature
	 *
	 * @param string $template
	 * @return void
	 */
	public function create_entry( $template_id, $merge_vars, $add_header = false ) {
		

		return false;
	}
}

$helpers_wilapp = new Helpers_Wilapp();