<?php

namespace App\Widgets\Meta;

use \Base;
use \Base\Base\BaseWidget;
use \Base\Base\Request;
use \Base\Base\DB;
use \Base\Base\Route;
use \App\Widgets\Categories\Categories;

class Meta extends BaseWidget
{
    protected function init()
    {
        $this->data->{'shortcut_icon'} = asset('img/favicon.ico');
        
        $this->data->{'viewport'} =
            "width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no";
        // <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

        $this->data->{'X-UA-Compatible'} = "IE=edge,chrome=1";
        // <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />

        $this->data->{'viewport'} = "width=device-width, initial-scale=1.0";
        // <meta name="viewport" content="width=device-width, initial-scale=1.0" />

        $this->data->{'country-code'} = "UA";
        // <meta name="country-code" content="UA" />

        $this->data->{'title'} = "";
        // <title>Фриланс. Фриланс биржа Weblancer — лучший сайт для поиска фрилансеров и удаленной работы</title>

        $this->data->{'description'} = null;
        // <meta name="description" content="Лучшая фриланс биржа &#11088; для поиска исполнителей и удаленной работы. &#11088; Фриланс для любых проектов &#11088; Официальный сайт фрилансеров: &#9989; выполнение фриланс заказов &#9989; отзывы &#9989; лучшие вакансии &am..." />

        $this->data->{'keywords'} = null;
        // <meta name="keywords" content="" />

        $this->data->{'robots'} = "noodp, noydir, noyaca";
        // <meta name="robots" content="noodp, noydir, noyaca" />

        $this->data->{'assets-base'} = null;
        // <meta name="assets-base" content="https://static.kabanchik.ua/static/" />


        $this->data->{'canonical'} = null;
        // <link rel="canonical" href="https://www.weblancer.net" />
        $this->data->{'prev'} = null;
        // <link rel="prev" href="https://www.weblancer.net/jobs/?page=2" />
        $this->data->{'next'} = null;
        // <link rel="next" href="https://www.weblancer.net/jobs/?page=4" />

        $this->data->{'apple-touch-icon'} = null;
        // <link rel="apple-touch-icon" sizes="180x180" href="/img/favicons/apple-touch-icon.png" />
        $this->data->{'manifest'} = '/manifest.json';
        // <link rel="manifest" href="/img/favicons/manifest.json" />
        $this->data->{'mask-icon'} = null;
        // <link rel="mask-icon" href="/img/favicons/safari-pinned-tab.svg" color="#5bbad5" />
        $this->data->{'image_src'} = '';
        //if (\App\Models\Site::site()->key == 'main')
            $this->data->{'image_src'} = "/img/f-64x64.png";
        // <link rel="image_src" href="https://www.weblancer.net/img/favicons/android-chrome-256x256.png" />
        
        $this->data->{'apple-touch-icon'} = "/img/f-64x64.png";

        if (isset(data()->canonical_link)) {
            $this->data->{'canonical'} = data()->canonical_link;
        }
        if (isset(data()->prev_link)) {
            $this->data->{'prev'} = data()->prev_link;
        }
        if (isset(data()->next_link)) {
            $this->data->{'next'} = data()->next_link;
        }

        if (!app()->components()->isPageIndexing(Route::nowRout())) {
            $this->data->{'robots'} = "noindex, nofollow";
        }

        if (data()->data && !isset(data()->data->rout)) {
            data()->data->rout = Route::nowRout();
        }

        if (data()->data) {
            if (\App\Models\Site::site()->key == 'main') {
                switch (data()->data->rout) {
                    case 'main.index':
                        $this->data->{'title'} =
                            "Биржа обмена услуг. Сайт заказа услуг Worklancer — сайт для онлайн работы";

                        $this->data->{'description'} =
                            "Найдите специалиста для выполнения ваших задач или предложите собственные услуги. &#11088; для выполнения бытовых или рабочих задач. &#11088; Сервис для работы выпония любых задач &#11088; Официальный сайт услуг: &#9989; выполнение задач по найму &#9989; выполнение фриланс заказов &#9989; отзывы &#9989;";

                        if (isset(data()->data->subdomain_title)) {
                            $this->data->{'title'} = data()->data->subdomain_title;
                        }
                        if (isset(data()->data->subdomain_description)) {
                            $this->data->{'description'} = data()->data->subdomain_description;
                        }

                        //MainController@index
                        break;
                    case 'users.contact.messages':
                        $this->data->{'title'} = data()->data->user->name . ' ' . data()->data->user->
                            surname . ', ' . data()->data->user->login . _t('widgets.meta.-_Privatnye_soobshcheniya',
                            " - Приватные сообщения");

                        $categories = [];

                        foreach (data()->user->categories as $category) {
                            $categories[] = $category->name;
                        }

                        shuffle($categories);

                        $this->data->{'description'} = _t('widgets.meta.Privatnye_soobshcheniya_polzovatelya',
                            "Приватные сообщения пользователя ") . data()->data->user->name . ' ' . data()->
                            data->user->surname . ', ' . data()->data->user->login . ' &#11088;' . (empty($categories) ?
                            '' : ' ' . implode('&#9989; ', $categories) . ' &#9989;') .
                            ' отзывы &#9989; портфолио &#9989; резюме — найти специалиста на любую работу | Ворклансер';
                        //UsersController@accountLogout
                        break;
                        //UsersController@accountContactMessages
                    case 'users.personal_information':
                    case 'users.account':
                        $this->data->{'title'} = data()->user->name . ' ' . data()->user->surname . '. ' .
                            data()->user->login . _t('widgets.meta.-_Lichnaya_informaciya_na_Worklancer_net',
                            " - Личная информация на Worklancer.net");

                        $categories = [];

                        foreach (data()->user->categories as $category) {
                            $categories[] = $category->name;
                        }

                        shuffle($categories);

                        $this->data->{'description'} = _t('widgets.meta.Lichnaya_informaciya_polzovatelya',
                            "Личная информация пользователя ") . data()->user->name . ' ' . data()->user->
                            surname . ', ' . data()->user->login . ' &#11088;' . (empty($categories) ? '' :
                            ' ' . implode('&#9989; ', $categories) . ' &#9989;') .
                            ' отзывы &#9989; портфолио &#9989; резюме — найти специалиста на любую работу | Ворклансер';
                        //UsersController@accountLogout
                        break;
                        //UsersController@accountPersonalInformation
                    case 'users.contact_details':
                        $this->data->{'title'} = data()->user->name . ' ' . data()->user->surname . '. ' .
                            data()->user->login . _t('widgets.meta.-_Kontaktnye_dannye_na_Worklancer_net',
                            " - Контактные данные на Worklancer.net");

                        $categories = [];

                        foreach (data()->user->categories as $category) {
                            $categories[] = $category->name;
                        }

                        shuffle($categories);

                        $this->data->{'description'} = _t('widgets.meta.Kontaktnye_dannye_polzovatelya',
                            "Контактные данные пользователя ") . data()->user->name . ' ' . data()->user->
                            surname . ', ' . data()->user->login . ' &#11088;' . (empty($categories) ? '' :
                            ' ' . implode('&#9989; ', $categories) . ' &#9989;') .
                            ' отзывы &#9989; портфолио &#9989; резюме — найти специалиста на любую работу | Ворклансер';
                        //UsersController@accountLogout
                        break;
                        //UsersController@accountContactDetails
                    case 'users.contacts':
                        $this->data->{'title'} = data()->user->name . ' ' . data()->user->surname . '. ' .
                            data()->user->login . _t('widgets.meta.-_kontakty_na_Worklancer_net',
                            " - Контакты на Worklancer.net");

                        $categories = [];

                        foreach (data()->user->categories as $category) {
                            $categories[] = $category->name;
                        }

                        shuffle($categories);

                        $this->data->{'description'} = _t('widgets.meta.Kontakty_polzovatelya',
                            "Контакты пользователя ") . data()->user->name . ' ' . data()->user->surname .
                            ', ' . data()->user->login . ' &#11088;' . (empty($categories) ? '' : ' ' .
                            implode('&#9989; ', $categories) . ' &#9989;') .
                            ' отзывы &#9989; портфолио &#9989; резюме — найти специалиста на любую работу | Ворклансер';
                        //UsersController@accountLogout
                        break;
                        //UsersController@accountContacts
                    case 'users.notifications':
                        $this->data->{'title'} = data()->user->name . ' ' . data()->user->surname . ', ' .
                            data()->user->login . _t('widgets.meta.-_uvedomleniya_na_Worklancer_net',
                            " - уведомления на Worklancer.net");

                        $categories = [];

                        foreach (data()->user->categories as $category) {
                            $categories[] = $category->name;
                        }

                        shuffle($categories);

                        $this->data->{'description'} = _t('widgets.meta.Uvedomleniya_polzovatelya',
                            "Уведомления пользователя ") . data()->user->name . ' ' . data()->user->surname .
                            ', ' . data()->user->login . ' &#11088;' . (empty($categories) ? '' : ' ' .
                            implode('&#9989; ', $categories) . ' &#9989;') .
                            ' отзывы &#9989; портфолио &#9989; резюме — найти специалиста на любую работу | Ворклансер';
                        //UsersController@accountLogout
                        break;
                        //UsersController@accountNotifications
                    case 'users.tariff_plan':
                        $this->data->{'title'} = data()->user->name . ' ' . data()->user->surname . '. ' .
                            data()->user->login . _t('widgets.meta.-_tarifnyy_plan_na_Worklancer_net',
                            " - Тарифный план на Worklancer.net");

                        $categories = [];

                        foreach (data()->user->categories as $category) {
                            $categories[] = $category->name;
                        }

                        shuffle($categories);

                        $this->data->{'description'} = _t('widgets.meta.Tarifnyy_plan_polzovatelya',
                            "Тарифный план пользователя ") . data()->user->name . ' ' . data()->user->
                            surname . ', ' . data()->user->login . ' &#11088;' . (empty($categories) ? '' :
                            ' ' . implode('&#9989; ', $categories) . ' &#9989;') .
                            ' отзывы &#9989; портфолио &#9989; резюме — найти специалиста на любую работу | Ворклансер';
                        //UsersController@accountLogout
                        break;
                    case 'advertisings.index':
                        $this->data->{'title'} = data()->user->name . ' ' . data()->user->surname . '. ' .
                            data()->user->login . _t('widgets.meta.-_Reklama_v_servise_na_Worklancer_net',
                            " - Реклама в сервисе на Worklancer.net");

                        $categories = [];

                        foreach (data()->user->categories as $category) {
                            $categories[] = $category->name;
                        }

                        shuffle($categories);

                        $this->data->{'description'} = _t('widgets.meta.Reklama_v_servise_polzovatelya',
                            "Реклама в сервисе пользователя ") . data()->user->name . ' ' . data()->user->
                            surname . ', ' . data()->user->login . ' &#11088;' . (empty($categories) ? '' :
                            ' ' . implode('&#9989; ', $categories) . ' &#9989;') .
                            ' отзывы &#9989; портфолио &#9989; резюме — найти специалиста на любую работу | Ворклансер';
                        //UsersController@accountLogout
                        break;
                        //UsersController@accountTariffPlan
                    case 'users.portfolio':
                        $this->data->{'title'} = data()->user->name . ' ' . data()->user->surname . '. ' .
                            data()->user->login . _t('widgets.meta.-_Portfolio_na_Worklancer_net',
                            " - Портфолио на Worklancer.net");

                        $categories = [];

                        foreach (data()->user->categories as $category) {
                            $categories[] = $category->name;
                        }

                        shuffle($categories);

                        $this->data->{'description'} = _t('widgets.meta.Portfolio', "Портфолио ") . data()->
                            user->name . ' ' . data()->user->surname . ', ' . data()->user->login .
                            ' &#11088;' . (empty($categories) ? '' : ' ' . implode('&#9989; ', $categories) .
                            ' &#9989;') . ' отзывы &#9989; портфолио &#9989; резюме — найти специалиста на любую работу | Ворклансер';
                        //UsersController@accountLogout
                        break;
                        //UsersController@accountPortfolio
                    case 'users.portfolio.add':
                        $this->data->{'title'} = data()->user->name . ' ' . data()->user->surname . '. ' .
                            data()->user->login . _t('widgets.meta.-_Portfolio_na_Worklancer_net',
                            " - Портфолио на Worklancer.net");

                        $categories = [];

                        foreach (data()->user->categories as $category) {
                            $categories[] = $category->name;
                        }

                        shuffle($categories);

                        $this->data->{'description'} = _t('widgets.meta.Portfolio_polzovatelya',
                            "Портфолио пользователя ") . data()->user->name . ' ' . data()->user->surname .
                            ', ' . data()->user->login . ' &#11088;' . (empty($categories) ? '' : ' ' .
                            implode('&#9989; ', $categories) . ' &#9989;') .
                            ' отзывы &#9989; портфолио &#9989; резюме — найти специалиста на любую работу | Ворклансер';
                        //UsersController@accountLogout
                        break;
                        //UsersController@accountPortfolioAdd
                    case 'users.portfolio.edit':
                        $this->data->{'title'} = data()->user->name . ' ' . data()->user->surname . '. ' .
                            data()->user->login . _t('widgets.meta.-_Portfolio_na_Worklancer_net',
                            " - Портфолио на Worklancer.net");

                        $categories = [];

                        foreach (data()->user->categories as $category) {
                            $categories[] = $category->name;
                        }

                        shuffle($categories);

                        $this->data->{'description'} = _t('widgets.meta.Obnovlenie_portfolio_polzovatelya',
                            "Обновление портфолио пользователя ") . data()->user->name . ' ' . data()->user->
                            surname . ', ' . data()->user->login . ' &#11088;' . (empty($categories) ? '' :
                            ' ' . implode('&#9989; ', $categories) . ' &#9989;') .
                            ' отзывы &#9989; портфолио &#9989; резюме — найти специалиста на любую работу | Ворклансер';
                        //UsersController@accountLogout
                        break;
                        //UsersController@accountPortfolioEdit
                    case 'users.prices':
                        $this->data->{'title'} = data()->user->name . ' ' . data()->user->surname . '. ' .
                            data()->user->login . _t('widgets.meta.-_Ceny_na_Worklancer_net',
                            " - Цены на Worklancer.net");

                        $categories = [];

                        foreach (data()->user->categories as $category) {
                            $categories[] = $category->name;
                        }

                        shuffle($categories);

                        $this->data->{'description'} = _t('widgets.meta.Ceny_polzovatelya',
                            "Цены пользователя ") . data()->user->name . ' ' . data()->user->surname . ', ' .
                            data()->user->login . ' &#11088;' . (empty($categories) ? '' : ' ' . implode('&#9989; ',
                            $categories) . ' &#9989;') .
                            ' отзывы &#9989; портфолио &#9989; резюме — найти специалиста на любую работу | Ворклансер';
                        //UsersController@accountLogout
                        break;
                        //UsersController@accountPrices
                    case 'users.change_password':
                        $this->data->{'title'} = data()->user->name . ' ' . data()->user->surname . '. ' .
                            data()->user->login . _t('widgets.meta.-_obnovlenie_parolya_na_Worklancer_net',
                            " - Обновление пароля на Worklancer.net");

                        $categories = [];

                        foreach (data()->user->categories as $category) {
                            $categories[] = $category->name;
                        }

                        shuffle($categories);

                        $this->data->{'description'} = _t('widgets.meta.Obnovlenie_parolya_polzovatelya',
                            "Обновление пароля пользователя ") . data()->user->name . ' ' . data()->user->
                            surname . ', ' . data()->user->login . ' &#11088;' . (empty($categories) ? '' :
                            ' ' . implode('&#9989; ', $categories) . ' &#9989;') .
                            ' отзывы &#9989; портфолио &#9989; резюме — найти специалиста на любую работу | Ворклансер';
                        //UsersController@accountLogout
                        break;
                        //UsersController@accountChangePassword
                    case 'users.newsletter':
                        $this->data->{'title'} = data()->user->name . ' ' . data()->user->surname . '. ' .
                            data()->user->login . _t('widgets.meta.-_Rassylka_na_Worklancer_net',
                            " - Рассылка на Worklancer.net");

                        $categories = [];

                        foreach (data()->user->categories as $category) {
                            $categories[] = $category->name;
                        }

                        shuffle($categories);

                        $this->data->{'description'} = _t('widgets.meta.Rassylka_polzovatelya',
                            "Рассылка пользователя ") . data()->user->name . ' ' . data()->user->surname .
                            ', ' . data()->user->login . ' &#11088;' . (empty($categories) ? '' : ' ' .
                            implode('&#9989; ', $categories) . ' &#9989;') .
                            ' отзывы &#9989; портфолио &#9989; резюме — найти специалиста на любую работу | Ворклансер';
                        //UsersController@accountLogout
                        break;
                        //UsersController@accountNewsletter
                    case 'users.delete':
                        $this->data->{'title'} = data()->user->name . ' ' . data()->user->surname . '. ' .
                            data()->user->login . _t('widgets.meta.-_udalenie_polzovatelya_na_Worklancer_net',
                            " - удаление пользователя на Worklancer.net");

                        $categories = [];

                        foreach (data()->user->categories as $category) {
                            $categories[] = $category->name;
                        }

                        shuffle($categories);

                        $this->data->{'description'} = _t('widgets.meta.Udalenie_polzovatelya',
                            "Удаление пользователя ") . data()->user->name . ' ' . data()->user->surname .
                            ', ' . data()->user->login . ' &#11088;' . (empty($categories) ? '' : ' ' .
                            implode('&#9989; ', $categories) . ' &#9989;') .
                            ' отзывы &#9989; портфолио &#9989; резюме — найти специалиста на любую работу | Ворклансер';
                        //UsersController@accountLogout
                        break;
                        //UsersController@accountDelete
                    case 'users.logout':
                        $this->data->{'title'} = data()->user->name . ' ' . data()->user->surname . '. ' .
                            data()->user->login . _t('widgets.meta.-_profil_polzovatelya_na_Worklancer_net',
                            " - профиль пользователя на Worklancer.net");

                        $categories = [];

                        foreach (data()->user->categories as $category) {
                            $categories[] = $category->name;
                        }

                        shuffle($categories);

                        $this->data->{'description'} = _t('widgets.meta.Profil_polzovatelya',
                            "Профиль пользователя ") . data()->user->name . ' ' . data()->user->surname .
                            ', ' . data()->user->login . ' &#11088;' . (empty($categories) ? '' : ' ' .
                            implode('&#9989; ', $categories) . ' &#9989;') .
                            ' отзывы &#9989; портфолио &#9989; резюме — найти специалиста на любую работу | Ворклансер';
                        //UsersController@accountLogout
                        break;
                    case 'users.index':
                        $this->data->{'title'} = data()->data->user->name . ' ' . data()->data->user->
                            surname . '. ' . data()->data->user->login . _t('widgets.meta.-_profil_polzovatelya_na_Worklancer_net',
                            " - профиль пользователя на Worklancer.net");

                        $categories = [];

                        foreach (data()->data->user->categories as $category) {
                            $categories[] = $category->name;
                        }

                        shuffle($categories);

                        $this->data->{'description'} = _t('widgets.meta.Profil_polzovatelya',
                            "Профиль пользователя ") . data()->data->user->name . ' ' . data()->data->user->
                            surname . ', ' . data()->data->user->login . ' &#11088;' . (empty($categories) ?
                            '' : _t('widgets.meta.Uslugi_po_napravleniyu', ' Услуги по направлению ') .
                            implode('&#9989; ', $categories) . ' &#9989;') .
                            ' отзывы &#9989; портфолио &#9989; резюме — найти специалиста на любую работу | Ворклансер';
                        //UsersController@userReviews
                        break;
                        //UsersController@userIndex
                    case 'projects.user':
                        $this->data->{'title'} = _t('widgets.meta.Zakazy_polzovatelya',
                            "Заказы пользователя ") . data()->data->user->name . ' ' . data()->data->user->
                            surname . _t('widgets.meta.sayt_Worklancer_net', ". Сайт Worklancer.net");

                        $categories = [];

                        foreach (data()->data->user->categories as $category) {
                            $categories[] = $category->name;
                        }

                        shuffle($categories);

                        $this->data->{'description'} = _t('widgets.meta.Zakazy_polzovatelya',
                            "Заказы пользователя ") . data()->data->user->name . ' ' . data()->data->user->
                            surname . ', ' . data()->data->user->login . ' &#11088;' . (empty($categories) ?
                            '' : ' ' . implode('&#9989; ', $categories) . ' &#9989;') .
                            ' заказы &#9989; портфолио &#9989; резюме — найти специалиста на любую работу | Ворклансер';
                        //UsersController@userReviews
                        break;
                    case 'orders.user':
                        $this->data->{'title'} = _t('widgets.meta.Zayavki_polzovatelya',
                            "Заявки пользователя ") . data()->data->user->name . ' ' . data()->data->user->
                            surname . _t('widgets.meta.sayt_Worklancer_net', ". Сайте Worklancer.net");

                        $categories = [];

                        foreach (data()->data->user->categories as $category) {
                            $categories[] = $category->name;
                        }

                        shuffle($categories);

                        $this->data->{'description'} = _t('widgets.meta.Zayavki_polzovatelya',
                            "Заявки пользователя ") . data()->data->user->name . ' ' . data()->data->user->
                            surname . ', ' . data()->data->user->login . ' &#11088;' . (empty($categories) ?
                            '' : ' ' . implode('&#9989; ', $categories) . ' &#9989;') .
                            ' заявки &#9989; портфолио &#9989; резюме — найти специалиста на любую работу | Ворклансер';
                        //UsersController@userReviews
                        break;
                    case 'users.reviews':
                        $this->data->{'title'} = _t('widgets.meta.Otzyvy_polzovatelya',
                            "Отзывы пользователя ") . data()->data->user->name . ' ' . data()->data->user->
                            surname . _t('widgets.meta.sayt_Worklancer_net', ".Сайте Worklancer.net");

                        $categories = [];

                        foreach (data()->data->user->categories as $category) {
                            $categories[] = $category->name;
                        }

                        shuffle($categories);

                        $this->data->{'description'} = _t('widgets.meta.Otzyvy_polzovatelya',
                            "Отзывы пользователя ") . data()->data->user->name . ' ' . data()->data->user->
                            surname . ', ' . data()->data->user->login . ' &#11088;' . (empty($categories) ?
                            '' : ' ' . implode('&#9989; ', $categories) . ' &#9989;') .
                            ' отзывы &#9989; портфолио &#9989; резюме — найти специалиста на любую работу | Ворклансер';
                        //UsersController@userReviews
                        break;
                        //UsersController@userReviews
                    case 'users.reviews.positive':
                        $this->data->{'title'} = _t('widgets.meta.Polozhitelnye_otzyvy_polzovatelya',
                            "Положительные отзывы пользователя ") . data()->data->user->name . ' ' . data()->
                            data->user->surname . ',' . data()->data->user->login . _t('widgets.meta.na_sayte_Worklancer_net',
                            ". Сайт Worklancer.net");

                        $categories = [];

                        foreach (data()->data->user->categories as $category) {
                            $categories[] = $category->name;
                        }

                        shuffle($categories);

                        $this->data->{'description'} = _t('widgets.meta.Polozhitelnye_otzyvy_polzovatelya',
                            "Положительные отзывы пользователя ") . data()->data->user->name . ' ' . data()->
                            data->user->surname . ', ' . data()->data->user->login . ' &#11088;' . (empty($categories) ?
                            '' : ' ' . implode('&#9989; ', $categories) . ' &#9989;') .
                            ' отзывы &#9989; портфолио &#9989; резюме — найти специалиста на любую работу | Ворклансер';
                        //UsersController@userReviews
                        break;
                        //UsersController@userReviews
                    case 'users.reviews.negative':
                        $this->data->{'title'} = _t('widgets.meta.Otricatelnye_otzyvy_polzovatelya',
                            "Отрицательные отзывы пользователя ") . data()->data->user->name . ' ' . data()->
                            data->user->surname . ',' . data()->data->user->login . _t('widgets.meta.sayt_Worklancer_net',
                            ". Сайт Worklancer.net");

                        $categories = [];

                        foreach (data()->data->user->categories as $category) {
                            $categories[] = $category->name;
                        }

                        shuffle($categories);

                        $this->data->{'description'} = _t('widgets.meta.Otricatelnye_otzyvy_polzovatelya',
                            "Отрицательные отзывы пользователя ") . data()->data->user->name . ' ' . data()->
                            data->user->surname . ', ' . data()->data->user->login . ' &#11088;' . (empty($categories) ?
                            '' : ' ' . implode('&#9989; ', $categories) . ' &#9989;') .
                            ' отзывы &#9989; портфолио &#9989; резюме — найти специалиста на любую работу | Ворклансер';
                        //UsersController@userReviews
                        break;
                    case 'auth.register':
                        $this->data->{'title'} = _t('widgets.meta.Registraciya_v_servise_Vorklanser',
                            "Регистрация в сервисе Ворклансер.");

                        $this->data->{'description'} = _t('widgets.meta.Registraciya_v_servise_Vorklanser',
                            "Регистрация в сервисе Ворклансер");
                        break;
                        //AuthController@authRegister
                    case 'auth.register.success':
                        $this->data->{'title'} = _t('widgets.meta.Registraciya_proshla_uspeno',
                            "Регистрация прошла успено.");

                        $this->data->{'description'} = _t('widgets.meta.Registraciya_v_servise_Vorklanser',
                            "Регистрация в сервисе Ворклансер");
                        break;
                        //AuthController@authRegisterSuccess
                    case 'auth.valid':
                        $this->data->{'title'} = _t('widgets.meta.Proverka_Email', "Проверка Email.");

                        $this->data->{'description'} = _t('widgets.meta.Registraciya_v_servise_Vorklanser',
                            "Регистрация в сервисе Ворклансер");
                        break;
                        //AuthController@authValid
                    case 'auth.logout':
                        $this->data->{'title'} = _t('widgets.meta.Vyhod', "Выход.");

                        $this->data->{'description'} = _t('widgets.meta.Vyhod_s_akkaunta',
                            "Выход с аккаунта");
                        break;
                        //AuthController@authLogout
                    case 'auth.remember':
                        $this->data->{'title'} = _t('widgets.meta.Vosstanovlenie_dostupa_k_akkauntu',
                            "Восстановление доступа к аккаунту.");

                        $this->data->{'description'} = _t('widgets.meta.Vosstanovlenie_dostupa_k_akkauntu',
                            "Восстановление доступа к аккаунту");
                        break;
                        //AuthController@authRemember
                    case 'auth.remember.index':
                        $this->data->{'title'} = _t('widgets.meta.Vosstanovlenie_dostupa_k_akkauntu',
                            "Восстановление доступа к аккаунту.");

                        $this->data->{'description'} = _t('widgets.meta.Vosstanovlenie_dostupa_k_akkauntu',
                            "Восстановление доступа к аккаунту");
                        break;
                        //AuthController@authRememberIndex
                    case 'auth.remember.success':
                        $this->data->{'title'} = _t('widgets.meta.Parol_uspeshno_obnovlen',
                            "Пароль успешно обновлен.");

                        $this->data->{'description'} = _t('widgets.meta.Vosstanovlenie_dostupa_k_akkauntu',
                            "Восстановление доступа к аккаунту");
                        break;
                        //AuthController@authRememberSuccess
                    case 'auth.remember.password':
                        $this->data->{'title'} = _t('widgets.meta.Vosstanovlenie_dostupa_k_akkauntu',
                            "Восстановление доступа к аккаунту.");

                        $this->data->{'description'} = _t('widgets.meta.Vosstanovlenie_dostupa_k_akkauntu',
                            "Восстановление доступа к аккаунту");
                        break;
                        //AuthController@authRememberPassword
                    case 'projects.my':
                        $categories = [];

                        foreach (data()->user->categoriesByProjects as $category) {
                            $categories[] = $category->name;
                        }

                        shuffle($categories);
                        $this->data->{'title'} = _t('widgets.meta.Moi_proekty', "Мои проекты") . (empty
                            ($categories) ? '' : ' ' . implode(', ', $categories) . '') . ". " . (empty($categories) ?
                            '' : _t('widgets.meta.Moi_zakazy_-', ' Мои заказы - ') . implode(', ', $categories) .
                            ':') . " цены, задачи | биржа онлайн работы Worklancer";

                        $this->data->{'description'} = "" . (empty($categories) ? '' : _t('widgets.meta.Rabota_-',
                            ' Работа - ') . implode('&#9989; ', $categories) . ' &#11088;') .
                            " &#9989; интересные проекты и услуги — надежный поиск работы | сайт работы Ворклансер";
                        break;
                        //ProjectsController@projectsMy
                    case 'orders.my':
                        $categories = [];

                        foreach (data()->user->categoriesByProjects as $category) {
                            $categories[] = $category->name;
                        }

                        shuffle($categories);

                        $this->data->{'title'} = _t('widgets.meta.Moi_zayavki', "Мои заявки ") . (empty
                            ($categories) ? '' : ' ' . implode(', ', $categories) . '') . ". " . (empty($categories) ?
                            '' : _t('widgets.meta.Rabota_-', ' Работа - ') . implode(', ', $categories) .
                            ':') . " цены, задачи | биржа онлайн работы Worklancer";

                        $this->data->{'description'} = "" . (empty($categories) ? '' : _t('widgets.meta.Rabota_-',
                            ' Работа - ') . implode('&#9989; ', $categories) . ' &#11088;') .
                            " &#9989; интересные проекты и услуги — надежный поиск работы | сайт работы Ворклансер";
                        break;
                        //ProjectsController@ordersMy
                    case 'freelancers.index':
                        $this->data->{'title'} =
                            "Услуги. Найти специалиста: каталог. Услуги лучших специалистов: цены, отзывы, примеры работ | сервис заказа услуг Worklancer";

                        $this->data->{'description'} =
                            "Найти специалиста для выполнения бытовых и рабочих задач. &#11088; Лучшие специалисты &#9989; цены &#9989;отзывы &#9989; примеры работ в портфолио – надежное сотрудничество по проектам любых направлений на сайте Ворклансер";
                        break;
                        //MainController@freelancerIndex
                    case 'users.country':
                        $this->data->{'title'} = _t('widgets.meta.Uslugi_luchshih_specialistov_v',
                            "Услуги лучших специалистов в ") . data()->data->country->name_in_ru .
                            ": цены, отзывы, примеры работ | сервис заказа услуг Worklancer";

                        $items = [];
                        foreach (Categories::getMainCategorues() as $cat) {
                            $items[] = $cat->name;
                        }

                        shuffle($items);

                        $this->data->{'description'} = _t('widgets.meta.Nayti_specialista_v',
                            "Найти специалиста в ") . data()->data->country->name_in_ru . _t('widgets.meta.Vorklanser,_uslugi,_ceny',
                            ". Ворклансер, услуги, цены") . (empty($items) ? '' : ', ' . implode('&#9989; ',
                            $items));
                        break;
                    case 'users.region':
                        $this->data->{'title'} = _t('widgets.meta.Uslugi_luchshih_specialistov_v',
                            "Услуги лучших специалистов в ") . (data()->data->region->city ? data()->data->
                            region->city->name_in_ru . ' и ' : '') . data()->data->region->name_in_ru .
                            ": цены, отзывы, примеры работ | сервис заказа услуг Worklancer";

                        $items = [];
                        foreach (Categories::getMainCategorues() as $cat) {
                            $items[] = $cat->name;
                        }

                        shuffle($items);

                        $this->data->{'description'} = _t('widgets.meta.Nayti_specialista_v',
                            "Найти специалиста в ") . data()->data->region->name_in .
                            ": &#11088; лучшие специалисты в " . (data()->data->region->city ? data()->data->
                            region->city->name_in_ru . ' и ' : '') . data()->data->region->name_in_ru . _t('widgets.meta.Vorklanser,_uslugi,_ceny',
                            ". Ворклансер, услуги, цены") . (empty($items) ? '' : ', ' . implode('&#9989; ',
                            $items));
                        break;
                    case 'users.area':
                        $this->data->{'title'} = _t('widgets.meta.Uslugi_luchshih_specialistov_v',
                            "Услуги лучших специалистов в ") . data()->data->area->name_in_ru .
                            ". Цены, отзывы, примеры работ | сервис заказа услуг Worklancer";

                        $items = [];
                        foreach (Categories::getMainCategorues() as $cat) {
                            $items[] = $cat->name;
                        }

                        shuffle($items);

                        $this->data->{'description'} = _t('widgets.meta.Nayti_specialista_v',
                            "Найти специалиста в ") . data()->data->area->name_in_ru .
                            ": &#11088; лучшие специалисты в " . data()->data->area->name_in_ru . _t('widgets.meta.Vorklanser,_uslugi,_ceny',
                            ". Ворклансер, услуги, цены") . (empty($items) ? '' : ', ' . implode('&#9989; ',
                            $items));
                        break;
                        //MainController@freelancerIndex
                    case 'users.city':
                        $indexing = Base::dataModel('CityUsersIndexings', 'arrayCityUsersIndexings', ['city_id' =>
                            data()->data->city->id]);

                        if ($indexing) {
                            if (data()->data->city->type == '1') {
                                if ($indexing->status == 'no') {
                                    $this->data->{'robots'} = "noindex, nofollow";
                                }
                            } else {
                                if ($indexing->status != 'yes') {
                                    $this->data->{'robots'} = "noindex, nofollow";
                                }
                            }
                        } else {
                            if (data()->data->city->type == '1') {
                            } else {
                                $this->data->{'robots'} = "noindex, nofollow";
                            }
                        }

                        $this->data->{'title'} = _t('widgets.meta.Uslugi_v', "Услуги в ") . data()->
                            data->city->name() .
                            ". Найти специалиста: каталог. Услуги лучших специалистов в " . data()->data->
                            city->name() . ": цены, отзывы, примеры работ | сервис заказа услуг Worklancer";

                        $items = [];
                        foreach (Categories::getMainCategorues() as $cat) {
                            $items[] = $cat->name;
                        }

                        shuffle($items);

                        $this->data->{'description'} = _t('widgets.meta.Nayti_specialista',
                            'Найти специалиста ') . data()->data->city->name_in .
                            ": &#11088; лучшие специалисты в " . data()->data->city->name .
                            ". &#11088; Ворклансер, услуги, цены" . (empty($items) ? '' : ', ' . implode('&#9989; ',
                            $items));
                        break;
                        //MainController@freelancerIndex
                    case 'users.address':
                        $this->data->{'title'} = _t('widgets.meta.Uslugi_po_adresu', "Услуги по адресу ") .
                            data()->data->address->name .
                            ". Найти специалиста: каталог. Услуги лучших специалистов по адресу " . data()->
                            data->address->name .
                            ": цены, отзывы, примеры работ | сервис заказа услуг Worklancer";

                        $this->data->{'description'} = _t('widgets.meta.Nayti_specialista_po_adresu',
                            'Найти специалиста по адресу ') . data()->data->address->name .
                            ": &#11088; лучшие специалисты по адресу " . data()->data->address->name .
                            " &#9989; цены &#9989;отзывы &#9989; примеры работ в портфолио – надежное сотрудничество в работе по адресу " .
                            data()->data->address->name . _t('widgets.meta.na_sayte_Vorklanser',
                            " на сайте Ворклансер");
                        break;
                        //MainController@freelancerIndex
                    case 'users.country.category':
                        $indexing = Base::dataModel('CountryCategoryUsersIndexings',
                            'arrayCountryCategoryUsersIndexings', ['country_id' => data()->data->country->
                            id, 'category_id' => data()->data->category->id]);

                        if ($indexing) {
                            if ($indexing->status != 'yes') {
                                $this->data->{'robots'} = "noindex, nofollow";
                            }
                        } else {
                            $this->data->{'robots'} = "noindex, nofollow";
                        }

                        $this->data->{'title'} = _t('widgets.meta.Uslugi', "Услуги ") . data()->data->
                            category->name_ed_mr_od_vn . _t('widgets.meta.v', " в ") . data()->data->
                            country->name_in_ru . _t('widgets.meta.-_ceny,_otzyvy,_primery_rabot',
                            ". Цены, отзывы, примеры работ");

                        $items = [];
                        foreach (data()->data->category->childs as $cat) {
                            $items[] = $cat->name;
                        }

                        shuffle($items);

                        $this->data->{'description'} = _t('widgets.meta.Nayti', "Найти ") . data()->
                            data->category->name_ed_mr_od_vn . _t('widgets.meta.v', " в ") . data()->data->
                            country->name_in_ru . ". &#11088; Ворклансер, услуги, цены" . (empty($items) ?
                            '' : ', ' . implode('&#9989; ', $items));
                        break;
                    case 'users.region.category':
                        $indexing = Base::dataModel('RegionCategoryUsersIndexings',
                            'arrayRegionCategoryUsersIndexings', ['region_id' => data()->data->region->id,
                            'category_id' => data()->data->category->id]);

                        if ($indexing) {
                            if ($indexing->status != 'yes') {
                                $this->data->{'robots'} = "noindex, nofollow";
                            }
                        } else {
                            $this->data->{'robots'} = "noindex, nofollow";
                        }

                        $this->data->{'title'} = _t('widgets.meta.Uslugi', "Услуги ") . data()->data->
                            category->name_ed_mr_od_vn . _t('widgets.meta.v', " в ") . (data()->data->
                            region->city ? data()->data->region->city->name_in_ru . ' и ' : '') . data()->
                            data->region->name_in_ru . _t('widgets.meta.-_ceny,_otzyvy,_primery_rabot',
                            ". Цены, отзывы, примеры работ");

                        $items = [];
                        foreach (data()->data->category->childs as $cat) {
                            $items[] = $cat->name;
                        }

                        shuffle($items);

                        $this->data->{'description'} = _t('widgets.meta.Nayti', "Найти ") . data()->
                            data->category->name_ed_mr_od_vn . _t('widgets.meta.v', " в ") . (data()->data->
                            region->city ? data()->data->region->city->name_in_ru . ' и ' : '') . data()->
                            data->region->name_in_ru . ". &#11088; Ворклансер, услуги, цены" . (empty($items) ?
                            '' : ', ' . implode('&#9989; ', $items));
                        break;
                    case 'users.area.category':
                        $indexing = Base::dataModel('AreaCategoryUsersIndexings',
                            'arrayAreaCategoryUsersIndexings', ['area_id' => data()->data->area->id,
                            'category_id' => data()->data->category->id]);

                        if ($indexing) {
                            if ($indexing->status != 'yes') {
                                $this->data->{'robots'} = "noindex, nofollow";
                            }
                        } else {
                            $this->data->{'robots'} = "noindex, nofollow";
                        }

                        $this->data->{'title'} = _t('widgets.meta.Uslugi', "Услуги ") . data()->data->
                            category->name_ed_mr_od_vn . _t('widgets.meta.v', " в ") . data()->data->area->
                            name_in_ru . _t('widgets.meta.-_ceny,_otzyvy,_primery_rabot',
                            ". Цены, отзывы, примеры работ");

                        $items = [];
                        foreach (data()->data->category->childs as $cat) {
                            $items[] = $cat->name;
                        }

                        shuffle($items);

                        $this->data->{'description'} = _t('widgets.meta.Nayti', "Найти ") . data()->
                            data->category->name_ed_mr_od_vn . _t('widgets.meta.v', " в ") . data()->data->
                            area->name_in_ru . ". &#11088; Ворклансер, услуги, цены" . (empty($items) ? '' :
                            ', ' . implode('&#9989; ', $items));
                        break;
                    case 'users.city.category':
                        $indexing = Base::dataModel('CityCategoryUsersIndexings',
                            'arrayCityCategoryUsersIndexings', ['city_id' => data()->data->city->id,
                            'category_id' => data()->data->category->id]);

                        if ($indexing) {
                            if ($indexing->status != 'yes') {
                                $this->data->{'robots'} = "noindex, nofollow";
                            }
                        } else {
                            $this->data->{'robots'} = "noindex, nofollow";
                        }

                        $this->data->{'title'} = _t('widgets.meta.Uslugi', "Услуги ") . data()->data->
                            category->name_ed_mr_od_vn . _t('widgets.meta.v', " в ") . data()->data->city->
                            name() . _t('widgets.meta.-_ceny,_otzyvy,_primery_rabot',
                            ". Цены, отзывы, примеры работ");

                        $items = [];
                        foreach (data()->data->category->childs as $cat) {
                            $items[] = $cat->name;
                        }

                        shuffle($items);

                        $this->data->{'description'} = _t('widgets.meta.Nayti', "Найти ") . data()->
                            data->category->name_ed_mr_od_vn . _t('widgets.meta.v', " в ") . data()->data->
                            city->name() . ". &#11088; Ворклансер, услуги, цены" . (empty($items) ? '' :
                            ', ' . implode('&#9989; ', $items));
                        break;
                    case 'users.category':
                        $this->data->{'title'} = _t('widgets.meta.Uslugi', "Услуги ") . data()->data->
                            category->name_ed_mr_od_vn . _t('widgets.meta.-_ceny,_otzyvy,_primery_rabot',
                            ". Цены, отзывы, примеры работ");

                        $items = [];
                        foreach (data()->data->category->childs as $cat) {
                            $items[] = $cat->name;
                        }

                        shuffle($items);

                        $this->data->{'description'} = _t('widgets.meta.Nayti', "Найти ") . data()->
                            data->category->name_ed_mr_od_vn . ". &#11088; Ворклансер, услуги, цены" . (empty
                            ($items) ? '' : ', ' . implode('&#9989; ', $items));
                        break;
                        //MainController@freelancerIndex
                    case 'projects.index':
                        $this->data->{'title'} =
                            "Работа, подработка. Найти работу онлайн в Интернете: заказы, цены | сервис онлайн работы Worklancer";

                        $items = [];
                        foreach (Categories::getMainCategorues() as $cat) {
                            $items[] = $cat->name;
                        }

                        shuffle($items);

                        $this->data->{'description'} =
                            "Найти работу онлайн. &#11088; Ворклансер, задания, фриланс" . (empty($items) ?
                            '' : ', ' . implode('&#9989; ', $items));
                        break;
                        //ProjectsController@projectIndex
                    case 'projects.country':
                        $this->data->{'title'} = _t('widgets.meta.Rabota,_podrabotka_v',
                            "Работа, подработка в ") . data()->data->country->name_in_ru . _t('widgets.meta.Rabota_v',
                            ". Работа в ") . data()->data->country->name_in_ru .
                            ": цены, задачи, отзывы | биржа работы Worklancer";

                        $items = [];
                        foreach (Categories::getMainCategorues() as $cat) {
                            $items[] = $cat->name;
                        }

                        shuffle($items);

                        $this->data->{'description'} = _t('widgets.meta.Nayti_rabotu_onlayn_v',
                            "Найти работу онлайн в ") . data()->data->country->name_in_ru .
                            ". &#11088; Ворклансер, задания, фриланс" . (empty($items) ? '' : ', ' . implode
                            ('&#9989; ', $items));
                        break;
                        //ProjectsController@projectIndex
                    case 'projects.area':
                        $this->data->{'title'} = _t('widgets.meta.Rabota,_podrabotka_v',
                            "Работа, подработка в ") . data()->data->area->name_in_ru . _t('widgets.meta.Rabota_v',
                            ". Работа в ") . data()->data->area->name_in_ru .
                            ": цены, задачи, отзывы | биржа работы Worklancer";

                        $items = [];
                        foreach (Categories::getMainCategorues() as $cat) {
                            $items[] = $cat->name;
                        }

                        shuffle($items);

                        $this->data->{'description'} = _t('widgets.meta.Nayti_rabotu_onlayn_v',
                            "Найти работу онлайн в ") . data()->data->area->name_in_ru .
                            ". &#11088; Ворклансер, задания, фриланс" . (empty($items) ? '' : ', ' . implode
                            ('&#9989; ', $items));
                        break;
                    case 'projects.region':
                        $this->data->{'title'} = _t('widgets.meta.Rabota,_podrabotka_v',
                            "Работа, подработка в ") . (data()->data->region->city ? data()->data->region->
                            city->name_in_ru . ' и ' : '') . data()->data->region->name_in_ru .
                            ". Цены, задачи, отзывы | биржа работы Worklancer";

                        $items = [];
                        foreach (Categories::getMainCategorues() as $cat) {
                            $items[] = $cat->name;
                        }

                        shuffle($items);

                        $this->data->{'description'} = _t('widgets.meta.Nayti_rabotu_onlayn_v',
                            "Найти работу онлайн в ") . (data()->data->region->city ? data()->data->region->
                            city->name_in_ru . ' и ' : '') . data()->data->region->name_in_ru .
                            ". &#11088; Ворклансер, задания, фриланс" . (empty($items) ? '' : ', ' . implode
                            ('&#9989; ', $items));
                        break;
                    case 'projects.city':
                        $indexing = Base::dataModel('CityJobsIndexings', 'arrayCityJobsIndexings', ['city_id' =>
                            data()->data->city->id]);

                        if ($indexing) {
                            if (data()->data->city->type == '1') {
                                if ($indexing->status == 'no') {
                                    $this->data->{'robots'} = "noindex, nofollow";
                                }
                            } else {
                                if ($indexing->status != 'yes') {
                                    $this->data->{'robots'} = "noindex, nofollow";
                                }
                            }
                        } else {
                            if (data()->data->city->type == '1') {
                            } else {
                                $this->data->{'robots'} = "noindex, nofollow";
                            }
                        }

                        $this->data->{'title'} = _t('widgets.meta.Rabota,_podrabotka_v',
                            "Работа, подработка в ") . data()->data->city->name() . _t('widgets.meta.Rabota_dlya_specialistov_v',
                            ". Работа для специалистов в ") . data()->data->city->name() .
                            ": цены, задачи, отзывы | биржа работы Worklancer";

                        $items = [];
                        foreach (Categories::getMainCategorues() as $cat) {
                            $items[] = $cat->name;
                        }

                        shuffle($items);

                        $this->data->{'description'} = _t('widgets.meta.Nayti_rabotu_onlayn_v',
                            "Найти работу онлайн в ") . data()->data->city->name() .
                            ". &#11088; Для специалистов в " . data()->data->city->name() . _t('widgets.meta.Vorklanser,_zadaniya,_frilans',
                            ". Ворклансер, задания, фриланс") . (empty($items) ? '' : ', ' . implode('&#9989; ',
                            $items));
                        break;
                        //ProjectsController@projectIndex
                    case 'projects.address':
                        $this->data->{'title'} = _t('widgets.meta.Rabota,_podrabotka_po_adresu',
                            "Работа, подработка по адресу ") . data()->data->address->name . _t('widgets.meta.Rabota_dlya_specialistov_po_adresu',
                            ". Работа для специалистов по адресу ") . data()->data->address->name .
                            ": цены, задачи, отзывы | биржа работы Worklancer";

                        $this->data->{'description'} = _t('widgets.meta.Nayti_rabotu_onlayn_po_adresu',
                            "Найти работу онлайн по адресу ") . data()->data->address->name .
                            ". &#11088; Для специалистов по адресу " . data()->data->address->name .
                            ": &#9989; своевременная оплата &#9989; выполнение работы в нужный срок &#9989; отзывы о заказчиках и исполнителях &#9989; интересные проекты и услуги — надежный поиск работы | сайт онлайн работы Ворклансер";
                        break;
                        //ProjectsController@projectIndex
                    case 'projects.country.category':
                        $indexing = Base::dataModel('CountryCategoryJobsIndexings',
                            'arrayCountryCategoryJobsIndexings', ['country_id' => data()->data->country->id,
                            'category_id' => data()->data->category->id]);

                        if ($indexing) {
                            if ($indexing->status != 'yes') {
                                $this->data->{'robots'} = "noindex, nofollow";
                            }
                        } else {
                            $this->data->{'robots'} = "noindex, nofollow";
                        }

                        $this->data->{'title'} = _t('widgets.meta.Rabota,_podrabotka_dlya',
                            "Работа, подработка для ") . data()->data->category->name_ed_mr_od_vn . _t('widgets.meta.v',
                            " в ") . data()->data->country->name_in_ru . _t('widgets.meta.-Ceny,_zadachi,_otzyvy',
                            " . Цены, задачи, отзывы");

                        $items = [];
                        foreach (data()->data->category->childs as $cat) {
                            $items[] = $cat->name;
                        }

                        shuffle($items);

                        $this->data->{'description'} = _t('widgets.meta.Nayti_rabotu_onlayn_dlya',
                            "Найти работу онлайн для ") . data()->data->category->name_ed_mr_od_vn . _t('widgets.meta.v',
                            " в ") . data()->data->country->name_in_ru .
                            ". &#11088; Ворклансер, задания, фриланс" . (empty($items) ? '' : ', ' . implode
                            ('&#9989; ', $items));
                        ;
                        break;
                    case 'projects.region.category':
                        $indexing = Base::dataModel('RegionCategoryJobsIndexings',
                            'arrayRegionCategoryJobsIndexings', ['region_id' => data()->data->region->id,
                            'category_id' => data()->data->category->id]);

                        if ($indexing) {
                            if ($indexing->status != 'yes') {
                                $this->data->{'robots'} = "noindex, nofollow";
                            }
                        } else {
                            $this->data->{'robots'} = "noindex, nofollow";
                        }

                        $this->data->{'title'} = _t('widgets.meta.Rabota,_podrabotka_dlya',
                            "Работа, подработка для ") . data()->data->category->name_ed_mr_od_vn . _t('widgets.meta.v',
                            " в ") . (data()->data->region->city ? data()->data->region->city->name_in_ru .
                            ' и ' : '') . data()->data->region->name_in_ru . _t('widgets.meta.-Ceny,_zadachi,_otzyvy',
                            ". Цены, задачи, отзывы");

                        $items = [];
                        foreach (data()->data->category->childs as $cat) {
                            $items[] = $cat->name;
                        }

                        shuffle($items);

                        $this->data->{'description'} = _t('widgets.meta.Nayti_rabotu_onlayn_dlya',
                            "Найти работу онлайн для ") . data()->data->category->name_ed_mr_od_vn . _t('widgets.meta.v',
                            " в ") . (data()->data->region->city ? data()->data->region->city->name_in_ru .
                            ' и ' : '') . data()->data->region->name_in_ru .
                            ". &#11088; Ворклансер, задания, фриланс" . (empty($items) ? '' : ', ' . implode
                            ('&#9989; ', $items));
                        ;
                        break;
                    case 'projects.area.category':
                        $indexing = Base::dataModel('AreaCategoryJobsIndexings',
                            'arrayAreaCategoryJobsIndexings', ['area_id' => data()->data->area->id,
                            'category_id' => data()->data->category->id]);

                        if ($indexing) {
                            if ($indexing->status != 'yes') {
                                $this->data->{'robots'} = "noindex, nofollow";
                            }
                        } else {
                            $this->data->{'robots'} = "noindex, nofollow";
                        }

                        $this->data->{'title'} = _t('widgets.meta.Rabota,_podrabotka_dlya',
                            "Работа, подработка для ") . data()->data->category->name_ed_mr_od_vn . _t('widgets.meta.v',
                            " в ") . data()->data->area->name_in_ru . _t('widgets.meta.-Ceny,_zadachi,_otzyvy',
                            ". Цены, задачи, отзывы");

                        $items = [];
                        foreach (data()->data->category->childs as $cat) {
                            $items[] = $cat->name;
                        }

                        shuffle($items);

                        $this->data->{'description'} = _t('widgets.meta.Nayti_rabotu_onlayn_dlya',
                            "Найти работу онлайн для ") . data()->data->category->name_ed_mr_od_vn . _t('widgets.meta.v',
                            " в ") . data()->data->area->name_in_ru .
                            ". &#11088; Ворклансер, задания, фриланс" . (empty($items) ? '' : ', ' . implode
                            ('&#9989; ', $items));
                        ;
                        break;
                    case 'projects.city.category':
                        $indexing = Base::dataModel('CityCategoryJobsIndexings',
                            'arrayCityCategoryJobsIndexings', ['city_id' => data()->data->city->id,
                            'category_id' => data()->data->category->id]);

                        if ($indexing) {
                            if ($indexing->status != 'yes') {
                                $this->data->{'robots'} = "noindex, nofollow";
                            }
                        } else {
                            $this->data->{'robots'} = "noindex, nofollow";
                        }

                        $this->data->{'title'} = _t('widgets.meta.Rabota,_podrabotka_dlya',
                            "Работа, подработка для ") . data()->data->category->name_ed_mr_od_vn . _t('widgets.meta.v',
                            " в ") . data()->data->city->name() . _t('widgets.meta.-Ceny,_zadachi,_otzyvy',
                            ". Цены, задачи, отзывы");

                        $items = [];
                        foreach (data()->data->category->childs as $cat) {
                            $items[] = $cat->name;
                        }

                        shuffle($items);

                        $this->data->{'description'} = _t('widgets.meta.Nayti_rabotu_onlayn_dlya',
                            "Найти работу онлайн для ") . data()->data->category->name_ed_mr_od_vn . _t('widgets.meta.v',
                            " в ") . data()->data->city->name() . ". &#11088; Ворклансер, задания, фриланс" . (empty
                            ($items) ? '' : ', ' . implode('&#9989; ', $items));
                        ;
                        break;
                    case 'projects.category':


                        $this->data->{'title'} = _t('widgets.meta.Rabota,_podrabotka_dlya',
                            "Работа, подработка для ") . data()->data->category->name_ed_mr_od_vn . _t('widgets.meta.-Ceny,_zadachi,_otzyvy',
                            ". Цены, задачи, отзывы");

                        $items = [];
                        foreach (data()->data->category->childs as $cat) {
                            $items[] = $cat->name;
                        }

                        shuffle($items);

                        $this->data->{'description'} = _t('widgets.meta.Nayti_rabotu_onlayn_dlya',
                            "Найти работу онлайн для ") . data()->data->category->name_ed_mr_od_vn .
                            ". &#11088; Ворклансер, задания, фриланс" . (empty($items) ? '' : ', ' . implode
                            ('&#9989; ', $items));
                        break;
                        //ProjectsController@projectIndex
                    case 'projects.page':
                        //ProjectsController@projectPage
                    case 'projects.page.code':
                        $categories = [];

                        foreach (data()->data->project->categories as $category) {
                            $categories[] = $category->name;
                        }

                        shuffle($categories);


                        $this->data->{'title'} = ucfirst(data()->data->project->title) .
                            " — Работа по направлению: " . implode('&#9989; ', $categories);

                        $this->data->{'description'} = str_limit(ucfirst(data()->data->project->
                            description), 250, "...");
                        break;
                        //MainController@projectPage
                    case 'projects.create':
                        $this->data->{'title'} = _t('widgets.meta.Sozdanie_zadaniya', "Создание задания");

                        $this->data->{'description'} = _t('widgets.meta.Opishite_chto_vam_nuzhno_sdelat_i_ukazhite_vr-5dHF',
                            "Опишите что вам нужно сделать и укажите время и цену заказа");
                        break;
                        //ProjectsController@projectCreate
                    case 'projects.create.success':
                        $this->data->{'title'} = _t('widgets.meta.Zakaz_uspeshno_sozdano',
                            "Заказ успешно создано");

                        $this->data->{'description'} = _t('widgets.meta.Zakaz_uspeshno_sozdan',
                            "Заказ успешно создан");
                        break;
                        //ProjectsController@projectCreateSuccess
                    case 'projects.edit':
                        $this->data->{'title'} = ucfirst(data()->data->project->title) .
                            " — Обновление заказа";

                        $this->data->{'description'} = str_limit(ucfirst(data()->data->project->
                            description), 250, "...");
                        break;
                        //ProjectsController@projectEdit
                    case 'feedback.index':
                        $this->data->{'title'} = _t('widgets.meta.Sluzhba_podderzhki',
                            "Служба поддержки");

                        $this->data->{'description'} = _t('widgets.meta.Napishete_svoy_vopros',
                            "Напишете свой вопрос");
                        break;
                        //MainController@feedbackIndex
                    case 'users.portfolio.index':
                        $this->data->{'title'} = data()->data->portfolio->title;

                        $this->data->{'description'} = str_limit(data()->data->portfolio->description,
                            250);
                        //UsersController@userReviews
                        break;
                        //UsersController@userPortfolio
                    case 'terms.index':
                        $this->data->{'title'} = _t('widgets.meta.Pravila_sayta_Worklancer',
                            "Правила сайта Worklancer");

                        $this->data->{'description'} = _t('widgets.meta.Pravila_sayta_Worklancer',
                            "Правила сайта Worklancer");
                        break;
                        //MainController@termsIndex
                    case 'policy.index':
                        $this->data->{'title'} = _t('widgets.meta.Politika_konfidencialnosti',
                            "Политика конфиденциальности");

                        $this->data->{'description'} = _t('widgets.meta.Politika_konfidencialnosti',
                            "Политика конфиденциальности");
                        break;
                    case 'dmca.index':
                        $this->data->{'title'} = _t('widgets.meta.Avtorskie_prava', "Авторские права");

                        $this->data->{'description'} = _t('widgets.meta.Avtorskie_prava',
                            "Авторские права");
                        break;
                    case 'codeofconduct.index':
                        $this->data->{'title'} = _t('widgets.meta.Pravila_povedeniya',
                            "Правила поведения");

                        $this->data->{'description'} = _t('widgets.meta.Pravila_povedeniya',
                            "Правила поведения");
                        break;
                    case 'orders.delete':
                        //OrdersController@ordersDelete
                        break;
                    case 'orders.select_freelance':
                        //ProjectsController@projectSelectFreelance
                        break;
                    case 'orders.delete_freelance':
                        //ProjectsController@projectDeleteFreelance
                        break;
                    case 'forum.index':
                        $this->data->{'title'} = _t('widgets.meta.Forum_soobshchestva_na_frilans_ploshchadke_Wo-8HgE',
                            "Форум сообщества на фриланс площадке Worklancer.net");

                        $this->data->{'description'} =
                            "Форум фрилансеров: вопросы, ответы и обсуждение работы. Сообщество фриланс специалистов на бирже Ворклансер! &#11088; Лучшый онлайн сервис заказа услуг.";

                        break;
                    case 'users.portfolios':
                        $this->data->{'title'} = data()->data->user->name . ' ' . data()->data->user->
                            surname . ', ' . data()->data->user->login . _t('widgets.meta.-_Portfolio_polzovatelya_na_Worklancer_net',
                            " - Портфолио пользователя на Worklancer.net");

                        $categories = [];

                        foreach (data()->data->user->categories as $category) {
                            $categories[] = $category->name;
                        }

                        shuffle($categories);


                        $this->data->{'description'} = _t('widgets.meta.Portfolio_polzovatelya',
                            "Портфолио пользователя ") . data()->data->user->name . ' ' . data()->data->
                            user->surname . ', ' . data()->data->user->login . ' &#11088;' . (empty($categories) ?
                            '' : ' ' . implode('&#9989; ', $categories) . ' &#9989;') .
                            ' отзывы &#9989; портфолио &#9989; резюме — найти специалиста на любую работу | Ворклансер';
                        //ProjectsController@projectSelectFreelance
                        break;
                    case 'blog.index':
                        $this->data->{'title'} = _t('widgets.meta.Blog_o_rabote_i_deyatelnost-hJZC',
                            "Блог о работе и зайнятости на фриланс площадке Worklancer.net");

                        $this->data->{'description'} =
                            "&#9989; Интересные статьи о работе в разных сферах деятельности &#9989; Преимущества и недостатки професий &#11088; События биржи фриланса Worklancer &#9889; Всё самое полезное для фрилансеров и заказчиков | биржа работы Ворклансер &#9996;";

                        break;
                    case 'blog.category':
                        $this->data->{'title'} = "Блог. Раздел: " . data()->data->category->name . _t('widgets.meta.-_na_frilans_ploshchadke_Worklancer_net',
                            " -  на фриланс площадке Worklancer.net");

                        $this->data->{'description'} = "Раздел: " . data()->data->category->name .
                            "&#9989; Интересные статьи о работе в разных сферах деятельности &#9989; Преимущества и недостатки професий &#11088; События биржи фриланса Worklancer &#9889; Всё самое полезное для фрилансеров и заказчиков | биржа работы Ворклансер &#9996;";

                        break;
                    case 'forum.theme':
                        $this->data->{'title'} = data()->data->theme->name . " — Форум";

                        $this->data->{'description'} = str_limit(str_replace(["\n"], '', strip_tags(data
                            ()->data->theme->text)), 100, '') . _t('widgets.meta.Voprosy_i_otvety_na_forume_Worklancer',
                            " .... Вопросы и ответы на форуме Worklancer.");

                        break;
                    case 'forum.category':
                        $this->data->{'title'} = "Форум. Раздел: " . data()->data->category->name . _t('widgets.meta.-_na_frilans_ploshchadke_Worklancer_net-CdAx',
                            " - на фриланс площадке Worklancer.net");

                        $this->data->{'description'} = "Форум. Раздел: " . data()->data->category->name .
                            " -  вопросы, ответы и обсуждение работы. Сообщество фриланс специалистов на бирже Ворклансер! &#11088; Лучшый онлайн сервис заказа услуг.";

                        break;
                    case 'blog.blog':
                        $this->data->{'title'} = data()->data->blog->title .
                            " | сервис услуг Worklancer";

                        $this->data->{'description'} = '&#9989; ' . str_limit(str_replace(["\n"], '',
                            strip_tags(data()->data->blog->text)), 200, '...') .
                            ". | сервис услуг Ворклансер";

                        break;
                    default:
                        $this->data->{'title'} =
                            "Работа Подработка. Сервис заказа услуг Worklancer — лучший сайт для заказа любых услуг";

                        $this->data->{'description'} =
                            "Лучшый онлайн сервис заказа услуг &#11088; для выполнения бытовых или рабочих задач. &#11088; Сервис для работы выпония любых задач &#11088; Официальный сайт услуг: &#9989; выполнение задач по найму &#9989; выполнение фриланс заказов &#9989; отзывы &#9989;";

                        break;
                }
            }
        }

        $uri = trim(trim(Route::now(), '/'));

        $webmasters = [];

        foreach (Base::dataModel('Webmaster', 'dataArrayWebmasterByUri', ['uri' => $uri,
            'limit' => 5]) as $webmaster) {
            $webmasters[] = $webmaster;
        }

        usort($webmasters, function ($a, $b) {
            if ($a->ctr == $b->ctr) {
                return 0; }

            if ($a->ctr < $b->ctr) {
                return 1; 
            } else {
                return - 1; 
            }
        });
        
        usort($webmasters, function ($a, $b) {
            if ($a->position == $b->position) {
                return 0; 
            }
            
            if ($a->position < $b->position) {
                return - 1; 
            } else {
                return 1; 
            }
        });

        if (count_items($webmasters)) {
            $titles = [];

            $title = $this->data->{'title'};

            foreach ($webmasters as $webmaster) {
                if (stripos(mb_strtolower($title), mb_strtolower($webmaster->query)) === false) {
                    $titles[] = mb_ucfirst($webmaster->query);
                }
            }

            if (count_items($titles)) {
                preg_match("#^.*?\.#sui", $title, $match);

                if ($match && isset($match[0])) {
                    $title = str_replace($match[0], $match[0] . ' ' . implode('. ', $titles) . '.',
                        $title);
                } else {
                    $title = implode('. ', $titles) . '. ' . $title;
                }
            }

            $this->data->{'title'} = $title;
        }

        //$this->data->{'title'} = str_limit($this->data->{'title'}, 80);

        $this->data->{'description'} = str_limit($this->data->{'description'}, 150);

        //$this->data->{'robots'} = "noodp, noydir, noyaca";

        if (data()->dbDomain) {
            if (!$this->data->{'title'} && data()->dbDomain->title)
                $this->data->{'title'} = data()->dbDomain->title;
            if (!$this->data->{'description'} && data()->dbDomain->description)
                $this->data->{'description'} = data()->dbDomain->description;
            if (!$this->data->{'keywords'} && data()->dbDomain->keywords)
                $this->data->{'keywords'} = data()->dbDomain->keywords;
            if (!$this->data->{'robots'} && data()->dbDomain->robots)
                $this->data->{'robots'} = data()->dbDomain->robots;
        }

        if (data()->dbPage) {
            if (data()->dbPage->title)
                $this->data->{'title'} = data()->dbPage->title;
            if (data()->dbPage->description)
                $this->data->{'description'} = data()->dbPage->description;
            if (data()->dbPage->keywords)
                $this->data->{'keywords'} = data()->dbPage->keywords;
            if (data()->dbPage->robots)
                $this->data->{'robots'} = data()->dbPage->robots;
        }

        $this->data->{'og:title'} = $this->data->{'title'};
        // <meta property="og:title" content="Фриланс. Фриланс биржа Weblancer — лучший сайт для поиска фрилансеров и удаленной работы" />

        $this->data->{'og:type'} = "website";
        // <meta property="og:type" content="website" />

        $this->data->{'og:image'} = $this->data->{'image_src'};
        // <meta property="og:image" content="https://www.weblancer.net/img/favicons/android-chrome-256x256.png" />

        $this->data->{'og:image:width'} = null;
        // <meta property="og:image:width" content="1200" />

        $this->data->{'og:image:height'} = null;
        // <meta property="og:image:height" content="1200" />

        $this->data->{'og:url'} = Route::now([], [], false);
        // <meta property="og:url" content="https://www.weblancer.net" />

        $this->data->{'og:description'} = $this->data->{'description'};
        // <meta property="og:description" content="Лучшая фриланс биржа &amp;amp;#11088; для поиска исполнителей и удаленной работы. &amp;amp;#11088; Фриланс для любых проектов &amp;amp;#11088; Официальный сайт фрилансеров: &amp;amp;#9989; выполнение фриланс заказов &amp;amp;#9989; отзывы &amp;amp;#9989; лучшие вакансии &amp;am..." />

        $this->data->{'og:site_name'} = "Лучшый сервис заказа услуг — Worklancer.net";
        // <meta property="og:site_name" content="Лучшая биржа фриланса — Weblancer.net" />


        $this->data->{'twitter:card'} = "summary_large_image";
        // <meta name="twitter:card" content="summary_large_image" />

        $this->data->{'twitter:site'} = "@Worklancer_net";
        // <meta name="twitter:site" content="@weblancer_net" />

        $this->data->{'twitter:title'} = $this->data->{'og:title'};
        // <meta name="twitter:title" content="Фриланс. Фриланс биржа Weblancer — лучший сайт для поиска фрилансеров и удаленной работы" />

        $this->data->{'twitter:description'} = $this->data->{'og:description'};
        // <meta name="twitter:description" content="Лучшая фриланс биржа &amp;amp;#11088; для поиска исполнителей и удаленной работы. &amp;amp;#11088; Фриланс для любых проектов &amp;amp;#11088; Официальный сайт фрилансеров: &amp;amp;#9989; выполнение ..." />

        $this->data->{'twitter:image'} = $this->data->{'og:image'};
        // <meta name="twitter:image" content="https://www.weblancer.net/img/favicons/android-chrome-256x256.png" />

        if (data()->dbDomain) {
            if (!$this->data->{'viewport'} && data()->dbDomain->viewport)
                $this->data->{'viewport'} = data()->dbDomain->viewport;
            if (!$this->data->{'country-code'} && data()->dbDomain->country_code)
                $this->data->{'country-code'} = data()->dbDomain->country_code;
            if (!$this->data->{'canonical'} && data()->dbDomain->canonical)
                $this->data->{'canonical'} = data()->dbDomain->canonical;
            if (!$this->data->{'prev'} && data()->dbDomain->prev)
                $this->data->{'prev'} = data()->dbDomain->prev;
            if (!$this->data->{'next'} && data()->dbDomain->next)
                $this->data->{'next'} = data()->dbDomain->next;
            if (!$this->data->{'apple-touch-icon'} && data()->dbDomain->apple_touch_icon)
                $this->data->{'apple-touch-icon'} = data()->dbDomain->apple_touch_icon;
            if (!$this->data->{'manifest'} && data()->dbDomain->manifest)
                $this->data->{'manifest'} = data()->dbDomain->manifest;
            if (!$this->data->{'mask-icon'} && data()->dbDomain->mask_icon)
                $this->data->{'mask-icon'} = data()->dbDomain->mask_icon;
            if (!$this->data->{'image_src'} && data()->dbDomain->image_src)
                $this->data->{'image_src'} = data()->dbDomain->image_src;
            if (!$this->data->{'og:title'} && data()->dbDomain->og_title)
                $this->data->{'og:title'} = data()->dbDomain->og_title;
            if (!$this->data->{'og:type'} && data()->dbDomain->og_type)
                $this->data->{'og:type'} = data()->dbDomain->og_type;
            if (!$this->data->{'og:image'} && data()->dbDomain->og_image)
                $this->data->{'og:image'} = data()->dbDomain->og_image;
            if (!$this->data->{'og:image:width'} && data()->dbDomain->og_image_width)
                $this->data->{'og:image:width'} = data()->dbDomain->og_image_width;
            if (!$this->data->{'og:image:height'} && data()->dbDomain->og_image_height)
                $this->data->{'og:image:height'} = data()->dbDomain->og_image_height;
            if (!$this->data->{'og:url'} && data()->dbDomain->og_url)
                $this->data->{'og:url'} = data()->dbDomain->og_url;
            if (!$this->data->{'og:description'} && data()->dbDomain->og_description)
                $this->data->{'og:description'} = data()->dbDomain->og_description;
            if (!$this->data->{'og:site_name'} && data()->dbDomain->og_site_name)
                $this->data->{'og:site_name'} = data()->dbDomain->og_site_name;
            if (!$this->data->{'twitter:card'} && data()->dbDomain->twitter_card)
                $this->data->{'twitter:card'} = data()->dbDomain->twitter_card;
            if (!$this->data->{'twitter:site'} && data()->dbDomain->twitter_site)
                $this->data->{'twitter:site'} = data()->dbDomain->twitter_site;
            if (!$this->data->{'twitter:title'} && data()->dbDomain->twitter_title)
                $this->data->{'twitter:title'} = data()->dbDomain->twitter_title;
            if (!$this->data->{'twitter:description'} && data()->dbDomain->
                twitter_description)
                $this->data->{'twitter:description'} = data()->dbDomain->twitter_description;
            if (!$this->data->{'twitter:image'} && data()->dbDomain->twitter_image)
                $this->data->{'twitter:image'} = data()->dbDomain->twitter_image;
        }

        if (data()->dbPage) {
            if (data()->dbPage->viewport)
                $this->data->{'viewport'} = data()->dbPage->viewport;
            if (data()->dbPage->country_code)
                $this->data->{'country-code'} = data()->dbPage->country_code;
            if (data()->dbPage->canonical)
                $this->data->{'canonical'} = data()->dbPage->canonical;
            if (data()->dbPage->prev)
                $this->data->{'prev'} = data()->dbPage->prev;
            if (data()->dbPage->next)
                $this->data->{'next'} = data()->dbPage->next;
            if (data()->dbPage->apple_touch_icon)
                $this->data->{'apple-touch-icon'} = data()->dbPage->apple_touch_icon;
            if (data()->dbPage->manifest)
                $this->data->{'manifest'} = data()->dbPage->manifest;
            if (data()->dbPage->mask_icon)
                $this->data->{'mask-icon'} = data()->dbPage->mask_icon;
            if (data()->dbPage->image_src)
                $this->data->{'image_src'} = data()->dbPage->image_src;
            if (data()->dbPage->og_title)
                $this->data->{'og:title'} = data()->dbPage->og_title;
            if (data()->dbPage->og_type)
                $this->data->{'og:type'} = data()->dbPage->og_type;
            if (data()->dbPage->og_image)
                $this->data->{'og:image'} = data()->dbPage->og_image;
            if (data()->dbPage->og_image_width)
                $this->data->{'og:image:width'} = data()->dbPage->og_image_width;
            if (data()->dbPage->og_image_height)
                $this->data->{'og:image:height'} = data()->dbPage->og_image_height;
            if (data()->dbPage->og_url)
                $this->data->{'og:url'} = data()->dbPage->og_url;
            if (data()->dbPage->og_description)
                $this->data->{'og:description'} = data()->dbPage->og_description;
            if (data()->dbPage->og_site_name)
                $this->data->{'og:site_name'} = data()->dbPage->og_site_name;
            if (data()->dbPage->twitter_card)
                $this->data->{'twitter:card'} = data()->dbPage->twitter_card;
            if (data()->dbPage->twitter_site)
                $this->data->{'twitter:site'} = data()->dbPage->twitter_site;
            if (data()->dbPage->twitter_title)
                $this->data->{'twitter_:title'} = data()->dbPage->twitter_title;
            if (data()->dbPage->twitter_description)
                $this->data->{'twitter:description'} = data()->dbPage->twitter_description;
            if (data()->dbPage->twitter_image)
                $this->data->{'twitter:image'} = data()->dbPage->twitter_image;
        }
    }
}
