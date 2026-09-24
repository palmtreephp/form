import { elementFromHtml } from "./element-from-html";

type BootstrapAlertsOptions = {
    dismissible: boolean;
    position: InsertPosition;
};

const defaults: BootstrapAlertsOptions = {
    dismissible: true,
    position: "afterbegin",
};

export function useBootstrapAlerts(element: HTMLElement) {
    return function createAlert(message: string, type = "success", options: Partial<BootstrapAlertsOptions> = {}): void {
        if (type === "danger" && typeof options.dismissible === "undefined") {
            options.dismissible = false;
        }

        const config: BootstrapAlertsOptions = { ...defaults, ...options };

        const alert = document.createElement("div");
        alert.setAttribute("role", "alert");
        alert.classList.add("alert", `alert-${type}`);
        alert.textContent = message;

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
