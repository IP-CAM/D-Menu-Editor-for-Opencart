<?php
/**
 * Controller Module D.Menu Editor Class
 *
 * @version 1.0
 * 
 * @author D.art <d.art.reply@gmail.com>
 */

class ControllerExtensionModuleDMenuEditorMenu extends Controller {
    public function index($data) {
        return $this->load->view('extension/module/dmenu_editor/menu', $data);
    }
}