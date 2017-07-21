<?php

/**
 *
 * @package ESIG_CFDS_Admin
 * @author  Arafat Rahman <arafatrahmank@gmail.com>
 */
if (!class_exists('ESIG_CFDS_Admin')) :

    class ESIG_CFDS_Admin extends ESIG_CF_SETTING {

        /**
         * Instance of this class.
         * @since    1.0.1
         * @var      object
         */
        protected static $instance = null;
        public $name;

        /**
         * Slug of the plugin screen.
         * @since    1.0.1
         * @var      string
         */
        protected $plugin_screen_hook_suffix = null;

        /**
         * Initialize the plugin by loading admin scripts & styles and adding a
         * settings page and menu.
         * @since     0.1
         */
        public function __construct() {
            /*
             * Call $plugin_slug from public plugin class.
             */
            $plugin = ESIG_CFDS::get_instance();
            $this->plugin_slug = $plugin->get_plugin_slug();

            $this->name = __('Esignature', 'esig-cfds');
            $this->current_tab = empty($tab) ? 1 : $tab;
            $this->document_view = new esig_caldera_document_view();
            // Add an action link pointing to the options page.
            //register text domain
            add_action('init', 'cf_wpesignature_init_text_domain');
            add_filter('caldera_forms_get_form_processors', array($this, 'cf_wpesignature_register'));
            add_filter('esig_sif_buttons_filter', array($this, 'add_sif_caldera_buttons'), 12, 1);
            add_filter('esig_text_editor_sif_menu', array($this, 'add_sif_caldera_text_menu'), 12, 1);
            add_filter('esig_admin_more_document_contents', array($this, 'document_add_data'), 10, 1);
            add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'), 999);
            add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
            add_shortcode('esigcaldera', array($this, 'render_shortcode_esigcaldera'));
            add_action('wp_ajax_esig_caldera_form_fields', array($this, 'esig_caldera_form_fields'));
            //add_action('wp_ajax_nopriv_esig_caldera_form_fields', array($this, 'esig_caldera_form_fields'));
            add_action('admin_init', array($this, 'esig_almost_done_caldera_settings'));
            add_filter('show_sad_invite_link', array($this, 'show_sad_invite_link'), 10, 3);
            add_filter('esig_invite_not_sent', array($this, 'show_invite_error'), 10, 2);
            add_action('admin_menu', array($this, 'esig_cladera_adminmenu'));
            add_action('admin_notices', array($this, 'esig_cf_addon_requirement'));
            add_filter('caldera_forms_submit_redirect_complete', array($this, 'cf_ajax_redirect'), -100, 3);
            add_action('esig_signature_loaded', array($this, 'after_sign_check_next_agreement'), 99, 1);
        }

        final function after_sign_check_next_agreement($args) {

            $document_id = $args['document_id'];

            if (!ESIG_CF_SETTING::is_caldera_requested_agreement($document_id)) {
                return;
            }
            if (!ESIG_CF_SETTING::is_cf_esign_required()) {
                return;
            }

            $invite_hash = WP_E_Sig()->invite->getInviteHash_By_documentID($document_id);
            ESIG_CF_SETTING::save_esig_cf_meta($invite_hash, "signed", "yes");

            $temp_data = ESIG_CF_SETTING::get_temp_settings();

            //$t_data = krsort($temp_data);

            foreach ($temp_data as $invite => $data) {
                if ($data['signed'] == "no") {
                    $invite_url = ESIG_CF_SETTING::get_invite_url($invite);
                    wp_redirect($invite_url);
                    exit;
                }
            }
        }

        public function cf_ajax_redirect($referrer, $form, $process_id) {

            $referrer = self::get_invite_url();
            return $referrer;
        }

        final function esig_cf_addon_requirement() {

            if (class_exists('Caldera_Forms_Admin') && function_exists("WP_E_Sig") && class_exists('ESIG_SAD_Admin') && class_exists('ESIG_SIF_Admin'))
                return;


            include_once "views/alert-modal.php";
        }

        public function esig_cladera_adminmenu() {

            add_submenu_page('caldera-forms', __('E-signature', 'esig'), __('E-signature', 'esig'), 'read', 'esign-caldera-about', array(&$this, 'caldera_about_page'));
            if (!function_exists('WP_E_Sig')) {

                if (empty($GLOBALS['admin_page_hooks']['esign'])) {
                    add_menu_page('E-Signature', 'E-Signature', 'read', "esign", array(&$this, 'esig_core_page'), plugins_url('assets/images/pen_icon.svg', __FILE__));
                }

                add_submenu_page("esign", "Caldera E-signature", "Caldera E-signature", 'read', "esign-caldera-about", array(&$this, 'caldera_about_page'));


                return;
            }
        }
        
        

        public function caldera_about_page() {

            include_once(dirname(__FILE__) . "/views/caldera-esign-about.php");
        }
        
        public function esig_core_page() {

            include_once(dirname(__FILE__) . "/views/esig-core-about.php");
        }

        final function show_sad_invite_link($show, $doc, $page_id) {
            if (!isset($doc->document_content)) {
                return $show;
            }
            $document_content = $doc->document_content;
            $document_raw = WP_E_Sig()->signature->decrypt(ENCRYPTION_KEY, $document_content);

            if (has_shortcode($document_raw, 'esigcaldera')) {

                $show = false;
                return $show;
            }
            return $show;
        }

        final function show_invite_error($ret, $docId) {

            $doc = WP_E_Sig()->document->getDocument($docId);
            if (!isset($doc->document_content)) {
                return $ret;
            }
            $document_content = $doc->document_content;
            $document_raw = WP_E_Sig()->signature->decrypt(ENCRYPTION_KEY, $document_content);

            if (has_shortcode($document_raw, 'esigcaldera')) {

                $ret = true;
                return $ret;
            }
            return $ret;
        }

        final function esig_almost_done_caldera_settings() {

            if (!function_exists('WP_E_Sig'))
                return;

            // getting sad document id 
            $sad_document_id = isset($_GET['doc_preview_id']) ? $_GET['doc_preview_id'] : null;


            if (!$sad_document_id) {
                return;
            }



            $documents = WP_E_Sig()->document->getDocument($sad_document_id);


            $document_content = $documents->document_content;

            $document_raw = WP_E_Sig()->signature->decrypt(ENCRYPTION_KEY, $document_content);

            if (has_shortcode($document_raw, 'esigcaldera')) {


                preg_match_all('/' . get_shortcode_regex() . '/s', $document_raw, $matches, PREG_SET_ORDER);

                $esigcaldera_shortcode = '';

                foreach ($matches as $match) {
                    if (in_array('esigcaldera', $match)) {
                        $esigcaldera_shortcode = $match[0];
                    }
                }

                $atts = shortcode_parse_atts($esigcaldera_shortcode);

                extract(shortcode_atts(array(
                    'formid' => '',
                    'field_id' => '', //foo is a default value
                                ), $atts, 'esigcaldera'));
                $data = array("form_id" => $formid);
                $display_notice = dirname(__FILE__) . '/views/alert-almost-done.php';
                WP_E_Sig()->view->renderPartial('', $data, true, '', $display_notice);
            }
        }

        public function esig_caldera_form_fields() {


            if (!function_exists('WP_E_Sig'))
                return;


            $html = '';

            $html .='<select name="esig_cf_field_id" class="chosen-select" style="width:250px;">';
            $form_id = $_POST['form_id'];
            $forms = Caldera_Forms::get_forms();

            $form = Caldera_Forms_Forms::get_form($form_id);
            $form = apply_filters('caldera_forms_render_get_form', $form);
            foreach ($form['fields'] as $field) {
                if ($field['label'] == 'submit') {
                    continue;
                }
                $html .= '<option value=' . $field['ID'] . '>' . $field['label'] . '</option>';
            }
            echo $html;

            die();
        }

        public function render_shortcode_esigcaldera($atts) {


            extract(shortcode_atts(array(
                'formid' => '',
                'field_id' => '', //foo is a default value
                            ), $atts, 'esigcaldera'));

            if (!function_exists('WP_E_Sig'))
                return;



            $csum = isset($_GET['csum']) ? sanitize_text_field($_GET['csum']) : null;

            if (empty($csum)) {
                $document_id = get_option('esig_global_document_id');
            } else {
                $document_id = WP_E_Sig()->document->document_id_by_csum($csum);
            }

            $form_id = WP_E_Sig()->meta->get($document_id, 'esig_caldera_form_id');
            $entry_id = WP_E_Sig()->meta->get($document_id, 'esig_caldera_entry_id');

            if (empty($entry_id)) {
                return;
            }


            //$forms = Caldera_Forms::get_forms();
            $form = Caldera_Forms_Forms::get_form($form_id);
            $esign_processor = $form['processor'];
            $submit_type = $esign_processor['submit_type'];



            $cf_value = self::get_value($document_id, $form, $entry_id, $field_id);

            if (!$cf_value) {
                return;
            }


            if (strpos($cf_value, 'opt') !== false) {

                $checkboxvalue = json_decode($cf_value, true);
                $html = '';

                foreach ($checkboxvalue as $value) {

                    if ($submit_type == "underline") {
                        $html .= '<input type="checkbox" disabled readonly value="' . $value . '" checked="checked" ><u>' . $value . '</u>';
                    } else {
                        $html .= '<input type="checkbox" disabled readonly value="' . $value . '" checked="checked" >' . $value;
                    }
                }
                return $html;
            }

            if (strpos($cf_value, 'click') !== false) {
                $html = '';
                return $html;
            }
            return self::display_value($form, $form_id, $cf_value, $submit_type);
        }

        public function enqueue_admin_styles() {
            $screen = get_current_screen();

            $admin_screens = array(
                'admin_page_esign-caldera-about',
                'forms_page_esign-caldera-about',
                'caldera-forms_page_esign-caldera-about'
            );


            if (in_array($screen->id, $admin_screens)) {

                wp_enqueue_style($this->plugin_slug . '-admin-styles', plugins_url('assets/css/esig-caldera-about.css', __FILE__), array());
            }
        }

        public function enqueue_admin_scripts() {



            $screen = get_current_screen();
            $admin_screens = array(
                'admin_page_esign-add-document',
                'admin_page_esign-edit-document',
                'e-signature_page_esign-view-document',
            );


            if (in_array($screen->id, $admin_screens)) {

                wp_enqueue_script('jquery');
                wp_enqueue_script('' . '-admin-script', plugins_url('assets/js/esig-add-caldera.js', __FILE__), array('jquery', 'jquery-ui-dialog'), '0.1.0', true);
            }
            if ($screen->id != "plugins") {
                wp_enqueue_script($this->plugin_slug . '-admin-script', plugins_url('assets/js/esig-caldera-control.js', __FILE__), array('jquery', 'jquery-ui-dialog'), ESIG_CFDS::VERSION, true);
            }
        }

        public function document_add_data($more_contents) {


            $document_view = new esig_caldera_document_view();
            $more_contents .=$document_view->add_document_view();


            return $more_contents;
        }

        public function add_sif_caldera_buttons($sif_menu) {

            $esig_type = isset($_GET['esig_type']) ? $_GET['esig_type'] : null;
            $document_id = isset($_GET['document_id']) ? $_GET['document_id'] : null;

            if (empty($esig_type) && !empty($document_id)) {

                $document_type = WP_E_Sig()->document->getDocumenttype($document_id);
                if ($document_type == "stand_alone") {
                    $esig_type = "sad";
                }
            }

            if ($esig_type != 'sad') {
                return $sif_menu;
            }

            $sif_menu .=' {text: "Caldera Form Data",value: "caldera", onclick: function () { tb_show( "+ Caldera form option", "#TB_inline?width=450&height=300&inlineId=esig-caldera-option");}},';

            return $sif_menu;
        }

        public function add_sif_caldera_text_menu($sif_menu) {

            $esig_type = esigget('esig_type');
            $document_id = esigget('document_id');

            if (empty($esig_type) && !empty($document_id)) {
                $document_type = WP_E_Sig()->document->getDocumenttype($document_id);
                if ($document_type == "stand_alone") {
                    $esig_type = "sad";
                }
            }

            if ($esig_type != 'sad') {
                return $sif_menu;
            }
            $sif_menu['Caldera'] = array('label' => "Caldera Form Data");
            return $sif_menu;
        }

        public function cf_wpesignature_register($processors) {

            $processors['cf_wpesignature'] = array(
                "name" => __(' WP E-Signature by ApproveMe', ''),
                "description" => __('Automatically produce a legally enforceable & court recognized contract from a Caldera Form submission.', 'cf-wpesignature'),
                "icon" => CF_WPESIGNATURE_URL . "icon.png",
                "author" => '',
                "author_url" => '',
                "pre_processor" => 'cf_wpesignature_pre_process',
                "processor" => array($this, 'cf_wpesignature_process'),
                "template" => CF_WPESIGNATURE_PATH . "admin/includes/config.php",
                "cf_ver" => '1.2.4'
            );

            return $processors;
        }

        public static function cf_wpesignature_process($config, $form) {
            global $form;

            $esign_config = apply_filters('esign_caldera_form_config', $config, $form);

            //$message = $esign_config['message'];
            foreach ($esign_config as $tag => &$value) {
                if ($tag !== 'message') {
                    //$message = str_replace('%'.$tag.'%', $value, $message);
                    $value = Caldera_Forms::do_magic_tags($value);
                }
            }

            $esign_processor = $form['processor'];


            $data = Caldera_Forms::get_submission_data($form);

            $sad = new esig_sad_document();
            $form_id = $form['ID'];
            $entry_id = $data['_entry_id'];
            // $post_id = $esign_config['processor_id'];
            $signing_logic = $esign_processor['signing_logic'];
            //$submit_type = $esign_processor['submit_type'];
            $sad_page_id = $esign_processor['select_sad'];
            $document_id = $sad->get_sad_id($sad_page_id);
            $signer_email = $esign_config['signer_email'];
            $signer_name = $esign_config['signer_name'];
            $reminder_set = isset($esign_processor['reminder_data']);



            if ($reminder_set == '1') {

                $esig_caldera_reminders_settings = array(
                    "esig_reminder_for" => absint($esign_config['reminder_email']),
                    "esig_reminder_repeat" => absint($esign_config['first_reminder_send']),
                    "esig_reminder_expire" => abs($esign_config['expire_reminder']),
                );

                WP_E_Sig()->meta->add($document_id, "esig_reminder_settings_", json_encode($esig_caldera_reminders_settings));
                WP_E_Sig()->meta->add($document_id, "esig_reminder_send_", "1");
            }



            // if not email address 
            if (!is_email($signer_email)) {
                return;
            }

            //sending email invitation / redirecting .
            $result = self::esig_invite_document($document_id, $signer_email, $signer_name, $form_id, $entry_id, $signing_logic);
        }

        public static function esig_invite_document($old_doc_id, $signer_email, $signer_name, $form_id, $entry_id, $signing_logic) {

            if (!function_exists('WP_E_Sig'))
                return;

            $esig = WP_E_Sig();

            global $wpdb;



            /* make it a basic document and then send to sign */
            $old_doc = WP_E_Sig()->document->getDocument($old_doc_id);

            $doc_table = $wpdb->prefix . 'esign_documents';


            // Copy the document
            $doc_id = WP_E_Sig()->document->copy($old_doc_id);

            WP_E_Sig()->meta->add($doc_id, 'esig_caldera_form_id', $form_id);
            WP_E_Sig()->meta->add($doc_id, 'esig_caldera_entry_id', $entry_id);
            WP_E_Sig()->document->saveFormIntegration($doc_id, 'caldera');

            // set document timezone
            $esig_common = new WP_E_Common();
            $esig_common->set_document_timezone($doc_id);
            // Create the user=
            $recipient = array(
                "user_email" => $signer_email,
                "first_name" => $signer_name,
                "document_id" => $doc_id,
                "wp_user_id" => '',
                "user_title" => '',
                "last_name" => ''
            );

            $recipient['id'] = WP_E_Sig()->user->insert($recipient);

            $document_type = 'normal';
            $document_status = 'awaiting';
            $doc_title = $old_doc->document_title . ' - ' . $signer_name;
            // Update the doc title
            $affected = $wpdb->query($wpdb->prepare(
                            "UPDATE " . $doc_table . " SET document_title = '%s',document_type ='%s' , document_status='%s' where document_id = %d", $doc_title, $document_type, $document_status, $doc_id));

            $doc = WP_E_Sig()->document->getDocument($doc_id);

            // trigger an action after document save .
            do_action('esig_sad_document_invite_send', array(
                'document' => $doc,
                'old_doc_id' => $old_doc_id,
            ));


            // Get Owner
            $owner = WP_E_Sig()->user->getUserByID($doc->user_id);


            // Create the invitation?
            $invitation = array(
                "recipient_id" => $recipient['id'],
                "recipient_email" => $recipient['user_email'],
                "recipient_name" => $recipient['first_name'],
                "document_id" => $doc_id,
                "document_title" => $doc->document_title,
                "sender_name" => $owner->first_name . ' ' . $owner->last_name,
                "sender_email" => $owner->user_email,
                "sender_id" => 'stand alone',
                "document_checksum" => $doc->document_checksum,
                "sad_doc_id" => $old_doc_id,
            );

            $invite_controller = new WP_E_invitationsController();

            if ($signing_logic == "email") {

                if ($invite_controller->saveThenSend($invitation, $doc)) {

                    return true;
                }
            } elseif ($signing_logic == "redirect") {

                $invitation_id = $invite_controller->save($invitation);
                $invite_hash = WP_E_Sig()->invite->getInviteHash($invitation_id);

                self::save_invite_url($invite_hash, $doc->document_checksum);
            }
        }

        public function cf_wpesignature_init_text_domain() {

            load_plugin_textdomain('cf-wpesignature', FALSE, CF_WPESIGNATURE_PATH . 'languages');
        }

        /**
         * Return an instance of this class.
         * @since     0.1
         * @return    object    A single instance of this class.
         */
        public static function get_instance() {

            // If the single instance hasn't been set, set it now.
            if (null == self::$instance) {
                self::$instance = new self;
            }

            return self::$instance;
        }

    }

    


    
endif;

