<?php

declare(strict_types=1);

namespace Gingerminds\LaravelCore\Console\Commands\Make;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CreatePolicy extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:policy {name : Namespace/Model name (e.g. PartnerCompany/PartnerCompany)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a Policy and register it in AuthServiceProvider';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        /** @var string $name */
        $name   = $this->argument('name');
        $name   = trim($name);
        $parts  = explode('/', $name);
        $status = Command::SUCCESS;

        if ($name === '') {
            $this->error('Name is required');
            $status = Command::FAILURE;
        } elseif (count($parts) < 2) {
            $this->error('Name must be in the format Namespace/Model');
            $status = Command::FAILURE;
        }

        if ($status === Command::SUCCESS) {
            [$namespace, $model] = $parts;
            $policyPath          = app_path("Policies/{$namespace}/{$model}Policy.php");

            if (file_exists($policyPath)) {
                $this->error("Policy already exists at: {$policyPath}");
                $status = Command::FAILURE;
            } else {
                $status = $this->createPolicyFile($policyPath, $namespace, $model);
            }
        }

        return $status;
    }

    /**
     * Crée le fichier de Policy à partir du stub.
     */
    protected function createPolicyFile(string $path, string $namespace, string $model): int
    {
        $stubPath = file_exists(base_path('stubs/vendor/gingerminds-core/policy.stub'))
            ? base_path('stubs/vendor/gingerminds-core/policy.stub')
            : __DIR__ . '/../../../../stubs/policy.stub';

        if (!file_exists($stubPath)) {
            $this->error("Stub file not found: {$stubPath}");
            return Command::FAILURE;
        }

        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $stub             = (string) file_get_contents($stubPath);
        $pluralSnakeModel = Str::lower(Str::plural(Str::snake($model, ' ')));

        $content = str_replace(
            ['{{namespace}}', '{{model}}', '{{camelModel}}', '{{pluralSnakeModel}}'],
            [$namespace, $model, Str::camel($model), $pluralSnakeModel],
            $stub
        );

        file_put_contents($path, $content);
        $this->info("Policy created: {$path}");

        $this->registerPolicy($namespace, $model);

        return Command::SUCCESS;
    }

    /**
     * Register the policy in AuthServiceProvider.
     */
    protected function registerPolicy(string $namespace, string $model): void
    {
        $providerPath = app_path('Providers/AuthServiceProvider.php');

        if (!file_exists($providerPath)) {
            $providerPath = base_path(
                'vendor/gingerminds/laravel-core/src/Providers/LaravelCoreAuthServiceProvider.php'
            );
        }

        if (!file_exists($providerPath)) {
            $this->warn('AuthServiceProvider not found. Registration skipped.');
            return;
        }

        $content    = (string) file_get_contents($providerPath);
        $modelFqcn  = "App\\Models\\{$namespace}\\{$model}";
        $policyFqcn = "App\\Policies\\{$namespace}\\{$model}Policy";

        if (str_contains($content, "{$model}::class") && str_contains($content, "{$model}Policy::class")) {
            $this->info('Policy already registered in AuthServiceProvider');
            return;
        }

        // 1. Add use statements
        $content = $this->injectUseStatement($content, $modelFqcn);
        $content = $this->injectUseStatement($content, $policyFqcn);

        // 2. Add to $policies array
        if (preg_match('/protected \$policies = \[(.*?)]/s', $content, $matches)) {
            $content = $this->updatePoliciesArray($content, $matches, $model);
        }

        file_put_contents($providerPath, $content);
        $this->info('Policy registered in AuthServiceProvider');
    }

    /**
     * Injection propre d'un namespace 'use' s'il n'existe pas.
     */
    protected function injectUseStatement(string $content, string $fqcn): string
    {
        if (str_contains($content, "use {$fqcn};")) {
            return $content;
        }

        return (string) preg_replace(
            '/(use .*;\n)(?!use)/',
            "$1use {$fqcn};\n",
            $content,
            1
        );
    }

    /**
     * Met à jour le tableau $policies dans le contenu du fichier.
     *
     * @param array<int, string> $matches
     */
    protected function updatePoliciesArray(string $content, array $matches, string $model): string
    {
        $policiesArray = $matches[1];
        $lines         = array_filter(array_map('trim', explode("\n", trim($policiesArray))));

        $lines[] = "{$model}::class => {$model}Policy::class,";
        $lines   = array_unique($lines);
        sort($lines);

        $newPoliciesArray = "\n        " . implode("\n        ", $lines) . "\n    ";
        return str_replace($matches[1], $newPoliciesArray, $content);
    }
}
