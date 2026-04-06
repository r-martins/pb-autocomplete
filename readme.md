# PB Autocomplete — CEP para WooCommerce

[![Versão mínima do PHP](https://img.shields.io/badge/php-%3E%3D%207.4-8892BF.svg?style=flat-square)](https://php.net/)
[![Última versão](https://img.shields.io/github/v/release/r-martins/pb-autocomplete)](https://github.com/r-martins/pb-autocomplete/releases)
![Último commit (master)](https://img.shields.io/github/last-commit/r-martins/pb-autocomplete/master)
![WordPress Plugin: Tested WP Version](https://img.shields.io/wordpress/plugin/tested/pb-autocomplete)
![Downloads por mês](https://img.shields.io/wordpress/plugin/dm/pb-autocomplete)
![Avaliação dos clientes no WordPress](https://img.shields.io/wordpress/plugin/stars/pb-autocomplete?color=yellow)
[![Deploy pro WordPress SVN](https://github.com/r-martins/pb-autocomplete/actions/workflows/releaseToSvnWp.yaml/badge.svg)](https://github.com/r-martins/pb-autocomplete/actions/workflows/releaseToSvnWp.yaml)

Autocompleta endereço a partir do CEP no **Checkout em Blocos** do WooCommerce. Funciona apenas com **[PagBank Connect](https://wordpress.org/plugins/pagbank-connect/)** ativo e com **ao menos um método de pagamento** PagBank disponível no checkout.

> Para o diretório do WordPress.org, o arquivo canônico é o [`readme.txt`](readme.txt) (português do Brasil). A variante em inglês está em [`readme-en_US.txt`](readme-en_US.txt). Este `readme.md` resume o mesmo conteúdo para o GitHub.

---

## Descrição

O **PB Autocomplete** preenche automaticamente os campos de endereço (rua, bairro, cidade, estado) no **Checkout em Blocos** quando o cliente informa o CEP. Utiliza as APIs públicas [OpenCEP](https://opencep.com/) (prioritária) e [ViaCEP](https://viacep.com.br/) (alternativa).

### Recursos

- Autocomplete de endereço por CEP no Checkout em Blocos (nativo do WooCommerce)
- Integração OpenCEP + ViaCEP (redundância)
- Opção para exibir o campo CEP como primeiro na seção de **cobrança** ou **entrega** (no editor de blocos, ao editar a página de checkout)
- Dependência explícita: WooCommerce e PagBank Connect instalados e ativos

### Requisitos

- [WooCommerce](https://wordpress.org/plugins/woocommerce/) instalado e ativo
- [PagBank Connect](https://wordpress.org/plugins/pagbank-connect/) instalado e ativo, com **pelo menos um** método de pagamento (PIX, cartão, boleto, etc.) **habilitado e disponível** no checkout
- Uso do **Checkout em Blocos** (não se aplica ao checkout legado / shortcode)

---

## Instalação

### Pelo WordPress.org (pacote pronto — recomendado)

1. Abra **[PB Autocomplete no diretório brasileiro](https://br.wordpress.org/plugins/pb-autocomplete/)** ou a [página em inglês no WordPress.org](https://wordpress.org/plugins/pb-autocomplete/) e use **Baixar** o ZIP oficial, ou no wp-admin vá em **Plugins → Adicionar novo**, procure **PB Autocomplete** e instale/atualize a partir do repositório.
2. Garanta que **WooCommerce** e **PagBank Connect** estão instalados e ativos, com **pelo menos um** método PagBank habilitado no checkout (ver [Requisitos](#requisitos) acima).
3. **Não** é necessário `npm install` nem `npm run build` — o pacote do WordPress.org já inclui a pasta `build/`.

### A partir do repositório (código-fonte / GitHub)

1. Certifique-se de que **WooCommerce** e **PagBank Connect** estão instalados e ativos.
2. Instale o plugin (clone, ZIP do [GitHub](https://github.com/r-martins/pb-autocomplete), etc.) e ative-o em **Plugins**.
3. Se a pasta `build/` não existir ou você alterou arquivos em `src/`, na raiz do plugin execute:

```bash
npm install
npm run build
```

---

## Configuração

1. O autocomplete funciona no Checkout em Blocos quando o cliente digita um **CEP válido (8 dígitos)**. Os campos são preenchidos após consulta ao **OpenCEP** (ou **ViaCEP**, se necessário).
2. Para mostrar o **CEP primeiro** (acima do endereço): edite a **página de checkout** no editor de blocos, selecione o bloco **Endereço de entrega** ou **Endereço de cobrança**, abra no painel lateral a seção **PB Autocomplete**, marque as opções desejadas e clique em **Salvar** no topo da página.

---

## Serviços externos

O plugin depende de **APIs públicas** para consultar endereço a partir do CEP. As requisições são feitas em **HTTPS** pelo **navegador do cliente** no checkout em blocos.

| Serviço | Uso | O que é enviado | Termos / política |
|--------|-----|-----------------|-------------------|
| **OpenCEP** (`opencep.com`) | Consulta principal | GET `https://opencep.com/v1/{CEP}` — só o CEP (8 dígitos) no URL. Não enviamos nome, e-mail, telefone nem endereço completo. | [Licença MIT (projeto OpenCEP)](https://github.com/SeuAliado/OpenCEP/blob/main/LICENSE) · [Privacidade Cloudflare](https://www.cloudflare.com/privacypolicy/) (CDN usada por `opencep.com`) |
| **ViaCEP** (`viacep.com.br`) | Fallback se OpenCEP falhar | GET `https://viacep.com.br/ws/{CEP}/json/` — só o CEP no caminho. | [Documentação e condições no site](https://viacep.com.br/) · [Contato do operador](https://viacep.com.br/faleconosco/) (não há política de privacidade em URL separada) |
| **PagBank Connect** | Dependência obrigatória do plugin | Dados de pagamento e integração PagBank são tratados pelo **PagBank Connect**, não por este plugin. | [Plugin no WordPress.org](https://wordpress.org/plugins/pagbank-connect/) |

> No WordPress.org, a seção **External services** em português do Brasil está no [`readme.txt`](readme.txt); a versão em inglês no [`readme-en_US.txt`](readme-en_US.txt).

---

## Perguntas frequentes

### O plugin funciona no checkout legado (clássico)?

**Não.** Foi desenvolvido apenas para o **Checkout em Blocos**. No checkout legado os campos não são preenchidos por este plugin.

### Por que o autocomplete não aparece no meu checkout?

Confira:

1. PagBank Connect ativo e com **pelo menos um** método de pagamento habilitado no WooCommerce;
2. Loja usando **Checkout em Blocos** (página de checkout em blocos);
3. CEP com **8 dígitos** e válido em OpenCEP ou ViaCEP.

### De onde vêm os dados de endereço?

Da API pública [OpenCEP](https://opencep.com/). Se estiver indisponível, usa [ViaCEP](https://viacep.com.br/).

### Posso usar sem o PagBank Connect?

**Não.** O plugin se integra ao ecossistema PagBank; sem PagBank Connect ativo e com método disponível, **o script de autocomplete não é carregado** no checkout.

### Como colocar o CEP como primeiro campo?

Edite a página de checkout no editor de blocos, selecione o bloco de endereço (entrega ou cobrança), abra **PB Autocomplete** no painel lateral, marque as opções e **Salve** a página.

---

## Capturas de tela

Imagens na pasta [`assets/`](assets/).

### Checkout com autocomplete

![Checkout em Blocos — campo CEP e autocomplete de endereço](assets/screenshot-1.png)

*Checkout em Blocos com campo CEP e autocomplete de endereço.*

### Editor de blocos — opções PB Autocomplete

![Editor de blocos — painel PB Autocomplete no bloco de endereço](assets/screenshot-2.png)

*Painel **PB Autocomplete** no editor de blocos ao editar o bloco de endereço do checkout.*

---

## Changelog

### 1.0.4

- Documentação de **serviços externos** (OpenCEP, ViaCEP, PagBank Connect) nos readmes para o WordPress.org.
- Demais itens: ver `readme.txt`.

### 1.0.3

- Versão antiga listada aqui apenas como referência; veja `readme.txt` para o histórico completo.

---

## Licença e metadados

| | |
|---|---|
| **Licença** | GPLv3 — [texto da licença](https://www.gnu.org/licenses/gpl-3.0.html) |
| **PHP** | 7.4+ |
| **WordPress** | 5.2+ (testado até 6.9 no `readme.txt`) |
| **Plugins obrigatórios** | `woocommerce`, `pagbank-connect` |

**Contribuidores:** martins56  
**Apoiar:** [GitHub Sponsors](https://github.com/sponsors/r-martins)
