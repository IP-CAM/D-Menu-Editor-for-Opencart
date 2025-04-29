<?php
/**
 * Controller Module D.Menu Editor Class
 *
 * @version 1.1.0
 * 
 * @author D.art <d.art.reply@gmail.com>
 */

namespace Opencart\Admin\Controller\Extension\DMenuEditor\Module\DMenuEditor;
class Menu extends \Opencart\System\Engine\Controller {
    public function index(array $data): string  {
        return $this->load->view('extension/dmenu_editor/module/dmenu_editor/menu', $data);
    }
}