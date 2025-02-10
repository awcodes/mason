import { Extension } from "@tiptap/core";
import { Plugin, PluginKey } from '@tiptap/pm/state'

const DragAndDropPlugin = ({ editor }) => {
    return new Plugin({
        key: new PluginKey('dragAndDrop'),
        props: {
            handleDrop(view, event) {
                if (!event) return false

                event.preventDefault()

                const coordinates = view.posAtCoords({
                    left: event.clientX,
                    top: event.clientY,
                })

                if (event.dataTransfer.getData('brickIdentifier')) {
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
            },
        },
    })
}

export default Extension.create({
    name: 'dragAndDrop',
    addProseMirrorPlugins() {
        return [
            DragAndDropPlugin({
                editor: this.editor,
            })
        ]
    },
})
