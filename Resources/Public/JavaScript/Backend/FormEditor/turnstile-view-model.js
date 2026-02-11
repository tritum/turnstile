/**
 * Module: @tritum/turnstile/Backend/FormEditor/turnstile-view-model.js
 */
import $ from 'jquery';
import * as Helper from '@typo3/form/backend/form-editor/helper.js';

let formEditorApp = null;

function getPublisherSubscriber() {
    return formEditorApp.getPublisherSubscriber();
}

function assert(test, message, messageCode) {
    return formEditorApp.assert(test, message, messageCode);
}

function helperSetup() {
    assert(
        typeof Helper.bootstrap === 'function',
        'The view model helper does not implement the method "bootstrap"',
        1491643380
    );
    Helper.bootstrap(formEditorApp);
}

function subscribeEvents() {
    getPublisherSubscriber().subscribe('view/stage/abstract/render/template/perform', (topic, args) => {
        if (args[0].get('type') === 'Turnstile') {
            formEditorApp.getViewModel().getStage().renderSimpleTemplateWithValidators(args[0], args[1]);
        }
    });
}

export function bootstrap(app) {
    formEditorApp = app;
    helperSetup();
    subscribeEvents();
}
