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
		$this->set_data_object_initial( $config, $form );
		$api_key = $this->data_object->get_value( 'cf-convertkit-apikey' );
		if( ! $api_key ){
			$this->data_object->add_error( esc_html__( 'No ConvertKit API key set.', 'cf-converkit' ) );
			return $this->data_object->get_errors();

		}
		$ck_form = $this->data_object->get_value( 'cf-convertkit-form' );
		$subscriber = $this->prepare_subscriber();

		if( ! isset( $subscriber[ 'email'] ) || ! is_email( $subscriber[ 'email'] ) ){
			$this->data_object->add_error( esc_html__( 'Email invalid', 'cf-converkit' ) );
		}else{
			if ( is_numeric( $ck_form ) ) {
				$form_client = new forms( $api_key );
				$added       = $form_client->add( $ck_form, $subscriber );
				if ( is_string( $added ) || is_numeric( $added ) ) {
					$this->data_object->add_error( $added );
				} else {
					Caldera_Forms::set_submission_meta( 'converkit-form', $added, $form, $proccesid );
				}
			}

			if ( is_null( $this->data_object->get_errors() ) ) {
				$sequence = $this->data_object->get_value( 'cf-convertkit-sequence' );
				if ( is_numeric( $sequence ) ) {
					$sequence_client = new sequences( $api_key );
					$added           = $sequence_client->add( $sequence, $subscriber );
					if ( is_string( $added ) || is_numeric( $added ) ) {
						$this->data_object->add_error( $added );
					} else {
						Caldera_Forms::set_submission_meta( 'convertkit-sequence', $added, $form, $proccesid );
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
		
	}

}
