<?php

namespace Jedymatt\LaravelEnvSail\Console;

use Laravel\Sail\Console\InstallCommand;
use Symfony\Component\Yaml\Yaml;


class EnvSailCommand extends InstallCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'env-sail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Configure the environment variables for the application';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Create .env file if it doesn't exist
        if (!file_exists($this->laravel->basePath('.env'))) {
            $this->warn('No .env file found. Creating .env file from .env.example');
            copy($this->laravel->basePath('.env.example'), $this->laravel->basePath('.env'));
        }

        if (!file_exists($this->laravel->basePath('docker-compose.yml'))) {
            $this->error('docker-compose.yml not found. Please run "sail:install" first.');
            return 1;
        }

        $services = $this->getSailServices();

        $this->comment('Service(s) detected from docker-compose.yml: ' . implode(', ', $services));

        $this->replaceEnvVariables($services);

        $this->info('Successfully configured .env file.');
    }

    protected function getSailServices(): array
    {
        $dockerCompose = Yaml::parseFile($this->laravel->basePath('docker-compose.yml'));

        $services = [
            'mysql',
            'pgsql',
            'mariadb',
            'redis',
            'memcached',
            'meilisearch',
            'minio',
            'mailhog',
            'selenium',
        ];

        $sailServices = array_filter($dockerCompose['services'], function ($service) use ($services) {
            return in_array($service, $services);
        }, ARRAY_FILTER_USE_KEY);

        return array_keys($sailServices);
    }
}
