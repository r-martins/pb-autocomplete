/**
 * PB Autocomplete – Checkout em Blocos (WooCommerce Blocks).
 * Usa registerPlugin + slot fill para rodar dentro do React do checkout e atualizar
 * a store (wc/store/cart) via useDispatch, sem intercept de fetch nem JS puro no DOM.
 */
import { registerPlugin } from '@wordpress/plugins';
import { useEffect, useRef, useState } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';

const CART_STORE = 'wc/store/cart';

function normalizeCep( cep ) {
	return String( cep || '' ).replace( /\D/g, '' ).slice( 0, 8 );
}

function parseCepResponse( data, digits ) {
	if ( ! data || typeof data !== 'object' || data.erro ) return null;
	const postcode = ( data.postcode || data.cep || '' ).replace( /\D/g, '' ) || digits;
	const address_1 = data.address_1 || data.logradouro || '';
	const address_2 = data.address_2 !== undefined
		? data.address_2
		: [ data.bairro, data.complemento ].filter( Boolean ).join( ', ' ).trim();
	const city = data.city || data.localidade || '';
	const state = ( data.state || data.uf || '' ).toUpperCase().slice( 0, 2 );
	const neighborhood = data.neighborhood || data.bairro || '';
	return { postcode, address_1, address_2: address_2 || '', city, state, neighborhood };
}

function fetchFromUrl( url ) {
	return fetch( url ).then( ( r ) => r.json() ).catch( () => null );
}

function fetchAddressByCep( cep ) {
	const digits = normalizeCep( cep );
	if ( digits.length !== 8 ) return Promise.resolve( null );
	const openCepUrl = `https://opencep.com/v1/${ digits }`;
	const viaCepUrl = `https://viacep.com.br/ws/${ digits }/json/`;
	return fetchFromUrl( openCepUrl ).then( ( data ) => {
		const parsed = parseCepResponse( data, digits );
		if ( parsed ) return parsed;
		return fetchFromUrl( viaCepUrl ).then( ( data2 ) => parseCepResponse( data2, digits ) );
	} );
}

function AddressAutocompleteFill() {
	// O "CEP aparece primeiro" (reorder) é configurável no editor, mas o autocomplete
	// em si deve sempre funcionar ao digitar um CEP válido (8 dígitos).
	const watchBilling = true;
	const watchShipping = true;

	const billingAddress = useSelect( ( select ) => {
		const cart = select( CART_STORE );
		if ( ! cart ) return {};
		if ( typeof cart.getBillingAddress === 'function' ) return cart.getBillingAddress() || {};
		const data = typeof cart.getCustomerData === 'function' ? cart.getCustomerData() : ( typeof cart.getCartData === 'function' ? cart.getCartData() : null );
		return ( data && data.billingAddress ) ? data.billingAddress : {};
	}, [] );

	const shippingAddress = useSelect( ( select ) => {
		const cart = select( CART_STORE );
		if ( ! cart ) return {};
		if ( typeof cart.getShippingAddress === 'function' ) return cart.getShippingAddress() || {};
		const data = typeof cart.getCustomerData === 'function' ? cart.getCustomerData() : ( typeof cart.getCartData === 'function' ? cart.getCartData() : null );
		return ( data && data.shippingAddress ) ? data.shippingAddress : {};
	}, [] );

	const { setBillingAddress, setShippingAddress } = useDispatch( CART_STORE ) || {};
	const lastFetchedCep = useRef( { billing: null, shipping: null } );
	// Evita requisições duplicadas quando billing e shipping têm o mesmo CEP (ex.: "usar mesmo endereço").
	const pendingFetchByCep = useRef( {} );
	// Guarda último endereço preenchido por CEP para reaplicar quando o batch sobrescrever a store.
	const lastFilledAddress = useRef( { billing: null, shipping: null } );
	const [ isFetchingBySection, setIsFetchingBySection ] = useState( {
		billing: false,
		shipping: false,
	} );

	// Dots no texto que o usuário enxerga (label "Endereço", label "Cidade" e opção inicial do select "Estado").
	const textIntervals = useRef( {} );
	const originalTexts = useRef( {} );
	const dots = [ '.', '..', '...' ];

	const getAddressLabelEl = ( sectionKey ) => {
		if ( typeof document === 'undefined' ) return null;
		return document.querySelector( `label[for="${ sectionKey }-address_1"]` );
	};

	const getCityLabelEl = ( sectionKey ) => {
		if ( typeof document === 'undefined' ) return null;
		return document.querySelector( `label[for="${ sectionKey }-city"]` );
	};

	const getStateOptionEl = ( sectionKey ) => {
		if ( typeof document === 'undefined' ) return null;
		const select = document.querySelector( `select#${ sectionKey }-state` );
		if ( ! select ) return null;
		return select.querySelector( 'option[data-alternate-values]' ) || select.querySelector( 'option[value=""][disabled]' );
	};

	const startSectionDots = ( sectionKey ) => {
		if ( textIntervals.current[ sectionKey ] ) return;
		if ( typeof document === 'undefined' ) return;

		const addressLabel = getAddressLabelEl( sectionKey );
		const cityLabel = getCityLabelEl( sectionKey );
		const stateOption = getStateOptionEl( sectionKey );

		if ( ! addressLabel && ! cityLabel && ! stateOption ) return;

		const stored = originalTexts.current[ sectionKey ] || {};
		if ( addressLabel && stored.address === undefined ) stored.address = addressLabel.innerHTML;
		if ( cityLabel && stored.city === undefined ) stored.city = cityLabel.innerHTML;
		if ( stateOption && stored.state === undefined ) stored.state = stateOption.innerHTML;
		originalTexts.current[ sectionKey ] = stored;

		let i = 0;
		const apply = () => {
			const current = originalTexts.current[ sectionKey ];
			if ( addressLabel && current.address !== undefined ) addressLabel.innerHTML = `${ current.address } ${ dots[ i ] }`;
			if ( cityLabel && current.city !== undefined ) cityLabel.innerHTML = `${ current.city } ${ dots[ i ] }`;
			if ( stateOption && current.state !== undefined ) stateOption.innerHTML = `${ current.state } ${ dots[ i ] }`;
		};

		apply();

		textIntervals.current[ sectionKey ] = window.setInterval( () => {
			i = ( i + 1 ) % dots.length;
			apply();
		}, 350 );
	};

	const stopSectionDots = ( sectionKey ) => {
		const interval = textIntervals.current[ sectionKey ];
		if ( interval ) {
			window.clearInterval( interval );
			delete textIntervals.current[ sectionKey ];
		}

		const stored = originalTexts.current[ sectionKey ];
		if ( ! stored ) return;

		const addressLabel = getAddressLabelEl( sectionKey );
		const cityLabel = getCityLabelEl( sectionKey );
		const stateOption = getStateOptionEl( sectionKey );

		if ( addressLabel && stored.address !== undefined ) addressLabel.innerHTML = stored.address;
		if ( cityLabel && stored.city !== undefined ) cityLabel.innerHTML = stored.city;
		if ( stateOption && stored.state !== undefined ) stateOption.innerHTML = stored.state;

		delete originalTexts.current[ sectionKey ];
	};

	// Só atualizamos a store local (setBillingAddress/setShippingAddress). Não chamamos
	// updateCustomerData para não disparar o batch e evitar loop: batch sobrescreve -> reaplicamos -> batch de novo.
	// O checkout envia o estado atual da store no pedido.

	// 1) Busca CEP e preenche endereço quando o usuário informa 8 dígitos.
	useEffect( () => {
		if ( ! setBillingAddress || ! setShippingAddress ) return;

		const run = async ( section, postcode ) => {
			const digits = normalizeCep( postcode );
			if ( digits.length !== 8 ) return;
			if ( section === 'billing' && lastFetchedCep.current.billing === digits ) return;
			if ( section === 'shipping' && lastFetchedCep.current.shipping === digits ) return;

			// Reutiliza fetch em andamento para o mesmo CEP (billing e shipping iguais = uma só requisição).
			if ( ! pendingFetchByCep.current[ digits ] ) {
				pendingFetchByCep.current[ digits ] = fetchAddressByCep( digits ).then( ( result ) => {
					delete pendingFetchByCep.current[ digits ];
					return result;
				} );
			}
			// Mostra o loader nos campos enquanto buscamos.
			setIsFetchingBySection( ( prev ) => ( {
				...prev,
				[ section ]: true,
			} ) );

			try {
				const data = await pendingFetchByCep.current[ digits ];
				if ( ! data ) return;

				const country = section === 'billing'
					? ( billingAddress.country || 'BR' )
					: ( shippingAddress.country || 'BR' );
				const current = section === 'billing' ? billingAddress : shippingAddress;
				const merged = {
					...current,
					postcode: digits,
					address_1: data.address_1 || '',
					address_2: current.address_2 || '', // Não preenche complemento; deixa o que já estava ou vazio.
					city: data.city || '',
					state: data.state || '',
					country,
				};
				if ( data.neighborhood ) merged.neighborhood = data.neighborhood;

				if ( section === 'billing' ) {
					lastFetchedCep.current.billing = digits;
					lastFilledAddress.current.billing = merged;
					setBillingAddress( merged );
				} else {
					lastFetchedCep.current.shipping = digits;
					lastFilledAddress.current.shipping = merged;
					setShippingAddress( merged );
				}
			} finally {
				setIsFetchingBySection( ( prev ) => ( {
					...prev,
					[ section ]: false,
				} ) );
			}
		};

		const billingCep = normalizeCep( billingAddress?.postcode );
		const shippingCep = normalizeCep( shippingAddress?.postcode );

		if ( watchBilling && billingCep.length === 8 ) {
			run( 'billing', billingCep );
		}
		if ( watchShipping && shippingCep.length === 8 ) {
			run( 'shipping', shippingCep );
		}
	// Só reage a mudança de CEP; merge usa billing/shipping do render atual.
	// eslint-disable-next-line react-hooks/exhaustive-deps -- dispatch estável; billing/shipping usados no closure.
	}, [
		billingAddress?.postcode,
		shippingAddress?.postcode,
		watchBilling,
		watchShipping,
		setBillingAddress,
		setShippingAddress,
	] );

	// Controla placeholders animados conforme o loader ligado/desligado.
	useEffect( () => {
		if ( isFetchingBySection.billing ) startSectionDots( 'billing' );
		else stopSectionDots( 'billing' );
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [ isFetchingBySection.billing ] );

	useEffect( () => {
		if ( isFetchingBySection.shipping ) startSectionDots( 'shipping' );
		else stopSectionDots( 'shipping' );
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [ isFetchingBySection.shipping ] );

	// 2) Reaplica endereço do CEP quando a store foi sobrescrita pela resposta do batch
	//    (postcode continua 8 dígitos mas cidade/estado ficaram vazios). Só set local, sem updateCustomerData.
	useEffect( () => {
		if ( ! setBillingAddress || ! setShippingAddress ) return;

		const reapplyIfOverwritten = ( section, current, filled ) => {
			if ( ! filled || ! current ) return;
			const postcode = normalizeCep( current.postcode );
			if ( postcode.length !== 8 || postcode !== normalizeCep( filled.postcode ) ) return;
			const missing = ! ( current.city && current.state );
			if ( ! missing ) return;
			const restored = { ...filled, ...current, city: filled.city, state: filled.state, address_1: filled.address_1, address_2: current.address_2 || '' };
			if ( section === 'billing' ) {
				lastFilledAddress.current.billing = null;
				setBillingAddress( restored );
			} else {
				lastFilledAddress.current.shipping = null;
				setShippingAddress( restored );
			}
		};

		if ( watchBilling ) {
			reapplyIfOverwritten( 'billing', billingAddress, lastFilledAddress.current.billing );
		}
		if ( watchShipping ) {
			reapplyIfOverwritten( 'shipping', shippingAddress, lastFilledAddress.current.shipping );
		}
	}, [
		billingAddress,
		shippingAddress,
		watchBilling,
		watchShipping,
		setBillingAddress,
		setShippingAddress,
	] );

	return null;
}

function registerCheckoutAutocomplete() {
	const wc = typeof window !== 'undefined' ? window.wc : null;
	const blocksCheckout = wc && wc.blocksCheckout ? wc.blocksCheckout : {};
	const { ExperimentalOrderMeta } = blocksCheckout;

	if ( ! ExperimentalOrderMeta ) {
		return;
	}

	const render = () => (
		<ExperimentalOrderMeta>
			<AddressAutocompleteFill />
		</ExperimentalOrderMeta>
	);

	registerPlugin( 'pb-autocomplete-checkout', {
		render,
		scope: 'woocommerce-checkout',
	} );
}

registerCheckoutAutocomplete();
