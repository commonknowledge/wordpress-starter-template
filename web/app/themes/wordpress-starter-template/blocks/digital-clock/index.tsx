import { useBlockProps } from "@wordpress/block-editor";
import { registerBlockType } from "@wordpress/blocks";
import { useEffect, useState } from "react";

import metadata from "./block.json";
import "./style.css";
import { formatTime } from "./time";

function Edit() {
  const blockProps = useBlockProps();
  const [time, setTime] = useState(() => formatTime(new Date()));

  useEffect(() => {
    const timer = window.setInterval(() => {
      setTime(formatTime(new Date()));
    }, 1000);
    return () => window.clearInterval(timer);
  }, []);

  return (
    <div {...blockProps}>
      <time>{time}</time>
    </div>
  );
}

registerBlockType(metadata, {
  edit: Edit,
  // The front-end markup comes from render.php.
  save: () => null,
});
