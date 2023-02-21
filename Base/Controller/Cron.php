<?php

namespace App\Controllers;

use \Base;
use \Base\Base\BaseController;
use \Base\Base\Request;
use \Base\Base\DB;
use \App\Models\User;
use \Base\Base\BaseObj;
use \App\Components\Bot;
use \App\Components\SocialVk;
use \App\Models\Social;
use \App\Widgets\Categories\Categories;

class Cron extends BaseController
{
    public function init()
    {
        echo 'CronInit';

        $cron = \App\Components\Cron::instance();
        
        $cron->addTask(['name' => 'flash_public_tmp_dir', 'duration' => 60, 'function' => function () {
            foreach (scandir(Base::app()->config('SITE_ROOT') . '/public/tmp') as $file) {
                if ($file == '.') {
                    continue; }
                if ($file == '..') {
                    continue; }

                if (time() - filemtime(Base::app()->config('SITE_ROOT') . '/public/tmp/' . $file) >
                    60) {
                    unlink(Base::app()->config('SITE_ROOT') . '/public/tmp/' . $file); }
            }
        }]);

        $cron->addTask(['name' => 'createProjectsBot', 'duration' => (rand((60 * 3), (60 *
            5))), 'function' => function ()
        {
            Bot::createProjectsBot(); }
        ]);

        $cron->addTask(['name' => 'ordersProjecstBot', 'duration' => (rand((60 * 5), (60 *
            10))), 'function' => function ()
        {
            Bot::ordersProjecstBot(); }
        ]);

        $cron->addTask(['name' => 'selectOrderProjectsBot', 'duration' => (rand((60 * 60),
            (60 * 60 * 1.5))), 'function' => function ()
        {
            Bot::selectOrderProjectsBot(); }
        ]);

        $cron->addTask(['name' => 'completeProjectsBot', 'duration' => (rand((60 * 60 *
            2), (60 * 60 * 3))), 'function' => function ()
        {
            Bot::completeProjectsBot(); }
        ]);

        $cron->addTask(['name' => 'generateSitemap', 'duration' => (60 * 60 * 24 * 3),
            'function' => function ()
        {
            \App\Components\Cron::instance()->sitemap(); }
        ]);

        $cron->addTask(['name' => 'generateSitemap', 'duration' => (60 * 60 * 24), 'function' => function () {
            if (file_exists(config('storage_dir') . '/socials/vk-captcha-error.txt')) {
                unlink(file_exists(config('storage_dir') . '/socials/vk-captcha-error.txt')); 
            }
        }]);
        
        $cron->addTask(['name' => 'postInVk', 'duration' => 60 * 30, 'function' => function () {
            Bot::postInVk(); 
        }]);

        //$this->indexing();
        //$this->setIndexingProjectsUsers();
        //$this->each();
        //$this->SocialVk();
        //$this->SocialVkCategory();
        
        //eachTableInBd('Social', function ($obj) {});

\App\Components\Cron::instance()->run();
    }

    private function SocialVk()
    {
        $this->SocialVkCountry();
        $this->SocialVkCity();
        $this->SocialVkCategory();
    }

    private function SocialVkCountry()
    {
        $social_vk = new SocialVk;

        $coutries = Base::dataModel('Country', 'dataArrayCountriesAll');

        foreach ($coutries as $country) {
            if ($country->countSocials < config('countSocialsByItem')) {
                $groups = $social_vk->api('groups.search', ['q' => 'Работа в ' . $country->
                    name_in_ru, 'type' => 'public', 'count' => 500]);

                //print_r($groups);exit;

                if ($groups && isset($groups->items)) {
                    $i_success = 0;
                    $is_success = false;

                    foreach ($groups->items as $group) {
                        $post_in_group = null;

                        if ($group->is_closed == '0') {
                            $is_success = true;
                        }

                        if ($is_success) {
                            $i_success++;

                            if (!$social = Base::dataModel('Social', 'arraySocialBySocialId', ['id_social' =>
                                $group->id])) {
                                $social = new Social;

                                $social->id_social = $group->id;
                                $social->type = 'vk';
                            }

                            $social->save();

                            $social->syncCountry($country->id);
                        }

                        if ($i_success >= config('countSocialsByItem')) {
                            break;
                        }
                    }
                }
            }
        }
    }

    private function SocialVkCity()
    {
        $social_vk = new SocialVk;

        $cities = Base::dataModel('City', 'dataArrayCityPopulation', ['max_population' =>
            50000]);

        foreach ($cities as $city) {
            if ($city->countSocials < config('countSocialsByItem')) {
                $groups = $social_vk->api('groups.search', ['q' => 'Работа в ' . $city->
                    name_in_ru, 'type' => 'public', 'count' => 500]);

                //print_r($groups);exit;

                if ($groups && isset($groups->items)) {
                    $i_success = 0;
                    $is_success = false;

                    foreach ($groups->items as $group) {
                        $post_in_group = null;

                        if ($group->is_closed == '0') {
                            $is_success = true;
                        }

                        if ($is_success) {
                            $i_success++;

                            if (!$social = Base::dataModel('Social', 'arraySocialBySocialId', ['id_social' =>
                                $group->id])) {
                                $social = new Social;

                                $social->id_social = $group->id;
                                $social->type = 'vk';
                            }

                            $social->save();

                            $social->syncCity($city->id);
                        }

                        if ($i_success >= config('countSocialsByItem')) {
                            break;
                        }
                    }
                }
            }
        }
    }

    private function SocialVkCategory()
    {
        $social_vk = new SocialVk;

        eachTableInBd('Category', function ($obj)use ($social_vk)
        {
            if ($obj->countSocials < config('countSocialsByItem')) {
                $groups = $social_vk->api('groups.search', ['q' => 'Работа, подработка для ' . $obj->
                    name_ed_mr_od_vn, 'type' => 'public', 'count' => 500]); //print_r($groups);exit;

                if ($groups && isset($groups->items)) {
                    $i_success = 0; $is_success = false; foreach ($groups->items as $group) {
                        $post_in_group = null; if ($group->is_closed == '0') {
                            $is_success = true; }

                        if ($is_success) {
                            $i_success++; if (!$social = Base::dataModel('Social', 'arraySocialBySocialId', ['id_social' =>
                                $group->id])) {
                                $social = new Social; $social->id_social = $group->id; $social->type = 'vk'; }

                            $social->save(); $social->syncCategory($obj->id); }

                        if ($i_success >= config('countSocialsByItem')) {
                            break; }
                    }
                }
            }
        }
        );
    }

    private function each()
    {
        eachTableInBd('Review', function ($obj)
        {
            $obj->updateReviews(); }
        );

        eachTableInBd('User', function ($obj)
        {
            foreach ($obj->categories as $category) {
                if ($category->category_user_price && $category->category_user_currency_id) {
                    $obj->price = $category->category_user_price; $obj->currency_id = $category->
                        category_user_currency_id; break; }
            }

            $obj->save(); }
        );

        eachTableInBd('CategoryProject', function ($obj)
        {
            if ($obj->category) {
                $obj->nm = $obj->category->nm; }

            $obj->save(); }
        );

        eachTableInBd('CategoryUser', function ($obj)
        {
            if ($obj->category) {
                $obj->nm = $obj->category->nm; }

            $obj->save(); }
        );
    }

    private function indexing()
    {
        eachTableInBd('CountryCategoryJobsIndexings', function ($obj)
        {
            objIndexing('CountryCategoryJobsIndexings', [$obj->category_id, $obj->
                country_id]); }
        );
        eachTableInBd('CountryCategoryUsersIndexings', function ($obj)
        {
            objIndexing('CountryCategoryUsersIndexings', [$obj->category_id, $obj->
                country_id]); }
        );
        eachTableInBd('RegionCategoryJobsIndexings', function ($obj)
        {
            objIndexing('RegionCategoryJobsIndexings', [$obj->category_id, $obj->region_id]); }
        );
        eachTableInBd('RegionCategoryUsersIndexings', function ($obj)
        {
            objIndexing('RegionCategoryUsersIndexings', [$obj->category_id, $obj->region_id]); }
        );
        eachTableInBd('AreaCategoryJobsIndexings', function ($obj)
        {
            objIndexing('AreaCategoryJobsIndexings', [$obj->category_id, $obj->area_id]); }
        );
        eachTableInBd('AreaCategoryUsersIndexings', function ($obj)
        {
            objIndexing('AreaCategoryUsersIndexings', [$obj->category_id, $obj->area_id]); }
        );
        eachTableInBd('CityCategoryJobsIndexings', function ($obj)
        {
            objIndexing('CityCategoryJobsIndexings', [$obj->category_id, $obj->city_id]); }
        );
        eachTableInBd('CityCategoryUsersIndexings', function ($obj)
        {
            objIndexing('CityCategoryUsersIndexings', [$obj->category_id, $obj->city_id]); }
        );
        eachTableInBd('CategoryJobsIndexings', function ($obj)
        {
            objIndexing('CategoryJobsIndexings', [$obj->category_id]); }
        );
        eachTableInBd('CategoryUsersIndexings', function ($obj)
        {
            objIndexing('CategoryUsersIndexings', [$obj->category_id]); }
        );
        eachTableInBd('CityJobsIndexings', function ($obj)
        {
            objIndexing('CityJobsIndexings', [$obj->city_id]); }
        );
        eachTableInBd('CityUsersIndexings', function ($obj)
        {
            objIndexing('CityUsersIndexings', [$obj->city_id]); }
        );
    }

    public function setIndexingProjectsUsers()
    {
        $offset = 0;
        $limit = 50;

        while ($rows = DB::GetAll("SELECT `id` FROM `users` LIMIT " . $offset . ", " . $limit)) {
            $offset += 50;
            foreach ($rows as $row) {
                $user = Base::dataModel('User', 'arrayUserById', ['id' => $row['id']]);

                if ($user) {
                    foreach ($user->categories as $category) {
                        do {
                            if ($user->countryObj) {
                                if (!$indexing = Base::dataModel('CountryCategoryUsersIndexings',
                                    'arrayCountryCategoryUsersIndexings', ['country_id' => $user->countryObj->id,
                                    'category_id' => $category->id])) {
                                    $indexing = new \App\Models\CountryCategoryUsersIndexings;

                                    $indexing->status = 'yes';
                                    $indexing->country_id = $user->countryObj->id;
                                    $indexing->category_id = $category->id;

                                    $indexing->save();
                                }
                            }
                            if ($user->regionObj) {
                                if (!$indexing = Base::dataModel('RegionCategoryUsersIndexings',
                                    'arrayRegionCategoryUsersIndexings', ['region_id' => $user->regionObj->id,
                                    'category_id' => $category->id])) {
                                    $indexing = new \App\Models\RegionCategoryUsersIndexings;

                                    $indexing->status = 'yes';
                                    $indexing->region_id = $user->regionObj->id;
                                    $indexing->category_id = $category->id;

                                    $indexing->save();
                                }
                            }
                            if ($user->areaObj) {
                                if (!$indexing = Base::dataModel('AreaCategoryUsersIndexings',
                                    'arrayAreaCategoryUsersIndexings', ['area_id' => $user->areaObj->id,
                                    'category_id' => $category->id])) {
                                    $indexing = new \App\Models\AreaCategoryUsersIndexings;

                                    $indexing->status = 'yes';
                                    $indexing->area_id = $user->areaObj->id;
                                    $indexing->category_id = $category->id;

                                    $indexing->save();
                                }
                            }
                            if ($user->cityObj) {
                                if (!$indexing = Base::dataModel('CityCategoryUsersIndexings',
                                    'arrayCityCategoryUsersIndexings', ['city_id' => $user->cityObj->id,
                                    'category_id' => $category->id])) {
                                    $indexing = new \App\Models\CityCategoryUsersIndexings;

                                    $indexing->status = 'yes';
                                    $indexing->city_id = $user->cityObj->id;
                                    $indexing->category_id = $category->id;

                                    $indexing->save();
                                }
                            }

                            $category = $category->parent;
                        } while ($category);
                    }

                    if ($user->cityObj) {
                        if (!$indexing = Base::dataModel('CityUsersIndexings',
                            'arrayCityUsersIndexings', ['city_id' => $user->cityObj->id])) {
                            $indexing = new \App\Models\CityUsersIndexings;

                            $indexing->status = 'yes';
                            $indexing->city_id = $user->cityObj->id;

                            $indexing->save();
                        }
                    }
                }
            }

            if (count($rows) != 50)
                break;
        }


        $offset = 0;
        $limit = 50;

        while ($rows = DB::GetAll("SELECT `id` FROM `projects` LIMIT " . $offset . ", " .
            $limit)) {
            $offset += 50;
            foreach ($rows as $row) {
                $project = Base::dataModel('Project', 'arrayProjectById', ['id' => $row['id']]);

                if ($project) {
                    foreach ($project->categories as $category) {
                        do {
                            if ($project->countryObj) {
                                if (!$indexing = Base::dataModel('CountryCategoryJobsIndexings',
                                    'arrayCountryCategoryJobsIndexings', ['country_id' => $project->countryObj->id,
                                    'category_id' => $category->id])) {
                                    $indexing = new \App\Models\CountryCategoryJobsIndexings;

                                    $indexing->status = 'yes';
                                    $indexing->country_id = $project->countryObj->id;
                                    $indexing->category_id = $category->id;

                                    $indexing->save();
                                }
                            }
                            if ($project->regionObj) {
                                if (!$indexing = Base::dataModel('RegionCategoryJobsIndexings',
                                    'arrayRegionCategoryJobsIndexings', ['region_id' => $project->regionObj->id,
                                    'category_id' => $category->id])) {
                                    $indexing = new \App\Models\RegionCategoryJobsIndexings;

                                    $indexing->status = 'yes';
                                    $indexing->region_id = $project->regionObj->id;
                                    $indexing->category_id = $category->id;

                                    $indexing->save();
                                }
                            }
                            if ($project->areaObj) {
                                if (!$indexing = Base::dataModel('AreaCategoryJobsIndexings',
                                    'arrayAreaCategoryJobsIndexings', ['area_id' => $project->areaObj->id,
                                    'category_id' => $category->id])) {
                                    $indexing = new \App\Models\AreaCategoryJobsIndexings;

                                    $indexing->status = 'yes';
                                    $indexing->area_id = $project->areaObj->id;
                                    $indexing->category_id = $category->id;

                                    $indexing->save();
                                }
                            }
                            if ($project->cityObj) {
                                if (!$indexing = Base::dataModel('CityCategoryJobsIndexings',
                                    'arrayCityCategoryJobsIndexings', ['city_id' => $project->cityObj->id,
                                    'category_id' => $category->id])) {
                                    $indexing = new \App\Models\CityCategoryJobsIndexings;

                                    $indexing->status = 'yes';
                                    $indexing->city_id = $project->cityObj->id;
                                    $indexing->category_id = $category->id;

                                    $indexing->save();
                                }
                            }

                            $category = $category->parent;
                        } while ($category);
                    }

                    if ($project->cityObj) {
                        if (!$indexing = Base::dataModel('CityJobsIndexings', 'arrayCityJobsIndexings', ['city_id' =>
                            $project->cityObj->id])) {
                            $indexing = new \App\Models\CityJobsIndexings;

                            $indexing->status = 'yes';
                            $indexing->city_id = $project->cityObj->id;

                            $indexing->save();
                        }
                    }
                }
            }

            if (count($rows) != 50)
                break;
        }
    }
}
