<?php

return [
    'APP_NAME' => env('APP_NAME', 'Doctor Access'),
    'APP_URL' => env('APP_URL', 'localhost'),
    'YES' => 1,
    'NO' => 0,
    'SUPER_ADMIN_EMAIL_ADDRESS' => env('SUPER_ADMIN_EMAIL_ADDRESS', ''),
    'SUPER_ADMIN_FIRST_NAME' => env('SUPER_ADMIN_FULL_NAME', ''),
    'SUPER_ADMIN_PHONE_NUMBER' => env('SUPER_ADMIN_PHONE_NUMBER', ''),
    'SUPER_ADMIN_PASSWORD' => env('SUPER_ADMIN_PASSWORD', ''),
    'RESET_PASSWORD_URL' => env('RESET_PASSWORD_URL', ''),
    'SMS_KEY' => env('SMS_KEY', ''),
    'SMS_SECRET' => env('SMS_SECRET', ''),
    'SMS_MESSAGE' => 'Your two factor authentication code is : ',

    // Role Management
    'SUPER_ADMIN_ROLE_LABEL' => 'Super Admin',
    'ADMIN_ROLE_LABEL' => 'Admin',

    'SUPER_ADMIN_ROLE_SLUG' => 'super-admin',
    'ADMIN_ROLE_SLUG' => 'admin',

    'SUPER_ADMIN_ROLE_ID' => 1,
    'ADMIN_ROLE_ID' => 2,

    // Status
    'ACTIVE_FLAG' => 1,
    'INACTIVE_FLAG' => 0,
    'ARCHIVED_FLAG' => 2,

    // 2FA Checker Status
    '2FA_CHECKER_ENABLE_FLAG' => 1,
    '2FA_CHECKER_DISABLE_FLAG' => 0,
];
