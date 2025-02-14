<?php

return [


    /*
    |--------------------------------------------------------------------------
    | Web Application Queue Name
    |--------------------------------------------------------------------------
    |
    | Queue Name of Web Application Jobs
    |
    */

    'app' => env('APP_QUEUE_NAME', 'app'),

    /*
    |--------------------------------------------------------------------------
    | Redis Separator
    |--------------------------------------------------------------------------
    |
    | Redis Separator
    |
    */
    'redis_separator' => ':',

    /*
    |--------------------------------------------------------------------------
    | Redis Separator
    |--------------------------------------------------------------------------
    |
    | Redis Separator
    |
    */
    'redis_config_app' => 'config',

    /*
    |--------------------------------------------------------------------------
    | Redis Separator
    |--------------------------------------------------------------------------
    |
    | Redis Separator
    |
    */
    'redis_config_app_refresh_time' => 120,

    /*
    |--------------------------------------------------------------------------
    | Redis Separator
    |--------------------------------------------------------------------------
    |
    | Redis Separator
    |
    */
    'redis_cache_app' => 'cache',

    /*
    |--------------------------------------------------------------------------
    | Total Elements for Process Recycle
    |--------------------------------------------------------------------------
    |
    | Total Elements for Process Recycle
    |
    */
    'elements_for_process_recycle' => 10000,

    /*
    |--------------------------------------------------------------------------
    | Redis Separator
    |--------------------------------------------------------------------------
    |
    | Redis Separator
    |
    */
    'prefix_workers' => 'workers',

    /*
    |--------------------------------------------------------------------------
    | GEO IP CONFIGURATIONS
    |--------------------------------------------------------------------------
    |
    | GEO IP MAX MIND CONFIGURATIONS
    |
    */
    //'path_local_database_file' => '/dev/shm/GeoLite2-ASN.mmdb',
    'url_database_file' => 'http://192.168.183.239/GeoLite2-ASN.tar.gz',
    'path_local_database_file' => storage_path('app/geolite/') . 'GeoLite2-ASN.mmdb',
    'database_filename' => 'GeoLite2-ASN.mmdb',
    'path_local_database_directory' => storage_path('app/geolite/'),
    'path_local_database_temp_gzfile' => storage_path('app/geolite/') . 'GeoLite2-ASN.mmdb.tar.gz',
    'path_local_database_temp_tarfile' => storage_path('app/geolite/') . 'GeoLite2-ASN.mmdb.tar',
    'expiration_time' => 604800, //7 days

    /*
    |--------------------------------------------------------------------------
    | CSV IMPORT CONFIGURATIONS
    |--------------------------------------------------------------------------
    |
    | CSV Import configurations
    |
    */
    'stats_enabled' => true,
    'stats_key' => 'stats:csv-import',

    //const PATH_LOCAL_DATABASE_FILE = '/dev/shm/GeoLite2-ASN.mmdb';
    //const PATH_LOCAL_DATABASE_FILE = '/Users/lordmaster/GeoLite2-ASN.mmdb';


    //const DATABASE_FILENAME = 'GeoLite2-ASN.mmdb';


    //const PATH_LOCAL_DATABASE_DIRECTORY = '/dev/shm';
    //const PATH_LOCAL_DATABASE_DIRECTORY = '/Users/lordmaster';


    //const PATH_LOCAL_DATABASE_TEMP_GZFILE = '/dev/shm/GeoLite2-ASN.mmdb.tar.gz';
    //const PATH_LOCAL_DATABASE_TEMP_GZFILE = '/Users/lordmaster/GeoLite2-ASN.mmdb.tar.gz';


    //const PATH_LOCAL_DATABASE_TEMP_TARFILE = '/dev/shm/GeoLite2-ASN.mmdb.tar';
    //const PATH_LOCAL_DATABASE_TEMP_TARFILE = '/Users/lordmaster/GeoLite2-ASN.mmdb.tar';
];
