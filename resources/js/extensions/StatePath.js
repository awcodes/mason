import {Extension} from '@tiptap/core'

export default Extension.create({
    name: 'statePath',

    addOptions() {
        return {
            statePath: null,
        }
    },

    addStorage() {
        return {
            statePath: null
        }
    },

    onBeforeCreate() {
        this.storage.statePath = this.options.statePath
    },

    addCommands() {
        return {
            getStatePath: () => () => {
                return this.storage.statePath
            },
        }
    },
})
