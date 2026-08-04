<?php

declare(strict_types=1);

namespace Gingerminds\LaravelCore\Console\Commands\Make;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CreateResource extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:resource
        {name : Namespace/Model name (e.g. PartnerCompany/PartnerCompany)}
        {--trad-base=}
        {--api}
        {--m|migration}
        {--f|factory}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a full resource: Model, Repository, FormRequest,
    and Controller with blades, routes, and translations';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        /** @var string $name */
        $name   = $this->argument('name');
        $name   = trim($name);
        $status = Command::SUCCESS;

        if ($name === '') {
            $this->error('Name is required');
            $status = Command::FAILURE;
        }

        if ($status === Command::SUCCESS) {
            $this->info("Creating resource for: {$name}");
            $this->executeSteps($name);
        }

        return $status;
    }

    /**
     * Centralise l'exécution des différentes commandes de création.
     */
    protected function executeSteps(string $name): void
    {
        $modelOptions = ['name' => $name];
        if ($this->option('migration')) {
            $modelOptions['--migration'] = true;
        }
        if ($this->option('factory')) {
            $modelOptions['--factory'] = true;
        }

        $this->call('make:model', $modelOptions);
        $this->call('make:repository', ['name' => $name]);
        $this->call('make:form-request', ['name' => $name]);

        $controllerOptions = ['name' => $name];
        if ($this->option('trad-base')) {
            $controllerOptions['--trad-base'] = $this->option('trad-base');
        }
        $this->call('make:controller-full', $controllerOptions);
        $this->call('make:policy', ['name' => $name]);

        if ($this->option('api')) {
            $this->call('make:state-processor', ['name' => $name]);
            $this->call('make:api-provider', ['name' => $name]);
            $this->setupApi($name);
        }

        $this->info('Resource created successfully.');
    }

    /**
     * Setup API Resource in the model.
     */
    protected function setupApi(string $name): void
    {
        $modelPath = app_path('Models/' . str_replace('\\', '/', $name) . '.php');
        $stubPath  = file_exists(base_path('stubs/vendor/gingerminds-core/api-resource.stub'))
            ? base_path('stubs/vendor/gingerminds-core/api-resource.stub')
            : __DIR__ . '/../../../../stubs/api-resource.stub';
        $error = null;

        if (!file_exists($modelPath)) {
            $error = "Model not found at: {$modelPath}";
        } elseif (!file_exists($stubPath)) {
            $error = "Stub not found at: {$stubPath}";
        }

        if (!$error) {
            $modelContent = file_get_contents($modelPath);
            $stubContent  = file_get_contents($stubPath);

            if ($modelContent === false || $stubContent === false) {
                $error = 'Could not read source files.';
            } else {
                $this->processApiGeneration($name, $modelPath, $modelContent, $stubContent);
            }
        }

        if ($error) {
            $this->error($error);
        }
    }

    /**
     * Manipule le contenu du modèle pour injecter l'API Platform.
     */
    protected function processApiGeneration(string $name, string $path, string $content, string $stub): void
    {
        $baseName       = class_basename($name);
        $processorClass = "{$baseName}StateProcessor";
        $providerClass  = "{$baseName}Provider";

        // Injection des namespaces
        $content = $this->injectNamespaces($content, $name, $processorClass, $providerClass);

        // Préparation de l'attribut
        $attr = str_replace(
            [
                '{{model}}',
                '{{ model }}',
                '{{snakeModel}}',
                '{{ snakeModel }}',
                '{{stateProcessorClass}}',
                '{{ stateProcessorClass }}',
                '{{stateProviderClass}}',
                '{{ stateProviderClass }}',
            ],
            [
                $baseName,
                $baseName,
                Str::snake($baseName),
                Str::snake($baseName),
                $processorClass,
                $processorClass,
                $providerClass,
                $providerClass,
            ],
            $stub
        );

        // Injection de l'attribut au-dessus de la classe
        if (!str_contains($content, '#[ApiResource')) {
            $content = (string) preg_replace('/class ' . $baseName . '/', "{$attr}class {$baseName}", $content);
        }

        // Remplacement des placeholders restants dans tout le modèle (ex: snakeModel dans les constantes)
        $content = str_replace(
            ['{{snakeModel}}', '{{ snakeModel }}'],
            [Str::snake($baseName), Str::snake($baseName)],
            $content
        );

        file_put_contents($path, $content);
        $this->info("API Resource added to Model: {$baseName}");
    }

    /**
     * Ajoute proprement les imports 'use' en haut du fichier.
     */
    protected function injectNamespaces(string $content, string $name, string $processor, string $provider): string
    {
        $namespacePath = str_replace('/', '\\', $name);
        $uses          = [
            "App\\StateProcessor\\{$namespacePath}StateProcessor",
            "App\\ApiProvider\\{$namespacePath}Provider",
            'ApiPlatform\Metadata\ApiProperty',
            'ApiPlatform\Metadata\ApiResource',
            'ApiPlatform\Metadata\Delete',
            'ApiPlatform\Metadata\Get',
            'ApiPlatform\Metadata\GetCollection',
            'ApiPlatform\Metadata\Patch',
            'ApiPlatform\Metadata\Post',
            'Symfony\Component\Serializer\Attribute\Groups',
        ];

        $newUses = '';
        foreach ($uses as $use) {
            if (!str_contains($content, "use {$use};")) {
                $newUses .= "use {$use};\n";
            }
        }

        return (string) preg_replace('/namespace .*;/', "$0\n\n" . rtrim($newUses), $content);
    }
}
