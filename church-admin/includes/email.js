jQuery(document).ready(function( $) {




			$('#btnAdd').click(function() {
				var num		= $('.clonedInput').length;	// how many "duplicatable" input fields we currently have
				var newNum	= new Number(num + 1);		// the numeric ID of the new input field being added

				// create the new element via clone(), and manipulate it's ID using newNum value
				var newElem = $('#input' + num).clone().attr('id', 'input' + newNum);
				
				// manipulate the name/id values of the input inside the new element
				
				newElem.find('select').attr('id', 'person' + newNum).val('');
				
				
				// insert the new element after the last "duplicatable" input field
				$('#input' + num).after(newElem);
				 $('#hide' + newNum).hide();
				
				// enable the "remove" button
				$('#btnDel').attr('disabled','');

				// business rule: you can only add 5 names
				if (newNum == 25)
					$('#btnAdd').attr('disabled','disabled');
			});

			$('#btnDel').click(function() {
				var num	= $('.clonedInput').length;	// how many "duplicatable" input fields we currently have
				$('#input' + num).remove();		// remove the last element

				// enable the "add" button
				$('#btnAdd').attr('disabled','');

				// if only one element remains, disable the "remove" button
				if (num-1 == 1)
					$('#btnDel').attr('disabled','disabled');
			});

		






			$('#roleadd').click(function() {
				
				var num		= $('.roleclonedInput').length;	// how many "duplicatable" input fields we currently have
				var newNum	= new Number(num + 1);		// the numeric ID of the new input field being added

				// create the new element via clone(), and manipulate it's ID using newNum value
				var newElem = $('#roleinput' + num).clone().attr('id', 'roleinput' + newNum);
				
				// manipulate the name/id values of the input inside the new element
				
				newElem.find('select').attr('id', 'roleid' + newNum).val('');
				
				
				// insert the new element after the last "duplicatable" input field
				$('#roleinput' + num).after(newElem);
				 $('#rolehide' + newNum).hide();
				
				// enable the "remove" button
				$('#roledel').removeAttr("disabled");
				
				
			});

			$('#roledel').click(function() {
				console.log('Delete fired');
				var num	= $('.roleclonedInput').length;	// how many "duplicatable" input fields we currently have
				$('#roleinput' + num).remove();		// remove the last element

				// enable the "add" button
				$('#roleadd').removeAttr("disabled");
				console.log( $('#roleadd').attr('disabled') );
				// if only one element remains, disable the "remove" button
				if (num-1 == 1) $('#roledel').attr('disabled','disabled');
			});

			
	

			
		




});