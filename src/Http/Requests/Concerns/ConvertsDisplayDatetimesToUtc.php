<?php

declare(strict_types=1);

namespace Gingerminds\LaravelCore\Http\Requests\Concerns;

use Gingerminds\LaravelCore\Support\DisplayTimezone;

trait ConvertsDisplayDatetimesToUtc
{
    /** @return list<string> */
    abstract protected function displayDatetimeFields(): array;

    protected function passedValidation(): void
    {
        foreach ($this->displayDatetimeFields() as $field) {
            if ($this->has($field)) {
                $this->merge([$field => DisplayTimezone::toUtc($this->input($field))?->toDateTimeString()]);
            }
        }
    }
}
