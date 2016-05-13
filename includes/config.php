<?php
/**
 * Processor config UI for ConvertKit for Caldera FOrms
 *
 * @package   cf_convertkit
 * @author    Josh Pollock Josh Pollock for CalderaWP LLC (email : Josh@CalderaWP.com)
 * @license   GPL-2.0+
 * @link
 * @copyright 2015 Josh Pollock for CalderaWP LLC for CalderaWP LLC
 */

$class = 'notice notice-error';
$message = printf( '<div class="notice"><p>%s</p></div>', esc_html__( 'You can choose to add subscriber to a ConvertKit form, a ConvertKit sequence or both. If you choose neither nothing will be changed in your ConvertKit account.', 'cf-convetkit' ) );

$config_fields              = Caldera_Forms_Processor_UI::config_fields( cf_convertkit_fields() );
echo $config_fields;
?>
<script type="text/javascript">
	jQuery(document).ready(function( $ ) {
		var apiKey = $( '#cf-convertkit-apikey' ).val();
		if( '' != $( '#cf-convertkit-apikey' ).val() ){
			resetDropdown( 'form', false );
			resetDropdown( 'sequence', false );
		}


		$( '.cf-convertkit-refresh' ).on( 'click', function(e){
			e.preventDefault();
			var type = $( this ).attr( 'data-refresh-type' );
			alert( type );
			resetDropdown( type, true );
		});

		function resetDropdown( type, hardRefresh ){

			var spinnerEL = document.getElementById( 'cf-convertkit-' + type + '-spinner' );
			var data = {
				dropdown: type,
				hard_refresh: hardRefresh,
				api_key: $( '#cf-convertkit-apikey').val(),
				action: 'cf_convertkit_dropdown_options',
				nonce: "<?php echo wp_create_nonce(); ?>"
			};
			$( spinnerEL ).css( 'visibility', 'visible' ).attr( 'aria-hidden', 'false' ).show();

			var xhr = $.get( ajaxurl, data );
			xhr.done(function( r ) {

				if( 'object' == typeof  r ){
					$( '#cf-convertkit-' + type + '-wrap .caldera-config-field' ).html( '' ).append( r.data.input );
				}
				$( spinnerEL ).css( 'visibility', 'hidden' ).attr( 'aria-hidden', 'true' ).hide();
			});
			xhr.error(function(r) {
				$( spinnerEL ).css( 'visibility', 'hidden' ).attr( 'aria-hidden', 'true' ).hide();
			});
		}

	});

</script>
