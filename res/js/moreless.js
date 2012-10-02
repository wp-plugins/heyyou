//animated more/less
jQuery(document).ready(function() {  
	jQuery('a.hys_readmore').attr('onclick','');
	jQuery('a.hys_readless').attr('onclick','');

	// onclick MORE: let's do so stuff
	jQuery('.hys_readmore').click( function() {
		$getid = jQuery(this).attr('id').replace('morelink', '');
		jQuery('#moreless'+$getid).slideDown();
		jQuery(this).hide();
	} );
	
	// onclick LESS: let's do so stuff
	jQuery('.hys_readless').click( function() {
		$getid = jQuery(this).attr('id').replace('lesslink', '');
		jQuery('#moreless'+$getid).slideUp('slow',function(){ jQuery('#morelink'+$getid+'').fadeIn(); });
	} );
});