<?php

namespace Gingerminds\LaravelCore\Resolver;

use Illuminate\Database\Eloquent\Model;

class ResourceResolver
{
    /**
     * @return class-string<Model>
     */
    public static function model(string $resource): string
    {
        return config("gingerminds-core.resources.{$resource}.model");
    }

    public static function repository(string $resource): string
    {
        return config("gingerminds-core.resources.{$resource}.repository");
    }

    public static function controller(string $resource): string
    {
        return config("gingerminds-core.resources.{$resource}.controller");
    }

    public static function provider(string $resource): string
    {
        return config("gingerminds-core.resources.{$resource}.provider");
    }

    public static function request(string $resource): string
    {
        return config("gingerminds-core.resources.{$resource}.request");
    }

    public static function stateProcessor(string $resource): string
    {
        return config("gingerminds-core.resources.{$resource}.state_processor");
    }
}
