<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminErstellen extends Command
{
    protected $signature = 'admin:erstellen';
    protected $description = 'Erstellt den ersten Admin-Benutzer für die Bank-Verwaltung';

    public function handle(): int
    {
        $this->info('');
        $this->info('╔══════════════════════════════════════╗');
        $this->info('║   Bank – Admin-Benutzer erstellen    ║');
        $this->info('╚══════════════════════════════════════╝');
        $this->info('');

        $adminCount = User::where('is_admin', true)->count();

        if ($adminCount > 0) {
            $this->warn("Es gibt bereits {$adminCount} Admin-Benutzer.");
            if (! $this->confirm('Trotzdem einen weiteren Admin anlegen?', false)) {
                $this->info('Abgebrochen.');
                return 0;
            }
        }

        // Name
        $name = $this->ask('Vollständiger Name (z. B. Frau Müller)');
        if (blank($name)) {
            $this->error('Name darf nicht leer sein.');
            return 1;
        }

        // E-Mail
        $email = $this->ask('E-Mail-Adresse');
        $emailValidator = Validator::make(['email' => $email], ['email' => 'required|email|unique:users,email']);
        if ($emailValidator->fails()) {
            $this->error($emailValidator->errors()->first('email'));
            return 1;
        }

        // Passwort
        $password = $this->secret('Passwort (mindestens 8 Zeichen)');
        $passwordConfirm = $this->secret('Passwort bestätigen');

        if ($password !== $passwordConfirm) {
            $this->error('Die Passwörter stimmen nicht überein.');
            return 1;
        }

        if (strlen($password) < 8) {
            $this->error('Das Passwort muss mindestens 8 Zeichen lang sein.');
            return 1;
        }

        $user = User::create([
            'name'       => $name,
            'email'      => $email,
            'password'   => Hash::make($password),
            'is_admin'   => true,
            'is_manager' => true,
        ]);

        $this->info('');
        $this->info("✅ Admin-Benutzer \"{$user->name}\" ({$user->email}) wurde erfolgreich erstellt!");
        $this->info('   is_admin = true | is_manager = true');
        $this->info('');

        return 0;
    }
}

