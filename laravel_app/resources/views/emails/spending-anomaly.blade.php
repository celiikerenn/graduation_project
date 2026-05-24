<x-mail::message>
# Unusual spending detected

Hi {{ $userName }},

Your **total spending** for **{{ $monthLabel }}** is more than **50% above** your average over the last three months.

<x-mail::panel>
**This month (so far):** {{ number_format($monthTotal, 2, ',', '.') }} {{ $currencySymbol }}

**Average (last 3 months):** {{ number_format($baselineAverage, 2, ',', '.') }} {{ $currencySymbol }}

**Increase:** +{{ number_format($increasePercent, 1, ',', '.') }}%
</x-mail::panel>

Review your trends and categories in Analytics to see what changed.

<x-mail::button :url="rtrim(config('app.url'), '/').'/charts'">
Open Analytics
</x-mail::button>

You can turn off these alerts anytime under **Settings → Email alerts**.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
