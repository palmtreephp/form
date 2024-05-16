type PalmtreeFormOptions = {
    url: string;
    method: string;
    removeSubmitButton: boolean;
    controlStates: string[];
}

const defaults: PalmtreeFormOptions = {
    url: '',
    method: 'GET',
    removeSubmitButton: true,
    controlStates: ['valid', 'invalid']
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll<HTMLFormElement>('.palmtree-form.is-ajax').forEach((form) => {
        palmtreeForm(form);
    });
});

const palmtreeForm = (form: HTMLFormElement, options: Partial<PalmtreeFormOptions> = {}) => {
    const config = { ...defaults, ...options };
    const submitBtn = form.querySelector<HTMLInputElement>('button[type="submit"]');

    function handleResponse(response) {
        const formControls = form.querySelectorAll('.palmtree-form-control');

        // Clear all form control states
        clearState(formControls);

        if (!response.success) {
            const errors = response.data.errors || null;

            setControlStates(formControls, errors);

            var $first = formControls.filter('.is-invalid').first();
            $first.trigger('focus');

            if (response.data.message) {
                _this.showAlert(response.data.message, 'danger');
            }

            form.trigger(this.getEvent('error'), {
                responseData: response.data
            });

            return false;
        }
    }

    const setControlStates = (formControls: NodeListOf<HTMLElement>, errors) => {
        formControls.forEach((formControl) => {
            const errorKey = formControl.data('name');
            const formGroup = formControl.closest('.form-group');
            let feedback = formGroup?.querySelectorAll('.palmtree-invalid-feedback');

            if (errors && errorKey && typeof errors[errorKey] !== 'undefined') {
                if (!feedback) {
                    feedback = $(_this.$form.data('invalid_element'));
                }

                feedback.innerHTML = errors[errorKey];
                formGroup.append(feedback);

                setState(formControl, 'invalid');

                const listener = () => {
                    const state = formControl.value.length ? '' : 'invalid';
                    setState(formControl, state);
                };

                ['input', 'change'].forEach((eventName) => {
                    formControl.removeEventListener(`${eventName}.palmtreeForm`, listener);
                    formControl.addEventListener(`${eventName}.palmtreeForm`, listener);
                })
            } else {
                clearState(formControl);
            }
        });
    }

    const clearState = (formControls: NodeListOf<Element>) => {
        setState(formControls, '');
    }

    const setState = (formControls: NodeListOf<Element>, state: string) => {
        formControls.forEach((formControl) => {
            const formGroup = formControl.closest('.form-group');
            const feedback = formGroup.find('.palmtree-invalid-feedback');

            // Remove all states first.
            for (var i = 0; i < config.controlStates.length; i++) {
                formControl.removeClass('is-' + _this.options.controlStates[i]);
            }

            if (!state) {
                feedback.hide();
            } else if (config.controlStates.indexOf(state) > -1) {
                formControl.classList.add('is-' + state);
                feedback.show();
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

        const json = response.json();

        handleResponse(json);

        if (response.ok) {
            //form.reset();
        } else {
            console.error(json);
        }

        form.classList.remove('is-submitting');
    });
}
