import { __ } from '@wordpress/i18n';

const App = () => {
	return (
		<div>
			<h1>{ __( 'Products', 'hello-elementor-child' ) }</h1>
			<p>{ __( 'App is working.', 'hello-elementor-child' ) }</p>
		</div>
	);
};

export default App;
