=== Dash Notifier ===
Contributors: LiteSpeedTech
Tags: dashboard notify, plugin installer
Requires at least: 4.0
Tested up to: 5.0.3
Stable tag: 1.0
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl.html

This plugin can be used to notify clients with a banner in WordPress dashboard only. Need to be called via API.

== Description ==

This plugin can be used to notify clients with a banner in WordPress dashboard only. Need to be called via API.

To add a new banner, predefine a PHP const `DASH_NOTIFIER_MSG` before hook `setup_theme` like below:

```
define( 'DASH_NOTIFIER_MSG', json_encode( array( 'msg' => 'Your message to display in banner', 'plugin' => 'your_plugin_slug' ) ) ) ;
```

The `your_plugin_slug` is optional. If set, there will generate an one click install button along with the message in banner.

If the plugin is https://wordpress.org/plugins/hello-dolly/, the `your_plugin_slug` will be `hello-dolly`.

== Screenshots ==

1. Dashboard looks

== Changelog ==

= 1.0 - Feb 20 2019 =
* 🎉 Initial Release.