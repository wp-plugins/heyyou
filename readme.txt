=== heyyou ===
Contributors: davidsword.ca, heyshauna.com
Donate link: http://davidsword.ca/
Tags: custom post types for pages, page posts, page features, features, faq, news, small news, easy news, easy posts, custom post types
Requires at least: 3.0
Tested up to: 3.0
Stable tag: 3.0

* ATTENTION: ALPHA PLUGIN, FOR DEVELOPERS ONLY * this was uploaded to wp.org for our team of developers and testers. This is a pre-beta with an incomplete website and documentation. Please contact @david_sword for more information *


== Description ==

* ATTENTION: ALPHA PLUGIN, FOR DEVELOPERS ONLY * this was uploaded to wp.org for our team of developers and testers. This is a pre-beta with an incomplete website and documentation. Please contact @david_sword for more information * Add heyyou Posts Directly Into a Page, Unique & customizable dynamic custom post types, for individual pages. A Framework for Developers heyyou adds settings, functions and other tools for site wide features & Page Configuration defined by the heyyou Settings for individual page options. Simplify WordPress for clients by removing things you don’t use in your theme.


== Installation ==

1. Upload the `/heyyou/` folder into the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Visit "heyyou" Settings in the admin
4. Go into a Page and use the "heyyou" metabox

* ATTENTION: ALPHA PLUGIN, FOR DEVELOPERS ONLY * this was uploaded to wp.org for our team of developers and testers. This is a pre-beta with an incomplete website and documentation. Please contact @david_sword for more information *

== Frequently Asked Questions ==

= I don't understand what this is =

Visit http://hey-you.ca/ * ATTENTION: ALPHA PLUGIN, FOR DEVELOPERS ONLY * this was uploaded to wp.org for our team of developers and testers. This is a pre-beta with an incomplete website and documentation. Please contact @david_sword for more information *

== Screenshots ==

* ATTENTION: ALPHA PLUGIN, FOR DEVELOPERS ONLY * this was uploaded to wp.org for our team of developers and testers. This is a pre-beta with an incomplete website and documentation. Please contact @david_sword for more information *

== Changelog ==

= 0.0.9.7 =
* added: hys_get_meta() to easily get hys post meta in a single array
* fixed: default image size for auto-lightbox galleries now large instead of undefined (full)
* fixed: issues with hys_return_url() returning HTTPS instead of HTTP fixed
* fixed: settings metabox "title" id changed to "hys_title" to prevent error w/ jquery wp post->title place holder
* added: default css for .attachments (same as .hys_gallery)
* fixed: attachments "Undefined" caption (for real lightbox- not jquery lightbox)
* added: "use secondary images on pages/posts" checkbox setting added to heyyou settings. also "add feature img to posts" now available
* added: attachments: update to latest 1.5.9
* fixed: attachments: when attaching, image title doesn't carry into default "title" field
* fixed: attachments: when saving heyyou post attachments for 1st time, caption sticks
* fixed: attachments: removed media categories while "Attach" button is clicked.. the "Attach" button removes the necessary "save" button to assign an uploaded/viewed media item to a cat
* fixed: attachments: when attaching for blog posts, "Attach" now seen instead of "Insert into.." as expected. (Media cats not available here)
* fixed: attachments: when attaching and using the "search", the "attach" button sticks and doesn't revert back to "insert into.."
* fixed: media categories: when uploading multiple at same time, you can now select a category for each image, and upon save, each image will go into req. cat:: as expected.

= 0.0.9
* admin: TILE attachments (css) instead of listing..
* js: media: when attaching, don't carry title into attach
* media lib: put cat plugin, into heyyou
* moreless javascript broken !!!!!! in heyyou. find out why
* media: categories
* media lib: make uncat default (instead of default)
* if $hys['settings'] doesn't exsist.. insert a default one
* media lib: multi-checker for mass deletes
* media lib: menu highlighting.
* mobile detection issue TEST ;; wait till sessions die
* admin: media: if not hys_attach media size, use default thumbnail (as wp defaults "full" cause sloooooow)
* when editing a heyyou post, the order gets fucked
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
* trim() on heyyou meta fields ** MAY CAUSE ISSUES TO EXSISTING
* showhide_inline
* if (!empty($cat_format[0]) && $cat_format[0] != 'Array')
* editable location of default tinymce.css & favicon.ico ** MAY CAUSE ISSUES
* heyyou not putting it's /js/js.js file into site....?
* in meta type drop down, added "Ritch Blurb" for tinymce isntead of just textarea
* admin: media: when in pop-up media library, css has been fucked, titles below thumb instead of to-right-of

= 0.0.8 =

* fixed issue where when uploading/inserting media the "Attach" button would appear instead of the "Insert" button (& it's positioning and link options).
* settings: added "Client Tutorials" ID entry for hiding page from public, viewable to heyyou_client in site, but not available to edit to anyone but Super Admins.
* admin: CSS changes to heyyou posts categories, hover revealer like posts.. & better linning up to not confuse actions with check-all button
* admin: "heyyou page config" metabox now shows no tabs & page-options for heyyou_clients, so configs like "Add twitter button" and "hexadecimal color" are available to heyyou_clients
* issue resolved with new secondary blurb line breaking
* css: image alignment patch.. images w/ default class="align{center/left/right}" will now do accordingly (with added margins), instead of taking the properties from some themes main elements under the same class names
* created "hys_grant()" function for easy under-dev lockout
* clause to remove extra "http://http://" if link is pasted into URL field without removing default "http://" value
* fixed issue with "under construction" banner requiring 2x clicks to reveal msg
* "← back" HTML issue resolved with htmlentities()
* removed "My Sites" (sub-page of "Dashboard") from heyyou_client's admin menu
* heyyou posts now get added to the BOTTOM of a list of posts by default. option added in "heyyou page config" > features > "New posts get added to the {top/bottom}" toggle
* added availability to preview meta fields "media" thumbnail to an admin list of heyyou posts for easier visual cues on heyyou lists with images as main focus
* before/after HTML categories added to output formats (for better placing heyyou in a <ul><li> output)
* removed auto "theme/js/js.js" import.. may cause conflicts - revisits
* Added jQuery auto-write "<script src=...>" for jquery + various/common plugin support
* Support for "---" (dividing titles (bold) & captions (newline & reg.)) added to lightbox.js & jquery.lightbox.js & all auto-lightbox gallery functions
* both attach gallery generators (hys_photogallery() for heyyou posts, and hys_attach_attachments() for page posts) now share "ul.hys_gallery" class. default properties now set in hys_style.css
* only add mootools on page that have drag-n-drop list of heyyou posts
* solved issue with admin conflicts between wp-ecomm & heyyou (the "media category" sub-plugin, conflicting jquery)
* slugs for pages and posts now editable again
* wp_tiny_mce() vs jquery conflict  resolved
* Force download has been added to "Download: Hi Res | Low Res" links. anyfile anwhere can force download with the URL query ?hys_download={$fileurl}
* Regenerating Thumbnails now works with heyyou, no more JS conflicts
* Attachments (photo galleries) facelift: images now tile instead of list, removed clutter & made simpler.
* Fixed mobile issue was mobile detection wasn't registered until the second page visit

= 0.0.3 =
* KidPiv - build

= 0.0.2 =
* PuSh - build

= 0.0.1 =
* PTC - build