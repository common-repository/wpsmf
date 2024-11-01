=== Plugin Name ===
Contributors: SchattenMann
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=TJ3A7K6DFDFN8&lc=PT&currency_code=EUR&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted
Tags: posts, smf
Requires at least: 2.9.2
Tested up to: 3.0
Stable tag: 0.4

Automatically posts wordpress post to a SMF board.

== Description ==

Automatically posts wordpress post to a SMF board.

WPSMF adds Wordpress posts as topics in SimpleMachines Forum 2.x, and allows to put a link to the topic below the Wordpress post.

Contains a Synchronize utility for latter use if WP already contains posts.

In order to use you need to provide your SMF (local) Path and map WP users to SMF user ID.

Also only posts in Categories with a corresponding SMF board ID are Synch.

 = WP COMMENT EXPORT IS IN BETA. PLEASE USE IT CAREFULLY = 
 
 = Script tested up to 15000 comments exported without problems! = 

 = Main features: = 

    * SMF post is added when the Wordpress post is published
    * SMF post is updated any time the WP post is updated
    * SMF Comment is added when the Wordpress post is published
    * SMF Comment is updated any time the WP post is updated
	* It is possible to show the link to the topic bellow Wordpress Post
	* It is possible to map a WP Category to a SMF Topic
	* It is possible to map a WP User to a SMF User
	* Supports all HTML
	* Include a "check all" option when using the "synchronize" menu
	* Option to remove WP posts from SMF
	* Remove trackback and pings from comment export
	* Option to enable/disable auto post function
	* Fancy filtering option on export utility

 = Admin page options: = 

	* WP Category to SMF Board ID to post to
	* WP User to SMF User ID to set the post author
    * SMF Forum path
	* Link from Wordpress to Forum text
	* Choose SMF Charset
	* Enable/Disable auto post
	
 = To Do List: = 

	* work with SMF 1.1.11
	* Export WP Users to SMF
	* Export WP Categories to SMF
	* Create uninstall script
	* Import from SMF to WP
	
 = Is it broken? =
 Tell me where so i can fix it!
 
  = Don't you like it? =
 Tell me why so i can improve it!	
 
 = Do you like it? =
 What's missing so you love it? 
 
 = Do you love it? =
 [Buy me a beer!](https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=TJ3A7K6DFDFN8&lc=PT&currency_code=EUR&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted)

== Installation ==

1. Upload WPSMF folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Go to WPSMF Options and fill the required fields
1. Use Sync Util or Start Posting

(Optional) If desired post the following on you template single.php to display SMF Link Text

`<?php 
	if (function_exists(myWPSMF_link_to_smf)){ 
		myWPSMF_link_to_smf(get_the_id());
	}
?>`

== Frequently Asked Questions ==

 = SMF Path: i put the path but it didn't worked! = 
 
that field needs the absolute path to your SMF forum (on the same machine offcourse!)

so if you have for example:

http://www.yourdomain.com/mysmfforum
http://www.yourdomain.com/mywpblog

it will probably be

/home/user/public_html/mysmfforum

so the field would need "/mysmfforum" (without quotes)

the easier way is to search for SSI.php on smf folder.

the path needs to point to that specific file.

== Screenshots ==

1. WPSMF Admin Area
2. Export Result Example

== Changelog ==

= 0.4 =
* NEW : Fancy Filtering options
* NEW : Remove Option
* NEW : Enable/Disable Auto Post

= 0.3.2 =
* NEW : Add option to remove WP posts from SMF
* NEW : PHP code improvements
* NEW : Remove trackback and pings from comment export

= 0.3.1 =
* Bugfix : minor bugfix

= 0.3 =
* NEW : WP comments are now sync (beta stage)
* NEW : Is Possible to chose the SMF Charset (UTF-8 or ISO-8859-1)

= 0.2.1 =
* NEW : Add option to unlink WP posts from SMF
* Changed : Update Admin Layout
* NEW : 100% compatibility with WP 3.0

= 0.2 =
* NEW : include a "check all" option when using the "synchronize" menu
* Bugfix : pauses so that it does not overload the server, when synchronizing huge blogs
* Bugfix : order by ID ASC so posts on WP appears on the same order in SMF
* Bugfix : increase user postcount on SMF
* NEW : add more information, like the total number of blog posts listed etc

= 0.1 =
* Created WPSMF

== Upgrade Notice ==

= 0.2 =
* Improve layout
* Fixed some minor bugs found by shaks
