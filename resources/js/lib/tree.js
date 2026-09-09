export function findNodeById(nodes, id) {
    for (const node of nodes) {
        if (node.id === id) return node;
        if (node.children) {
            const found = findNodeById(node.children, id);
            if (found) return found;
        }
    }
    return null;
}

export function removeNode(nodes, id) {
    const index = nodes.findIndex((node) => node.id === id);

    if (index !== -1) {
        nodes.splice(index, 1);
        return true;
    }

    return nodes.some((node) => node.children && removeNode(node.children, id));
}

export function containingArray(nodes, id) {
    if (nodes.some((node) => node.id === id)) return nodes;

    for (const node of nodes) {
        if (node.children) {
            const found = containingArray(node.children, id);
            if (found) return found;
        }
    }

    return null;
}

export function reassignIds(node) {
    return {
        ...node,
        id: crypto.randomUUID(),
        children: node.children ? node.children.map(reassignIds) : undefined,
    };
}

export function insertNode(nodes, node, parentId = null) {
    if (! parentId) {
        nodes.push(node);
        return true;
    }

    const parent = findNodeById(nodes, parentId);

    if (! parent) return false;

    parent.children = parent.children || [];
    parent.children.push(node);

    return true;
}
