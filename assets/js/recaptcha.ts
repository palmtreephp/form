declare interface Window {
    [key: string]: any;

    grecaptcha: any;
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll<HTMLFormElement>('.palmtree-form').forEach(function (form) {
        const element = form.querySelector<HTMLElement>('.g-recaptcha-autoload');

        if (element && element.dataset.onload && element.dataset.script_url) {
            window[element.dataset.onload] = () => {
                const widgetId = window.grecaptcha.render(element.id, {
                    sitekey: element.dataset.site_key,
                    callback: (response: string) => {
                        const formControl = document.querySelector<HTMLInputElement>(`#${element.dataset.form_control}`);
                        if (formControl) {
                            formControl.value = response;

                            if (form.palmtreeForm('isInitialized')) {
                                form.palmtreeForm('clearState', formControl);
                            }
                        }
                    }
                });

                ['error', 'success'].forEach((event) => {
                    form.addEventListener(`${event}.palmtreeForm`, () => {
                        window.grecaptcha.reset(widgetId);
                    });
                });
            };

            const script = document.createElement('script');
            script.async = true;
            script.defer = true;
            script.src = element.dataset.script_url;

            document.head.appendChild(script);
        }
    });
});
