<?php
/**
 * Controller Module D.Menu Editor Class
 *
 * @version 1.0
 * 
 * @author D.art <d.art.reply@gmail.com>
 */

namespace Opencart\Admin\Controller\Extension\DMenuEditor\Module;
class DMenuEditor extends \Opencart\System\Engine\Controller {
    private $error = array();
    private $languages = array();
    private $prepared = array();

    private $settings = array(
        'menu' => array(
            'main' => array(
                'icon' => array(
                    'dimensions' => array('width' => 16, 'height' => 16)
                )
            ),
            'top' => array(
                'icon' => array(
                    'dimensions' => array('width' => 16, 'height' => 16)
                )
            ),
            'footer' => array(
                'icon' => array(
                    'dimensions' => array('width' => 16, 'height' => 16)
                )
            ),
            'social' => array(
                'icon' => array(
                    'dimensions' => array('width' => 16, 'height' => 16)
                )
            )
        ),
        'items_limit' => 50,
        'search_limit' => 20
    );

    public function index(): void {
        $this->load->language('extension/dmenu_editor/module/dmenu_editor');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('localisation/language');
        $this->load->model('setting/setting');
        $this->load->model('tool/image');
        $this->load->model('extension/dmenu_editor/module/dmenu_editor');

        // Module Settings.
        if (isset($this->request->post['module_dmenu_editor_settings'])) {
            $data['module_settings'] = $this->request->post['module_dmenu_editor_settings'];
        } else if (!empty($this->config->get('module_dmenu_editor_settings'))) {
            $data['module_settings'] = $this->config->get('module_dmenu_editor_settings');
        } else {
            $data['module_settings'] = array();
        }

        // Setting 'Icon Dimensions'.
        foreach ($this->settings['menu'] as $menu_type => $menu) {
            $this->dimensions($data['module_settings'], $menu_type);
        }

        // Save Module data.
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate($this->request->post, array('validate', 'edit'))) {
            // Set prepared data to POST.
            $this->request->post['module_dmenu_editor_prepared'] = $this->prepared;

            // Set POST data to DB.
            $this->model_setting_setting->editSetting('module_dmenu_editor', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            if (!isset($this->request->get['apply'])) {
                $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module'));
            } else {
                $data['success'] = $this->language->get('text_success');
            }
        }

        $data['_x_'] = version_compare(VERSION, '4.0.2.0', '>=') ? '.' : '|';

        $this->document->addStyle(HTTP_CATALOG . '/extension/dmenu_editor/admin/view/javascript/module-dmenu_editor/dmenu_editor.css');
        $this->document->addScript(HTTP_CATALOG . '/extension/dmenu_editor/admin/view/javascript/module-dmenu_editor/sortable/sortable.min.js');
        $this->document->addScript(HTTP_CATALOG . '/extension/dmenu_editor/admin/view/javascript/module-dmenu_editor/dmenu_editor.js');

        // Module Warnings.
        if (isset($this->error['error_items'])) {
            $data['error_items'] = $this->error['error_items'];
        } else {
            $data['error_items'] = array();
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/dmenu_editor/module/dmenu_editor', 'user_token=' . $this->session->data['user_token'])
        );

        // Module buttons.
        $data['apply'] = $this->url->link('extension/dmenu_editor/module/dmenu_editor', 'user_token=' . $this->session->data['user_token'] . '&apply=1');
        $data['action'] = $this->url->link('extension/dmenu_editor/module/dmenu_editor', 'user_token=' . $this->session->data['user_token']);
		$data['back'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module');

        // User Token.
        $data['user_token'] = $this->session->data['user_token'];

        // Current language ID.
        $data['config_language_id'] = (int)$this->config->get('config_language_id');

        // Translated Text.
        $data['translated_text'] = array(
            'text_item_desc_none'       => $this->language->get('text_item_desc_none'),
            'text_item_desc_catalog'    => $this->language->get('text_item_desc_catalog'),
            'text_result_categories'    => $this->language->get('text_result_categories'),
            'text_select_none'          => $this->language->get('text_select_none'),
            'text_target_self'          => $this->language->get('text_target_self'),
            'text_target_blank'         => $this->language->get('text_target_blank'),
            'text_target_parent'        => $this->language->get('text_target_parent'),
            'text_target_top'           => $this->language->get('text_target_top'),
            'text_yes'                  => $this->language->get('text_yes'),
            'text_no'                   => $this->language->get('text_no'),

            'entry_status'              => $this->language->get('entry_status'),
            'entry_name'                => $this->language->get('entry_name'),
            'entry_name_hide'           => $this->language->get('entry_name_hide'),
            'entry_url'                 => $this->language->get('entry_url'),
            'entry_target'              => $this->language->get('entry_target'),
            'entry_xfn'                 => $this->language->get('entry_xfn'),
            'entry_class'               => $this->language->get('entry_class'),
            'entry_icon'                => $this->language->get('entry_icon'),
            'entry_category_menu'       => $this->language->get('entry_category_menu'),
            'entry_category_menu_title' => $this->language->get('entry_category_menu_title'),

            'button_look_tip'           => $this->language->get('button_look_tip'),
            'button_remove_item_tip'    => $this->language->get('button_remove_item_tip'),
            'button_edit_item_tip'      => $this->language->get('button_edit_item_tip'),
            'button_lock_tip'           => $this->language->get('button_lock_tip'),
            'button_unlock_tip'         => $this->language->get('button_unlock_tip'),
            'button_edit'               => $this->language->get('button_edit'),
            'button_clear'              => $this->language->get('button_clear'),

            'note_title_empty'          => $this->language->get('note_title_empty')
        );

        // Setting 'Status'.
        if (isset($this->request->post['module_dmenu_editor_status'])) {
            $data['module_dmenu_editor_status'] = $this->request->post['module_dmenu_editor_status'];
        } else if (!empty($this->config->get('module_dmenu_editor_status'))) {
            $data['module_dmenu_editor_status'] = $this->config->get('module_dmenu_editor_status');
        } else {
            $data['module_dmenu_editor_status'] = 0;
        }

        // Settings 'Icon Dimensions' to $data.
        $data['icon_dimensions'] = array(
            'menu' => array(
                'main'   => $this->settings['menu']['main']['icon']['dimensions'],
                'top'    => $this->settings['menu']['top']['icon']['dimensions'],
                'footer' => $this->settings['menu']['footer']['icon']['dimensions'],
                'social' => $this->settings['menu']['social']['icon']['dimensions']
            )
        );

        // Setting 'CMS Blog Support'.
        if (empty($data['module_settings']['general']['cms_blog'])) {
            $data['module_settings']['general']['cms_blog'] = 0;
        }

        // Stores.
		$data['stores'] = array();

		$data['stores'][] = array(
			'store_id' => 0,
			'name'     => $this->language->get('text_store_default'),
            'url'      => ''
		);

        $results = $this->model_extension_dmenu_editor_module_dmenu_editor->getStores();

        foreach ($results as $result) {
            $data['stores'][] = $result;
        }

        // Main Menu Items.
        $data['menus']['main'] = array();

        foreach ($data['stores'] as $store) {
            if (isset($this->request->post['module_dmenu_editor_items_main_' . $store['store_id']])) {
                $data['menus']['main']['store_' . $store['store_id']] = $this->request->post['module_dmenu_editor_items_main_' . $store['store_id']];
            } else if (is_array($this->config->get('module_dmenu_editor_items_main_' . $store['store_id']))) {
                $data['menus']['main']['store_' . $store['store_id']] = $this->config->get('module_dmenu_editor_items_main_' . $store['store_id']);
            } else {
                $data['menus']['main']['store_' . $store['store_id']] = array();
            }
        }

        // Top Menu Items.
        $data['menus']['top'] = array();

        foreach ($data['stores'] as $store) {
            if (isset($this->request->post['module_dmenu_editor_items_top_' . $store['store_id']])) {
                $data['menus']['top']['store_' . $store['store_id']] = $this->request->post['module_dmenu_editor_items_top_' . $store['store_id']];
            } else if (is_array($this->config->get('module_dmenu_editor_items_top_' . $store['store_id']))) {
                $data['menus']['top']['store_' . $store['store_id']] = $this->config->get('module_dmenu_editor_items_top_' . $store['store_id']);
            } else {
                $data['menus']['top']['store_' . $store['store_id']] = array();
            }
        }

        // Footer Menu Items.
        $data['menus']['footer'] = array();

        foreach ($data['stores'] as $store) {
            if (isset($this->request->post['module_dmenu_editor_items_footer_' . $store['store_id']])) {
                $data['menus']['footer']['store_' . $store['store_id']] = $this->request->post['module_dmenu_editor_items_footer_' . $store['store_id']];
            } else if (is_array($this->config->get('module_dmenu_editor_items_footer_' . $store['store_id']))) {
                $data['menus']['footer']['store_' . $store['store_id']] = $this->config->get('module_dmenu_editor_items_footer_' . $store['store_id']);
            } else {
                $data['menus']['footer']['store_' . $store['store_id']] = array();
            }
        }

        // Social Menu Items.
        $data['menus']['social'] = array();

        foreach ($data['stores'] as $store) {
            if (isset($this->request->post['module_dmenu_editor_items_social_' . $store['store_id']])) {
                $data['menus']['social']['store_' . $store['store_id']] = $this->request->post['module_dmenu_editor_items_social_' . $store['store_id']];
            } else if (is_array($this->config->get('module_dmenu_editor_items_social_' . $store['store_id']))) {
                $data['menus']['social']['store_' . $store['store_id']] = $this->config->get('module_dmenu_editor_items_social_' . $store['store_id']);
            } else {
                $data['menus']['social']['store_' . $store['store_id']] = array();
            }
        }

        // Menu Item placeholder.
        $data['item_placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

        // Menu Item layouts.
        $data['module_layouts'] = array(
            'home'          => $this->language->get('text_item_desc_home'),          // Home
            'account'       => $this->language->get('text_item_desc_account'),       // Account
            'login'         => $this->language->get('text_item_desc_login'),         // Account Login
            'logout'        => $this->language->get('text_item_desc_logout'),        // Account Logout
            'register'      => $this->language->get('text_item_desc_register'),      // Account Register
            'contact'       => $this->language->get('text_item_desc_contact'),       // Contact Us
            'sitemap'       => $this->language->get('text_item_desc_sitemap'),       // Sitemap
            'compare'       => $this->language->get('text_item_desc_compare'),       // Compare
            'wishlist'      => $this->language->get('text_item_desc_wishlist'),      // Wishlist
            'cart'          => $this->language->get('text_item_desc_cart'),          // Cart
            'checkout'      => $this->language->get('text_item_desc_checkout'),      // Checkout
            'special'       => $this->language->get('text_item_desc_special'),       // Special
            'search'        => $this->language->get('text_item_desc_search'),        // Search
            'information'   => $this->language->get('text_item_desc_information'),   // Information
            'catalog'       => $this->language->get('text_item_desc_catalog'),       // Catalog
            'category'      => $this->language->get('text_item_desc_category'),      // Category
            'product'       => $this->language->get('text_item_desc_product'),       // Product
            'manufacturers' => $this->language->get('text_item_desc_manufacturers'), // Manufacturers
            'manufacturer'  => $this->language->get('text_item_desc_manufacturer'),  // Manufacturer
            'blog_category' => $this->language->get('text_item_desc_blog_category'), // ocStore Blog Category
            'blog_article'  => $this->language->get('text_item_desc_blog_article'),  // ocStore Blog Article
            'custom'        => $this->language->get('text_item_desc_custom'),        // Custom
            'html'          => $this->language->get('text_item_desc_html'),          // HTML
            'none'          => $this->language->get('text_item_desc_none')           // None
        );

        // Search limit.
        $data['search_limit'] = $this->settings['search_limit'];
        $data['search_limit_text'] = sprintf($this->language->get('help_sticky_search'), $data['search_limit']);

        // All languages.
        $this->languages = $this->model_localisation_language->getLanguages();
        $data['languages'] = $this->languages;

        // Information.
        $data['information_limit'] = $this->settings['items_limit'];
        $data['information'] = $this->model_extension_dmenu_editor_module_dmenu_editor->getInformation($data['information_limit']);

        // Categories.
        $data['categories_limit'] = $this->settings['items_limit'];
        $data['categories'] = $this->model_extension_dmenu_editor_module_dmenu_editor->getCategories($data['categories_limit']);

        // Products.
        $data['products_limit'] = $this->settings['items_limit'];
        $data['products'] = $this->model_extension_dmenu_editor_module_dmenu_editor->getProducts($data['products_limit']);

        // Manufacturers.
        $data['manufacturers_limit'] = $this->settings['items_limit'];
        $data['manufacturers'] = $this->model_extension_dmenu_editor_module_dmenu_editor->getManufacturers($data['manufacturers_limit']);

        // CMS Blog Categories (v4.1.0.0+).
        if ($data['module_settings']['general']['cms_blog']) {
            $data['blog_categories_limit'] = $this->settings['items_limit'];
            $data['blog_categories'] = $this->model_extension_dmenu_editor_module_dmenu_editor->getBlogCategories($data['blog_categories_limit']);
        } else {
            $data['blog_categories_limit'] = 0;
            $data['blog_categories'] = array();
        }

        // CMS Blog Articles (v4.1.0.0+).
        if ($data['module_settings']['general']['cms_blog']) {
            $data['blog_articles_limit'] = $this->settings['items_limit'];
            $data['blog_articles'] = $this->model_extension_dmenu_editor_module_dmenu_editor->getBlogArticles($data['blog_articles_limit']);
        } else {
            $data['blog_articles_limit'] = 0;
            $data['blog_articles'] = array();
        }

        // Module pages.
        $data['opencart_pages'] = array();
        $data['other_pages'] = array();

        // OC Page 'Home'.
        $data['opencart_pages']['home'] = array(
            'id'     => 0,
            'layout' => 'home',
            'names'  => $this->names('text_item_desc_home'),
            'url'    => $this->model_extension_dmenu_editor_module_dmenu_editor->getUrl('home'),
            'title'  => $this->language->get('text_item_desc_home')
        );

        // OC Page 'Contact Us'.
        $data['opencart_pages']['contact'] = array(
            'id'     => 0,
            'layout' => 'contact',
            'names'  => $this->names('text_item_desc_contact'),
            'url'    => $this->model_extension_dmenu_editor_module_dmenu_editor->getUrl('contact'),
            'title'  => $this->language->get('entry_add_contactus')
        );

        // OC Page 'Sitemap'.
        $data['opencart_pages']['sitemap'] = array(
            'id'     => 0,
            'layout' => 'sitemap',
            'names'  => $this->names('text_item_desc_sitemap'),
            'url'    => $this->model_extension_dmenu_editor_module_dmenu_editor->getUrl('sitemap'),
            'title'  => $this->language->get('entry_add_sitemap')
        );

        // OC Page 'Cart'.
        $data['opencart_pages']['cart'] = array(
            'id'     => 0,
            'layout' => 'cart',
            'names'  => $this->names('text_item_desc_cart'),
            'url'    => $this->model_extension_dmenu_editor_module_dmenu_editor->getUrl('cart'),
            'title'  => $this->language->get('entry_add_cart')
        );

        // OC Page 'Checkout'.
        $data['opencart_pages']['checkout'] = array(
            'id'     => 0,
            'layout' => 'checkout',
            'names'  => $this->names('text_item_desc_checkout'),
            'url'    => $this->model_extension_dmenu_editor_module_dmenu_editor->getUrl('checkout'),
            'title'  => $this->language->get('entry_add_checkout')
        );

        // OC Page 'Compare'.
        $data['opencart_pages']['compare'] = array(
            'id'     => 0,
            'layout' => 'compare',
            'names'  => $this->names('text_item_desc_compare'),
            'url'    => $this->model_extension_dmenu_editor_module_dmenu_editor->getUrl('compare'),
            'title'  => $this->language->get('entry_add_compare')
        );

        // OC Page 'Wishlist'.
        $data['opencart_pages']['wishlist'] = array(
            'id'     => 0,
            'layout' => 'wishlist',
            'names'  => $this->names('text_item_desc_wishlist'),
            'url'    => $this->model_extension_dmenu_editor_module_dmenu_editor->getUrl('wishlist'),
            'title'  => $this->language->get('entry_add_wishlist')
        );

        // OC Page 'Manufacturers'.
        $data['opencart_pages']['manufacturers'] = array(
            'id'     => 0,
            'layout' => 'manufacturers',
            'names'  => $this->names('text_item_desc_manufacturers'),
            'url'    => $this->model_extension_dmenu_editor_module_dmenu_editor->getUrl('manufacturers'),
            'title'  => $this->language->get('entry_add_manufacturers')
        );

        // OC Page 'Special'.
        $data['opencart_pages']['special'] = array(
            'id'     => 0,
            'layout' => 'special',
            'names'  => $this->names('text_item_desc_special'),
            'url'    => $this->model_extension_dmenu_editor_module_dmenu_editor->getUrl('special'),
            'title'  => $this->language->get('entry_add_special')
        );

        // OC Page 'Search'.
        $data['opencart_pages']['search'] = array(
            'id'     => 0,
            'layout' => 'search',
            'names'  => $this->names('text_item_desc_search'),
            'url'    => $this->model_extension_dmenu_editor_module_dmenu_editor->getUrl('search'),
            'title'  => $this->language->get('entry_add_search')
        );

        // OC Page 'Account Register'.
        $data['opencart_pages']['register'] = array(
            'id'     => 0,
            'layout' => 'register',
            'names'  => $this->names('text_item_desc_register'),
            'url'    => $this->model_extension_dmenu_editor_module_dmenu_editor->getUrl('register'),
            'title'  => $this->language->get('entry_add_register')
        );

        // OC Page 'Account'.
        $data['opencart_pages']['account'] = array(
            'id'     => 0,
            'layout' => 'account',
            'names'  => $this->names('text_item_desc_account'),
            'url'    => $this->model_extension_dmenu_editor_module_dmenu_editor->getUrl('account'),
            'title'  => $this->language->get('entry_add_account')
        );

        // OC Page 'Account Login'.
        $data['opencart_pages']['login'] = array(
            'id'     => 0,
            'layout' => 'login',
            'names'  => $this->names('text_item_desc_login'),
            'url'    => $this->model_extension_dmenu_editor_module_dmenu_editor->getUrl('login'),
            'title'  => $this->language->get('entry_add_login')
        );

        // OC Page 'Account Logout'.
        $data['opencart_pages']['logout'] = array(
            'id'     => 0,
            'layout' => 'logout',
            'names'  => $this->names('text_item_desc_logout'),
            'url'    => $this->model_extension_dmenu_editor_module_dmenu_editor->getUrl('logout'),
            'title'  => $this->language->get('entry_add_logout')
        );

        // Other Page 'Catalog'.
        $data['other_pages']['catalog'] = array(
            'id'     => 0,
            'layout' => 'catalog',
            'names'  => $this->names('text_category_menu_catalog'),
            'url'    => '',
            'title'  => ''
        );

        // Other Page 'Custom link'.
        $data['other_pages']['custom'] = array(
            'id'     => 0,
            'layout' => 'custom',
            'names'  => $this->names('entry_add_custom_link'),
            'url'    => '',
            'title'  => ''
        );

        // Main Menu HTML.
        $data['menu_type'] = 'main';
        $data['menu_main'] = $this->load->controller('extension/dmenu_editor/module/dmenu_editor/menu', $data);

        // Top Menu HTML.
        $data['menu_type'] = 'top';
        $data['menu_top'] = $this->load->controller('extension/dmenu_editor/module/dmenu_editor/menu', $data);

        // Footer Menu HTML.
        $data['menu_type'] = 'footer';
        $data['menu_footer'] = $this->load->controller('extension/dmenu_editor/module/dmenu_editor/menu', $data);

        // Social Menu HTML.
        $data['menu_type'] = 'social';
        $data['menu_social'] = $this->load->controller('extension/dmenu_editor/module/dmenu_editor/menu', $data);

        // Settings HTML.
        $data['menu_type'] = '';
        $data['settings'] = $this->load->controller('extension/dmenu_editor/module/dmenu_editor/settings', $data);

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/dmenu_editor/module/dmenu_editor', $data));
    }

    /**
     * Search data in Titles.
     * 
     * @return void
     */
    public function search(): void {
        $this->load->model('extension/dmenu_editor/module/dmenu_editor');

        $json = array();

        if (isset($this->request->post['layout']) && !empty($this->request->post['layout'])) {
            $layout = $this->request->post['layout'];
        } else {
            $layout = '';
        }

        if (isset($this->request->post['limit']) && !empty($this->request->post['limit'])) {
            $limit = (int)$this->request->post['limit'];
        } else {
            $limit = $this->search_limit;
        }

        if (isset($this->request->post['search']) && !empty($this->request->post['search'])) {
            $search_query = strip_tags($this->request->post['search']);

            $json = $this->model_extension_dmenu_editor_module_dmenu_editor->search($layout, $search_query, $limit);
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Change Menu Items. Recursion.
     * 
     * @param array $items
     * @param string $menu_type
     * @param int $store_id
     * @param array $meaning
     * 
     * @return void
     */
    private function changeMenuItems(array &$items, string $menu_type, int $store_id, array $meaning = array()): void {
        $items_count = count($items);

        for ($i = 0; $i < $items_count; $i++) {
            $layout = $items[$i]['data']['layout'];

            switch ($layout) {
                // Layout 'Catalog'.
                case 'catalog':
                    // Validate Menu Item.
                    if (in_array('validate', $meaning)) {
                        if ($items[$i]['data']['category_menu']) {
                            foreach ($items[$i]['data']['category_menu_names'] as $key_name => $name) {
                                if (empty($name)) {
                                    if (!array_key_exists('error', $items[$i])) {
                                        $items[$i]['error'] = array();
                                    }

                                    $items[$i]['error']['category_menu_names'][$key_name] = $this->language->get('error_empty_field');

                                    $this->error['error_items'][$menu_type]['store_' . $store_id]['empty_fields'] = $this->language->get('error_empty_fields');
                                }
                            }
                        }
                    }

                    // Edit Menu Item.
                    //if (in_array('edit', $meaning)) {}

                    break;

                // Layout Other.
                default:
                    // Validate Menu Item.
                    if (in_array('validate', $meaning)) {
                        foreach ($items[$i]['data']['names'] as $key_name => $name) {
                            if (empty($name)) {
                                if (!array_key_exists('error', $items[$i])) {
                                    $items[$i]['error'] = array();
                                }

                                $items[$i]['error']['names'][$key_name] = $this->language->get('error_empty_field');

                                $this->error['error_items'][$menu_type]['store_' . $store_id]['empty_fields'] = $this->language->get('error_empty_fields');
                            }
                        }

                        if (array_key_exists('seo', $items[$i]['data']['url'])) {
                            foreach ($items[$i]['data']['url']['seo'] as $key_seo => $seo) {
                                if (empty(trim($seo))) {
                                    if (!array_key_exists('error', $items[$i])) {
                                        $items[$i]['error'] = array();
                                    }

                                    $items[$i]['error']['seo'][$key_seo] = $this->language->get('error_empty_field');

                                    $this->error['error_items'][$menu_type]['store_' . $store_id]['empty_fields'] = $this->language->get('error_empty_fields');
                                }
                            }
                        }
                    }

                    // Edit Menu Item.
                    //if (in_array('edit', $meaning)) {}

                    // Recursion.
                    if (array_key_exists('rows', $items[$i]) && count($items[$i]['rows']) > 0) {
                        $this->changeMenuItems($items[$i]['rows'], $menu_type, $store_id, $meaning);
                    }

                    break;
            }

            // Edit Menu Item.
            if (in_array('edit', $meaning)) {
                // Menu Item Icon.
                if (isset($items[$i]['data']['icon']['image']) && is_file(DIR_IMAGE . $items[$i]['data']['icon']['image'])) {
                    // Menu Item thumbnail.
                    $items[$i]['data']['icon']['thumb'] = $this->model_tool_image->resize($items[$i]['data']['icon']['image'], $this->settings['menu'][$menu_type]['icon']['dimensions']['width'], $this->settings['menu'][$menu_type]['icon']['dimensions']['height']);
                }

                // Set prepared item ID.
                if (isset($this->prepared['menu'][$menu_type]['store_' . $store_id]['IDs'][$layout])) {
                    if (!in_array($items[$i]['data']['id'], $this->prepared['menu'][$menu_type]['store_' . $store_id]['IDs'][$layout])) {
                        $this->prepared['menu'][$menu_type]['store_' . $store_id]['IDs'][$layout][] = $items[$i]['data']['id'];
                    }
                } else {
                    $this->prepared['menu'][$menu_type]['store_' . $store_id]['IDs'][$layout][] = $items[$i]['data']['id'];
                }
            }
        }
    }

    /**
     * Change Icon Dimensions.
     * 
     * @param array $module_settings
     * @param string $menu_type
     * 
     * @return void
     */
    private function dimensions(array $module_settings, string $menu_type): void {
        if (!empty($module_settings['menu'][$menu_type]['icon'])) {
            if ((int)$module_settings['menu'][$menu_type]['icon']['width'] > 0) {
                $this->settings['menu'][$menu_type]['icon']['dimensions']['width'] = (int)$module_settings['menu'][$menu_type]['icon']['width'];
            }

            if ((int)$module_settings['menu'][$menu_type]['icon']['height'] > 0) {
                $this->settings['menu'][$menu_type]['icon']['dimensions']['height'] = (int)$module_settings['menu'][$menu_type]['icon']['height'];
            }
        }
    }

    /**
     * Set names.
     * 
     * @param string $text
     * 
     * @return array $names
     */
    private function names(string $text): array {
        $names = array();

        foreach($this->languages as $language) {
            $lang = new \Opencart\System\Library\Language($language['code']);
            $lang->addPath(DIR_EXTENSION . 'dmenu_editor/admin/language/');
            $lang->load('module/dmenu_editor', '', $language['code']);

            $names[$language['language_id']] = $lang->get($text);
        }

        return $names;
    }

    /**
     * Validate Menu Items.
     * 
     * @param array $data
     * @param array $meaning
     * 
     * @return bool $this->error
     */
    protected function validate(array &$data, array $meaning = array()): bool {
        // Stores.
		$stores = array();

		$stores[] = array(
			'store_id' => 0,
			'name'     => $this->language->get('text_store_default'),
            'url'      => ''
		);

        $results = $this->model_extension_dmenu_editor_module_dmenu_editor->getStores();

        foreach ($results as $result) {
            $stores[] = $result;
        }

        // Change Main Menu Items.
        foreach ($stores as $store) {
            if (!empty($data['module_dmenu_editor_items_main_' . $store['store_id']])) {
                $this->changeMenuItems($data['module_dmenu_editor_items_main_' . $store['store_id']], 'main', $store['store_id'], $meaning);
            } else {
                $data['module_dmenu_editor_items_main_' . $store['store_id']] = array();
            }
        }

        // Change Top Menu Items.
        foreach ($stores as $store) {
            if (!empty($data['module_dmenu_editor_items_top_' . $store['store_id']])) {
                $this->changeMenuItems($data['module_dmenu_editor_items_top_' . $store['store_id']], 'top', $store['store_id'], $meaning);
            } else {
                $data['module_dmenu_editor_items_top_' . $store['store_id']] = array();
            }
        }

        // Change Footer Menu Items.
        foreach ($stores as $store) {
            if (!empty($data['module_dmenu_editor_items_footer_' . $store['store_id']])) {
                $this->changeMenuItems($data['module_dmenu_editor_items_footer_' . $store['store_id']], 'footer', $store['store_id'], $meaning);
            } else {
                $data['module_dmenu_editor_items_footer_' . $store['store_id']] = array();
            }
        }

        // Change Social Menu Items.
        foreach ($stores as $store) {
            if (!empty($data['module_dmenu_editor_items_social_' . $store['store_id']])) {
                $this->changeMenuItems($data['module_dmenu_editor_items_social_' . $store['store_id']], 'social', $store['store_id'], $meaning);
            } else {
                $data['module_dmenu_editor_items_social_' . $store['store_id']] = array();
            }
        }

        return !$this->error;
    }


    /**
    * Install method.
    *
    * @return void
    */
    public function install(): void {
        // Registering events.
        $this->__registerEvents();
    }

    /**
    * Uninstall method.
    *
    * @return void
    */
    public function uninstall(): void {
        $this->load->model('setting/event');
        $this->model_setting_event->deleteEventByCode('dmenu_editor_1');
        $this->model_setting_event->deleteEventByCode('dmenu_editor_2');
        $this->model_setting_event->deleteEventByCode('dmenu_editor_3');
        $this->model_setting_event->deleteEventByCode('dmenu_editor_4');
        $this->model_setting_event->deleteEventByCode('dmenu_editor_5');

        /* ----------------------------------- */

        $this->model_setting_event->deleteEventByCode('dmenu_editor_6');
        $this->model_setting_event->deleteEventByCode('dmenu_editor_7');
    }

    /**
    * Registering events.
    *
    * @return void
    */
    protected function __registerEvents(): void {
        $x = version_compare(VERSION, '4.0.2.0', '>=') ? '.' : '|';

        // Events array.
        $events = array();

        $events[] = array(
            'code'        => 'dmenu_editor_1',
            'description' => 'Event for «D.Menu Editor» module. Modification «common/currency» template.',
            'trigger'     => 'catalog/view/common/currency/before',
            'action'      => 'extension/dmenu_editor/event/dmenu_editor' . $x . 'catalogViewCurrencyBefore',
            'status'      => 1,
            'sort_order'  => 2
        );

        $events[] = array(
            'code'        => 'dmenu_editor_2',
            'description' => 'Event for «D.Menu Editor» module. Modification «common/language» template.',
            'trigger'     => 'catalog/view/common/language/before',
            'action'      => 'extension/dmenu_editor/event/dmenu_editor' . $x . 'catalogViewLanguageBefore',
            'status'      => 1,
            'sort_order'  => 2
        );

        $events[] = array(
            'code'        => 'dmenu_editor_3',
            'description' => 'Event for «D.Menu Editor» module. Modification «common/menu» template.',
            'trigger'     => 'catalog/controller/common/menu/before',
            'action'      => 'extension/dmenu_editor/event/dmenu_editor' . $x . 'catalogControllerMenuBefore',
            'status'      => 1,
            'sort_order'  => 2
        );

        $events[] = array(
            'code'        => 'dmenu_editor_4',
            'description' => 'Event for «D.Menu Editor» module. Modification «common/header» template.',
            'trigger'     => 'catalog/view/common/header/after',
            'action'      => 'extension/dmenu_editor/event/dmenu_editor' . $x . 'catalogViewHeaderAfter',
            'status'      => 1,
            'sort_order'  => 2
        );

        $events[] = array(
            'code'        => 'dmenu_editor_5',
            'description' => 'Event for «D.Menu Editor» module. Modification «common/footer» template.',
            'trigger'     => 'catalog/view/common/footer/after',
            'action'      => 'extension/dmenu_editor/event/dmenu_editor' . $x . 'catalogViewFooterAfter',
            'status'      => 1,
            'sort_order'  => 2
        );

        /* ----------------------------------- */

        if (version_compare(VERSION, '4.1.0.0', '>=')) {
            // Ignore OpenCart Menu template (twig) from default controller.
            $events[] = array(
                'code'        => 'dmenu_editor_6',
                'description' => 'Event for «D.Menu Editor» module. Modification «common/menu» template.',
                'trigger'     => 'catalog/view/common/menu/before',
                'action'      => 'extension/dmenu_editor/event/dmenu_editor' . $x . 'catalogViewMenuBefore',
                'status'      => 1,
                'sort_order'  => 2
            );

            // Change OpenCart Menu template (twig) from module controller.
            $events[] = array(
                'code'        => 'dmenu_editor_7',
                'description' => 'Event for «D.Menu Editor» module. Modification «common/header» template.',
                'trigger'     => 'catalog/view/common/header/before',
                'action'      => 'extension/dmenu_editor/event/dmenu_editor' . $x . 'catalogViewHeaderBefore',
                'status'      => 1,
                'sort_order'  => 2
            );
        }

        // Loading event model.
        $this->load->model('setting/event');

        // Registering events in DB.
        if (version_compare(VERSION, '4.0.0.0', '>')) {
            foreach($events as $event){
                $this->model_setting_event->addEvent($event);
            }
        } else {
            foreach($events as $event){
                $this->model_setting_event->addEvent($event['code'], $event['description'], $event['trigger'], $event['action'], $event['status'], $event['sort_order']);
            }
        }
    }
}