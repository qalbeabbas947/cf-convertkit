<?php
/**
 * Functions for ConvertKit for Caldera FOrms
 *
 * @package   cf_convertkit
 * @author    Josh Pollock for CalderaWP LLC (email : Josh@CalderaWP.com)
 * @license   GPL-2.0+
 * @link
 * @copyright 2015 Josh Pollock for CalderaWP LLC
 */
use calderawp\convertKit\forms;
use calderawp\convertKit\sequences;


/**
 * Registers the ConvertKit for Caldera Forms Processor
 *
 * @uses "caldera_forms_pre_load_processors" action
 *
 * @since 0.1.0

 */
function cf_convertkit_load(){
	Caldera_Forms_Autoloader::add_root( 'CF_ConvertKit', CF_CONVERTKIT_PATH . 'classes' );
	new CF_ConvertKit_Processor( cf_convertkit_config(), cf_convertkit_fields(), 'cf_converkit' );
}

/**
 * ConverKit for Caldera Forms config
 *
 * @since 0.1.0
 *
 * @return array	Processor configuration
 */
function cf_convertkit_config(){

	return array(
		"name"				=>	__( 'ConvertKit for Caldera Forms', 'cf-convertkit'),
		"description"		=>	__( 'ConvertKit integration for Caldera Forms', 'cf-convertkit'),
		"icon"				=>	CF_CONVERTKIT_URL . "/icon.png",
		"author"			=>	'Josh Pollock for CalderaWP LLC',
		"author_url"		=>	'https://CalderaWP.com',
		"template"			=>	CF_CONVERTKIT_PATH . "includes/config.php",

	);

}

/**
 * Get the field configuration for the forms input
 *
 * @since 0.1.0
 *
 * @return array
 */
function cf_convertkit_forms_field_config(){
	return array(
		'id'            => 'cf-convertkit-form',
		'label'         => __( 'Form', 'cf-convertkit' ),
		'type'          => 'dropdown',
		'options'       => array(
			0 => __( '-- Select A ConvertKit Form --', 'cf-convertkit' )
		),
		'desc'          => __( 'ConvertKit form to add subscriber to.', 'cf-convertkit' ),
		'required'      => false,
		'extra_classes' => 'field-config',
		'magic'         => false
	);
}

/**
 * Get the field configuration for the sequences input
 *
 * @since 0.1.0
 *
 * @return array
 */
function cf_convertkit_sequences_field_config(){
	return array(
		'id'            => 'cf-convertkit-sequence',
		'label'         => __( 'Sequence', 'cf-convertkit' ),
		'type'          => 'dropdown',
		'options'       => array(
			0 => __( '-- Select A ConvertKit Sequence --', 'cf-convertkit' )
		),
		'desc'          => __( 'ConvertKit sequence to add subscriber to. Sequences are also referred to as courses.', 'cf-convertkit' ),
		'required'      => false,
		'extra_classes' => 'field-config',
		'magic'         => false
	);
}

/**
 * Get the field configurations for all of the fields.
 *
 * @since 0.1.0
 *
 * @return array
 */
function cf_convertkit_fields(){
	return array(
		array(
			'id'            => 'cf-convertkit-apikey',
			'label'         => __( 'API Key', 'cf-convertkit' ),
			'type'          => 'text',
			'required'      => true,
		),
		cf_convertkit_forms_field_config(),
		cf_convertkit_sequences_field_config(),
		array(
			'id'       => 'cf-convertkit-sequence-id',
			'type'     => 'hidden',
			'label' => __( 'Sequence ID', 'cf-convertkit' ),
			'required' => false,
		),
		array(
			'id'       => 'cf-convertkit-form-id',
			'type'     => 'hidden',
			'label' => __( 'Form ID', 'cf-convertkit' ),
			'required' => false,

		),
		array(
			'id'       => 'cf-convertkit-email',
			'label'    => __( 'Email Address', 'cf-convertkit' ),
			'desc'     => __( 'Subscriber email address.', 'cf-convertkit' ),
			'type'     => 'advanced',
			'allow_types' => array( 'email' ),
			'required' => true,
			'magic' => false
		),
		array(
			'id'            => 'cf-convertkit-name',
			'label'         => __( 'Name', 'cf-convertkit' ),
			'type'          => 'text',
			'desc'          => __( 'Subscriber name.', 'cf-convertkit' ),
			'required'      => true,
		),
		array(
			'id'    => 'cf-convertkit-tags',
			'label' => __( 'Tags', 'cf-convertkit' ),
			'desc'  => __( 'Comma separated list of tags.', 'cf-convertkit' ),
			'type'  => 'text',
			'required' => false,
		),
	);
}

/**
 * Initializes the licensing system
 *
 * @uses "admin_init" action
 *
 * @since 0.1.0
 */
function cf_convertkit_init_license(){

	$plugin = array(
		'name'		=>	'ConvertKit for Caldera Forms',
		'slug'		=>	'convertkit-for-caldera-forms',
		'url'		=>	'https://calderawp.com/',
		'version'	=>	CF_CONVERTKIT_VER,
		'key_store'	=>  'cf_convertkit_license',
		'file'		=>  CF_CONVERTKIT_CORE,
	);

	new \calderawp\licensing_helper\licensing( $plugin );

}

/**
 * Get all of the forms of an account
 *
 * @since 0.1.0
 *
 * @param string $api_key The API key.
 * @param bool $from_cache Optional. If true, cache is skipped. Default is false.
 *
 * @return array|mixed|object|string
 */
function cf_convertkit_get_forms( $api_key, $from_cache = false ){
	$cache_key = md5( __FUNCTION__, $api_key );
	$forms = array();
	if( ! $from_cache || false == ( $forms = get_transient( $cache_key) ) ){
		$client = new forms( $api_key );
		$forms =  $client->get_all();
		set_transient( $cache_key, $forms, HOUR_IN_SECONDS  );

	}

	return $forms;

}

/**
 * Get all of the sequences of an account
 *
 * @since 0.1.0
 *
 * @param string $api_key The API key.
 * @param bool $from_cache Optional. If true, cache is skipped. Default is false.
 *
 * @return array|mixed|object|string
 */
function cf_convertkit_get_sequences( $api_key, $from_cache = false ){

	$cache_key = md5( __FUNCTION__, $api_key );
	$sequences = array();
	if( ! $from_cache || false == ( $sequences = get_transient( $cache_key) ) ){
		$client = new sequences( $api_key );
		$sequences = $client->get_all();

		set_transient( $cache_key, $sequences, HOUR_IN_SECONDS  );

	}

	return $sequences;
}


/**
 * Add our example form
 *
 * @uses "caldera_forms_get_form_templates"
 *
 * @since 0.1.0
 *
 * @param array $forms Example forms.
 *
 * @return array
 */
function cf_convertkit_example_form( $forms ) {
	$forms['cf_convertkit']	= array(
		'name'	=>	__( 'ConvertKit for Caldera Forms Example', 'cf-convertkit' ),
		'template'	=>	include CF_CONVERTKIT_PATH . 'includes/templates/example.php'
	);

	return $forms;

}



/**
 * Add refresh lists button to forms/sequences input
 *
 * @uses "caldera_forms_processor_ui_input_html" filter
 *
 * @param string $field Field HTML
 * @param string $type Field type
 * @param string $id ID attribute for field
 *
 * @return string
 */
function cf_convert_kit_add_refresh_button( $field, $type, $id ){
	
	if( 'cf-convertkit-sequence' == $id || 'cf-convertkit-form' == $id ){
		$id_attr = $id . '-refresh';
		$field .= sprintf( ' <a class="button cf-convertkit-refresh" id="%s" data-refresh-type="%s">%s</a>',
			esc_attr( $id_attr ),
			esc_attr( str_replace( array( '-refresh', 'cf-convertkit-' ), '', $id_attr  ) ),
			esc_html__( 'Refresh', 'cf-converkit' )
		);
		$id_attr = $id . '-spinner';
		$field .= sprintf( '<span id="%s" class="spinner" aria-hidden="true"></span>', $id_attr );
	}

	return $field;

}

/**
 * Get dropdown options for forms/sequences inputs via AJAX
 *
 * @uses "wp_ajax_cf_convertkit_dropdown_options" action
 *
 * @since 0.1.0
 */
function cf_convertkit_dropdown_options(){
	if( isset( $_GET[ 'nonce' ] ) && isset( $_GET[ 'dropdown' ] ) && is_string(  $_GET[ 'dropdown' ] ) && in_array( $_GET[ 'dropdown' ], array( 'form', 'sequence') ) && isset( $_GET[ 'api_key' ]) ){
		if( wp_verify_nonce(  $_GET[ 'nonce'] ) && current_user_can( Caldera_Forms::get_manage_cap( 'edit' ) ) ){
			$api_key = trim( strip_tags( $_GET[ 'api_key' ]  ) );
			$from_cache = true;
			if( isset( $_GET[ 'hard_refresh' ] ) && 'false' == $_GET[ 'hard_refresh' ] || false == $_GET[ 'hard_refresh' ] ){
				$from_cache = false;
			}
			switch ($_GET[ 'dropdown' ]  ){
				case 'form' :
					$config  = cf_convertkit_sequences_field_config();
					$prop = 'forms';
					$options = cf_convertkit_get_forms( $api_key, $from_cache );
					break;
				case 'sequence' :
					$config  = cf_convertkit_sequences_field_config();
					$prop = 'courses';
					$options = cf_convertkit_get_sequences( $api_key, $from_cache );
					break;
				default:
					status_header( 400 );
					exit;
			}

			$the_options = array();
			if( ! empty( $options  ) && property_exists( $options, $prop )){
				$the_options = array_combine( wp_list_pluck( $options->$prop, 'id'  ), wp_list_pluck( $options->$prop, 'name'  )  );
				$the_options[0] = __( sprintf( '-- Select A ConvertKit %s --', ucwords( $_GET[ 'dropdown' ] ) ), 'cf-convertkit' );
				ksort( $the_options );

			}

			$config[ 'type' ] = 'dropdown';
			$config[ 'options' ] = $the_options;
			wp_send_json_success( array( 'input' => Caldera_Forms_Processor_UI::input( 'dropdown', $config, $config[ 'id'], 'block-input', true, true ) ) );
		}
	}

	wp_send_json_error();

	exit;
}
