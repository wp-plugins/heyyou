(function() {
	tinymce.create('tinymce.plugins.Yourhys_line', {
		init : function(ed, url) {
			ed.addButton('yourhys_line', {
				title : 'yourhys_line.hys_line',
				image : url+'/hys_line.png',
				onclick : function() {
					idPattern = /(?:(?:[^v]+)+v.)?([^&=]{11})(?=&|$)/;
					ed.execCommand('mceInsertContent', false, '<div class="line">..........................</div>');
				}
			});
		},
		createControl : function(n, cm) {
			return null;
		},
		getInfo : function() {
			//...
		}
	});
	tinymce.PluginManager.add('yourhys_line', tinymce.plugins.Yourhys_line);
	
	tinymce.create('tinymce.plugins.Yourhys_space', {
		init : function(ed, url) {
			ed.addButton('yourhys_space', {
				title : 'yourhys_space.hys_space',
				image : url+'/hys_space.png',
				onclick : function() {
					idPattern = /(?:(?:[^v]+)+v.)?([^&=]{11})(?=&|$)/;
					ed.execCommand('mceInsertContent', false, '<div class="space"></div>');
				}
			});
		},
		createControl : function(n, cm) {
			return null;
		},
		getInfo : function() {
			//...
		}
	});
	tinymce.PluginManager.add('yourhys_space', tinymce.plugins.Yourhys_space);
})();