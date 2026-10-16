import "./style.css";
import { RichText, useBlockProps } from "@wordpress/block-editor";
import {
  type BlockConfiguration,
  type BlockEditProps,
  registerBlockType,
} from "@wordpress/blocks";
import metadata from "./block.json";

// A type rather than an interface, as @wordpress/blocks requires attributes
// to satisfy Record<string, unknown>
// eslint-disable-next-line @typescript-eslint/consistent-type-definitions
type Attributes = {
  heading: string;
};

function Edit({ attributes, setAttributes }: BlockEditProps<Attributes>) {
  const blockProps = useBlockProps();

  return (
    <div {...blockProps}>
      <RichText
        tagName="h2"
        value={attributes.heading}
        onChange={(heading: string) => setAttributes({ heading })}
        allowedFormats={[]}
        placeholder="Heading…"
      />
    </div>
  );
}

// The block is rendered on the server by render.php, so there is no save()
registerBlockType<Attributes>(metadata as BlockConfiguration<Attributes>, {
  edit: Edit,
});
