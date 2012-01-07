=== heyyou ===
Contributors: davidsword.ca, heyshauna.com
Donate link: http://wordpress.org/donate
Tags: custom post types for pages, wordpress developer framework, wordpress simplifier
Requires at least: 3.0
Tested up to: 3.2
Stable tag: 0.0.10.1

*heyyou* is a large plugin framework that - in addition to supplying  resources for rapid developent - adds unique dynamic custom post types directly within a Wordpress Page


== Description ==

The plugin was intended as a developers framework housing lots of useful template functions - it's goal to turn wordpress into more of a CMS by: in-page dynamic custom post types, an extensive admin settings page, auto includes (for things like MooTools, lightbox, favicon.ico), and a wordpress simplifier, as well as many other features for rapid template development.

In short: *heyyou* has three main purposes:

1. Adding (dynamic) Custom Post Types directly within a Wordpress Page, below the pages main content
2. Developer Framework, housing a wide range of preset, and custom meta values for eaisly creating a template
3. Simplying Wordpress

Elaborating on features, *heyyou*:

**In Admin:**

* Dynamic Custom Post Types
	* page-specific configuration of posts with wide range of configs, and format output using basic %macros% and HTML
	* built-in per-page pagination for longer lists of posts
	* Drag and drop ordering
* custom media library (grid thumbnails)
* media library categories
* Dynamic Page Meta Feilds (for things like having a featured image's "Photo Credit" meta feild) (Eaiser to use than Wordpress's custom feilds)
* TinyMCE: simplifier, reduces TinyMCE buttons to single row
* TinyMCE: GUI for adding dynamic styles to tinymce (ie: add "Blue Italic" to your themes styled drop down)
* Database-to-email backup
* Allows check-box addition of technologies (like Lightbox, Mootools, Attachments)
* Edit main <meta> keyword/description
* Interface for adding attractive "Notice" banner to site
* Options to: 
	* Add seondary blurb
	* Add Twitter / Facebook plugin
	* Configure which metaboxes user types have access to and receive by default (hidding client unessisary metaboxes like Wordpress News and Plugin Widget Feed)
* Creates "Sub-Admin" and "Client" user types with edit-able admin navigations to remove menu items like "Tools" or "Links" if unrelevant for your theme
* *& much more.. (documentation still being written)*

**On Front-End:**

* Cleans up HTML header info outputted by wp_head()
* Adds .mobile class to <body> when detected
* Javascript instant-reveal "..read more" replace <!--more-->
* *& much more.. (documentation still being written)*

** Live Sites powered by *heyyou* **

* [hey-you.ca](http://hey-you.ca/)
* [heyshauna.com](http://heyshauna.com/)
* [davidsword.ca](http://davidsword.ca/)
* [expbc.com](http://expbc.com/)
* [leisuretravel2000.ca](http://leisuretravel2000.ca/)
* [wenweidance.ca](http://wenweidance.ca/)
* [thomascannell.com](http://thomascannell.com/)
* [kiddpivot.org](http://kiddpivot.org/)
* [shaunbalbar.ca](http://shaunbalbar.ca/)
* [eponymous.ca](http://eponymous.ca/)
* [lesliemcguffin.com](http://lesliemcguffin.com/)
* [greenspacedesigns.com](http://greenspacedesigns.com/)
* [pushfestival.ca](http://pushfestival.ca/)
* [trenchgallery.com](http://trenchgallery.com/)
* [newmusic.org](http://newmusic.org/)
* [ekistics.ca](http://ekistics.ca/)
* [cocoondesigns.ca](http://cocoondesigns.ca/)
* [playwrightstheatre.com](http://playwrightstheatre.com/)
* *& much more.. (many websites in development / nearing launch)*


** External Credit **

* [Jonathan Christopher](http://mondaybynoon.com/)'s [Attachment Plugin](http://wordpress.org/extend/plugins/attachments/) is used, it's layout altered to simplified thumbnail grid
* (Name Missing) (Link Missing) Media Categories was intgrated
* *& 1 more.. (documentation still being written)*

**!important notice**

This plugin is a *very large* framework design specifically for development of websites by heyshauna & David Sword. It esentially has the functionality of 30 or so plugins. *heyyou* is not likly compatible with all themes or plugins and is reccomended for intermidiate-to-advance users only that are commited to eperimenting with this plugin. Always backup your database and files before installing a plugin of this scale. It is reccomened that potentional users test this plugin in a test enviroment prior to installing on a live site. Please view the documentation at : [hey-you.ca](http://hey-you.ca/)

Documentation and plugin are at ALPHA LEVEL, the plugin is currently being developed.

http://hey-you.ca/


== Installation ==

1. Upload the `/heyyou/` folder into the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Visit "*heyyou*" Settings in the admin
4. Go into a Page and use the "*heyyou*" metabox


== Frequently Asked Questions ==

= What are dynamic custom post types? =

Dynamic Custom Post Types - ("DCPT")'s - are posts that you can add directly into a Wordpress Page. Unlike a "Post" a DCPT by default only has a title: within that page, there's a configuration form where you can configure that DCPT to whatever required, some options include:

 * Adding a blurb and/or a date feild
 * Categories / Groupings for the DCPT's
 * Meta feilds for Media items, text, code, URLs
 * & much much more..

= What type of Pages are dynamic custom post types useful for? =

The entire orgin of this project bases from wanting the following within a wordpress page:

 * A FAQ listing, simple, directly below a pages content
 * Staff BIO listing
 * Simple news posts (paginated titles + date + description)

= I still don't understand what this is =

Visit: [hey-you.ca](http://hey-you.ca/)

Tweet: [@david_sword](http://twitter.com/david_sword/)


== Screenshots ==

1. Dynamic Custom Post Types added directly to the page the post types will show in. Simplying CTP's for your clients.
2. Custom post types apear just as they do in the admin - directly below the corrosponding pages main content


== Changelog ==

= 0.0.10.1 =
 * January 5, 2012
 * finally fixed: readme.txt documentation


= 0.0.10 =
 * December 22, 2011
 * added: updatenag auto removed, no more obstructing update notices 
 * fixed: bug: in 3.3 where hys_admin usertypes weren't being recconized as full admin's in hys settings
 * fixed: has_cap/userrole warning/notice for media cats
 * fixed: undefined index warning for mc_default_media_category
 * fixed: Undefined property: Mobile_Detect::$isIphone warning
 * fixed: if in network (super) admin, don't edit menu/navigation as everything is needed
 * fixed: in token legend for formating, `%moreless:more/less%` was opposite
 * updated: !headers_sent() check for setting session to make LESS errors incase of a notice/warning
 * fixed: accient warning about "undefined index" in "post-template.php on line 30"
 * added: when you select a media item via checkbox: the cell lights up, to eaisly see what's been selected
 * fixed: media quick view/delete buttons: titles & actions
 * fixed: issues with "Delete Selected" in media library now fixed: will delete any selected
 
= 0.0.9.18 =
* fix: issue with "Fatal error: Call to undefined function get_post_thumbnail_id() in .../media.php on line 1336" cause because `add_theme_support('post-thumbnails')` was called in `init` instead of `after_setup_theme` .. changed hys_load to start at `after_setup_theme` instead of `init` .. this may cause unknown issues.

= 0.0.9.17 =
* Changed: reversed alphabedical order of media cats

= 0.0.9.16 =
* fixed: attachements: random deleting of attachments after: deleting attachment(s) then attaching new

= 0.0.9.15 =
* fixed issue preventing files from being deleted

= 0.0.9.14 =
* added: bulk "change category" for media
* added: when hovering media thumbnails, full file name appears
* fixed: errors where "captions" wouldn't stick on new attachments until after page/post update/save
* fixed: when multi-deleting (using checkboxes) media items
* fixed: attachments only show in site or backend when the media library item exists
* fixed: when doing a update/re-categorize/delete action in media, cat-count updates

= 0.0.9.13 =
* fixed: errors from attachment plugin showing division by 0 for file size when no file exsists

= 0.0.9.12 =

= 0.0.9.11 =

= 0.0.9.10 =

= 0.0.9.9 =

= 0.0.9.8 =

= 0.0.9.7 =
* added: hys_get_meta() to easily get hys post meta in a single array
* fixed: default image size for auto-lightbox galleries now large instead of undefined (full)
* fixed: issues with hys_return_url() returning HTTPS instead of HTTP fixed
* fixed: settings metabox "title" id changed to "hys_title" to prevent error w/ jquery wp post->title place holder
* added: default css for .attachments (same as .hys_gallery)
* fixed: attachments "Undefined" caption (for real lightbox- not jquery lightbox)
* added: "use secondary images on pages/posts" checkbox setting added to *heyyou* settings. also "add feature img to posts" now available
* added: attachments: update to latest 1.5.9
* fixed: attachments: when attaching, image title doesn't carry into default "title" field
* fixed: attachments: when saving *heyyou* post attachments for 1st time, caption sticks
* fixed: attachments: removed media categories while "Attach" button is clicked.. the "Attach" button removes the necessary "save" button to assign an uploaded/viewed media item to a cat
* fixed: attachments: when attaching for blog posts, "Attach" now seen instead of "Insert into.." as expected. (Media cats not available here)
* fixed: attachments: when attaching and using the "search", the "attach" button sticks and doesn't revert back to "insert into.."
* fixed: media categories: when uploading multiple at same time, you can now select a category for each image, and upon save, each image will go into req. cat:: as expected.

= 0.0.9
* admin: TILE attachments (css) instead of listing..
* js: media: when attaching, don't carry title into attach
* media lib: put cat plugin, into *heyyou*
* moreless javascript broken !!!!!! in *heyyou*. find out why
* media: categories
* media lib: make uncat default (instead of default)
* if $hys['settings'] doesn't exsist.. insert a default one
* media lib: multi-checker for mass deletes
* media lib: menu highlighting.
* mobile detection issue TEST ;; wait till sessions die
* admin: media: if not hys_attach media size, use default thumbnail (as wp defaults "full" cause sloooooow)
* when editing a *heyyou* post, the order gets fucked
* turn off auto-save on posts
* Make dashboard widget not manditory -- maybe a custom text one
* Dashboard widgets (Side) not sticking in hys_settings -- && google analytics mising
* quick edit deleteing attachments issue - see if persist
* double uncategorizing happening
* all metaboxes (in pages) randomly showing up?
* hys_client's navigation is missing... only dash and profile..
* $_GET['post_type'] == 'page'
* a way to disable johnathans attachments plugin
* change ie 6 warning, remove shauna
* don't disable I/O when inactive
* trim() on *heyyou* meta fields ** MAY CAUSE ISSUES TO EXSISTING
* showhide_inline
* if (!empty($cat_format[0]) && $cat_format[0] != 'Array')
* editable location of default tinymce.css & favicon.ico ** MAY CAUSE ISSUES
* *heyyou* not putting it's /js/js.js file into site....?
* in meta type drop down, added "Ritch Blurb" for tinymce isntead of just textarea
* admin: media: when in pop-up media library, css has been fucked, titles below thumb instead of to-right-of

= 0.0.8 =

* fixed issue where when uploading/inserting media the "Attach" button would appear instead of the "Insert" button (& it's positioning and link options).
* settings: added "Client Tutorials" ID entry for hiding page from public, viewable to heyyou_client in site, but not available to edit to anyone but Super Admins.
* admin: CSS changes to *heyyou* posts categories, hover revealer like posts.. & better linning up to not confuse actions with check-all button
* admin: "*heyyou* page config" metabox now shows no tabs & page-options for *heyyou*_clients, so configs like "Add twitter button" and "hexadecimal color" are available to *heyyou*_clients
* issue resolved with new secondary blurb line breaking
* css: image alignment patch.. images w/ default class="align{center/left/right}" will now do accordingly (with added margins), instead of taking the properties from some themes main elements under the same class names
* created "hys_grant()" function for easy under-dev lockout
* clause to remove extra "http://http://" if link is pasted into URL field without removing default "http://" value
* fixed issue with "under construction" banner requiring 2x clicks to reveal msg
* "← back" HTML issue resolved with htmlentities()
* removed "My Sites" (sub-page of "Dashboard") from *heyyou*_client's admin menu
* *heyyou* posts now get added to the BOTTOM of a list of posts by default. option added in "*heyyou* page config" > features > "New posts get added to the {top/bottom}" toggle
* added availability to preview meta fields "media" thumbnail to an admin list of *heyyou* posts for easier visual cues on *heyyou* lists with images as main focus
* before/after HTML categories added to output formats (for better placing *heyyou* in a < ul >< li > output)
* removed auto "theme/js/js.js" import.. may cause conflicts - revisits
* Added jQuery auto-write "<script src=...>" for jquery + various/common plugin support
* Support for "---" (dividing titles (bold) & captions (newline & reg.)) added to lightbox.js & jquery.lightbox.js & all auto-lightbox gallery functions
* both attach gallery generators (hys_photogallery() for *heyyou* posts, and hys_attach_attachments() for page posts) now share "ul.hys_gallery" class. default properties now set in hys_style.css
* only add mootools on page that have drag-n-drop list of *heyyou* posts
* solved issue with admin conflicts between wp-ecomm & *heyyou* (the "media category" sub-plugin, conflicting jquery)
* slugs for pages and posts now editable again
* wp_tiny_mce() vs jquery conflict  resolved
* Force download has been added to "Download: Hi Res | Low Res" links. anyfile anwhere can force download with the URL query ?hys_download={$fileurl}
* Regenerating Thumbnails now works with *heyyou*, no more JS conflicts
* Attachments (photo galleries) facelift: images now tile instead of list, removed clutter & made simpler.
* Fixed mobile issue was mobile detection wasn't registered until the second page visit

= 0.0.3 =
* KidPiv - build

= 0.0.2 =
* PuSh - build

= 0.0.1 =
* PTC - build

== Upgrade Notice ==

Upgrade at your own risk, this is an unstable alpha plugin.