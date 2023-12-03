import { startStimulusApp } from '@symfony/stimulus-bridge';
import Clipboard from 'stimulus-clipboard';
import AnimatedNumber from 'stimulus-animated-number'

// Registers Stimulus controllers from controllers.json and in the controllers/ directory
export const app = startStimulusApp(require.context(
    '@symfony/stimulus-bridge/lazy-controller-loader!./controllers',
    true,
    /\.[jt]sx?$/
));
// register any custom, 3rd party controllers here
// app.register('some_controller_name', SomeImportedController);

app.register('clipboard', Clipboard);
app.register('animated-number', AnimatedNumber);
