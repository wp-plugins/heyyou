<?php
function hys_default_settings() {
		global $hys;
		
		
		$defaults = array(
			'installed'			=> 1,
			
			'not_heyshauna'		=> 0,
			'no_attachments'	=> 0,
			
            'lightbox' 			=> 1,
            'facebook' 			=> 1,
            'mobile_css' 		=> 0,
            'show_opt_banner' 	=> 0,
            'show_opt_color' 	=> 0,
            'show_opt_fb' 		=> 0,
            'show_opt_tw'		=> 1,
            'show_opt_hide' 	=> 0,
            
            'heyyou_menu_2' 	=> 1,
            'subadmin_menu_2' 	=> 1,
            'admin_menu_2' 		=> 1,
            'heyyou_menu_10' 	=> 1,
            'subadmin_menu_10' 	=> 1,
            'admin_menu_10' 	=> 1,
            'heyyou_menu_20' 	=> 1,
            'subadmin_menu_20' 	=> 1,
            'admin_menu_20' 	=> 1,
            'subadmin_menu_60' 	=> 1,
            'admin_menu_60' 	=> 1,
            'subadmin_menu_70' 	=> 1,
            'subadmin_menu_80' 	=> 1,
            'admin_menu_80' 	=> 1,
            'heyyou_menu_100' 	=> 1,
            'subadmin_menu_100' => 1,
            'admin_menu_100' 	=> 1,
            'heyyou_menu_126' 	=> 1,
            'subadmin_menu_126' => 1,
            'admin_menu_126' 	=> 1,
            
            'show_opt_blurbhide'=> 1,
            'backup_onoff'		=> 'off',
            
            'undercon' 			=> 0,
            'undercon_reveal' 	=> 1,
            'undercon_sess' 	=> 0,
            'undercon_cook' 	=> 0,
            'ie6msg' 			=> 1,
            'undercontit' 		=> 'Under Construction',
            'underconmsg' 		=> "Message or notice to apear on site.",
            'meta_keywords' 	=> 'Add 5 keywords/phrases here seperated by comas for better SEO',
            'meta_description' 	=> 'Add a brief description of your website here for search engine descriptions, and better SEO, limit to 150-200 characters.',
            'moreless' 			=> 200,
            'viewport' 			=> 480,
            'navview' 			=> '',
            'more' 				=> '..more',
            'less' 				=> 'less..',
            'pages' 			=> 'Pages: ',
            'pages_sep' 		=> 1,
            'back' 				=> '&larr; Back',
            'line1' 			=> '<div class=\'line\'>......................</div>',
            'line2' 			=> '<div class=\'line\'>..........</div>',
            'search_text' 		=> 'Results: ',
            
            'tinymce_css' 		=> array('dark','gray','light')
            
		);
		
		update_option( 'hys_options', $defaults );
}


/*-------------------------------------------------------------
 Name:      hys_bkup_sched_cron
 
 Purpose:   schedule cron on page load if not scheduled
 Receive:   - none -
 Return:    - none -
-------------------------------------------------------------*/
    function hys_bkup_sched_cron() {
		global $hys;
		
		//if backup is turned on (wp-admin > heyyou > automated backup)
		if (@$hys['settings']['backup_onoff'] == 'on') {
        	
        	//get the interval from the stored user selected value
        	$interval = (isset($hys['settings']['backup_period']) && !empty($hys['settings']['backup_period'])) ? $hys['settings']['backup_period'] : 'monthly';
        	
        	//if the interval just changed via submitted a new interval
			$interval_changed = false;
			if (isset($_POST['backup_period_currently']) && isset($_POST['hys_options']['backup_period'])) {
				if ($_POST['backup_period_currently'] != $_POST['hys_options']['backup_period']) {
					$interval_changed = true;
					$interval = $_POST['hys_options']['backup_period'];
				}
			}
        	
        	//if it's not scheduled or, if we've changed the interval
        	if ( !wp_next_scheduled( 'hys_bkp_cron' ) || $interval_changed ) {
				
				//clear exsisting interval..
				wp_clear_scheduled_hook( 'hys_bkp_cron' );
				
				//add new interval
				wp_schedule_event(time(), $interval, 'hys_bkp_cron');
				
			} 
           	 
		} else {
			
			//if turned off, remove all backup cronjobs
    		wp_clear_scheduled_hook( 'hys_bkp_cron' );
			
		}
    }



/*-------------------------------------------------------------
 Name:      hys_crontimes
 
 Purpose:   add additional crontimes
 Receive:   - none -
 Return:    - none -
-------------------------------------------------------------*/
function hys_crontimes( $schedules ) {
	$schedules['weekly'] = array(
		'interval' => 604800,
		'display' => __('Weekly')
	);
	$schedules['monthly'] = array(
		'interval' => 2629743,
		'display' => __('Monthly')
	);
	$schedules['quarterly'] = array(
		'interval' => 10518972,
		'display' => __('Quarterly')
	);
	$schedules['biyearly'] = array(
		'interval' => 15778458,
		'display' => __('BiYearly')
	);
	$schedules['yearly'] = array(
		'interval' => 31556926,
		'display' => __('Yearly')
	);
	return $schedules;
}

/*-------------------------------------------------------------
 Name:      hys_backup_check

 Purpose:   run every admin_init to see if backup is called
 Receive:   - none -
 Return:	- none -
-------------------------------------------------------------*/
	function hys_backup_check() {
	
		// if delete all backups
		if (isset($_GET['deleteallbackups'])) {
			$backup_folder 	= 'hys_sql_backups/'.$hys['site'];
			$backup_dir 	= WP_CONTENT_DIR.'/'.$backup_folder;

			$dir = $backup_dir;
			if (is_dir($dir)) { 
				$objects = scandir($dir); 
				foreach ($objects as $object) { 
					if ($object != "." && $object != "..") { 
						if (filetype($dir."/".$object) == "dir") 
							rmdir($dir."/".$object); 
						else 
							unlink($dir."/".$object); 
					} 
				} 
				reset($objects); 
				rmdir($dir); 
			} 
		}
		
		// if hit the backup button
		if (isset($_GET['backup_now']))
			hys_backup_email();			
	}

/*-------------------------------------------------------------
 Name:      hys_backup_make

 Purpose:   create the actual backups
 Receive:   backup type (XML, or SQL), 
 			the file name (filename.sql)
 Return:	the full path to the backedup file
-------------------------------------------------------------*/
	function hys_backup_make($bkup_type,$file_name) {	
		global $hys;
		
		$dir_error 		= false;
		$file_error		= false;
		
		$backup_dir 	= WP_CONTENT_DIR.'/hys_sql_backups';
		$backup_folder 	= $backup_dir.'/'.$hys['site'];
		$backup_file 	= $backup_folder.'/'.$file_name;
		
		//create backup directory and index file if not exsist
		if (!is_dir($backup_dir)) {
			if (!mkdir($backup_dir, 0777))
				$dir_error = "Unable to make directory: ".$backup_dir;
			
			//make index file to prevent indexing
			$fp = fopen($backup_dir.'/index.php', 'w+');
			fwrite($fp, "<?php\n//silence is golden\n?>");
			fclose($fp);
		}
		//create backup directory and index file if not exsist
		if (!is_dir($backup_folder)) {
			if (!mkdir($backup_folder, 0777))
				$dir_error = "Unable to make directory: ".$backup_folder;
			
			//make index file to prevent indexing
			$fp = fopen($backup_folder.'/index.php', 'w+');
			fwrite($fp, "<?php\n//silence is golden\n?>");
			fclose($fp);
		}

		//dump database into bckup file
		if (fopen($backup_file, "w+")) {
			$mysqldump 	= (isset($hys['settings']['backup_from']) && !empty($hys['settings']['backup_from'])) ? $hys['settings']['backup_from']."/" : false;
			$command 	= "{$mysqldump}mysqldump -h".DB_HOST." -u".DB_USER." -p".DB_PASSWORD." ".DB_NAME." > {$backup_file}";
			exec($command,$error);
		} else {			
			$file_error = "Unable to open/create .sql file: {$backup_file}";
		}
		
		//if there was an error, email it and kill the page
		if ($error || !$mysqldump || $dir_error || $file_error) {
			if (!$mysqldump) $error = "location of mysqldump is not set";
			//EMAIL //@TODO: remove this before putting public
			$to 		= 'heyyou@davidsword.ca';
			$subject 	= 'BACKUP ERROR';
			$headers    = 'From: heyyou <errors@hey-you.ca>' . "\r\n";
			$message	= "<p><strong>WARNING</strong>: Backup Error for ".get_bloginfo('url')."</p> <p>: {--{ <strong>". $error ."</strong> }--} : </p> ".
						  "<p>An email of this error has been sent to heyyou staff.".
						  "If this persists please contact www.hey-you.ca/contact/ directly.</p>".
						  "<p>&nbsp;</p>\n<p>Regards,<p>\n\n".
						  "<p>heyyou<br />\n".
						  "www.hey-you.ca</p>\n";
			$mailit		= wp_mail($to, $subject, $message, $headers);
			die($message);			
		} 
		
		//return full path to file for mailing
		return $backup_file;
	}

/*-------------------------------------------------------------
 Name:      hys_backup_email

 Purpose:   setup the file names, email, make the backups, and 
 			send the backup email with msg and attachments
 Receive:   - none -
 Return:	- none -
-------------------------------------------------------------*/
	function hys_backup_email() {
		global $hys, $current_user;
		
		get_currentuserinfo();
		$disp_name  = $current_user->data->display_name;
		$logn_name  = $current_user->data->display_name;
		$name 		= $current_user->user_firstname." ".$current_user->user_lastname;
		$name 		= (empty($name)) ? $disp_name : $name;
		$name 		= (empty($name) && empty($disp_name)) ? $logn_name : $name;
		
		$file_name 	= $hys['site'];
		$date 		= date('Y-m-d_H-i-s');
		$name		= (empty($name)) ? $file_name : $name;
		
		$period		= @(isset($hys['settings']['backup_period']) && !empty($hys['settings']['backup_period']) && !isset($_GET['backup_now'])) ? 
						"<strong>{$hys['settings']['backup_period']}</strong>" : "as requested";
		
		$name_sql	= $file_name.'-sql_'.$date.'.sql'; //domainname_SQL_2011-03-04.sql
		#$name_xml	= 'xml'.$file_name.'_XML_'.$date.'.xml'; //domainname_XML_2011-03-04.xml
		
		$file_sql	= hys_backup_make('sql',$name_sql);
		#$file_xml	= hys_backup_make('xml',$name_xml);
		
		$to 		= (isset($hys['settings']['backup_to']) && !empty($hys['settings']['backup_to'])) 
						? $hys['settings']['backup_to'] : 'heyyou@davidsword.ca'; //heyyou_backups@gmail.com
		$subject 	= $file_name.' backup - '.date('M j Y').'';
		$headers  	= 'MIME-Version: 1.0' . "\r\n";
		$headers   .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		$headers   .= 'To: '.$name.'' . "\r\n";
		$headers   .= 'From: heyyou <backups@hey-you.ca>' . "\r\n";
		$headers   .= 'Cc: heyyou@davidsword.ca' . "\r\n";
		$attachs 	= '';//array($file_sql); #$file_xml
		$message	= @"<p>{$name},</p>\n\n".
					
					"<p>This email is an automated backup sent {$period} containing a database export (.sql file). In the event of a server failer or accidential deletion, this file can be used to restore content.</p>\n\n".
					
					"<p>If you would prefer not to receive these emails- feel free to turn automated backups off, or change the email recipient via \"Backups\" in the <a href='".get_bloginfo('wpurl')."/admin.php?page=heyyou/_functions.php'>heyyou settings</a> of your website's administrative panel.</p>\n\n".
					
					"<p>&nbsp;</p>\n<p>Regards,<p>\n\n".
					
					"<p>heyyou<br />\n".
					"www.hey-you.ca<br />
					<a href='http://twitter.com/heyyou_plugin'>@heyyou_plugin</a>
					</p>\n";
		
		wp_mail( $to, $subject, $message, $headers, $attachs);
		
	}


/*-------------------------------------------------------------
 Name:      hys_settings_page_output

 Purpose:   the options page
 Receive:   - none -
 Return:	- none -
-------------------------------------------------------------*/
	function hys_settings_page_output() {
		global $hys, $current_user, $menu, $submenu, $wpdb, $wp_query;
		
		
		$prefix = $wpdb->base_prefix.'capabilities';
		
		$user = object_2_array($current_user->data);

		if ( @$user[$prefix]['administrator'] == 1 ||
			 @$user[$prefix]['heyyou_subadmin'] == 1  ) {
			$a_admin = true;
		} else {
			if ($current_user->caps && ($current_user->caps['administrator'] == 1 || $current_user->caps['heyyou_subadmin'] == 1)) {
				$a_admin = true;
			} else {
				$a_admin = false;
			}
		}
				
	?>
	
	<div class="wrap">
	<h2><em>heyyou</em> Plugin Options</h2>
	
		<?php	
		if (isset($_GET['delete_media_folder'])) {
			wp_delete_term(intval($_GET['delete_media_folder']), 'mediacategory' );	
			$ii = 0;
			$get_categories = get_terms('mediacategory','hide_empty=0');		
			foreach ($get_categories as $category)
				$hys['settings']['media_cats'][$ii] = $category->name;
				
			for ($i = $ii; $i != 15; $i++) {
				$hys['settings']['media_cats'][$i] = '';
			}
		}

		if (isset($_GET['settings-updated']))
			echo "<div id='message' class='updated fade'><p>Settings successfully updated.</p></div>";
				
		if (isset($_GET['message']) && $_GET['message'] == 'dlted')
			echo "<div id='message' class='updated fade'><p>Media category deleted.</p></div>";
	
		if (isset($_GET['message']) && $_GET['message'] == 'backup')
			echo "<div id='message' class='updated fade'><p>Wordpress successfully backed up.</p></div>";

		if (isset($_GET['message']) && $_GET['message'] == 'backupsdeleted')
			echo "<div id='message' class='updated fade'><p>Backups siccessfully deleted.</p></div>";
	?>
	
	<form method="post" action="options.php">
	
	
	
	    <?php settings_fields( 'hys_settings' ); ?>
	    <input type='hidden' name='hys_options[installed]' value='1' />
	    <table class="form-table">
	    
    <?php 
	if (!$a_admin) {
	?>
			</table>
		<div style='display:none;'>
			<table class="form-table">
	<?php
	} //endif($a_admin) 
	?>		
	        
    <tr valign="top">
    <th scope="row"><h3>Options:</h3></th>
    <td>
		<table cellpadding="0" cellspacing="0">
			<tr>
				<td valign="top">
					
					<h4 style='padding-top:0;margin-top:0'>Include Tools/Apps:</h4>
		        	<label>
		        	<input type='checkbox' name='hys_options[mootools]' value='1' <?php 
		        		echo chckchckbox(@$hys['settings']['mootools'])
		        		?> /> MooTools
		        	</label><br />
		        	<label>
		        	<input type='checkbox' name='hys_options[lightbox]' value='1' <?php 
		        		echo chckchckbox(@$hys['settings']['lightbox'])
		        		?> /> Lightbox
		        	</label><br />
		        	<label>
		        	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;  <input type='checkbox' name='hys_options[lightboxcustom]' value='1' <?php 
		        		echo chckchckbox(@$hys['settings']['lightboxcustom'])
		        		?> /> custom LightboxOptions()
		        	</label><br />
		        	<label>
		        	<input type='checkbox' name='hys_options[jquery]' value='1' <?php 
		        		echo chckchckbox(@$hys['settings']['jquery'])
		        		?> /> jQuery
		        	</label><br />
		        	<label>
		        	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='checkbox' name='hys_options[jquery_lightbox]' value='1' <?php 
		        		echo chckchckbox(@$hys['settings']['jquery_lightbox'])
		        		?> /> jQuery.Lightbox
		        	</label><br />
		        	<label>
		        	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;  <input type='checkbox' name='hys_options[jquery_lightbox_assign]' value='1' <?php 
		        		echo chckchckbox(@$hys['settings']['jquery_lightbox_assign'])
		        		?> /> <span title='$(.attachments a, .hys_attach ul li .attach_image a, ul.photo_gallery li a).lightBox...'>define galleries: <span class='hys_description'>(hover to reveal)</span></span>
		        	</label><br />
		        	<label>
		        	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='checkbox' name='hys_options[jquery_opacityrollovers]' value='1' <?php 
		        		echo chckchckbox(@$hys['settings']['jquery_opacityrollovers'])
		        		?> /> jQuery.OpacityRollovers
		        	</label><br />
		        	<label>
		        	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='checkbox' name='hys_options[jquery_cycle]' value='1' <?php 
		        		echo chckchckbox(@$hys['settings']['jquery_cycle'])
		        		?> /> jQuery.Cycle
		        	</label><br />
		        	<label>
		        	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='checkbox' name='hys_options[jquery_fx]' value='1' <?php 
		        		echo chckchckbox(@$hys['settings']['jquery_fx'])
		        		?> /> jQuery.Effects
		        	</label><br />
		        	<label>
		        	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='checkbox' name='hys_options[jquery_color]' value='1' <?php 
		        		echo chckchckbox(@$hys['settings']['jquery_color'])
		        		?> /> jQuery.Color
		        	</label><br />
		        	<br />
		        	
					<h4 style='padding-top:0;margin-top:0'>Wordpress Options:</h4>
		        	<label>
		        	<input type='checkbox' name='hys_options[page_featured_image]' value='1' <?php 
		        		echo chckchckbox(@$hys['settings']['page_featured_image'])
		        		?> /> Use "Featured Image" on pages
		        	</label><br />
		        	<label>
		        	<input type='checkbox' name='hys_options[post_featured_image]' value='1' <?php 
		        		echo chckchckbox(@$hys['settings']['post_featured_image'])
		        		?> /> Use "Featured Image" on posts
		        	</label><br />
		        	<label>
		        	<input type='checkbox' name='hys_options[page_secondary_image]' value='1' <?php 
		        		echo chckchckbox(@$hys['settings']['page_secondary_image'])
		        		?>  />  Use "Secondary Image" on pages
		        	</label><br />
		        	<label>
		        	<input type='checkbox' name='hys_options[post_secondary_image]' value='1' <?php 
		        		echo chckchckbox(@$hys['settings']['post_secondary_image'])
		        		?>  /> Use "Secondary Image" on posts
		        	</label><br />
		        	<label>
		        	<input type='checkbox' name='hys_options[page_excerpts]' value='1' <?php 
		        		echo chckchckbox(@$hys['settings']['page_excerpts'])
		        		?> /> Use "Excerpts" on pages
		        	</label><br />
		        	<input type='checkbox' name='hys_options[post_excerpts]' value='1' <?php 
		        		echo chckchckbox(@$hys['settings']['post_excerpts'])
		        		?> /> Use "Excerpts" on posts
		        	</label><br />
				     <br />
				     <br />
					<h4 style='padding-top:0;margin-top:0'>Generate &lt;head&gt; html:</h4>
				    
				    <label>
					    TinyMCE Stylesheet:<br />
			        	&nbsp; &nbsp; ..<? 
			        	echo "<span class='description'>".str_replace(array(get_bloginfo('url'),'/wp-content/themes'),'',
			        	get_bloginfo('stylesheet_directory')) ?>/</span>  <input type='text' style='color: #999;' name='hys_options[header_tinymce]' value='<?= @$hys['settings']['header_tinymce'] ?>'  />
			        </label>
			        <br />
			        
			        <label>
					    .favicon URL:<br />
			        	&nbsp; &nbsp; ..<? 
			        	echo "<span class='description'>".str_replace(array(get_bloginfo('url'),'/wp-content/themes'),'',
			        	get_bloginfo('stylesheet_directory')) ?>/</span>  <input type='text' style='color: #999;' name='hys_options[header_favicon]' value='<?= @$hys['settings']['header_favicon'] ?>' />
			        </label>
			        <br />
				     
			        <label>
					    js.js URL:<br />
			        	&nbsp; &nbsp; ..<? 
			        	echo "<span class='description'>".str_replace(array(get_bloginfo('url'),'/wp-content/themes'),'',
			        	get_bloginfo('stylesheet_directory')) ?>/</span>  <input type='text' style='color: #999;' name='hys_options[header_js]' value='<?= @$hys['settings']['header_js'] ?>' />
			        </label>
				  </td>
				  <td>
				  
					<h4 style='padding-top:0;margin-top:0'>Exceptions:</h4>
		        	<label>
		        	<input type='checkbox' name='hys_options[not_heyshauna]' value='1' <?php 
		        		echo chckchckbox(@$hys['settings']['not_heyshauna'])
		        		?> /> This is <u>not</u> a heyshauna website
		        	</label><br />
		        	<label>
		        	<input type='checkbox' name='hys_options[no_attachments]' value='1' <?php 
		        		echo chckchckbox(@$hys['settings']['no_attachments'])
		        		?> /> This site does <u>not</u> use Attachments
		        	</label><br />
					<br />
					<h4 style='padding-top:0;margin-top:0'>Navigation:</h4>
					
					
					
				        	<table cellpadding="0" cellspacing="0" style='padding-left:20px;'>
				        		<tr>
				        			<td>&nbsp;</td>
				        			<td class='hys_description'>client</td>
				        			<td class='hys_description'>subadmin</td>
				        		</tr>
				        		
				        			<?php
				        				//global $menu;
				        				
				        				$menu = $hys['menu_copy'];
				        				
				        				foreach ($menu as $ke => $pageinfo) {
				        					$pageinfo[0] = trim(str_replace(array(' 0',' 1'),'',strip_tags($pageinfo[0])));
				        					
				        					//if (!in_array($pageinfo[0],array('Dashboard','Pages','Users','heyyou'))) {
						        				if (empty($pageinfo[0])) {

						        				} else {
						        					$alwys_show 	= array('Dashboard','Media','Pages','heyyou', 'Users','heyyoumedia');
						        					$if_subadmin 	= array('Settings','Appearance');
	
						        					//'Users','Settings',
						        					#$disnchk 		= (in_array($pageinfo[0],$alwys_show)) ? " CHECKED DISABLED" : '';
						        					#$disnchk_admin 	=  ? " CHECKED DISABLED" : '';
						        					
						        					if (in_array($pageinfo[0],$alwys_show)) {
									        				$form1 = "
									        				<input type='hidden' name='hys_options[heyyou_menu_{$ke}]' value='1' />
									        				<input type='checkbox' name='dumby' value='1' CHECKED DISABLED />";
									        		} else {
									        				$form1 = "<input type='checkbox' name='hys_options[heyyou_menu_{$ke}]' value='1' ".
									        						chckchckbox(@$hys['settings']['heyyou_menu_'.$ke])." />";
									        		}
									        		
						        					if (in_array($pageinfo[0],$if_subadmin) || in_array($pageinfo[0],$alwys_show)) {
									        				$form2 = "
									        				<input type='hidden' name='hys_options[subadmin_menu_{$ke}]' value='1' />
									        				<input type='checkbox' name='dumby' value='1' CHECKED DISABLED />";
									        		} else {
									        				$form2 = "<input type='checkbox' name='hys_options[subadmin_menu_{$ke}]' value='1' ".
									        						chckchckbox(@$hys['settings']['heyyou_menu_'.$ke])." />";
									        		}
									        				
					        						echo "
									        		<tr>
									        			<td style='text-align:right;padding:0;margin:0;'>
															{$pageinfo[0]}<br />
									        			</td>
									        			<td style='padding:0;margin:0;text-align:center;'>
															{$form1}
									        			</td>
									        			<td style='padding:0;margin:0;text-align:center;'>
									        				{$form2}
									        			</td>
									        		</tr>\n";
					        					}
				        					//}
				        				}
				        				
				        			?>
				        	</table>	
				  </td>
				  </tr>
				  <tr>
<td>
				      
				        	<br />
					<h4 style='padding-top:0;margin-top:0'>Add the following options to <em>heyyou</em>'s page configuration tab:</h4>
		        	<label>
		        	<input type='checkbox' name='hys_options[show_opt_hide]' value='1' <?php 
		        		echo chckchckbox(@$hys['settings']['show_opt_hide'])
		        		?> /> Hide <em>heyyou</em> Output <span class='hys_description'>(prim. for if using templates that utilize heyyou)</span>
		        	</label><br />
		        	
		        	<label>
		        	<input type='checkbox' name='hys_options[show_opt_title]' value='1' <?php 
		        		echo chckchckbox(@$hys['settings']['show_opt_title'])
		        		?> /> Include <em>heyyou</em> Title
		        	</label><br />
		        	
		       			<? hys_space() ?>

				    <label>
		        	<input type='checkbox' name='hys_options[show_opt_titlehide]' value='1' <?php 
		        		echo chckchckbox(@$hys['settings']['show_opt_titlehide'])
		        		?> /> Disable/hide Wp's Page Title <span class='hys_description'>(on selected sites page)</span>
		        	</label><br />
		        	
		        	<label>
		        	<input type='checkbox' name='hys_options[show_opt_blurbhide]' value='1' <?php 
		        		echo chckchckbox(@$hys['settings']['show_opt_blurbhide'])
		        		?> /> Disable/hide Wp's Page Content <span class='hys_description'>(main textarea content)</span>
		        	</label><br />
		        	
		        	<label>
		        	<input type='checkbox' name='hys_options[show_opt_sec_blurb]' value='1' <?php 
		        		echo chckchckbox(@$hys['settings']['show_opt_sec_blurb'])
		        		?> /> Secondary Blurb <span class='hys_description'>(ie: two colounms)</span>
		        	</label><br />
		        	
		        	<label>
		        	 &nbsp; &nbsp;  <input type='checkbox' name='hys_options[secondary_blurbs]' value='1' <?php 
		        		echo chckchckbox(@$hys['settings']['secondary_blurbs'])
		        		?> /> Default Secondary Blurbs <span class='hys_description'>enabled on all pages</span>
		        	</label><br />

		        	<label <?= (@$hys['settings']['show_opt_sec_blurb'] == 1) ? '' : " style='display:none;'" ?>>
		        	   <? $hys['settings']['secondary_blurb_title'] = (empty($hys['settings']['secondary_blurb_title'])) ? 'Secondary Blurb' : $hys['settings']['secondary_blurb_title']; ?>
		        	 &nbsp; &nbsp;  <input type='text' style='color: #999;' name='hys_options[secondary_blurb_title]' value='<?= @$hys['settings']['secondary_blurb_title'] ?>'  /> 
		        	 	<span class='hys_description'>- Title of textarea (ie: Right Column)</span><br />
		        	</label>
		        	
		        	<label>
		        	<input type='checkbox' name='hys_options[show_opt_fb]' value='1' <?php 
		        		echo chckchckbox(@$hys['settings']['show_opt_fb'])
		        		?> /> Facebook "Like" button
		        	</label><br />
		        	
		        	<label>
		        	<input type='checkbox' name='hys_options[show_opt_tw]' value='1' <?php 
		        		echo chckchckbox(@$hys['settings']['show_opt_tw'])
		        		?> /> Share on Twitter button
		        	</label><br />
		        	
		       			<? hys_space() ?>
		        	
		        	<label>
		        	<input type='checkbox' name='hys_options[show_opt_banner]' value='1' <?php 
		        		echo chckchckbox(@$hys['settings']['show_opt_banner'])
		        		?> /> Banner URL <span class='hys_description'>(alt. to Featured Image)</span>
		        	</label><br />
		        	<label> &nbsp;&nbsp;&nbsp;&nbsp;
		        	<input type='checkbox' name='hys_options[show_opt_banner_credit]' value='1' <?php 
		        		echo chckchckbox(@$hys['settings']['show_opt_banner_credit'])
		        		?> /> Banner Image Credit
		        	</label><br />

		        	<label>
		        	<input type='checkbox' name='hys_options[show_opt_color]' value='1' <?php 
		        		echo chckchckbox(@$hys['settings']['show_opt_color'])
		        		?> /> Main Hexidecimal Color
		        	</label><br />

		        	<label>
		        	<input type='checkbox' name='hys_options[show_opt_sec_color]' value='1' <?php 
		        		echo chckchckbox(@$hys['settings']['show_opt_sec_color'])
		        		?> /> Secondary Hexidecimal Color
		        	</label><br />
		        	
		        	
		       			<? hys_space() ?>
		        	
		        	
		        	<label>
		        	<input type='checkbox' name='hys_options[show_pg_img]' value='1' <?php 
		        		echo chckchckbox(@$hys['settings']['show_pg_img'])
		        		?> /> Add image gallery/attachments to page
		        	</label><br />
		        	
		        	<label>
		        	<input type='checkbox' name='hys_options[attach_use_titles]' value='1' <?php 
		        		echo chckchckbox(@$hys['settings']['attach_use_titles'])
		        		?> /> Enable <b>Title</b>s in Attachments Plugin
		        	</label><br />
		        	
		        	<label>
		        	<input type='checkbox' name='hys_options[attach_disable-hys_photo_gallery]' value='1' <?php 
		        		echo chckchckbox(@$hys['settings']['attach_disable-hys_photo_gallery'])
		        		?> /> Disable autoplacement of "Page Photo Gallery" <span class='hys_description'>*for custom themes</span>
		        	</label><br />


				</td>
				<td valign=top>
				    <?

	$main_metaboxes_post = array(
		'postcustom' 		=> 'Custom Fields',
		'postexcerpt' 		=> 'Excerpt',
		'commentstatusdiv' 	=> 'Comments',
		'trackbacksdiv' 	=> 'Talkback',
		'slugdiv' 			=> 'Slug',
		'authordiv' 		=> 'Author'
	);
	$main_metaboxes_pages = array(
		'postcustom' 		=> 'Custom Fields',
		'postexcerpt' 		=> 'Excerpt',
		'commentstatusdiv' 	=> 'Comments',
		'commentsdiv' 		=> 'Comments',
		'trackbacksdiv' 	=> 'Talkback',
		'slugdiv' 			=> 'Slug',
		'authordiv' 		=> 'Author',
	);
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
				    ?>
				    <br />
					<h4 style='padding-top:0;margin-top:0'>Default MetaBoxes:</h4>
				       	<table cellpadding="0" cellspacing="0" style=''>
				       		<tr>
				       		<td style="width:50%;" valign="top">
				        	<span class='hys_description'>Wordpress Posts:</span><br />
					        	<?
					        		$diabledlist = array('slugdiv');
					        		foreach ($main_metaboxes_post as $widget=>$widget_name) {
					        		
					        		?>
					        			<label>
							        	&nbsp; &nbsp; <input type='checkbox' name='hys_options[widget_post_<?=$widget?>]' value='1' <?php 
							        		if (in_array($widget,$diabledlist)) {
							        			echo " CHECKED disabled='disabled'/> <span class='hys_description'>{$widget_name}</span>";
							        		} else {
								        		echo chckchckbox(@$hys['settings']['widget_post_'.$widget]);
								        		echo "/> {$widget_name}<span class='hys_description'><!--{$widget}--></span>";
								        	}
							        		?> 
							        	</label><br />
					        	<?	}
					        	?>
				        	</td>
				        	<td valign="top">
					        	<span class='hys_description'>Wordpress Pages:</span><br />
					        	<?
					        		foreach ($main_metaboxes_pages as $widget=>$widget_name) {?>
					        			<label>
							        	&nbsp; &nbsp; <input type='checkbox' name='hys_options[widget_page_<?=$widget?>]' value='1' <?php 
							        		if (in_array($widget,$diabledlist)) {
							        			echo " CHECKED disabled='disabled'/> <span class='hys_description'>{$widget_name}</span>";
							        		} else {
								        		echo chckchckbox(@$hys['settings']['widget_post_'.$widget]);
								        		echo "/> {$widget_name}<span class='hys_description'><!--{$widget}--></span>";
								        	}
							        		?>
							        	</label><br />
					        	<?	}
					        	?>
				        	</td>
				       	</tr>
				       		<tr>
				       		<td valign="top">
				        	<span class='hys_description'>Dashboard (Side):</span><br />
					        	<?
					        		foreach ($dash_metaboxes_side as $widget=>$widget_name) {?>
					        			<label>
							        	&nbsp; &nbsp; <input type='checkbox' name='hys_options[<?=$widget?>]' value='1' <?php 
							        		if (in_array($widget,$diabledlist)) {
							        			echo " CHECKED disabled='disabled'/> <span class='hys_description'>{$widget_name}</span>";
							        		} else {
								        		echo chckchckbox(@$hys['settings'][$widget]);
								        		echo "/> {$widget_name}<span class='hys_description'><!--{$widget}--></span>";
								        	}
							        		?>
							        	</label><br />
					        	<?	}
					        	?>
				        	</td>
				       		<td valign="top">
				        	<span class='hys_description'>Dashbrd (Norm):</span><br />
					        	<?
					        		foreach ($dash_metaboxes_norm as $widget=>$widget_name) {?>
					        			<label>
							        	&nbsp; &nbsp; <input type='checkbox' name='hys_options[<?=$widget?>]' value='1' <?php 
							        		if (in_array($widget,$diabledlist)) {
							        			echo " CHECKED disabled='disabled'/> <span class='hys_description'>{$widget_name}</span>";
							        		} else {
								        		echo chckchckbox(@$hys['settings'][$widget]);
								        		echo "/> {$widget_name}<span class='hys_description'><!--{$widget}--></span>";
								        	}
							        		?>
							        	</label><br />
					        	<?	}
					        	?>
				        	</td>
				       	</tr>
				       </table> 
				</td>
			</tr>
		</table>
    </td>
    </tr>
    
    
    
	        
    
    
	       	<tr>
	       		<td colspan=2>
	       			<hr />
	       		</td>
	       	</tr>
	        
	        
	        
	        <tr valign="top">
	        <th scope="row"  valign=top>
	        	<h3>Site Meta Feilds</h3>
	        </th>
	        <td valign=middle><br />
			<?php
			
			$field_types = @$hys['metatypes'];
			
			
			for ($i = 0; $i != 15; $i++) {
				$vis = ($i == 0 || !isset($hys['settings']['meta'][$i])) ? "block": "none";
				$vis = (isset($hys['settings']['meta'][$i]) && !empty($hys['settings']['meta'][$i])) ? "block": $vis;
				$vis = (isset($hys['settings']['meta'][$i])) ? $vis: "none";
				$vis = ($i == 0) ? "block" : $vis;
			
				$hys['settings']['meta'] = (isset($hys['settings']['meta'])) ? $hys['settings']['meta'] : array();
				
				if (is_array($hys['settings']['meta'])) {

					$typedd = '';
					foreach ($field_types as $fieldname) {
						$selt = @(strtolower($fieldname) == $hys['settings']['meta_type'][$i]) ? " selected='selected'": '';
						$typedd .= "<option value='".strtolower($fieldname)."'{$selt}>{$fieldname}</option>";
					}

					$hys['settings']['meta'][$i] = (isset($hys['settings']['meta'][$i])) ? $hys['settings']['meta'][$i]: '';
					$hys['settings']['meta_blurb'][$i] = (isset($hys['settings']['meta_blurb'][$i])) ? $hys['settings']['meta_blurb'][$i]: '';
					
					
					$spacing = " style='padding:0 10px 4px 0;margin:0;'";
					echo "
					<div id='hys_meta_{$i}' style='display:{$vis};padding-bottom:15px;'>
					
					<!-- ############## {$i} ################ -->
					<table cellpadding=0 cellspacing=0 style='paddin:0;margin:0;'>
						<tr>
							<td{$spacing}>Meta Feild:</td>
							<td{$spacing}>
								<input 
									type='text' 
									name='hys_options[meta][{$i}]' 
									value='{$hys['settings']['meta'][$i]}'
									size='15' class='code'
								/>
							</td>
						</tr>
						<tr>
							<td{$spacing}>Meta Type:</td>
							<td{$spacing}>
								<select name='hys_options[meta_type][{$i}]' style='width:75px;'>
									{$typedd}
								</select>
							</td>
						</tr>
						<tr>
							<td{$spacing}>Instructions:</td>
							<td{$spacing}>
								<input 
									type='text' 
									name='hys_options[meta_blurb][{$i}]' 
									value='{$hys['settings']['meta_blurb'][$i]}'
									size='15' class=''
								/> <span class='hys_description'>for field information (like this)</span>
							</td>
						</tr>
						<tr>
							<td{$spacing}>Use in Theme:</td>
							<td{$spacing}>
								<input type='text' readonly='readonly' class='text urlfield code' value=\"&lt;?php \$hys['hys_page_config']['meta_".hys_url_friendly($hys['settings']['meta'][$i])."'] ?&gt;\" style='width:290px;font-size:10px;' />
							</td>
						</tr>
					</table>
					";
					if ($i != 14 && empty($hys['settings']['meta'][($i+1)])) {
					  echo "<br /><a class='hys_fake_link'  id='hys_meta_{$i}_link' onclick=\"showhide('hys_meta_{$i}_link'); showhide('hys_meta_".($i+1)."')\" >add..</a>";
					}
					echo "</div>
					<!-- ############## END {$i} ################ -->\n\n\n";
					
				}
			}
			?>
			<span class='hys_description'>For adding additional fields to heyyou page config, "Page" options (tab): </span>
	        </td>
	        </tr>
    
    
    
    
    
	       	
	       	
	       	
	       	<tr>
	       		<td colspan=2>
	       			<hr />
	       		</td>
	       	</tr>
	        
	        
	        
	        <tr valign="top">
	        <th scope="row"  valign=top>
	        	<h3>Backup</h3>
	        </th>
	        <td valign=middle><br />
	        	<div style="margin:0 0 10px 0;">
	        		<a href="admin.php?page=heyyou/_functions.php&backup_now=true&message=backup" class='button' style='margin: 5px 0;'>Backup Database &amp; WP Now!</a><br />
	        	</div>
		        <div class='description'>
					note: the use of mysqldump may require <code>open_basedir</code>. allowing this weakens your servers security.<br />
					note: backup emails are <u><b>not</b></u> sent will SSL or any other encryption. all information in backup is susceptible to interception.<br />
					note: storing .sql files on the server is not concidered "safe practice".<br />
				</div>
	        </td>
	        </tr>
	        
	        
	        <tr valign="top">
	        <th scope="row">Automated Backups:</th>
	        <td valign=center>
	        
	        	<?php 
	        		$backupon = (@$hys['settings']['backup_onoff'] == 'on') ? 1 : 0;
	        	?>

	        	<label><input type="radio" name="hys_options[backup_onoff]" value="off" <?= ($backupon != 1 ) ? ' CHECKED' : ''; ?> /> Off</label><br />
	        	<label><input type="radio" name="hys_options[backup_onoff]" value="on"  <?= ($backupon == 1 ) ? ' CHECKED' : ''; ?> /> On: 
	        (an email with attached <code>{database}.SQL</code> file will be sent, &amp; stored on the server) </label>
		        <div class='description'>
					note: automated backup and sending is done via cron jobs. server configurment must allow Wordpress cronjobs.<br />
				</div>
	        <br />
	        </td>
	        </tr>
	        
	        
	        
	        <tr valign="top">
	        <th scope="row">
	        	Backup Interval:
	        </th>
	        <td valign=middle>
	        	<select name="hys_options[backup_period]">
					<?
						$backup = array(
							'',
							'daily',
							'weekly',
							'monthly',
							'quarterly',
							'biyearly',
							'yearly',
						);
						foreach ($backup as $period) {
							$presl = ($period == @$hys['settings']['backup_period']) ? " selected='selected'": '';
							echo "<option{$presl}>{$period}</option>";
						}
						
						$adminemail = (!isset($hys['settings']['backup_to']) || empty($hys['settings']['backup_to'])) ? get_bloginfo('admin_email') : $hys['settings']['backup_to'];
					?>
	        	</select>
	        	<input type='hidden' name='backup_period_currently' value='<?= @$hys['settings']['backup_period'] ?>' />
	        </td>
	        </tr>
	        
	        
	        <tr valign="top">
	        <th scope="row">
	        	Using <code>/mysqldump</code> located in: 
	        </th>
	        <td valign=middle>
	        	<input type="text" name="hys_options[backup_from]" value="<?php echo @$hys['settings']['backup_from'] ?>" style='width:230px;' class='code' />  <span class="hys_description"><code>/usr/dir/bin</code> *no trailing slash</span>
	        </td>
	        </tr>
	        
	        
	        
	        <tr valign="top">
	        <th scope="row">
	        	Send backup email to:
	        </th>
	        <td valign=middle>
	        	<input type="text" name="hys_options[backup_to]" value="<?php echo $adminemail ?>" style='width:230px;' class='code' /> <span class="hys_description">you may use <code>backups@hey-you.ca</code></span>
	        </td>
	        </tr>
	        
	        
	        <tr valign="top">
	        <th scope="row">
	        	Exisiting Backups:
	        </th>
	        <td valign=middle>

				<?php
				$backup_folder 	= 'hys_sql_backups/'.$hys['site'];
				$backup_dir 	= WP_CONTENT_DIR.'/'.$backup_folder;
				$numodbackups 	= 0;
				$listoffiles	= '';
				if (is_dir($backup_dir)) { 
					if ($handle = opendir($backup_dir)) {
					    while (false !== ($file = readdir($handle))) {
					        if ($file != "." && $file != ".." && $file != "index.php") {
								$file_name	= $hys['site'];
					        	
					        	$dateoffile = str_replace(array($file_name.'-sql_','.sql'),'',$file);
					        	$dateoffile = explode('_',$dateoffile);
					        	$dateoffile = $dateoffile[0].' '.str_replace('-',':',$dateoffile[1]);
					        	$dateoffile = date('Y, F j - H:i:s',strtotime($dateoffile));
					        	
								$fsize = filesize($backup_dir.'/'.$file);
								$fsize = round($fsize / 1048576, 2);
					        	
					        	
					            $listoffiles .= " &nbsp; &gt; <a href='".WP_CONTENT_URL."/{$backup_folder}/{$file}' class='afile'>{$dateoffile} &nbsp; ($fsize mb)</a><br />";
	
					            $numodbackups++;
					        }
					    }
					    closedir($handle);
					}
				} else {
					$listoffiles = "<div>- there are currently no backups -</div>";
				}
				?>

	        	<div class='hys_list_backup_files' <? echo ($listoffiles > 8) ? " style='max-height: 200px;overflow-y: scroll;' " : ''; ?>>
	        	IN <? echo str_replace(get_bloginfo('url'),'',WP_CONTENT_URL.'/'.$backup_folder.'/..') ?><br />
	        	-------------------------------------------------<br />
	        	<?= $listoffiles ?>
	        	-------------------------------------------------<br />
				<br />
				</div>
				<?php if ($numodbackups > 0) { ?>
				<a href='admin.php?page=heyyou/_functions.php&settings-updated=true&deleteallbackups=true&message=backupsdeleted' class='button' style='margin:-12px 0 0 13px;z-index:999;float:left;'>Delete (<?= $numodbackups; ?>) backups</a>
				<?php } ?>
	        </td>
	        </tr>
	        
	        
    <?php 
	if (!$a_admin) {
	?>
			</table>
		</div>
			<table class="form-table">
	<?php
	} //endif($a_admin) 
	?>			  
		
	        <tr valign="top">
	        <th colspan="2"><hr /><h3>Banner(s):</h3></td>
	        </tr>
	        
	        <tr valign="top">
	        <th scope="row" style=''>Banner Options(s):</th>
	        <td>
	        	<label>
	        	<input type='checkbox' name='hys_options[undercon]' value='1' <?php 
	        		echo chckchckbox(@$hys['settings']['undercon'])
	        		?> /> Show custom banner
	        	</label><br />
	        	&nbsp; &nbsp; &nbsp; 
	        	<label>
	        	<input type='checkbox' name='hys_options[undercon_reveal]' value='1' <?php 
	        		if (isset($hys['settings']['undercon_reveal']))
	        		echo chckchckbox($hys['settings']['undercon_reveal'])
	        		?> /> Auto reveal the message (no show/hide toggling)
	        	</label><br />
	        	
    	<?php if (!$a_admin) { ?>
				<div style='display:none'>
    	<? } ?>
	        	&nbsp; &nbsp; &nbsp; 
	        	<label>
	        	<input type='checkbox' name='hys_options[undercon_sess]' value='1' <?php 
	        		if (isset($hys['settings']['undercon_sess']))
	        		echo chckchckbox($hys['settings']['undercon_sess'])
	        		?> /> Show $_SESSION in banner (for developing)
	        	</label><br />
	        	&nbsp; &nbsp; &nbsp; 
	        	<label>
	        	<input type='checkbox' name='hys_options[undercon_cook]' value='1' <?php 
	        		if (isset($hys['settings']['undercon_cook']))
	        		echo chckchckbox($hys['settings']['undercon_cook'])
	        		?> /> Show $_COOKIE in banner (for developing)
	        	</label><br />
	        	<label>
	        	<input type='checkbox' name='hys_options[ie6msg]' value='1' <?php 
	        		if (isset($hys['settings']['ie6msg']))
	        		echo chckchckbox($hys['settings']['ie6msg'])
	        		?> /> Show IE6 debunker banner (when viewed with IE6)
	        	</label>
	    <?php 
	    	if (!$a_admin) {
	    		echo "</div>";
	    	} //endif($a_admin) 
	    ?>
	        </td>
	        </tr>
	        <tr valign="top">
	        <th scope="row" style=''>Custom banner title and message:</th>
	        <td>

	        	Custom banner title and message:<br />
	        	<input type="text" name="hys_options[undercontit]" 
	        		value="<?php echo $hys['settings']['undercontit']; ?>" style='color: #333 !important;
						font-family: Courier;
						font-size: 14px;
						background: #fcf3bb' /><br />
	        	<textarea name='hys_options[underconmsg]' style='
	        			width:600px;height:50px;
	        			color: #333 !important;
						font-family: Courier;
						font-size: 14px;
						padding: 30px 10px 10px 10px;
						background: #fcf3bb url(<?= $hys['dir'] ?>/res/imgs/underconbanner.png) repeat-x top '><?php echo $hys['settings']['underconmsg'] ?></textarea>
	        </td>
	        </tr>
	        
	        
	        <tr valign="top">
	        <th colspan="2"><hr /><h3>Document/Page Settings</h3></td>
	        </tr>
	        
	        
	        <tr valign="top">
	        <th scope="row">&lt;META&gt; keywords:</th>
	        <td><input type="text" name="hys_options[meta_keywords]" 
	        		value="<?php echo @$hys['settings']['meta_keywords']; ?>" style='width:400px !important;' class='code'><br />
	        		<span class='hys_description'>3-5 keyword/phrases seperate with coma "<code>keyphrase, keyphrase, ect</code>"</span></td>
	        </tr>
	        <tr valign="top">
	        <th scope="row">&lt;META&gt; description:</th>
	        <td><textarea name="hys_options[meta_description]" class="code" style='width:400px !important;height:60px !important;'><?php 
	        		echo @$hys['settings']['meta_description']; ?></textarea><br />
	        		<span class='hys_description'>limit to 150-200 characters.</td>
	        </tr>
	        
    <?php 
	if (!$a_admin) {
	?>
			</table>
		<div style='display:none;'>
			<table class="form-table">
	<?php
	} //endif($a_admin) 
	?>		
	      
	        <tr valign="top">
	        <th scope="row"># to break more/less at:</th>
	        <td><input type="text" name="hys_options[moreless]" 
	        		value="<?php echo @$hys['settings']['moreless']; ?>" size=4 class='code'>characters</td>
	        </tr>
	        
	        
	        <tr valign="top">
	        <th scope="row">mobile viewport width:</th>
	        <td><input type="text" name="hys_options[viewport]" 
	        		value="<?php echo @$hys['settings']['viewport']; ?>" size=4 class='code'>px</td>
	        </tr>
	        
	        
	        <tr valign="top">
	        <th scope="row">ID's exempt from navigation: </th>
	        <td><input type="text" name="hys_options[navview]" 
	        		value="<?php echo @$hys['settings']['navview']; ?>" size=10 class='code'><span class='hys_description'>*seperate with coma: 1,2,3..</span></td>
	        </tr>
	        
	        
	        <tr valign="top">
	        <th scope="row">Tutorial Page ID: </th>
	        <td><input type="text" name="hys_options[tutid]" 
	        		value="<?php echo @$hys['settings']['tutid']; ?>" size=4 class='code'><span class='hys_description'>*page ID: visible to <em>hys_client</em> in live site, but not in admin. To get ID: edit page &amp; extract from URL<br /> <code>http://.../wp-admin/post.php?post=<span style='text-decoration:underline;padding:0 2px;'>524</span>&action=edit</span></code></td>
	        </tr>
	        
	        
	        
	        <tr valign="top">
	        <th colspan="2"><hr /><h3>Output Texts</h3></td>
	        </tr>
	        
	        
	        
	        <tr valign="top">
	        <th scope="row">More/less text:</th>
	        <td>
	        	<input type="text" name="hys_options[more]" 
	        			value="<?php echo @$hys['settings']['more']; ?>" 
	        			class=''> <span class='hys_description'>(more)</span><br />
	        	<input type="text" name="hys_options[less]" value="<?php 
	        		echo $hys['settings']['less']; 
	        		?>" class=''> <span class='hys_description'>(less)</span>
	        </td>
	        </tr>
	
	
	        <tr valign="top">
	        <th scope="row">Pagination "Pages" text:</th>
	        <td>
	        	<input type="text" name="hys_options[pages]" value="<?php 
	        			echo @$hys['settings']['pages']; 
	        	?>" class='' /> 
	        	<span class='hys_description'>"<code>Pages:</code> 1 | 2 | 3 | ..."</span><br />
	        	<div style='height:7px'></div>
	        	<label>
	        	<input type='checkbox' name='hys_options[pages_sep]' value='1' <?php 
	        		echo chckchckbox(@$hys['settings']['pages_sep'])
	        		?> /> Show seperator "<code>|</code>" between page numbers
	        	</label>
	        </td>
	        </tr>
	        
	        
	        
	        <tr valign="top">
	        <th scope="row">View lightbox gallery link:</th>
	        <td>
	        	<input type="text" name="hys_options[lightbox_gallery_link]" value="<?php 
	        			echo @$hys['settings']['lightbox_gallery_link']; 
	        	?>" class='' /> 
	        	<span class='hys_description'>"<code>%lightbox_gallery%</code> = <code>View Gallery:</code></span><br />
	        </td>
	        </tr>
	        
	        <tr valign="top">
	        <th scope="row">Download Hi/ Low Res Text:</th>
	        <td>
	        	<input type="text" name="hys_options[text_hi_res]" value="<?php 
	        			echo @$hys['settings']['text_hi_res']; 
	        	?>" class='' /> 
	        	<span class='hys_description'>"hi res"</span><br />
	        	
	        	<input type="text" name="hys_options[text_low_res]" value="<?php 
	        			echo @$hys['settings']['text_low_res']; 
	        	?>" class='' /> 
	        	<span class='hys_description'>"low res"</span><br />
	        </td>
	        </tr>
	        
	        <tr valign="top">
	        <th scope="row">"Back" text:</th>
	        <td>
	        	<input type="text" name="hys_options[back]" value="<?php 
	        			echo htmlentities($hys['settings']['back']); 
	        	?>" class='' /> 
	        	<span class='hys_description'>"<code>&lt; Back</code>"</span><br />
	        	<div style='height:7px'></div>
	        </td>
	        </tr>
	        
	        
	        
	        
	        <tr valign="top">
	        <th scope="row">Text Seporators/lines:</th>
	        <td>
	        	<input type="text" name="hys_options[line1]" value="<?php 
	        			echo @$hys['settings']['line1']; 
	        	?>" class='' /> 
	        	<span class='hys_description'>"<code>&lt;hr /&gt;</code>", 
	        	"<code>&lt;div&gt;.....&lt;/div&gt;</code>", ect</span><br />
	        	<div style='height:7px'></div>
	        	<input type="text" name="hys_options[line2]" value="<?php 
	        			echo @$hys['settings']['line2']; 
	        	?>" class='' /> 
	        	<span class='hys_description'>"<code>&lt;hr /&gt;</code>", 
	        	"<code>&lt;div&gt;.....&lt;/div&gt;</code>", ect</span><br />
	        	<div style='height:7px'></div>
	        </td>
	        </tr>
	        
	        <tr valign="top">
	        <th scope="row">feature search result text:</th>
	        <td><input type="text" name="hys_options[search_text]" 
	        		value="<?php echo @$hys['settings']['search_text']; ?>"></td>
	        </tr>
	      
	        <tr valign="top">
	        <th colspan="2"><hr /><h3>TinyMCE</h3></td>
	        </tr>
	        
	        <tr valign="top">
	        <th scope="row">TinyMCE CSS:</th>
	        <td>
				<?php
					for ($i = 0; $i != 10; $i++) {
						$vis = ($i == 0 || !isset($hys['settings']['tinymce_css'][$i])) ? "block": "none";
						$vis = (isset($hys['settings']['tinymce_css'][$i]) && !empty($hys['settings']['tinymce_css'][$i])) ? "block": $vis;
						$vis = (isset($hys['settings']['tinymce_css'][$i])) ? $vis: "none";
						$vis = ($i == 0) ? "block" : $vis;		
						$hys['settings']['tinymce_css'] = (isset($hys['settings']['tinymce_css'])) ? $hys['settings']['tinymce_css'] : array();
						if (is_array($hys['settings']['tinymce_css'])) {
							if ( !term_exists($hys['settings']['tinymce_css'][$i],'mediacategory') )
								wp_insert_term($hys['settings']['tinymce_css'][$i],'mediacategory');
							$hys['settings']['tinymce_css'][$i] = (isset($hys['settings']['tinymce_css'][$i])) ? $hys['settings']['tinymce_css'][$i]: '';
							echo "
							<div id='hys_tinymcecss_{$i}' style='display:{$vis};'>
							<code>.</code><label style='display:inline;'>
								<input 
									type='text' 
									name='hys_options[tinymce_css][{$i}]' 
									value='{$hys['settings']['tinymce_css'][$i]}'
									size=15
									class='code'
								/>
								</label> ";
							echo ($i==0) ? "<span class='hys_description'>text only - don't include <code>#</code>id or class element identifies</span>" : '';
							if ($i != 14 && empty($hys['settings']['tinymce_css'][($i+1)])) {
							  echo "<br /><a class='hys_fake_link'  id='hys_tinymcecss_{$i}_link' 
							   onclick=\"showhide('hys_tinymcecss_{$i}_link'); showhide('hys_tinymcecss_".($i+1)."')\" 
							  >add..</a>";
							}
							echo "</div>";
						}
					}
				?>
			</td>
			</tr>
			
			
			
	        <tr valign="top">
	        <th colspan="2"><hr /><h3>Media Library</h3></td>
	        </tr>
			<tr>
	        	<td valign=top>
	        		Display Media Items as:
	        	</td>
				<td>
					<? $media_layout = (@$hys['settings']['media_layout'] == 'list') ? 1 : 0; ?>
					<label><input type="radio" name="hys_options[media_layout]" value="grid" <?= ($media_layout != 1 ) ? ' CHECKED' : ''; ?> /> Thumb Tile Grid </label><br />
					<label><input type="radio" name="hys_options[media_layout]" value="list"  <?= ($media_layout == 1 ) ? ' CHECKED' : ''; ?> /> Text List <span class="description">recommended for >70 images</span>
				</td>
			</tr>
			
			<tr>
	        	<td valign=top>
	        		heyyou Library:
	        	</td>
				<td>
					<label>
					<input type='checkbox' name='hys_options[dont_use_heyyou_media_library]' value='1' <?php 
	        			if (isset($hys['settings']['dont_use_heyyou_media_library']))
		        		echo chckchckbox($hys['settings']['dont_use_heyyou_media_library'])
	    	    		?> />  Dont use <em>heyyou</em> media library<br /> 
	    	    		&nbsp; &nbsp; &nbsp;<span class="description">(reverts to core Wordpress Media Library)</span>
	        		</label><br />
	        	</td>
			</tr>
			
			
				      
    <?php 
	if (!$a_admin) {
	?>
			</table>
		</div>
			<table class="form-table">
	<?php
	} //endif($a_admin) 
	?>		
	
	    </table>
	    <hr />
	    <p class="submit">
	    <input type="submit" class="button-primary" value="<?php _e('Save All Changes') ?>" />
	    </p>
	
	</form>
	</div>
	<?php 
	}

?>