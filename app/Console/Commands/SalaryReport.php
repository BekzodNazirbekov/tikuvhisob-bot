<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\WorkEntry;
use Illuminate\Support\Facades\DB;

class SalaryReport extends Command
{
    protected $signature = 'salaries:report {--month=}';

    protected $description = 'Print user salaries for a month';

    public function handle(): int
    {
        $month = $this->option('month') ?? now()->format('Y-m');

        $entries = WorkEntry::query()
            ->select('user_id', DB::raw('sum(quantity * parts.price) as salary'))
            ->join('parts', 'parts.id', '=', 'work_entries.part_id')
            ->whereBetween('date', ["$month-01", "$month-31"])
            ->groupBy('user_id')
            ->get();

        foreach ($entries as $entry) {
            $user = User::find($entry->user_id);
            $this->line($user->name . ': ' . $entry->salary);
        }

        return self::SUCCESS;
    }
}
