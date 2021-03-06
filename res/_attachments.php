<?php



/*
 ALL CREDITS FOR THIS TO ORIGINAL AUTHOR:
 ------------------------------------------------
 Attachments
 http://mondaybynoon.com/wordpress-attachments/
 Attachments gives the ability to append any number of Media Library items to Pages, Posts, and Custom Post Types
 999.1.5.9
 Jonathan Christopher
 http://mondaybynoon.com/
 ------------------------------------------------
*/

/*  Copyright 2009-2011 Jonathan Christopher  (email : jonathan@irontoiron.com)

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */


// constant definition
if( !defined( 'IS_ADMIN' ) )
    define( 'IS_ADMIN',  is_admin() );



// ===========
// = GLOBALS =
// ===========

global $wpdb, $hys;
global $units;

$units = array( ' bytes', ' KB', ' MB', ' GB', ' TB', ' PB' );



// =========
// = HOOKS =
// =========

if( IS_ADMIN )
{
	// if not setup, don't worry about doing anything
	global $hys;
//	if (@$hys['settings']['no_attachments'] == 1) return;

    add_action( 'admin_menu', 'attachments_init' );
    add_action( 'admin_head', 'attachments_init_js' );
    add_action( 'save_post',  'attachments_save' );
    add_action( 'admin_menu', 'attachments_menu' );
    add_action( 'admin_footer', 'attachments_footer_js' );
    add_filter( 'plugin_row_meta', 'attachments_filter_plugin_row_meta', 10, 2 );

    load_plugin_textdomain( 'attachments', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}



// =============
// = FUNCTIONS =
// =============


/**
 * Compares two array values with the same key "order"
 *
 * @param string $a First value
 * @param string $b Second value
 * @return int
 * @author Jonathan Christopher
 */
function attachments_cmp($a, $b)
{
    $a = intval( $a['order'] );
    $b = intval( $b['order'] );

    if( $a < $b )
    {
        return -1;
    }
    else if( $a > $b )
    {
        return 1;
    }
    else
    {
        return 0;
    }
}


/**
 * Creates the markup for the WordPress admin options page
 *
 * @return void
 * @author Jonathan Christopher
 */
function attachments_options()
{
	// if not setup, don't worry about doing anything
	global $hys;
	if (@$hys['settings']['no_attachments'] == 1) return;

	update_option( '_attachments_dismiss_pro', 1 );

?>

<div class="wrap">

    <div id="icon-options-general" class="icon32"><br /></div>
    <h2>Attachments Options</h2>

    <?php $attachments_dismiss_pro = get_option( '_attachments_dismiss_pro' ); if( !$attachments_dismiss_pro ) : ?>
        <div class="updated">
            <?php _e( '<p><strong>Attachments Pro is now available!</strong> <a href="http://mondaybynoon.com/store/attachments-pro/">Find out more</a> about Attachments Pro. <a href="options-general.php?page=attachments/attachments.php&dismisspro=1">Dismiss</a>.</p>', 'attachments' ); ?>
        </div>
    <?php endif; ?>

    <form action="options.php" method="post">
        <?php wp_nonce_field('update-options'); ?>

        <?php if( function_exists( 'get_post_types' ) ) : ?>

            <?php
                $args           = array(
                                    'public'    => true,
                                    'show_ui'   => true,
                                    '_builtin'  => false
                                    );
                $output         = 'objects';
                $operator       = 'and';
                $post_types     = get_post_types( $args, $output, $operator );

                // we also want to optionally enable Pages and Posts
                $post_types['post']->labels->name   = 'Posts';
                $post_types['post']->name           = 'post';
                $post_types['page']->labels->name   = 'Pages';
                $post_types['page']->name           = 'page';

            ?>

            <?php if( count( $post_types ) ) : ?>

                <h3><?php _e("Post Type Settings", "attachments"); ?></h3>
                <p><?php _e("Include Attachments in the following Post Types:", "attachments"); ?></p>
                <?php foreach($post_types as $post_type) : ?>

                    <div class="attachments_checkbox">
                        <input type="checkbox" name="attachments_cpt_<?php echo $post_type->name; ?>" id="attachments_cpt_<?php echo $post_type->name; ?>" value="true"<?php if (get_option('attachments_cpt_' . $post_type->name)=='true') : ?> checked="checked"<?php endif ?> />
                        <label for="attachments_cpt_<?php echo $post_type->name; ?>"><?php echo $post_type->labels->name; ?></label>
                    </div>

                <?php endforeach ?>

            <?php else: ?>

                <p><?php _e('Attachments can be integrated with your Custom Post Types. Unfortunately, there are none to work with at this time.', 'attachments'); ?></p>

            <?php endif ?>

        <?php endif ?>

        <h3><?php _e("Miscellaneous", "attachments"); ?></h3>
        <div class="attachments_checkbox">
            <input type="checkbox" name="attachments_store_native" id="attachments_store_native" value="true"<?php if (get_option('attachments_store_native')=='true') : ?> checked="checked"<?php endif ?> />
            <label for="attachments_store_native">Make WordPress-level attachment relationships</label>
            <p class="note">If checked, Attachments will tell WordPress that all Attachments for the entry should be marked as such as though it were included in the main editor. The association will be made as though it were. Changing this option <strong>will not</strong> update existing Attachments, it only effects future saves.</p>
        </div>

        <input type="hidden" name="action" value="update" />
        <input type="hidden" name="page_options" value="attachments_store_native,<?php if( !empty( $post_types ) ) : foreach( $post_types as $post_type ) : ?>attachments_cpt_<?php echo $post_type->name; ?>,<?php endforeach; endif; ?>" />
        <p class="submit">
            <input type="submit" class="button-primary" value="<?php _e("Save", "attachments");?>" />
        </p>

    </form>

</div>
    <?
}


/**
 * Creates the entry for Attachments Options under Settings in the WordPress Admin
 *
 * @return void
 * @author Jonathan Christopher
 */
function attachments_menu()
{
	// if not setup, don't worry about doing anything
	global $hys;
	if (@$hys['settings']['no_attachments'] == 1) return;

    add_options_page('Settings', 'Attachments', 'manage_options', __FILE__, 'attachments_options');
}


/**
 * Inserts HTML for meta box, including all existing attachments
 *
 * @return void
 * @author Jonathan Christopher
 */
function attachments_add()
{
	// if not setup, don't worry about doing anything
	global $hys;
	if (@$hys['settings']['no_attachments'] == 1) return;
	
	$thumbnailsize = 'hys_attachment_size';
?>

    <div id="attachments-inner">

        <?php
			$media_upload_iframe_src = "media-upload.php?type=image&attachbtn=true&TB_iframe=1";
            $image_upload_iframe_src = apply_filters( 'image_upload_iframe_src', "$media_upload_iframe_src" );
            ?>

                <ul id="attachments-actions">
                    <li>
                        <a id="attachments-thickbox" href="<?php echo $image_upload_iframe_src; ?>&attachments_thickbox=1" title="Attachments" class="button button-highlighted">
                            <?php _e( 'Attach', 'attachments' ) ?>
                        </a>
                    </li>
                </ul>

                <div id="attachments-list">
                    <input type="hidden" name="attachments_nonce" id="attachments_nonce" value="<?php echo wp_create_nonce( plugin_basename(__FILE__) ); ?>" />
                    <ul>
                        <?php
                        
					$thepost = (isset($_GET['edit_ftr']))  ? intval($_GET['edit_ftr']) : $_GET['post'];

                        
                    if( !empty($thepost) )
                    {
                        // get all attachments
                        $existing_attachments = attachments_get_attachments( intval( $thepost ) );

                        if( is_array($existing_attachments) && !empty($existing_attachments) )
                        {
                            $attachment_index = 0;
                            foreach ($existing_attachments as $attachment) : $attachment_index++; ?>
								<li class="attachments-file">
									<span class="attachment-delete">
										<a href='#' title='Remove <?= $attachment['name'] ?>'><img src='<?=$hys['dir']?>/res/imgs/delete.png' alt='' class='hys_admin_ico' style='' /></a>
									</span>
									
									<a class="attachment-handle">
										<span class="attachment-handle-icon">
											<? 
											$thhtumb = wp_get_attachment_image_src( $attachment['id'], $thumbnailsize); 
											
											if ($thhtumb[1] != 120) { //120x70											
												$thumbnailsize = 'thumbnail';
												$thhtumb = wp_get_attachment_image_src($attachment['id'],$thumbnailsize);
											}
											
											echo "<img src='{$thhtumb[0]}' class='attachment_thumbnail_main'>"; ?>
										</span>
									</a>
									<div class="attachments-fields">
										<div class="textfield field_attachment_title" id="field_attachment_title_<?php echo $attachment_index ; ?>">
											<input type="text" id="attachment_title_<?php echo $attachment_index; ?>" name="attachment_title_<?php echo $attachment_index; ?>" value="<?php echo $attachment['title']; ?>" size="20" />
										</div>
										<div class="textfield field_attachment_caption" id="field_attachment_caption_<?php echo $attachment_index; ?>">
											<input type="text" id="attachment_caption_<?php echo $attachment_index; ?>" name="attachment_caption_<?php echo $attachment_index; ?>" value="<?php echo $attachment['caption']; ?>" size="20" />
										</div>
									</div>
									<div class="attachments-data">
										<input type="hidden" name="attachment_id_<?php echo $attachment_index; ?>" id="attachment_id_<?php echo $attachment_index; ?>" value="<?php echo $attachment['id']; ?>" />
										<input type="hidden" class="attachment_order" name="attachment_order_<?php echo $attachment_index; ?>" id="attachment_order_<?php echo $attachment_index; ?>" value="<?php echo $attachment['order']; ?>" />
									</div>
								</li>




                        <?php endforeach;
                    }
                }
                ?>
            </ul>
        </div>
    </div>
<?php }


/**
 * Creates meta box on all Posts and Pages
 *
 * @return void
 * @author Jonathan Christopher
 */
function attachments_meta_box()
{
	// if not setup, don't worry about doing anything
	global $hys;
	if (@$hys['settings']['no_attachments'] == 1) return;

    // for custom post types
    if( function_exists( 'get_post_types' ) )
    {
        $args = array(
            'public'    => true,
            'show_ui'   => true
            );
        $output         = 'objects';
        $operator       = 'and';
        $post_types     = get_post_types( $args, $output, $operator );

        foreach($post_types as $post_type)
        {
            if (get_option('attachments_cpt_' . $post_type->name)=='true')
            {
                add_meta_box( 'attachments_list', __( 'Attachments', 'attachments' ), 'attachments_add', $post_type->name, 'normal' );
            }
        }
    }
}


/**
 * Echos JavaScript that sets some required global variables
 *
 * @return void
 * @author Jonathan Christopher
 */
function attachments_init_js()
{
	// if not setup, don't worry about doing anything
	global $hys;
	if (@$hys['settings']['no_attachments'] == 1) return;

    global $pagenow;

    echo '<script type="text/javascript" charset="utf-8">';
    echo '  var attachments_base = "' . WP_PLUGIN_URL . '/heyyou/res"; ';
    echo '  var attachments_media = ""; ';
    echo '</script>';
}


/**
 * Fired when Post or Page is saved. Serializes all attachment data and saves to post_meta
 *
 * @param int $post_id The ID of the current post
 * @return void
 * @author Jonathan Christopher
 * @author JR Tashjian
 */
function attachments_save($post_id)
{

	// if not setup, don't worry about doing anything
	global $hys;
	

	
	if (@$hys['settings']['no_attachments'] == 1) return;

    // verify this came from the our screen and with proper authorization,
    // because save_post can be triggered at other times
    if( !isset( $_POST['attachments_nonce'] ) )
    {
        return $post_id;
    }

    if( !wp_verify_nonce( $_POST['attachments_nonce'], plugin_basename(__FILE__) ) )
    {
        return $post_id;
    }

    // verify if this is an auto save routine. If it is our form has not been submitted, so we dont want
    // to do anything
    if( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE )
    {
        return $post_id;
    }

    // Check permissions
    if( 'page' == @$_POST['post_type'] )
    {
        if( !current_user_can( 'edit_page', $post_id ) )
        {
            return $post_id;
        }
    }
    else
    {
        if( !current_user_can( 'edit_post', $post_id ) )
        {
            return $post_id;
        }
    }
    
    

    // OK, we're authenticated: we need to find and save the data

    // delete all current attachments meta
    // moved outside conditional, else we can never delete all attachments
    delete_post_meta( $post_id, '_attachments' );

    // Since we're allowing Attachments to be sortable, we can't simply increment a counter
    // we need to keep track of the IDs we're given
    $attachment_ids = array();

    // We'll build our array of attachments
    foreach( $_POST as $key => $data )
    {
        // Arbitrarily using the id
        if( substr($key, 0, 14) == 'attachment_id_' )
        {
            array_push( $attachment_ids, substr( $key, 14, strlen( $key ) ) );
        }

    }

    // If we have attachments, there's work to do
    if( !empty( $attachment_ids ) )
    {
    
        foreach ( $attachment_ids as $i )
        {
        	//@heyyou edit
        	$the_attach_url = wp_get_attachment_url( $_POST['attachment_id_' . $i] ) ; // only attach if the media item exsists in library
        	
            if( !empty( $_POST['attachment_id_' . $i] ) && $the_attach_url )
            {

                $attachment_id      = intval( $_POST['attachment_id_' . $i] );

                $attachment_details = array(
                    'id'                => $attachment_id,
                    'title'             => str_replace( '"', '&quot;', $_POST['attachment_title_' . $i] ),
                    'caption'           => str_replace( '"', '&quot;', $_POST['attachment_caption_' . $i] ),
                    'order'             => intval( $_POST['attachment_order_' . $i] )
                    );

                // serialize data and encode
                $attachment_serialized = base64_encode( serialize( $attachment_details ) );

                // add individual attachment
                add_post_meta( $post_id, '_attachments', $attachment_serialized );
                
                // save native Attach
/*
                if( get_option( 'attachments_store_native' ) == 'true' )
                {
                    // need to first check to make sure we're not overwriting a native Attach
                    $attach_post_ref                = get_post( $attachment_id );

                    if( $attach_post_ref->post_parent == 0 )
                    {
                        // no current Attach, we can add ours
                        $attach_post                    = array();
                        $attach_post['ID']              = $attachment_id;
                        $attach_post['post_parent']     = $post_id;

                        wp_update_post( $attach_post );
                    }
                }
*/
            } 
        }

    }
}


/**
 * Returns a formatted filesize
 *
 * @param string $path Path to file on disk
 * @return string $formatted formatted filesize
 * @author Jonathan Christopher
 */
function attachments_get_filesize_formatted( $path = NULL )
{
    global $units;
    $formatted = '0 bytes';
//@heyyou edit: comment out, unneeded
/*
    if( file_exists( $path ) )
    {
        $bytes      = intval( filesize( $path ) );
        $s          = $units;
        $e          = floor( log( $bytes ) / log( 1024 ) );
        $formatted  = sprintf( '%.2f ' . $s[$e], ( $bytes / pow( 1024, floor( $e ) ) ) );
    }
*/
    return $formatted;
}


/**
 * Retrieves all Attachments for provided Post or Page
 *
 * @param int $post_id (optional) ID of target Post or Page, otherwise pulls from global $post
 * @return array $post_attachments
 * @author Jonathan Christopher
 * @author JR Tashjian
 */

function attachments_get_attachments( $post_id=null )
{
    global $post;

    if( $post_id==null )
    {
        $post_id = $post->ID;
    }

    // get all attachments
    $existing_attachments = get_post_meta( $post_id, '_attachments', false );

    if( !empty( $existing_attachments ) )
    {
            $legacy_existing_attachments = @unserialize( $existing_attachments[0] );
    }

    // Check for legacy attachments
    if( isset( $legacy_existing_attachments ) )
    {
        if( is_array( $legacy_existing_attachments ) )
        {
            $tmp_legacy_attachments = array();

            // Legacy attachments (single serialized record)
            foreach ( $legacy_existing_attachments as $legacy_attachment )
            {
                array_push( $tmp_legacy_attachments, base64_encode( serialize( $legacy_attachment ) ) );
            }

            $existing_attachments = $tmp_legacy_attachments;
        }
    }


    // We can now proceed as normal, all legacy data should now be upgraded

    $post_attachments = array();

    if( is_array( $existing_attachments ) && count( $existing_attachments ) > 0 )
    {

        foreach ($existing_attachments as $attachment)
        {
            // decode and unserialize the data
            $data = unserialize( base64_decode( $attachment ) );
            //@heyyou edit: only show if the img exsists in the library
			if (wp_get_attachment_url( $data['id'] )) { // 
            	array_push( $post_attachments, array(
                'id' 			=> stripslashes( $data['id'] ),
                'name' 			=> stripslashes( get_the_title( $data['id'] ) ),
                'mime' 			=> stripslashes( get_post_mime_type( $data['id'] ) ),
                'title' 		=> stripslashes( $data['title'] ),
                'caption' 		=> stripslashes( $data['caption'] ),
                'filesize'      => stripslashes( attachments_get_filesize_formatted( get_attached_file( $data['id'] ) ) ),
                'location' 		=> stripslashes( wp_get_attachment_url( $data['id'] ) ),
                'order' 		=> stripslashes( $data['order'] )
                ));
			} else {
				// dont show if the img doesn't exsist //@heyyou edit:
			}
        }

        // sort attachments
        if( count( $post_attachments ) > 1 )
        {
            usort( $post_attachments, "attachments_cmp" );
        }
    }

    return $post_attachments;
}


/**
 * Outputs Attachments JS into the footer
 *
 * @return void
 * @author Jonathan Christopher
 */
function attachments_footer_js()
{
	// if not setup, don't worry about doing anything
	global $hys;
	if (@$hys['settings']['no_attachments'] == 1) return;

    $uri    = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : NULL ;
    $file   = basename( parse_url( $uri, PHP_URL_PATH ) );

    if( ($uri && in_array( $file, array( 'post.php', 'post-new.php' ) )) || (isset($_GET['page']) && $_GET['page'] == 'editheyyoupost') )
    {
        // we only want this to fire on edit screens
        echo '<script type="text/javascript">';
        include 'js/attachments.js';
        echo '</script>';
    }
}


/**
 * This is the main initialization function, it will invoke the necessary meta_box
 *
 * @return void
 * @author Jonathan Christopher
 */
function attachments_init()
{
	// if not setup, don't worry about doing anything
	global $pagenow, $hys;
	if (@$hys['settings']['no_attachments'] == 1) return;

    wp_enqueue_script( 'jquery-ui-core' );
    wp_enqueue_script( 'thickbox' );

    wp_enqueue_style( 'thickbox' );
#    wp_enqueue_style( 'attachments', WP_PLUGIN_URL . '/attachments/css/attachments.css' );

    if( function_exists( 'load_plugin_textdomain' ) )
    {
        if( !defined('WP_PLUGIN_DIR') )
        {
            load_plugin_textdomain( 'attachments', str_replace( ABSPATH, '', dirname( __FILE__ ) ) );
        }
        else
        {
            load_plugin_textdomain( 'attachments', false, dirname( plugin_basename( __FILE__ ) ) );
        }
    }

    attachments_meta_box();
}


/**
 * Modifies the plugin meta line on the WP Plugins page
 *
 * @return $plugin_meta Array of plugin meta data
 * @author Jonathan Christopher
 */
function attachments_filter_plugin_row_meta( $plugin_meta, $plugin_file )
{

    if( strstr( $plugin_file, 'attachments/attachments.php' ) )
    {
        $plugin_meta[2] = '<a title="Attachments Pro" href="http://mondaybynoon.com/store/attachments-pro/">Attachments Pro</a>';
        $plugin_meta[3] = 'Visit <a title="Iron to Iron" href="http://irontoiron.com/">Iron to Iron</a>';
        return $plugin_meta;
    }
    else
    {
        return $plugin_meta;
    }
}


?>