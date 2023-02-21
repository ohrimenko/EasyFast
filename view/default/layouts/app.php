<?php

use \Base\Base\DB;
use \Base\Base\Request;
use \Base\Base\Route;
use \App\Components\Show;


?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8" />
  <?php $this->show('title'); ?>
    <title>Title Site</title>
  <?php $this->end(); ?>
</head>
<body>
<?php $this->block('body'); ?>    
</body>
</html>