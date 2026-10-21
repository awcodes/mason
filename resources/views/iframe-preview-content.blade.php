<div id="mason-preview-container">
    @if (empty($blocks))
        <div
            class="mason-drop-zone mason-drop-zone-start"
            data-drop-index="0"
            data-drop-end
        >
            <span>{{ __('mason::mason.preview.placeholder') }}</span>
            @include('mason::components.inserter')
        </div>
    @else
        {{-- Between bricks the add line shows on hover, after the last one always --}}
        <div
            class="mason-drop-zone"
            data-drop-index="0"
            style="min-height: 2rem"
        >
            @include('mason::components.inserter')
        </div>
        @foreach ($blocks as $block)
            <div
                class="mason-block"
                draggable="true"
                data-block-index="{{ $block['index'] }}"
                data-brick-id="{{ $block['id'] }}"
                data-config="{{ json_encode($block['config']) }}"
                data-total-blocks="{{ count($blocks) }}"
            >
                <div
                    class="mason-block-controls"
                    data-focus="mason-block-controls"
                >
                    <button
                        class="mason-block-btn"
                        title="Move Up"
                        data-action="move-up"
                        data-block-index="{{ $block['index'] }}"
                        data-total-blocks="{{ count($blocks) }}"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <title>
                                {{ __('mason::mason.preview.move_up') }}
                            </title>
                            <polyline points="18 15 12 9 6 15"></polyline>
                        </svg>
                    </button>
                    <button
                        class="mason-block-btn"
                        title="Move Down"
                        data-action="move-down"
                        data-block-index="{{ $block['index'] }}"
                        data-total-blocks="{{ count($blocks) }}"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <title>
                                {{ __('mason::mason.preview.move_down') }}
                            </title>
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </button>
                    <button
                        class="mason-block-btn"
                        title="{{ __('mason::mason.preview.add') }}"
                        data-action="add"
                        data-focus-action="mason-add-brick"
                    >
                        @include('mason::components.icons.plus', ['title' => __('mason::mason.preview.add')])
                    </button>
                    {{-- An unregistered brick cannot be rendered again, so it is not copied --}}
                    @if ($block['registered'] ?? true)
                        <button
                            class="mason-block-btn"
                            title="{{ __('mason::mason.preview.duplicate') }}"
                            data-action="duplicate"
                        >
                            @include('mason::components.icons.duplicate', ['title' => __('mason::mason.preview.duplicate')])
                        </button>
                    @endif

                    {{-- Bricks without a form have nothing to edit --}}
                    @if ($block['editable'] ?? true)
                        <button
                            class="mason-block-btn"
                            title="Edit"
                            data-action="edit"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                                class="size-5"
                            >
                                <title>
                                    {{ __('mason::mason.preview.edit') }}
                                </title>
                                <path
                                    d="m5.433 13.917 1.262-3.155A4 4 0 0 1 7.58 9.42l6.92-6.918a2.121 2.121 0 0 1 3 3l-6.92 6.918c-.383.383-.84.685-1.343.886l-3.154 1.262a.5.5 0 0 1-.65-.65Z"
                                />
                                <path
                                    d="M3.5 5.75c0-.69.56-1.25 1.25-1.25H10A.75.75 0 0 0 10 3H4.75A2.75 2.75 0 0 0 2 5.75v9.5A2.75 2.75 0 0 0 4.75 18h9.5A2.75 2.75 0 0 0 17 15.25V10a.75.75 0 0 0-1.5 0v5.25c0 .69-.56 1.25-1.25 1.25h-9.5c-.69 0-1.25-.56-1.25-1.25v-9.5Z"
                                />
                            </svg>
                        </button>
                    @endif

                    <button
                        class="mason-block-btn"
                        title="Delete"
                        data-action="delete"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                            class="size-5"
                        >
                            <title>
                                {{ __('mason::mason.preview.delete') }}
                            </title>
                            <path
                                fill-rule="evenodd"
                                d="M8.75 1A2.75 2.75 0 0 0 6 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 1 0 .23 1.482l.149-.022.841 10.518A2.75 2.75 0 0 0 7.596 19h4.807a2.75 2.75 0 0 0 2.742-2.53l.841-10.52.149.023a.75.75 0 0 0 .23-1.482A41.03 41.03 0 0 0 14 4.193V3.75A2.75 2.75 0 0 0 11.25 1h-2.5ZM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4ZM8.58 7.72a.75.75 0 0 0-1.5.06l.3 7.5a.75.75 0 1 0 1.5-.06l-.3-7.5Zm4.34.06a.75.75 0 1 0-1.5-.06l-.3 7.5a.75.75 0 1 0 1.5.06l.3-7.5Z"
                                clip-rule="evenodd"
                            />
                        </svg>
                    </button>
                </div>
                <div class="mason-block-content">
                    {!! $block['html'] ?? '' !!}
                </div>
            </div>
            <div
                class="mason-drop-zone"
                data-drop-index="{{ $block['index'] + 1 }}"
                @if ($loop->last) data-drop-end @endif
            >
                @include('mason::components.inserter')
            </div>
        @endforeach
    @endif
</div>

<script>
    // This will be replaced by the actual iframe-handler.js content
    // For now, include basic postMessage handling
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

        // Listen for messages from parent
        window.addEventListener('message', function (event) {
            // Only the editor around this preview sends commands
            if (event.source !== window.parent) return

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
                case 'insertBlock':
                    insertBlock(data.brick, data.position)
                    break
                case 'updateBlock':
                    updateBlock(data.index, data.brick)
                    break
                case 'deleteBlock':
                    deleteBlock(data.index)
                    break
                case 'moveBlock':
                    moveBlock(data.from, data.to)
                    break
                case 'selectBlock':
                    selectBlock(data.index, data.scroll)
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

            container.classList.toggle('mason-disabled', isDisabled)

            // Out of the tab order too, not only hidden
            container.querySelectorAll('.mason-inserter').forEach((button) => {
                button.disabled = isDisabled
            })
        }

        function insertBlock(brick, position) {
            postToParent({
                type: 'insertBlockRequest',
                brick,
                position,
            })
        }

        function updateBlock(index, brick) {
            postToParent({
                type: 'updateBlockRequest',
                index,
                brick,
            })
        }

        function deleteBlock(index) {
            postToParent({
                type: 'deleteBlockRequest',
                index,
            })
        }

        function moveBlock(from, to) {
            postToParent({
                type: 'moveBlockRequest',
                from,
                to,
            })
        }

        function selectBlock(index, scroll = false) {
            // Remove previous selection
            if (selectedBlock) {
                selectedBlock.classList.remove('selected')
            }

            const block = container.querySelector(
                `[data-block-index="${index}"]`,
            )
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

        function updateMoveButtons(block) {
            if (!block) return

            const index = parseInt(block.getAttribute('data-block-index'))
            const allBlocks = container.querySelectorAll('.mason-block')
            const totalBlocks = allBlocks.length

            const moveUpBtn = block.querySelector('[data-action="move-up"]')
            const moveDownBtn = block.querySelector('[data-action="move-down"]')

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

        // Update move buttons for all blocks on load
        function updateAllMoveButtons() {
            const blocks = container.querySelectorAll('.mason-block')
            blocks.forEach((block, idx) => {
                // Update the data-block-index to match current position
                block.setAttribute('data-block-index', idx.toString())
                updateMoveButtons(block)
            })
        }

        // Handle block clicks for editing
        container.addEventListener('click', function (e) {
            // The add line of a drop zone opens the brick picker at its position
            const inserter = e.target.closest('[data-action="add-at"]')
            if (inserter) {
                // Only the round button adds, not the hover strip around it; keyboard clicks have no position
                if (
                    e.detail !== 0 &&
                    !e.target.closest('.mason-inserter-icon')
                ) {
                    return
                }

                const insertIndex = parseInt(
                    inserter
                        .closest('.mason-drop-zone')
                        ?.getAttribute('data-drop-index'),
                )

                if (!isDisabled && !isNaN(insertIndex)) {
                    postToParent({ type: 'openBrickPicker', insertIndex })
                }

                return
            }

            const block = e.target.closest('.mason-block')
            if (!block) return

            const action = e.target.closest('[data-action]')
            if (action) {
                const actionType = action.getAttribute('data-action')
                const index = parseInt(block.getAttribute('data-block-index'))
                const brickId = block.getAttribute('data-brick-id')
                const config = JSON.parse(
                    block.getAttribute('data-config') || '{}',
                )

                if (actionType === 'edit') {
                    postToParent({
                        type: 'editBlock',
                        index,
                        brickId,
                        config,
                    })
                } else if (actionType === 'duplicate') {
                    postToParent({
                        type: 'duplicateBlockRequest',
                        index,
                    })
                } else if (actionType === 'delete') {
                    postToParent({
                        type: 'deleteBlockRequest',
                        index,
                    })
                } else if (actionType === 'move-up') {
                    if (!action.disabled) {
                        postToParent({
                            type: 'moveBlockRequest',
                            from: index,
                            to: index - 1,
                        })
                    }
                } else if (actionType === 'move-down') {
                    if (!action.disabled) {
                        postToParent({
                            type: 'moveBlockRequest',
                            from: index,
                            to: index + 1,
                        })
                    }
                } else if (actionType === 'add') {
                    postToParent({
                        type: 'openBrickPicker',
                        blockIndex: index,
                    })
                }
            } else {
                // Click on block content - select it
                const index = parseInt(block.getAttribute('data-block-index'))
                selectBlock(index)
                postToParent({ type: 'blockSelected', index })
            }
        })

        // Handle double-click to edit
        container.addEventListener('dblclick', function (e) {
            if (!dblClickToEdit) return

            const block = e.target.closest('.mason-block')
            if (!block) return

            // Bricks without a form have no edit control and nothing to edit
            if (!block.querySelector('[data-action="edit"]')) return

            // Ignore double-clicks on controls
            if (e.target.closest('.mason-block-controls')) {
                return
            }

            // Only trigger on block content, not on buttons
            if (e.target.closest('[data-action]')) {
                return
            }

            const index = parseInt(block.getAttribute('data-block-index'))
            const brickId = block.getAttribute('data-brick-id')
            const config = JSON.parse(block.getAttribute('data-config') || '{}')

            // Trigger edit action
            postToParent({
                type: 'editBlock',
                index,
                brickId,
                config,
            })
        })

        // Handle drag and drop for repositioning blocks
        let draggedBlockIndex = null
        let draggedBlock = null
        let dragOverIndex = null

        // The drop zones catch the pointer only while something is dragged over the preview,
        // a brick of the preview or one from the sidebar
        function setDragging(dragging) {
            container.classList.toggle('mason-dragging', dragging)
        }

        // Time of the last drag event; dragover keeps firing while a drag is over the preview
        let lastDragEvent = 0

        document.addEventListener('dragenter', () => setDragging(true), true)
        document.addEventListener(
            'dragover',
            () => {
                lastDragEvent = performance.now()
                setDragging(true)
            },
            true,
        )
        document.addEventListener(
            'dragleave',
            (e) => {
                // Leaving the preview
                if (!e.relatedTarget) setDragging(false)
            },
            true,
        )
        document.addEventListener('drop', () => setDragging(false), true)
        document.addEventListener('dragend', () => setDragging(false), true)
        // A drag can end without these, e.g. when cancelled outside the preview: a move without
        // a pressed button means it is over. Safari sends such moves during a drag too, so only
        // once no drag event has come for a while.
        document.addEventListener('pointermove', (e) => {
            if (e.buttons !== 0 || performance.now() - lastDragEvent < 1000) {
                return
            }

            setDragging(false)

            if (draggedBlock) endBlockDrag()
        })

        let pointerDownTarget = null
        document.addEventListener(
            'pointerdown',
            (e) => {
                pointerDownTarget = e.target
            },
            true,
        )

        // Make blocks draggable for repositioning
        container.addEventListener('dragstart', function (e) {
            const block = e.target.closest('.mason-block')
            if (!block) return

            // Prevent dragging from the controls; the event's target is the block itself, so check where the pointer went down
            if (pointerDownTarget?.closest('.mason-block-controls')) {
                e.preventDefault()
                return
            }

            // Ensure we're dragging the block
            if (
                !block.hasAttribute('draggable') ||
                block.getAttribute('draggable') !== 'true'
            ) {
                return
            }

            draggedBlock = block
            draggedBlockIndex = parseInt(block.getAttribute('data-block-index'))

            if (isNaN(draggedBlockIndex)) {
                return
            }

            block.classList.add('dragging')

            // Disable all interactions during drag
            document.body.style.userSelect = 'none'
            document.body.style.webkitUserSelect = 'none'
            document.body.style.mozUserSelect = 'none'
            document.body.style.msUserSelect = 'none'

            // Disable pointer events on all content inside the block
            const blockContent = block.querySelector('.mason-block-content')
            if (blockContent) {
                blockContent.style.pointerEvents = 'none'
                blockContent.style.userSelect = 'none'
                blockContent.style.webkitUserSelect = 'none'
                blockContent.style.mozUserSelect = 'none'
                blockContent.style.msUserSelect = 'none'
            }

            // Set drag data
            e.dataTransfer.effectAllowed = 'move'
            e.dataTransfer.setData('text/plain', draggedBlockIndex.toString())
        })

        container.addEventListener('dragend', endBlockDrag)

        // Ends moving a brick: after dragend, and on drop, which comes before dragend
        // and could otherwise leave the brick unclickable when it does not move
        function endBlockDrag() {
            // Re-enable interactions
            document.body.style.userSelect = ''
            document.body.style.webkitUserSelect = ''
            document.body.style.mozUserSelect = ''
            document.body.style.msUserSelect = ''

            if (draggedBlock) {
                draggedBlock.classList.remove('dragging')
                const blockContent = draggedBlock.querySelector(
                    '.mason-block-content',
                )
                if (blockContent) {
                    blockContent.style.pointerEvents = ''
                    blockContent.style.userSelect = ''
                    blockContent.style.webkitUserSelect = ''
                    blockContent.style.mozUserSelect = ''
                    blockContent.style.msUserSelect = ''
                }
            }

            // Clear all active drop zones and outlines
            container
                .querySelectorAll('.mason-drop-zone.active')
                .forEach((zone) => {
                    zone.classList.remove('active')
                })
            container.querySelectorAll('.mason-block').forEach((block) => {
                block.style.outline = ''
                block.style.outlineOffset = ''
            })

            draggedBlock = null
            draggedBlockIndex = null
            dragOverIndex = null
        }

        // Handle drag over for drop zones (inserting new blocks from sidebar)
        container.addEventListener('dragover', function (e) {
            e.preventDefault()

            // Check if this is a block being repositioned
            if (draggedBlockIndex !== null) {
                // Handle block repositioning
                const dropZone = e.target.closest('.mason-drop-zone')
                const block = e.target.closest('.mason-block')

                if (dropZone) {
                    // Clear all active zones
                    container
                        .querySelectorAll('.mason-drop-zone.active')
                        .forEach((zone) => {
                            zone.classList.remove('active')
                        })

                    const targetIndex = parseInt(
                        dropZone.getAttribute('data-drop-index'),
                    )

                    // Don't highlight if dropping at same position or immediately after
                    // Allow dropping at index 0 (before first block)
                    if (
                        !isNaN(targetIndex) &&
                        targetIndex !== draggedBlockIndex &&
                        targetIndex !== draggedBlockIndex + 1
                    ) {
                        dropZone.classList.add('active')
                        dragOverIndex = targetIndex
                    }
                } else if (block) {
                    // Allow dropping on blocks (will insert after)
                    const blockIndex = parseInt(
                        block.getAttribute('data-block-index'),
                    )
                    if (blockIndex !== draggedBlockIndex) {
                        block.style.outline = '2px dashed #0ea5e9'
                        block.style.outlineOffset = '2px'
                    }
                }
            } else {
                // Handle inserting new blocks from sidebar
                const dropZone = e.target.closest('.mason-drop-zone')
                if (dropZone) {
                    dropZone.classList.add('active')
                    dragOverIndex = parseInt(
                        dropZone.getAttribute('data-drop-index'),
                    )
                }
            }
        })

        container.addEventListener('dragleave', function (e) {
            const dropZone = e.target.closest('.mason-drop-zone')
            const block = e.target.closest('.mason-block')

            // Moving onto the zone's own placeholder or add line does not leave it
            if (dropZone && !dropZone.contains(e.relatedTarget)) {
                dropZone.classList.remove('active')
            }

            if (block && draggedBlockIndex !== null) {
                block.style.outline = ''
                block.style.outlineOffset = ''
            }
        })

        container.addEventListener('drop', function (e) {
            e.preventDefault()
            e.stopPropagation()

            // Clear all active zones and outlines
            container
                .querySelectorAll('.mason-drop-zone.active')
                .forEach((zone) => {
                    zone.classList.remove('active')
                })
            container.querySelectorAll('.mason-block').forEach((block) => {
                block.style.outline = ''
                block.style.outlineOffset = ''
            })

            if (draggedBlockIndex !== null) {
                // Handle block repositioning
                // Try to find drop zone first (more precise)
                let dropZone = e.target.closest('.mason-drop-zone')
                // If not found, check if we're over a drop zone element directly
                if (
                    !dropZone &&
                    e.target.classList.contains('mason-drop-zone')
                ) {
                    dropZone = e.target
                }

                // The dragged brick still takes the pointer (see .dragging), dropping it on itself keeps it in place
                const target = e.target.closest('.mason-block')
                const block = target === draggedBlock ? null : target

                let targetIndex = null

                if (dropZone) {
                    const dropIndex = parseInt(
                        dropZone.getAttribute('data-drop-index'),
                    )
                    if (!isNaN(dropIndex)) {
                        // Drop zone index represents insertion position in the original array
                        targetIndex = dropIndex
                    }
                } else if (block) {
                    // Dropping on a block - insert after it
                    const blockIndex = parseInt(
                        block.getAttribute('data-block-index'),
                    )
                    if (!isNaN(blockIndex)) {
                        targetIndex = blockIndex + 1
                    }
                }

                // Prevent dropping at the same position
                if (
                    targetIndex !== null &&
                    !isNaN(targetIndex) &&
                    targetIndex !== draggedBlockIndex
                ) {
                    const allBlocks = container.querySelectorAll('.mason-block')
                    const totalBlocks = allBlocks.length

                    // Send the target index as position in the original array
                    // handleMoveBlock will adjust for the removal
                    if (targetIndex >= 0 && targetIndex <= totalBlocks) {
                        postToParent({
                            type: 'moveBlockRequest',
                            from: draggedBlockIndex,
                            to: targetIndex,
                        })
                    }
                }

                endBlockDrag()
            } else {
                // Handle inserting new blocks from sidebar
                const dropZone = e.target.closest('.mason-drop-zone')
                if (dropZone) {
                    const position = parseInt(
                        dropZone.getAttribute('data-drop-index'),
                    )
                    const brickId = e.dataTransfer.getData('brick')

                    if (brickId && !isNaN(position)) {
                        postToParent({
                            type: 'insertBlockRequest',
                            brickId,
                            position,
                        })
                    }
                }
            }
        })
    })()
</script>
