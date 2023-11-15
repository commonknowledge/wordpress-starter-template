import { registerBlockType } from '@wordpress/blocks';
import Edit from './edit';
import Save from './save';

registerBlockType('search-and-filter/search-and-filter', {
	title: 'Search and Filter',
	icon: 'filter',
	category: 'widgets',
	edit: Edit,
	save: Save,
	attributes: {
		posts: {
			type: 'array',
			default: []
		},
		category: {
			type: 'string',
			default: ''
		}
	},
});