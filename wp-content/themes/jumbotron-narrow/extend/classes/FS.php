<?php

class FS {

    public static function path_to(){
        $arg = func_get_args();
        return implode(DIRECTORY_SEPARATOR, $arg);
    }

} 