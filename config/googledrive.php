<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Google Drive API Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration options for Google Drive API
    | integration. You need to set up a Google Cloud Project and enable
    | the Google Drive API to use these features.
    |
    */

    // OAuth Configuration (legacy)
    'client_id' => env('GOOGLE_DRIVE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_DRIVE_CLIENT_SECRET'),
    'refresh_token' => env('GOOGLE_DRIVE_REFRESH_TOKEN'),
    
    // Service Account Configuration (preferred)
    'service_account_path' => env('GOOGLE_DRIVE_SERVICE_ACCOUNT_PATH') 
        ? (str_starts_with(env('GOOGLE_DRIVE_SERVICE_ACCOUNT_PATH'), '/') 
            ? env('GOOGLE_DRIVE_SERVICE_ACCOUNT_PATH') 
            : storage_path(env('GOOGLE_DRIVE_SERVICE_ACCOUNT_PATH')))
        : storage_path('app/public/ensino-certo-da37111e688c.json'),
    'use_service_account' => env('GOOGLE_DRIVE_USE_SERVICE_ACCOUNT', false),
    
    'folder_id' => env('GOOGLE_DRIVE_FOLDER_ID'),
    'shared_drive_id' => env('GOOGLE_DRIVE_SHARED_DRIVE_ID'),
    
    /*
    |--------------------------------------------------------------------------
    | Google Drive Scopes
    |--------------------------------------------------------------------------
    |
    | The OAuth 2.0 scopes that your application requests determine
    | the level of access your application has to the user's Google Drive.
    |
    */
    
    'scopes' => [
        'https://www.googleapis.com/auth/drive',
        'https://www.googleapis.com/auth/drive.file',
    ],
    
    /*
    |--------------------------------------------------------------------------
    | File Upload Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for file uploads
    |
    */
    
    'max_file_size' => env('GOOGLE_DRIVE_MAX_FILE_SIZE', 10485760), // 10MB default
    'allowed_extensions' => [
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
        'jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg',
        'txt', 'rtf', 'zip', 'rar', '7z'
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Folder Structure
    |--------------------------------------------------------------------------
    |
    | Configuration for automatic folder organization
    |
    */
    
    'auto_create_folders' => env('GOOGLE_DRIVE_AUTO_CREATE_FOLDERS', true),
    'student_folder_template' => '{student_name} - {student_cpf}',
    'organization_folders' => [
        'Documentos Pendentes',
        'Documentos Aprovados',
        'Contratos',
        'Históricos Escolares',
        'Certificados'
    ],
]; 