<?php

use \Base\Base\Request;
use \Base\Base\Route;
use \App\Components\Show;
use \App\Components\ReqDetect;

?><?php $this->view('layouts/app'); ?>

<?php $this->section('title'); ?>
<?= _t('view.errors.Oshibka_404', 'Ошибка 404') ?>
<?php $this->end(); ?>

<?php $this->section('header-h1'); ?>
<?= _t('view.errors.Oshibka_404', 'Ошибка 404') ?>
<?php $this->end(); ?>

<?php $this->append('block-content'); ?>
<div class="col">
 <div class="section">
             <div class="container-fluid">
              <div class="row">
                <div class="col">
                  <div class="recent-activities card">
                    <div class="card-body">
      <div class="col">
        <div><?= _t('view.errors.Zaprashivaemaya_Vami_stranica_ne_sushchestvue-5jaP', 'Запрашиваемая Вами страница не существует или была переименована.') ?></div>
        
        <br />

        <div><?= _t('view.errors.Pozhaluysta,_proverte_pravilnost_napisaniya_a-bF8P', 'Пожалуйста, проверьте правильность написания адреса либо обратитесь в') ?> <a <?= attrs_route('feedback.index') ?>><?= _t('view.errors.sluzhbu_podderzhki', 'службу поддержки') ?></a>.</div>
      </div>
                  </div>
                </div>
              </div>
            </div>
            </div>
           </div>
            </div>
<?php $this->end(); ?>