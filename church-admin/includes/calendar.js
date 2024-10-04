jQuery(document).ready(function( $) {
			$(".ca-item").click(function(e) {
    			e.preventDefault();
    			console.log('id'+this.id);
    			var data = {"action": "church_admin_calendar_event_display","date": this.id};
    			
				// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
				jQuery.post(ajaxurl, data, function(response) {
					console.log(response);
					var res=JSON.parse(response);
					$("#popup"+res.id).html(res.output);
					$("#popup"+res.id).show();
    			});
			});
			$("body").on("click",".ca-calendar-wrapper",function()  {$(".ca-calendar-overlay").hide();});
	

		});