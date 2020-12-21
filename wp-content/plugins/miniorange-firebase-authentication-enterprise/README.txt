=== Firebase Authentication ===
Contributors: cyberlord92
Donate link: https://miniorange.com
Tags: firebase, authentication, login, sso, jwt
Requires at least: 3.0.1
Tested up to: 5.4
Stable tag: 21.2.0
License: GPLv2 or later
License URI: http://miniorange.com/usecases/miniOrange_User_Agreement.pdf

This plugin allows login into WordPress using Firebase user credentials and keep data in sync between WordPress and Firebase.

== Description ==

This plugin allows you to login or Single Sign-On into WordPress using your Firebase user credentials.
Firebase authentication works using both default WordPress login page and also we support custom login pages.

= Features =
*	**Firebase Authentication** : WordPress login / SSO using Firebase user credentials
*	**Auto Create Users** : After login, new user automatically gets created in WordPress 
*	**Configurable login options** :
	Provide option to login with,
	a) Only Firebase credentials
	b) Only WordPress credentials
	c) Both Firebase and WordPress credentials
*	**Support for Authentication Methods** : Allow login with Firebase User Email and Password Authentication method to sign in into WordPress
*	Keep data in sync between WordPress and Firebase database (Firestore)


== Installation ==

1. Visit `Plugins > Add New`
2. Search for `firebase authentication`. Find and Install `firebase authentication` plugin by miniOrange
3. Activate the plugin

== Frequently Asked Questions ==
= I need help to configure the plugin? =
Please email us at <a href="mailto:info@xecurify.com" target="_blank">info@xecurify.com</a> or <a href="http://miniorange.com/contact" target="_blank">Contact us</a>. You can also submit your query from plugin's configuration page.

= I am locked out of my account and can't login with either my WordPress credentials or Firebase credentials. What should I do? =
Firstly, please check if the `user you are trying to login with` exists in your WordPress. To unlock yourself, rename firebase-authentication plugin name. You will be able to login with your WordPress credentials. After logging in, rename the plugin back to firebase-authentication. If the problem persists, `activate, deactivate and again activate` the plugin.

= For support or troubleshooting help =
Please email us at info@xecurify.com or <a href="https://miniorange.com/contact" target="_blank">Contact us</a>.

== Screenshots ==

1. Configure Firebase Authentication plugin
2. Option to allow WP Administrators to login
3. Result after successful Test Authentication

== Changelog ==

= 1.1.2 =
* Plugin deactivation form

= 1.1.1 =
* Configurable option to allow WP login only to Administrators

= 1.0.0 =
* Initial release

== Upgrade Notice ==
