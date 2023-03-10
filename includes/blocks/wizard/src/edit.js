import { useBlockProps } from "@wordpress/block-editor";
import apiFetch from "@wordpress/api-fetch";
import { useState, useEffect } from "@wordpress/element";

const Edit = (props) => {
  const { attributes, setAttributes, context } = props;
  const blockProps = useBlockProps();
  const [posts, setPosts] = useState([]);
  const { postId } = context;

  const fetchPosts = async () => {
    let path = "/close/v1/subservicios/" + postId;
    const newPosts = await apiFetch({ path });
    setPosts(newPosts);
  };

  useEffect(() => {
    fetchPosts();
  }, []);

  return (
    <>
      {posts.length > 0 && (
			<div {...blockProps}>
				{posts.map((post) => {
					return (
						<div key={post.id} className="item-subservicios" style={{ 
							backgroundImage: `url("${post.image}")`
						 }}>
							<a className="link" href={post.link}></a>
							<h2 className="title-subservicios gb-headline">{post.title}</h2>
						</div>
					);
				})}
			</div>
      )}
    </>
  );
};

export default Edit;
