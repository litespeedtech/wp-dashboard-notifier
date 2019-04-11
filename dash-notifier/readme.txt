=== Dash Notifier ===
Contributors: LiteSpeedTech
Tags: dashboard notify, plugin installer
Requires at least: 4.0
Tested up to: 5.1.1
Stable tag: 1.1.2
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl.html

Developers and Sysadmins, use this plugin to add a notification to clients' WordPress Dashboards via API.

== Description ==

This plugin can be used by developers and system administrators to add a notification banner to their clients' WordPress Dashboard. It's useful for broadcasting important messages as well as suggesting plugins that clients' might find useful, and is handled through an API.

To add a new banner, predefine a PHP constant called `DASH_NOTIFIER_MSG` before the `setup_theme` hook, like so:

`
define( 'DASH_NOTIFIER_MSG', json_encode( array( 'msg' => 'Your message to display in banner', 'plugin' => 'your_plugin_slug', 'plugin_name' => 'Your Plugin Name' ) ) ) ;
`

You can define 'DASH_NOTIFIER_MSG' in your own plugin or in `functions.php`, as long as it is before `setup_theme`.

The `plugin` parameter is optional. If set, an install button will be included with the message, allowing the client to install the plugin in one click.

The `plugin_name` parameter is also optional. If `plugin` is provided but `plugin_name` is not, the name will default to the official name found in the WordPress Plugin Directory.

**Example**: If the plugin you'd like to recommend is `https://wordpress.org/plugins/hello-dolly/`, replace `your_plugin_slug` with `hello-dolly` and `Your Plugin Name` with `Hello Dolly`.

**NOTE**: Your clients must have this plugin installed in order for the notification banner to be displayed.

== Screenshots ==

1. Dashboard

== Changelog ==

= 1.1.2 - Apr 11 2019 =
* [Update] Fixed a potential PHP notice in certain PHP environment.

= 1.1.1 - Mar 20 2019 =
* 🐞 Deactivate notifier before uninstalling to avoid warning in plugin list.

= 1.1 - Mar 12 2019 =
* [Tweak] Able to save message when using short init.

= 1.0 - Feb 28 2019 =
* 🎉 Initial Release.