<?php

namespace App\Notifications;

use App\Models\School;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TrialEndingReminder extends Notification
{
    use Queueable;

    public function __construct(
        private readonly School $school,
        private readonly int $daysLeft,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $dayLabel = $this->daysLeft <= 0 ? 'hari ini' : "dalam {$this->daysLeft} hari";

        return (new MailMessage)
            ->subject("Trial {$this->school->name} berakhir {$dayLabel}")
            ->greeting("Halo {$notifiable->name},")
            ->line("Masa trial sekolah {$this->school->name} akan berakhir {$dayLabel}.")
            ->line('Agar akses tidak terputus, lakukan upgrade langganan sebelum trial habis.')
            ->action('Buka Dashboard Sekolah', url("/t/{$this->school->slug}/dashboard"))
            ->line('Terima kasih telah menggunakan Platform Sekolah.');
    }
}
