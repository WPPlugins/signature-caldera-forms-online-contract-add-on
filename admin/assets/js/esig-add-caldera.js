(function($){
        

        // next step click from sif pop
        $( "#esig-caldera-create" ).click(function() {
          
 
                   var form_id= $('select[name="esig_cf_form_id"]').val();
                 
                   $("#esig-caldera-form-first-step").hide();
                   
                   // jquery ajax to get form field . 
                   jQuery.post(esigAjax.ajaxurl,{ action:"esig_caldera_form_fields",form_id:form_id},function( data ){ 
                       
				      $("#esig-cf-field-option").html(data);
				},"html");
                   
                   $("#esig-cf-second-step").show();                        
  
        });
 
        // ninja add to document button clicked 
        $( "#esig-caldera-insert" ).click(function() {
 
                   var form_id= $('select[name="esig_cf_form_id"]').val();
                   
                   var field_id =$('select[name="esig_cf_field_id"]').val();
                   // 
                   var return_text = ' [esigcaldera formid="'+ form_id +'" field_id="'+ field_id +'" ] ';
		  esig_sif_admin_controls.insertContent(return_text);
            
             tb_remove();
                     
                   
        });
        
        
        //if overflow
        $('#select-caldera-form-list').click(function(){
            
            
          
            $(".chosen-drop").show(0, function () { 
				$(this).parents("div").css("overflow", "visible");
				});
            
            
            
        });
	
})(jQuery);



