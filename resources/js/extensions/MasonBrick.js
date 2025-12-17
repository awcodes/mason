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
            insertAboveBrickButtonIconHtml: null,
            insertBelowBrickButtonIconHtml: null,
            moveBrickUpButtonIconHtml: null,
            moveBrickDownButtonIconHtml: null,
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
                tag: `div[data-type="brick"]`,
            },
        ];
    },

    renderHTML({ HTMLAttributes }) {
        return ['div', mergeAttributes(HTMLAttributes)]
    },

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

            if (editor.isEditable) {
                const insertAboveButtonContainer = document.createElement('div')
                insertAboveButtonContainer.className = 'mason-insert-brick-btn'
                brickControls.appendChild(insertAboveButtonContainer)

                const insertAboveButton = document.createElement('button')
                insertAboveButton.className = 'mason-icon-btn'
                insertAboveButton.type = 'button'
                insertAboveButton.title = 'Insert Brick Above'
                insertAboveButton.innerHTML = extension.options.insertAboveBrickButtonIconHtml
                insertAboveButton.addEventListener('click', () =>
                    extension.options.insertBrickUsing(
                        node.attrs.id,
                        getPos(),
                    ),
                )
                insertAboveButtonContainer.appendChild(insertAboveButton)

                const insertBelowButtonContainer = document.createElement('div')
                insertBelowButtonContainer.className = 'mason-insert-brick-btn'
                brickControls.appendChild(insertBelowButtonContainer)

                const insertBelowButton = document.createElement('button')
                insertBelowButton.className = 'mason-icon-btn'
                insertBelowButton.type = 'button'
                insertBelowButton.title = 'Insert Brick Below'
                insertBelowButton.innerHTML = extension.options.insertBelowBrickButtonIconHtml
                insertBelowButton.addEventListener('click', () =>
                    extension.options.insertBrickUsing(
                        node.attrs.id,
                        getPos() + 1,
                    ),
                )
                insertBelowButtonContainer.appendChild(insertBelowButton)

                const moveUpButtonContainer = document.createElement('div')
                moveUpButtonContainer.className = 'mason-move-up-brick-btn'
                brickControls.appendChild(moveUpButtonContainer)

                const moveUpButton = document.createElement('button')
                moveUpButton.className = 'mason-icon-btn'
                moveUpButton.type = 'button'
                moveUpButton.title = 'Move Brick Up'
                moveUpButton.innerHTML = extension.options.moveBrickUpButtonIconHtml
                moveUpButton.addEventListener('click', () => {
                    const currentPos = getPos()
                    if (currentPos === 0) {
                        return
                    }
                    const transaction = editor.state.tr
                    const nodeToMove = editor.state.doc.nodeAt(currentPos)
                    transaction.delete(currentPos, currentPos + nodeToMove.nodeSize)
                    transaction.insert(currentPos - 1, nodeToMove)
                    editor.view.dispatch(transaction)
                })
                moveUpButtonContainer.appendChild(moveUpButton)

                const moveDownButtonContainer = document.createElement('div')
                moveDownButtonContainer.className = 'mason-move-down-brick-btn'
                brickControls.appendChild(moveDownButtonContainer)

                const moveDownButton = document.createElement('button')
                moveDownButton.className = 'mason-icon-btn'
                moveDownButton.type = 'button'
                moveDownButton.title = 'Move Brick Down'
                moveDownButton.innerHTML = extension.options.moveBrickDownButtonIconHtml
                moveDownButton.addEventListener('click', () => {
                    const currentPos = getPos()
                    const nodeToMove = editor.state.doc.nodeAt(currentPos)
                    if (currentPos + nodeToMove.nodeSize >= editor.state.doc.content.size) {
                        return
                    }
                    const transaction = editor.state.tr
                    transaction.delete(currentPos, currentPos + nodeToMove.nodeSize)
                    transaction.insert(currentPos + 1, nodeToMove)
                    editor.view.dispatch(transaction)
                })
                moveDownButtonContainer.appendChild(moveDownButton)
            }

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
                editButton.title = 'Edit Brick'
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
                deleteButton.title = 'Delete Brick'
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

                if (
                    editor.isEditable &&
                    typeof node.attrs.config === 'object' &&
                    node.attrs.config !== null &&
                    Object.keys(node.attrs.config).length > 0
                ) {
                    preview.addEventListener('dblclick', () =>
                        extension.options.editBrickUsing(
                            node.attrs.id,
                            node.attrs.config,
                        ),
                    )
                }

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

                        const customBrickId = event.dataTransfer.getData('brick')

                        let position = view.posAtCoords({
                            left: event.clientX,
                            top: event.clientY,
                        }).pos

                        if (view.state.doc.content.content[0].type.name === 'paragraph') {
                            position = 1
                        }

                        insertBrickUsing(
                            customBrickId,
                            position,
                        )

                        return false
                    },
                },
            }),
        ]
    },
})
