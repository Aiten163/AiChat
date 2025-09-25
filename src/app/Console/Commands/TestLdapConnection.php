<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use LdapRecord\Connection;

class TestLdapConnection extends Command
{
    protected $signature = 'ldap:test-connection';
    protected $description = 'Test LDAP connection';

    public function handle()
    {
        try {
            $connection = new Connection(config('ldap.connections.default'));
            $connection->connect();

            $this->info('LDAP connection successful!');
            return 0;

        } catch (\Exception $e) {
            $this->error('LDAP connection failed: ' . $e->getMessage());
            return 1;
        }
    }
}
