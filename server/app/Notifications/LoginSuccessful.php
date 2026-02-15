<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LoginSuccessful extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
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
     * line() adds a paragraph of text to the email body.
     * Think of it as:A normal sentence Rendered as a text block in the email
     * the action is the call to action button like the the
     *  ->action('View Invoice', url('/invoices/' . $this->invoice->id))
     * So here, $notifiable is your User model instance — the exact user who is being notified.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Welcome Back to ' . config('app.name'))
            ->greeting('Hello ' . ($notifiable->name ?? 'there') . '!')
            ->line('We’re happy to see you again. You’ve successfully logged in to your account.')
            ->line('If this login wasn’t you, please secure your account immediately by changing your password.')
            ->line('Thank you for choosing ' . config('app.name') . '!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     * If you add 'database' to via(), this method becomes required.
     *   return ['message' => 'Login successful',logged_in_at' => now(),];
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
