<?php $this->show('nav-menu'); ?>
<!-- Modal Auth -->
<div class="modal fade" id="auth-modal-form" tabindex="-1" role="dialog" aria-labelledby="auth-modal-form-label" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="auth-modal-form-label"><?= _t('view.auth.Avtorizaciya', 'Авторизация') ?></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form init-load_ajax_form="0" scroll-to-form="1" action="<?= route('auth.login') ?>" onsubmit="return false;" method="post">
                        <div class="form-group">
                          <label class="form-control-label"><?= _t('view.auth.Login_ili_Email', 'Логин или Email') ?></label>
                          <input id="inpt_auth_login" name="login" type="text" placeholder="Логин или Email" class="form-control" />
                        </div>
                        <div class="form-group">
                          <label class="form-control-label"><?= _t('view.auth.Parol-fqgL', 'Пароль') ?></label>
                          <input id="inpt_auth_password" name="password" type="password" placeholder="Пароль" class="form-control" />
                        </div>
                        <div class="form-group text-error" id="div_auth_errors">
                        </div>
                      </form>
                      
        <div style="font-size: 1.5em!important"><?php $this->view('main/social') ?></div>
        
      </div>
      <div class="modal-footer">
        <div class="float-left" style="width: 40%;">
          <button type="button" class="btn btn-primary" id="btn_auth_login"><?= _t('view.auth.Voyti', 'Войти') ?></button>
        </div>
        <div class="float-right" style="width: 60%;">
          <a <?= attrs_route('auth.register') ?>><?= _t('view.auth.Registraciya', 'Регистрация') ?></a>
          <a <?= attrs_route('auth.remember') ?>><?= _t('view.auth.Napomnit_parol', 'Напомнить пароль') ?></a>
        </div>
      </div>
    </div>
  </div>
</div>
<?php $this->end(); ?>