<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Minishlink\WebPush\VAPID;

class PushVapidGenerieren extends Command
{
    protected $signature = 'push:vapid-generieren';
    protected $description = 'Generiert VAPID-Schlüssel für Web-Push-Benachrichtigungen und gibt sie aus';

    public function handle(): int
    {
        $this->info('Generiere VAPID-Schlüssel …');

        $keys = VAPID::createVapidKeys();

        $this->info('');
        $this->info('Folgende Zeilen in die .env einfügen:');
        $this->info('');
        $this->line('VAPID_PUBLIC_KEY=' . $keys['publicKey']);
        $this->line('VAPID_PRIVATE_KEY=' . $keys['privateKey']);
        $this->info('');
        $this->warn('⚠️  Schlüssel nur einmal generieren – danach nicht mehr ändern, sonst verlieren alle Abonnenten ihre Push-Benachrichtigungen!');

        return 0;
    }
}

