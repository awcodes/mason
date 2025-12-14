import { Dropcursor } from '@tiptap/extension-dropcursor'
import { Document } from '@tiptap/extension-document'
import { Editor } from '@tiptap/core'
import { History } from '@tiptap/extension-history'
import { Paragraph } from '@tiptap/extension-paragraph'
import { Placeholder } from '@tiptap/extension-placeholder'
import { Text } from '@tiptap/extension-text'
import MasonBrick from './extensions/MasonBrick'
import Customizations from './extensions/Customizations'
import StatePath from './extensions/StatePath'
import { Selection } from '@tiptap/pm/state'

document.addEventListener('livewire:init', () => {
    const findClosestLivewireComponent = (el) => {
        let closestRoot = Alpine.findClosest(el, (i) => i.__livewire)

        if (!closestRoot) {
            throw 'Could not find Livewire component in DOM tree'
        }

        return closestRoot.__livewire
    }

    Livewire.hook('commit', ({ component, commit, respond, succeed, fail }) => {
        succeed(({ snapshot, effects }) => {
            effects.dispatches?.forEach((dispatch) => {
                if (!dispatch.params?.awaitMasonComponent) {
                    return
                }

                let els = Array.from(
                    component.el.querySelectorAll(
                        `[wire\\:partial="mason-component::${dispatch.params.awaitMasonComponent}"]`,
                    ),
                ).filter((el) => findClosestLivewireComponent(el) === component)

                if (els.length === 1) {
                    return
                }

                if (els.length > 1) {
                    throw `Multiple mason components found with key [${dispatch.params.awaitMasonComponent}].`
                }

                window.addEventListener(
                    `mason-component-${component.id}-${dispatch.params.awaitMasonComponent}-loaded`,
                    () => {
                        window.dispatchEvent(
                            new CustomEvent(dispatch.name, {
                                detail: dispatch.params,
                            }),
                        )
                    },
                    { once: true },
                )
            })
        })
    })
})

export default function masonComponent({
    key,
    livewireId,
    state,
    statePath,
    placeholder = null,
}) {
    let editor = null;

    return {
        editorUpdatedAt: Date.now(),
        state: state,
        statePath: statePath,
        fullscreen: false,
        viewport: 'desktop',
        isFocused: false,
        sidebarOpen: true,
        shouldUpdateState: true,
        isUpdatingBrick: false,
        isInsertingBrick: false,
        isInsertingBrickPosition: null,
        editorSelection: { type: 'text', anchor: 0, head: 1 },
        init: function () {

            if (this.state?.content?.length > 0) {
                const renderer = document.querySelector('#mason-brick-renderer').getAttribute('wire:id')

                this.state.content.forEach(async (node) => {
                    node.attrs.view = await window.Livewire
                        .find(renderer)
                        .call('getView', node.attrs.path, node.attrs.values)
                        .then(e => {
                            return e
                        })
                })
            }

            editor = new Editor({
                element: this.$refs.editor,
                extensions: this.getExtensions(),
                content: this.state ?? null,
                editorProps: {
                    handlePaste(view, event, slice) {
                        slice.content.descendants(node => {
                            if (node.type.name === 'masonBrick') {
                                const parser = new DOMParser()
                                const doc = parser.parseFromString(node.attrs.view, 'text/html')
                                node.attrs.view = doc.documentElement.textContent

                                for (const key in node.attrs.values) {
                                    if (
                                        typeof node.attrs.values[key] === 'string'
                                        &&  /&amp;|&lt;|&gt;|&quot;|&#039;/.test(node.attrs.values[key])
                                    ) {
                                        node.attrs.values[key] = (() => {
                                            const value = parser.parseFromString(node.attrs.values[key], 'text/html')
                                            return value.documentElement.textContent
                                        })()
                                    }
                                }
                            }
                        });
                    },
                    handleKeyDown: (view, event) => {
                        if (event.key === 'Backspace') {
                            return false;
                        }

                        if (view.state.selection.$head.parent.type.name === 'doc') {
                            if (event.key === ' ') {
                                event.preventDefault()
                                return true;
                            }
                        }

                        if (view.state.selection.$head.parent.type.name === 'paragraph') {
                            const modifiers = {
                                alt: event.altKey,
                                shift: event.shiftKey,
                                ctrl: event.ctrlKey,
                                meta: event.metaKey,
                            }

                            if (Object.values(modifiers).every((mod) => !mod)) {
                                event.preventDefault()
                                return true;
                            }
                        }

                        return false;
                    }
                }
            })

            editor.on('create', ({ editor }) => {
                this.editorUpdatedAt = Date.now()
            })

            editor.on('update', ({ editor }) => {
                this.editorUpdatedAt = Date.now()

                this.state = editor.getJSON()

                this.shouldUpdateState = false

                // this.scrollToCurrentBrick()
            })

            editor.on('selectionUpdate', ({ editor, transaction }) => {
                this.editorUpdatedAt = Date.now()
                this.editorSelection = transaction.selection.toJSON()
            })

            editor.on('focus', ({ editor }) => {
                this.isFocused = true
                this.editorUpdatedAt = Date.now()
            })

            editor.on('drop', ({editor, event}) => {
                if (event.dataTransfer.getData('brickIdentifier')) {
                    event.preventDefault()

                    const coordinates = editor.view.posAtCoords({
                        left: event.clientX,
                        top: event.clientY,
                    })

                    if (editor.isEmpty) {
                        coordinates.pos = 0
                    }

                    event.target.dispatchEvent(new CustomEvent('dragged-brick', {
                        detail: {
                            name: event.dataTransfer.getData('brickIdentifier'),
                            coordinates,
                        },
                        bubbles: true,
                    }))
                    return false
                }

                return false;
            })

            this.$watch('isFocused', (value) => {
                if (value === false) {
                    this.blurEditor()
                }
            })

            this.$watch('state', () => {
                if (! this.shouldUpdateState) {
                    this.shouldUpdateState = true

                    return
                }

                if (this.state === undefined) {
                    return
                }

                editor.chain().setContent(this.state).setNodeSelection(this.editorSelection.anchor).run()
            });

            window.addEventListener('run-mason-commands', (event) => {
                if (event.detail.livewireId !== livewireId) {
                    return
                }

                if (event.detail.key !== key) {
                    return
                }

                this.runEditorCommands(event.detail)
            })

            window.dispatchEvent(
                new CustomEvent(`mason-component-${livewireId}-${key}-loaded`),
            )
        },
        getEditor: function () {
            return editor;
        },
        getExtensions: function () {
            const coreExtensions = [
                Document.configure({
                    content: '(inline|block)+'
                }),
                MasonBrick,
                Customizations,
                Dropcursor.configure({
                    color: 'var(--mason-primary)',
                    width: 4,
                    class: 'mason-drop-cursor',
                }),
                History,
                StatePath.configure({
                    statePath: statePath
                }),
                Text,
                Paragraph,
            ];

            if (placeholder) {
                coreExtensions.push(Placeholder.configure({placeholder: placeholder}))
            }

            return coreExtensions;
        },
        toggleFullscreen: function () {
            this.fullscreen = !this.fullscreen

            editor.commands.focus()

            if (! this.fullscreen) {
                this.viewport = 'desktop'
            }

            this.editorUpdatedAt = Date.now()
        },
        toggleViewport: function (viewport) {
            this.viewport = viewport

            this.editorUpdatedAt = Date.now()
        },
        toggleSidebar: function () {
            this.sidebarOpen = ! this.sidebarOpen
            editor.commands.focus()
            this.editorUpdatedAt = Date.now()
        },
        focusEditor: function (event) {
            if (event.detail.statePath === this.editor().commands.getStatePath()) {
                setTimeout(() => this.editor().commands.focus(), 200)
                this.editorUpdatedAt = Date.now()
            }
        },
        blurEditor: function () {
            const tippy = this.$el.querySelectorAll('[data-tippy-content]')
            this.$el.querySelectorAll('.is-active')?.forEach((item) => item.classList.remove('is-active'))

            if (tippy) {
                tippy.forEach((item) => item.destroy())
            }

            this.isFocused = false
            this.editorUpdatedAt = Date.now()
        },
        setEditorSelection: function (selection) {
            if (!selection) {
                return
            }

            this.editorSelection = selection

            const { $to } = editor.state.selection
            const lastPos = (editor.state.doc.content.size - editor.state.doc.lastChild.nodeSize) + 1

            if (($to.nodeBefore && $to.nodeBefore.type.name !== 'paragraph') && lastPos === this.editorSelection.anchor) {
                editor.commands.insertContentAt(this.editorSelection.anchor, { type: 'paragraph' })
            }

            editor
                .chain()
                .command(({ tr }) => {
                    tr.setSelection(
                        Selection.fromJSON(
                            editor.state.doc,
                            this.editorSelection,
                        ),
                    )

                    return true
                })
                .run()
        },
        runEditorCommands: function ({ commands, editorSelection }) {

            this.setEditorSelection(editorSelection)

            let commandChain = editor.chain().focus()

            if (this.isUpdatingBrick) {
                commandChain.setMeta('isUpdatingBrick', true)
                this.isUpdatingBrick = false
            }

            if (this.isInsertingBrick) {
                commandChain.setMeta('isInsertingBrick', true)
                commandChain.setMeta('isInsertingBrickPosition', this.isInsertingBrickPosition)
                this.isInsertingBrick = false
                this.isInsertingBrickPosition = null
            }

            commands.forEach(
                (command) =>
                    (commandChain = commandChain[command.name](
                        ...(command.arguments ?? []),
                    )),
            )

            commandChain.run()
        },
        handleBrickUpdate: function (identifier) {
            if (! this.isUpdatingBrick) {
                this.isUpdatingBrick = true
            }
            const data = editor.getAttributes('masonBrick')
            this.$wire.mountAction(
                identifier,
                this.statePath,
                { ...data.values, editorSelection: this.editorSelection },
                this.key
            )
        },
        handleBrickDrop: function (event) {
            let pos = event.detail.coordinates.pos

            this.$nextTick(() => {
                this.$wire.mountAction(
                    event.detail.name,
                    this.statePath,
                    { editorSelection: { type: 'node', anchor: pos, head: pos } },
                    this.key
                )
            })
        },
        handleBrickInsert: function (event) {
            if (this.statePath !== event.detail.statePath) {
                return
            }

            let anchor = event.detail.editorSelection.anchor

            if (! this.isInsertingBrick) {
                this.isInsertingBrick = true
                this.isInsertingBrickPosition = event.detail.position
            }

            this.$nextTick(() => {
                this.$wire.mountAction(
                    event.detail.name,
                    this.statePath,
                    { editorSelection: {type: 'node', anchor: anchor, head: anchor} },
                    this.key
                )
            })
        },
        handleBrickDelete: function () {
            editor.commands.deleteSelection()
        },
        moveBrick: function (direction) {
            const { state, view } = editor;
            const { selection } = state;
            const { $from, $to, node } = selection;

            if (direction === 'up') {
                const currentNode = node;
                const { nodeBefore } = view.state.doc.resolve($from.pos);

                if (nodeBefore) {
                    const moveNodeUp = view.state.tr
                        .replaceWith($from.pos, $to.pos, nodeBefore)
                        .replaceWith($from.pos - 1, $to.pos - 1, currentNode)

                    view.dispatch(moveNodeUp);

                    editor.commands.setNodeSelection($from.pos - 1)
                }
            } else {
                const currentNode = node;
                const { nodeAfter } = view.state.doc.resolve($to.pos);

                if (nodeAfter) {
                    const moveNodeDown = view.state.tr
                        .replaceWith($from.pos, $to.pos, nodeAfter)
                        .replaceWith($from.pos + 1, $to.pos + 1, currentNode)

                    view.dispatch(moveNodeDown);

                    editor.commands.setNodeSelection($from.pos + 1)
                }
            }

            this.editorUpdatedAt = Date.now()

            // this.scrollToCurrentBrick()
        },
        insertBrick: function () {
            this.$wire.mountAction(
                'insertBrick',
                this.statePath,
                { editorSelection: this.editorSelection },
                this.key
            )
        },
        scrollToCurrentBrick: function () {
            this.$nextTick(() => {
                const currentBrick = this.$el.querySelector('.ProseMirror-selectednode')

                if (currentBrick) {
                    currentBrick.scrollIntoView({behavior: 'auto'})
                }
            })
        }
    }
}
