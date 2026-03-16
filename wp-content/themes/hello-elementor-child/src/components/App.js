import { HashRouter, Route, Routes } from 'react-router-dom';
import ProductList from './ProductList';

const App = () => (
	<HashRouter>
		<Routes>
			<Route path="/" element={ <ProductList /> } />
			<Route path="/add" element={ <div>Add screen — coming in step 5</div> } />
			<Route path="/edit/:id" element={ <div>Edit screen — coming in step 5</div> } />
		</Routes>
	</HashRouter>
);

export default App;
