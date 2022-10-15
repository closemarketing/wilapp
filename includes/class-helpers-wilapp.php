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
	public function api_post( $username, $password, $action, $query = array() ) {
		if ( ! $username && ! $password ) {
			return array(
				'status' => 'error',
				'data'   => 'No credentials',
			);
		}
		$args     = array(
			'timeout' => 120,
			'body'    => array(
				'action'   => $action,
				'usuario'  => $username,
				'password' => $password,
			)
		);
		if ( ! empty( $query ) ) {
			$args['body'] = array_merge( $args['body'], $query );
		}
		$result      = wp_remote_post( 'https://app.firmafy.com/ApplicationProgrammingInterface.php', $args );
		$result_body = wp_remote_retrieve_body( $result );
		$body        = json_decode( $result_body, true );

		if ( isset( $body['error'] ) && $body['error'] ) {
			return array(
				'status' => 'error',
				'data'   => isset( $body['error_message'] ) ? $body['error_message'] : '',
			);
		} else {
			return array(
				'status' => 'ok',
				'data'   => isset( $body['data'] ) ? $body['data'] : '',
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
			$settings = get_option( 'firmafy_options' );
			$username = isset( $settings['username'] ) ? $settings['username'] : '';
			$password = isset( $settings['password'] ) ? $settings['password'] : '';
		}

		return $this->api_post( $username, $password, 'login' );
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