jQuery(function($) {


	$("#site-keywords a.edit").click(function() {
			
			//event.preventDefault();
			var theID = $(this).attr("id");
			
			theID = "tr#" + theID;
			
			$("tr.edit_keyword").hide();
			$("tr.delete_keyword").hide();
			
			var choice_select = theID + " input.expires_value";
			var if_expires = theID + " div#if_expires";
			
			var choice = $(choice_select).val();
			
			if (choice == "No") {
				$(if_expires).hide();
			} else if (choice == "Yes") {
				$(if_expires).show();
			}
			
			var the_selected = theID + " select.edit_expires";
			
			$(the_selected).val(choice);
    		
    		
			$(theID).show();
			
			return false;
	});
	
	$("#site-keywords input.close").click(function() {
		
		$(this).parents("tr").hide();
		
	});
	
	$("#site-keywords a.delete").click(function() {
			
			//event.preventDefault();
			var theID = $(this).attr("id");
			
			theID = "tr#" + theID;
			$("tr.edit_keyword").hide();
			$("tr.delete_keyword").hide();
			$(theID).show();
			
			return false;
	});
	
	$("#site-keywords input.no").click(function() {
		
		$(this).parents("tr").hide();
		
	});
	
	$("select.edit_expires").change(function() {
		var theID = $(this).parents("tr").attr("id");
			
		theID = "tr#" + theID;
		var if_expires = theID + " div#if_expires";
			
		var choice = $(this).val();
		
		
		if (choice == "No") {
			$(if_expires).hide();
		} else if (choice == "Yes") {
			$(if_expires).show();
		}
		
	});
	

});