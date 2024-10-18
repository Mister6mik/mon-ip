import { startStimulusApp } from '@symfony/stimulus-bundle'

const app = startStimulusApp()

import { Dropdown, Toggle } from 'tailwindcss-stimulus-components'

app.register('dropdown', Dropdown)
app.register('toggle', Toggle)