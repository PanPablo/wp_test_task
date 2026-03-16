import apiFetch from '@wordpress/api-fetch';
import {
	Button,
	Notice,
	SelectControl,
	TextareaControl,
	TextControl,
	ToggleControl,
} from '@wordpress/components';
import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Link, useNavigate } from 'react-router-dom';
import MediaUploadButton from './MediaUploadButton';

const EMPTY_FORM = {
	title: '',
	content: '',
	price: '',
	sale_price: '',
	is_on_sale: false,
	youtube_video: '',
	featured_media: 0,
	product_category: [],
};

const ProductForm = ( { productId = null } ) => {
	const navigate = useNavigate();

	const [ form, setForm ] = useState( EMPTY_FORM );
	const [ thumbnailUrl, setThumbnailUrl ] = useState( null );
	const [ categories, setCategories ] = useState( [] );
	const [ isLoading, setIsLoading ] = useState( !! productId );
	const [ isSaving, setIsSaving ] = useState( false );
	const [ error, setError ] = useState( null );

	// Fetch categories
	useEffect( () => {
		apiFetch( { path: '/wp/v2/product_category?per_page=100&orderby=name&order=asc' } )
			.then( ( data ) => {
				setCategories( data );
			} )
			.catch( () => {} );
	}, [] );

	// Fetch product data for edit mode
	useEffect( () => {
		if ( ! productId ) return;

		apiFetch( { path: `/wp/v2/product/${ productId }?_embed&context=edit` } )
			.then( ( product ) => {
				setForm( {
					title: product.title?.rendered ?? '',
					content: product.content?.raw ?? '',
					price: product.meta?.price ?? '',
					sale_price: product.meta?.sale_price ?? '',
					is_on_sale: product.meta?.is_on_sale ?? false,
					youtube_video: product.meta?.youtube_video ?? '',
					featured_media: product.featured_media ?? 0,
					product_category: product.product_category ?? [],
				} );

				const media = product._embedded?.[ 'wp:featuredmedia' ]?.[ 0 ];
				if ( media ) {
					setThumbnailUrl(
						media.media_details?.sizes?.medium?.source_url ?? media.source_url
					);
				}
			} )
			.catch( () => {
				setError( __( 'Failed to load product.', 'hello-elementor-child' ) );
			} )
			.finally( () => setIsLoading( false ) );
	}, [ productId ] );

	const setField = ( key, value ) =>
		setForm( ( prev ) => ( { ...prev, [ key ]: value } ) );

	const handleSubmit = ( e ) => {
		e.preventDefault();
		setIsSaving( true );
		setError( null );

		const body = {
			title: form.title,
			content: form.content,
			status: 'publish',
			featured_media: form.featured_media,
			product_category: form.product_category,
			meta: {
				price: parseFloat( form.price ) || 0,
				sale_price: parseFloat( form.sale_price ) || 0,
				is_on_sale: form.is_on_sale,
				youtube_video: form.youtube_video,
			},
		};

		const path = productId ? `/wp/v2/product/${ productId }` : '/wp/v2/product';
		const method = productId ? 'PUT' : 'POST';

		apiFetch( { path, method, data: body } )
			.then( ( saved ) => {
				navigate( '/', {
					state: {
						notice: {
							status: 'success',
							message: productId
								? `"${ saved.title.rendered }" ${ __( 'has been updated.', 'hello-elementor-child' ) }`
								: `"${ saved.title.rendered }" ${ __( 'has been created.', 'hello-elementor-child' ) }`,
						},
					},
				} );
			} )
			.catch( () => {
				setError( __( 'Failed to save product.', 'hello-elementor-child' ) );
				setIsSaving( false );
			} );
	};

	const categoryOptions = [
		{ label: __( '— No category —', 'hello-elementor-child' ), value: '' },
		...categories.map( ( cat ) => ( {
			label: cat.name,
			value: String( cat.id ),
		} ) ),
	];

	const selectedCategory = form.product_category?.[ 0 ]
		? String( form.product_category[ 0 ] )
		: '';

	if ( isLoading ) {
		return <p>{ __( 'Loading…', 'hello-elementor-child' ) }</p>;
	}

	return (
		<div className="products-manager wrap">
			<h1>
				{ productId
					? __( 'Edit Product', 'hello-elementor-child' )
					: __( 'Add New Product', 'hello-elementor-child' ) }
			</h1>

			{ error && (
				<Notice status="error" onRemove={ () => setError( null ) }>
					{ error }
				</Notice>
			) }

			<form onSubmit={ handleSubmit }>
				<table className="form-table">
					<tbody>

						{ /* Main image */ }
						<tr>
							<th scope="row">
								<label>{ __( 'Main Image', 'hello-elementor-child' ) }</label>
							</th>
							<td>
								<MediaUploadButton
									value={ form.featured_media }
									previewUrl={ thumbnailUrl }
									onSelect={ ( media ) => {
										setField( 'featured_media', media.id );
										setThumbnailUrl( media.url );
									} }
									onRemove={ () => {
										setField( 'featured_media', 0 );
										setThumbnailUrl( null );
									} }
								/>
							</td>
						</tr>

						{ /* Title */ }
						<tr>
							<th scope="row">
								<label>{ __( 'Title', 'hello-elementor-child' ) }</label>
							</th>
							<td>
								<TextControl
									value={ form.title }
									onChange={ ( val ) => setField( 'title', val ) }
									required
								/>
							</td>
						</tr>

						{ /* Description */ }
						<tr>
							<th scope="row">
								<label>{ __( 'Description', 'hello-elementor-child' ) }</label>
							</th>
							<td>
								<TextareaControl
									value={ form.content }
									onChange={ ( val ) => setField( 'content', val ) }
									rows={ 6 }
								/>
							</td>
						</tr>

						{ /* Price */ }
						<tr>
							<th scope="row">
								<label>{ __( 'Price', 'hello-elementor-child' ) }</label>
							</th>
							<td>
								<TextControl
									type="number"
									min="0"
									step="0.01"
									value={ form.price }
									onChange={ ( val ) => setField( 'price', val ) }
								/>
							</td>
						</tr>

						{ /* Sale price */ }
						<tr>
							<th scope="row">
								<label>{ __( 'Sale Price', 'hello-elementor-child' ) }</label>
							</th>
							<td>
								<TextControl
									type="number"
									min="0"
									step="0.01"
									value={ form.sale_price }
									onChange={ ( val ) => setField( 'sale_price', val ) }
								/>
							</td>
						</tr>

						{ /* Is on sale */ }
						<tr>
							<th scope="row">
								<label>{ __( 'Is On Sale?', 'hello-elementor-child' ) }</label>
							</th>
							<td>
								<ToggleControl
									checked={ form.is_on_sale }
									onChange={ ( val ) => setField( 'is_on_sale', val ) }
								/>
							</td>
						</tr>

						{ /* YouTube video */ }
						<tr>
							<th scope="row">
								<label>{ __( 'YouTube Video URL', 'hello-elementor-child' ) }</label>
							</th>
							<td>
								<TextControl
									type="url"
									placeholder="https://www.youtube.com/watch?v=..."
									value={ form.youtube_video }
									onChange={ ( val ) => setField( 'youtube_video', val ) }
								/>
							</td>
						</tr>

						{ /* Category */ }
						<tr>
							<th scope="row">
								<label>{ __( 'Category', 'hello-elementor-child' ) }</label>
							</th>
							<td>
								<SelectControl
									value={ selectedCategory }
									options={ categoryOptions }
									onChange={ ( val ) =>
										setField(
											'product_category',
											val ? [ parseInt( val, 10 ) ] : []
										)
									}
								/>
							</td>
						</tr>

					</tbody>
				</table>

				<div className="products-manager__actions" style={ { marginTop: '24px' } }>
					<Button
						variant="primary"
						type="submit"
						isBusy={ isSaving }
						disabled={ isSaving }
					>
						{ isSaving
							? __( 'Saving…', 'hello-elementor-child' )
							: __( 'Save Product', 'hello-elementor-child' ) }
					</Button>
					{ ' ' }
					<Link to="/">
						<Button variant="tertiary" disabled={ isSaving }>
							{ __( 'Cancel', 'hello-elementor-child' ) }
						</Button>
					</Link>
				</div>
			</form>
		</div>
	);
};

export default ProductForm;
