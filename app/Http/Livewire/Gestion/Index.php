<?php

namespace App\Http\Livewire\Gestion;

use App\Models\Estado;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Index extends Component
{
    public $tickets;
    public $ticketsSolucionados;
    public $ticketsEnProceso;
    public $ticketsPorIniciar;
    public $totalHorasSoporte;
    public $fecha_desde, $fecha_hasta;
    public $item;
    public $estados, $SelectedEstado;

    protected $listeners = ['cargarDatos', 'gestionTicket'];
    protected $queryString = ['fecha_desde', 'fecha_hasta'];


    public function mount()
    {
        $this->iniciarFechas();
        $this->cargarDatos();
        $this->estados = Estado::all();
    }

    public function cargarDatos()
    {
        $userId = Auth::id();

        $query = Ticket::where(function ($query) use ($userId) {
            $query->where('asignado_a', $userId)
                ->orWhereHas('colaboradores', function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                });
        });

        if ($this->SelectedEstado) {
            $query->where('estado_id', $this->SelectedEstado);
        }

        if ($this->fecha_desde && $this->fecha_hasta) {
            $fecha_desde = date('Y-m-d', strtotime($this->fecha_desde));
            $fecha_hasta = date('Y-m-d 23:59:59', strtotime($this->fecha_hasta));
            $query->whereBetween('created_at', [$fecha_desde, $fecha_hasta]);
        }

        $tickets = $query->with('urgencia', 'estado', 'colaboradores', 'categoria', 'subcategoria', 'usuario')->get();

        $this->tickets = $tickets->map(function ($ticket) use ($userId) {
            if ($ticket->asignado_a == $userId) {
                $ticket->rol = 'Agente';
            } elseif ($ticket->colaboradores->contains('id', $userId)) {
                $ticket->rol = 'Colaborador';
            }
            return $ticket;
        });

        $this->ticketsSolucionados = $this->tickets->where('estado_id', 4);
        $this->ticketsEnProceso = $this->tickets->where('estado_id', 3)->count();
        $this->ticketsPorIniciar = $this->tickets->where('estado_id', 1)->count();
        $this->totalHorasSoporte = $this->calcularHorasSoporte($this->tickets);

        $this->emit('cargarGestioTicketTabla', json_encode($this->tickets));
    }


    public function calcularHorasSoporte($tickets)
    {
        // Aquí va la lógica para calcular las horas de soporte
        return 60; // Suponiendo que el cálculo retorna 60 horas
    }

    public function render()
    {
        if (!$this->fecha_desde && !$this->fecha_hasta) {
            $this->iniciarFechas();
        }
        return view('livewire.gestion.index');
    }

    // inicia las fechas el desde, según el primer dia del mes actual y el hasta sera el día actual
    public function iniciarFechas()
    {
        // $hoy = date('Y-m-d');
        $this->fecha_desde = date('Y-m-1');
        $this->fecha_hasta = date('Y-m-d');
    }

    public function updateStatus($ticketId, $statusId)
    {
        $ticket = Ticket::find($ticketId);
        if ($ticket && $ticket->asignado_a == Auth::id()) {
            $ticket->estado_id = $statusId;
            $ticket->save();
            $this->tickets = Ticket::where('asignado_a', Auth::id())->get();
        }
    }

    // public function addComment($ticketId, $comentario)
    // {
    //     $ticket = Ticket::find($ticketId);
    //     // $this->validate([
    //     //     'comentario' => 'required|string|max:255',
    //     // ]);
    //     if ($ticket && $ticket->asignado_a == Auth::id()) {
    //         $ticket->comentarios()->create([
    //             'user_id' => Auth::id(),
    //             'comentario' => $comentario,
    //         ]);
    //         $this->tickets = Ticket::where('asignado_a', Auth::id())->get();
    //     }
    // }
}
