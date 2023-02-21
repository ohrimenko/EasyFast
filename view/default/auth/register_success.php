<?php

use \Base\Base\Request;
use \Base\Base\Route;
use \App\Components\Show;

?><?php $this->view('layouts/app'); ?>

<?php $this->section('title'); ?>
<?= _t('view.auth.Registraciya', 'Регистрация') ?>
<?php $this->end(); ?>

<?php $this->section('header-h1'); ?>
<?= _t('view.auth.Registraciya', 'Регистрация') ?>
<?php $this->end(); ?>

<?php $this->append('block-main'); ?>
           <div class="section">
             <div class="container-fluid">
              <div class="row">
                <div class="col">
                  <div class="card">
                    <div class="card-body">
                        <div>
                          <?= request()->messages->register_success ?>
                        </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
           </div>
<?php $this->end(); ?>


<?php $this->append('block-sidebar'); ?>          
<?php $this->view('sidebar/help-register') ?>
<?php $this->end(); ?>