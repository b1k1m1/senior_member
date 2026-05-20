<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Organization Information
    |--------------------------------------------------------------------------
    |
    | This information will be used in receipts, welcome letters, and other documents.
    |
    */
    
    'name' => env('ORG_NAME', 'Your Organization Name'),
    'address' => env('ORG_ADDRESS', '123 Organization Street, City, State ZIP'),
    'phone' => env('ORG_PHONE', '(555) 123-4567'),
    'email' => env('ORG_EMAIL', 'info@organization.com'),
    'website' => env('ORG_WEBSITE', 'www.organization.com'),
    'tax_id' => env('ORG_TAX_ID', 'XX-XXXXXXX'),
    'registration_no' => env('ORG_REGISTRATION_NO', 'REG123456'),
    
    /*
    |--------------------------------------------------------------------------
    | Founder & President Information
    |--------------------------------------------------------------------------
    */
    'founder_name' => env('ORG_FOUNDER_NAME', 'John Founder'),
    'founder_title' => env('ORG_FOUNDER_TITLE', 'Founder President'),
    'founder_photo' => env('ORG_FOUNDER_PHOTO', 'images/org/founder.jpg'),
    
    /*
    |--------------------------------------------------------------------------
    | Current Office Bearers
    |--------------------------------------------------------------------------
    */
    'office_bearers' => [
        'president' => ['name' => 'President Name', 'title' => 'President'],
        'vice_president' => ['name' => 'VP Name', 'title' => 'Vice President'],
        'secretary' => ['name' => 'Secretary Name', 'title' => 'Secretary'],
        'treasurer' => ['name' => 'Treasurer Name', 'title' => 'Treasurer'],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Welcome Message
    |--------------------------------------------------------------------------
    */
    'welcome_message' => env('ORG_WELCOME_MESSAGE', 'Welcome to our organization! We are delighted to have you as a member.'),
    
    /*
    |--------------------------------------------------------------------------
    | Receipt Settings
    |--------------------------------------------------------------------------
    */
    'receipt_prefix' => env('RECEIPT_PREFIX', 'RCP'),
    'currency_symbol' => env('CURRENCY_SYMBOL', '$'),
];
