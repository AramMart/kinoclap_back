<?php

namespace App\Providers;

use Google\Cloud\Storage\StorageClient;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use \League\Flysystem\FileSystem;
use Superbalist\Flysystem\GoogleStorage\GoogleStorageAdapter;

class GoogleCloudProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Storage::extend('gcs', function($app, $config) {
            $storageClient = new StorageClient([
                'projectId' => $config['project_id'],
                'keyFilePath' => $config['key_file']
            ]);
            $bucket = $storageClient->bucket($config['bucket']);
            $adapter = new GoogleStorageAdapter($storageClient, $bucket);
            return new FileSystem($adapter);
        });
    }
}
