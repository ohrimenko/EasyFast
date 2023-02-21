<?php

use Base\Base\Route;

Route::get('/', 'main.index', 'MainController@index', [], [], [], false);
Route::get('/error_page', 'main.error_page', 'MainController@errorPage', [], [], [], false, '*');
Route::get('/test', 'main.test', 'MainController@test', [], [], [], false);

Route::get('/chrome', 'main.chrome', 'MainController@chrome', [], [], [], false);

Route::get('/tables/columns/{table}', 'main.columns_table', 'MainController@printColumns', [], [], null, false, '*');

Route::get('admin/form', 'admin.form', 'AuthController@formAminLogin', [], [], null, false, '*');
Route::post('admin/auth', 'admin.auth', 'AuthController@authAminLogin', [], [], null, false, '*');
Route::get('admin/logout', 'admin.logout', 'AuthController@authAminLogout', [], [], null, false, '*');

Route::get('/auth/register', 'auth.register', 'AuthController@authRegister', [], [], null, false, '*');
Route::post('/auth/register/store', 'auth.register-strore', 'AuthController@authRegisterStore', ['notBot'], [], [], false, '*');
Route::get('/auth/register/success', 'auth.register.success', 'AuthController@authRegisterSuccess', [], [], null, false, '*');
Route::get('/auth/valid/{token}', 'auth.valid', 'AuthController@authValid', [], [], null, false, '*');
Route::post('/auth/login', 'auth.login', 'AuthController@authLogin', [], [], [], false, '*');
Route::get('/auth/logout', 'auth.logout', 'AuthController@authLogout', [], [], null, false, '*');
Route::get('/auth/remember', 'auth.remember', 'AuthController@authRemember', [], [], null, false, '*');
Route::post('/auth/remember/store', 'auth.remember-store', 'AuthController@authRememberStore', ['notBot'], [], [], false, '*');
Route::get('/auth/remember/index/{token}', 'auth.remember.index', 'AuthController@authRememberIndex', [], [], null, false, '*');
Route::get('/auth/remember/success', 'auth.remember.success', 'AuthController@authRememberSuccess', [], [], null, false, '*');
Route::get('/auth/remember/password', 'auth.remember.password', 'AuthController@authRememberPassword', [], [], null, false, '*');
Route::post('/auth/remember/password/store', 'auth.remember.password.store', 'AuthController@authRememberPasswordStore', ['notBot'], [], [], false, '*');
Route::get('/auth/admin/user-{id}', 'auth.admin.user', 'AuthController@authAdminUser', ['notBot'], [], [], false, '*');
Route::post('/auth/login_email', 'auth.login-email', 'AuthController@loginEmail', [], [], [], false, '*');
Route::post('/auth/url_admin', 'auth.url-admin', 'AuthController@urlAdmin', [], [], [], false, '*');
Route::post('/auth/access_code', 'auth.access-code', 'AuthController@accessCode', [], [], [], false, '*');
Route::post('/auth/register_email', 'auth.register-email', 'AuthController@registerEmail', [], [], [], false, '*');
Route::post('/auth/send_code_email', 'auth.send-code-email', 'AuthController@sendCodeEmail', [], [], [], false, '*');
Route::post('/auth/access_code_remember', 'auth.access-code-remember', 'AuthController@accessCodeRemember', [], [], [], false, '*');
Route::post('/auth/auth_remember_email', 'auth.auth-remember-email', 'AuthController@authRememberEmail', [], [], [], false, '*');

Route::get('/auth/google', 'auth.google.redirect', 'AuthController@authGoogle', [], [], null, false, '*');
Route::get('/auth/facebook', 'auth.facebook.redirect', 'AuthController@authFacebook', [], [], null, false, '*');
Route::post('/ajax/get_link_auth_twitter', 'ajax.auth.twitter.link', 'AuthController@ajaxGetLinkAuthTwitter', ['notBot'], [], [], false, '*');
Route::get('/auth/twitter', 'auth.twitter.redirect', 'AuthController@authTwitter', [], [], null, false, '*');

Route::get('/auth2/google', 'auth2.google', 'AuthController@auth2Google', [], [], [], false, '*');
Route::get('/auth2/google/token', 'auth2.google.token', 'AuthController@auth2GoogleToken', [], [], [], false, '*');

Route::get('/user/{login}', 'users.index', 'UsersController@userIndex', [], [], null, false, '*');

Route::get('/cron/init', 'cron.init', 'Cron@init', [], [], null, false);

Route::group(['middleware' => ['AdminAuth'], 'prefix' => 'admin'], function()
{
    Route::get('/', 'admin.index', 'Admin\MainController@index', [], [], null, false);
});
