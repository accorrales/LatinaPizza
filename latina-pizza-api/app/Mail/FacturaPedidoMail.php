<?php

namespace App\Mail;

use App\Models\Pedido;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

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
