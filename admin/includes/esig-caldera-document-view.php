<?php
/**
 *
 * @package ESIG_NINJAYFORM_DOCUMENT_VIEW
 * @author  Abu Shoaib <abushoaib73@gmail.com>
 */



if (! class_exists('esig-caldera-document-view')):
class esig_caldera_document_view {
    
    
            /**
        	 * Initialize the plugin by loading admin scripts & styles and adding a
        	 * settings page and menu.
        	 * @since     0.1
        	 */
        	final function __construct() {
                        
        	}
        	
        	/**
        	 *  This is add document view which is used to load content in 
        	 *  esig view document page
        	 *  @since 1.1.0
        	 */
        	
        	final function add_document_view()
        	{
        	    
        	    if(!function_exists('WP_E_Sig'))
                                return ;
                    
                    
                    
                    
        	    
        	    $api = WP_E_Sig();
        	    $assets_dir = ESIGN_ASSETS_DIR_URI;
        	    
                    
        	   $more_option_page = ''; 
        	   
        	    
        	    $more_option_page .= '<div id="esig-caldera-option" class="esign-form-panel" style="display:none;">
        	        
        	        
                	               <div align="center"><img src="' . $assets_dir .'/images/logo.png" width="200px" height="45px" alt="Sign Documents using WP E-Signature" width="100%" style="text-align:center;"></div>
                    			
                                    
                    				<div id="esig-caldera-form-first-step">
                        				
                                        	<h3 class="esign-form-header">'.__('What Are You Trying To Do?', 'esig').'</h3>
                                            	
                        				<p id="create_caldera" align="center">';
                                	    
                                	    $more_option_page .=	'
                        			
                        				<p id="select-caldera-form-list" align="center">
                                	    
                        		        <select data-placeholder="Choose a Option..." class="chosen-select" tabindex="2" id="esig-caldera-form-id" name="esig_cf_form_id">
                        			     <option value="sddelect">'.__('Select a caldera Form', 'esig').'</option>';
                                	    
                                           if(class_exists('Caldera_Forms'))
                                           {
                                               
                                            $forms = Caldera_Forms::get_forms();
                                            
                                           }
                                           
                                           
                                            if(!empty($forms)){
                                            
                                	    foreach($forms as $form_id=>$form)
                                	    {
                                           
                                	       
                                	        $more_option_page .=	'<option value="'. $form_id . '">'.$form['name'] .'</option>';
                                	    }
                                            }
                                            
                            	         
                                                        
                                            
                                	    $more_option_page .='</select>
                                	    
                        				</p>
                         	  
                                	    </p>
                                	    
                                        <p id="upload_caldera_button" align="center">
                                           <a href="#" id="esig-caldera-create" class="button-primary esig-button-large">'.__('Next Step', 'esig').'</a>
                                         </p>
                                     
                                    </div>  <!-- Frist step end here  --> ';
                            
                                    
                 $more_option_page .='<!-- Caldera form second step start here -->
                                            <div id="esig-cf-second-step" style="display:none;">
                                            
                                        	<h4 class="esign-form-header">'.__('What caldera form field data would you like to insert?', 'esig').'</h4>
                                            
                                            <p id="esig-cf-field-option" align="center">
                               



                                             </p>
                                            
                                            
                                             <p id="upload_caldera_button" align="center">
                                           <a href="#" id="esig-caldera-insert" class="button-primary esig-button-large">'.__('Add to Document', 'esig').'</a>
                                         </p>
                                            
                                            </div>
                                    <!-- caldera form second step end here -->';           
                                    
                                    
        	    
        	    $more_option_page .= '</div><!--- caldera option end here -->' ;
        	    
        	    
        	    return $more_option_page ; 
        	}
        	
        	
	   
    }
endif ; 

