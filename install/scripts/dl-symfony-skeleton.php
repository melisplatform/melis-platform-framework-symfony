<?php

$isCliReqs = php_sapi_name() == 'cli' ? true : false;
//third party Lumen
$thirdPartyFolder = !$isCliReqs ? $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'thirdparty/Symfony' : 'thirdparty/Symfony';

if (!is_dir($thirdPartyFolder)) {
    return MelisPlatformFrameworks\Support\MelisPlatformFrameworks::downloadFrameworkSkeleton('symfony');
}else{
    return [
        'success' => true,
        'message' => 'Symfony skeleton downloaded successfully'
    ];
}