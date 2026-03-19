jQuery(document).ready(function( $ ) {
 /* version 2.6750 */   
           
    bindEvents();
    $("body").on('click',".ca-sermon-search",function(e)
    {               e.preventDefault();
					var page=$(this).attr('data-page');
                    
                    var limit=$(this).attr('data-limit');
                    var search=$(".sermon-search").val();
					var data = {
						"action":  "church_admin",
						"method": "sermon-search",
						"page":page,
                        "sermon-search":search,
                        "limit":limit,
                        "nonce":nonce
					};
					console.log(data);
					$.ajax({
						url: ajaxurl,
						type: 'post',
						data:data,
						success: function( response ) {
							console.log(response);
							$(".ca-media-file-list").html(response);
							bindEvents();
						},
					});

				});
    $("body").on('click',".ca-media-file-list .more-sermons",function()
    {
					var page=$(this).attr('data-page');
                    var series_id=$(this).attr('data-series');
                    var limit=$(this).attr('data-limit');
                    var speaker=$(this).attr('data-speaker');
                    var search=$(this).attr('data-search');
					var data = {
						"action":  "church_admin",
						"method": "more-sermons",
						"page":page,
                        "series_id":series_id,
                        "limit":limit,
                        "speaker":speaker,
                        "sermon-search":search,
                        "nonce":nonce
					};
					console.log(data);
					$.ajax({
						url: ajaxurl,
						type: 'post',
						data:data,
						success: function( response ) {
							console.log(response);
							$(".ca-media-file-list").html(response);
							bindEvents();
						},
					});

				});
    $("body").on("click",".ca-media-list-item",function()
    {
		var id=$(this).attr("data-id");
        var data = {
						"action":  "church_admin",
				        "method": "podcast-file",
				        "id":id,
                        "nonce":nonce
					};
		console.log(data);
		$.ajax({
        				url: ajaxurl,
        				type: 'post',
        				data:data,
        				success: function( response ) {

            					$(".ca-podcast-left-column").html(response);
            					bindEvents();
        				},
    				});
        $('html, body').animate({scrollTop: $("#ca-sermons").offset().top}, 1000);
    });
    $("body").on("change",".ca-speaker-dropdown",function()
	{
        console.log('SPEAKER DROPDOWN CHANGED')
                    $('body .sermon-search').val("");
                    var speaker='';
                    var order='DESC';
                    var series_id;
                    order=$("body .ca-order option:selected").val();
                    speaker=$("body .ca-speaker-dropdown option:selected").val();
                    series_id=$("body .ca-series-dropdown option:selected").val();
                    var page=$(this).data("page");
                    var limit=$(this).data("limit");
                    var data = {
								"action":  "church_admin",
								"method": "dropdown",
								"speaker":speaker,
                                "order":order,
                                "series_id":series_id,
                                "limit":limit,
                                "page":page,
                                "nonce":nonce
					};
                    console.log(data)
					$.getJSON({
        				url: ajaxurl,
        				type: 'post',
        				data:data,
        				success: function( response ) {
                                console.log(response)
            					$(".ca-media-file-list").html(response.file_list);
                                $(".ca-series-current").html(response.series_detail);
                                $(".ca-podcast-current").html(response.first_sermon);
            					bindEvents();
        				},
    				});
                });            
    
    $("body").on("change",".ca-series-dropdown",function()
    {
                    console.log('SERIES DROPDOWN CHANGED')
                    $('.sermon-search').val('');
                    var speaker;
                    var order='DESC';
                    var series_id;
                    order=$("body .ca-order option:selected").val();
                    speaker=$("body .ca-speaker-dropdown option:selected").val();
                    series_id=$("body .ca-series-dropdown option:selected").val();
                    console.log('Order:' + order + ' Speaker '+ speaker + ' series_id '+ series_id)
                    var page=$(this).data("page");
                    var limit=$(this).data("limit");
                    var data = {
								"action":  "church_admin",
								"method": "dropdown",
								"speaker":speaker,
                                "order":order,
                                "series_id":series_id,
                                "limit":limit,
                                "page":page,
                                "nonce":nonce
					};
                    console.log(data);
					$.getJSON({
        				url: ajaxurl,
        				type: 'post',
        				data:data,
        				success: function( response ) {
                                console.log(response)
            					$(".ca-media-file-list").html(response.file_list);
                                $(".ca-series-current").html(response.series_detail);
                                $(".ca-podcast-current").html(response.first_sermon);
            					bindEvents();
        				},
    				});
                });
        $("body").on("change",".ca-order",function()
    {
                    console.log('ORDER DROPDOWN CHANGED')
                    $('.sermon-search').val('');
                    var speaker='';
                    var order='DESC';
                    var series_id;
                    order=$("body .ca-order option:selected").val();
                    speaker=$("body .ca-speaker-dropdown option:selected").val();
                    series_id=$("body .ca-series-dropdown option:selected").val();
                    var page=$(this).data("page");
                    var limit=$(this).data("limit");
                    var data = {
								"action":  "church_admin",
								"method": "dropdown",
								"speaker":speaker,
                                "order":order,
                                "series_id":series_id,
                                "limit":limit,
                                "page":page
					};
                    console.log(data);
					$.getJSON({
        				url: ajaxurl,
        				type: 'post',
        				data:data,
        				success: function( response ) {
                                console.log(response)
            					$(".ca-media-file-list").html(response.file_list);
                                $(".ca-series-current").html(response.series_detail);
                                $(".ca-podcast-current").html(response.first_sermon);
            					bindEvents();
        				},
    				});
                });       
				
		function bindEvents()  {
                $('body a.mp3download').click(function(event) {
                    var href=this.href;
                    var fileID=$(this).data('id');

					jQuery.post(ajaxurl, { 'action': 'church_admin','method':'mp3_plays',"nonce":mp3nonce,'file_id':fileID },
						function(response)  {
                            console.log("plays updated" + response)
							
                            $('body .plays').html(response);

						}
					);
                });
				$('body .sermonmp3').on('playing',function()  {
                    console.log("Playing")
					var fileID=$(this).data('id');
                    console.log("ID "+fileID);
                    var played=window.localStorage.getItem('played'+fileID);
                    if(!played)
                    {
                        window.localStorage.setItem('played'+fileID,"1");
                        var args = { 'action': 'church_admin','method':'mp3_plays','security':mp3nonce,'file_id':fileID };
                        console.log(args)
                        jQuery.post(ajaxurl, args,
                            function(response)  {
                                console.log("plays updated" + response)

                                $('body .plays').html(response);

                            }
                        );
                    }

				});
			}

    $('.ca-share').click(function(e) {
        e.preventDefault();
        window.open( $(this).attr('href'), 'fbShareWindow', 'height=450, width=550, top=' + ( $(window).height() / 2 - 275) + ', left=' + ( $(window).width() / 2 - 225) + ', toolbar=0, location=0, menubar=0, directories=0, scrollbars=0');
        return false;
    });

});
