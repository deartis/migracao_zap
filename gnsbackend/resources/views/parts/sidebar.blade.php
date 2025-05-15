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

{{--
<!-- Mobile menu toggle -->
<div class="menu-toggle" id="menuToggle">
    <i class="bi bi-list"></i>
</div>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <h5 class="d-flex align-items-center mb-0">
            <i class="bi bi-whatsapp whatsapp-icon me-2"></i>
            Dashboard de Mensagens
        </h5>
    </div>

    <div class="profile-section">
        <div class="profile-img">
            <i class="bi bi-person"></i>
        </div>
        <div>
            <div class="d-flex align-items-center">
                <span class="me-2">Verificando</span>
                <span class="badge bg-success rounded-pill"></span>
            </div>
        </div>
    </div>

    <div class="mt-3">
        <a href="{{ route('home') }}" class="menu-item active">
            <i class="bi bi-house-door"></i>
            <span>Início</span>
        </a>

        <div class="accordion" id="menuAccordion">
            <!-- Mensagem Menu -->
            <div class="accordion-item border-0">
                <h2 class="accordion-header" id="headingOne">
                    <button class="menu-item accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                        <i class="bi bi-chat-dots"></i>
                        <span>Mensagem</span>
                    </button>
                </h2>
                <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#menuAccordion">
                    <div class="accordion-body p-0">
                        <a href="#" class="menu-item ps-5">
                            <i class="bi bi-file-earmark-spreadsheet"></i>
                            <span>Em massa: xlsx/csv</span>
                        </a>
                        <a href="#" class="menu-item ps-5">
                            <i class="bi bi-people"></i>
                            <span>Em massa: Contatos do telefone</span>
                        </a>
                        <a href="{{ route('page.single.contact') }}" class="menu-item ps-5">
                            <i class="bi bi-phone"></i>
                            <span>Único número</span>
                        </a>
                        <a href="#" class="menu-item ps-5">
                            <i class="bi bi-reply"></i>
                            <span>Respostas (Beta)</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Histórico Menu -->
            <div class="accordion-item border-0">
                <h2 class="accordion-header" id="headingTwo">
                    <button class="menu-item accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                        <i class="bi bi-clock-history"></i>
                        <span>Histórico de Envios</span>
                    </button>
                </h2>
                <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#menuAccordion">
                    <div class="accordion-body p-0">
                        <a href="#" class="menu-item ps-5">
                            <i class="bi bi-journal-text"></i>
                            <span>Registro de Envio</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Configurações Menu -->
            <div class="accordion-item border-0">
                <h2 class="accordion-header" id="headingThree">
                    <button class="menu-item accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                        <i class="bi bi-gear"></i>
                        <span>Configurações</span>
                    </button>
                </h2>
                <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#menuAccordion">
                    <div class="accordion-body p-0">
                        <a href="#" class="menu-item ps-5">
                            <i class="bi bi-sliders"></i>
                            <span>Perfil do usuário</span>
                        </a>
                        <a href="#" class="menu-item ps-5">
                            <i class="bi bi-qr-code-scan"></i>
                            <span>Conectar ao WhatsApp</span>
                        </a>
                        <a href="{{ route('adm.register.user') }}" class="menu-item ps-5">
                            <i class="bi bi-person-lock"></i>
                            <span>Administrativo</span>
                        </a>
                        <a href="{{ route('logout') }}" class="menu-item ps-5">
                            <i class="bi bi-box-arrow-right"></i>
                            <span>Deslogar</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
--}}
