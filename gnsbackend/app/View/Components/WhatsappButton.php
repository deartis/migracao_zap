<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class WhatsappButton extends Component
{
    public mixed $phone;
    public mixed $message;
    public mixed $position;
    /**
     * Create a new component instance.
     */
    public function __construct($phone = '', $message = '', $position = 'right')
    {
        // Formato internacional do telefone (sem + ou outros caracteres)
        $this->phone = $phone;
        // Mensagem pré-definida (opcional)
        $this->message = $message;
        // Posição do botão (right ou left)
        $this->position = $position;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.whatsapp-button');
    }

    /**
     * Gera a URL do WhatsApp com o número e mensagem
     *
     * @return string
     */
    public function whatsappUrl()
    {
        $url = "https://wa.me/{$this->phone}";

        if ($this->message) {
            $url .= "?text=" . urlencode($this->message);
        }

        return $url;
    }
}
