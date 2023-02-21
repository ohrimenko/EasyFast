<?php

namespace App\Components;

use \Base;
use \Base\Base\DB;
use \Base\Base\Request;
use \Base\Base\Route;

class Data extends \Base\Base\BaseData
{

    public function arrayCurrencyByShort(array $args = [])
    {
        $sql = "SELECT `currencies`.* FROM `currencies` WHERE `currencies`.`short` = ? LIMIT 1";

        $params = [];

        $params[1] = array_get($args, 'short', 0);

        return DB::GetRow($sql, $params);
    }

    public function dataArrayAllCurrencies(array $args = [])
    {
        $sql = "SELECT `currencies`.* FROM `currencies`";

        $params = [];

        return DB::GetAll($sql, $params);
    }

    public function arrayCurrencyById(array $args = [])
    {
        $sql = "SELECT `currencies`.* FROM `currencies` WHERE `currencies`.`id` = ? LIMIT 1";

        $params = [];

        $params[1] = array_get($args, 'id', 0);

        return DB::GetRow($sql, $params);
    }

    public function arrayCurrencyPrevSort(array $args = [])
    {
        $sql = "SELECT `currencies`.* FROM `currencies` WHERE `currencies`.`id` != ? AND `sort` > ? ORDER BY `sort` ASC LIMIT 1";

        $params = [];

        $params[1] = array_get($args, 'not_id', 0);
        $params[2] = array_get($args, 'sort', 0);

        return DB::GetRow($sql, $params);
    }

    public function arrayCurrencyNextSort(array $args = [])
    {
        $sql = "SELECT `currencies`.* FROM `currencies` WHERE `currencies`.`id` != ? AND `sort` < ? ORDER BY `sort` DESC LIMIT 1";

        $params = [];

        $params[1] = array_get($args, 'not_id', 0);
        $params[2] = array_get($args, 'sort', 0);

        return DB::GetRow($sql, $params);
    }

    public function countCurrencyIndex(array $args = [])
    {
        $params = [];
        $i = 0;

        $sql = "SELECT count(*) FROM `currencies`";
        
        $where = [];
        
        if (is_array(array_get($args, 'search'))) {
            foreach (array_get($args, 'search') as $key => $value) {
                $value = trim($value);

                if (empty($value) && $value != '0') {
                    continue;
                }

                switch ($key) {
                    case 'name':
                        $where[++$i] = "`" . preg_replace("#[^a-zA-Z_]#", '', $key) . "` like ?";
                        $params[$i] = '%' . urldecode($value) . '%';

                        break;
                    default:
                        $where[++$i] = "`" . preg_replace("#[^a-zA-Z_]#", '', $key) . "` = ?";
                        $params[$i] = urldecode($value);

                        break;
                }
            }
        }
        
        if (!empty($where)) {
            $sql .= " WHERE " . implode(' and ', $where);
        }

        return DB::GetOne($sql, $params);
    }

    public function dataArrayCurrencyIndex(array $args = [])
    {
        $params = [];
        $i = 0;

        $sql = "SELECT * FROM `currencies`";
        
        $where = [];
        
        if (is_array(array_get($args, 'search'))) {
            foreach (array_get($args, 'search') as $key => $value) {
                $value = trim($value);

                if (empty($value) && $value != '0') {
                    continue;
                }

                switch ($key) {
                    case 'name':
                        $where[++$i] = "`" . preg_replace("#[^a-zA-Z_]#", '', $key) . "` like ?";
                        $params[$i] = '%' . urldecode($value) . '%';

                        break;
                    default:
                        $where[++$i] = "`" . preg_replace("#[^a-zA-Z_]#", '', $key) . "` = ?";
                        $params[$i] = urldecode($value);

                        break;
                }
            }
        }
        
        if (!empty($where)) {
            $sql .= " WHERE " . implode(' and ', $where);
        }

        $sql .= " ORDER BY `" . preg_replace("#[^a-zA-Z_]#", '', array_get($args, 'sort', 'id')) . "` " . (stripos(array_get($args, 'sort', 'id'), '-') === 0 ? 'desc' : 'asc');

        $sql .= " LIMIT ?, ?";

        $params[++$i] = array_get($args, 'offset', 0);
        $params[++$i] = array_get($args, 'limit', 22);

        return DB::GetAll($sql, $params);
    }

    public function arrayProductByPidAreaId(array $args = [])
    {
        $sql = "SELECT `products`.* FROM `products` WHERE `products`.`pid` = ? AND `products`.`area_id` = ? LIMIT 1";

        $params = [];

        $params[1] = array_get($args, 'pid', 0);
        $params[2] = array_get($args, 'area_id', 0);

        return DB::GetRow($sql, $params);
    }

    public function dataArrayUrlByIds(array $args = [])
    {
        $sql = "SELECT `urls`.* FROM `urls`";

        $params = [];
        $ins = [];
        $i = 0;
        
        foreach (array_get($args, 'ids', []) as $id) {
            $params[++$i] = $id;
            $ins[] = '?';
        }
        
        if (empty($ins)) {
            return [];
        }
        
        $sql .= " WHERE `id` IN (" . implode(',', $ins) . ")";

        return DB::GetAll($sql, $params);
    }

    public function dataArrayProductByIds(array $args = [])
    {
        $sql = "SELECT `products`.* FROM `products`";

        $params = [];
        $ins = [];
        $i = 0;
        
        foreach (array_get($args, 'ids', []) as $id) {
            $params[++$i] = $id;
            $ins[] = '?';
        }
        
        if (empty($ins)) {
            return [];
        }
        
        $sql .= " WHERE `id` IN (" . implode(',', $ins) . ")";

        return DB::GetAll($sql, $params);
    }

    public function dataArrayFieldsByTypeProductId(array $args = [])
    {
        $sql = "SELECT `fields`.* FROM `fields` where `product_id` = ? LIMIT 0, 300";

        $params = [];
        
        $params[1] = array_get($args, 'product_id', 0);

        return DB::GetAll($sql, $params);
    }

    public function dataArrayProductsByUrlId(array $args = [])
    {
        $sql = "SELECT `products`.* FROM `products` where `url_id` = ? LIMIT 0, 300";

        $params = [];
        
        $params[1] = array_get($args, 'url_id', 0);

        return DB::GetAll($sql, $params);
    }

    public function dataArrayProductsByTypeAreaId(array $args = [])
    {
        $sql = "SELECT `products`.* FROM `products` where `area_id` = ? LIMIT 0, 300";

        $params = [];
        
        $params[1] = array_get($args, 'area_id', 0);

        return DB::GetAll($sql, $params);
    }

    public function dataArrayUrlsByTypeAreaId(array $args = [])
    {
        $sql = "SELECT `urls`.* FROM `urls` where `area_id` = ? LIMIT 0, 300";

        $params = [];
        
        $params[1] = array_get($args, 'area_id', 0);

        return DB::GetAll($sql, $params);
    }

    public function dataArrayFieldsByTypeFieldId(array $args = [])
    {
        $sql = "SELECT `fields`.* FROM `fields` where `typefield_id` = ? LIMIT 0, 300";

        $params = [];
        
        $params[1] = array_get($args, 'typefield_id', 0);

        return DB::GetAll($sql, $params);
    }

    public function dataArrayFieldsByProduct(array $args = [])
    {
        $sql = "SELECT `fields`.* FROM `fields` where `product_id` = ?";

        $params = [];
        
        $params[1] = array_get($args, 'product_id', 0);

        return DB::GetAll($sql, $params);
    }

    public function dataArrayTypeFieldsByFields(array $args = [])
    {
        $sql = "SELECT `typefields`.* FROM `typefields`";

        $params = [];
        $ins = [];
        $i = 0;
        
        foreach (array_get($args, 'fields', []) as $field) {
            $params[++$i] = $field->typefield_id;
            $ins[] = '?';
        }
        
        if (empty($ins)) {
            return [];
        }
        
        $sql .= " WHERE `id` IN (" . implode(',', $ins) . ")";

        return DB::GetAll($sql, $params);
    }

    public function dataArrayProductsByFields(array $args = [])
    {
        $sql = "SELECT `products`.* FROM `products`";

        $params = [];
        $ins = [];
        $i = 0;
        
        foreach (array_get($args, 'fields', []) as $field) {
            $params[++$i] = $field->product_id;
            $ins[] = '?';
        }
        
        if (empty($ins)) {
            return [];
        }
        
        $sql .= " WHERE `id` IN (" . implode(',', $ins) . ")";

        return DB::GetAll($sql, $params);
    }

    public function dataArrayAllTypeFields(array $args = [])
    {
        $sql = "SELECT `typefields`.* FROM `typefields`";

        $params = [];

        return DB::GetAll($sql, $params);
    }
    
    public function arrayFieldById(array $args = [])
    {
        $sql = "SELECT `fields`.* FROM `fields` WHERE `fields`.`id` = ? LIMIT 1";

        $params = [];

        $params[1] = array_get($args, 'id', 0);

        return DB::GetRow($sql, $params);
    }

    public function arrayFieldPrevSort(array $args = [])
    {
        $sql = "SELECT `fields`.* FROM `fields` WHERE `fields`.`id` != ? AND `sort` > ? ORDER BY `sort` ASC LIMIT 1";

        $params = [];

        $params[1] = array_get($args, 'not_id', 0);
        $params[2] = array_get($args, 'sort', 0);

        return DB::GetRow($sql, $params);
    }

    public function arrayFieldNextSort(array $args = [])
    {
        $sql = "SELECT `fields`.* FROM `fields` WHERE `fields`.`id` != ? AND `sort` < ? ORDER BY `sort` DESC LIMIT 1";

        $params = [];

        $params[1] = array_get($args, 'not_id', 0);
        $params[2] = array_get($args, 'sort', 0);

        return DB::GetRow($sql, $params);
    }

    public function countFieldIndex(array $args = [])
    {
        $params = [];
        $i = 0;

        $sql = "SELECT count(*) FROM `fields`";
        
        $where = [];
        
        if (is_array(array_get($args, 'search'))) {
            foreach (array_get($args, 'search') as $key => $value) {
                $value = trim($value);

                if (empty($value) && $value != '0') {
                    continue;
                }

                switch ($key) {
                    case 'value':
                        $where[++$i] = "`" . preg_replace("#[^a-zA-Z_]#", '', $key) . "` like ?";
                        $params[$i] = '%' . urldecode($value) . '%';

                        break;
                    case 'content':
                        $where[++$i] = "`" . preg_replace("#[^a-zA-Z_]#", '', $key) . "` like ?";
                        $params[$i] = '%' . urldecode($value) . '%';

                        break;
                    case 'valuecontent':
                        $params[++$i] = '%' . urldecode($value) . '%';
                        $params[++$i] = '%' . urldecode($value) . '%';
                        $where[$i] = "(`value` like ? OR `content` like ?)";

                        break;
                    case 'name':
                        $where[++$i] = "`" . preg_replace("#[^a-zA-Z_]#", '', $key) . "` like ?";
                        $params[$i] = '%' . urldecode($value) . '%';

                        break;
                    default:
                        $where[++$i] = "`" . preg_replace("#[^a-zA-Z_]#", '', $key) . "` = ?";
                        $params[$i] = urldecode($value);

                        break;
                }
            }
        }
        
        if (!empty($where)) {
            $sql .= " WHERE " . implode(' and ', $where);
        }

        return DB::GetOne($sql, $params);
    }

    public function dataArrayFieldIndex(array $args = [])
    {
        $params = [];
        $i = 0;

        $sql = "SELECT * FROM `fields`";
        
        $where = [];
        
        if (is_array(array_get($args, 'search'))) {
            foreach (array_get($args, 'search') as $key => $value) {
                $value = trim($value);

                if (empty($value) && $value != '0') {
                    continue;
                }

                switch ($key) {
                    case 'value':
                        $where[++$i] = "`" . preg_replace("#[^a-zA-Z_]#", '', $key) . "` like ?";
                        $params[$i] = '%' . urldecode($value) . '%';

                        break;
                    case 'content':
                        $where[++$i] = "`" . preg_replace("#[^a-zA-Z_]#", '', $key) . "` like ?";
                        $params[$i] = '%' . urldecode($value) . '%';

                        break;
                    case 'valuecontent':
                        $params[++$i] = '%' . urldecode($value) . '%';
                        $params[++$i] = '%' . urldecode($value) . '%';
                        $where[$i] = "(`value` like ? OR `content` like ?)";

                        break;
                    case 'name':
                        $where[++$i] = "`" . preg_replace("#[^a-zA-Z_]#", '', $key) . "` like ?";
                        $params[$i] = '%' . urldecode($value) . '%';

                        break;
                    default:
                        $where[++$i] = "`" . preg_replace("#[^a-zA-Z_]#", '', $key) . "` = ?";
                        $params[$i] = urldecode($value);

                        break;
                }
            }
        }
        
        if (!empty($where)) {
            $sql .= " WHERE " . implode(' and ', $where);
        }

        $sql .= " ORDER BY `" . preg_replace("#[^a-zA-Z_]#", '', array_get($args, 'sort', 'id')) . "` " . (stripos(array_get($args, 'sort', 'id'), '-') === 0 ? 'desc' : 'asc');

        $sql .= " LIMIT ?, ?";

        $params[++$i] = array_get($args, 'offset', 0);
        $params[++$i] = array_get($args, 'limit', 22);

        return DB::GetAll($sql, $params);
    }
    
    public function arrayTypeFieldById(array $args = [])
    {
        $sql = "SELECT `typefields`.* FROM `typefields` WHERE `typefields`.`id` = ? LIMIT 1";

        $params = [];

        $params[1] = array_get($args, 'id', 0);

        return DB::GetRow($sql, $params);
    }
    
    public function arrayTypeFieldByName(array $args = [])
    {
        $sql = "SELECT `typefields`.* FROM `typefields` WHERE `typefields`.`name` = ? LIMIT 1";

        $params = [];

        $params[1] = array_get($args, 'name', 0);

        return DB::GetRow($sql, $params);
    }

    public function arrayTypeFieldPrevSort(array $args = [])
    {
        $sql = "SELECT `typefields`.* FROM `typefields` WHERE `typefields`.`id` != ? AND `sort` > ? ORDER BY `sort` ASC LIMIT 1";

        $params = [];

        $params[1] = array_get($args, 'not_id', 0);
        $params[2] = array_get($args, 'sort', 0);

        return DB::GetRow($sql, $params);
    }

    public function arrayTypeFieldNextSort(array $args = [])
    {
        $sql = "SELECT `typefields`.* FROM `typefields` WHERE `typefields`.`id` != ? AND `sort` < ? ORDER BY `sort` DESC LIMIT 1";

        $params = [];

        $params[1] = array_get($args, 'not_id', 0);
        $params[2] = array_get($args, 'sort', 0);

        return DB::GetRow($sql, $params);
    }

    public function countTypeFieldIndex(array $args = [])
    {
        $params = [];
        $i = 0;

        $sql = "SELECT count(*) FROM `typefields`";
        
        $where = [];
        
        if (is_array(array_get($args, 'search'))) {
            foreach (array_get($args, 'search') as $key => $value) {
                $value = trim($value);

                if (empty($value) && $value != '0') {
                    continue;
                }

                switch ($key) {
                    case 'name':
                        $where[++$i] = "`" . preg_replace("#[^a-zA-Z_]#", '', $key) . "` like ?";
                        $params[$i] = '%' . urldecode($value) . '%';

                        break;
                    default:
                        $where[++$i] = "`" . preg_replace("#[^a-zA-Z_]#", '', $key) . "` = ?";
                        $params[$i] = urldecode($value);

                        break;
                }
            }
        }
        
        if (!empty($where)) {
            $sql .= " WHERE " . implode(' and ', $where);
        }

        return DB::GetOne($sql, $params);
    }

    public function dataArrayTypeFieldIndex(array $args = [])
    {
        $params = [];
        $i = 0;

        $sql = "SELECT * FROM `typefields`";
        
        $where = [];
        
        if (is_array(array_get($args, 'search'))) {
            foreach (array_get($args, 'search') as $key => $value) {
                $value = trim($value);

                if (empty($value) && $value != '0') {
                    continue;
                }

                switch ($key) {
                    case 'name':
                        $where[++$i] = "`" . preg_replace("#[^a-zA-Z_]#", '', $key) . "` like ?";
                        $params[$i] = '%' . urldecode($value) . '%';

                        break;
                    default:
                        $where[++$i] = "`" . preg_replace("#[^a-zA-Z_]#", '', $key) . "` = ?";
                        $params[$i] = urldecode($value);

                        break;
                }
            }
        }
        
        if (!empty($where)) {
            $sql .= " WHERE " . implode(' and ', $where);
        }

        $sql .= " ORDER BY `" . preg_replace("#[^a-zA-Z_]#", '', array_get($args, 'sort', 'id')) . "` " . (stripos(array_get($args, 'sort', 'id'), '-') === 0 ? 'desc' : 'asc');

        $sql .= " LIMIT ?, ?";

        $params[++$i] = array_get($args, 'offset', 0);
        $params[++$i] = array_get($args, 'limit', 22);

        return DB::GetAll($sql, $params);
    }

    public function dataArrayUrlsByProducts(array $args = [])
    {
        $sql = "SELECT `urls`.* FROM `urls`";

        $params = [];
        $ins = [];
        $i = 0;
        
        foreach (array_get($args, 'products', []) as $url) {
            $params[++$i] = $url->url_id;
            $ins[] = '?';
        }
        
        if (empty($ins)) {
            return [];
        }
        
        $sql .= " WHERE `id` IN (" . implode(',', $ins) . ")";

        return DB::GetAll($sql, $params);
    }

    public function dataArrayFieldsByProducts(array $args = [])
    {
        $sql = "SELECT `fields`.* FROM `fields`";

        $params = [];
        $ins = [];
        $i = 0;
        
        foreach (array_get($args, 'products', []) as $url) {
            $params[++$i] = $url->id;
            $ins[] = '?';
        }
        
        if (empty($ins)) {
            return [];
        }
        
        $sql .= " WHERE `product_id` IN (" . implode(',', $ins) . ")";

        return DB::GetAll($sql, $params);
    }

    public function dataArrayAreasByProducts(array $args = [])
    {
        $sql = "SELECT `areas`.* FROM `areas`";

        $params = [];
        $ins = [];
        $i = 0;
        
        foreach (array_get($args, 'products', []) as $url) {
            $params[++$i] = $url->area_id;
            $ins[] = '?';
        }
        
        if (empty($ins)) {
            return [];
        }
        
        $sql .= " WHERE `id` IN (" . implode(',', $ins) . ")";

        return DB::GetAll($sql, $params);
    }

    public function dataArrayCurrenciesByProducts(array $args = [])
    {
        $sql = "SELECT `currencies`.* FROM `currencies`";

        $params = [];
        $ins = [];
        $i = 0;
        
        foreach (array_get($args, 'products', []) as $url) {
            $params[++$i] = $url->currency_id;
            $ins[] = '?';
        }
        
        if (empty($ins)) {
            return [];
        }
        
        $sql .= " WHERE `id` IN (" . implode(',', $ins) . ")";

        return DB::GetAll($sql, $params);
    }

    public function dataArrayAreasByUrls(array $args = [])
    {
        $sql = "SELECT `areas`.* FROM `areas`";

        $params = [];
        $ins = [];
        $i = 0;
        
        foreach (array_get($args, 'urls', []) as $url) {
            $params[++$i] = $url->area_id;
            $ins[] = '?';
        }
        
        if (empty($ins)) {
            return [];
        }
        
        $sql .= " WHERE `id` IN (" . implode(',', $ins) . ")";

        return DB::GetAll($sql, $params);
    }

    public function arrayUrlPrevSort(array $args = [])
    {
        $sql = "SELECT `urls`.* FROM `urls` WHERE `urls`.`id` != ? AND `sort` > ? ORDER BY `sort` ASC LIMIT 1";

        $params = [];

        $params[1] = array_get($args, 'not_id', 0);
        $params[2] = array_get($args, 'sort', 0);

        return DB::GetRow($sql, $params);
    }

    public function arrayUrlNextSort(array $args = [])
    {
        $sql = "SELECT `urls`.* FROM `urls` WHERE `urls`.`id` != ? AND `sort` < ? ORDER BY `sort` DESC LIMIT 1";

        $params = [];

        $params[1] = array_get($args, 'not_id', 0);
        $params[2] = array_get($args, 'sort', 0);

        return DB::GetRow($sql, $params);
    }

    public function arrayUrlById(array $args = [])
    {
        $sql = "SELECT `urls`.* FROM `urls` WHERE `urls`.`id` = ? LIMIT 1";

        $params = [];

        $params[1] = array_get($args, 'id', 0);

        return DB::GetRow($sql, $params);
    }

    public function countUrlIndex(array $args = [])
    {
        $params = [];
        $i = 0;

        $sql = "SELECT count(*) FROM `urls`";
        
        $where = [];
        
        if (is_array(array_get($args, 'search'))) {
            foreach (array_get($args, 'search') as $key => $value) {
                $value = trim($value);

                if (empty($value) && $value != '0') {
                    continue;
                }

                switch ($key) {
                    case 'url':
                        $where[++$i] = "`" . preg_replace("#[^a-zA-Z_]#", '', $key) . "` like ?";
                        $params[$i] = '%' . urldecode($value) . '%';

                        break;
                    default:
                        $where[++$i] = "`" . preg_replace("#[^a-zA-Z_]#", '', $key) . "` = ?";
                        $params[$i] = urldecode($value);

                        break;
                }
            }
        }
        
        if (!empty($where)) {
            $sql .= " WHERE " . implode(' and ', $where);
        }

        return DB::GetOne($sql, $params);
    }

    public function dataArrayUrlIndex(array $args = [])
    {
        $params = [];
        $i = 0;

        $sql = "SELECT `urls`.* FROM `urls`";
        
        $where = [];
        
        if (is_array(array_get($args, 'search'))) {
            foreach (array_get($args, 'search') as $key => $value) {
                $value = trim($value);

                if (empty($value) && $value != '0') {
                    continue;
                }

                switch ($key) {
                    case 'url':
                        $where[++$i] = "`" . preg_replace("#[^a-zA-Z_]#", '', $key) . "` like ?";
                        $params[$i] = '%' . urldecode($value) . '%';

                        break;
                    default:
                        $where[++$i] = "`" . preg_replace("#[^a-zA-Z_]#", '', $key) . "` = ?";
                        $params[$i] = urldecode($value);

                        break;
                }
            }
        }
        
        if (!empty($where)) {
            $sql .= " WHERE " . implode(' and ', $where);
        }

        $sql .= " ORDER BY `" . preg_replace("#[^a-zA-Z_]#", '', array_get($args, 'sort', 'id')) . "` " . (stripos(array_get($args, 'sort', 'id'), '-') === 0 ? 'desc' : 'asc');

        $sql .= " LIMIT ?, ?";

        $params[++$i] = array_get($args, 'offset', 0);
        $params[++$i] = array_get($args, 'limit', 22);

        return DB::GetAll($sql, $params);
    }

    public function dataArrayAllAreas(array $args = [])
    {
        $sql = "SELECT `areas`.* FROM `areas`";

        $params = [];

        return DB::GetAll($sql, $params);
    }

    public function arrayAreaById(array $args = [])
    {
        $sql = "SELECT `areas`.* FROM `areas` WHERE `areas`.`id` = ? LIMIT 1";

        $params = [];

        $params[1] = array_get($args, 'id', 0);

        return DB::GetRow($sql, $params);
    }

    public function arrayAreaPrevSort(array $args = [])
    {
        $sql = "SELECT `areas`.* FROM `areas` WHERE `areas`.`id` != ? AND `sort` > ? ORDER BY `sort` ASC LIMIT 1";

        $params = [];

        $params[1] = array_get($args, 'not_id', 0);
        $params[2] = array_get($args, 'sort', 0);

        return DB::GetRow($sql, $params);
    }

    public function arrayAreaNextSort(array $args = [])
    {
        $sql = "SELECT `areas`.* FROM `areas` WHERE `areas`.`id` != ? AND `sort` < ? ORDER BY `sort` DESC LIMIT 1";

        $params = [];

        $params[1] = array_get($args, 'not_id', 0);
        $params[2] = array_get($args, 'sort', 0);

        return DB::GetRow($sql, $params);
    }

    public function countAreaIndex(array $args = [])
    {
        $params = [];
        $i = 0;

        $sql = "SELECT count(*) FROM `areas`";
        
        $where = [];
        
        if (is_array(array_get($args, 'search'))) {
            foreach (array_get($args, 'search') as $key => $value) {
                $value = trim($value);

                if (empty($value) && $value != '0') {
                    continue;
                }

                switch ($key) {
                    case 'name':
                        $where[++$i] = "`" . preg_replace("#[^a-zA-Z_]#", '', $key) . "` like ?";
                        $params[$i] = '%' . urldecode($value) . '%';

                        break;
                    default:
                        $where[++$i] = "`" . preg_replace("#[^a-zA-Z_]#", '', $key) . "` = ?";
                        $params[$i] = urldecode($value);

                        break;
                }
            }
        }
        
        if (!empty($where)) {
            $sql .= " WHERE " . implode(' and ', $where);
        }

        return DB::GetOne($sql, $params);
    }

    public function dataArrayAreaIndex(array $args = [])
    {
        $params = [];
        $i = 0;

        $sql = "SELECT * FROM `areas`";
        
        $where = [];
        
        if (is_array(array_get($args, 'search'))) {
            foreach (array_get($args, 'search') as $key => $value) {
                $value = trim($value);

                if (empty($value) && $value != '0') {
                    continue;
                }

                switch ($key) {
                    case 'name':
                        $where[++$i] = "`" . preg_replace("#[^a-zA-Z_]#", '', $key) . "` like ?";
                        $params[$i] = '%' . urldecode($value) . '%';

                        break;
                    default:
                        $where[++$i] = "`" . preg_replace("#[^a-zA-Z_]#", '', $key) . "` = ?";
                        $params[$i] = urldecode($value);

                        break;
                }
            }
        }
        
        if (!empty($where)) {
            $sql .= " WHERE " . implode(' and ', $where);
        }

        $sql .= " ORDER BY `" . preg_replace("#[^a-zA-Z_]#", '', array_get($args, 'sort', 'id')) . "` " . (stripos(array_get($args, 'sort', 'id'), '-') === 0 ? 'desc' : 'asc');

        $sql .= " LIMIT ?, ?";

        $params[++$i] = array_get($args, 'offset', 0);
        $params[++$i] = array_get($args, 'limit', 22);

        return DB::GetAll($sql, $params);
    }

    public function arrayProductPrevSort(array $args = [])
    {
        $sql = "SELECT `products`.* FROM `products` WHERE `products`.`id` != ? AND `sort` > ? ORDER BY `sort` ASC LIMIT 1";

        $params = [];

        $params[1] = array_get($args, 'not_id', 0);
        $params[2] = array_get($args, 'sort', 0);

        return DB::GetRow($sql, $params);
    }

    public function arrayProductNextSort(array $args = [])
    {
        $sql = "SELECT `products`.* FROM `products` WHERE `products`.`id` != ? AND `sort` < ? ORDER BY `sort` DESC LIMIT 1";

        $params = [];

        $params[1] = array_get($args, 'not_id', 0);
        $params[2] = array_get($args, 'sort', 0);

        return DB::GetRow($sql, $params);
    }

    public function arrayProductById(array $args = [])
    {
        $sql = "SELECT `products`.* FROM `products` WHERE `products`.`id` = ? LIMIT 1";

        $params = [];

        $params[1] = array_get($args, 'id', 0);

        return DB::GetRow($sql, $params);
    }

    public function countProductIndex(array $args = [])
    {
        $params = [];
        $i = 0;

        $sql = "SELECT count(*) FROM `products`";
        
        $where = [];
        
        if (is_array(array_get($args, 'search'))) {
            foreach (array_get($args, 'search') as $key => $value) {
                $value = trim($value);

                if (empty($value) && $value != '0') {
                    continue;
                }

                switch ($key) {
                    case 'name':
                        $where[++$i] = "`" . preg_replace("#[^a-zA-Z_]#", '', $key) . "` like ?";
                        $params[$i] = '%' . urldecode($value) . '%';

                        break;
                    default:
                        $where[++$i] = "`" . preg_replace("#[^a-zA-Z_]#", '', $key) . "` = ?";
                        $params[$i] = urldecode($value);

                        break;
                }
            }
        }
        
        if (!empty($where)) {
            $sql .= " WHERE " . implode(' and ', $where);
        }

        return DB::GetOne($sql, $params);
    }

    public function dataArrayProductIndex(array $args = [])
    {
        $params = [];
        $i = 0;

        $sql = "SELECT * FROM `products`";
        
        $where = [];
        
        if (is_array(array_get($args, 'search'))) {
            foreach (array_get($args, 'search') as $key => $value) {
                $value = trim($value);

                if (empty($value) && $value != '0') {
                    continue;
                }

                switch ($key) {
                    case 'name':
                        $where[++$i] = "(`pid` = ? OR `" . preg_replace("#[^a-zA-Z_]#", '', $key) . "` like ?)";
                        $params[$i] = urldecode($value);
                        $params[++$i] = '%' . urldecode($value) . '%';

                        break;
                    default:
                        $where[++$i] = "`" . preg_replace("#[^a-zA-Z_]#", '', $key) . "` = ?";
                        $params[$i] = urldecode($value);

                        break;
                }
            }
        }
        
        if (!empty($where)) {
            $sql .= " WHERE " . implode(' and ', $where);
        }

        $sql .= " ORDER BY `" . preg_replace("#[^a-zA-Z_]#", '', array_get($args, 'sort', 'id')) . "` " . (stripos(array_get($args, 'sort', 'id'), '-') === 0 ? 'desc' : 'asc');

        $sql .= " LIMIT ?, ?";

        $params[++$i] = array_get($args, 'offset', 0);
        $params[++$i] = array_get($args, 'limit', 22);

        return DB::GetAll($sql, $params);
    }

    public function dataArrayAllCron(array $args = [])
    {
        $sql = "SELECT * FROM `cron` WHERE 1";

        $params = [];

        return DB::GetAll($sql, $params);
    }

    public function insertCron(array $args = [])
    {
        $sql = "INSERT INTO `cron`(`name`, `launch_at`) VALUES (?,?)";

        $params = [];

        $params[1] = array_get($args, 'name');
        $params[2] = array_get($args, 'launch_at');

        return DB::Execute($sql, $params);
    }

    public function updateCategoryUserNm(array $args = [])
    {
        $sql = "UPDATE `category_user` SET `nm` = ? WHERE `category_id` = ?";

        $params = [];

        $params[1] = array_get($args, 'nm', 0);
        $params[2] = array_get($args, 'category_id', 0);

        return DB::Execute($sql, $params);
    }

    public function updateCategoryProjectNm(array $args = [])
    {
        $sql = "UPDATE `category_project` SET `nm` = ? WHERE `category_id` = ?";

        $params = [];

        $params[1] = array_get($args, 'nm', 0);
        $params[2] = array_get($args, 'category_id', 0);

        return DB::Execute($sql, $params);
    }

    public function updateCron(array $args = [])
    {
        $sql = "UPDATE `cron` SET `launch_at`=? WHERE `id` = ?";

        $params = [];

        $params[1] = array_get($args, 'launch_at');
        $params[2] = array_get($args, 'id');

        return DB::Execute($sql, $params);
    }

    public function arrayStatByRout(array $args = [])
    {
        $sql = "SELECT * FROM `stats` WHERE `rout` = ? LIMIT 1";

        $params = [];

        $params[1] = array_get($args, 'rout', 0);

        return DB::GetRow($sql, $params);
    }

    public function arrayLanguageByShortname(array $args = [])
    {
        $sql = "SELECT * FROM `languages` WHERE `shortname` = ? and `languages`.`is_active` ORDER BY `name`";

        $params = [];

        $params[1] = array_get($args, 'shortname', 0);

        return DB::GetRow($sql, $params);
    }

    public function dataArrayAllLanguages(array $args = [])
    {
        $sql = "SELECT * FROM `languages` WHERE `languages`.`is_active`";

        $params = [];

        return DB::GetAll($sql, $params);
    }

    public function arrayLanguageById(array $args = [])
    {
        $sql = "SELECT * FROM `languages` WHERE `id` = ? and `languages`.`is_active` ORDER BY `name`";

        $params = [];

        $params[1] = array_get($args, 'id', 0);

        return DB::GetRow($sql, $params);
    }
}
