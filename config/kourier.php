<?php

return [
    'storage' => [
        'disk' => env('DATASET_FILESYSTEM_DISK', env('FILESYSTEM_DISK', 'local')),
        'artifact_path' => 'datasets/artifacts',
        'export_path' => 'datasets/exports',
    ],
];
