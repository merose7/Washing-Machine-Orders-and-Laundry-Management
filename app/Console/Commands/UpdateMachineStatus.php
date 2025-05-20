<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Machine;
use Carbon\Carbon;

class UpdateMachineStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'machines:update-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update machine status from booked to available after booking ends, excluding machines under repair';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();
        $this->info("Current time: " . $now->toDateTimeString());

        $machinesToUpdate = Machine::where('status', 'booked')
            ->whereNotNull('booking_ends_at')
            ->where('booking_ends_at', '<=', $now)
            ->get();

        $this->info("Machines found for update: " . $machinesToUpdate->count());

        foreach ($machinesToUpdate as $machine) {
            $this->info("Checking machine ID {$machine->id} with booking_ends_at {$machine->booking_ends_at}");

            // Skip if machine is under repair
            if ($machine->status === 'repair') {
                $this->info("Skipping machine ID {$machine->id} because it is under repair.");
                continue;
            }

            $machine->status = 'available';
            $machine->booking_ends_at = null;
            $machine->save();

            $this->info("Updated machine ID {$machine->id} status to available.");
        }

        $this->info('Machine status update completed.');
    }
}
