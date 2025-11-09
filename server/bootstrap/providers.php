<?php
// so the providers is called like this when the app.php load
// laravel calls PackageManifest class what reads all install packages on the 
// composer.json 

// "post-autoload-dump": [
//     "Illuminate\\Foundation\\ComposerScripts::postAutoloadDump",
//     "@php artisan package:discover --ansi"
// ]
// this is what get the composer for that package or services to run the
//  "providers": [
//             "Some\\Package\\ServiceProvider"
//         ]
//         that load the service

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\FortifyServiceProvider::class,
];
