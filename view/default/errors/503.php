<?php

use \Base\Base\Request;
use \Base\Base\Route;
use \App\Components\Show;
use \App\Components\ReqDetect;

?><?php $this->view('layouts/app'); ?>

<?php $this->section('title'); ?>
<?= _t('view.errors.Oshibka_503', 'Ошибка 503') ?>
<?php $this->end(); ?>

<?php $this->section('header-h1'); ?>
<?= _t('view.errors.Oshibka_503', 'Ошибка 503') ?>
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
        <div><?= _t('view.errors.Zaprashivaemaya_stranica_vremenno_ne_dostupna', 'Запрашиваемая страница временно не доступна') ?></div>
        
        <br />

        <div><?= _t('view.errors.Pozhaluysta,_proverte_pravilnost_napisaniya_a-dJZe', 'Пожалуйста, проверьте правильность написания адреса либо обратитесь в') ?> <a <?= attrs_route('feedback.index') ?>><?= _t('view.errors.sluzhbu_podderzhki', 'службу поддержки') ?></a>.</div>
      </div>
                  </div>
                </div>
              </div>
            </div>
            </div>
           </div>
            </div>
<?php $this->end(); ?>