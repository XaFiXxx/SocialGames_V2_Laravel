<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Confirme ton adresse email</title>
</head>
<body style="margin:0; padding:0; background-color:#060816; font-family:Arial, Helvetica, sans-serif; color:#ffffff;">
    <div style="margin:0; padding:40px 20px; background:
        radial-gradient(circle at top left, rgba(124,58,237,0.22), transparent 28%),
        radial-gradient(circle at top right, rgba(6,182,212,0.16), transparent 26%),
        radial-gradient(circle at bottom, rgba(99,102,241,0.12), transparent 35%),
        #060816;
    ">
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
            <tr>
                <td align="center">
                    <table role="presentation" width="100%" style="max-width:640px;" cellspacing="0" cellpadding="0" border="0">
                        <tr>
                            <td style="padding-bottom:24px;">
                                <div style="display:inline-block; padding:10px 16px; border:1px solid rgba(255,255,255,0.10); border-radius:999px; background:rgba(255,255,255,0.05); color:#ffffff;">
                                    <span style="font-size:14px; font-weight:700; letter-spacing:0.04em;">🎮 SquadBase</span>
                                    <span style="font-size:12px; color:rgba(255,255,255,0.55); margin-left:8px;">Le réseau social des gamers</span>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <td style="padding:0;">
                                <div style="
                                    border:1px solid rgba(255,255,255,0.10);
                                    border-radius:32px;
                                    background:rgba(255,255,255,0.05);
                                    padding:40px 32px;
                                    box-shadow:0 20px 60px rgba(0,0,0,0.35);
                                ">
                                    <div style="
                                        width:72px;
                                        height:72px;
                                        margin:0 auto 24px auto;
                                        border-radius:24px;
                                        background:linear-gradient(135deg, #7c3aed, #22d3ee);
                                        text-align:center;
                                        line-height:72px;
                                        font-size:32px;
                                    ">
                                        ✉️
                                    </div>

                                    <p style="margin:0 0 12px 0; text-align:center; font-size:12px; font-weight:700; letter-spacing:0.2em; text-transform:uppercase; color:#67e8f9;">
                                        Presque terminé
                                    </p>

                                    <h1 style="margin:0; text-align:center; font-size:34px; line-height:1.2; font-weight:800; color:#ffffff;">
                                        Confirme ton adresse email
                                    </h1>

                                    <p style="margin:20px 0 0 0; text-align:center; font-size:15px; line-height:1.8; color:rgba(255,255,255,0.70);">
                                        Salut {{ $user->username ?? $user->name }},
                                        ton compte a bien été créé sur SquadBase.
                                        Clique sur le bouton ci-dessous pour confirmer ton adresse email
                                        et débloquer toutes les fonctionnalités sociales de la plateforme.
                                    </p>

                                    <div style="
                                        margin:28px 0 0 0;
                                        padding:16px 18px;
                                        border-radius:20px;
                                        border:1px solid rgba(251,191,36,0.20);
                                        background:rgba(251,191,36,0.10);
                                        color:#fde68a;
                                        font-size:14px;
                                        line-height:1.7;
                                    ">
                                        Tant que ton email n’est pas vérifié, certaines actions comme publier,
                                        commenter, aimer du contenu ou envoyer des messages peuvent être limitées.
                                    </div>

                                    <div style="text-align:center; margin-top:32px;">
                                        <a href="{{ $verificationUrl }}"
                                           style="
                                               display:inline-block;
                                               padding:15px 28px;
                                               border-radius:18px;
                                               background:linear-gradient(90deg, #7c3aed, #22d3ee);
                                               color:#ffffff;
                                               text-decoration:none;
                                               font-size:15px;
                                               font-weight:700;
                                               box-shadow:0 12px 30px rgba(34,211,238,0.18);
                                           ">
                                            Vérifier mon adresse email
                                        </a>
                                    </div>

                                    <p style="margin:28px 0 0 0; text-align:center; font-size:13px; line-height:1.8; color:rgba(255,255,255,0.45);">
                                        Si tu n’as pas créé de compte sur SquadBase, tu peux simplement ignorer cet email.
                                    </p>

                                    <div style="margin-top:32px; padding-top:24px; border-top:1px solid rgba(255,255,255,0.08);">
                                        <p style="margin:0; text-align:center; font-size:12px; line-height:1.8; color:rgba(255,255,255,0.35);">
                                            SquadBase — crée ton profil, trouve ta communauté, partage ton univers gaming.
                                        </p>
                                    </div>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <td style="padding-top:20px; text-align:center;">
                                <p style="margin:0; font-size:12px; color:rgba(255,255,255,0.30); line-height:1.7;">
                                    Si le bouton ne fonctionne pas, copie ce lien dans ton navigateur :
                                </p>
                                <p style="margin:10px 0 0 0; font-size:12px; line-height:1.7; word-break:break-all;">
                                    <a href="{{ $verificationUrl }}" style="color:#67e8f9; text-decoration:none;">
                                        {{ $verificationUrl }}
                                    </a>
                                </p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>