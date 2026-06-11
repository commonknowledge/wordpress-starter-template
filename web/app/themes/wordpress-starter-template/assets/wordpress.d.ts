/*
 * @wordpress/block-editor does not ship TypeScript types, so the parts of its
 * API used by this theme are declared here. Extend as you use more of it.
 */
declare module "@wordpress/block-editor" {
  import type { HTMLAttributes } from "react";

  export function useBlockProps(
    props?: HTMLAttributes<HTMLElement>,
  ): HTMLAttributes<HTMLElement>;
}
