<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\WorkEntry;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Command\Command as CommandAlias;

class SalaryReport extends Command
{
    protected $signature = 'salaries:report {--month=}';

    protected $description = 'Hisobchilar uchun oylik hisobot (tikuvchilar ish haqi)';

    public function handle(): int
    {
        $month = $this->option('month') ?? now()->format('m');

        $users = User::where('role', 'worker')->get();

        foreach ($users as $user) {
            $salary = WorkEntry::where('user_id', $user->id)
                ->whereMonth('date', $month)
                ->join('parts', 'work_entries.part_id', '=', 'parts.id')
                ->sum(DB::raw('quantity * price'));

            $this->line("👤 {$user->name}: 💰 {$salary} so'm");
        }

        return CommandAlias::SUCCESS;
    }
}
