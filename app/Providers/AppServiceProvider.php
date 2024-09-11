<?php

namespace App\Providers;
use App\Services\FirebaseArchiveService;
use App\Services\QrService;
use App\Services\PdfService;
use App\Services\SmsService;
use App\Services\PdfService2;
use App\Services\UserService;
use App\Services\DetteService;
use App\Services\ClientService;
use App\Services\MailerService;
use App\Services\ArticleService;
use App\Services\PaymentService;
use App\Repository\UserRepository;
use App\Repository\DetteRepository;
use App\Services\FileUploadService;
use App\Exceptions\ServiceException;
use App\Repository\ClientRepository;
use App\Services\ArticleServiceImpl;
use App\Repository\ArticleRepository;
use App\Repository\PaymentRepository;
use App\Services\AuthenticateSanctum;
use App\Services\SmsServiceInterface;
use App\Services\AuthenticatePassport;
use App\Services\UserServiceInterface;
use App\Exceptions\RepositoryException;
use App\Services\DetteServiceInterface;
use App\Services\MongoDBArchiveService;
use Illuminate\Support\ServiceProvider;
use App\Services\ClientServiceInterface;
use App\Repository\ArticleRepositoryImpl;
use App\Services\PaymentServiceInterface;
use App\Repository\UserRepositoryInterface;
use App\Repository\DetteRepositoryInterface;
use App\Repository\ClientRepositoryInterface;
use App\Services\CloudinaryFileUploadService;
use App\Providers\AuthenticateServiceProvider;
use App\Repository\PaymentRepositoryInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(AuthenticateServiceProvider::class, function ($app) {
            if (config('auth.default') === 'passport') {
                return new AuthenticatePassport();
            } else {
                return new AuthenticateSanctum();
            }
        });
        $this->app->bind(ArticleRepository::class, ArticleRepositoryImpl::class);
        $this->app->bind(ArticleService::class, ArticleServiceImpl::class);
        
        $this->app->bind(ClientRepositoryInterface::class, ClientRepository::class);
        $this->app->bind(ClientServiceInterface::class, ClientService::class);
        $this->app->singleton('fileupload', function ($app) {
            return new FileUploadService();
        });
        $this->app->singleton('fileupload2', function ($app) {
            return new CloudinaryFileUploadService();
        });
        $this->app->singleton('mail', function ($app) {
            return new MailerService();
        });
        $this->app->singleton('pdf', function ($app) {
            return new PdfService2();
        });
        $this->app->singleton('qrcode', function ($app) {
            return new QrService();
        });
        $this->app->singleton('rep_except', function ($app) {
            return new RepositoryException();
        });
        $this->app->singleton('ser_except', function ($app) {
            return new ServiceException();
        });
        $this->app->singleton('archiveService', function ($app) {
            $archiveService = env('ARCHIVAGE', 'mongodb');
            if ($archiveService ==='mongodb') {
                return new MongoDBArchiveService();
            }
            return new FirebaseArchiveService();
        });
        $this->app->singleton(DetteRepositoryInterface::class, DetteRepository::class);
        $this->app->singleton(DetteServiceInterface::class, DetteService::class);
        $this->app->bind(PaymentRepositoryInterface::class, PaymentRepository::class);

        // Lier l'interface du service à l'implémentation
        $this->app->bind(PaymentServiceInterface::class, PaymentService::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(UserServiceInterface::class, UserService::class);
        $this->app->bind(SmsServiceInterface::class, function ($app) {
            return new SmsService(
                'j3yl3j.api.infobip.com',
                'e68940ad8204c13a82fb50b7c40426af-7a507825-d1c7-4da0-b306-265040007c85'
            );
        });

        $this->app->singleton('sms-service', function ($app) {
            return $app->make(SmsServiceInterface::class);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
            
    }
}
