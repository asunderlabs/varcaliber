@component('mail::message')
{{-- Greeting --}}
@if (! empty($greeting))
# {{ $greeting }}
@else
@if ($level === 'error')
# @lang('Whoops!')
@else
# @lang('Hello!')
@endif
@endif

{{-- Intro Lines --}}
@foreach ($introLines as $line)
{{ $line }}
@endforeach

@if (!empty($billableHoursReport))
@component('mail::table')
| {{ $billableHoursReport->name }} ({{ $start }} - {{ $end }}) |                             |
|:------------------------------------------ | ---------------------------:|
@foreach ($weeks as $week)
| {{ $week['label'] }}                       | {{ $week['amount'] }}       |
@endforeach
| **Total Billable Amount**                  | {{ $billableHoursAmountTotal }}   |
@endcomponent
@endif

@component('mail::table')
| Invoices Due                               |                             |
|:------------------------------------------ | ---------------------------:|
@if ($invoiceRows)
@foreach ($invoiceRows as $row)
| {{ $row['label'] }}                        | {{ $row['amount'] }}        |
@endforeach
| **Total Amount Due**                       | {{ $invoiceTotal }}         |
@else
| No invoices due                            |                             |
@endif
@endcomponent

{{-- Action Button --}}
@isset($actionText)
<?php
    switch ($level) {
        case 'success':
        case 'error':
            $color = $level;
            break;
        default:
            $color = 'primary';
    }
?>
@component('mail::button', ['url' => $actionUrl, 'color' => $color])
{{ $actionText }}
@endcomponent
@endisset


{{-- Outro Lines --}}
@foreach ($outroLines as $line)
{{ $line }}
@endforeach

{{-- Salutation --}}
@if (! empty($salutation))
{{ $salutation }}
@else
@lang('Regards'),<br>
{{ config('settings.name') }}
@endif

{{-- Subcopy --}}
@isset($actionText)
@slot('subcopy')
@lang(
    "If you're having trouble clicking the \":actionText\" button, copy and paste the URL below\n".
    'into your web browser:',
    [
        'actionText' => $actionText,
    ]
) <span class="break-all">[{{ $displayableActionUrl }}]({{ $actionUrl }})</span>
@endslot
@endisset
@endcomponent