<?php

namespace App\Notifications;

use App\Models\Exam;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewActiveExam extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Exam $exam)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Active Exam' . $this->exam->title)
            ->greeting('New Active Exam' . $this->exam->title)
            ->line('A new exam has been activated for the lesson: ' . $this->exam->lesson->title)
            ->action('View Exam', route('exams.show', $this->exam->id))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}