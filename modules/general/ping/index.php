<?php

if (cfr('PING')) {

    $ipTools = new IPTools();
    show_window(__('ICMP ping'), $ipTools->renderIpForm('Ping'));

    //run ping subroutine
    if ($ipTools->catchIp()) {
        show_window(__('ICMP ping result'), $ipTools->runIcmpPing());
    }
    
} else {
    show_error(__('Access denied'));
}