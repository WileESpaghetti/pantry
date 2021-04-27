<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookmarksImported extends Notification
{
    use Queueable;

    private $counts;
    private $warnings;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($counts, $warnings)
    {
        $this->counts = $counts;
        $this->warnings = $warnings;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'counts' => $this->counts,
            'warnings' => json_encode($this->warnings),
            'warning_count' => count($this->warnings),
        ];
    }
}
