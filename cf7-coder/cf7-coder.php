<?php
/**
 * Plugin Name:       CF7 HTML Editor
 * Plugin URI:        https://wordpress.org/cf7-coder
 * Description:       Add HTML editor to Contact Form 7.
 * Version:           1.0.1
 * Author:            Wow-Company
 * Author URI:        https://wow-estore.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       cf7-coder
 * Domain Path:       /languages
 *
 * PHP version 7.4
 *
 * @category    Wordpress_Plugin
 * @package     Wow_Plugin
 * @copyright   2021 Wow-Company
 * @license     GNU Public License
 * @version     0.1
 */

if ( ! defined( "ABSPATH" ) ) {
	exit();
}

class CF7_Coder {
	public function __construct() {
		add_action( "admin_enqueue_scripts", [ $this, "style_script" ] );
		add_action( 'wpcf7_admin_misc_pub_section', [ $this, 'wpcf7_add_test_mode' ] );
		add_filter( 'wpcf7_contact_form_properties', [ $this, 'wpcf7_add_properties' ] );
		add_action( 'wpcf7_save_contact_form', [ $this, 'wpcf7_save' ] );
		add_filter( 'do_shortcode_tag', [ $this, 'wpcf7_frontend' ], 10, 4 );

		// Conditional loading of CF7 assets
		if ( get_option( 'wpcf7_load_assets_shortcode' ) ) {
			add_action( 'wp_enqueue_scripts', [ $this, 'dequeue_cf7_assets' ], 100 );
		}
	}

	// Include script and style in CF7 page
	public function style_script( $hook ) {
		$page_new = 'contact_page_wpcf7-new';
		$page     = 'toplevel_page_wpcf7';


		if ( $page === $hook || $page_new === $hook ) {
			$version = '1.1';

			wp_enqueue_script( 'code-editor' );
			wp_enqueue_style( 'code-editor' );
			wp_enqueue_script( 'htmlhint' );


			$url_style = plugin_dir_url( __FILE__ ) . 'assets/style.css';
			wp_enqueue_style( "coder-wpcf7", $url_style, array(), $version );

			$url_matirial = plugin_dir_url( __FILE__ ) . 'assets/material.css';
			wp_enqueue_style( "coder-wpcf7-matirial", $url_matirial, array(), $version );

			$url_script = plugin_dir_url( __FILE__ ) . 'assets/script.js';
			wp_enqueue_script( "coder-wpcf7", $url_script, [ "jquery" ], $version, false );
		}
	}


	// Add checkbox 'Test Mode' in sidebar
	public function wpcf7_add_test_mode() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verified by CF7
		$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : '-1';
		$checked = get_post_meta( $post_id, '_wpcf7_test_mode', true );
		?>
        <div class="misc-pub-section">
            <label class="wpcf7-test-mode">
                <input value="1" type="checkbox" name="wpcf7-test-mode" <?php checked( $checked ); ?>>
				<?php esc_html_e( 'Test Mode', 'cf7-coder' ); ?>
                <sup class="has-tooltip" data-tooltip="The Form will only be displayed for administrators.">ℹ</sup>
            </label>
        </div>
		<?php
		$checked = get_post_meta( $post_id, '_wpcf7_remove_auto_tags', true );
		?>
        <div class="misc-pub-section">
            <label class="wpcf7-remove-auto-tags">
                <input value="1" type="checkbox" name="wpcf7-remove-auto-tags" <?php checked( $checked ); ?>>
				<?php esc_html_e( 'Remove Auto tags p and br', 'cf7-coder' ); ?>
            </label>
        </div>
		<?php
		$checked = get_post_meta( $post_id, '_wpcf7_codemiror_dark', true );
		?>
        <div class="misc-pub-section">
            <label class="wpcf7-remove-auto-tags">
                <input value="1" type="checkbox" id="wpcf7_codemiror_dark"
                       name="wpcf7_codemiror_dark" <?php checked( $checked ); ?>>
				<?php esc_html_e( 'Enable dark theme (Material)', 'cf7-coder' ); ?>
            </label>
        </div>
        <div class="misc-pub-section">
            <label class="wpcf7-load-assets">
                <input value="1" type="checkbox" name="wpcf7_load_assets_shortcode" <?php checked( get_option( 'wpcf7_load_assets_shortcode' ) ); ?>>
				<?php esc_html_e( 'Load scripts only on pages with shortcode', 'cf7-coder' ); ?>
                <sup class="has-tooltip on-left" data-tooltip="CF7 scripts and styles will only be loaded on pages containing the contact form shortcode.">ℹ</sup>
            </label>
        </div>
		<?php
		$redirect_enabled = get_post_meta( $post_id, '_wpcf7_redirect_enabled', true );
		$redirect_url = get_post_meta( $post_id, '_wpcf7_redirect_url', true );
		$redirect_acf_field = get_post_meta( $post_id, '_wpcf7_redirect_acf_field', true );
		?>
        <div class="misc-pub-section">
            <label class="wpcf7-redirect">
                <input value="1" type="checkbox" id="wpcf7_redirect_enabled" name="wpcf7-redirect-enabled" <?php checked( $redirect_enabled ); ?>>
				<?php esc_html_e( 'Redirect after submit', 'cf7-coder' ); ?>
                <sup class="has-tooltip on-left" data-tooltip="Redirect to a URL after successful form submission.">ℹ</sup>
            </label>
            <div id="wpcf7-redirect-url-wrap" style="margin-top: 8px; <?php echo $redirect_enabled ? '' : 'display: none;'; ?>">
                <input type="url" name="wpcf7-redirect-url" id="wpcf7-redirect-url"
                       value="<?php echo esc_attr( $redirect_url ); ?>"
                       placeholder="https://example.com/thank-you" style="width: 100%;">
				<?php if ( class_exists( 'ACF' ) ) : ?>
					<?php $acf_field = get_post_meta( $post_id, '_wpcf7_redirect_acf_field', true ); ?>
                    <input type="text" name="wpcf7-redirect-acf-field" id="wpcf7-redirect-acf-field"
                           value="<?php echo esc_attr( $acf_field ); ?>"
                           placeholder="ACF field name" style="width: 100%; margin-top: 5px;">
                    <small style="color: #666;">ACF field from current page (overrides URL above)</small>
				<?php endif; ?>
				<?php $redirect_new_tab = get_post_meta( $post_id, '_wpcf7_redirect_new_tab', true ); ?>
				<?php $redirect_download = get_post_meta( $post_id, '_wpcf7_redirect_download', true ); ?>
                <label style="display: block; margin-top: 8px;">
                    <input type="checkbox" name="wpcf7-redirect-new-tab" value="1" <?php checked( $redirect_new_tab ); ?>>
					<?php esc_html_e( 'Open in new tab', 'cf7-coder' ); ?>
                </label>
                <label style="display: block; margin-top: 4px;">
                    <input type="checkbox" name="wpcf7-redirect-download" value="1" <?php checked( $redirect_download ); ?>>
					<?php esc_html_e( 'Force download', 'cf7-coder' ); ?>
                </label>
            </div>
        </div>
		<?php
		$hide_form = get_post_meta( $post_id, '_wpcf7_hide_form', true );
		?>
        <div class="misc-pub-section">
            <label class="wpcf7-hide-form">
                <input value="1" type="checkbox" name="wpcf7-hide-form" <?php checked( $hide_form ); ?>>
				<?php esc_html_e( 'Hide form after submit', 'cf7-coder' ); ?>
                <sup class="has-tooltip on-left" data-tooltip="Hide the form after successful submission, show only the success message.">ℹ</sup>
            </label>
        </div>
		<?php
		$remove_refill = get_post_meta( $post_id, '_wpcf7_remove_refill', true );
		?>
        <div class="misc-pub-section">
            <label class="wpcf7-remove-refill">
                <input value="1" type="checkbox" name="wpcf7-remove-refill" <?php checked( $remove_refill ); ?>>
				<?php esc_html_e( 'Remove refill', 'cf7-coder' ); ?>
                <sup class="has-tooltip on-left" data-tooltip="Clear form fields after validation error instead of keeping entered values.">ℹ</sup>
            </label>
        </div>
		<?php
		$disable_submit = get_post_meta( $post_id, '_wpcf7_disable_submit', true );
		?>
        <div class="misc-pub-section">
            <label class="wpcf7-disable-submit">
                <input value="1" type="checkbox" name="wpcf7-disable-submit" <?php checked( $disable_submit ); ?>>
				<?php esc_html_e( 'Disable submit button while sending', 'cf7-coder' ); ?>
                <sup class="has-tooltip on-left" data-tooltip="Disable submit button during form submission to prevent double submissions.">ℹ</sup>
            </label>
        </div>
		<?php
		$prefill_url = get_post_meta( $post_id, '_wpcf7_prefill_url', true );
		?>
        <div class="misc-pub-section">
            <label class="wpcf7-prefill-url">
                <input value="1" type="checkbox" name="wpcf7-prefill-url" <?php checked( $prefill_url ); ?>>
				<?php esc_html_e( 'Pre-fill fields from URL', 'cf7-coder' ); ?>
                <sup class="has-tooltip on-left" data-tooltip="Fill form fields from URL parameters (e.g., ?your-email=test@example.com).">ℹ</sup>
            </label>
        </div>
		<?php
		$ga_event = get_post_meta( $post_id, '_wpcf7_ga_event', true );
		$ga_event_name = get_post_meta( $post_id, '_wpcf7_ga_event_name', true );
		?>
        <div class="misc-pub-section">
            <label class="wpcf7-ga-event">
                <input value="1" type="checkbox" id="wpcf7_ga_event" name="wpcf7-ga-event" <?php checked( $ga_event ); ?>>
				<?php esc_html_e( 'GA/GTM Event on submit', 'cf7-coder' ); ?>
                <sup class="has-tooltip on-left" data-tooltip="Send event to Google Analytics/GTM dataLayer on successful submission.">ℹ</sup>
            </label>
            <div id="wpcf7-ga-event-wrap" style="margin-top: 8px; <?php echo ! empty( $ga_event ) ? '' : 'display: none;'; ?>">
                <input type="text" name="wpcf7-ga-event-name" id="wpcf7-ga-event-name"
                       value="<?php echo esc_attr( $ga_event_name ); ?>"
                       placeholder="cf7_form_submit" style="width: 100%;">
                <small style="color: #666;">Event name for dataLayer</small>
            </div>
        </div>
		<?php
		$scroll_to_message = get_post_meta( $post_id, '_wpcf7_scroll_to_message', true );
		?>
        <div class="misc-pub-section">
            <label class="wpcf7-scroll-to-message">
                <input value="1" type="checkbox" name="wpcf7-scroll-to-message" <?php checked( $scroll_to_message ); ?>>
				<?php esc_html_e( 'Scroll to message after submit', 'cf7-coder' ); ?>
                <sup class="has-tooltip on-left" data-tooltip="Automatically scroll to success/error message after form submission.">ℹ</sup>
            </label>
        </div>
		<?php
		$auto_hide_message = get_post_meta( $post_id, '_wpcf7_auto_hide_message', true );
		$auto_hide_timeout = get_post_meta( $post_id, '_wpcf7_auto_hide_timeout', true );
		if ( empty( $auto_hide_timeout ) ) {
			$auto_hide_timeout = 5;
		}
		?>
        <div class="misc-pub-section">
            <label class="wpcf7-auto-hide-message">
                <input value="1" type="checkbox" id="wpcf7_auto_hide_message" name="wpcf7-auto-hide-message" <?php checked( $auto_hide_message ); ?>>
				<?php esc_html_e( 'Auto-hide success message', 'cf7-coder' ); ?>
                <sup class="has-tooltip on-left" data-tooltip="Automatically hide success message after specified seconds.">ℹ</sup>
            </label>
            <div id="wpcf7-auto-hide-wrap" style="margin-top: 8px; <?php echo ! empty( $auto_hide_message ) ? '' : 'display: none;'; ?>">
                <input type="number" name="wpcf7-auto-hide-timeout" id="wpcf7-auto-hide-timeout"
                       value="<?php echo esc_attr( $auto_hide_timeout ); ?>"
                       min="1" max="60" style="width: 60px;"> <?php esc_html_e( 'seconds', 'cf7-coder' ); ?>
            </div>
        </div>
        <div class="misc-pub-section">
            <a href="https://wordpress.org/plugins/wp-coder/" target="_blank">Check out WP Coder – advanced code
                injection</a>
        </div>
		<?php
	}

	// Add properties for form
	public function wpcf7_add_properties( $properties ) {
		$more_properties = array(
			'wpcf7_test_mode'        => '',
			'wpcf7_remove_auto_tags' => '',
			'wpcf7_codemiror_dark'   => '',
		);

		return array_merge( $more_properties, $properties );
	}

	// Save custom properties
	// phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce verified by Contact Form 7
	public function wpcf7_save( $contact_form ) {
		$post_id    = $contact_form->id();
		$properties = $contact_form->get_properties();

		$properties['wpcf7_test_mode']        = isset( $_POST['wpcf7-test-mode'] ) ? '1' : '';
		$properties['wpcf7_remove_auto_tags'] = isset( $_POST['wpcf7-remove-auto-tags'] ) ? '1' : '';
		$properties['wpcf7_codemiror_dark']   = isset( $_POST['wpcf7_codemiror_dark'] ) ? '1' : '';

		$contact_form->set_properties( $properties );

		// Save global option for conditional assets loading
		$load_assets_shortcode = isset( $_POST['wpcf7_load_assets_shortcode'] ) ? '1' : '';
		update_option( 'wpcf7_load_assets_shortcode', $load_assets_shortcode );

		// Save redirect enabled checkbox
		$redirect_enabled = isset( $_POST['wpcf7-redirect-enabled'] ) ? '1' : '';
		update_post_meta( $post_id, '_wpcf7_redirect_enabled', $redirect_enabled );

		// Save redirect URL
		$redirect_url = isset( $_POST['wpcf7-redirect-url'] ) ? esc_url_raw( wp_unslash( $_POST['wpcf7-redirect-url'] ) ) : '';
		update_post_meta( $post_id, '_wpcf7_redirect_url', $redirect_url );

		// Save ACF field name for redirect
		$acf_field = isset( $_POST['wpcf7-redirect-acf-field'] ) ? sanitize_text_field( wp_unslash( $_POST['wpcf7-redirect-acf-field'] ) ) : '';
		update_post_meta( $post_id, '_wpcf7_redirect_acf_field', $acf_field );

		// Save redirect new tab option
		$redirect_new_tab = isset( $_POST['wpcf7-redirect-new-tab'] ) ? '1' : '';
		update_post_meta( $post_id, '_wpcf7_redirect_new_tab', $redirect_new_tab );

		// Save redirect download option
		$redirect_download = isset( $_POST['wpcf7-redirect-download'] ) ? '1' : '';
		update_post_meta( $post_id, '_wpcf7_redirect_download', $redirect_download );

		// Save hide form option
		$hide_form = isset( $_POST['wpcf7-hide-form'] ) ? '1' : '';
		update_post_meta( $post_id, '_wpcf7_hide_form', $hide_form );

		// Save remove refill option
		$remove_refill = isset( $_POST['wpcf7-remove-refill'] ) ? '1' : '';
		update_post_meta( $post_id, '_wpcf7_remove_refill', $remove_refill );

		// Save disable submit button option
		$disable_submit = isset( $_POST['wpcf7-disable-submit'] ) ? '1' : '';
		update_post_meta( $post_id, '_wpcf7_disable_submit', $disable_submit );

		// Save pre-fill from URL option
		$prefill_url = isset( $_POST['wpcf7-prefill-url'] ) ? '1' : '';
		update_post_meta( $post_id, '_wpcf7_prefill_url', $prefill_url );

		// Save GA/GTM event options
		$ga_event = isset( $_POST['wpcf7-ga-event'] ) ? '1' : '';
		update_post_meta( $post_id, '_wpcf7_ga_event', $ga_event );

		$ga_event_name = isset( $_POST['wpcf7-ga-event-name'] ) ? sanitize_text_field( wp_unslash( $_POST['wpcf7-ga-event-name'] ) ) : '';
		update_post_meta( $post_id, '_wpcf7_ga_event_name', $ga_event_name );

		// Save scroll to message option
		$scroll_to_message = isset( $_POST['wpcf7-scroll-to-message'] ) ? '1' : '';
		update_post_meta( $post_id, '_wpcf7_scroll_to_message', $scroll_to_message );

		// Save auto-hide message options
		$auto_hide_message = isset( $_POST['wpcf7-auto-hide-message'] ) ? '1' : '';
		update_post_meta( $post_id, '_wpcf7_auto_hide_message', $auto_hide_message );

		$auto_hide_timeout = isset( $_POST['wpcf7-auto-hide-timeout'] ) ? absint( $_POST['wpcf7-auto-hide-timeout'] ) : 5;
		if ( $auto_hide_timeout < 1 ) {
			$auto_hide_timeout = 1;
		}
		if ( $auto_hide_timeout > 60 ) {
			$auto_hide_timeout = 60;
		}
		update_post_meta( $post_id, '_wpcf7_auto_hide_timeout', $auto_hide_timeout );
	}
	// phpcs:enable WordPress.Security.NonceVerification.Missing

	// Work with Frontend
	public function wpcf7_frontend( $output, $tag, $atts, $m ) {
		if ( $tag === 'contact-form-7' ) {
			// Enqueue CF7 assets if conditional loading is enabled
			if ( get_option( 'wpcf7_load_assets_shortcode' ) ) {
				$this->enqueue_cf7_assets();
			}

			if ( ! function_exists( 'wpcf7_get_contact_form_by_hash' ) ) {
				return $output;
			}

			$form = wpcf7_get_contact_form_by_hash( $atts['id'] );

			if ( ! $form instanceof WPCF7_ContactForm ) {
				return $output;
			}

			$form_id = $form->id();

			$remove_tags = get_post_meta( $form_id, '_wpcf7_remove_auto_tags', true );
			if ( ! empty( $remove_tags ) ) {
				$output = str_replace( array( '<p>', '</p>', '<br>', '<br/>', '<br />' ), '', $output );
			}

			$test_mode = get_post_meta( $form_id, '_wpcf7_test_mode', true );
			if ( ! empty( $test_mode ) && ! current_user_can( 'administrator' ) ) {
				$output = '';
			}

			// Redirect after submit - check if enabled first
			$redirect_enabled = get_post_meta( $form_id, '_wpcf7_redirect_enabled', true );
			if ( ! empty( $redirect_enabled ) ) {
				$redirect_url = '';
				$acf_field_name = get_post_meta( $form_id, '_wpcf7_redirect_acf_field', true );

				// Check ACF field first (from current page)
				if ( ! empty( $acf_field_name ) && function_exists( 'get_field' ) ) {
					$current_post_id = get_the_ID();
					$acf_url = get_field( $acf_field_name, $current_post_id );
					if ( ! empty( $acf_url ) ) {
						$redirect_url = $acf_url;
					}
				}

				// Fallback to static URL only if ACF field name is not set
				if ( empty( $redirect_url ) && empty( $acf_field_name ) ) {
					$redirect_url = get_post_meta( $form_id, '_wpcf7_redirect_url', true );
				}

				if ( ! empty( $redirect_url ) ) {
				$redirect_new_tab  = get_post_meta( $form_id, '_wpcf7_redirect_new_tab', true );
				$redirect_download = get_post_meta( $form_id, '_wpcf7_redirect_download', true );

				// Determine redirect action: download > new tab > same window
				if ( ! empty( $redirect_download ) ) {
					$redirect_action = 'var link = document.createElement("a"); link.href = "' . esc_url( $redirect_url ) . '"; link.download = ""; document.body.appendChild(link); link.click(); document.body.removeChild(link);';
				} elseif ( ! empty( $redirect_new_tab ) ) {
					$redirect_action = 'window.open("' . esc_url( $redirect_url ) . '", "_blank");';
				} else {
					$redirect_action = 'window.location.href = "' . esc_url( $redirect_url ) . '";';
				}

					$output .= '<script>
						document.addEventListener("wpcf7mailsent", function(event) {
							if (event.detail.contactFormId == ' . (int) $form_id . ') {
								' . $redirect_action . '
							}
						});
					</script>';
				}
			}

			// Hide form after submit
			$hide_form = get_post_meta( $form_id, '_wpcf7_hide_form', true );
			if ( ! empty( $hide_form ) ) {
				$output .= '<script>
					document.addEventListener("wpcf7mailsent", function(event) {
						if (event.detail.contactFormId == ' . (int) $form_id . ') {
							var wrapper = event.target.closest(".wpcf7");
							if (wrapper) {
								var form = wrapper.querySelector("form");
								if (form) {
									Array.from(form.children).forEach(function(child) {
										if (!child.classList.contains("wpcf7-response-output")) {
											child.style.display = "none";
										}
									});
								}
							}
						}
					});
				</script>';
			}

			// Remove refill - clear form fields after validation error
			$remove_refill = get_post_meta( $form_id, '_wpcf7_remove_refill', true );
			if ( ! empty( $remove_refill ) ) {
				$output .= '<script>
					document.addEventListener("wpcf7invalid", function(event) {
						if (event.detail.contactFormId == ' . (int) $form_id . ') {
							var wrapper = event.target.closest(".wpcf7");
							if (wrapper) {
								var form = wrapper.querySelector("form");
								if (form) { form.reset(); }
							}
						}
					});
				</script>';
			}

			// Disable submit button while sending
			$disable_submit = get_post_meta( $form_id, '_wpcf7_disable_submit', true );
			if ( ! empty( $disable_submit ) ) {
				$output .= '<script>
					(function() {
						var formId = ' . (int) $form_id . ';
						document.addEventListener("wpcf7beforesubmit", function(event) {
							if (event.detail.contactFormId == formId) {
								var wrapper = event.target.closest(".wpcf7");
								if (wrapper) {
									var btn = wrapper.querySelector("input[type=submit], button[type=submit]");
									if (btn) {
										btn.disabled = true;
										btn.dataset.originalValue = btn.value || btn.textContent;
										if (btn.tagName === "INPUT") {
											btn.value = "' . esc_js( __( 'Sending...', 'cf7-coder' ) ) . '";
										} else {
											btn.textContent = "' . esc_js( __( 'Sending...', 'cf7-coder' ) ) . '";
										}
									}
								}
							}
						});
						document.addEventListener("wpcf7mailsent", function(event) {
							if (event.detail.contactFormId == formId) {
								var wrapper = event.target.closest(".wpcf7");
								if (wrapper) {
									var btn = wrapper.querySelector("input[type=submit], button[type=submit]");
									if (btn) {
										btn.disabled = false;
										if (btn.tagName === "INPUT") {
											btn.value = btn.dataset.originalValue;
										} else {
											btn.textContent = btn.dataset.originalValue;
										}
									}
								}
							}
						});
						document.addEventListener("wpcf7mailfailed", function(event) {
							if (event.detail.contactFormId == formId) {
								var wrapper = event.target.closest(".wpcf7");
								if (wrapper) {
									var btn = wrapper.querySelector("input[type=submit], button[type=submit]");
									if (btn) {
										btn.disabled = false;
										if (btn.tagName === "INPUT") {
											btn.value = btn.dataset.originalValue;
										} else {
											btn.textContent = btn.dataset.originalValue;
										}
									}
								}
							}
						});
						document.addEventListener("wpcf7invalid", function(event) {
							if (event.detail.contactFormId == formId) {
								var wrapper = event.target.closest(".wpcf7");
								if (wrapper) {
									var btn = wrapper.querySelector("input[type=submit], button[type=submit]");
									if (btn) {
										btn.disabled = false;
										if (btn.tagName === "INPUT") {
											btn.value = btn.dataset.originalValue;
										} else {
											btn.textContent = btn.dataset.originalValue;
										}
									}
								}
							}
						});
					})();
				</script>';
			}

			// Pre-fill fields from URL parameters
			$prefill_url = get_post_meta( $form_id, '_wpcf7_prefill_url', true );
			if ( ! empty( $prefill_url ) ) {
				$output .= '<script>
					(function() {
						var wrapper = document.querySelector(".wpcf7[data-wpcf7-id=\"' . (int) $form_id . '\"]");
						if (wrapper) {
							var params = new URLSearchParams(window.location.search);
							params.forEach(function(value, key) {
								var field = wrapper.querySelector("[name=\"" + key + "\"]");
								if (field) {
									if (field.type === "checkbox" || field.type === "radio") {
										if (field.value === value || value === "1" || value === "true") {
											field.checked = true;
										}
									} else if (field.tagName === "SELECT") {
										var option = field.querySelector("option[value=\"" + value + "\"]");
										if (option) { field.value = value; }
									} else {
										field.value = value;
									}
								}
							});
						}
					})();
				</script>';
			}

			// GA/GTM Event on successful submit
			$ga_event = get_post_meta( $form_id, '_wpcf7_ga_event', true );
			if ( ! empty( $ga_event ) ) {
				$ga_event_name = get_post_meta( $form_id, '_wpcf7_ga_event_name', true );
				if ( empty( $ga_event_name ) ) {
					$ga_event_name = 'cf7_form_submit';
				}
				$output .= '<script>
					document.addEventListener("wpcf7mailsent", function(event) {
						if (event.detail.contactFormId == ' . (int) $form_id . ') {
							if (typeof dataLayer !== "undefined") {
								dataLayer.push({
									"event": "' . esc_js( $ga_event_name ) . '",
									"cf7FormId": ' . (int) $form_id . ',
									"cf7FormTitle": "' . esc_js( $form->title() ) . '"
								});
							}
						}
					});
				</script>';
			}

			// Scroll to message after submit
			$scroll_to_message = get_post_meta( $form_id, '_wpcf7_scroll_to_message', true );
			if ( ! empty( $scroll_to_message ) ) {
				$output .= '<script>
					(function() {
						var formId = ' . (int) $form_id . ';
						function scrollToMessage(event) {
							if (event.detail.contactFormId == formId) {
								var wrapper = event.target.closest(".wpcf7");
								if (wrapper) {
									var message = wrapper.querySelector(".wpcf7-response-output");
									if (message) {
										message.scrollIntoView({ behavior: "smooth", block: "center" });
									}
								}
							}
						}
						document.addEventListener("wpcf7mailsent", scrollToMessage);
						document.addEventListener("wpcf7mailfailed", scrollToMessage);
						document.addEventListener("wpcf7invalid", scrollToMessage);
					})();
				</script>';
			}

			// Auto-hide success message
			$auto_hide_message = get_post_meta( $form_id, '_wpcf7_auto_hide_message', true );
			if ( ! empty( $auto_hide_message ) ) {
				$auto_hide_timeout = get_post_meta( $form_id, '_wpcf7_auto_hide_timeout', true );
				if ( empty( $auto_hide_timeout ) ) {
					$auto_hide_timeout = 5;
				}
				$output .= '<script>
					document.addEventListener("wpcf7mailsent", function(event) {
						if (event.detail.contactFormId == ' . (int) $form_id . ') {
							var wrapper = event.target.closest(".wpcf7");
							if (wrapper) {
								var message = wrapper.querySelector(".wpcf7-response-output");
								if (message) {
									setTimeout(function() {
										message.style.transition = "opacity 0.5s";
										message.style.opacity = "0";
										setTimeout(function() {
											message.style.display = "none";
											message.style.opacity = "1";
										}, 500);
									}, ' . (int) $auto_hide_timeout * 1000 . ');
								}
							}
						}
					});
				</script>';
			}
		}

		return $output;
	}

	// Enqueue CF7 assets when shortcode is found
	public function enqueue_cf7_assets() {
		if ( function_exists( 'wpcf7_enqueue_scripts' ) ) {
			wpcf7_enqueue_scripts();
		}
		if ( function_exists( 'wpcf7_enqueue_styles' ) ) {
			wpcf7_enqueue_styles();
		}
		// Enqueue reCAPTCHA if available
		if ( function_exists( 'wpcf7_recaptcha_enqueue_scripts' ) ) {
			wpcf7_recaptcha_enqueue_scripts();
		}
	}

	// Dequeue CF7 scripts and styles
	public function dequeue_cf7_assets() {
		wp_dequeue_script( 'contact-form-7' );
		wp_dequeue_style( 'contact-form-7' );
		wp_dequeue_script( 'wpcf7-recaptcha' );
		wp_dequeue_script( 'google-recaptcha' );
	}

}

add_action( 'plugins_loaded', 'wpcf7_coder_load', 999 );
function wpcf7_coder_load() {
	if ( ! class_exists( 'WPCF7' ) ) {
		require_once 'class.wpcf7coder-extension-activation.php';
		$activation = new wpcf7_Coder_Extension_Activation( plugin_dir_path( __FILE__ ), basename( __FILE__ ) );
		$activation = $activation->run();
		deactivate_plugins( plugin_basename( __FILE__ ) );
	} else {
		new CF7_Coder();
	}
}
