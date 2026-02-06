=== HTML Editor for Contact Form 7 ===
Contributors: Wpcalc, lobov
Donate link: https://wow-estore.com/
Tags: cf7, contact form 7, html editor, code editor, redirect
Requires at least: 5.0
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add HTML editor to Contact Form 7 with code highlighter and extended form options.

== Description ==
Contact Form 7 plugin allows editing forms with a standard textarea. This addon adds an HTML editor with code highlighter to each contact form and provides many useful options to enhance your forms.

= Editor Features =
* **HTML Editor** with syntax highlighting powered by CodeMirror
* **Dark Theme** (Material) support for comfortable editing
* **Auto-close** brackets and tags
* **Code folding** and line numbers
* **Search and replace** functionality (Ctrl+F)

= Form Behavior Options =
* **Test Mode** - Hide form from non-administrators for testing purposes
* **Remove Auto Tags** - Remove auto-added p and br tags from form output
* **Redirect After Submit** - Redirect users to a custom URL after successful submission
  * Support for ACF fields to get dynamic redirect URL from current page
  * Option to open redirect URL in new tab
  * Option to force file download
* **Hide Form After Submit** - Hide the form and show only success message
* **Disable Submit Button** - Prevent double submissions by disabling button during form submission
* **Pre-fill Fields from URL** - Auto-fill form fields from URL parameters (e.g., ?your-email=test@example.com)
* **GA/GTM Event** - Send custom event to Google Analytics/GTM dataLayer on successful submission
* **Scroll to Message** - Automatically scroll to success/error message after form submission
* **Auto-hide Success Message** - Automatically hide success message after specified seconds
* **Remove Refill** - Clear form fields after validation error

= Performance =
* **Conditional Script Loading** - Load CF7 scripts and styles only on pages with contact form shortcode

To improve the plugin's functions and add new functions, write to us on the support [forum](https://wordpress.org/support/plugin/cf7-coder/).

= Support =
Search for answers and ask your questions at [forum](https://wordpress.org/support/plugin/cf7-coder/) or send requests on the [github](https://github.com/wow-company/cf7-coder/issues).


== Installation ==
* Installation option 1: Find and install this plugin in the `Plugins` -> `Add new` section of your `wp-admin`
* Installation option 2: Download the zip file, then upload the plugin via the wp-admin in the `Plugins` -> `Add new` section. Or unzip the archive and upload the folder to the plugins directory `/wp-content/plugins/` via ftp
* Press `Activate` when you have installed the plugin via dashboard.
* Go to `Contact Form`

== Screenshots ==
1. Contact Form
2. HTML editor


== Changelog ==
= 1.0.1 =
* Fixed: if enable 'Load scripts only on pages with shortcode' recaptcha show only page with shortcode
* Fixed: option 'Remove Auto tags p and br '

= 1.0 =
* Added: Redirect after submit with URL input
* Added: ACF field support for dynamic redirect URL
* Added: Open redirect in new tab option
* Added: Force download option for redirect URL
* Added: Hide form after submit (show only success message)
* Added: Disable submit button while sending to prevent double submissions
* Added: Pre-fill form fields from URL parameters
* Added: GA/GTM Event tracking on successful form submission
* Added: Scroll to message after form submission
* Added: Auto-hide success message with configurable timeout
* Added: Remove refill option (clear fields after validation error)
* Added: Conditional loading of CF7 scripts only on pages with shortcode
* Improved: Tag generator integration with CodeMirror editor

= 0.2 =
* Added: Dark theme support.
* Added: Vertical resize option for the editor.

= 0.1 =
* Initial release