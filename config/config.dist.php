<?php
// Go to
// https://github.com/settings/applications#personal-access-tokens
// Personal access tokens => Generate new token
$app['config.github.token'] = '';

$app['config.github.repositories'] = [
    'doctrine/dbal',
];

$app['storage.config'] = [
    'storage.system' => 'file', // file | s3
    'file.cacheDir' => sprintf('%s/../prLog', __DIR__),
    's3.client' => [
        'key'    => 'your-aws-access-key-id',
        'secret' => 'your-aws-secret-access-key',
        'region' => 'us-east-1',
    ],
    's3.bucket' => 'repo-pr-stats',
];