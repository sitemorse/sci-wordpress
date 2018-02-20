# sci-wordpress

## Installing
This module is designed to be installed in the same way as any other Wordpress
module through the plugins dialogue.

## Configuration
In Settings > Sitemorse > Main Settings, enter your licence key and select 
which user roles are allowed to publish content even if it fails Sitemorse
Priority checks. The default ports, host name, etc should not be changed unless
advised to do so by Sitemorse.

## Testing
Running an assessment on any piece of content will test the full connection. For
example, from the assessment button whilst editing content or the Sitemorse
button on the admin bar.

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
