// Bricks are addressed by their path: "2" is the third brick at the top level,
// "2.0" the first brick inside it, "" is the top level itself.
function toPath(path) {
    if (path === null || path === undefined || path === '') {
        return []
    }

    return String(path).split('.').map(Number)
}

function isWithin(path, ancestor) {
    return (
        ancestor.length > 0 &&
        ancestor.every((index, depth) => path[depth] === index)
    )
}

// The bricks inside the brick at the path, or the top level for []
function childrenAt(blocks, path, create = false) {
    let children = blocks

    for (const index of path) {
        const block = children?.[index]

        if (!block) return null

        if (!Array.isArray(block.content)) {
            if (!create) return []

            block.content = []
        }

        children = block.content
    }

    return children
}

function findPath(blocks, node, prefix = []) {
    for (let index = 0; index < blocks.length; index++) {
        const block = blocks[index]
        const path = [...prefix, index]

        if (block === node) return path

        if (Array.isArray(block?.content)) {
            const found = findPath(block.content, node, path)

            if (found) return found
        }
    }

    return null
}

export default function masonComponent({
    key,
    state,
    statePath,
    disabled,
    dblClickToEdit,
    context = null,
    defaultColorMode = 'light',
    hasColorModeToggle = false,
    outlineBricks = {},
}) {
    let iframe = null
    let eventListeners = []
    let isDestroyed = false
    let savedScrollPosition = { x: 0, y: 0 }
    let outlineSortables = []
    // Block to re-select in the preview once it reloads after an outline move
    let pendingFocusPath = null

    return {
        state: state,
        statePath: statePath,
        fullscreen: false,
        viewport: 'desktop',
        sidebarOpen: true,
        sidebarTab: 'bricks',
        outlineSheetOpen: false,
        selectedBlockPath: null,
        colorMode: hasColorModeToggle
            ? localStorage.getItem('mason-color-mode') || defaultColorMode
            : defaultColorMode,
        isUpdatingBrick: false,
        isInsertingBrick: false,
        previewUrl: null,
        brickPickerOpen: false,
        // { path } inserts next to a brick, { parent, index } at a position in a container
        brickPickerTarget: null,
        brickPickerPosition: 'below',

        // Undo/Redo state
        undoStack: [],
        redoStack: [],
        maxHistorySize: 20,
        isUndoRedoOperation: false,

        async init() {
            // Listen for messages from iframe
            const messageHandler = (event) => {
                // Security: verify origin if needed
                // if (event.origin !== window.location.origin) return

                const { type, instanceId, ...data } = event.data

                // Ignore messages from other instances
                if (instanceId && instanceId !== statePath) {
                    return
                }

                switch (type) {
                    case 'ready':
                        this.sendMessageToIframe({
                            type: 'setContent',
                            blocks: this.getBlocksFromState(),
                        })
                        break
                    case 'editBlock':
                        this.handleEditBlock(data)
                        break
                    case 'deleteBlockRequest':
                        this.handleDeleteBlock(data.path ?? data.index)
                        break
                    case 'insertBlockRequest':
                        this.handleInsertBlock(
                            data.brickId,
                            data.position,
                            data.parent ?? '',
                        )
                        break
                    case 'updateBlockAttributes':
                        this.handleUpdateBlocksAttributes([
                            { path: data.path, attrs: data.attrs },
                        ])
                        break
                    case 'updateBlocksAttributes':
                        this.handleUpdateBlocksAttributes(data.updates)
                        break
                    case 'updateBlockRequest':
                        this.handleUpdateBlock(
                            data.path ?? data.index,
                            data.brick,
                        )
                        break
                    case 'moveBlockRequest':
                        this.handleMoveBlock(
                            data.from,
                            data.toParent ?? '',
                            data.toIndex,
                        )
                        break
                    case 'openBrickPicker':
                        this.openBrickPicker(
                            data.path !== undefined ||
                                data.blockIndex !== undefined
                                ? { path: String(data.path ?? data.blockIndex) }
                                : {
                                      parent: data.parent ?? '',
                                      index: data.index,
                                  },
                        )
                        break
                    case 'blockSelected':
                        this.selectedBlockPath = String(data.path ?? data.index)
                        this.scrollOutlineToSelected()
                        break
                    case 'keyboardShortcut':
                        const isMac =
                            navigator.platform.toUpperCase().indexOf('MAC') >= 0
                        const cmdOrCtrl = isMac ? data.metaKey : data.ctrlKey
                        if (cmdOrCtrl && !data.altKey) {
                            if (data.key === 'z' || data.key === 'Z') {
                                data.shiftKey ? this.redo() : this.undo()
                            } else if (
                                (data.key === 'y' || data.key === 'Y') &&
                                !data.shiftKey
                            ) {
                                this.redo()
                            }
                        }
                        break
                }
            }

            window.addEventListener('message', messageHandler)
            eventListeners.push(['message', messageHandler])

            // Keyboard shortcuts for undo/redo
            const keyboardHandler = (event) => {
                const activeElement = document.activeElement
                const isInputFocused =
                    activeElement &&
                    (activeElement.tagName === 'INPUT' ||
                        activeElement.tagName === 'TEXTAREA' ||
                        activeElement.isContentEditable)
                if (isInputFocused) return

                const isMac =
                    navigator.platform.toUpperCase().indexOf('MAC') >= 0
                const cmdOrCtrl = isMac ? event.metaKey : event.ctrlKey

                if (cmdOrCtrl && !event.altKey) {
                    if (event.key === 'z' || event.key === 'Z') {
                        event.preventDefault()
                        if (event.shiftKey) {
                            this.redo()
                        } else {
                            this.undo()
                        }
                    } else if (
                        (event.key === 'y' || event.key === 'Y') &&
                        !event.shiftKey
                    ) {
                        event.preventDefault()
                        this.redo()
                    }
                }
            }

            window.addEventListener('keydown', keyboardHandler)
            eventListeners.push(['keydown', keyboardHandler])

            // Watch for state changes
            this.$watch('state', () => {
                if (isDestroyed) return
                // The preview reloads and loses its selection
                if (pendingFocusPath === null) {
                    this.selectedBlockPath = null
                }
                this.updateIframeContent()
                // Update move buttons after iframe updates (with delay for iframe to load)
                setTimeout(() => {
                    if (iframe && iframe.contentWindow) {
                        try {
                            iframe.contentWindow.postMessage(
                                { type: 'updateMoveButtons' },
                                '*',
                            )
                        } catch (e) {
                            // Ignore cross-origin errors
                        }
                    }
                }, 200)
            })

            // Handle drag and drop from the sidebar
            this.setupDragAndDrop()
        },

        // Called from x-init on the iframe itself, so that if the element is ever
        // replaced (browser extensions injecting nodes can defeat Livewire's morph
        // matching) the new element is bound instead of leaving a stale reference.
        initPreviewIframe(el) {
            if (isDestroyed || !el || el._masonPreviewBound) {
                return
            }

            el._masonPreviewBound = true
            iframe = el

            el.addEventListener('load', () => {
                // Restore scroll position after iframe loads
                this.restoreScrollPosition()

                // Send postMessage once the iframe is loaded with config
                this.sendMessageToIframe({
                    type: 'setContent',
                    blocks: this.getBlocksFromState(),
                    dblClickToEdit: dblClickToEdit,
                    disabled: disabled,
                })

                // Send initial color mode to iframe
                this.sendMessageToIframe({
                    type: 'setColorMode',
                    mode: this.colorMode,
                })

                // Update move buttons after iframe loads
                setTimeout(() => {
                    this.sendMessageToIframe({
                        type: 'updateMoveButtons',
                    })
                }, 100)

                // Runs after restoreScrollPosition so the scroll isn't undone
                if (pendingFocusPath !== null) {
                    const path = pendingFocusPath
                    pendingFocusPath = null

                    setTimeout(() => {
                        this.sendMessageToIframe({
                            type: 'selectBlock',
                            path,
                            scroll: true,
                        })
                    }, 150)
                }
            })

            // Load initial content via form submission
            this.$nextTick(() => this.updatePreview())
        },

        getBlocksFromState() {
            if (!this.state) {
                return []
            }

            // Convert Proxy to a plain array to avoid cloning issues
            let stateArray = []
            try {
                // Use JSON parse/stringify to convert Proxy to a plain object
                stateArray = JSON.parse(JSON.stringify(this.state))
            } catch (e) {
                // Fallback: try to convert manually
                if (Array.isArray(this.state)) {
                    stateArray = Array.from(this.state)
                }
            }

            if (!Array.isArray(stateArray)) {
                return []
            }

            return stateArray.filter(
                (block) => block && block.type === 'masonBrick',
            )
        },

        sendMessageToIframe(message) {
            if (iframe && iframe.contentWindow) {
                // Include instanceId for multi-instance support
                iframe.contentWindow.postMessage({ ...message, instanceId: statePath }, '*')
            }
        },

        updateIframeContent() {
            this.updatePreview()
            // Also send config update to iframe
            this.sendMessageToIframe({
                type: 'setConfig',
                dblClickToEdit: dblClickToEdit,
                disabled: disabled,
            })
        },

        setupDragAndDrop() {
            // The sidebar already has draggable="true" and dragstart handler
            // We just need to handle the drop on the iframe
            const iframeContainer = this.$el.querySelector(
                '.mason-editor-wrapper',
            )

            if (iframeContainer) {
                iframeContainer.addEventListener('dragover', (e) => {
                    e.preventDefault()
                })

                iframeContainer.addEventListener('drop', (e) => {
                    e.preventDefault()
                    const brickId = e.dataTransfer.getData('brick')

                    if (brickId) {
                        // Calculate drop position (we'll insert at the end for now)
                        const blocks = this.getBlocksFromState()
                        const position = blocks.length

                        this.handleInsertBlock(brickId, position)
                    }
                })
            }
        },

        async handleInsertBlock(brickId, position, parent = '') {
            this.isInsertingBrick = true
            const preInsertState = this.captureState()

            try {
                await this.$wire.mountAction(
                    'handleBrick',
                    { id: brickId, parent, position, mode: 'insert' },
                    { schemaComponent: key },
                )
                this.pushToUndoStack(preInsertState)
            } finally {
                this.isInsertingBrick = false
            }
        },

        async handleEditBlock({ path, index, brickId, config }) {
            this.isUpdatingBrick = true
            const preEditState = this.captureState()

            try {
                await this.$wire.mountAction(
                    'handleBrick',
                    {
                        id: brickId,
                        config,
                        mode: 'edit',
                        blockPath: String(path ?? index),
                    },
                    { schemaComponent: key },
                )
                this.pushToUndoStack(preEditState)
            } finally {
                this.isUpdatingBrick = false
            }
        },

        handleDeleteBlock(path) {
            const blocks = this.getBlocksFromState()
            const target = toPath(path)
            const siblings = childrenAt(blocks, target.slice(0, -1))
            const index = target.at(-1)

            if (siblings && index >= 0 && index < siblings.length) {
                this.pushToUndoStack(this.captureState())
                siblings.splice(index, 1)

                this.updateStateFromBlocks(blocks)
            }
        },

        handleUpdateBlock(path, brick) {
            const blocks = this.getBlocksFromState()
            const target = toPath(path)
            const siblings = childrenAt(blocks, target.slice(0, -1))
            const index = target.at(-1)

            if (siblings && index >= 0 && index < siblings.length) {
                this.pushToUndoStack(this.captureState())
                siblings[index] = brick

                this.updateStateFromBlocks(blocks)
            }
        },

        // Merges attributes into bricks, e.g. layout data a container keeps on its
        // children, as one undo step. A null value removes the attribute; id and
        // config stay untouched. updates: [{ path, attrs }]
        handleUpdateBlocksAttributes(updates) {
            if (!Array.isArray(updates)) return

            const blocks = this.getBlocksFromState()
            let changed = false

            updates.forEach(({ path, attrs }) => {
                if (!attrs || typeof attrs !== 'object') return

                const target = toPath(path)
                const block = childrenAt(blocks, target.slice(0, -1))?.[
                    target.at(-1)
                ]

                if (!block) return

                block.attrs = block.attrs ?? {}

                Object.entries(attrs).forEach(([key, value]) => {
                    if (key === 'id' || key === 'config') return

                    if (value === null) {
                        delete block.attrs[key]
                    } else {
                        block.attrs[key] = value
                    }
                })

                changed = true
            })

            if (!changed) return

            this.pushToUndoStack(this.captureState())
            this.updateStateFromBlocks(blocks)
        },

        // toIndex is a drop position: it counts the target's bricks before the moved one is taken out
        handleMoveBlock(from, toParent, toIndex) {
            if (this.moveBlock(from, toParent, toIndex) === null) return

            // Update move buttons in iframe after move completes
            setTimeout(() => {
                this.sendMessageToIframe({ type: 'updateMoveButtons' })
            }, 150)
        },

        // Returns the moved brick's new path, or null if nothing moved
        moveBlock(from, toParent, toIndex) {
            const blocks = this.getBlocksFromState()
            const fromPath = toPath(from)
            const parentPath = toPath(toParent)

            if (fromPath.length === 0) return null

            // A brick can't be moved into itself
            if (isWithin(parentPath, fromPath)) return null

            const source = childrenAt(blocks, fromPath.slice(0, -1))
            const fromIndex = fromPath.at(-1)
            const moved = source?.[fromIndex]
            const target = childrenAt(blocks, parentPath, true)

            if (!moved || !target) return null
            if (toIndex < 0 || toIndex > target.length) return null
            if (
                source === target &&
                (toIndex === fromIndex || toIndex === fromIndex + 1)
            ) {
                return null
            }

            this.pushToUndoStack(this.captureState())

            // Hold the old place while inserting, so that no index shifts underneath
            const placeholder = {}
            source.splice(fromIndex, 1, placeholder)
            target.splice(toIndex, 0, moved)
            source.splice(source.indexOf(placeholder), 1)

            const newPath = findPath(blocks, moved).join('.')

            this.updateStateFromBlocks(blocks)

            return newPath
        },

        // Moves a brick among its siblings; to is its index afterwards
        moveBlockTo(from, to) {
            const fromPath = toPath(from)
            const siblings = childrenAt(
                this.getBlocksFromState(),
                fromPath.slice(0, -1),
            )
            const fromIndex = fromPath.at(-1)

            if (!siblings || to < 0 || to >= siblings.length) return
            if (fromIndex === to) return

            // A position after the brick counts it as still being there
            const newPath = this.moveBlock(
                from,
                fromPath.slice(0, -1).join('.'),
                to > fromIndex ? to + 1 : to,
            )

            if (newPath === null) return

            this.selectedBlockPath = newPath
            pendingFocusPath = newPath
        },

        // The outline lists nested bricks below their container, indented by depth
        outlineItems() {
            const items = []

            const visit = (blocks, prefix) => {
                blocks.forEach((block, index) => {
                    if (!block || block.type !== 'masonBrick') return

                    const path = [...prefix, index]
                    const brick = outlineBricks[block.attrs?.id]

                    const type =
                        brick?.label ?? block.attrs?.label ?? block.attrs?.id
                    const outlineLabel = block.attrs?.outlineLabel

                    items.push({
                        path: path.join('.'),
                        depth: prefix.length,
                        index,
                        siblings: blocks.length,
                        label: outlineLabel || type,
                        type: outlineLabel ? type : null,
                        icon: brick?.icon ?? null,
                    })

                    if (Array.isArray(block.content)) {
                        visit(block.content, path)
                    }
                })
            }

            visit(this.getBlocksFromState(), [])

            return items
        },

        initOutline(list) {
            if (!window.Sortable) {
                return
            }

            // On touch, Sortable appends a copy of the dragged row to the list.
            // Alpine would try to initialise that copy outside of x-for and fail
            // on `item` and `index`. x-for renders its rows without the observer.
            list._x_ignoreMutationObserver = true

            let originalNextSibling = null

            outlineSortables.push(
                // Only top level rows are dragged here; nested bricks move with
                // the keyboard or in the preview
                window.Sortable.create(list, {
                    draggable: '[data-outline-item][data-outline-depth="0"]',
                    handle: '[data-outline-handle]',
                    animation: 150,
                    ghostClass: 'mason-outline-ghost',
                    onStart: ({ item }) => {
                        originalNextSibling = item.nextSibling
                    },
                    onEnd: ({ item, oldDraggableIndex, newDraggableIndex }) => {
                        // Put the row back where Sortable took it from so that
                        // Alpine's x-for stays in charge of the DOM, then let the
                        // state change re-render the list in its new order.
                        list.insertBefore(item, originalNextSibling)
                        originalNextSibling = null

                        if (oldDraggableIndex === newDraggableIndex) return

                        this.moveBlockTo(
                            String(oldDraggableIndex),
                            newDraggableIndex,
                        )
                    },
                }),
            )
        },

        moveOutlineBlock(path, delta, button) {
            const list = button.closest('.mason-outline-list')

            this.moveBlockTo(path, toPath(path).at(-1) + delta)

            this.$nextTick(() => {
                list?.querySelector(
                    `[data-outline-path="${this.selectedBlockPath}"] .mason-outline-select`,
                )?.focus()
            })
        },

        closeOutlineSheet() {
            if (!this.outlineSheetOpen) return

            this.outlineSheetOpen = false
            this.$el
                .querySelector('.mason-outline-sheet-trigger')
                ?.focus({ preventScroll: true })
        },

        focusBlock(path) {
            this.selectedBlockPath = path
            this.sendMessageToIframe({
                type: 'selectBlock',
                path,
                scroll: true,
            })
        },

        scrollOutlineToSelected() {
            this.$nextTick(() => {
                this.$el
                    .querySelectorAll(
                        `.mason-outline-item[data-outline-path="${this.selectedBlockPath}"]`,
                    )
                    .forEach((row) => {
                        const list = row.closest('.mason-outline')

                        if (!list || row.offsetParent === null) return

                        // Scroll only the list; scrollIntoView() would also scroll the page
                        const rowRect = row.getBoundingClientRect()
                        const listRect = list.getBoundingClientRect()

                        if (rowRect.top < listRect.top) {
                            list.scrollTop -= listRect.top - rowRect.top
                        } else if (rowRect.bottom > listRect.bottom) {
                            list.scrollTop += rowRect.bottom - listRect.bottom
                        }
                    })
            })
        },

        updateStateFromBlocks(blocks) {
            // Ensure blocks is a plain array (not Proxy) before updating
            let plainBlocks = blocks
            try {
                // Convert to plain objects via JSON (handles Proxy objects)
                plainBlocks = JSON.parse(JSON.stringify(blocks))
            } catch (e) {
                // If conversion fails, try to create a new array manually
                if (Array.isArray(blocks)) {
                    plainBlocks = blocks.map((block) => {
                        try {
                            return JSON.parse(JSON.stringify(block))
                        } catch {
                            return block
                        }
                    })
                }
            }

            // Update the state with new blocks array
            this.state = plainBlocks
            // Use the statePath from the component
            const path = this.statePath
            this.$wire.set(path, plainBlocks, false)
        },

        toggleFullscreen() {
            this.fullscreen = !this.fullscreen

            if (!this.fullscreen) {
                this.viewport = 'desktop'
            }
        },

        toggleViewport(viewport) {
            if (this.viewport === viewport) {
                this.viewport = 'desktop'
                return
            }

            this.viewport = viewport
        },

        toggleSidebar() {
            this.sidebarOpen = !this.sidebarOpen
        },

        toggleColorMode() {
            this.colorMode = this.colorMode === 'dark' ? 'light' : 'dark'
            if (hasColorModeToggle) {
                localStorage.setItem('mason-color-mode', this.colorMode)
            }
            this.sendMessageToIframe({
                type: 'setColorMode',
                mode: this.colorMode,
            })
        },

        saveScrollPosition() {
            // Save scroll position from iframe's content window
            if (iframe && iframe.contentWindow) {
                try {
                    savedScrollPosition = {
                        x:
                            iframe.contentWindow.scrollX ||
                            iframe.contentWindow.pageXOffset ||
                            0,
                        y:
                            iframe.contentWindow.scrollY ||
                            iframe.contentWindow.pageYOffset ||
                            0,
                    }
                } catch (e) {
                    // Cross-origin or other error, reset to 0
                    savedScrollPosition = { x: 0, y: 0 }
                }
            }
        },

        restoreScrollPosition() {
            // Restore scroll position in iframe's content window
            if (
                iframe &&
                iframe.contentWindow &&
                (savedScrollPosition.x > 0 || savedScrollPosition.y > 0)
            ) {
                try {
                    // Use multiple timeouts to ensure content is fully loaded and rendered
                    const restore = () => {
                        if (iframe && iframe.contentWindow) {
                            const doc =
                                iframe.contentDocument ||
                                iframe.contentWindow.document
                            if (doc && doc.readyState === 'complete') {
                                iframe.contentWindow.scrollTo(
                                    savedScrollPosition.x,
                                    savedScrollPosition.y,
                                )
                            } else {
                                // If not ready, try again
                                setTimeout(restore, 50)
                            }
                        }
                    }

                    // Try immediately, then with delays
                    setTimeout(restore, 0)
                    setTimeout(restore, 50)
                    setTimeout(restore, 100)
                } catch (e) {
                    // Cross-origin or other error, ignore
                }
            }
        },

        updatePreview() {
            if (!iframe) {
                return
            }

            // Save scroll position before updating
            this.saveScrollPosition()

            // Update iframe using form submission (more reliable than srcdoc)
            const blocks = this.getBlocksFromState()

            // Ensure blocks are plain objects (not Proxy) before stringifying
            let plainBlocks = blocks
            try {
                plainBlocks = JSON.parse(JSON.stringify(blocks))
            } catch (e) {
                // If JSON conversion fails, use blocks as-is (shouldn't happen after getBlocksFromState)
                plainBlocks = blocks
            }

            // Create a form that will submit to the iframe
            const form = document.createElement('form')
            form.method = 'POST'
            form.action = '/mason/preview'
            form.target = iframe.name
            form.style.display = 'none'

            // Add blocks data
            const blocksInput = document.createElement('input')
            blocksInput.type = 'hidden'
            blocksInput.name = 'blocks'
            blocksInput.value = JSON.stringify(plainBlocks)
            form.appendChild(blocksInput)

            // Add the signed bricks and layout context
            const contextInput = document.createElement('input')
            contextInput.type = 'hidden'
            contextInput.name = 'context'
            contextInput.value = context
            form.appendChild(contextInput)

            // Add CSRF token
            const csrfInput = document.createElement('input')
            csrfInput.type = 'hidden'
            csrfInput.name = '_token'
            csrfInput.value =
                document.querySelector('meta[name="csrf-token"]')?.content || ''
            form.appendChild(csrfInput)

            // Append form to body, submit, then remove
            document.body.appendChild(form)
            form.submit()

            // Remove form after a short delay
            setTimeout(() => {
                if (form.parentNode) {
                    form.parentNode.removeChild(form)
                }
            }, 100)
        },

        deselectAllBlocks() {
            if (this.isUpdatingBrick) return
            this.selectedBlockPath = null
            this.sendMessageToIframe({ type: 'deselectAllBlocks' })
        },

        openBrickPicker(target) {
            this.brickPickerTarget = target
            this.brickPickerOpen = true
        },

        closeBrickPicker() {
            this.brickPickerOpen = false
            this.brickPickerTarget = null
            this.brickPickerPosition = 'below'
        },

        insertFromPicker(brickId) {
            const target = this.brickPickerTarget ?? { parent: '', index: null }

            if (target.path !== undefined) {
                const path = toPath(target.path)
                const index = path.at(-1)

                this.handleInsertBlock(
                    brickId,
                    this.brickPickerPosition === 'above' ? index : index + 1,
                    path.slice(0, -1).join('.'),
                )
            } else {
                this.handleInsertBlock(brickId, target.index, target.parent)
            }

            this.closeBrickPicker()
        },

        // Undo/Redo methods
        captureState() {
            return JSON.parse(JSON.stringify(this.state || []))
        },

        pushToUndoStack(previousState) {
            if (this.isUndoRedoOperation) return

            this.undoStack.push(previousState)
            if (this.undoStack.length > this.maxHistorySize) {
                this.undoStack.shift()
            }
            this.redoStack = []
        },

        undo() {
            if (this.undoStack.length === 0) return

            this.redoStack.push(this.captureState())
            if (this.redoStack.length > this.maxHistorySize) {
                this.redoStack.shift()
            }

            const previousState = this.undoStack.pop()
            this.isUndoRedoOperation = true
            this.updateStateFromBlocks(previousState)
            this.isUndoRedoOperation = false
        },

        redo() {
            if (this.redoStack.length === 0) return

            this.undoStack.push(this.captureState())
            if (this.undoStack.length > this.maxHistorySize) {
                this.undoStack.shift()
            }

            const nextState = this.redoStack.pop()
            this.isUndoRedoOperation = true
            this.updateStateFromBlocks(nextState)
            this.isUndoRedoOperation = false
        },

        canUndo() {
            return this.undoStack.length > 0
        },

        canRedo() {
            return this.redoStack.length > 0
        },

        clearAllBlocks() {
            if (this.state && this.state.length > 0) {
                this.pushToUndoStack(this.captureState())
            }
            this.state = []
            this.updateStateFromBlocks([])
        },

        destroy() {
            isDestroyed = true

            eventListeners.forEach(([eventName, handler]) => {
                window.removeEventListener(eventName, handler)
            })
            eventListeners = []

            outlineSortables.forEach((sortable) => sortable.destroy())
            outlineSortables = []

            if (iframe) {
                // Allow a future init to rebind if the element outlives this component
                delete iframe._masonPreviewBound
            }

            iframe = null
        },
    }
}
