import { Node, mergeAttributes } from '@tiptap/core'
import { Plugin } from '@tiptap/pm/state'

export default Node.create({
    name: 'masonBrick',

    group: 'block',

    atom: true,

    isBlock: true,

    inline: false,

    draggable: true,

    defining: true,

    selectable: true,

    isolating: true,

    addOptions() {
        return {
            deleteBrickButtonIconHtml: null,
            editBrickButtonIconHtml: null,
            editBrickUsing: () => {},
            insertBrickUsing: () => {},
        }
    },

    addAttributes() {
        return {
            config: {
                default: null,
                parseHTML: (element) => JSON.parse(element.getAttribute('data-config')),
            },

            id: {
                default: null,
                parseHTML: (element) => element.getAttribute('data-id'),
                renderHTML: (attributes) => {
                    if (!attributes.id) {
                        return {}
                    }

                    return {
                        'data-id': attributes.id,
                    }
                }
            },

            label: {
                default: null,
                parseHTML: (element) => element.getAttribute('data-label'),
                rendered: false,
            },

            preview: {
                default: null,
                parseHTML: (element) => element.getAttribute('data-preview'),
                rendered: false,
            },
        };
    },

    parseHTML() {
        return [
            {
                tag: `div[data-type="${this.name}"]`,
            },
        ];
    },

    renderHTML({ HTMLAttributes }) {
        return ['div', mergeAttributes(HTMLAttributes)]
    },

    // addCommands() {
    //     return {
    //         setBrick: (attributes) => {
    //             return ({ chain, state, tr }) => {
    //                 const currentChain = chain()
    //                 const { selection } = state
    //                 const { $from, $to } = selection
    //
    //                 if ($to.nodeBefore && $to.nodeBefore.type.name === 'paragraph') {
    //                     return currentChain
    //                         .insertContentAt(0, { type: this.name, attrs: attributes })
    //                         .setNodeSelection(1)
    //                         .deleteSelection()
    //                 }
    //
    //                 let insertAt = $from.pos
    //
    //                 if (tr.getMeta('isUpdatingBrick')) {
    //                     insertAt = {from: $from.pos, to: $to.pos}
    //                 }
    //
    //                 if (tr.getMeta('isInsertingBrick')) {
    //                     insertAt = tr.getMeta('isInsertingBrickPosition') === 'before'
    //                         ? $from.pos
    //                         : $to.pos
    //                 }
    //
    //                 return currentChain
    //                     .insertContentAt(insertAt, { type: this.name, attrs: attributes })
    //
    //             }
    //         }
    //     }
    // },

    addNodeView() {
        return ({
            editor,
            node,
            getPos,
            extension,
        }) => {
            const brick = document.createElement('div')
            brick.setAttribute('data-config', node.attrs.config)
            brick.setAttribute('data-id', node.attrs.id)
            brick.setAttribute('data-type', 'brick')

            const brickWrapper = document.createElement('div')
            brickWrapper.className = 'mason-brick-wrapper'
            brick.appendChild(brickWrapper)

            const brickControls = document.createElement('div')
            brickControls.className = 'mason-brick-controls'
            brickWrapper.appendChild(brickControls)

            if (
                editor.isEditable &&
                typeof node.attrs.config === 'object' &&
                node.attrs.config !== null &&
                Object.keys(node.attrs.config).length > 0
            ) {
                const editButtonContainer = document.createElement('div')
                editButtonContainer.className = 'mason-edit-brick-btn'
                brickControls.appendChild(editButtonContainer)

                const editButton = document.createElement('button')
                editButton.className = 'mason-icon-btn'
                editButton.type = 'button'
                editButton.innerHTML = extension.options.editBrickButtonIconHtml
                editButton.addEventListener('click', () =>
                    extension.options.editBrickUsing(
                        node.attrs.id,
                        node.attrs.config,
                    ),
                )
                editButtonContainer.appendChild(editButton)
            }

            if (editor.isEditable) {
                const deleteButtonContainer = document.createElement('div')
                deleteButtonContainer.className = 'mason-delete-brick-btn'
                brickControls.appendChild(deleteButtonContainer)

                const deleteButton = document.createElement('button')
                deleteButton.className = 'mason-icon-btn'
                deleteButton.type = 'button'
                deleteButton.innerHTML = extension.options.deleteBrickButtonIconHtml
                deleteButton.addEventListener('click', () =>
                    editor
                        .chain()
                        .setNodeSelection(getPos())
                        .deleteSelection()
                        .run(),
                )
                deleteButtonContainer.appendChild(deleteButton)
            }

            /* TODO: Re-implement insert, move up, move down. */

            if (node.attrs.preview) {
                const preview = document.createElement('div')
                preview.className = 'mason-brick-rendered'
                preview.innerHTML = new TextDecoder().decode(
                    Uint8Array.from(atob(node.attrs.preview), (char) =>
                        char.charCodeAt(0),
                    ),
                )

                brick.appendChild(preview)
            }

            return {
                dom: brick,
            }
        }
    },

    addProseMirrorPlugins() {
        const { insertBrickUsing } = this.options

        return [
            new Plugin({
                props: {
                    handleDrop(view, event) {
                        if (!event) {
                            return false
                        }

                        event.preventDefault()

                        if (!event.dataTransfer.getData('brick')) {
                            return false
                        }

                        const customBrickId =
                            event.dataTransfer.getData('brick')

                        insertBrickUsing(
                            customBrickId,
                            view.posAtCoords({
                                left: event.clientX,
                                top: event.clientY,
                            }).pos,
                        )

                        return false
                    },
                },
            }),
        ]
    },
})
