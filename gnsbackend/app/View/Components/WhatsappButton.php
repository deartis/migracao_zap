<?php

namespace App\View\Components;

use Illuminate\View\Component;

class WhatsappButton extends Component
{
    public $phone;
    public $message;
    public $position;
    public $label;
    public $labelPosition;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
        $phone = '',
        $message = '',
        $position = 'right',
        $label = '',
        $labelPosition = 'top'
    ) {
        // Formato internacional do telefone (sem + ou outros caracteres)
        $this->phone = $phone;
        // Mensagem pré-definida (opcional)
        $this->message = $message;
        // Posição do botão (right ou left)
        $this->position = $position;
        // Texto do label (opcional)
        $this->label = $label;
        // Posição do label (top, right, bottom, left)
        $this->labelPosition = $labelPosition;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
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
