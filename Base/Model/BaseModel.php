<?php

namespace Base\Model;

use \Base;
use \Base\Base\DB;

class BaseModel extends ModelObject
{
    const TABLE = null;

    protected $columns = [];
    protected $trl_langs = [];

    public static function table()
    {
        return static::TABLE;
    }
    
    public function __construct($arg = array())
    {
        if (is_array($arg)) {
            foreach ($arg as $key => $value) {
                $this->properties[$key] = $value;

                $this->selects[$key] = ($value);
            }
        }

        parent::__construct();
        
        if (isset($this->columns['trl_langs']) && isset($this->selects['trl_langs'])) {
            foreach(explode('|lng|', $this->selects['trl_langs']) as $lng) {
                $expl1 = explode('|<=>|', $lng);
                
                if (count($expl1) == 2) {
                    foreach(explode('|trl|', $expl1[1]) as $trl) {
                        $expl2 = explode('|=>|', $trl);
                
                        if (count($expl2) == 2) {
                            $this->trl_langs[$expl1[0]][$expl2[0]] = $expl2[1];
                        }
                    }
                }
            }
        }
    }
    
    public function translate($names)
    {
        if(!empty($names) && isset($this->columns['trl_langs']) && isset($this->columns['lng'])) {
            $is = false;
            
            foreach ($names as $name) {
                foreach (\App\Models\Language::getAllLanguages() as $language) {
                    if ($this->__get($name) && !isset($this->trl_langs[$language->shortname][$name]) && $language->shortname != $this->__get('lng')) {
                        $is = true;
                        
                        $trl = components()->translate($this->__get($name), $this->__get('lng') . '-' . $language->shortname);
                        
                        if($trl)
                            $this->trl_langs[$language->shortname][$name] = $trl;
                    }
                }
            }
            
            if ($is) {
                $trl_langs = [];
            
                foreach ($this->trl_langs as $lng => $values) {
                    foreach ($values as $key => $value) {
                        $trl_langs[$lng][$key] = $key . '|=>|' . $value;
                    }
                }
                
                foreach ($trl_langs as $key => $value) {
                    $trl_langs[$key] = $key . '|<=>|' . implode('|trl|', $value);
                }
            
                $trl_langs = implode('|lng|', $trl_langs);
            
                $this->__set('trl_langs', $trl_langs);
            
                $this->save();
            }
        }
    }
    
    public function _t($name)
    {
        if(isset($this->trl_langs[\App\Models\Language::getLanguage()->shortname]) && 
           isset($this->trl_langs[\App\Models\Language::getLanguage()->shortname][$name])){
            return $this->trl_langs[\App\Models\Language::getLanguage()->shortname][$name];
        }
        
        return $this->__get($name);
    }

    public function getColumns()
    {
        return $this->columns;
    }

    public function isColumnInDn($name)
    {
        if (isset($this->columns[$name])) {
            return true;
        }

        return false;
    }

    public function save()
    {
        if ($this->id) {
            if (isset($this->events['save'])) {
                $func = $this->events['save'];
                $func($this);
            }

            if (isset($this->events['update'])) {
                $func = $this->events['update'];
                $func($this);
            }

            $this->finder()->update($this);

            if (isset($this->events['update-after'])) {
                $func = $this->events['update-after'];
                $func($this);
            }

            if (isset($this->events['save-after'])) {
                $func = $this->events['save-after'];
                $func($this);
            }
        } else {
            if (isset($this->events['save'])) {
                $func = $this->events['save'];
                $func($this);
            }

            if (isset($this->events['insert'])) {
                $func = $this->events['insert'];
                $func($this);
            }

            $this->finder()->insert($this);

            if (isset($this->events['insert-after'])) {
                $func = $this->events['insert-after'];
                $func($this);
            }

            if (isset($this->events['save-after'])) {
                $func = $this->events['save-after'];
                $func($this);
            }
        }
    }

    public function delete()
    {
        if (isset($this->events['delete'])) {
            $func = $this->events['delete'];
            $func($this);
        }

        $this->finder()->delete($this);

        if (isset($this->events['delete-after'])) {
            $func = $this->events['delete-after'];
            $func($this);
        }
    }

    public static function createObjects($rows)
    {
        return \Base\Record\PersistenceFactory::getFactory('\Base\Model\BaseModel')->
            getCollection($rows, static::class);
    }

    public static function createObject($row)
    {
        $class = static::class;

        $obj = new $class($row);

        $obj->build();

        return $obj;
    }

    public static function data($method, $args = [], $emptyFail = false)
    {
        $data = Base::data()->{$method}($args);

        if (empty($data)) {
            if ($emptyFail) {
                abort(404);
            } else {
                return [];
            }
        }

        if (stripos($method, 'dataArray') === 0) {
            return self::createObjects($data);
        }

        if (stripos($method, 'array') === 0) {
            return self::createObject($data);
        }

        return $data;
    }
    
    public function getDateFormat()
    {
        if ($this->created_at || $this->last_at) {
            $dt = date_create($this->last_at ? $this->last_at : $this->created_at);
            
            $time = time()-($dt->getTimestamp());
            
            if ($dt->format('Y-m-d') == date_create()->format('Y-m-d')) {
                return 'Сегодня в ' . $dt->format('H:i');
            } elseif ($dt->format('Y-m-d') == date_create()->modify('-1 day')->format('Y-m-d')) {
                return 'Вчера в ' . $dt->format('H:i');
            } elseif ($dt->format('Y-m-d') == date_create()->modify('-2 day')->format('Y-m-d')) {
                return 'Позавчера в ' . $dt->format('H:i');
            } else {
                return $dt->format('Y.m.d H:i');
            }
        }
    }
    
    public function getLastCreate()
    {
        if ($this->created_at || $this->last_at) {
            $dt = date_create($this->last_at ? $this->last_at : $this->created_at);
            $arr = [];
            
            $time = time()-(date_create($this->last_at ? $this->last_at : $this->created_at)->getTimestamp());
            
            $years = floor($time/31536000);
            
            $months = floor(($time-($years*31536000))/2592000);
            
            $days = floor(($time-($years*31536000)-($months*2592000))/86400);
            
            $hours = floor(($time-($years*31536000)-($months*2592000)-($days*86400))/3600);
            
            $minutes = floor(($time-($years*31536000)-($months*2592000)-($days*86400)-($hours*3600))/60);
            
            $seconds = floor(($time-($years*31536000)-($months*2592000)-($days*86400)-($hours*3600)-($minutes*60)));
            
            if ($years) {
                $arr[] = $years.' года';
            }
            if ($months) {
                $arr[] = $months.' месяца';
            }
            if ($days && empty($arr)) {
                $arr[] = $days.' дня';
            }
            if ($hours && empty($arr)) {
                $arr[] = $hours.' часов';
            }
            if ($minutes && empty($arr)) {
                $arr[] = $minutes.' минут';
            }
            if ($seconds && empty($arr)) {
                $arr[] = $seconds.' секунд';
            }
            
            if ($time < 60) {
                return '<span>Только что</span>';
            } else {
                return '<span>'.implode(', ', $arr).' назад.</span>';
            }
        }
        
        return '';
    }
}
