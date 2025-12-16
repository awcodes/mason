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

export default function masonComponent({
    key,
    livewireId,
    state,
    statePath,
    placeholder = null,
    isDisabled,
    isLiveDebounced,
    isLiveOnBlur,
    liveDebounce,
    deleteBrickButtonIconHtml,
    editBrickButtonIconHtml,
}) {
    let editor = null
    let eventListeners = []
    let isDestroyed = false

    return {
        state: state,
        editorSelection: { type: 'text', anchor: 0, head: 1 },
        shouldUpdateState: true,
        editorUpdatedAt: Date.now(),
        statePath: statePath,
        fullscreen: false,
        viewport: 'desktop',
        isFocused: false,
        sidebarOpen: true,
        isUpdatingBrick: false,
        isInsertingBrick: false,
        isInsertingBrickPosition: null,
        async init() {
            editor = new Editor({
                editable: !isDisabled,
                element: this.$refs.editor,
                extensions: await this.getExtensions(),
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

            editor.on('create', () => {
                this.editorUpdatedAt = Date.now()
            })

            const debouncedCommit = Alpine.debounce(() => {
                if (!isDestroyed) {
                    this.$wire.commit()
                }
            }, liveDebounce ?? 300)

            editor.on('update', ({ editor }) =>
                this.$nextTick(() => {
                    if (isDestroyed) return

                    this.editorUpdatedAt = Date.now()

                    this.state = editor.getJSON()

                    this.shouldUpdateState = false

                    if (isLiveDebounced) {
                        debouncedCommit()
                    }
                }),
            )

            editor.on('selectionUpdate', ({ transaction }) => {
                if (isDestroyed) return

                this.editorUpdatedAt = Date.now()
                this.editorSelection = transaction.selection.toJSON()
            })

            editor.on('focus', ({ editor }) => {
                this.isFocused = true
            })

            if (isLiveOnBlur) {
                editor.on('blur', () => {
                    this.isFocused = false

                    if (!isDestroyed) {
                        this.$wire.commit()
                    }
                })
            }

            this.$watch('state', () => {
                if (isDestroyed) return

                if (!this.shouldUpdateState) {
                    this.shouldUpdateState = true

                    return
                }

                editor.commands.setContent(this.state)
            })

            const runCommandsHandler = (event) => {
                if (event.detail.livewireId !== livewireId) {
                    return
                }

                if (event.detail.key !== key) {
                    return
                }

                this.runEditorCommands(event.detail)
            }

            window.addEventListener(
                'run-mason-commands',
                runCommandsHandler,
            )

            eventListeners.push([
                'run-mason-commands',
                runCommandsHandler,
            ])

            window.dispatchEvent(
                new CustomEvent(`schema-component-${livewireId}-${key}-loaded`),
            )
        },

        getEditor() {
            return editor;
        },

        $getEditor() {
            return this.getEditor()
        },

        async getExtensions() {
            const coreExtensions = [
                Document.configure({
                    content: '(inline|block)+'
                }),
                MasonBrick.configure({
                    deleteBrickButtonIconHtml,
                    editBrickButtonIconHtml,
                    editBrickUsing: (id, config) =>
                        this.$wire.mountAction(
                            'handleBrick',
                            {
                                editorSelection: this.editorSelection,
                                id,
                                config,
                                mode: 'edit',
                            },
                            { schemaComponent: key },
                        ),
                    insertBrickUsing: (id, dragPosition = null) =>
                        this.$wire.mountAction(
                            'handleBrick',
                            { id, dragPosition, mode: 'insert' },
                            { schemaComponent: key },
                        ),
                }),
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
        toggleFullscreen() {
            this.fullscreen = !this.fullscreen

            editor.commands.focus()

            if (! this.fullscreen) {
                this.viewport = 'desktop'
            }

            this.editorUpdatedAt = Date.now()
        },
        toggleViewport(viewport) {
            this.viewport = viewport

            this.editorUpdatedAt = Date.now()
        },
        toggleSidebar() {
            this.sidebarOpen = ! this.sidebarOpen
            editor.commands.focus()
            this.editorUpdatedAt = Date.now()
        },
        focusEditor(event) {
            if (event.detail.statePath === this.editor().commands.getStatePath()) {
                setTimeout(() => this.editor().commands.focus(), 200)
                this.editorUpdatedAt = Date.now()
            }
        },
        blurEditor() {
            const tippy = this.$el.querySelectorAll('[data-tippy-content]')
            this.$el.querySelectorAll('.is-active')?.forEach((item) => item.classList.remove('is-active'))

            if (tippy) {
                tippy.forEach((item) => item.destroy())
            }

            this.isFocused = false
            this.editorUpdatedAt = Date.now()
        },
        setEditorSelection(selection) {
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
        runEditorCommands({ commands, editorSelection }) {
            this.setEditorSelection(editorSelection)

            let commandChain = editor.chain()

            commands.forEach(
                (command) =>
                    (commandChain = commandChain[command.name](
                        ...(command.arguments ?? []),
                    )),
            )

            commandChain.run()
        },
        scrollToCurrentBrick: function () {
            this.$nextTick(() => {
                const currentBrick = this.$el.querySelector('.ProseMirror-selectednode')

                if (currentBrick) {
                    currentBrick.scrollIntoView({behavior: 'auto'})
                }
            })
        },
        destroy() {
            isDestroyed = true

            eventListeners.forEach(([eventName, handler]) => {
                window.removeEventListener(eventName, handler)
            })
            eventListeners = []

            if (editor) {
                editor.destroy()
                editor = null
            }

            this.shouldUpdateState = true
        },
    }
}
