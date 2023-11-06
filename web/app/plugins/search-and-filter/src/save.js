import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';

export default function save({ attributes }) {

    // Destructure the 'categories' attribute and check if it's an array
    const categories = attributes.categories && Array.isArray(attributes.categories)
        ? attributes.categories
        : [];
    const posts = attributes.posts && Array.isArray(attributes.posts)
        ? attributes.posts
        : [];


    // If the category details are available, display the category name
    return (
        <>
            <p {...useBlockProps.save()}></p>
            <label htmlFor="categories">Choose a category:</label>
            <select name="categories" id="categories">
                {categories.map((category, index) => (
                    <option key={index} value={category.name}>
                        {category.name}
                    </option>
                ))}
            </select>

            <ul>
                {posts.map((post, index) => (
                    <li key={index}>
                        {post.slug}</li>

                ))}</ul>
        </>
    );
}