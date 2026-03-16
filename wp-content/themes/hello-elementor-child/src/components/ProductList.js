import apiFetch from '@wordpress/api-fetch';
import { Button, Notice, Spinner } from '@wordpress/components';
import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Link } from 'react-router-dom';

const ProductList = () => {
	const [ products, setProducts ] = useState( [] );
	const [ isLoading, setIsLoading ] = useState( true );
	const [ error, setError ] = useState( null );
	const [ deletingId, setDeletingId ] = useState( null );
	const [ notice, setNotice ] = useState( null );

	const fetchProducts = () => {
		setIsLoading( true );
		setError( null );

		apiFetch( { path: '/wp/v2/product?_embed&per_page=100&orderby=date&order=desc' } )
			.then( ( data ) => {
				setProducts( data );
			} )
			.catch( () => {
				setError( __( 'Failed to load products.', 'hello-elementor-child' ) );
			} )
			.finally( () => {
				setIsLoading( false );
			} );
	};

	useEffect( () => {
		fetchProducts();
	}, [] );

	const handleDelete = ( product ) => {
		if (
			! window.confirm(
				`${ __( 'Are you sure you want to delete', 'hello-elementor-child' ) } "${ product.title.rendered }"?`
			)
		) {
			return;
		}

		setDeletingId( product.id );

		apiFetch( {
			path: `/wp/v2/product/${ product.id }?force=true`,
			method: 'DELETE',
		} )
			.then( () => {
				setProducts( ( prev ) => prev.filter( ( p ) => p.id !== product.id ) );
				setNotice( {
					status: 'success',
					message: `"${ product.title.rendered }" ${ __( 'has been deleted.', 'hello-elementor-child' ) }`,
				} );
			} )
			.catch( () => {
				setNotice( {
					status: 'error',
					message: __( 'Failed to delete product.', 'hello-elementor-child' ),
				} );
			} )
			.finally( () => {
				setDeletingId( null );
			} );
	};

	const getThumbnail = ( product ) => {
		return product._embedded?.[ 'wp:featuredmedia' ]?.[ 0 ]?.media_details?.sizes?.thumbnail?.source_url
			?? product._embedded?.[ 'wp:featuredmedia' ]?.[ 0 ]?.source_url
			?? null;
	};

	return (
		<div className="products-manager wrap">
			<h1 className="wp-heading-inline">
				{ __( 'Products', 'hello-elementor-child' ) }
			</h1>

			<Link to="/add">
				<Button variant="primary" className="page-title-action">
					{ __( 'Add New Product', 'hello-elementor-child' ) }
				</Button>
			</Link>

			<hr className="wp-header-end" />

			{ notice && (
				<Notice
					status={ notice.status }
					onRemove={ () => setNotice( null ) }
				>
					{ notice.message }
				</Notice>
			) }

			{ isLoading && (
				<div className="products-manager__spinner">
					<Spinner />
				</div>
			) }

			{ error && (
				<Notice status="error" isDismissible={ false }>
					{ error }
				</Notice>
			) }

			{ ! isLoading && ! error && (
				<table className="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th style={ { width: '80px' } }>
								{ __( 'Thumbnail', 'hello-elementor-child' ) }
							</th>
							<th>{ __( 'Name', 'hello-elementor-child' ) }</th>
							<th style={ { width: '160px' } }>
								{ __( 'Actions', 'hello-elementor-child' ) }
							</th>
						</tr>
					</thead>
					<tbody>
						{ products.length === 0 && (
							<tr>
								<td colSpan={ 3 }>
									{ __( 'No products found.', 'hello-elementor-child' ) }
								</td>
							</tr>
						) }
						{ products.map( ( product ) => {
							const thumbnail = getThumbnail( product );

							return (
								<tr key={ product.id }>
									<td>
										{ thumbnail ? (
											<img
												src={ thumbnail }
												alt={ product.title.rendered }
												style={ { width: '60px', height: '60px', objectFit: 'cover' } }
											/>
										) : (
											<span className="products-manager__no-thumb">—</span>
										) }
									</td>
									<td>
										<strong>{ product.title.rendered }</strong>
									</td>
									<td>
										<Link to={ `/edit/${ product.id }` }>
											<Button variant="secondary" size="small">
												{ __( 'Edit', 'hello-elementor-child' ) }
											</Button>
										</Link>
										{ ' ' }
										<Button
											variant="tertiary"
											size="small"
											isDestructive
											isBusy={ deletingId === product.id }
											disabled={ deletingId === product.id }
											onClick={ () => handleDelete( product ) }
										>
											{ __( 'Delete', 'hello-elementor-child' ) }
										</Button>
									</td>
								</tr>
							);
						} ) }
					</tbody>
				</table>
			) }
		</div>
	);
};

export default ProductList;
