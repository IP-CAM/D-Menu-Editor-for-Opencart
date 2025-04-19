<?php
/**
 * Controller Module D.Menu Editor Class
 *
 * @version 1.0
 * 
 * @author D.art <d.art.reply@gmail.com>
 */

class ControllerExtensionModuleDMenuEditorSettings extends Controller {
    public function index($data) {
        $this->load->language('extension/module/dmenu_editor');

        // Settings «Main Menu».
        if (!isset($data['module_settings']['menu']['main']['status'])) {
            $data['module_settings']['menu']['main']['status'] = 0;
            $data['module_settings']['menu']['main']['close'] = 0;
        }

        // Settings «Top Menu».
        if (!isset($data['module_settings']['menu']['top']['status'])) {
            $data['module_settings']['menu']['top']['status'] = 0;
            $data['module_settings']['menu']['top']['close'] = 0;
        }

        // Settings «Footer Menu».
        if (!isset($data['module_settings']['menu']['footer']['status'])) {
            $data['module_settings']['menu']['footer']['status'] = 0;
            $data['module_settings']['menu']['footer']['close'] = 0;
        }

        // Settings «Social Menu».
        if (!isset($data['module_settings']['menu']['social']['status'])) {
            $data['module_settings']['menu']['social']['status'] = 0;
            $data['module_settings']['menu']['social']['close'] = 0;
        }

        // Settings 'Main Menu' HTML.
        $data['menu_type'] = 'main';
        $data['settings_menu_main'] = $this->load->view('extension/module/dmenu_editor/settings/menu', $data);

        // Settings 'Top Menu' HTML.
        $data['menu_type'] = 'top';
        $data['settings_menu_top'] = $this->load->view('extension/module/dmenu_editor/settings/menu', $data);

        // Settings 'Footer Menu' HTML.
        $data['menu_type'] = 'footer';
        $data['settings_menu_footer'] = $this->load->view('extension/module/dmenu_editor/settings/menu', $data);

        // Settings 'Social Menu' HTML.
        $data['menu_type'] = 'social';
        $data['settings_menu_social'] = $this->load->view('extension/module/dmenu_editor/settings/menu', $data);

        // Settings 'General' HTML.
        $data['menu_type'] = '';
        $data['settings_general'] = $this->load->view('extension/module/dmenu_editor/settings/general', $data);

        return $this->load->view('extension/module/dmenu_editor/settings', $data);
    }
}