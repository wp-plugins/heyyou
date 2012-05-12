<?php


/*====================================================================================================================
   // !hys_metabox - main config
--------------------------------------------------------------------------------------------------------------------*/

/*-------------------------------------------------------------
 Name:      hys_metabox

 Purpose:   
 Receive:   - none -
 Return:	- none -
-------------------------------------------------------------*/
	function hys_metabox() {
		global $hys;
		
		if ( current_user_can('manage_options') ) {
			add_meta_box( 'myplugin_sectionid', 'heyyou Page Configuration', 'hys_metabox_output', 'page','side');
			add_meta_box( 'hys_metabox_page_options', 'heyyou Page Options', 'hys_metabox_page_options_output', 'page','side');

			if (@$hys['hys_page_config']['sec_blurb'] == 1 || @$hys['settings']['secondary_blurbs'] == 1) {
				$hys['settings']['secondary_blurb_title'] = (empty($hys['settings']['secondary_blurb_title'])) ? 'Secondary Blurb' : $hys['settings']['secondary_blurb_title'];
				add_meta_box( 'secondary_blurb_box_id', __( $hys['settings']['secondary_blurb_title'], 'heyyou_sec_blurb' ), 'secondary_blurb_box', 'page' );
			}
		}
	}

/*-------------------------------------------------------------
 Name:      hys_metabox_save

 Purpose:   save custom metabox
 Receive:   - none -
 Return:	- none -
-------------------------------------------------------------*/
	function hys_metabox_save( $post_id ) {
		global $hys;
	
		// verify this came from the our screen and we want it and we have permission

		if (!isset($_POST['hys_noncename']) || 
		    !wp_verify_nonce( $_POST['hys_noncename'],'aunqiuehysid' ))
			return $post_id;
#		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) 
#			return $post_id;  
		if ( 'page' == $_POST['post_type'] ) {
		if ( !current_user_can( 'edit_page', $post_id ) )
			return $post_id;
		}

		
		if ( current_user_can('manage_options')) {
			
			//lets see if a preset has been used.. or if a preset has been customized
			$saved_config 		= $_POST['hys_page_config'];
			$selected_config 	= $_POST['hys_page_config']['preset'];
			$its_custom 		= false;
	
			//if the dropdown has selected a preset
			if ($selected_config != '' && $selected_config != 'custom') {
				
				$selected_config_array = @unserialize($hys['presets'][$selected_config]);
				
				//if the page is already the selected config
				if (trim($_POST['current_config']) == trim($selected_config)) {
					//remove stupid feilds
					if (isset($saved_config['meta'])) 				unset($saved_config['meta']);
					if (isset($saved_config['format'])) 			unset($saved_config['format']);
					if (isset($selected_config_array['meta'])) 		unset($selected_config_array['meta']);
					if (isset($selected_config_array['format'])) 	unset($selected_config_array['format']);
					
					//compare the configs to see if anything's different
					#$compar_configs = array_diff($saved_config, $selected_config_array);
					$differnt = false; $whatsdiff = array();
					foreach ($saved_config as $key => $value) {
						if (!isset($selected_config_array[$key])) {
							$differnt = true;
							#$whatsdiff[] = "!isset: \$selected_config_array[{$key}]";
						}
						if ($value != $selected_config_array[$key]) {
							$differnt = true;
							#$whatsdiff[] = "\$saved_config[{$key}] != \$selected_config_array[{$key}]";
						}
					}
					foreach ($selected_config_array as $key => $value) {
						if (!isset($saved_config[$key])) {
							$differnt = true;
							#$whatsdiff[] = "!isset: \$saved_config[{$key}]";
						}
						if ($value != $saved_config[$key]) {
							$differnt = true;
							#$whatsdiff[] = "\$selected_config_array[{$key}] != \$saved_config[{$key}]";
						}
					}
					
					if ($differnt) {
						//there is a difference, so it's now custom
						$_POST['hys_page_config']['preset'] = 'custom';
						$its_custom = true;
					}
				}
				
				//if it's not custom, set config to preset
				if (!$its_custom)
					$_POST['hys_page_config'] = $selected_config_array;
			}
			
			$post_id = intval($_POST['post_ID']);
	
			//we're authenticated: insert meta
			$meta_name = 'hys_page_config'; //an array of the heyyou page configuration..		
			add_post_meta($post_id, $meta_name, $_POST[$meta_name], true) 
			or update_post_meta($post_id, $meta_name, $_POST[$meta_name]);
			
			//save the right-side text if applic...
			if (isset($_POST['secondary_blurb'])) {
				update_post_meta($post_id,'secondary_blurb',$_POST['secondary_blurb']) or
				add_post_meta($post_id,'secondary_blurb',$_POST['secondary_blurb']);
			}
		}
		
		return;		
	}

/*-------------------------------------------------------------
 Name:      hys_metabox_output

 Purpose:   Prints the options for the page, and the page feature
 			options, all fields, name="hys_page_config[x]"
 			where x = the field name, ie: "page title"
 			the values later become "$hys['hys_page_config'][x]"
 Receive:   - none -
 Return:	- none -
-------------------------------------------------------------*/
	function hys_metabox_page_options_output() {
		global $hys;
		?>
		<div class='hys_metabox_output'>
			<!-- hide output -->
				<? if(@$hys['settings']['show_opt_hide'] == 1):?>
							<label>
								<input type='checkbox' name='hys_page_config[hideoutput]' value=1 
								<?php 
									if (isset($hys['hys_page_config']['hideoutput'])) 
									  echo chckchckbox($hys['hys_page_config']['hideoutput']); 
								?> /> 
								Hide <em>heyyou</em> output
							</label>
				<? endif; ?>

			<!--  Hide Title -->
				<? if(@$hys['settings']['show_opt_titlehide'] == 1): ?>
							<label>
								<input type='checkbox' name='hys_page_config[hidetitle]' value=1 
								<?php 
									if (isset($hys['hys_page_config']['hidetitle'])) 
									  echo chckchckbox($hys['hys_page_config']['hidetitle']); 
								?> /> 
								Hide Page Title On Site
							</label>
				<? endif; ?>
				<? if(@$hys['settings']['show_opt_blurbhide'] == 1): ?>
							<label>
								<input type='checkbox' name='hys_page_config[hideblurb]' value=1 
								<?php 
									if (isset($hys['hys_page_config']['hideblurb'])) 
									  echo chckchckbox($hys['hys_page_config']['hideblurb']); 
								?> /> 
								Disable page main blurb
							</label>
				<? endif; ?>
				<? if(@$hys['settings']['show_opt_sec_blurb'] == 1 || @$hys['settings']['secondary_blurbs'] == 1): ?>
							<label>
								<input type='checkbox' name='hys_page_config[sec_blurb]' value=1 <?= @($hys['settings']['secondary_blurbs'] == 1) ? "CHECKED disabled='disabled'" : "" ?>
								<?php 
									if (isset($hys['hys_page_config']['sec_blurb'])) 
									  echo chckchckbox($hys['hys_page_config']['sec_blurb']); 
								?> /> 
								<?= @($hys['settings']['secondary_blurbs'] == 1) ? "<span style='color:#666'>" : ''; ?>
								Add secondary Blurb
								<?= @($hys['settings']['secondary_blurbs'] == 1) ? "</span>" : ''; ?>
							</label>
				<? endif; ?>
				
		       			<? hys_space() ?>


			<!-- Facebook -->
				<? if(@$hys['settings']['show_opt_fb'] == 1):?>
							<label>
								<input type='checkbox' name='hys_page_config[facebooklike]' value=1 
								<?php 
									if (isset($hys['hys_page_config']['facebooklike'])) 
									  echo chckchckbox($hys['hys_page_config']['facebooklike']); 
								?> /> 
								Add Facebook "Like" Button
							</label>
				<? endif; ?>
			<!-- Twitter -->
				<? if(@$hys['settings']['show_opt_tw'] == 1):?>
							<label>
								<input type='checkbox' name='hys_page_config[twitter]' value=1 
								<?php 
									if (isset($hys['hys_page_config']['twitter'])) 
									  echo chckchckbox($hys['hys_page_config']['twitter']); 
								?> /> 
								Add Twitter "Tweet" Button
							</label>
				<? endif; ?>
			<!-- Google + -->
				<? if(@$hys['settings']['show_opt_gp'] == 1):?>
							<label>
								<input type='checkbox' name='hys_page_config[googleplus]' value=1 
								<?php 
									if (isset($hys['hys_page_config']['googleplus'])) 
									  echo chckchckbox($hys['hys_page_config']['googleplus']); 
								?> /> 
								Add Google+ Share Button
							</label>
				<? endif; ?>
				
		       			<? hys_space() ?>
				
				
				<? if(@$hys['settings']['lightbox'] == 1):?>
							<label>
								<input type='checkbox' name='hys_page_config[disable_lightbox]' value=1 
								<?php 
									if (isset($hys['hys_page_config']['disable_lightbox'])) 
									  echo chckchckbox($hys['hys_page_config']['disable_lightbox']); 
								?> /> 
								Disable Lightbox scripts on this page
							</label>
				<? endif; ?>

			<!-- Page Photo/Image Gallery (attachments plugin) -->
				<? if(@$hys['settings']['show_pg_img'] == 1 && @$hys['settings']['no_attachments'] != 1):
					
				?>
							<label>
								<input type='checkbox' name='hys_page_config[show_pg_img]' value=1 
								<?php 
									if (isset($hys['hys_page_config']['show_pg_img'])) 
									  echo chckchckbox($hys['hys_page_config']['show_pg_img']); 
								?> /> 
								Add Photo Gallery to Page
							</label>
							
							<label title='Turn off auto placement: of the image gallery. Instead use custom theme or %gallery% token in output format'>
								&nbsp;&nbsp;&nbsp;&nbsp;  <input type='checkbox' name='hys_page_config[show_pg_img_autoplaceoff]' value=1 
								<?php 
									if (isset($hys['hys_page_config']['show_pg_img_autoplaceoff'])) 
									  echo chckchckbox($hys['hys_page_config']['show_pg_img_autoplaceoff']); 
								?> /> 
								 Turn <b>off</b> auto placement of Gallery
							</label>							
				<? endif;  ?>
				
				
		       			<? hys_space() ?>
				
						<table cellpadding="1" cellspacing="2" border="0" style='width:100%;'>
						
						
			<!-- FEATURE TITLE -->
				<? if(@$hys['settings']['show_opt_title'] == 1):?>
							<tr>
								<td valign="top">
									<label for="hys_title">heyyou Title:</label> 
								</td>
								<td>
									<input type='text' name='hys_page_config[title]' id='hys_title'
										value='<?= @$hys['hys_page_config']['title'] ?>' style='width:150px' />
								</td>
							</tr>
				<? endif; ?>
												
			<!-- BANNER -->
				<? if(@$hys['settings']['show_opt_banner'] == 1):?>
							<tr>
								<td valign="top">
									<label for="banner_credit">Banner (URL):</label> 
								</td>
								<td>
								 <select name='hys_page_config[banner]' style='width:150px'> 
								 	<?= hys_listmedia( @$hys['hys_page_config']['banner'], 10); ?>
								 </select>
								</td>
							</tr>
				<? endif; ?>
							
			<!-- Credit -->
				<? if(@$hys['settings']['show_opt_banner_credit'] == 1):?>
							<tr>
								<td valign="top">
									<label for="banner_credit">Photo Credit:</label> 
								</td>
								<td>
									<input type="text" id="banner_credit" name="hys_usrpg_config[banner_credit]" value="<?= @$banner_credit ?>" style='width:150px' />
								</td>
							</tr>
				<? endif; ?>
							
			<!-- Main Color -->
				<? if(@$hys['settings']['show_opt_color'] == 1):?>
							<tr>
								<td valign="top">
									<label for="color">Main color:</label> 
								</td>
								<td>
									<code>#</code><input type='text' name='hys_page_config[color]' id='color' value='<?= @$hys['hys_page_config']['color']; ?>' class='code' size='6' maxlength='6' />
									  <label>
										&nbsp; &nbsp; <input type='checkbox' name='hys_page_config[ignorcolor]' value=1 
										<?php 
											if (isset($hys['hys_page_config']['ignorcolor'])) 
											  echo chckchckbox($hys['hys_page_config']['ignorcolor']); 
										?> /> 
										ignore in <code>#content</code>
									</label>
								</td>
							</tr>
				<? endif; ?>
				
				
			<!-- Secondary Color -->
				<? if(@$hys['settings']['show_opt_sec_color'] == 1):?>
							<tr>
								<td valign="top">
									<label for="sec_color">Secondary color:</label> 
								</td>
								<td>
									<code>#</code><input type='text' name='hys_page_config[sec_color]' id='sec_color' value='<?= @$hys['hys_page_config']['sec_color']; ?>' class='code' size='6' maxlength='6' />
								</td>
							</tr>
				<? endif; ?>
				
				<tr>
				<td colspan=2><h4></h4><br /></td>
				</tr>
				
				
			<!-- Site meta fields -->
				<? if (is_array(@$hys['settings']['meta'])) : ?>

						<?
						$numofmeta = count($hys['settings']['meta']);
				
						foreach ($hys['settings']['meta'] as $k => $sitemetavalue) {
							if (!empty($hys['settings']['meta'][$k])) {
							
							
								$mname = strtolower($hys['settings']['meta'][$k]);
								$mtype = @$hys['settings']['meta_type'][$k];
								$vmeta = @$hys['hys_page_config']['meta_'.hys_url_friendly($sitemetavalue)];
								$fname = "hys_page_config[meta_".hys_url_friendly($sitemetavalue)."]";
								$minfo = @$hys['settings']['meta_blurb'][$k];
								  
								if (strpos($mname,'(media)') || $mtype == 'media') {
									$metafeild = "<select name='{$fname}'>".hys_listmedia($vmeta,10)."</select>";
								}
								elseif ($mtype == 'page') {
									$metafeild = "<select name='{$fname}'>";
									$metafeild .= "<option></option><optgroup label=\"Page\">";
										$pagess = get_pages();
										foreach ($pagess as $apage) {
											$preselpg = ($vmeta == $apage->ID) ? " selected='selected'" : '';
											$metafeild .= "<option value='{$apage->ID}'{$preselpg}>".hys_chopstring($apage->post_title,25)."</option>";
										}
									$metafeild .= "</optgroup>";
									$metafeild .= "</select>";
								}
								elseif (strpos($mname,'(blurb)') || $mtype == 'blurb') {
									$metafeild = "<textarea name='{$fname}' style='height:75px !important;font-size:10px;'>{$vmeta}</textarea>";
								}
								elseif (strpos($mname,'(code)')  || $mtype == 'code'){
									$metafeild = "<textarea name='{$fname}' class='code' style='height:75px !important;font-size:10px;'>{$vmeta}</textarea>";
								}
								elseif (strpos($mname,'(checkbox)')  || $mtype == 'checkbox'){
									$metafeild = "<input type='checkbox' value='1' name='{$fname}' ".chckchckbox($vmeta)." />";
								}			
								elseif (strpos($mname,'(url)')  || $mtype == 'url') {
									$vmeta = (empty($vmeta)) ? 'http://' : $vmeta;
									$metafeild = "<input type='text' name='{$fname}' value='{$vmeta}' style='width:150px' class='code hys_url_meta_feild' id='url_feild_{$k}' ".
												 "onblur='change_url_color(0, \"url_feild_{$k}\")' onfocus='change_url_color(1, \"url_feild_{$k}\")' /> ";
												 												 
								}
								else {
									$metafeild = "<input type='text' name='{$fname}' value='{$vmeta}' style='width:150px' />";
								}
								
								//if textarea, utilize space with colspan=2
								if ((strpos($mname,'(blurb)') || $mtype == 'blurb') || (strpos($mname,'(code)')  || $mtype == 'code')) {
									echo "<tr> <td colspan=2> ".str_replace(':','',$sitemetavalue).":<br />";
								} else {
									echo  "<tr>"."<td valign=top>".$sitemetavalue.":</td>"."<td>";
								}
							
							
								echo "<!--<input name='hys_page_config[meta_".hys_url_friendly($sitemetavalue)."]' type='text' value='".$vmeta."'  />-->{$metafeild}";
								echo (!empty($minfo)) ? "<div class='description hys_meta_feild_instructions'>{$minfo}</div>" : '';
								echo "</td></tr>";
							}
						} 
						echo "</table>";
						
						?>
			<!-- Site meta fields -->
		</div><!--/hys_metabox_output-->
				<? endif;

	}
	
	
	
	
	
	
	
	function hys_metabox_output() {	
		global $post,$hys,$current_user;
		
		// get user type..
		get_currentuserinfo();		
		$ahysuser = (isset($current_user->push_capabilities['heyshauna']) && $current_user->push_capabilities['heyshauna'] == 1) ? 1 : 0;
		
		// Use nonce for verification on the form
		echo '<input type="hidden" name="hys_noncename" id="hys_noncename" value="' . 
				wp_create_nonce( 'aunqiuehysid' ) . '" />';		
		/*
		echo "<pre>
\$hys['config']          = {$hys['config']}
\$hys['hys_page_config'] = {$hys['hys_page_config']}
		</pre>";
		/**/
		?>
		
		<div class='hys_metabox_output'>
	
		<!-- ## TuRN ON/OFF HEYYOU ## -->						
				<?php
					$presets = array(
						'posts' => 'Posts',
						'faq' 	=> 'FAQ',
						'img' 	=> 'Image Gallery',
						#'links' => 'Links',
						#'bios'  => 'Biographies',
					);
					$its_a_preset 	= false;
					$custom			= '';
					$selcted 		= " selected='selected'";
					$ddrown 		= '';
					$curconfig 		= $hys['hys_page_config'];
					
					foreach ($presets as $va => $na) {
						$thisconfig = @unserialize($hys['presets'][$va]);
						$presl = '';
						if (is_array($curconfig) && is_array($thisconfig)) {
							$compar_configs = array_diff($curconfig, $thisconfig);
							if (count($compar_configs) < 1) {
								$presl = $selcted;
								$its_a_preset = true;
							}
						}
						$ddrown .= "<option value='{$va}'{$presl}>{$na}</option>";
					}
					
					if (is_array($curconfig) && isset($hys['presets']['blank']) && is_array(unserialize($hys['presets']['blank']))) {
						$compar_configs = array_diff($curconfig, unserialize($hys['presets']['blank']));
						if (count($compar_configs) < 1 || $its_a_preset) {
							// it's blank or it's a present
						} else {
							// it's a custom confige
							$custom = $selcted;
						}
					}
				
					
				?>				
				<input type='hidden' name='current_config' value='<?= $hys['config'] ?>' />
		
		<script type='text/javascript'>
			function heyyoutab(id) {				
				document.getElementById(id).style.display='block'
				document.getElementById(id+'_link').className='heyyou_tab_current'
				
				for(i = 1; i != 6; i++) {
					thisid = "heyyou_tab_"+i
					if (thisid != id) {
						document.getElementById(thisid).style.display='none'
						document.getElementById(thisid+'_link').className='heyyou_tab_inactive'
					}
				}
			}
		</script>
<? if ($hys['user'] == 'heyyou_client') { echo "<div style='display:none;'>"; } ?>
		<ul class='heyyou_tabs'>
			<li><div class='sidecell'>&nbsp;</div></li>
			<li id='heyyou_tab_1_link' class='heyyou_tab_current'><a onclick='heyyoutab("heyyou_tab_1")'>Page</a></li>
			<?php 
				$tabs = array(
					2 => 'Features',
					3 => 'Meta',
					4 => 'Format',
					5 => 'I/O'
				);
				foreach ($tabs as $k => $tab) {
					$disabled = @((empty($hys['hys_page_config']) || empty($hys['hys_page_config']['preset'])) && $k != 5) 
						? "class='disabled_tab' title='heyyou must be enabled to use these tabs' " : "onclick='heyyoutab(\"heyyou_tab_{$k}\")'";
						echo "<li id='heyyou_tab_{$k}_link'><a {$disabled}>{$tab}</a></li>";
				}
			?>
			<li><div class='sidecell'>&nbsp;</div></li>
		</ul>
<? if ($hys['user'] == 'heyyou_client') { echo "</div><!--/display:none;-->"; } ?>
	
	<!-- ====================================== -->
	<!-- ====================================== -->
	<!-- ====================================== -->
	<div id="heyyou_tab_1" style='display:block;'><!-- -->

<? if ($hys['user'] == 'heyyou_client') { echo "<div style='display:none;'>"; } ?>
		<h4>heyyou posts<!--configuration--></h4>
				
				<?php /*
		  		<label>
		  			<select name='hys_page_config[preset]'>
		  				<optgroup label="HEYYOU CONFIGURATION">
		  					<option value='0'><!-- DISABLE HEYYOU --></option>
		  				</optgroup>
		  				<!--<optgroup label="PRESETS">
		  					<?=$ddrown?>
		  				</optgroup>-->
		  				<optgroup label="CUSTOM">
		  					<option value='custom' <?= $custom ?>>Custom configuration</option>
		  				</optgroup>
		  			</select>		  		
				</label>
				*/ ?>
				
				<label style='padding:4px 0 0 0;'><input type='radio' name='hys_page_config[preset]' value='' <?php echo (@$hys['hys_page_config']['preset'] == '') ? "CHECKED ": ''; ?> onclick='this.form.submit();'> disabled</label>
				<label style='padding:2px 0 5px 0;'><input type='radio' name='hys_page_config[preset]' value='custom' <?php echo (@$hys['hys_page_config']['preset'] == 'custom') ? "CHECKED ": ''; ?> onclick='this.form.submit();'> enabled</label>

<? if ($hys['user'] == 'heyyou_client') { echo "</div><!--/display:none;-->"; } ?>
								

							
			
		
	</div><!--/heyyou_tab_1-->
	<!-- ====================================== -->
	<!-- ====================================== -->
	<!-- ====================================== -->
	<div id="heyyou_tab_2" style='display:none;'>
		
		

		<!-- ## FEATURE OPTIONS ## -->
			<h4>heyyou Features</h4>
			
			<!-- USE THE TITLE -->
				<label>
					<input type='checkbox' name='' disabled='disabled' CHECKED /> Use Title
				</label>
						
					<!-- add anchor navigation -->
						<label>
							&nbsp;&nbsp;&nbsp;&nbsp; <input type='checkbox' name='hys_page_config[custom_title]' value=1 
							<?php 
								if (isset($hys['hys_page_config']['custom_title'])) 
								  echo chckchckbox($hys['hys_page_config']['custom_title']); 
							?> /> 
							Chng label from <code>Title</code> to <input type='text' name='hys_page_config[custom_title_alt]' style='margin-top:0px;'
								  value='<?php echo (!isset($hys['hys_page_config']['custom_title_alt']) || empty($hys['hys_page_config']['custom_title_alt'])) ? "Name" : $hys['hys_page_config']['custom_title_alt']; ?>' 
								  size=3 />
						</label>						

					<!-- add anchor navigation -->
						<label>
							&nbsp;&nbsp;&nbsp;&nbsp; <input type='checkbox' name='hys_page_config[anchors]' value=1 
							<?php 
								if (isset($hys['hys_page_config']['anchors'])) 
								  echo chckchckbox($hys['hys_page_config']['anchors']); 
							?> /> 
							Add anchor navigation/jump-to's
						</label>
		
					<!-- add anchor navigation -->
						<label>
							&nbsp;&nbsp;&nbsp;&nbsp; <input type='checkbox' name='hys_page_config[numanchors]' value=1 
							<?php 
								if (isset($hys['hys_page_config']['numanchors'])) 
								  echo chckchckbox($hys['hys_page_config']['numanchors']); 
							?> /> 
							Make posts (&amp; anchor) numeric
						</label>	
						
			<!-- USE THE DATE -->
				<label>
					<input type='checkbox' name='hys_page_config[include_date]' value=1 <?php 
						if (isset($hys['hys_page_config']['include_date'])) 
							echo chckchckbox($hys['hys_page_config']['include_date']); 
					?> /> Use Date
				</label>

			<!-- USE THE BLURB -->
				<label>
					<input type='checkbox' name='hys_page_config[include_blurb]' value=1 <?php 
						if (isset($hys['hys_page_config']['include_blurb'])) 
							echo chckchckbox($hys['hys_page_config']['include_blurb']); 
					?> /> Use Blurb
				</label>
				
					<!-- show/hide content (toggel visiblity w title) -->
						<label>
							&nbsp;&nbsp;&nbsp;&nbsp; <input type='checkbox' name='hys_page_config[hidecontent]' value=1 
							<?php 
								if (isset($hys['hys_page_config']['hidecontent'])) 
								  echo chckchckbox($hys['hys_page_config']['hidecontent']); 
							?> /> 
							Auto-Collaps/Hide Blurb (%blurb%)
						</label>
						
					<!-- show/hide content (toggel visiblity w title) -->
						<label>
							&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;  <input type='checkbox' name='hys_page_config[hidecontent_notitlelink]' value=1 
							<?php 
								if (isset($hys['hys_page_config']['hidecontent_notitlelink'])) 
								  echo chckchckbox($hys['hys_page_config']['hidecontent_notitlelink']); 
							?> /> 
							Don't use %title% as reveal link
						</label>

			<!-- USE CATEGORIES -->
				<label>
					<input type='checkbox' name='hys_page_config[include_cats]' value=1 <?php 
						if (isset($hys['hys_page_config']['include_cats'])) 
							echo chckchckbox($hys['hys_page_config']['include_cats']); 
					?> /> Use Categories
				</label>
				
				
			<!-- USE ATTACHMENTS -->
				<? if (@$hys['settings']['no_attachments'] != 1) : ?>
				<label>
					<input type='checkbox' name='hys_page_config[include_attach]' value=1 <?php 
						if (isset($hys['hys_page_config']['include_attach'])) 
							echo chckchckbox($hys['hys_page_config']['include_attach']); 
					?> /> Use Images Attachments
				</label>	
						
						<label title='Checking this add the images TITLE and CAPTION under the image on the page'>
						&nbsp; &nbsp; &nbsp; 
							<input type='checkbox' name='hys_page_config[show_pg_img_printlabel]' value=1 
							<?php 
								if (isset($hys['hys_page_config']['show_pg_img_printlabel'])) 
								  echo chckchckbox($hys['hys_page_config']['show_pg_img_printlabel']); 
							?> /> 
							 Print title+caption labels
						</label>
				
					<!-- USE ATTACHMENTS AS IMAGE GALLERY -->
						<label>
							&nbsp; &nbsp; &nbsp; 
							<input type='checkbox' name='hys_page_config[attachments_gallery]' value=1 
							<?php 
								if (isset($hys['hys_page_config']['attachments_gallery'])) 
								  echo chckchckbox($hys['hys_page_config']['attachments_gallery']); 
							?> /> 
							Use Attachments as Image Gallery
						</label>
												
					
					<!-- AUTO PALCE ATTACHMENTS -->
						<label title='Turn off auto placement: instead use a custom theme or the token %attach% in the HTML output format.'>
							&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; 
							<input type='checkbox' name='hys_page_config[autoattach]' value=1 
							<?php 
								if (isset($hys['hys_page_config']['autoattach'])) 
								  echo chckchckbox($hys['hys_page_config']['autoattach']); 
							?> /> 
							Turn <b>off</b> auto placement
						</label>
						
					<!-- AUTO PALCE ATTACHMENTS -->
						<label>
							&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; 
							<input type='checkbox' name='hys_page_config[downloadattach]' value=1 
							<?php 
								if (isset($hys['hys_page_config']['downloadattach'])) 
								  echo chckchckbox($hys['hys_page_config']['downloadattach']); 
							?> /> 
							Add hi/low res downloads
						</label>

					<!-- IMAGE SIZE: -->
						<label>
							&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Image Size:
						<select name='hys_page_config[img_size]' id='img_size'>
							<option value='full'>full size</option>
							<?php 
								$sizes = array('thumbnails','medium','large','full');
								foreach ($sizes as $size) {
									$preselect = (isset($hys['hys_page_config']['img_size']) && !empty($hys['hys_page_config']['img_size'])) ? $hys['hys_page_config']['img_size'] : 'large';
									$sel = ($size == $preselect) ? " selected='selected'": '';
									echo "<option{$sel}>{$size}</option>";
								}
							?>
						</select>
						</label>
			<? endif ?>
				
			<!-- single page -->
				<label>
					<input type='checkbox' name='hys_page_config[single]' value=1 disabled='disabled' CHECKED
					<?php
						// we want this always on now that  'single_altr_morelesslink' determins if %blurb% will be altered
						#if (isset($hys['hys_page_config']['single'])) 
						#  echo chckchckbox($hys['hys_page_config']['single']); 
					?> /> 
					Enable Single Pages ("hypg") for posts
				</label>
				
						<!-- change %blurb% to link to single -->
							<label>
								&nbsp; &nbsp; &nbsp; <input type='checkbox' name='hys_page_config[single_altr_morelesslink]' value=1 
								<?php 
									if (isset($hys['hys_page_config']['single_altr_morelesslink'])) 
									  echo chckchckbox($hys['hys_page_config']['single_altr_morelesslink']); 
								?> /> 
								Cut %blurb% @ <span class='code'>&lt;!more&gt;</span>: link to hypg
							</label>
			
						<!--  Use more/less in  -->	
							<label>
								&nbsp; &nbsp; &nbsp; <input type='checkbox' name='hys_page_config[single_moreless]' value=1 
								<?php 
									  echo chckchckbox(@$hys['hys_page_config']['single_moreless']); 
								?> /> 
								Use more/less in hypg
							</label>				
									
						<!--  Hide Title Single -->
							<label>
								&nbsp; &nbsp; &nbsp; <input type='checkbox' name='hys_page_config[hidetitlesingle]' value=1 
								<?php 
									  echo chckchckbox(@$hys['hys_page_config']['hidetitlesingle']); 
								?> /> 
								Hide the page title on hypg
							</label>
														
						<!--  Hide Title Single -->
						<? if (@$hys['settings']['no_attachments'] != 1) : ?>
							<label>
								&nbsp; &nbsp; &nbsp; <input type='checkbox' name='hys_page_config[single_showattchinpage]' value=1 
								<?php 
									  echo chckchckbox(@$hys['hys_page_config']['single_showattchinpage']); 
								?> /> 
								Show attachments on hypg (not in list)
							</label>
						<? endif; ?>
				<? hys_space() ?>
				<h4>heyyou, list settings</h4>
					<!-- New posts positioning: -->
					<label>
						New posts get added to the:
					<select name='hys_page_config[newposts_toporbottom]' id='newposts_toporbottom'>
						<?php 
							$tb = array('top','bottom');
							foreach ($tb as $torb) {
								$sel = ($torb == @$hys['hys_page_config']['newposts_toporbottom']) ? " selected='selected'": '';
								echo "<option{$sel}>{$torb}</option>";
							}
						?>
					</select>
					</label>
					
					<?
						$optns = '';
						for ($i = 0; $i != 15; $i++) {
							if (isset($hys['hys_page_config']['meta'][$i]) && !empty($hys['hys_page_config']['meta'][$i]) && ($hys['hys_page_config']['meta_type'][$i] == 'media')) {
								$sel = (hys_url_friendly($hys['hys_page_config']['meta'][$i]) == $hys['hys_page_config']['meta_thumb_preview']) ? " selected='selected'": '';
								$optns .= "<option value='".hys_url_friendly($hys['hys_page_config']['meta'][$i])."'{$sel}>".$hys['hys_page_config']['meta'][$i]."</option>";
							}
						}
					  if (!empty($optns)) { ?> 
						Show thumbnail preview of 
						<select name='hys_page_config[meta_thumb_preview]' id='newposts_toporbottom'>
							<option></option>
							<?= $optns ?>
						</select>
					<? } ?>
					
	</div><!--/heyyou_tab_2-->
	<!-- ====================================== -->
	<!-- ====================================== -->
	<!-- ====================================== -->
	<div id="heyyou_tab_3" style='display:none;'>
			<h4>heyyou meta fields</h4>
			<?php
			
			$field_types = $hys['metatypes'];
			
			for ($i = 0; $i != 15; $i++) {
				$vis = ($i == 0 || !isset($hys['hys_page_config']['meta'][$i])) ? "block": "none";
				$vis = (isset($hys['hys_page_config']['meta'][$i]) && !empty($hys['hys_page_config']['meta'][$i])) ? "block": $vis;
				$vis = (isset($hys['hys_page_config']['meta'][$i])) ? $vis: "none";
				$vis = ($i == 0) ? "block" : $vis;

				$hys['hys_page_config']['meta'] = (isset($hys['hys_page_config']['meta'])) ? $hys['hys_page_config']['meta'] : array();
				
				if (is_array($hys['hys_page_config']['meta'])) {
					$typedd = '';
					foreach ($field_types as $fieldname) {
						$selt = @(strtolower($fieldname) == $hys['hys_page_config']['meta_type'][$i]) ? " selected='selected'": '';
						$typedd .= "<option value='".strtolower($fieldname)."'{$selt}>{$fieldname}</option>";
					}
					
					$hys['hys_page_config']['meta'][$i] = (isset($hys['hys_page_config']['meta'][$i])) ? $hys['hys_page_config']['meta'][$i]: '';
					echo "
					<div id='hys_meta_{$i}' style='display:{$vis};'>
					<label style='display:inline;'>
						<!--{$i} {$hys['hys_page_config']['meta'][$i]}<br />-->
						<input 
							type='text' 
							name='hys_page_config[meta][{$i}]' 
							value='{$hys['hys_page_config']['meta'][$i]}'
							size=33
							class='code'
							style='width:260px;'
						/></label> 
						
						<select name='hys_page_config[meta_type][{$i}]' style='width:75px;'>
							{$typedd}
						</select>
						
						<input type='text' readonly='readonly' class='text urlfield code' value='%".hys_url_friendly($hys['hys_page_config']['meta'][$i])."%' style='width:175px;font-size:10px;' />
						
						<div style='height:12px;'><!--space--></div>";
					
					if ($i != 14 && empty($hys['hys_page_config']['meta'][($i+1)])) {
					  echo "<a class='hys_fake_link'  id='hys_meta_{$i}_link' style='display:block;'
					   onclick=\"showhide('hys_meta_{$i}_link'); showhide('hys_meta_".($i+1)."')\" 
					  >[+ add another]</a>";
					}
					
					echo "</div>";
					
				}
			}
			?>
<div class="hys_help">
<h4>help/key</h4>
<div class='description' style='padding:4px 0;'>Add any of the following "<code>(*)</code>" list below to a meta field name to turn it from a standard text field, into a different input type:</div>
<textarea readonly='readonly' class='text urlfield code' style='background:#eee; font-size:10px; height:60px !important; '>
(URL)   = url field
(Media) = dropdown list of media
(Blurb)	= textarea
</textarea>
</div><!--/hys_help-->
	</div><!--/heyyou_tab_3-->
	<div id="heyyou_tab_4" style='display:none;'>
		
			<h4>Add Lines</h4>
			<!-- ADD LINE BEFORE FEATURE -->
				<label>
					<input type='checkbox' name='hys_page_config[line_before_list]' value=1 
					<?php 
						if (isset($hys['hys_page_config']['line_before_list'])) 
						  echo chckchckbox($hys['hys_page_config']['line_before_list']); 
					?> /> 
					Add primary line <em>before</em> heyyou list
				</label>
			<!-- LINE BETWEEN Cats -->
				<label>
					<input type='checkbox' name='hys_page_config[line_between_cats]' value=1 
						<?php if (isset($hys['hys_page_config']['line_between_cats'])) 
							echo chckchckbox($hys['hys_page_config']['line_between_cats']); 
						?> 
					/> Add secondary line <em>between</em> categories
				</label>
			<!-- LINE BETWEEN POSTINGS -->
				<label>
					<input type='checkbox' name='hys_page_config[line_between_list]' value=1 
						<?php if (isset($hys['hys_page_config']['line_between_list'])) 
							echo chckchckbox($hys['hys_page_config']['line_between_list']); 
						?> 
					/> Add secondary line <em>between</em> <strong>posts</strong>
				</label>
			<!-- ADD LINE AFTER FEATURE -->
				<label>
					<input type='checkbox' name='hys_page_config[line_after_list]' value=1 
					<?php 
						if (isset($hys['hys_page_config']['line_after_list'])) 
						  echo chckchckbox($hys['hys_page_config']['line_after_list']); 
					?> /> 
					Add primary line <em>after</em> heyyou list
				</label>

			<h4>Pagination</h4>
						<table cellpadding="1" cellspacing="2" border="0">
			<!-- SHOW X PER PAGE -->
							<tr>
								<td valign="top">
									<label for="title">Paginate:</label> 
								</td>
								<td>
								<input type='text' name='hys_page_config[perpage]' 
								  value='<?php echo @$hys['hys_page_config']['perpage'] ?>' 
								  size=1 />  per (page / cat), using: <br />
								  <?php $hys['hys_page_config']['paginatemthod'] = @(empty($hys['hys_page_config']['paginatemthod'])) ? 'pages' : $hys['hys_page_config']['paginatemthod']; ?>
								<label style='padding:4px 0 0 0;'><input type='radio' name='hys_page_config[paginatemthod]' value='pages' <?php echo ($hys['hys_page_config']['paginatemthod'] == 'pages') ? "CHECKED ": ''; ?>> Pages</label>
								<label style='padding:2px 0 5px 0;'><input type='radio' name='hys_page_config[paginatemthod]' value='moreless' <?php echo ($hys['hys_page_config']['paginatemthod'] == 'moreless') ? "CHECKED ": ''; ?>> more/less reveal</label>
								</td>
							</tr>
			<!-- Pagination Text -->
							<tr>
								<td valign="top">
									<label for="pagination_text">Pages Text:</label> 
								</td>
								<td>
									<input type='text' name='hys_page_config[pagination_text]' id='pagination_text'
								value='<?= @$hys['hys_page_config']['pagination_text'] ?>' />
							<span class='description'><code>Pages:</code></span>
								</td>
							</tr>
						</table>
				
			<h4>HTML Output Formats</h4>
			<?
				$ba_output = @$hys['hys_page_config']['before_heyyou'].@$hys['hys_page_config']['after_heyyou'];
			?>
			<h5><a class='<?=(!empty($ba_output)) ? "blacklink" : '' ?>' id='' onclick="showhide('beforeafter_output_format');" >Before / After heyyou Output</a></h5>
			<div id='beforeafter_output_format' <?=(empty($ba_output)) ? "style='display:none;'" : '' ?>>
				<textarea name='hys_page_config[before_heyyou]'  class='code' style='width:100%;height:44px !important;font-size:10px;'><?php echo @$hys['hys_page_config']['before_heyyou'];?></textarea>
				<div>-</div>
				<textarea name='hys_page_config[after_heyyou]' class='code' style='width:100%;height:44px !important;font-size:10px;'><?php echo @$hys['hys_page_config']['after_heyyou'];?></textarea>
			<br />
			<br />
			</div><!--/beforeafter_output_format-->	
			
			
			<?
				$ba_output = @$hys['hys_page_config']['before_cats_heyyou'].@$hys['hys_page_config']['after_cats_heyyou'];
			?>
			
			<h5><a class='<?=(!empty($ba_output)) ? "blacklink" : '' ?>' id='' onclick="showhide('beforeaftercat_output_format');" >Before / After heyyou Categories</a></h5>
			<div id='beforeaftercat_output_format' <?=(empty($ba_output)) ? "style='display:none;'" : '' ?>>
				<textarea name='hys_page_config[before_cats_heyyou]'  class='code' style='width:100%;height:44px !important;font-size:10px;'><?php echo @$hys['hys_page_config']['before_cats_heyyou'];?></textarea>
				<div>-</div>
				<textarea name='hys_page_config[after_cats_heyyou]' class='code' style='width:100%;height:44px !important;font-size:10px;'><?php echo @$hys['hys_page_config']['after_cats_heyyou'];?></textarea>
			<br />
			<br />
			</div><!--/after_cats_heyyou-->			
			
					
			
			<?php  if (isset($hys['hys_page_config']['include_cats'])) {  ?>
			<h5><a class='<?=(!empty($hys['hys_page_config']['include_cats'])) ? "blacklink" : '' ?>' id='' onclick="showhide('cat_output_format');" >Category HTML output format</a></h5>
			
				<div id='cat_output_format' <?=(@$hys['hys_page_config']['include_cats'] != 1) ? "style='display:none;'" : '' ?>>
				<label>
						<textarea name='hys_page_config[cat_format]' 
										style='width:100%;height:44px !important;font-size:10px;'><?php 
				echo @$hys['hys_page_config']['cat_format'];
				?></textarea>
				</label>	
				
				<span class='oj'>&rarr;</span> <a class='hys_fake_link hys_readmore nouline view_tokens' id='' onclick="showhide('token_help_cat');" >%tokens%</a>
				<div id="token_help_cat" style="display:none; margin-top:5px;">
					<textarea readonly='readonly' class='text urlfield code disabled_textarea'>
%title% / %name% / %cat_title% / %cat%
%cat_blurb% / %blurb% / %description%
					</textarea>
				</div>
			<br />
			<br />
			</div><!--/cat_output_format-->
			<?php  }  ?>
			
			
			<!-- Blocks HTML Output format -->

					<h5><a class='<?=(!empty($hys['hys_page_config']['singleformat'])) ? "blacklink" : '' ?>' id='' onclick="showhide('single_output_format');" >Single Page (hypg) HTML output format</a></h5>
					<div id='single_output_format' <?=(@$hys['hys_page_config']['singleformat'] != 1) ? "style='display:none;'" : '' ?>>
					<textarea name='hys_page_config[singleformat]' class='code'
					  style='width:100%;height:300px;font-size:10px;'><?php 
					  echo @$hys['hys_page_config']['singleformat'];
					?></textarea>
				
				
				<span class='oj'>&rarr;</span> <a class='hys_fake_link hys_readmore nouline view_tokens' id='' onclick="showhide('token_help_sing');" >%tokens%</a>
				<div id="token_help_sing" style="display:none;">
					<textarea readonly='readonly' class='text urlfield code disabled_textarea'>
%id% / %ID%
%title% / %title:moreless%
%date%
%blurb%
%num%
%media% / %media:lightbox%
%line% / %line2%
%attach%
%attachments%
%lightbox_gallery%
%moreless:more% / %moreless:less% / %moreless%
%view_single% / %view_single_link%
%back%

--------------------
Extra Meta Tokens:
--------------------
<?php 
for ($i = 0; $i != 15; $i++) {
	if (isset($hys['hys_page_config']['meta'][$i]) && !empty($hys['hys_page_config']['meta'][$i])) {
		echo "%".hys_url_friendly($hys['hys_page_config']['meta'][$i])."%"."\n";
	}
}
?>
					</textarea>
				</div>
				
				</div><!--/single_output_format-->






			<h5>heyyou_post HTML output format</h5>
					<?php $format = @(empty($hys['hys_page_config']['format'])) ? "       <!-- CHANGE ME -->\n<!-- TO DESIRED HTML OUTPUT -->\n\n<!-- Delete everything in this textarea, and create a  output for your posts using HTML and tokens. If need help visit: {@link: http://hey-you.ca/tutorials/output_format/ } -->\n\n<b>%title%</b><br />\n// <span style='color:red;'>change the HTML output format</span> of this post in <code>wp-admin > pages > (this page) > heyyou > Format (Tab) > \"heyyou_post HTML output format\"</code>\n<br />\n<br />" : $hys['hys_page_config']['format']; ?>
					<textarea name='hys_page_config[format]' class='code'
					  style='width:100%;height:100px !important;font-size:10px;'><?php 
					  echo $format;
					?></textarea>
				
				<span class='oj'>&rarr;</span> <a class='hys_fake_link hys_readmore nouline view_tokens' id='' onclick="showhide('token_help_post');" >%tokens%</a>
				<div id="token_help_post" style="display:none; margin-top:5px;">
					<textarea readonly='readonly' class='text urlfield code disabled_textarea'>
%id% / %ID%
%title% / %title:moreless%
%date%
%blurb%
%num%
%media% / %media:lightbox%
%line% / %line2%
%attach%
%attachments%
%lightbox_gallery%
%more:moreless% / %less:moreless% / %moreless%
%view_single% / %view_single_link%

--------------------
Extra Meta Tokens:
--------------------
<?php 
for ($i = 0; $i != 15; $i++) {
	if (isset($hys['hys_page_config']['meta'][$i]) && !empty($hys['hys_page_config']['meta'][$i])) {
		echo "%".hys_url_friendly($hys['hys_page_config']['meta'][$i])."%"."\n";
	}
}
?>
					</textarea>
				</div>




			
	</div><!--/heyyou_tab_4-->
	<!-- ====================================== -->
	<!-- ====================================== -->
	<!-- ====================================== -->
	<div id="heyyou_tab_5" style='display:none;'>
		<h4>Import Configuration</h4>
						<textarea name='IMPORT_hys_page_config' style='width:100%;height:44px !important;font-size:10px;'></textarea>
		<h4>Export Configuration</h4>
			<textarea readonly='readonly' class='text urlfield code' style='background:#eee; font-size:10px; height:60px !important; '><? echo serialize($hys['hys_page_config']); ?></textarea>
	</div><!--/heyyou_tab_5-->
		
		
			<?php 
		
		echo "</div><!--/hys_metabox_output-->";
	}






























/*====================================================================================================================
   // !manage posts metabox
--------------------------------------------------------------------------------------------------------------------*/

/*-------------------------------------------------------------
 Name:      hys_metabox_mang

 Purpose:   
 Receive:   - none -
 Return:	- none -
-------------------------------------------------------------*/
	function hys_metabox_mang() {
		global $hys;
		if (isset($_GET['action']) && !empty($hys['config'])) {
	    	add_meta_box( 'hys_manage_metabox', 'Manage heyyou posts', 'hys_metabox_mang_output', 'page', 'advanced' );
		}
	}	

/*-------------------------------------------------------------
 Name:      hys_metabox_mang_output

 Purpose:   prints lists of posts and form for editing
 Receive:   - none -
 Return:	- none -
-------------------------------------------------------------*/
	function hys_metabox_mang_output() {	
		global $wpdb,$post,$hys;
		
		hys_pre_form_checks(); // check if deleteing, duplicated, toggeling, ect

		echo hys_gather_posts_for_managing(); // list all heyyou posts for that page
				
		echo hys_post_form(); // print the form for NEW heyyou posts
		
	}
	
	
/*-------------------------------------------------------------
 Name:      hys_pre_form_checks

 Purpose:   check if deleteing, duplicated, toggeling, ect
 Receive:   - none -
 Return:	- none -
-------------------------------------------------------------*/
	function hys_pre_form_checks() {
		global $hys;
		
		// if we're duplicating a post-
		if (isset($_GET['dbl_ftr']) && !empty($_GET['dbl_ftr'])) {
			$dblme = intval($_GET['dbl_ftr']);
			$getpost = get_post($dblme,'OBJECT');
			// Create post object
			$my_dbl_post = array(
				'post_type' 	=> 'hys_post',
				'post_status' 	=> 'publish',			
				'post_date' 	=> $getpost->post_date,
				'post_title' 	=> $getpost->post_title,
				'menu_order' 	=> ($getpost->menu_order + 1),
				'post_content' 	=> $getpost->post_content,
				'post_parent'	=> intval($_GET['post'])
			);
			// insert (or update) the post into the database
			$dbl_id = wp_insert_post( $my_dbl_post , false);
			hys_showhide_post($dbl_id, 1);
			
			//duplicate the meta feilds
			$cutm = get_post_custom($dblme);
			foreach ($cutm as $metaname => $metavalue) {
				if ($metaname == '_attachments') { //attachments have multiple..
					foreach ($metavalue as $encodedvalue)
						add_post_meta($dbl_id, $metaname, $encodedvalue) ;
				} else {
					add_post_meta($dbl_id, $metaname, unserialize($metavalue[0])) ;	 //@TODO change meta to custom to carrie all meta info over		
				}
			}						
		}
		
		
		// if DELETE hys_post
		if (isset($_GET['delete_ftr']) && !empty($_GET['delete_ftr']))
			wp_delete_post(intval($_GET['delete_ftr']));
				
				
		// if DELETE cat
		if (isset($_GET['delete_cat']) && !empty($_GET['delete_cat']))
			wp_delete_term( intval($_GET['delete_cat']), 'hys_post_cats' );
			
			
		// if TOGGEL VISIBILITY
		if (isset($_GET['unshow_ftr']) && !empty($_GET['unshow_ftr'])) 
			hys_showhide_post($_GET['unshow_ftr'], 0);
		if (isset($_GET['unhide_ftr']) && !empty($_GET['unhide_ftr'])) 
			hys_showhide_post($_GET['unhide_ftr'], 1);
		
	}
	
/*-------------------------------------------------------------
 Name:      hys_gather_posts_for_managing

 Purpose:   creates the queries for listing the categories and
 			uncategorized posts..
 Receive:   - none -
 Return:	- none -
-------------------------------------------------------------*/
	function hys_gather_posts_for_managing() {
	global $hys, $items_order;
		
		//get hys posts
		$ftr_query = array (
						'post_type' 	=> 'hys_post',
						'numberposts' 	=> '-1',
						'post_status' 	=> 'any',
						'orderby' 		=> 'menu_order',
						'order' 		=> 'ASC',
						'post_parent' 	=> intval($_GET['post'])
					);
		$get_feature_posts = get_posts($ftr_query);
		$get_feature_posts_copy = $get_feature_posts; //copy array
		
		// define vars
		$uri 		= "?post={$_GET['post']}&action=edit"; //the current url
		$count 		= 0;

		//get categories to sort by
		$get_taxonomies = get_taxonomies();
		$myterms 		= get_terms('hys_post_cats', 'orderby=count&hide_empty=0');			

		// if there are categories:
		if (isset($myterms)) {
		
			//find parent term (hys_post -> hys_post-xx [where xx = parent page_id]))
			foreach ($myterms as $k => $cat) {
				if ($cat->name == $hys['feature_code'])
					$parent_term_id = $cat->term_id;
			}
			
			$using_categories = (isset($hys['hys_page_config']['include_cats']) && $hys['hys_page_config']['include_cats'] == 1) ? true : false;
			
			if ($using_categories) {
				//run though the posts, build into an array for move up/down locating //@TODO simplify this since dropdrag
				$feature_post_arr = array();	
				foreach ($myterms as $k => $cat) { //cycle through cats	
					if ($cat->parent == $parent_term_id) { // if the cat is in the parent (the feature code)
						foreach($get_feature_posts_copy as $k => $f_post) {
							$custom_fields =  get_post_meta($f_post->ID, 'meta');	
							$custom_fields = (isset($custom_fields[0])) ? $custom_fields[0] : array();
							if (isset($custom_fields['hys_post_cat']) && $custom_fields['hys_post_cat'] == $cat->term_id) {
								$feature_post_arr[] = array(
									'id'	=> $f_post->ID,
									'tit'	=> $f_post->post_title,
									'order'	=> $f_post->menu_order);
								unset($get_feature_posts_copy[$k]); //to not be repeated in "uncategorized"
							}
						}
					}
				}
			}
			
			//get "uncategorized" posts
			foreach($get_feature_posts_copy as $k => $f_post) {
				$feature_post_arr[] = array('id'=>$f_post->ID,'order'=>$f_post->menu_order);
				unset($get_feature_posts_copy[$k]);
			}

			$cati = 0;
			$total_i = 0;
								
			//print cats if in $parent_term_id
			if ($using_categories) {
				foreach ($myterms as $k => $cat) {		
					if ($cat->parent == $parent_term_id) {
						$list_posts 		= hys_admin_list_posts_for_managing($get_feature_posts, $cat, $cati, $total_i);
						$get_feature_posts 	= $list_posts['get_feature_posts'];
						$count 				= $list_posts['count']+$count;
						$total_i			= $list_posts['total_i'];
						echo $list_posts['return'];
						$cati++;
					}
				}
			}
			
			//print uncategorized posts
			if (count($get_feature_posts) > 0) {
				$list_posts 			= hys_admin_list_posts_for_managing($get_feature_posts,'',$cati, $total_i);
				$get_feature_posts 		= $list_posts['get_feature_posts'];
				$count 					= $list_posts['count']+$count;
				$total_i				= $list_posts['total_i'];
				echo $list_posts['return'];
			}
			
			if ($count == 0)
				echo "<li class='hys_cat_empty_row'> - no heyyou posts entered, add one below - </li>";
		}
		
		//drang and drop //@TODO: sort this out
		$or = @implode(',',$items_order);
			
			
		echo '
		<input type="hidden" name="sort_order" id="sort_order" value="'.$or.'" />
		<input type="hidden" name="do_submit" value="Submit Sortation"  />
		<input type="hidden" value="1" name="autoSubmit" id="autoSubmit" />';
	}

	
	
/*-------------------------------------------------------------
 Name:      hys_admin_list_posts_for_managing

 Purpose:   outputs the lists based on query provided..
 			creates catagories, drop and drag & other heyyou
 			post form elements
 Receive:   query of posts, category, and the category count
 Return:	array(): html output list, the $get_feature_posts
 			with printed values unset, and the count of posts
-------------------------------------------------------------*/
	function hys_admin_list_posts_for_managing($get_feature_posts, $cat = '', $cati = 1, $total_i = 0) {
		global $wpdb, $hys, $items_order;
		
		$return 	= '';
		$uri 		= "?post={$_GET['post']}&action=edit"; //the current url
		$i 			= 0; //counter
		$cc 		= 0; //counter
		$first 		= 0;
		$numofposts = count($get_feature_posts);
		$last 		= $numofposts-1;
		
		//Cat header row...
		
		$using_categories = (isset($hys['hys_page_config']['include_cats']) && $hys['hys_page_config']['include_cats'] == 1) ? true : false;
		
		if ($using_categories) {
			if (empty($cat)) {
				$the_cat_title = "<span class='uncategorized_cat' style='color:#999;'>Uncategorized</span>";
			} else {
				//get category meta: title / blurb / html format (older heyyou's)
				$cat_meta = @unserialize($cat->description); 
				// if the descrition is serialized (older heyyou's)
				if ($cat_meta == true) {
					$cat_blurb = $cat_meta['blurb'];
					$cat_format = stripslashes($cat_meta['format']);
				} else {
					$cat_blurb 	= $cat->description;
					$cat_format =  get_post_meta($cat->term_id, 'cat_override');	
					$cat_format	= (isset($cat_format[0])) ? $cat_format[0] : array();
				}
				
				$catnotifyhtml = (isset($cat_meta['format']) && !empty($cat_meta['format'])) ? "<code style='font-style:normal !important'>&lt;HTML&gt;</code> " : '';
				$catshorttitle = (isset($cat_meta['title']) && !empty($cat_meta['title'])) ? "<strong>".$cat_meta['title']."</strong> - " : '';
				$catshortdescript = "<a class='hys_fake_link' style='color:#999 !important; font-style:italic' onclick='showhide(\"cat_{$cat->term_id}_moreinfo\")'>{$catnotifyhtml}".$catshorttitle.nostyle($cat_meta['blurb'],50,'...')."</a>";
				
				$cat_format = ($cat_format == 'Array') ? '' : $cat_format;
	
				$the_cat_title = "
						<!-- CAT DELETE -->
						<a href='{$uri}&delete_cat={$cat->term_id}&message=3' ".
					 		"title='DELETE cat #{$cat->term_id}' onclick='return doConfirm(this.id);'><img src='{$hys['dir']}/res/imgs/delete.png' alt='' class='hys_admin_ico' style='padding: 0 8px 0 32px;' /></a>
						
						<!-- CAT EDIT -->
						<a class='hys_fake_link' onclick='showhide(\"cat_{$cat->term_id}_moreinfo\")' ".
							"title='Edit Category'><img src='{$hys['dir']}/res/imgs/pencil.png' alt='' class='hys_admin_ico' /></a> 
							&nbsp; 
						
						<!-- CAT COUNT/ORDER -->
						<input type='text' name='update_cat[{$cat->term_id}][count]' value='{$cat->count}' size=1 ".
											" style='width:35px;' class='code hys_cat_counter' />  
						
						<!-- CAT TITLE -->
						<input type='text' name='update_cat[{$cat->term_id}][name]' value='{$cat->name}' size=30/>
						{$catshortdescript}<br />
						
						<!-- CAT DESCRIPT -->
						<div id='cat_{$cat->term_id}_moreinfo'  style='display:none;'>
							<table border=0>
							  <tr>
								<td valign='top'  align=right  style='font-size:11px !important;'>Description:<br />
									<a onClick='addtext(\"moredescript_{$cat->term_id}\");'>
									<img src='{$hys['dir']}/res/imgs/more_btn.jpg' alt='' ".
									"class='' style='width:26px !important;height:24px !important;margin:5px' />
									</a>
								</td>
								<td>
									<textarea name='update_cat[{$cat->term_id}][description]' ".
										"id='moredescript_{$cat->term_id}' class='cat_textarea'>{$cat_blurb}</textarea>
								</td>
							  </tr>
							  <tr>
								<td valign='top'  align=right  style='font-size:11px !important;'>Override HTML<br /> output format:
								</td>
								<td>
									<textarea name='cat_override[{$cat->term_id}]' class='code cat_textarea' style='font-size:10px;'>".$cat_format."</textarea>
								</td>
							  </tr>
							</table>
						</div><!--/cat_{$cat->term_id}_moreinfo-->";
			}
					
			$return .= "
			<div class='hys_cat_edit_row hys_cat_edit_row_top'> 
				{$the_cat_title}
			</div><!--/hys_cat_edit_row-->";
		}

		$return .= "<ul class='sortablelist' id='sortable-list-{$cati}'>";
		
		$select_checkboxes = '';
				
		//go through the posts, print out buttons ect
		foreach($get_feature_posts as $k => $f_post) {		
			$showhide = ($f_post->post_status == 'publish') ? 'show' : 'hide';
			$custom_fields =  get_post_meta($f_post->ID, 'meta');	
			$custom_fields = (isset($custom_fields[0])) ? $custom_fields[0] : array();
			
			/*
			echo "<pre>";
			print_r($custom_fields);
			echo "</pre>";
			/**/
			
			/*
			echo "<pre>";
			echo @"{$custom_fields['hys_post_cat']} == {$cat->term_id}";
			echo "</pre>";
			/**/
			
			if (empty($cat) || ( @$custom_fields['hys_post_cat'] == @$cat->term_id) ) {
				//fix title .. if empty of too long
				$f_post->post_title = preg_replace('/<!--(.*)-->/Uis', '', $f_post->post_title);
				$f_post->post_title = (empty($f_post->post_title)) ? "<em>- no title -</em>": nostyle($f_post->post_title,20,'..');
				
				//add attachments
				$attachments = '';
				if (@$hys['hys_page_config']['attachments_gallery'] && function_exists('attachments_get_attachments')) {
					$post_images = attachments_get_attachments($f_post->ID); 
					$total_images = count($post_images);
					if($total_images > 0) {
					  $attachments .= "<div class='hys_admin_attach'>";
					      for ($v = 0; $v < $total_images; $v++)
					      	if (isset($post_images[$i]) && $v < 3 && get_post($post_images[$v]['id']))
						      		$attachments .= wp_get_attachment_image( $post_images[$v]['id'], 'thumbnail', 1 );
					     $attachments .= "</div>";
					}
				}
				
				//change descript if empty.. look at meta's
				$descptshort = nostyle($f_post->post_content,40,'..');
				if (empty($descptshort)) {
					$getmeta =  get_post_meta($f_post->ID, 'meta');	
					$getmeta = $getmeta[0];
					$numofmeta = count($getmeta);
					
					if (is_array($getmeta)) {
						foreach ($getmeta as $getmeta_name => $getmeta_value) {
							if (!empty($getmeta_value) && !is_int($getmeta_value) && $getmeta_value != 'http://' && $getmeta_name != 'hys_post_cat') {
								//if it's a URL, we'll take it, but we'll keep looking at other meta
								if (strpos($getmeta_name, 'url')) {
									$descptshort = "<a href='{$getmeta_value}' target='_Blank' title='Visit Link: {$getmeta_value}' class='gray'>".hys_chopstring($getmeta_value,50)."</a>";
								} 
								//if not a URL, leave the foreach loop,
								else {
									$descptshort = nostyle($getmeta_value,40,'..');
									break;
								}
							}
						}
					}
				}
				
				// FOR DRAG AND DROP ORDERING..
				$items_order[] = $f_post->ID;
				
				$strike = ($showhide == 'hide') ? ' style="text-decoration:line-through !important;"': '';
				
				$return .= "
					<li title='{$f_post->ID}'> <!--{$total_i}-->
						<input type='checkbox' name='selected_id[]' value='{$f_post->ID}' />
						<!-- SHOW/HIDE -->
						<a href='{$uri}&un{$showhide}_ftr={$f_post->ID}#hys_manage_metabox' title='HIDE/SHOW  post #{$f_post->ID}'>
							<img src='{$hys['dir']}/res/imgs/{$showhide}.png' alt='' /> 
						</a>&nbsp; 						
						<!-- EDIT -->
						<a href='admin.php?page=editheyyoupost&post={$_GET['post']}&edit_ftr={$f_post->ID}' title='EDIT post #{$f_post->ID}'>
							<img src='{$hys['dir']}/res/imgs/pencil.png' alt='' />
						</a>&nbsp;
						<!-- DELETE -->
						<a href='{$uri}&delete_ftr={$f_post->ID}&message=3#hys_manage_metabox' title='DELETE post #{$f_post->ID}' 
						 onclick='return doConfirm(this.id);'>
							<img src='{$hys['dir']}/res/imgs/delete.png' alt='' />
						</a>&nbsp;";
						
				if ($hys['user'] != 'heyyou_client') {
					$return .= "
						<!-- DUPLICATE -->
						<a href='{$uri}&dbl_ftr={$f_post->ID}&ts=".time()."&message=3#hys_manage_metabox' title='DUPLICATE post #{$f_post->ID}' >
							<img src='{$hys['dir']}/res/imgs/dbl.png' alt='' style='' />
						</a>\n";
				}
				
				if (isset($hys['hys_page_config']['meta_thumb_preview']) && !empty($hys['hys_page_config']['meta_thumb_preview'])) {
					$getthumbrev = wp_get_attachment_image_src($custom_fields[$hys['hys_page_config']['meta_thumb_preview']]);
					$return .= "<img src='{$getthumbrev[0]}' alt='' class='hys_meta_thumb_preview' onMouseOver='meta_thumb_preview(\"meta_thumb_preview_{$f_post->ID}\")' onMouseOut='meta_thumb_preview_off()' />
					<img src='{$getthumbrev[0]}' alt='' id='meta_thumb_preview_{$f_post->ID}' class='hys_meta_thumb_full' style='width:{$getthumbrev[1]}px !important;height: {$getthumbrev[2]}px !important' />";
				}
				
				$return .= "
						<!-- TITLE -->
						&nbsp;&nbsp;<span class='hys_post_list_title' {$strike}>".$f_post->post_title."</span>						
						<!-- DESCRITION -->
						<em {$strike}>".$descptshort."</em>
						{$attachments}
					</li>";
				
				//remove the post so we don't use it in uncat
				unset($get_feature_posts[$k]);
				
				//counters
				$cc++; $i++; $total_i++;
				
				$select_checkboxes .= "{$total_i},";
			}
			
		}
		
		$return .= "
		</ul>
			<div class='hys_cat_edit_row hys_cat_edit_row_bottom' style='border-top:0;'> 
				<a class='selectall' onclick='  checkAll(document.post[\"selected_id[]\"],\"{$select_checkboxes}\")'><!--  selectall(this,\"post\",\"selected_id\") --></a> 
				<a title='DELETE Selected Posts' onclick='doConfirm(this.id,\"Are you sure you want to deleted the selected heyyou post(s)? This action cannot be undone.\"); document.post.submit()'>Delete Selected</a>
			</div><!--/hys_cat_edit_row-->\n";
		

		//return
		return array(
			'return' => $return, 
			'get_feature_posts' => $get_feature_posts,
			'count' => $i,
			'total_i' => $total_i
		);
	}	
	
	





?>