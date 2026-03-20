/**
 * No editor do checkout, adiciona no bloco de endereço (entrega/cobrança) a opção
 * "Exibir CEP acima dos campos de endereço".
 * Padrão WordPress: alterações refletem no painel e no preview; só são gravadas
 * ao clicar no "Salvar" do topo (editPost meta marca o post como modificado).
 */
import { addFilter } from '@wordpress/hooks';
import { createElement, useState, useEffect } from '@wordpress/element';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, CheckboxControl } from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';
import { useSelect, useDispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

const NAMESPACE = '/pb-autocomplete/v1/settings';
const META_BILLING = '_pb_autocomplete_postcode_first_billing';
const META_SHIPPING = '_pb_autocomplete_postcode_first_shipping';
const ADDRESS_BLOCKS = [
	'woocommerce/checkout-shipping-address-block',
	'woocommerce/checkout-billing-address-block',
];

function AddressBlockSettingsPanel( { blockName } ) {
	const [ postcodeFirstBilling, setPostcodeFirstBilling ] = useState( false );
	const [ postcodeFirstShipping, setPostcodeFirstShipping ] = useState( false );
	const [ loading, setLoading ] = useState( true );
	const { editPost } = useDispatch( 'core/editor' );
	const existingMeta = useSelect( ( select ) => {
		const post = select( 'core/editor' ).getCurrentPost();
		return ( post && post.meta ) || {};
	}, [] );

	useEffect( () => {
		apiFetch( { path: NAMESPACE } )
			.then( ( data ) => {
				setPostcodeFirstBilling( !! data.postcode_first_billing );
				setPostcodeFirstShipping( !! data.postcode_first_shipping );
			} )
			.catch( () => {} )
			.finally( () => setLoading( false ) );
	}, [] );

	// Atualiza estado e marca o post como modificado (habilita "Salvar" do topo).
	const onCheckboxChange = ( key, value ) => {
		const nextBilling = key === 'billing' ? value : postcodeFirstBilling;
		const nextShipping = key === 'shipping' ? value : postcodeFirstShipping;
		if ( key === 'billing' ) setPostcodeFirstBilling( value );
		else setPostcodeFirstShipping( value );
		editPost( {
			meta: {
				...existingMeta,
				[ META_BILLING ]: nextBilling,
				[ META_SHIPPING ]: nextShipping,
			},
		} );
	};

	if ( loading ) return null;

	return createElement(
		InspectorControls,
		{ key: 'pb-autocomplete-address' },
		createElement(
			PanelBody,
			{
				title: __( 'PB Autocomplete', 'pb-autocomplete' ),
				initialOpen: true,
			},
			createElement( CheckboxControl, {
				label: __( 'Exibir CEP acima dos campos (entrega)', 'pb-autocomplete' ),
				checked: postcodeFirstShipping,
				onChange: ( val ) => onCheckboxChange( 'shipping', val ),
			} ),
			createElement( CheckboxControl, {
				label: __( 'Exibir CEP acima dos campos (cobrança)', 'pb-autocomplete' ),
				checked: postcodeFirstBilling,
				onChange: ( val ) => onCheckboxChange( 'billing', val ),
			} )
		)
	);
}

addFilter( 'editor.BlockEdit', 'pb-autocomplete/address-block-settings', ( BlockEdit ) => {
	return ( props ) => {
		if ( ! ADDRESS_BLOCKS.includes( props.name ) ) {
			return createElement( BlockEdit, props );
		}
		return createElement(
			'div',
			{ key: 'pb-autocomplete-wrap' },
			createElement( BlockEdit, props ),
			createElement( AddressBlockSettingsPanel, { blockName: props.name } )
		);
	};
} );
