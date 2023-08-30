<?php
// phpcs:ignore Generic.Commenting.DocComment.MissingShort
/** @noinspection AutoloadingIssuesInspection */

namespace WPFormsCaptcha;

use WPForms_Field;
use WPForms_Updater;

/**
 * Captcha field.
 *
 * @since 1.0.0
 */
class Plugin extends WPForms_Field {

	/**
	 * Min & max values to participate in equation and operators.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	public $math;

	/**
	 * Questions to ask.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	public $qs;

	/**
	 * Get a single instance of the addon.
	 *
	 * @since 1.8.0
	 *
	 * @return Plugin
	 */
	public static function get_instance() {

		static $instance;

		if ( ! $instance ) {
			$instance = new self();
		}

		return $instance;
	}

	/**
	 * Init class.
	 *
	 * @since 1.0.0
	 */
	public function init() {

		// Define field type information.
		$this->name     = esc_html__( 'Custom Captcha', 'wpforms-captcha' );
		$this->keywords = esc_html__( 'spam, math, maths, question', 'wpforms-captcha' );
		$this->type     = 'captcha';
		$this->icon     = 'fa-question-circle';
		$this->order    = 300;
		$this->group    = 'fancy';
		$this->math     = [
			'min' => 1,
			'max' => 15,
			'cal' => [ '+', '*' ],
		];
		$this->qs       = [
			1 => [
				'question' => esc_html__( 'What is 7+4?', 'wpforms-captcha' ),
				'answer'   => esc_html__( '11', 'wpforms-captcha' ),
			],
		];

		$this->hooks();
	}

	/**
	 * Add hooks.
	 *
	 * @since 1.8.0
	 */
	private function hooks() {

		// Apply wpforms_math_captcha filters when theme functions.php is loaded.
		add_action( 'after_setup_theme', [ $this, 'after_setup_theme' ] );

		// Form frontend javascript.
		add_action( 'wpforms_frontend_js', [ $this, 'frontend_js' ] );

		// Admin form builder enqueues.
		add_action( 'wpforms_builder_enqueues', [ $this, 'admin_builder_enqueues' ] );

		// Remove the field from saved data.
		add_filter( 'wpforms_process_after_filter', [ $this, 'process_remove_field' ], 10, 3 );

		// Set field as required by default.
		add_filter( 'wpforms_field_new_required', [ $this, 'field_default_required' ], 10, 2 );

		// Define additional field properties.
		add_filter( 'wpforms_field_properties_captcha', [ $this, 'field_properties' ], 5, 3 );

		// Do not display this field on the entry edit admin page.
		add_filter( "wpforms_pro_admin_entries_edit_is_field_displayable_{$this->type}", '__return_false' );

		// Ignore the field inside the entry preview field.
		add_filter( 'wpforms_pro_fields_entry_preview_get_ignored_fields', [ $this, 'ignore_entry_preview' ] );

		// Remove empty values before saving the form in Form Builder.
		add_filter( 'wpforms_save_form_args', [ $this, 'save_form' ], 11, 3 );

		add_action( 'wpforms_updater', [ $this, 'updater' ] );
	}

	/**
	 * Load the plugin updater.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key WPForms license key.
	 */
	public function updater( $key ) {

		new WPForms_Updater(
			[
				'plugin_name' => 'WPForms Captcha',
				'plugin_slug' => 'wpforms-captcha',
				'plugin_path' => plugin_basename( WPFORMS_CAPTCHA_FILE ),
				'plugin_url'  => trailingslashit( plugin_dir_url( WPFORMS_CAPTCHA_FILE ) ),
				'remote_url'  => WPFORMS_UPDATER_API,
				'version'     => WPFORMS_CAPTCHA_VERSION,
				'key'         => $key,
			]
		);
	}

	/**
	 * Run when theme functions.php is loaded.
	 *
	 * @since 1.2.1
	 */
	public function after_setup_theme() {

		// Apply wpforms_math_captcha filters.
		// phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName, WPForms.Comments.PHPDocHooks.RequiredHookDocumentation
		$this->math = apply_filters( 'wpforms_math_captcha', $this->math );
	}

	/**
	 * Enqueue frontend field js.
	 *
	 * @since 1.0.0
	 *
	 * @param array $forms Forms on the current page.
	 */
	public function frontend_js( $forms ) {

		if (
			wpforms_has_field_type( 'captcha', $forms, true ) === true ||
			wpforms()->frontend->assets_global()
		) {

			$min = wpforms_get_min_suffix();

			wp_enqueue_script(
				'wpforms-captcha',
				WPFORMS_CAPTCHA_URL . "assets/js/wpforms-captcha{$min}.js",
				[ 'jquery', 'wpforms' ],
				WPFORMS_CAPTCHA_VERSION,
				true
			);

			$strings = [
				'max'      => $this->math['max'],
				'min'      => $this->math['min'],
				'cal'      => $this->math['cal'],
				'errorMsg' => esc_html__( 'Incorrect answer.', 'wpforms-captcha' ),
			];

			wp_localize_script( 'wpforms-captcha', 'wpforms_captcha', $strings );
		}
	}

	/**
	 * Enqueue for the admin form builder.
	 *
	 * @since 1.0.0
	 */
	public function admin_builder_enqueues() {

		$min = wpforms_get_min_suffix();

		// JavaScript.
		wp_enqueue_script(
			'wpforms-builder-custom-captcha',
			WPFORMS_CAPTCHA_URL . "assets/js/admin-builder-captcha{$min}.js",
			[ 'jquery', 'wpforms-builder' ],
			WPFORMS_CAPTCHA_VERSION
		);

		// CSS.
		wp_enqueue_style(
			'wpforms-builder-custom-captcha',
			WPFORMS_CAPTCHA_URL . "assets/css/admin-builder-captcha{$min}.css",
			[],
			WPFORMS_CAPTCHA_VERSION
		);

		// Localize strings.
		wp_localize_script(
			'wpforms-builder-custom-captcha',
			'wpforms_builder_custom_captcha',
			[
				'error_not_empty_question' => esc_html__( 'Custom Captcha field should contain at least one not empty question.', 'wpforms-captcha' ),
			]
		);
	}

	/**
	 * Define additional field properties.
	 *
	 * @since 1.3.8
	 *
	 * @param array $properties Field properties.
	 * @param array $field      Field settings.
	 * @param array $form_data  Form data and settings.
	 *
	 * @return array
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function field_properties( $properties, $field, $form_data ) {

		$field_id = absint( $field['id'] );
		$format   = ! empty( $field['format'] ) ? $field['format'] : 'math';

		// Input Primary: adjust name.
		$properties['inputs']['primary']['attr']['name'] = "wpforms[fields][{$field_id}][a]";

		// Input Primary: adjust class.
		$properties['inputs']['primary']['class'][] = 'a';

		// Input Primary: type dat attr.
		$properties['inputs']['primary']['data']['rule-wpf-captcha'] = $format;

		// Input Primary: mark this field as wrapped.
		$properties['inputs']['primary']['data']['is-wrapped-field'] = true;

		return $properties;
	}

	/**
	 * Whether current field can be populated dynamically.
	 *
	 * @since 1.5.0
	 *
	 * @param array $properties Field properties.
	 * @param array $field      Current field specific data.
	 *
	 * @return bool
	 */
	public function is_dynamic_population_allowed( $properties, $field ) {

		return false;
	}

	/**
	 * Whether current field can be populated dynamically.
	 *
	 * @since 1.5.0
	 *
	 * @param array $properties Field properties.
	 * @param array $field      Current field specific data.
	 *
	 * @return bool
	 */
	public function is_fallback_population_allowed( $properties, $field ) {

		return false;
	}

	/**
	 * Field should default to being required.
	 *
	 * @since 1.0.0
	 *
	 * @param bool  $required Required status, true is required.
	 * @param array $field    Field settings.
	 *
	 * @return bool
	 */
	public function field_default_required( $required, $field ) {

		if ( $field['type'] === 'captcha' ) {
			return true;
		}

		return $required;
	}

	/**
	 * Don't store captcha fields since it's for validation only.
	 *
	 * @since 1.0.0
	 *
	 * @param array $fields    Field settings.
	 * @param array $entry     Form $_POST.
	 * @param array $form_data Form data and settings.
	 *
	 * @return array
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function process_remove_field( $fields, $entry, $form_data ) {

		foreach ( $fields as $id => $field ) {
			// Remove captcha from saved data.
			if ( ! empty( $field['type'] ) && $field['type'] === 'captcha' ) {
				unset( $fields[ $id ] );
			}
		}

		return $fields;
	}

	/**
	 * Pre-process field data before saving it in form_data when editing form.
	 *
	 * @since 1.5.0
	 *
	 * @param array $form Form array, usable with wp_update_post.
	 * @param array $data Data retrieved from $_POST and processed.
	 * @param array $args Update form arguments.
	 *
	 * @return array
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function save_form( $form, $data, $args ) {

		$form_data = json_decode( stripslashes( $form['post_content'] ), true );

		if ( empty( $form_data['fields'] ) ) {
			return $form;
		}

		foreach ( (array) $form_data['fields'] as $key => $field ) {

			if ( empty( $field['type'] ) || $field['type'] !== 'captcha' ) {
				continue;
			}

			if ( $field['format'] !== 'qa' ) {
				continue;
			}

			$form_data['fields'][ $key ]['questions'] = ! empty( $form_data['fields'][ $key ]['questions'] ) ?
				$this->remove_empty_questions( $form_data['fields'][ $key ]['questions'] ) :
				[];
		}

		$form['post_content'] = wpforms_encode( $form_data );

		return $form;
	}

	/**
	 * Field options panel inside the builder.
	 *
	 * @since 1.0.0
	 *
	 * @param array $field Field settings.
	 */
	public function field_options( $field ) {

		// Defaults.
		$format = ! empty( $field['format'] ) ? esc_attr( $field['format'] ) : 'math';
		$qs     = ! empty( $field['questions'] ) ? $field['questions'] : $this->qs;
		$qs     = array_filter( $qs );

		// Field is always required.
		$this->field_element(
			'text',
			$field,
			[
				'type'  => 'hidden',
				'slug'  => 'required',
				'value' => '1',
			]
		);

		/*
		 * Basic field options.
		 */

		// Options open markup.
		$this->field_option(
			'basic-options',
			$field,
			[
				'markup' => 'open',
			]
		);

		// Label.
		$this->field_option( 'label', $field );

		// Format.
		$lbl = $this->field_element(
			'label',
			$field,
			[
				'slug'    => 'format',
				'value'   => esc_html__( 'Type', 'wpforms-captcha' ),
				'tooltip' => esc_html__( 'Select type of captcha to use.', 'wpforms-captcha' ),
			],
			false
		);
		$fld = $this->field_element(
			'select',
			$field,
			[
				'slug'    => 'format',
				'value'   => $format,
				'options' => [
					'math' => esc_html__( 'Math', 'wpforms-captcha' ),
					'qa'   => esc_html__( 'Question and Answer', 'wpforms-captcha' ),
				],
			],
			false
		);

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'format',
				'content' => $lbl . $fld,
			]
		);

		// Questions.
		$lbl = $this->field_element(
			'label',
			$field,
			[
				'slug'    => 'questions',
				'value'   => esc_html__( 'Questions and Answers', 'wpforms-captcha' ),
				'tooltip' => esc_html__( 'Add questions to ask the user. Questions are randomly selected.', 'wpforms-captcha' ),
			],
			false
		);
		$fld = sprintf(
			'<ul data-next-id="%s" data-field-id="%d" data-field-type="%s" class="choices-list">',
			max( array_keys( $qs ) ) + 1,
			esc_attr( $field['id'] ),
			esc_attr( $this->type )
		);

		foreach ( $qs as $key => $value ) {
			$fld .= '<li data-key="' . absint( $key ) . '">';
			$fld .= sprintf(
				'<input type="text" name="fields[%1$d][questions][%2$s][question]" value="%3$s" data-prev-value="%3$s" class="question" placeholder="%4$s">',
				(int) $field['id'],
				esc_attr( $key ),
				esc_attr( $value['question'] ),
				esc_html__( 'Question', 'wpforms-captcha' )
			);
			$fld .= '<a class="add" href="#"><i class="fa fa-plus-circle"></i></a><a class="remove" href="#"><i class="fa fa-minus-circle"></i></a>';
			$fld .= sprintf(
				'<input type="text" name="fields[%d][questions][%s][answer]" value="%s" class="answer" placeholder="%s">',
				(int) $field['id'],
				esc_attr( $key ),
				esc_attr( $value['answer'] ),
				esc_html__( 'Answer', 'wpforms-captcha' )
			);
			$fld .= '</li>';
		}
		$fld .= '</ul>';

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'questions',
				'content' => $lbl . $fld,
				'class'   => $format === 'math' ? 'wpforms-hidden' : '',
			]
		);

		// Description.
		$this->field_option( 'description', $field );

		// Options close markup.
		$this->field_option(
			'basic-options',
			$field,
			[
				'markup' => 'close',
			]
		);

		/*
		 * Advanced field options.
		 */

		// Options open markup.
		$this->field_option(
			'advanced-options',
			$field,
			[
				'markup' => 'open',
			]
		);

		// Size.
		$this->field_option(
			'size',
			$field,
			[
				'class' => $format === 'math' ? 'wpforms-hidden' : '',
			]
		);

		// Custom CSS classes.
		$this->field_option( 'css', $field );

		// Placeholder.
		$this->field_option( 'placeholder', $field );

		// Hide Label.
		$this->field_option( 'label_hide', $field );

		// Options close markup.
		$this->field_option(
			'advanced-options',
			$field,
			[
				'markup' => 'close',
			]
		);
	}

	/**
	 * Field preview inside the builder.
	 *
	 * @since 1.0.0
	 *
	 * @param array $field Field settings.
	 */
	public function field_preview( $field ) {

		// Define data.
		$placeholder = ! empty( $field['placeholder'] ) ? $field['placeholder'] : '';
		$format      = ! empty( $field['format'] ) ? $field['format'] : 'math';
		$num1        = wp_rand( $this->math['min'], $this->math['max'] );
		$num2        = wp_rand( $this->math['min'], $this->math['max'] );
		$cal         = $this->math['cal'][ wp_rand( 0, count( $this->math['cal'] ) - 1 ) ];
		$questions   = ! empty( $field['questions'] ) ? $field['questions'] : $this->qs;

		// Label.
		$this->field_preview_option( 'label', $field );

		$first_question = array_shift( $questions );
		?>

		<div class="format-selected-<?php echo esc_attr( $format ); ?> format-selected">

			<span class="wpforms-equation"><?php echo esc_html( "$num1 $cal $num2 = " ); ?></span>

			<p class="wpforms-question"><?php echo wp_kses( $first_question['question'], wpforms_builder_preview_get_allowed_tags() ); ?></p>

			<input type="text" placeholder="<?php echo esc_attr( $placeholder ); ?>" class="primary-input" readonly>

		</div>

		<?php
		// Description.
		$this->field_preview_option( 'description', $field );
	}

	/**
	 * Field display on the form front-end.
	 *
	 * @since 1.0.0
	 *
	 * @param array $field      Field settings.
	 * @param array $deprecated Deprecated array.
	 * @param array $form_data  Form data and settings.
	 *
	 * @noinspection PhpParameterNameChangedDuringInheritanceInspection
	 */
	public function field_display( $field, $deprecated, $form_data ) {

		// Define data.
		$primary                             = $field['properties']['inputs']['primary'];
		$format                              = $form_data['fields'][ $field['id'] ]['format'];
		$field_id                            = "wpforms-{$form_data['id']}-field_{$field['id']}";
		$desc_id                             = "{$field_id}-question";
		$primary['attr']['aria-describedby'] = empty( $primary['attr']['aria-describedby'] ) ? $desc_id : $primary['attr']['aria-describedby'] . ' ' . $desc_id;

		if ( $format === 'math' ) {
			// Math Captcha.
			?>
			<div class="wpforms-captcha-math">
				<span <?php echo wpforms_html_attributes( $desc_id, [ 'wpforms-captcha-equation' ] ); ?>>
					<?php

					if ( defined( 'REST_REQUEST' ) || is_admin() || wp_doing_ajax() ) {

						// Instead of outputting empty tags we can prefill them with random values.
						// This way we'll get correct visual appearance of the field even if JavaScript file wasn't loaded.
						// This is useful for displaying previews in Gutenberg and, potentially, in other page builders.
						printf(
							'<span class="n1">%1$s</span>
							<span class="cal">%2$s</span>
							<span class="n2">%3$s</span>',
							esc_html( wp_rand( $this->math['min'], $this->math['max'] ) ),
							esc_html( $this->math['cal'][ wp_rand( 0, count( $this->math['cal'] ) - 1 ) ] ),
							esc_html( wp_rand( $this->math['min'], $this->math['max'] ) )
						);

					} else {

						?>
						<span class="n1"></span>
						<span class="cal"></span>
						<span class="n2"></span>
						<?php

					}

					?>
					<span class="e">=</span>
				</span>
				<?php
				printf(
					'<input type="text" %s %s>',
					wpforms_html_attributes( $primary['id'], $primary['class'], $primary['data'], $primary['attr'] ),
					esc_attr( $primary['required'] )
				);
				?>
				<input type="hidden" name="wpforms[fields][<?php echo (int) $field['id']; ?>][cal]" class="cal">
				<input type="hidden" name="wpforms[fields][<?php echo (int) $field['id']; ?>][n2]" class="n2">
				<input type="hidden" name="wpforms[fields][<?php echo (int) $field['id']; ?>][n1]" class="n1">
			</div>
			<?php
		} else {
			// Back-compat: remove invalid questions with empty question or answer value.
			$form_data['fields'][ $field['id'] ]['questions'] = ! empty( $form_data['fields'][ $field['id'] ]['questions'] ) ?
				$this->remove_empty_questions( $form_data['fields'][ $field['id'] ]['questions'] ) :
				[];

			// Do not output the field if, for some reason, all questions have been filtered out as invalid.
			if ( empty( $form_data['fields'][ $field['id'] ]['questions'] ) ) {
				return;
			}

			// Question and Answer captcha.
			$qid = $this->random_question( $field, $form_data );
			$q   = $form_data['fields'][ $field['id'] ]['questions'][ $qid ]['question'];

			printf(
				'<p %s>%s</p>',
				wpforms_html_attributes( $desc_id, [ 'wpforms-captcha-question' ] ),
				esc_html( $q )
			);
			?>

			<?php
			$primary['data']['a'] = esc_attr( $form_data['fields'][ $field['id'] ]['questions'][ $qid ]['answer'] );

			printf(
				'<input type="text" %s %s>',
				wpforms_html_attributes( $primary['id'], $primary['class'], $primary['data'], $primary['attr'] ),
				esc_attr( $primary['required'] )
			);
			?>

			<input type="hidden" name="wpforms[fields][<?php echo (int) $field['id']; ?>][q]" value="<?php echo esc_attr( $qid ); ?>">

			<?php
		}
	}

	/**
	 * Select a random question.
	 *
	 * @since 1.0.0
	 *
	 * @param array $field     Field settings.
	 * @param array $form_data Form data and settings.
	 *
	 * @return bool|int
	 */
	public function random_question( $field, $form_data ) {

		if ( empty( $form_data['fields'][ $field['id'] ]['questions'] ) ) {
			return false;
		}

		$index = array_rand( $form_data['fields'][ $field['id'] ]['questions'] );

		if (
			! isset(
				$form_data['fields'][ $field['id'] ]['questions'][ $index ]['question'],
				$form_data['fields'][ $field['id'] ]['questions'][ $index ]['answer']
			)
		) {
			$index = $this->random_question( $field, $form_data );
		}

		return $index;
	}

	/**
	 * Validate field on form submit.
	 *
	 * @since 1.0.0
	 *
	 * @param int   $field_id     Field ID.
	 * @param array $field_submit Submitted field value.
	 * @param array $form_data    Form data and settings.
	 */
	public function validate( $field_id, $field_submit, $form_data ) { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		// Math captcha.
		if ( $form_data['fields'][ $field_id ]['format'] === 'math' ) {

			// All calculation fields are required.
			if (
				( empty( $field_submit['a'] ) && $field_submit['a'] !== '0' ) ||
				empty( $field_submit['n1'] ) ||
				empty( $field_submit['cal'] ) ||
				empty( $field_submit['n2'] )
			) {
				wpforms()->get( 'process' )->errors[ $form_data['id'] ][ $field_id ] = wpforms_get_required_label();

				return;
			}

			$n1  = $field_submit['n1'];
			$cal = $field_submit['cal'];
			$n2  = $field_submit['n2'];
			$a   = (int) trim( $field_submit['a'] );
			$x   = false;

			switch ( $cal ) {
				case '+':
					$x = ( $n1 + $n2 );
					break;

				case '-':
					$x = ( $n1 - $n2 );
					break;

				case '*':
					$x = ( $n1 * $n2 );
					break;
			}

			if ( $x !== $a ) {
				wpforms()->get( 'process' )->errors[ $form_data['id'] ][ $field_id ] = esc_html__( 'Incorrect answer', 'wpforms-captcha' );

				return;
			}
		}

		if ( $form_data['fields'][ $field_id ]['format'] === 'qa' ) {

			// All fields are required.
			if (
				! isset( $field_submit['q'], $field_submit['a'] ) ||
				(
					empty( $field_submit['q'] ) &&
					$field_submit['q'] !== '0'
				) || (
					empty( $field_submit['a'] ) &&
					$field_submit['a'] !== '0'
				)
			) {
				wpforms()->get( 'process' )->errors[ $form_data['id'] ][ $field_id ] = wpforms_get_required_label();

				return;
			}

			if ( strtolower( trim( $field_submit['a'] ) ) !== strtolower( trim( $form_data['fields'][ $field_id ]['questions'][ $field_submit['q'] ]['answer'] ) ) ) {
				wpforms()->get( 'process' )->errors[ $form_data['id'] ][ $field_id ] = esc_html__( 'Incorrect answer', 'wpforms-captcha' );
			}
		}
	}

	/**
	 * Don't display captcha field on the entry edit admin page.
	 *
	 * @since 1.3.1
	 * @deprecated 1.4.0
	 *
	 * @param array $fields Do not display fields.
	 *
	 * @return array Do not display fields with this field included.
	 * @noinspection PhpUnused
	 */
	public function entries_edit_fields_dont_display( $fields ) {

		_deprecated_function(
			__FUNCTION__,
			'1.4.0 of the WPForms plugin',
			sprintf(
				'Use the wpforms_pro_admin_entries_edit_is_field_displayable_%s hook instead',
				esc_attr( $this->type )
			)
		);

		$fields   = (array) $fields;
		$fields[] = $this->type;

		return $fields;
	}

	/**
	 * Ignore the captcha field inside the entry preview field.
	 *
	 * @since 1.3.2
	 *
	 * @param array $fields Ignored fields.
	 *
	 * @return array
	 */
	public function ignore_entry_preview( $fields ) {

		$fields[] = $this->type;

		return $fields;
	}

	/**
	 * Remove invalid questions - with empty question and/or answer value.
	 *
	 * @since 1.5.0
	 *
	 * @param array $questions All questions and answers.
	 *
	 * @return array
	 */
	private function remove_empty_questions( $questions ) {

		return array_filter(
			$questions,
			static function( $question ) {

				return isset( $question['question'], $question['answer'] ) &&
					! wpforms_is_empty_string( $question['question'] ) &&
					! wpforms_is_empty_string( $question['answer'] );
			}
		);
	}
}
