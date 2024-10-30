(function($) {
    $(function() {
		$(".upload_image_button").click(function() {
			var send_attachment_bkp = wp.media.editor.send.attachment;
			var button = $(this);
			wp.media.editor.send.attachment = function(props, attachment) {
				//$(button).parent().prev().attr('src', attachment.url);
				$(button).parent().prev().attr('src', attachment.url);
				$(button).prev().prev().val(attachment.id);
				$(button).prev().prop('disabled', false);
				wp.media.editor.send.attachment = send_attachment_bkp;
			}
			wp.media.editor.open(button);
			return false;
		});

		// The "Remove" button (remove the value from input type='hidden')
		$('.remove_image_button').click(function() {
			var answer = confirm('Are you sure?');
			if (answer == true) {
				var src = $(this).parent().prev().attr('data-src');
				$(this).parent().prev().attr('src', src);
				$(this).prev().val('');
				$(this).prop('disabled', true);
			}
			return false;
		});
    });
})(jQuery);