type RecaptchaApi = {
    render: (elementId: string, options: Record<string, unknown>) => number;
    reset: (widgetId: number) => void;
};

type Config = {
    siteKey: string;
    scriptSrc: string;
    formControlId: string;
    type: "grecaptcha" | "hcaptcha";
    onLoadCallbackName: string;
};

interface Window {
    [key: string]: any;

    grecaptcha: RecaptchaApi;
    hcaptcha: RecaptchaApi;
}

document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll<HTMLFormElement>(".palmtree-form").forEach((form) => {
        const element = form.querySelector<HTMLElement>(".palmtree-captcha-autoload");

        if (!element) {
            return;
        }

        const config = JSON.parse(element.dataset.palmtreeFormCaptcha || "") as Config;

        if (!config) {
            console.error("Invalid configuration");
            return;
        }

        if (config.type !== "grecaptcha" && config.type !== "hcaptcha") {
            console.error(`Captcha type ${config.type} is not supported.`);
            return;
        }

        window[config.onLoadCallbackName] = () => {
            const api = window[config.type];

            const widgetId = api.render(element.id, {
                sitekey: config.siteKey,
                callback: (response: string) => {
                    const formControl = document.querySelector<HTMLInputElement>(`#${config.formControlId}`);
                    if (formControl) {
                        formControl.value = response;

                        const palmtreeForm = window.palmtreeForm.getInstance(form);

                        if (palmtreeForm) {
                            palmtreeForm.clearState([formControl]);
                        }
                    }
                },
            });

            ["error", "success"].forEach((event) => {
                form.addEventListener(`palmtreeForm.${event}`, () => {
                    api.reset(widgetId);
                });
            });
        };

        const script = document.createElement("script");
        script.async = true;
        script.defer = true;
        script.src = config.scriptSrc;

        document.head.append(script);
    });
});
