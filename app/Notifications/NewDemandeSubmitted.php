<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewDemandeSubmitted extends Notification implements ShouldQueue
{
    use Queueable;

    protected $demande;

    /**
     * Create a new notification instance.
     *
     * @param  $demande
     * @return void
     */
    public function __construct($demande)
    {
        $this->demande = $demande;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database']; // Vous pouvez ajouter d'autres canaux comme 'database', 'sms', etc.
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
            ->subject('Nouvelle Demande Soumise')
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line('Une nouvelle demande a été soumise.')
            ->action('Voir la demande', url('/demandes/' . $this->demande->id))
            ->line('Merci de vérifier les détails de la demande.');
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
            'demande_id' => $this->demande->id,
            'client_id' => $this->demande->client_id,
            'montant' => $this->demande->montant,
        ];
    }
}