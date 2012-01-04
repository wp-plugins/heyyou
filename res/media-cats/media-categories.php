<?php
/*
Media Categories - https://sites.google.com/site/medialibarycategories/
Media Library Categories is a WordPress plugin that lets you add custom categories for use in the media library. Media items can then be sorted per category.
By: Hart Associates (Rick Mead) - http://www.hartinc.com
Ve: 1.0.6
*/   
   


/**
*  wp-content and plugin urls/paths
*/
// Pre-2.6 compatibility
if ( ! defined( 'WP_CONTENT_URL' ) )
      define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' );
if ( ! defined( 'WP_CONTENT_DIR' ) )
      define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
if ( ! defined( 'WP_PLUGIN_URL' ) )
      define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' );
if ( ! defined( 'WP_PLUGIN_DIR' ) )
      define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );

global $rl_dir, $rl_base, $rl_markets;
$rl_dir=dirname(plugin_basename(__FILE__)); //plugin absolute server directory name
$rl_base=get_option('siteurl')."/wp-content/plugins/".$rl_dir; //URL to plugin directory
$rl_path=ABSPATH."wp-content/plugins/".$rl_dir; //absolute server pather to plugin directory

$view_link="| <!--<a href='".get_option('siteurl')."/wp-admin/admin.php?page=$rl_dir/view.php'>View Media Categories</a>-->";

$web_domain=@$_SERVER[HTTP_HOST];
		
if (!class_exists('mc')) {
    class mc {
        /**
        * @var string The options string name for this plugin
        */
        var $optionsName = 'mc_options';
        
        /**
        * @var string $localizationDomain Domain used for localization
        */
        var $localizationDomain = "mc";
        
        /**
        * @var string $pluginurl The path to this plugin
        */ 
        var $thispluginurl = '';
        /**
        * @var string $pluginurlpath The path to this plugin
        */
        var $thispluginpath = '';
            
        /**
        * @var array $options Stores the options for this plugin
        */
        var $options = array();
        
		
		var $isAdmin = true;
        
		var $optionsmenuRoleCapabilityLevel=10;
		var $adminmenuRoleCapabilityLevel=10;
		
		
        //Class Functions
        /**
        * PHP 4 Compatible Constructor
        */
        function mc(){$this->__construct();}
        
        /**
        * PHP 5 Constructor
        */        
        function __construct(){
			$admin_init = 0;
			$attachment_fields_to_edit = 22;
			$attachment_fields_to_save = 0;			
		   
            //"Constants" setup
            $this->thispluginurl = WP_PLUGIN_URL . '/' . dirname(plugin_basename(__FILE__)).'/';
            $this->thispluginpath = WP_PLUGIN_DIR . '/' . dirname(plugin_basename(__FILE__)).'/';           
		   
			//Initialize the options
            $this->getOptions();						
			
			//Register Media category type and default
            add_action('init', array(&$this,"create_my_taxonomies"), 0);
			
			//Menu for Media Categories Admin section
			add_action('admin_menu',  array(&$this,"mediaCategory_add_admin"), 0);
			
			//Menu for Media Categories Options Admin section
			add_action("admin_menu", array(&$this,"admin_menu_link"), 1);
			
			//Add admin js scripts
            add_action('admin_init', array(&$this, 'add_admin_scripts'), $admin_init++);
		
			
			//Add Filters for editing and saving media records
			add_filter('attachment_fields_to_edit', array(&$this, 'add_media_category_field'), $attachment_fields_to_edit++, 2);
			add_filter('attachment_fields_to_save', array(&$this, 'save_media_category_field'), $attachment_fields_to_save++, 2);
		
			//Add custom column to media library admin page
			add_filter('manage_media_columns',  array(&$this, 'add_media_column'));
			add_action('manage_media_custom_column',  array(&$this, 'manage_media_column'), 10, 2);

			//Add custom filter dropdown to media library admin page
			add_action('restrict_manage_posts',array(&$this, 'restrict_media_by_category'));
			add_filter('posts_where', array(&$this, 'convert_attachment_id_to_taxonomy_term_in_query'));
			
			add_filter('admin_head',array(&$this, 'show_tinyMCE'));

			//for making sure the rewrites show  'www.url.com/media/[media category slug]'
			add_action('admin_init', 'flush_rewrite_rules');
        }
		///
		// Functions Called From Init
		///
		function getOptions() {
            if (!$theOptions = get_option($this->optionsName)) {
                $theOptions = array('default'=>'options');
                update_option($this->optionsName, $theOptions);
            }
            $this->options = $theOptions;
			
			if(@$this->options['optionsmenuRoleCapabilityLevel'] !=null && @$this->options['optionsmenuRoleCapabilityLevel'] !='' && is_numeric($this->options['optionsmenuRoleCapabilityLevel']))
			{
				$this->optionsmenuRoleCapabilityLevel=$this->options['optionsmenuRoleCapabilityLevel'];
			}
			
			if(@$this->options['adminmenuRoleCapabilityLevel'] !=null && @$this->options['adminmenuRoleCapabilityLevel'] !='' && is_numeric($this->options['adminmenuRoleCapabilityLevel']))
			{
				$this->adminmenuRoleCapabilityLevel=$this->options['adminmenuRoleCapabilityLevel'];
			}
				
			
        }
		function create_my_taxonomies() {
			register_taxonomy(
				'media_category',
				'media',
				array(
					'hierarchical' => true,
					'label' => 'Media Categories',
					'public' => true,
					'show_ui' => true,
					'query_var' => 'media_categories',
					'rewrite' => array('slug' => 'media')
				)
			);
			
			$isterm = term_exists( 'Uncategorized', 'media_category' ); // array is returned if taxonomy is given
			$parent_term_id = '0'; // get numeric term id
			if(!$isterm)
			{
				wp_insert_term(
				  'Uncategorized', // the term 
				  'media_category', // the taxonomy
				  array(
					'description'=> 'The default media category.',
					'slug' => 'uncategorized',
					'parent'=> $parent_term_id
				  )
				);
			}
			
			$term = term_exists( 'Uncategorized', 'media_category' ); // array is returned if taxonomy is given
			if($term)
			{
				if( isset($this->options['mc_default_media_category']) && ($this->options['mc_default_media_category'] ==null ||$this->options['mc_default_media_category'] ==''))
				{
					$this->options['mc_default_media_category'] = $term["term_id"];  
					$this->saveAdminOptions();
				}
			}
			
			
		}
		function restrict_admin(){
 			global $current_user;
			get_currentuserinfo();
			
			//if not admin, die with message
			if ( $current_user->user_level <  8 ) {
				$this->isAdmin = false;
			}
		}
		public function add_admin_scripts() {
			global $pagenow;
			if ($pagenow=='admin.php' &&
					isset($_GET['page']) && $_GET['page']=='media-library-categories/sort.php' &&
					isset($_GET['termid']) && is_numeric($_GET['termid'])) {				
				
				// Insert jQuery 1.4.2
				wp_enqueue_script(
					 'jqueryrequired', 'http://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js', false); 
				 
				wp_enqueue_script(
					'WPMediaCategory-jquery-sort',
					$this->thispluginurl. 'jquery.tablednd_0_5.js', array( 'jqueryrequired' ));
			
				wp_enqueue_script(
				'WPMediaCategory-jquery-init',
				$this->thispluginurl. 'jquery.admin.js', array( 'jqueryrequired', 'WPMediaCategory-jquery-sort' ));
				
			}
				
		}
		function mediaCategory_add_admin() {
			global $rl_dir, $rl_base, $text_domain;
			//add_submenu_page("upload.php", "Media Categories", "Media Categories", $this->adminmenuRoleCapabilityLevel, $rl_dir."/view.php");
			
			//add_submenu_page($rl_dir."/view.php", "Sort", "Sort", $this->adminmenuRoleCapabilityLevel, $rl_dir."/sort.php");
			//add_submenu_page($rl_dir."/view.php", "Add Media Category", "Add Media Category", $this->adminmenuRoleCapabilityLevel, $rl_dir."/add.php");
			
				
		}
        function admin_menu_link() {
            add_options_page('Media Category Options', 'Media Cat Optns', 'manage_options', basename(__FILE__), array(&$this,'admin_options_page'));
			add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array(&$this, 'filter_plugin_actions'), $this->optionsmenuRoleCapabilityLevel, 2 );			
        }
		public function add_media_category_field($fields, $object) {
			$html = '';
			
			$ignor_this_displaying_of = (isset($_GET['attachbtn']) || (isset($_GET['s']) && !empty($_GET['s']))) ? true : false;


			if (!isset($fields['media_library_categories']) && !$ignor_this_displaying_of) {
				
				$categories = $this->get_category_hierarchical_terms();
				$selected_categories = (array)wp_get_object_terms($object->ID, 'media_category');
				
				$html .= "<div class='hys_media_select_categorys' id='hys_media_select_categorys'>";
				
				//$html='<select id="media-category"  multiple="multiple" style="height:100px;">';
						
							$randval = hys_random();
					
					if (!empty($categories) && !empty($selected_categories)) {
						
						foreach ($categories AS $category) {
						
							$select = ($selected_categories[0]->term_id == $category["id"]) ? ' checked="checked"' : '';
							$select = (empty($select) && empty($selected_categories[0]->term_id) && $category["id"] == 1) ? ' checked="checked"' : $select;
	
							$highlight = ($selected_categories[0]->term_id == $category["id"]) ? ' style="color:#000;"' : '';
							$highlight = (empty($highlight) && empty($selected_categories[0]->term_id) && $category["id"] == 1) ? ' style="color:#000;"' : '';
	
							//$html.="<option value='".$category["id"]."'  $select>".$category["name"]."</option>";
							$html.="<label class='mediacat_highlight' id='hys_media_radio_{$category['id']}_{$randval}'{$highlight}>
										<input type='radio' onclick='mediacat_highlight(\"hys_media_radio_{$category['id']}_{$randval}\")' name='media-category[{$object->ID}]' class='a-media-category' id='hys_media_radio_{$category['id']}_input_{$randval}' value='".$category["id"]."'  $select>".$category["name"]."</option>
									</label>";
						}
	
					} 
					else
					{
						foreach ($categories AS $category) {
							$select = (@$selected_categories[0]->term_id == $category["id"]) ? ' checked="checked"' : '';
							$select = (empty($select) && empty($selected_categories[0]->term_id) && $category["id"] == 1) ? ' checked="checked"' : $select;
	
							$highlight = (@$selected_categories[0]->term_id == $category["id"]) ? ' style="color:#000;"' : '';
							$highlight = (empty($highlight) && empty($selected_categories[0]->term_id) && $category["id"] == 1) ? ' style="color:#000;"' : '';
							//$html.="<option value='".$category["id"]."'  $select>".$category["name"]."</option>";
							$html.="							
							<label class='mediacat_highlight' id='hys_media_radio_{$category['id']}_{$randval}'{$highlight}>
										<input type='radio' onclick='mediacat_highlight(\"hys_media_radio_{$category['id']}_{$randval}\")' name='media-category[{$object->ID}]' class='a-media-category' id='hys_media_radio_{$category['id']}_input_{$randval}' value='".$category["id"]."'  $select>".$category["name"]."</option>
									</label>";
						}
					}
				$html .= "</div><!--/hys_media_select_categorys-->";

				$label = 'Media Categories';
				$fields['media_library_categories'] = array(
					'label' => $label,
					'input' => 'html',
					'html' =>  $html,
					'value' => (!empty($selected_categories)) ? $selected_categories->term_id : '',
					'helps' => ''
				); 
				
			}
			return $fields;
		}
		
		
		
		public function save_media_category_field($post, $attachment) {
			$terms = array();

			if (is_array($_POST['media-category'])) {
				foreach ($_POST['media-category'] as $attach_id => $attach_wants_cat) {
					$termID = (empty($attach_wants_cat)) ? 1 : $attach_wants_cat;
					$term = get_term( $termID, 'media_category' );
					array_push($terms, $term->name);
					wp_set_object_terms($attach_id, $terms, 'media_category', false); 
				}
			} else {
				if ( $attachment && (count($attachment['media-categories'])>0)) {
					foreach ($attachment['media-categories'] as $termID)
					{
						$termID = (empty($_POST['media-category'])) ? 1 : $_POST['media-category'];
						$term = get_term( $termID, 'media_category' );
						array_push($terms, $term->name);
					}
				} else {			
					$termID = (empty($_POST['media-category'])) ? 1 : $_POST['media-category'];
					$term = get_term( $termID, 'media_category' );
					array_push($terms, $term->name);
				}
				//push the new values for this attachment
				wp_set_object_terms($post['ID'], $terms, 'media_category', false); 
			}			
			return $post;
		}
		
		
		
		
		
		
		function add_media_column($posts_columns) {
			// Add a new column
			$posts_columns['att_cats'] = _x('Categories', 'column name');
		 
			return $posts_columns;
		}
		function manage_media_column($column_name, $id) {
			
			switch($column_name) {
			case 'att_cats':
				$tagparent = "upload.php?";
				
				$categories = (array)wp_get_object_terms($id, 'media_category');
				
				if (!empty($categories)) {
				
					$currentLabels = '';
					foreach ($categories AS $category) {
						$currentLabels .= (($currentLabels != "")?", ":"").$category->name;
					}
					
					echo $currentLabels;
				}else {
					_e('No Categories');
				}
				break;
			default:
				break;
			}
		 
		}
		function restrict_media_by_category() {
			global $pagenow;
			global $typenow;
			global $wp_query;
			if ($pagenow=='upload.php') {
				$taxonomy = 'media_category';
				$media_taxonomy = get_taxonomy($taxonomy);
				wp_dropdown_categories(array(
					'show_option_all' =>  __("Show All {$media_taxonomy->label}"),
					'taxonomy'        =>  $taxonomy,
					'name'            =>  'media_category',
					'orderby'         =>  'name',
					'selected'        =>  $wp_query->query['term'],
					'hierarchical'    =>  true,
					'depth'           =>  3,
					'show_count'      =>  true, // Show # listings in parens
					'hide_empty'      =>  true, // Don't show businesses w/o listings
				));
			}
		}
		function convert_attachment_id_to_taxonomy_term_in_query($where) {
			global $pagenow;
			global $wpdb;
				
			if( $pagenow=='upload.php' &&
					isset($_GET['media_category']) && is_numeric($_GET['media_category'])&& $_GET['media_category']>0 ) {
					
					 $subquery = "	SELECT r.object_id FROM $wpdb->term_relationships r
									INNER JOIN $wpdb->term_taxonomy tax on tax.term_taxonomy_id = r.term_taxonomy_id
									WHERE tax.term_id = ".$_GET['media_category']; 
					
					
				$where .= " AND ID IN ($subquery)";
				
			}
			
			return $where;
		}
		function show_tinyMCE() {
			global $pagenow;
			if ($pagenow=='admin.php' &&
					isset($_GET['page']) && $_GET['page']=='media-library-categories/add.php') {
				wp_enqueue_script( 'common' );
				wp_enqueue_script( 'jquery-color' );
				wp_print_scripts('editor');
				if (function_exists('add_thickbox')) add_thickbox();
				wp_print_scripts('media-upload');
				if (function_exists('wp_tiny_mce')) wp_tiny_mce();
				wp_admin_css();
				wp_enqueue_script('utils');
				do_action("admin_print_styles-post-php");
				do_action('admin_print_styles');
				remove_all_filters('mce_external_plugins');
				}
		}     
		///
		// END Functions Called From Init
		///
		
		
		
		
		
		

		
		
		
		
		
			
		public function get_category_hierarchical_list($parentID = 0, $num_per_page=0, $start=0) {
			$return = array();
			if($num_per_page==0)
			{
				$args = array(
					'hide_empty' => false,
					'parent' => (int)$parentID,
					'hierarchical' => false,				
					'taxonomy' => 'media_category',
					'offset'=>$start
				);
			}
			else
			{
				$args = array(
					'hide_empty' => false,
					'parent' => (int)$parentID,
					'hierarchical' => false,				
					'taxonomy' => 'media_category',
					'number'=>$num_per_page,
					'offset'=>$start
				);
			}
			
			$categorias = get_categories($args);
			
			if (empty($categorias)) return $return;

			foreach ($categorias AS $categoria) {
				$array = array();
				$array['id'] = $categoria->term_id;
				$array['name'] = $categoria->name;
				$array['slug'] = $categoria->category_nicename;
				$array['count'] = $categoria->count;
				$array['children'] = $this->get_category_hierarchical_list($categoria->term_id);
				$return[] = $array;
			}			
			
			return $return;
		}
		
		
		public function get_category_hierarchical_terms($parentID = 0, $return = array(), $dashes ='') {
			
			$args = array(
					'hide_empty' => false,
					'parent' => (int)$parentID,
					'hierarchical' => false,				
					'taxonomy' => 'media_category'
				);
			
			
			$categorias = get_categories($args);
			
			if (empty($categorias)) return $return;

			foreach ($categorias AS $categoria) {
				
				if($parentID>0)$dashes.='&mdash;';
				
					$array = array();
					$array['id'] = $categoria->term_id;
					$array['name'] = $dashes.' '.$categoria->name;
					$array['slug'] = $categoria->category_nicename;
					$array['description'] = $categoria->description;
					$array['count'] = $categoria->count;
					
					$_attachments = array();
					
					
						$attachmentIds = get_objects_in_term( $categoria->term_id, 'media_category', $args );
			
						$args = array(
								'orderby'         => 'post_date',
								'order'           => 'DESC',
								'include'         => $attachmentIds,
								'post_type'       => 'attachment',
								); 
						$attachments = get_posts($args);
						if(count($attachments>0)){
							foreach ( $attachments as $attachment ) {
								
								$mime = strtolower($attachment->post_mime_type);
								
								$_array = array();
								$_array['id'] = $attachment->ID;
								$_array['title'] = @$row['post_title'];
												
								if($mime=='image/jpeg'
									|| $mime=='image/jpg'
									|| $mime=='image/gif'
									|| $mime=='image/png'
									|| $mime=='image/bmp'
									|| $mime=='image/tiff'
									)
								{
									$thumb = wp_get_attachment_thumb_url( $attachment->ID );
									$fullsize = $attachment->guid;
									
									$_array['thumb'] = $thumb;								
									$_array['fileurl'] = $attachment->guid;
								}
								else
								{
									$_array['thumb'] = '';								
									$_array['fileurl'] = $attachment->guid;
								}
								$_attachments[]=$_array;
							}
						}
						
					$array['attachments'] =$_attachments;
					$return[] = $array;
				
				$return = $this->get_category_hierarchical_terms($categoria->term_id, $return, $dashes);
			}			
			
			return $return;
		}
		
		
		public function get_category_hierarchical_selectoptions($selected=0, $parentID = 0, $return ='', $dashes ='') {
						
			$args = array(
				'hide_empty' => false,
				'parent' => (int)$parentID,
				'hierarchical' => false,				
				'taxonomy' => 'media_category'
			);
			$categorias = get_categories($args);
			
			foreach ($categorias AS $categoria) {
				if($parentID>0)$dashes.='-';
				
				$selectedhtml='';
				
				
				if($selected==$categoria->term_id)
				{
					$selectedhtml = " selected='selected' ";
				}
				
				$return .="<option $selectedhtml value='".$categoria->term_id."'>$dashes".$categoria->name."</option>";
				
				$return =$this->get_category_hierarchical_selectoptions($selected, $categoria->term_id, $return, $dashes);
				$dashes='';
			}			
			
			return $return;
		}
		
		
		public function get_category_select($selected=0, $parentID = 0, $return ='') {
			
			$selectInput = $this->get_category_hierarchical_selectoptions($selected, $parentID, $return);
			
			return "<select>".$selectInput."</select>";
		}
		
		
		public function get_category_archive($term_id=0, $parentID = 0, $return = '') {
			
			$args = null;
			if($term_id>0)
			{
				$args = array(
						'hide_empty' => false,
						'include' => (int)$term_id,
						'hierarchical' => false,				
						'taxonomy' => 'media_category'
					);
			}
			if($parentID>0)
			{
				$args = array(
						'hide_empty' => false,
						'parent' => (int)$parentID,
						'hierarchical' => false,				
						'taxonomy' => 'media_category'
					);
			}
			
			$categorias = get_categories($args);
			
			if (empty($categorias)) return $return;

			foreach ($categorias AS $categoria) {
				
				
				
					/* $array['id'] = $categoria->term_id;
					$array['name'] = $dashes.' '.$categoria->name;
					$array['slug'] = $categoria->category_nicename;
					$array['description'] = $categoria->description;
					$array['count'] = $categoria->count; */
				$return .="<div id='category_".$categoria->term_id."'>";
				$return .="<h2>". $categoria->name ."</h2>";
				$return .=(($categoria->description!='')?'<p>'.$categoria->description.'</p>':''); 
				
				$return .="<ul>";
					
							$attachmentIds = get_objects_in_term( $categoria->term_id, 'media_category', $args );
				
							$args = array(
									'orderby'         => 'post_date',
									'order'           => 'DESC',
									'include'         => $attachmentIds,
									'post_type'       => 'attachment',
									); 
							$attachments = get_posts($args);
							if(count($attachments>0)):
								foreach ( $attachments as $attachment ) {
									
									$mime = strtolower($attachment->post_mime_type);
									
									/*$_array['id'] = $attachment->ID;
									$_array['title'] = $attachment->post_title;*/
													
									if($mime=='image/jpeg'
										|| $mime=='image/jpg'
										|| $mime=='image/gif'
										|| $mime=='image/png'
										|| $mime=='image/bmp'
										|| $mime=='image/tiff'
										)
									{
										$thumb = wp_get_attachment_thumb_url( $attachment->ID );
										$fullsize = $attachment->guid;
										$return .="<li>";
												$return .="<a href='". $fullsize ."' target='_blank'>";
													$return .="<img class='thumb' src='". $thumb ."' alt='". $attachment->post_title ."' />	";
												$return .="</a>";
										$return .="</li>";
									}
									else
									{
										$return .="<li>";
												$return .="<a href='". $attachment->guid ."' target='_blank'>". $attachment->post_title ."</a>";
										$return .="</li>";
									}
									
								}
							endif;
				$return .="</ul>";	
				
				$return = $this->get_category_archive(0, $categoria->term_id, $return);
				$return .="</div>";	
				
				
			}			
			
			return $return;
		}
		
		
		
		function saveAdminOptions(){
            return update_option($this->optionsName, $this->options);
        }
        function filter_plugin_actions($links, $file) {
           //If your plugin is under a different top-level menu than Settiongs (IE - you changed the function above to something other than add_options_page)
           //Then you're going to want to change options-general.php below to the name of your top-level page
           $settings_link = '<a href="options-general.php?page=' . basename(__FILE__) . '">' . __('Settings') . '</a>';
           array_unshift( $links, $settings_link ); // before other links

           return $links;
        }
        function admin_options_page() { 
			global $rl_dir, $rl_base, $text_domain;
			
		
            if($_POST['mc_save']){
                if (! wp_verify_nonce($_POST['_wpnonce'], 'mc-update-options') ) die('Whoops! There was a problem with the data you posted. Please go back and try again.'); 
                $this->options['mc_default_media_category'] = $_POST['mc_default_media_category'];  
                $this->options['adminmenuRoleCapabilityLevel'] = $_POST['adminmenuRoleCapabilityLevel'];  
                $this->options['optionsmenuRoleCapabilityLevel'] = $_POST['optionsmenuRoleCapabilityLevel'];  
                                        
                $this->saveAdminOptions();
                
                echo '<div class="updated"><p>Success! Your changes were sucessfully saved!</p></div>';
            }
			//echo $this->thispluginpath.'taxonomy-media_category.php';
			
?>                                
                <div class="wrap">
                <h2>Media Category Options </h2><!--<a class='button add-new-h2' href='admin.php?page=<?php echo $rl_dir ?>/view.php'>Manage Media Categories</a>-->
                <form method="post" id="mc_options">
                <?php wp_nonce_field('mc-update-options'); ?>
                    <table width="100%" cellspacing="2" cellpadding="5" class="form-table"> 
                        <tr valign="top"> 
                            <th width="33%" scope="row"><?php _e('Default Category ID:', $this->localizationDomain); ?></th> 
                            <td><input name="mc_default_media_category" type="text" id="mc_default_media_category" size="45" value="<?php echo $this->options['mc_default_media_category'] ;?>"/>
                        </td> 
                        </tr>
                        <tr valign="top"> 
                            <th width="33%" scope="row"><?php _e('Admin Page Role Access:', $this->localizationDomain); ?></th> 
                            <td>
							<select name="adminmenuRoleCapabilityLevel" id="adminmenuRoleCapabilityLevel">
								<option value="10" <?php echo $this->options['adminmenuRoleCapabilityLevel']=="10"? ' selected="selected"':''; ?>>Administrator</option>
								<option value="7" <?php echo $this->options['adminmenuRoleCapabilityLevel']=="7"? ' selected="selected"':''; ?>>Editor</option>
								<option value="4" <?php echo $this->options['adminmenuRoleCapabilityLevel']=="4"? ' selected="selected"':''; ?>>Author</option>
								<option value="1" <?php echo $this->options['adminmenuRoleCapabilityLevel']=="1"? ' selected="selected"':''; ?>>Contributor</option>
								<option value="0" <?php echo $this->options['adminmenuRoleCapabilityLevel']=="0"? ' selected="selected"':''; ?>>Subscriber</option>
							</select>
                        </td> 
                        </tr>
                        <tr valign="top"> 
                            <th width="33%" scope="row"><?php _e('Options Page Role Access:', $this->localizationDomain); ?></th> 
                            <td>
							<select name="optionsmenuRoleCapabilityLevel" id="optionsmenuRoleCapabilityLevel">
								<option value="10" <?php echo $this->options['optionsmenuRoleCapabilityLevel']=="10"? ' selected="selected"':''; ?>>Administrator</option>
								<option value="7" <?php echo $this->options['optionsmenuRoleCapabilityLevel']=="7"? ' selected="selected"':''; ?>>Editor</option>
								<option value="4" <?php echo $this->options['optionsmenuRoleCapabilityLevel']=="4"? ' selected="selected"':''; ?>>Author</option>
								<option value="1" <?php echo $this->options['optionsmenuRoleCapabilityLevel']=="1"? ' selected="selected"':''; ?>>Contributor</option>
								<option value="0" <?php echo $this->options['optionsmenuRoleCapabilityLevel']=="0"? ' selected="selected"':''; ?>>Subscriber</option>
							</select>
                        </td> 
                        </tr>
						
                        <tr>
                            <th colspan=2><input type="submit" name="mc_save" value="Save" /></th>
                        </tr>
                    </table>
                </form>
                <?php
        }
        
           
         
  } //End Class
} //End if class exists statement

//instantiate the class
if (class_exists('mc')) {

    $mc_var = new mc();

}

/* ============================
* Plugin Shortcodes
* ============================
*/ 

// [mediacategories foo="foo-value"]
function mediacategories_func($atts) {

	//extract with shortcodes
	extract(shortcode_atts(array(
	      'categories' => array(),
	      'ul_class' => '',
		  'ul_id' => '',
		  'thumnail_size' => 'thumbnail', //thumbnail, medium, large or full
		  'include_link' => true,
		  'target' => '_blank',
		  'rel' => ''
    ), $atts));
     
	$optionsName = 'mc_options';
	$options = get_option($optionsName);

	$content = "";
	$where = '';
	if($categories)
	{
		$currentCats = explode(",", $categories);
		foreach ($currentCats as $termID)
		{
				$where = " && tt.term_id=".$termID;
			
				
			
				$content .= "

							<ul id='".(($ul_id!='')?"".$ul_id."_$termID":$termID)."'".(($ul_class!='')?" class='".$ul_class."'":"").">

				";
							  
								//FINISH LIST OF ATTACHMENTS
								if (file_exists("./wp-config.php")){include("./wp-config.php");}
								elseif (file_exists("../wp-config.php")){include("../wp-config.php");}
								elseif (file_exists("../../wp-config.php")){include("../../wp-config.php");}
								elseif (file_exists("../../../wp-config.php")){include("../../../wp-config.php");}
								elseif (file_exists("../../../../wp-config.php")){include("../../../../wp-config.php");}
								elseif (file_exists("../../../../../wp-config.php")){include("../../../../../wp-config.php");}
								elseif (file_exists("../../../../../../wp-config.php")){include("../../../../../../wp-config.php");}
								elseif (file_exists("../../../../../../../wp-config.php")){include("../../../../../../../wp-config.php");}
								elseif (file_exists("../../../../../../../../wp-config.php")){include("../../../../../../../../wp-config.php");}


								 
									$db_host = DB_HOST; 
									$db_user = DB_USER; 
									$db_pass = DB_PASSWORD; 
									$db_name = DB_NAME; 
									
									
								$connect = mysql_connect( $db_host, $db_user, $db_pass ) or die( mysql_error() ); 
								$connection = $connect; 

								mysql_select_db( $db_name, $connect ) or die( mysql_error() ); 
								
								
								$query = 	"SELECT p.*, a.term_order FROM " . $table_prefix . "posts p
											inner join " . $table_prefix . "term_relationships a on a.object_id = p.ID
											inner join " . $table_prefix . "term_taxonomy ttt on ttt.term_taxonomy_id = a.term_taxonomy_id
											inner join " . $table_prefix . "terms tt on ttt.term_id = tt.term_id
											where ttt.taxonomy='media_category' $where order by a.term_order asc;";
								$results = mysql_query($query); 
								
								if ($results){ 
								$num_rows = mysql_num_rows($results);
								if($num_rows>0)
								{
									$i=1;
									while ($row = mysql_fetch_array($results)) { 
												
										$label = $row['post_title'];
										$id = $row['ID'];
										$fileUrl = $row['guid'];
										$mime = $row['post_mime_type'];
										
										$thumb = wp_get_attachment_image_src( $id, $thumnail_size ); 
										if($mime=='image/jpeg'
									|| $mime=='image/jpg'
									|| $mime=='image/gif'
									|| $mime=='image/png'
									|| $mime=='image/bmp'
									|| $mime=='image/tiff')
										{$content .= "<li>".(($include_link=="true")?"<a href='".$fileUrl."' target='$target' rel='$rel'>":"")."<img src='".$thumb[0]."' />".(($include_link=="true")?"</a>":"")."</li>"; }
										else
										{$content .= "<li>".(($include_link=="true")?"<a href='".$fileUrl."' target='$target' rel='$rel'>":"").$label.(($include_link=="true")?"</a>":"")."</li>"; }
									}
								}
								}else { 
								echo "Error!".mysql_error().$query; 
								} 
								mysql_close();
							  
							  
							  $content .=  "
						   
						   </ul>
						   
						   ";
		}
	}
	

	return $content;
}

add_shortcode('mediacategories', 'mediacategories_func'); 




?>
