<?php

if (cfr('PING')) {
    if ($system->getConfigOption('PING_ENABLED')) {
        $ipTools = new IPTools();
        show_window(__('ICMP ping'), $ipTools->renderIpForm('Ping it'));

        if ($ipTools->catchIp()) {
            show_window(__('ICMP ping result'), $ipTools->runIcmpPing());
        }
    } else {
        show_error(__('This module is disabled'));
    }
} else {
    show_error(__('Access denied'));
}