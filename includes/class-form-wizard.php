<?php
/**
 * CCOO Registre
 *
 * @package    WordPress
 * @author     David Perez <david@closemarketing.es>
 * @copyright  2022 Closemarketing
 * @version    1.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Registre.
 *
 * Class Registre Form.
 *
 * @since 1.0
 */
class WilApp_Wizard {

	/**
	 * Construct of Class
	 */
	public function __construct() {
		$this->wilapp_options = get_option( 'wilapp_options' );

		add_action( 'wp_enqueue_scripts', array( $this, 'scripts_frontend' ) );
		add_shortcode( 'wilapp', array( $this, 'render_form' ) );

		// AJAX Validate Step.
		add_action( 'wp_ajax_wizard_step', array( $this, 'wizard_step' ) );
		add_action( 'wp_ajax_nopriv_wizard_step', array( $this, 'wizard_step' ) );

		// AJAX Validate Submit.
		add_action( 'wp_ajax_validate_submit', array( $this, 'validate_submit' ) );
		add_action( 'wp_ajax_nopriv_validate_submit', array( $this, 'validate_submit' ) );
	}

	/**
	 * Loads Scripts
	 *
	 * @return void
	 */
	public function scripts_frontend() {

		wp_enqueue_style(
			'wilapp-wizard',
			plugins_url( '/assets/wilapp-frontend.css', __FILE__ ),
			array(),
			WILAPP_VERSION
		);

		wp_register_script(
			'wilapp-wizard',
			WILAPP_PLUGIN_URL . 'includes/assets/wilapp-app.js',
			array(),
			WILAPP_VERSION,
			true
		);
		wp_enqueue_script( 'wilapp-wizard' );

		// Form steps AJAX.
		wp_localize_script(
			'wilapp-wizard',
			'AjaxVarStep',
			array(
				'url'   => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'validate_step_nonce' ),
			)
		);

		// Form steps SUBMIT AJAX.
		wp_localize_script(
			'wilapp-wizard',
			'AjaxVarSubmit',
			array(
				'url'   => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'validate_submit_nonce' ),
			)
		);
	}

	/**
	 * Renders form
	 *
	 * @return void
	 */
	public function render_form( $atts = array() ) {
		global $helpers_wilapp;
		$mode        = isset( $atts['type'] ) ? $atts['type'] : 'full';
		$is_recupera = isset( $atts['recupera'] ) ? $atts['recupera'] : 'no';

		$html  = '<section class="wilapp-wizard" data-type="' . $mode . '" data-recupera="' . $is_recupera . '">';
		$html .= '<h2>' . __( 'Make an appointment', 'wilapp' ) . '</h2>';
		$html .= '<div class="form-wizard"><form action="" method="post" role="form" autocomplete="off">';

		$login_result = $helpers_wilapp->login();
		if ( 'error' === $login_result['status'] ) {
			echo 'error';
			return;
		}

		$professional = get_transient( 'wilapp_query_professional' );
		if ( ! $professional ) {
			$professional = ! empty( $login_result['data'] ) ? $login_result['data'] : false;
			set_transient( 'wilapp_query_professional', $login_result['data'], HOUR_IN_SECONDS * 3 );
		}
		$categories = isset( $professional['categories'] ) ? $professional['categories'] : array();

		// Nonce.
		$html .= wp_nonce_field( 'validate_step', 'validate_step_nonce' );

		/**
		 * ## STEP 1 - Category
		 * --------------------------- */
		$html .= '<fieldset class="wizard-fieldset show" data-page="1">';
		$html .= '<h3>' . __( 'Select a category', 'wilapp' ) . '</h3>';

		$html .= '<div class="row"><ul class="options categories">';
		foreach ( $categories as $category ) {
			$html .= '<li class="wilapp-item" data-cat-id="' . esc_attr( $category['id'] ) . '">';
			$html .= '<img src="' . esc_url( $category['image'] ) . '" width="80" height="60" />';
			$html .= esc_html( $category['name'] );
			$html .= '</li>';
		}
		$html .= '</ul></div>';
		$html .= '<div id="response-error-page-1" class="response-error"></div>';
		$html .= '</fieldset>';

		/**
		 * ## STEP 2 - Services
		 * --------------------------- */
		$html .= '<fieldset class="wizard-fieldset" data-page="2">';
		$html .= '<button id="wilapp-step-back">' . esc_html__( 'Back', 'wilapp' ) . '</button>';
		$html .= '<h3>' . __( 'Select a service', 'wilapp' ) . '</h3>';
		$html .= '<div class="row"><ul class="options services">';
		$html .= '</ul></div>';
		$html .= '<div id="response-error-page-1" class="response-error"></div>';
		$html .= '</fieldset>';

		/**
		 * ## STEP 3 - Appointment Day
		 * --------------------------- */
		$html .= '<fieldset class="wizard-fieldset" data-page="3">';
		$html .= '<button id="wilapp-step-back">' . esc_html__( 'Back', 'wilapp' ) . '</button>';
		$html .= '<h3>' . __( 'Select a day', 'wilapp' ) . '</h3>';
		$html .= '<div class="row"><ul class="options appointment-day"></ul></div>';
		$html .= '<div id="response-error-page-3" class="response-error"></div>';
		$html .= '</fieldset>';

		/**
		 * ## STEP 4 - Appointment Hour
		 * --------------------------- */
		$html .= '<fieldset class="wizard-fieldset" data-page="4">';
		$html .= '<button id="wilapp-step-back">' . esc_html__( 'Back', 'wilapp' ) . '</button>';
		$html .= '<h3>' . __( 'Select a hour', 'wilapp' ) . '</h3>';
		$html .= '<div class="row"><ul class="options appointment-hour"></ul></div>';
		$html .= '<div id="response-error-page-4" class="response-error"></div>';
		$html .= '</fieldset>';

		/**
		 * ## STEP 5 - Worker
		 * --------------------------- */
		$html .= '<fieldset class="wizard-fieldset" data-page="5">';
		$html .= '<button id="wilapp-step-back">' . esc_html__( 'Back', 'wilapp' ) . '</button>';
		$html .= '<h3>' . __( 'Select worker', 'wilapp' ) . '</h3>';
		$html .= '<div class="row"><ul class="options appointment-worker"></ul></div>';
		$html .= '<div id="response-error-page-5" class="response-error"></div>';
		$html .= '</fieldset>';

		/**
		 * ## STEP 6 - Appointment
		 * --------------------------- */
		$html .= '<fieldset class="wizard-fieldset" data-page="6" data-worker="">';
		$html .= '<button id="wilapp-step-back">' . esc_html__( 'Back', 'wilapp' ) . '</button>';
		$html .= '<h3>' . __( 'New Appointmet', 'wilapp' ) . '</h3>';

		// First and Last name.
		$html .= '<div class="form-group focus-input">';
		$html .= '<label for="name" class="wizard-form-text-label">' . __( 'Name and lastname', 'wilapp' ) . '*</label>';
		$html .= '<input autocomplete="off" type="text" class="form-control wizard-required" id="wilapp-name"';
		if ( WILAPP_DEBUG ) { $html .= ' value="Name and surname"'; }
		$html .= '>';
		$html .= '<div class="wizard-form-error"></div>';
		$html .= '</div>';

		// Phone.
		$html .= '<div class="form-group focus-input">';
		$html .= '<label for="name" class="wizard-form-text-label">' . __( 'Phone', 'wilapp' ) . '*</label>';
		$html .= '<input autocomplete="off" type="text" class="form-control wizard-required" id="wilapp-phone"';
		if ( WILAPP_DEBUG ) { $html .= ' value="669904426"'; }
		$html .= '>';
		$html .= '<div class="wizard-form-error"></div>';
		$html .= '</div>';

		// Email.
		$html .= '<div class="form-group focus-input">';
		$html .= '<label for="email" class="wizard-form-text-label">' . __( 'Email', 'wilapp' ) . '*</label>';
		$html .= '<input autocomplete="off" type="email" class="form-control wizard-required" id="wilapp-email"';
		if ( WILAPP_DEBUG ) { $html .= ' value="test@pruebasclose.com"'; }
		$html .= '>';
		$html .= '<div class="wizard-form-error"></div>';
		$html .= '</div>';

		// Notes.
		$html .= '<div class="form-group focus-input">';
		$html .= '<label for="email" class="wizard-form-text-label">' . __( 'Write a note, e.g. any specific requirements.', 'wilapp' ) . '</label>';
		$html .= '<input autocomplete="off" type="text" class="form-control wizard-required" id="wilapp-notes"';
		if ( WILAPP_DEBUG ) { $html .= ' value="Notes"'; }
		$html .= '>';
		$html .= '<div class="wizard-form-error"></div>';
		$html .= '</div>';

		// GDPR.
		$html .= '<div class="form-group focus-input form-conditions">';
		$html .= '<label for="gdpr"><input type="checkbox" class="form-check wizard-required" id="wilapp-gdpr"';
		if ( WILAPP_DEBUG ) { $html .= ' value="1"'; }
		$html .= '>';
		$html .= 'I’ve read and agree with <a target="_blank" href="#">Terms and Conditions</a> and <a target="_blank" href="#">Privacy Policy</a>. </label>';
		$html .= '<div class="wizard-form-error"></div>';
		$html .= '</div>';

		// Submit.
		$html .= '<div class="form-group clearfix">';
		$html .= '<a href="#" id="wilapp-submit" class="button form-wizard-submit">' . esc_html__( 'Confirm', 'ccoo-registre-app' ) . '</a>';
		$html .= '<div id="response-error-submit" class="response-error"></div>';
		$html .= '</div>';
	
		$html .= '<div id="response-error-page-6" class="response-error"></div>';
		$html .= '</fieldset>';

		/**
		 * ## STEP 7 - Finish
		 * --------------------------- */
		$html .= '<fieldset class="wizard-fieldset" data-page="7">';
		$html .= '<div id="wilapp-result-appointment"></div>';
		$html .= '</fieldset>';

		/**
		 * ## END
		 * --------------------------- */
		$html .= '</div></form></div></section>';

		return $html;
	}

	/**
	 * # AJAX Validations
	 * ---------------------------------------------------------------------------------------------------- */

	/**
	 * Validates steps
	 *
	 * @return void
	 */
	public function wizard_step() {
		global $helpers_wilapp;
		$page       = isset( $_POST['page'] ) ? (int) sanitize_text_field( $_POST['page'] ) : 1;
		session_start();
		if ( 2 === $page ) {
			unset( $_SESSION['wilapp'] );
		}
		if ( ! empty( $_POST['cat_id'] ) && 'null' !== $_POST['cat_id'] ) { 
			$_SESSION['wilapp']['cat_id'] = sanitize_text_field( $_POST['cat_id'] );
		}
		if ( ! empty( $_POST['service_id'] ) && 'null' !== $_POST['service_id'] ) { 
			$_SESSION['wilapp']['service_id'] = sanitize_text_field( $_POST['service_id'] );
		}
		if ( ! empty( $_POST['day'] ) && 'null' !== $_POST['day'] ) { 
			$_SESSION['wilapp']['day'] = sanitize_text_field( $_POST['day'] );
		}
		if ( ! empty( $_POST['hour'] ) && 'null' !== $_POST['hour'] ) { 
			$_SESSION['wilapp']['hour'] = sanitize_text_field( $_POST['hour'] );
		}
		if ( ! empty( $_POST['worker'] ) && 'null' !== $_POST['worker'] ) { 
			$_SESSION['wilapp']['worker'] = sanitize_text_field( $_POST['worker'] );
		}

		$cat_id     = isset( $_SESSION['wilapp']['cat_id'] ) ? $_SESSION['wilapp']['cat_id'] : '';
		$service_id = isset( $_SESSION['wilapp']['service_id'] ) ? $_SESSION['wilapp']['service_id'] : '';
		$day        = isset( $_SESSION['wilapp']['day'] ) ? $_SESSION['wilapp']['day'] : '';
		$hour       = isset( $_SESSION['wilapp']['hour'] ) ? $_SESSION['wilapp']['hour'] : '';
		$worker     = isset( $_SESSION['wilapp']['worker'] ) ? $_SESSION['wilapp']['worker'] : '';

		wp_verify_nonce( $_POST['validate_step_nonce'], 'validate_step' );
		if ( true ) {
			$professional = get_transient( 'wilapp_query_professional' );
			$services     = $professional['services'];
			// Request from page 1.
			if ( 2 === $page ) {
				$services_cat = $helpers_wilapp->filter_services( $services, $cat_id );
				$options = array();
				foreach ( $services_cat as $service ) {
					$options[] = array(
						'id'    => $service['id'],
						'image' => $service['image'],
						'name'  => $service['name'],
						'type'  => 'service-id',
					);
				}
				wp_send_json_success( $options );
			} elseif ( 3 === $page ) {
				// Schedules Day.
				$service    = $helpers_wilapp->filter_service( $services, $service_id );
				$offer_days = explode( ',', $service['offer_days'] );

				$start_time = strtotime( 'today' );
				$end_time   = strtotime( '+' . WILAPP_MAXDAYS . ' day' );
				$options    = array();
				for ( $i = $start_time; $i <= $end_time; $i = $i + 86400 ) {
					$week_day = (int) $helpers_wilapp->convert_week( date( 'w', $i ) );
					if ( isset( $offer_days[ $week_day ] ) && $offer_days[ $week_day ] ) {
						$options[] = array(
							'id'    => date( 'Y-m-d', $i ),
							'name'  => $helpers_wilapp->get_week_name( $week_day ) . ' ' . date( 'd-m-Y', $i ),
							'type'  => 'appointment-weekday',
						);
					}
				}
				wp_send_json_success( $options );
			} elseif ( 4 === $page ) {
				// Schedules Hour.
				$options           = array();
				$service           = $helpers_wilapp->filter_service( $services, $service_id );
				$schedules_service = $helpers_wilapp->get_schedules( $professional, $service );
				
				$start_time = strtotime( $day . ' ' . $schedules_service['init'] );
				$end_time   = strtotime( $day . ' ' . $schedules_service['end'] );
				$options    = array();
				for ( $i = $start_time; $i <= $end_time; $i = $i + $schedules_service['duration'] * 60 ) {
					$options[] = array(
						'id'    => date( 'H:i', $i ),
						'name'  => date( 'H:i', $i ),
						'type'  => 'appointment-hour',
					);
				}
				wp_send_json_success( $options );
			} elseif ( 5 === $page ) {
				$workers_service = $helpers_wilapp->get_workers(
					$professional,
					array(
						'service_id' => $service_id,
						'date'       => $day,
						'time'       => $hour,
					)
				);
				// Workers.
				$options    = array();
				foreach ( $workers_service as $worker ) {
					$options[] = array(
						'id'    => $worker['id'],
						'image' => $worker['image'],
						'name'  => $worker['name'],
						'type'  => 'worker-id',
					);
				}
				wp_send_json_success( $options );
			}
		} else {
			wp_send_json_error( esc_html__( 'Error connecting API', 'wilapp' ) );
		}
	}
	/**
	 * Validates Final submission
	 *
	 * @return void
	 */
	public function validate_submit() {
		global $helpers_wilapp;
		session_start();
		if ( ! isset( $_SESSION['wilapp'] ) ) {
			return false;
		}

		$worker_id = isset( $_POST['worker_id'] ) ? sanitize_text_field( $_POST['worker_id'] ) : '';
		$name      = isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : '';
		$phone     = isset( $_POST['phone'] ) ? sanitize_text_field( $_POST['phone'] ) : '';
		$email     = isset( $_POST['email'] ) ? sanitize_text_field( $_POST['email'] ) : '';
		$notes     = isset( $_POST['notes'] ) ? sanitize_text_field( $_POST['notes'] ) : '';

		wp_verify_nonce( $_POST['validate_submit_nonce'], 'validate_submit' );
		if ( true ) {
			$professional = get_transient( 'wilapp_query_professional' );
			$services     = $professional['services'];
			$service      = $helpers_wilapp->filter_service( $services, $_SESSION['wilapp']['service_id'] );

			// Process dates: Y-m-d H:i:s
			$start_date = $_SESSION['wilapp']['day'] . ' ' . $_SESSION['wilapp']['hour'];
			$end_date  = date( 'Y-m-d H:i', strtotime( $start_date ) + $service['duration'] * 60 );

			$result_appointment = $helpers_wilapp->post_appointment(
				$professional,
				array(
					'professional_id' => $professional['id'],
					'service_id'      => $_SESSION['wilapp']['service_id'],
					'worker_id'       => $worker_id,
					'start_date'      => $start_date,
					'end_date'        => $end_date,
					'client_name'     => $name,
					'client_email'    => $email,
					'client_phone'    => $phone,
					'client_notes'    => $notes,
					'isProfessional'  => false,
				)
			);

			if ( 'ok' === $result_appointment['status'] ) {
				wp_send_json_success( esc_html__( 'Appointment created correctly', 'wilapp' ) );
			} else {
				wp_send_json_error( esc_html__( 'Error creating the appointment', 'wilapp' ) );
			}
		}
	}

}

new WilApp_Wizard();
