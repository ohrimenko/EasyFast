<?php $this->show('section-register'); ?>
            <div class="section">
             <div class="container-fluid">
              <div class="row">
                <div class="col">
                  <div class="card">
                    <div class="card-body">
                      <form init-load_ajax_form="0" scroll-to-form="1" action="<?= route('auth.register-strore') ?>" method="post">
                        <?php $this->view('errors/errors') ?>
                        
                        <div class="form-group row">
                          <label class="col-sm-3 form-control-label"><?= _t('view.auth.Imya', 'Имя*') ?></label>
                          <div class="col-sm-9">
                            <input name="name" value="<?= old('name') ?>" type="text" placeholder="Имя" class="form-control" />
                            <div class="text-error"><?= request()->errors->name ?></div>
                          </div>
                        </div>
                        <div class="form-group row">
                          <label class="col-sm-3 form-control-label"><?= _t('view.auth.Familiya', 'Фамилия*') ?></label>
                          <div class="col-sm-9">
                            <input name="surname" value="<?= old('surname') ?>" type="text" placeholder="Фамилия" class="form-control" />
                            <div class="text-error"><?= request()->errors->surname ?></div>
                          </div>
                        </div>
                        <div class="form-group row">
                          <label class="col-sm-3 form-control-label"><?= _t('view.auth.Login', 'Логин*') ?></label>
                          <div class="col-sm-9">
                            <input name="login" value="<?= old('login') ?>" type="text" placeholder="Логин" class="form-control" />
                            <div class="text-error"><?= request()->errors->login ?></div>
                          </div>
                        </div>
                        <div class="form-group row">
                          <label class="col-sm-3 form-control-label">Email*</label>
                          <div class="col-sm-9">
                            <input type="email" name="email" value="<?= old('email') ?>" class="form-control" />
                           <div class="text-error"><?= request()->errors->email ?></div>
                          </div>
                        </div>
                        <div class="form-group row">
                          <label class="col-sm-3 form-control-label"><?= _t('view.auth.Data_rozhdeniya', 'Дата рождения*') ?></label>
                          <div class="col-sm-9">
                            <div class="btn-group" style="width: 100%;">
                            <select class="form-control" name="birth_day" id="birth_day" validate="true">
                              <option value="0"><?= _t('view.auth.den', 'день') ?></option>
                              <?php for($i=1;$i<=31;$i++){ ?>
                              <option value="<?= $i ?>" <?= old('birth_day') == $i ? ' selected=""' : '' ?>><?= $i ?></option>
                              <?php } ?>
                            </select>
                          <select class="form-control" name="birth_month" id="birth_month">
                              <option value="0"><?= _t('view.auth.mesyac', 'месяц') ?></option>
                              <option value="1"<?= old('birth_month') == 1 ? ' selected=""' : '' ?>><?= _t('view.auth.yanvarya', 'января') ?></option>
                              <option value="2"<?= old('birth_month') == 2 ? ' selected=""' : '' ?>><?= _t('view.auth.fevralya', 'февраля') ?></option>
                              <option value="3"<?= old('birth_month') == 3 ? ' selected=""' : '' ?>><?= _t('view.auth.marta', 'марта') ?></option>
                              <option value="4"<?= old('birth_month') == 4 ? ' selected=""' : '' ?>><?= _t('view.auth.aprelya', 'апреля') ?></option>
                              <option value="5"<?= old('birth_month') == 5 ? ' selected=""' : '' ?>><?= _t('view.auth.maya', 'мая') ?></option>
                              <option value="6"<?= old('birth_month') == 6 ? ' selected=""' : '' ?>><?= _t('view.auth.iyunya', 'июня') ?></option>
                              <option value="7"<?= old('birth_month') == 7 ? ' selected=""' : '' ?>><?= _t('view.auth.iyulya', 'июля') ?></option>
                              <option value="8"<?= old('birth_month') == 8 ? ' selected=""' : '' ?>><?= _t('view.auth.avgusta', 'августа') ?></option>
                              <option value="9"<?= old('birth_month') == 9 ? ' selected=""' : '' ?>><?= _t('view.auth.sentyabrya', 'сентября') ?></option>
                              <option value="10"<?= old('birth_month') == 10 ? ' selected=""' : '' ?>><?= _t('view.auth.oktyabrya', 'октября') ?></option>
                              <option value="11"<?= old('birth_month') == 11 ? ' selected=""' : '' ?>><?= _t('view.auth.noyabrya', 'ноября') ?></option>
                              <option value="12"<?= old('birth_month') == 12 ? ' selected=""' : '' ?>><?= _t('view.auth.dekabrya', 'декабря') ?></option>
                            </select>
                          <select class="form-control" name="birth_year" id="birth_year">
                              <option value="0"><?= _t('view.auth.god', 'год') ?></option>
                              <?php for($i=2010;$i>=1944;$i--){ ?>
                              <option value="<?= $i ?>" <?= old('birth_year') == $i ? ' selected=""' : '' ?>><?= $i ?></option>
                              <?php } ?>
                            </select>
                            </div>
                              <div class="text-error"><?= request()->errors->birth_day ?></div>
                              <div class="text-error"><?= request()->errors->birth_month ?></div>
                              <div class="text-error"><?= request()->errors->birth_year ?></div>
                          </div>
                        </div>
                        <div class="form-group row">
                          <label class="col-sm-3 form-control-label"><?= _t('view.auth.Pol', 'Пол*') ?></label>
                          <div class="col-sm-9">
                            <div class="btn-group" style="width: 100%;">
                            <div class="i-checks">
                              <input id="radioCustom1" type="radio" value="male" name="sex"  <?= old('sex') == 'male' ? ' checked=""' : '' ?>class="radio-template">
                              <label for="radioCustom1"><?= _t('view.auth.Muzhskoy', 'Мужской') ?></label>
                            </div>
                            <div class="i-checks">
                              <input id="radioCustom2" type="radio" value="female" <?= old('sex') == 'female' ? ' checked=""' : '' ?> name="sex" class="radio-template">
                              <label for="radioCustom2"><?= _t('view.auth.ZHenskiy', 'Женский') ?></label>
                            </div>
                            </div>
                           <div class="text-error"><?= request()->errors->sex ?></div>
                          </div>
                        </div>
                        
                        <div class="form-group row">
                          <label class="col-sm-3 form-control-label" onchange="formCityChange()" id="form_project_city_label"><?= _t('view.auth.Gorod', 'Город') ?></label>
                          
                          <div class="col-sm-9">
                            <div class="form-item" id="formSelectLocation">
                            <div class="my-auto select-field" style="width: 100%;">
                              <?php if (isset($data->obj_city)) { ?>
                              <div data-href="" data-id="<?= $data->obj_city->id ?>" data-name="<?= $data->obj_city->full_name ?>" data-trans="<?= $data->obj_city->trans ?>" data-type="<?= $data->obj_city->className ?>" id="btn-select-7" class="form-control block-select" onclick="clickSelectLocation('btn-select-7')" data-type-select="location" data-show-bottom="1">
                                <span data-type-select="location"><?= $data->obj_city->full_name ?></span>
                                <input type="hidden" name="city_id" value="<?= $data->obj_city->id ?>" /> 
                              </div>
                              <?php } elseif (isset($data->obj_area)) { ?>
                              <div data-href="" data-id="<?= $data->obj_area->id ?>" data-name="<?= $data->obj_area->full_name ?>" data-trans="<?= $data->obj_area->trans ?>" data-type="<?= $data->obj_area->className ?>" id="btn-select-7" class="form-control block-select" onclick="clickSelectLocation('btn-select-7')" data-type-select="location" data-show-bottom="1">
                                <span data-type-select="location"><?= $data->obj_area->full_name ?></span>
                                <input type="hidden" name="area_id" value="<?= $data->obj_area->id ?>" /> 
                              </div>
                              <?php } elseif (isset($data->obj_region)) { ?>
                              <div data-href="" data-id="<?= $data->obj_region->id ?>" data-name="<?= $data->obj_region->full_name ?>" data-trans="<?= $data->obj_region->trans ?>" data-type="<?= $data->obj_region->className ?>" id="btn-select-7" class="form-control block-select" onclick="clickSelectLocation('btn-select-7')" data-type-select="location" data-show-bottom="1">
                                <span data-type-select="location"><?= $data->obj_region->full_name ?></span>
                                <input type="hidden" name="region_id" value="<?= $data->obj_region->id ?>" /> 
                              </div>
                              <?php } elseif (isset($data->obj_country)) { ?>
                              <div data-href="" data-id="<?= $data->obj_country->id ?>" data-name="<?= $data->obj_country->full_name ?>" data-trans="<?= $data->obj_country->trans ?>" data-type="<?= $data->obj_country->className ?>" id="btn-select-7" class="form-control block-select" onclick="clickSelectLocation('btn-select-7')" data-type-select="location" data-show-bottom="1">
                                <span data-type-select="location"><?= $data->obj_country->full_name ?></span>
                                <input type="hidden" name="country_id" value="<?= $data->obj_country->id ?>" /> 
                              </div>
                              <?php } else { ?>
                              <div id="btn-select-7" class="form-control block-select" onclick="clickSelectLocation('btn-select-7')" data-type-select="location" data-show-bottom="1">
                                <span data-type-select="location"><?= _t('view.auth.Mestopolozhenie', 'Местоположение') ?></span>
                              </div>
                              <?php } ?>
                            </div>                         
                          </div>
                          
                          <div class="text-error"><?= request()->errors->city ?></div></div>
                        </div>
                        <!--
                        <div class="form-group row">
                          <label class="col-sm-3 form-control-label"><?= _t('view.auth.Ulica,_Nomer_Doma', 'Улица, Номер Дома') ?></label>
                          <div class="col-sm-9">
                            <input name="address" value="<?= old('address') ?>" type="text" placeholder="Улица, Номер Дома" class="form-control" />
                           <div class="text-error"><?= request()->errors->address ?></div>
                          </div>
                        </div>
                        -->
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
                        <div class="form-group row">
                          <div class="col">
                            <div class="i-checks">
                              <input type="checkbox" <?= old('accept_terms') ? 'checked=""' : '' ?> id="accept_terms" name="accept_terms" value="1" validate="true" class="checkbox-template" />
                              <label for="accept_terms"><?= _t('view.auth.Registriruyas_v_servise,_vy_prinimaete', 'Регистрируясь в сервисе, вы принимаете') ?> <a <?= attrs_route('terms.index') ?>><?= _t('view.auth.Polzovatelskoe_soglashenie', 'Пользовательское соглашение') ?></a></label>
                            <div class="text-error"><?= request()->errors->accept_terms ?></div>
                           </div>
                          </div>
                        </div>
                        <div class="form-group">       
                          <input type="submit" value="Зарегистрироваться" class="btn btn-primary">
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
              </div>
            </div>
           </div>
<?php $this->end(); ?>