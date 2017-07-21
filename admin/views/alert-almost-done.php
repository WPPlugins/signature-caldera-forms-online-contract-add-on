<div id="esig-caldera-almost-done" style="display: none;"> 

        	<div class="esig-dialog-header">
        	<div class="esig-alert">
            	<span class="icon-esig-alert"></span>
            </div>
		   <h3><?php _e('Almost there... you\'re 50% complete','esig-cf7'); ?></h3>
		   
		   <p class="esig-updater-text"><?php 
		   
		    $esig_user= new WP_E_User();
		    
		    $wpid = get_current_user_id();
		    
		    $users = $esig_user->getUserByWPID($wpid); 
		    echo $users->first_name . ","; 
		   
		   ?>
		   
		   
		  <?php _e('Congrats on setting up your document! You\'ve got part 1 of 2 complete! Now you need to
          head over to the "Form Settings" tab for the Caldera Form  you are trying to connect it to.' ,'esig-nf'); ?> </p>
		</div>
        

         <div > <img src="<?php echo plugins_url("caldera-forms.png",__FILE__) ; ?>" style="border: 1px solid #efefef; width: 500px; height:186px" /> </div>

        
        <div class="esig-updater-button">

		  <span> <a href="#" class="button esig-secondary-btn"  id="esig-caldera-setting-later"> <?php _e('I\'LL DO THIS LATER','esig-cf7');?> </a></span>
                  <span> <a href="admin.php?page=caldera-forms&edit=<?php echo $data['form_id']; ?>" class="button esig-dgr-btn" id="esig-cf-lets-go"> <?php _e('LET\'S GO NOW!','esig-cf7');?> </a></span>

		</div>

 </div>