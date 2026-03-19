jQuery(document).ready(function( $) {

$('#btnAdd').click(function() {
    var numFields		= $('.clonedInput').length?$('.clonedInput').length:1;	// how many "duplicatable" input fields we have now

    var oldNum =new Number(numFields);
    var newNum	= new Number(numFields + 1);
    $('#fields').val(newNum)
    var clone = $('#input'+numFields).clone().attr("id","input"+newNum);
    clone.find(".person").html(newNum);
    clone.find('input').each(function(i)  {
        $(this).attr('name',$(this).data('name')+newNum);
    });
   
    clone.find("#date_of_birth"+oldNum).each(function(i)  {
        $(this).attr('name',"date_of_birth"+newNum);
        $(this).attr('data-name',"date_of_birth"+newNum);
        $(this).attr('id',"date_of_birth"+newNum);
        $(this).val('');
        
    });
    clone.find("#date_of_birth"+oldNum+"x").each(function(i)  {
        $(this).attr('name',"date_of_birth"+newNum+"x");
        $(this).attr('data-name',"date_of_birth"+newNum+"x");
        $(this).attr('id',"date_of_birth"+newNum+"x");
    });
    
    clone.find(".clonableDatePicker").each(function(i)  {
        var name=$(this).data("name").slice(0, -1);

        $(this).attr('name',name+newNum+"x");
        $(this).attr('id',name+newNum+"x");
        $(this).val('');
        
    });
    clone.find(".clonableHiddenDatePicker").each(function(i)  {
        var name=$(this).data("name").slice(0, -1);

        $(this).attr('name',name+newNum);
        $(this).attr('id',name+newNum);
        $(this).val('');
        
    });
    clone.find('select').each(function(i)  {
        console.log('Current name = '+$(this).attr('name'))
        $(this).attr('name',$(this).attr('data-name')+newNum);
        $(this).attr('id',$(this).attr('data-name')+newNum);
        
    });
    clone.find('textarea').each(function(i)  {
        $(this).attr('name',$(this).attr('data-name')+newNum);
    })
    
    $('.edit-people-form').append(clone);
    $('#btnDel').removeAttr( "disabled");
    
});
	
    $('#btnDel').click(function() {
				var numFields		= $('.clonedInput').length?$('.clonedInput').length:1;	
				$('#input' + numFields).remove();		// remove the last element
				$('#fields').val(numFields-1);
				// enable the "add" button
				$('#btnAdd').prop( "disabled", false );

				// if only one element remains, disable the "remove" button
				if (numFields-1 == 1)
					$('#btnDel').prop( "disabled", true);
				
				var fields= $('.first_name').length;
				$('#fields').val(fields);
        if(numFields<=2)		$('#btnDel').prop( "disabled", "disabled" );
    });

	


});
