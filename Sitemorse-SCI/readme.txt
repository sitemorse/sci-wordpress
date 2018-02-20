=== Plugin Name ===
Contributors: (this should be a list of wordpress.org userid's) XXX
Donate link: NA
Tags: sitemorse, accessibility, admin, content
Requires at least: 3.7
Tested up to: 4.5.2
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The Sitemorse CMS Integration (SCI) Plugin is an easy-to-use tool for accessing Sitemorse testing and metrics during the development process.

== Description ==

The Sitemorse CMS Integration (SCI) Plugin is an easy-to-use tool for accessing Sitemorse testing and metrics during the development process. It makes it simple to integrate Sitemorse into your daily workflow and ensure all pages conform fully to standards before they are published.

Sitemorse SCI effectively provides a web proxy to Sitemorse servers to retrieve the content to be tested. Typically there will be no need for firewall changes since the only connection is outgoing, no incoming connections are needed. The connection is secure over SSL.

= Usage Guide =

After the plugin has been activated Sitemorse Tasks will appear on the
Wordpress Dashboard. All pages will be flagged as 'Assess Now', since nothing
has been assessed yet.

To Assess a page simply re-publish it. You should Assess each page to ensure
your site is valid.

The Assess button is added to the Edit page. You can check any changes before
there published but clicking this, which will load a Sitemorse preview.

Pages are published by clicking the Publish button. The page will be checked
before it is published and will give you a chance to fix any issues.


== Installation ==

1. Receive licence key from Sitemorse.
2. Upload the plugin files to the `/wp-content/plugins/Sitemorse-SCI` directory, or install the plugin through the WordPress plugins screen directly.
3. Activate the plugin through the 'Plugins' screen in WordPress.
4. Enter the licence key in the Settings->Sitemorse section. Normally other settings can be left with the default values.
5. For further information please contact your Sitemorse representative.


== Testing ===
Running an assessment on any piece of content will test the full connection. For
example, 

If for some reason, the assessment fails to execute, you can test the connection
from the configuration dialogue. Settings > Sitemorse > Connection Settings,
click the test button. The test can take a couple of minutes to complete. It
validate the license key and tests both the home page of sitemorse.com and the 
home page of the Wordpress instance. The results page shows all the variables 
for the current connection with the messages that have been passed to and from 
the Sitemorse SCI servers.

If the configuration test button is successful but for some reason a particular
content item fails to be assessed, then you can select debug mode. Settings > 
Sitemorse > Connection Settings Debug Mode check box. This intercepts every 
assessment and issues debug results in the same way as the test button but for 
the particular content item.

== Frequently Asked Questions ==

=Can I prevent publishing of content with issues
Yes, select 'Prevent Publishing' in the settings section.

=Can I configure my own custom brand rules
Yes, please contact your Sitemorse representative.

=Do I need to open ports on my firewall
Typically there will be no need for firewall changes since the only connection
is outgoing, no incoming connections are needed.

=Can I control which parts of my templates are checked?
Yes, in the 'Marked Content' settings section. You can select which template
output functions will be checked.

=Is the connection to sitemorse servers secure?
Yes, the connection is over SSL

=Can I connect via a proxy
Yes, this can be configured in the Advanced Settings section



== Screenshots ==

1. This screen shot description corresponds to screenshot-1.(png|jpg|jpeg|gif).
Note that the screenshot is taken from
the /assets directory or the directory that contains the stable readme.txt
(tags or trunk). Screenshots in the /assets 
directory take precedence. For example, `/assets/screenshot-1.png` would win
over `/tags/4.3/screenshot-1.png` 
(or jpg, jpeg, gif). XXX



== Changelog ==

= 1.0 =
Initial release.

= 1.1 =
Test and debug functions.


== Upgrade Notice ==

= 1.0 =
Initial release.
