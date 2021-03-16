<?php

if (cfr('MTR')) {

    $ipTools = new IPTools();
    show_window(__('MTR'), $ipTools->renderIpForm('mtr this!'));


    if ($ipTools->catchIp()) {
        show_window(__('mtr results'), $ipTools->runMtr());
    }
} else {
    show_error(__('Access denied'));
}