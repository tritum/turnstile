/**
 * Module: @tritum/turnstile/Backend/FormEditor/TurnstileViewModel.js
 *
 * TYPO3 v13 Form Framework: ViewModel module for custom FormEditor element "Turnstile".
 * Registers a stage render hook to render the SimpleTemplate incl. validators.
 */
import * as Helper from '@typo3/form/backend/form-editor/helper.js';

let formEditorApp = null;

/**
 * @returns {import('@typo3/form/backend/form-editor/app.js').FormEditorApp|any}
 */
function getApp() {
    return formEditorApp;
}

function assert(test, message, messageCode) {
    return getApp().assert(test, message, messageCode);
}

function getPublisherSubscriber() {
    return getApp().getPublisherSubscriber();
}

function setupHelper() {
    assert(
        typeof Helper.bootstrap === 'function',
        'The view model helper does not implement the method "bootstrap"',
        1491643380
    );
    Helper.bootstrap(getApp());
}

function handleRenderTemplate(topic, args) {
    // args[0] = formElement
    // args[1] = template
    const [formElement, template] = Array.isArray(args) ? args : [];

    if (!formElement || typeof formElement.get !== 'function') {
        return;
    }

    if (formElement.get('type') !== 'Turnstile') {
        return;
    }

    getApp()
        .getViewModel()
        .getStage()
        .renderSimpleTemplateWithValidators(formElement, template);
}

function subscribeEvents() {
    getPublisherSubscriber().subscribe(
        'view/stage/abstract/render/template/perform',
        handleRenderTemplate
    );
}

/**
 * Entry point for TYPO3 FormEditor module loading.
 *
 * @param {object} app
 */
export function bootstrap(app) {
    formEditorApp = app;

    // basic sanity guard: fail early with TYPO3's assert handling
    assert(!!formEditorApp, 'No form editor app instance provided', 1700000001);

    setupHelper();
    subscribeEvents();
}
