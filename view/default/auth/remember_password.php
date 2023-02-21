<?php

use \Base\Base\Request;
use \Base\Base\Route;
use \App\Components\Show;

?><?php $this->view('layouts/app'); ?>

<?php $this->section('title'); ?>
<?= _t('view.auth.Vosstanovlenie_parolya', 'Восстановление пароля') ?>
<?php $this->end(); ?>

<?php $this->section('header-h1'); ?>
<?= _t('view.auth.Vosstanovlenie_parolya', 'Восстановление пароля') ?>
<?php $this->end(); ?>

<?php $this->append('block-main'); ?>
<?php $this->show('section-register'); ?>
            <div class="section">
             <div class="container-fluid">
              <div class="row">
                <div class="col">
                  <div class="card">
                    <div class="card-body">
                      <form init-load_ajax_form="0" scroll-to-form="1" action="<?= route('auth.remember.password.store') ?>" method="post">
                        <?php $this->view('errors/errors') ?>
                        
                        <div class="form-group row">
                          <div class="col">
                            <?= _t('view.auth.Ukazhite_novyy_parol', 'Укажите новый пароль') ?>
                          </div>
                        </div>
                        
                        <div class="form-group row">
                          <label class="col-sm-3 form-control-label"><?= _t('view.auth.Parol', 'Пароль*') ?></label>
                          <div class="col-sm-9">
                            <input type="password" name="password" value="<?= old('password') ?>" class="form-control" />
                           <div class="text-error"><?= request()->errors->password ?></div>
                          </div>
                        </div>
                        <div class="form-group row">
                          <label class="col-sm-3 form-control-label"><?= _t('view.auth.Povtor_parolya', 'Повтор пароля*') ?></label>
                          <div class="col-sm-9">
                            <input type="password" name="password_again" value="<?= old('password_again') ?>" class="form-control" />
                           <div class="text-error"><?= request()->errors->password_again ?></div>
                          </div>
                        </div>
                        <div class="form-group">       
                          <input type="submit" value="Отправить" class="btn btn-primary">
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
              </div>
            </div>
           </div>
<?php $this->end(); ?>
<?php $this->end(); ?>


<?php $this->append('block-sidebar'); ?>          
<?php $this->view('sidebar/help-remember-password') ?>
<?php $this->end(); ?>