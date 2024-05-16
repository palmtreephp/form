type BootstrapAlertsOptions = {
    dismissible?: boolean;
    position?: 'top' | 'bottom';
}

const defaults: BootstrapAlertsOptions = {
    dismissible: true,
    position: 'top',
}

export function useBootstrapAlerts(element: HTMLElement, options: Partial<BootstrapAlertsOptions> = {}) {
    const config: BootstrapAlertsOptions = { ...defaults, ...options };

    return function createAlert(type: string, message: string): void {
        const alert = document.createElement('div');

        alert.setAttribute('role', 'alert');

        alert.classList.add('alert');
        alert.classList.add(`alert-${type}`);

        alert.textContent = message;

        if (config.dismissible) {
            alert.classList.add('alert-dismissible');
            alert.classList.add('fade');
            alert.classList.add('show');

            const button = document.createElement('button');

            button.setAttribute('type', 'button');
            button.setAttribute('aria-label', 'Close');

            button.classList.add('btn-close');

            button.addEventListener('click', () => {
                alert.remove();
            });

            alert.appendChild(button);
        }

        if (config.position === 'top') {
            element.prepend(alert);
        } else {
            element.append(alert);
        }
    }
}
