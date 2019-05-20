<?php
/*
|--------------------------------------------------------------------------
| TypeRocket Routes
|--------------------------------------------------------------------------
|
| Manage your web routes here.
|
*/

tr_route()->post( '/api/v1/wp-crawler/preview', 'handle@CrawlPreview' );
