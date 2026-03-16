import apiFetch from '@wordpress/api-fetch';
import { render } from '@wordpress/element';
import App from './components/App';

const { restUrl, nonce } = window.productsAdminData ?? {};

apiFetch.use( apiFetch.createNonceMiddleware( nonce ) );
apiFetch.use( apiFetch.createRootURLMiddleware( restUrl ) );

const root = document.getElementById( 'products-admin-root' );

if ( root ) {
	render( <App />, root );
}
