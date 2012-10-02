=== heyyou ===
Contributors: davidsword, heyshauna.com
Donate link: http://wordpress.org/donate
Tags: custom post types for pages, Wordpress developer framework, posts in page
Requires at least: 3.0
Tested up to: 3.4.2
Stable tag: 1.3.2

heyyou is a plugin adds unique dynamic custom post types directly in a Wordpress Page & a framework that supplies options for rapid development


== Description ==

** If you expereince any issues: before rating, please contact: we will help you ** 

This plugin (currently in beta stage) is intended as a developers framework - it's goal to turn Wordpress into more of a CMS by adding a wide range of functionalities, settings, and functions to easily:

1. Add (dynamic) Custom Post Types directly within a Wordpress Page - below the pages main content
2. Serve as a Development Framework - housing an assortment of presets with an extensive admin settings page
3. Simplifying Wordpress, but adding options to cut down unneeded admin items

* Visit: [hey-you.ca](http://hey-you.ca/)
* Tweet: [@david_sword](http://twitter.com/david_sword/#)


= Elaborating on some primary features, heyyou: =

* Creation of Dynamic Custom Post Types:
	* option on every page, directly within that page, to add page-specific custom post types on the fly, with wide range of preset and meta configuration options, 
	* built-in per-page-pagination for longer lists of posts, directly within a page
	* drag and drop ordering of posts
	* create categories of posts within a page
	* add "attachments" with a checkbox to create galleries for posts
	* an interface to eaisly add meta fields to lists, ie: "Age", "Media"
	* outputting: a simple textarea with %macros% placeholders and HTML for formatting the posts output (useful for: FAQ, Staff Listing, Press Releases, ect.)
	* outputting: can bypass default %macros% output and use PHP in your theme instead
	* importing/exporting for transferring/duplicating page configurations
* Custom Media Library:
	* Media items listed as a simplified grid thumbnails,
	* Cleaned CSS for showing file information
* Media Categories
	* Sort media items into categories
	* functions for listing media items in dropdown menu for dynamic forms
* Page Options (Options in setting page to add the following into a pages 'heyyou page settings' metabox):
	* Add secondary blurb to pages (to use in template)
	* Add Twitter / Facebook social buttons
	* Dynamic Page Meta Field(s) (for things like having a featured image's "Photo Credit" meta field - easier to use than Wordpress's custom fields)
	* Hide Wordpress Page title
	* Hide Wordpress Page Content (.entry-content)
	* Set a hexadecimal color (for themes that use a different color on every page)
	* Add Attachments to page
	* Disable auto placement of attachments
* Settings Page with options to: 
	* Configure which metaboxes user types have access to and receive by default (hiding client unnecessary metaboxes like Wordpress News and Plugin Widget Feed)
	* Adding a (visually attractive) under construction "Notice" banner to site
	* Edit Admin Menu / Navigations (to remove menu items like "Tools" or "Links" if irrelevant for your theme)
	* Edit main < meta > keyword/description
	* Setup database-to-email backup
	* (TinyMCE) Add dynamic styles to TinyMCE via easy to use GUI (i.e.: add "Blue Italic" to your themes styled drop down)
	* Include < scripts > via check-box for technologies (like Mootools, Attachments)
	* Add Featured &or Secondary images for your theme's pages &or posts (no more remembering function)
	* Add Excerpts on page (no more remembering function)
* Miscellaneous:
	* Adds Greeting Widget
	* TinyMCE gets simplified / limited buttons to a single row, new 'line' button to split content
	* Creates "Sub-Admin" and "Client" user types
	* *& much much more.. (documentation still being written)*
	* Cleans up HTML header info outputted by wp_head()
	* Outputs the dynamic custom post types under their corresponding pages
	* Adds .mobile class to < body > when detected
	* Javascript instant-reveal "..read more" replace <! -- more -- > *see documentation*
	* shortcode for retreving text list of recent tweets
* *& much more.. (documentation still being written)*


= SHOWCASE: Live sites powered by heyyou =

* heyshauna sites
	* [heyshauna.com](http://heyshauna.com/)
	* [joeink.ca](http://joeink.ca)
	* [steminteriordesign.com](http://steminteriordesign.com)
	* [judegriebel.com](http://judegriebel.com)
	* [susanpoint.com](http://susanpoint.com)
	* [spokendress.com](http://spokendress.com)
	* [pushfestival.ca](http://pushfestival.ca/)
	* [lissavino.com](http://lissavino.com/)
	* [greenspacedesigns.com](http://greenspacedesigns.com/)
	* [kiddpivot.org](http://kiddpivot.org/)
	* [visionselective.org](http://visionselective.org/)
	* [cocoondesigns.ca](http://cocoondesigns.ca/)
	* [eponymous.ca](http://eponymous.ca/)
	* [kiddpivot.org](http://kiddpivot.org/)
	* [leisuretravel2000.ca](http://leisuretravel2000.ca/)
	* [thomascannell.com](http://thomascannell.com/)
	* [wenweidance.ca](http://wenweidance.ca/)
	* [lesliemcguffin.com](http://lesliemcguffin.com/)
	* [trenchgallery.com](http://trenchgallery.com/)
	* [newmusic.org](http://newmusic.org/)
	* [ekistics.ca](http://ekistics.ca/)
	* [playwrightstheatre.com](http://playwrightstheatre.com/)
	* *& much more on the way*
* David Sword sites
	* [davidsword.ca](http://davidsword.ca/)
	* [ianmorris.ca](http://ianmorris.ca/)
	* [interioracademy.com](http://interioracademy.com/)
	* [shaunbalbar.ca](http://shaunbalbar.ca/)
	* [createddifferently.com](http://createddifferently.com/)
	* [expbc.com](http://expbc.com/)
	* [hey-you.ca](http://hey-you.ca/)



= CREDIT: External Plugins =

The following plugins are hard coded into heyyou - they were edited enough that they are no longer updatable.. a thousand thank you's to the authors, please check out their and if you have money to donate, I highly recommend visiting any of these three sites and pledging there.. *heyyou* will print a **function already exists** error if any of the following plugins are used.

* [Jonathan Christopher](http://mondaybynoon.com/)'s [Attachment Plugin](http://wordpress.org/extend/plugins/attachments/)
	* included directly into the plugins core- with an option to turn it off - the interface for attachments was majorly altered to a simplified thumbnail grid, and many edits where written in the plugins source to handle and play nicely with the media categories.
* [Hart Associates (Rick Mead)](http://www.hartinc.com)'s [Media Categories](https://sites.google.com/site/medialibarycategories/)
	* integrated directly into the plugins core and manipulated to work with plugin more effectively
* [Chris Scott](http://vocecommuncations.com/)'s [Multiple Post Thumbnails](http://wordpress.org/extend/plugins/multiple-post-thumbnails/)
	* built into heyyou as well, for adding secondary images in a single click


= NOTICE: Important!!! =

This plugin is a *very large* framework design specifically for development of websites by [heyshauna.com](http://heyshauna.com/) & [davidsword.ca](http://davidsword.ca/). It essentially has the functionality of 20+ plugins (see "Features" list in description). *heyyou* is not likely to be compatible with all themes or plugins and is recommended for intermediate-to-advance Wordpress users only that are committed to experimenting with this plugin. **Always** backup your database and files before installing a plugin of this scale. It is recommended that potential users test this plugin in a test environment prior to installing on a live site. Please view the documentation at : [hey-you.ca](http://hey-you.ca/)

`ALL Documentation and plugin are at BETA LEVEL, the plugin is currently being developed / written.`

[hey-you.ca](http://hey-you.ca/)


== Installation ==

1. Upload the `/heyyou/` folder into the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Visit "*heyyou*" Settings in the admin
4. Go into a Page and use the "*heyyou*" metabox by 'enabling' *heyyou* posts for that page


== Frequently Asked Questions ==

= What are dynamic custom post types? =

Dynamic Custom Post Types - ("DCPT")'s - are posts that you can add directly into a Wordpress Page. Unlike a "Post" a DCPT by default only has a title: within that page, there's a configuration form where you can configure that DCPT to whatever required, some options include:

 * Adding a blurb and/or a date field
 * Categories / Groupings for the DCPT's
 * Meta fields for Media items, text, code, URLs
 * & much much more..
 
This is very different then using Wordpress `register_post_type()` method as the posts go directly within 

= What type of Pages are dynamic custom post types useful for? =

The entire origin of this project bases from wanting the following within a Wordpress page:

 * A FAQ listing, simple, directly below a pages content
 * Staff BIO listing
 * Simple news posts (paginated titles + date + description)

= How do I add Posts to a page? =

Once installed, follow [this 4 step tutorial](http://hey-you.ca/overview/posts-for-pages/).

= I still don't understand what this is =

 * Visit: [hey-you.ca](http://hey-you.ca/)
 * Tweet: [@david_sword](http://twitter.com/david_sword/)


== Screenshots ==

1. Dynamic Custom Post Types added directly to the page the post types will show in. Simplifying CTP's for your clients.
2. Custom post types appear just as they do in the admin - directly below the corresponding pages main content
3. Some of the options in the Settings page to configure what scripts, tools, theme_support, to include on the site; options to change the admin navigation, menu bar, and more.
4. 4-Steps to adding posts to a page ([full image here](http://hey-you.ca/overview/posts-for-pages/))

== Changelog ==


= 1.3.2 =
* Sept 29, 2012
* added: TinyMCE "fullscreen" button back by default
* added: TinyMCE customization function `hys_custom_tinymce()`, allowing modification of buttons per/theme
* added: twitter shortcode [hys_tweets id='' count='' refresh_rate='']
* added: "message to display when no posts" to show dynamic text on empty hys post pages.. adds class hys_noposts to hys_output
* added: %if:defined..% arguments can now be in upper or lower case
* changed: twitter feed from get_option to transient
* changed: ties using external resources, every js and img now from self..  
* changed: name of js.js to heyyou.js
* changed: header scripts and styles now use enqueue_script/enqueue_style
* changed: move ie7-9 HTML5 compat to footer for cleaner header
* fixed: %if:defined:attach% argument can now be used to hide gallery related info when using %attach% in output format instead of auto-gallery placement
* fixed: issue where <!--more--> tag was inserting self on a new line between <h#> and other tags. <!--more--> is now pushed to beggining of line.. which still might result in HTML breaking as a tag might open on previous line and close after <!--more-->
* fixed: issue where mobile redirect may of taken to https instead of http because of unanticipated capitalization of $_SERVER['HTTPS'] variable.
* removed: auto redirecting of normal site to mobile on first load (this stops error from happening on mobile Wordpress apps, also allows for resposive web design)

= 1.3.1 =
* July 23rd, 2012
* fixed: issue with force download where if !allow_url_fopen, errors would occur
* fixed: issue where non-admins could see advance (developer) heyyou options/setting
* fixed: max length of string in text drop-down for form select media lists

= 1.3 = 
* July 17th, 2012
* fixed: issue where posts were posting to bottom, regardless if "new posts: top" was set
* added: hyperlink to lightbox notice

= 1.2 = 
* June 10th, 2012
* fixed: viewport for more tablet friendly rendering
* removed: dynamic viewport pixel width option

= 1.1 =
* June 5th, 2012
* FIXED: whitespace issue to hopefully resolve "The plugin does not have a valid header" issue

= 1.0 =
* May 27th, 2012
* BETA RELEASE! http://hey-you.ca/
* FIXED: issue where super admins may of had hys_subadmin limitations
* UPDATED: Lightbox-needed-notice points to webpage for install instructions; only shows if lightbox is selected to be used
* FIXED: html issue when lightbox was set
* UPDATED: disable lightbox from being auto-included on new installs
* UPDATED: divided settings page into 4x panels instead of long scroll. sorted for better documentation
* UPDATED: cleaned up metaboxes: for checkbox with hierarchy only show child options if parent option is checked, hiding rarely used advance options in "+advance" more/less
* ADDED: Social media URL feilds in the admin for eaisly dynamically adding/changing hard coded social tools/links in themes

= 0.0.0.4 =
* May 11th, 2012
* FIXED: issue with media items uncategorizing after hiting "save changes" while using the "attach" button. The "as if they moved overnight" issue

= 0.0.0.3 =
* April 28th, 2012
* FIXED: drop and drag issue on posts without cats set, and uncateogized. 
* FIXED: added timestamp to duplicate button so as URL's are not the same (allowing you to re-duplicate same file again and again instead of clicking the current url (dead link))
* FIXED: issue with media dropdown sticking on edit post

= 0.0.0.2 =
* April 7th, 2012
* REMOVED "+NEW" and "COMMENTS" tabs from new Wordpress Admin Tool Bar.. solves issue where sites w/out posts don't find +post
* REMOVED: "uncategorized" post heading page categories aren't turned on
* REMOVED: ie6 debunker message and option
* UPDATED: hys_attach_attachments() depreciated, now auto uses: hys_photo_gallery()
* UPDATED: compressed: hys_style.css & js.js
* UPDATED: metabox's in page revisited: new "heyyou Page Options" pulled client editable features from "heyyou Page Configuration", config only for admins now
* UPDATED: changed hys_list_media() WP_Query to get_posts() to ensure no error happens w/ redeclaring the loop
* UPDATED: media library category item counter replaced with same as actual cat output, counting should now be more accurate 
* FIXED: bug: when a category is assigned and posts and categorized, if admin turns off categories, posts also uncategorize: categories only show when categories are turned on
* FIXED: lightbox title/captions issues..
* FIXED: jquery include now working properly,
* FIXED: bug where drop-n-drag didn't work if a middle category was empty (cat count was -x)
* FIXED: bug where page config > HTML Output Formats, weren't auto expanded if content was entered and/or could be entered
* FIXED: if default meta keyword/description is in use, do not output it
* FIXED: 3rd issue with Media Categories: cats displaying in unspecified categories, sometimes as if they moved, others as if they duplicated
* ADDED: option to "add image gallery/attachments to POST" in heyyou settings
* ADDED: "disable lightbox scripts on this page" override to global "add lightbox" script.. reduce load times when unused
* ADDED: Message when changing media items cats in admin
* NEW: "Page" meta type, primiarly for "redirect to.." page meta feild, but also avaliable in hys_post's meta
* NEW: "Checkbox" meta type
* NEW: "Use jQuery more/less slide up/down animation" option for animated more/less
* NEW: added hys_make_moreless() function
* NEW: Share on Google+ and Pinterest buttons added to options
* NEW: hys_social() function and [social] shortcode, configure which social btn's to use in settings, output via checkbox in pg options or w/ shortcode
* NEW: option to use Wordpress's default media library isntead of heyyou's



= 0.0.0.1 =
* April 6th, 2012
* REMOVED lightbox from plugin to comply with GPL, I'm sorry for this misunderstanding of GPL, please contact me for info on how to fix your heyyou website if it was using the lightbox features; no charge,
* Version numbering reverted to manually edit exisiting sites for this lightbox issue. once solved, heyyou will jump to milestone 1.0 release,

= 0.0.13.2 =
* February 18th, 2012
* FIXED: issue were media cat items may apear in their set cat as well as uncategorized.. (as media items can have multi-cats, and "1" uncat is set as default..)
* FIXED: same bug in hey_media library from before in media dropdown (slow due to large # of queries)
* REMOVED: initial-scale=1.0 from mobile detection
* FIXED: bug where when attaching 1 img, gallery doesn't show. ($y > 0 && isset($x[0]['id']))

= 0.0.13 =
* February 4th, 2012
* Updated: Minor IE9 compat in 3rd party JS tools.... awh IE..... 
* updated: updater for pre-alpha versions of heyyou to convert "hys_%feature%" post types into "hys_post" and be recognized as enabled
* FIXED: numberposts issue in hys_media library that may of cause "disappearing" and "uncategorizing" media items

= 0.0.12 =
* "Hey Jude" - January 22, 2012
* FIXED: bug where new media items that were uncategorized weren't showing up in library

= 0.0.11 =
* "Susan" - January 6, 2012
* fixed: speed boost for library: reduce number of queries by 90%
* added: option to view library in Thumbnail Grid or Text list
* added: option to revert back to Wordpress's library instead of heyyou library
* added: more to readme.txt
* added assets banner, 
* added screenshot-3
* fixed: "Add a brief description" spelling error in admin
* added:  initial-scale=1.0 to < meta > view port
* fixed: duplicate use of class att. in more/less links
* changed: <a name= anchors changed to <div id= tags
* fixed: added another check prior to add_theme_support(.. add_thumbs_to_pages
* fixed: syntax errors in Attachments
* changed: compressed prototype.js
* changed: make lb display 'medium' sizes at most in lb when mobile detected.. note lots of themes use custom lb scripts & not heyyou's, so this may not be on all themes
* fixed: re-enabled chronical link
* fixed: hi/low res download link text now dynamic via options page
* added: function hys_get_feature_image_src() for easy retrieval of img
* added: function hys_ids_of_nav_menu() for retrieval of wp menu object id's
* added: post excerpts option

= 0.0.10.1 =
* January 5, 2012
* finally fixed: readme.txt documentation

= 0.0.10 =
* "Spoken" - December 22, 2011
* added: update nag auto removed, no more obstructing update notices 
* fixed: bug: in 3.3 where hys_admin usertypes weren't being recognized as full admin's in hys settings
* fixed: has_cap/userrole warning/notice for media cats
* fixed: undefined index warning for mc_default_media_category
* fixed: Undefined property: Mobile_Detect::$isIphone warning
* fixed: if in network (super) admin, don't edit menu/navigation as everything is needed
* fixed: in token legend for formatting, `%moreless:more/less%` was opposite
* updated: !headers_sent() check for setting session to make LESS errors incase of a notice/warning
* fixed: ancient warning about "undefined index" in "post-template.php on line 30"
* added: when you select a media item via checkbox: the cell lights up, to easily see what's been selected
* fixed: media quick view/delete buttons: titles & actions
* fixed: issues with "Delete Selected" in media library now fixed: will delete any selected
 
= 0.0.9.18 =
* fix: issue with "Fatal error: Call to undefined function get_post_thumbnail_id() in .../media.php on line 1336" cause because `add_theme_support('post-thumbnails')` was called in `init` instead of `after_setup_theme` .. changed hys_load to start at `after_setup_theme` instead of `init` .. this may cause unknown issues.

= 0.0.9.17 =
* Changed: reversed alphabetical order of media cats

= 0.0.9.16 =
* fixed: attachments: random deleting of attachments after: deleting attachment(s) then attaching new

= 0.0.9.15 =
* fixed issue preventing files from being deleted

= 0.0.9.14 =
* "Greenspace" - September, 2011
* added: bulk "change category" for media
* added: when hovering media thumbnails, full file name appears
* fixed: errors where "captions" wouldn't stick on new attachments until after page/post update/save
* fixed: when multi-deleting (using checkboxes) media items
* fixed: attachments only show in site or backend when the media library item exists
* fixed: when doing a update/re-categorize/delete action in media, cat-count updates

= 0.0.9.13 =
* fixed: errors from attachment plugin showing division by 0 for file size when no file exists

= 0.0.9.7 =
* "CVS" - October 22, 2011
* added: hys_get_meta() to easily get hys post meta in a single array
* fixed: default image size for auto-lb galleries now large instead of undefined (full)
* fixed: issues with hys_return_url() returning HTTPS instead of HTTP fixed
* fixed: settings metabox "title" id changed to "hys_title" to prevent error w/ jquery wp post->title place holder
* added: default css for .attachments (same as .hys_gallery)
* fixed: attachments "Undefined" caption (for real lb- not jquery lb)
* added: "use secondary images on pages/posts" checkbox setting added to *heyyou* settings. also "add feature img to posts" now available
* added: attachments: update to latest 1.5.9
* fixed: attachments: when attaching, image title doesn't carry into default "title" field
* fixed: attachments: when saving *heyyou* post attachments for 1st time, caption sticks
* fixed: attachments: removed media categories while "Attach" button is clicked.. the "Attach" button removes the necessary "save" button to assign an uploaded/viewed media item to a cat
* fixed: attachments: when attaching for blog posts, "Attach" now seen instead of "Insert into.." as expected. (Media cats not available here)
* fixed: attachments: when attaching and using the "search", the "attach" button sticks and doesn't revert back to "insert into.."
* fixed: media categories: when uploading multiple at same time, you can now select a category for each image, and upon save, each image will go into req. cat:: as expected.

= 0.0.9 =
* "Interior Acad" - July 30, 2011
* admin: TILE attachments (css) instead of listing..
* js: media: when attaching, don't carry title into attach
* media lib: put cat plugin, into *heyyou*
* moreless javascript broken !!!!!! in *heyyou*. find out why
* media: categories
* media lib: make uncat default (instead of default)
* if $hys['settings'] doesn't exist.. insert a default one
* media lib: multi-checker for mass deletes
* media lib: menu highlighting.
* mobile detection issue TEST ;; wait till sessions die
* admin: media: if not hys_attach media size, use default thumbnail (as wp defaults "full" cause sloooooow)
* when editing a *heyyou* post, the order gets fucked
* turn off auto-save on posts
* Make dashboard widget not mandatory -- maybe a custom text one
* Dashboard widgets (Side) not sticking in hys_settings -- && google analytics missing
* quick edit deleting attachments issue - see if persist
* double uncategorizing happening
* all metaboxes (in pages) randomly showing up?
* hys_client's navigation is missing... only dash and profile..
* $_GET['post_type'] == 'page'
* a way to disable attachments plugin
* change ie 6 warning, remove shauna
* don't disable I/O when inactive
* trim() on *heyyou* meta fields ** MAY CAUSE ISSUES TO EXISTING
* showhide_inline
* if (!empty($cat_format[0]) && $cat_format[0] != 'Array')
* editable location of default TinyMCE.css & favicon.ico ** MAY CAUSE ISSUES
* *heyyou* not putting it's /js/js.js file into site....?
* in meta type drop down, added "Ritch Blurb" for TinyMCE instead of just textarea
* admin: media: when in pop-up media library, css has been fucked, titles below thumb instead of to-right-of

= 0.0.8 =
* "Ekistics" - June 17, 2011
* fixed issue where when uploading/inserting media the "Attach" button would appear instead of the "Insert" button (& it's positioning and link options).
* settings: added "Client Tutorials" ID entry for hiding page from public, viewable to heyyou_client in site, but not available to edit to anyone but Super Admins.
* admin: CSS changes to *heyyou* posts categories, hover revealer like posts.. & better lining up to not confuse actions with check-all button
* admin: "*heyyou* page config" metabox now shows no tabs & page-options for *heyyou*_clients, so configs like "Add twitter button" and "hexadecimal color" are available to *heyyou*_clients
* issue resolved with new secondary blurb line breaking
* css: image alignment patch.. images w/ default class="align{centre/left/right}" will now do accordingly (with added margins), instead of taking the properties from some themes main elements under the same class names
* created "hys_grant()" function for easy under-dev lockout
* clause to remove extra "http://http://" if link is pasted into URL field without removing default "http://" value
* fixed issue with "under construction" banner requiring 2x clicks to reveal msg
* "← back" HTML issue resolved with htmlentities()
* removed "My Sites" (sub-page of "Dashboard") from *heyyou*_client's admin menu
* *heyyou* posts now get added to the BOTTOM of a list of posts by default. option added in "*heyyou* page config" > features > "New posts get added to the {top/bottom}" toggle
* added availability to preview meta fields "media" thumbnail to an admin list of *heyyou* posts for easier visual cues on *heyyou* lists with images as main focus
* before/after HTML categories added to output formats (for better placing *heyyou* in a < ul >< li > output)
* removed auto "theme/js/js.js" import.. may cause conflicts - revisits
* Added jQuery auto-write "< script src=... >" for jquery + various/common plugin support
* Support for "---" (dividing titles (bold) & captions (newline & reg.)) added to lb.js & jquery.lb.js & all auto-lb gallery functions
* both attach gallery generators (hys_photogallery() for *heyyou* posts, and hys_attach_attachments() for page posts) now share "ul.hys_gallery" class. default properties now set in hys_style.css
* only add mootools on page that have drag-n-drop list of *heyyou* posts
* solved issue with admin conflicts between wp-ecomm & *heyyou* (the "media category" sub-plugin, conflicting jquery)
* slugs for pages and posts now editable again
* wp_tiny_mce() vs jquery conflict  resolved
* Force download has been added to "Download: Hi Res | Low Res" links. any file anywhere can force download with the URL query ?hys_download={$fileurl}
* Regenerating Thumbnails now works with *heyyou*, no more JS conflicts
* Attachments (photo galleries) facelift: images now tile instead of list, removed clutter & made simpler.
* Fixed mobile issue was mobile detection wasn't registered until the second page visit

= 0.0.7 =
* "McGuffin" - April 20, 2011
* setting - title for ttachments on/off
* remove sleep() on mobile change... andor add Loading.gif
* create tile_attachments() function to auto prodce
* make "enable" onclick='this.form.submit'
* add 'hide main/page blurb' option, for pages that use heyyou instead of the_content()
* !!!important: drag and drop broken (jquery issue?)

= 0.0.6 = 
* "Manor" - March 14, 2011
* !important* blurb still not sticking on new entries... Functions first entry blurb didn't stick
* !important* drop-n-drag with categories
* !important* add link button not working in editheyyoupost
* add 'feild type' and 'comments' to site-meta fields
* Comments jump to anchors should categorize & appear above 'before' line
* add default user-settings.. ie: auto hide comments/author/revisions for hey_client / hys_subadmin
* make Media Folders manageable in options
* Notice: Trying to get property of non-object in /Users/../post-template.php on line 30 because I unset $post on new pages because of pre-loading heyyou
* TinyMCE CSS: empty
* make defaults for keywords & twitter default = 1
* make "Feature Image" & "Secondary Image" a heyyou option
* default HTML heyyou_post output format to "%title% %blurb%"
* move pagination to another tab since Page-options are dedicated for things that don't require heyyou turned on.
* Comments instead of default "title" as main field... make an option for title/name
* remove "remove flash uploader"
* turn off revisions completely... useless for non-multiple-blogging
* *include http:// overflowing
* if is_int() on first meta, don't show as description sub
* "cut.....string" on cat lists
* add "add primary line AFTER heyyou"
* Add secondary line between categories not working
* heyyou media organizer (folders & drop-down media select) // popup media: css hack to remove unnecessary/confusing fields
* switch meta (blurb/media) to dropdown.. saves key's
* site meta fields for page configuration
* Meta (Media) to not store full URL. only /y/m/file.ext ... CONTENT_URL.(media) to rebuild on output
* shortcode [ heyyou ], [ heyyou cat="Category A" ]
* secondary color
* Paginate options, use: [] Pages [] more/less reveal
* pagination with categories (?pg=x&cat=y)
* cropping thumbnails: find if way to receive proper ratio while cropping
* if list doesn't have pagination and does have "add line(2) between posts" remove last line as it's not "between" anything
* Existing Backups: make folder for each site (because of networks)
* ..more only if %blurb% not empty
* put heyyou preset dropdown in 'page' tab. if empty, disable other tabs
* css for showing thumbs in list of manage posts mucked up
* css on edit post for attachments mucked up
* css issues in navigation
* add wpautop to meta "x (Blurb)" output/s
* %if:defined% issues
* direct domain to ds.me
* the method of saving cat html override (serializing) doesn't work: can't serialize "%" or other html chars.. either find new place to save html over-ride, or drop category descript and title
* !important Page Att > Parent not sticking
* "Auto-Collaps Content" to " Auto-Collaps Blurb"
* when 'post hv individ page' is on, 'line(2) between posts' doesn't work
* add %view_individual_page% link token for "more..." link to full pages without using blurb
* make auto-collapes not possible when individual pages is on
* Comments add ..more button for "auto collapse content" (personnel in epony)
* add line conversion support to heyyou

= 0.0.3 =
* KidPiv - build

= 0.0.2 =
* PuSh - build

= 0.0.1 =
* PTC - build

== Upgrade Notice ==

= 1.3.2 =
* loading jQuery and other lightbox resources from new directory
* changed jQuery's lighbox's  prefix from "$(.." to Wordpress's "jQuery(..", may break some sites jQuery Lightbox

= 0.0.0.0.1 =
* jQuery $() may be undefined.. using wp_enqueue_scripts now including actual script..

= 0.0.13 =
* lb Alt title/caption'ing may be out of whack

= Pre 0.0.13 =

Upgrade at your own risk, this is an unstable alpha plugin.