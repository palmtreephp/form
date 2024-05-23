import { elementFromHtml } from "./element-from-html.ts";
import { useBootstrapAlerts } from "./bootstrap-alerts.ts";

type PalmtreeFormOptions = {
    url: string;
    method: string;
    removeSubmitButton: boolean;
    controlStates: string[];
}

type Response = {
    success: boolean;
    data: {
        message: string;
        errors: Record<string, string>;
    }

}

const defaults: PalmtreeFormOptions = {
    url: '',
    method: 'GET',
    removeSubmitButton: true,
    controlStates: ['valid', 'invalid']
}

type FormControl = HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement;

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll<HTMLFormElement>('.palmtree-form.is-ajax').forEach((form) => {
        palmtreeForm(form);
    });
});

export const palmtreeForm = (form: HTMLFormElement, options: Partial<PalmtreeFormOptions> = {}) => {
    const config = { ...defaults, ...options };
    const submitBtn = form.querySelector<HTMLInputElement>('button[type="submit"]');

    const createAlert = useBootstrapAlerts(form);

    const handleResponse = function (response: Response) {
        const formControls = [...form.querySelectorAll<FormControl>('.palmtree-form-control')]

        // Clear all form control states
        clearState(formControls);

        if (!response.success) {
            const errors = response.data.errors || null;

            setControlStates(formControls, errors);

            const firstInvalidFormControl = formControls.find((formControl) => formControl.classList.contains('is-invalid'));
            firstInvalidFormControl?.focus();

            if (response.data.message) {
                createAlert(response.data.message, 'danger');
            }

            form.dispatchEvent(new CustomEvent('palmtreeForm.error', {
                detail: {
                    responseData: response.data
                }
            }));

            return false;
        }
    };

    const setControlStates = (formControls: FormControl[], errors: Record<string, string>) => {
        formControls.forEach((formControl) => {
            const errorKey = formControl.dataset.name;
            const formGroup = formControl.closest('.form-group');

            if (formGroup && errors && errorKey && typeof errors[errorKey] !== 'undefined') {
                let feedback = formGroup.querySelector('.palmtree-invalid-feedback');

                if (!feedback) {
                    feedback = elementFromHtml(form.dataset.invalid_element || '');
                }

                feedback.innerHTML = errors[errorKey];
                formGroup.append(feedback);

                setState([formControl], 'invalid');

                const listener = () => {
                    const state = formControl.value.length ? '' : 'invalid';
                    setState([formControl], state);
                };

                ['input', 'change'].forEach((eventName) => {
                    formControl.removeEventListener(`${eventName}.palmtreeForm`, listener);
                    formControl.addEventListener(`${eventName}.palmtreeForm`, listener);
                })
            } else {
                clearState([formControl]);
            }
        });
    }

    const clearState = (formControls: FormControl[]) => {
        setState(formControls, '');
    }

    const setState = (formControls: FormControl[], state: string) => {
        formControls.forEach((formControl) => {
            const formGroup = formControl.closest('.form-group');
            if (formGroup) {
                config.controlStates.forEach((controlState) => {
                    formControl.classList.remove(`is-${controlState}`);
                });

                const feedback = formGroup.querySelector('.palmtree-invalid-feedback');

                if (!state) {
                    feedback?.classList.add('d-none');
                } else if (config.controlStates.includes(state)) {
                    formControl.classList.add(`is-${state}`);
                    feedback?.classList.remove('d-none');
                }
            }
        });
    }

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        form.classList.add('is-submitting');

        if (submitBtn) {
            submitBtn.disabled = true;
        }

        const formData = new FormData(form);

        const response = await fetch(form.action, {
            method: form.method,
            body: formData,
        });

        const json: Response = await response.json();

        handleResponse(json);

        if (response.ok) {
            //form.reset();
        } else {
            console.error(json);
        }

        form.classList.remove('is-submitting');
    });
}
