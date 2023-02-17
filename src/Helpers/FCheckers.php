<?php
/*
 * Copyright © 2023. mPhpMaster(https://github.com/mPhpMaster) All rights reserved.
 */

if( !function_exists('isCurrentAction') ) {
    /**
     * get current route
     *
     * @return bool
     */
    function isCurrentAction($mode): bool
    {
        return strtolower(trim($mode)) == strtolower(trim(currentAction()));
    }
}