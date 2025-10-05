<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TelnetCheckCommand extends Command
{
    protected $signature = 'telnet:check';

    protected $description = 'Check telnet connection and restart ipsec if telnet fails.';

    public function handle()
    {
        $host = '10.156.42.160';
        $port = '3111';

        $telnetResult = $this->checkTelnet($host, $port);

        if (!$telnetResult) {
            exec('sudo ipsec restart');
            $this->info('Telnet failed. IPSec restarted successfully.');
        } else {
            $this->info('Telnet check successful. No action taken.');
        }
    }

    private function checkTelnet($host, $port)
    {
        // Check the telnet connection to the given host and port
        $fp = @fsockopen($host, $port, $errno, $errstr, 5);

        if (!$fp) {
            return false; // Telnet failed
        } else {
            fclose($fp);
            return true; // Telnet successful
        }
    }

    private function testConnection($telnetHost, $telnetPort)
    {
        $telnetResult = shell_exec("timeout 5 telnet $telnetHost $telnetPort");

        if (strpos($telnetResult, 'Escape character') === false) {
            return false; // Telnet failed
        } else {
            return true; // Telnet successful
        }
    }
}
