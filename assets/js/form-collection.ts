import { elementFromHtml } from "./element-from-html";

export type FormCollectionOptions = {
    entrySelector: string;
    addLabel: string;
    minEntries: number;
    maxEntries: number;
};

const defaults: FormCollectionOptions = {
    addLabel: "Add",
    entrySelector: ".palmtree-form-collection-entries > .palmtree-form-collection-entry",
    minEntries: 0,
    maxEntries: -1,
};

document.addEventListener("DOMContentLoaded", () => {
    formCollection(document.querySelectorAll("[data-palmtree-form-collection]"));

    document.body.addEventListener("palmtreeFormCollection.add", (event) => {
        formCollection((event as CustomEvent).detail.$entry.querySelectorAll("[data-palmtree-form-collection]"));
    });
});

export function formCollection(widgets: NodeListOf<HTMLElement>): void {
    widgets.forEach((widget) => {
        new FormCollection(widget);
    });
}

class FormCollection {
    private readonly options: FormCollectionOptions;
    private widget: HTMLElement;
    private addButton: HTMLButtonElement;

    constructor(widget: HTMLElement) {
        this.widget = widget;
        this.options = {
            ...defaults,
            ...JSON.parse(this.widget.dataset.palmtreeFormCollection || ""),
        };

        this.addButton = this.createAddButton();

        this.widget.querySelectorAll(this.options.entrySelector).forEach((entry) => {
            this.createRemoveButton(entry);
        });

        if (this.options.minEntries > 0 && this.length < this.options.minEntries) {
            for (let i = 0; i < this.options.minEntries; i++) {
                this.addEntry();
            }
        }
    }

    get length(): number {
        return this.widget.querySelectorAll(this.options.entrySelector).length;
    }

    addEntry() {
        if (this.options.maxEntries > -1 && this.length >= this.options.maxEntries) {
            return;
        }

        const index = this.widget.dataset.index || this.length;
        const html = this.widget.dataset.prototype?.replace(/\[-1]/g, `[${index}]`).replace(/(id|for)="(\S+)--1"/g, `$1="$2-${index}"`) || "";

        const entry = elementFromHtml(html);

        this.createRemoveButton(entry);

        this.widget.querySelector(".palmtree-form-collection-entries")?.append(entry);

        this.widget.dataset.index = (parseInt(index.toString()) + 1).toString();

        const event = new CustomEvent("palmtreeFormCollection.add", {
            detail: { entry },
        });

        this.widget.dispatchEvent(event);

        if (this.options.maxEntries > -1 && this.length >= this.options.maxEntries) {
            this.addButton.disabled = true;
        }
    }

    removeEntry(entry: Element) {
        if (this.options.minEntries > 0 && this.length <= this.options.minEntries) {
            return;
        }

        entry.remove();

        const event = new CustomEvent("palmtreeFormCollection.remove", {
            detail: { entry },
        });

        this.widget.dispatchEvent(event);

        if (this.options.maxEntries > -1 && this.length < this.options.maxEntries) {
            this.addButton.disabled = false;
        }
    }

    createAddButton(): HTMLButtonElement {
        const addButton = elementFromHtml<HTMLButtonElement>(`<button type="button" class="btn btn-secondary btn-sm">${this.options.addLabel}</button>`);

        addButton.addEventListener("click", () => {
            this.addEntry();
        });

        const wrapper = elementFromHtml('<section class="d-flex justify-content-end palmtree-form-collection-bottom"></section>');

        wrapper.append(addButton);

        this.widget.append(wrapper);

        return addButton;
    }

    createRemoveButton(entry: Element) {
        const removeButton = elementFromHtml('<button type="button" class="btn btn-close" title="Remove"></button>');

        entry.append(removeButton);

        removeButton.addEventListener("click", () => {
            this.removeEntry(entry);
        });
    }
}
