<?php

if (cfr('WHOIS')) {

    $ipTools = new IPTools();
    show_window(__('WHOIS'), $ipTools->renderIpForm('who is this?'));


    if ($ipTools->catchIp()) {
        show_window(__('whois results'), $ipTools->runWhois());
    }
} else {
    show_error(__('Access denied'));
}