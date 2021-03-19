<?php

if (cfr('TRACEROUTE')) {
    if ($system->getConfigOption('TRACEROUTE_ENABLED')) {
        $ipTools = new IPTools();
        show_window(__('traceroute'), $ipTools->renderIpForm('traceroute'));


        if ($ipTools->catchIp()) {
            show_window(__('traceroute results'), $ipTools->runTraceroute());
        }
    } else {
        show_error(__('This module is disabled'));
    }
} else {
    show_error(__('Access denied'));
}