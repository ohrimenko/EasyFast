<?php

use \Base\Base\Request;
use \Base\Base\Route;
use \App\Components\Show;

data()->data->isViewMeta = true;

?><meta charset="utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge" />

<meta name="csrf-token" content="<?= key_token(); ?>" />

<meta name="theme-color" content="white" />

<?php if($data->{'title'}){ ?><title><?= $data->{'title'} ?></title><?php } ?>

<?php if($data->{'viewport'}){ ?><meta name="viewport" content="<?= $data->{'viewport'} ?>" /><?php } ?>

<?php if($data->{'country-code'}){ ?><meta name="country-code" content="<?= $data->{'country-code'} ?>" /><?php } ?>
<?php if($data->{'assets-base'}){ ?><meta name="assets-base" content="<?= $data->{'assets-base'} ?>" /><?php } ?>
<?php if($data->{'description'}){ ?><meta name="description" content='<?= $data->{'description'} ?>' /><?php } ?>
<?php if($data->{'keywords'}){ ?><meta name="keywords" content="<?= $data->{'keywords'} ?>" /><?php } ?>
<?php if($data->{'X-UA-Compatible'}){ ?><meta http-equiv="X-UA-Compatible" content="<?= $data->{'X-UA-Compatible'} ?>" /><?php } ?>
<?php if($data->{'viewport'}){ ?><meta name="viewport" content="<?= $data->{'viewport'} ?>" /><?php } ?>

<?php if($data->{'robots'}){ ?><meta name="robots" content="<?= $data->{'robots'} ?>" /><?php } ?>

<link rel="shortcut icon" href="<?= $data->{'shortcut_icon'} ?>" />
    
<?php if($data->{'apple-touch-icon'}){ ?><link rel="apple-touch-icon" href="/img/f-192x192.png" /><?php } ?>

<?php if($data->{'og:title'}){ ?><meta property="og:title" content="<?= $data->{'og:title'} ?>" /><?php } ?>
<?php if($data->{'og:type'}){ ?><meta property="og:type" content="<?= $data->{'og:type'} ?>" /><?php } ?>
<?php if($data->{'og:image'}){ ?><meta property="og:image" content="<?= $data->{'og:image'} ?>" /><?php } ?>
<?php if($data->{'og:image:width'}){ ?><meta property="og:image:width" content="<?= $data->{'og:image:width'} ?>" /><?php } ?>
<?php if($data->{'og:image:height'}){ ?><meta property="og:image:height" content="<?= $data->{'og:image:height'} ?>" /><?php } ?>
<?php if($data->{'og:url'}){ ?><meta property="og:url" content="<?= $data->{'og:url'} ?>" /><?php } ?>
<?php if($data->{'og:description'}){ ?><meta property="og:description" content="<?= $data->{'og:description'} ?>" /><?php } ?>
<?php if($data->{'og:site_name'}){ ?><meta property="og:site_name" content="<?= $data->{'og:site_name'} ?>" /><?php } ?>

<?php if($data->{'twitter:card'}){ ?><meta name="twitter:card" content="<?= $data->{'twitter:card'} ?>" /><?php } ?>
<?php if($data->{'twitter:site'}){ ?><meta name="twitter:site" content="<?= $data->{'twitter:site'} ?>" /><?php } ?>
<?php if($data->{'twitter:title'}){ ?><meta name="twitter:title" content="<?= $data->{'twitter:title'} ?>" /><?php } ?>
<?php if($data->{'twitter:description'}){ ?><meta name="twitter:description" content="<?= $data->{'twitter:description'} ?>" /><?php } ?>
<?php if($data->{'twitter:image'}){ ?><meta name="twitter:image" content="<?= $data->{'twitter:image'} ?>" /><?php } ?>

<?php if($data->{'canonical'}){ ?><link rel="canonical" href="<?= $data->{'canonical'} ?>" /><?php } ?>
<?php if($data->{'prev'}){ ?><link rel="prev" href="<?= $data->{'prev'} ?>" /><?php } ?>
<?php if($data->{'next'}){ ?><link rel="next" href="<?= $data->{'next'} ?>" /><?php } ?>
<?php if($data->{'apple-touch-icon'}){ ?><link rel="apple-touch-icon" sizes="180x180" href="<?= $data->{'apple-touch-icon'} ?>" /><?php } ?>
<?php if($data->{'manifest'}){ ?><link rel="manifest" href="<?= $data->{'manifest'} ?>" /><?php } ?>
<?php if($data->{'mask-icon'}){ ?><link rel="mask-icon" href="<?= $data->{'mask-icon'} ?>" color="#5bbad5" /><?php } ?>
<?php if($data->{'image_src'}){ ?><link rel="image_src" href="<?= $data->{'image_src'} ?>" /><?php } ?>