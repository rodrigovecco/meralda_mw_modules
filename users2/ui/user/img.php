<?php
/**
 * Users2 - User Profile Image
 * 
 * Admin interface for managing user profile image.
 * Uses croppermaster for image upload and cropping.
 */
class mwmod_mw_users2_ui_user_img extends mwmod_mw_users2_ui_user_abs {
    
    public function __construct($cod, $parent) {
        $this->init_as_subinterface($cod, $parent);
        $this->set_def_title($this->lng_get_msg_txt("profile_img", "Imagen de perfil"));
    }
    
    /**
     * Check if allowed for current item
     */
    public function is_allowed_for_current_item() {
        if (!parent::is_allowed_for_current_item()) {
            return false;
        }
        
        if ($user = $this->get_current_edit_user()) {
            // User must allow admin for image
            return $user->allowadmin_admin();
        }
        
        return false;
    }
    
    /**
     * Prepare before execution - croppermaster uses its own JS, not modern inputs
     */
    public function prepare_before_exec_no_sub_interface(): void {
        // Don't call parent - croppermaster doesn't need modern inputs JS
    }
    
    /**
     * Execute no sub interface - setup croppermaster and load user
     */
    public function do_exec_no_sub_interface() {
        // Setup croppermaster utilities
        $cmutil = new mwmod_mw_helper_croppermaster_util();
        $cmutil->preapare_ui($this);
        $cmutil->preapare_ui_avatar($this);
        
        // Load user from request (same as original)
        if (!$uman = $this->getUman()) {
            return false;
        }
        if (!$user = $uman->get_user($_REQUEST["iditem"] ?? null)) {
            return false;
        }
        $this->set_current_item($user);
        $this->current_edit_user = $user;
        $this->set_url_param("iditem", $user->get_id());
    }
    
    /**
     * Execute page: show image form
     */
    public function do_exec_page_in() {
        if (!$user = $this->get_current_edit_user()) {
            echo "<div class='alert alert-danger'>Error: No se pudo cargar el usuario</div>";
            return false;
        }
        
        if (!$user->allowadmin_admin()) {
            echo "<div class='alert alert-warning'>No tienes permisos para administrar este usuario</div>";
            return false;
        }
        
        if (!$dm = $this->getUserDataMan()) {
            echo "<div class='alert alert-danger'>Error: No se pudo obtener el Data Manager</div>";
            return false;
        }
        
        $msgs = new mwmod_mw_html_elem();
        $msgs->only_visible_when_has_cont = true;
        
        // Handle delete image
        if ($_REQUEST["deleteimg"] ?? null) {
            $dm->delete_profile_img($user);
        }
        
        // Handle upload
        $mwmod_mw_helper_croppermaster_uploaderhtml = new mwmod_mw_helper_croppermaster_uploaderhtml();
        if ($input = $mwmod_mw_helper_croppermaster_uploaderhtml->get_upload_input()) {
            if ($dm->upload_profile_imgs_from_input_crop($input, $user, $msgs)) {
                $done = true;
            }
        }
        
        echo "<div id='crop-avatar'>";
        
        $params = new mwmod_mw_jsobj_obj();
        if ($imgsgr = $user->profile_imgs_group) {
            $params->set_prop("aspectRatio", $imgsgr->get_aspect_ratio());
        }
        
        // Modal for cropping
        $modal = new mwmod_mw_bootstrap_html_template_modal("avatar-modal", $this->lng_get_msg_txt("profile_img", "Imagen de perfil"));
        $modal_cont = $modal->cont_elem;
        $modal_cont->add_cont($mwmod_mw_helper_croppermaster_uploaderhtml);
        
        // Panel with current image
        $panel = new mwmod_mw_bootstrap_html_template_panel();
        if ($title = $panel->get_key_cont("title")) {
            $title->add_cont($this->lng_get_msg_txt("profile_img", "Imagen de perfil"));
        }
        
        if ($imgelem = $user->get_img_elem("big")) {
            $imgscontainer = new mwmod_mw_html_elem();
            $imgscontainer->set_style("text-align", "center");
            $imgscontainer->add_cont($imgelem);
            $panel->cont_elem->add_cont($imgscontainer);
        }
        
        // Buttons container
        $btncontainer = new mwmod_mw_templates_html_btnscontainer();
        
        // Delete button (if has image)
        if ($user->has_img()) {
            $url = $this->get_url(array("deleteimg" => "true"));
            $jsevent = new mwmod_mw_jsobj_codecontainer();
            $msg = $jsevent->get_txt($this->lng_get_msg_txt("confirm_delete_image", "¿Realmente desea eliminar la imagen?"));
            $jsevent->add_cont("if(confirm('$msg')){window.location='$url'}else{return false}");
            
            $btn = new mwmod_mw_bootstrap_html_specialelem_btn($this->lng_get_msg_txt("delete_image", "Eliminar imagen"), "warning");
            $btn->set_att("onclick", $jsevent->get_as_js_val());
            $btncontainer->add_cont($btn);
        }
        
        // Change image button (opens modal)
        $btn = $modal->new_open_btn($this->lng_get_msg_txt("change_image", "Cambiar imagen"));
        $btncontainer->add_cont($btn);
        
        if ($footer = $panel->get_key_cont("footer")) {
            $footer->add_cont($btncontainer);
        } else {
            $panel->cont_elem->add_cont($btncontainer);
        }
        
        $panel->do_output();
        $modal->do_output();
        
        echo "</div>"; // crop-avatar
        
        // Initialize CropAvatar JS
        $js = new mwmod_mw_jsobj_jquery_docreadyfnc();
        $jsin = "var crophelper=new CropAvatar($('#crop-avatar')," . $params->get_as_js_val() . "); crophelper.initPreview();";
        $js->add_cont($jsin);
        echo $js->get_js_script_html();
        
        if ($msgs) {
            echo $msgs->get_as_html();
        }
        
        return true;
    }
}
?>
