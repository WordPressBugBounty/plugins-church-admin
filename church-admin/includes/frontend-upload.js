(function( $) {

$(document).ready( function() {
	var file_frame; // variable for the wp.media file_frame
	
	// attach a click event (or whatever you want) to some element on your page
	$( 'body' ).on( 'click','.frontend-button', function( event ) {
		
		event.preventDefault();
		var num=this.id;//get the number of which cloned element we are uploading too.
		
      	//recreate the file_frame each time because we are using cloned input and the id won't change if reused

		file_frame = wp.media.frames.file_frame = wp.media({
			title: $( this ).data( 'uploader_title' ),
			button: {
				text: $( this ).data( 'uploader_button_text' ),
			},
			multiple: false // set this to true for multiple file selection
		});

		file_frame.on('select',function() {
			attachment = file_frame.state().get('selection').first().toJSON();
			
		
			// do something with the file here
			$( '#frontend-button'+num ).hide();
			$( '#frontend-image'+num ).attr('src', attachment.url);
			$( '#logo_url' ).val(attachment.url);
			$('#attachment_id'+num).val(attachment.id);
		});

		file_frame.open();
	});
});

})(jQuery);


