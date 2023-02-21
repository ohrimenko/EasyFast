<?php

namespace Base\Base;

use \Base;

class Mail
{
    public static function send($to, $subject, $message, $is_queue = true)
    {
        if ($is_queue) {
            \Base\Base\Queue::add('\App\Components\Queue', 'mail', ['to' => $to, 'subject' => $subject, 'message' => $message]);
        
            return;
        }
        
        require_once (config('SITE_ROOT') . '/libs/Mail.php');

        $m = new \Mail('utf-8'); // можно сразу указать кодировку, можно ничего не указывать ($m= new Mail;)
        $m->From(config('SMTP_MAIL_FROM')); // от кого Можно использовать имя, отделяется точкой с запятой
        $m->ReplyTo(config('SMTP_MAIL_REPLY')); // куда ответить, тоже можно указать имя
        if (is_array($to)) {
            foreach ($to as $key => $value) {
                $m->To($value, rand(1111, 9999)); // кому, в этом поле так же разрешено указывать имя
            }
        } else {
            $m->To($to); // кому, в этом поле так же разрешено указывать имя
        }
        $m->Subject($subject);
        $m->Body("<div>" . $message . "</div>", "html");
        $m->Priority(1); // установка приоритета
        if (config('SMTP_MAIL')) {
            $m->smtp_on(config('SMTP_MAIL_SERVER'), config('SMTP_MAIL_LOGIN'),
                config('SMTP_MAIL_PASSWORD'), config('SMTP_MAIL_PORT'),
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
}
