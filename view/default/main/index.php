<?php

use \Base\Base\Request;
use \Base\Base\Route;
use \App\Components\Show;
use \App\Components\ReqDetect;

?><?php $this->view('layouts/app'); ?>

<?php $this->section('body'); ?>
<h1>Site Page</h1>
<div>
<a target="_blank" href="<?= route("admin.index") ?>"><h2>GO TO ADMIN PANEL</h2></a>
</div>
<?php $this->end(); ?>
