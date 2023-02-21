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
<?php $this->view('auth/remember-form') ?>
<?php $this->end(); ?>


<?php $this->append('block-sidebar'); ?>          
<?php $this->view('sidebar/help-remember') ?>
<?php $this->end(); ?>