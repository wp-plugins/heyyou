<?php

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
