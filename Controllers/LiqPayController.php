<?php

namespace App\Controllers;

use \Base;
use \Base\Base\BaseController;
use \Base\Base\Request;
use \Base\Base\DB;
use \App\Models\User;
use \App\Components\LiqPay;
use \App\Models\Payment;
use \App\Models\Service;

class LiqPayController extends PaymentController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getLiqpayField()
    {
        
        $this->data->payment = Base::dataModel('Payment', 'arrayPaymentById', ['id' => request()->
            post->payment_id], true);
        $currency = Base::dataModel('Currency', 'arrayCurrencyById', ['id' => request
            ()->post->currency_id], true);

        if ($this->data->payment->status == '1') {
            return $this->json([
                'status' => 1, 
                'status_pay' => 'success', 
                'payment_id' => $this->data->payment->id, 
            ]);
        }
        
        if ($this->data->payment->status != '0') {
            return;
            abort();
        }
        
        $this->toPayByBalance($this->data->payment);
        
        if(!($this->data->payment->getPrice() > '0')){
            return $this->json([
                'status' => 1, 
                'status_pay' => 'success', 
                'payment_id' => $this->data->payment->id, 
            ]);
        }
        
        if(!config('isPaidServices')){
            return $this->json(['status' => 1, 'signature' => 'signature',
            'data' => 'data', ]);
        }

        $liqpay = new LiqPay;

        $params = ['action' => 'pay', 'amount' => round(($this->data->payment->getPrice() * $this->data->payment->
            currency->rate_dollar_to_currency) * $currency->rate_currency_to_dollar),
            'currency' => $currency->sign, 'description' => $this->data->payment->description,
            'order_id' => $this->data->payment->id, 'version' => '3', 'server_url' => route('liqpay.server'),
            'result_url' => route('liqpay.client'), ];

        $cnb_params_form = $liqpay->cnb_params_form($params);

        return $this->json(['status' => 1, 'signature' => $cnb_params_form['signature'],
            'data' => $cnb_params_form['data'], ]);
    }

    public function serverRequest()
    {
        $liqpay = new LiqPay;

        $data = $_POST['data'];
        $signature = $_POST['signature'];

        $parsed_data = $liqpay->decode_params($data);

        if ($liqpay->gen_signature($data) == $signature && (config('LiqPayIsTest') ?
            config('LiqPayTestPublicKey') : config('LiqPayPublicKey')) == $parsed_data['public_key']) {
            if (isset($parsed_data['payment_id']))
                $this->data->payment = Base::dataModel('Payment', 'arrayPaymentById', ['id' =>
                    $parsed_data['order_id']], true);

            if (isset($this->data->payment)) {
                $this->data->payment->type = 'liqpay';
                if (isset($parsed_data['order_id']))
                    $this->data->payment->payment_id = $parsed_data['payment_id'];
                $this->data->payment->_data = $parsed_data;
                if (isset($parsed_data['create_date']))
                    $this->data->payment->payment_create_at = date('Y-m-d H:i:s', $parsed_data['create_date'] /
                        1000);
                if (isset($parsed_data['end_date']))
                    $this->data->payment->payment_finish_at = date('Y-m-d H:i:s', $parsed_data['end_date'] /
                        1000);
                if (isset($parsed_data['amount']))
                    $this->data->payment->price_rfn = $parsed_data['amount'];
                if (isset($parsed_data['amount']) && isset($parsed_data['receiver_commission']))
                    $this->data->payment->price_price = $parsed_data['amount'] + $parsed_data['receiver_commission'];
                    
                    
                if (isset($parsed_data['currency']))
                    $this->data->payment->price_currency = $parsed_data['currency'];

                $this->data->payment->save();

                switch ($parsed_data['status']) {
                    case 'success': // Успешный платеж
                    case 'wait_accept': // Деньги с клиента списаны, но магазин еще не прошел проверку. Если магазин не пройдет активацию в течение 180 дней, платежи будут автоматически отменены
                    case 'wait_compensation': // Платеж успешный, будет зачислен в ежесуточной проводке
                        $this->data->payment->executeSuccess();
                        
                        $this->finishPay($this->data->payment);

                        break;

                    case 'error': // Неуспешный платеж. Некорректно заполнены данные
                    case 'failure': // Неуспешный платеж
                    case 'reversed': // Платеж возвращен
                        $this->data->payment->executeError();

                        break;

                    case 'wait_secure': // Платеж на проверке
                    case '3ds_verify': // Требуется 3DS верификация. Для завершения платежа, требуется выполнить 3ds_verify
                    case 'captcha_verify': // Ожидается подтверждение captcha
                    case 'cvv_verify': // Требуется ввод CVV карты отправителя. Для завершения платежа, требуется выполнить cvv_verify
                    case 'ivr_verify': // Ожидается подтверждение звонком ivr
                    case 'otp_verify': // Требуется OTP подтверждение клиента. OTP пароль отправлен на номер телефона Клиента. Для завершения платежа, требуется выполнить otp_verify
                    case 'password_verify': // Ожидается подтверждение пароля приложения Приват24
                    case 'phone_verify': // Ожидается ввод телефона клиентом. Для завершения платежа, требуется выполнить phone_verify
                    case 'pin_verify': // Ожидается подтверждение pin-code
                    case 'receiver_verify': // Требуется ввод данных получателя. Для завершения платежа, требуется выполнить receiver_verify
                    case 'sender_verify': // Требуется ввод данных отправителя.Для завершения платежа, требуется выполнить sender_verify
                    case 'senderapp_verify': // Ожидается подтверждение в приложении SENDER
                    case 'invoice_wait': // Инвойс создан успешно, ожидается оплата
                    case 'prepared': // Платеж создан, ожидается его завершение отправителем
                    case 'processing': // Платеж обрабатывается
                    case 'wait_qr': // Ожидается сканирование QR-кода клиентом
                    case 'wait_sender': // Ожидается подтверждение оплаты клиентом в приложении Privat24/SENDERДругие статусы платежа
                    case 'cash_wait': // Ожидается оплата наличными в ТСО
                    case 'hold_wait': // Сумма успешно заблокирована на счету отправителя
                    case 'wait_lc': // Аккредитив. Деньги с клиента списаны, ожидается подтверждение доставки товара
                    case 'wait_reserve': // Средства по платежу зарезервированы для проведения возврата по ранее поданной заявке

                        break;

                    case 'subscribed': // Подписка успешно оформлена
                    case 'unsubscribed': // Подписка успешно деактивирована
                        break;
                    case 'wait_card': // Не установлен способ возмещения у получателя
                        break;
                }
            }
        }
    }

    public function clientRequest()
    {
        $liqpay = new LiqPay;

        $data = $_POST['data'];
        $signature = $_POST['signature'];

        $parsed_data = $liqpay->decode_params($data);

        if ($liqpay->gen_signature($data) == $signature && (config('LiqPayIsTest') ?
            config('LiqPayTestPublicKey') : config('LiqPayPublicKey')) == $parsed_data['public_key']) {
            if (isset($parsed_data['payment_id']))
                $this->data->payment = Base::dataModel('Payment', 'arrayPaymentById', ['id' =>
                    $parsed_data['order_id']], true);

            if (isset($this->data->payment)) {
                $this->data->payment->type = 'liqpay';
                if (isset($parsed_data['order_id']))
                    $this->data->payment->payment_id = $parsed_data['payment_id'];
                $this->data->payment->_data = $parsed_data;
                if (isset($parsed_data['create_date']))
                    $this->data->payment->payment_create_at = date('Y-m-d H:i:s', $parsed_data['create_date'] /
                        1000);
                if (isset($parsed_data['end_date']))
                    $this->data->payment->payment_finish_at = date('Y-m-d H:i:s', $parsed_data['end_date'] /
                        1000);
                if (isset($parsed_data['amount']))
                    $this->data->payment->price_rfn = $parsed_data['amount'];
                if (isset($parsed_data['amount']) && isset($parsed_data['receiver_commission']))
                    $this->data->payment->price_price = $parsed_data['amount'] + $parsed_data['receiver_commission'];
                    
                if (isset($parsed_data['currency']))
                    $this->data->payment->price_currency = $parsed_data['currency'];

                switch ($parsed_data['status']) {
                    case 'success': // Успешный платеж
                    case 'wait_accept': // Деньги с клиента списаны, но магазин еще не прошел проверку. Если магазин не пройдет активацию в течение 180 дней, платежи будут автоматически отменены
                    case 'wait_compensation': // Платеж успешный, будет зачислен в ежесуточной проводке
                        return $this->view('/main/payment_success');
                        break;

                    case 'error': // Неуспешный платеж. Некорректно заполнены данные
                    case 'failure': // Неуспешный платеж
                    case 'reversed': // Платеж возвращен
                        return $this->view('/main/payment_error');
                        break;

                    case 'wait_secure': // Платеж на проверке
                    case '3ds_verify': // Требуется 3DS верификация. Для завершения платежа, требуется выполнить 3ds_verify
                    case 'captcha_verify': // Ожидается подтверждение captcha
                    case 'cvv_verify': // Требуется ввод CVV карты отправителя. Для завершения платежа, требуется выполнить cvv_verify
                    case 'ivr_verify': // Ожидается подтверждение звонком ivr
                    case 'otp_verify': // Требуется OTP подтверждение клиента. OTP пароль отправлен на номер телефона Клиента. Для завершения платежа, требуется выполнить otp_verify
                    case 'password_verify': // Ожидается подтверждение пароля приложения Приват24
                    case 'phone_verify': // Ожидается ввод телефона клиентом. Для завершения платежа, требуется выполнить phone_verify
                    case 'pin_verify': // Ожидается подтверждение pin-code
                    case 'receiver_verify': // Требуется ввод данных получателя. Для завершения платежа, требуется выполнить receiver_verify
                    case 'sender_verify': // Требуется ввод данных отправителя.Для завершения платежа, требуется выполнить sender_verify
                    case 'senderapp_verify': // Ожидается подтверждение в приложении SENDER
                    case 'invoice_wait': // Инвойс создан успешно, ожидается оплата
                    case 'prepared': // Платеж создан, ожидается его завершение отправителем
                    case 'processing': // Платеж обрабатывается
                    case 'wait_qr': // Ожидается сканирование QR-кода клиентом
                    case 'wait_sender': // Ожидается подтверждение оплаты клиентом в приложении Privat24/SENDERДругие статусы платежа
                    case 'cash_wait': // Ожидается оплата наличными в ТСО
                    case 'hold_wait': // Сумма успешно заблокирована на счету отправителя
                    case 'wait_lc': // Аккредитив. Деньги с клиента списаны, ожидается подтверждение доставки товара
                    case 'wait_reserve': // Средства по платежу зарезервированы для проведения возврата по ранее поданной заявке

                        return $this->view('/main/payment_performed');
                        break;

                    case 'subscribed': // Подписка успешно оформлена
                    case 'unsubscribed': // Подписка успешно деактивирована
                        break;
                    case 'wait_card': // Не установлен способ возмещения у получателя
                        break;
                }
            }
        }

        return $this->view('/main/payment_error');
    }
}
