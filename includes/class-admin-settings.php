<?php
/**
 * Library for admin settings
 *
 * @package    WordPress
 * @author     David Perez <david@closemarketing.es>
 * @copyright  2019 Closemarketing
 * @version    1.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Library for WooCommerce Settings
 *
 * Settings in order to sync products
 *
 * @package    WordPress
 * @author     David Perez <david@closemarketing.es>
 * @copyright  2019 Closemarketing
 * @version    0.1
 */
class Wilapp_Admin_Settings {
	/**
	 * Settings
	 *
	 * @var array
	 */
	private $wilapp_settings;

	/**
	 * Construct of class
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
		add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'page_init' ) );
		add_action( 'admin_notices', array( $this, 'admin_notices_action' ) );

		register_activation_hook( WILAPP_PLUGIN, array( $this, 'loads_templates_cpt' ) );
	}

	/**
	* function_description
	*
	* @return void
	*/
	function admin_scripts() {
		wp_enqueue_style(
			'wilapp-admin',
			WILAPP_PLUGIN_URL . '/includes/assets/wilapp-admin.css',
			array(),
			WILAPP_VERSION
		);
	}
	/**
	 * Adds plugin page.
	 *
	 * @return void
	 */
	public function add_plugin_page() {
		add_submenu_page(
			'options-general.php',
			__( 'Wilapp', 'ccoo-admin' ),
			__( 'Wilapp', 'ccoo-admin' ),
			'manage_options',
			'wilapp-options',
			array( $this, 'create_admin_page' )
		);
	}
	function admin_notices_action() {
		settings_errors( 'wilapp_notification_error' );
	}

	/**
	 * Create admin page.
	 *
	 * @return void
	 */
	public function create_admin_page() {
		global $helpers_wilapp;
		$this->wilapp_settings = get_option('wilapp_options');
		?>
		<div class="header-wrap">
			<div class="wrapper">
				<h2 style="display: none;"></h2>
				<div id="nag-container"></div>
				<div class="header wilapp-header">
					<div class="logo">
						<img src="<?php echo WILAPP_PLUGIN_URL . 'includes/assets/logo.svg'; ?>" height="35" width="154"/>
						<h2><?php esc_html_e( 'Wilapp Settings', 'wilapp' ); ?></h2>
					</div>
					<div class="connection">
						<p>
						<?php
						$login_result = $helpers_wilapp->login();
						if ( 'error' === $login_result['status'] ) {
							echo '<svg width="24" height="24" viewBox="0 0 24 24" class="license-icon"><defs><circle id="license-unchecked-a" cx="8" cy="8" r="8"></circle></defs><g fill="none" fill-rule="evenodd" transform="translate(4 4)"><use fill="#dc3232" xlink:href="#license-unchecked-a"></use><g fill="#FFF" transform="translate(4 4)"><rect width="2" height="8" x="3" rx="1" transform="rotate(-45 4 4)"></rect><rect width="2" height="8" x="3" rx="1" transform="rotate(-135 4 4)"></rect></g></g></svg>';
							esc_html_e( 'ERROR: We could not connect to Wilapp.', 'wilapp' );
							echo esc_html( $login_result['data'] );
						} else {
							echo '<svg width="24" height="24" viewBox="0 0 24 24" class="icon-24 license-icon"><defs><circle id="license-checked-a" cx="8" cy="8" r="8"></circle></defs><g fill="none" fill-rule="evenodd" transform="translate(4 4)"><mask id="license-checked-b" fill="#fff"><use xlink:href="#license-checked-a"></use></mask><use fill="#52AA59" xlink:href="#license-checked-a"></use><path fill="#FFF" fill-rule="nonzero" d="M7.58684811,11.33783 C7.19116948,11.7358748 6.54914653,11.7358748 6.15365886,11.33783 L3.93312261,9.10401503 C3.53744398,8.70616235 3.53744398,8.06030011 3.93312261,7.66244744 C4.32861028,7.26440266 4.97063323,7.26440266 5.36631186,7.66244744 L6.68931454,8.99316954 C6.78918902,9.09344917 6.95131795,9.09344917 7.0513834,8.99316954 L10.6336881,5.38944268 C11.0291758,4.9913979 11.6711988,4.9913979 12.0668774,5.38944268 C12.2568872,5.5805887 12.3636364,5.83993255 12.3636364,6.11022647 C12.3636364,6.3805204 12.2568872,6.63986424 12.0668774,6.83101027 L7.58684811,11.33783 Z" mask="url(#license-checked-b)"></path></g></svg>';
							esc_html_e( 'Connected to Wilapp', 'wilapp' );
						}
						?>
						</p>
					</div>
				</div>
			</div>
		</div>

		<div class="wrap">
			<p></p>
			<?php settings_errors(); ?>

			<form method="post" action="options.php">
				<?php
					settings_fields( 'admin_wilapp_settings' );
					do_settings_sections( 'wilapp_options' );
					submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Init for page
	 *
	 * @return void
	 */
	public function page_init() {

		/**
		 * ## API Settings
		 * --------------------------- */
		register_setting(
			'admin_wilapp_settings',
			'wilapp_options',
			array( $this, 'sanitize_fields_api' )
		);

		add_settings_section(
			'admin_wilapp_settings',
			__( 'Settings for integration to Wilapp', 'wilapp' ),
			array( $this, 'admin_section_api_info' ),
			'wilapp_options'
		);

		add_settings_field(
			'wilapp_username',
			__( 'Username', 'wilapp' ),
			array( $this, 'username_callback' ),
			'wilapp_options',
			'admin_wilapp_settings'
		);

		add_settings_field(
			'wilapp_password',
			__( 'Password', 'wilapp' ),
			array( $this, 'password_callback' ),
			'wilapp_options',
			'admin_wilapp_settings'
		);

		add_settings_field(
			'wilapp_id_show',
			__( 'Public API Key', 'wilapp' ),
			array( $this, 'id_show_callback' ),
			'wilapp_options',
			'admin_wilapp_settings'
		);

		add_settings_field(
			'wilapp_notification',
			__( 'Notification settings', 'wilapp' ),
			array( $this, 'notification_callback' ),
			'wilapp_options',
			'admin_wilapp_settings'
		);

		if ( class_exists( 'WooCommerce' ) ) {
			add_settings_field(
				'wilapp_woocommerce',
				__( 'Sign WooCommerce orders with Wilapp?', 'wilapp' ),
				array( $this, 'woocommerce_callback' ),
				'wilapp_options',
				'admin_wilapp_settings'
			);
		}
	}

	/**
	 * # API Settings
	 * ---------------------------------------------------------------------------------------------------- */

	/**
	 * Sanitize fiels before saves in DB
	 *
	 * @param array $input Input fields.
	 * @return array
	 */
	public function sanitize_fields_api( $input ) {
		global $helpers_wilapp;
		$sanitary_values = array();

		if ( isset( $input['username'] ) ) {
			$sanitary_values['username'] = sanitize_text_field( $input['username'] );
		}

		if ( isset( $input['password'] ) ) {
			$sanitary_values['password'] = sanitize_text_field( $input['password'] );
		}

		if ( isset( $input['id_show'] ) ) {
			$sanitary_values['id_show'] = sanitize_text_field( $input['id_show'] );
		}

		if ( isset( $input['woocommerce'] ) ) {
			$sanitary_values['woocommerce'] = sanitize_text_field( $input['woocommerce'] );
		}

		if ( isset( $_POST['notification'] ) && is_array( $_POST['notification'] ) ) {
			foreach ( $_POST['notification'] as $notification ) {
				$sanitary_values['notification'][] = sanitize_text_field( $notification );
			}
		}

		if ( empty( $sanitary_values['notification'] ) ) {
			add_settings_error(
				'wilapp_notification_error',
				esc_attr( 'settings_updated' ),
				__( 'Notifications option cannot be empty', 'wilapp' ),
				'error'
			);
		}

		$helpers_wilapp->login( $sanitary_values['username'], $sanitary_values['password'] );

		return $sanitary_values;
	}

	/**
	 * Info for neo automate section.
	 *
	 * @return void
	 */
	public function admin_section_api_info() {
		esc_html_e( 'Put the connection API key settings in order to connect external data.', 'wilapp' );
	}

	public function username_callback() {
		printf(
			'<input class="regular-text" type="text" name="wilapp_options[username]" id="wilapp_username" value="%s">',
			isset( $this->wilapp_settings['username'] ) ? esc_attr( $this->wilapp_settings['username'] ) : ''
		);
	}

	public function password_callback() {
		printf(
			'<input class="regular-text" type="password" name="wilapp_options[password]" id="password" value="%s">',
			isset( $this->wilapp_settings['password'] ) ? esc_attr( $this->wilapp_settings['password'] ) : ''
		);
	}

	public function id_show_callback() {
		printf(
			'<input class="regular-text" type="password" name="wilapp_options[id_show]" id="id_show" value="%s">',
			isset( $this->wilapp_settings['id_show'] ) ? esc_attr( $this->wilapp_settings['id_show'] ) : ''
		);
	}

	public function notification_callback() {
		$notification = isset( $this->wilapp_settings['notification'] ) ? (array) $this->wilapp_settings['notification'] : [];
		echo '<input type="checkbox" name="notification[]" value="sms" ';
		echo checked( in_array( 'sms', $notification ), 1 ) . ' />';
		echo '<label for="notification">SMS</label>';
		echo '<br/><input type="checkbox" name="notification[]" value="email" ';
		echo checked( in_array( 'email', $notification ), 1 ) . ' />';
		echo '<label for="notification">Email</label>';
	}

	public function woocommerce_callback() {
		?>
		<select name="wilapp_options[woocommerce]" id="woocommerce">
			<?php $selected = ( isset( $this->wilapp_settings['woocommerce'] ) && $this->wilapp_settings['woocommerce'] === 'yes' ) ? 'selected' : ''; ?>
			<option value="no" <?php echo esc_html( $selected ); ?>><?php esc_html_e( 'No', 'wilapp' ); ?></option>
			<option value="yes" <?php echo esc_html( $selected ); ?>><?php esc_html_e( 'Yes', 'wilapp' ); ?></option>
			<?php $selected = ( isset( $this->wilapp_settings['woocommerce'] ) && $this->wilapp_settings['woocommerce'] === 'no' ) ? 'selected' : ''; ?>
		</select><br/>
		<label for="woocommerce"><?php esc_html_e( 'This adds a NIF field required for Wilapp and signs the order when the order is placed.', 'wilapp' ); ?></label>
		<br/>
		<label for="woocommerce">
			<?php
			echo sprintf(
				__( 'You will need to setup the <a href="%s">Terms and conditions page</a>', 'wilapp' ),
				admin_url('admin.php?page=wc-settings&tab=advanced')
			);
			?>
		</label>
		<?php
	}

	/**
	 * Register Post Type Templates
	 *
	 * @return void
	 **/
	public function create_wilapp_templates_type() {
		$labels = array(
			'name'               => __( 'Templates', 'wilapp' ),
			'singular_name'      => __( 'Template', 'wilapp' ),
			'add_new'            => __( 'Add New Template', 'wilapp' ),
			'add_new_item'       => __( 'Add New Template', 'wilapp' ),
			'edit_item'          => __( 'Edit Template', 'wilapp' ),
			'new_item'           => __( 'New Template', 'wilapp' ),
			'view_item'          => __( 'View Template', 'wilapp' ),
			'search_items'       => __( 'Search Templates', 'wilapp' ),
			'not_found'          => __( 'Not found Template', 'wilapp' ),
			'not_found_in_trash' => __( 'Not found Template in trash', 'wilapp' ),
		);
		$args   = array(
			'labels'             => $labels,
			'public'             => false,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => false,
			'show_in_rest'       => true,
			'query_var'          => true,
			'has_archive'        => false,
			'capability_type'    => 'post',
			'hierarchical'       => false,
			'menu_position'      => 5,
			'supports'           => array( 'title', 'editor', 'revisions' ),
		);
		register_post_type( 'wilapp_template', $args );
	}

	/**
	 * Adds columns to post type wilapp_template
	 *
	 * @param array $wilapp_template_columns  Header of admin post type list.
	 * @return array $wilapp_template_columns New elements for header.
	 */
	function add_new_wilapp_template_columns( $wilapp_template_columns ) {
		$new_columns['cb']    = '<input type="checkbox" />';
		$new_columns['title'] = __( 'Title', 'wilapp' );
		$new_columns['variables'] = __( 'Variables', 'wilapp' );
	
		return $new_columns;
	}

	/**
	 * Add columns content
	 *
	 * @param array $column_name Column name of actual.
	 * @param array $id Post ID.
	 * @return void
	 */
	function manage_wilapp_template_columns( $column_name, $id ) {
		global $helpers_wilapp;
	
		switch ( $column_name ) {
			case 'variables':
				$variables = $helpers_wilapp->get_variables_template( $id );
				if ( is_array( $variables ) ) {
					echo implode( ', ', array_column( $variables, 'label' ) );
				}
				
				break;
	
			default:
				break;
		} // end switch
	}

	/**
	 * Creates predefined templates
	 *
	 * @return void
	 */
	public function loads_templates_cpt() {
		$initial_templates = array(
			array(
				'slug'  => 'sepa',
				'title' => __( 'Sign SEPA', 'wilapp' ),
			),
			array(
				'slug'  => 'rgpd',
				'title' => __( 'RGPD New user', 'wilapp' ),
			),
		);

		foreach ( $initial_templates as $template ) {
			$file_template = WILAPP_PLUGIN_PATH . '/includes/templates/' . $template['slug'] . '.html';
			$post_exists   = get_page_by_path( $template['slug'], OBJECT, 'wilapp_template' );

			if ( file_exists( $file_template ) && ! $post_exists ) {
				$template_post = array(
					'post_title'    => wp_strip_all_tags( $template['title'] ),
					'post_name'     => $template['slug'],
					'post_content'  => file_get_contents( $file_template ),
					'post_status'   => 'publish',
					'post_type'     => 'wilapp_template',
				);
				  
				// Insert the post into the database
				wp_insert_post( $template_post );
			}
		}

	}
}

new Wilapp_Admin_Settings();
