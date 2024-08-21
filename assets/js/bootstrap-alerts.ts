import { elementFromHtml } from "./element-from-html";

type BootstrapAlertsOptions = {
    dismissible: boolean;
    position: InsertPosition;
};

const defaults: BootstrapAlertsOptions = {
    dismissible: true,
    position: "beforebegin",
};

export function useBootstrapAlerts(element: HTMLElement) {
    return function createAlert(message: string, type = "success", options: Partial<BootstrapAlertsOptions> = {}): void {
        if (type === "danger" && typeof options.dismissible === "undefined") {
            options.dismissible = false;
        }

        const config: BootstrapAlertsOptions = { ...defaults, ...options };

        const alert = elementFromHtml(`<div role="alert" class="alert alert-${type}">${message}</div>`);

        if (config.dismissible) {
            alert.classList.add("alert-dismissible", "fade", "show");

            const button = elementFromHtml(`<button type="button" class="btn-close" aria-label="Close"></button>`);

            button.addEventListener("click", () => {
                alert.remove();
            });

            alert.append(button);
        }

        element.insertAdjacentElement(config.position, alert);
    };
}
