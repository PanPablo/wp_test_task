import { render } from '@wordpress/element';
import App from './components/App';

const root = document.getElementById( 'products-admin-root' );

if ( root ) {
	render( <App />, root );
}
