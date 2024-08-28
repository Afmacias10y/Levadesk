<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NotificacionAprobacion extends Notification implements ShouldQueue
{
    use Queueable;

    protected $aprobacion;
    protected $ticket;

    public function __construct($aprobacion, $ticket)
    {
        $this->aprobacion = $aprobacion;
        $this->ticket = $ticket;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('Aprobacion requerida')
                    ->line('El agente TI ' . $this->ticket->asignado->name .' Te ha asignado como aprobado para el ticket. ' . $this->ticket->nomenclatura)
                    ->line('Por favor ingresa a LevaDesk y dirigete a la seccion de aprobaciones')
                    ->action('Ver aprobacion', url('/tickets/' . $this->ticket->id));
    }

    public function toArray($notifiable)
    {
        return [
            'agente_ti' => $this->ticket->asignado->name ,
            'nomenclatura_ticket' => $this->ticket->nomenclatura,
        ];
    }
}
