@props(['type' => 'success', 'message' => ''])

@php
    $alertClasses = [
        'success' => 'alert-success',
        'error' => 'alert-danger',
        'warning' => 'alert-warning',
        'info' => 'alert-info',
    ];

    $iconClasses = [
        'success' => 'bi-check-circle-fill text-success',
        'error' => 'bi-exclamation-triangle-fill text-danger',
        'warning' => 'bi-exclamation-circle-fill text-warning',
        'info' => 'bi-info-circle-fill text-info',
    ];
@endphp
<div class="position-fixed top-0 end-0 p-3" style="z-index: 9999;">
    <div class="alert {{ $alertClasses[$type] }} alert-dismissible fade show d-flex align-items-center" role="alert">
        <i class="bi {{ $iconClasses[$type] }} me-2"></i>
        {{ $message }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
</div>
