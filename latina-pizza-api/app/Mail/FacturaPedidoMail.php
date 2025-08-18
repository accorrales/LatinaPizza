<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Pedido;

class FacturaPedidoMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Pedido $pedido, public string $pdfBin) {}

    public function build()
    {
        return $this->subject('Factura de tu pedido #'.$this->pedido->id)
            ->view('emails.factura_pedido')
            ->with(['pedido' => $this->pedido])
            ->attachData(
                $this->pdfBin,
                "Factura-{$this->pedido->id}.pdf",
                ['mime' => 'application/pdf']
            );
    }
}
