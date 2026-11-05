<?php

/**
 * Copy this file to config.php and fill in your values.
 * Every value can also be set by the environment variable shown next to it;
 * environment variables take precedence over config.php.
 */
return [
    // Steam Web API key, https://steamcommunity.com/dev/apikey          (OJ_STEAM_API_KEY)
    'steam_api_key' => '',

    // Public base URL of the site, used for shareable links.            (OJ_BASE_URL)
    // Leave empty to derive it from the request.
    // 'base_url' => 'https://stats.example.com',

    // SQLite database caching global daily stats.                       (OJ_DATABASE)
    // 'database' => __DIR__ . '/var/global-stats.sqlite',

    // First UTC day with global history on Steam.                      (OJ_EARLIEST_DATE)
    // 'earliest_date' => '2014-05-31',

    // Timeout in seconds of one Steam API request.                      (OJ_HTTP_TIMEOUT)
    // 'http_timeout' => 15,
];
