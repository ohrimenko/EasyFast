<?php $this->show('section-register'); ?>
            <div class="section">
             <div class="container-fluid">
              <div class="row">
                <div class="col">
                  <div class="card">
                    <div class="card-body">
                      <form init-load_ajax_form="0" scroll-to-form="1" action="<?= route('auth.remember-store') ?>" method="post">
                        <?php $this->view('errors/errors') ?>
                        
                        <div class="form-group row">
                          <label class="col-sm-3 form-control-label"><?= _t('view.auth.Email_ili_Login', 'Email или Логин') ?></label>
                          <div class="col-sm-6">
                            <input type="text" name="login" class="form-control" value="<?= old('login') ?>" />
                          </div>
                          <div class="col-sm-3">
                            <input type="submit" value="Отправить" class="form-control btn btn-primary" />
                          </div>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
              </div>
            </div>
           </div>
<?php $this->end(); ?>