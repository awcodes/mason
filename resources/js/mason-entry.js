export default function masonEntryComponent({
    state,
    context = null,
    data = null,
}) {
    let iframe = null

    return {
        async init() {
            this.$nextTick(() => {
                iframe = this.$refs.entryIframe

                if (iframe) {
                    this.updatePreview()
                }
            })
        },

        getBlocksFromState() {
            if (!state) {
                return []
            }

            let stateArray = []
            try {
                stateArray = JSON.parse(JSON.stringify(state))
            } catch (e) {
                if (Array.isArray(state)) {
                    stateArray = Array.from(state)
                }
            }

            if (!Array.isArray(stateArray)) {
                return []
            }

            return stateArray.filter(
                (block) => block && block.type === 'masonBrick',
            )
        },

        updatePreview() {
            const blocks = this.getBlocksFromState()

            let plainBlocks = blocks
            try {
                plainBlocks = JSON.parse(JSON.stringify(blocks))
            } catch (e) {
                plainBlocks = blocks
            }

            // Create a form that will submit to the iframe
            const form = document.createElement('form')
            form.method = 'POST'
            form.action = '/mason/entry'
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

            // Add the encrypted render data if provided
            if (data) {
                const dataInput = document.createElement('input')
                dataInput.type = 'hidden'
                dataInput.name = 'data'
                dataInput.value = data
                form.appendChild(dataInput)
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

            setTimeout(() => {
                if (form.parentNode) {
                    form.parentNode.removeChild(form)
                }
            }, 100)
        },
    }
}
