<?php
/*
Plugin Name: heyyou
Plugin URI: http://hey-you.ca/
Description: heyyou puts posts into pages - easily.
Version: 1.3.2
Author: David Sword
Author URI: http://davidsword.ca/
License: GPL2
*/
/*-- ============================================================================================================= -->
<!-- >                                                                                                           < -->
<!-- >                                             by: davidsword.ca                                             < -->
<!-- >                                                                                                           < -->
<!-- ------------------------------------------------------------------------------------------------------------- -->


      This program is free software; you can redistribute it and/or modify it under the terms of the GNU General 
      Public License, version 2, as published by the Free Software Foundation. This program is distributed in the 
      hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY 
      or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details. You should have 
      received a copy of the GNU General Public License along with this program; if not, write to the Free Soft-
      ware Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301  USA 


--------------------------------------------------------------------------------------------------------------------*/
     
    // !include library of functions for entire plugin
    
    include('_functions.php');                                  # functions

    include('hys_metaboxs.php');                                # three admin metabox's
    include('hys_options.php');                                 # heyyou 'Settings' pg & backup functions
    
    include('res/_attachments.php');                            # plugin: Attachments by Jonathan Christopher
    include('res/media-cats/media-categories.php');             # plugin: Media Categories by Hart Associates (Rick Mead)
    include('res/_mobile_detect.php');                          # function: php mobile check
    
    
    // !1: Start heyyou
    
    add_action('init',                  'hys_update_to_beta');  # update..
    add_action('init',                  'hys_load',4);          # define $hys
    add_action('init',                  'hys_post_reg',5);      # register 'hys_post' post type
    add_action('init',                  'hys_checkmble', 3);    # check mobile & if ?mobile= request
    add_action('init',                  'hys_bkup_sched_cron'); # schedule cronjob if not scheduled
    add_action('hys_bkp_cron',          'hys_backup_email');    # run funct if called by cronjob
    add_filter('cron_schedules',        'hys_crontimes');       # additional cron intervals
    
    
    // !2: heyyou admin
    
    add_action('admin_init',            'hys_reg_options');     # register "get_option('hys_options')"
    add_action('admin_init',            'hys_adminmenu');       # filter menu based on hys_{user} type
    add_action('admin_init',            'hys_photogaltopage');  # add 'attachments` plugin to page
    add_action('admin_menu',            'hys_admin_nav');       # main settings page
    add_action('admin_head',            'hys_admin_header');    # add css to admin header
    add_action('post_updated',          'hys_post_save');       # save post
    add_action('admin_init',            'hys_metabox');         # hys_metaboxs.php: main with page config
    add_action('save_post',             'hys_metabox_save');    # hys_metaboxs.php: save meta box
    add_action('admin_init',            'hys_metabox_mang');    # hys_metaboxs.php: manage the hys_posts metabox
    add_action('admin_init',            'hys_update_post',99);  # if were updating a heyyou post from heyyou edit page
    add_action('admin_init',            'hys_backup_check');    # if "Backup Now" or "Delete All" was pressed
    
    
    // !3: TinyMCE & WP Edits
    
    add_filter('tiny_mce_before_init',  'hys_mce');             # arrange buttons
    add_filter('tiny_mce_version',      'hys_rfh_mce');         # change version
    add_action('admin_init',            'hys_line_btn');        # add line button
    add_action('admin_init',            'hys_mce_admin_init');  # enable additional editors
    add_action('admin_head',            'hys_mce_admin_head');  # enable additional editors
    add_filter('mce_css',               'hys_tinymce_css' );    # add tinymce css code
    add_action('admin_menu',            'hys_rmv_metabxs');
    add_action('wp_dashboard_setup',    'hys_rmv_dash_metabxs');
    
    
    // !4: heyyou output
    
    add_action('init',                  'hys_clean_wp_head');   # remove 'unessisary' added header info
    add_action('wp_enqueue_scripts',    'hys_enqueue_scripts');	# load js and css
    
    add_action('wp_head',               'hys_header_meta', 0);  # edit head, put last
    add_action('wp_head',               'hys_header', 999);     # edit head, put last
    add_action('the_content',           'hys_content');         # main placement of heyyou on page !important
    add_action('wp_footer',             'hys_footer', 999);     # edit head, put last
    
    
    // !heyyou shortcode
    
    add_shortcode('mobile_link',        'mobile_link_f');       # "view {mobile/full} site"
    add_shortcode('heyyou',             'hys_shortcode');       # [heyyou] output
	add_shortcode('social',             'hys_social_shrtcd');   # add social buttons
	add_shortcode('hys_tweets',			'hys_tweets_shortcode');# [hys_tweets id='' count='' refresh_rate='']
	
    
    // !some hacks and other duct-tape work arounds...
    
    
    // force download any file from anywhere with ?download=...
    if (isset($_GET['download'])) hys_download($_GET['download']);
    
    
    // highlight the proper page if on Media > Cats.
    if (isset($_GET['taxonomy']) && $_GET['taxonomy'] == 'media_category' || strpos(hys_return_url(),'media.php?attachment_id') !== false)
        add_filter('parent_file', 'hys_change_parent_to_media',999);
            
        
    // set the timezone
    date_default_timezone_set(hys_get_timezone());
    

/*--------------------------------------------------------------------------------------------------------------------
====================================================================================================================*/
?>