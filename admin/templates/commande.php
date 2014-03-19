<?php

$html_page = <<<HTML
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF8">
<title>Commande</title>
</head>
<body>
<style type="text/css">
    body,td { color:#2f2f2f; font:11px/1.35em Verdana, Arial, Helvetica, sans-serif; }
</style>

<div style="font:11px/1.35em Verdana, Arial, Helvetica, sans-serif;text-align: left;">
<table cellspacing="0" cellpadding="0" border="0" width="98%" style="margin-top:10px; font:11px/1.35em Verdana, Arial, Helvetica, sans-serif; margin-bottom:10px;">
<tr>
    <td align="center" valign="top">
        <!-- [ header starts here] -->
        <table cellspacing="0" cellpadding="0" border="0" width="650">
            <tr>
                <td align="left" valign="top"><a href="http://www.doublet.com"><img src="http://contact.doublet.pro/logo-doublet-home.jpg" alt="Doublet" style="margin-bottom:10px;" border="0"/></a></td>
            </tr>
        </table>
        <!-- [ middle starts here] -->
        <table cellspacing="0" cellpadding="0" border="0" width="650">
            <tr>
                <td align="left" valign="top">
                    <p>
                        <strong>Bonjour {$data['ClientPrenom']} {$data['ClientNom']}</strong>,<br/>
						Nous vous remerçions pour la confiance que vous nous accordez en commandant 
						sur notre site Doublet. Nous allons traiter votre commande dans les 
						meilleurs délais et nous vous informerons du suivi de celle-ci. Si 
						vous avez la moindre question concernant votre commande ou un de nos 
						produits, vous pouvez nous contacter par email 
						<a style="color: #0080c6" href="mailto:{$data['DoubletMail']}">{$data['DoubletMail']}</a>
						ou par téléphone au <span class=nobr>{$data['DoubletTel']}</span> de 8h30 à 18h30 
						du lundi au vendredi.
					</p>
					<p>
						Pour rappel, veuillez trouver le détail de votre commande, ci-dessous.
					</p>
                    <h3 align="left" style="border-bottom:2px solid #eee; font-size:1.05em; padding-bottom:1px;"><span style="color:#C00;">Votre commande #{$data['CommandeNum']}</span> <small>(du {$data['CommandeDate']})</small></h3>
                    <table cellspacing="0" cellpadding="0" border="0" width="100%">
                        <thead>
                        <tr>
                            <th align="left" width="48.5%" bgcolor="#0080C6" style="padding:5px 9px 6px 9px; border:1px solid #0080C6; border-bottom:none; line-height:1em; color:#FFFFFF;">Adresse de facturation :</th>
                            <th width="3%"></th>
                            <th align="left" width="48.5%" bgcolor="#0080C6" style="padding:5px 9px 6px 9px; border:1px solid #0080C6; border-bottom:none; line-height:1em; color:#FFFFFF;">Mode de paiement :</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td align="left" valign="top" style="padding:7px 9px 9px 9px; border:1px solid #0080C6; border-top:0; background:#FFFFFF;">
                                {$data['AdresseFacturation']}
                            </td>
                            <td>&nbsp;</td>
                            <td align="left" valign="top" style="padding:7px 9px 9px 9px; border:1px solid #0080C6; border-top:0; background:#FFFFFF;">
								{$data['ModePaiement']}
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <br />
                    <table cellspacing="0" cellpadding="0" border="0" width="100%">
                        <thead>
                        <tr>
                            <th align="left" width="48.5%" bgcolor="#0080C6" style="padding:5px 9px 6px 9px; border:1px solid #0080C6; border-bottom:none; line-height:1em; color:#FFFFFF;">Adresse de livraison :</th>
                            <th width="3%"></th>
                            <th align="left" width="48.5%" bgcolor="#0080C6" style="padding:5px 9px 6px 9px; border:1px solid #0080C6; border-bottom:none; line-height:1em; color:#FFFFFF;">Votre message personnel :</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td align="left" valign="top" style="padding:7px 9px 9px 9px; border:1px solid #0080C6; border-top:0; background:#FFFFFF;">
                                {$data['AdresseLivraison']}
                            </td>
                            <td>&nbsp;</td>
                            <td align="left" valign="top" style="padding:7px 9px 9px 9px; border:1px solid #0080C6; border-top:0; background:#FFFFFF;">
								{$data['MessageClient']}
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <br />
					<table style="border-right: #bebcb7 1px solid; border-top: #bebcb7 1px solid; background: #f8f7f5; border-left: #bebcb7 1px solid; border-bottom: #bebcb7 1px solid" cellSpacing=0 cellPadding=0 width="100%" border=0>
              			<thead>
              				<tr>
                				<th style="padding-right: 9px; padding-left: 9px; padding-bottom: 3px; padding-top: 3px" align=left bgColor=#d9e5ee>
									Article
								</th>
                				<th style="padding-right: 9px; padding-left: 9px; padding-bottom: 3px; padding-top: 3px" align=left bgColor=#d9e5ee>
									Réf.
								</th>
                				<th style="padding-right: 9px; padding-left: 9px; padding-bottom: 3px; padding-top: 3px" align=left bgColor=#d9e5ee>
									Prix
								</th>
                				<th style="padding-right: 9px; padding-left: 9px; padding-bottom: 3px; padding-top: 3px" align=left bgColor=#d9e5ee>
									Eco-contribution
								</th>
                				<th style="padding-right: 9px; padding-left: 9px; padding-bottom: 3px; padding-top: 3px" align=middle bgColor=#d9e5ee>
									Quantité
								</th>
                				<th style="padding-right: 9px; padding-left: 9px; padding-bottom: 3px; padding-top: 3px" align=right bgColor=#d9e5ee>
									Sous-total
								</th>
							</tr>
						</thead>
              			<tbody bgColor=#eeeded>
							{$data['ListeArticles']}
						</tbody>
              			<tfoot>
              				<tr>
                				<td style="padding-right: 9px; padding-left: 9px; padding-bottom: 3px; padding-top: 3px" align=right colSpan=5>
									Sous-total
								</td>
                				<td style="padding-right: 9px; padding-left: 9px; padding-bottom: 3px; padding-top: 3px" align=right>
									<span class=price>{$data['SousTotal']}</span>
								</td>
							</tr>
              				<tr>
                				<td style="padding-right: 9px; padding-left: 9px; padding-bottom: 3px; padding-top: 3px" align=right colSpan=5>
									Livraison et traitement
								</td>
                				<td style="padding-right: 9px; padding-left: 9px; padding-bottom: 3px; padding-top: 3px" align=right>
									<span class=price>{$data['FraisDePort']}</span>
								</td>
							</tr>
              				<tr>
                				<td style="padding-right: 9px; padding-left: 9px; padding-bottom: 3px; padding-top: 3px" align=right colSpan=5>
									TVA
								</td>
                				<td style="padding-right: 9px; padding-left: 9px; padding-bottom: 3px; padding-top: 3px" align=right>
									<span class=price>{$data['TVA']}</span>
								</td>
							</tr>
              				<tr bgColor=#dee5e8>
                				<td style="padding-right: 9px; padding-left: 9px; padding-bottom: 3px; padding-top: 3px" align=right colSpan=5>
									<strong><big>Montant global</big></strong>
								</td>
                				<td style="padding-right: 9px; padding-left: 9px; padding-bottom: 6px; padding-top: 6px" align=right>
									<strong><big><span class=price>{$data['MontantGlobal']}</span></big></strong>
								</td>
							</tr>
						</tfoot>
					</table>
                    <br />
                    <p align="left">Merci encore pour votre confiance<br/><strong>{$data['Signature']}</strong></p>
                </td>
            </tr>
        </table>
    </td>
</tr>
</table>
</div>
</body>
</html>
HTML;


