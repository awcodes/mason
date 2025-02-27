import { Dropcursor } from '@tiptap/extension-dropcursor'
import { Document } from '@tiptap/extension-document'
import { Editor } from '@tiptap/core'
import { History } from '@tiptap/extension-history'
import { Paragraph } from '@tiptap/extension-paragraph'
import { Placeholder } from '@tiptap/extension-placeholder'
import { Text } from '@tiptap/extension-text'
import MasonBrick from './extensions/MasonBrick'
import Customizations from './extensions/Customizations'
import DragAndDrop from './extensions/DragAndDrop'
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
        editorSelection: { type: 'text', anchor: 0, head: 0 },
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
                content: this.state ?? '',
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

                const currentBrick = this.$el.querySelector('.ProseMirror-selectednode')

                if (currentBrick) {
                    currentBrick.scrollIntoView({behavior: 'auto'})
                }
            })

            editor.on('selectionUpdate', ({ editor, transaction }) => {
                this.editorUpdatedAt = Date.now()
                this.editorSelection = transaction.selection.toJSON()
            })

            editor.on('focus', ({ editor }) => {
                this.isFocused = true
                this.editorUpdatedAt = Date.now()
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

                editor.commands.setContent(this.state)
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
        getExtensions: function () {
            const coreExtensions = [
                Document.configure({
                    content: 'block+'
                }),
                MasonBrick,
                Customizations,
                DragAndDrop,
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
        setEditorSelection: function (selection) {
            if (!selection) {
                return
            }

            this.editorSelection = selection

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

            commands.forEach(
                (command) =>
                    (commandChain = commandChain[command.name](
                        ...(command.arguments ?? []),
                    )),
            )

            commandChain.run()
        },
        handleBlockUpdate: function (identifier) {
            const data = editor.getAttributes('masonBrick')
            this.$wire.mountFormComponentAction(this.statePath, identifier, { ...data.values, editorSelection: this.editorSelection }, this.key)
        },
        handleBrickDrop: function (event) {
            let pos = event.detail.coordinates.pos

            if (editor.isEmpty && pos !== 1) {
                pos = pos - 1
            }

            this.setEditorSelection({ type: 'text', anchor: pos, head: pos})

            this.$nextTick(() => {
                this.handleBlockUpdate(event.detail.name)
            })
        }
    }
}
