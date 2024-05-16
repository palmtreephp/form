declare interface Window {
    grecaptcha: any;
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll<HTMLFormElement>('.palmtree-form').forEach(function (form) {
        const element = form.querySelector<HTMLElement>('.g-recaptcha-autoload');

        if (element.dataset.onload && element.dataset.script_url) {
            window[element.dataset.onload] = function () {
                const widgetId = window.grecaptcha.render(element.id, {
                    sitekey: element.dataset.site_key,
                    callback: function (response) {
                        const formControl = document.querySelector<HTMLInputElement>('#' + element.dataset.form_control);
                        formControl.value = response;

                        if (form.palmtreeForm('isInitialized')) {
                            form.palmtreeForm('clearState', formControl);
                        }
                    }
                });

                ['error', 'success'].forEach((event)  => {
                    form.addEventListener(`${event}.palmtreeForm`, () => {
                        window.grecaptcha.reset(widgetId);
                    });
                });
            };
        }
    });
});
