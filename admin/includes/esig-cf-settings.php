<?php

if (!class_exists('ESIG_CF_SETTING')):

    class ESIG_CF_SETTING {

        const ESIG_CF_COOKIE = 'esig-cf-redirect';
        const CF_COOKIE = 'esig-caldera-temp-data';
        const CF_FORM_ID_META = 'esig_caldera_form_id';
        const CF_ENTRY_ID_META = 'esig_caldera_entry_id';
        
        public static function is_caldera_requested_agreement($document_id){
             $cf_form_id = WP_E_Sig()->meta->get($document_id,self::CF_FORM_ID_META);
             $cf_entry_id = WP_E_Sig()->meta->get($document_id,self::CF_ENTRY_ID_META);
             if($cf_form_id && $cf_entry_id){
                 return true;   
             }
             return false;
        }
        
        public static function is_cf_esign_required(){
            if(self::get_temp_settings()){
                return true;
            }
            else {
                return false;
            }
        }
        
        public static function get_temp_settings(){
             if(ESIG_COOKIE(self::CF_COOKIE))
             {
                 return json_decode(stripslashes(ESIG_COOKIE(self::CF_COOKIE)),true);
             }
             return false;
        }
        
        public static function save_esig_cf_meta($meta_key, $meta_index, $meta_value) {
            
            $temp_settings = self::get_temp_settings();
            if (!$temp_settings) {
                $temp_settings= array();
                $temp_settings[$meta_key] = array($meta_index => $meta_value);
                // finally save slv settings . 
                self::save_temp_settings($temp_settings);
            } else {
                
                if (array_key_exists($meta_key, $temp_settings)) {
                    $temp_settings[$meta_key][$meta_index] = $meta_value;
                    self::save_temp_settings($temp_settings);
                } else {
                    $temp_settings[$meta_key] = array($meta_index => $meta_value);
                    self::save_temp_settings($temp_settings);
                }
            }
        }
        
        public static function save_temp_settings($value){
            $json = json_encode($value);
            esig_setcookie(self::CF_COOKIE,  $json ,600);
            // for instant cookie load. 
            $_COOKIE[self::CF_COOKIE] = $json;
        }
        
        public static function save_invite_url($invite_hash, $document_checksum) {
            $invite_url = WP_E_Invite::get_invite_url($invite_hash, $document_checksum);
            
            esig_setcookie(self::ESIG_CF_COOKIE, $invite_url, 600);
            $_COOKIE[self::ESIG_CF_COOKIE] = $invite_url;
        }

        public static function get_invite_url() {
            return esigget(self::ESIG_CF_COOKIE, $_COOKIE);
        }
        
        
        public static function remove_invite_url() {
            setcookie(self::ESIG_CF_COOKIE, null, time() - YEAR_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN);
        }

        /**
         * Generate fields option using form id
         * @param type $form_id
         * @return string
         */
        public static function get_value($document_id, $form, $entry_id, $field_id) {

            $data = Caldera_Forms::get_submission_data($form, $entry_id);
            
            if (is_array($data)) {
                return $data[$field_id];
            }
            return false;
        }

        public static function display_value($form, $form_id, $cf_value,$submit_type) {

            $result = '';
            if ($submit_type == "underline") {
                $result .= '<u>' . $cf_value . '</u>';
            } else {
                $result .= $cf_value;
            }
            return $result;
        }
        

    }

    

    
endif;