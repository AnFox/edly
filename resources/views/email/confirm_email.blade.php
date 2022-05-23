@extends('token::clients.edly.email.layout')

@section('content')
    <tr style="border-collapse:collapse;">
        <td align="left" style="padding:0;Margin:0;padding-left:40px;padding-right:40px;">
            <table cellpadding="0" cellspacing="0" width="100%"
                   style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;">
                <tr style="border-collapse:collapse;">
                    <td width="820" align="center" valign="top" style="padding:0;Margin:0;">
                        <table cellpadding="0" cellspacing="0" width="100%"
                               role="presentation"
                               style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;">
                            <tr style="border-collapse:collapse;">
                                <td align="left" bgcolor="#ffffff"
                                    style="padding:0;Margin:0;"><p
                                            style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:14px;font-family:'open sans', 'helvetica neue', helvetica, arial, sans-serif;line-height:21px;color:#222B45;">
                                        <strong><span
                                                    style="font-size:36px;line-height:54px;">Регистрация<br/></span></strong><br><br><span
                                                style="font-size:18px;line-height:27px;">Здравствуйте, {{ $username }}!<br><br>Вы начали регистрацию в сервисе Edly. Для активации вашей учетной записи необходимо перейти по ссылке или нажать кнопку.</span>
                                    </p></td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr style="border-collapse:collapse;">
        <td align="left"
            style="Margin:0;padding-top:20px;padding-bottom:30px;padding-left:40px;padding-right:40px;">
            <table width="100%" cellspacing="0" cellpadding="0"
                   style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;">
                <tr style="border-collapse:collapse;">
                    <td class="es-m-p0r es-m-p20b" width="820" valign="top" align="center"
                        style="padding:0;Margin:0;">
                        <table width="100%" cellspacing="0" cellpadding="0"
                               role="presentation"
                               style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;">
                            <tr style="border-collapse:collapse;">
                                <td align="left" style="padding:0;Margin:0;"><p
                                            style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:18px;font-family:'open sans', 'helvetica neue', helvetica, arial, sans-serif;line-height:27px;color:#222B45;">
                                        <a href="{{ $confirmLink }}" target="_blank"
                                           style="text-decoration: none;color:#222B45"><strong>{{ $confirmLink }}</strong>
                                        </a>
                                    </p>

                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr style="border-collapse:collapse;">
        <td align="left" style="padding:0;Margin:0;padding-left:40px;padding-right:40px;">
            <table cellpadding="0" cellspacing="0" width="100%"
                   style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;">
                <tr style="border-collapse:collapse;">
                    <td width="820" align="center" valign="top" style="padding:0;Margin:0;">
                        <table cellpadding="0" cellspacing="0" width="100%"
                               role="presentation"
                               style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;">
                            <tr style="border-collapse:collapse;">
                                <td align="left" style="padding:10px;Margin:0;"><span
                                            class="es-button-border"
                                            style="margin-bottom: 40px;border-style:solid;background:linear-gradient(0deg, #3366FF 0%, #598BFF 100%);border-width:0px 0px 2px 0px;display:inline-block;border-radius:30px;width:auto;border-bottom-width:0px;"><a
                                                href="{{ $confirmLink }}" class="es-button"
                                                target="_blank"
                                                style="mso-style-priority:100 !important;text-decoration:none;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:'open sans', 'helvetica neue', helvetica, arial, sans-serif;font-size:18px;color:#FFFFFF;border-style:solid;border-color:#3366FF;border-width:15px 30px;display:inline-block;background:#3366FF;border-radius:30px;font-weight:bold;font-style:normal;line-height:22px;width:auto;text-align:center;">Активировать</a></span>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
@endsection