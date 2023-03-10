import { registerBlockType } from "@wordpress/blocks";

import meta from "../block.json";
import edit from "./edit";
import "./style.scss";

registerBlockType(meta, {
  edit,
  save: () => null,
});
