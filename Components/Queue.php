<?php

namespace App\Components;

use \Base;

class Queue
{
    public function mail($data)
    {
        require_once (Base::app()->config('SITE_ROOT') . '/libs/Mail.php');

        $m = new \Mail('utf-8'); // можно сразу указать кодировку, можно ничего не указывать ($m= new Mail;)
        $m->From(Base::app()->config('SMTP_MAIL_FROM')); // от кого Можно использовать имя, отделяется точкой с запятой
        $m->ReplyTo(Base::app()->config('SMTP_MAIL_REPLY')); // куда ответить, тоже можно указать имя
        if (is_array($data['to'])) {
            foreach ($data['to'] as $key => $value) {
                $m->To($value, rand(1111, 9999)); // кому, в этом поле так же разрешено указывать имя
            }
        } else {
            $m->To($data['to']); // кому, в этом поле так же разрешено указывать имя
        }
        $m->Subject($data['subject']);
        $m->Body("<div>" . $data['message'] . "</div>", "html");
        $m->Priority(4); // установка приоритета
        if (Base::app()->config('SMTP_MAIL')) {
            $m->smtp_on(Base::app()->config('SMTP_MAIL_SERVER'), Base::app()->config('SMTP_MAIL_LOGIN'),
                Base::app()->config('SMTP_MAIL_PASSWORD'), Base::app()->config('SMTP_MAIL_PORT'),
                60);
            // используя эту команду отправка пойдет через smtp
        }
        $m->log_on(true);
        $m->Send(); // отправка
        if ($m->status_mail['status']) {
            return true;
        }

        return false;
    }

    public function push($data)
    {
        $obj = Base::dataModel('Push', 'arrayPushById', ['id' => $data['push_id']], true);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, config('GooglePushSendURL'));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: key=' . config('GooglePushServerKey'),
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'to' => $obj->token,
            'notification' => [
                'title' => strip_tags($data['title']),
                'body' => strip_tags($data['message']),
                'icon' => asset('/img/f-64x64.png'),
                'click_action' => $data['click_action'] ? $data['click_action'] : route('users.notifications'),
            ],
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $response = json_decode(curl_exec($ch));
        curl_close($ch);
        
        if($response && is_object($response)){
            if($response->success == '1'){
                $obj->count_success = $obj->count_success + 1;
            } else {
                $obj->count_error = $obj->count_error + 1;
                
                if(isset($response->results) && is_array($response->results) && isset($response->results[0])){
                    if(is_object($response->results[0]) && isset($response->results[0]->error)){
                        if($response->results[0]->error == 'NotRegistered'){
                            $obj->delete();
                            return;
                        }
                    }
                }
            }
        } else {
            $obj->count_error = $obj->count_error + 1;
        }
        
        $obj->save();
    }

    public function subscribes($data)
    {
        (new \App\Components\Subscribe)->subscribeByArgs($data['args']);
        (new \App\Components\Subscribe)->subscribeEmail($data['args']);
        (new \App\Components\Subscribe)->subscribePush($data['args']);
    }

    public function subscribeByArgs($data)
    {
        (new \App\Components\Subscribe)->subscribeByArgs($data['args']);
    }

    public function subscribeEmail($data)
    {
        (new \App\Components\Subscribe)->subscribeEmail($data['args']);
    }

    public function subscribePush($data)
    {
        (new \App\Components\Subscribe)->subscribePush($data['args']);
    }
}
