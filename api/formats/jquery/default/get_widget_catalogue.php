<?php

$jq = array();

$api_data = $api->data();
$tva = isset($api_data['tva']) ? (float)$api_data['tva'] : 0;
$tva_line = "";
if ($tva) {
	$tva_line = <<<HTML
<p class="doublet-cart-tva">TVA : <span class="doublet-catalogue-panier-tva">0.00</span>&nbsp;€</p>
HTML;
	$jq['tva'] = $tva; 

}

$frais_de_port_lignes = array();
$max = "-";
foreach (array_reverse($data["forfaits"], true) as $min => $tarif) {
	$tarif = $tarif ? $tarif." €" : "offerts";
	$frais_de_port_lignes[] = "<tr><td>{$min} €</td><td>{$max}</td><td>{$tarif}</td></tr>";
	$max = ($min - 1)." €";
}
$lignes_frais_de_port = implode("", array_reverse($frais_de_port_lignes));


$checkout_url = "http://{$_SERVER['HTTP_HOST']}{$config->get('base_url')}checkout.php?key={$_GET['key']}";

$logo = "http://".$_SERVER['SERVER_NAME'].$config->media('logo-doublet.jpg');
$jq['html'] = <<<HTML
<div id="doublet-catalogue-top" class="boutique-doublet">
	<div id="doublet-catalogue-overlay">
		<div id="doublet-catalogue-overlay-message">Veuillez finaliser le paiement sur le site de Doublet</div>
	</div>
	<div class="doublet-catalogue-title">
		<h1 id="doublet-catalogue-title">La boutique doublet</h1>
		<p id="doublet-catalogue-slogan">Effectuez vos achat sur la boutique Doublet</p>
	</div>
	<div class="doublet-catalogue-container clearfix">
		<div class="doublet-catalogue-left">
			<div class="doublet-catalogue-panier">
				<p class="titre">Mon panier</p>
				<p>Nombre d'article(s) : <strong class="doublet-catalogue-panier-number">0</strong></p>
				<p>Total : <strong class="doublet-catalogue-panier-total">0.00</strong><strong>&nbsp;€</strong></p>
				<button type="button" class="doublet-catalogue-action doublet-catalogue-action-panier">Voir le panier</button>
			</div>
			<div class="doublet-catalogue-area-catalogue">
			</div>
			<div class="doublet-catalogue-footer">
				<div id="doublet-catalogue-contact"></div>
				<div class="doublet-logo"><a href="http://www.doublet.fr/" title="" target="_blank"><img src="{$logo}" alt="Logo Doublet"/></a></div>
			</div>
			
		</div>
		<div class="doublet-catalogue-area doublet-catalogue-area-panier doublet-catalogue-right">
			<h2 class="titre">Panier</h2>
			<div class="doublet-cart-empty">
				<p>Votre panier est vide.</p>
				<button type="button" class="doublet-cart-action doublet-cart-close">Continuer les achats</button>
			</div>
			<table class="checkout-products doublet-cart-table">
				<thead>
					<tr>
						<th></th>
						<th><p>Article</p></th>
						<th></th>
						<th><p>Prix unitaire HT</p></th>
						<th><p>Quantité</p></th>
						<th><p>Sous total</p></th>
						<th></th>
					</tr>
				</thead>
				<tbody class="doublet-cart-list"></tbody>
				<tfoot>
					<tr>
						<td colspan="4">
							<button type="button" class="doublet-cart-action doublet-cart-close">Continuer les achats</button>						
						</td>
						<td colspan="2">
							<button type="button" class="doublet-cart-action">Rafraichir le panier</button>	
						</td>
					</tr>
				</tfoot>
			</table>
			<div class="doublet-cart-checkout-box clearfix">
				<p class="doublet-cart-forfait">Frais de port : <span class="doublet-catalogue-panier-forfait">0.00</span>&nbsp;€</p>
				$tva_line
				<p class="doublet-cart-price">Total : <strong class="doublet-catalogue-panier-total">0.00</strong><strong>&nbsp;€</strong></p>
				<button type="button" href="{$checkout_url}" class="doublet-cart-action doublet-cart-checkout">Passer la commande</button>	
			</div>	
			<div class="doublet-cart-forfait-box">
				<div>
					<h4>Gestion commande et livraison</h4>
					<p>Pour le suivi de votre commande et les frais de port, veuillez trouver ci-contre les forfaits que nous appliquons pour la France continentale. Autres pays et région sur devis.</p>
				</div>
				<table class="doublet-cart-forfait">
					<thead>
						<tr>
							<th>De</th>
							<th>à</th>
							<th>Frais de port</th>
						</tr>
					</thead>
					<tbody>
						$lignes_frais_de_port
					</tbody>
				</table>
			</div>
		</div>
		<div class="doublet-catalogue-area doublet-catalogue-area-produit doublet-catalogue-right" style="display: none;"></div>
		<div class="doublet-catalogue-area doublet-catalogue-area-categorie doublet-catalogue-right" style="display: none;"></div>
		<div class="doublet-catalogue-bottom"></div>
	</div>
</div>
HTML;

$jq['items']['widget'][] = array(
	'selector' => ".boutique-doublet",
	'right' => ".doublet-catalogue-right",
	'title' => "#doublet-catalogue-title",
	'slogan' => "#doublet-catalogue-slogan",
	'contact' => "#doublet-catalogue-contact",
);
$jq['items']['overlay'][] = array('selector' => "#doublet-catalogue-overlay");
$jq['items']['catalogue_area'][] = array('selector' => ".doublet-catalogue-area-catalogue");
$jq['items']['category_area'][] = array('selector' => ".doublet-catalogue-area-categorie");
$jq['items']['product_area'][] = array('selector' => ".doublet-catalogue-area-produit");
$jq['items']['cart_area'][] = array('selector' => ".doublet-catalogue-area-panier");
$jq['items']['cart_content'][] = array('selector' => ".doublet-cart-list");
$jq['items']['cart_price'][] = array('selector' => "#doublet-cart-price");
$jq['items']['cart_total'][] = array('selector' => ".doublet-catalogue-panier-total");
$jq['items']['cart_forfait'][] = array('selector' => ".doublet-catalogue-panier-forfait");
$jq['items']['cart_tva'][] = array('selector' => ".doublet-catalogue-panier-tva");
$jq['items']['cart_number'][] = array('selector' => ".doublet-catalogue-panier-number");
$jq['items']['cart_close'][] = array('selector' => ".doublet-cart-close");
$jq['items']['cart_table'][] = array('selector' => ".doublet-cart-table");
$jq['items']['cart_empty'][] = array('selector' => ".doublet-cart-empty");
$jq['items']['cart_checkout'][] = array('selector' => ".doublet-cart-checkout");
$jq['items']['action_cart'][] = array('selector' => ".doublet-catalogue-action-panier");

$jq['dico']['added_to_cart'] = "ajouté au panier";
$jq['dico']['Estimate'] = "Devis";
$jq['dico']['Are_you_sure'] = "Êtes-vous sûr ?";
$jq['dico']['in_your_cart'] = "dans votre panier";

$data['jq'] = $jq;
