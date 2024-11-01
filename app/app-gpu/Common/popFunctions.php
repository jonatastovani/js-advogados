<?php

class popFunctions {

    public function insertHiddenPopData($arr) {
        $popName = $arr['popName'];
        $popId = isset($arr['popId'])?$arr['popId']:'';
        $popAction = isset($arr['popAction'])?$arr['popAction']:'';

        $strReturn = '
        <form id="formPopDataHidden">
            <input type="hidden" name="popName" value="'.$popName.'">
            <input type="hidden" name="popId" value="'.$popId.'">
            <input type="hidden" name="popAction" value="'.$popAction.'">
        </form>';

        return $strReturn;
    }

}
