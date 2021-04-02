<?php

if (cfr('EPC')) {
    if ($system->getConfigOption('EPC_ENABLED')) {
        
        $help=__('Set on your device "O" (Offset) = 0, "M" (Multiplier) = 1023 and Averaging=100 before calibration');

        
        $inputs = wf_TextInput('temp1', __('Temperature') . ' 1', ubRouting::post('temp1'), false, 5, 'digits') . ' ';
        $inputs .= wf_TextInput('val1', __('Value') . ' 1', ubRouting::post('val1'), true, 5, 'digits') . ' ';
        $inputs .= wf_TextInput('temp2', __('Temperature') . ' 2', ubRouting::post('temp2'), false, 5, 'digits') . ' ';
        $inputs .= wf_TextInput('val2', __('Value') . ' 2', ubRouting::post('val2'), true, 5, 'digits') . ' ';
        $inputs .= wf_Submit(__('Calculate'));

        $form = wf_Form('', 'POST', $inputs, 'glamour');
        
        show_window(__('How to use'),$help);
        show_window(__('PING3 calibration'), $form);
        
        
        if (ubRouting::checkPost(array('temp1', 'val1', 'temp2', 'val2'))) {
            $temp1 = ubRouting::post('temp1');
            $val1 = ubRouting::post('val1');
            $temp2 = ubRouting::post('temp2');
            $val2 = ubRouting::post('val2');

            $offset = $temp1 - (($temp2 - $temp1) / ($val2 - $val1)) * $val1;
            $multiplier = (($temp2 - $temp1) / ($val2 - $val1)) * 1023;
            
            $result= wf_img('skins/tsan1_corr.png');
            $result.= wf_delimiter(0);
            $result.= wf_tag('hr');
            $result.= wf_tag('h2').__('Fixed Offset').': '.round($offset,2).wf_tag('h2');
            $result.= wf_tag('h2').__('Fixed Multiplier').': '.round($multiplier,2).wf_tag('h2');
            
            show_window(__('Result'), $result);
            
        }
    } else {
        show_error(__('This module is disabled'));
    }
} else {
    show_error(__('Access denied'));
}    