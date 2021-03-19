<?php

$externalIp = $_SERVER['REMOTE_ADDR'];

$result = '';

$externalIpLabel = wf_tag('center') . wf_tag('h1') . $externalIp . wf_tag('h1', true) . wf_tag('center', true) . wf_tag('br');
$result .= $externalIpLabel;

if (ubRouting::checkGet('whoisdata')) {
    if (!empty($externalIp)) {
        $result .= wf_Link('?module=whatismyip', 'Less info', true, 'ubButton');
        $whois = new UbillingWhois($externalIp);
        $result .= $whois->renderData();
    } else {
        show_error(__('Something went wrong'));
    }
} else {
    $result .= wf_Link('?module=whatismyip&whoisdata=true', 'More info', true, 'ubButton');
}

show_window('Your external IP', $result);
