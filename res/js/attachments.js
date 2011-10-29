var attachments_button_label_updater = null;
var attachments_button_label = 'Attach File';
var attachments_asset = null;
var attachments_hijacked_thickbox = false;

function init_attachments_sortable() {
    if (jQuery('div#attachments-list ul:data(sortable)').length == 0 && jQuery('div#attachments-list ul li').length > 0) {
        jQuery('div#attachments-list ul').sortable({
            containment: 'parent',
            stop: function (e, ui) {
                jQuery('#attachments-list ul li').each(function (i, id) {
                    jQuery(this).find('input.attachment_order').val(i + 1);
                });
            }
        });
    }
}

function attachments_handle_attach(title, caption, id, thumb) {
    attachment_index = jQuery('li.attachments-file', top.document).length;
    new_attachments = '';
    attachment_name = title;
    attachment_caption = caption;
    attachment_id = id;
    attachment_thumb = thumb;
    attachment_index++;
/*
    new_attachments += '<li class="attachments-file">';
    new_attachments += '<h2><a href="#" class="attachment-handle"><span class="attachment-handle-icon"><img src="' + attachments_base + '/images/handle.gif" alt="Drag" /></span></a><span class="attachment-name">' + attachment_name + '</span><span class="attachment-delete"><a href="#">Delete</a></span></h2>';
    new_attachments += '<div class="attachments-fields">';
    new_attachments += '<div class="textfield" id="field_attachment_title_' + attachment_index + '"><label for="attachment_title_' + attachment_index + '">Title</label><input type="text" id="attachment_title_' + attachment_index + '" name="attachment_title_' + attachment_index + '" value="' + '' + '" size="20" /></div>';
    new_attachments += '<div class="textfield" id="field_attachment_caption_' + attachment_index + '"><label for="attachment_caption_' + attachment_index + '">Caption</label><input type="text" id="attachment_caption_' + attachment_index + '" name="attachment_caption_' + attachment_index + '" value="' + attachment_caption + '" size="20" /></div>';
    new_attachments += '</div>';
    new_attachments += '<div class="attachments-data">';
    new_attachments += '<input type="hidden" name="attachment_id_' + attachment_index + '" id="attachment_id_' + attachment_index + '" value="' + attachment_id + '" />';
    new_attachments += '<input type="hidden" class="attachment_order" name="attachment_order_' + attachment_index + '" id="attachment_order_' + attachment_index + '" value="' + attachment_index + '" />';
    new_attachments += '</div>';
    new_attachments += '<div class="attachment-thumbnail"><span class="attachments-thumbnail">';
    new_attachments += '<img src="' + attachment_thumb + '" alt="Thumbnail" />';
    new_attachments += '</span></div>';
    new_attachments += '</li>';
*/



new_attachments += '<li class="attachments-file">';


new_attachments += '	<span class="attachment-delete">';
new_attachments += '		<a href="#" title="Remove '+attachment_name+'"><img src="' + attachments_base + '/imgs/delete.png" alt="" class="hys_admin_ico" /></a>';
new_attachments += '	</span>';

	

new_attachments += '	<a class="attachment-handle">';
new_attachments += '		<span class="attachment-handle-icon">';
new_attachments += '			<img src="'+attachment_thumb+'" class="attachment_thumbnail_main" />';
new_attachments += '		</span>';
new_attachments += '	</a>';



new_attachments += '	<div class="attachments-fields">';
new_attachments += '		<div class="textfield field_attachment_title" id="field_attachment_title_'+attachment_index+'">';
new_attachments += '			<input type="text" id="attachment_title_'+attachment_index+'" name="attachment_title_'+attachment_index+'" value="'+''+'" size="20" />';
new_attachments += '		</div>';
new_attachments += '		<div class="textfield field_attachment_caption" id="field_attachment_caption_'+attachment_index+'">';
new_attachments += '			<input type="text" id="attachment_caption_'+attachment_index+'" name="attachment_caption_'+attachment_caption+'" value="'+''+'" size="20" />';
new_attachments += '		</div>';
new_attachments += '	</div>';


new_attachments += '	<div class="attachments-data">';
new_attachments += '		<input type="hidden" name="attachment_id_'+attachment_index+'" id="attachment_id_'+attachment_index+'" value="'+attachment_id+'" />';
new_attachments += '		<input type="hidden" class="attachment_order" name="attachment_order_'+attachment_index+'" id="attachment_order_'+attachment_index+'" value="'+attachment_index+'" />';
new_attachments += '	</div>';

new_attachments += '</li>';





    jQuery('div#attachments-list ul', top.document).append(new_attachments);
    if (jQuery('#attachments-list li', top.document).length > 0) {
        jQuery('#attachments-list', top.document).show();
    }
}
jQuery(document).ready(function () {
    if (typeof send_to_editor === 'function') {
        var attachments_send_to_editor_default = send_to_editor;
        send_to_editor = function (markup) {
            clearInterval(attachments_button_label_updater);
            if (attachments_hijacked_thickbox) {
                attachments_hijacked_thickbox = false;
            } else {
                attachments_send_to_editor_default(markup);
            }
        }
    }

    function attachments_update_button_label() {
        if (attachments_hijacked_thickbox) {
            jQuery('#TB_iframeContent').contents().find('td.savesend input').unbind('click').click(function (e) {
                theparent = jQuery(this).parent().parent().parent();
                jQuery(this).after('<span class="attachments-attached">Attached!</span>');
                thetitle = theparent.find('tr.post_title td.field input').val();
                thecaption = theparent.find('tr.post_excerpt td.field input').val();
                theid = theparent.find('td.imgedit-response').attr('id').replace('imgedit-response-', '');
                thethumb = theparent.parent().parent().find('img.pinkynail').attr('src');
                attachments_handle_attach(thetitle, thecaption, theid, thethumb);
                theparent.find('span.attachments-attached').delay(1000).fadeOut('fast');
                return false;
            });
            if (jQuery('#TB_iframeContent').contents().find('.media-item .savesend input[type=submit], #insertonlybutton').length) {
                jQuery('#TB_iframeContent').contents().find('.media-item .savesend input[type=submit], #insertonlybutton').val(attachments_button_label);
            }
            if (jQuery('#TB_iframeContent').contents().find('#tab-type_url').length) {
                jQuery('#TB_iframeContent').contents().find('#tab-type_url').hide();
            }
            if (jQuery('#TB_iframeContent').contents().find('tr.post_title').length) {
                jQuery('#TB_iframeContent').contents().find('tr.image-size input[value="full"]').prop('checked', true);
                jQuery('#TB_iframeContent').contents().find('tr.post_title,tr.image_alt,tr.post_excerpt,tr.image-size,tr.post_content,tr.url,tr.align,tr.submit>td>a.del-link').hide();
            }
        }
        if (jQuery('#TB_iframeContent').contents().length == 0 && attachments_hijacked_thickbox) {
            clearInterval(attachments_button_label_updater);
            attachments_hijacked_thickbox = false;
        }
    }
    jQuery('a#attachments-thickbox').live('click', function (event) {
        var href = jQuery(this).attr('href'),
            width = jQuery(window).width(),
            H = jQuery(window).height(),
            W = (720 < width) ? 720 : width;
        if (!href) return;
        href = href.replace(/&width=[0-9]+/g, '');
        href = href.replace(/&height=[0-9]+/g, '');
        jQuery(this).attr('href', href + '&width=' + (W - 80) + '&height=' + (H - 85));
        attachments_hijacked_thickbox = true;
        attachments_button_label_updater = setInterval(attachments_update_button_label, 500);
        tb_show('Attach a file', event.target.href, false);
        return false;
    });
    if (jQuery('div#attachments-list li').length == 0) {
        jQuery('#attachments-list').hide();
    }
    jQuery('span.attachment-delete a').live('click', function () {
        attachment_parent = jQuery(this).parent().parent();
        attachment_parent.slideUp(function () {
            attachment_parent.remove();
            jQuery('#attachments-list ul li').each(function (i, id) {
                jQuery(this).find('input.attachment_order').val(i + 1);
            });
            if (jQuery('div#attachments-list li').length == 0) {
                jQuery('#attachments-list').slideUp(function () {
                    jQuery('#attachments-list').hide();
                });
            }
        });
        return false;
    });
    setInterval('init_attachments_sortable()', 500);
});