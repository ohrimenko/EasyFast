<?php

namespace App\Components;

use \Base;
use \morphos;
use \App\Components\Cron;
use \Base\Base\BaseObj;
use \Base\Base\Route;
use \Base\Base\Request;
use \GeoIp2\Database\Reader;

class Components
{
    const COUNT_DO_SAVE = 5000;

    public $count_save = 0;

    public $urls = [];

    public $country_user = null;
    public $country_code_user = null;

    private static $iKeysYandexTranslate = 0;

    private static $instance = null;
    
    public $google_token = false;

    private function __construct()
    {
    }

    public function ctrlRoutAttr(&$as, &$args, &$attrs, $item = null)
    {
    }

    public function getConfigDomain($domain = null)
    {
        if ($domain) {
            if (config('domains.' . $domain)) {
                return config('domains.' . $domain);
            }
        }
        
        return config('domains.default');
    }

    public function getDomainByRegion($item)
    {return null;
        if ($item && isset($item->region_id) && $item->region_id > 0) {
            if (!Base::issetData('region')) {
                data()->region = Base::dataModel('Region', 'arrayRegionById', ['id' => $item->region_id]);
            }

            return $this->getConfigDomain('region_' . $item->region_id);
        }
        
        return null;
    }

    public function getDomainByCountry($item)
    {return null;
        if ($item && isset($item->country_id) && $item->country_id > 0) {
            if (!Base::issetData('region')) {
                data()->region = Base::dataModel('Country', 'arrayCountryById', ['id' => $item->country_id]);
            }

            return $this->getConfigDomain('country_' . $item->country_id);
        }
        
        return null;
    }

    public function prepareLink($link, $rout)
    {
        return config("site_dir") . $link;
    }

    public function repareLink($link, $is_link = true)
    {
        return config("site_dir") . $link;
    }

    public function prepareRouteAs($as, $args = [], $item = null)
    {
        return $as;
    }

    public function getDomain($rout = null, $item = null)
    {
        return getDomain();
    }

    public function getItemPage()
    {
        return (object) [];
    }

    public function getCoordinate($address)
    {
        $ch = curl_init();
        
        $url = 'https://geocode-maps.yandex.ru/1.x/?' . http_build_query(array(
            'apikey'     => config('YandexMapApiKey'),                           // ваш api key
            'geocode' => $address, // адрес
            'format'  => 'json',                          // формат ответа
            'results' => 1,                               // количество выводимых результатов
        ), '', '&');
        
        $options = [
            CURLOPT_URL => $url, 
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_TIMEOUT => 20,
        ];

        curl_setopt_array($ch, $options);

        curl_exec($ch);

        //$chinfo = curl_getinfo($ch);

        $chdata = curl_multi_getcontent($ch);
        
        curl_close($ch);

        if ($chdata) {
            $json = json_decode($chdata, 1);
            
            if ($json && isset($json['response']) && 
                isset($json['response']['GeoObjectCollection']) && 
                isset($json['response']['GeoObjectCollection']['featureMember']) && 
                isset($json['response']['GeoObjectCollection']['featureMember']['0']) && 
                isset($json['response']['GeoObjectCollection']['featureMember']['0']['GeoObject']) && 
                isset($json['response']['GeoObjectCollection']['featureMember']['0']['GeoObject']['Point']) && 
                isset($json['response']['GeoObjectCollection']['featureMember']['0']['GeoObject']['Point']['pos'])) {
                
                $expl = explode(' ', $json['response']['GeoObjectCollection']['featureMember']['0']['GeoObject']['Point']['pos']);
                
                return [
                    'lng' => $expl[0],
                    'lat' => $expl[1]
                ];
            }
        }
        
        return null;
    }

    public function getGoogleToken()
    {
        if ($this->google_token === false) {
            $this->google_token = null;
            
            if (file_exists(config('storage_dir') . '/google/token.txt')) {
                $data = unserialize(file_get_contents(config('storage_dir') . '/google/token.txt'));
                
                if($data && isset($data['access_token']) && isset($data['expires_time'])){
                    if ($data['expires_time'] > time()) {
                        $this->google_token = $data['access_token'];
                    } elseif(isset($data['refresh_token'])) {
                        $params = array(
                            'client_id' => config('OAuthGoogleId'),
                            'client_secret' => config('OAuthGoogleSecretKey'),
                            'refresh_token' => $data['refresh_token'],
                            'grant_type' => 'refresh_token',
                        );

                        $url = 'https://accounts.google.com/o/oauth2/token';

                        $curl = curl_init();
                        curl_setopt($curl, CURLOPT_URL, $url);
                        curl_setopt($curl, CURLOPT_POST, 1);
                        curl_setopt($curl, CURLOPT_POSTFIELDS, urldecode(http_build_query($params)));
                        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                        $result = curl_exec($curl);
                        curl_close($curl);

                        $tokenInfo = json_decode($result, true);
                        
                        if (isset($tokenInfo['access_token'])) {
                            $tokenInfo['refresh_token'] = $data['refresh_token'];
                            
                            $this->setGoogleToken($tokenInfo);
                            
                            $this->google_token = $data['access_token'];
                        }
                    }
                }
            }
        }
        
        return $this->google_token;
    }

    public function setGoogleToken($token)
    {
        if ($token && isset($token['access_token']) && isset($token['expires_in'])) {
            $token['expires_time'] = time() + $token['expires_in'];
            
            file_put_contents(config('storage_dir') . '/google/token.txt', serialize($token));
        } else {
            print_r($token);
        }
    }

    public function morphos()
    {
        // Inflect russian names:
        morphos\Russian\inflectName('Иванов Петр', 'родительный'); // 'Иванова Петра'

        // Inflect geographical names:
        morphos\Russian\GeographicalNamesInflection::getCase('Москва', 'родительный'); // 'Москвы'

        // Pluralize russian nouns and adjectives:
        morphos\Russian\pluralize(10, 'новый дом'); // '10 новых домов'

        // Generate russian cardinal numerals:
        morphos\Russian\CardinalNumeralGenerator::getCase(567, 'именительный'); // 'пятьсот шестьдесят семь'

        // Generate russian ordinal numerals:
        morphos\Russian\OrdinalNumeralGenerator::getCase(961, 'именительный'); // 'девятьсот шестьдесят первый'

        // Generate russian time difference
        morphos\Russian\TimeSpeller::spellDifference(time() + 3601, morphos\TimeSpeller::
            DIRECTION); // 'через 1 час'

        // other functions described in README-ru.md
    }

    public function isRoutIndexing($as, $args)
    {
        return true;
    }

    public function isPageLoadAjax()
    {
        return true;
    }

    public function isRoutLoadAjax($as, $args)
    {
        return false;
    }
    
    public function syncModel($m1, $m2, $name, $key1, $key2)
    {
        $m_sort = new BaseObj;

        foreach ($m2 as $m) {
            $m_sort->{$m->{$key2}} = $m;
        }

        foreach ($m1 as $m) {
            $m->{$name} = null;

            if ($m->{$key1} && isset($m_sort->{$m->{$key1}})) {
                $m->{$name} = $m_sort->{$m->{$key1}};
            }
        }
    }

    public function syncChildModels($m1, $m2, $name1, $name2, $key1, $key2)
    {
        $ms = [];
        
        foreach ($m1 as $m) {
            if (isset($m->{$name1})) {
                foreach ($m->{$name1} as $m_tmp) {
                    $ms[] = $m_tmp;
                }
            }
        }
        
        $this->syncModel($ms, $m2, $name2, $key1, $key2);
    }

    public function syncModels($m1, $m2, $name, $key1, $key2)
    {
        $m_sort = new BaseObj;

        foreach ($m1 as $m) {
            $m_sort->{$m->{$key1}} = $m;
            $m_sort->{$m->{$key1}}->{$name} = new BaseObj;
        }

        foreach ($m2 as $m) {
            if (isset($m_sort->{$m->{$key2}})) {
                $m_sort->{$m->{$key2}}->{$name}->{$m->id} = $m;
            }
        }
    }

    public static function instance($new = false)
    {
        if (is_null(self::$instance) || $new) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function init()
    {

    }
}
