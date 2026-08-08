export default function masonComponent({
    key,
    state,
    statePath,
    disabled,
    dblClickToEdit,
    bricks = [],
    previewLayout = null,
    defaultColorMode = 'light',
    hasColorModeToggle = false,
}) {
    let iframe = null
    let eventListeners = []
    let isDestroyed = false
    let savedScrollPosition = { x: 0, y: 0 }

    return {
        state: state,
        statePath: statePath,
        fullscreen: false,
        viewport: 'desktop',
        sidebarOpen: true,
        colorMode: hasColorModeToggle
            ? localStorage.getItem('mason-color-mode') || defaultColorMode
            : defaultColorMode,
        isUpdatingBrick: false,
        isInsertingBrick: false,
        previewUrl: null,
        previewLayout: previewLayout,
        brickPickerOpen: false,
        brickPickerBlockIndex: null,
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
                        this.handleDeleteBlock(data.index)
                        break
                    case 'insertBlockRequest':
                        this.handleInsertBlock(data.brickId, data.position)
                        break
                    case 'updateBlockRequest':
                        this.handleUpdateBlock(data.index, data.brick)
                        break
                    case 'moveBlockRequest':
                        this.handleMoveBlock(data.from, data.to)
                        break
                    case 'openBrickPicker':
                        this.openBrickPicker(data.blockIndex)
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

        async handleInsertBlock(brickId, position) {
            this.isInsertingBrick = true
            const preInsertState = this.captureState()

            try {
                await this.$wire.mountAction(
                    'handleBrick',
                    { id: brickId, dragPosition: position, mode: 'insert' },
                    { schemaComponent: key },
                )
                this.pushToUndoStack(preInsertState)
            } finally {
                this.isInsertingBrick = false
            }
        },

        async handleEditBlock({ index, brickId, config }) {
            this.isUpdatingBrick = true
            const preEditState = this.captureState()

            try {
                await this.$wire.mountAction(
                    'handleBrick',
                    {
                        id: brickId,
                        config,
                        mode: 'edit',
                        blockIndex: index,
                    },
                    { schemaComponent: key },
                )
                this.pushToUndoStack(preEditState)
            } finally {
                this.isUpdatingBrick = false
            }
        },

        handleDeleteBlock(index) {
            const blocks = this.getBlocksFromState()

            if (index >= 0 && index < blocks.length) {
                this.pushToUndoStack(this.captureState())
                // Create a new array without the deleted block
                const newBlocks = blocks.filter((_, i) => i !== index)

                this.updateStateFromBlocks(newBlocks)
            }
        },

        handleUpdateBlock(index, brick) {
            const blocks = this.getBlocksFromState()

            if (index >= 0 && index < blocks.length) {
                this.pushToUndoStack(this.captureState())
                // Create a new array with the updated block
                const newBlocks = blocks.map((block, i) =>
                    i === index ? brick : block,
                )

                this.updateStateFromBlocks(newBlocks)
            }
        },

        handleMoveBlock(from, to) {
            const blocks = this.getBlocksFromState()

            // Validate indices
            if (from < 0 || from >= blocks.length) return
            // Allow to be up to blocks.length (for moving to the end)
            if (to < 0 || to > blocks.length) return
            if (from === to) return

            this.pushToUndoStack(this.captureState())

            // Create a new array with moved block (blocks are already plain from getBlocksFromState)
            const newBlocks = blocks.slice() // Use slice instead of spread for safety
            const moved = newBlocks[from]
            newBlocks.splice(from, 1)

            // Adjust target index after removing the block
            // The 'to' index represents the target position in the original array
            // After removal, indices shift for items after 'from'
            let adjustedTo

            if (to === blocks.length) {
                // Moving to the end - insert at the end of the new array
                adjustedTo = newBlocks.length
            } else if (to > from) {
                // Moving down or dragging to a position after the block
                if (to === from + 1) {
                    // Move down button: adjacent swap (from=0, to=1)
                    // After removing block at 'from', insert at 'to' to swap with next block
                    // Example: [B1(0), B2(1)] -> remove B1: [B2(0)] -> insert at 1: [B2(0), B1(1)]
                    adjustedTo = to
                } else {
                    // Drag and drop: non-adjacent move (from=0, to=2+)
                    // After removing block at 'from', the drop zone at 'to' shifts to 'to - 1'
                    // Example: [B1(0), B2(1), B3(2)], drag B1 to drop zone 2
                    // After removing B1: [B2(0), B3(1)]
                    // Drop zone 2 is now at position 1, so insert at 1: [B2(0), B1(1), B3(2)]
                    adjustedTo = to - 1
                }
            } else {
                // Moving up - target is before the removed item, so no shift
                adjustedTo = to
            }

            // Clamp to valid range (0 to newBlocks.length)
            adjustedTo = Math.max(0, Math.min(adjustedTo, newBlocks.length))

            newBlocks.splice(adjustedTo, 0, moved)

            this.updateStateFromBlocks(newBlocks)

            // Update move buttons in iframe after move completes
            setTimeout(() => {
                this.sendMessageToIframe({ type: 'updateMoveButtons' })
            }, 150)
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

            // Add bricks data
            const bricksInput = document.createElement('input')
            bricksInput.type = 'hidden'
            bricksInput.name = 'bricks'
            bricksInput.value = JSON.stringify(bricks)
            form.appendChild(bricksInput)

            // Add preview layout if provided
            if (this.previewLayout) {
                const layoutInput = document.createElement('input')
                layoutInput.type = 'hidden'
                layoutInput.name = 'layout'
                layoutInput.value = this.previewLayout
                form.appendChild(layoutInput)
            }

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
            this.sendMessageToIframe({ type: 'deselectAllBlocks' })
        },

        openBrickPicker(blockIndex) {
            this.brickPickerBlockIndex = blockIndex
            this.brickPickerOpen = true
        },

        closeBrickPicker() {
            this.brickPickerOpen = false
            this.brickPickerBlockIndex = null
            this.brickPickerPosition = 'below'
        },

        insertFromPicker(brickId) {
            const position =
                this.brickPickerPosition === 'above'
                    ? this.brickPickerBlockIndex
                    : this.brickPickerBlockIndex + 1
            this.handleInsertBlock(brickId, position)
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

            if (iframe) {
                // Allow a future init to rebind if the element outlives this component
                delete iframe._masonPreviewBound
            }

            iframe = null
        },
    }
}
