<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
</head>
<body style="margin:0; padding:24px 0; background-color:#f3f6f4; font-family:Arial, Helvetica, sans-serif; color:#111111;">
    @php
        $logoDataUri = null;

        if (isset($logoPath) && is_string($logoPath) && file_exists($logoPath)) {
            $logoDataUri = 'data:image/png;base64,'.base64_encode((string) file_get_contents($logoPath));
        }
    @endphp

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:680px; border-collapse:collapse; background-color:#ffffff; border:1px solid #d7e4db; border-radius:18px; overflow:hidden;">
                    <tr>
                        <td style="background-color:#0f7b3a; padding:24px 32px; text-align:center;">
                            @if ($logoDataUri)
                                <img src="{{ $logoDataUri }}" alt="Jaspe Technologies" style="max-width:180px; width:100%; height:auto; display:block; margin:0 auto 16px auto;">
                            @endif
                            <div style="font-size:24px; line-height:32px; font-weight:700; color:#ffffff;">{{ $title }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;">
                            <p style="margin:0 0 16px 0; font-size:16px; line-height:26px; color:#111111;">
                                Bonjour {{ $recipientName }},
                            </p>

                            <p style="margin:0 0 24px 0; font-size:15px; line-height:25px; color:#111111;">
                                {{ $intro }}
                            </p>

                            @if (! empty($details))
                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse; margin-bottom:24px; border:1px solid #dce8df; border-radius:14px; overflow:hidden;">
                                    @foreach ($details as $detail)
                                        <tr>
                                            <td style="width:38%; padding:12px 16px; background-color:#eef7f1; font-size:14px; font-weight:700; color:#0f7b3a; border-bottom:1px solid #dce8df;">
                                                {{ $detail['label'] }}
                                            </td>
                                            <td style="padding:12px 16px; font-size:14px; color:#111111; border-bottom:1px solid #dce8df;">
                                                {{ $detail['value'] }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </table>
                            @endif

                            @if (! empty($highlights))
                                <div style="margin:0 0 24px 0; padding:18px 20px; background-color:#f7fbf8; border-left:5px solid #0f7b3a; border-radius:12px;">
                                    <div style="margin:0 0 10px 0; font-size:14px; font-weight:700; color:#0f7b3a;">Éléments concernés</div>
                                    <ul style="margin:0; padding-left:18px; color:#111111; font-size:14px; line-height:24px;">
                                        @foreach ($highlights as $highlight)
                                            <li>{{ $highlight }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @if ($hasAttachment)
                                <p style="margin:0 0 20px 0; font-size:14px; line-height:24px; color:#111111;">
                                    Le document PDF associé à cette opération est joint à cet e-mail.
                                </p>
                            @endif

                            @if ($footerNote)
                                <p style="margin:0; font-size:14px; line-height:24px; color:#111111;">
                                    {{ $footerNote }}
                                </p>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:18px 32px; background-color:#111111; text-align:center;">
                            <p style="margin:0; font-size:12px; line-height:20px; color:#ffffff;">
                                Jaspe Technologies - Gestion de parc et suivi du matériel
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
