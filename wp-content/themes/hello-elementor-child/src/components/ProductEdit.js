import { useParams } from 'react-router-dom';
import ProductForm from './ProductForm';

const ProductEdit = () => {
	const { id } = useParams();
	return <ProductForm productId={ parseInt( id, 10 ) } />;
};

export default ProductEdit;
