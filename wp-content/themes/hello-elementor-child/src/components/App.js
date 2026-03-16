import { HashRouter, Route, Routes } from 'react-router-dom';
import ProductAdd from './ProductAdd';
import ProductEdit from './ProductEdit';
import ProductList from './ProductList';

const App = () => (
	<HashRouter>
		<Routes>
			<Route path="/" element={ <ProductList /> } />
			<Route path="/add" element={ <ProductAdd /> } />
			<Route path="/edit/:id" element={ <ProductEdit /> } />
		</Routes>
	</HashRouter>
);

export default App;
