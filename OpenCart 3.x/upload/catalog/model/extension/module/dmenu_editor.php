<?php
/**
 * Model Module D.Menu Editor Class
 * 
 * @version 1.0.2
 * 
 * @author D.art <d.art.reply@gmail.com>
 */

class ModelExtensionModuleDMenuEditor extends Model {

    /**
     * Get Categories data from Parent category.
     * 
     * @param int $parent_id
     * 
     * @return array $query->rows
     */
    public function getCategories($parent_id = 0) {
        $query = $this->db->query("SELECT c.category_id, cd.name, c.top, c.column, c.status FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id) LEFT JOIN " . DB_PREFIX . "category_to_store c2s ON (c.category_id = c2s.category_id) WHERE c.parent_id = '" . (int)$parent_id . "' AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND c2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND c.status = '1' ORDER BY c.sort_order, LCASE(cd.name)");

        return $query->rows;
    }

    /**
     * Get All Categories data.
     * 
     * @return array $query->rows
     */
    public function getCategoriesAll() {
        $query = $this->db->query("SELECT c.category_id, c.parent_id, c.top, c.column, cd.name, c.status FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id) LEFT JOIN " . DB_PREFIX . "category_to_store c2s ON (c.category_id = c2s.category_id) WHERE cd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND c2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND c.status = '1' ORDER BY c.sort_order, LCASE(cd.name)");

        return $query->rows;
    }

    /**
     * Get Menu Item data.
     * 
     * @param int $id
     * @param string $layout
     * 
     * @return array $item_data
     */
    public function getItem($id, $layout) {
        $item_data = array();

        switch ($layout) {
            // Information.
            case 'information':
                $query = $this->db->query("SELECT i.status FROM " . DB_PREFIX . "information i LEFT JOIN " . DB_PREFIX . "information_to_store i2s ON (i.information_id = i2s.information_id) WHERE i.information_id = '" . (int)$id . "' AND i2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND i.status = '1'");

                $item_data = $query->row;

                break;

            // Category.
            case 'category':
                $query = $this->db->query("SELECT c1.status, TRIM('0_' FROM CONCAT(GROUP_CONCAT(c2.parent_id ORDER BY cp.level SEPARATOR '_'), '_', c1.category_id)) AS path FROM " . DB_PREFIX . "category_path cp LEFT JOIN " . DB_PREFIX . "category c1 ON (cp.category_id = c1.category_id) LEFT JOIN " . DB_PREFIX . "category c2 ON (cp.path_id = c2.category_id) LEFT JOIN " . DB_PREFIX . "category_to_store c2s ON (c1.category_id = c2s.category_id) WHERE c1.category_id = '" . (int)$id . "' AND c2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND c1.status = '1'");

                $item_data = $query->row;

                break;

            // Product.
            case 'product':
                $path = '';

                // Get Product from DB.
                $query_product = $this->db->query("SELECT p2c.category_id, p.status FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE p.product_id = '" . (int)$id . "' AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND p.status = '1'");

                if (isset($query_product->row['status']) && $query_product->row['status']) {
                    // Get Category ID.
                    if ($query_product->row['category_id']) {
                        $category_id = $query_product->row['category_id'];
                    } else {
                        $category_id = 0;
                    }

                    // Get category Path from DB.
                    $query_category_path = $this->db->query("SELECT TRIM('0_' FROM CONCAT(GROUP_CONCAT(c2.parent_id ORDER BY cp.level SEPARATOR '_'), '_', c1.category_id)) AS path FROM " . DB_PREFIX . "category_path cp LEFT JOIN " . DB_PREFIX . "category c1 ON (cp.category_id = c1.category_id) LEFT JOIN " . DB_PREFIX . "category c2 ON (cp.path_id = c2.category_id) LEFT JOIN " . DB_PREFIX . "category_to_store c2s ON (c1.category_id = c2s.category_id) WHERE cp.category_id = '" . (int)$category_id . "' AND c2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND c1.status = '1'");

                    if (isset($query_category_path->row['path'])) {
                        $path = $query_category_path->row['path'];
                    }

                    $item_data['status'] = $query_product->row['status'];
                } else {
                    $item_data['status'] = 0;
                }

                $item_data['path'] = $path;

                break;

            // Manufacturer.
            case 'manufacturer':
                $query = $this->db->query("SELECT m.manufacturer_id FROM " . DB_PREFIX . "manufacturer m LEFT JOIN " . DB_PREFIX . "manufacturer_to_store m2s ON (m.manufacturer_id = m2s.manufacturer_id) WHERE m.manufacturer_id = '" . (int)$id . "' AND m2s.store_id = '" . (int)$this->config->get('config_store_id') . "'");

                $item_data = $query->row;

                break;

            // ocStore Blog Category.
            case 'blog_category':
                $table_name = DB_PREFIX . 'blog_category';

                if ($this->tableExists($table_name)) {
                    $query = $this->db->query("SELECT TRIM('0_' FROM CONCAT(GROUP_CONCAT(bc2.parent_id ORDER BY bcp.level SEPARATOR '_'), '_', bc1.blog_category_id)) AS path, bc1.status FROM " . DB_PREFIX . "blog_category_path bcp LEFT JOIN " . DB_PREFIX . "blog_category bc1 ON (bcp.blog_category_id = bc1.blog_category_id) LEFT JOIN " . DB_PREFIX . "blog_category bc2 ON (bcp.path_id = bc2.blog_category_id) LEFT JOIN " . DB_PREFIX . "blog_category_to_store bc2s ON (bc1.blog_category_id = bc2s.blog_category_id) WHERE bc1.blog_category_id = '" . (int)$id . "' AND bc2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND bc1.status = '1'");

                    $item_data = $query->row;
                }

                break;

            // ocStore Blog Article.
            case 'blog_article':
                $table_name = DB_PREFIX . 'article';

                if ($this->tableExists($table_name)) {
                    // Get Article from DB.
                    $query_article = $this->db->query("SELECT a2bc.blog_category_id, a.status FROM " . DB_PREFIX . "article a LEFT JOIN " . DB_PREFIX . "article_to_blog_category a2bc ON (a.article_id = a2bc.article_id) LEFT JOIN " . DB_PREFIX . "article_to_store a2s ON (a.article_id = a2s.article_id) WHERE a.article_id = '" . (int)$id . "' AND a2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND a.status = '1'");

                    if (isset($query_article->row['status']) && $query_article->row['status']) {
                        // Get Category ID.
                        if ($query_article->row['blog_category_id']) {
                            $blog_category_id = $query_article->row['blog_category_id'];
                        } else {
                            $blog_category_id = 0;
                        }

                        // Get Category Path from DB.
                        $query_category_path = $this->db->query("SELECT TRIM('0_' FROM CONCAT(GROUP_CONCAT(bc2.parent_id ORDER BY bcp.level SEPARATOR '_'), '_', bc1.blog_category_id)) AS path FROM " . DB_PREFIX . "blog_category_path bcp LEFT JOIN " . DB_PREFIX . "blog_category bc1 ON (bcp.blog_category_id = bc1.blog_category_id) LEFT JOIN " . DB_PREFIX . "blog_category bc2 ON (bcp.path_id = bc2.blog_category_id) LEFT JOIN " . DB_PREFIX . "blog_category_to_store bc2s ON (bc1.blog_category_id = bc2s.blog_category_id) WHERE bcp.blog_category_id = '" . (int)$blog_category_id . "' AND bc2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND bc1.status = '1'");

                        if (isset($query_category_path->row['path'])) {
                            $path = $query_category_path->row['path'];
                        }

                        $item_data['status'] = $query_article->row['status'];
                    } else {
                        $item_data['status'] = 0;
                    }

                    $item_data['path'] = $path;
                }

                break;

            // etc.
            default:
                break;
        }

        return $item_data;
    }

    /**
     * Get Prepared Menu Items data.
     * 
     * @param array $IDs
     * @param string $layout
     * 
     * @return array $prepared_data
     */
    public function getItemsPrepared($IDs, $layout) {
        $prepared_data = array();

        switch ($layout) {
            // Information.
            case 'information':
                // Get Information from DB.
                $query = $this->db->query("SELECT i.information_id, i.status FROM " . DB_PREFIX . "information i LEFT JOIN " . DB_PREFIX . "information_to_store i2s ON (i.information_id = i2s.information_id) WHERE  i.information_id IN (" . implode(',', $IDs) . ") AND i2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND i.status = '1' GROUP BY i.information_id ORDER BY i.information_id");

                // Set Information data.
                foreach ($query->rows as $row) {
                    $prepared_data['information_' . $row['information_id']]['status'] = $row['status'];
                }

                break;

            // Category.
            case 'category':
                // Get Categories from DB.
                $query = $this->db->query("SELECT c1.category_id, c2.parent_id, c1.status FROM " . DB_PREFIX . "category_path cp LEFT JOIN " . DB_PREFIX . "category c1 ON (cp.category_id = c1.category_id) LEFT JOIN " . DB_PREFIX . "category c2 ON (cp.path_id = c2.category_id) LEFT JOIN " . DB_PREFIX . "category_to_store c2s ON (c1.category_id = c2s.category_id) WHERE c1.category_id IN (" . implode(',', $IDs) . ") AND c2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND c1.status = '1' ORDER BY c1.category_id, cp.level");

                $count_categories = count($query->rows);
                $last_id = isset($query->rows[0]['category_id']) ? (int)$query->rows[0]['category_id'] : 0;
                $prepared_data['category_' . $last_id]['status'] = isset($query->rows[0]['status']) ? $query->rows[0]['status'] : 0;
                $prepared_data['category_' . $last_id]['path'] = '';

                // Set Categories data.
                for ($i = 0; $i < $count_categories; $i++) {
                    if ($query->rows[$i]['category_id'] != $last_id) {
                        $prepared_data['category_' . $last_id]['path'] .= $last_id;

                        $last_id = (int)$query->rows[$i]['category_id'];
                        $prepared_data['category_' . $last_id]['path'] = '';
                        $prepared_data['category_' . $last_id]['status'] = $query->rows[$i]['status'];
                    }

                    if ($query->rows[$i]['parent_id']) {
                        $prepared_data['category_' . $last_id]['path'] .= $query->rows[$i]['parent_id'] . '_';
                    }

                    if ($i == ($count_categories - 1)) {
                        $prepared_data['category_' . $last_id]['path'] .= $query->rows[$i]['category_id'];
                    }
                }

                break;

            // Product.
            case 'product':
                $categories_data = array();

                // Get Products from DB.
                $query_products = $this->db->query("SELECT p.product_id, p2c.category_id, p.status FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE p.product_id IN (" . implode(',', $IDs) . ") AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND p.status = '1' GROUP BY p.product_id ORDER BY p.product_id");

                $categories = array();

                // Get categories of products.
                foreach ($query_products->rows as $product) {
                    if (!in_array($product['category_id'], $categories) && $product['category_id']) {
                        $categories[] = $product['category_id'];
                    }
                }

                if (!empty($categories)) {
                    // Get Categories data from DB.
                    $query_categories = $this->db->query("SELECT cp.category_id, c2.parent_id FROM " . DB_PREFIX . "category_path cp LEFT JOIN " . DB_PREFIX . "category c1 ON (cp.category_id = c1.category_id) LEFT JOIN " . DB_PREFIX . "category c2 ON (cp.path_id = c2.category_id) LEFT JOIN " . DB_PREFIX . "category_to_store c2s ON (cp.category_id = c2s.category_id) WHERE cp.category_id IN (" . implode(',', $categories) . ") AND c2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND c1.status = '1' ORDER BY cp.category_id, cp.level");

                    $count_categories = count($query_categories->rows);
                    $last_id = isset($query_categories->rows[0]['category_id']) ? (int)$query_categories->rows[0]['category_id']: 0;
                    $categories_data['category_' . $last_id]['path'] = '';

                    // Get Categories data.
                    for ($i = 0; $i < $count_categories; $i++) {
                        if ($query_categories->rows[$i]['category_id'] != $last_id) {
                            $categories_data['category_' . $last_id]['path'] .= $last_id;

                            $last_id = (int)$query_categories->rows[$i]['category_id'];
                            $categories_data['category_' . $last_id]['path'] = '';
                        }

                        if ($query_categories->rows[$i]['parent_id']) {
                            $categories_data['category_' . $last_id]['path'] .= $query_categories->rows[$i]['parent_id'] . '_';
                        }

                        if ($i == ($count_categories - 1)) {
                            $categories_data['category_' . $last_id]['path'] .= $query_categories->rows[$i]['category_id'];
                        }
                    }

                    // Set Products data.
                    foreach ($query_products->rows as $product) {
                        if (isset($categories_data['category_' . $product['category_id']]['path'])) {
                            $prepared_data['product_' . $product['product_id']]['path'] = $categories_data['category_' . $product['category_id']]['path'];
                        }

                        $prepared_data['product_' . $product['product_id']]['status'] = $product['status'];
                    }
                }

                break;

            // Manufacturer.
            case 'manufacturer':
                // Get Manufacturer from DB.
                $query = $this->db->query("SELECT m.manufacturer_id FROM " . DB_PREFIX . "manufacturer m LEFT JOIN " . DB_PREFIX . "manufacturer_to_store m2s ON (m.manufacturer_id = m2s.manufacturer_id) WHERE m.manufacturer_id IN (" . implode(',', $IDs) . ") AND m2s.store_id = '" . (int)$this->config->get('config_store_id') . "' GROUP BY m.manufacturer_id ORDER BY m.manufacturer_id");

                // Set Manufacturers data.
                foreach ($query->rows as $row) {
                    $prepared_data['manufacturer_' . $row['manufacturer_id']]['manufacturer_id'] = $row['manufacturer_id'];
                }

                break;

            // ocStore Blog Category.
            case 'blog_category':
                $table_name = DB_PREFIX . 'blog_category';

                if ($this->tableExists($table_name)) {
                    // Get Blog Categories from DB.
                    $query = $this->db->query("SELECT bc1.blog_category_id, bc2.parent_id, bc1.status FROM " . DB_PREFIX . "blog_category_path bcp LEFT JOIN " . DB_PREFIX . "blog_category bc1 ON (bcp.blog_category_id = bc1.blog_category_id) LEFT JOIN " . DB_PREFIX . "blog_category bc2 ON (bcp.path_id = bc2.blog_category_id) LEFT JOIN " . DB_PREFIX . "blog_category_to_store bc2s ON (bc1.blog_category_id = bc2s.blog_category_id) WHERE bc1.blog_category_id IN (" . implode(',', $IDs) . ") AND bc2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND bc1.status = '1' ORDER BY bc1.blog_category_id, bcp.level");

                    $count_categories = count($query->rows);
                    $last_id = (int)isset($query->rows[0]['blog_category_id']) ? $query->rows[0]['blog_category_id'] : 0;
                    $prepared_data['blog_category_' . $last_id]['status'] = isset($query->rows[0]['status']) ? $query->rows[0]['status'] : 0;
                    $prepared_data['blog_category_' . $last_id]['path'] = '';

                    // Set Blog Categories data.
                    for ($i = 0; $i < $count_categories; $i++) {
                        if ($query->rows[$i]['blog_category_id'] != $last_id) {
                            $prepared_data['blog_category_' . $last_id]['path'] .= $last_id;

                            $last_id = (int)$query->rows[$i]['blog_category_id'];
                            $prepared_data['blog_category_' . $last_id]['path'] = '';
                            $prepared_data['blog_category_' . $last_id]['status'] = $query->rows[$i]['status'];
                        }

                        if ($query->rows[$i]['parent_id']) {
                            $prepared_data['blog_category_' . $last_id]['path'] .= $query->rows[$i]['parent_id'] . '_';
                        }

                        if ($i == ($count_categories - 1)) {
                            $prepared_data['blog_category_' . $last_id]['path'] .= $query->rows[$i]['blog_category_id'];
                        }
                    }
                }

                break;

            // ocStore Blog Article.
            case 'blog_article':
                $table_name = DB_PREFIX . 'article';

                if ($this->tableExists($table_name)) {
                    $categories_data = array();

                    // Get Blog Articles from DB.
                    $query_articles = $this->db->query("SELECT a.article_id, atbc.blog_category_id, a.status FROM " . DB_PREFIX . "article a LEFT JOIN " . DB_PREFIX . "article_to_blog_category atbc ON (a.article_id = atbc.article_id) LEFT JOIN " . DB_PREFIX . "article_to_store a2s ON (a.article_id = a2s.article_id) WHERE a.article_id IN (" . implode(',', $IDs) . ") AND a2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND a.status = '1' GROUP BY a.article_id ORDER BY a.article_id");

                    $categories = array();

                    // Get categories of articles.
                    foreach ($query_articles->rows as $article) {
                        if (!in_array($article['blog_category_id'], $categories) && $article['blog_category_id']) {
                            $categories[] = $article['blog_category_id'];
                        }
                    }

                    if (!empty($categories)) {
                        // Get Blog Categories data from DB.
                        $query_categories = $this->db->query("SELECT bcp.blog_category_id, bc2.parent_id FROM " . DB_PREFIX . "blog_category_path bcp LEFT JOIN " . DB_PREFIX . "blog_category bc1 ON (bcp.blog_category_id = bc1.blog_category_id) LEFT JOIN " . DB_PREFIX . "blog_category bc2 ON (bcp.path_id = bc2.blog_category_id) LEFT JOIN " . DB_PREFIX . "blog_category_to_store bc2s ON (bc1.blog_category_id = bc2s.blog_category_id) WHERE bcp.blog_category_id IN (" . implode(',', $categories) . ") AND bc2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND bc1.status = '1' ORDER BY bcp.blog_category_id, bcp.level");

                        $count_categories = count($query_categories->rows);
                        $last_id = (int)$query_categories->rows[0]['blog_category_id'];
                        $categories_data['blog_category_' . $last_id]['path'] = '';

                        // Get Blog Categories data.
                        for ($i = 0; $i < $count_categories; $i++) {
                            if ($query_categories->rows[$i]['blog_category_id'] != $last_id) {
                                $categories_data['blog_category_' . $last_id]['path'] .= $last_id;

                                $last_id = (int)$query_categories->rows[$i]['blog_category_id'];
                                $categories_data['blog_category_' . $last_id]['path'] = '';
                            }

                            if ($query_categories->rows[$i]['parent_id']) {
                                $categories_data['blog_category_' . $last_id]['path'] .= $query_categories->rows[$i]['parent_id'] . '_';
                            }

                            if ($i == ($count_categories - 1)) {
                                $categories_data['blog_category_' . $last_id]['path'] .= $query_categories->rows[$i]['blog_category_id'];
                            }
                        }

                        // Set Blog Articles data.
                        foreach ($query_articles->rows as $article) {
                            if (isset($categories_data['blog_category_' . $article['blog_category_id']]['path'])) {
                                $prepared_data['blog_article_' . $article['article_id']]['path'] = $categories_data['blog_category_' . $article['blog_category_id']]['path'];
                            }

                            $prepared_data['blog_article_' . $article['article_id']]['status'] = $article['status'];
                        }
                    }
                }

                break;

            // etc.
            default:
                break;
        }

        return $prepared_data;
    }

    /**
     * A list of chosen tables.
     * 
     * @param string $tables_name
     * 
     * @return bool $exists
     */
    private function tableExists($tables_name) {
        return $this->db->query("SHOW TABLES LIKE '" . $tables_name . "'")->num_rows > 0;
    }
}