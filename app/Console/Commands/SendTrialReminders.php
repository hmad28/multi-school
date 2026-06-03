<?php

namespace App\Console\Commands;

use App\Enums\SchoolStatus;
use App\Models\School;
use App\Models\User;
use App\Notifications\TrialEndingReminder;
use App\Support\ActivityLogger;
use Illuminate\Console\Command;

class SendTrialReminders extends Command
{
    protected $signature = 'platform:trial-reminders {--days=3 : Remind when trial ends within this many days}';

    protected $description = 'Notify school admins of trials ending soon and expire trials past their end date.';

    public function handle(): int
    {
        $days = (int) $this->option('days');

        $reminded = $this->remindEndingSoon($days);
        $expired = $this->expirePastTrials();

        $this->info("Trial reminders sent: {$reminded}. Trials expired: {$expired}.");

        return self::SUCCESS;
    }

    private function remindEndingSoon(int $days): int
    {
        $schools = School::query()
            ->where('status', SchoolStatus::Trial)
            ->whereNotNull('trial_ends_at')
            ->whereBetween('trial_ends_at', [now(), now()->addDays($days)])
            ->get();

        $count = 0;

        foreach ($schools as $school) {
            $admins = User::query()->where('school_id', $school->id)->get();

            if ($admins->isEmpty()) {
                continue;
            }

            $daysLeft = (int) max(0, now()->diffInDays($school->trial_ends_at, false));

            foreach ($admins as $admin) {
                $admin->notify(new TrialEndingReminder($school, $daysLeft));
            }

            $count++;
        }

        return $count;
    }

    private function expirePastTrials(): int
    {
        $schools = School::query()
            ->where('status', SchoolStatus::Trial)
            ->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '<', now())
            ->get();

        foreach ($schools as $school) {
            $school->update(['status' => SchoolStatus::Expired]);

            ActivityLogger::log(
                'tenant.trial_expired',
                "Trial {$school->name} berakhir dan status diubah menjadi expired.",
                null,
                $school,
                ['trial_ends_at' => $school->trial_ends_at?->toIso8601String()],
            );
        }

        return $schools->count();
    }
}
