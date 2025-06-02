<div class="whatsapp-container {{ $position === 'left' ? 'whatsapp-container-left' : '' }}">
    @if($label)
        <div class="whatsapp-label whatsapp-label-{{ $labelPosition }}">
            {{ $label }}
        </div>
    @endif

    <a href="{{ $whatsappUrl() }}" class="whatsapp-float" target="_blank">
        <i class="bi bi-whatsapp"></i>
    </a>
</div>

<style>
    .whatsapp-container {
        position: fixed;
        bottom: 10px;
        right: 10px;
        z-index: 1000;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .whatsapp-container-left {
        right: auto;
        left: 40px;
    }

    .whatsapp-float {
        width: 50px;
        height: 50px;
        background-color: #25d366;
        color: #FFF;
        border-radius: 50px;
        text-align: center;
        font-size: 30px;
        box-shadow: 2px 2px 3px #999;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        transition: all 0.3s;
    }

    .whatsapp-float:hover {
        background-color: #1ea952;
        color: #FFF;
        transform: scale(1.1);
    }

    .whatsapp-float i {
        font-size: 1.5rem;
    }

    .whatsapp-label {
        background-color: #fff;
        color: #333;
        padding: 5px 12px;
        border-radius: 4px;
        font-size: 14px;
        font-weight: 500;
        box-shadow: 0 1px 3px rgba(0,0,0,0.12);
        margin-bottom: 10px;
        white-space: nowrap;
        transition: all 0.3s;
        border: 1px solid #e0e0e0;
    }

    .whatsapp-label-top {
        margin-bottom: 10px;
        order: -1;
    }

    .whatsapp-label-bottom {
        margin-top: 10px;
        order: 1;
    }

    .whatsapp-container:hover .whatsapp-label {
        background-color: #f8f8f8;
    }

    /* Configuração para posições laterais */
    .whatsapp-container.whatsapp-label-right,
    .whatsapp-container.whatsapp-label-left {
        flex-direction: row;
        align-items: center;
    }

    .whatsapp-label-right {
        margin-left: 10px;
        order: 1;
    }

    .whatsapp-label-left {
        margin-right: 10px;
        order: -1;
    }

    /* Para dispositivos móveis */
    @media screen and (max-width: 767px) {
        .whatsapp-container {
            bottom: 20px;
            right: 20px;
        }

        .whatsapp-container-left {
            right: auto;
            left: 20px;
        }

        .whatsapp-float {
            width: 50px;
            height: 50px;
            font-size: 22px;
        }

        .whatsapp-float i {
            font-size: 1.25rem;
        }

        .whatsapp-label {
            font-size: 12px;
            padding: 4px 8px;
        }
    }
</style>
