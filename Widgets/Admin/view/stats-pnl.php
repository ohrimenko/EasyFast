<?php

use \Base\Base\Request;
use \Base\Base\Route;
use \App\Components\Show;
use \Base\Base\DB;

$cp = 0;

if (function_exists('getrusage')) {
    $dat = getrusage();
    $cp = (((float)($dat["ru_utime.tv_usec"] + (float)$dat["ru_stime.tv_usec"]))/1000000);
}

?><style>
.trcp_show_small {
    display: none;
    margin-right: 3px;
}
@media (min-width: 800px) {
    .trcp_show_small {
        display: inline;
    }
}
@media (max-width: 800px) {
    .trcp_hidden_small {
        display: none;
    }
    .trcp_show_small {
        display: inline;
    }
}
@media (max-width: 400px) {
    .trcp_show_small {
        display: none;
        margin-right: 1px;
    }
}
.btnkOpenTrcpAdmnPanel
{
    border: 1px solid black;
}
.btnkOpenTrcpAdmnPanel:hover {
    background-color: #888888!important;
    border: 1px solid #000!important;
    color: #242328!important;
}
</style>
<div id="trcpAdmnIframeElement"  style="overflow: hidden!important; z-index: 99999999999999999;">
<div id="trcpAdmnIframeElementPanel" style="box-shadow: 0 0 15px rgba(20,20,20,0.9);
    overflow: hidden!important; 
    white-space: nowrap; 
    font-size: 13px; 
    color: #3b3131;
    border-top: 1px solid rgba(135,135,135); 
    z-index: 99999999999999999;
    background-color: #DCDCDC;
    height: 36px;
    display: flex;align-items: center;
    padding: 0px 6px 0px 6px!important;
    position: fixed;
    bottom: 0px;
    left: 0px;
    right: 0px;">
<span style="margin-left: 12px;"><span class="trcp_show_small" title="Время генерации страницы"><img style="height: 16px;" src="/img/clock_time_1230.png" /></span><span class="trcp_hidden_small">время генерации страницы:</span> <?= round(microtime(true) - app()->begin_time, 2) ?> с.</span>
<span style="margin-left: 12px;"><span class="trcp_show_small" title="Количество запросов к БД"><img style="height: 16px;" src="/img/database_8287.png" /></span><span class="trcp_hidden_small">количество запросов к БД:</span> <?= DB::getCountQuery() ?></span>
<span style="margin-left: 12px;"><span class="trcp_show_small" title="Память"><img style="height: 16px;" src="/img/circuit-memory_7837.png" /></span><span class="trcp_hidden_small">память:</span> <?= round(memory_get_usage()*0.000001, 2) ?>Mb.</span>
<span style="margin-left: 12px;"><span class="trcp_show_small" title="Время на запросы к БД"><img style="height: 16px;" src="/img/db_add_1035.png" /></span><span class="trcp_hidden_small">время на запросы к БД:</span> <?= round(DB::getDurationQuery(), 2) ?> с.</span>
<span style="margin-left: 12px;"><span class="trcp_show_small" title="Время затраченное на php"><img style="height: 16px;" src="/img/source_php_9624.png" /></span><span class="trcp_hidden_small">время затраченное на php:</span> <?= round($cp, 2) ?> с.</span>
</div>
</div>
<script>
setTimeout(function () {document.body.style.paddingBottom = '36px';}, 1);
</script>