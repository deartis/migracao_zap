<div class="row mb-4 align-items-center">
    <div
        class="whatsapp-global-status bg-white rounded shadow-sm d-flex justify-content-between align-items-center p-1 w-100">
        <!-- Título à esquerda -->
        <div>
            <div class="menu-toggle">
                <i class="bi bi-list fs-4"></i>
            </div>
            <div class="mobile-menu-toggle">
                <i class="bi bi-list fs-4"></i>
            </div>
        </div>
        <div>@yield('title_page')</div>
        <!-- Status à direita -->
        <div class="d-flex align-items-center">
            <div id="global-status-indicator" class="d-flex align-items-center me-2">
                <span class="status-circle d-inline-block rounded-circle me-2"
                      style="width: 10px; height: 10px; background-color: #6c757d;"></span>
                <small id="global-status-text">Verificando...</small>
                <i class="bi bi-person-circle" style="font-size: 27pt; margin-left: 10px"></i>
            </div>
        </div>
    </div>

</div>
