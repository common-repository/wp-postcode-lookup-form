=== WP Postcode Lookup Form ===
Contributors: rkbcomputing
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=2ZPLBQJS2KCH2
Tags: uk postcode, shortcode, lead generation, contact form
Requires at least: 4.0
Tested up to: 4.7
Stable tag:  0.6.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Easily add lead generation functionality to your Wordpress websites with UK address auto-complete.

== Description ==

WP Postcode Lookup Form plugin was designed to help you easily add lead generation functionality to your Wordpress websites with UK address auto-complete.

Features:

* UK Postcode lookup and validation
* UK Telephone number validation
* Customizable email alerts
* Restrict to specified postcodes
* Export submissions as .csv file
* reCAPTCHA integration
* Multi-Page form
* Auto-complete addresses from postcode

== Installation ==

1. Upload "wp-postcode-lookup-form" directory to the "/wp-content/plugins/" directory
1. Activate the plugin through the "Plugins" menu in WordPress
1. Add this shortcode To the page you want to show the form on: [postcode_lookup_form_sc]
1. Create a page to hold the main form contents and place the same shortcode in it
1. Sign up to Google maps API and reCAPTCHA to get your access keys
1. Create a thankyou page (no shortcode needed, just the content for the message). The post content from this page will be displayed on the same page after successful submission, it will not be redirected
1. Go to the options (Postcode Lookup Form in admin menu). In the option called "Postcode form redirect page" select a page for the main form created for the main form (E.g. "/my-lead-gen-form/")
1. In the 'Thankyou Page' field select your tankyou page.
1. Now configure your options for notification emails, reCAPTCHA, Google Maps API key etc
1. You can manage your submissions from the same admin menu

== Frequently asked questions ==

= Can I add fields to the form? =
Not currently but I do plan on adding the abilty in the future, for now you can edit the plugin manually but any updates may override your customizations.

= Does this support US or other countries? =
Not yet, only UK addresses and telephone numbers for now. I may add that functionality in the future.

== Screenshots ==

1. http://rkbcomputing.co.uk/wp-content/uploads/2016/12/postcode-lookup-form.jpg
1. http://rkbcomputing.co.uk/wp-content/uploads/2016/12/postcode-lookup-form-address.jpg

== Changelog ==

= 0.6.0 =
* Changed how the form page selection works. You use a dropdown list to select your main form and thankyou page

= 0.5.8 =
* Changed how the thankyou page works. You now have to create a page manually and enter it's post id in the options

= 0.5.7 =
* Added UK telephone number validation capabilty
* Database table and subissions are now deleted when you uninstall
* Minor bug fixes

= 0.5.5 =
* First public release

== Upgrade notice ==

= 0.5.5 =
* First public release