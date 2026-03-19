jQuery(document).ready(function( $) {



    /**************************************
    *
    *   Function to populate month data
    *
    ***************************************/    
    
    function populateCalendarMonth(data)
    {
        
        //reset
        $('body .ca-day').removeClass('ca-inactive');
        /*************************
        *
        * Handle dates
        *
        ***************************/
        var dates=data.dates;
        
        for (i = 1; i <=42; i++) { 
            
            if(dates[i] )
            {
                //Put day number in block
                $("#day"+i).html(dates[i] );
                $("#day"+i).attr("data-date",data.partDate+data.dateNumber[i] )
            }
            else
            {
                //no date so make inactive
                $("#day"+i).html('');
                $("#day"+i).addClass('ca-inactive');
            }
        }
        /* Handle title with buttons */
        $("#ca-date").html(data.title);
        /* Handle events */
        $(".ca-sidebar-list").html(data.eventList)

    }
    /**************************************
    *
    *   Function to populate day data
    *
    ***************************************/    
    function getCalendarDay()
    {
        $('.ca-day').removeClass('ca-current');
        $(this).addClass('ca-current')
        var date = $(this).attr('data-date');
        var cat_id = $(this).attr('data-cat_id');
        var facilities_id = $(this).attr('data-facilities_id');
        console.log('Clicked date '+ date);
        var args = {
			"action": "church_admin",
			"method": "calendar-day-render",
            "date": date,
            "cat_id":cat_id,
            "facilities_id":facilities_id,
            "nonce":nonce
		};
        console.log(args);
    
        $.getJSON(ajaxurl,args,populateCalendarDay)
    }
    function populateCalendarDay(data)
    {
        $(".ca-sidebar-list").html(data)
    }
    $("body").on("click"," .ca-calendar-render .ca-day",getCalendarDay)

    
    /***********************************************
    *
    *   Handle next/previous buttons
    *
    ************************************************/
    $("body").on("click",".ca-calendar-nav",function()  {
        $('.ca-day').removeClass('ca-current');
        var date=$(this).data("date");
        var args = {
			"action": "church_admin",
			"method": "calendar-render",
            "date": date,
            "nonce":nonce
		};
        $.getJSON(ajaxurl,args,populateCalendarMonth)
    });
    /***************************************
    *   Initialise with this months data
    ***************************************/
    if(typeof caCalendar !=="undefined")
    {
        var d = new Date().toJSON().slice(0, 10);
        var args = {
                "action": "church_admin",
                "method": "calendar-render",
                "date": d,
                "cat_id":cat_id,
                "fac_ids":facilities_ids,
                "nonce":nonce
            };
        $.getJSON(ajaxurl,args,populateCalendarMonth);
    }
    
    
});