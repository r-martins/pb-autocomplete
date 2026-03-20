=== PB Autocomplete CEP for WooCommerce ===
Contributors: martins56
Tags: woocommerce, checkout, cep, autocomplete, brasil
Donate link: https://github.com/sponsors/r-martins
Requires at least: 5.2
Tested up to: 6.9
Stable tag: 1.0.3
Requires PHP: 7.4
Requires Plugins: woocommerce, pagbank-connect
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Autocompleta endereço a partir do CEP no Checkout em Blocos do WooCommerce. Requer PagBank Connect ativo.

== Description ==

**PB Autocomplete** preenche automaticamente os campos de endereço (rua, bairro, cidade, estado) no **Checkout em Blocos** do WooCommerce quando o cliente informa o CEP. Utiliza as APIs públicas [OpenCEP](https://opencep.com/) e [ViaCEP](https://viacep.com.br/) e funciona apenas quando o [PagBank Connect](https://wordpress.org/plugins/pagbank-connect/) está instalado e com ao menos um método de pagamento disponível no checkout.

= Recursos =

* Autocomplete de endereço por CEP no Checkout em Blocos (nativo do WooCommerce)
* Integração com a API OpenCEP (dados de endereço para CEPs brasileiros) e ViaCEP (para redundância)
* Opção para exibir o campo CEP como primeiro campo na seção de cobrança ou entrega (configurável no editor de blocos ao editar a página de checkout)
* Dependência explícita: requer WooCommerce e PagBank Connect instalados e ativos

= Requisitos =

* [WooCommerce](https://wordpress.org/plugins/woocommerce/) instalado e ativo
* [PagBank Connect](https://wordpress.org/plugins/pagbank-connect/) instalado e ativo, com ao menos um método de pagamento (PIX, Cartão, Boleto etc.) habilitado
* Uso do **Checkout em Blocos** do WooCommerce (não se aplica ao checkout legado/clássico)

== Installation ==

= Instalação =

1. Certifique-se de ter o **WooCommerce** e o **PagBank Connect** instalados e ativos
2. Instale e ative o plugin PB Autocomplete (Plugins > Adicionar Novo > procurar por "PB Autocomplete" ou fazer upload do zip)
3. Se estiver compilando a partir do código-fonte: na pasta do plugin, execute `npm install` e depois `npm run build`

= Configuração =

1. O autocomplete passa a funcionar automaticamente no Checkout em Blocos quando o cliente digitar um CEP válido (8 dígitos). Os campos de endereço serão preenchidos após a consulta ao OpenCEP (ou ViaCEP, se necessário).
2. Para exibir o CEP como primeiro campo (acima do endereço): edite a **página de checkout** no editor de blocos, selecione o bloco "Endereço de entrega" ou "Endereço de cobrança" e, no painel à direita, abra a secção **PB Autocomplete**. Marque as opções desejadas e clique em **Salvar** no topo da página.

== Frequently Asked Questions ==

= O plugin funciona no checkout legado (clássico) do WooCommerce? =

Não. O PB Autocomplete foi desenvolvido apenas para o **Checkout em Blocos** do WooCommerce. No checkout legado, os campos não são preenchidos automaticamente por este plugin.
Há outros plugins no mercado para esta finalidade.

= Por que o autocomplete não aparece no meu checkout? =

Verifique se: (1) o PagBank Connect está ativo e tem ao menos um método de pagamento habilitado nas configurações do WooCommerce; (2) sua loja está usando o Checkout em Blocos (página de checkout construída com blocos); (3) o CEP informado tem 8 dígitos e é válido na base do OpenCEP ou ViaCEP.

= De onde vêm os dados de endereço? =

Os dados são obtidos da API pública [OpenCEP](https://opencep.com/), que retorna logradouro, bairro, cidade e UF a partir do CEP informado.

Se este estiver indisponível, usamos o [ViaCEP](https://viacep.com.br/) como alternativa.

= Posso usar sem o PagBank Connect? =

Não. O PB Autocomplete foi pensado para o ecossistema PagBank Integrações e depende do plugin PagBank Connect estar ativo e com método de pagamento disponível. Sem isso, o script de autocomplete não é carregado no checkout.

= Como faço pro CEP vir primeiro? =

Edite a página de checkout no editor de blocos, clique no bloco de endereço (entrega ou cobrança) e, no painel à direita, abra a secção PB Autocomplete. Marque as opções e clique em Salvar no topo.

== Changelog ==

= 1.0.3 =
* Versão atual do plugin.

== Screenshots ==

1. Checkout em Blocos com campo CEP e autocomplete de endereço
2. Painel PB Autocomplete no editor de blocos (ao editar o bloco de endereço do checkout)
