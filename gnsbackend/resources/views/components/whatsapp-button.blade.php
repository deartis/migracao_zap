<a href="{{ $whatsappUrl() }}" class="whatsapp-float {{ $position === 'left' ? 'whatsapp-float-left' : '' }}" target="_blank">
    <i class="bi bi-whatsapp"></i>
</a>

<style>
    .whatsapp-float {
        position: fixed;
        width: 60px;
        height: 60px;
        bottom: 40px;
        right: 40px;
        background-color: #25d366;
        color: #FFF;
        border-radius: 50px;
        text-align: center;
        font-size: 30px;
        box-shadow: 2px 2px 3px #999;
        z-index: 1000;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        transition: all 0.3s;
    }

    .whatsapp-float-left {
        right: auto;
        left: 40px;
    }

    .whatsapp-float:hover {
        background-color: #1ea952;
        color: #FFF;
        transform: scale(1.1);
    }

    .whatsapp-float i {
        font-size: 1.5rem;
    }

    /* Para dispositivos móveis */
    @media screen and (max-width: 767px) {
        .whatsapp-float {
            width: 50px;
            height: 50px;
            bottom: 20px;
            right: 20px;
            font-size: 22px;
        }

        .whatsapp-float-left {
            right: auto;
            left: 20px;
        }

        .whatsapp-float i {
            font-size: 1.25rem;
        }
    }
</style>

// 4. Certifique-se de adicionar o Bootstrap Icons no seu arquivo de layout principal (resources/views/layouts/app.blade.php)
// <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">

// 5. Como usar o componente nas suas views
// <x-whatsapp-button phone="5511987654321" message="Olá, gostaria de mais informações!" position="right" />
