import { useState, useEffect } from 'react';
import { useBlockProps } from '@wordpress/block-editor';
import apiFetch from '@wordpress/api-fetch';


export default function Edit({ attributes, setAttributes }) {
	const blockProps = useBlockProps();
	const [categories, setCategories] = useState([]);
	const [posts, setPosts] = useState([]);

	useEffect(() => {
		// Fetch categories
		apiFetch({ path: '/wp/v2/categories' }).then((categories) => {
			setCategories(categories);
			setAttributes({ categories: categories }); // Save categories to block's attributes
		});
	}, [attributes.category]);

	useEffect(() => {
		const fetchPosts = async () => {
			try {
				// Fetch all posts
				const posts = await apiFetch({ path: '/wp/v2/posts' });

				// Extract category IDs from posts
				const categoryIds = posts.map((post) => post.categories[0]);

				// Fetch category details for each category ID
				const categoriesData = await Promise.all(
					categoryIds.map((categoryId) =>
						apiFetch({ path: `/wp/v2/categories/${categoryId}` })
					)
				);

				// Combine posts with category names
				const postsWithCategories = posts.map((post) => {
					const category = categoriesData.find((cat) => cat.id === post.categories[0]);
					return { ...post, categoryName: category.name, categoryID: category.id  };
				});

				setPosts(postsWithCategories);
				setAttributes({ posts: postsWithCategories }); // Save posts to block's attributes
			} catch (error) {
				console.error('Error fetching posts:', error);
			}
		};

		fetchPosts();
	}, [attributes.posts]);

	// Filter posts based on the selected category
	const filteredPosts = posts.filter(
		(post) => !attributes.category || post.categories[0] === parseInt(attributes.category, 10)
	);

	return (
		<div {...blockProps}>
			{typeof categories !== 'undefined' && categories.length > 0 ? (<select
				value={attributes.category}
				onChange={(e) => setAttributes({ category: e.target.value })}
			>
				<option value="">Select a category</option>
				{categories.map((category) => (
					<option key={category.id} value={category.id}>
						{category.name}
					</option>
				))}
			</select>
			) : (
				<p>Loading categories...</p>
			)}

			{/* Display a list of posts based on the selected category */}
			<ul>
				{filteredPosts.map((post) => (
					<li key={post.id}>
						<strong>{post.title.rendered}</strong> - {post.categoryName}
					</li>
				))}
			</ul>
		</div>
	);
}