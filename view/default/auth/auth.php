<?php

use \Base\Base\Request;
use \Base\Base\Route;
use \App\Components\Show;
use \App\Components\ReqDetect;

?><?php $this->view('layouts/app'); ?>

<?php $this->section('title'); ?>
<?= _t('view.auth.Avtorizaciya', 'Авторизация') ?>
<?php $this->end(); ?>

<?php $this->section('header-h1'); ?>
<?= _t('view.auth.Pozhaluysta_Avtorizuytes', 'Пожалуйста Авторизуйтесь') ?>
<?php $this->end(); ?>

<?php $this->section('body-content'); ?>
<div class="row" id="block-content">
  <main class="col block-main" id="block-main" role="main">
    <div class="col">
 <div class="section">
             <div class="container-fluid">
              <div class="row">
                <div class="col">
                  <div class="recent-activities card">
                    <div class="card-body">
      <div class="col">
        <div><?= _t('view.auth.Zaprashivaemaya_stranica_dostupna_tolko_avtor-by8Y', 'Запрашиваемая страница доступна только авторизованым пользователям.') ?></div>
      </div>
                  </div>
                </div>
              </div>
            </div>
            </div>
           </div>
            </div>
  </main>
</div>
<?php $this->end(); ?>

<?php if(data()->is_ajax) { ?>
<?php $this->append('script_page_init'); ?>
setTimeout(function () {$('#nav_auth-modal-form').click();}, 10);
<?php $this->end(); ?>
<?php } else { ?>
<?php $this->append('scripts'); ?>
<script>
$(document).ready(function () {
    $('#nav_auth-modal-form').click();
});
</script>
<?php $this->end(); ?>
<?php } ?>
