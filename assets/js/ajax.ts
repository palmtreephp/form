import { elementFromHtml } from "./element-from-html";
import { useBootstrapAlerts } from "./bootstrap-alerts";

type PalmtreeFormOptions = {
    url: string;
    method: string;
    removeSubmitButton: boolean;
    controlStates: string[];
};

type Response = {
    success: boolean;
    data: {
        message: string;
        errors: Record<string, string>;
    };
};

const defaults: PalmtreeFormOptions = {
    url: "",
    method: "GET",
    removeSubmitButton: true,
    controlStates: ["valid", "invalid"],
};

type FormControl = HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement;

document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll<HTMLFormElement>(".palmtree-form.is-ajax").forEach((form) => {
        ajax(form);
    });
});

type PalmtreeFormInstance = {
    clearState: (formControls: FormControl[]) => void;
};

const instances = new Map<HTMLFormElement, PalmtreeFormInstance>();

declare global {
    interface Window {
        palmtreeForm: {
            getInstance: (form: HTMLFormElement) => PalmtreeFormInstance | undefined;
        };
    }
}

window.palmtreeForm = {
    getInstance: (form: HTMLFormElement) => instances.get(form),
};

export const ajax = (form: HTMLFormElement, options: Partial<PalmtreeFormOptions> = {}) => {
    const config = { ...defaults, ...options };
    const submitBtn = form.querySelector<HTMLInputElement>('button[type="submit"]');

    const createAlert = useBootstrapAlerts(form);

    const setControlStates = (formControls: FormControl[], errors: Record<string, string>) => {
        formControls.forEach((formControl) => {
            const errorKey = formControl.dataset.name;
            const formGroup = formControl.closest(".form-group");

            if (formGroup && errors && errorKey && typeof errors[errorKey] !== "undefined") {
                let feedback = formGroup.querySelector(".palmtree-invalid-feedback");

                if (!feedback) {
                    feedback = elementFromHtml(form.dataset.invalid_element || "");
                }

                feedback.innerHTML = errors[errorKey];
                formGroup.append(feedback);

                setState([formControl], "invalid");

                const listener = () => {
                    const state = formControl.value.length ? "" : "invalid";
                    setState([formControl], state);
                };

                ["input", "change"].forEach((eventName) => {
                    formControl.removeEventListener(`${eventName}.palmtreeForm`, listener);
                    formControl.addEventListener(`${eventName}.palmtreeForm`, listener);
                });
            } else {
                clearState([formControl]);
            }
        });
    };

    const clearState = (formControls: FormControl[]) => {
        setState(formControls, "");
    };

    const setState = (formControls: FormControl[], state: string) => {
        formControls.forEach((formControl) => {
            const formGroup = formControl.closest(".form-group");
            if (formGroup) {
                config.controlStates.forEach((controlState) => {
                    formControl.classList.remove(`is-${controlState}`);
                });

                const feedback = formGroup.querySelector(".palmtree-invalid-feedback");

                if (!state) {
                    feedback?.classList.add("d-none");
                } else if (config.controlStates.includes(state)) {
                    formControl.classList.add(`is-${state}`);
                    feedback?.classList.remove("d-none");
                }
            }
        });
    };

    form.addEventListener("submit", async (event) => {
        event.preventDefault();

        form.querySelectorAll(".alert").forEach((alert) => {
            alert.remove();
        });

        form.classList.add("is-submitting");

        if (submitBtn) {
            submitBtn.disabled = true;
        }

        const formData = new FormData(form);

        const response = await fetch(form.action, {
            method: form.method,
            body: formData,
            headers: {
                "X-Requested-With": "XMLHttpRequest",
            },
        });

        const json: Response = await response.json();

        const formControls = Array.from(form.querySelectorAll<FormControl>(".palmtree-form-control"));

        // Clear all form control states
        clearState(formControls);

        if (json.success) {
            form.reset();

            if (json.data.message) {
                createAlert(json.data.message);
            }

            if (config.removeSubmitButton) {
                form.querySelectorAll("[type=submit]").forEach((button) => {
                    button.remove();
                });
            }

            form.dispatchEvent(
                new CustomEvent("palmtreeForm.success", {
                    detail: {
                        responseData: json.data,
                    },
                }),
            );
        } else {
            const errors = json.data.errors || null;

            setControlStates(formControls, errors);

            const firstInvalidFormControl = formControls.find((formControl) => formControl.classList.contains("is-invalid"));
            firstInvalidFormControl?.focus();

            if (json.data.message) {
                createAlert(json.data.message, "danger");
            }

            form.dispatchEvent(
                new CustomEvent("palmtreeForm.error", {
                    detail: {
                        responseData: json.data,
                    },
                }),
            );
        }

        if (!response.ok) {
            console.error(response.statusText);
        }

        form.classList.remove("is-submitting");
        if (submitBtn) {
            submitBtn.disabled = false;
        }
    });

    instances.set(form, { clearState });
};
