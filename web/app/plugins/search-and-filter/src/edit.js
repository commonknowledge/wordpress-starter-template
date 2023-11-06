import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';
import './editor.scss';
import { useState, useEffect } from 'react';



async function fetchCategories() {
	const response = await fetch('/wp-json/wp/v2/categories');
	const categories = await response.json();
	return categories;
 }

 async function fetchPosts() {
	const response = await fetch('/wp-json/wp/v2/posts');
	const posts = await response.json();
	return posts;
 }


 export default function Edit( { attributes, setAttributes } ) {
	const [categories, setCategories] = useState([]);
	const [posts, setPosts] = useState([]);
 
	useEffect(() => {
		fetchCategories().then(fetchedCategories => {
		   setCategories(fetchedCategories);
		   setAttributes({ categories: fetchedCategories });
		});
	 }, [setAttributes]);

	 useEffect(() => {
		fetchPosts().then(fetchedPosts => {
		   setCategories(fetchedPosts);
		   setAttributes({ posts: fetchedPosts });
		});
	 }, [setAttributes]);
 
	return (
		<div {...useBlockProps()}>
	
	  </div>
	);
}
