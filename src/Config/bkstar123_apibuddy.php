<?php
/**
 * bkstar123_apibuddy.php
 *
 * @author: tuanha
 * @last-mod: 11-July-2019
 */

return [
    'cache_duration' => env('API_BUDDY_CACHE_DURATION', 30), // API response caching time in seconds
    'max_per_page' => env('API_BUDDY_MAX_PER_PAGE', 1000), // max per_page that user can specify
    'default_per_page' => env('API_BUDDY_DEFAULT_PER_PAGE', 10), // default per_page if not specified
    'replace_exceptionhandler' => true
];