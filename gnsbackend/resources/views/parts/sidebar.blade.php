<!-- Sidebar Overlay -->
<div class="sidebar-overlay"></div>
<!-- Sidebar -->
<div class="sidebar">
    <div class="logo">
        <i class="bi bi-check-circle-fill text-success"></i>
        <span>GnsWhatSender</span>
    </div>

    {{--<div class="nav-heading">Dashboard</div>--}}
    <ul class="nav flex-column">
        <li class="nav-item" data-title="Início">
            <a class="nav-link" href="{{ route('home') }}">
                <i class="bi bi-house-door me-2 text-primary"></i> <span>Início</span>
            </a>
        </li>
    </ul>

    <div class="nav-heading">Mensagens</div>
    <ul class="nav flex-column">
        <li class="nav-item" data-title="Envio em massa xls/csv">
            <a class="nav-link" href="{{ route('page.from.sheet') }}">
                <i class="bi bi-person-lines-fill me-2 text-success"></i> <span>Envio em Massa xlsx/csv</span>
            </a>
        </li>
        <li class="nav-item" data-title="Envio em massa contatos telefone">
            <a class="nav-link" href="{{ route('page.multi.msg') }}">
                <i class="bi bi-list-check me-2 text-success"></i> <span>Envio em massa contatos telefone</span>
            </a>
        </li>
        <li class="nav-item" data-title="Envio único número">
            <a class="nav-link" href="{{ route('page.single.contact') }}">
                <i class="bi bi-send me-2 text-success"></i> <span>Envio único número</span>
            </a>
        </li>
        <li class="nav-item" data-title="Responder mensagens">
            <a class="nav-link" href="{{ route('page.respond.msg') }}">
                <i class="bi bi-chat-left-text me-2 text-success"></i> <span>Responder mensagens</span>
            </a>
        </li>
    </ul>

    <div class="nav-heading">Histórico</div>
    <ul class="nav flex-column">
        <li class="nav-item" data-title="Registros de envios">
            <a class="nav-link" href="{{ route('page.historic') }}">
                <i class="bi bi-journal-text me-2 text-success"></i> <span>Registros de envios</span>
            </a>
        </li>
    </ul>

    <div class="nav-heading">Configurações</div>
    <ul class="nav flex-column">
        <li class="nav-item" data-title="Dados do Perfil">
            <a class="nav-link" href="{{ route('page.profile') }}">
                <i class="bi bi-gear me-2 text-success"></i> <span>Dados do Perfil</span>
            </a>
        </li>
        @if(Auth::user()->role === 'admin')
        <li class="nav-item" data-title="Área Administrativa">
            <a class="nav-link" href="{{ route('adm.user') }}">
                <i class="bi bi-person-badge me-2 text-success"></i> <span>Área Administrativa</span>
            </a>
        </li>
        @endif
        <li class="nav-item" data-title="Conectar Whatsapp">
            <a class="nav-link" href="{{ route('page.connection') }}">
                <i class="bi bi-qr-code-scan me-2 text-success"></i> <span id="btn_conn">Conectar Whatsapp</span>
            </a>
        </li>
        <li class="nav-item" data-title="Sair">
            <a class="nav-link" href="{{ route('logout') }}"
            onclick="event.preventDefault();
            document.getElementById('logout_form').submit()">
                <i class="bi bi-box-arrow-left me-2 text-danger"></i> <span>Sair</span>
            </a>
            <form id="logout_form" action="{{ route('logout') }}" method="POST" style="display: none">
                @csrf
            </form>
        </li>
    </ul>
</div>
