//ADMIN & FRONT END

	function showHide(shID) {
		if (document.getElementById(shID)) {
			if (document.getElementById(shID+'-show').style.display != 'none') {
				document.getElementById(shID+'-show').style.display = 'none';
				document.getElementById(shID).style.display = 'block';
			} else {
				document.getElementById(shID+'-show').style.display = 'inline';
				document.getElementById(shID).style.display = 'none';
			}
		}
	}
	function showhide(id) {
		var e = document.getElementById(id);
		if(e.style.display == 'none')
		e.style.display = 'block';
		else
		e.style.display = 'none';
	}
	function showhideinlineblock(id) {
		var e = document.getElementById(id);
		if(e.style.display == 'none')
		e.style.display = 'inline-block';
		else
		e.style.display = 'none';
	}	
	function showhide_inline(id) {
		var e = document.getElementById(id);
		if(e.style.display == 'none')
		e.style.display = 'inline-block';
		else
		e.style.display = 'none';
	}
	
//end