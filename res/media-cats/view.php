<div class='wrap'>
<?php

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////   START PROPERTIES
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

global $admin_records_per_page;

$admin_records_per_page=get_option('admin_records_per_page');
if (empty($admin_records_per_page)) {
	$admin_records_per_page="10";
	add_option('admin_records_per_page', $admin_records_per_page);
	}



$hidden="";
foreach($_GET as $key=>$val) {
	//hidden keys to keep same view after form submission
	if ($key!="q" && $key!="o" && $key!="d" && $key!="changeView" && $key!="start") {
		$hidden.="<input type='hidden' value='$val' name='$key'>\n"; 
	}
}


$optionsName = 'mc_options';
$options = get_option($optionsName);
            
 

$defaultTermId = 0;

if($options['mc_default_media_category'] !=null ||$options['mc_default_media_category'] =='')
{
	$defaultTermId=$options['mc_default_media_category'];
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////   END PROPERTIES
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////   START EVENT HANDLERS
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if ($_GET[delete]!="") {
	//If delete link is clicked
	wp_delete_term( $_GET[delete], 'media_category' ) ;
	print "<script>location.replace('".ereg_replace("&delete=$_GET[delete]", "", $_SERVER[REQUEST_URI])."');</script>";
	
}
 if ($_GET[edit] && $_POST[act]!="delete") {
	//If edit link is clicked
	$term_id=$_GET[edit];

	/*
print '<script type="text/javascript">
	<!--
	window.location = "admin.php?page='.$rl_dir.'/add.php&edit='.$term_id.'"
	//-->
	</script>';
*/
	
} 

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////   END EVENT HANDLERS
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

print "
<link media='all' type='text/css' href=\"/wp-admin/css/colors-fresh.css\" id=\"colors-css\" rel=\"stylesheet\">
<form>
	<table cellpadding='0px' cellspacing='0px' width='100%'>
		<tr>
			<td>
				<h2>Manage Media Categories <a class='button add-new-h2' href='admin.php?page=$rl_dir/add.php'>Add Media Category</a> </h2>
			</td>
			
		</tr>
		<tr>
			<td align='right'>
				<h2 style='float:right; padding-right:0px; width:100%'><input value='$_GET[q]' name='q'><input type='submit' value='Search' class='button'></h2>
				$hidden
			</td>
		</tr>
	</table>
</form>";


function array_search_i($str,$terms){
	$return = array();
    foreach($terms as $term) {
		if (strlen(stristr($term["name"],$str))>0)
		{
			$return[]=$term;
		}
    }
    return $return;
} 
print "<form name='itemsForm' method='post'>";

		$start=($_GET[start]=="")? 0 : $_GET[start];
		$num_per_page=$admin_records_per_page; 
		
		
	
		
		$_terms = $mc_var->get_category_hierarchical_terms();
		
		$search ='';
		if($_GET[q]!="")
		{
			$search=$_GET[q];
			$_terms=array_search_i($search, $_terms);
		}
		
		$countCategories = count($_terms);
		
		//get paged amount
		$terms = array_slice($_terms,$start, $num_per_page);
		
		if ($countCategories!=0) {
			//include("$rl_path/search.php");
		}


print "

<div style='overflow:hidden;'>
	<div style='width:40%;float:left;'>
		<h4>Add New Media Category</h4>
	</div>
	<div style='width:55%;float:right;'>
		<h4>Manage Media Category</h4>";

echo "
<table class='widefat' cellspacing=0>
    <thead>
        <tr>
        <th colspan='1'>Actions</th>
        <th>Category</th>
        <th>&nbsp;</th>
        <!--<th>Description</th>-->
        <th></th>
		
        <th>&nbsp;</th>
        </tr>
    </thead>";
	
	if($countCategories>0)
	{

		foreach( $terms as $term ) : 
			
			$bgcol=($bgcol=="" || $bgcol=="#fcfcfc")?"#fff":"#fcfcfc";			
			
			
			
			echo "
            <tr style='background-color:$bgcol' class='catyactionsrow'>
			<th>
			<div class='catyactions'>
				<a href='".ereg_replace("&edit=$_GET[edit]", "",$_SERVER[REQUEST_URI])."&edit=" . $term['id'] ."#a".$term['id']."'>Edit</a>
				";
			if($term['id'] != $defaultTermId)
			{
				print "&nbsp;|&nbsp;
				<a href='$_SERVER[REQUEST_URI]&delete=" . $term['id'] ."' onclick=\"return showNotice.warn();\">Delete</a>
				";
			}
			print "	
			</div>
			</th>
			<td>".$term['name']."</td>
            <td>&nbsp;</td>
			<td>&nbsp;<!--[mediacategories categories=\"".$term['id']."\"--></td>
            <td><!--<a href='admin.php?page=$rl_dir/sort.php&termid=" . $term['id'] ."'>Sort Media Items (" . $term['count'] .")</a>--></td>
            </tr>";
			
			
		endforeach;

		
	}
	else {
		$notice=($_GET[q]!="")? "No Categories Showing for this Search of "."<b>\"$_GET[q]\"</b>. $view_rllink" :"No Categories Currently in Database";
		//print "<tr><td colspan='5'>$notice | <a href='admin.php?page=$rl_dir/add.php'>Add Media Category</a></td></tr>";
	}
    
    
print " <tfoot>
        <tr>
        <th colspan='1'>Actions</th>
        <th>Category</th>
        <th>&nbsp;</th>
        <!--<th>Description</th>-->
        <th>&nbsp;</th>
        <th>&nbsp;</th>
        </tr>
    </tfoot>
</table>


<input name='act' type='hidden'><br>";

echo "
	</div>
</div>";


//if ($countCategories!=0) {include("$rl_path/search.php");}



print "</form>";

	
?>
</div>