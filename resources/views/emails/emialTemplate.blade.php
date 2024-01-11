@component('mail::message')

{!! $template_body !!}

{{-- Action Button --}}
@isset($actionText)
<?php
    $level = 'danger';
    switch ($level) {
        case 'success':
        case 'error':
            $color = $level;
            break;
        default:
            $color = 'primary';
    }
?>

    {{ $actionText }}

@endisset

{!! $template_signature !!}
