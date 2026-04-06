=== PB Autocomplete CEP for WooCommerce ===
Contributors: martins56
Tags: woocommerce, checkout, cep, autocomplete, brazil
Donate link: https://github.com/sponsors/r-martins
Requires at least: 5.2
Tested up to: 6.9
Stable tag: 1.0.4
Requires PHP: 7.4
Requires Plugins: woocommerce, pagbank-connect
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Autocompletes address fields from Brazilian postal code (CEP) on WooCommerce Block-based Checkout. Requires PagBank Connect.

== Description ==

**PB Autocomplete** automatically fills address fields (street, neighborhood, city, state) on WooCommerce **Block-based Checkout** when the customer enters a Brazilian postal code (CEP). It uses the public APIs [OpenCEP](https://opencep.com/) and [ViaCEP](https://viacep.com.br/), and only runs when [PagBank Connect](https://wordpress.org/plugins/pagbank-connect/) is installed and has at least one payment method available at checkout.

= Features =

* Address autocomplete by CEP on WooCommerce Block-based Checkout
* Integration with OpenCEP (primary) and ViaCEP (fallback) for Brazilian CEP data
* Option to show the postal code field first in billing or shipping (configurable in the block editor when editing the checkout page)
* Explicit dependency on WooCommerce and PagBank Connect

= Requirements =

* [WooCommerce](https://wordpress.org/plugins/woocommerce/) installed and active
* [PagBank Connect](https://wordpress.org/plugins/pagbank-connect/) installed and active with at least one payment method (PIX, card, boleto, etc.) enabled
* Use of WooCommerce **Block-based Checkout** (does not apply to the legacy/shortcode checkout)

== Installation ==

1. Ensure **WooCommerce** and **PagBank Connect** are installed and active
2. Install and activate PB Autocomplete (Plugins > Add New, search for "PB Autocomplete" or upload the zip)
3. If building from source: in the plugin folder run `npm install` then `npm run build`

== Configuration ==

1. Autocomplete works automatically on Block-based Checkout when the customer enters a valid 8-digit CEP. Address fields are filled after querying OpenCEP (or ViaCEP if needed).
2. To show the postal code field first: edit the **checkout page** in the block editor, select the "Shipping address" or "Billing address" block, and in the right-hand panel open the **PB Autocomplete** section. Check the desired options and click **Save** at the top.

== External services ==

This plugin sends **HTTPS GET** requests from the **customer’s browser** (WooCommerce Block Checkout) to third-party APIs in order to look up Brazilian address data for a postal code (CEP). The plugin does **not** send the customer’s name, email, phone, or full street address to these APIs—only the **CEP digits** are included in the request URL path, when the shopper has entered a valid **8-digit** CEP.

= OpenCEP (primary) =

* **What it is / used for:** Public CEP lookup API operated at `opencep.com`, used to return street, neighborhood, city, and state for the entered CEP.
* **What data is sent and when:** When autocomplete runs, the browser requests `https://opencep.com/v1/{CEP}` (8 digits only). Typical browser metadata (IP address, user agent, referrer) may be processed by the service or its infrastructure like any normal website request.
* **Terms:** OpenCEP project license (MIT): https://github.com/SeuAliado/OpenCEP/blob/main/LICENSE  
  Service overview: https://opencep.com/
* **Privacy:** Requests to `opencep.com` are served through **Cloudflare** (CDN). See Cloudflare’s privacy policy: https://www.cloudflare.com/privacypolicy/

= ViaCEP (fallback) =

* **What it is / used for:** Public CEP webservice at `viacep.com.br`, used only if OpenCEP does not return usable data for the same CEP.
* **What data is sent and when:** The browser requests `https://viacep.com.br/ws/{CEP}/json/`. Only the CEP is included in the path. The same note about typical browser/request metadata applies.
* **Terms / service conditions:** Official documentation and acceptable-use notes (including limitations on abusive bulk use) are published on the ViaCEP website: https://viacep.com.br/
* **Privacy:** ViaCEP does **not** publish a separate privacy policy URL. For operator contact, see: https://viacep.com.br/faleconosco/

= PagBank Connect (required dependency) =

PB Autocomplete requires the **PagBank Connect** plugin. Payment processing, credentials, and any additional third-party services are governed by **PagBank Connect** and PagBank—not by PB Autocomplete. See the PagBank Connect plugin documentation on WordPress.org at https://wordpress.org/plugins/pagbank-connect/ and Terms and Privacy at https://pbintegracoes.com/terms

== Frequently Asked Questions ==

= Does the plugin work with the legacy (shortcode) WooCommerce checkout? =

No. PB Autocomplete is built only for WooCommerce **Block-based Checkout**. On the legacy checkout, address fields are not auto-filled by this plugin. Other plugins exist for that setup.

= Why does autocomplete not appear on my checkout? =

Check that: (1) PagBank Connect is active and has at least one payment method enabled in WooCommerce settings; (2) your store uses Block-based Checkout (checkout page built with blocks); (3) the CEP entered has 8 digits and is valid in OpenCEP or ViaCEP.

= Where do the address data come from? =

Data are fetched from the public API [OpenCEP](https://opencep.com/), which returns street, neighborhood, city, and state from the given CEP.

If OpenCEP is unavailable, [ViaCEP](https://viacep.com.br/) is used as a fallback.

= Can I use it without PagBank Connect? =

No. PB Autocomplete is part of the PagBank Integrações ecosystem and requires the PagBank Connect plugin to be active with a payment method available. Otherwise, the autocomplete script is not loaded on checkout.

= How do I show the postal code field first? =

Edit the checkout page in the block editor, select the address block (shipping or billing), and in the right panel open the PB Autocomplete section. Check the options and click Save at the top.

== Changelog ==

= 1.0.4 =
* Document third-party/external services (OpenCEP, ViaCEP, PagBank Connect) in the readme for WordPress.org compliance.
* Limit dependency admin notices to the Plugins and Add Plugin screens (WordPress.org Plugin Directory guideline 11).
* Detect PagBank payment methods by gateway id `rm-pagbank` (unified) or `rm-pagbank-*` (PIX, cartão, boleto, Checkout PagBank, recorrência, etc.); optional admin notice when PagBank Connect is active but no PagBank method is enabled (Plugins, Add Plugin, WooCommerce → Settings → Checkout).
* Run the “no PagBank payment method” check on the `init` hook so other plugins’ translations are not loaded too early (WordPress 6.7+).

= 1.0.3 =
* Current plugin version.

== Screenshots ==

1. Block-based Checkout with CEP field and address autocomplete
2. PB Autocomplete panel in the block editor when editing the checkout address block
