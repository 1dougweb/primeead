<?php

namespace App\Mail;

use App\Models\Inscricao;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InscricaoMail extends Mailable
{
    use Queueable, SerializesModels;

    public $inscricao;
    public $tipo;

    /**
     * Create a new message instance.
     */
    public function __construct(Inscricao $inscricao, string $tipo = 'admin')
    {
        $this->inscricao = $inscricao;
        $this->tipo = $tipo; // 'admin' ou 'aluno'
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        if ($this->tipo === 'admin') {
            return new Envelope(
                subject: 'Nova Inscrição EJA - ' . $this->inscricao->nome,
            );
        } else {
            return new Envelope(
                subject: 'Confirmação de Inscrição - EJA Supletivo',
            );
        }
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $view = $this->tipo === 'admin' ? 'emails.inscricao' : 'emails.confirmacao';
        
        return new Content(
            view: $view,
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $subject = $this->tipo === 'admin' 
            ? 'Nova Inscrição EJA - ' . $this->inscricao->nome
            : 'Confirmação de Inscrição - EJA Supletivo';
            
        $view = $this->tipo === 'admin' ? 'emails.inscricao' : 'emails.confirmacao';
        
        return $this->subject($subject)
                    ->view($view)
                    ->with([
                        'nome' => $this->inscricao->nome,
                        'email' => $this->inscricao->email,
                        'telefone' => $this->inscricao->telefone,
                        'curso' => $this->inscricao->curso,
                        'curso_label' => $this->inscricao->curso_label ?? $this->inscricao->curso,
                        'modalidade' => $this->inscricao->modalidade,
                        'modalidade_label' => $this->inscricao->modalidade_label ?? $this->inscricao->modalidade,
                        'termos' => $this->inscricao->termos ? 'Aceito' : 'Não aceito',
                        'data' => $this->inscricao->created_at->format('d/m/Y H:i:s'),
                        'ip' => $this->inscricao->ip_address,
                    ]);
    }
}
