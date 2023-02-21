<?php

namespace App\Controllers;

use \Base;
use \Base\Base\BaseController;
use \Base\Base\Request;
use \Base\Base\DB;
use \App\Models\User;
use \App\Models\File;
use \Base\Base\Mail;
use \Base\Base\BaseObj;
use \Base\Base\Route;

class AuthController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }
    
    public function formAminLogin()
    {
        return $this->view('/admin/auth/login');
    }
    
    public function authAminLogin()
    {
        if (request()->post->login == config('loginadmin') && request()->post->password == config('passwordadmin')) {
            $_SESSION['user'] = [
                'login' => config('loginadmin'),
                'password' => config('passwordadmin'),
            ];
            
            redirect('admin.index', []);
        }
        
        redirect('admin.form', []);
    }
    
    public function authAminLogout()
    {
        if (isset($_SESSION['user'])) {
            unset($_SESSION['user']);
        }
        
        redirect('admin.form', []);
    }

    public function authRememberEmail()
    {
        $res = [];
        
        $res['status'] = 1;
        
        if (request()->post->code && request()->post->email && request()->post->password && mb_strlen(request()->post->password) >= 6) {
            if (isset($_SESSION['auth_user_remember'])) {
                $user = dataModel('User', 'arrayAuthUserByEmail', ['email' => request()->post->email]);
                
                if ($user) {
                    if ($user->count_remember <= 20) {
                        $user->count_remember = $user->count_remember +1;
                        $user->save();
                        
                        if ($_SESSION['auth_user_remember']['email'] == request()->post->email && $_SESSION['auth_user_remember']['code'] == request()->post->code) {
                            $res['is_valid_email'] = 1;
                    
                            $res['is_success_remember'] = 1;
                        
                            $user->password = password_hash(request()->password, PASSWORD_DEFAULT);
                            
                            $user->count_remember = 0;

                            $user->save();

                            $user->authorize();

                            $this->sendAuthUser($user);

                            $purl = parse_url(request()->post->url);

                            Mail::send(request()->post->email, 'Восстановления пароля', widget('Mail', [
                               'placeType' => 'success_remember',
                               'email' => $_SESSION['auth_user_remember']['email'],
                               'password' => request()->password,
                               'code' => $_SESSION['auth_user_remember']['code'],
                               'site' => isset($purl['host']) ? $purl['host'] : config('domain'),
                            ], false));
                        }
                    } else {
                        $res['is_count_remember_excess'] = 1;
                    }
                }
            }
        }
        
        return $this->json($res);
    }

    public function accessCodeRemember()
    {
        $res = [];
        
        $res['status'] = 1;
        
        if (request()->post->code && request()->post->email) {
            $user = dataModel('User', 'arrayAuthUserByEmail', ['email' => request()->post->email]);
            
            if ($user) {
                if ($user->count_remember <= 20) {
                    $user->count_remember = $user->count_remember +1;
                    $user->save();
                    
                    if (isset($_SESSION['auth_user_remember'])) {
                        if ($_SESSION['auth_user_remember']['email'] == request()->post->email && $_SESSION['auth_user_remember']['code'] == request()->post->code) {
                            $res['is_valid_email'] = 1;
                        }
                    }
                } else {
                    $res['is_count_remember_excess'] = 1;
                }
            }
        }
        
        return $this->json($res);
    }

    public function sendCodeEmail()
    {
        $user = dataModel('User', 'arrayAuthUserByEmail', ['email' => request()->post->email]);
        
        $res = [];
        
        if (request()->post->email) {
            $res['status'] = 1;
        }
        
        if ($user) {
            if ($user->status == '1') {
                $res['is_email'] = 1;
                
                if ($user->count_remember <= 20) {
                    $user->count_remember = $user->count_remember +1;
                    $user->save();
                    
                    $_SESSION['auth_user_remember'] = [
                        'email' => request()->post->email,
                        'code' => rand(111111, 999999),
                    ];
                
                    $purl = parse_url(request()->post->url);
            
                    Mail::send(request()->post->email, 'Код подтверждения', widget('Mail', [
                        'placeType' => 'code_send_email',
                        'email' => $_SESSION['auth_user_remember']['email'],
                        'code' => $_SESSION['auth_user_remember']['code'],
                        'site' => isset($purl['host']) ? $purl['host'] : config('domain'),
                    ], false));
            
                    $res['is_send_email'] = 1;
                } else {
                    $res['is_count_remember_excess'] = 1;
                }
            } else {
                $res['not_access_email'] = 1;
            }
        }
        
        return $this->json($res);
    }

    public function registerEmail()
    {
        $res = [];
        
        $res['status'] = 1;
        
        if (request()->post->code && request()->post->email && request()->post->password && mb_strlen(request()->post->password) >= 6) {
            if (isset($_SESSION['auth_user_authorization'])) {
                if ($_SESSION['auth_user_authorization']['email'] == request()->post->email && $_SESSION['auth_user_authorization']['code'] == request()->post->code) {
                    $res['is_valid_email'] = 1;
                    $res['is_success_register'] = 1;
                    
                    $login = translit(request()->email);
                    
                    if (data()->count(['table' => 'users', 'field' => 'login', 'value' => $login])) {
                        $login = $login.'_'.DB::GetAutoIncrement('users');
                    }
                    
                    $user = new User;

                    $user->email = request()->email;
                    $user->password = password_hash(request()->password, PASSWORD_DEFAULT);
                    $user->login = $login;
                    $user->trans = translit(request()->email);
                    $user->remember_token = str_rand(5);
                    $user->remember_token_at = date('Y-m-d H:i:s');
                    $user->type = 3;
                    $user->status = 1;
        
                    $user->accept_terms = 'yes';
                    
                    $user->save();
                    
                    $user->authorize();
                    
                    $this->sendAuthUser($user);
                    
                    $purl = parse_url(request()->post->url);
                    
                    Mail::send(request()->post->email, 'Успешная регистрация', widget('Mail', [
                       'placeType' => 'success_register',
                       'email' => $_SESSION['auth_user_authorization']['email'],
                       'password' => request()->password,
                       'code' => $_SESSION['auth_user_authorization']['code'],
                       'site' => isset($purl['host']) ? $purl['host'] : config('domain'),
                   ], false));
                }
            }
        }
        
        return $this->json($res);
    }

    public function accessCode()
    {
        $res = [];
        
        $res['status'] = 1;
        
        if (request()->post->code && request()->post->email) {
            if (isset($_SESSION['auth_user_authorization'])) {
                if ($_SESSION['auth_user_authorization']['email'] == request()->post->email && $_SESSION['auth_user_authorization']['code'] == request()->post->code) {
                    $res['is_valid_email'] = 1;
                }
            }
        }
        
        return $this->json($res);
    }

    public function loginEmail()
    {
        $obj = dataModel('User', 'arrayAuthUserByEmail', ['email' => request()->post->email]);
        
        $res = [];
        
        if (request()->post->email) {
            $res['status'] = 1;
        }
        
        if ($obj) {
            if ($obj->status == '1') {
                $res['is_email'] = 1;
            } else {
                $res['not_access_email'] = 1;
            }
        } else {
            $res['is_not_email'] = 1;
            
            $_SESSION['auth_user_authorization'] = [
                'email' => request()->post->email,
                'code' => rand(111111, 999999),
            ];
            
            $purl = parse_url(request()->post->url);
            
            Mail::send(request()->post->email, 'Код подтверждения', widget('Mail', [
                'placeType' => 'code_send_email',
                'email' => $_SESSION['auth_user_authorization']['email'],
                'code' => $_SESSION['auth_user_authorization']['code'],
                'site' => isset($purl['host']) ? $purl['host'] : config('domain'),
            ], false));
            
            $res['is_send_email'] = 1;
        }
        
        return $this->json($res);
    }

    public function auth2Google()
    {
        echo '<meta charset="utf-8" />';
        
        $json = json_decode(file_get_contents(config('storage_dir') . '/google/auth.json'), true);
        
        $scopes = [
            'https://www.googleapis.com/auth/userinfo.email',
            'https://www.googleapis.com/auth/userinfo.profile',
            
            'https://www.googleapis.com/auth/webmasters',
            'https://www.googleapis.com/auth/webmasters.readonly',
            
            'https://www.googleapis.com/auth/drive',
        ];
        
        ?>
        <a rel="nofollow" style="font-size: inherit!important;" class="btn" title="Google" href="<?= config('OAuthGoogleUrl'). '?' . urldecode(http_build_query([
        'redirect_uri'  => route('auth2.google.token'),
        'response_type' => 'code',
        'access_type' => 'offline',
        'client_id'     => config('OAuthGoogleId'),
        'scope'         => implode(' ', $scopes)])) ?>">
          Получить токен
        </a>
        <?php
        
        echo "<br />";
        
        echo components()->getGoogleToken();
    }

    public function auth2GoogleToken()
    {
        if (request()->get->code) {
            $result = false;

            $params = array(
                'client_id' => config('OAuthGoogleId'),
                'client_secret' => config('OAuthGoogleSecretKey'),
                'redirect_uri' => route('auth2.google.token'),
                'grant_type' => 'authorization_code',
                'code' => request()->get->code);

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
                components()->setGoogleToken($tokenInfo);
            } else {
                echo 'fail tokenInfo';
            }
        } else {
            echo 'not request code';
        }
    }

    public function authAdminUser()
    {
        if(!isAdmin()){
            abort();
        }
        
        $user = Base::dataModel('User', 'arrayUserById', ['id' => request()->vars->id], true);   
        
        $user->authorize();
        
        if (!isset($_SESSION['user']['auth_admin_id'])) {
            if (data()->user->type == '1') {
                $_SESSION['user']['auth_by_admin'] = true;
                $_SESSION['user']['auth_admin_id'] = data()->user->id;
            }
        }
        
        $this->redirectBack();     
    }

    public function ajaxGetLinkAuthTwitter()
    {
        return $this->json(['link_auth_twitter' => getOauthLinkTwitter()]);
    }

    public function authTwitter()
    {
        initTwitterOauthSession();

        if (request()->get->oauth_token && request()->get->oauth_verifier) {
            // готовим подпись для получения токена доступа

            $oauth_nonce = md5(uniqid(rand(), true));
            $oauth_timestamp = time();
            $oauth_token = request()->get->oauth_token;
            $oauth_verifier = request()->get->oauth_verifier;


            $oauth_base_text = "GET&";
            $oauth_base_text .= urlencode(config('AppTwitterAccecTokenUrl')) . "&";

            $params = array(
                'oauth_consumer_key=' . config('AppTwitterKey') . URL_SEPARATOR,
                'oauth_nonce=' . $oauth_nonce . URL_SEPARATOR,
                'oauth_signature_method=HMAC-SHA1' . URL_SEPARATOR,
                'oauth_token=' . $oauth_token . URL_SEPARATOR,
                'oauth_timestamp=' . $oauth_timestamp . URL_SEPARATOR,
                'oauth_verifier=' . $oauth_verifier . URL_SEPARATOR,
                'oauth_version=1.0');

            $key = config('AppTwitterSecretKey') . URL_SEPARATOR . config('AppTwitterOAuthSecretToken');
            $oauth_base_text = 'GET' . URL_SEPARATOR . urlencode(config('AppTwitterAccecTokenUrl')) .
                URL_SEPARATOR . implode('', array_map('urlencode', $params));
            $oauth_signature = base64_encode(hash_hmac("sha1", $oauth_base_text, $key, true));

            // получаем токен доступа
            $params = array(
                'oauth_nonce=' . $oauth_nonce,
                'oauth_signature_method=HMAC-SHA1',
                'oauth_timestamp=' . $oauth_timestamp,
                'oauth_consumer_key=' . config('AppTwitterKey'),
                'oauth_token=' . urlencode($oauth_token),
                'oauth_verifier=' . urlencode($oauth_verifier),
                'oauth_signature=' . urlencode($oauth_signature),
                'oauth_version=1.0');

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, urldecode(config('AppTwitterAccecTokenUrl') . '?' . implode('&', $params)));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($curl);
            curl_close($curl);

            parse_str($response, $response);
            //$response = json_decode($response, true);

            // формируем подпись для следующего запроса
            $oauth_nonce = md5(uniqid(rand(), true));
            $oauth_timestamp = time();

            $oauth_token = isset($response['oauth_token']) ? $response['oauth_token'] : '';
            $oauth_token_secret = isset($response['oauth_token_secret']) ? $response['oauth_token_secret'] : '';
            $screen_name = isset($response['screen_name']) ? $response['screen_name'] : '';

            $params = array(
                'oauth_consumer_key=' . config('AppTwitterKey') . URL_SEPARATOR,
                'oauth_nonce=' . $oauth_nonce . URL_SEPARATOR,
                'oauth_signature_method=HMAC-SHA1' . URL_SEPARATOR,
                'oauth_timestamp=' . $oauth_timestamp . URL_SEPARATOR,
                'oauth_token=' . $oauth_token . URL_SEPARATOR,
                'oauth_version=1.0' . URL_SEPARATOR,
                'screen_name=' . $screen_name);
            $oauth_base_text = 'GET' . URL_SEPARATOR . urlencode(config('AppTwitterAccountDataUrl')) .
                URL_SEPARATOR . implode('', array_map('urlencode', $params));

            $key = config('AppTwitterSecretKey') . '&' . $oauth_token_secret;
            $signature = base64_encode(hash_hmac("sha1", $oauth_base_text, $key, true));

            // получаем данные о пользователе
            $params = array(
                'oauth_consumer_key=' . config('AppTwitterKey'),
                'oauth_nonce=' . $oauth_nonce,
                'oauth_signature=' . urlencode($signature),
                'oauth_signature_method=HMAC-SHA1',
                'oauth_timestamp=' . $oauth_timestamp,
                'oauth_token=' . urlencode($oauth_token),
                'oauth_version=1.0',
                'screen_name=' . $screen_name);

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, config('AppTwitterAccountDataUrl') . '?' . implode(URL_SEPARATOR, $params));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($curl);
            curl_close($curl);
    
            $userInfo = json_decode($response, true);

            if ($userInfo && isset($userInfo['id'])) {
                $name = '';
                $surname = '';

                if (isset($userInfo['first_name'])) {
                    $name = $userInfo['first_name'];
                }
                if (isset($userInfo['last_name'])) {
                    $surname = $userInfo['last_name'];
                }

                if (!$name && isset($userInfo['name'])) {
                    $expl = explode(' ', $userInfo['name']);

                    if (isset($expl[0])) {
                        $name = $expl[0];
                    }
                }

                if (!$surname && isset($userInfo['name'])) {
                    $expl = explode(' ', $userInfo['name']);

                    if (isset($expl[1])) {
                        $surname = $expl[1];
                    }
                }

                $this->authSocialInfo('id_twitter', ['id' => isset($userInfo['id']) ? $userInfo['id'] : null,
                    'email' => isset($userInfo['email']) ? $userInfo['email'] : null, 'name' => $name,
                    'surname' => $surname, 'login' => isset($userInfo['screen_name']) ? translit($userInfo['screen_name']) : null,
                    'lang' => isset($userInfo['lang']) ? $userInfo['lang'] : null, 'gender' => isset
                    ($userInfo['gender']) ? $userInfo['gender'] : null, 'img' => isset($userInfo['profile_image_url']) ?
                    $userInfo['profile_image_url'] : null, ]);


                $result = true;
            }
        }
        $this->redirectVisitorBack();
    }

    public function authFacebook()
    {
        if (request()->get->code) {
            $result = false;

            $params = array(
                'client_id' => config('OAuthFbId'),
                'redirect_uri' => route('auth.facebook.redirect'),
                'client_secret' => config('OAuthFbSecretKey'),
                'code' => request()->get->code);

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, config('OAuthFbUrlAccessToken') . '?' . http_build_query($params));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            $result = curl_exec($curl);
            curl_close($curl);
            
            $tokenInfo = json_decode($result, true);

            if ($tokenInfo && isset($tokenInfo['access_token'])) {
                $params = array('access_token' => $tokenInfo['access_token']);

                $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, config('OAuthFbUrlAccessMe') . '?' . urldecode(http_build_query($params)));
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                $result = curl_exec($curl);
                curl_close($curl);
                
                $userInfo = json_decode($result, true);

                if (isset($userInfo['id'])) {
                    $name = '';
                    $surname = '';

                    if (isset($userInfo['first_name'])) {
                        $name = $userInfo['first_name'];
                    }
                    if (isset($userInfo['last_name'])) {
                        $surname = $userInfo['last_name'];
                    }

                    if (!$name && isset($userInfo['name'])) {
                        $expl = explode(' ', $userInfo['name']);

                        if (isset($expl[0])) {
                            $name = $expl[0];
                        }
                    }

                    if (!$surname && isset($userInfo['name'])) {
                        $expl = explode(' ', $userInfo['name']);

                        if (isset($expl[1])) {
                            $surname = $expl[1];
                        }
                    }

                    $this->authSocialInfo('id_facebook', ['id' => isset($userInfo['id']) ? $userInfo['id'] : null,
                        'email' => isset($userInfo['email']) ? $userInfo['email'] : null, 'name' => $name,
                        'lang' => isset($userInfo['locale']) ? $userInfo['locale'] : null, 'surname' =>
                        $surname, 'gender' => isset($userInfo['gender']) ? $userInfo['gender'] : null, ]);
                    $result = true;
                }
            }
        }

        $this->redirectVisitorBack();
    }

    public function authGoogle()
    {
        if (request()->get->code) {
            $result = false;

            $params = array(
                'client_id' => config('OAuthGoogleId'),
                'client_secret' => config('OAuthGoogleSecretKey'),
                'redirect_uri' => route('auth.google.redirect'),
                'grant_type' => 'authorization_code',
                'code' => request()->get->code);

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, config('OAuthGoogleUrlToken'));
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, urldecode(http_build_query($params)));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            $result = curl_exec($curl);
            curl_close($curl);

            $tokenInfo = json_decode($result, true);

            if (isset($tokenInfo['access_token'])) {
                $params['access_token'] = $tokenInfo['access_token'];
                
                $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, config('OAuthGoogleUrlUserInfo') . '?' . urldecode(http_build_query($params)));
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                $result = curl_exec($curl);
                curl_close($curl);

                $userInfo = json_decode($result, true);
                
                if (isset($userInfo['id'])) {
                    $this->authSocialInfo('id_google', ['id' => isset($userInfo['id']) ? $userInfo['id'] : null,
                        'email' => isset($userInfo['email']) ? $userInfo['email'] : null, 'name' =>
                        isset($userInfo['given_name']) ? $userInfo['given_name'] : null, 'surname' =>
                        isset($userInfo['family_name']) ? $userInfo['family_name'] : null, 'img' =>
                        isset($userInfo['picture']) ? $userInfo['picture'] : null, 'lang' => isset($userInfo['locale']) ?
                        $userInfo['locale'] : null, ]);
                }
            }
        }

        $this->redirectVisitorBack();
    }

    protected function authSocialInfo($field, $info)
    {
        $user = Base::dataModel('User', 'arrayAuthUserSocial', ['id' => $info['id'],
            'field' => $field]);

        if (!$user) {
            if (data()->user) {
                $user = data()->user;
                
                $user->{$field} = $info['id'];
            } else {
                if ($info['email'] && data()->count(['table' => 'users', 'field' => 'email',
                    'value' => $info['email']])) {
                    $info['email'] = null;
                }

                $user = new User;

                $password = str_rand(6);

                if (isset($info['name']))
                    $user->name = $info['name'];
                if (isset($info['surname']))
                    $user->surname = $info['surname'];
                if (isset($info['gender']) && $info['gender'])
                    $user->gender = $info['gender'];
                if (isset($info['lang']))
                    $user->lang = $info['lang'];
                if (isset($info['email']))
                    $user->email = $info['email'];
                $user->{$field} = $info['id'];
                $user->password = password_hash(request()->password, PASSWORD_DEFAULT);
                $user->remember_token = str_rand(5);

                if (isset($info['login']) && $info['login'] && !data()->count(['table' =>
                    'users', 'field' => 'login', 'value' => $info['login']])) {
                    $user->login = $info['login'];
                } else {
                    $user->login = ($user->name ? translit($user->name) : 'user') . '_' . DB::GetAutoIncrement('users');
                }

                $user->trans = translit($user->login);
                $user->remember_token_at = date('Y-m-d H:i:s');
                $user->type = 3;
                $user->status = 1;
                $user->accept_terms = 'yes';

                $user->save();

                if (isset($info['img'])) {
                    $img = $user->id . '-' . str_rand(10);

                    $pu = parse_url($info['img']);

                    if (isset($pu['path'])) {
                        $pt = pathinfo($pu['path']);

                        $extension = 'jpeg';

                        if (isset($pt['extension'])) {
                            $extension = $pt['extension'];
                        }

                        $img = 'img/' . $img . '.' . $extension;

                        file_put_contents(config('SITE_ROOT') . '/public/' . $img, file_get_contents($info['img']));

                        $file = new File;

                        $file->file = $img;
                        $file->title = $user->name . ' ' . $user->surname;

                        $file->save();

                        $user->photo_id = $file->id;
                    }
                }

                $user->save();

                Mail::send($user->email, 'Success Register', 'Ваш логин: ' . $user->login .
                    '<br /> Ваш пароль: ' . $password . '');
            }
        } else {
            if (data()->user) {
                if($user->id != data()->user->id) {
                    //$user->{$field} = null;
                    //$user->save();
                    
                    //$user = data()->user;
                    
                    //$user->{$field} = $info['id'];
                }
            }
        }
        
        $user->save();

        $user->authorize();
        
        $this->sendAuthUser($user);
    }
    
    protected function sendAuthUser($user)
    {
        if ($user->type != '1') {
            User::sendMessageAdmin(_t('controllers.Avtorizaciya', 'Авторизация'),
            '<div>Авторизовался пользователь: <a target="_blank" href="' . route('users.index', ['login' => $user->login], $user) . '">' . $user->name . ' ' . $user->surname . '</a><br /></div>');
        }
    }

    public function authLogout()
    {
        request()->cookie->auth_token = '';
        
        if(data()->user){
            data()->user->unauthorize();
        }
        
        if (isset($_SESSION['user']) && isset($_SESSION['user']['auth_by_admin']) && isset($_SESSION['user']['auth_admin_id'])) {
            $user = Base::dataModel('User', 'arrayUserById', ['id' => $_SESSION['user']['auth_admin_id']]); 
            
            unset($_SESSION['user']); 
            
            if ($user) {
                $user->authorize();
            } 
        } else {
            unset($_SESSION['user']);
        }
        
        if (request()->type = 'json') {
            return $this->json(['status' => 1]);
        }

        $this->redirectBack();
    }

    public function authRememberPasswordStore()
    {
        if (false && isset($_SESSION['id_user_to_remember_token'])) {
            if ($user = Base::dataModel('User', 'arrayUserById', ['id' => $_SESSION['id_user_to_remember_token']])) {
                $_SESSION['id_user_to_remember_token'] = $user->id;
                if (!request()->password) {
                    request()->errors->password = _t('controllers.Parol_ne_ukazano', 'Пароль не указано');
                } elseif (strlen(request()->password) < 6) {
                    request()->errors->password = _t('controllers.Dlina_parolya_dolzhna_byt_ne_menee_6_simvolov', 'Длина пароля должна быть не менее 6 символов');
                } elseif (preg_match("#[^0-9a-zA-Z_\.-]#i", request()->password)) {
                    request()->errors->password =
                        'Пароль должен содержать только латиннские буквы, цифры, знаки: - _ .';
                }
                if (!request()->password_again) {
                    request()->errors->password_again = _t('controllers.Povtor_parolya_ne_ukazano', 'Повтор пароля не указано');
                } elseif (request()->password != request()->password_again) {
                    request()->errors->password_again = _t('controllers.Povtor_parolya_ne_vernyy', 'Повтор пароля не верный');
                }

                if (request()->errors->count()) {
                    $this->redirectBack();
                }

                $user->password = password_hash(request()->password, PASSWORD_DEFAULT);
                $user->remember_token_at = null;
                $user->remember_token = null;

                $user->save();

                request()->messages->register_success =
                    'Ваш пароль успешно изменен. <br />Можете авторизоваться.';
                redirect('auth.remember.success');
            } else {
                redirect('auth.remember');
            }
        } else {
            redirect('auth.remember');
        }
    }

    public function authRememberPassword()
    {
        if (isset($_SESSION['id_user_to_remember_token'])) {
            return $this->view('/auth/remember_password');
        } else {
            redirect('auth.remember');
        }
    }

    public function authRememberIndex()
    {
        if (false && $user = Base::dataModel('User', 'arrayUserByToken', ['token' => request()->
            token])) {
            $_SESSION['id_user_to_remember_token'] = $user->id;
            redirect('auth.remember.password');
        } else {
            request()->messages->register_success = _t('controllers.Ukazanyy_polzovatel_ne_nayden', 'Указаный пользователь не найден.');
            redirect('auth.remember.success');
        }
    }

    public function authRememberStore()
    {
        if (false && $user = Base::dataModel('User', 'arrayAuthUserByLoginOrEmail', ['login' =>
            request()->login])) {
            $user->remember_token_at = date('Y-m-d H:i:s');
            $user->remember_token = str_rand(5);
            $user->save();

            $html = view('emails/remember_code', ['token' => $user->remember_token]);

            Mail::send($user->email, _t('controllers.Vosstanovlenie_parolya', 'Восстановление пароля'), $html);

            request()->messages->register_success = 'На ваш Email: ' . $user->email .
                ' было отправлено письмо с кодом для возобновления доступа. <br />Перейдите по ссылке в письме чтобы изменить свой пароль.<br /> Ссылка актуальна 24 часа.';

            redirect('auth.remember.success');
        } else {
            request()->errors->email = _t('controllers.Ukazanyy_polzovatel_ne_nayden', 'Указаный пользователь не найден.');

            $this->redirectBack();
        }
    }

    public function authLogin()
    {
        $user = Base::dataModel('User', 'arrayAuthUserByEmail', ['email' => request()->post->login]);

        if ($user && password_verify(request()->password, $user->password)) {
            $user->authorize();
            
            $this->sendAuthUser($user);
            
            $res = [
                'json_auth_ajax' => [
                    'status' => 1
                ]
            ];
            
            if (request()->action_admin != 'panel' && $user->type == '1') {
                $res['trcpAdmnIframeElement'] = widget('Admin', ['placeType' => 'stats-pnl'], false);
            }

            return $this->json($res);
        } else {
            return $this->json(['json_auth_ajax' => ['errors' => [_t('controllers.Login_ili_parol_vvedeny_ne_verno', 'Email или пароль введены не верно')]]]);
        }
    }

    public function authRegister()
    {
        $this->data->countries = new BaseObj;
        $this->data->regions = new BaseObj;
        $this->data->areas = new BaseObj;
        $this->data->cities = new BaseObj;

        $this->data->countries = Base::dataModel('Country', 'dataArrayCountriesAll');
        $this->data->currencies = Base::dataModel('Currency', 'dataArrayCurrenciesAll');

        if (old('country_id')) {
            $this->data->obj_country = Base::dataModel('Country', 'arrayCountryById', ['id' =>
                old('country_id')], true);
        }

        if (old('region_id')) {
            $this->data->obj_region = Base::dataModel('Region', 'arrayRegionById', ['id' =>
                old('region_id')], true);
        }

        if (old('area_id')) {
            $this->data->obj_area = Base::dataModel('Area', 'arrayAreaById', ['id' => old('area_id')], true);
        }

        if (old('city_id')) {
            $this->data->obj_city = Base::dataModel('City', 'arrayCityById', ['id' => old('city_id')], true);
        }


        return $this->view('/auth/register');
    }

    public function authRegisterStore()
    {
        if (!request()->name) {
            request()->errors->name = _t('controllers.Imya_ne_ukazano', 'Имя не указано');
        }
        if (!request()->surname) {
            request()->errors->surname = _t('controllers.Familiya_ne_ukazano', 'Фамилия не указано');
        }
        if (!request()->login) {
            request()->errors->login = _t('controllers.Login_ne_ukazano', 'Логин не указано');
        } elseif (preg_match("#[^0-9a-zA-Z_\.-]#i", request()->login)) {
            request()->errors->login =
                'Логин должен содержать только латиннские буквы, цифры, знаки: - _ .';
        } elseif (data()->count(['table' => 'users', 'field' => 'login', 'value' =>
        request()->login])) {
            request()->errors->login = _t('controllers.Etot_login_zanyat', 'Этот логин занят');
        }
        if (!request()->birth_day) {
            request()->errors->birth_day = _t('controllers.Den_rozhdeniya_ne_ukazano', 'День рождения не указано');
        }
        if (!request()->birth_month) {
            request()->errors->birth_month = _t('controllers.Mesyac_rozhdeniya_ne_ukazano', 'Месяц рождения не указано');
        }
        if (!request()->birth_year) {
            request()->errors->birth_year = _t('controllers.God_rozhdeniya_ne_ukazano', 'Год рождения не указано');
        }
        if (!request()->sex) {
            request()->errors->sex = _t('controllers.Pol_ne_ukazano', 'Пол не указано');
        }
        if (!(request()->area_id || request()->city_id || request()->region_id || request()->country_id)) {
            request()->errors->city = _t('controllers.Gorod_ne_ukazano', 'Город не указано');
        }
        if (!request()->email) {
            request()->errors->email = _t('controllers.Email_ne_ukazano', 'Email не указано');
        } elseif (!preg_match("#^[-0-9a-z_\.]+@[-0-9a-z^\.]+\.[a-z]{2,6}$#i", request()->
        email)) {
            request()->errors->email = _t('controllers.Ukazhite_korrektnyy_Email', 'Укажите корректный Email');
        } elseif (data()->count(['table' => 'users', 'field' => 'email', 'value' =>
        request()->email])) {
            request()->errors->email = _t('controllers.Etot_email_zanyat', 'Этот email занят');
        }
        if (!request()->password) {
            request()->errors->password = _t('controllers.Parol_ne_ukazano', 'Пароль не указано');
        } elseif (strlen(request()->password) < 6) {
            request()->errors->password = _t('controllers.Dlina_parolya_dolzhna_byt_ne_menee_6_simvolov', 'Длина пароля должна быть не менее 6 символов');
        } elseif (preg_match("#[^0-9a-zA-Z_\.-]#i", request()->password)) {
            request()->errors->password =
                'Пароль должен содержать только латиннские буквы, цифры, знаки: - _ .';
        }
        if (!request()->password_again) {
            request()->errors->password_again = _t('controllers.Povtor_parolya_ne_ukazano', 'Повтор пароля не указано');
        } elseif (request()->password != request()->password_again) {
            request()->errors->password_again = _t('controllers.Povtor_parolya_ne_vernyy', 'Повтор пароля не верный');
        }
        if (!request()->accept_terms) {
            request()->errors->accept_terms = _t('controllers.Primite_polzovatelskoe_soglashcheniya', 'Примите пользовательськое соглащения');
        }
        if (request()->password != request()->password_again) {
            request()->errors->password_again = _t('controllers.Povtor_parolya_ne_vernyy', 'Повтор пароля не верный');
        }

        if (request()->errors->count()) {
            $this->redirectBack();
        }

        $user = new User;

        $user->email = request()->email;
        $user->password = password_hash(request()->password, PASSWORD_DEFAULT);
        $user->name = request()->name;
        $user->surname = request()->surname;
        $user->gender = request()->sex;
        $user->login = request()->login;
        $user->trans = request()->login;
        $user->remember_token = str_rand(5);
        $user->remember_token_at = date('Y-m-d H:i:s');
        $user->birth_at = date_create(request()->birth_year . '-' . request()->
            birth_month . '-' . request()->birth_day)->format('Y-m-d H:i:s');
        $user->type = 3;
        $user->status = 0;
        if (request()->country_id){
            $this->data->obj_country = Base::dataModel('Country', 'arrayCountryById', ['id' => request()->country_id], true);
            
            $user->country_id = request()->country_id;
        }
        if (request()->region_id){
            $this->data->obj_region = Base::dataModel('Region', 'arrayRegionById', ['id' => request()->region_id], true);
            
            $user->region_id = request()->region_id;
            
            if($this->data->obj_region->country){
                $user->country_id = $this->data->obj_region->country->id;
            }
        }
        if (request()->area_id){
            $this->data->obj_area = Base::dataModel('Area', 'arrayAreaById', ['id' => request()->area_id], true);
            
            $user->area_id = request()->area_id;
            
            if($this->data->obj_area->country){
                $user->country_id = $this->data->obj_area->country->id;
            }
            
            if($this->data->obj_area->region){
                $user->region_id = $this->data->obj_area->region->id;
            }
        }
        if (request()->city_id){
            $this->data->obj_city = Base::dataModel('City', 'arrayCityById', ['id' => request()->city_id], true);
            
            $user->city_id = request()->city_id;
            
            if($this->data->obj_city->country){
                $user->country_id = $this->data->obj_city->country->id;
            }
            
            if($this->data->obj_city->region){
                $user->region_id = $this->data->obj_city->region->id;
            }
            
            if($this->data->obj_city->area){
                $user->area_id = $this->data->obj_city->area->id;
            }
        }
        if (request()->address)
            $user->address = request()->address;
        $user->accept_terms = 'yes';

        //var_dump($user);exit;

        $user->save();

        $html = view('emails/valid_email', ['token' => $user->remember_token]);

        Mail::send($user->email, 'Validate Email', $html);

        request()->messages->register_success = 'На ваш Email: ' . request()->email .
            ' было отправлено письмо с кодом для авторизации. <br />Перейдите по ссылке в письме чтобы завершить регистрацию.<br /> Код активации актуальный 24 часа.';

        redirect('auth.register.success');
    }

    public function authValid()
    {
        if ($user = Base::dataModel('User', 'arrayUserByTokenStautus0', ['token' =>
            request()->token])) {
            request()->messages->register_success =
                'Ваша учетная запись успешно активирована. <br />Теперь можете авторизоваться.';

            $user->status = '1';
            $user->remember_token_at = null;
            $user->remember_token = null;

            $user->save();
        } else {
            request()->messages->register_success = _t('controllers.Peredannyy_kod_ne_sushchestvuet', 'Переданный код не существует.');
        }

        return $this->view('/auth/register_success');
    }

    public function authRememberSuccess()
    {
        return $this->view('/auth/remember_success');
    }

    public function authRegisterSuccess()
    {
        return $this->view('/auth/register_success');
    }

    public function authRemember()
    {
        return $this->view('/auth/remember');
    }
    
    public  function redirectBack()
    {
        $route = Route::lastGetRoute();
        
        if ($route['as'] == 'auth.logout') {
            $link = route('main.index');
        } else {
            $link = route($route['as'], $route['data']);
        }
        
        if (stripos(Route::nowRout(), 'auth') === false) {
            setAuthUserToken($link);
        }
        
        set_http_response_code(200);

        header_sent("Location: " . $link);

        exit;
    }
    
    public function redirectVisitorBack()
    {
        $route = Route::lastGetRoute();
        
        $params = [];
        
        $params = array_merge($route['data'], $params);
        
        if (isset($params['cookie_token_request'])) {
            unset($params['cookie_token_request']);
        }
        
        if (isset($params['auth_token_request'])) {
            unset($params['auth_token_request']);
        }
        
        if (isset($params['auth_token'])) {
            unset($params['auth_token']);
        }
        
        if ($route['as'] == 'auth.logout') {
            $link = route('main.index', $params);
        } else {
            $link = route($route['as'], array_merge($route['data'], $params));
        }
        
        //$link = setAuthUserToken($link);
        
        $out = '';
        
        $out .= widget('Page', ['placeType' => 'index'], false);
        $out .= widget('Page', ['placeType' => 'redirect', 'url' => $link], false);
        
        echo $out;exit;
    }

    public function urlAdmin()
    {
        $res = [];
        
        $res['status'] = 1;
        
        if (request()->url_admin) {
            $parsed_url = parse_url(request()->url_admin);
            
            if (isset($parsed_url['query'])) {
                parse_str($parsed_url['query'], $args);
                
                if (isset($args['source_url'])) {
                    $parsed_source_url = parse_url($args['source_url']);
                    
                    if (isset($parsed_source_url['host']) && isSuccesSetAuthUserTokenHost($parsed_source_url['host'])) {
                        $res['url_admin'] = setAuthUserToken(request()->url_admin);
                    }
                }
            }
        }
        
        return $this->json($res);
    }
}
