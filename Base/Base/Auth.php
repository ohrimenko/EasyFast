<?php

namespace Base\Base;

class Auth
{
    private static $user = "empty";

    private static function isUserSession()
    {
        if (isset($_SESSION['auth']) && is_array($_SESSION['auth']) && isset($_SESSION['auth']['id']) &&
            isset($_SESSION['auth']['name']) && isset($_SESSION['auth']['login']) && isset($_SESSION['auth']['password']) &&
            isset($_SESSION['auth']['privileges'])) {
            return true;
        }
    }

    public static function statusAutorization()
    {
        if (\Base::app()->config('WORDPRESS')) {
            return is_user_logged_in();
        }
        
        if (self::isUserSession() && self::get('id') == $_SESSION['auth']['id'] && self::
            get('login') == $_SESSION['auth']['login'] && self::get('password') == $_SESSION['auth']['password']) {
            return true;
        }

        return false;
    }

    private static function get($key)
    {
        if (self::isUserSession()) {
            if (self::$user == "empty") {
                $user_obj = \Base\model\User::find($_SESSION['auth']['id']);

                self::$user = array(
                    'id' => $user_obj->id,
                    'name' => $user_obj->name,
                    'login' => $user_obj->login,
                    'privileges' => $user_obj->privileges,
                    'password' => $user_obj->password);
            }

            if (self::$user && is_array(self::$user)) {
                if (isset(self::$user[$key])) {
                    return self::$user[$key];
                }
            }
        }
        return null;
    }

    public static function getId()
    {
        if (\Base::app()->config('WORDPRESS')) {
            $user = wp_get_current_user();
            return $user ? $user->ID : null;
        }
        
        if (self::statusAutorization()) {
            return self::get('id');
        }
        return null;
    }

    public static function getName()
    {
        if (\Base::app()->config('WORDPRESS')) {
            $user = wp_get_current_user();
            return $user ? $user->user_nicename : null;
        }
        
        if (self::statusAutorization()) {
            return self::get('name');
        }
        return null;
    }

    public static function getLogin()
    {
        if (\Base::app()->config('WORDPRESS')) {
            $user = wp_get_current_user();
            return $user ? $user->user_login : null;
        }
        
        if (self::statusAutorization()) {
            return self::get('login');
        }
        return null;
    }

    public static function getPrivileges()
    {
        if (\Base::app()->config('WORDPRESS')) {
            $role = 'guest';
            
            $user = wp_get_current_user();
            
            $roles = $user ? $user->roles : [];
            
            if (is_array($roles)) {
                
            }
            
            foreach ($roles as $key => $value) {
                $role = $value;
                
                if($role == 'administrator'){
                    $role = 'admin';
                    break;
                }
            }
            
            return $role;
        }
        
        if (self::statusAutorization()) {
            return self::get('privileges');
        }
        return null;
    }
}

?>