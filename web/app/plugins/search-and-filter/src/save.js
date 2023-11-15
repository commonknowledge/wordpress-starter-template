
import { useBlockProps } from '@wordpress/block-editor';

export default function Save({ attributes }) {
    const blockProps = useBlockProps.save();

    
   const categories = attributes.categories;
   const posts = attributes.posts;


    // Filter posts based on the selected category
    // const filteredPosts = attributes.posts.filter(
    //     (post) => !attributes.category || post.categories[0] === parseInt(attributes.category, 10)
    // );


    return (
        <div {...blockProps}>
            {typeof categories !== 'undefined' && categories.length > 0 ? (<select
             value={attributes.category}
                
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

            <ul>
                {posts.map((post) => (
                    <li key={post.id} data-category={post.categoryID}>
                        <strong>{post.title.rendered}</strong> - {post.categoryName}
                    </li>
                ))}
            </ul>
        </div>
    );
}