jQuery(document).ready(function() {
	jQuery('.hys_media_library_list li input').change(function() { 
		$chcked = jQuery(this).is(':checked');
		if ( $chcked ) {
			jQuery(this).parent().parent().parent().css('background','#fffeed')
		} else {
			jQuery(this).parent().parent().parent().css('background','#FCFCFC')
		}
	});
	
	jQuery('.magnifier').hoverIntent(
	     function() { // over
			parent = jQuery(this).parent().attr('id')
			jQuery('#'+parent+' .magnifi_me').fadeIn();
			newimgsrc = jQuery('#'+parent+' .magnifi_me').attr('rel')
			jQuery('#'+parent+' img').attr('src',newimgsrc)
	     },
	     function() { // out
			parent = jQuery(this).parent().attr('id')
			jQuery('#'+parent+' .magnifi_me').hide();
	     }
	);
	
	// some checkbox, if checked, reveal other boxes, 
	// run on load
	change_checkbox_visibility()
	
	//run every time a checkbox is un/checked
	jQuery('input').bind('change',function(){
	      change_checkbox_visibility();
	});
	
	function change_checkbox_visibility() {	
		var feilds = [
			{input:"#undercon",div:".if_undercon"}, 
			{input:"#show_pg_img",div:"#if_show_pg_img"}, 
			{input:"#include_blurb",div:"#if_include_blurb"},
			{input:"#single_hys",div:"#if_single_hys"},
			{input:"#include_attach",div:"#if_include_attach"},
			{input:"#hidecontent",div:"#if_hidecontent"},
			{input:"#attachments_gallery",div:"#if_attachments_gallery"},
			{input:"#show_lines",div:"#if_show_lines"},
			{input:"#show_pagination",div:"#if_show_pagination"},
			{input:"#facebook_using",div:"#if_facebook_using"},
			
			{input:"#twitter_using",div:"#if_twitter_using"},
			{input:"#youtube_using",div:"#if_youtube_using"},
			{input:"#linkedin_using",div:"#if_linkedin_using"},
			{input:"#pinterest_using",div:"#if_pinterest_using"},
			{input:"#mailing_list_using",div:"#if_mailing_list_using"},
			{input:"#google_plus_using",div:"#if_google_plus_using"},
			//{input:"",div:""},
			//{input:"",div:""},
			//{input:"",div:""},
			//{input:"",div:""},
			//{input:"",div:""},
			//{input:"",div:""},
		]; 
		var x;
		for (x in feilds)
			jQuery(feilds[x].input).is(':checked') ? jQuery(feilds[x].div).show() : jQuery(feilds[x].div).hide()
	}

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


/**
* hoverIntent is similar to jQuery's built-in "hover" function except that
* instead of firing the onMouseOver event immediately, hoverIntent checks
* to see if the user's mouse has slowed down (beneath the sensitivity
* threshold) before firing the onMouseOver event.
* 
* hoverIntent r6 // 2011.02.26 // jQuery 1.5.1+
* http://cherne.net/brian/resources/jquery.hoverIntent.html
* 
*/
(function($) {
	$.fn.hoverIntent = function(f,g) {
		var cfg = {
			sensitivity: 7,
			interval: 100,
			timeout: 0
		};
		cfg = $.extend(cfg, g ? { over: f, out: g } : f );
		var cX, cY, pX, pY;
		var track = function(ev) {
			cX = ev.pageX;
			cY = ev.pageY;
		};
		var compare = function(ev,ob) {
			ob.hoverIntent_t = clearTimeout(ob.hoverIntent_t);
			if ( ( Math.abs(pX-cX) + Math.abs(pY-cY) ) < cfg.sensitivity ) {
				$(ob).unbind("mousemove",track);
				ob.hoverIntent_s = 1;
				return cfg.over.apply(ob,[ev]);
			} else {
				pX = cX; pY = cY;
				ob.hoverIntent_t = setTimeout( function(){compare(ev, ob);} , cfg.interval );
			}
		};
		var delay = function(ev,ob) {
			ob.hoverIntent_t = clearTimeout(ob.hoverIntent_t);
			ob.hoverIntent_s = 0;
			return cfg.out.apply(ob,[ev]);
		};
		var handleHover = function(e) {
			var ev = jQuery.extend({},e);
			var ob = this;
			if (ob.hoverIntent_t) { ob.hoverIntent_t = clearTimeout(ob.hoverIntent_t); }
			if (e.type == "mouseenter") {
				pX = ev.pageX; pY = ev.pageY;
				$(ob).bind("mousemove",track);
				if (ob.hoverIntent_s != 1) { ob.hoverIntent_t = setTimeout( function(){compare(ev,ob);} , cfg.interval );}
			} else {
				$(ob).unbind("mousemove",track);
				if (ob.hoverIntent_s == 1) { ob.hoverIntent_t = setTimeout( function(){delay(ev,ob);} , cfg.timeout );}
			}
		};
		return this.bind('mouseenter',handleHover).bind('mouseleave',handleHover);
	};
})(jQuery);