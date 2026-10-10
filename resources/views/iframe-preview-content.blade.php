<div id="mason-preview-container">
    @if (empty($blocks))
        <div
            class="mason-drop-zone"
            data-drop-index="0"
            data-drop-parent=""
            style="
                min-height: 4rem;
                display: flex;
                align-items: center;
                justify-content: center;
                color: #9ca3af;
                position: absolute;
                inset: 0;
                margin: 0;
            "
        >
            {{ __('mason::mason.preview.placeholder') }}
        </div>
    @else
        <div
            class="mason-drop-zone"
            data-drop-index="0"
            data-drop-parent=""
            style="min-height: 2rem"
        ></div>
        @foreach ($blocks as $block)
            @include('mason::components.preview-block', ['block' => $block, 'total' => count($blocks)])
            <div
                class="mason-drop-zone"
                data-drop-index="{{ $block['index'] + 1 }}"
                data-drop-parent=""
            ></div>
        @endforeach
    @endif
</div>

<script>
    // Bricks are addressed by their path: "2" is the third brick at the top level,
    // "2.0" the first brick inside it, "" is the top level itself.
    ;(function () {
        const container = document.getElementById('mason-preview-container')
        let selectedBlock = null
        let dblClickToEdit = false
        let isDisabled = false
        let instanceId = null // Store instance ID for multi-instance support

        // Helper to send messages back to parent with instanceId
        function postToParent(message) {
            window.parent.postMessage({ ...message, instanceId }, '*')
        }

        // For scripts of the preview layout, e.g. a container brick's own editing UI
        window.masonPreview = {
            post: (message) => postToParent(message),
            blockByPath: (path) => blockByPath(path),
            updateBlockAttributes: (path, attrs) =>
                postToParent({ type: 'updateBlockAttributes', path, attrs }),
            // [{ path, attrs }], applied as one undo step
            updateBlocksAttributes: (updates) =>
                postToParent({ type: 'updateBlocksAttributes', updates }),
        }

        function blockByPath(path) {
            return container.querySelector(
                `.mason-block[data-block-path="${CSS.escape(String(path))}"]`,
            )
        }

        function parentOf(path) {
            const position = path.lastIndexOf('.')

            return position === -1 ? '' : path.slice(0, position)
        }

        function indexOf(path) {
            return parseInt(path.slice(path.lastIndexOf('.') + 1))
        }

        function isWithin(path, ancestor) {
            return (
                ancestor !== '' &&
                (path === ancestor || path.startsWith(ancestor + '.'))
            )
        }

        // Whether the brick may be placed in the parent; the top level takes every brick
        function accepts(parent, brickId) {
            if (parent === '') return true

            const accepted = blockByPath(parent)?.getAttribute('data-accepts')

            if (accepted === null || accepted === undefined) return false

            return accepted.split(',').includes(brickId)
        }

        // Listen for messages from parent
        window.addEventListener('message', function (event) {
            // Security: verify origin if needed
            // if (event.origin !== window.location.origin) return;

            const { type, instanceId: msgInstanceId, ...data } = event.data

            // Capture instanceId from parent for multi-instance support
            if (msgInstanceId && !instanceId) {
                instanceId = msgInstanceId
            }

            // Ignore messages from other instances
            if (msgInstanceId && instanceId && msgInstanceId !== instanceId) {
                return
            }

            switch (type) {
                case 'setContent':
                    if (data.dblClickToEdit !== undefined) {
                        dblClickToEdit = data.dblClickToEdit
                    }

                    shouldBeDisabled(data)

                    updateContent(data.blocks)
                    break
                case 'setConfig':
                    if (data.dblClickToEdit !== undefined) {
                        dblClickToEdit = data.dblClickToEdit
                    }

                    shouldBeDisabled(data)
                    break
                case 'selectBlock':
                    selectBlock(String(data.path), data.scroll)
                    break
                case 'updateMoveButtons':
                    updateAllMoveButtons()
                    break
                case 'deselectAllBlocks':
                    deselectAllBlocks()
                    break
                case 'setColorMode':
                    if (data.mode === 'dark') {
                        document.documentElement.classList.add('dark')
                    } else {
                        document.documentElement.classList.remove('dark')
                    }
                    break
            }
        })

        // Send ready message when loaded
        window.addEventListener('load', function () {
            updateAllMoveButtons()
            postToParent({ type: 'ready' })
        })

        // Forward undo/redo shortcuts to parent
        window.addEventListener('keydown', function (event) {
            const isMac = navigator.platform.toUpperCase().indexOf('MAC') >= 0
            const cmdOrCtrl = isMac ? event.metaKey : event.ctrlKey

            if (cmdOrCtrl && !event.altKey) {
                if (
                    event.key === 'z' ||
                    event.key === 'Z' ||
                    event.key === 'y' ||
                    event.key === 'Y'
                ) {
                    postToParent({
                        type: 'keyboardShortcut',
                        key: event.key,
                        ctrlKey: event.ctrlKey,
                        metaKey: event.metaKey,
                        shiftKey: event.shiftKey,
                        altKey: event.altKey,
                    })
                    event.preventDefault()
                }
            }
        })

        function updateContent(blocks) {
            // This will be handled by reloading the iframe with new content
            // For now, we'll just notify parent that we need a reload
            postToParent({ type: 'contentUpdated' })
            // Update buttons after a short delay to ensure DOM is updated
            setTimeout(updateAllMoveButtons, 100)
        }

        function shouldBeDisabled(data) {
            if (data.disabled !== undefined) {
                isDisabled = data.disabled
            }

            if (isDisabled) {
                container.style.pointerEvents = 'none'
            } else {
                container.style.pointerEvents = ''
            }
        }

        function selectBlock(path, scroll = false) {
            // Remove previous selection
            if (selectedBlock) {
                selectedBlock.classList.remove('selected')
            }

            const block = blockByPath(path)
            if (block) {
                block.classList.add('selected')
                selectedBlock = block
                updateMoveButtons(block)

                // scrollIntoView() would also scroll the host page around the iframe
                if (scroll) {
                    window.scrollTo({
                        top: block.getBoundingClientRect().top + window.scrollY,
                        behavior: 'smooth',
                    })
                }
            }
        }

        // Each block knows its position among its siblings from the server
        function updateMoveButtons(block) {
            if (!block) return

            const index = parseInt(block.getAttribute('data-block-index'))
            const totalBlocks = parseInt(
                block.getAttribute('data-total-blocks'),
            )

            const moveUpBtn = block.querySelector(
                ':scope > .mason-block-controls [data-action="move-up"]',
            )
            const moveDownBtn = block.querySelector(
                ':scope > .mason-block-controls [data-action="move-down"]',
            )

            if (moveUpBtn) {
                moveUpBtn.disabled = index === 0
            }

            if (moveDownBtn) {
                moveDownBtn.disabled = index === totalBlocks - 1
            }
        }

        function deselectAllBlocks() {
            container.querySelectorAll('.mason-block').forEach((block) => {
                block.classList.remove('selected')
            })
            selectedBlock = null
        }

        function updateAllMoveButtons() {
            container
                .querySelectorAll('.mason-block')
                .forEach((block) => updateMoveButtons(block))
        }

        // Handle block clicks for editing
        container.addEventListener('click', function (e) {
            const action = e.target.closest('[data-action]')

            // The add button of a container's drop zone adds into the container
            if (action && action.getAttribute('data-action') === 'add-into') {
                const zone = action.closest('.mason-drop-zone')

                postToParent({
                    type: 'openBrickPicker',
                    parent: zone.getAttribute('data-drop-parent') ?? '',
                    index: parseInt(zone.getAttribute('data-drop-index')),
                })

                return
            }

            const block = (action ?? e.target).closest('.mason-block')
            if (!block) return

            const path = block.getAttribute('data-block-path')

            if (action) {
                const actionType = action.getAttribute('data-action')
                const index = indexOf(path)
                const brickId = block.getAttribute('data-brick-id')
                const config = JSON.parse(
                    block.getAttribute('data-config') || '{}',
                )

                if (actionType === 'edit') {
                    postToParent({
                        type: 'editBlock',
                        path,
                        brickId,
                        config,
                    })
                } else if (actionType === 'delete') {
                    postToParent({
                        type: 'deleteBlockRequest',
                        path,
                    })
                } else if (actionType === 'move-up') {
                    if (!action.disabled) {
                        postToParent({
                            type: 'moveBlockRequest',
                            from: path,
                            toParent: parentOf(path),
                            toIndex: index - 1,
                        })
                    }
                } else if (actionType === 'move-down') {
                    if (!action.disabled) {
                        // Drop positions count the siblings before the block is taken out
                        postToParent({
                            type: 'moveBlockRequest',
                            from: path,
                            toParent: parentOf(path),
                            toIndex: index + 2,
                        })
                    }
                } else if (actionType === 'add') {
                    postToParent({
                        type: 'openBrickPicker',
                        path,
                    })
                }
            } else {
                // Click on block content - select it
                selectBlock(path)
                postToParent({ type: 'blockSelected', path })
            }
        })

        // Handle double-click to edit
        container.addEventListener('dblclick', function (e) {
            if (!dblClickToEdit) return

            const block = e.target.closest('.mason-block')
            if (!block) return

            // Ignore double-clicks on controls and drop zones
            if (
                e.target.closest('.mason-block-controls') ||
                e.target.closest('[data-action]')
            ) {
                return
            }

            // Trigger edit action
            postToParent({
                type: 'editBlock',
                path: block.getAttribute('data-block-path'),
                brickId: block.getAttribute('data-brick-id'),
                config: JSON.parse(block.getAttribute('data-config') || '{}'),
            })
        })

        // Handle drag and drop for repositioning blocks
        let draggedPath = null
        let draggedBlock = null

        // Where a drop would go: a drop zone, or before / after the block under the pointer
        function resolveDropTarget(e) {
            const zone = e.target.closest('.mason-drop-zone')

            if (zone) {
                return {
                    parent: zone.getAttribute('data-drop-parent') ?? '',
                    index: parseInt(zone.getAttribute('data-drop-index')),
                    zone,
                }
            }

            const block = e.target.closest('.mason-block')

            if (!block) return null

            const path = block.getAttribute('data-block-path')
            const rect = block.getBoundingClientRect()
            const after = e.clientY > rect.top + rect.height / 2

            return {
                parent: parentOf(path),
                index: indexOf(path) + (after ? 1 : 0),
                block,
                after,
            }
        }

        function canDrop(target, brickId) {
            if (!target || isNaN(target.index)) return false

            if (brickId && !accepts(target.parent, brickId)) return false

            if (draggedPath === null) return true

            // Not into itself, and not where it already is
            if (isWithin(target.parent, draggedPath)) return false

            if (target.parent === parentOf(draggedPath)) {
                const index = indexOf(draggedPath)

                return target.index !== index && target.index !== index + 1
            }

            return true
        }

        function clearDropIndicators() {
            container
                .querySelectorAll('.mason-drop-zone.active')
                .forEach((zone) => zone.classList.remove('active'))
            container
                .querySelectorAll('.mason-drop-before, .mason-drop-after')
                .forEach((block) =>
                    block.classList.remove(
                        'mason-drop-before',
                        'mason-drop-after',
                    ),
                )
        }

        // Make blocks draggable for repositioning
        container.addEventListener('dragstart', function (e) {
            const block = e.target.closest('.mason-block')
            if (!block) return

            // Prevent dragging if clicking on controls (but allow drag handle)
            if (e.target.closest('.mason-block-controls')) {
                e.preventDefault()
                return
            }

            // Nested blocks sit inside their parent's block; only move the innermost one
            e.stopPropagation()

            draggedBlock = block
            draggedPath = block.getAttribute('data-block-path')

            block.classList.add('dragging')

            // Disable all interactions during drag
            document.body.style.userSelect = 'none'
            document.body.style.webkitUserSelect = 'none'

            // Set drag data
            e.dataTransfer.effectAllowed = 'move'
            e.dataTransfer.setData('text/plain', draggedPath)
        })

        container.addEventListener('dragend', function () {
            // Re-enable interactions
            document.body.style.userSelect = ''
            document.body.style.webkitUserSelect = ''

            if (draggedBlock) {
                draggedBlock.classList.remove('dragging')
            }

            clearDropIndicators()

            draggedBlock = null
            draggedPath = null
        })

        container.addEventListener('dragover', function (e) {
            e.preventDefault()

            clearDropIndicators()

            const target = resolveDropTarget(e)
            const brickId = draggedBlock?.getAttribute('data-brick-id')

            // A brick from the sidebar is only known on drop, so it is checked there
            if (!canDrop(target, brickId)) {
                if (e.dataTransfer) e.dataTransfer.dropEffect = 'none'
                return
            }

            if (target.zone) {
                target.zone.classList.add('active')
            } else {
                target.block.classList.add(
                    target.after ? 'mason-drop-after' : 'mason-drop-before',
                )
            }
        })

        container.addEventListener('dragleave', function (e) {
            if (!container.contains(e.relatedTarget)) {
                clearDropIndicators()
            }
        })

        container.addEventListener('drop', function (e) {
            e.preventDefault()
            e.stopPropagation()

            clearDropIndicators()

            const target = resolveDropTarget(e)

            if (draggedPath !== null) {
                const brickId = draggedBlock?.getAttribute('data-brick-id')

                if (canDrop(target, brickId)) {
                    postToParent({
                        type: 'moveBlockRequest',
                        from: draggedPath,
                        toParent: target.parent,
                        toIndex: target.index,
                    })
                }

                draggedPath = null
                draggedBlock = null

                return
            }

            // Inserting a new brick from the sidebar
            const brickId = e.dataTransfer.getData('brick')

            if (brickId && canDrop(target, brickId)) {
                postToParent({
                    type: 'insertBlockRequest',
                    brickId,
                    parent: target.parent,
                    position: target.index,
                })
            }
        })
    })()
</script>
