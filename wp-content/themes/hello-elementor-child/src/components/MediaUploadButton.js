import { Button } from '@wordpress/components';
import { useRef } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

const MediaUploadButton = ( { value, previewUrl, onSelect, onRemove } ) => {
	const frameRef = useRef( null );

	const openMediaLibrary = () => {
		if ( ! frameRef.current ) {
			frameRef.current = window.wp.media( {
				title: __( 'Select Image', 'hello-elementor-child' ),
				button: { text: __( 'Use this image', 'hello-elementor-child' ) },
				library: { type: 'image' },
				multiple: false,
			} );

			frameRef.current.on( 'select', () => {
				const attachment = frameRef.current
					.state()
					.get( 'selection' )
					.first()
					.toJSON();

				onSelect( {
					id: attachment.id,
					url: attachment.sizes?.medium?.url ?? attachment.url,
				} );
			} );
		}

		frameRef.current.open();
	};

	return (
		<div className="products-manager__media">
			{ previewUrl && (
				<img
					src={ previewUrl }
					alt=""
					style={ {
						display: 'block',
						maxWidth: '200px',
						marginBottom: '8px',
						borderRadius: '4px',
					} }
				/>
			) }

			<Button variant="secondary" onClick={ openMediaLibrary }>
				{ value
					? __( 'Change Image', 'hello-elementor-child' )
					: __( 'Select Image', 'hello-elementor-child' ) }
			</Button>

			{ value > 0 && (
				<Button
					variant="tertiary"
					isDestructive
					style={ { marginLeft: '8px' } }
					onClick={ onRemove }
				>
					{ __( 'Remove', 'hello-elementor-child' ) }
				</Button>
			) }
		</div>
	);
};

export default MediaUploadButton;
