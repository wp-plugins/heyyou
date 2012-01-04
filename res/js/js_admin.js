	
jQuery(document).ready(function() {
  		
	jQuery('.hys_media_library_list li input').change(function() { 
		
		

		$chcked = jQuery(this).is(':checked');
		
		if ( $chcked ) {
			jQuery(this).parent().parent().parent().css('background','#fffeed')
		} else {
			jQuery(this).parent().parent().parent().css('background','#FCFCFC')
		}
		
	});
		  

});






	function mediacat_highlight(mainid) {
	
		feilds = document.getElementsByClassName('mediacat_highlight');
		for (i = 0; i < feilds.length; i++) {
			id = feilds[i].id
			document.getElementById(id).style.color='#999'
		}
	
		document.getElementById(mainid).style.color='#000'
			
	}


	function change_url_color(focusblur, id) {
		if (focusblur == 1) { //focus
			if (document.getElementById(id).value == 'http://') {
			  document.getElementById(id).value = 'http://'
			  document.getElementById(id).style.color='#000'
			}
		} else { // blur
			if (document.getElementById(id).value == '' || document.getElementById(id).value == 'http://') {
			  document.getElementById(id).value = 'http://'
			  document.getElementById(id).style.color='#aaa'
			}
		}
	}
	
	onload=function(){
	
		feilds = document.getElementsByClassName('hys_url_meta_feild');
		
	
		for (i = 0; i < feilds.length; i++) {
			id = feilds[i].id
			if (document.getElementById(id).value == 'http://') {
			   document.getElementById(id).style.color='#aaa'
			}
		}
	}	
	
			function meta_thumb_preview_off() {
				feilds = document.getElementsByClassName('hys_meta_thumb_full');
				for (i = 0; i < feilds.length; i++) {
					id = feilds[i].id
					document.getElementById(id).style.display='none'
				}
			}
	
	
			function meta_thumb_preview(theid) {
				//turn all tooltips off
				meta_thumb_preview_off();
				//turn only hovered on
			  	document.getElementById(theid).style.display='block'
			}
	
	function checkAll(field,toselect) {
		
		var thisset=toselect.split(',');
		
		var start 	= parseInt(thisset[0]) - 1
		var thelast = parseInt(thisset.length) - 2
		var end 	= parseInt(thisset[thelast])
		
		var allchecked = 1
		for (i = start; i < end; i++)
			if (field[i].checked != true && allchecked != 0)
				allchecked = 0 //not all checked
		
		var check = (allchecked == 1) ? false : true;
		
		for (i = start; i < end; i++) {
			field[i].checked = check;
			
		}

	}
	
	var doConfirm = function(id,msg) {
		msg = (msg) ? msg : "Really?"
		var link = document.getElementById(id);
		if(confirm(msg)) return true;	
		else return false;
	}
	
	function addtext(id) {
		var myTextField = document.getElementById(id);
		myTextField.value += "<!--more-->";
	}
	
	function form_focus(id) {
		document.getElementById(id).style.color='#333333'
		va = document.getElementById(id).value
		if (va == 'Add New Category')
		 document.getElementById(id).value = ''
	}
	
	function form_blur(id) {
		va = document.getElementById(id).value
		if (va == '') {
		  document.getElementById(id).value = 'Add New Category'
		  document.getElementById(id).style.color='#999999'
		}
	}	



