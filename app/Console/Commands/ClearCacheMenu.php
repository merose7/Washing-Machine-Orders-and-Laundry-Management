<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ClearCacheMenu extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "cache:clear-menu";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Clear config and view cache to refresh AdminLTE menu";

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->call("config:clear");
        $this->call("view:clear");

        $this->info("Config and view cache cleared successfully.");

        return 0;
    }
}
