@extends('layout.app')
@section('title_page', 'Bem Vindo!')
@section('content')
  <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-4 mb-3 mb-md-0">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <h5 class="card-title mb-4">Uso do Pacote Ciclo Out/24</h5>
                            <div class="chart-container">
                                <canvas id="usageChart"></canvas>
                                <div class="chart-label">30%</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3 mb-md-0">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <h5 class="card-title mb-4">Mensagens Enviadas</h5>
                            <div class="chart-container">
                                <canvas id="messagesChart"></canvas>
                                <div class="chart-label">10/4000</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3 mb-md-0">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <h5 class="card-title mb-4">Erros de Envio</h5>
                            <div class="chart-container">
                                <canvas id="errorsChart"></canvas>
                                <div class="chart-label">02</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activities Table -->
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title">Últimas Movimentações</h5>
                        <a href="#" class="text-success">Mais...</a>
                    </div>
                    <div class="table-actions table-responsive">
                        <table class="table table-hover">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>Contato</th>
                                <th>Status</th>
                                <th>Nome</th>
                                <th>Tipo de erro</th>
                                <th>Data/Hora</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>200</td>
                                <td>5522962111</td>
                                <td><span class="badge bg-success">Enviado</span></td>
                                <td>Carlos Alves</td>
                                <td>-</td>
                                <td>21/01/2024</td>
                            </tr>
                            <tr>
                                <td>199</td>
                                <td>5529672112</td>
                                <td><span class="badge bg-success">Enviado</span></td>
                                <td>Karla Sjow</td>
                                <td>-</td>
                                <td>21/01/2024</td>
                            </tr>
                            <tr>
                                <td>198</td>
                                <td>5522672113</td>
                                <td><span class="badge bg-danger">Não Enviado</span></td>
                                <td>Amanda Silva</td>
                                <td><span class="text-danger">Número irregular</span></td>
                                <td>21/01/2024</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="mt-4 text-center">
                <p class="small text-muted">Fontes: Trebuchet MS</p>
                <p class="small">
                    <a href="https://globalnetsis.com.br" class="text-decoration-none">globalnetsis.com.br</a>
                </p>
            </div>

    {{-- @push('scripts')
         <script>
             // Toggle sidebar on desktop
             document.querySelector('.menu-toggle').addEventListener('click', function () {
                 document.querySelector('.sidebar').classList.toggle('collapsed');
                 document.querySelector('.main-content').classList.toggle('expanded');
             });

             // Toggle sidebar on mobile
             document.querySelector('.mobile-menu-toggle').addEventListener('click', function () {
                 document.querySelector('.sidebar').classList.toggle('mobile-active');
                 document.querySelector('.sidebar-overlay').classList.toggle('active');
             });

             // Close sidebar when clicking overlay
             document.querySelector('.sidebar-overlay').addEventListener('click', function () {
                 document.querySelector('.sidebar').classList.remove('mobile-active');
                 document.querySelector('.sidebar-overlay').classList.remove('active');
             });

             // Handle window resize
             function handleResize() {
                 if (window.innerWidth < 992) {
                     // Mobile view
                     document.querySelector('.sidebar').classList.remove('collapsed');
                     document.querySelector('.main-content').classList.remove('expanded');
                 }
             }

             // Initial check
             handleResize();

             // Listen for window resize
             window.addEventListener('resize', handleResize);

             // Chart.js Configuration
             const createDonutChart = (elementId, percentage, colors) => {
                 const ctx = document.getElementById(elementId).getContext('2d');

                 return new Chart(ctx, {
                     type: 'doughnut',
                     data: {
                         datasets: [{
                             data: [percentage, 100 - percentage],
                             backgroundColor: colors,
                             borderWidth: 0,
                             cutout: '75%'
                         }]
                     },
                     options: {
                         responsive: true,
                         maintainAspectRatio: false,
                         plugins: {
                             legend: {
                                 display: false
                             },
                             tooltip: {
                                 enabled: false
                             }
                         },
                         animation: {
                             animateRotate: true,
                             animateScale: true
                         }
                     }
                 });
             };

             // Usage Chart - 30%
             createDonutChart('usageChart', 30, ['#0dcaf0', '#e9ecef']);

             // Messages Chart - 10/4000 (0.25%)
             createDonutChart('messagesChart', 0.25, ['#20c997', '#e9ecef']);

             // Errors Chart - 2 errors
             createDonutChart('errorsChart', 2, ['#dc3545', '#e9ecef']);

         </script>
     @endpush--}}
@endsection
