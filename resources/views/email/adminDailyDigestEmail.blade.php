@component('mail::message')

@component('mail::table')
| Stat                 | Value                 |
|:-------------------- |:--------------------- |
@foreach ($stats as $statName => $statValue)
| {{ $statName }}      | {{ $statValue }}      |
@endforeach
@endcomponent

@if ($errors)

@component('mail::table')
| Errors               |
|:-------------------- |
@foreach ($errors as $logError)
| {!! $logError !!}    |
@endforeach
@endcomponent

@endif

@if ($scheduleFailures)

@component('mail::table')
| Failures             |
|:-------------------- |
@foreach ($scheduleFailures as $failureDescription)
| {!! $failureDescription !!}      |
@endforeach
@endcomponent

@endif

@endcomponent
