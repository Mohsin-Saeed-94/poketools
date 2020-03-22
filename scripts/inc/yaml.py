from ruamel.yaml import YAML
from ruamel.yaml.comments import CommentedBase

yaml = YAML()
yaml.default_flow_style = False
yaml.indent(mapping=2, sequence=4, offset=2)


def remove_anchors(anchored_val: CommentedBase):
    """
    Remove the anchor in the given value and any children, if applicable.
    :param anchored_val:
    """
    anchored_val.yaml_set_anchor(None)
    if isinstance(anchored_val, dict):
        it = anchored_val.values()
    elif isinstance(anchored_val, list):
        it = anchored_val
    else:
        it = None
    if it:
        for val in it:
            if isinstance(val, CommentedBase):
                remove_anchors(val)
