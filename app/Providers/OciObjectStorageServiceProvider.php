<?php

namespace App\Providers;

use Aws\S3\S3Client;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter as S3Adapter;
use Illuminate\Filesystem\AwsS3V3Adapter;
// use League\Flysystem\Filesystem;
use Illuminate\Filesystem\Filesystem;
// use League\Flysystem\FilesystemAdapter;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Arr;
use \League\Flysystem\Filesystem as Flysystem;
use Storage;
use Log;

class OciObjectStorageServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */

    public function boot()
        {
            if (config('filesystems.default') != 'oci') {
                return;
            }
            Storage::extend('s3', function($app, $config) {

                // illuminateのAwsS3V3AdapterでOCIのbucket名をパスのprefixに使うための設定
                $config['directory_separator'] = '/';
                $config['prefix'] = $config['prefix'] ?? null;
                if($config['use_path_style_endpoint']) {
                    $config['prefix'] = $config['bucket']. $config['prefix'];
                }
                
                Log::debug($config);
                $client = new S3Client([
                    'credentials' => [
                        'key'    => $config['key'],
                        'secret' => $config['secret'],
                    ],
                    'region' => $config['region'],
                    'version' => '2006-03-01',
                    'bucket_endpoint' => true,
                    'use_path_style_endpoint' => true,
                    'endpoint' => $config['endpoint'],
                ]);
                
                // S3クライアント
                $s3_adapter = new S3Adapter($client, $config['bucket'], $config['prefix']);
                
                $fs_driver = new Filesystem(
                    $s3_adapter,
                    $config,
                );

                // \Illuminate\Filesystem\FilesystemManager.php createflysystemのパクリ
                $flysystem_adapter = new Flysystem($s3_adapter, Arr::only($config, [
                    'directory_visibility',
                    'disable_asserts',
                    'temporary_url',
                    'url',
                    'visibility',
                ]));

                // IlluminateのAwsS3V3Adapter
                $adapter = new AwsS3V3Adapter($flysystem_adapter, $s3_adapter, $config, $client);
                
                // throw new \Exception('test');
                return $adapter;

                $fs_driver::macro('getUrl', function (string $path) {
                    return config('filesystems.disks.oci.url') . '/' . $path;
                });

                $fs_adapter = new FilesystemAdapter(
                    $fs_driver,
                    $adapter,
                    $config,
                );
                return $fs_adapter;
            });
        }
}
