<?php
/*
 * _functions.php
 *
 * @plugin 	heyyou
 * @since 	0.1
 * @global 	$hys
 */
 
 
/*-------------------------------------------------------------
 Name:      hys_get_meta

 Purpose:   Get meta data for a hys post.
 Receive:   ID (int)
 Return:	array of meta values
-------------------------------------------------------------*/
function hys_get_meta($id = '') { hys_return_meta($id = ''); }
function hys_return_meta($id = '') {
	global $post;
	$id = (empty($id)) ? $post->ID : intval($id);
	$meta = get_post_meta($id,'meta');
	return (isset($meta[0])) ? $meta[0] : '';
}


/*-------------------------------------------------------------
 Name:      hys_space

 Purpose:   Add consistent or dynamic spacing ( for admin mainly )
 Receive:   ID (int)
 Return:	array of meta values
-------------------------------------------------------------*/
 function hys_space($size = 10,$echo = 1) {
 	$return = "<div style='height:{$size}px;'> <!-- --> </div>\n";
 	if ($echo == 1) echo $return;
 	else return $return;
 }
 
 
/*-------------------------------------------------------------
 Name:      hys_grant

 Purpose:   allow indiviuals with correct URL access to view site
 			(used for previewing under-dev sites)
-------------------------------------------------------------*/
 function hys_grant($splashURL = '') {
	if (isset($_GET['grant']) || isset($_GET['preview']) || isset($_GET['heyshauna'])) {
		setcookie("hys_grant", '1', (time()+2800));	
		sleep(2);
		header('Location: '.get_bloginfo('url'));	
		die('HERE');
	}
	if (!isset($_COOKIE['hys_grant'])) {
		if (empty($splashURL))
			echo "website under construction. please check back soon!";
		else
			header('Location: '.$splashURL);
		die();
	}
	//refresh timmer if visiting within active grant/cookie
	if (isset($_COOKIE['hys_grant']))
		setcookie("hys_grant", '1', (time()+2800));
 }
  
	



/*====================================================================================================================
    =UPDATE to beta
--------------------------------------------------------------------------------------------------------------------*/
/*-------------------------------------------------------------
 Name:      hys_update_to_beta

 Purpose:   required to update from 0.0.x to 0.1.0
-------------------------------------------------------------*/
	function hys_update_to_beta() {
		global $wpdb, $hys;
		if (isset($_GET['update']) && ($_GET['update'] == '0.0.9' || $_GET['update'] == 'prebeta') && is_admin()) {
			$wpdb->show_errors();
			//Update all post types to new hys_post type
			$wpdb->query("UPDATE {$wpdb->prefix}posts SET post_type = 'hys_post' WHERE post_type LIKE 'hys_%'");
			//change post-meta hey... "hys_page_config" to "hys_page_config"
			$wpdb->query("UPDATE {$wpdb->prefix}postmeta SET meta_key = 'hys_page_config' WHERE meta_key = 'hys_page_feature'");
			$wpdb->query("UPDATE {$wpdb->prefix}postmeta SET meta_key = 'hys_usrpg_config' WHERE meta_key = 'hys_usrpg_config'");
			//@TODO, turn  'hys_page_config[preset] == custom' where heyyou is already active..
			//move post > meta > post_parent to post > post_parent
			$myrows = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}postmeta WHERE meta_key = 'feature_parent'");
			$i = 1;
			foreach ($myrows as $row) {
				$qry = "UPDATE {$wpdb->prefix}posts SET post_parent = '{$row->meta_value}' WHERE ID = '{$row->post_id}'";
				$wpdb->query($qry);
				$wpdb->print_error();
				$i++; //86
			}			
			//lets do the opposite...
			$myrows = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}posts WHERE post_parent = '0'");
			foreach ($myrows as $row) {
				$myparent = $wpdb->get_results("SELECT meta_value FROM {$wpdb->prefix}postmeta WHERE meta_key = 'feature_parent' AND post_id = {$row->ID}");
				foreach ($myparent as $parentrow) {
					$qry = "UPDATE {$wpdb->prefix}posts SET post_parent = '{$parentrow->meta_value}' WHERE ID = '{$row->post_id}'";
					$wpdb->query($qry);
				}
			}
			die('heyyou is now up to date<br /><br />The past, present, and future walked into a bar -- it was tense.');
		}
	}




/*====================================================================================================================
   // !1 Start Heyyou 
--------------------------------------------------------------------------------------------------------------------*/

/*-------------------------------------------------------------
 Name:      hys_load
-------------------------------------------------------------*/
	function hys_load($id = '') {
		global $wp, $wpdb, $hys, $post, $menu, $settingsmenu;
				
		if (!is_object($post))
			$post = get_post(get_option('show_on_front')); 	//@TODO: confirm this fixing/does anything
															// probably doesn't as $post isn't loaded at init
		// get the ID & META for page
		$id 	= (!empty($id)) ? intval($id) : hys_return_id();
		$pmeta 	= (!empty($id)) ? get_post_custom($id) : array();
		$url 	= hys_return_url();
		
		//if it's a new page dont load the home pages post custom ($pmeta)
		if (is_admin() && strpos($url,'post-new.php?post_type=page'))
			$pmeta = array(); //blank instead of get_post_custom(home_id)
				
		$url = parse_url(hys_return_url());		
		
		// get heyyou settings..
		$hys 					= array();
		$hys['user']			= hys_current_user_role();
		$hys['settings'] 		= get_option('hys_options');  //global settings
		$hys['settings']		= (!isset($hys['settings']['installed'])) ? hys_default_settings(): $hys['settings'];
		$hys['dir']				= plugins_url('',__FILE__);	
		$hys['hys_page_config'] = @($pmeta['hys_page_config'])  ? unserialize($pmeta['hys_page_config'][0])  : 0;	
		$hys['hys_usrpg_config']= @($pmeta['hys_usrpg_config']) ? unserialize($pmeta['hys_usrpg_config'][0]) : 0;
		$hys['feature_code'] 	= 'hys_post-'.$id;
		$hys['mobile'] 			= (  (isset($_SESSION['hys_mobile']) && $_SESSION['hys_mobile'] == 1)
									   OR
									 (isset($_COOKIE['hys_mobile']) && $_COOKIE['hys_mobile'] == 1)								   
								   ) ? 1 : 0;

		$site_name				= explode('.',str_replace(array('http://','www.','/'),'',get_bloginfo('url')));
		$hys['site'] 			= $site_name[0];
		$hys['metatypes'] 		= array( 'Text' , 'URL' , 'Media' , 'Blurb' , 'Page', 'Code', 'Checkbox', 'Textarea' /* , 'Chckbx' */ );

		//get configurations.. preset.. check if there's "feature" from old versions of 'heyyou'
		$feature 				= (isset($hys['hys_page_config']['feature'])) ? $hys['hys_page_config']['feature'] : '';
		$feature 				= ($feature == 'NONE' || $feature == '0') ? '' : $feature;
		$preset 				= (isset($hys['hys_page_config']['preset'])) ? $hys['hys_page_config']['preset'] : '';
		$preset 				= (!isset($hys['hys_page_config']['preset']) && !empty($feature)) ? 'custom' : $preset;
		$preset 				= (isset($hys['hys_page_config']['preset']) && !empty($feature)) ? 'custom' : $preset;
		$hys['config'] 			= $preset;
		
		//..pre 0.1 heyyou.. convert..
		if (isset($hys['hys_page_config']['feature']) && !empty($hys['hys_page_config']['feature'])) {
			$hys['config'] = 'custom';	
			$hys['hys_page_config']['preset'] = 'custom';	
		}		
		$hys['presets'] = array( );

		//on the blog page, there's no hys_page_config as it will overwrite blog posts
//		$this_page = url_to_postid( "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'] );
//		if ($this_page == get_option('page_for_posts')) {
//			$hys['hys_page_config'] = 0;
//		}

		add_image_size( 'hys_attachment_size', 120, 70, true ); //300 pixels wide (and unlimited height)
		remove_post_type_support( 'page', 'revisions' );
		if (@$hys['settings']['page_excerpts'] == 1)	
			add_post_type_support( 'page', 'excerpt' );
		if (@$hys['settings']['post_excerpts'] == 1)	
			add_post_type_support( 'post', 'excerpt' );

		//Add ftr img to PAGES andor POSTS
		$add_thumbs_to_pages = array();
		if (@$hys['settings']['page_featured_image'] == 1) $add_thumbs_to_pages[] = 'page';
		if (@$hys['settings']['post_featured_image'] == 1) $add_thumbs_to_pages[] = 'post';
		
		if (isset($add_thumbs_to_pages[0]) && !empty($add_thumbs_to_pages[0])) 
			add_theme_support('post-thumbnails', $add_thumbs_to_pages);
		
		//Add secondary ftr img to PAGES andor POSTS
		$add_thumbs_to_posts = array();
		if (@$hys['settings']['page_secondary_image'] == 1) $add_thumbs_to_posts[] = 'page';
		if (@$hys['settings']['post_secondary_image'] == 1) $add_thumbs_to_posts[] = 'post';
		
		if (isset($add_thumbs_to_posts[0])) {
			require('res/multiple-post-thumbnails/multi-post-thumbnails.php');
		    foreach($add_thumbs_to_posts as $type) {
		        $thumb = new MultiPostThumbnails(array(
		            'label' => 'Secondary Image',
		            'id' => 'secondary-image',
		            'post_type' => $type
		            )
		        );
		    }
		}
		// if it's a media post submit
		if (@$hys['settings']['dont_use_heyyou_media_library'] != 1) {
			if ((strpos(hys_return_url(),'/upload.php') !== false) || (strpos(hys_return_url(),'/media.php') !== false && empty($url['query']))) {
				$redirect = $url['scheme']."://".$url['host'].str_replace(array('upload.php','media.php'),'admin.php?page=hys_media&updated',$url['path']);
				if(!headers_sent()) {
					header('Location: '.$redirect);
				} else {
					echo '<meta http-equiv="refresh" content="0;url='.$redirect.'">';
				}
				exit;
			}
		}			
	}



/*-------------------------------------------------------------
 Name:      add_hys_classes

 Purpose:   add class to <body> to let jQuery know we're 
 			animating
 Receive:   wordpress's and theme's $classes array
 Return:	same
-------------------------------------------------------------*/
	function add_hys_classes($classes) {
		global $hys;	
		if ($hys['settings']['animated_moreless'] == 1)
			$classes[] = 'animated_moreless';
		return $classes;
	}


/*-------------------------------------------------------------
 Name:      hys_post_reg

 Purpose:   Register post type w wordpress
-------------------------------------------------------------*/
	function hys_post_reg() {
		global $hys; //hys_links
		
		// Register custom post types
		register_post_type('hys_post', array(
			'label' 			=> __('heyyou posts'),
			'singular_label' 	=> __('heyyou posts'),
			'public' 			=> true,
			'show_ui' 			=> false, // UI in admin panel
			'_builtin' 			=> false, // It's a custom post type, not built in
			'_edit_link' 		=> 'post.php?post=%d',
			'capability_type' 	=> 'post',
			'hierarchical' 		=> false,
			'rewrite' 			=> array("slug" => "hys_post"), // Permalinks
			'query_var' 		=> "hys_post", // This goes to the WP_Query schema
			'supports' 			=> array('title','editor', 'excerpt') 
		));
						
		// Add new taxonomy (like tags)		
		register_taxonomy('hys_post_cats','hys_post',array(
			'hierarchical' => true,
			'show_ui' => false,
			'query_var' => true,
			'rewrite' => array( 'slug' => 'hys_cat' ),
		));

		// if we're using hys_post - create term to group with
		if (!empty($hys['config']) && !term_exists( $hys['feature_code'], 'hys_post_cats')) {
			wp_insert_term(
				$hys['feature_code'], 	// the PARENT term 
				'hys_post_cats', 		// the taxonomy
				array(
					'description '	=> 'for heyyou',
					'slug' 			=> $hys['feature_code'],
				)
			);
		}
	}

/*-------------------------------------------------------------
 Name:      hys_checkmble

 Purpose:   checks if mobile or not, refreshs if requested
 Return:	boolen
-------------------------------------------------------------*/
	function hys_checkmble() {
		global $hys;
		// if the SESSION hasn't been started, or another badly coded plugin calls it in a init instead of config.php
		// use cookies instead
		$usecookies = (!session_id()) ? true : false;
		$dest_url 		= str_replace(array('?mobile','&mobile'),'',hys_return_url());
		$domain 		= $_SERVER['SERVER_NAME'];
		$subdomain 		= substr_replace($domain, '', 2, strlen($domain));
		$subdomain_www 	= substr_replace($domain, '', 4, strlen($domain));
		$detect = new Mobile_Detect();
		//if starting anew load
		if ((!isset($_SESSION['hys_mobile']) && !$usecookies) || (!isset($_COOKIE['hys_mobile']) && $usecookies)) {
			$amobile = $detect->isMobile();
			if ($amobile == 1 || $amobile === true) {
				$_SESSION['hys_mobile'] = 1; //is mobile
				if ($usecookies) setcookie("hys_mobile", 1, time()+3600);
			} 
			// not mobile
			else {
				$_SESSION['hys_mobile'] = 0;
				if ($usecookies && !headers_sent()) setcookie("hys_mobile", 0, time()+3600);
			}
		}
		//see if we're manually toggeling/ using the footer links
		if ( isset($_GET['mobile']) ) {
			if ($usecookies) {
				$switch = ($_COOKIE['hys_mobile'] == 0) ? 1 : 0;
				setcookie("hys_mobile", $switch, time()-3600);
				setcookie("hys_mobile", $switch, time()+3600);
			} else {
				$_SESSION['hys_mobile'] = ($_SESSION['hys_mobile'] == 0) ? 1 : 0;
			}
			// reload new site
			if(!headers_sent()) 
				header('Location: '.$dest_url);
			 else 
			 	echo '<meta http-equiv="refresh" content="0;url='.$dest_url.'">';
			die('redirecting..');
			exit;
		}	
	}	




/*====================================================================================================================
   // !2 heyyou admin 
--------------------------------------------------------------------------------------------------------------------*/

/*-------------------------------------------------------------
 Name:      hys_reg_options

 Purpose:   Register Settings for new menu item/page
-------------------------------------------------------------*/
	function hys_reg_options() {
		register_setting( 'hys_settings', 'hys_options' );
		hys_settings_default();
	}

/*-------------------------------------------------------------
 Name:      hys_adminmenu

 Purpose:   change the navigation based on the user types..
 			add user types if not added.
-------------------------------------------------------------*/
	function hys_adminmenu() {
		global $hys, $current_user, $menu, $submenu, $wpdb, $wp;
		
		//if network admin, don't do anything
		$mystring = hys_return_url();
		$findme   = '/network/';
		$pos = strpos($mystring, $findme);
		if ($pos === false) {
			//isn't network
		} else {
		    return '';//is network
		}
		
		// get user
		get_currentuserinfo();
		
		$hys['menu_copy'] = $menu;
		$hys['submenu_copy'] = $submenu;
		
		//remove "Users"
		if (isset($current_user->allcaps['heyyou_client'])) {
			$menu[70][0] = 'User Profile'; // rename "Users"
			$menu[70][1] = 'read';
			$menu[70][2] = 'profile.php';
			unset($submenu['index.php'][0]); // dashabord > main sub
			unset($submenu['index.php'][5]); // dashabord > my sites
			unset($submenu['users.php'][5]); // Users: main 
			unset($submenu['users.php'][10]); // Users: add new
			if (!isset($_GET['edit_ftr']))
			unset($submenu['heyyou/_functions.php'][1]);
		}
		
		// put "add new media"
		if (@$hys['settings']['dont_use_heyyou_media_library'] != 1) {
			$submenu['hys_media'][2] = @$submenu['upload.php'][10];
			//remove media-sub
			unset($menu[10]);
			unset($submenu['upload.php']);
		}
		
		//add default user types if not 
		if(!isset($current_user->allcaps['heyyou_subadmin']) || !isset($current_user->allcaps['heyyou_client']) ) {
			$getq = "SELECT * FROM $wpdb->options WHERE option_name = '{$wpdb->prefix}user_roles'";
			$results = $wpdb->get_results( $getq );
			$results = unserialize($results[0]->option_value);
			if (!isset($results['heyyou_client'])) {
				$results['heyyou_client'] 			= $results['administrator'];
				$results['heyyou_client']['name'] 	= 'heyyou_client';
				$results['heyyou_subadmin'] 		= $results['administrator'];
				$results['heyyou_subadmin']['name'] = 'heyyou_subadmin';
				$new_rslts = serialize($results);
				$updateq = "UPDATE $wpdb->options SET option_value = '{$new_rslts}' WHERE  
							option_name = '{$wpdb->prefix}user_roles'";
				$wpdb->query( $updateq );
			}
		}
		$exempt = @$hys['settings']['navview'];
		$exempt = explode(',',$exempt);
		$default_exempt = array('Users','heyyou');
		
		
		
		//go through menu
		if (is_array($menu)) {
			foreach ($menu as $ke => $name) {
				$prefix = $wpdb->prefix.'_capabilities';				
				if (!in_array($ke,$exempt) && !empty($name[0]) && !in_array($name[0],$exempt)) {
					//if item unchecked in settings, remove from meny
					if (@$current_user->allcaps['heyyou_subadmin'] == 1)  {
						if(!isset($hys['settings']['subadmin_menu_'.$ke])) {
							unset($menu[$ke]); //posts
						}
					}
					if (@$current_user->allcaps['heyyou_client'] == 1)  {
						if(!isset($hys['settings']['heyyou_menu_'.$ke])) {
							unset($menu[$ke]); //posts
						}
					}
				}
			}
		}
		
	}
	
/* // edit the admin menu
add_action( 'admin_menu', 'websitename_clean_menu' );
	function websitename_clean_menu() {
		$user_id = get_current_user_id();
		
		if ($user_id != 1) {
        remove_menu_page('link-manager.php');
        #remove_menu_page('themes.php');
        remove_menu_page('tools.php');
        remove_menu_page('edit-comments.php');
        #remove_menu_page('plugins.php');
        #remove_submenu_page( 'themes.php', 'themes.php' );
        #remove_submenu_page( 'themes.php', 'theme-editor.php' );
        #remove_submenu_page( 'themes.php', 'themes.php?page=custom-background' );
        #remove_submenu_page( 'themes.php', 'themes.php?page=thematic_opt' );
        #remove_submenu_page( 'widgets.php', 'theme-editor.php' );
        #remove_submenu_page( 'options-general.php', 'options-discussion.php' );
        #remove_submenu_page( 'options-general.php', 'options-writing.php' );
        #remove_submenu_page( 'options-general.php', 'options-reading.php' );
        #remove_submenu_page( 'options-general.php', 'options-permalink.php' );
        #remove_submenu_page( 'options-general.php', 'options-media.php' );
        #remove_menu_page('upload.php');
        #remove_menu_page('admin.php?page=w3tc_general');
        #remove_menu_page('admin.php?page=better_wp_security');
        #remove_menu_page('admin.php?page=wpcf7');
        #remove_submenu_page( 'index.php', 'update-core.php' );
        }
    }
	 */
	

/*-------------------------------------------------------------
 Name:      hys_create_edit_menus_and_columns

 Purpose:   Add heyyou plugin to menu, register settings
-------------------------------------------------------------*/
	function hys_admin_nav() {
		global $hys;
		//hys settings
		add_menu_page('hys_settings_page', 'heyyou', 'level_8', __FILE__, 'hys_settings_page',WP_PLUGIN_URL.'/heyyou/res/imgs/favicon.png',126);
		add_submenu_page( __FILE__, 'edit heyyou post', 'form', 'level_8', 'editheyyoupost', 'hys_submenu_editpage' );
		//edit colounms output				
		add_filter('manage_edit-page_columns', 'hys_edit_columns');
		add_action('manage_pages_custom_column', 'hys_custom_columns');
		//&& lets throw in the new easy-media page...
		if (@$hys['settings']['dont_use_heyyou_media_library'] != 1) {
			add_menu_page('heyyoumedia', 'Media', 'level_8', 'hys_media', 'hys_media','./images/media-button-music.gif',11);
			add_submenu_page("hys_media", "Media Categories", "Media Categories", "level_10", "edit-tags.php?taxonomy=media_category");
		}
	}	
	function hys_submenu_heyyoumedia() {
		global $taxnow;
		include('edit-tags.php');
	}
	function hys_update_post() {
		if (isset($_GET['update_heyyoupost']) && $_POST['hys_post_title']) {
			hys_post_save($_GET['post'],true);
			header('Location: '.get_bloginfo('wpurl').'/wp-admin/admin.php?page=editheyyoupost&post='.$_GET['post'].'&edit_ftr='.$_GET['edit_ftr'].'&message=1');
		}
	}

/*-------------------------------------------------------------
 Name:      hys_submenu_editpage
-------------------------------------------------------------*/
	function hys_submenu_editpage() {
		echo "<div class='wrap' style='padding-bottom:35px;'>";
		if (isset($_GET['edit_ftr'])) {
			$getparentpage 	= get_post($_GET['post']);
			$getheyyou 		= get_post($_GET['edit_ftr']);
			echo "
				<form name='post' method='POST' action='admin.php?page=editheyyoupost&post={$_GET['post']}&edit_ftr={$_GET['edit_ftr']}&update_heyyoupost'>
				<h2>Editing heyyou post: \"{$getheyyou->post_title}\" (from: \"{$getparentpage->post_title}\")</h2>";
			if (isset($_GET['message']))
				echo "<div id='message' class='updated fade'><p>heyyou post edited. <a href='post.php?post={$_GET['post']}&action=edit#hys_manage_metabox'>&larr; Back to full list</a> | 
				<a href='".get_permalink($_GET['post'])."'>View Page</a>.</p></div>";
			echo hys_post_form(); //edit form
			echo "</form>";
		} else {
			echo "<br /><br />A post must be selected to edit!";
		}
		echo "</div><!--/wrap-->";
	}
	
	
	
/*-------------------------------------------------------------
 Name:      hys_post_form

 Purpose:   Create the form for a hys post for new or editing
 Receive:   (int) ID
 Return:	html form
-------------------------------------------------------------*/
	function hys_post_form($theid = false) {
		global $wpdb,$post,$hys;
		
		$theid 		= (isset($_GET['edit_ftr']) && !empty($_GET['edit_ftr'])) ? intval($_GET['edit_ftr']) : $theid;
		
		//find parent term (hys_post -> hys_post-xx [where xx = parent page_id]))
		$myterms	= get_terms('hys_post_cats', 'orderby=count&hide_empty=0');			
		if ($myterms) {
			foreach ($myterms as $k => $cat)
				if ($cat->name == $hys['feature_code'])
					$parent_term_id = $cat->term_id;
		}
		
		// Add/Edit Post form title
		$thiseditpost 		= get_post($theid); // get the object from the ID
		if (!$theid)
			echo "<h4>Add new <em>heyyou</em> post</h4>";

		// define the vars to be used in the edit form
		$title			= $theid ? _wp_specialchars($thiseditpost->post_title,'single') : '';
		$date			= $theid ? $thiseditpost->post_date : '';
		$content		= $theid ? $thiseditpost->post_content : '';
		$timeanddate 	= ($theid) ? true : false;
		$meta 			= array();
	  	$hidedate 		= (isset($hys['hys_page_config']['include_date']) && $hys['hys_page_config']['include_date'] == 1) ? '' : "style='display:none;'";
		if ($theid) {
			$meta =  get_post_meta($theid, 'meta');	
			$meta = (isset($meta[0])) ? $meta[0] : array();
		}
		
		//find the order
		if ($theid) { // if editing keep the same
			$order = $thiseditpost->menu_order;
		} else { // if new post, add to top or botton
			$order = ($hys['hys_page_config']['newposts_toporbottom'] == 'top') ? 0 : hys_get_next_in_order('hys_post');
		}
		
		// Use nonce for verification, hidden details
		echo "		
			<input type='hidden' name='edit_ftr' 			value='{$theid}' />
			<input type='hidden' name='hys_post_noncename' 	value='".wp_create_nonce( 'noncename_heyyou_post' )."' />
			<input type='hidden' name='hys_parent_id' 		value='{$_GET['post']}' />
			<input type='hidden' name='parent_term_id' 		value='{$parent_term_id}' />
			<input type='hidden' name='feature_code' 		value='{$hys['feature_code']}' />
			<input type='hidden' name='is_a_draft' 			value='{$thiseditpost->post_status}' />
			<input type='hidden' name='hys_post_order' 		value='{$order}' />\n 
			<input type='hidden' name='toporbottom'			value='{$hys['hys_page_config']['newposts_toporbottom']}' />\n"; 
		//HTML FORM..
		?>
	  	<br />
	  	<table <?= (isset($_GET['page']) && $_GET['page'] == 'editheyyoupost') ? "style='width:800px;'" : '' ?>>
	  		<tr>
	  			<td valign=top style='width:150px !important;'>
	  				<p>
	  				<? 
	  					if (@$hys['hys_page_config']['custom_title'] == 1)
	  						echo @(!empty($hys['hys_page_config']['custom_title_alt'])) ? str_replace(':','',$hys['hys_page_config']['custom_title_alt']).":" : "Name:";
	  					else
	  						echo "Title:";
	  				 ?>
	  				 </p>
	  			</td>
	  			<td style='<?= (isset($_GET['page']) && $_GET['page'] == 'editheyyoupost') ? "width:650px;" : '' ?>'>
	  				<input type='text' name='hys_post_title' value='<?=$title?>' class='regular-text' size=55/><br />
	  				<div style='height:7px;'><!-- --></div>
	  			</td>
	  		</tr>
	  		<tr <?=$hidedate?>>
	  			<td valign=top width=100>
	  				<p>Date:</p>
	  			</td>
	  			<td>
					<?= datedropdown($date,$timeanddate); ?>
	  				<div style='height:7px;'><!-- --></div>
	  			</td>
	  		</tr>
			<?php
			//META INFO
			$m = 0;
			if (isset($hys['hys_page_config']['meta'])) {
				foreach ($hys['hys_page_config']['meta'] as $k => $meta_name) {
					$tinymcetextarea = false;
					$slug = hys_url_friendly($meta_name);
					$meta[$slug] = (isset($meta[$slug])) ? _wp_specialchars($meta[$slug],'single') : @$meta[$m];
					//different media types
					  # (URL)   = url field
					  # (Media) = dropdown list of media
					  # (Blurb)	= textarea
					  # (Code)  = output will be <pre>, no nl2br
					  $mname = strtolower($meta_name);
					  $mtype = @$hys['hys_page_config']['meta_type'][$k];
					  
					if (strpos($mname,'(media)') || $mtype == 'media') {
						$metafeild = "<select name='hys_posts_meta[{$slug}]'>".hys_listmedia($meta[$slug])."</select>";
					}
					elseif ($mtype == 'page') {
						$metafeild = "<select name='hys_posts_meta[{$slug}]'>";
						$metafeild .= "<option></option><optgroup label=\"Pages\">";
							$pagess = get_pages();
							foreach ($pagess as $apage) {
								$preselpg = ($meta[$slug] == $apage->ID) ? " selected='selected'" : '';
								$metafeild .= "<option value='{$apage->ID}'{$preselpg}>".hys_chopstring($apage->post_title,35)."</option>";
							}
						$metafeild .= "</optgroup>";
						$metafeild .= "</select>";
					}
					elseif (strpos($mname,'(blurb)') || $mtype == 'blurb') {
						$metafeild = "<textarea name='hys_posts_meta[{$slug}]' style='height:75px !important;'>{$meta[$slug]}</textarea>";
					}
					elseif (strpos($mname,'(textarea)') || $mtype == 'textarea') {
						$tinymcetextarea = true;
						$metafeild = '';
					}
					elseif (strpos($mname,'(checkbox)')  || $mtype == 'checkbox'){
						$metafeild = "<input type='checkbox' value='1' name='hys_posts_meta[{$slug}]' ".chckchckbox($meta[$slug])." />";
					}
					elseif (strpos($mname,'(code)')  || $mtype == 'code'){
						$metafeild = "<textarea name='hys_posts_meta[{$slug}]' class='code' style='height:75px !important;font-size:10px;'>{$meta[$slug]}</textarea>";
					}
					elseif (strpos($mname,'(url)')  || $mtype == 'url') {
						$meta[$slug] = (strlen($meta[$slug]) < 8) ? 'http://' : $meta[$slug];
						$metafeild = "<input type='text' name='hys_posts_meta[{$slug}]' value='{$meta[$slug]}' size=30 class='code hys_url_meta_feild' id='url_feild_{$k}' ".
									 "onblur='change_url_color(0, \"url_feild_{$k}\")' onfocus='change_url_color(1, \"url_feild_{$k}\")' /> ";
					}
					/*elseif @(strpos(strtolower($meta_name),'(chckbx)')  || $hys['hys_page_config']['meta_type'][$i]== 'chckbx')
						$metafeild = "<input type='checkbox' name='hys_posts_meta[{$slug}]' value='1' ".chckchckbox($meta[$slug])." />";*/
					else {
						$metafeild = "<input type='text' name='hys_posts_meta[{$slug}]' value='{$meta[$slug]}' size=30 />";
					}

					if (!empty($meta_name)) {
						echo "
				  		<tr>
				  			<td valign=top width=100>
				  				<p>{$meta_name}:</p>
				  			</td>
				  			<td>";
				  		if ($tinymcetextarea == true) {
							echo '<div id="poststuff" class="hys_post_textarea" style="margin-bottom:15px">';
				  			the_editor( html_entity_decode ($meta[$slug]), "hys_posts_meta[{$slug}]");
				  			echo '</div>';
				  		}
				  		echo "
				  				{$metafeild}
				  			</td>
				  		</tr>\n";
					}
					$m++;
				}
			}
			
			//ATTACHMENTS
	  		if (isset($hys['hys_page_config']['include_attach']) && $hys['hys_page_config']['include_attach'] == 1) {	
	  		?>
	  		<tr>
	  			<td valign=top width=100>
	  				<p>Attachments:</p>
	  			</td>
	  			<td>
					<?php
					if (isset($_GET['edit_ftr'])) 
						attachments_add();
					else 
						echo "<small style='color:#888'><em>(Attachments can be added after a post is submited)</em></small>";
					?>
	  				<div style='height:7px;'><!-- --></div>
	  			</td>
	  		</tr>
	  		<?php
	  		}

			//CATEGORIES
	  		if (@$hys['hys_page_config']['include_cats'] == 1) {
	  		?>
	  		<tr>
	  			<td valign=top width=100>
	  				<p>Category:</p>
	  			</td>
	  			<td>
	  				<select name='hys_post_cat'>
	  					<option value=0></option>
	  					<?php 
  						$myterms = get_terms('hys_post_cats', 'hide_empty=0&parent='.$parent_term_id.'&orderby=count');
  						$lastctorder = 1;
  						$cterms = 0;
  						foreach ($myterms as $k => $termcat) {
  							$sel = ($cat == $termcat->term_id || @$meta['hys_post_cat'] == $termcat->term_id) ? " selected='selected'": '';
  							echo "<option value='{$termcat->term_id}'{$sel}>".hys_chopstring($termcat->name)."</option>";
	  						$lastctorder = $termcat->count;
	  						$cterms++;
  						}
  						$lastctorder = ($cterms == 0) ? 0 : $lastctorder;
	  					?>	
	  				</select> or: 
	  				<input type='text' name='hys_post_cat_new' value='Add New Category' class='regular-text' size=30 id='newcategory' onfocus='form_focus("newcategory")' onblur="form_blur('newcategory')" style='width:150px;' /><br />
	  				<input type="hidden" name="hys_post_cat_new_order" value="<?= ($lastctorder+1) ?>" />
	  				<div style='height:7px;'><!-- --></div>
	  			</td>
	  		</tr>
	  		<?php
	  		} else {
				echo "<input type='hidden' name='hys_post_cat' value='' />"; //@TODO: find out why this is here and remove it
	  		}
	  		
	  		//MAIN hys_post BLURB
	  		?>
	  		<tr>
	  			<td valign=top colspan=2> 	  			
					<?php
					if (@$hys['hys_page_config']['include_blurb'] == 1)  {
						echo '<div id="poststuff" class="hys_post_textarea">';
						the_editor(stripslashes($content), "hys_post_blurb");
						echo "</div>";
						?>
						<table id="post-status-info" cellspacing="0">
							<tbody><tr> 
							<td><div style='height:20px;'></div></td> 
						</tr></tbody></table> 
						<?php
					} else {
	  					echo "<input type='hidden' name='hys_post_blurb' value='{$content}' />";
			  		}
					?>
	  				<div style='height:17px;'></div>
	  				<input type='submit' class='button-primary' name='hys_post_submit' value='Submit <?php 
	  					echo $theid ? "EDIT" : "NEW"." heyyou post"; 
						?> entry' />
						
					<?php if ($theid) echo "<a href='post.php?post={$_GET['post']}&action=edit#hys_manage_metabox' class='button'>&larr; Back to full list / Cancel</a>"; ?>
	  			</td>	  			
	  		</tr>
	  	</table>
	  	<br class='clear' />
	  	
		<?
	}

/*-------------------------------------------------------------
 Name:      temds10_edit_columns

 Purpose:   edit the colounms to the following:
-------------------------------------------------------------*/
	function hys_edit_columns($columns) {
		$columns = array(
			"cb" 		=> "<input type=\"checkbox\" />",
			"title" 	=> "Event Title",
			"hys_inuse" => "heyyou"
		);
		return $columns;
	}

/*-------------------------------------------------------------
 Name:      temds10_custom_columns

 Purpose:   put content in custom colounms
-------------------------------------------------------------*/
	function hys_custom_columns($column) {
		global $hys, $post;
		$custom = get_post_custom($post->ID);
			// get the id and the META for that page
			$thispagesid 		= $post->ID;
			//get custom "hys_page_config" fields
			$custom_fields 		=  get_post_meta($thispagesid, 'hys_page_config');
			$hys_page_config = ($custom_fields) ? $custom_fields[0] : 0;
			
			switch ($column) {
			case "hys_inuse":
				echo (!empty($hys_page_config['preset'])) ? "<span style='color:#999'>*</span>" : '';
			break;
		}
	}
/*-------------------------------------------------------------
 Name:      hys_photogaltopage

 Purpose:   adds attachments plugin to specified page(s)
 			as option provides in heyyou
-------------------------------------------------------------*/
function hys_photogaltopage() {
	global $hys;
	
	if (@$hys['settings']['no_attachments'] == 1) return;
	if (isset($hys['hys_page_config']['show_pg_img']))
		add_meta_box( 'attachments_list', __( 'Photo Gallery', 'attachments_textdomain' ), 'attachments_add', 'page', 'normal' );
	if (isset($hys['hys_page_config']['show_pt_img']))
		add_meta_box( 'attachments_list', __( 'Photo Gallery', 'attachments_textdomain' ), 'attachments_add', 'post', 'normal' );
}

/*-------------------------------------------------------------
 Name:      hys_secondary_blurb

 Purpose:   return secondary blurb for page
-------------------------------------------------------------*/
	function hys_secondary_blurb($id = '') {
		$id = (empty($id)) ? get_the_ID() : intval($id);
		$custom = get_post_custom($id);
		return @$custom['secondary_blurb'][0];
	}
	
/*-------------------------------------------------------------
 Name:      secondary_blurb_box

 Purpose:   metabox for secondary blurb
-------------------------------------------------------------*/
	function secondary_blurb_box() {
		global $post;
		echo '
		<style type="text/css">
		#ed_toolbar { display:none; }
		</style>
		<div id="" class="">';
		the_editor(hys_secondary_blurb(), "secondary_blurb");
		echo "</div>";
	}

/*-------------------------------------------------------------
 Name:      hys_settings_page

 Purpose:   Add heyyou plugin to menu
-------------------------------------------------------------*/
	function hys_settings_page() {
		global $hys;
		if (!current_user_can('manage_options')) 
			wp_die( __('You cannot access this page. (mention error hys function error 244).') );
		hys_settings_page_output(); //print form from options.php
	} 	
	
	function hys_load_jquery() {
		global $hys;
	    wp_enqueue_script( 'jquery' , $hys['dir'].'/res/js/jquery.1.7.1.js');
	} 
	
/*-------------------------------------------------------------
 Name:      hys_admin_header

 Purpose:   add to header
-------------------------------------------------------------*/
	function hys_admin_header() {
		global $hys, $post;
		
		$admin_css = '';
		
		if (@$hys['hys_page_config']['hideblurb'] == 1 && $post->post_type == 'page')
			$admin_css .= "#postdivrich.postarea { display:none; }\n";
		
		// "Title" for the "Attachments" plugin
		if (@$hys['settings']['attach_use_titles'] == 1) {
			$admin_css .= ".field_attachment_title, body#media-upload tr.post_title { visibility: visible !important; }\n";
		} else {
			$admin_css .= ".field_attachment_title, body#media-upload tr.post_title { display:none !important; }\n";
		}
		
		if (@$_GET['post_type'] == 'page' && $hys['user'] == 'heyyou_client' && !empty($hys['settings']['tutid']))
			$admin_css .= " tr#post-{$hys['settings']['tutid']} { display:none !important; } ";
		
		if (!empty($admin_css))  {
			$admin_css = "
				<style type='text/css'>
					{$admin_css}
				</style>\n";
		}
		
		$header = "
			<!-- heyyou HEADER INFO -->";
		$header .= $admin_css;
		
		$header .= "
		
				<link rel='shortcut icon' href='{$hys['dir']}/res/imgs/favicon.ico' /> 
				<link href='{$hys['dir']}/res/css/hys_style_admin.css' rel='stylesheet' type='text/css' />
				<script type='text/javascript' src='{$hys['dir']}/res/js/heyyou.js'></script>
				<script type='text/javascript' src='{$hys['dir']}/res/js/js_admin.js'></script>";
		$header .= (isset($_GET['post']) && @$_GET['action'] == 'edit' && !empty($hys['config'])) ? "
				<script src='{$hys['dir']}/res/js/mootools-1.2.4.js' type='text/javascript'></script>" : '';
		$header .= "
			<!-- /heyyou HEADER INFO -->\n\n";
		echo $header;
		
		// count number of cats, hard recount to include empty cats (pulled from function get_heyyou())
		$numoflists = 0;
 		$page_id = (empty($page_id)) ? @$post->ID : intval($page_id);
		$get_taxonomies 		= get_taxonomies();
		$myterms 				= get_terms('hys_post_cats', 'orderby=count&hide_empty=0');			
		$using_categories = (isset($hys['hys_page_config']['include_cats']) && $hys['hys_page_config']['include_cats'] == 1) ? true : false;

		if (isset($myterms)) { // if there are categories:
			$parent_term_id = '';
			foreach ($myterms as $k => $cat)
				if ($cat->name == 'hys_post-'.$page_id)
					$parent_term_id = $cat->term_id;
			if ($using_categories) {
				//run though the cats and posts, build into an array
				foreach ($myterms as $k => $cat) { //cycle through cats	
					if ($cat->parent == $parent_term_id) // if the cat is in the parent (hys_post-xxx)
						$numoflists++;
				}
			}
		}
		$getheyyou = get_heyyou();
		if (isset($getheyyou['uncategorized']))
			$numoflists++; //add uncategorized
		
		if ($numoflists > 0) {
			$list_vars = '';
			$sort_order = '';
			$get_ids = '';
			$make_sort = '';
			
			for($i = 0; $i != $numoflists; $i++) {
			$list_vars .= "
			var list{$i} 		= document.id('sortable-list-{$i}');\n\n";
			
			$sort_order .= "
			list{$i}.getElements('li').each(function(li) {
				sortOrder.push(li.retrieve('id'));
			});\n\n";
				
			$get_ids .= "
			list{$i}.getElements('li').each(function(li) {
				li.store('id',li.get('title')).set('title','');
			});\n\n";
	
			$make_sort .= "#sortable-list-{$i} ";
			$make_sort .= ($i == ($numoflists-1)) ? "" : ', ';
			}
		}
		?>
		<? add_action('admin_enqueue_scripts', 'hys_load_jquery'); ?>
		<script type='text/javascript'>
				
		window.addEvent('domready', function() {	
			
		<? if ($numoflists > 0) { ?>
			//define vars
			var sortInput 	= document.id('sort_order');
			
			<?= $list_vars?>
			
			// pull id's and define $_POST[sortInput]
			var fnSubmit = function(save) {
				var sortOrder = [];
				<?= $sort_order ?>
				sortInput.value = sortOrder.join(',');
			};
			
			//turn the titles into ids
				<?= $get_ids ?>
			
			//make items sortable
			new Sortables("<?=$make_sort?>",{
				constrain: true,
				clone: true,
				revert: true,
				onComplete: function(el,clone) { fnSubmit(); }
			});

		<? } ?>
		});
		</script>
		<?
	}
	
	
/*-------------------------------------------------------------
 Name:      content_save_pre

 Purpose:   in the admin, if a <!--more--> tag doesn't apear at 
 			the beggining of a line, move it there
 Receive:   $the_content (string)
 Return:	$the_content (string)
-------------------------------------------------------------*/
add_filter('content_save_pre','hys_fix_position_of_more');
	function hys_fix_position_of_more($the_content) {
		//Find: <!--more--> and extract it from that line,
		$lines = explode("\n", $the_content);
		$mr = '<!--more-->';
		foreach ($lines as $key => $aline) {
			if (strpos($aline, $mr)) {
				//put a beginning of that line
				$lines[$key] = $mr.str_replace($mr,'',$aline);
				break;
			}
		}
		return implode("\n", $lines);
	}
	
	
/*-------------------------------------------------------------
 Name:      hys_post_save

 Purpose:   handle saving of content from hys_post_form()
-------------------------------------------------------------*/
	function hys_post_save($post_id, $from_hardcall = false) {
		global $wpdb, $hys;
		
		if (!isset($_POST['hys_post_title'])) return;
				
		// lets trim the meta feilds...
		if (isset($_POST['hys_page_config']['meta']) && array($_POST['hys_page_config']['meta'])) {
			$trimed_meta_feilds = array();
			foreach ($_POST['hys_page_config']['meta'] as $key => $value) {
				$trimed_meta_feilds[$key] = trim($value);
			}
			$_POST['hys_page_config']['meta'] = $trimed_meta_feilds;
		}
		
		// verify this came from the our screen
		$post_id = (isset($post_id)) ? $post_id : intval($_GET['post']);
		if (!isset($_POST['hys_post_noncename']) || !wp_verify_nonce( $_POST['hys_post_noncename'],'noncename_heyyou_post'))
			return $post_id;
		if( !current_user_can( 'edit_post', $post_id ) ) // check permissions
			return $post_id;
		
		//drag and drop ordering save
		$postorder_ids = @explode(',',$_POST['sort_order']);
		$offsetorder = (@$hys['hys_page_config']['newposts_toporbottom'] == 'bottom') ? 1 : 2;

		foreach($postorder_ids as $index=>$porderid) {
			$index = intval($index);
			$offsetorder = intval($offsetorder);
			$porderid = intval($porderid);
			if($porderid != '') {
				$query = 'UPDATE '.$wpdb->prefix.'posts SET menu_order = '.($index + $offsetorder).' WHERE ID = '.$porderid;
				$wpdb->query($query);
			}
		}
		
		//IMPORT from EXPORT
		if (!empty($_POST['IMPORT_hys_page_config'])) {
			$importme = stripslashes($_POST['IMPORT_hys_page_config']);
			$_POST['hys_page_config'] = unserialize($importme);
		}
		
		//if DELETE selected posts
		if (isset($_POST['selected_id'])) {
			foreach ($_POST['selected_id'] as $theid => $dlt) {
				wp_trash_post($dlt);
				$_POST['selected_id'][$theid] = '';
				unset($_POST['selected_id'][$theid]);
				#wp_delete_post(intval($theid));//@TODO
			}
			unset($_POST['selected_id']);
		}
		
		//lets update the categories if applic
		if (count(@$_POST['update_cat']) > 0) {
		  foreach ($_POST['update_cat'] as $term_id => $t_vals) {
			$wpdb->query("UPDATE $wpdb->term_taxonomy SET count = ".intval($t_vals['count'])." WHERE term_id = ".intval($term_id) ); //update count/order
			$wpdb->query("UPDATE $wpdb->term_taxonomy SET description = '".mysql_real_escape_string($t_vals['description'])."' WHERE term_id = ".intval($term_id)); //update meta
			$wpdb->query("UPDATE $wpdb->terms SET name = '".mysql_real_escape_string($t_vals['name'])."' WHERE term_id = ".intval($term_id)); //update name/title			
			//cat_override
			if(isset($_POST['cat_override'])) {
				foreach($_POST['cat_override'] as $blurb_term_id => $cat_override) {
					//add the meta
					update_post_meta($blurb_term_id,'cat_override',$cat_override) or
					add_post_meta($blurb_term_id,'cat_override',$cat_override);
				}
			}
			
		  }
		}
		

		

		// find the post date
		$sel_post_date = (isset($_POST['sel_date'])) ? reconstruct_datedropdown($_POST['sel_date']) : '';
				
		//get the order
		$hys_post_order = ( isset($_POST['hys_post_order']) && !empty($_POST['hys_post_order'])) 
								? $_POST['hys_post_order'] : 0;
		//put on buttom of list if selected in: heyyou page config > "Features", or put where pre-exsist if editing
		$hys_post_order = @($_POST['toporbottom'] == 'bottom' || isset($_POST['edit_ftr'])) ? $_POST['hys_post_order'] : 0;
		
		//if post cats new
		if (!empty($_POST['hys_post_cat_new']) && $_POST['hys_post_cat_new'] != 'Add New Category') {
			$slug = hys_url_friendly($_POST['hys_post_cat_new']);

			//get the parent term
			$get_taxonomies = get_taxonomies();
			$myterms = get_terms('hys_post_cats', 'orderby=count&hide_empty=0');						
			
			if (isset($myterms)) {
				//get the parent TERM
				foreach ($myterms as $k => $cat)
					if ($cat->name == $_POST['feature_code'])
						$parent_term_id = $cat->term_id;
			}
			
			//define new term
			$newterm = wp_insert_term(
				$_POST['hys_post_cat_new'], // the term 
				'hys_post_cats', // the taxonomy
				array(
					'description '	=> $_POST['hys_post_cat_new'],
					'slug' 			=> $slug,
					'parent'		=> $parent_term_id
				)
			);
			
			//get newly entered cat and set it as the selected cat
			$mynewterm = $wpdb->get_row("SELECT * FROM $wpdb->terms WHERE name = '".mysql_real_escape_string($_POST['hys_post_cat_new'])."'");
			$_POST['hys_post_cat_new'] 	= '';
			$_POST['hys_post_cat'] 	 	= (isset($mynewterm->term_id)) ? $mynewterm->term_id : '';
			
			//add the last order +1
			$termordr = intval($_POST['hys_post_cat_new_order']);
			$wpdb->query("UPDATE $wpdb->term_taxonomy SET count = {$termordr} WHERE term_id = {$mynewterm->term_id}");
		}
		
		$the_post_status = (isset($_POST['is_a_draft']) && $_POST['is_a_draft'] == 'draft') ? 'draft' : 'publish';
		
		// Create post object
		$my_post = array(
			'post_type' 	=> 'hys_post',
			'post_status' 	=> $the_post_status,			
			'post_date' 	=> $sel_post_date,
			'post_title' 	=> $_POST['hys_post_title'],
			'menu_order' 	=> intval($hys_post_order),
			'post_content' 	=> $_POST['hys_post_blurb'],
			'post_parent'	=> @intval($_POST['hys_parent_id'])
		);
		
		if (isset($_POST['edit_ftr']) && !empty($_POST['edit_ftr'])) {
			$new_id = intval($_POST['edit_ftr']);
			$my_post['ID'] = $new_id;
			$new_id = wp_insert_post( $my_post ); //<< this call's post_updated()
			// this hook doesn't trigger for whatever reason, so lets add it
			attachments_save($new_id);
		} else {
			$new_id = wp_insert_post($my_post,false);
		}
		
		// show/publish new ALL updated/new posts
		if ($the_post_status != 'draft') //@TODO: make sure this doesn't break anything
			hys_showhide_post(intval($new_id), 1);
		
		//add the meta
		$hys_posts_meta = (isset($_POST['hys_posts_meta'])) ? $_POST['hys_posts_meta'] : get_post_meta(intval($new_id), 'meta',true);
				
		//add the grouping
		$hys_posts_meta['hys_post_cat'] = @$_POST['hys_post_cat'];
				
		// remove any "http://http://" if user copy/pasted a URL without noticing the default value "http://"
		foreach ($hys_posts_meta as $mk => $mv) {
			$pos = substr($mv,0,14);
			$poss = substr($mv,0,15);
			if ($pos === 'http://http://')
				$hys_posts_meta[$mk] = str_replace('http://http://','http://',$mv);
			if ($poss === 'http://https://')
				$hys_posts_meta[$mk] = str_replace('http://https://','https://',$mv);
		}
		
		//add the meta
		delete_post_meta(intval($new_id), 'meta');
		add_post_meta(intval($new_id), 'meta', $hys_posts_meta);
		
		//if there's attachments on the parent, don't give them to the heyyou post
		if (isset($_POST['attachment_title_1']) && (isset($_POST['action']) && $_POST['action'] == 'editpost'))
			delete_post_meta($new_id, '_attachments');
	}




/*====================================================================================================================
   // !3 TinyMCE & Wordpress Edits 
--------------------------------------------------------------------------------------------------------------------*/

/*-------------------------------------------------------------
  Name:      hys_mce_admin_init
-------------------------------------------------------------*/
	function hys_mce_admin_init() {
		global $hys; 
		wp_enqueue_script('jquery');
		wp_enqueue_script('word-count');
		wp_enqueue_script('post');
		wp_enqueue_script('editor');
		wp_enqueue_script('media-upload');
		wp_enqueue_script('jquery-ui-tabs');
		// add the "Insert Link" hidden <form> at the end of the page
		// only needed on the EDIT page as it's already called on the Page screen
		if (isset($_GET['edit_ftr']) && @$hys['hys_page_config']['include_blurb'] == 1) {
			add_action( 'tiny_mce_preload_dialogs', 'wp_link_dialog', 30 );
		}
	}
	

/*-------------------------------------------------------------
  Name:      hys_mce
-------------------------------------------------------------*/
	function hys_mce( $init ) {
		global $hys;
		
		if (function_exists('hys_custom_tinymce')) {
			$custom_mce = hys_custom_tinymce();
			if ($custom_mce == 'all') {
				//nothing, default tinymce used
			} else {
				$init['theme_advanced_blockformats'] = $custom_mce['blockformats'];
				$init['theme_advanced_buttons1'] = $custom_mce['buttons1'];
				$init['theme_advanced_buttons2'] = $custom_mce['buttons2'];
				$init['spellchecker_languages'] = '+English=en';
			} 
		} else {
			$init['theme_advanced_blockformats'] = 'p,h1,h2,h3,h4,h5,h6,code';
			$init['theme_advanced_buttons1'] = /*removeformat,*/ 'formatselect,styleselect,|,bold,italic,underline,'.
				'yourhys_line,yourhys_space,|,link,unlink,|,justifyleft,justifycenter,justifyright'.
				',|,bullist,blockquote,|,pastetext,wp_fullscreen,|,wp_more';
			$init['theme_advanced_buttons2'] = '';	
			$init['spellchecker_languages'] = '+English=en';
		}
				
		$styles = '';
		if (isset($hys['settings']['tinymce_css'])) {
			$numstyls = 0;
			foreach ($hys['settings']['tinymce_css'] as $style)
				if (!empty($style)) $numstyls++;
			$s = 1;
			foreach ($hys['settings']['tinymce_css'] as $style) {
				if (!empty($style)) {
					$styles .= "{$style}={$style}";
					$styles .= ($s != $numstyls) ? "," : '';
					$s++;
				}
			}
		}
		$init['theme_advanced_styles'] = $styles; //'Purple=purple,Gray=gray,Black=black'
		return $init;		
	}

/*-------------------------------------------------------------
  Name:      hys_line_btn
-------------------------------------------------------------*/
	function hys_line_btn() {
		// Don't bother doing this stuff if the current user lacks permissions
		if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') ) return;
		
		// Add only in Rich Editor mode
		if ( get_user_option('rich_editing') == 'true')
			add_filter("mce_external_plugins", "hys_line_tinymce_plugin");
	}

/*-------------------------------------------------------------
  Name:      hys_line_tinymce_plugin

  Purpose:   Load the TinyMCE plugin : editor_plugin.js (wp2.5)
-------------------------------------------------------------*/
	function hys_line_tinymce_plugin($plugin_array) {
		$plugin_array['yourhys_line'] = get_bloginfo('wpurl').'/wp-content/plugins/heyyou/res/js/editor_plugin.js';
		return $plugin_array;
	}

/*-------------------------------------------------------------
  Name:      hys_rfh_mce
-------------------------------------------------------------*/
	function hys_rfh_mce($ver) {
		$ver += 3;
		return $ver;
	}
	

	// custom CSS for TINYMCE
	function hys_tinymce_css($wp) {
		global $hys;
		
		if (file_exists($hys['dir']."/style_tinymce.css")) {
		$wp = $hys['dir']."/style_tinymce.css";
		
		if (isset($hys['settings']['header_tinymce']) && !empty($hys['settings']['header_tinymce']))
			$wp = get_bloginfo('stylesheet_directory')."/{$hys['settings']['header_tinymce']}";
				
		return $wp;
		} else {
			return '';
		}
	}	
	
/*-------------------------------------------------------------
 Name:      hys_mail

 Purpose:   change content type of emails
-------------------------------------------------------------*/
	function hys_mail($content_type){
		return 'text/html';
	}
	
	
/*-------------------------------------------------------------
 Name:      hys_rmv_metabxs

 Purpose:   remove metaboxes from pages/posts
-------------------------------------------------------------*/
	function hys_rmv_metabxs() {
		global $hys;
		
		$main_metaboxes_post = array(
			'postcustom' 		=> 'Custom Fields',
			'postexcerpt' 		=> 'Excerpt',
			'commentstatusdiv' 	=> 'Comments',
			'trackbacksdiv' 	=> 'Talkback',
			//'slugdiv' 			=> 'Slug',
			'authordiv' 		=> 'Author'
		);
		$main_metaboxes_pages = array(
			'postcustom' 		=> 'Custom Fields',
			'postexcerpt' 		=> 'Excerpt',
			'commentstatusdiv' 	=> 'Comments',
			'commentsdiv' 		=> 'Comments',
			'trackbacksdiv' 	=> 'Talkback',
			//'slugdiv' 			=> 'Slug',
			'authordiv' 		=> 'Author',
		);
		
		//POSTS
		foreach ($main_metaboxes_post as $widget=>$widget_name) {		
			if (@$hys['settings']['widget_post_'.$widget] != 1)
				remove_meta_box( $widget, 'post','normal' );
		}
		//PAGES
		foreach ($main_metaboxes_pages as $widget=>$widget_name) {
		
			if ($widget == 'postexcerpt' && @$hys['settings']['page_excerpts'] == 1) {
				//do not remove
			} else {
				if (@$hys['settings']['widget_page_'.$widget] != 1) {
					remove_meta_box( $widget, 'page','normal' );
				}
			}
		
		}
	}
	
	function hys_change_parent_to_media() {
		return 'hys_media';
	}

/*-------------------------------------------------------------
 Name:      hys_rmv_dash_metabxs

 Purpose:   remove dashboard widgets
-------------------------------------------------------------*/
	function hys_rmv_dash_metabxs() {
		global $hys, $wp_meta_boxes;
		$dash_metaboxes_side = array(
			'dashboard_primary' 		=> 'Primary??',
			'dashboard_secondary' 		=> 'Secondary??',
			'dashboard_quick_press' 	=> 'Quick Press',
			'dashboard_recent_drafts' 	=> 'Recent Drafts'
		);
		$dash_metaboxes_norm = array(
			'dashboard_right_now' 		=> 'Right Now',
			'dashboard_recent_comments' => 'Comments',
			'dashboard_incoming_links' 	=> 'Incom. Links',
			'dashboard_plugins' 		=> 'WP Plugins',
		);	
		//side
		foreach ($dash_metaboxes_side as $widget=>$widget_name) {
			if (@$hys['settings'][$widget] != 1) {
				unset($wp_meta_boxes['dashboard']['side']['core'][$widget]);
			}
		}
		//normal
		foreach ($dash_metaboxes_norm as $widget=>$widget_name) {
			if (@$hys['settings'][$widget] != 1) {
				unset($wp_meta_boxes['dashboard']['normal']['core'][$widget]);
			}
		}
		if (@$hys['settings']['not_heyshauna'] != 1)
			wp_add_dashboard_widget('custom_help_widget', 'heyyou', 'hys_dashboard_widget');

	}
/*-------------------------------------------------------------
 Name:      hys_dashboard_widget

 Purpose:   welcome ntoe widget on dashboard
-------------------------------------------------------------*/
	function hys_dashboard_widget() {
		global $current_user;
		get_currentuserinfo();
		
		//Load session username and global
		$username = $current_user->user_login;
		//find out what time of day message should be printed
		$hour = date("H");
		if 		($hour >= 0 && $hour <= 4) 	$timeofday = "you're up late";
		elseif 	($hour > 4 && $hour < 7) 	$timeofday = "you're up early";
		elseif 	($hour > 7 && $hour < 12) 	$timeofday = "good morning";
		elseif 	($hour >= 12 && $hour < 17) 	$timeofday = "good afternoon";
		elseif 	($hour >= 17 && $hour < 24) $timeofday = "good evening";
		else 	$timeofday = "hello";

		//print welcome note
		echo "
		<div class='hys_welcome_note'>
		{$timeofday} {$username}!<br /><br />
		welcome to the administrative panel for your website. if you have any questions at all, or run into any oddities, please feel free to contact us:<br />
		<br />
		shauna: 
				<ul>
					<li>
						send a tweet to <a href='http://twitter.com/hey_shauna' target='_Blank'>@hey_shauna</a>
					</li>
					<li>
						or email at <a href='mailto:info@heyshauna.com'>info@heyshauna.com</a>
					</li>
				</ul>
		<span style='color:#F6F6F6;font-family: Courier; font-size: 10px; '>
		".date("l F jS, Y, g:ia")."
		</span>
		</div>";	
	
	}


 
/*====================================================================================================================
   // !4 heyyou output 
--------------------------------------------------------------------------------------------------------------------*/

/*-------------------------------------------------------------
  Name:      hys_clean_wp_head

  Purpose:   remove defaults from wp_head
-------------------------------------------------------------*/

remove_action('wp_head', 'index_rel_link');
remove_action('wp_head', 'parent_post_rel_link', 10, 0);
remove_action('wp_head', 'start_post_rel_link', 10, 0);
remove_action('wp_head', 'adjacent_posts_rel_link', 10, 0);
remove_action( 'wp_head', 'rsd_link'); 
remove_action( 'wp_head', 'wlwmanifest_link');
remove_action( 'wp_head', 'index_rel_link');
remove_action( 'wp_head', 'wp_generator');
remove_action( 'wp_print_styles', 'wpcf7_enqueue_styles' );
add_filter( 'index_rel_link', 'disable_stuff' );
add_filter( 'parent_post_rel_link', 'disable_stuff' );
add_filter( 'start_post_rel_link', 'disable_stuff' );
add_filter( 'previous_post_rel_link', 'disable_stuff' );
add_filter( 'next_post_rel_link', 'disable_stuff' );

	
	
/*-------------------------------------------------------------
 Name:      hys_header_keywords

 Purpose:   heyyou header..
-------------------------------------------------------------*/
	function hys_header_meta() {
		global $hys;
		$viewport = "
	<meta name='viewport' content='width=device-width'>";
		$meta_seo = '';
		//meta keywords and description, show if not empty and not default input placeholder instructions
		$key_default = 'Add 5 keywords/phrases here seperated by comas for better SEO';
		if (isset($hys['settings']['meta_keywords']) && !empty($hys['settings']['meta_keywords']) && ($key_default != $hys['settings']['meta_keywords']))
		$meta_seo .=  "
	<meta name='keywords' content=\"".str_replace('"', "'", $hys['settings']['meta_keywords'])."\">";
		$pos = strpos($hys['settings']['meta_description'], 'of your website here for search engine descriptions, and better SEO');
		if ($pos === false && !empty($hys['settings']['meta_description'])) {
		$meta_seo .= "
	<meta name='description' content=\"".str_replace('"', "'", $hys['settings']['meta_description'])."\">\n";
		}
		
		echo apply_filters('hys_viewport',$viewport);
		echo apply_filters('hys_meta_seo',$meta_seo);
	}
	

/*-------------------------------------------------------------
 Name:      hys_header

 Purpose:   heyyou header..
-------------------------------------------------------------*/
	function hys_header() {
		global $hys;
		
		//if someone trys to get into the tutorial page whose not logged in, boot
		if ((isset($hys['settings']['tutid']) && !empty($hys['settings']['tutid'])) && (get_the_ID() == $hys['settings']['tutid']) && !is_user_logged_in())
			die('please login to view this page.');
				
		// fav and apple touch icon, if defined in wp-admin > heyyou > settings
		if (isset($hys['settings']['header_favicon']) && !empty($hys['settings']['header_favicon']))
			echo "
	<link rel='shortcut icon' href='".get_bloginfo('stylesheet_directory')."/{$hys['settings']['header_favicon']}' type='image/x-icon' /> ";
		if (isset($hys['settings']['header_touchicon']) && !empty($hys['settings']['header_touchicon']))
			echo "
	<link rel='apple-touch-icon' href='".get_bloginfo('stylesheet_directory')."/{$hys['settings']['header_touchicon']}'  /> ";
		
		//custom styles for tutorial page..
		$styles = '';
		if (isset($hys['settings']['tutid']) && !empty($hys['settings']['tutid']) && !is_user_logged_in())
			$styles .= ".page-item-{$hys['settings']['tutid']}, .menu-item-{$hys['settings']['tutid']} { display:none !important }";		
		if ((@$hys['hys_page_config']['hidetitle'] == 1 || (@$hys['hys_page_config']['hidetitlesingle'] == 1 && isset($_GET['hypg'])))  && !is_single())
			$styles .= "h1.entry-title, .entry-title { display:none !important; }";
			
		if (!empty($styles)) echo "
	<style type='text/css'>
	{$styles}
	</style>\n";
	
	if (@$hys['settings']['jquery_lightbox'] == 1 && @$hys['settings']['jquery_lightbox_assign'] == 1) {
		echo "
	<script type='text/javascript'>
	jQuery(function() {
	    jQuery('.attachments a, .hys_attach ul li .attach_image a').lightBox({
	        imageLoading: '{$hys['dir']}/res/imgs/lightbox-ico-loading.gif',
	        imageBtnPrev: '{$hys['dir']}/res/imgs/lightbox-btn-prev.gif',
	        imageBtnNext: '{$hys['dir']}/res/imgs/lightbox-btn-next.gif',
	        imageBtnClose:'{$hys['dir']}/res/imgs/lightbox-btn-close.gif',
	        imageBlank:   '{$hys['dir']}/res/imgs/lightbox-blank.gif'
	    });
	    if (jQuery('ul.photo_gallery')) {
			jQuery('ul.photo_gallery').each( function (index) {
				id = jQuery(this).attr('id')
				newlightbos = 'ul#'+id+' li a';
				jQuery(newlightbos).lightBox({
			        imageLoading: '{$hys['dir']}/res/imgs/lightbox-ico-loading.gif',
			        imageBtnPrev: '{$hys['dir']}/res/imgs/lightbox-btn-prev.gif',
			        imageBtnNext: '{$hys['dir']}/res/imgs/lightbox-btn-next.gif',
			        imageBtnClose:'{$hys['dir']}/res/imgs/lightbox-btn-close.gif',
			        imageBlank:   '{$hys['dir']}/res/imgs/lightbox-blank.gif'
				});		
			})
		}
	});
	</script>
	";
	}
}

	
function hys_enqueue_scripts() {
    global $hys;
    //jQuery
	if (@$hys['settings']['jquery'] == 1)
	    wp_enqueue_script( 'jquery' , $hys['dir'].'/res/js/jquery.1.7.1.js'); 
    //heyyou javascript
    wp_register_script( 'heyyou_js', "{$hys['dir']}/res/js/heyyou.js");
    wp_enqueue_script( 'heyyou_js' );
    //motools
    if(@$hys['settings']['mootools'] == 1) {
	    wp_register_script( 'mootools', "{$hys['dir']}/res/js/mootools-1.2.4.js");
	    wp_enqueue_script( 'mootools' );
    }    
	//include jQuery plugins.. from heyyou settings
	$jquery_plugins = array(
		'jquery_opacityrollovers' 	=> 'jquery.opacityrollover.js',
		'jquery_cycle' 				=> 'jquery.cycle.js',
		'jquery_fx' 				=> 'jquery.effects.js',
		'jquery_color' 				=> 'jquery.color.js',
	);
	//include jquery lightbox..
	if (@$hys['settings']['jquery_lightbox'] == 1) {
	    wp_register_script( 'jquery_lightbox', "{$hys['dir']}/res/js/jquery.lightbox.js");
	    wp_enqueue_script( 'jquery_lightbox' );
	    wp_register_style( 'jquery_lightbox', "{$hys['dir']}/res/css/jquery.lightbox.css");
	    wp_enqueue_style('jquery_lightbox');
	}
    //javascript header
	if (isset($hys['settings']['header_js']) && !empty($hys['settings']['header_js'])) {
	    wp_register_script( 'header_js', get_bloginfo('stylesheet_directory')."/{$hys['settings']['header_js']}");
	    wp_enqueue_script( 'header_js' );
	}
	//include jQuery plugins.. from heyyou settings
	$jquery_plugins = array(
		'jquery_opacityrollovers' 	=> 'jquery.opacityrollover.js',
		'jquery_cycle' 				=> 'jquery.cycle.js',
		'jquery_fx' 				=> 'jquery.effects.js',
		'jquery_color' 				=> 'jquery.color.js',
	);
	foreach ($jquery_plugins as $kjq => $vjq) {
		if (@$hys['settings'][$kjq] == 1) {
			wp_register_script( $kjq, "{$hys['dir']}/res/js/{$vjq}");
			wp_enqueue_script( $kjq );
		}
	}
	//lightbox
	if (@$hys['settings']['lightbox'] == 1 && (@$hys['hys_page_config']['disable_lightbox'] != 1)) {
	    wp_register_script( 'prototype', str_replace('/heyyou','/hylb',$hys['dir'])."/js/prototype.js");
	    wp_enqueue_script( 'prototype' );
	    wp_register_script( 'scriptaculous', str_replace('/heyyou','/hylb',$hys['dir'])."/js/scriptaculous.js?load=effects,builder");
	    wp_enqueue_script( 'scriptaculous' );
	    wp_register_script( 'lightbox', str_replace('/heyyou','/hylb',$hys['dir'])."/js/lightbox.js");
	    wp_enqueue_script( 'lightbox' );
	    wp_register_style( 'lightbox', str_replace('/heyyou','/hylb',$hys['dir'])."/css/lightbox.css" );
	    wp_enqueue_style('lightbox');
	}
	//css time..
    wp_register_style( 'hys_style', get_bloginfo('wpurl').'/wp-content/plugins/heyyou/res/css/hys_style.css' );
    wp_enqueue_style('hys_style');
}    



	
/*-------------------------------------------------------------
 Name:      hys_footer

 Purpose:   add some scripts and such
-------------------------------------------------------------*/
function hys_footer() {
	global $hys;
	echo "
	<!--[if lt IE 9]><script src='http://ie7-js.googlecode.com/svn/version/2.1(beta4)/IE9.js'></script><![endif]-->\n";
	if (@$hys['settings']['lightbox'] == 1 && (@$hys['hys_page_config']['disable_lightbox'] != 1)) {
		if (@$hys['settings']['lightboxcustom'] != 1) {
		echo "
	<script type='text/javascript'>
		<!--
		LightboxOptions = Object.extend({
		    fileLoadingImage:        '".str_replace('/heyyou','/hylb',$hys['dir'])."/images/loading.gif',     
		    fileBottomNavCloseImage: '".str_replace('/heyyou','/hylb',$hys['dir'])."/images/closelabel.gif',
		    overlayOpacity: 0.8, animate: true, resizeSpeed: 8, borderSize: 10,					
			labelImage: 'Image', labelOf: 'of'
		}, window.LightboxOptions || {});
		//-->
	</script>\n";
		}
	}
}


/*-------------------------------------------------------------
 Name:      hys_social

 Purpose:   string of social buttons based on either pg config
 			or site-wide config and use in auto or with shrtcd
-------------------------------------------------------------*/
function hys_social_shrtcd(){
 return hys_social(1);
}
	
function hys_social($using_shortcode = 0) {
	global $hys;
	$facebook 	= (@$hys['hys_page_config']['facebooklike'] == 1 || ($using_shortcode == 1 && @$hys['settings']['show_opt_fb'] == 1)) ? 
					"<div class='social_btn hys_facebook'><a name='fb_share' type='box_count' href='http://www.facebook.com/sharer.php'>Share</a>".
					"<script src='http://static.ak.fbcdn.net/connect.php/js/FB.Share' type='text/javascript'></script></div>" : '';
	$twitter 	= (@$hys['hys_page_config']['twitter'] == 1 || ($using_shortcode == 1 && @$hys['settings']['show_opt_tw'] == 1)) ? 
					"<div class='social_btn hys_twitter'><a href='http://twitter.com/share' class='twitter-share-button' data-count='horizontal'>Tweet</a>".
					"<script type='text/javascript' src='http://platform.twitter.com/widgets.js'></script></div>" : '';
	$google 	= (@$hys['hys_page_config']['googleplus'] == 1 || ($using_shortcode == 1 && @$hys['settings']['show_opt_gp'] == 1)) ? 
					"<div class='social_btn hys_googleplus'><g:plusone size=\"medium\"></g:plusone>
					<script type=\"text/javascript\">
					  (function() {
					    var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
					    po.src = 'https://apis.google.com/js/plusone.js';
					    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
					  })();
					</script></div>" : '';
	return "<div class='hys_social'>".$facebook.$twitter.$google."</div>";
}

/*-------------------------------------------------------------
 Name:      hys_content

 Purpose:   !important. adds "hys_{$feature}_display() 
 			to end of the_content() hook. also enables
 			more/less to the_content() 
-------------------------------------------------------------*/
	function hys_content($thecontent) {
		global $post, $hys;
		
		//run filter
		$thecontent 		= hys_output_filter($thecontent);
		$return 			= '';
		$social 			= hys_social();
		$page_blurb_content = hys_moreless($thecontent, "-page".get_the_ID());
		
		if (isset($hys['hys_page_config']) && is_array($hys['hys_page_config']))
			$hys['hys_page_config']['hideoutput'] = (isset($hys['hys_page_config']['hideoutput'])) ? $hys['hys_page_config']['hideoutput'] : 0;
		
		//if we're not hidding the output, get hys_post output
		if ((@$hys['hys_page_config']['hideoutput'] != 1))
			$return .= hys_output();
		
		//add on social tools if applic
		$return .= $social;
		
		// page photo gallery
		if (
			@$hys['hys_page_config']['show_pg_img'] == 1 && 				//if page photo gallery is enabled
			@$hys['hys_page_config']['show_pg_img_autoplaceoff'] != 1 &&	//auto-placement disabled for page
			@$hys['settings']['attach_disable-hys_photo_gallery'] != 1 		//auto-placement disabled for site
			) {
			$return .= hys_photo_gallery(get_the_ID());
		}
		//if it's a single page, don't show page blurb, just heyyou
		if (isset($_GET['hypg']))
			return hys_output().$social;
		//handle password
		if (empty($post->post_password)) {
			return $page_blurb_content.$return; //no pass, return everything
		} else {
			if (@$_COOKIE['wp-postpass_' . COOKIEHASH] == $post->post_password)
				return $return; // no "$page_blurb_content" cause it's a password req notice
			else
				return $thecontent; // just the pass req notice
		}
	}
	
	
/*-------------------------------------------------------------
 Name:      hys_output_filter

 Purpose:   filter text content for heyyou
 Receive:   $content
 Return:	$content
-------------------------------------------------------------*/
	function hys_output_filter($content) {
		//change "&#8230;" dotted lines back to expected "..." lines
		$content = str_replace('&#8230;','...',$content);
		//
		return $content;
	}
	
/*-------------------------------------------------------------
 Name:      hys_start

 Purpose:   adds under construction banner if on and function 
-------------------------------------------------------------*/
	add_action('thematic_before','hys_under_con'); // themeatic
	function hys_after_body() 	{ hys_start(); } //new
	function hys_under_con() 	{ hys_start(); } //decip
	function hys_start()  {
		global $hys, $post;
		?>
		
		<?php if (@$hys['settings']['undercon'] == 1) :
			$underconmsg = (isset($hys['settings']['undercontit'])) ? $hys['settings']['undercontit'] : "UNDER CONSTRUCTION";
			?>
				<div class='hys_alertmsg_holder' onclick="showhide('hys_undercon_msg');"> 
					<div class='hys_altertmsg_toggel'> 
						<div id='morelink<?= $post->ID ?>' class='hys_readmore hys_fake_link underconclickmsg'> <?= $underconmsg ?> </div>
					</div> 
					<div id='hys_undercon_msg' class='hys_moreless hys_undercon_msg' style='  display:none; <?php 
							echo (@$hys['settings']['undercon_reveal'] == 1 || (@$hys['settings']['undercon_sess'] == 1 || @$hys['settings']['undercon_cook'] == 1)) 
																						? " display:block !important;" : ''; ?>'>
						<p class='hys_undercon_msgtxt'><?php 
							echo nl2br($hys['settings']['underconmsg']);
						?></p>
						
						<?php
						if (isset($hys['settings']['undercon_sess']) && $hys['settings']['undercon_sess'] ==1) {
							echo "<pre style='text-align:left;'>";
							print_r($_SESSION);
							echo "</pre>";
						}
						if (isset($hys['settings']['undercon_cook']) && $hys['settings']['undercon_cook'] ==1) {
							echo "<pre style='text-align:left;'>";
							print_r($_COOKIE);
							echo "</pre>";
						}
						?>
					</div>
				</div>	
		<?php endif;
		
	}
/*-------------------------------------------------------------
 Name:      hys_attach_attachments

 Purpose:   create attachment gallery for HEYYOU posts
 Receive:   id of gallery parent
 Return:	array(string of attach gallerized, lightbox links
 			for hidden gallery, & first link to hidden gallery
 			lightbox)
-------------------------------------------------------------*/
function  hys_attach_attachments($heyyou_post_id) {
	global $hys;
	
	$attachments 			= '';
	$lightbox_links 		= '';
	$lightbox_links_first 	= '';
	
	//add attachments
	if (function_exists('attachments_get_attachments')) {
	
		$post_images 	= attachments_get_attachments($heyyou_post_id); 
		$total_images 	= count($post_images);
		if ($total_images > 0 && isset($post_images[0]['id'])) {
			$attachments .= "
			
			<!-- HEYYOU POST ATTACHMENTS -->
				<ul class='hys_attach photo_gallery hys_gallery hys_attach_attachments' id='gallery_{$heyyou_post_id}'>\n";
			for ($i = 0; $i < $total_images; $i++) {
				if (isset($post_images[$i]) && get_post($post_images[$i]['id'])) {
					$req_size = (!empty($hys['hys_page_config']['img_size'])) ? $hys['hys_page_config']['img_size'] : 'large';

					$size_full = ($hys['mobile'] == 1) ? 'medium' : 'full';

					$full_download	= wp_get_attachment_image_src( $post_images[$i]['id'], $size_full, 1 );
					$lrg_download 	= wp_get_attachment_image_src( $post_images[$i]['id'], $req_size, 1 );
					$low_download 	= wp_get_attachment_image_src( $post_images[$i]['id'], 'medium', 1 );
					
					$lbtitle = (!empty($post_images[$i]['caption'])) ? str_replace("'",'&rsquo;',$post_images[$i]['caption']) : '';
										
					$lbtitle  = str_replace("'",'&rsquo;',$lbtitle); //remove "<br />" from titles for alt=''s
					if (@$hys['settings']['attach_use_titles'] == 1) {
						$lbtitle = $post_images[$i]['title']; //title=title
						$lbtitle .= (!empty($post_images[$i]['caption'])) ? " --- {$post_images[$i]['caption']}" : ''; //add caption
						$lbtitle = (empty($post_images[$i]['title'])) ? str_replace(' --- ','',$lbtitle) : $lbtitle; //remove "---" if ONLY caption
					}				
					$attachments .= "
					<li>
						<a class='attach_image' href='{$lrg_download[0]}' rel='lightbox[gallery{$heyyou_post_id}]' title=\"{$lbtitle}\">".
							wp_get_attachment_image( $post_images[$i]['id'], 'thumbnail', 1 ).
						"</a>
					  \n";
					
					if (@$hys['hys_page_config']['show_pg_img_printlabel'] == 1) {
						if (@$hys['settings']['attach_use_titles'] == 1) {
						$attachments .= "
					  		<div class='attach_title'>{$post_images[$i]['title']}</div>
						\n";
						}
						$attachments .= "
					  <div class='attach_caption'>{$post_images[$i]['caption']}</div>
						\n";
					}
					if (@$hys['hys_page_config']['downloadattach'] == 1) {
						$hi = (isset($hys['settings']['text_hi_res']) && !empty($hys['settings']['text_hi_res'])) ? $hys['settings']['text_hi_res'] : 'Hi Res';
						$low = (isset($hys['settings']['text_low_res']) && !empty($hys['settings']['text_low_res'])) ? $hys['settings']['text_low_res'] : 'Low Res';
						$attachments .= "
					  <div class='attach_download'>
						<span class='attach_download_title'>Download:</span>
						<a href='".hys_return_url()."?download={$full_download[0]}' target='_Blank' class='attach_download_hi'>{$hi}</a>
						<span class='attach_download_seperator'> | </span>
						<a href='".hys_return_url()."?download={$low_download[0]}' target='_Blank' class='attach_download_low'>{$low}</a>
					  </div>\n";
					}
					$attachments .= "
					</li>";
				}
				if (isset($lrg_download) && is_array($lrg_download) && isset($lrg_download[0])) {
					//Add "%lightbox_gallery%" Lightbox Link
					if (empty($lightbox_links_first)) {
						$lb_text = @ trim($hys['settings']['lightbox_gallery_link']);
						$lb_text = (empty($lb_text)) ? 'View Photo Gallery' : $lb_text;
						$lightbox_links_first = "<a href='{$lrg_download[0]}' rel='lightbox[hidden_attach{$heyyou_post_id}]' title='{$lbtitle}'>{$lb_text}</a>";
					} else {
						$lightbox_links .= "<a href='{$lrg_download[0]}' rel='lightbox[hidden_attach{$heyyou_post_id}]' style='display:none;' title='{$lbtitle}'></a>";
					}
				}			
			}
			$attachments .= "
				</ul>
			<!-- END HEYYOU POST ATTACHMENTS -->
			";
		}
	}
	return array('attachments' => $attachments, 'lightboxlinks' => $lightbox_links, 'lightboxlinksfirst' => $lightbox_links_first);
}


/*-------------------------------------------------------------
 Name:      mobile_link_f

 Purpose:   adds view full/mobile based on curent mobile status
 Receive:   - none -
 Return:	boolen
-------------------------------------------------------------*/	
	function hys_mobile_link($phrase = '') {
		global $hys;
		if (empty($phrase))
			$phrase = array('View Full Sized Site','View Mobile Site');		
		if ($hys['mobile'] == 1)
			return "<a href='".get_bloginfo('url')."/?mobile'>{$phrase[0]}</a>";
		else
			return "<a href='".get_bloginfo('url')."/?mobile'>{$phrase[1]}</a>";
	}
	function mobile_link_f($phrase = '') {
		return hys_mobile_link($phrase); // deciprecated
	}
	

/*-------------------------------------------------------------
 Name:      hys_tweets_shortcode

 Purpose:   return list of twitter posts from RSS feed
 Receive:   [hys_tweets id='' count='' refresh_rate='']
 Return:	string with tweets wrap in tags
-------------------------------------------------------------*/
	function hys_tweets_shortcode( $atts ) {
		extract( shortcode_atts( array(
			'id' 			=> 264304328,
			'count' 		=> 1,
			'refresh_rate' 	=> 14400
		), $atts ) );
		return hys_twitter_feed($id, $count, $refresh_rate);
	}

	
/*-------------------------------------------------------------
 Name:      hys_twitter_feed

 Purpose:   return list of twitter posts from RSS feed
 Receive:   EVERY x seconds we're going to refresh the wp 
 			option containing the latest TWEETS, when called.
 Return:	string with tweets
-------------------------------------------------------------*/
	function hys_twitter_feed( $id = '264304328', $count = 1 , $refresh_rate = 14400 ) {
		if ( false === ( $get_tweets = get_transient( 'hys_twitter_tweets' ) ) ) {
			$get_tweets = get_tweets($id,$count);
		    set_transient( 'hys_twitter_tweets', $get_tweets , $refresh_rate );
		}
		return $get_tweets;
	}
		
	function get_tweets($id = '264304328', $count = 1) {	
		$feed_url 					= "http://twitter.com/statuses/user_timeline/{$id}.rss";			  
		$ch 						= @curl_init($feed_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		$data 						= @curl_exec($ch);
		curl_close ($ch);
		$xml 						= @ new SimpleXmlElement($data, LIBXML_NOCDATA);
		if (is_object($xml)) {
			$xml 					= @$xml->channel;
			$xml_out 				= array();
			$xml_out['username'] 	= str_replace('Twitter / ','',$xml->title);
			$i 						= 0;
			foreach ($xml->item as $k => $atweet) {
				$title 				= (string)$xml->item[$i]->title;
				$date 				= (string)$xml->item[$i]->pubDate;
				$link 				= (string)$xml->item[$i]->link;
				$xml_out['tweets'][]= array('tweet' => $title, 'date' => $date, 'link' => $link);
				$i++;
			}
			$_SESSION['twitter_xml_out'] = serialize($xml_out);		
			$xml = $xml_out;
		} else {
			return false; // abort abort abort
		}
		$i = 1;
		$tweets = "<ul class='hys_tweets'>";
		$the_tweets = '';
		if (is_array($xml['tweets'])) {
			foreach ($xml['tweets'] as $atweet) {
				if ($i <= $count) {
					$the_tweets .= "
						<li class='hys_tweet_{$i}'>
							<div class='hys_tweet_title'>
								<a href='{$atweet['link']}'>".
								trim(str_replace($xml['username'].": ",'',$atweet['tweet'])).
							"	</a>
							 </div><!--/hys_tweet_title-->
							<div class='hys_tweet_date'>
								".human_time_diff(strtotime($atweet['date']))." ago.
							</div><!--/hys_tweet_date-->
						</li><!--/hys_tweet_{$i}-->"; 
				}
				$i++;
			}
		}
		$tweets .= $the_tweets."</ul>";
		return (empty($the_tweets)) ? false : $tweets;
	} 

/*-------------------------------------------------------------
 Name:      hys_output

 Purpose:   Output of config and data onto page
-------------------------------------------------------------*/
	function hys_output($heyyou_posts = '', $parent = '') {
		global $hys, $wp, $anchors, $anchors_count;
		
		$return = '';
		
		//get the parent id
		$parent = (!empty($parent)) ? $parent : get_the_ID();
		
		//if the num per page is undefined, make it max
		$perpg 	= @(!empty($hys['hys_page_config']['perpage'])) ? $hys['hys_page_config']['perpage'] : '99999';
		$pg 	= (!empty($_GET['pg'])) ? intval($_GET['pg']) : 1;
		$ct 	= (!empty($_GET['ct'])) ? intval($_GET['ct']) : 'uncategorized';
		$numpgs = 1;
		$anchors_count = 1;
				
		// get all heyyou posts for this page		
		$heyyou_posts = (empty($heyyou_posts) || !is_array($heyyou_posts)) ? get_heyyou() : $heyyou_posts;
		
		//loop through cats+posts for this page, get hys_post from get_posts() array
		$hysi = 0;
				
		foreach ($heyyou_posts as $heyyou_category_id => $heyyou_posts_array) {
			
			//group categories
			if (!isset($_GET['hypg']) || (isset($_GET['hypg']) && in_array($_GET['hypg'],$heyyou_posts_array)))
				$return 	.= "<div class='hys_block_cat hys_block_cat_{$heyyou_category_id}' id='c{$heyyou_category_id}'>";
			
			//category
			$heyyou_cat  = get_term($heyyou_category_id,'hys_post_cats');
			$return		.= @$hys['hys_page_config']['before_cats_heyyou'];
			$return 	.= (!isset($_GET['hypg'])) ? hys_output_cat_title($heyyou_cat) : '';
			$num_psts 	 = count($heyyou_posts_array);
			$num_pgs 	 = ($num_psts > $perpg) ? ceil($num_psts/$perpg) : 1;
			$offset 	 = ($ct == $heyyou_category_id && $perpg > 0 && $pg != 0) ? (($pg-1)*$perpg) : 0;
			$pgmethod	 = @$hys['hys_page_config']['paginatemthod'];
			$unlimrang	 = ($pgmethod == 'moreless') ? true : false;
			$pc 		 = 1; //posts in category count
			$posts_shown = ($num_psts > $perpg) ? $perpg : $num_psts;
			$posts_offst = ($pg == 1) ? $posts_shown : $num_psts - $offset;
			$anchors 	.= "<p class='hys_anchors'>";
			$rang 		 = array(); // get the rang of visiable pages
			for ($r = $offset; $r != ($offset+$perpg); $r++) $rang[] = $r;
			
			//get post from array
			foreach ($heyyou_posts_array as $aheyyou_post => $heyyou_post) {
								
				//output the post (if not single page)
				if (in_array($hysi,$rang) || $unlimrang) {
					if ((isset($_GET['hypg']) && $aheyyou_post == $_GET['hypg']) || !isset($_GET['hypg'])) {
						## ########################################## ##
						## >>>>>>>> THE ACTUAL POST OUTPUT <<<<<<<<<< ##
						## ------------------------------------------ ##
						$return .= hys_output_post($heyyou_post,$pc,$heyyou_cat,$parent);
						## ------------------------------------------ ##
						## >>>>>>>> THE ACTUAL POST OUTPUT <<<<<<<<<< ##
						## ########################################## ##
						$return .= ($pc != $posts_offst) ? hys_lines('secondary_line') : "";
						$pc++;
					}
				}	
				if ($pgmethod == 'moreless' && $pc == ($perpg+1) && $num_psts > $perpg) {
					$return .= "
					<a class='hys_fake_link hys_readmore'  id='hys_morelink_{$heyyou_category_id}' 
					   onclick=\"showhide('hys_moreless_{$heyyou_category_id}');
					   			 showhide('hys_morelink_{$heyyou_category_id}')
					 	\" >{$hys['settings']['more']}</a>
					<div id='hys_moreless_{$heyyou_category_id}' class='hys_moreless hys_post_moreless' style='display:none;'>";
				}
				$hysi++;		
			}
			$anchors 	.= "</p><!--/hys_anchors-->";
			if ($pgmethod == 'moreless' && ($num_psts > $perpg)) {
				$return .= "
				<a class='hys_fake_link hys_readless' id='hys_lesslink_{$heyyou_category_id}'
				  onclick=\"showhide('hys_moreless_{$heyyou_category_id}');
				  			showhide('hys_morelink_{$heyyou_category_id}')\"
				  >{$hys['settings']['less']}</a>
				</div>";
			}
			//pagination for categories
			if ($num_pgs > 1 && $pgmethod != 'moreless') {
				// Determin the "Pages:" text
				$pagestit = @(empty($hys['settings']['pages'])) ? $hys['settings']['pages'] : "Pages: ";
				$pagestit = @(!empty($hys['hys_page_config']['pagination_text'])) ? $hys['hys_page_config']['pagination_text'] : $pagestit;
				$return .= "<div class='hys_pagination_line'>".hys_lines(2)."</div>				
				<div class='hys_pagination'><ul><li id='hys_pagination_title'>{$pagestit} </li>";
				for ($p = 1; $p != ($num_pgs+1); $p++) {
					$selct = (($pg == $p && $heyyou_category_id == $ct) || ($heyyou_category_id != $ct && $p == 1)) ? " class='active'": '';
					$return .= "<li{$selct}><a href='?pg={$p}&ct={$heyyou_category_id}#c{$heyyou_category_id}'>{$p}</a></li>";
					$return .= (($p != $num_pgs) 
							&& (isset($hys['settings']['pages_sep']) 
							&& !empty($hys['settings']['pages_sep']))) 
						? "<li id='hys_li_sep'>|</li>": '';
				}
				$return .= "</ul></div>";
			}
			
			if (!isset($_GET['hypg']) || (isset($_GET['hypg']) && in_array($_GET['hypg'],$heyyou_posts_array))) {
				$return .= "</div><!--/hys_block_cat hys_block_cat_{$heyyou_category_id}-->";
			}
			$return	.= @$hys['hys_page_config']['after_cats_heyyou'];
			$return .= @($hys['hys_page_config']['line_between_cats'] == 1) ? hys_lines(2) : '';
		}
		
		// before/after list
		$anchors 	= @($hys['hys_page_config']['anchors'] == 1 ) ? "<p class='hys_anchor_navigation'>".$anchors."</p>" : '';
		$list_title = @(!empty($hys['hys_page_config']['title'])) ? "<h1>{$hys['hys_page_config']['title']}</h1>": '';
		$line_befor = @($hys['hys_page_config']['line_before_list'] == 1) ? hys_lines(1) : '';
		$line_after = @($hys['hys_page_config']['line_after_list'] == 1) ? hys_lines(1) : '';
		
		//if there's no posts to be returned..
		if (empty($return)) {
			$return .= wpautop($hys['hys_page_config']['noposts_output_format']);
		}
		$noposts = (empty($return)) ? " hys_noposts" : '';
		
		// RETURN LIST
		return 	$anchors.
				$list_title. 										// << heyyou title
				$line_befor.
				@$hys['hys_page_config']['before_heyyou'].
				"<div class='hys_output hys_output_{$parent}{$noposts}'>".
					$return.										// << heyyou output, list of heyyou posts
				"</div>".
				@$hys['hys_page_config']['after_heyyou'].
				$line_after;
	}
	
	
	
/*-------------------------------------------------------------
 Name:      hys_photo_gallery

 Purpose:   Gallery attachment builder for PAGE posts
 Receive:   id of gallery parent
 Return:	string of attachments
-------------------------------------------------------------*/
	function hys_photo_gallery($id = '') {
		return hys_attach_page_attachments($id);
	}

/*-------------------------------------------------------------
 Name:      hys_attach_page_attachments

 Purpose:   create attachment gallery for PAGES
 Receive:   id of gallery parent
 Return:	string of attachments
-------------------------------------------------------------*/
	function hys_attach_page_attachments($id = '') {
		global $post,$hys;
		
		if (!function_exists('attachments_get_attachments')) return;
		
		$id 			= (empty($id)) ? get_the_ID() : intval($id);		
		$size 			= array(get_option('thumbnail_size_w'),get_option('thumbnail_size_h'));
		$attachments 	= attachments_get_attachments($id);
		$total_att 		= count($attachments);
		$return 		= '';
		
		if ($total_att > 0 && isset($attachments[0]['id'])) {
		    $return .= '<ul class="hys_attach photo_gallery hys_gallery hys_attach_page_attachments" id="gallery_'.$id.'">';
		    for ($i=0; $i < $total_att; $i++) {
				// remove single quote to not break links
		    	$attachments[$i]['title']      	 = str_replace(array("'"),'&rsquo;',$attachments[$i]['title']);
		    	$attachments[$i]['caption']      = str_replace(array("'"),'&rsquo;',$attachments[$i]['caption']);
		    	$size_full = ($hys['mobile'] == 1) ? 'medium' : 'large';
		    	//get image urls
		    	$thumbnail 	= wp_get_attachment_image_src($attachments[$i]['id'], 'thumbnail');
				$full 		= wp_get_attachment_image_src($attachments[$i]['id'], $size_full);
				//construct the title(s)
				$title = $lbtitle =(!empty($attachments[$i]['caption'])) ? $attachments[$i]['caption'] : '';									
				//if we're printing the labeling
				$txt_title  = str_replace("'",'&rsquo;',strip_tags($title)); //remove "<br />" from titles for alt=''s
				if (@$hys['settings']['attach_use_titles'] == 1) {
					$lbtitle = $attachments[$i]['title']; //title=title
					$lbtitle .= (!empty($attachments[$i]['caption'])) ? " --- {$attachments[$i]['caption']}" : ''; //add caption
					$lbtitle = (empty($attachments[$i]['title'])) ? str_replace(' --- ','',$lbtitle) : $lbtitle; //remove "---" if ONLY caption
				}				
				//the thumbnail
				$return .= "\n<li>
				<a rel='lightbox[{$post->ID}]' title=\"{$lbtitle}\" href='{$full[0]}'>
					<img src='{$thumbnail[0]}' alt='{$txt_title}' style='width:{$size[0]}px;height:{$size[0]}px;' />
				</a>";
				$opttitle = (@$hys['hys_page_config']['show_pg_img_printlabel'] == 1 && !empty($attachments[$i]['title'])) ? 
					"<span class='hys_page_gallery_title attach_title'>{$attachments[$i]['title']}</span> " : '';
				$optcaption = (@$hys['hys_page_config']['show_pg_img_printlabel'] == 1 && !empty($attachments[$i]['caption'])) ? 
					"<span class='hys_page_gallery_caption attach_caption'>{$attachments[$i]['caption']}</span> " : '';
				if (@$hys['hys_page_config']['show_pg_img_printlabel'] == 1) {
					$return .= "<span class='hys_page_gallery_label hys_page_gallery_label_{$attachments[$i]['id']}'>
									{$opttitle}
									{$optcaption}
								</span>";				
				}
				$return .= "</li>\n\n";
		    }
		    $return .= '</ul>';
		}
		return $return;
	}
	
	
/*-------------------------------------------------------------
 Name:      hys_output_cat_title

 Purpose:   format category title for main output
 Receive:   cat, get_term() object
 Return:	string, formated category title from heyyou config
 			category HTML output format
-------------------------------------------------------------*/
	function hys_output_cat_title($cat,$post_id = '') {
		global $hys, $anchors;
		$post_id = (empty($post_id)) ? get_the_ID() : intval($post_id);
		if (is_object($cat)) {
			//get category meta: title / blurb / html format (older heyyou's)
			$cat_meta = @unserialize($cat->description); 
			// if the descrition is serialized (older heyyou's)
			if ($cat_meta == true) {
				$descript = $cat_meta['blurb'];
				$cat_override_format = stripslashes($cat_meta['format']);
			} else {
				$descript 	= $cat->description;
				$cat_format =  get_post_meta($cat->term_id, 'cat_override');	
				$cat_override_format	= (isset($cat_format[0])) ? $cat_format[0] : array();
			}
			$anchors .= "<h6 class='hys_anchor_cat'>{$cat->name}</h6>";
			//get cat format for post
			$pmeta 	= get_post_custom($post_id);
			$hys_page_config = @($pmeta['hys_page_config'])  ? unserialize($pmeta['hys_page_config'][0])  : 0;	
			$format = (isset($hys_page_config['cat_format']) && !empty($hys_page_config['cat_format'])) ? $hys_page_config['cat_format'] : "<h3>%title%</h3>";
			return str_replace(
				array(
					'%title%',
					'%name%',
					'%cat_title%',
					'%cat%',
					'%cat_name%',
					'%cat_blurb%',
					'%blurb%',
					'%description%',
					'%descript%'
				),
				array(
					apply_filters('hys_filter_cat_title',$cat->name),
					apply_filters('hys_filter_cat_name',$cat->name),
					apply_filters('hys_filter_cat_meta_title',$cat_meta['title']),
					apply_filters('hys_filter_cat_meta_title',$cat_meta['title']),
					apply_filters('hys_filter_cat_meta_title',$cat_meta['title']),
					apply_filters('hys_filter_cat_meta_descript',$descript),
					apply_filters('hys_filter_cat_meta_descript',$descript),
					apply_filters('hys_filter_cat_meta_descript',$descript),
					apply_filters('hys_filter_cat_meta_descript',$descript)
				),
				$format
			);
		}
	}

/*-------------------------------------------------------------
 Name:      hys_output_post

 Purpose:   format post for main output
 Receive:   $post, object
 Return:	string, formated post from options in hys config
-------------------------------------------------------------*/
function hys_output_post($hyspost, $i, $cat, $parent = '') {
	global $hys, $anchors, $anchors_count;
	
	$hyspost 			= (is_integer($hyspost)) ? get_post($hyspost) : $hyspost;
	$parent 			= (empty($parent)) ? get_the_ID() : intval($parent);
	// meta	
	$meta 				=  get_post_meta($hyspost->ID, 'meta');	
	$meta 				= (isset($meta[0])) ? $meta[0] : array();
	//get custom heyyou incase we're calling from a different page
	$hysmeta 		 	= get_post_custom($parent);
	$hys_page_config 	= @($hysmeta['hys_page_config'])  ? unserialize($hysmeta['hys_page_config'][0])  : 0;
	// attachments
	$get_atth 			= hys_attach_attachments($hyspost->ID);
	$lb_links   		= $get_atth['lightboxlinks'];
	$attchmts 			= $get_atth['attachments'];
	$lb_first 			= $get_atth['lightboxlinksfirst'];
	// content
	$post_content 		= hys_moreless_(do_shortcode($hyspost->post_content));
	
	// Single hypg: alter content
	if (!isset($_GET['hypg']) && @$hys_page_config['single_altr_morelesslink'] == 1) {
		$mre = "<!--more-->";
		$split_content 	= (strpos($hyspost->post_content,$mre)) 
							? explode($mre,$hyspost->post_content) : nostyle($hyspost->post_content, $hys['settings']['moreless'],'...');
		$first_chunk 	= (is_array($split_content) && isset($split_content[0])) ? $split_content[0] : $split_content;
		$post_content 	= wpautop($first_chunk)."
							<a href='?hypg={$hyspost->ID}' class='hys_fake_link hys_readmore'>{$hys['settings']['more']}</a>\n\n";
	}
	if (isset($_GET['hypg']))
		$post_content 	= (@$hys_page_config['single_moreless'] == 1) ? hys_moreless_($hyspost->post_content) : wpautop($hyspost->post_content);
	
	// date
	$the_date = date(get_option('date_format'), strtotime($hyspost->post_date));
	
	// for "Add anchor navigation" option
	$post_title = (@$hys_page_config['numanchors'] == 1) ? ($i).". ".$hyspost->post_title : $hyspost->post_title;
	$post_title = ($post_title == "&nbsp;" || $post_title == ' ') ? '' : $post_title;
	$anchors .= "<a href='#hys".($anchors_count)."'>".($i).". {$hyspost->post_title}</a><br />";
	
	// for custom "more/less" buttons
	$title_moreless = "<a class='hys_fake_link hys_readmore auto_hidecontent'  id='morelink{$hyspost->ID}' onclick=\"showhide('moreless{$hyspost->ID}'); ".
					  "showhide('morelink{$hyspost->ID}');\" >".$post_title."</a>";
	$self_more = "<a class='hys_fake_link hys_readmore'  id='self_morelink{$hyspost->ID}' ".
		"onclick=\"showhide('moreless{$hyspost->ID}'); showhideinlineblock('self_morelink{$hyspost->ID}'); ".
		"showhideinlineblock('self_lesslink{$hyspost->ID}') \">{$hys['settings']['more']}</a>";
	$self_less = "<a class='hys_fake_link hys_readmore hys_readless'  id='self_lesslink{$hyspost->ID}' onclick=\"showhide('moreless{$hyspost->ID}'); ".
		"showhideinlineblock('self_morelink{$hyspost->ID}'); showhideinlineblock('self_lesslink{$hyspost->ID}')\" ".
		"style='display:none;'>{$hys['settings']['less']}</a>";		
	$self_moreless = $self_more.$self_less;
	
	// for "Auto-Collaps/Hide Blurb (%blurb%)"
	if (@$hys_page_config['hidecontent'] == 1 && !isset($_GET['hypg'])) {
		if (@$hys_page_config['hidecontent_notitlelink'] != 1)
			$post_title = (!empty($hyspost->post_content)) ? $title_moreless."<br class='hys_moreless_title_br' />" : $post_title;
		$post_content = "<div id='moreless{$hyspost->ID}' class='hys_moreless' style='display:none'>".$post_content."</div>";
	}
	
	// link tokens
	$back_link  	= "<div class='hys_back'><a href='".get_permalink()."' class='hys_back_link'>";
	$back_link 	   .= (isset($hys['settings']['back']) && !empty($hys['settings']['back'])) ? $hys['settings']['back'] : "&lt; Back";
	$back_link 	   .= "</a></div><!--/hys_back-->";
	$single_link 	= "<a href='?hypg={$hyspost->ID}' class='hys_single_link'>{$hys['settings']['more']}</a>";
	$single_url 	= "?hypg={$hyspost->ID}";

	// this output
	$thisheyyou 	= get_heyyou();
	
	// NXT / PRV on single pages
	$prev_link  = '';
	$next_link = '';
	if (isset($_GET['hypg'])) {
		$siblings = array();
		//remove the cateogies, only thing we want is id's
		foreach ($thisheyyou as $cid => $category_posts) {
			foreach ($category_posts as $aheyyoupostid => $aheyyoupost)
				$siblings[] = $aheyyoupostid;
		}
		foreach ($siblings as $thekey => $theid) {
			if ($theid == $_GET['hypg']) {
				$prev_link  = (isset($siblings[$thekey-1])) ? '<a href="?hypg='.($siblings[$thekey-1]).'">Prev</a>' : '';
				$next_link 	= (isset($siblings[$thekey+1])) ? '<a href="?hypg='.($siblings[$thekey+1]).'">Next</a>' : '';
			}	
		}
	}
	
	
	//avaliable tokens and their replacements
	$replace_this = array(
		'%id%', '%ID%',
		'%title%',
		'%title:moreless%',
		'%date%',
		'%blurb%',
		'%num%',
		'%media%','%media:lightbox%',
		'%line%','%line2%',
		'%back%',
		'%prev%','%next%',
		'%attach%','%attachments%',
		'%lightbox_gallery%',
		'%moreless%','%moreless:more%', '%moreless:less%',
		'%view_single_post%','%view_single%',
		'%single_link%','%view_single_link%',
		'%gallery%'
	);
	$with_this = array(
		$hyspost->ID, $hyspost->ID,
		apply_filters('hys_filter_title',$post_title),
		apply_filters('hys_filter_title_moreless',$title_moreless),
		apply_filters('hys_filter_date',$the_date),
		apply_filters('hys_filter_content',$post_content),
		apply_filters('hys_filter_num',$i),
		'<!-- [DEPRECIATED] -->',apply_filters('hys_filter_lb',$lb_first.$lb_links),
		apply_filters('hys_filter_line',hys_lines(1)), apply_filters('hys_filter_line2',hys_lines(2)),
		apply_filters('hys_filter_back',$back_link),
		apply_filters('hys_filter_prev',$prev_link), apply_filters('hys_filter_next',$next_link),
		apply_filters('hys_filter_attachments',$attchmts), apply_filters('hys_filter_attachments',$attchmts),
		apply_filters('hys_filter_lb_first',$lb_first),
		apply_filters('hys_filter_self_moreless',$self_moreless),apply_filters('hys_filter_self_more',$self_more),apply_filters('hys_filter_self_less',$self_less),
		apply_filters('hys_filter_single_link',$single_link),apply_filters('hys_filter_single_link',$single_link),
		apply_filters('hys_filter_single_url',$single_url),apply_filters('hys_filter_single_url',$single_url),
		apply_filters('hys_filter_gallery',hys_photo_gallery($hyspost->ID))
	);
	
	
	
	// output format
	$format = (isset($_GET['hypg'])) ? 'singleformat': 'format';
	if ($format == 'singleformat' && (!isset($hys_page_config[$format]) || empty($hys_page_config[$format])))
		$output_format = '%back%<br />'.$hys_page_config['format'];
	else
		$output_format = $hys_page_config[$format];
	if (is_object($cat)) {
	
		//get category meta: title / blurb / html format (older heyyou's)
		$cat_meta = @unserialize($cat->description); 
				
		// if the descrition is serialized (older heyyou's)
		if ($cat_meta == true && (isset($cat_meta['format']) && !empty($cat_meta['format']))) {
			$post_output_format = str_replace('&#37;','%', stripslashes(htmlspecialchars_decode($cat_meta['format'])));
			if (!empty($post_output_format))
			$output_format = $post_output_format;
		} else {
			$descript 	= $cat->description;
			$cat_format =  get_post_meta($cat->term_id, 'cat_override');	
			if (!empty($cat_format[0]) && $cat_format[0] != 'Array')
			$output_format	= $cat_format[0];
		}

	}
	
	// meta tags 
	if (isset($hys_page_config['meta'])) {
	
		//this defines the unknown meta values, if left empty
		$metafields_default = array();
		foreach($hys_page_config['meta'] as $k => $mtaname) {
			$token = hys_url_friendly($mtaname);
			$metafields_default[] = "%dflt_val_of_".$token."%<br />";
		}
	
		$metanum = 0;
		$meta =  get_post_meta($hyspost->ID, 'meta');	
		$meta = (isset($meta[0])) ? $meta[0] : array();
	
		foreach($hys_page_config['meta'] as $k => $mtaname) {
			$token = hys_url_friendly($mtaname);
			if (!empty($token)) {
				$meta[$metanum] = (isset($meta[$metanum])) ? $meta[$metanum] : '';
				$metaval 		= (isset($meta[$token])) ? $meta[$token] : $meta[$metanum];
				//run filters on (blurb)
				if (
					(strpos(strtolower($mtaname),'(blurb)') || @$hys_page_config['meta_type'][$k] == 'blurb')
					OR
					(strpos(strtolower($mtaname),'(textarea)') || @$hys_page_config['meta_type'][$k] == 'textarea')
					OR
					(strpos(strtolower($mtaname),'(code)') || @$hys_page_config['meta_type'][$k] == 'code')
				) 
					$metaval = nl2br($metaval);
				//get URL of (media) drop-down selected file
				if (strpos(strtolower($mtaname),'(media)') || @$hys_page_config['meta_type'][$k] == 'media')  {
					
					if (strpos($mtaname,':thumb')) {  
						$metaval = wp_get_attachment_image_src( $metaval, 'thumbnail' );
						$metaval = $metaval[0];
					} else {
						$metaval = wp_get_attachment_url($metaval);
					}
				
				}
				$replace_this[] = "%".$token."%";
				$with_this[] 	= $metaval;
				$metanum++;
			}
			
			//SEE IF %if:defined:token% clause in in effect. if so, alter $output_format
			$ifdefined 			= "%if:defined:".$token."%";
			$ifdefined_esc 		= "\%if:defined:".$token."\%";
			$endifdefined 		= "%endif:defined:".$token."%";
			$endifdefined_esc 	= "\%endif:defined:".$token."\%";
			
			//if the output has if:defined arugments
			if (strpos(strtolower($output_format), $ifdefined)) {
			
				//isolate the code between the clause
				$isolate = substr_replace($output_format, '', 0, (strpos($output_format, $ifdefined)+strlen($ifdefined)));
				$isolate = substr_replace($isolate, '', strpos($isolate, $endifdefined), strlen($isolate));

				//find the token in the clause				
				foreach ($replace_this as $k => $atoken)
			
					//if this is the token, in the clause, and it's NOT DEFINED (in $with_this)
					if ($atoken == "%{$token}%" && (empty($with_this[$k]) || $with_this[$k] == 'http://'))
			
						//remove clause and content+token between
						$output_format = preg_replace("({$ifdefined_esc}(.+?){$endifdefined_esc})is", "", $output_format);
			
				//output, remove the %if:defined:...%
				$output_format = str_replace(array($ifdefined,$endifdefined),'',$output_format);
				
			}
		}						
	}
	
	// different than meta %if:defined%'s we have to see if it's used for %if:defined:attach%
	//if the output has if:defined arugments
	if (strpos(strtolower($output_format), '%if:defined:attach%')) {
		$ifdefined 			= "%if:defined:attach%";
		$ifdefined_esc 		= "\%if:defined:attach\%";
		$endifdefined 		= "%endif:defined:attach%";
		$endifdefined_esc 	= "\%endif:defined:attach\%";
		//isolate the code between the clause
		$isolate = substr_replace($output_format, '', 0, (strpos($output_format, $ifdefined)+strlen($ifdefined)));
		$isolate = substr_replace($isolate, '', strpos($isolate, $endifdefined), strlen($isolate));
		
		//see if this post even has attachments
		$attach_post_images 	= attachments_get_attachments($hyspost->ID); 
		$attach_total_images 	= count($attach_post_images);
		$has_attachs = ($attach_total_images > 0 && isset($attach_post_images[0]['id'])) ? true : false;
		
		//if this post doesn't have attachments, remove clause and content+token between
		if (!$has_attachs)
			$output_format = preg_replace("({$ifdefined_esc}(.+?){$endifdefined_esc})is", "", $output_format);

		//regardless: remove the %if:defined:...% arguments
		$output_format = str_replace(array($ifdefined,$endifdefined),'',$output_format);
	}
	
	//remove attachments from list if it's on single page or autoplacement is on
	$showsingleatt = @$hys_page_config['single_showattchinpage'];
	$attchmts = ($showsingleatt == 1 || @$hys_page_config['autoattach'] == 1) ? '' : $attchmts;
	$i++;	
	$anchors_count++;
	
	//do it!
	return  "\n<div id='".hys_url_friendly($hyspost->post_title)."-{$hyspost->ID}' class='hys_post_post hys-{$hyspost->ID} hys_post-{$parent}'>\n<div id='hys".($anchors_count-1)."'></div>\n".
					str_replace(
						$replace_this,
						$with_this,
						apply_filters('hys_filter_output_format',$output_format)
					)."</div>\n\n".apply_filters('hys_filter_lb_links',$lb_links).apply_filters('hys_filter_attachments',$attchmts);
}


/*-------------------------------------------------------------
 Name:      hys_shortcode

 Purpose:   [heyyou cat=""]
 Receive:   
 Return:	
-------------------------------------------------------------*/
function hys_shortcode( $atts ) {
	global $hys;
	extract( shortcode_atts( array(
		'cat' => ''
	), $atts ) );
	return hys_output(get_heyyou($cat));
}


/*====================================================================================================================
   // !Global Functions 
--------------------------------------------------------------------------------------------------------------------*/
/*-------------------------------------------------------------
 Name:      get_heyyou

 Purpose:   return heyyou posts in array for id
 Receive:   page_id: id of page: defaults current page id
 			category: if instead of entire array, only 1 cat
 Return:	$return, array of cats id with array of heyyou 
 			post ids
-------------------------------------------------------------*/
 	function get_heyyou($category = '',$page_id = '') {
 		global $post, $hys;
 		//if page isn't set, get current page
 		$page_id = (empty($page_id)) ? @$post->ID : intval($page_id);
		//get posts & categories to sort by
		$get_heyyou_posts_argg = array(
			'post_type' 		=> 'hys_post',
			'post_status'		=> 'publish,future',
			'post_parent'		=> $page_id,
			'orderby'			=> 'menu_order',
			'order'				=> 'ASC',
			'numberposts'		=> -1,
		);
		$get_heyyou_posts 		= get_posts($get_heyyou_posts_argg);		
		$get_taxonomies 		= get_taxonomies();
		$myterms 				= get_terms('hys_post_cats', 'orderby=count&hide_empty=0');			
		$return 				= array();
		if (isset($myterms)) { // if there are categories:
			//get the parent TERM (the feature_code, so hys_post -> hys_post-xxx)
			$parent_term_id = '';
			foreach ($myterms as $k => $cat)
				if ($cat->name == 'hys_post-'.$page_id)
					$parent_term_id = $cat->term_id;
			
			$using_categories = (isset($hys['hys_page_config']['include_cats']) && $hys['hys_page_config']['include_cats'] == 1) ? true : false;
			if ($using_categories) {
				//run though the cats and posts, build into an array
				$feature_post_arr = array();
				foreach ($myterms as $k => $cat) { //cycle through cats	
					if ($cat->parent == $parent_term_id) { // if the cat is in the parent (hys_post-xxx)
						foreach($get_heyyou_posts as $k => $f_post) {
							$custom_fields =  get_post_meta($f_post->ID, 'meta');	
							$custom_fields = (isset($custom_fields[0])) ? $custom_fields[0] : array();
							// if this post will show in our list, add it to array 
							if (@$custom_fields['hys_post_cat'] == $cat->term_id) {
								$return[$cat->term_id][$f_post->ID] = $f_post;
								unset($get_heyyou_posts[$k]);
							}
						}
					}
				}
			}
			//get "uncategorized" posts
			foreach($get_heyyou_posts as $k => $f_post) {
				$return['uncategorized'][$f_post->ID] = $f_post;
				//$return['uncategorized'][$f_post->ID] = $f_post; //@TODO: fix typo
			}
		}
		// if we're only returning a specific category
		if (!empty($category)) {
			$return_cat = array();
			if (is_integer($category)) {
				$return_cat_arr = @$return[$category];
			} else {
				$catterm = get_term_by('name', $category, 'hys_post_cats');
				$category = $catterm->term_id;
			}
			$return_cat[$category] = @$return[$category];
			return $return_cat;
		}
		return $return;
 	}

/*-------------------------------------------------------------
 Name:      hys_return_id

 Purpose:   finds and returns ID
 Receive:   - none -
 Return:	int()$id
-------------------------------------------------------------*/
	function hys_return_id() {
		global $post;
		//check the current post
		if (is_object($post) && isset($post->ID) && !empty($post->ID)) 
			return $post->ID;
		//if not check request
		if (isset($_GET['post']) && !empty($_GET['post'])) 
			return intval($_GET['post']);
		if (isset($_GET['page_id']) && !empty($_GET['page_id'])) 
			return intval($_GET['page_id']);
		
		//if not check full url
		if (!isset($_GET['post']) && !isset($_GET['page_id'])) {
			$id = url_to_postid(hys_return_url());
			if (!empty($id)) 
			return $id;
		}
		// if nothing else, it's the front page
		return intval(get_option('page_on_front'));
	}

/*-------------------------------------------------------------
 Name:      hys_current_user_role

 Purpose:   finds and returns role
 Receive:   - none -
 Return:	stringe
-------------------------------------------------------------*/
	function hys_current_user_role() {
		global $current_user, $wp_roles, $wpdb;
		get_currentuserinfo();
		$current_user = wp_get_current_user();
		$roles = $current_user->roles;
		$role = array_shift($roles);
		return trim(isset($wp_roles->role_names[$role]) 
				? translate_user_role($wp_roles->role_names[$role] ) : 'unknown');
	}

/*-------------------------------------------------------------
 Name:      hys_isset

 Purpose:   checks isset
 Receive:   $var
 Return:	boolean
-------------------------------------------------------------*/
	function hys_isset( & $var ) {
		return (isset($var) && !empty($var)) ? true : false;
	}

/*-------------------------------------------------------------
 Name:      chckchckbox

 Purpose:   returns CHECKED statment for input tag if = 1
 Receive:   $value
 Return:	- none -
-------------------------------------------------------------*/
	function chckchckbox($value) {
		//1=checked, 0=unchecked
		return ($value == 1) ? " checked='checked'" : "";
	}
	function chckselect($x,$y) {
		//1=checked, 0=unchecked
		return ($x==$y) ? " selected='selected'" : "";
	}

/*-------------------------------------------------------------
 Name:      nostyle

 Purpose:   chops at designated int
 Receive:   $Text, $int (cut off at), $cut_off_string (ie "...")
 Return:	$Text
-------------------------------------------------------------*/
	function nostyle($Text, $int = 999999, $cut_off_string = '..') {
		//Remoce all formatting
		$Text = trim($Text);
		$Text = str_replace("<br />", " ", $Text);
		$Text = strip_tags($Text);
		//truncated text if greater than 250
	   if (strlen($Text) > $int) {
	       preg_match('/(.{' . $int . '}.*?)\b/', $Text, $matches);
	       $Text = (isset($matches[1])) ? rtrim($matches[1]).$cut_off_string : $Text;
		}
		//Send back text
		return $Text;
		/* // @TODO: below may be a better cutter..
		$int++;
		if(strlen($Text)>$int) {
			$subex = substr($Text,0,$int-5);
			$exwords = explode(" ",$subex);
			$excut = -(strlen($exwords[count($exwords)-1]));
			if($excut<0) {
				return substr($subex,0,$excut).$cut_off_string;
			} else {
				return $subex;
			}
		} else {
			return $Text;
		} */
	}

/*-------------------------------------------------------------
 Name:      hys_get_next_in_order

 Purpose:   get next in order, 
 Receive:   $post_type
 Return:	(int)
-------------------------------------------------------------*/
	function hys_get_next_in_order($post_type) {
		global $wpdb;
		$next = $wpdb->get_row("SELECT menu_order FROM $wpdb->posts ORDER BY menu_order DESC LIMIT 1");
		return (isset($next->menu_order)) ? (($next->menu_order)+1) : 
		die('ERROR FINDING NEXT IN ORDER: SEND EMAIL WITH THIS ERROR: <code>#hys_get_next_in_order(false)</code>');
	}

/*-------------------------------------------------------------
 Name:      hys_showhide_post

 Purpose:   change post_type of post. 
 			toggel publish/draft w button
 Receive:   $id, $showhide
 Return:	- none -
-------------------------------------------------------------*/
   function hys_showhide_post($id, $showhide) {
   		global $wpdb;
   		// get the id to edit
   		$id = intval($id);
   		//make publish = 1, make draft = 0
   		$showhide = ($showhide == true || $showhide == 1) ? 'publish' : 'draft';
   		$wpdb->query("UPDATE $wpdb->posts SET post_status = '{$showhide}' WHERE ID = {$id}");
		return;
   }  
/*-------------------------------------------------------------
 Name:      hys_return_url

 Purpose:   returns full URL
 Receive:   - none -
 Return:	current (URL)
-------------------------------------------------------------*/
	function hys_return_url() {
		return (!isset($_SERVER['HTTPS']) || empty($_SERVER['HTTPS']) || strtolower($_SERVER['HTTPS']) == 'off') 
			? "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'] 
			: "https://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
	}

/*-------------------------------------------------------------
 Name:      hys_validate_email

 Purpose:   send true/false for email addys
 Receive:   $email, ie: email@domain.com
 Return:	true/false
-------------------------------------------------------------*/
	function hys_validate_email($email) {
	  if (!ereg("^[^@]{1,64}@[^@]{1,255}$", $email)) {
	    return false;
	  }
	  $email_array = explode("@", $email);
	  $local_array = explode(".", $email_array[0]);
	  for ($i = 0; $i < sizeof($local_array); $i++) {
	    if
		(!ereg("^(([A-Za-z0-9!#$%&'*+/=?^_`{|}~-][A-Za-z0-9!#$%&
		↪'*+/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$",
		$local_array[$i])) {
	      return false;
	    }
	  }
	  if (!ereg("^\[?[0-9\.]+\]?$", $email_array[1])) {
	    $domain_array = explode(".", $email_array[1]);
	    if (sizeof($domain_array) < 2) {
	        return false; // Not enough parts to domain
	    }
	    for ($i = 0; $i < sizeof($domain_array); $i++) {
	      if
			(!ereg("^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|
			↪([A-Za-z0-9]+))$",
			$domain_array[$i])) {
	        return false;
	      }
	    }
	  }
	  return true;
	}

/*-------------------------------------------------------------
 Name:      hys_toggle_boolean_int

 Purpose:   turn 1 to 0, or 0 to 1
 Receive:   $val (int)
 Return:	boolean (int)
-------------------------------------------------------------*/
	function hys_toggle_boolean_int($val) {
		$val = intval($val);
		if ($val == 1) return 0;
		if ($val == 0) return 1;
	}

/*-------------------------------------------------------------
 Name:      hys_lines

 Purpose:   call lines
 Receive:   $prmry_or_scndry (1= prim, 2= secnd)
 Return:	$hys['settings']['line'.$prmry_or_scndry]
-------------------------------------------------------------*/
	function hys_lines($prmry_or_scndry = 1) {
		global $hys;
		
		//if calling for the lines via the functino instead of the var, return the var
		if ($prmry_or_scndry == 1) return $hys['settings']['line1'];
		if ($prmry_or_scndry == 2) return $hys['settings']['line2'];
		
		//if usings hey you plugin feature options, output line where called
		if ($prmry_or_scndry == 'primary_line') {
			return (isset($hys['hys_page_config']['line_before_list']) && $hys['hys_page_config']['line_before_list'] == 1 && isset($hys['settings']['line1'])) 
				? $hys['settings']['line1'] : '';
		}
		if ($prmry_or_scndry == 'secondary_line') {
			return (isset($hys['hys_page_config']['line_between_list']) && $hys['hys_page_config']['line_between_list'] == 1 && isset($hys['settings']['line2'])) 
				? $hys['settings']['line2'] : '';
		}
	}

/*-------------------------------------------------------------
 Name:      hys_moreless

 Purpose:   chops string into two sections at pos. wraps
 			in HTML for read more/less.. links
 Receive:   $Text, $id (int),$pragraph_2_br(boolean)
 Return:	$Text
-------------------------------------------------------------*/
	function hys_moreless($origText,$id = false,$pragraph_2_br = false) {		
		global $hys;
		$Text = $origText;
		$mre = "<!--more-->";
		$Text = preg_replace("(<p><span id=\"more-(.+?)\"></span></p>)is", $mre, $Text);
		$Text = preg_replace("(<span id=\"more-(.+?)\"></span>)is", $mre, $Text);
		// find if there is <!--more--> in this blurb
		$is_more_less = strpos($Text, $mre);
		//paragraphing...
		$is_no_p = strpos($Text,'<p>');
		if (!$is_no_p) {
				//change parragraphs into line breaks
				$Text = preg_replace("(<p>)is", "", $Text);
				$Text = preg_replace("(<p >)is", "", $Text);
				$Text = preg_replace("(<p id=\"(.+?)\">)is", "", $Text);
				$Text = preg_replace("(<p id='(.+?)'>)is", "", $Text);
				$Text = preg_replace("(<p class='(.+?)'>)is", "", $Text);
				$Text = preg_replace("(<p class=\"(.+?)\">)is", "", $Text);
				$Text = preg_replace("(<p style='(.+?)'>)is", "", $Text);
				$Text = preg_replace("(<p style=\"(.+?)\">)is", "", $Text);
				$Text = preg_replace("(</p>)is", "<br /><br />", $Text);
				#$Text = preg_replace("(<br />)is", "", $Text);
			$Text = wpautop(trim($Text));
		}
		// if the more/less is in use...
		if ($is_more_less) {
			//define an id if one was not included
			$id = (!$id || $id == 0) ? rand(5,999) : $id;						
			//if removing <p>'s for chopping...
			if ($pragraph_2_br) {
				//change parragraphs into line breaks
				$Text = preg_replace("(<p>)is", "", $Text);
				$Text = preg_replace("(<p >)is", "", $Text);
				$Text = preg_replace("(<p id=\"(.+?)\">)is", "", $Text);
				$Text = preg_replace("(<p id='(.+?)'>)is", "", $Text);
				$Text = preg_replace("(<p class='(.+?)'>)is", "", $Text);
				$Text = preg_replace("(<p class=\"(.+?)\">)is", "", $Text);
				$Text = preg_replace("(<p style='(.+?)'>)is", "", $Text);
				$Text = preg_replace("(<p style=\"(.+?)\">)is", "", $Text);
				$Text = preg_replace("(</p>)is", "<br /><br />", $Text);
				#$Text = preg_replace("(<br />)is", "", $Text);
			}
			//divid/explode the string
			$thetext = explode($mre, $Text);
			
			//fix linebreak issue...
			$movep = '';
			$lastchars = (strlen($thetext[0])-3);
			$lasttext = substr_replace($thetext[0], '', 0, $lastchars);
			if ($lasttext == '<p>') {
				$thetext[0] = substr_replace($thetext[0], '', $lastchars, strlen($thetext[0]));
				$movep = "<p>";
			}
			$hidden_more_content = $movep.$thetext[1];
			//this is iffy... it's to solve if there's a line-break after <!--more-->
			$hidden_more_content = str_replace('<p><br />','<p>',$hidden_more_content);
			//form the html
			$Text = "
			<!-- visible CONTENT -->
				<div class='hys_entry' id='entry{$id}'>{$thetext[0]}</div>
			<!-- SHOW MORE LINK -->";
			// >>> the next string is VERY important that it stay how-is, trench-gallery hack in place for "hys_readmore' id='mor"
			$Text .= "
				<a class='hys_fake_link hys_readmore' id='morelink{$id}' onclick=\"showhide('moreless{$id}'); showhide('morelink{$id}')\" >{$hys['settings']['more']}</a>				
			<!-- HIDDEN MORE CONTENT -->
				<div id='moreless{$id}' class='hys_moreless' style='display:none;'>{$hidden_more_content}
			<!-- SHOW LESS LINK -->
					<div>";
			if (!$pragraph_2_br)
				$Text .= "<br class='optional_hysbr' />";
			$Text .= "
					<a class='hys_fake_link hys_readless' id='lesslink{$id}' onclick=\"showhide('moreless{$id}'); showhideinlineblock('morelink{$id}')\">{$hys['settings']['less']}</a>
					</div>
				</div>";
		}		
		//Send back text
		return $Text;
	}
	
	
/*-------------------------------------------------------------
 Name:      hys_moreless_

 Purpose:   ...
 Receive:   $content
 Return:	$content with more/less toggle on <!--more-->'s
-------------------------------------------------------------*/
	function hys_moreless_($content) {		
		return hys_moreless($content,hys_random(),true);
	}
	
/*-------------------------------------------------------------
 Name:      hys_moreless_

 Purpose:   ...
 Receive:   $content
 Return:	$content with more/less toggle on <!--more-->'s
-------------------------------------------------------------*/
function hys_make_moreless($first,$second,$id='',$readmore_text='',$readless_text='') {
    global $hys;
    $readmore_text = (empty($readmore_text) && isset($hys['settings']['more']) ) ? $hys['settings']['more'] : $readmore_text;
    $readmore_text = (empty($readmore_text)) ? 'read more' : $readmore_text;
    $readless_text = (empty($readless_text) && isset($hys['settings']['less']) ) ? $hys['settings']['less'] : $readless_text;
    $readless_text = (empty($readless_text)) ? 'read less' : $readless_text;
    $id = (empty($id)) ? 'moreless_'.hys_random() : $id;
    //form the html
    return "
        <div class='hys_entry' id='entry{$id}'>{$first}</div>
        <a class='hys_fake_link hys_readmore' id='morelink{$id}' onclick=\"showhide('moreless{$id}'); showhide('morelink{$id}')\" >{$readmore_text}</a>             
        <div id='moreless{$id}' class='hys_moreless' style='display:none;'>{$second}
            <div class='hys_readless_holder'>
                <a class='hys_fake_link hys_readless' id='lesslink{$id}' onclick=\"showhide('moreless{$id}'); showhideinlineblock('morelink{$id}')\">{$readless_text}</a>
            </div>
        </div>";
}


/*-------------------------------------------------------------
 Name:      hys_url_friendly

 Purpose:   removes all non alphanumeric characters from string
            replaced spaces with underscores
 Receive:   $string (string), $length limit
 Return:    $result, alphanumeric string
-------------------------------------------------------------*/
	function hys_urlfriendly($string, $maxLength = 49) { hys_url_friendly($string, $maxLength); }
/*-----------------------------------------------------------*/
	function hys_url_friendly($string, $maxLength = 49) {
	    $result = strtolower($string);
	    $result = preg_replace("/[^a-z0-9\s-]/", "", $result);
	    $result = trim(preg_replace("/[\s-]+/", " ", $result));
	    $result = trim(substr($result, 0, $maxLength));
	    $result = preg_replace("/\s/", "_", $result);
	    return $result;
	}

/*-------------------------------------------------------------
 Name:      hys_random

 Purpose:   ...
 Receive:   length of string
 Return:	$string
-------------------------------------------------------------*/
	function hys_random($length = 5) {
		$characters = '0123456789abcdefghijklmnopqrstuvwxyz';
		$string = '';
		for ($p = 0; $p < $length; $p++)
			$string .= $characters[mt_rand(0, (strlen($characters)-1))];
		return $string;
	}


/*====================================================================================================================
   // !Useful Functions 
--------------------------------------------------------------------------------------------------------------------*/
/*-------------------------------------------------------------
 Name:      hys_status

 Purpose:   return boolen weather heyyou is enabled for a page
 			THIS FUNCTION IS NOT TESTED //TODO: test function
 Receive:   page ID (int)
 Return:	boolean
-------------------------------------------------------------*/
	function hys_status($page_id = '') {
		global $post;
		// below is pealed from the function "hys_load()"
		$page_id = (empty($id)) ? get_the_ID() : intval($id);
		$pmeta 	= get_post_custom($id);
		$hys['hys_page_config'] = @($pmeta['hys_page_config'])  ? unserialize($pmeta['hys_page_config'][0])  : 0;	
		$feature 	= (isset($hys['hys_page_config']['feature'])) ? $hys['hys_page_config']['feature'] : '';
		$feature 	= ($feature == 'NONE' || $feature == '0') ? '' : $feature;
		$preset 	= (isset($hys['hys_page_config']['preset'])) ? $hys['hys_page_config']['preset'] : '';
		$preset 	= (!isset($hys['hys_page_config']['preset']) && !empty($feature)) ? 'custom' : $preset;
		$preset 	= (isset($hys['hys_page_config']['preset']) && !empty($feature)) ? 'custom' : $preset;
		$hys['config'] = $preset;
		if (isset($pmeta['hys_page_feature']) && !isset($pmeta['hys_page_config'])) {
			$old_config = unserialize($pmeta['hys_page_feature'][0]);
			$hys['hys_page_config'] = $old_config;
			$hys['config'] = 'custom';	
		}
		if (empty($hys['config'])) return false;
		else return true;
	}


/*-------------------------------------------------------------
 Name:      hys_listmedia

 Purpose:   lists all contents of the media library and divids
 			into categories if exsist
 Receive:   preselect file (int ID), 
 			# of characters to show for filename 
 Return:	a string of <options> for between <select>'s
-------------------------------------------------------------*/
	function hys_list_media($p='') { return hys_listmedia($p, $choplen = ''); }
	function hys_media_list($p='') { return hys_listmedia($p, $choplen = ''); }
/*-----------------------------------------------------------*/
	function hys_listmedia($preselect='', $choplen = '40') {
		$return = "<optgroup label='MEDIA LIBRARY'></optgroup><optgroup label='No File Selected'><option></option></optgroup>";
		$get_categories = get_terms('media_category');
		$numofcats = count($get_categories);
		
		$array_of_files = array();
		
		$preselect = trim($preselect);
		$getattch = get_posts('post_type=attachment&orderby=post_title&numberposts=-1');
		foreach ($getattch as $k=>$attch) {
			$fullimage = wp_get_attachment_image_src($attch->ID,'full');
			$filetitle = hys_chopstring($attch->post_title, $choplen);
			$array_of_files[$attch->ID] = array(
				'id' 	=> $attch->ID,
				'title' => $filetitle,
				'type' 	=> str_replace(array('/pdf','image/','audio/','jpeg','application.'),array('.PDF','.','.','jpg','.'),$attch->post_mime_type),
			);
		}
		foreach ($get_categories as $category) {
			if ($category->name != 'Uncategorized') {
				$return .= "<optgroup label='{$category->name}'>";				
					// GET POSTS FOR CAT
					$args = array(
						'tax_query' => array(
							array(
								'taxonomy' => 'media_category',
								'field' => 'slug',
								'terms' => $category->slug
							)
						),
						'post_type' => 'attachment',
						'post_status'=>'inherit',
						'numberposts' => '-1',
						'order' => 'ASC',
						'orderby' => 'title'
					);
					$ticker = 0;
					$posts = get_posts( $args );
					foreach ($posts as $apost) {
						$sel = ($apost->ID == $preselect) ? " selected='selected'" : '';
						$return .= "<option value='".$apost->ID."'{$sel}>".hys_chopstring($apost->post_title,90)."</option>";
						unset($array_of_files[$apost->ID]);
					}
				$return .= "</optgroup>";
			}
		}
		$return .= ($numofcats > 0) ? "<optgroup label='Uncategorized'>" : '';
		if (is_array($array_of_files)) {
			foreach ($array_of_files as $afile) {
				$sel = ($afile['id'] == $preselect) ? " selected='selected'" : '';
				$return .= "<option value='{$afile['id']}'{$sel}>{$afile['title']}".$afile['type']."</option>";
			}
		}
		$return .= ($numofcats > 0) ? "</optgroup>" : '';
		return "".$return;
	}




/*-------------------------------------------------------------
 Name:      hys_chopstring

 Purpose:   shorten a string by removing the middle section,
            leave x characters on either side
 Receive:   $string, double the length to leave on each side
 Return:    $string, shortened,
-------------------------------------------------------------*/
	function hys_chopstring($string,$choplen = 30,$cut = '....') {
		if (strlen($string) > $choplen) {
			$ashortertitle = strip_tags($string);
			$string = substr_replace($ashortertitle, '', (floor($choplen/2)), strlen($ashortertitle)).$cut.substr_replace($ashortertitle, '', 0, (strlen($ashortertitle)-(floor($choplen/2))));
		}
		return $string;
	}
	
/*-------------------------------------------------------------
 Name:      datedropdown_reconstruct

 Purpose:   changes date $_POST['sel'] array to mysql date string, 
 Receive:   $_POST['sel_date'][month/day/ect]
 Return:	string, YYYY-MM-DD HH:MM:SS
-------------------------------------------------------------*/
	function reconstruct_datedropdown($val) {
		return datedropdown_reconstruct($val);
	}
	function datedropdown_reconstruct($sel) {
		// define times
		if (!is_array($sel)) return;
		foreach ($sel as $k => $time) {
			if ($time == 'ampm')
				$sel['ampm'] = (!isset($sel['ampm'])) ? date('a') : $sel['ampm'];
			else 
				$sel[$time] = (!isset($sel[$time])) ? 00 : intval($sel[$time]);
		}
		//add 12 hours if PM
		$sel['hour'] = ($sel['ampm'] == 'pm' && $sel['hour'] != 12) 
									? (intval($sel['hour']) + 12) : $sel['hour'];
		//reconstruct
		$date = "{$sel['year']}-{$sel['month']}-{$sel['day']} ".
				"{$sel['hour']}:{$sel['min']}:00";
		//return in mysql format
		return date('Y-m-d H:i:s', strtotime($date));
	}

/*-------------------------------------------------------------
 Name:      datedropdown

 Purpose:   creates the date drop down, 
 Receive:   $preselectdate, date to preselect on drop down
 			$timeanddate, boolean, true = time+date, false = just date
 			$removeday, hide day and make the dates day number, auto to date '01'
 Return:	string, html <select> inputs, name='sel_date[year/month/day/hour/min/sec/ampm]'
-------------------------------------------------------------*/
	function datedropdown($preselectdate = '',$timeanddate = true, $removeday = false, $prefix = 'sel_date') {
		//brea down timestamp if exsists
		$preselectdate 	= (!empty($preselectdate)) 
										? $preselectdate : date('Y-m-d G').":00:00";
		$themonth 		= date('m', strtotime($preselectdate));
		$theday 		= date('d', strtotime($preselectdate));
		$theyear 		= date('Y', strtotime($preselectdate));
		$themin 		= date('i', strtotime($preselectdate));
		$thesec 		= date('s', strtotime($preselectdate));
		$thehour 		= date('G', strtotime($preselectdate));
		$theampm 		= ($thehour > 12) ? 'pm' : 'am';
		$thehour 		= ($thehour > 12) ? $thehour - 12 : $thehour;
		//make drop downs for month,day,year
		$m = "<select name='{$prefix}[month]' />";
		for ($i = 1; $i <= 12; $i++) {
			$i = (strlen($i) == 2) ? $i : '0'.$i;
			$s = ($i == $themonth) ? " selected='selected'" : null;
			$m .= "<option value='".date('m', mktime(0,0,0,$i,1,2008))."'{$s}>".
					date('M', mktime(0,0,0,$i,1,2008))."</option>";
		}
		$m .= "</select>";
		$d = "<select name='{$prefix}[day]'>";
		for ($i = 1; $i <= 31; $i++) {
			$i = (strlen($i) == 2) ? $i : '0'.$i;
			$s = ($i == $theday) ? " selected='selected'" : null;
			$d .= "<option value='".date('d', mktime(0,0,0,1,$i,2008))."'{$s}>".
					date('j', mktime(0,0,0,1,$i,2008))."</option>";
		}
		$d .= "</select>";
		$y = "<select name='{$prefix}[year]' />";
		for ($i = 2002; $i <= 2020; $i++) {
			$i = (strlen($i) == 2) ? $i : '0'.$i;
			$s = ($i == $theyear) ? " selected='selected'" : null;
			$y .= "<option value='".date('Y', mktime(0,0,0,1,1,$i))."'{$s}>".
					date('Y', mktime(0,0,0,1,1,$i))."</option>";
		}
		$y .= "</select>";
		$min = "<select name='{$prefix}[min]'>
					<option>00</option>
					<option>15</option>
					<option>30</option>
					<option>45</option>
					<option value='00'>--</option>";
		for ($i = 0; $i <= 59; $i++) {
			$i = (strlen($i) == 2) ? $i : '0'.$i;
			$s = ($i == $themin) ? " selected='selected'" : null;
			$min .= "<option value='".date('i', mktime(0,$i,0,1,1,2009))."'{$s}>".
					date('i', mktime(0,$i,0,1,1,2009))."</option>";
		}
		$min .= "</select>";
		$h = "<select name='{$prefix}[hour]' />";
		for ($i = 0; $i <= 12; $i++) {
			$i = (strlen($i) == 2) ? $i : '0'.$i;
			$s = ($i == $thehour) ? " selected='selected'" : null;
			$h .= "<option value='".date('H', mktime($i,0,0,1,1,2009))."'{$s}>".
					date('H', mktime($i,0,0,1,1,2009))."</option>";
		}
		$h .= "</select>";
		$ampoptions = array('pm','am');
		$ampm = "<select name='{$prefix}[ampm]'>";
		foreach ($ampoptions as $key=>$value) {
			$ss = ($theampm == $value) ? " selected='selected'": '';
			$ampm .= "<option {$ss}>{$value}</option>";
		}
		$ampm .= "</select>";
		
		$d = ($removeday) ? 
				"<input type='hidden' name='{$prefix}[day]' value='01' />" : $d;
		
		if ($timeanddate == true) {
				$return = "{$m} {$d} {$y} &nbsp; {$h} : {$min} {$ampm}";
		} else {
				$return = "{$m} {$d} {$y}
				<input type='hidden' name='{$prefix}[hour]' value='{$thehour}' />
				<input type='hidden' name='{$prefix}[min]' value='{$themin}' />
				<input type='hidden' name='{$prefix}[sec]' value='01' />
				
				<input type='hidden' name='{$prefix}[ampm]' value='{$theampm}' />";
		}
		
		return $return;
}

/*-------------------------------------------------------------
 Name:      hys_get_thumbnail

 Purpose:   finds ands retirns ID
-------------------------------------------------------------*/
	function hys_get_thumbnail($image_url, $size = 'thumbnail') {
		$size = (in_array($size,array('thumbnail','medium','large','full'))) 
										? strtolower($size) : 'thumbnail';
		if ($size == 'full')
			return $image_url;
		
		//break down the file url  string
		$ext = pathinfo($image_url, PATHINFO_EXTENSION);
		$file = str_replace('.'.$ext,'',$image_url);
		$ext = strtolower($ext);
		
		//get the ap width height
		$width = get_option($size.'_size_w');
		$height = get_option($size.'_size_h');
		
		//replace "jpeg" w wordpress "jpg"
		$ext = ($ext == 'jpeg') ? 'jpg': $ext;
		
		//reconstruct url
		$thumb = "{$file}-{$width}x{$height}.{$ext}";
		return $thumb;
	}	
	function wp_get_attachment_medium_url($id, $size = 'medium'){
		$medium_array = image_downsize( $id, $size );
		$medium_path = $medium_array[0];
		return $medium_path;
	}

/*-------------------------------------------------------------
 Name:      object_2_array

 Purpose:   turn object into an array
 Receive:   object
 Return:	array
-------------------------------------------------------------*/
	function object_2_array($result) { 
	    $array = array(); 
	    foreach ($result as $key=>$value) { 
	        if (is_object($value)) 
	            $array[$key]=object_2_array($value); 
	        elseif (is_array($value)) 
	            $array[$key]=object_2_array($value); 
	        else 
	            $array[$key]=$value; 
	    } 
	    return $array; 
	}  
/*-------------------------------------------------------------
 Name:      disable_stuff

 Purpose:   used to clear filters
 Receive:   anything
 Return:	nothings
-------------------------------------------------------------*/
function disable_stuff( $data ) {
	return false;
}




/*====================================================================================================================
   // !Future  //  Sort  //  ect
--------------------------------------------------------------------------------------------------------------------*/

	function hys_linkify($str) { return hys_linkafy($str); }
	function hys_linkafy($str) {
		return ereg_replace("[[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/]","<a href=\"\\0\">\\0</a>", $str);
	}

/*-------------------------------------------------------------
 Name:      hys_settings_default

 Purpose:   default options, run on register
-------------------------------------------------------------*/
	function hys_settings_default() {
		/*//@TODO: default options.. find out if exsists...
		if (!isset($hys['settings']['line1'])) {
			$dflt_settings = array(
				'plugins' 		=> array(),
				'pages_sep' 	=> 0,
				'moreless' 		=> '255',
				'undercon'		=> 0,
				'underconmsg'	=> 'This website is currently being developed.'
				'more' 			=> 'more..',
				'less' 			=> '..less',
				'pages' 		=> 'Pages:',
				'pages_sep' 	=> 1,
				'line1' 		=> '<div>............</div>',
				'line2' 		=> '<div>......</div>',
				'search_text' 	=> ''
			);
			foreach($dflt_settings as $field => $defaultv) {
				if (!isset($hys['settings'][$field]) && !is_int($defaultv))
					$hys['settings'][$field] = $defaultv;
			}
			update_option('hys_options',$hys['settings']);
		}
		*/
	}
	
	
/*-------------------------------------------------------------
 Name:      hys_get_timezone

 Purpose:   set server time to wordpress time:
-------------------------------------------------------------*/
	function hys_get_timezone() {
	    $timezones = array( 
	        '-12'	=>'Pacific/Kwajalein', 
	        '-11'	=>'Pacific/Samoa', 
	        '-10'	=>'Pacific/Honolulu', 
	        '-9'	=>'America/Juneau', 
	        '-8'	=>'America/Los_Angeles', 
	        '-7'	=>'America/Denver', 
	        '-6'	=>'America/Mexico_City', 
	        '-5'	=>'America/New_York', 
	        '-4'	=>'America/Caracas', 
	        '-3.5'	=>'America/St_Johns', 
	        '-3'	=>'America/Argentina/Buenos_Aires', 
	        '-2'	=>'Atlantic/Azores',
	        '-1'	=>'Atlantic/Azores', 
	        '0'		=>'Europe/London', 
	        '1'		=>'Europe/Paris', 
	        '2'		=>'Europe/Helsinki', 
	        '3'		=>'Europe/Moscow', 
	        '3.5'	=>'Asia/Tehran', 
	        '4'		=>'Asia/Baku', 
	        '4.5'	=>'Asia/Kabul', 
	        '5'		=>'Asia/Karachi', 
	        '5.5'	=>'Asia/Calcutta', 
	        '6'		=>'Asia/Colombo', 
	        '7'		=>'Asia/Bangkok', 
	        '8'		=>'Asia/Singapore', 
	        '9'		=>'Asia/Tokyo', 
	        '9.5'	=>'Australia/Darwin', 
	        '10'	=>'Pacific/Guam', 
	        '11'	=>'Asia/Magadan', 
	        '12'	=>'Asia/Kamchatka' 
	    ); 
		$timezone = trim(get_option('timezone_string'));
		return (empty($timezone)) ? $timezones[get_option('gmt_offset')] : $timezone;
	}


/*-------------------------------------------------------------
 Name:      hys_recount_media_count

 Purpose:   as the function says
-------------------------------------------------------------*/
	function hys_recount_media_count($media_term) {
		global $wpdb;
		$args = array(
			'tax_query' => array(
				array(
					'taxonomy' => 'media_category',
					'field' => 'slug',
					'terms' => $media_term->slug
				)
			),
			'post_type' => 'attachment',
			'post_status'=>'inherit',
			'numberposts' => '-1',
			'order' => 'ASC',
			'orderby' => 'title'
		);
		$get_posts = get_posts( $args );
		$count = 0;
		foreach ($get_posts as $apost) {
			$p = get_post($apost);
			if (is_object($p) && isset($p->post_type) && $p->post_type == 'attachment') $count++;
		}
		// fuck dealing with wp_update_term(), lets just do this manually
		$wpdb->update( $wpdb->term_taxonomy, array('count'=>$count), array( 'term_id' => $media_term->term_id ) );
	}

/*-------------------------------------------------------------
 Name:      hys_media

 Purpose:   custom media page
-------------------------------------------------------------*/
	function hys_media() {
		global $post, $hys,$wpdb;
		$dleteme = false;
		// RECATEGORIZE SELECTED
		if (isset($_POST['change_select_media_cats']) && $_POST['change_select_media_cats'] != '0' && is_array($_POST['hys_media_select'])) {
			if (count($_POST['hys_media_select']) > 0) {
				foreach ($_POST['hys_media_select'] as $ky => $post_change_cat) {
					// delete old relation:
					wp_delete_object_term_relationships($post_change_cat, 'media_category');
					// add new
					wp_set_post_terms($post_change_cat, $_POST['change_select_media_cats'], 'media_category');
				}
			}
		}
		// DELETE SELECTED
		if (isset($_POST['delete_selected_media']) || isset($_POST['delete_selected_media_btm'])) {
			$todelete = count($_POST['hys_media_select']);
			if ($todelete > 0) {
				foreach ($_POST['hys_media_select'] as $key => $deletemeplz) {
					//lets get the cats for this, to-be-deleted
					$get_term = wp_get_object_terms($deletemeplz, 'media_category', array( 'taxonomy' => 'media_category' ));
					// delete from category
					wp_delete_object_term_relationships($deletemeplz, 'media_category');
		  			// remove the post from any attachments
		  			delete_metadata('attachment',$deletemeplz,'_attachments');
					// trash the post
		  			wp_delete_post($deletemeplz);		  			
				}
				$dleteme = true;
			}
		}
		$media_ids_shown = array();		
		?>
	<div class="wrap">
		<div id="icon-upload" class="icon32"><br /></div>
	<h2>Media Library <a href="media-new.php" class="button add-new-h2">Add New</a></h2>
	
	<? if (isset($_GET['posted'])) {
		?><div id="message" class="updated"><p>Media updated.</p></div><?
	}
	elseif (isset($_GET['change_select_media_cats'])) {
		?><div id="message" class="updated"><p>Media items moved.</p></div><?
	}
	elseif (isset($_GET['deleted']) || $dleteme) {
		?><div id="message" class="updated"><p>Media attachment(s) permanently deleted.</p></div><?
	} else {
		echo "<br />";
	}
	
	//list cats...
	$redomediacats 	= get_terms('media_category','hide_empty=0&order=ASC');
	$media 			= get_posts('post_type=attachment&numberposts=-1&order=ASC&orderby=title');
	$number_of_media_items = count($media);
	$media_thumb_cap = 9999999999999999999999999;
	$show_thumbnails = (@$hys['settings']['media_layout'] != 'list') ? true : false;

	// if a media item had an action, recount all media categories
	// @TODO: find a more efficient way of doing this.
	if (
		isset($_GET['deleted']) || 
		(isset($_POST['change_select_media_cats']) && $_POST['change_select_media_cats'] != 0) || 
		isset($_POST['delete_selected_media']) ||
		isset($_GET['updated']) ||
		isset($_GET['hys_media_action'])
		) {
		foreach ($redomediacats as $k => $acat) {
			hys_recount_media_count($acat);
		}
	}
	$mediacats 	= get_terms('media_category','hide_empty=0&order=ASC');
?>

<form id="deletemulti" name='deletemulti' class'deletemulti' action="admin.php?page=hys_media&amp;hys_media_action" method="post">
	<div class="alignleft actions" style='padding-bottom:10px;'>
		<input type="submit" name="delete_selected_media" id="doaction" class="button-secondary action" value="Delete Selected Media" onclick='return showNotice.warn();' />
		<select name='change_select_media_cats' onChange="this.form.submit()">
			<optgroup label='Selecting a new category will submit form'>
				<option value='0'>Change Category</option>
			</optgroup>
			<optgroup label=''>
			</optgroup>
			<optgroup label='New Category'>
				<? foreach ($mediacats as $k => $acat) {
						$optionn = "
					<option value='{$acat->term_id}'>{$acat->name}</option>\n";

						if ($acat->name == 'Uncategorized')
							$end = $optionn;
						else
							echo $optionn;
					}
				 ?>
			</optgroup>
			<optgroup label=''>
				<?= $end ?>
			</optgroup>
		</select>
	</div><!--/alignleft actions-->
  <table class="wp-list-table widefat fixed media" cellspacing="0">
	<thead>
	<tr>
		<th scope='col' id='cb' class='manage-column column-cb check-column'  style="">&nbsp;<? hys_space(10) ?></th>
	</tr>
	</thead>
	<tfoot>
	<tr>
		<th scope='col'  class='manage-column column-cb check-column'  style="">&nbsp;<? hys_space(10) ?></th>
	</tr>
	</tfoot>
	<tbody id="the-list">
	
	<?
	// Cycle through categories,
	foreach ($redomediacats as $acat) {
		
		// if not default, print ct and it's inside photos
		if ($acat->term_id != '1') : ?>
			<tr>
				<td class='hys_media_cat_title'><?=$acat->name?> <? if ($acat->count > 0) { ?><span style='color:#999;'>(<?= $acat->count ?> files)</span><? } ?></td>
			</tr>
			<tr id='post-<?= $mediaid ?>' class='author-self status-inherit' valign="top"><!-- alternate-->
				<td>
					<ul class='hys_media_library_list <?= ($show_thumbnails) ? 'media_list_thumbnails' : "no_thumbnails media_list_text" ?>'>
					<?
					// GET POSTS FOR CAT
					$args = array(
						'tax_query' => array(
							array(
								'taxonomy' => 'media_category',
								'field' => 'slug',
								'terms' => $acat->slug
							)
						),
						'post_type' => 'attachment',
						'post_status'=>'inherit',
						'numberposts' => '-1',
						'order' => 'ASC',
						'orderby' => 'title'
					);
					$ticker = 0;
					// The Query
					$get_posts = get_posts( $args );
					// The Loop
					foreach ($get_posts as $apost) {
						if (!in_array($apost->ID,$media_ids_shown))
						hys_media_output_row($apost);
						$media_ids_shown[] = $apost->ID;
						$ticker++;
					}
		endif; //$acat->term_id != '1'
	}
	?>
					</ul>
				</td>
			</tr>
			<tr>
				<td class='hys_media_cat_title' >Uncategorized.. <!--<span style='color:#999;'>(<?= $extra ?> files)</span>--></td>
			</tr>
			<tr id='post-<?= $mediaid ?>' class='author-self status-inherit' valign="top"><!-- alternate-->
				<td>
					<ul class='hys_media_library_list <?= ($show_thumbnails) ? 'media_list_thumbnails' : "no_thumbnails media_list_text" ?>'>
					<?
					// GET POSTS FOR CAT
					$args = array(
						'tax_query' => array(
							array(
								'taxonomy' => 'media_category',
								'field' => 'term_id',
								'terms' => 1
							)
						),
						'post_type' => 'attachment',
						'post_status'=>'inherit',
						'numberposts' => '-1',
						'order' => 'ASC',
						'orderby' => 'title'
					);
					// The Query
					$get_posts = get_posts( $args );					
					// The Loop
					foreach ($get_posts as $apost) {
					
						if (!in_array($apost->ID,$media_ids_shown))
						hys_media_output_row($apost);
						$media_ids_shown[] = $apost->ID;
					}
					// UNCATEGORIZED
					$args = array(
						'post_type' => 'attachment',
						'post_status'=>'inherit',
						'numberposts' => '-1',
						'order' => 'ASC',
						'orderby' => 'title'
					);
					// The Query
					$get_posts = get_posts( $args );
					// The Loop
					foreach ($get_posts as $apost) {
						if (!in_array($apost->ID,$media_ids_shown))
						hys_media_output_row($apost);
						
					}
					?>
					</ul>
				</td>
			</tr>
	</tbody>
</table>
		<div class="alignleft actions" style='padding:10px 0;'>
			<input type="submit" name="delete_selected_media_btm" id="doaction" class="button-secondary action" value="Delete Selected Media" onclick='return showNotice.warn();' />
		</div>
		<br class="clear" />

		</form>
		</div>
		<? 
		//echo "<small style='color:white'>".get_num_queries()." queries = ".(get_num_queries()/count($media))." per media item</small>";
	}
	
	
/*-------------------------------------------------------------
 Name:      hys_media_output_row

 Purpose:   ,,,
-------------------------------------------------------------*/
	function hys_media_output_row($amedia) {
		global $hys;
		
		$thumbnailsize = 'hys_attachment_size';
		$show_thumbnails = (@$hys['settings']['media_layout'] != 'list') ? true : false;
		
		?>
		<li id='media_item_<?= $amedia->ID?>'>
			<?							
			$custom = wp_get_attachment_image_src($amedia->ID,$thumbnailsize);
			if ($custom[1] != 120) { //120x70											
				$thumbnailsize = 'thumbnail';
				$custom = wp_get_attachment_image_src($amedia->ID,$thumbnailsize);
			}
			?>
			<a href="media.php?attachment_id=<?= $amedia->ID ?>&amp;action=edit" title="Edit <?= $amedia->post_name ?>">
				<? 
				if ($show_thumbnails) {
					$goahead = array('image/jpeg','image/jpg','image/bmp','image/gif','image/png','image/tiff');
					if (in_array($amedia->post_mime_type,$goahead))  { ?>
						<img src="<?=$custom[0]?>" class="attachment_thumbnail_main" alt="<?= $amedia->post_name ?>" title="<?= $amedia->post_name ?>" />
					<? } else { ?>
							
							<?
							if (in_array($amedia->post_mime_type,array('application/pdf','application/msword','application/document')))  {
								?> <img src='<? bloginfo('wpurl') ?>/wp-includes/images/crystal/document.png ' alt='' class='' style='' /></a> <?
							}
							elseif (in_array($amedia->post_mime_type,array('audio/mpeg','audio/mpeg','audio/mp4','audio/x-wav')))  {
								?> <img src='<? bloginfo('wpurl') ?>/wp-includes/images/crystal/audio.png ' alt='' class='' style='' /></a> <?
							}
							elseif (in_array($amedia->post_mime_type,array('video/mpeg','video/mp4','video/quicktime','video/x-msvideo')))  {
								?> <img src='<? bloginfo('wpurl') ?>/wp-includes/images/crystal/video.png ' alt='' class='' style='' /></a> <?
							} else {
								echo $amedia->post_mime_type;
							}
							?>
							
						<div class='hys_description' style='padding-bottom:4px;'><?	echo str_replace(array('application/','video/','audio/'),'',$amedia->post_mime_type); ?></div>
					
					<? } 
				} ?>
			</a>
			<a class='submitdelete' onclick='return showNotice.warn();' href='<?= wp_nonce_url('post.php?action=delete&amp;post='.$amedia->ID, 'delete-attachment_' . $amedia->ID) ?>'>
				<img src='<?=$hys['dir']?>/res/imgs/delete.png' alt='' class='hys_admin_ico delete_attach' style='' /></a>
			<a href='media.php?attachment_id=<?= $amedia->ID ?>&amp;action=edit' title='View <?= $amedia->post_title ?>'><img src='<?=$hys['dir']?>/res/imgs/right.png' alt='' class='hys_admin_ico view_attach' style='' /></a>
			<? if (!$show_thumbnails) : ?>
			<a href='media.php?attachment_id=<?= $amedia->ID ?>&amp;action=edit' title='Edit <?= $amedia->post_title ?>'><img src='<?=$hys['dir']?>/res/imgs/pencil.png' alt='' class='hys_admin_ico view_attach' style='' /></a>
				<div id='mag_holder_<?= $amedia->ID ?>' class='mag_holder'><img src='<?=$hys['dir']?>/res/imgs/magnifier.png' class='hys_admin_ico preview_attach magnifier' />
				<div class='magnifi_me' id='magnifi_me_<?= $amedia->ID ?>' rel='<?=$custom[0]?>'><img src='<?=$hys['dir']?>/res/imgs/magnifier.png' width='120' height='70' alt='' /></div>
				</div>
			<? endif ?>
			<div class='hys_media_title'><!--<a href="media.php?attachment_id=<?= $amedia->ID ?>&amp;action=edit" title="Edit &#8220;home_thumb_3&#8221;">-->
			<label><input type='checkbox' name='hys_media_select[]' value='<?= $amedia->ID?>' id='this_media_item_<?= $amedia->ID ?>' onchange='hysmedia_highlight(<?= $amedia->ID ?>)' />
			<?= ($show_thumbnails) ? hys_chopstring($amedia->post_title,11) : $amedia->post_title; ?><!--</a>--></label></div>
		</li>
		<?
	}
	
	
	
	
/*-------------------------------------------------------------
 Name:      hys_download

 Purpose:   force download a URL
-------------------------------------------------------------*/
	function hys_download($filename) {
		global $hys;
		
		if (ini_get('allow_url_fopen') != 1) {
			header('Location: '.$filename);
			exit;
			die;
		} else {
			// required for IE, otherwise Content-disposition is ignored
			if(ini_get('zlib.output_compression'))
			  ini_set('zlib.output_compression', 'Off');
			// addition by Jorg Weske
			$file_extension = strtolower(substr(strrchr($filename,"."),1));
			if( $filename == "" ) {
			  //echo "ERROR: download file NOT SPECIFIED <!--. USE force-download.php?file=filepath-->";
			  //exit;
			} elseif ( ! file_exists( $filename ) )  {
			  //echo "ERROR: File not found";
			  //exit;
			};
			switch( $file_extension ) {
			  case "pdf": $ctype="application/pdf"; break;
			  case "exe": $ctype="application/octet-stream"; break;
			  case "zip": $ctype="application/zip"; break;
			  case "doc": $ctype="application/msword"; break;
			  case "xls": $ctype="application/vnd.ms-excel"; break;
			  case "ppt": $ctype="application/vnd.ms-powerpoint"; break;
			  case "gif": $ctype="image/gif"; break;
			  case "png": $ctype="image/png"; break;
			  case "jpeg":
			  case "jpg": $ctype="image/jpg"; break;
			  default: $ctype="application/force-download";
			}
			header("Pragma: public"); // required
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Cache-Control: private",false); // required for certain browsers 
			header("Content-Type: $ctype");
			// change, added quotes to allow spaces in filenames, by Rajkumar Singh
			$dlme = basename($filename);
			header("Content-Disposition: attachment; filename=\"".$dlme."\";" );
			header("Content-Transfer-Encoding: binary");
			//header("Content-Length: ".filesize($filename));
			readfile("$filename");
			exit();
		}
	}




/*-------------------------------------------------------------
 Name:      

 Purpose:   
-------------------------------------------------------------*/
function wt_get_category_count($input = '') {
	global $wpdb;
	if($input == '') {
		$category = get_the_category();
		return $category[0]->category_count;
	} elseif(is_numeric($input)) {
		$SQL = "SELECT $wpdb->term_taxonomy.count FROM $wpdb->terms, $wpdb->term_taxonomy WHERE $wpdb->terms.term_id=$wpdb->term_taxonomy.term_id AND $wpdb->term_taxonomy.term_id=".intval($input);
		return $wpdb->get_var($SQL);
	} else {
		$SQL = "SELECT $wpdb->term_taxonomy.count FROM $wpdb->terms, $wpdb->term_taxonomy WHERE $wpdb->terms.term_id=$wpdb->term_taxonomy.term_id AND $wpdb->terms.slug='".mysql_real_escape_string($input)."'";
		return $wpdb->get_var($SQL);
	}
}



/*-------------------------------------------------------------
 Name:   	hys_ids_of_nav_menu 

 Purpose:   get the object ID of a menu item ID used when 
 			wanting to force-hightlight the "products" page 
 			when viewing a single project
-------------------------------------------------------------*/

	function hys_ids_of_nav_menu($nav_slug, $reverse_array = false) {                              
	    $terms   = get_terms('nav_menu');
	    $term_id = '';
	    foreach ($terms as $aterm) {
	       if ($aterm->slug == $nav_slug || $aterm->name == $nav_slug)
	            $term_id = $aterm->term_id;    
	    }
	    $items        = get_objects_in_term( $term_id , 'nav_menu' );
	    $menuids      = array();
	    foreach ($items as $uselss => $menu_id) {
	       $realitem                       = get_post($menu_id);
	       $object_id                      = get_post_meta( $realitem->ID, '_menu_item_object_id', true );
	       $menuids[$realitem->menu_order] = array('menu_id' => $menu_id, 'object_id' => $object_id );
	    }
	    ksort($menuids);
	    $return = array();
	    foreach ($menuids as $order => $ids) {
			if ($reverse_array) {
				$return[$ids['object_id']] = $ids['menu_id'];
	    	} else {
				$return[$ids['menu_id']] = $ids['object_id'];
	    	}
	    }
	    return $return;
	}


/*-------------------------------------------------------------
 Name:   	hys_get_feature_image_src 

 Purpose:   ...
-------------------------------------------------------------*/

function hys_get_feature_image_src($id = '',$size = 'full') {
	$id = (empty($id)) ? get_the_ID() : $id;
	return wp_get_attachment_image_src( $id , $size );
}




/*-------------------------------------------------------------
 Name:   	showAdminMessages 

 Purpose:   warn users of removed lightbox, provide solution on 
 			how to fix
-------------------------------------------------------------*/

function showAdminMessages() {
	global $hys;
    if( isset( $_GET['dismiss_lb_notice'] ) )
        update_option( 'dismiss_lb_notice', 1 );
		$dismiss_lb_notice = get_option( 'dismiss_lb_notice' );
	if ( (@$hys['settings']['lightbox'] == 1)   &&   !is_dir(  WP_CONTENT_DIR.'/plugins/hylb'  )  && $dismiss_lb_notice != 1 ) {
   echo '<div id="message" class="error" style="padding:10px;">'."<strong>ATTENTION:</strong> a recent <em>heyyou</em> update requires the manual exteneral installation of LightBox to your website. Please download this plugin at: <a href='http://hey-you.ca/lightbox-extension/' target='_Blank'>http://hey-you.ca/lightbox-extension/</a>.<br />
   <br />
   <a href='?dismiss_lb_notice'>Dismiss</a>"."</div>";
   }
}
add_action('admin_notices', 'showAdminMessages');




 
 
 


/**
 * change rewrite rules.. for when veiwing single heyyou posts.., since we view posts within a page, as part
 * of the reasoning for heyyou, we don't use Wordpress built-in custom URL rewriting for the custom post
 * type, instead just a URL query like "/?hypg=711" - we need this to look like /{variable}/{id or slug}/
 *
 * @since 0.1.3
 */	
// rewrite
/*
add_action('init','add_hyspg_rewrite');
	function add_hyspg_rewrite() {
		add_rewrite_rule('^hypg/([^/]*)/?','?hypg=$matches[1]');
		add_rewrite_tag('%hypg%','([^&]+)');
	}
*/

/*
add_filter( 'rewrite_rules_array','my_insert_rewrite_rules' );
//add_filter( 'query_vars','my_insert_query_vars' );
add_action( 'wp_loaded','my_flush_rules' );

// flush_rules() if our rules are not yet included
function my_flush_rules(){
	$rules = get_option( 'rewrite_rules' );

	if ( ! isset( $rules['(hypg)/(\d*)$'] ) ) {
		global $wp_rewrite;
	   	$wp_rewrite->flush_rules();
	}
}

// Adding a new rule
function my_insert_rewrite_rules( $rules )
{
	$newrules = array();
	$newrules['(hypg)/(\d*)$'] = '?hypg=$matches[1]';
	return $newrules + $rules;
}
*/

// Adding the id var so that WP recognizes it
//function my_insert_query_vars( $vars )
//{
//    array_push($vars, 'id');
//    return $vars;
//}



function hys_closetags($html) {
  preg_match_all("/<\/?(\w+)((\s+(\w|\w[\w-]*\w)(\s*=\s*(?:\".*?\"|'.*?'|[^'\">\s]+))?)+\s*|\s*)\/?>/i",$html,$result);
  $tags = &$result[0]; $closeCnt = 0;
  for ($i=count($tags)-1;$i>=0;$i--) {
  if ($tags[$i]{strlen($tags[$i])-2}!='/') {
  if ($tags[$i]{1}!='/') {
  if (!$closeCnt) 
  $html .= ''; 
   else 
   $closeCnt--;
  } else {
  $closeCnt++;
  }
  }
  }
  return $html;
}


// List pages (for admin options page drop down)

function hys_list_pages($preselect) {
$pages = get_pages('');
foreach ($pages as $page) {
$presel = ($page->ID == $preselect) ? " selected='selected'" : '';
$ischild = ( !empty($page->post_parent) ) ? " &nbsp;&nbsp;&nbsp;&nbsp; " : '';
echo "<option value='{$page->ID}'{$presel}>{$ischild}{$page->post_title}</option>\n";
}
}

// list images in <option>

function hys_list_images($preselect) {
$pages = get_posts('post_type=attachment&numberposts=-1');
foreach ($pages as $page) {
$presel = ($page->ID == $preselect) ? " selected='selected'" : '';
echo "<option value='{$page->ID}'{$presel}>".getp_chopstring($page->post_title)."</option>\n";
}
}


// Get the current template

function hys_this_template() {
	return get_post_meta(get_the_ID(),'_wp_page_template',true);
}


	


/*--------------------------------------------------------------------------------------------------------------------
====================================================================================================================*/
?>