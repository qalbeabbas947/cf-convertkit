<?php


use calderawp\convertKit\forms;
use calderawp\convertKit\sequences;

class CF_ConvertKit_Processor extends Caldera_Forms_Processor_Newsletter {

	/**
	 * Subscribe if possible, and if not return errors.
	 *
	 * @since 0.0.1
	 *
	 * @param array $config Processor config
	 * @param array $form Form config
	 * @param string $proccesid Unique ID for this instance of the processor
	 *
	 * @return array Return if errors, do not return if not
	 */
	public function pre_processor( array $config, array $form, $proccesid ){
		add_filter(  'caldera_forms_' . $this->slug . '_fields', array( $this, 'fixed_required' )  );
		$this->set_data_object_initial( $config, $form );
		$api_key = $this->data_object->get_value( 'cf-convertkit-apikey' );
		if( ! $api_key ){
			$this->data_object->add_error( esc_html__( 'No ConvertKit API key set.', 'cf-converkit' ) );
			return $this->data_object->get_errors();

		}
		$ck_form = $this->data_object->get_value( 'cf-convertkit-form-id' );
		$subscriber = $this->prepare_subscriber();

		if( ! isset( $subscriber[ 'email'] ) || ! is_email( $subscriber[ 'email'] ) ){
			$this->data_object->add_error( esc_html__( 'Email invalid', 'cf-converkit' ) );
		}else{
			if ( 0 < absint( $ck_form ) ) {
				$form_client = new forms( $api_key );
				$added       = $form_client->add( $ck_form, $subscriber );
				if ( is_string( $added ) || is_numeric( $added ) ) {
					$this->data_object->add_error( $added );
				} else {
					if( isset( $added->subscription ) && isset( $added->subscription->id ) ) {
						Caldera_Forms::set_submission_meta( 'convertkit-form-subscriber-id', $added->subscription->id, $form, $proccesid );
					}

				}
			}

			if ( is_null( $this->data_object->get_errors() ) ) {
				$sequence = $this->data_object->get_value( 'cf-convertkit-sequence-id' );
				if ( 0 < absint( $sequence ) ) {
					$sequence_client = new sequences( $api_key );
					$added           = $sequence_client->add( $sequence, $subscriber );
					if ( is_string( $added ) || is_numeric( $added ) ) {
						$this->data_object->add_error( $added );
					} else {
						if ( isset( $added->subscription ) && isset( $added->subscription->id ) ) {
							Caldera_Forms::set_submission_meta( 'convertkit-form-sequence-id', $added->subscription->id, $form, $proccesid );
						}
					}

				}
			}
		}

		if ( ! is_null( $this->data_object->get_errors() ) ) {
			return $this->data_object->get_errors();
		}

		$this->setup_transata( $proccesid );

	}

	protected function prepare_subscriber(){
		$subscriber = array();
		foreach( array( 'email', 'name', 'tags' ) as $field ){
			$subscriber[ $field ] = $this->data_object->get_value( 'cf-convertkit-' . $field  );
		}

		return $subscriber;
	}

	/**
	 * Unset our extra fields onbly used in admin
	 *
	 * @since 1.0.2
	 *
	 * @uses "caldera_forms_cf_convertkit_fields" filter
	 *
	 * @param $fields
	 *
	 * @return mixed
	 */
	public function fixed_required( $fields ){
		foreach( $fields as $i => $field ){
			if( in_array( $field[ 'id' ], array( 'cf-convertkit-sequence', 'cf-convertkit-form' ) ) ){
				unset( $fields[$i] );
			}
		}

		return $fields;
	}


	/**
	 * Add a subscriber to a list
	 *
	 * @since 0.1.0
	 *
	 * @param array $subscriber_data Data for new subscriber
	 * @param string $list_name Name of list
	 *
	 * @return mixed
	 */
	public function subscribe( array $subscriber_data, $list_name ){
		//it smells bad that Josh didn't use this, but we have two different types of things like lists with ConvertKit, so...
	}

}
