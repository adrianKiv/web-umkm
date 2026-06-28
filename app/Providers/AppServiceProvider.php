<?php

namespace App\Providers;

use App\Listeners\AttachUserActivitiesToLogin;
use Illuminate\Auth\Events\Login;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;

//Import library untuk Azure Blob Storage
use Illuminate\Support\Facades\Storage;
use League\Flysystem\AzureBlobStorage\AzureBlobStorageAdapter;
use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use League\Flysystem\Filesystem;
use Illuminate\Filesystem\FilesystemAdapter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (App::environment('production')) {
            URL::forceScheme('https');
        }

        Paginator::useBootstrapFive();

        Event::listen(Login::class, AttachUserActivitiesToLogin::class);

        //Registrasi driver 'azure' agar dikenali oleh Laravel
        Storage::extend('azure', function ($app, $config) {
            $client = BlobRestProxy::createBlobService($config['connection_string']);
            $adapter = new AzureBlobStorageAdapter($client, $config['container']);

            return new FilesystemAdapter(
                new Filesystem($adapter, $config),
                $adapter,
                $config
            );
        });
    }
}
