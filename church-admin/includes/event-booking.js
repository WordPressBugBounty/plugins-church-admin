jQuery(document).ready(function($)  {
    $("body").on("change",".ticket",function()  {
        //when a ticket is slected, add ticket button is enabled.
        console.log('Ticket changed');
        $(".ca-add-ticket").attr("disabled",false);
        var cost=0;


       
        $('.ticket  option:selected').each(function() {
            var ticketCost = $(this).attr('data-price')
            console.log(ticketCost);
            cost = cost + parseFloat(ticketCost)
        });
        console.log('Total ' + parseFloat(cost));
       
        $(".booking-cost").val(cost);
        $(".total").html(cost);

     
    });
    $("body").on("click", ".ca-add-ticket",function(e)  {
        e.preventDefault();
        console.log("add ticket");
        if(totalTicketsLeft>0)
        {
            //disable add ticket button
            $(".ca-add-ticket").attr("disabled",true)
            var ticketNo=$(this).attr("data-ticket");
            
            var newTicketNo=parseFloat(ticketNo)+1;
            //lock last ticket selection
            var selection=$("#ticket"+ticketNo).find(':selected');
            var chosen=$(selection).val();
            var cost=$(selection).data("price");
          
            var selectTicketName=$(selection).attr('data-ticketname');
            
            var locked ='<input type="hidden" class="lockedTicket" data-price="'+cost+'" name=ticket[] value="'+chosen+'" />'+selectTicketName;
            $('#ticketSelector'+ticketNo).html(locked);
            //now updated tickets left
            ticketsLeft[chosen].left =  parseFloat(ticketsLeft[chosen].left)-1;
            
          
            var ticket='<div class="ca-event-booking-person" id="ticketNo'+newTicketNo+'"><span class="ca-ticket-delete" data-ticketno="'+newTicketNo+'">x</span><input type="hidden" name="ticketNo[]" value="'+newTicketNo+'" /><div class="church-admin-form-group"><label>'+firstName+'*</label><input  type="text" id="first_name" name="first_name[]" class="church-admin-form-control" required="required" /></div><div class="church-admin-form-group"><label>'+lastName+'*</label><input class="church-admin-form-control" type="text" id="last_name" name="last_name[]" /></div><div class="church-admin-form-group"><label>'+ticketRequired+'*</label><div id="ticketSelector'+newTicketNo+'"><select class="church-admin-form-control ticket" id="ticket'+newTicketNo+'"  name="ticket[]" ><option value="">'+ticketChoose+'</option>';


            for(var tick in ticketsLeft)
            {
                if(parseFloat(ticketsLeft[tick].left)>0)
                {

                    ticket=ticket+'<option value="'+ticketsLeft[tick].ticket_id+'" data-price="'+ticketsLeft[tick].ticket_price+'">'+ticketsLeft[tick].name+' '+currSymbol+ ticketsLeft[tick].ticket_price+'</option>'
                }
            }
            ticket = ticket + '</select></div>';
            ticket = ticket + '<div class="checkbox"><label><input type="checkbox" name="photo_permission[]" value="1" /> '+photo+'</label></div>';
            console.log(ticket);
            $(".addedTickets").append(ticket);
            var newTicketNo=parseFloat(ticketNo)+1;
            console.log("new ticket no "+newTicketNo)
            $(".ca-add-ticket").attr("data-ticket",newTicketNo)
        }else{
            $(".ca-add-ticket").disabled();
        }
    });
    $("body").on("click",".ca-ticket-delete",function()  {
        var ticketNo=$(this).data("ticketno");
        $("#ticketNo"+ticketNo).remove();
    })




})