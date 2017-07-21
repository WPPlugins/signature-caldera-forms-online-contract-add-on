(function($){

	  // almost done modal dialog here 
       $( "#esig-caldera-almost-done" ).dialog({
			  dialogClass: 'esig-dialog',
			  height:350,
			  width:350,
			  modal: true,
			});
            
      // do later button click 
       $( "#esig-caldera-setting-later" ).click(function() {
          $( '#esig-caldera-almost-done' ).dialog( "close" );
        });
      
     
		
})(jQuery);


