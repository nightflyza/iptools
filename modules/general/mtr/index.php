<?php

if (cfr('MTR')) {
if ($system->getConfigOption('MTR_ENABLED')) {
    $ipTools = new IPTools();
    show_window(__('MTR'), $ipTools->renderIpForm('mtr this!'));


    if ($ipTools->catchIp()) {
        show_window(__('mtr results'), $ipTools->runMtr());
    }
} else {
      show_error(__('This module is disabled'));
    }
} else {
    show_error(__('Access denied'));
}