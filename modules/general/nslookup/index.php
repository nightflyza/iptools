<?php

if (cfr('NSLOOKUP')) {

    $ipTools = new IPTools();
    show_window(__('DNS lookup'), $ipTools->renderIpForm('nslookup'));


    if ($ipTools->catchIp()) {
        show_window(__('DNS lookup results'), $ipTools->runDnsLookup());
    }
} else {
    show_error(__('Access denied'));
}