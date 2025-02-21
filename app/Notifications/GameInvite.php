<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\HtmlString;

class GameInvite extends Notification implements ShouldQueue
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
     */
    public function toMail(object $notifiable): MailMessage
    {
        $game_invite_validity = config('app.game.invite_validity_mins');
        $event = $notifiable->event;
        $notifiable->invite_expires_at = now()->addMinutes($game_invite_validity);
        $notifiable->save();

        return (new MailMessage)
                    ->subject("SnaQ @ {$event->name} | Access your game")
                    ->greeting("Hello {$notifiable->display_name},")
                    ->line('Your game is now ready for you to play. To start playing, simply click on the link below.')
                    ->action('Game Link', URL::signedRoute('game', ['player' => $notifiable->id, 'event' => $event?->slug]))
                    ->line('We hope you enjoy the game and have a great time playing it! If you have any questions or concerns, please feel free to reach out to us at our stall.')
                    ->line(new HtmlString("<strong>Note: </strong>This link can be used only once and will expire after {$game_invite_validity} mins. Please open the link as soon as you receive it. After you start the game, you can\'t reload or reuse the link."))
                    ->line("Best of luck! And we hope you win the prize @ {$event->name}");
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
