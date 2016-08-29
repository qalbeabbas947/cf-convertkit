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
?>

<?php
$class = 'notice notice-error';
$message = printf( '<div class="notice"><p>%s</p></div>', esc_html__( 'You can use this processor to add a subscriber to a ConvertKit form, a ConvertKit sequence or both. If you choose neither nothing will be changed in your ConvertKit account.', 'cf-convertkit' ) );

$config_fields              = Caldera_Forms_Processor_UI::config_fields( cf_convertkit_fields() );
echo $config_fields;

?>
<span class="foost" data-process-id="{{_id}}"></span>
<script type="text/javascript">

	var pId = "{{_id}}";

	var apiKey = jQuery( '#cf-convertkit-apikey' ).val();

	if( '' != jQuery( apiKey ).val() ){
		console.log( jQuery( this ));
		resetDropdown( 'form', false );
		resetDropdown( 'sequence', false );
	}

	jQuery( '.cf-convertkit-refresh' ).click( function(e){
		e.preventDefault();
		var type = jQuery( this ).attr( 'data-refresh-type' );
		resetDropdown( type, true );
	});



	function resetDropdown( type, hardRefresh ){

		var tag = 'config[processors][' + pId + '][config][cf-convertkit-' + type + ']';
		console.log( tag );
		var sel = document.getElementsByName( tag );
		sel = sel[0];

		var spinnerEL = document.getElementById( 'cf-convertkit-' + type + '-spinner' );
		var data = {
			dropdown: type,
			hard_refresh: hardRefresh,
			api_key: jQuery( '#cf-convertkit-apikey').val(),
			action: 'cf_convertkit_dropdown_options',
			nonce: "<?php echo wp_create_nonce(); ?>"
		};
		jQuery( spinnerEL ).css( 'visibility', 'visible' ).attr( 'aria-hidden', 'false' ).show();

		var xhr = jQuery.get( ajaxurl, data );
		xhr.done(function( r ) {
			if( 'object' == typeof  r ){
				jQuery( sel ).html( '' ).append( jQuery( r.data.input ).children() );
				jQuery( '#cf-convertkit-' + type ).val( jQuery( '#cf-convertkit-' + type + '-id' ).val() )
					.on( 'change', function () {
						jQuery( '#cf-convertkit-' + type + '-id' ).val( jQuery( this ).val() );
					}
				);
			}
			jQuery( spinnerEL ).css( 'visibility', 'hidden' ).attr( 'aria-hidden', 'true' ).hide();
		});
		xhr.error(function(r) {
			jQuery( spinnerEL ).css( 'visibility', 'hidden' ).attr( 'aria-hidden', 'true' ).hide();
		});
	}

</script>
