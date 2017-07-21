<?php
/**
 * CF ESIG Licensed Downloads
 *
 * @package   Caldera_Forms_ESIG
 * @author    Arafat Rahman <arafatrahmank@gmail.com>
 * @license   GPL-2.0+
 * @link
 */
?>

<?php 

if(!isset($element['processor']['signing_logic'])){
	$element['processor']['signing_logic'] = 'redirect';
}  
if(!isset($element['processor']['submit_type'])){
	$element['processor']['submit_type'] = 'underline';
}
if(!isset($element['processor']['select_sad'])){
	$element['processor']['select_sad'] = 'selected';
}
?>

<?php if (function_exists("WP_E_Sig")){ ?>
<div class="caldera-config-group">
    <label for="signer_name">
        <?php _e('Signer Name', 'cf-form-connector'); ?><font color="red">*</font>
    </label>
    <div class="caldera-config-field">
        <input type="text" class="block-input field-config magic-tag-enabled required"   name="{{_name}}[signer_name]" value="{{signer_name}}" >
        <span class="description"><?php _e('Name will appear to be from this name.. ', 'esig'); ?></span>
        <span id="signer-name-validation-msg" color="red" style="display:none;color:red;"> <b>This field is required </b></span>
        
    </div>
</div>


<div class="caldera-config-group">
    <label for="signer_email">
        <?php _e('Signer Email Address', 'cf-form-connector'); ?><font color="red">*</font>
    </label>
    <div class="caldera-config-field">
        <input type="text" class="block-input field-config magic-tag-enabled required" name="{{_name}}[signer_email]" value="{{signer_email}}" >
        <span class="description"><?php _e('Email will appear to be from this name.. ', 'esig'); ?></span>
        <span id="signer-email-validation-msg" color="red" style="display:none;color:red;"> <b>This field is required </b></span>
    </div>
</div>


<div class="caldera-config-group">
    <label><?php _e('Signing Logic', 'esig'); ?></label>
    <div class="caldera-config-field">
        <select class="field-config" name="config[processor][signing_logic]">
            <option value="redirect" <?php if($element['processor']['signing_logic'] == 'redirect'){ echo 'selected="selected"'; } ?>><?php _e('Redirect user to Contract/Agreement after Submission', 'esig'); ?></option>
            <option value="email" <?php if($element['processor']['signing_logic'] == 'email'){ echo 'selected="selected"'; } ?> ><?php _e('Send User an Email Requesting their Signature after Submission', 'esig'); ?></option>
        </select>
        
        <span class="description"><?php _e('Please select your desired signing logic once this form is submitted', 'esig'); ?></span>
    </div>
</div>

<?php do_action( 'caldera_forms_processor_config', $element ); ?>
<div class="caldera-config-group">
    <label for="select_sad"><?php _e('Select stand alone document', 'esig'); ?><font color="red">*</font></label>
    <div class="caldera-config-field">
        <select name="config[processor][select_sad]" id="select_sad" class="field-config required">
            <?php
            if (class_exists('esig_sad_document')) {

                $sad = new esig_sad_document();
                $sad_pages = $sad->esig_get_sad_pages();
                echo'<option value=""> ' . __('Select an agreement page', 'esig') . ' </option>';
                foreach ($sad_pages as $page) {
                     $selected = ($page->page_id == $element['processor']['select_sad']) ? "selected" : null;
                    if (get_the_title($page->page_id)) {
                        echo '<option value="' . $page->page_id . '" '. $selected .' > ' . get_the_title($page->page_id) . ' </option>';
                    }
                }
            }
            ?>
        </select><br><span id="signer-sad-validation-msg" color="red" style="display:none;color:red;"> <b>This field is required </b></span><span class="description"><?php _e('If you would like to can <a href="edit.php?post_type=esign&amp;page=esign-add-document&amp;esig_type=sad">create new document</a>', 'esig'); ?></span><br><br>

        <select class="field-config" name="config[processor][submit_type]">
            <option value="underline" <?php if($element['processor']['submit_type'] == 'underline'){ echo 'selected="selected"'; } ?>><?php _e('Underline the data That was submitted from this Caldera form', 'esig'); ?></option>
            <option value="not_under" <?php if($element['processor']['submit_type'] == 'not_under'){ echo 'selected="selected"'; } ?> ><?php _e('Do not underline the data that was submitted from the Caldera Form', 'esig'); ?></option>
        </select>
    </div>
</div>


<div class="caldera-config-group">
    <label for="signing_reminder_email"><?php _e('Signing Reminder Email', 'esig'); ?></label>
    <label for="reminder_email"></label> 
    <label for="first_reminder_send"></label> 
    <label for="expire_reminder"></label> 
    <div class="caldera-config-field">
        <input name="signing_reminder_email"  name="{{_name}}[signing_reminder_email]" value="{{signing_reminder_email}}" type="hidden"/>
        <input type="checkbox" id="reminder_data" onclick="prefill()" name="config[processor][reminder_data]" value="1" <?php if(isset($element['processor']['reminder_data'])) {echo 'checked="checked"' ; } ?>><?php _e('Enabling signing reminder email. If/When user has not sign the document', 'esig'); ?><br>
        <div id="reminder_section" style="visibility:hidden">
        <?php _e('Send the reminder email to the signer in ', 'esig'); ?><input type="textbox"  name="{{_name}}[reminder_email]" id ="reminder_email" value="{{reminder_email}}"  style="width:40px;height:30px;"> <?php _e('Days', 'esig'); ?><br>
        <?php _e('After the first Reminder send reminder every ', 'esig'); ?><input type="textbox"  name="{{_name}}[first_reminder_send]" id ="first_reminder_send"  value="{{first_reminder_send}}"  style="width:40px;height:30px;"> <?php _e('Days', 'esig'); ?><br>
        <?php _e('Expire reminder in ', 'esig'); ?><input type="textbox"  name="{{_name}}[expire_reminder]" id ="expire_reminder" value="{{expire_reminder}}" style="width:40px;height:30px;"> Days
        </div>
    </div>
     
</div>
<?php } ?>

<?php 
if (!function_exists("WP_E_Sig")){ 
 include_once("core-alert.php");
}?>



<script>
function prefill() {
   
  if (document.getElementById('reminder_data').checked) 
  {
  document.getElementById("reminder_section").style.visibility = "visible";
  } else {
    document.getElementById("reminder_section").style.visibility = "hidden";
  }
    document.getElementById("reminder_email").value = "1";
    document.getElementById("first_reminder_send").value = "3";
    document.getElementById("expire_reminder").value = "21";
}
</script>