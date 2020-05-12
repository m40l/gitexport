<?php

return [
    /*
     * Holds the hash of the current head
     */
    'hash' => env('COMMIT_HASH'),

    /*
     * Holds the commit date of the current head
     */
    'date' => env('COMMIT_DATE'),

    /*
     * Tells the @githash directive the default length to truncate the git hash to
     */
    'hash_length' => 7,

    /*
     * Tells the @gitdate directive the default format to display the git commit date as
     */
    'commit_date_format' => 'U',
];
