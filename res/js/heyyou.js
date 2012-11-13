//showhide('moreless396'); showhide('morelink396');

function showhideinlineblock(id) {
	showhide(id,1)
}

function showhide_inline(id) {
	showhide(id,1)
}

function showhide(id,inline) {
	inline = (inline) ? "inline-" : ''
	current = jQuery('#'+id).css('display');
	newdisplay = (current == 'none') ? inline+"block" : 'none'
	
	// revert trying to collapes itself even though it shouldn't 
	// because `auto_hidecontent` is set
	
	isalink = (id.indexOf("morelink") != -1) ? true : false;
	isalink = (id.indexOf("lesslink") != -1) ? true : isalink;
	
	if (jQuery('#'+id).hasClass('auto_hidecontent')) {
    	if (id.indexOf("morelink") != -1) {
        	newdisplay = "block"
        }
	}
	if (jQuery('body').hasClass('animated_moreless') && !isalink) {
		if (newdisplay == "block")
    		jQuery('#'+id).slideDown()
    	else 
    		jQuery('#'+id).slideUp()
	} else {
    	jQuery('#'+id).css('display',newdisplay)
	}
}

function showHide(id) {
	if (jQuery('#'+id)) {
		showstyle = jQuery('#'+id+"-show").css('display')
		if (showstyle != 'none') {
			if (jQuery('body').hasClass('animated_moreless')) {
				jQuery('#'+id+"-show").slideUp()
				jQuery('#'+id+"").slideDown()
			} else {
				jQuery('#'+id+"-show").css('display','none')
				jQuery('#'+id+"").css('display','block')
			}
		} else {
			if (jQuery('body').hasClass('animated_moreless')) {
				jQuery('#'+id+"-show").slideDown()
				jQuery('#'+id+"").slideUp()
			} else {
				jQuery('#'+id+"-show").css('display','inline')
				jQuery('#'+id+"").css('display','none')
			}
		}
	}
}




