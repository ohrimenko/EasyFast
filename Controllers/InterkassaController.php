<?php

namespace App\Controllers;

use \Base;
use \Base\Base\BaseController;
use \Base\Base\Request;
use \Base\Base\DB;
use \App\Models\User;
use \App\Models\Payment;
use \App\Components\LiqPay;
use \App\Models\Service;

class InterkassaController extends PaymentController
{
    public function __construct()
    {
        parent::__construct();
    }
    
    public function getIkFieldProjectUsefulOptions()
    {
        
        $this->data->payment = Base::dataModel('Payment', 'arrayPaymentById', ['id' =>request()->post->payment_id], true);
        $currency = Base::dataModel('Currency', 'arrayCurrencyById', ['id' =>request()->post->currency_id], true);
        
        if ($this->data->payment->status == '1') {
            return $this->json([
                'status' => 1, 
                'status_pay' => 'success', 
                'payment_id' => $this->data->payment->id, 
            ]);
        }
        
        if($this->data->payment->status != '0'){
            return;
            abort();
        }
        
        $dataSet = $_POST;
        
        $this->toPayByBalance($this->data->payment);
        
        if(!($this->data->payment->getPrice() > '0')){
            return $this->json([
                'status' => 1, 
                'status_pay' => 'success', 
                'payment_id' => $this->data->payment->id, 
            ]);
        }
        
        if(!config('isPaidServices')){
            return $this->json([
                'status' => 1, 
                'ik_sign' => 'ik_sign', 
                'ik_pm_no' => 'ik_pm_no', 
                'ik_am' => 'ik_am', 
                'ik_desc' => 'ik_desc',
                'ik_cur' => 'ik_cur',
            ]);
        }
        
        $dataSet['ik_pm_no'] = $this->data->payment->id;
        $dataSet['ik_am'] = round(($this->data->payment->getPrice() * $this->data->payment->currency->rate_dollar_to_currency) * $currency->rate_currency_to_dollar);
        $dataSet['ik_desc'] = prepareDescriptionInterkassa($this->data->payment->description);
        $dataSet['ik_cur'] = ($currency->sign);
        
        $ik_sign = getInterkassaSign($dataSet);
        
        return $this->json([
            'status' => 1, 
            'ik_sign' => $ik_sign, 
            'ik_pm_no' => $dataSet['ik_pm_no'], 
            'ik_am' => $dataSet['ik_am'], 
            'ik_desc' => $dataSet['ik_desc'],
            'ik_cur' => $dataSet['ik_cur'],
        ]);
    }

    public function getIkSign()
    {
        $currency = Base::dataModel('Currency', 'arrayCurrencyById', ['id' => 2], true);
        
        $payment = new Payment;
        
        if (data()->user) {
            $payment->user_id = data()->user->id;
        }
        $payment->currency_id = $currency->id;
        
        $payment->save();
        
        $service = new Service;
        
        if (data()->user) {
            $service->user_id = data()->user->id;
        }
        
        $service->payment_id = $payment->id;
        $service->currency_id = $currency->id;
        $service->status = 0;
        $service->type = 'assistance';
        $service->price = request()->post->ik_am;
        $service->description = _t('controllers.Pomoshch_na_razvitie_proekta_worklancer_net', 'Помощь на развитие проекта worklancer.net');
        
        $service->save();
        
        $payment->saveDescription();
        $payment->savePrice();
        
        $dataSet = $_POST;
        
        $dataSet['ik_pm_no'] = $payment->id;
        $dataSet['ik_am'] = $payment->getPrice();
        $dataSet['ik_desc'] = $payment->description;
        $dataSet['ik_cur'] = ($currency->sign);
        
        $ik_sign = getInterkassaSign($dataSet);
        
        return $this->json([
            'status' => 1, 
            'ik_sign' => $ik_sign, 
            'ik_pm_no' => $payment->id, 
            'ik_am' => $payment->getPrice(), 
            'ik_desc' => $payment->description,
            'ik_cur' => ($currency->sign),
        ]);
    }

    public function successfulPayment()
    {
        $dataSet = $_POST;
        
        $this->data->payment = Base::dataModel('Payment', 'arrayPaymentById', ['id' => $dataSet['ik_pm_no']], true);
        
        if ($dataSet['ik_inv_st'] == 'success') {
            return $this->view('/main/payment_success');
        }

        return $this->view('/main/payment_error');
    }

    public function paymentFailure()
    {
        $dataSet = $_POST;
        
        $this->data->payment = Base::dataModel('Payment', 'arrayPaymentById', ['id' => $dataSet['ik_pm_no']], true);
        
        if ($dataSet['ik_inv_st'] == 'canceled') {
            $this->data->payment->executeError();
        
            $this->data->payment->_data = $dataSet;
            $this->data->payment->save();
        }
        
        return $this->view('/main/payment_error');
    }

    public function paymentPerformed()
    {
        $dataSet = $_POST;
        
        //file_put_contents(__DIR__.'/t.txt', serialize($dataSet));
        
        $this->data->payment = Base::dataModel('Payment', 'arrayPaymentById', ['id' => $dataSet['ik_pm_no']], true);
        
        if ($dataSet['ik_inv_st'] == 'waitAccept') {
            $this->data->payment->_data = $dataSet;
            $this->data->payment->save();
        }
        
        return $this->view('/main/payment_performed');
    }

    public function interaction()
    {
        $dataSet = $_POST;
        
        //file_put_contents(__DIR__.'/t.txt', serialize($dataSet));
        
        $this->data->payment = Base::dataModel('Payment', 'arrayPaymentById', ['id' => $dataSet['ik_pm_no']], true);
        
        $needle = $dataSet['ik_sign'];
        
        $ik_sign = getInterkassaSign($dataSet, config('InterkassaIsTest'));
        
        if ($ik_sign == $needle) {
            $this->data->payment->type = 'interkassa';
            $this->data->payment->payment_id = $dataSet['ik_inv_id'];
            $this->data->payment->_data = $dataSet;
            $this->data->payment->payment_create_at = $dataSet['ik_inv_crt'];
            $this->data->payment->payment_finish_at = $dataSet['ik_inv_prc'];
            $this->data->payment->price_rfn = $dataSet['ik_co_rfn'];
            $this->data->payment->price_price = $dataSet['ik_ps_price'];
            $this->data->payment->price_currency = $parsed_data['ik_cur'];
            
            $this->data->payment->save();
            
            if ($dataSet['ik_inv_st'] == 'success') {
                $this->data->payment->executeSuccess();
                
                $this->finishPay($this->data->payment);
            } else {
                $this->data->payment->executeError();
            }
        }
    }
}
