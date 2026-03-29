=== PB Autocomplete CEP for WooCommerce ===
Contributors: martins56
Tags: woocommerce, checkout, cep, autocomplete, brasil
Donate link: https://github.com/sponsors/r-martins
Requires at least: 5.2
Tested up to: 6.9
Stable tag: 1.0.4
Requires PHP: 7.4
Requires Plugins: woocommerce, pagbank-connect
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Autocompleta endereço a partir do CEP no Checkout em Blocos do WooCommerce. Requer PagBank Connect.

== Description ==

**PB Autocomplete** preenche automaticamente os campos de endereço (rua, bairro, cidade, estado) no **Checkout em Blocos** do WooCommerce quando o cliente informa o CEP. Utiliza as APIs públicas [OpenCEP](https://opencep.com/) e [ViaCEP](https://viacep.com.br/) e só é carregado quando o [PagBank Connect](https://wordpress.org/plugins/pagbank-connect/) está instalado e há ao menos um método de pagamento disponível no checkout.

= Recursos =

* Autocomplete de endereço por CEP no Checkout em Blocos do WooCommerce
* Integração com OpenCEP (principal) e ViaCEP (alternativa) para dados de CEP no Brasil
* Opção para exibir o CEP como primeiro campo na cobrança ou na entrega (configurável no editor de blocos ao editar a página de checkout)
* Dependência explícita de WooCommerce e PagBank Connect

= Requisitos =

* [WooCommerce](https://wordpress.org/plugins/woocommerce/) instalado e ativo
* [PagBank Connect](https://wordpress.org/plugins/pagbank-connect/) instalado e ativo, com ao menos um método de pagamento (PIX, cartão, boleto etc.) habilitado
* Uso do **Checkout em Blocos** do WooCommerce (não se aplica ao checkout legado/shortcode)

== Installation ==

1. Certifique-se de ter o **WooCommerce** e o **PagBank Connect** instalados e ativos
2. Instale e ative o PB Autocomplete (Plugins > Adicionar novo, pesquise por "PB Autocomplete" ou envie o zip)
3. Se estiver compilando a partir do código-fonte: na pasta do plugin, execute `npm install` e depois `npm run build`

== Configuration ==

1. O autocomplete funciona automaticamente no Checkout em Blocos quando o cliente informar um CEP válido (8 dígitos). Os campos de endereço são preenchidos após a consulta ao OpenCEP (ou ViaCEP, se necessário).
2. Para exibir o CEP como primeiro campo: edite a **página de checkout** no editor de blocos, selecione o bloco "Endereço de entrega" ou "Endereço de cobrança" e, no painel à direita, abra a secção **PB Autocomplete**. Marque as opções desejadas e clique em **Salvar** no topo.

== Frequently Asked Questions ==

= O plugin funciona no checkout legado (shortcode) do WooCommerce? =

Não. O PB Autocomplete foi desenvolvido apenas para o **Checkout em Blocos** do WooCommerce. No checkout legado, os campos não são preenchidos automaticamente por este plugin. Existem outros plugins para esse cenário.

= Por que o autocomplete não aparece no meu checkout? =

Verifique se: (1) o PagBank Connect está ativo e tem ao menos um método de pagamento habilitado nas configurações do WooCommerce; (2) a loja usa o Checkout em Blocos (página de checkout com blocos); (3) o CEP tem 8 dígitos e é válido no OpenCEP ou ViaCEP.

= De onde vêm os dados de endereço? =

Os dados vêm da API pública [OpenCEP](https://opencep.com/), que retorna logradouro, bairro, cidade e UF a partir do CEP.

Se o OpenCEP estiver indisponível, é usado o [ViaCEP](https://viacep.com.br/) como alternativa.

= Posso usar sem o PagBank Connect? =

Não. O PB Autocomplete integra o ecossistema PagBank Integrações e exige o PagBank Connect ativo com método de pagamento disponível. Caso contrário, o script de autocomplete não é carregado no checkout.

= Como faço para o CEP aparecer primeiro? =

Edite a página de checkout no editor de blocos, selecione o bloco de endereço (entrega ou cobrança) e, no painel à direita, abra a secção PB Autocomplete. Marque as opções e clique em Salvar no topo.

== Changelog ==

= 1.0.4 =
* Avisos de dependência limitados às telas Plugins e Adicionar plugin (diretriz 11 do diretório de plugins WordPress.org).
* Detecção de métodos PagBank pelos IDs de gateway `rm-pagbank` (unificado) ou `rm-pagbank-*` (PIX, cartão, boleto, Checkout PagBank, recorrência etc.); aviso opcional no admin quando o PagBank Connect está ativo mas nenhum método PagBank está habilitado (Plugins, Adicionar plugin, WooCommerce → Configurações → Finalizar compra).
* A verificação “sem método PagBank” executa no hook `init`, para não carregar traduções de outros plugins cedo demais (WordPress 6.7+).

= 1.0.3 =
* Versão atual do plugin.

== Screenshots ==

1. Checkout em Blocos com campo CEP e autocomplete de endereço
2. Painel PB Autocomplete no editor de blocos ao editar o bloco de endereço do checkout
