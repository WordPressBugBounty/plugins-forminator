<?php
/**
 * The Forminator_Website class.
 *
 * @package Forminator
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Website
 *
 * @since 1.0
 */
class Forminator_Website extends Forminator_Field {

	/**
	 * Name
	 *
	 * @var string
	 */
	public $name = '';

	/**
	 * Slug
	 *
	 * @var string
	 */
	public $slug = 'url';

	/**
	 * Position
	 *
	 * @var int
	 */
	public $position = 5;

	/**
	 * Type
	 *
	 * @var string
	 */
	public $type = 'url';

	/**
	 * Options
	 *
	 * @var array
	 */
	public $options = array();

	/**
	 * Is input
	 *
	 * @var bool
	 */
	public $is_input = true;

	/**
	 * Icon
	 *
	 * @var string
	 */
	public $icon = 'sui-icon-web-globe-world';

	/**
	 * Forminator_Website constructor.
	 *
	 * @since 1.0
	 */
	public function __construct() {
		parent::__construct();

		$this->name = esc_html__( 'Website', 'forminator' );
		$required   = __( 'This field is required. Please input a valid URL', 'forminator' );

		self::$default_required_messages[ $this->type ] = $required;
	}

	/**
	 * Field defaults
	 *
	 * @since 1.0
	 * @return array
	 */
	public function defaults() {
		return array(
			'field_label' => esc_html__( 'Website', 'forminator' ),
		);
	}

	/**
	 * Autofill Setting
	 *
	 * @since 1.0.5
	 *
	 * @param array $settings Settings.
	 *
	 * @return array
	 */
	public function autofill_settings( $settings = array() ) {
		$providers = apply_filters( 'forminator_field_' . $this->slug . '_autofill', array(), $this->slug );

		$autofill_settings = array(
			'website' => array(
				'values' => forminator_build_autofill_providers( $providers ),
			),
		);

		return $autofill_settings;
	}

	/**
	 * Field front-end markup
	 *
	 * @since 1.0
	 *
	 * @param array                  $field Field.
	 * @param Forminator_Render_Form $views_obj Forminator_Render_Form object.
	 * @param array                  $draft_value Draft value.
	 *
	 * @return mixed
	 */
	public function markup( $field, $views_obj, $draft_value = null ) {

		$settings    = $views_obj->model->settings;
		$this->field = $field;

		$html        = '';
		$id          = self::get_property( 'element_id', $field );
		$name        = $id;
		$id          = self::get_field_id( $id );
		$required    = $this->get_property( 'required', $field, false );
		$ariareq     = 'false';
		$placeholder = $this->sanitize_value( $this->get_property( 'placeholder', $field ) );
		$value       = esc_html( self::get_post_data( $name, $this->get_property( 'default', $field ) ) );
		$label       = esc_html( $this->get_property( 'field_label', $field, '' ) );
		$description = $this->get_property( 'description', $field, '' );
		$design      = $this->get_form_style( $settings );

		$descr_position = self::get_description_position( $field, $settings );

		if ( (bool) $required ) {
			$ariareq = 'true';
		}

		if ( isset( $draft_value['value'] ) ) {

			$value = esc_attr( $draft_value['value'] );

		} elseif ( $this->has_prefill( $field ) ) {
			// We have pre-fill parameter, use its value or $value.
			$value = $this->get_prefill( $field, $value );
		}

		$website = array(
			'type'          => 'url',
			'name'          => $name,
			'value'         => $value,
			'placeholder'   => $placeholder,
			'id'            => $id,
			'class'         => 'forminator-input forminator-website--field' . ( 'basic' === $design ? ' input-text s' : '' ),
			'data-required' => $required,
			'aria-required' => $ariareq,
		);

		$html .= '<div class="forminator-field">';

			$html .= self::create_input(
				$website,
				$label,
				$description,
				$required,
				$descr_position,
			);

		$html .= '</div>';

		return apply_filters( 'forminator_field_website_markup', $html, $id, $required, $placeholder, $value );
	}

	/**
	 * Return string with scheme part if needed
	 *
	 * @since 1.1
	 *
	 * @param string $url URL.
	 *
	 * @return string
	 */
	public function add_scheme_url( $url ) {
		if ( empty( $url ) ) {
			return $url;
		}
		$parts = wp_parse_url( $url );
		if ( false !== $parts ) {
			if ( ! isset( $parts['scheme'] ) ) {
				$url = 'http://' . $url;
			}
		}

		return $url;
	}

	/**
	 * Return field inline validation rules
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_validation_rules() {
		$field              = $this->field;
		$id                 = self::get_property( 'element_id', $field );
		$rules              = '"' . $this->get_id( $field ) . '": {' . "\n";
		$validation_enabled = self::get_property( 'validation', $field, false, 'bool' );

		if ( $this->is_required( $field ) ) {
			$rules .= '"required": true,';
		}

		if ( $validation_enabled ) {
			$rules .= '"validurl": true,';
		}

		$rules .= '"url": false,';
		$rules .= '},' . "\n";

		return apply_filters( 'forminator_field_website_validation_rules', $rules, $id, $field );
	}

	/**
	 * Return field inline validation errors
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_validation_messages() {
		$field              = $this->field;
		$id                 = $this->get_id( $field );
		$validation_enabled = self::get_property( 'validation', $field, false, 'bool' );
		$validation_message = self::get_property( 'validation_message', $field, self::FIELD_PROPERTY_VALUE_NOT_EXIST );
		$required_message   = self::get_property( 'required_message', $field, self::$default_required_messages[ $this->type ] );
		if ( self::FIELD_PROPERTY_VALUE_NOT_EXIST === $validation_message ) {
			$validation_message = self::get_property( 'validation_text', $field, '' );
		}

		$validation_message = htmlentities( $validation_message );

		$messages = '"' . $id . '": {' . "\n";

		if ( $this->is_required( $field ) ) {

			$required_message = apply_filters(
				'forminator_website_field_required_validation_message',
				$required_message,
				$field,
				$id
			);
			$messages        .= '"required": "' . forminator_addcslashes( $required_message ) . '",' . "\n";
		}

		$validation_message = ! empty( $validation_message ) ? $validation_message : esc_html__( 'Please enter a valid Website URL (e.g. https://wpmudev.com/).', 'forminator' );
		if ( $validation_enabled ) {

			$validation_message = apply_filters(
				'forminator_website_field_custom_validation_message',
				$validation_message,
				$id,
				$field,
				$validation_enabled
			);
		}
		$messages .= '"validurl": "' . forminator_addcslashes( $validation_message ) . '",' . "\n";
		$messages .= '},' . "\n";

		$messages = apply_filters(
			'forminator_website_field_validation_message',
			$messages,
			$id,
			$field,
			$validation_enabled
		);

		return $messages;
	}

	/**
	 * Field back-end validation
	 *
	 * @since 1.0
	 *
	 * @param array        $field Field.
	 * @param array|string $data Data.
	 */
	public function validate( $field, $data ) {
		$id                 = self::get_property( 'element_id', $field );
		$validation_enabled = self::get_property( 'validation', $field, false, 'bool' );
		$validation_message = self::get_property( 'validation_message', $field, self::FIELD_PROPERTY_VALUE_NOT_EXIST );
		$required_message   = self::get_property( 'required_message', $field, esc_html( self::$default_required_messages[ $this->type ] ) );
		if ( self::FIELD_PROPERTY_VALUE_NOT_EXIST === $validation_message ) {
			$validation_message = self::get_property( 'validation_text', $field, '' );
		}

		$validation_message = htmlentities( $validation_message );

		if ( $this->is_required( $field ) ) {

			if ( empty( $data ) ) {

				$this->validation_message[ $id ] = apply_filters(
					'forminator_website_field_required_validation_message',
					$required_message,
					$id,
					$field
				);
			}
		}
		if ( $validation_enabled && ! empty( $data ) ) {
			if ( ! filter_var( $data, FILTER_VALIDATE_URL ) ) {
				$this->validation_message[ $id ] = apply_filters(
					'forminator_website_field_custom_validation_message',
					$validation_message,
					$id,
					$field
				);
			}
		}
	}


	/**
	 * Sanitize data
	 *
	 * @since 1.0.2
	 *
	 * @param array        $field Field.
	 * @param array|string $data - the data to be sanitized.
	 *
	 * @return array|string $data - the data after sanitization
	 */
	public function sanitize( $field, $data ) {
		$original_data = $data;
		// Sanitize.
		$data = esc_url_raw( $data );

		return apply_filters( 'forminator_field_website_sanitize', $data, $field, $original_data );
	}
}
