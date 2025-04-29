<?php
/**
 * Model Module D.Menu Editor Class
 *
 * @version 1.0
 * 
 * @author D.art <d.art.reply@gmail.com>
 */

namespace Opencart\Admin\Model\Extension\DMenuEditor\Module;
class DMenuEditor extends \Opencart\System\Engine\Model {

    /**
     * Get Information data.
     * 
     * @param int $limit
     * 
     * @return array $information_data
     */
    public function getInformation(int $limit = 20): array {
        $information_data = array();

        $query = $this->db->query("SELECT i.`information_id` FROM `" . DB_PREFIX . "information` i LEFT JOIN `" . DB_PREFIX . "information_description` id ON (i.`information_id` = id.`information_id`) WHERE id.`language_id` = '" . (int)$this->config->get('config_language_id') . "' AND i.`status` = '1' GROUP BY i.`information_id` ORDER BY id.`title` ASC LIMIT " . (int)$limit);

        // Get Description in all languages.
        foreach ($query->rows as $result) {
            $information_data[] = $this->getLayoutDescription('information', $result['information_id']);
        }

        //usort($information_data, function($a, $b){ return strcmp($a['title'], $b['title']); });

        return $information_data;
    }

    /**
     * Get Categories data.
     * 
     * @param int $limit
     * 
     * @return array $categories_data
     */
    public function getCategories(int $limit = 20): array {
        $categories_data = array();

        $query = $this->db->query("SELECT c1.`category_id`, TRIM('0_' FROM CONCAT(GROUP_CONCAT(c2.`parent_id` ORDER BY cp.`level` SEPARATOR '_'), '_', c1.`category_id`)) AS path FROM `" . DB_PREFIX . "category_path` cp LEFT JOIN `" . DB_PREFIX . "category` c1 ON (cp.`category_id` = c1.`category_id`) LEFT JOIN `" . DB_PREFIX . "category` c2 ON (cp.`path_id` = c2.`category_id`) LEFT JOIN `" . DB_PREFIX . "category_description` cd1 ON (cp.`path_id` = cd1.`category_id`) LEFT JOIN `" . DB_PREFIX . "category_description` cd2 ON (cp.`category_id` = cd2.`category_id`) WHERE cd1.`language_id` = '" . (int)$this->config->get('config_language_id') . "' AND cd2.`language_id` = '" . (int)$this->config->get('config_language_id') . "' GROUP BY cp.`category_id` ORDER BY cd1.`name` DESC LIMIT " . (int)$limit);

        // Get Description in all languages.
        foreach ($query->rows as $result) {
            $categories_data[] = $this->getLayoutDescription('category', $result['category_id'], $result['path']);
        }

        usort($categories_data, function($a, $b){ return strcmp($a['title'], $b['title']); });

        return $categories_data;
    }

    /**
     * Get Products data.
     * 
     * @param int $limit
     * 
     * @return array $products_data
     */
    public function getProducts(int $limit = 20): array {
        $categories = array();
        $products = array();
        $products_data = array();

        // Get Products from DB.
        $query_products = $this->db->query("SELECT p.`product_id`, ptc.`category_id` FROM `" . DB_PREFIX . "product` p LEFT JOIN `" . DB_PREFIX . "product_description` pd ON (p.`product_id` = pd.`product_id`) LEFT JOIN `" . DB_PREFIX . "product_to_category` ptc ON (p.`product_id` = ptc.`product_id`) WHERE pd.`language_id` = '" . (int)$this->config->get('config_language_id') . "' AND p.`status` = '1' GROUP BY p.`product_id` ORDER BY p.`date_added` DESC LIMIT " . (int)$limit);

        // Array of categories.
        foreach ($query_products->rows as $result) {
            if ($result['category_id']) {
                $categories[] = $result['category_id'];
            }
        }

        // Get category Path.
        $query_category_path = $this->db->query("SELECT c1.`category_id`, TRIM('0_' FROM CONCAT(GROUP_CONCAT(c2.`parent_id` ORDER BY cp.`level` SEPARATOR '_'), '_', c1.`category_id`)) AS path FROM `" . DB_PREFIX . "category_path` cp LEFT JOIN `" . DB_PREFIX . "category` c1 ON (cp.`category_id` = c1.`category_id`) LEFT JOIN `" . DB_PREFIX . "category` c2 ON (cp.`path_id` = c2.`category_id`) WHERE cp.`category_id` IN (" . implode(',', $categories) . ") GROUP BY cp.`category_id`");

        // Array of Products.
        foreach ($query_products->rows as $result) {
            $path = '';

            foreach ($query_category_path->rows as $row) {
                if ($row['category_id'] == $result['category_id']) {
                    $path = $row['path'];
                    break;
                }
            }

            $products[] = array(
                'product_id'  => $result['product_id'],
                'path'        => $path
            );
        }

        // Get Description in all languages.
        foreach ($products as $result) {
            $products_data[] = $this->getLayoutDescription('product', $result['product_id'], $result['path']);
        }

        usort($products_data, function($a, $b){ return strcmp($a['title'], $b['title']); });

        return $products_data;
    }

    /**
     * Get Manufacturers data.
     * 
     * @param int $limit
     * 
     * @return array $manufacturers_data
     */
    public function getManufacturers(int $limit = 20): array {
        $this->load->model('localisation/language');

        // All languages.
        $languages = $this->model_localisation_language->getLanguages();

        $manufacturers_data = array();

        $query = $this->db->query("SELECT m.`manufacturer_id`, m.`name` FROM `" . DB_PREFIX . "manufacturer` m GROUP BY m.`manufacturer_id` ORDER BY m.`name` ASC LIMIT " . (int)$limit);

        // Get Description in all languages.
        foreach ($query->rows as $result) {
            $names = array();

            foreach ($languages as $language) {
                $names[$language['language_id']] = $result['name'];
            }

            $manufacturers_data[] = array(
                'id'     => $result['manufacturer_id'],
                'layout' => 'manufacturer',
                'url'    => $this->getUrl('manufacturer', $result['manufacturer_id']),
                'names'  => $names,
                'title'  => $result['name']
            );
        }

        //usort($manufacturers_data, function($a, $b){ return strcmp($a['title'], $b['title']); });

        return $manufacturers_data;
    }

    /**
     * Get CMS Blog Categories data.
     * 
     * @param int $limit
     * 
     * @return array $blog_categories_data
     */
    public function getBlogCategories(int $limit = 20): array {
        $blog_categories_data = array();

        $table_name = DB_PREFIX . 'topic';

        if ($this->tableExists($table_name)) {
            $query = $this->db->query("SELECT t.`topic_id` FROM `" . DB_PREFIX . "topic` t LEFT JOIN `" . DB_PREFIX . "topic_description` td ON (t.`topic_id` = td.`topic_id`) WHERE td.language_id = '" . (int)$this->config->get('config_language_id') . "' AND t.`status` = '1' GROUP BY t.`topic_id` ORDER BY td.`name` ASC LIMIT " . (int)$limit);

            // Get Description in all languages.
            foreach ($query->rows as $result) {
                $blog_categories_data[] = $this->getLayoutDescription('blog_category', $result['topic_id']);
            }

            usort($blog_categories_data, function($a, $b){ return strcmp($a['title'], $b['title']); });
        }

        return $blog_categories_data;
    }

    /**
     * Get CMS Blog Articles data.
     * 
     * @param int $limit
     * 
     * @return array $blog_articles_data
     */
    public function getBlogArticles(int $limit = 20): array {
        $blog_articles_data = array();

        $table_name = DB_PREFIX . 'article';

        if ($this->tableExists($table_name)) {
            // Get Articles from DB.
            $query_articles = $this->db->query("SELECT a.`article_id`, a.`topic_id` FROM `" . DB_PREFIX . "article` a LEFT JOIN `" . DB_PREFIX . "article_description` ad ON (a.`article_id` = ad.`article_id`) WHERE ad.`language_id` = '" . (int)$this->config->get('config_language_id') . "' AND a.`status` = '1' GROUP BY a.`article_id` ORDER BY a.`date_added` DESC LIMIT " . (int)$limit);

            // Get Description in all languages.
            foreach ($query_articles->rows as $result) {
                $blog_articles_data[] = $this->getLayoutDescription('blog_article', $result['article_id']);
            }

            usort($blog_articles_data, function($a, $b){ return strcmp($a['title'], $b['title']); });
        }

        return $blog_articles_data;
    }

    /**
     * Get Stores.
     *
     * @return array $query->rows
     */
    public function getStores(): array {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "store` s ORDER BY s.`store_id`");
        return $query->rows;
    }

    /**
     * Search data.
     * 
     * @param string $layout
     * @param string $search
     * @param int $limit
     * 
     * @return array $search_data
     */
    public function search(string $layout, string $search, int $limit = 20): array {
        $search_data = array();

        switch ($layout) {
            // Information.
            case 'information':
                $layout_id = 'information_id';
                $order_by = 'id.`title`';
                $group_by = 'i.`information_id`';

                $sql = "SELECT i.`information_id` FROM `" . DB_PREFIX . "information` i LEFT JOIN `" . DB_PREFIX . "information_description` id ON (i.`information_id` = id.`information_id`) WHERE id.`language_id` = '" . (int)$this->config->get('config_language_id') . "' AND i.`status` = '1' AND (";

                break;

            // Category.
            case 'category':
                $layout_id = 'category_id';
                $order_by = 'cd.`name`';
                $group_by = 'c.`category_id`';

                $sql = "SELECT c.`category_id` FROM `" . DB_PREFIX . "category` c LEFT JOIN `" . DB_PREFIX . "category_description` cd ON (c.`category_id` = cd.`category_id`) WHERE cd.`language_id` = '" . (int)$this->config->get('config_language_id') . "' AND c.`status` = '1' AND (";

                break;

            // Product.
            case 'product':
                $layout_id = 'product_id';
                $order_by = 'pd.`name`';
                $group_by = 'p.`product_id`';

                $sql = "SELECT p.`product_id` FROM `" . DB_PREFIX . "product` p LEFT JOIN `" . DB_PREFIX . "product_description` pd ON (p.`product_id` = pd.`product_id`) WHERE pd.`language_id` = '" . (int)$this->config->get('config_language_id') . "' AND p.`status` = '1' AND (";

                break;

            // Manufacturer.
            case 'manufacturer':
                $layout_id = 'manufacturer_id';
                $order_by = 'm.`name`';
                $group_by = 'm.`manufacturer_id`';
                $title = 'name';

                $sql = "SELECT m.`manufacturer_id`, m.`name` FROM `" . DB_PREFIX . "manufacturer` m WHERE (";

                break;

            // CMS Blog Categories.
            case 'blog_category':
                $layout_id = 'topic_id';
                $order_by = 'td.`name`';
                $group_by = 't.`topic_id`';

                $sql = "SELECT t.`topic_id` FROM `" . DB_PREFIX . "topic` t LEFT JOIN `" . DB_PREFIX . "topic_description` td ON (t.`topic_id` = td.`topic_id`) WHERE td.`language_id` = '" . (int)$this->config->get('config_language_id') . "' AND t.`status` = '1' AND (";

                break;

            // CMS Blog Articles.
            case 'blog_article':
                $layout_id = 'article_id';
                $order_by = 'ad.`name`';
                $group_by = 'a.`article_id`';

                $sql = "SELECT a.`article_id` FROM `" . DB_PREFIX . "article` a LEFT JOIN `" . DB_PREFIX . "article_description` ad ON (a.`article_id` = ad.`article_id`) WHERE ad.`language_id` = '" . (int)$this->config->get('config_language_id') . "' AND a.`status` = '1' AND (";

                break;

            // etc.
            default:
                return $search_data;
        }

        $sql .= $order_by . " LIKE '%" . $this->db->escape(trim($search)) . "%') GROUP BY " . $group_by . " ORDER BY " . $order_by . " ASC LIMIT " . (int)$limit;

        $query = $this->db->query($sql);

        // Search Layout Description.
        switch ($layout) {
            // Manufacturer.
            case 'manufacturer':
                $this->load->model('localisation/language');

                // All languages.
                $languages = $this->model_localisation_language->getLanguages();

                foreach ($query->rows as $result) {
                    $names = array();

                    foreach ($languages as $language) {
                        $names[$language['language_id']] = $result[$title];
                    }

                    $search_data[] = array(
                        'id'     => $result[$layout_id],
                        'layout' => $layout,
                        'url'    => $this->getUrl($layout, $result[$layout_id]),
                        'names'  => $names,
                        'title'  => $result[$title]
                    );
                }

                break;

            // etc.
            default:
                foreach ($query->rows as $result) {
                    $search_data[] = $this->getLayoutDescription($layout, $result[$layout_id]);
                }

                break;
        }

        usort($search_data, function($a, $b){ return strcmp($a['title'], $b['title']); });

        return $search_data;
    }

    /**
     * Get Layout Description.
     * 
     * @param string $layout
     * @param int $id
     * 
     * @return array $layout_data
     */
    private function getLayoutDescription(string $layout, int $id): array {
        $layout_data = array(
            'id'     => $id,
            'layout' => $layout,
            'url'    => $this->getUrl($layout, $id),
            'names'  => array()
        );

        switch ($layout) {
            // Information.
            case 'information':
                $search_column = 'title';
                $query = $this->db->query("SELECT id.`title`, id.`language_id` FROM `" . DB_PREFIX . "information_description` id WHERE id.`information_id` = '" . (int)$id . "'");
                break;

            // Category.
            case 'category':
                $search_column = 'name';
                $query = $this->db->query("SELECT cd.`name`, cd.`language_id` FROM `" . DB_PREFIX . "category_description` cd WHERE cd.`category_id` = '" . (int)$id . "'");
                break;

            // Product.
            case 'product':
                $search_column = 'name';
                $query = $this->db->query("SELECT pd.`name`, pd.`language_id` FROM `" . DB_PREFIX . "product_description` pd WHERE pd.`product_id` = '" . (int)$id . "'");
                break;

            // CMS Blog Category.
            case 'blog_category':
                $search_column = 'name';
                $query = $this->db->query("SELECT td.`name`, td.`language_id` FROM `" . DB_PREFIX . "topic_description` td WHERE td.`topic_id` = '" . (int)$id . "'");
                break;

            // CMS Blog Article.
            case 'blog_article':
                $search_column = 'name';
                $query = $this->db->query("SELECT ad.`name`, ad.`language_id` FROM `" . DB_PREFIX . "article_description` ad WHERE ad.`article_id` = '" . (int)$id . "'");
                break;

            // etc.
            default:
                return $layout_data;
        }

        foreach ($query->rows as $result) {
            if ($result['language_id'] == $this->config->get('config_language_id')) {
                $layout_data['title'] = $result[$search_column];
            }

            $layout_data['names'][$result['language_id']] = $result[$search_column];
        }

        return $layout_data;
    }

    /**
     * Get URLs.
     * 
     * @param string $layout
     * @param int $id
     * @param string $path
     * 
     * @return array $url
     */
    public function getUrl(string $layout, int $id = 0, string $path = ''): array {
        $x = version_compare(VERSION, '4.0.2.0', '>=') ? '.' : '|';
        //$language_route = 'language=' . $this->config->get('config_language');

        $url = array();

        switch ($layout) {
            // Home.
            case 'home':
                $key = 'route';
                $value = 'common/home';
                $link = 'index.php?route=common/home';
                break;

            // Contact Us.
            case 'contact':
                $key = 'route';
                $value = 'information/contact';
                $link = 'index.php?route=information/contact';
                break;

            // Sitemap.
            case 'sitemap':
                $key = 'route';
                $value = 'information/sitemap';
                $link = 'index.php?route=information/sitemap';
                break;

            // Cart.
            case 'cart':
                $key = 'route';
                $value = 'checkout/cart';
                $link = 'index.php?route=checkout/cart';
                break;

            // Checkout.
            case 'checkout':
                $key = 'route';
                $value = 'checkout/checkout';
                $link = 'index.php?route=checkout/checkout';
                break;

            // Compare.
            case 'compare':
                $key = 'route';
                $value = 'product/compare';
                $link = 'index.php?route=product/compare';
                break;

            // Wishlist.
            case 'wishlist':
                $key = 'route';
                $value = 'account/wishlist';
                $link = 'index.php?route=account/wishlist';
                break;

            // Manufacturers.
            case 'manufacturers':
                $key = 'route';
                $value = 'product/manufacturer';
                $link = 'index.php?route=product/manufacturer';
                break;

            // Special.
            case 'special':
                $key = 'route';
                $value = 'product/special';
                $link = 'index.php?route=product/special';
                break;

            // Search.
            case 'search':
                $key = 'route';
                $value = 'product/search';
                $link = 'index.php?route=product/search';
                break;

            // Account.
            case 'account':
                $key = 'route';
                $value = 'account/account';
                $link = 'index.php?route=account/account';
                break;

            // Account Login.
            case 'login':
                $key = 'route';
                $value = 'account/login';
                $link = 'index.php?route=account/login';
                break;

            // Account Register.
            case 'register':
                $key = 'route';
                $value = 'account/register';
                $link = 'index.php?route=account/register';
                break;

            // Account Logout.
            case 'logout':
                $key = 'route';
                $value = 'account/logout';
                $link = 'index.php?route=account/logout';
                break;

            // Information.
            case 'information':
                $key = 'information_id';
                $value = $id;
                $link = 'index.php?route=information/information&information_id=' . $id;
                break;

            // Category.
            case 'category':
                $key = 'path';
                $value = $path;

                if (empty($path)) {
                    $link = 'index.php?route=product/category&path=' . (int)$id;
                } else {
                    $link = 'index.php?route=product/category&path=' . $path;
                }

                break;

            // Product.
            case 'product':
                $key = 'product_id';
                $value = $id;

                if (empty($path)) {
                    $link = 'index.php?route=product/product&product_id=' . (int)$id;
                } else {
                    $link = 'index.php?route=product/product&path=' . $path . '&product_id=' . (int)$id;
                }

                break;

            // Manufacturer.
            case 'manufacturer':
                $key = 'manufacturer_id';
                $value = $id;
                $link = 'index.php?route=product/manufacturer' . $x . 'info&manufacturer_id=' . $id;
                break;

            // CMS Blog Category.
            case 'blog_category':
                $key = 'topic_id';
                $value = $id;
                $link = 'index.php?route=cms/blog&topic_id=' . (int)$id;
                break;

            // CMS Blog Article.
            case 'blog_article':
                $key = 'article_id';
                $value = $id;
                $link = 'index.php?route=cms/blog' . $x . 'info&article_id=' . (int)$id;
                break;

            // etc.
            default:
                $url['link'] = '';
                return $url;
        }

        $url['link'] = $link;

        $seo_urls = $this->getSeoUrls($key, $value);

        if ($this->config->get('config_seo_url') && !empty($seo_urls)) {
            $url['seo'] = $seo_urls;
        }

        return $url;
    }

    /**
     * Get SEO URLs.
     * 
     * @param string $key
     * @param string $value
     * 
     * @return array $seo_url_data
     */
    private function getSeoUrls(string $key, string $value): array {
        $seo_url_data = array();

        $query = $this->db->query("SELECT su.`language_id`, su.`keyword` FROM `" . DB_PREFIX . "seo_url` su WHERE su.`key` = '" . $key . "' AND su.`value` = '" . $value . "'");

        foreach ($query->rows as $result) {
            $seo_url_data[$result['language_id']] = $result['keyword'];
        }

        return $seo_url_data;
    }

    /**
     * A list of chosen tables.
     * 
     * @param string $tables_name
     * 
     * @return bool $exists
     */
    private function tableExists(string $tables_name): bool {
        return $this->db->query("SHOW TABLES LIKE '" . $tables_name . "'")->num_rows > 0;
    }
}