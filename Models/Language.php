<?php

namespace App\Models;

use \Base;
use \Base\Model\BaseModel;
use \Base\Base\Request;
use \Base\Base\DB;

class Language extends BaseModel
{
    protected static $languages = null;
    protected static $language = null;
    
    const TABLE = 'languages';
    
    protected $columns = array(
        'id' => 'int(10) unsigned',
        'name' => 'varchar(250)',
        'name_ru' => 'varchar(250)',
        'name_en' => 'varchar(250)',
        'shortname' => 'varchar(50)',
        'created_at' => 'timestamp',
        'updated_at' => 'timestamp',
        'is_active' => 'varchar(50)',
    );

    public function build()
    {
        
    }
    
    public static function getAllLanguages ()
    {
        if(is_null(self::$languages)){
            self::$languages = Base::dataModel('Language','dataArrayAllLanguages');
        }
        
        return self::$languages;
    }
    
    public static function getLanguage ()
    {
        if(is_null(self::$language)){
            foreach(self::getAllLanguages() as $language) {
                if($language->id == $_SESSION['setting']['language_id']){
                    self::$language = $language;
                }
            }
            
            if(is_null(self::$language)){
                self::$language = self::$languages[0];
            }
        }
        
        return self::$language;
    }
}
