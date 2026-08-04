<?php

declare(strict_types=1);

namespace Gingerminds\LaravelCore\Http\Requests\User;

use Gingerminds\LaravelCore\Http\Requests\FormRequestInterface;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest implements FormRequestInterface
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email'    => 'required|email|max:255',
            'password' => 'nullable|string|min:8|confirmed',
            'roles'    => 'nullable|array',
            'roles.*'  => 'string',
            // Permet soit un id existant, soit la valeur spéciale "__new__" pour créer un nouveau contributeur côté dépôt
            'contributor_id' => 'nullable',
            'contributor_firstname' => 'nullable|string|max:255',
            'contributor_lastname'  => 'nullable|string|max:255',
            'contributor_trigram'   => 'nullable|string|max:50',
            'contributor_civility'  => 'nullable|in:mr,mrs',
        ];
    }
}
