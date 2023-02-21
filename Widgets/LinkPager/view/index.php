<?php

use \Base\Base\Request;
use \Base\Base\Route;
use \App\Components\Show;

?>
<?php if($data->pageCount > 1) { ?>
<section>
             <div class="container-fluid">
              <div class="row">
                <div class="col">
                  <nav aria-label="navigation">
                    <ul class="pagination">
                      <?php if($data->isLinkPrev) { ?>
                      <li class="page-item"><a <?= isset($data->is_href_ajax) ? 'data-href-ajax="'.routeNow(array_merge($data->params, [$data->pageName => $data->pageNow-1]), [], false, false).'"' : '' ?> <?= isset($data->data_clicks) ? ('data_clicks="' . $data->data_clicks . '"') : '' ?> rel="prev" class="page-link" <?= attrsRouteNow(array_merge($data->params, [$data->pageName => $data->pageNow-1]), $data->item) ?>><?= _t('widgets.linkpager.Predydushchaya', 'Предыдущая') ?></a></li>
                      <?php } ?>
                      <?php if($data->isLinkFirst) { ?>
                      <li class="page-item <?=1==$data->pageName ? 'active' : ''?>"><a <?= isset($data->is_href_ajax) ? 'data-href-ajax="'.routeNow(array_merge($data->params, [$data->pageName => 1]), [], false, false).'"' : '' ?> <?= isset($data->data_clicks) ? ('data_clicks="' . $data->data_clicks . '"') : '' ?> class="page-link" href="<?= attrsRouteNow(array_merge($data->params, [$data->pageName => 1]), $data->item) ?>">1</a></li>
                      <?php } ?>
                      
                      <?php if($data->isLeftSkip) { ?>
                      <li class="page-item"><a <?= isset($data->data_clicks) ? ('data_clicks="' . $data->data_clicks . '"') : '' ?> class="page-link">...</a></li>
                      <?php } ?>
                      
                      <?php if($data->isLinksCenter) { ?>
                      
                      <?php for($i = $data->pageNow - $data->countLinks; $i < $data->pageNow; $i++){if($i<1)continue; ?>
                      <li class="page-item <?=$i==$data->pageNow ? 'active' : ''?>"><a <?= isset($data->is_href_ajax) ? 'data-href-ajax="'.routeNow(array_merge($data->params, [$data->pageName => $i]), [], false, false).'"' : '' ?> <?= isset($data->data_clicks) ? ('data_clicks="' . $data->data_clicks . '"') : '' ?> class="page-link" <?= attrsRouteNow(array_merge($data->params, [$data->pageName => $i]), $data->item) ?>><?= $i ?></a></li>
                      <?php } ?>
                      
                      <li class="page-item active"><a class="page-link" href="#"><?= $data->pageNow ?></a></li>
                      
                      <?php for($i = $data->pageNow+1; $i < $data->pageNow + $data->countLinks + 1; $i++){if($i>$data->pageCount)break; ?>
                      <li class="page-item <?=$i==$data->pageNow ? 'active' : ''?>"><a <?= isset($data->is_href_ajax) ? 'data-href-ajax="'.routeNow(array_merge($data->params, [$data->pageName => $i]), [], false, false).'"' : '' ?> <?= isset($data->data_clicks) ? ('data_clicks="' . $data->data_clicks . '"') : '' ?> class="page-link" <?= attrsRouteNow(array_merge($data->params, [$data->pageName => $i]), $data->item) ?>><?= $i ?></a></li>
                      <?php } ?>
                      
                      <?php } ?>
                      
                      <?php if($data->isRightSkip) { ?>
                      <li class="page-item"><a <?= isset($data->data_clicks) ? ('data_clicks="' . $data->data_clicks . '"') : '' ?> class="page-link">...</a></li>
                      <?php } ?>
                      
                      <?php if($data->isLinkLast) { ?>
                      <li class="page-item <?=$data->countLinks==$data->pageName ? 'active' : ''?>"><a <?= isset($data->is_href_ajax) ? 'data-href-ajax="'.routeNow(array_merge($data->params, [$data->pageName => $data->pageCount]), [], false, false).'"' : '' ?> <?= isset($data->data_clicks) ? ('data_clicks="' . $data->data_clicks . '"') : '' ?> class="page-link" <?= attrsRouteNow(array_merge($data->params, [$data->pageName => $data->pageCount]), $data->item) ?>><?= $data->pageCount ?></a></li>
                      <?php } ?>
                      <?php if($data->isLinkNext) { ?>
                      <li class="page-item"><a <?= isset($data->is_href_ajax) ? 'data-href-ajax="'.routeNow(array_merge($data->params, [$data->pageName => $data->pageNow+1]), [], false, false).'"' : '' ?> <?= isset($data->data_clicks) ? ('data_clicks="' . $data->data_clicks . '"') : '' ?> rel="next" class="page-link" <?= attrsRouteNow(array_merge($data->params, [$data->pageName => $data->pageNow+1]), $data->item) ?>><?= _t('widgets.linkpager.Sleduyushchaya', 'Следующая') ?></a></li>
                      <?php } ?>
                    </ul>
                  </nav>
                </div>
              </div>
            </div>
           </section>
<?php } ?>
