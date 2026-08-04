<?php

declare(strict_types=1);

namespace Gingerminds\LaravelCore\Console\Commands\Security;

use Exception;
use Gingerminds\LaravelCore\Models\Role\Role;
use Gingerminds\LaravelCore\Models\User\Contributor;
use Gingerminds\LaravelCore\Models\User\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreateUser extends Command
{
    protected $signature = 'gingerminds:create:user';

    protected $description = 'Créer un utilisateur (User + Contributor) et lui attribuer un rôle';

    public function handle(): int
    {
        /** @var class-string<User> $userClass */
        $userClass = config('auth.providers.users.model');
        $roles     = Role::query()->pluck('name', 'id')->toArray();
        $status    = self::SUCCESS;

        if (empty($roles)) {
            $this->error("Aucun rôle n'est défini. Exécutez les seeders avant.");
            return self::FAILURE;
        }

        $email = $this->ask("Email de l'utilisateur ?");
        if ($userClass::where('email', $email)->exists()) {
            $this->error('Un utilisateur avec cet email existe déjà.');
            return self::FAILURE;
        }

        $role      = $this->choice('Quel rôle ?', $roles);
        $lastname  = $this->ask('Nom de famille ?');
        $firstname = $this->ask('Prénom ?');
        $password  = $this->secret('Mot de passe ?');

        try {
            DB::transaction(function () use ($userClass, $email, $password, $role, $lastname, $firstname) {
                $user = $userClass::create([
                    'email'             => $email,
                    'password'          => bcrypt($password),
                    'email_verified_at' => now(),
                ]);

                $user->assignRole($role);

                Contributor::create([
                    'lastname'  => $lastname,
                    'firstname' => $firstname,
                    'user_id'   => $user->id,
                ]);

                $this->line("Utilisateur créé avec succès (ID: {$user->id})");
            });
        } catch (Exception $e) {
            $this->error('Erreur lors de la création : ' . $e->getMessage());
            $status = self::FAILURE;
        }

        return $status;
    }
}
