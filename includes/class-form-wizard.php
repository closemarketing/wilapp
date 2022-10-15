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
class CCOO_Registre_APP {

	/**
	 * Construct of Class
	 */
	public function __construct() {
		global $wilapp_api_connector;
		$this->wilapp_registre_options = get_option( 'wilapp_registre_options' );
		$this->form_type             = isset( $wilapp_registre_options['type'] ) && $wilapp_registre_options['type'] ? $wilapp_registre_options['type'] : 'FppUser';
		add_action( 'wp_enqueue_scripts', array( $this, 'registre_scripts' ) );
		add_shortcode( 'wilapp-registre-app', array( $this, 'render_form' ) );

		// AJAX Validate Step.
		add_action( 'wp_ajax_validate_step', array( $this, 'validate_step' ) );
		add_action( 'wp_ajax_nopriv_validate_step', array( $this, 'validate_step' ) );

		// AJAX Validate Submit.
		add_action( 'wp_ajax_validate_submit', array( $this, 'validate_submit' ) );
		add_action( 'wp_ajax_nopriv_validate_submit', array( $this, 'validate_submit' ) );

		// AJAX search companies.
		add_action( 'wp_ajax_registre_covenant_search', array( $this, 'registre_covenant_search' ) );
		add_action( 'wp_ajax_nopriv_registre_covenant_search', array( $this, 'registre_covenant_search' ) );
		
	}

	/**
	 * Loads Scripts
	 *
	 * @return void
	 */
	public function registre_scripts() {
		wp_register_script(
			'wilapp-registre-app',
			plugins_url( '/assets/registre-app.js', __FILE__ ),
			array( 'jquery' ),
			WILAPP_VERSION,
			true
		);
		wp_enqueue_script( 'wilapp-registre-app' );

		wp_register_script(
			'wilapp-registre-app-validation',
			plugins_url( '/assets/validation-fields.js', __FILE__ ),
			array( 'jquery', 'wilapp-registre-app' ),
			WILAPP_VERSION,
			true
		);
		wp_enqueue_script( 'wilapp-registre-app-validation' );

		// Google Autocomplete JS.
		wp_register_script(
			'wilapp-registre-app-google-api',
			'https://maps.googleapis.com/maps/api/js?key=' . CCOO_REG_APP_GOOGLE_KEY . '&libraries=places',
			'',
			WILAPP_VERSION,
			true
		);
		wp_enqueue_script( 'wilapp-registre-app-google-api' );

		wp_enqueue_style(
			'wilapp-registre-app',
			plugins_url( '/assets/registre.css', __FILE__ ),
			array(),
			WILAPP_VERSION
		);

		// Form steps AJAX.
		wp_localize_script(
			'wilapp-registre-app-validation',
			'AjaxVarStep',
			array(
				'url'           => admin_url( 'admin-ajax.php' ),
				'nonce'         => wp_create_nonce( 'validate_step_nonce' ),
				'validationMsg' => get_option( 'wilapp_registre_options' ),
			)
		);

		// Form steps SUBMIT AJAX.
		wp_localize_script(
			'wilapp-registre-app-validation',
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

		$mode        = isset( $atts['type'] ) ? $atts['type'] : 'full';
		$is_recupera = isset( $atts['recupera'] ) ? $atts['recupera'] : 'no';

		global $wilapp_api_connector;
		$html  = '<section class="wilapp-registre wizard-section" data-type="' . $mode . '" data-recupera="' . $is_recupera . '">';
		$html .= '<h2>' . esc_html__( 'Register', 'wilapp-registre-app' ) . '</h2>';
		$html .= '<div class="form-wizard"><form action="" method="post" role="form" autocomplete="off">';

		// Header.
		$html .= '<div class="form-wizard-header">';
		$html .= '<ul class="list-unstyled form-wizard-steps clearfix">';
		$html .= '<li class="active"><span>1</span></li>';
		$html .= '<li><span>2</span></li>';
		$html .= '<li><span>3</span></li>';
		$html .= '</ul>';
		$html .= '</div>';

		/**
		 * ## STEP 1
		 * --------------------------- */
		$html .= '<fieldset class="wizard-fieldset show">';
		// Nonce.
		$html .= wp_nonce_field( 'validate_step_1', 'validate_step_1_nonce' );
		// Email.
		$html .= '<div class="form-group focus-input">';
		if ( ! empty( $this->wilapp_registre_options['registre_app_email_label'] ) ) {
			$html .= '<label for="email" class="wizard-form-text-label">' . $this->wilapp_registre_options['registre_app_email_label'] . '*</label>';
		} else {
			$html .= '<label for="email" class="wizard-form-text-label">Correu electrònic*</label>';
		}
		$html .= '<input autocomplete="off" type="email" class="js-check-domain form-control wizard-required" id="email"';
		//$html .= '<input onblur="validateEmail( this, true )" type="email" class="form-control wizard-required" id="email"';
		if ( CCOO_REG_APP_DEBUG ) { $html .= ' value="test@pruebasclose.com"'; }
		$html .= '>';
		$html .= '<div class="wizard-form-error"></div>';
		$html .= '</div>';

		// Password and confirmed.
		$html .= '<div class="row">';
		$html .= '<div class="form-half left">';
		$html .= '<div class="form-group focus-input">';
		if ( ! empty( $this->wilapp_registre_options['registre_app_password_label'] ) ) {
			$html .= '<label for="pwd" class="wizard-form-text-label">' . $this->wilapp_registre_options['registre_app_password_label'] . '*</label>';
		} else {
			$html .= '<label for="pwd" class="wizard-form-text-label">Nova Clau*</label>';
		}
		$html .= '<input autocomplete="off" type="password" class="form-control wizard-required" id="pwd"';
		//$html .= '<input onblur="validatePassword( this, true )" type="password" class="form-control wizard-required" id="pwd"';
		if ( CCOO_REG_APP_DEBUG ) { $html .= ' value="Prueb@123456&"'; }
		$html .= '>';
		$html .= '<div class="wizard-form-error"></div>';
		$html .= '<span class="wizard-password-eye"><i class="far fa-eye"></i></span>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '<div class="form-half">';
		$html .= '<div class="form-group focus-input">';
		if ( ! empty( $this->wilapp_registre_options['registre_app_repeat_password_label'] ) ) {
			$html .= '<label for="pwd" class="wizard-form-text-label">' . $this->wilapp_registre_options['registre_app_repeat_password_label'] . '*</label>';
		} else {
			$html .= '<label for="cpwd" class="wizard-form-text-label">Confirm Password*</label>';
		}
		$html .= '<input autocomplete="off" type="password" class="form-control wizard-required" id="cpwd"';
		if ( CCOO_REG_APP_DEBUG ) { $html .= ' value="Prueb@123456&"'; }
		$html .= '>';
		$html .= '<div class="wizard-form-error"></div>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</div>'; // row

		// Type document and Document.
		$html .= '<div class="row">';
		// Tipus.
		$html .= '<div class="form-half left">';
		$html .= '<div class="form-group focus-input">';
		if ( ! empty( $this->wilapp_registre_options['registre_app_document_type_label'] ) ) {
			$html .= '<label for="tipus" class="wizard-form-text-label">'  . $this->wilapp_registre_options['registre_app_document_type_label'] . '*</label>';
		} else {
			$html .= '<label for="tipus" class="wizard-form-text-label">Tipus de document*</label>';
		}
		$html .= '<select id="tipus" class="form-control">';
		$html .= '<option value="tipus-dni">DNI / NIE</option>';
		$html .= '<option value="tipus-pass">Passaport</option>';
		$html .= '</select>';
		$html .= '<div class="wizard-form-error"></div>';
		$html .= '</div>';
		$html .= '</div>';
		// DNI.
		$html .= '<div class="form-half">';
		$html .= '<div class="form-group focus-input">';
		if ( ! empty( $this->wilapp_registre_options['registre_app_document_label'] ) ) {
			$html .= '<label for="tipus" class="wizard-form-text-label">'  . $this->wilapp_registre_options['registre_app_document_label'] . '*</label>';
		} else {
			$html .= '<label for="dni_nie" class="wizard-form-text-label">DNI/NIE*</label>';
		}
		$html .= '<input autocomplete="off" type="text" class="form-control wizard-required" maxlength="9" id="dni_nie"';
		if ( CCOO_REG_APP_DEBUG ) { $html .= ' value="91281411T"'; }
		$html .= '>';
		$html .= '<div class="wizard-form-error"></div>';
		$html .= '</div>';
		$html .= '</div>';
		// Passport.
		$html .= '<div class="form-half hidden">';
		$html .= '<div class="form-group focus-input">';
		if ( ! empty( $this->wilapp_registre_options['registre_app_passaport_label'] ) ) {
			$html .= '<label for="tipus" class="wizard-form-text-label">'  . $this->wilapp_registre_options['registre_app_passaport_label'] . '*</label>';
		} else {
			$html .= '<label for="passport" class="wizard-form-text-label">Passaport*</label>';
		}
		$html .= '<input autocomplete="off" type="text" class="form-control" id="passport">';
		$html .= '<div class="wizard-form-error"></div>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</div>'; // row

		// Mobile.
		$html .= '<div class="form-group focus-input">';
		if ( ! empty( $this->wilapp_registre_options['registre_app_mobile_label'] ) ) {
			$html .= '<label for="tipus" class="wizard-form-text-label">'  . $this->wilapp_registre_options['registre_app_mobile_label'] . '*</label>';
		} else {
			$html .= '<label for="mobile" class="wizard-form-text-label">Mòbil (per validació SMS)*</label>';
		}
		$html .= '<input autocomplete="off" type="text" class="form-control wizard-required" maxlength="9" id="mobile"';
		if ( CCOO_REG_APP_DEBUG ) { $html .= ' value="778888889"'; }
		$html .= '>';
		$html .= '<div class="wizard-form-error"></div>';
		$html .= '</div>';

		// Pagination.
		$html .= '<div class="form-group clearfix">';
		$html .= '<a href="javascript:;" data-page="1" class="js-validate-page-1 form-wizard-next-btn">' . esc_html__( 'Next', 'wilapp-registre-app' ) . '</a>';
		$html .= '</div>';
		$html .= '<div id="response-error-page-1" class="response-error"></div>';
		$html .= '</fieldset>';

		/**
		 * ## STEP 2
		 * --------------------------- */
		$html .= '<fieldset class="wizard-fieldset space-y-1">';
		// Hash.
		$html .= '<div class="form-group focus-input">';
		if ( ! empty( $this->wilapp_registre_options['registre_app_hash_label'] ) ) {
			$html .= '<label for="tipus" class="wizard-form-text-label">'  . $this->wilapp_registre_options['registre_app_hash_label'] . '*</label>';
		} else {
			$html .= '<label for="hash" class="wizard-form-text-label">' . esc_html__( 'Code', 'wilapp-registre-app' ) . '</label>';
		}
		$html .= '<input autocomplete="off" type="text" maxlength="4" class="form-control wizard-required" id="hash">';
		$html .= '<div class="wizard-form-error"></div>';
		$html .= '</div>';

		// IDValidated.
		if ( CCOO_REG_APP_DEBUG ) { $field_type = 'text'; } else { $field_type = 'hidden'; }
		$html .= '<div class="form-group focus-input ' . $field_type . '">';
		$html .= '<input autocomplete="off" type="' . $field_type . '" class="form-control wizard-required" id="idvalidated">';
		$html .= '<label for="idvalidated" class="wizard-form-text-label">' . esc_html__( 'ID validated', 'wilapp-registre-app' ) . '</label>';
		$html .= '<div class="wizard-form-error"></div>';
		$html .= '</div>';

		if ( CCOO_REG_APP_DEBUG ) { 
			$html .= '<a href="https://registre-gyq7ea4fwq-ey.a.run.app/registre/swagger-ui/index.html?configUrl=/registre/v3/api-docs/swagger-config#/status/getHash" target="_blank">Get Hash</a>';
		}

		// Pagination.
		$html .= '<div class="form-group clearfix">';
		$html .= '<a href="javascript:;" data-page="2" class="js-validate-page-2 form-wizard-next-btn">' . esc_html__( 'Next', 'wilapp-registre-app' ) . '</a>';
		$html .= '</div>';
		$html .= '<div id="response-error-page-2" class="response-error"></div>';
		$html .= '</fieldset>';

		/**
		 * ## STEP 3
		 * --------------------------- */
		$html .= '<fieldset class="wizard-fieldset">';
		// Name and surname.
		$html .= '<div class="row">';
		$html .= '<div class="form-third left">';
		$html .= '<div class="form-group focus-input">';
		if ( ! empty( $this->wilapp_registre_options['registre_app_name_label'] ) ) {
			$html .= '<label for="tipus" class="wizard-form-text-label">'  . $this->wilapp_registre_options['registre_app_name_label'] . '*</label>';
		} else {
			$html .= '<label for="nom" class="wizard-form-text-label">' . esc_html__( 'Name', 'wilapp-registre-app' ) . '*</label>';
		}
		$html .= '<input autocomplete="off" required type="text" class="form-control wizard-required" id="nom"';
		if ( CCOO_REG_APP_DEBUG ) { $html .= ' value="Nombre"'; }
		$html .= '>';
		$html .= '<div class="wizard-form-error"></div>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '<div class="form-third left">';
		$html .= '<div class="form-group focus-input">';
		if ( ! empty( $this->wilapp_registre_options['registre_app_last_name_1_label'] ) ) {
			$html .= '<label for="tipus" class="wizard-form-text-label">'  . $this->wilapp_registre_options['registre_app_last_name_1_label'] . '*</label>';
		} else {
			$html .= '<label for="cognom1" class="wizard-form-text-label">' . esc_html__( 'Surname 1', 'wilapp-registre-app' ) . '*</label>';
		}
		$html .= '<input autocomplete="off" required type="type" class="form-control wizard-required" id="cognom1"';
		if ( CCOO_REG_APP_DEBUG ) { $html .= ' value="Apellido1"'; }
		$html .= '>';
		$html .= '<div class="wizard-form-error"></div>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '<div class="form-third">';
		$html .= '<div class="form-group focus-input">';
		if ( ! empty( $this->wilapp_registre_options['registre_app_last_name_2_label'] ) ) {
			$html .= '<label for="tipus" class="wizard-form-text-label">'  . $this->wilapp_registre_options['registre_app_last_name_2_label'] . '</label>';
		} else {
			$html .= '<label for="cognom2" class="wizard-form-text-label">' . esc_html__( 'Surname 2', 'wilapp-registre-app' ) . '</label>';
		}

		$html .= '<input autocomplete="off" type="type" class="form-control" id="cognom2"';
		if ( CCOO_REG_APP_DEBUG ) { $html .= ' value="Apellido2"'; }
		$html .= '>';
		$html .= '<div class="wizard-form-error"></div>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</div>'; // row

		// Fields for full form.
		if ( 'full' == $mode ) {
			// Address.
			$html .= '<div class="row">';
			$html .= '<div class="form-fourth left">';
			$html .= '<div class="form-group focus-input">';
			if ( ! empty( $this->wilapp_registre_options['registre_app_address_label'] ) ) {
				$html .= '<label for="tipus" class="wizard-form-text-label">'  . $this->wilapp_registre_options['registre_app_address_label'] . '*</label>';
			} else {
				$html .= '<label for="address" class="wizard-form-text-label">' . esc_html__( 'Address and number', 'wilapp-registre-app' ) . '*</label>';
			}
			$html .= '<input maxlength="100" autocomplete="off" required type="text" class="form-control wizard-required" id="address"';
			if ( CCOO_REG_APP_DEBUG ) { $html .= ' value="Via Laietana, 16"'; }
			$html .= '>';
			$html .= '<div class="wizard-form-error"></div>';
			$html .= '</div>';
			$html .= '</div>';

			// Beep.
			$html .= '<div class="form-one left">';
			$html .= '<div class="form-group focus-input">';
			if ( ! empty( $this->wilapp_registre_options['registre_app_beep_label'] ) ) {
				$html .= '<label for="tipus" class="wizard-form-text-label">'  . $this->wilapp_registre_options['registre_app_beep_label'] . '*</label>';
			} else {
				$html .= '<label for="bloc" class="wizard-form-text-label">' . esc_html__( 'Block, door', 'wilapp-registre-app' ) . '</label>';
			}
			$html .= '<input autocomplete="off" type="text" maxlength="50" class="form-control wizard-required" id="bloc"';
			if ( CCOO_REG_APP_DEBUG ) { $html .= ' value="Porta A"'; }
			$html .= '>';
			$html .= '<div class="wizard-form-error"></div>';
			$html .= '</div>';
			$html .= '</div>';

			// Zip code.
			$html .= '<div class="form-one">';
			$html .= '<div class="form-group focus-input">';
			if ( ! empty( $this->wilapp_registre_options['registre_app_zipcode_label'] ) ) {
				$html .= '<label for="tipus" class="wizard-form-text-label">'  . $this->wilapp_registre_options['registre_app_zipcode_label'] . '*</label>';
			} else {
				$html .= '<label for="zipcode" class="wizard-form-text-label">' . esc_html__( 'Zip Code', 'wilapp-registre-app' ) . '*</label>';
			}
			$html .= '<input autocomplete="off" required type="number" maxlength= "5" class="form-control wizard-required" id="zipcode" oninput="this.value=this.value.slice(0,this.maxLength)"';
			if ( CCOO_REG_APP_DEBUG ) { $html .= ' value="18100"'; }
			$html .= '>';
			$html .= '<div class="wizard-form-error"></div>';
			$html .= '</div>';
			$html .= '</div>';

			$html .= '<div class="row hidden">';

			// Address 1, hidden.
			$html .= '<div class="form-third left'; if ( ! CCOO_REG_APP_DEBUG ) { $html.=' hidden'; } $html .= '">';
			$html .= '<div class="form-group focus-input">';
			$html .= '<input autocomplete="off" type="text" class="form-control wizard-required" id="address_1"';
			if ( CCOO_REG_APP_DEBUG ) { $html .= ' value=""'; }
			$html .= '>';
			$html .= '<label for="address_1" class="wizard-form-text-label">' . esc_html__( 'Address 1', 'wilapp-registre-app' ) . '</label>';
			$html .= '<div class="wizard-form-error"></div>';
			$html .= '</div>';
			$html .= '</div>';

			// Address 2, hidden.
			$html .= '<div class="form-third left'; if ( ! CCOO_REG_APP_DEBUG ) { $html.=' hidden'; } $html .= '">';
			$html .= '<div class="form-group focus-input">';
			$html .= '<input autocomplete="off" type="text" class="form-control wizard-required" id="address_2"';
			if ( CCOO_REG_APP_DEBUG ) { $html .= ' value=""'; }
			$html .= '>';
			$html .= '<label for="address_2" class="wizard-form-text-label">' . esc_html__( 'Address 2', 'wilapp-registre-app' ) . '</label>';
			$html .= '<div class="wizard-form-error"></div>';
			$html .= '</div>';
			$html .= '</div>';

			// City, hidden.
			$html .= '<div class="form-third left'; if ( ! CCOO_REG_APP_DEBUG ) { $html.=' hidden'; } $html .= '">';
			$html .= '<div class="form-group focus-input">';
			$html .= '<input autocomplete="off" type="type" class="form-control wizard-required" id="city"';
			if ( CCOO_REG_APP_DEBUG ) { $html .= ' value="Barcelona"'; }
			$html .= '>';
			$html .= '<label for="city" class="wizard-form-text-label">' . esc_html__( 'City', 'wilapp-registre-app' ) . '</label>';
			$html .= '<div class="wizard-form-error"></div>';
			$html .= '</div>';
			$html .= '</div>';

			// State, hidden.
			$html .= '<div class="form-third'; if ( ! CCOO_REG_APP_DEBUG ) { $html.=' hidden'; } $html .= '">';
			$html .= '<div class="form-group focus-input">';
			$html .= '<input autocomplete="off" type="type" class="form-control wizard-required" id="state"';
			if ( CCOO_REG_APP_DEBUG ) { $html .= ' value=""'; }
			$html .= '>';
			$html .= '<label for="state" class="wizard-form-text-label">' . esc_html__( 'State', 'wilapp-registre-app' ) . '*</label>';
			$html .= '<div class="wizard-form-error"></div>';
			$html .= '</div>';
			$html .= '</div>';

			$html .= '</div>';
			$html .= '</div>'; // row

			// Birth.
			$html .= '<div class="row">';
			$html .= '<div class="form-third left">';
			$html .= '<div class="form-group focus-input">';
			if ( ! empty( $this->wilapp_registre_options['registre_app_birthdate_label'] ) ) {
				$html .= '<label for="date" class="wizard-form-text-label">'  . $this->wilapp_registre_options['registre_app_birthdate_label'] . '*</label>';
			} else {
				$html .= '<label for="date" class="wizard-form-text-label">' . esc_html__( 'Birthday', 'wilapp-registre-app' ) . '*</label>';
			}
			$html .= '<input autocomplete="off" required type="date" class="form-control wizard-required" id="birth"';
			if ( CCOO_REG_APP_DEBUG ) { $html .= ' value="21/01/1980"'; }
			$html .= 'placeholder="' . esc_html__( 'dd/mm/yyyy', 'wilapp-registre-app' ) . '"';
			$html .= ' min="' . date( 'Y-m-d', strtotime( '-100 year', time() ) ) . '" ';
			$html .= 'max="' . date( 'Y-m-d', strtotime( '-18 year', time() ) ) . '"';
			$html .= '>';
			$html .= '<div class="wizard-form-error"></div>';
			$html .= '</div>';
			$html .= '</div>'; // form-third

			// API Fields
			$form_fields = array(
				array(
					'api'    => 'fpp/estudis',
					'field'  => 'studies',
					'label'  => ! empty( $this->wilapp_registre_options['registre_app_estudis_label'] ) ? $this->wilapp_registre_options['registre_app_estudis_label'] : esc_html__( 'Studies', 'wilapp-registre-app' ),
					'parent' => 'form-third left',
				),
				array(
					'api'    => 'fpp/genere',
					'field'  => 'sexe',
					'label'  => ! empty( $this->wilapp_registre_options['registre_app_sexe_label'] ) ? $this->wilapp_registre_options['registre_app_sexe_label'] : esc_html__( 'Gender', 'wilapp-registre-app' ),
					'parent' => 'form-third left',
					'row'    => 'close',
				),
				array(
					'api'    => 'fpp/estat_treballador',
					'field'  => 'situacio',
					'label'  => ! empty( $this->wilapp_registre_options['registre_app_situacio_label'] ) ? $this->wilapp_registre_options['registre_app_situacio_label'] : esc_html__( 'Employment Situation', 'wilapp-registre-app' ),
					'parent' => 'form-half left',
					'row'    => 'start',
				),
				array(
					'api'    => 'fpp/colectiu',
					'field'  => 'collectiu',
					'label'  => ! empty( $this->wilapp_registre_options['registre_app_collectiu_label'] ) ? $this->wilapp_registre_options['registre_app_collectiu_label'] : esc_html__( 'Collective', 'wilapp-registre-app' ),
					'parent' => 'form-third left hidden',
				),
				array(
					'api'    => 'fpp/categoria',
					'field'  => 'categoria',
					'label'  => ! empty( $this->wilapp_registre_options['registre_app_categoria_label'] ) ? $this->wilapp_registre_options['registre_app_categoria_label'] : esc_html__( 'Category', 'wilapp-registre-app' ),
					'parent' => 'form-third left hidden',
				),
				array(
					'api'    => 'fpp/area_funcional',
					'field'  => 'area_func',
					'label'  => ! empty( $this->wilapp_registre_options['registre_app_area_label'] ) ? $this->wilapp_registre_options['registre_app_area_label'] : esc_html__( 'Functional Area', 'wilapp-registre-app' ),
					'parent' => 'form-third left hidden',
				),
				array(
					'api'   => 'procedencia',
					'field' => 'procedencia',
					'label'  => ! empty( $this->wilapp_registre_options['registre_app_source_label'] ) ? $this->wilapp_registre_options['registre_app_source_label'] : esc_html__( 'Source', 'wilapp-registre-app' ),
					'parent' => 'form-half left',
					'row'    => 'close',
				),
			);

		} else {
			// API Fields
			$form_fields = array(
				array(
					'api'    => 'fpp/estat_treballador',
					'field'  => 'situacio',
					'label'  => ! empty( $this->wilapp_registre_options['registre_app_situacio_label'] ) ? $this->wilapp_registre_options['registre_app_situacio_label'] : esc_html__( 'Employment Situation', 'wilapp-registre-app' ),
					//'parent' => 'full-width',
					//'row'    => 'close',
				),
			);
		}

		// API Fields
		foreach ( $form_fields as $form_field ) {
			$api_values = $wilapp_api_connector->get_form_values( '/registre/api/util/' . $form_field['api'] );
			if ( isset( $form_field['row'] ) && 'start' === $form_field['row'] ) {
				$html .= '<div class="row js-dynamic-row">';
			}
			if ( isset( $form_field['parent'] ) ) {
				$html .= '<div class="' . $form_field['parent'] . '">';
			}
			$html .= '<div class="form-group focus-input';
			if ( isset( $form_field['style'] ) ) {
				$html .= ' ' . $form_field['style'];
			}
			$html .= '">';
			$html .= '<label for="' . $form_field['field'] . '" class="wizard-form-text-label">' . $form_field['label'] . '*</label>';
			$html .= '<select required id="' . $form_field['field'] . '" class="form-control">';
			$html .= $this->html_choices( $api_values );
			$html .= '</select>';
			$html .= '<div class="wizard-form-error"></div>';
			$html .= '</div>';
			if ( isset( $form_field['parent'] ) ) {
				$html .= '</div>';
			}
			if ( isset( $form_field['row'] ) && 'close' === $form_field['row'] ) {
				$html .= '</div>';
			}
		}

		if ( 'simple' == $mode ) {
			$html .= '<div class="js-company-search">';
			$html .= '<h3>' . esc_html__( 'Empresa', 'wilapp-registre-app' ) . '</h3>';
			$html .= '<div class="row">';
			// Province selector.
			$html .= '<div class="form-four left">';
			$html .= '<div class="form-group focus-input">';
			$html .= '<select autocomplete="off" class="form-control js-company-province">';
			$html .= '<option value="" selected="selected">Seleccioneu una província</option>';

			$provinces = array(
				'08' => 'BARCELONA',
				'17' => 'GIRONA',
				'25' => 'LLEIDA',
				'43' => 'TARRAGONA',
			);

			foreach ( $provinces as $id => $name ) {
				$html .= '<option value="' . $id . '">'. $name . '</option>';
			}

			$html .= '</select>';
			$html .= '<div class="wizard-form-error"></div>';
			$html .= '</div>';
			$html .= '</div>';

			// Search by selector.
			$html .= '<div class="form-four left">';
			$html .= '<div class="form-group focus-input">';
			$html .= '<select autocomplete="off" class="form-control js-company-search-type">';

			$type = array(
				'name' => 'Cerca per nom',
				'nif' => 'Cercar per NIF',
			);

			foreach ( $type as $id => $name ) {
				$html .= '<option value="' . $id . '">'. $name . '</option>';
			}

			$html .= '</select>';
			$html .= '<div class="wizard-form-error"></div>';
			$html .= '</div>';
			$html .= '</div>';

			// Search value.
			$html .= '<div class="form-four left">';
			$html .= '<div class="form-group focus-input">';
			$html .= '<input type="text" autocomplete="off" class="form-control js-company-search-value">';
			$html .= '<div class="wizard-form-error"></div>';
			$html .= '</div>';
			$html .= '</div>';

			// Search buttom.
			$html .= '<div class="form-four left">';
			$html .= '<div class="form-group focus-input">';
			$html .= '<button href="#"class="form-wizard-btn js-company-search-action">Cerca empresa</button>';
			$html .= '</div>';
			$html .= '</div>';

			// Close row.
			$html .= '</div>';

			// Companies result.
			$html .= '<div class="form-group focus-input">';
			$html .= '<select id="companies-result" class="form-control js-company-search-results">';
			$html .= '</select>';
			$html .= '<div class="wizard-form-error"></div>';
			$html .= '</div>';

			// Close js-company-search.
			$html .= '</div>';

		}

		// GDPR.
		$html .= '<div class="form-group focus-input form-conditions">';
		$html .= '<label for="gdpr"><input type="checkbox" class="form-check wizard-required" id="gdpr"';
		if ( CCOO_REG_APP_DEBUG ) { $html .= ' value="1"'; }
		$html .= '>';
		$html .= 'Accepto <a target="_blank" href="https://www.wilapp.cat/politica-de-privacitat/">les condicions</a></label>';
		if ( ! empty( $this->wilapp_registre_options['conditions'] ) ) {
			$html .= '<p class="conditions">' . $this->wilapp_registre_options['conditions'] . '</p>';
		}
		$html .= '<div class="wizard-form-error"></div>';
		$html .= '</div>';

		// Pagination.
		$html .= '<div class="form-group clearfix">';
		$html .= '<a href="javascript:;" class="js-validate-submit form-wizard-submit">' . esc_html__( 'Registre', 'wilapp-registre-app' ) . '</a>';
		$html .= '</fieldset>';
		$html .= '<div id="response-error-submit" class="response-error"></div>';
		$html .= '</div>';


		/**
		 * ## END
		 * --------------------------- */
		$html .= '</form></div></section>';

		return $html;
	}

	private function html_choices( $form_values ) {
		$html = '<option value=""></option>';
		foreach ( $form_values as $value ) {
			if ( ! empty( $value['value'] ) ) {
				$html .= '<option value="';
				$html .= esc_attr( $value['value'] );
				$html .= '">' . esc_attr( $value['label'] ) . '</option>';
			}
		}
		return $html;
	}

	/**
	 * Ajax function to load info
	 *
	 * @return void
	 */
	public function registre_covenant_search() {
		$keyword    = isset( $_POST['keyword'] ) ? esc_sql( $_POST['keyword'] ) : '';
		$state      = isset( $_POST['state'] ) ? esc_sql( $_POST['state'] ) : '';
		$type       = isset( $_POST['type'] ) ? esc_sql( $_POST['type'] ) : '';

		if ( true ) {
			if ( 'nif' === $type ) {
				$args = array(
					'body'    => array(
						'nif'  => $keyword,
						'prov' => $state,
						'page' => 0,
						'size' => 100,
						'sort' => 'nom,desc',
					),
					'timeout'   => 50,
					'sslverify' => false,
				);
				$api_url = 'https://frontend.wilapp.cat/api/empresesExportEntities/search/findByNif';
			} else {
				$args = array(
					'body'    => array(
						'nom'  => $keyword,
						'prov' => $state,
						'page' => 0,
						'size' => 100,
						'sort' => 'nom,asc',
					),
					'sslverify' => false,
					'timeout'   => 50,
				);
				$api_url = 'https://frontend.wilapp.cat/api/empresesExportEntities/search/findByNomContainingIgnoreCaseAndProv';
			}
			$response      = wp_remote_get( $api_url, $args );
			if ( isset( $response->errors ) && $response->errors ) {
				$html_error = 'Error: ';
				foreach ( $response->errors as $error => $message ) {
					$html_error .= $error . ' ' .  implode( ' ', $message );
				}
				wp_send_json_error( array( 'error' => $html_error ) );
			}
			$response_body = wp_remote_retrieve_body( $response );
			$body_response = json_decode( $response_body, true );
			$results = $body_response['_embedded']['empresesExportEntities'];

			$html = '';
			if ( empty( $results ) ) {
				$html .= '<option value="">' . esc_html__( 'The search has not got any result.', 'wilapp-registre' ) . '</option>';
			} else {
				$html .= '<option value="none">' . esc_html__( 'Select a company...', 'wilapp-registre' ) . '</option>';
				foreach ( $results as $result ) {
					$data_metadata = json_decode( $result['metadata'], true );

					$html .= '<option value="' . esc_html( $result['clau'] ) . '" >';
					$html .= esc_html( $result['nom'] );
					$html .= ' (' . esc_html( $data_metadata['direccion'] ) . ' ';
					$html .= esc_html( $data_metadata['cpostal'] ) . ' ' . esc_html( $data_metadata['poblacion'] );
					$html .= ')';  
					$html .= ' - ' . esc_html( $result['nif'] );
					$html .= '</option>';
				}
			}
			wp_send_json_success( $html );
		} else {
			wp_send_json_error( array( 'error' => 'Error' ) );
		}
	}

	/**
	 * # AJAX Validations
	 * ---------------------------------------------------------------------------------------------------- */

	/**
	 * Validates step 1 and 2
	 *
	 * @return void
	 */
	public function validate_step() {

		global $wilapp_api_connector;
		$id          = empty( $_POST['dni_nie'] ) ? esc_attr( $_POST['passport'] ) : esc_attr( $_POST['dni_nie'] );
		$page        = isset( $_POST['page'] ) ? (int) esc_attr( $_POST['page'] ) : 1;
		$form_type   = isset( $_POST['form_type'] ) ? esc_attr( $_POST['form_type'] ) : 'full';
		$is_recupera = isset( $_POST['is_recupera'] ) ? esc_attr( $_POST['is_recupera'] ) : 'no';
		$dni         = strtoupper( $id );
		$origin      = 'FppUser';

		if ( 'simple' == $form_type ) {
			$origin = 'User';
		} elseif ( 'yes' == $is_recupera ) {
			$origen = 'RECUPERA';
		}

		if ( '2' == $_POST['page'] && '0' == substr( $dni, 0, 1 ) ) {
			$dni = substr( $dni, 1 );
		}

		$data = array(
			'dni'         => $dni,
			'telfMobil'   => isset( $_POST['mobile'] ) ? esc_attr( $_POST['mobile'] ) : '',
			'email'       => isset( $_POST['email'] ) ? esc_attr( $_POST['email'] ) : '',
			'clau'        => isset( $_POST['password'] ) ? esc_attr( $_POST['password'] ) : '',
			'lang'        => get_bloginfo( 'language' ),
			'origen'      => ! empty( $this->form_type ) ? $this->form_type : $origin,
			'idValidated' => ! empty( $_POST['idvalidated'] ) ? esc_attr( $_POST['idvalidated'] ) : 0,
			'hash'        => ! empty( $_POST['hash'] ) ? esc_attr( $_POST['hash'] ) : '',
		);

		wp_verify_nonce( $_POST['validate_step_1_nonce'], 'validate_step_1' );

		if ( true ) {
			$query_type = 1 === $page ? 'POST' : 'PATCH';
			$response   = $wilapp_api_connector->request( '/registre/api/check', $query_type, $data );

			// Check for errors.
			if ( false === $response->success ) {
				$response_data = array(
					'error' => isset( $response->data ) ? $response->data : 'API Error',
				);
				wp_send_json_error( $response_data );
			}

			// If is page 1, then send SMS.
			if ( 1 == $page ) {
				$response_data = array(
					'idvalidated' => isset( $response->idValidated ) ? $response->idValidated : 0,
					'hash'        => isset( $response->hash ) ? $response->hash : '',
				);

				// Send debug email step 1.
				$array_response = json_decode( json_encode( $response, true ), true );
				$wilapp_api_connector->send_log_email( 'Step 1 validation', $data, $array_response );

				wp_send_json_success( $response_data );
			} elseif ( 2 == $page ) {
				$response_message = isset( $response->status->message ) ? $response->status->message : 'Error general';
				$response_data    = array(
					'ID'      => $response->id,
					'message' => $response_message,
				);

				// Send debug email step 2.
				$array_response = json_decode( json_encode( $response, true ), true );
				$wilapp_api_connector->send_log_email( 'Step 2 validation', $data, $array_response );

				// General validation.
				if ( isset( $response->id ) && '60' == $response->id ) {
					wp_send_json_error(
						$response_data,
						400,
					);
					exit;
				}

				if ( '0' != strval( $response->codiPersona ) ) {
					// Prepare fields.
					$repe_request    = isset( $response->estadoObj->repeRequest ) ? $response->estadoObj->repeRequest : 999;
					$robinson_email  = isset( $response->estadoObj->robinsonEmail ) ? $response->estadoObj->robinsonEmail : false;
					$robinson_telf_m = isset( $response->estadoObj->robinsonTelfM ) ? $response->estadoObj->robinsonTelfM : false;
					$validation      = isset( $response->estadoObj->validation ) ? $response->estadoObj->validation : false;
					$wp_usuari       = isset( $response->estadoObj->wpusuari ) ? $response->estadoObj->wpusuari : false;
					$integra         = isset( $response->estadoObj->integra ) ? $response->estadoObj->integra : false;

					// Load messages.
					$error_messages  = get_option( 'wilapp_registre_options' );

					// Validation for "Case 1": repeRequest > 1 OR robinsonEmail = 1 OR robinsonTelfM = 1 OR validation = 0.
					if ( 1 < $repe_request || 1 == $robinson_email || 1 == $robinson_telf_m || 0 == $validation ) {
						wp_send_json_error(
							array(
								'message'     => $error_messages['case_1'],
								'hidden_form' => true,
							)
						);
						exit;
					}

					// Validation for "Case 2": wpusuario = 1 AND integra = 1.
					if ( 1 == $wp_usuari && 1 == $integra ) {
						wp_send_json_error(
							array(
								'message' => $error_messages['case_2'],
								'hidden_form' => true,
							)
						);
						exit;
					}

					// Validation for "Case 3": wpusuario = 1 AND integra = 0.
					if ( 1 == $wp_usuari && 0 == $integra ) {
						wp_send_json_error(
							array(
								'message' => $error_messages['case_3'],
								'hidden_form' => true,
							)
						);
						exit;
					}
				}

				wp_send_json_success( $response_data );
			}
		} else {
			wp_send_json_error( esc_html__( 'Error connecting API', 'wilapp-registre-app' ) );
		}

	}
	/**
	 * Validates Final submission
	 *
	 * @return void
	 */
	public function validate_submit() {

		global $wilapp_api_connector;
		// Data to send
		$form_type         = isset( $_POST['form_type'] ) ? esc_attr( $_POST['form_type'] ) : 'full';
		$id                = empty( $_POST['dni_nie'] ) ? esc_attr( $_POST['passport'] ) : esc_attr( $_POST['dni_nie'] );
		$idvalidated       = ! empty( $_POST['idvalidated'] ) ? esc_attr( $_POST['idvalidated'] ) : 0;
		$user_email        = isset( $_POST['email'] ) ? esc_attr( $_POST['email'] ) : '';
		$user_password     = isset( $_POST['password'] ) ? esc_attr( $_POST['password'] ) : '';
		$validation_passed = true;

		$dni  = strtoupper( $id );

		if ( '0' == substr( $dni, 0, 1 ) ) {
			$dni = substr( $dni, 1 );
		}

		wp_verify_nonce( $_POST['validate_step_1_nonce'], 'validate_step_1' );

		if ( true ) {

			if ( 'full' === $form_type ) {
				$step_submit_data = array(
					'email'             => $user_email,
					'dni'               => $dni,
					'telfMobil'         => ! empty( $_POST['mobile'] ) ? esc_attr( $_POST['mobile'] ) : '',
					'nom'               => ! empty( $_POST['nom'] ) ? esc_attr( $_POST['nom'] ) : '',
					'cognom1'           => ! empty( $_POST['cognom1'] ) ? esc_attr( $_POST['cognom1'] ) : '',
					'cognom2'           => ! empty( $_POST['cognom2'] ) ? esc_attr( $_POST['cognom2'] ) : '',
					'sitLabor'          => '00',
					'codictreb'         => '',
					'clau'              => $user_password,
					'idValidated'       => "$idvalidated",
					'dnaixement'        => ! empty( $_POST['birth'] ) ? esc_attr( $_POST['birth'] ) : '',
					'genere'            => ! empty( $_POST['sexe'] ) ? esc_attr( $_POST['sexe'] ) : '',
					'lang'              => get_bloginfo( 'language' ),
					'procedencia'       => ! empty( $_POST['procedencia'] ) ? esc_attr( $_POST['procedencia'] ) : '',
					'estatTreballador'  => ! empty( $_POST['situacio'] ) ? esc_attr( $_POST['situacio'] ) : '',
					'areaFuncional'     => ! empty( $_POST['area_func'] ) ? esc_attr( $_POST['area_func'] ) : '',
					'colectiu'          => ! empty( $_POST['collectiu'] ) ? esc_attr( $_POST['collectiu'] ) : '',
					'estudis'           => ! empty( $_POST['studies'] ) ? esc_attr( $_POST['studies'] ) : '',
					'categoria'         => ! empty( $_POST['categoria'] ) ? esc_attr( $_POST['categoria'] ) : '00',
					'googleMapsAddress' => array(
						'address1'      => ! empty( $_POST['address_1'] ) ? esc_attr( $_POST['address_1'] ) : esc_attr( $_POST['address'] ),
						'address2'      => ! empty( $_POST['bloc'] ) ? esc_attr( $_POST['bloc'] ) : null,
						'city'          => ! empty( $_POST['city'] ) ? esc_attr( $_POST['city'] ) : null,
						'postalCode'    => ! empty( $_POST['zipcode'] ) ? esc_attr( $_POST['zipcode'] ) : null,
						'stateProvince' => ! empty( $_POST['state'] ) ? esc_attr( $_POST['state'] ) : null,
						'country'       => 'SPAIN', // Valor "SPAIN" para que la búsqueda de calles no sea a nivel mundial.
					),
				);

				// Send info.
				$response_form = $wilapp_api_connector->request( '/registre/api/status/fpp2', 'POST', $step_submit_data );

				// Send debug email.
				$array_response = json_decode( json_encode( $response_form, true ), true );
				$wilapp_api_connector->send_log_email( 'Step 3 validation', $step_submit_data, $array_response );

			} else {
				$step_submit_data = array(
					'email'             => $user_email,
					'dni'               => $dni,
					'telfMobil'         => ! empty( $_POST['mobile'] ) ? esc_attr( $_POST['mobile'] ) : '',
					'clau'              => $user_password,
					'idValidated'       => "$idvalidated",
					'lang'              => get_bloginfo( 'language' ),
					'nom'               => ! empty( $_POST['nom'] ) ? esc_attr( $_POST['nom'] ) : '',
					'cognom1'           => ! empty( $_POST['cognom1'] ) ? esc_attr( $_POST['cognom1'] ) : '',
					'cognom2'           => ! empty( $_POST['cognom2'] ) ? esc_attr( $_POST['cognom2'] ) : '',
					'sitLabor'          => '00',
					'codictreb'         => null,
				);

				// Send info.
				$response_form = $wilapp_api_connector->request( '/registre/api/status', 'POST', $step_submit_data );

				// Send debug email.
				$array_response = json_decode( json_encode( $response_form, true ), true );
				$wilapp_api_connector->send_log_email( 'INTRANET REGISTRE - Step 3', $step_submit_data, $array_response );

				// Check if user is registered.
				if ( isset( $response_form->message ) && 'ok' != strtolower( $response_form->message ) ) {
					$message = isset(  $response_form->message ) ? $response_form->message : "S'ha produït un error, si us plau, torneu-ho a provar";
					$validation_passed = false;
				}
			}

			// Validate response.
			// Java Error.
			if ( isset( $response_form->message ) && 'error jdbcexception occurred' == strtolower( $response_form->message ) ) {
				$message = isset(  $response_form->message ) ? $response_form->message : "S'ha produït un error, si us plau, torneu-ho a provar";
				$validation_passed = false;
			}

			// Error on response.
			if ( isset( $response_form->estado ) && '-1' == strval( $response_form->estado ) ) {
				$message = isset(  $response_form->status->message ) ? $response_form->status->message : "S'ha produït un error, si us plau, torneu-ho a provar";
				$validation_passed = false;
			}

			// If ID is not 59, then show error.
			if ( isset( $response_form->id ) && '59' != strval( $response_form->id ) ) {
				// We have a error, remove fields and show error.
				$message = isset(  $response_form->status->message ) ? $response_form->status->message : "S'ha produït un error, si us plau, torneu-ho a provar";
				$validation_passed = false;
			}

			// ID dont exist on response, then must be a Oracle error. Print it.
			if ( ! isset( $response_form->id ) && isset( $response_form->errors ) ) {
				$message = isset(  $response_form->message) ? $response_form->message : "S'ha produït un error, si us plau, torneu-ho a provar";
				$validation_passed = false;
			}

			if ( isset( $response_form->id ) && '59' != strval( $response_form->id ) && is_object( $response_form ) && isset( $response_form->message ) && 'ok' != strtolower( $response_form->message ) ) {
				$message = "Error: $response_form->error";
				$validation_passed = false;
			}

			if ( is_object( $response_form ) && isset( $response_form->message ) && 'error createusuari' == strtolower( $response_form->message ) ) {
				$message =  "Error: $response_form->error";
				$validation_passed = false;
			}

			if ( is_object( $response_form ) && isset( $response_form->message ) && 'not valid' === strtolower( $response_form->message ) && 'OK' !== $response_form->message  ) {
				$message =  "Error: $response_form->error";
				$validation_passed = false;
			}

			if ( false === $validation_passed && 'full' === $form_type ) {
				$wilapp_api_connector->generate_log( $message, $step_submit_data );
				wp_send_json_error( array(
					'message'     => $message,
					'redirect'    => false,
					'redirect_to' => '',
				));
			}

			// Login and redirect user.
			if ( $user_password && $user_email && 'full' === $form_type ) {

				$user = get_user_by( 'email', $user_email );

				if ( $user && wp_check_password( $user_password, $user->data->user_pass, $user->ID ) ) {

					wp_set_current_user( $user->ID, $user->user_login );
					wp_set_auth_cookie( $user->ID );
					do_action( 'wp_login', $user->user_login, $user );

					$cart_page_ID  = function_exists( 'wc_get_page_id' ) ? wc_get_page_id( 'cart' ) : 58841;
					$cart_page_url = get_permalink( $cart_page_ID );

					wp_send_json_success( array(
						'message'     => esc_html__( 'OK' ),
						'redirect'    => true,
						'redirect_to' => $cart_page_url,
					));
					exit;

				} else {
					if ( ! is_user_logged_in() ) {
						// Automatic login fails, return error to from.
						wp_send_json_error( array(
							'message'     => esc_html__( 'Unable to log in automatically' ),
							'redirect'    => true,
							'redirect_to' => wp_login_url(),
						));
					}
				}

				wp_send_json_error( esc_html__( 'No email and Password' ) );
			} else {
				wp_send_json_success( array(
					'message'     => esc_html__( 'OK' ),
					'redirect'    => true,
					'redirect_to' => home_url(),
				));
				exit;
			}

		}
	}

}

new CCOO_Registre_APP();