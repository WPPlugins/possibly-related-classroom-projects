=== Possibly Related Classroom Projects ===
Contributors: Social Actions, E. Cooper
Donate link: http://www.socialactions.com
Tags: posts, related, page, philanthropy, donations, education, donorschoose, students, teachers, school, social actions
Requires at least: 2.6
Tested up to: 2.6
Stable tag: 0.31

"Possibly Related Classroom Projects" enables you to share relevant classroom projects from DonorsChoose.org based on the content of your posts.

== Description ==

"Possibly Related Classroom Projects" enables you to share relevant classroom projects from DonorsChoose.org based on the content of your posts. 

DonorsChoose.org is where teachers submit project proposals for materials or experiences their students need to learn and succeed. Anyone can then choose projects to help bring to life. DonorsChoose.org usually has over 14,000 active proposals.

"Possibly Related Classroom Projects" makes it super easy to connect your readers to relevant classroom projects in need of help.

You'll be amazed at the relevancy of many of these classroom projects to your posts (as well as the awesome and imaginative projects that are happening in classrooms around the US).

"Possibly Related Classroom Projects" is a project of Social Actions Labs.
[http://socialactions.com/labs]

For more info about the WordPress plugin, please see our project page.
[http://www.socialactions.com/labs/wordpress-donorschoose-plugin]

For more info. about DonorsChoose.org, please see their Help section.
[http://www.donorschoose.org/help/help.html]

== Screenshots ==

1. This is a screenshot of the DonorsChoose Wordpress plugin -- in a testing environment -- using a post from Beyond-School, which discusses the potential of political & civic engagement in schools. 

== Installation == 

"Possibly Related Classroom Projects" is compatiable with both PHP4 and PHP5. Thanks for your patience!

"Possibly Related Classroom Projects" follows the WordPress standard for adding and installing plugins:

1. Upload the `possibly-related-classroom-projects` directory to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

...And that's it. Everything else is handled by the plugin when it's installed.

== Frequently Asked Questions == 

= Help! My post doesn't show three related projects! =

In order to maintain proper performance on your blog (and be as unobtrusive as possible), if the DonorsChoose.org API doesn't respond in a timely manner, the "Possibly Related Classroom Projects" plugin will essentially turn itself "off" for that particular post, for that particular page view. This sort of thing should occur very rarely.

= This plugin is displaying completely unrelated projects! =

Much like the above question, depending on the circumstances, if the DonorsChoose.org API doesn't respond favorably, the "Possibly Related Classroom Projects" plugin will react accordingly. Occasionally, that means it will display a previously cached result for that particular post, for that particular page view. This is done purely in the interest in maintaining your blog's proper load times. This sort of display should happen rather rarely, however.

= What if I don't want to display the plugin on a post? =

The "Possibly Related Classroom Projects" plugin can be disabled for a particular post by using the tag %NOCP% somewhere within your post. The plugin will recognize and remove the tag while disabling itself for that post.

= The plugin looks different than the screenshot! Help? =

The "Possibly Related Classroom Projects" plugin uses the Wordpress hook wp head (http://codex.wordpress.org/Hook_Reference/wp\_head) to properly link the stylesheet included in its root directory. If the related classroom projects appear to be drastically different on your blog than on other blogs, the wp head hook is probably not being called from your template.

To check, please search for wp head() within your Wordpress template. If it's not to be found, you can either insert it in the proper place within the template or add the CSS in ra style.css to the CSS for your Wordpress blog.

= Ugh. Can I change the display of the plugin? =

Yes! The CSS file used can be found in the root directory of the plugin, ra_style.css. Feel free to edit it to your heart's desire!
