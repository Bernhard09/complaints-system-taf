<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0; padding:0; background-color:#f3f4f6; font-family:'Segoe UI',Roboto,Arial,sans-serif;">

    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f3f4f6; padding:40px 0;">
        <tr>
            <td align="center">
                <table width="560" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border-radius:12px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.08);">

                    {{-- Header --}}
                    <tr>
                        <td style="background-color:#4f46e5; padding:24px 32px;">
                            <h1 style="margin:0; color:#ffffff; font-size:20px; font-weight:600;">
                                {{ config('app.name') }}
                            </h1>
                        </td>
                    </tr>

                    {{-- Icon + Type badge --}}
                    <tr>
                        <td style="padding:32px 32px 16px;">
                            @php
                                $badgeColors = [
                                    'success' => 'background-color:#dcfce7; color:#166534;',
                                    'error'   => 'background-color:#fee2e2; color:#991b1b;',
                                    'warning' => 'background-color:#fef3c7; color:#92400e;',
                                    'info'    => 'background-color:#dbeafe; color:#1e40af;',
                                ];
                                $badgeStyle = $badgeColors[$notifType] ?? $badgeColors['info'];
                            @endphp
                            <span style="display:inline-block; padding:4px 14px; border-radius:20px; font-size:12px; font-weight:600; text-transform:uppercase; {{ $badgeStyle }}">
                                {{ $notifType }}
                            </span>
                        </td>
                    </tr>

                    {{-- Title --}}
                    <tr>
                        <td style="padding:0 32px 8px;">
                            <h2 style="margin:0; font-size:18px; font-weight:600; color:#111827;">
                                {{ $notifTitle }}
                            </h2>
                        </td>
                    </tr>

                    {{-- Message --}}
                    <tr>
                        <td style="padding:0 32px 24px;">
                            <p style="margin:0; font-size:15px; line-height:1.6; color:#4b5563;">
                                {{ $notifMessage }}
                            </p>
                        </td>
                    </tr>

                    {{-- CTA Button --}}
                    @if($notifLink)
                    <tr>
                        <td style="padding:0 32px 32px;">
                            <a href="{{ $notifLink }}" style="display:inline-block; background-color:#4f46e5; color:#ffffff; padding:12px 28px; border-radius:8px; text-decoration:none; font-size:14px; font-weight:600;">
                                View Details →
                            </a>
                        </td>
                    </tr>
                    @endif

                    {{-- Footer --}}
                    <tr>
                        <td style="background-color:#f9fafb; padding:20px 32px; border-top:1px solid #e5e7eb;">
                            <p style="margin:0; font-size:12px; color:#9ca3af; text-align:center;">
                                This is an automated notification from {{ config('app.name') }}.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>
</html>
