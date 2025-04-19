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
        <a href="#" class="menu-item active">
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
                        <a href="#" class="menu-item ps-5">
                            <i class="bi bi-box-arrow-right"></i>
                            <span>Deslogar</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
