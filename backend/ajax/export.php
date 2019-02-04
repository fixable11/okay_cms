<?php

class ExportAjax extends Okay {

    /*����(�������) ��� ����� ��������*/
    private $columns_names = array(
        'category'=>         'Category',
        'brand'=>            'Brand',
        'name'=>             'Product',
        'variant'=>          'Variant',
        'sku'=>              'SKU',
        'price'=>            'Price',
        'compare_price'=>    'Old price',
        'currency'=>         'Currency ID',
        'weight'=>           'Weight',
        'stock'=>            'Stock',
        'units'=>            'Units',
        'visible'=>          'Visible',
        'featured'=>         'Featured',
        'meta_title'=>       'Meta title',
        'meta_keywords'=>    'Meta keywords',
        'meta_description'=> 'Meta description',
        'annotation'=>       'Annotation',
        'description'=>      'Description',
        'images'=>           'Images',
        'url'=>              'URL',

        'output_as_radio'=>  'Output as radio',
        
    );
    
    private $column_delimiter = ';';
    private $subcategory_delimiter = '/';
    private $products_count = 100;
    private $export_files_dir = 'backend/files/export/';
    private $filename = 'export.csv';
    
    public function fetch() {
        if(!$this->managers->access('export')) {
            return false;
        }
        session_write_close();
        unset($_SESSION['lang_id']);
        unset($_SESSION['admin_lang_id']);

        // ������ ������ ������ 1251
        setlocale(LC_ALL, 'ru_RU.1251');
        $this->db->query('SET NAMES cp1251');
        
        // ��������, ������� ������������
        $page = $this->request->get('page');
        if(empty($page) || $page==1) {
            $page = 1;
            // ���� ������ ������� - ������ ������ ���� ��������
            if(is_writable($this->export_files_dir.$this->filename)) {
                unlink($this->export_files_dir.$this->filename);
            }
        }
        
        // ��������� ���� �������� �� ����������
        $f = fopen($this->export_files_dir.$this->filename, 'ab');

        $filter = array('page'=>$page, 'limit'=>$this->products_count);
        $features_filter = array();
        if (($cid = $this->request->get('category_id', 'integer')) && ($category = $this->categories->get_category($cid))) {
            $filter['category_id'] = $features_filter['category_id'] = $category->children;
        }
        if ($brand_id = $this->request->get('brand_id', 'integer')) {
            $filter['brand_id'] = $brand_id;
        }
        
        // ������� � ������ ������� �������� �������
        $features = $this->features->get_features($features_filter);
        foreach($features as $feature) {
            $this->columns_names[$feature->name] = $feature->name;
        }
        
        // ���� ������ ������� - ������� � ������ ������ �������� �������
        if($page == 1) {
            fputcsv($f, $this->columns_names, $this->column_delimiter);
        }

        // ��� ������
        $products = array();
        foreach($this->products->get_products($filter) as $p) {
            $products[$p->id] = (array)$p;
            
            // �������� �������
            $options = $this->features->get_product_options(array('product_id'=>$p->id));
            foreach($options as $option) {
                if(!isset($products[$option->product_id][$option->name])) {
                    $products[$option->product_id][$option->name] = str_replace(',', '.', trim($option->value));
                }
            }
        }
        
        if(empty($products)) {
            return false;
        }
        
        // ��������� �������
        foreach($products as $p_id=>&$product) {
            $categories = array();
            $cats = $this->categories->get_product_categories($p_id);
            foreach($cats as $category) {
                $path = array();
                $cat = $this->categories->get_category((int)$category->category_id);
                if(!empty($cat)) {
                    // ��������� ������������ ���������
                    foreach($cat->path as $p) {
                        $path[] = str_replace($this->subcategory_delimiter, '\\'.$this->subcategory_delimiter, $p->name);
                    }
                    // ��������� ��������� � ������
                    $categories[] = implode('/', $path);
                }
            }
            $product['category'] = implode(',, ', $categories);
        }
        
        // ����������� �������
        $images = $this->products->get_images(array('product_id'=>array_keys($products)));
        foreach($images as $image) {
            // ��������� ����������� � ������ ����� �������
            if(empty($products[$image->product_id]['images'])) {
                $products[$image->product_id]['images'] = $image->filename;
            } else {
                $products[$image->product_id]['images'] .= ', '.$image->filename;
            }
        }
        
        $variants = $this->variants->get_variants(array('product_id'=>array_keys($products)));
        
        foreach($variants as $variant) {
            if(isset($products[$variant->product_id])) {
                $v                    = array();
                $v['variant']         = $variant->name;
                $v['price']           = $variant->price;
                $v['compare_price']   = $variant->compare_price;
                $v['sku']             = $variant->sku;
                $v['stock']           = $variant->stock;
                $v['weight']           = $variant->weight;
                $v['units']           = $variant->units;
                $v['currency']        = $variant->currency_id;
                if($variant->infinity) {
                    $v['stock']           = '';
                }
                $products[$variant->product_id]['variants'][] = $v;
            }
        }
        
        $all_brands = array();
        foreach ($this->brands->get_brands() as $b) {
            $all_brands[$b->id] = $b;
        }
        
        foreach($products as &$product) {
            if ($product['brand_id'] && isset($all_brands[$product['brand_id']])) {
                $product['brand'] = $all_brands[$product['brand_id']]->name;
            }
            $variants = $product['variants'];
            unset($product['variants']);
            
            if(isset($variants)) {
                foreach($variants as $variant) {
                    $result = array();
                    $result =  $product;
                    foreach($variant as $name=>$value) {
                        $result[$name]=$value;
                    }
                    
                    foreach($this->columns_names as $internal_name=>$column_name) {
                        if(isset($result[$internal_name])) {
                            $res[$internal_name] = $result[$internal_name];
                        } else {
                            $res[$internal_name] = '';
                        }
                    }
                    fputcsv($f, $res, $this->column_delimiter);
                }
            }
        }
        
        $total_products = $this->products->count_products($filter);
        fclose($f);
        if($this->products_count*$page < $total_products) {
            return array('end'=>false, 'page'=>$page, 'totalpages'=>$total_products/$this->products_count);
        } else {
            return array('end'=>true, 'page'=>$page, 'totalpages'=>$total_products/$this->products_count);
        }
    }
    
}

$export_ajax = new ExportAjax();
$data = $export_ajax->fetch();
if($data) {
    header("Content-type: application/json; charset=utf-8");
    header("Cache-Control: must-revalidate");
    header("Pragma: no-cache");
    header("Expires: -1");
    $json = json_encode($data);
    print $json;
}